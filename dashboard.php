<?php

session_start();


if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login_page.php");
    exit();
}


include 'koneksi.php';


$nama_petugas = $_SESSION['nama_petugas'];
$username_petugas = $_SESSION['username'];


$sql_total_buku = "SELECT COUNT(*) AS stok FROM buku";
$result_total_buku = $koneksi->query($sql_total_buku);
$total_buku = $result_total_buku->fetch_assoc()['stok'];

$sql_total_anggota = "SELECT COUNT(*) AS total_anggota FROM anggota";
$result_total_anggota = $koneksi->query($sql_total_anggota);
$total_anggota = $result_total_anggota->fetch_assoc()['total_anggota'];

$sql_buku_dipinjam = "SELECT COUNT(*) AS total_dipinjam FROM peminjaman WHERE status = 'Dipinjam'";
$result_buku_dipinjam = $koneksi->query($sql_buku_dipinjam);
$total_dipinjam = $result_buku_dipinjam->fetch_assoc()['total_dipinjam'];


$sql_recent_books = "SELECT id_buku, judul, penulis, gambar FROM buku ORDER BY id_buku DESC LIMIT 4";
$result_recent_books = $koneksi->query($sql_recent_books);
$recent_books = [];
if ($result_recent_books->num_rows > 0) {
    while ($row = $result_recent_books->fetch_assoc()) {
        $recent_books[] = $row;
    }
}


