<?php

session_start();


if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login_page.php");
    exit();
}


include 'koneksi.php';


$nama_petugas_session = $_SESSION['nama_petugas'];
$username_petugas_session = $_SESSION['username'];
$id_petugas_session = $_SESSION['id_petugas'];


$sql = "SELECT p.id_peminjaman, a.nama AS nama_anggota, b.judul AS judul_buku, pet.nama_petugas,
                p.tanggal_pinjam, p.tanggal_kembali, p.status, p.id_buku, p.id_anggota
        FROM peminjaman p
        JOIN anggota a ON p.id_anggota = a.id_anggota
        JOIN buku b ON p.id_buku = b.id_buku
        LEFT JOIN petugas pet ON p.id_petugas = pet.id_petugas
        ORDER BY p.tanggal_pinjam DESC";
$result = $koneksi->query($sql);

$peminjaman_data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $peminjaman_data[] = $row;
    }
}


$anggota_sql = "SELECT id_anggota, nama FROM anggota ORDER BY nama ASC";
$anggota_result = $koneksi->query($anggota_sql);
$anggota_list = [];
if ($anggota_result->num_rows > 0) {
    while ($row = $anggota_result->fetch_assoc()) {
        $anggota_list[] = $row;
    }
}


$buku_sql = "SELECT id_buku, judul, stok FROM buku WHERE stok > 0 ORDER BY judul ASC";
$buku_result = $koneksi->query($buku_sql);
$buku_list = [];
if ($buku_result->num_rows > 0) {
    while ($row = $buku_result->fetch_assoc()) {
        $buku_list[] = $row;
    }
}



