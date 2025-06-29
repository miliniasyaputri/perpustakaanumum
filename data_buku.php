<?php

session_start();


if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login_page.php");
    exit();
}


include 'koneksi.php';


$nama_petugas = $_SESSION['nama_petugas'];
$username_petugas = $_SESSION['username'];


$sql = "SELECT id_buku, isbn, judul, penulis, penerbit, tahun_terbit, kategori, stok, gambar FROM buku ORDER BY judul ASC";
$result = $koneksi->query($sql);

$buku_data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $buku_data[] = $row;
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
    <title>Data Buku | LibSys</title>
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
            /* width: 100%; */
            /* Hapus atau sesuaikan */
            padding: 0.75rem 1.25rem;
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
            margin: 2rem auto 0 auto;
            /* Tengah-kan tombol */
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

        .message-box i {
            font-size: 1.5rem;
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
            min-width: 1000px;
            /* Adjust for more columns */
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

        .book-image-cell img {
            width: 4rem;
            /* w-16 */
            height: 5rem;
            /* h-20 */
            object-fit: cover;
            border-radius: 0.375rem;
            /* rounded-md */
        }

        .book-image-cell .fallback-image {
            width: 4rem;
            height: 5rem;
            background-color: #E5E7EB;
            /* bg-gray-200 */
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.375rem;
            color: #6B7280;
            /* text-gray-500 */
            font-size: 1.875rem;
            /* text-3xl */
        }

        .stok-high {
            color: #16A34A;
        }

        /* text-green-600 */
        .stok-medium {
            color: #F59E0B;
        }

        /* text-yellow-600 */
        .stok-low {
            color: #EF4444;
        }

        /* text-red-600 */

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

        .btn-edit {
            background-color: #9fb7e3;
            color: white;
            margin-right: 0.5rem;
        }

        .btn-edit:hover {
            background-color: #8da4cf;
        }

        .btn-delete {
            background-color: #9295d0;
            color: white;
        }

        .btn-delete:hover {
            background-color: #7d84d0;
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
            width: 90%;
            max-width: 500px;
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
            background-color: #9295d0;
            color: white;
        }

        .modal-button.confirm:hover {
            background-color: #7d84d0;
            transform: translateY(-2px);
        }

        .btn-red-confirm {
            background-color: #EF4444;
            color: white;
        }

        .btn-red-confirm:hover {
            background-color: #DC2626;
        }

        /* Custom styling for file input */
        .modal-form-input.file {
            background-color: white;
            cursor: pointer;
        }

        .modal-form-input.file::-webkit-file-upload-button {
            background-color: #E5E7EB;
            border: none;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-right: 1rem;
            cursor: pointer;
            font-weight: 600;
            color: #374151;
            transition: background-color 0.2s;
        }

        .modal-form-input.file::-webkit-file-upload-button:hover {
            background-color: #D1D5DB;
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
                <img src="uploads/logo1.png"
                    alt="Book Icon"
                    class="modal-icon"
                    style="width: 100px; height: 100px; object-fit: contain;">
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
                        <a href="data_buku.php" class="sidebar-item active">
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
                        <a href="data_peminjaman.php" class="sidebar-item">
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
            <h1 class="main-header-title">Data Buku</h1>
            <div class="welcome-message">
                Selamat datang, <span><?php echo htmlspecialchars($nama_petugas); ?></span>!
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
                <button id="addBookBtn" class="action-btn btn-add">
                    <i class="fas fa-plus"></i> Tambah Buku Baru
                </button>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Gambar</th>
                            <th>ISBN</th>
                            <th>Judul</th>
                            <th>Penulis</th>
                            <th>Penerbit</th>
                            <th>Tahun</th>
                            <th>Kategori</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($buku_data)): ?>
                            <tr>
                                <td colspan="10" class="text-center-col" style="padding: 2rem; color: #6b7280; font-weight: normal;">Tidak ada data buku yang tersedia.</td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1;
                            foreach ($buku_data as $buku): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td class="book-image-cell">
                                        <?php
                                        $gambar = isset($buku['gambar']) ? $buku['gambar'] : '';
                                        $path = "uploads/" . $gambar;
                                        ?>
                                        <?php if (!empty($gambar) && file_exists($path)): ?>
                                            <img src="<?= $path ?>" alt="Gambar Buku">
                                        <?php else: ?>
                                            <div class="fallback-image">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($buku['isbn']); ?></td>
                                    <td><?php echo htmlspecialchars($buku['judul']); ?></td>
                                    <td><?php echo htmlspecialchars($buku['penulis']); ?></td>
                                    <td><?php echo htmlspecialchars($buku['penerbit']); ?></td>
                                    <td><?php echo htmlspecialchars($buku['tahun_terbit']); ?></td>
                                    <td><?php echo htmlspecialchars($buku['kategori']); ?></td>
                                    <td>
                                        <span class="font-bold <?php echo ($buku['stok'] > 5) ? 'stok-high' : (($buku['stok'] > 0) ? 'stok-medium' : 'stok-low'); ?>">
                                            <?php echo htmlspecialchars($buku['stok']); ?>
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap">
                                        <button class="action-btn btn-edit"
                                            data-id="<?php echo $buku['id_buku']; ?>"
                                            data-isbn="<?php echo htmlspecialchars($buku['isbn']); ?>"
                                            data-judul="<?php echo htmlspecialchars($buku['judul']); ?>"
                                            data-penulis="<?php echo htmlspecialchars($buku['penulis']); ?>"
                                            data-penerbit="<?php echo htmlspecialchars($buku['penerbit']); ?>"
                                            data-tahun="<?php echo htmlspecialchars($buku['tahun_terbit']); ?>"
                                            data-kategori="<?php echo htmlspecialchars($buku['kategori']); ?>"
                                            data-stok="<?php echo htmlspecialchars($buku['stok']); ?>"
                                            data-gambar="<?php echo htmlspecialchars($buku['gambar'] ?? ''); ?>"
                                            onclick="openEditModal(this)">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="action-btn btn-delete"
                                            data-id="<?php echo $buku['id_buku']; ?>"
                                            data-judul="<?php echo htmlspecialchars($buku['judul']); ?>"
                                            onclick="openDeleteModal(this)">
                                            <i class="fas fa-trash-alt"></i> Hapus
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <div id="bookModal" class="modal">
        <div class="modal-content">
            <button class="close-button" onclick="closeModal()">&times;</button>
            <i class="fas fa-book modal-icon"></i>
            <h2 id="modalTitle" class="modal-title-text">Tambah Buku Baru</h2>
            <form id="bookForm" action="proses_buku.php" method="POST" class="modal-form" enctype="multipart/form-data">
                <input type="hidden" id="formAction" name="action" value="tambah">
                <input type="hidden" id="bookId" name="id_buku">

                <div>
                    <label for="isbn" class="modal-form-label">ISBN</label>
                    <input type="text" id="isbn" name="isbn" class="modal-form-input" required>
                </div>
                <div>
                    <label for="judul" class="modal-form-label">Judul</label>
                    <input type="text" id="judul" name="judul" class="modal-form-input" required>
                </div>
                <div>
                    <label for="penulis" class="modal-form-label">Penulis</label>
                    <input type="text" id="penulis" name="penulis" class="modal-form-input">
                </div>
                <div>
                    <label for="penerbit" class="modal-form-label">Penerbit</label>
                    <input type="text" id="penerbit" name="penerbit" class="modal-form-input">
                </div>
                <div>
                    <label for="tahun_terbit" class="modal-form-label">Tahun Terbit</label>
                    <input type="number" id="tahun_terbit" name="tahun_terbit" class="modal-form-input" min="1000" max="9999">
                </div>
                <div>
                    <label for="kategori" class="modal-form-label">Kategori</label>
                    <input type="text" id="kategori" name="kategori" class="modal-form-input">
                </div>
                <div>
                    <label for="stok" class="modal-form-label">Stok</label>
                    <input type="number" id="stok" name="stok" class="modal-form-input" required min="0">
                </div>
                <div>
                    <label for="gambar" class="modal-form-label">Unggah Gambar Cover (Opsional)</label>
                    <input type="file" id="gambar" name="gambar" class="modal-form-input file">
                    <p class="text-xs text-gray-500 mt-1 text-left">Format: JPG, PNG, GIF (Max 2MB)</p>
                    <p id="currentImageInfo" class="text-xs text-gray-600 mt-2 text-left hidden">Gambar saat ini: <span style="font-weight: 600;" id="currentImageUrl"></span></p>
                </div>

                <div class="modal-buttons-container">
                    <button type="button" onclick="closeModal()" class="modal-button cancel">Batal</button>
                    <button type="submit" class="modal-button confirm">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div id="deleteConfirmModal" class="modal">
        <div class="modal-content">
            <button class="close-button" onclick="closeDeleteModal()">&times;</button>
            <i class="fas fa-exclamation-triangle modal-icon" style="color: #EF4444;"></i>
            <h2 class="modal-title-text">Konfirmasi Hapus</h2>
            <p class="modal-text">Apakah Anda yakin ingin menghapus buku "<span id="deleteBookTitle" style="font-weight: 600;"></span>"?</p>
            <div class="modal-buttons-container">
                <button type="button" onclick="closeDeleteModal()" class="modal-button cancel">Batal</button>
                <a id="confirmDeleteBtn" href="#" class="modal-button btn-red-confirm">Hapus</a>
            </div>
        </div>
    </div>

    <script>
        const bookModal = document.getElementById('bookModal');
        const deleteConfirmModal = document.getElementById('deleteConfirmModal');
        const addBookBtn = document.getElementById('addBookBtn');
        const bookForm = document.getElementById('bookForm');
        const modalTitle = document.getElementById('modalTitle');
        const formAction = document.getElementById('formAction');
        const bookId = document.getElementById('bookId');
        const deleteBookTitle = document.getElementById('deleteBookTitle');
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        const gambarInput = document.getElementById('gambar');
        const currentImageInfo = document.getElementById('currentImageInfo');
        const currentImageUrlSpan = document.getElementById('currentImageUrl');


        addBookBtn.onclick = function() {
            modalTitle.textContent = 'Tambah Buku Baru';
            formAction.value = 'tambah';
            bookId.value = '';
            bookForm.reset();
            currentImageInfo.classList.add('hidden');
            gambarInput.required = false;
            bookModal.style.display = 'flex';
        }


        function openEditModal(button) {
            modalTitle.textContent = 'Edit Data Buku';
            formAction.value = 'edit';
            bookId.value = button.dataset.id;
            document.getElementById('isbn').value = button.dataset.isbn;
            document.getElementById('judul').value = button.dataset.judul;
            document.getElementById('penulis').value = button.dataset.penulis;
            document.getElementById('penerbit').value = button.dataset.penerbit;
            document.getElementById('tahun_terbit').value = button.dataset.tahun;
            document.getElementById('kategori').value = button.dataset.kategori;
            document.getElementById('stok').value = button.dataset.stok;


            gambarInput.value = '';
            gambarInput.required = false;

            const existingImageUrl = button.dataset.gambar;
            if (existingImageUrl) {
                currentImageUrlSpan.textContent = existingImageUrl.substring(existingImageUrl.lastIndexOf('/') + 1);
                currentImageInfo.classList.remove('hidden');
            } else {
                currentImageInfo.classList.add('hidden');
            }

            bookModal.style.display = 'flex';
        }


        function openDeleteModal(button) {
            const id = button.dataset.id;
            const judul = button.dataset.judul;
            deleteBookTitle.textContent = judul;
            confirmDeleteBtn.href = 'proses_buku.php?action=hapus&id=' + id;
            deleteConfirmModal.style.display = 'flex';
        }

        function closeModal() {
            bookModal.style.display = 'none';
        }

        function closeDeleteModal() {
            deleteConfirmModal.style.display = 'none';
        }


        window.onclick = function(event) {
            if (event.target == bookModal) {
                closeModal();
            }
            if (event.target == deleteConfirmModal) {
                closeDeleteModal();
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