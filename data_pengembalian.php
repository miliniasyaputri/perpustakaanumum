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


$sql = "SELECT pg.id_pengembalian, p.id_peminjaman, a.nama AS nama_anggota, b.judul AS judul_buku,
                p.tanggal_pinjam, pg.tanggal_dikembalikan, pg.denda, pet.nama_petugas AS petugas_kembali
        FROM pengembalian pg
        JOIN peminjaman p ON pg.id_peminjaman = p.id_peminjaman
        JOIN anggota a ON p.id_anggota = a.id_anggota
        JOIN buku b ON p.id_buku = b.id_buku
        LEFT JOIN petugas pet ON p.id_petugas = pet.id_petugas
        ORDER BY pg.tanggal_dikembalikan DESC";
$result = $koneksi->query($sql);

$pengembalian_data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pengembalian_data[] = $row;
    }
}

// Pesan sukses atau error dari operasi CRUD (jika ada, dari proses_peminjaman)
$message = '';
$message_type = ''; // success or error
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message']); // Hapus pesan setelah ditampilkan
    unset($_SESSION['message_type']);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pengembalian | LibSys</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
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

        /* Main Content Wrapper (Glassmorphism) */
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

        /* Table Card */
        .card-data {
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 1.5rem;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.7);
            padding: 2.5rem;
        }

        .table-container {
            overflow-x: auto;
            border-radius: 1rem;
            /* Smaller radius for table container */
            border: 1px solid rgba(229, 231, 235, 0.5);
            /* Light border */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            /* Subtle shadow */
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            overflow: hidden;
            min-width: 900px;
            /* Min-width to prevent squishing */
        }

        th,
        td {
            padding: 1rem 1.5rem;
            text-align: left;
            border-bottom: 1px solid rgba(229, 231, 235, 0.6);
            color: #374151;
            white-space: nowrap;
            /* Prevent text wrapping in cells */
        }

        th {
            background-color: rgba(249, 250, 251, 0.5);
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.875rem;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        thead th:first-child {
            border-top-left-radius: 1rem;
        }

        thead th:last-child {
            border-top-right-radius: 1rem;
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

        .denda-format {
            font-weight: 700;
            color: #EF4444;
        }

        .denda-nol {
            font-weight: 500;
            color: #4B5563;
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

            .card-data {
                padding: 1.5rem;
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

            .card-data {
                padding: 1.5rem;
            }

            th,
            td {
                font-size: 0.75rem;
                padding: 0.6rem;
            }
        }
    </style>
</head>

<body class="flex min-h-screen">
    <aside class="sidebar">
        <div>
            <div class="sidebar-header">
                <img src="uploads/logo1.png"
                    alt="Book Icon"
                    class="modal-icon mx-auto block"
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
                        <a href="data_peminjaman.php" class="sidebar-item">
                            <i class="fas fa-hand-holding"></i>
                            <span>Peminjaman</span>
                        </a>
                    </li>
                    <li>
                        <a href="data_pengembalian.php" class="sidebar-item active">
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
            <h1 class="main-header-title">Data Pengembalian</h1>
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

        <section class="card-data" style="margin-bottom: 2rem;">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>ID Peminjaman</th>
                            <th>Anggota</th>
                            <th>Buku</th>
                            <th>Tanggal Pinjam</th>
                            <th>Tanggal Dikembalikan</th>
                            <th>Denda</th>
                            <th>Petugas Penerima</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pengembalian_data)): ?>
                            <tr>
                                <td colspan="8" class="text-center-col" style="padding: 2rem; color: #6b7280; font-weight: normal;">Tidak ada data pengembalian yang tercatat.</td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1;
                            foreach ($pengembalian_data as $pengembalian): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td>#<?php echo htmlspecialchars($pengembalian['id_peminjaman']); ?></td>
                                    <td><?php echo htmlspecialchars($pengembalian['nama_anggota']); ?></td>
                                    <td><?php echo htmlspecialchars($pengembalian['judul_buku']); ?></td>
                                    <td><?php echo htmlspecialchars($pengembalian['tanggal_pinjam']); ?></td>
                                    <td><?php echo htmlspecialchars($pengembalian['tanggal_dikembalikan']); ?></td>
                                    <td>
                                        <span class="<?php echo ($pengembalian['denda'] > 0) ? 'denda-format' : 'denda-nol'; ?>">
                                            Rp <?php echo number_format($pengembalian['denda'], 0, ',', '.'); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($pengembalian['petugas_kembali'] ? $pengembalian['petugas_kembali'] : '-'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script>
        document.querySelectorAll('.sidebar-item, .logout-button, .action-btn').forEach(button => {
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

<?php

$koneksi->close();
?>