$message = '';
$message_type = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Peminjaman | LibSys</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* --- General Body & Layout --- */
        body {
            font-family: 'Inter', sans-serif;
            background: #9295d0;
            min-height: 100vh;
            padding: 1.5rem;
            overflow-x: hidden;
            overflow-y: auto;
            display: flex;
            gap: 1.5rem;
            box-sizing: border-box;
            margin: 0;
        }

        /* --- Sidebar Styling (Glassmorphism) - Consistent with Dashboard --- */
        .sidebar {
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 2.5rem;
            box-shadow: 0 15px 30px -8px rgba(0, 0, 0, 0.15), 0 8px 15px -4px rgba(0, 0, 0, 0.08);
            backdrop-filter: blur(20px) saturate(1.5);
            border: 2px solid rgba(255, 255, 255, 0.6);
            padding: 2.5rem;
            width: 280px;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            animation: fadeInLeft 0.8s ease-out forwards;
            transform: translateX(-20px);
            opacity: 0;
        }

        @keyframes fadeInLeft {
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .sidebar-header {
            font-size: 2.2rem;
            font-weight: 800;
            color: #1F2937;
            margin-bottom: 2rem;
            text-align: center;
            letter-spacing: -0.02em;
        }

        .sidebar-header img {
            width: 100px;
            height: 100px;
            object-fit: contain;
            margin: 0 auto;
            display: block;
        }

        .sidebar nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-item {
            display: flex;
            align-items: center;
            padding: 0.9rem 1.25rem;
            border-radius: 1rem;
            color: #4B5563;
            font-weight: 600;
            transition: all 0.2s cubic-bezier(0.25, 0.8, 0.25, 1);
            position: relative;
            overflow: hidden;
            margin-bottom: 0.75rem;
            text-decoration: none;
        }

        .sidebar-item:hover {
            background-color: rgba(255, 255, 255, 0.2);
            color: #1F2937;
            transform: translateX(5px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .sidebar-item.active {
            background: #9295d0;
            color: white;
            font-weight: 700;
            box-shadow: 0 4px 12px #9295d0;
            transform: translateX(5px);
        }

        .sidebar-item.active:hover {
            background: #9295d0;
            color: white;
        }

        .sidebar-item i {
            margin-right: 1rem;
            font-size: 1.2rem;
            width: 1.5rem;
            text-align: center;
        }

        .logout-button {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 1rem 1.5rem;
            border-radius: 1rem;
            font-size: 1.1rem;
            font-weight: 700;
            background: #9295d0;
            color: white;
            box-shadow: 0 4px 12px #9295d0;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            position: relative;
            overflow: hidden;
            cursor: pointer;
            text-decoration: none;
            border: none;
            margin-top: 2rem;
        }

        .logout-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px #9295d0;
        }

        .logout-button i {
            margin-right: 0.75rem;
        }

        /* Ripple effect */
        .ripple {
            position: absolute;
            border-radius: 50%;
            transform: scale(0);
            animation: ripple 0.6s linear;
            background-color: rgba(255, 255, 255, 0.7);
            pointer-events: none;
        }

        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }

        /* --- Main Content Wrapper --- */
        .main-content-wrapper {
            flex: 1;
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 2.5rem;
            box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.35), 0 20px 40px -10px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(30px) saturate(1.8);
            border: 2px solid rgba(255, 255, 255, 0.7);
            padding: 3.5rem;
            overflow-y: auto;
            animation: fadeInRight 0.8s ease-out forwards;
            transform: translateX(20px);
            opacity: 0;
        }

        @keyframes fadeInRight {
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .main-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #E5E7EB;
        }

        .main-header-title {
            font-size: 3rem;
            font-weight: 800;
            color: #1F2937;
            letter-spacing: -0.03em;
        }

        .welcome-message {
            font-size: 1.1rem;
            color: #4B5563;
            font-weight: 500;
        }

        .welcome-message span {
            color: #9295d0;
            font-weight: 700;
        }

        /* Message Boxes */
        .message-box {
            padding: 1.2rem;
            border-radius: 0.75rem;
            margin-bottom: 2rem;
            font-weight: 600;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: fadeIn 0.5s ease-out;
        }

        .message-box.error {
            background-color: #9fb7e3;
            border: 1px solid #9295d0;
            color: #991B1B;
        }

        .message-box.success {
            background-color: #D1FAE5;
            border: 1px solid #A7F3D0;
            color: #065F46;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* --- Card Data Table --- */
        .card-data-table {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 2.5rem;
            box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.35), 0 20px 40px -10px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(30px) saturate(1.8);
            border: 2px solid rgba(255, 255, 255, 0.7);
            padding: 2.5rem;
            margin-bottom: 2rem;
        }

        .card-data-table h2 {
            font-size: 2rem;
            font-weight: 700;
            color: #1F2937;
            margin-bottom: 1.5rem;
        }

        .table-controls {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 1.5rem;
        }

        .table-container {
            overflow-x: auto;
            border-radius: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            overflow: hidden;
            min-width: 900px;
        }

        th,
        td {
            padding: 1rem 1.25rem;
            text-align: left;
            border-bottom: 1px solid #E5E7EB;
            font-size: 0.95rem;
            color: #374151;
        }

        th {
            background-color: #F9FAFB;
            font-weight: 700;
            color: #1F2937;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.05em;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        thead th:first-child {
            border-top-left-radius: 1.5rem;
        }

        thead th:last-child {
            border-top-right-radius: 1.5rem;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        tbody tr:hover {
            background-color: rgba(243, 244, 246, 0.4);
        }

        td {
            font-weight: 500;
        }

        .text-center-col {
            text-align: center;
        }

        .whitespace-nowrap {
            white-space: nowrap;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-weight: 700;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            display: inline-block;
        }

        .status-dipinjam {
            background-color: #FEF3C7;
            color: #92400E;
        }

        .status-dikembalikan {
            background-color: #9295d0;
            color: #0a111a;
        }

        /* Action Buttons (Table) */
        .action-btn {
            background-color: #d8d0e7;
            color: white;
            padding: 0.6rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .btn-add {
            background-color: #294a8f;
            color: white;
        }

        .btn-add:hover {
            background-color: #1f3a74;
        }

        .btn-return {
            background-color: #6a82fb;
            color: white;
        }

        .btn-return:hover {
            background-color: #5a6ebe;
        }

        /* --- Modal Styles (Consistent with other pages) --- */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            justify-content: center;
            align-items: center;
            overflow-y: auto;
            animation: fadeInOverlay 0.3s forwards;
        }

        @keyframes fadeInOverlay {
            from {
                background-color: rgba(0, 0, 0, 0);
            }

            to {
                background-color: rgba(0, 0, 0, 0.6);
            }
        }

        .modal-content {
            background-color: rgba(255, 255, 255, 0.98);
            border-radius: 1.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(25px) saturate(1.5);
            border: 1px solid rgba(255, 255, 255, 0.8);
            padding: 2.5rem;
            width: 100%;
            max-width: 450px;
            position: relative;
            text-align: center;
            max-height: 90vh;
            overflow-y: auto;
            animation: modalPopIn 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
            transform: scale(0.8);
            opacity: 0;
        }

        @keyframes modalPopIn {
            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .close-button {
            color: #9CA3AF;
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            font-size: 2rem;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.2s ease;
            background: none;
            border: none;
        }

        .close-button:hover,
        .close-button:focus {
            color: #4B5563;
        }

        .modal-icon {
            color: #294a8f;
            font-size: 4rem;
            margin-bottom: 1.5rem;
        }

        .modal-title-text {
            font-size: 2.25rem;
            font-weight: 700;
            color: #1F2937;
            margin-bottom: 1rem;
        }

        .modal-text {
            color: #4B5563;
            font-size: 1rem;
            margin-bottom: 2rem;
        }

        .modal-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            text-align: left;
        }

        .modal-form-label {
            display: block;
            text-align: left;
            font-size: 0.9rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .modal-form-input {
            width: 100%;
            padding: 0.9rem 1.2rem;
            border-radius: 0.75rem;
            border: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            font-size: 1rem;
            outline: none;
            transition: all 0.3s ease;
            color: #1F2937;
        }

        .modal-form-input:focus {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15), 0 0 0 4px rgba(255, 111, 145, 0.3);
        }

        .modal-buttons-container {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .modal-button {
            padding: 0.8rem 1.8rem;
            border-radius: 0.75rem;
            font-weight: 700;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .modal-button.cancel {
            background-color: #E5E7EB;
            color: #4B5563;
        }

        .modal-button.cancel:hover {
            background-color: #D1D5DB;
            transform: translateY(-2px);
        }

        .modal-button.confirm {
            background-color: #294a8f;
            color: white;
        }

        .modal-button.confirm:hover {
            background-color: #1f3a74;
            transform: translateY(-2px);
        }

        .btn-red-confirm {
            background-color: #EF4444;
            color: white;
        }

        .btn-red-confirm:hover {
            background-color: #DC2626;
        }

        /* Responsive Adjustments */
        @media (max-width: 1024px) {
            body {
                flex-direction: column;
                padding: 1rem;
                gap: 1rem;
            }

            .sidebar {
                width: 100%;
                max-width: none;
                padding: 1.5rem;
                border-radius: 1.5rem;
                flex-direction: row;
                flex-wrap: wrap;
                justify-content: center;
                animation: fadeInDown 0.8s ease-out forwards;
            }

            @keyframes fadeInDown {
                to {
                    transform: translateY(0);
                    opacity: 1;
                }
            }

            .sidebar-header {
                width: 100%;
                margin-bottom: 1rem;
                font-size: 1.8rem;
            }

            .sidebar nav {
                width: 100%;
            }

            .sidebar ul {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                gap: 0.5rem;
            }

            .sidebar-item {
                margin-bottom: 0;
                padding: 0.8rem 1rem;
                font-size: 0.9rem;
            }

            .sidebar-item i {
                margin-right: 0.5rem;
                font-size: 1rem;
                width: 1.2rem;
            }

            .logout-button {
                width: auto;
                margin-top: 1rem;
                padding: 0.8rem 1.5rem;
                font-size: 0.95rem;
            }

            .main-content-wrapper {
                padding: 2rem;
                border-radius: 1.5rem;
                animation: fadeInUp 0.8s ease-out forwards;
                transform: translateY(20px);
                opacity: 0;
            }

            @keyframes fadeInUp {
                to {
                    transform: translateY(0);
                    opacity: 1;
                }
            }

            .main-header {
                flex-direction: column;
                align-items: flex-start;
                margin-bottom: 1.5rem;
            }

            .main-header-title {
                font-size: 2.5rem;
                margin-bottom: 0.5rem;
            }

            .welcome-message {
                font-size: 1rem;
            }

            .card-data-table {
                padding: 2rem;
            }

            th,
            td {
                padding: 0.8rem;
                font-size: 0.85rem;
            }
        }

        @media (max-width: 640px) {
            body {
                padding: 0.5rem;
            }

            .sidebar {
                padding: 1rem;
                border-radius: 1rem;
                justify-content: space-around;
            }

            .sidebar ul {
                gap: 0.3rem;
            }

            .sidebar-item {
                padding: 0.6rem 0.8rem;
                font-size: 0.8rem;
            }

            .sidebar-item i {
                margin-right: 0.3rem;
                font-size: 0.9rem;
            }

            .logout-button {
                padding: 0.6rem 1rem;
                font-size: 0.85rem;
            }

            .main-content-wrapper {
                padding: 1.5rem;
                border-radius: 1.5rem;
            }

            .main-header-title {
                font-size: 2rem;
            }

            .card-data-table {
                padding: 1.5rem;
            }

            th,
            td {
                font-size: 0.7rem;
                padding: 0.5rem;
            }

            .action-btn {
                font-size: 0.7rem;
                padding: 0.5rem 0.75rem;
            }

            .modal-content {
                padding: 1.5rem;
                border-radius: 1rem;
            }

            .modal-title-text {
                font-size: 1.8rem;
            }

            .modal-form-input {
                padding: 0.75rem 1rem;
                font-size: 0.9rem;
            }

            .modal-button {
                padding: 0.6rem 1.5rem;
                font-size: 0.9rem;
            }
        }
    </style>
</head>

<body>
    <aside class="sidebar">
        <div>
            <div class="sidebar-header">
                <img src="uploads/logo1.png" alt="Book Icon">
            </div>
            <nav>
                <ul>
                    <li>
                        <a href="dashboard.php" class="sidebar-item">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="data_buku.php" class="sidebar-item">
                            <i class="fas fa-book"></i>
                            <span>Data Buku</span>
                        </a>
                    </li>
                    <li>
                        <a href="data_anggota.php" class="sidebar-item">
                            <i class="fas fa-users"></i>
                            <span>Data Anggota</span>
                        </a>
                    </li>
                    <li>
                        <a href="data_petugas.php" class="sidebar-item">
                            <i class="fas fa-user-tie"></i>
                            <span>Data Petugas</span>
                        </a>
                    </li>
                    <li>
                        <a href="data_peminjaman.php" class="sidebar-item active">
                            <i class="fas fa-hand-holding"></i>
                            <span>Peminjaman</span>
                        </a>
                    </li>
                    <li>
                        <a href="data_pengembalian.php" class="sidebar-item">
                            <i class="fas fa-undo-alt"></i>
                            <span>Pengembalian</span>
                        </a>
                    </li>
                    <li>
                        <a href="data_kategori.php" class="sidebar-item">
                            <i class="fas fa-tags"></i>
                            <span>Kategori Buku</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        <div>
            <a href="logout.php" class="logout-button">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>
        </div>
    </aside>

    <main class="main-content-wrapper">
        <header class="main-header">
            <h1 class="main-header-title">Data Peminjaman</h1>
            <div class="welcome-message">
                Selamat datang, <span><?php echo htmlspecialchars($nama_petugas_session); ?></span>!
            </div>
        </header>

        <?php if ($message): ?>
            <div class="message-box <?php echo $message_type; ?>">
                <?php if ($message_type == 'success'): ?><i class="fas fa-check-circle"></i><?php else: ?><i class="fas fa-exclamation-circle"></i><?php endif; ?>
                <span><?php echo $message; ?></span>
            </div>
        <?php endif; ?>

        <section class="card-data-table">
            <div class="table-controls">
                <button id="addPeminjamanBtn" class="action-btn btn-add">
                    <i class="fas fa-plus"></i> Tambah Peminjaman
                </button>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Anggota</th>
                            <th>Buku</th>
                            <th>Petugas</th>
                            <th>Tanggal Pinjam</th>
                            <th>Tanggal Kembali (Rencana)</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($peminjaman_data)): ?>
                            <tr>
                                <td colspan="8" class="text-center-col" style="padding: 2rem; color: #6b7280; font-weight: normal;">Tidak ada data peminjaman yang tersedia.</td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1;
                            foreach ($peminjaman_data as $peminjaman): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($peminjaman['nama_anggota']); ?></td>
                                    <td><?php echo htmlspecialchars($peminjaman['judul_buku']); ?></td>
                                    <td><?php echo htmlspecialchars($peminjaman['nama_petugas'] ? $peminjaman['nama_petugas'] : '-'); ?></td>
                                    <td><?php echo htmlspecialchars($peminjaman['tanggal_pinjam']); ?></td>
                                    <td><?php echo htmlspecialchars($peminjaman['tanggal_kembali'] ? $peminjaman['tanggal_kembali'] : '-'); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo ($peminjaman['status'] == 'Dipinjam') ? 'status-dipinjam' : 'status-dikembalikan'; ?>">
                                            <?php echo htmlspecialchars($peminjaman['status']); ?>
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap">
                                        <?php if ($peminjaman['status'] == 'Dipinjam'): ?>
                                            <button class="action-btn btn-return"
                                                data-id="<?php echo htmlspecialchars($peminjaman['id_peminjaman']); ?>"
                                                data-judul="<?php echo htmlspecialchars($peminjaman['judul_buku']); ?>"
                                                data-anggota="<?php echo htmlspecialchars($peminjaman['nama_anggota']); ?>"
                                                onclick="openReturnModal(this)">
                                                <i class="fas fa-check-circle"></i> Kembalikan
                                            </button>
                                        <?php else: ?>
                                            <span style="color: #6b7280; font-style: italic; font-size: 0.875rem;">Selesai</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <div id="peminjamanModal" class="modal">
        <div class="modal-content">
            <button class="close-button" onclick="closeModal()">&times;</button>
            <i class="fas fa-hand-holding modal-icon"></i>
            <h2 id="modalTitle" class="modal-title-text">Tambah Peminjaman Baru</h2>
            <form id="peminjamanForm" action="proses_peminjaman.php" method="POST" class="modal-form">
                <input type="hidden" name="action" value="tambah">
                <input type="hidden" name="id_petugas" value="<?php echo htmlspecialchars($id_petugas_session); ?>">

                <div>
                    <label for="id_anggota" class="modal-form-label">Anggota</label>
                    <select id="id_anggota" name="id_anggota" class="modal-form-input" required>
                        <option value="">Pilih Anggota</option>
                        <?php foreach ($anggota_list as $anggota): ?>
                            <option value="<?php echo htmlspecialchars($anggota['id_anggota']); ?>">
                                <?php echo htmlspecialchars($anggota['nama']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="id_buku" class="modal-form-label">Buku</label>
                    <select id="id_buku" name="id_buku" class="modal-form-input" required>
                        <option value="">Pilih Buku</option>
                        <?php foreach ($buku_list as $buku): ?>
                            <option value="<?php echo htmlspecialchars($buku['id_buku']); ?>" data-stok="<?php echo htmlspecialchars($buku['stok']); ?>">
                                <?php echo htmlspecialchars($buku['judul']); ?> (Stok: <?php echo htmlspecialchars($buku['stok']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p id="stokWarning" style="color: #EF4444; font-size: 0.875rem; margin-top: 0.25rem; display: none; text-align: left; font-weight: 600;">Stok buku tidak tersedia.</p>
                </div>
                <div>
                    <label for="tanggal_pinjam" class="modal-form-label">Tanggal Pinjam</label>
                    <input type="date" id="tanggal_pinjam" name="tanggal_pinjam" class="modal-form-input" required>
                </div>
                <div>
                    <label for="tanggal_kembali_rencana" class="modal-form-label">Tanggal Kembali (Rencana)</label>
                    <input type="date" id="tanggal_kembali_rencana" name="tanggal_kembali" class="modal-form-input">
                </div>

                <div class="modal-buttons-container">
                    <button type="button" onclick="closeModal()" class="modal-button cancel">Batal</button>
                    <button type="submit" class="modal-button confirm">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div id="returnConfirmModal" class="modal">
        <div class="modal-content">
            <button class="close-button" onclick="closeReturnModal()">&times;</button>
            <i class="fas fa-undo-alt modal-icon"></i>
            <h2 class="modal-title-text">Konfirmasi Pengembalian</h2>
            <p class="modal-text">Apakah Anda yakin ingin mengembalikan buku "<span id="returnBookTitle" style="font-weight: 600;"></span>" yang dipinjam oleh <span id="returnAnggotaNama" style="font-weight: 600;"></span>?</p>
            <div class="modal-buttons-container">
                <button type="button" onclick="closeReturnModal()" class="modal-button cancel">Batal</button>
                <a id="confirmReturnBtn" href="#" class="modal-button confirm">Kembalikan</a>
            </div>
        </div>
    </div>

    <script>
        const peminjamanModal = document.getElementById('peminjamanModal');
        const returnConfirmModal = document.getElementById('returnConfirmModal');
        const addPeminjamanBtn = document.getElementById('addPeminjamanBtn');
        const peminjamanForm = document.getElementById('peminjamanForm');
        const modalTitle = document.getElementById('modalTitle');
        const stokWarning = document.getElementById('stokWarning');
        const selectBuku = document.getElementById('id_buku');
        const returnBookTitle = document.getElementById('returnBookTitle');
        const returnAnggotaNama = document.getElementById('returnAnggotaNama');
        const confirmReturnBtn = document.getElementById('confirmReturnBtn');


        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date();
            const year = today.getFullYear();
            let month = today.getMonth() + 1;
            let day = today.getDate();

            if (day < 10) day = '0' + day;
            if (month < 10) month = '0' + month;

            const formattedToday = year + '-' + month + '-' + day;
            document.getElementById('tanggal_pinjam').value = formattedToday;


            selectBuku.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];

                if (selectedOption.value) {
                    const stok = parseInt(selectedOption.dataset.stok);
                    if (stok <= 0) {
                        stokWarning.style.display = 'block';
                        peminjamanForm.querySelector('button[type="submit"]').disabled = true;
                    } else {
                        stokWarning.style.display = 'none';
                        peminjamanForm.querySelector('button[type="submit"]').disabled = false;
                    }
                } else {

                    stokWarning.style.display = 'none';
                    peminjamanForm.querySelector('button[type="submit"]').disabled = true;
                }
            });


            if (selectBuku.value) {
                const selectedOption = selectBuku.options[selectBuku.selectedIndex];
                if (selectedOption && parseInt(selectedOption.dataset.stok) <= 0) {
                    stokWarning.style.display = 'block';
                    peminjamanForm.querySelector('button[type="submit"]').disabled = true;
                }
            }
        });


        addPeminjamanBtn.onclick = function() {
            peminjamanModal.style.display = 'flex';
            peminjamanForm.reset();

            const today = new Date().toISOString().split('T')[0];
            document.getElementById('tanggal_pinjam').value = today;
            document.getElementById('tanggal_kembali_rencana').value = '';
            stokWarning.style.display = 'none';
            peminjamanForm.querySelector('button[type="submit"]').disabled = false;
        }


        function openReturnModal(button) {
            const id = button.dataset.id;
            const judul = button.dataset.judul;
            const anggota = button.dataset.anggota;
            returnBookTitle.textContent = judul;
            returnAnggotaNama.textContent = anggota;
            confirmReturnBtn.href = 'proses_peminjaman.php?action=kembalikan&id=' + id;
            returnConfirmModal.style.display = 'flex';
        }


        function closeModal() {
            peminjamanModal.style.display = 'none';
        }

        function closeReturnModal() {
            returnConfirmModal.style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == peminjamanModal) {
                closeModal();
            }
            if (event.target == returnConfirmModal) {
                closeReturnModal();
            }
        }


        document.querySelectorAll('.sidebar-item, .logout-button, .action-btn, .modal-button').forEach(button => {
            button.addEventListener('click', function(e) {
                const target = e.currentTarget;
                const rect = target.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;

                const ripple = document.createElement('span');
                ripple.classList.add('ripple');
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';

                const size = Math.max(rect.width, rect.height);
                ripple.style.width = ripple.style.height = size + 'px';

                const existingRipple = target.querySelector('.ripple');
                if (existingRipple) {
                    existingRipple.remove();
                }

                target.appendChild(ripple);

                ripple.addEventListener('animationend', () => {
                    ripple.remove();
                });
            });
        });
    </script>
</body>

</html>