$message = '';
$message_type = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];

    unset($_SESSION['message_type']);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Perpustakaan | The Pixel Library</title>
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

        /* --- Sidebar Styling (Glassmorphism) --- */
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
            margin-left: auto;
            margin-right: auto;
            display: block;
        }

        .sidebar nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar nav ul li {
            margin-bottom: 0.75rem;
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
            padding: 1rem;
            border-radius: 0.75rem;
            margin-bottom: 2rem;
            font-weight: 600;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .message-box.success {
            background-color: #D1FAE5;
            border: 1px solid #A7F3D0;
            color: #065F46;
        }

        .message-box.error {
            background-color: #FEE2E2;
            border: 1px solid #FCA5A5;
            color: #991B1B;
        }

        .message-box i {
            font-size: 1.5rem;
        }

        /* Welcome Section */
        .welcome-section {
            background: #9295d0;
            color: white;
            border-radius: 1.5rem;
            padding: 2.5rem;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            margin-bottom: 2.5rem;
        }

        .welcome-section h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            line-height: 1.2;
        }

        .welcome-section p {
            font-size: 1.1rem;
            font-weight: 400;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        @media (min-width: 768px) {

            /* md breakpoint */
            .stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (min-width: 1024px) {

            /* lg breakpoint */
            .stats-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        .stats-card {
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 1.5rem;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.7);
            padding: 2rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
            transition: transform 0.2s ease-out, box-shadow 0.2s ease-out;
            cursor: default;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
        }

        .stats-icon-wrapper {
            padding: 1rem;
            border-radius: 50%;
            font-size: 2.5rem;
            width: 4.5rem;
            height: 4.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .bg-blue-accent {
            background-color: #E0F2FE;
            color: #2196F3;
        }

        .bg-purple-accent {
            background-color: #EDE7F6;
            color: #673AB7;
        }

        .bg-green-accent {
            background-color: #E8F5E9;
            color: #4CAF50;
        }

        .stats-info div:first-child {
            font-size: 1.1rem;
            font-weight: 600;
            color: #4B5563;
            margin-bottom: 0.25rem;
        }

        .stats-info div:last-child {
            font-size: 2.5rem;
            font-weight: 800;
            color: #1F2937;
            line-height: 1;
        }

        /* Recent Activity / Quick Links Section */
        .recent-activity-section {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 1.5rem;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.7);
            padding: 2.5rem;
            margin-bottom: 2.5rem;
        }

        .recent-activity-section h2 {
            font-size: 2rem;
            font-weight: 700;
            color: #1F2937;
            margin-bottom: 1.5rem;
        }

        .activity-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 0.75rem 0;
            border-bottom: 1px dashed #E5E7EB;
            color: #4B5563;
            font-size: 1rem;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-item i {
            color: #FFB74D;
            font-size: 1.2rem;
            flex-shrink: 0;
            margin-top: 0.2rem;
        }

        .activity-item a {
            color: #4f46e5;
            text-decoration: none;
            transition: text-decoration 0.2s ease;
        }

        .activity-item a:hover {
            text-decoration: underline;
        }

        /* Recent Books Section */
        .recent-books-section {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 2.5rem;
            box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.35), 0 20px 40px -10px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(30px) saturate(1.8);
            border: 2px solid rgba(255, 255, 255, 0.7);
            padding: 2.5rem;
        }

        .recent-books-section h2 {
            font-size: 2rem;
            font-weight: 700;
            color: #1F2937;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .recent-books-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1.5rem;
        }

        @media (min-width: 640px) {

            /* sm breakpoint */
            .recent-books-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (min-width: 1024px) {

            /* lg breakpoint */
            .recent-books-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }

        /* Book Card Styles */
        .book-card-display {
            background-color: white;
            border-radius: 1.25rem;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.3s ease-out, box-shadow 0.3s ease-out;
            position: relative;
            overflow: hidden;
            border: 1px solid #E5E7EB;
            opacity: 0;
            transform: translateY(20px);
        }

        .book-card-display.animate-in {
            animation: cardFadeIn 0.6s ease-out forwards;
        }

        @keyframes cardFadeIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .book-card-display:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.25);
        }

        .book-image-container-display {
            width: 100%;
            height: 200px;
            overflow: hidden;
            border-radius: 0.75rem;
            margin-bottom: 1rem;
            position: relative;
        }

        .book-image-display {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease-out;
        }

        .book-card-display:hover .book-image-display {
            transform: scale(1.08);
        }

        .book-image-fallback-display {
            background-color: #CBD5E0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: #6B7280;
        }

        .book-title-display {
            font-size: 1.4rem;
            font-weight: 800;
            color: #1F2937;
            margin-bottom: 0.25rem;
            line-height: 1.2;
            min-height: 2.8rem;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .book-author-display {
            font-size: 0.9rem;
            color: #4B5563;
            font-weight: 500;
        }

        /* --- Responsive Adjustments --- */
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

            .welcome-section h2 {
                font-size: 2rem;
            }

            .welcome-section p {
                font-size: 1rem;
            }

            .stats-card {
                padding: 1.5rem;
            }

            .stats-icon-wrapper {
                font-size: 2rem;
                width: 3.5rem;
                height: 3.5rem;
            }

            .stats-info div:last-child {
                font-size: 2rem;
            }

            .recent-activity-section h2,
            .recent-books-section h2 {
                font-size: 1.8rem;
            }

            .book-image-container-display {
                height: 150px;
            }

            .book-title-display {
                font-size: 1.2rem;
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

            .welcome-section {
                padding: 1.5rem;
            }

            .welcome-section h2 {
                font-size: 1.8rem;
            }

            .welcome-section p {
                font-size: 0.9rem;
            }

            .stats-card {
                flex-direction: column;
                text-align: center;
            }

            .stats-icon-wrapper {
                margin-bottom: 0.5rem;
            }

            .recent-activity-section h2,
            .recent-books-section h2 {
                font-size: 1.5rem;
                margin-bottom: 1rem;
            }

            .activity-item {
                font-size: 0.9rem;
            }

            .book-card-display {
                padding: 1rem;
                border-radius: 1rem;
            }

            .book-image-container-display {
                height: 120px;
            }

            .book-title-display {
                font-size: 1.1rem;
            }

            .book-author-display {
                font-size: 0.8rem;
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
                        <a href="dashboard.php" class="sidebar-item active">
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
            <h1 class="main-header-title">Dashboard</h1>
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

        <section class="welcome-section">
            <h2>Sistem Informasi Perpustakaan LibSys</h2>
            <p>Kelola data buku, anggota, peminjaman, dan lainnya dengan mudah.</p>
        </section>

        <section class="stats-grid">
            <div class="stats-card">
                <div class="stats-icon-wrapper bg-blue-accent">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stats-info">
                    <div>Jumlah Buku</div>
                    <div><?php echo $total_buku; ?></div>
                </div>
            </div>

            <div class="stats-card">
                <div class="stats-icon-wrapper bg-purple-accent">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stats-info">
                    <div>Jumlah Anggota</div>
                    <div><?php echo $total_anggota; ?></div>
                </div>
            </div>

            <div class="stats-card">
                <div class="stats-icon-wrapper bg-green-accent">
                    <i class="fas fa-hand-holding"></i>
                </div>
                <div class="stats-info">
                    <div>Buku Dipinjam</div>
                    <div><?php echo $total_dipinjam; ?></div>
                </div>
            </div>
        </section>

        <section class="recent-activity-section">
            <h2>Aktivitas Terkini & Pintasan Cepat</h2>
            <ul>
                <li class="activity-item">
                    <i class="fas fa-circle-info"></i>
                    <span>Informasi terbaru tentang pembaruan sistem dan fitur baru.</span>
                </li>
                <li class="activity-item">
                    <i class="fas fa-bell"></i>
                    <span>Periksa data peminjaman yang akan jatuh tempo atau terlambat.</span>
                </li>
                <li class="activity-item">
                    <i class="fas fa-chart-line"></i>
                    <span>Lihat laporan bulanan perpustakaan dan statistik.</span>
                </li>
                <li class="activity-item">
                    <i class="fas fa-plus-circle"></i>
                    <a href="data_peminjaman.php">Catat Peminjaman Baru</a>
                </li>
                <li class="activity-item">
                    <i class="fas fa-plus-circle"></i>
                    <a href="data_buku.php">Tambah Buku Baru</a>
                </li>
            </ul>
        </section>

        <section class="recent-books-section">
            <h2>Buku Terbaru</h2>
            <div class="recent-books-grid">
                <?php if (empty($recent_books)): ?>
                    <div class="no-books-message">Tidak ada buku terbaru untuk ditampilkan.</div>
                <?php else: ?>
                    <?php foreach ($recent_books as $index => $buku): ?>
                        <?php
                        $gambar = !empty($buku['gambar']) && file_exists("uploads/" . $buku['gambar'])
                            ? "uploads/" . $buku['gambar']
                            : 'https://placehold.co/400x500/CBD5E0/6B7280?text=No+Image';
                        ?>
                        <div class="book-card-display" style="animation-delay: <?= $index * 0.1; ?>s;">
                            <div class="book-image-container-display">
                                <img src="<?= $gambar ?>"
                                    alt="<?= htmlspecialchars($buku['judul']); ?>"
                                    class="book-image-display"
                                    onerror="this.onerror=null; this.src='https://placehold.co/400x500/CBD5E0/6B7280?text=No+Image'; this.classList.add('book-image-fallback-display');">
                            </div>
                            <div>
                                <h3 class="book-title-display"><?= htmlspecialchars($buku['judul']); ?></h3>
                                <p class="book-author-display">Oleh: <?= htmlspecialchars($buku['penulis']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <script>
        document.querySelectorAll('.sidebar-item, .logout-button, .stats-card, .activity-item a, .book-card-display').forEach(element => {
            element.addEventListener('click', function(e) {

                const existingRipple = this.querySelector('.ripple');
                if (existingRipple) {
                    existingRipple.remove();
                }

                const rect = this.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;

                const ripple = document.createElement('span');
                ripple.classList.add('ripple');
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';


                const size = Math.max(rect.width, rect.height);
                ripple.style.width = ripple.style.height = size + 'px';

                this.appendChild(ripple);

                ripple.addEventListener('animationend', () => {
                    ripple.remove();
                });
            });
        });

        document.addEventListener('DOMContentLoaded', () => {
            const bookCards = Array.from(document.querySelectorAll('.book-card-display'));
            bookCards.forEach((card, index) => {
                setTimeout(() => {
                    card.classList.add('animate-in');
                }, 100 * index);
            });
        });
    </script>
</body>

</html>

<?php

$koneksi->close();
?>