<?php
session_start();




if (!isset($_SESSION['anggota_loggedin']) || $_SESSION['anggota_loggedin'] !== true) {
    header("Location: login_anggota.php");
    exit();
}


include 'koneksi.php';


$id_anggota_session = $_SESSION['id_anggota'] ?? null;
$nama_anggota_session = $_SESSION['nama_anggota'] ?? 'Anggota';


$buku_dipinjam = [];
if ($id_anggota_session) {
    $sql_peminjaman = "SELECT p.id_peminjaman, b.judul AS judul_buku, b.penulis, b.penerbit,
                               p.tanggal_pinjam, p.tanggal_kembali AS tanggal_kembali_rencana,
                               pet.nama_petugas
                         FROM peminjaman p
                         JOIN buku b ON p.id_buku = b.id_buku
                         LEFT JOIN petugas pet ON p.id_petugas = pet.id_petugas
                         WHERE p.id_anggota = ? AND p.status = 'Dipinjam'
                         ORDER BY p.tanggal_pinjam DESC";
    $stmt_peminjaman = $koneksi->prepare($sql_peminjaman);
    if ($stmt_peminjaman) {
        $stmt_peminjaman->bind_param("i", $id_anggota_session);
        $stmt_peminjaman->execute();
        $result_peminjaman = $stmt_peminjaman->get_result();

        if ($result_peminjaman->num_rows > 0) {
            while ($row = $result_peminjaman->fetch_assoc()) {
                $buku_dipinjam[] = $row;
            }
        }
        $stmt_peminjaman->close();
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
    <title>Dashboard Anggota | LibSys</title>
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
            margin: 0;
            box-sizing: border-box;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
            padding-left: 1rem;
            padding-right: 1rem;
        }


        .header-section {
            background: white;
            color: black;
            padding: 3rem 2rem;
            box-shadow: 0 15px 30px -8px rgba(0, 0, 0, 0.25);
            text-align: center;
            border-radius: 2.5rem;
            margin-bottom: 2rem;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
            animation: fadeInDown 1s ease-out forwards;
            opacity: 0;
            transform: translateY(-20px);
        }

        @keyframes fadeInDown {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header-title {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 1rem;
            text-shadow: 1px 1px 3px rgba(255, 255, 255, 0.5);
            color: black;
        }

        .header-subtitle {
            font-size: 1.5rem;
            font-weight: 500;
            margin-bottom: 2rem;
            color: black;
        }

        .header-buttons-container {
            margin-top: 1.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 1rem;
        }

        @media (min-width: 640px) {
            .header-buttons-container {
                flex-direction: row;
            }
        }


        .header-action-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 1rem 2rem;
            border-radius: 0.75rem;
            font-size: 1.125rem;
            font-weight: 700;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
            border: none;
            cursor: pointer;
            color: white;
            text-decoration: none;
        }

        .header-action-button:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }

        .header-action-button i {
            margin-right: 0.5rem;
        }

        .btn-pinjam-baru {
            background-color: #4e7ab1;
        }

        .btn-pinjam-baru:hover {
            background-color: #4e7ab1;
        }

        .btn-logout {
            background-color: #4e7ab1;
        }

        .btn-logout:hover {
            background-color: #4e7ab1;
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

        .card-data {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 2.5rem;
            box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.35), 0 20px 40px -10px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(30px) saturate(1.8);
            border: 2px solid rgba(255, 255, 255, 0.7);
            padding: 2.5rem;
            margin: 2rem auto;
            width: 100%;
            max-width: 1200px;
            animation: fadeInScale 1.2s cubic-bezier(0.23, 1, 0.32, 1) forwards;
            transform: scale(0.95) translateY(30px);
            opacity: 0;
            position: relative;
        }

        @keyframes fadeInScale {
            0% {
                opacity: 0;
                transform: scale(0.95) translateY(30px);
            }

            100% {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1F2937;
            margin-bottom: 1.5rem;
        }

        @media (min-width: 768px) {
            .card-title {
                font-size: 2rem;
            }
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
            min-width: 700px;
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


        .action-btn {
            background-color: #4e7ab1;
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
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            background-color: #4e7ab1;
        }


        .message-box {
            padding: 1.2rem;
            border-radius: 0.75rem;
            margin-bottom: 2rem;
            font-weight: 600;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-left: auto;
            margin-right: auto;
            max-width: 800px;
            animation: fadeIn 0.5s ease-out;
        }

        .message-box.error {
            background-color: #9fb7e3;
            border: 1px solid #d8d0e7;
            color: black;
        }

        .message-box.success {
            background-color: #9295d0;
            border: 1px solid #d8d0e7;
            color: black;
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
            top: 1rem;
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
            color: #4DB6AC;
            font-size: 4rem;
            margin-bottom: 1.5rem;
        }

        .modal-title {
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
            background-color: #50698d;
            color: white;
        }

        .modal-button.confirm:hover {
            background-color: #50698d;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .header-title {
                font-size: 2.5rem;
            }

            .header-subtitle {
                font-size: 1.2rem;
            }

            .header-section {
                padding: 2.5rem 1.5rem;
                margin-bottom: 1.5rem;
            }

            .header-action-button {
                padding: 0.8rem 1.5rem;
                font-size: 1rem;
            }

            .card-data {
                padding: 2rem;
            }

            th,
            td {
                padding: 0.8rem 1rem;
                font-size: 0.85rem;
            }

            .action-btn {
                padding: 0.5rem 0.8rem;
                font-size: 0.8rem;
            }

            .modal-title {
                font-size: 1.75rem;
            }

            .modal-text {
                font-size: 0.9rem;
            }

            .modal-content {
                padding: 2rem;
            }

            .modal-icon {
                font-size: 3rem;
            }
        }

        @media (max-width: 640px) {
            body {
                padding: 0.5rem;
            }

            .header-buttons-container {
                flex-direction: column;
            }

            .header-action-button {
                width: 100%;
                margin-bottom: 0.5rem;
            }

            .header-action-button:last-child {
                margin-bottom: 0;
            }

            .card-data {
                padding: 1.5rem;
                border-radius: 1.5rem;
                margin: 1.5rem auto;
            }

            .table-container {
                border-radius: 0.75rem;
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

            .modal-title {
                font-size: 1.8rem;
            }

            .modal-text {
                font-size: 0.85rem;
            }
        }
    </style>
</head>

<body>
    <header class="header-section">
        <h1 class="header-title">Halo, <?php echo htmlspecialchars($nama_anggota_session); ?>!</h1>
        <p class="header-subtitle">Mari Lakukan Hal Yang Menyenangkan!</p>
        <div class="header-buttons-container">
            <a href="pinjam_buku_anggota.php" class="header-action-button btn-pinjam-baru">
                <i class="fas fa-book-medical"></i> Pinjam Buku Baru
            </a>
            <a href="logout_anggota.php" class="header-action-button btn-logout">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </header>

    <main class="container">
        <?php if ($message): ?>
            <div class="message-box <?php echo $message_type; ?>">
                <?php if ($message_type == 'success'): ?><i class="fas fa-check-circle text-2xl"></i><?php else: ?><i class="fas fa-exclamation-circle text-2xl"></i><?php endif; ?>
                <span><?php echo $message; ?></span>
            </div>
        <?php endif; ?>

        <section class="card-data">
            <h2 class="card-title">Buku Yang Sedang Dipinjam</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Judul Buku</th>
                            <th>Penulis</th>
                            <th>Penerbit</th>
                            <th>Tanggal Pinjam</th>
                            <th>Dikembalikan (Rencana)</th>
                            <th>Petugas Peminjaman</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($buku_dipinjam)): ?>
                            <tr>
                                <td colspan="8" class="text-center-col" style="padding: 2rem; color: #6b7280; font-weight: normal;">Anda tidak sedang meminjam buku.</td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1;
                            foreach ($buku_dipinjam as $pinjam): ?>
                                <tr class="table-row-hover">
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($pinjam['judul_buku']); ?></td>
                                    <td><?php echo htmlspecialchars($pinjam['penulis']); ?></td>
                                    <td><?php echo htmlspecialchars($pinjam['penerbit']); ?></td>
                                    <td><?php echo htmlspecialchars($pinjam['tanggal_pinjam']); ?></td>
                                    <td><?php echo htmlspecialchars($pinjam['tanggal_kembali_rencana'] ? $pinjam['tanggal_kembali_rencana'] : '-'); ?></td>
                                    <td><?php echo htmlspecialchars($pinjam['nama_petugas'] ? $pinjam['nama_petugas'] : '-'); ?></td>
                                    <td class="whitespace-nowrap">
                                        <button class="action-btn"
                                            data-id="<?php echo $pinjam['id_peminjaman']; ?>"
                                            data-judul="<?php echo htmlspecialchars($pinjam['judul_buku']); ?>"
                                            data-anggota="<?php echo htmlspecialchars($nama_anggota_session); ?>"
                                            onclick="openReturnModal(this)">
                                            <i class="fas fa-check-circle"></i> Kembalikan
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

    <div id="returnConfirmModal" class="modal">
        <div class="modal-content">
            <button class="close-button" onclick="closeReturnModal()">&times;</button>
            <i class="fas fa-undo-alt modal-icon"></i>
            <h2 class="modal-title">Konfirmasi Pengembalian</h2>
            <p class="modal-text">Apakah Anda yakin ingin mengembalikan buku "<span id="returnBookTitle"></span>"?</p>
            <div class="modal-buttons-container">
                <button type="button" onclick="closeReturnModal()" class="modal-button cancel">Batal</button>
                <a id="confirmReturnBtn" href="#" class="modal-button confirm">Kembalikan</a>
            </div>
        </div>
    </div>

    <script>
        const returnConfirmModal = document.getElementById('returnConfirmModal');
        const returnBookTitle = document.getElementById('returnBookTitle');
        const confirmReturnBtn = document.getElementById('confirmReturnBtn');

        function openReturnModal(button) {
            const id = button.dataset.id;
            const judul = button.dataset.judul;
            returnBookTitle.textContent = judul;
            confirmReturnBtn.href = 'proses_peminjaman.php?action=kembalikan_anggota&id=' + id;
            returnConfirmModal.style.display = 'flex';
        }

        function closeReturnModal() {
            returnConfirmModal.style.display = 'none';
        }


        window.onclick = function(event) {
            if (event.target == returnConfirmModal) {
                closeReturnModal();
            }
        }


        document.querySelectorAll('.header-action-button, .action-btn, .modal-button').forEach(button => {
            button.addEventListener('click', function(e) {

                if (this.tagName.toLowerCase() === 'a' && e.target.tagName.toLowerCase() !== 'button') {

                    e.preventDefault();
                }

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


                if (this.tagName.toLowerCase() === 'a' && e.target.tagName.toLowerCase() !== 'button') {
                    setTimeout(() => {
                        window.location.href = target.href;
                    }, 300);
                }
            });
        });


        document.addEventListener('DOMContentLoaded', () => {
            const cardData = document.querySelector('.card-data');
            if (cardData) {
                cardData.style.opacity = '0';
                cardData.style.transform = 'translateY(30px) scale(0.95)';
                setTimeout(() => {
                    cardData.style.animation = 'fadeInScale 1.2s cubic-bezier(0.23, 1, 0.32, 1) forwards';
                }, 100);
            }
        });
    </script>
</body>

</html>

<?php

$koneksi->close();
?>