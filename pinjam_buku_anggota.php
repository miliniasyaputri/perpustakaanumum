<?php
session_start();

// Periksa apakah anggota sudah login, jika tidak, arahkan kembali ke halaman login anggota
if (!isset($_SESSION['anggota_loggedin']) || $_SESSION['anggota_loggedin'] !== true) {
    header("Location: login_anggota.php");
    exit();
}

// Sertakan file koneksi database
include 'koneksi.php';

// Ambil informasi anggota dari sesi
$id_anggota_session = $_SESSION['id_anggota'];
$nama_anggota_session = $_SESSION['nama_anggota'];

// Ambil data buku yang memiliki stok lebih dari 0
$sql_buku = "SELECT id_buku, isbn, judul, penulis, penerbit, tahun_terbit, kategori, stok, gambar
             FROM buku WHERE stok > 0 ORDER BY judul ASC";
$result_buku = $koneksi->query($sql_buku);

$buku_tersedia = [];
if ($result_buku->num_rows > 0) {
    while ($row = $result_buku->fetch_assoc()) {
        $buku_tersedia[] = $row;
    }
}

// Pesan sukses atau error dari operasi peminjaman
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
    <title>Pinjam Buku - Perpustakaan</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts - Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Font Awesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            /* Match the index.html background gradient */
            background: #9295d0;
            min-height: 100vh;
            padding: 1.5rem;
            overflow-x: hidden; /* Prevent horizontal scrolling */
            overflow-y: auto; /* Allow vertical scrolling */
        }
        .header-section {
            /* Match a prominent color from the index.html gradient or buttons (e.g., btn-katalog or btn-logout) */
            background: white; /* Using a deeper pink/coral from index.html btn-katalog */
            color: white;
            padding: 3rem 2rem;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            text-align: center;
            border-radius: 1.5rem; /* Rounded header */
            margin-bottom: 2rem; /* Space below header */
            animation: fadeInDown 0.8s ease-out forwards; /* Animation for header */
            transform: translateY(-20px);
            opacity: 0;
        }

        @keyframes fadeInDown {
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .header-title {
            font-size: 3.5rem; /* Larger title */
            font-weight: 800; /* Extra bold */
            line-height: 1.1;
            margin-bottom: 1rem;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.2);
            color: black;
        }
        .header-subtitle {
            font-size: 1.5rem;
            font-weight: 500;
            margin-bottom: 2rem;
            color: black;
        }
        .header-action-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 1rem 2rem;
            border-radius: 0.75rem; /* Large rounded corners */
            font-size: 1.125rem; /* text-lg */
            font-weight: 700; /* font-bold */
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
            border: none;
            cursor: pointer;
            color: white; /* Default text color for these buttons */
        }
        .header-action-button:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }
        /* Ripple effect */
        .header-action-button .ripple, .btn-pinjam .ripple, .modal-button .ripple {
            position: absolute;
            border-radius: 50%;
            transform: scale(0);
            animation: ripple 0.6s linear;
            background-color: rgba(255, 255, 255, 0.7); /* White ripple */
        }
        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }

        .btn-dashboard-back {
            /* Match btn-register from index.html: Teal */
            background-color: #294a8f;
            --hover-color: #294a8f; /* Darker Teal */
        }
        .btn-logout {
            /* Match btn-katalog from index.html: Coral */
            background-color: #294a8f;
            --hover-color: #294a8f; /* Darker Coral */
        }

        /* Main Content Wrapper - Glassmorphism style */
        .main-content-wrapper {
            background-color: rgba(255, 255, 255, 0.95); /* Even more opaque for crispness */
            border-radius: 2.5rem; /* Super rounded corners */
            box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.35), 0 20px 40px -10px rgba(0, 0, 0, 0.2); /* Deeper, more spread shadow */
            backdrop-filter: blur(30px) saturate(1.8); /* Stronger blur and saturation for "glass" look */
            border: 2px solid rgba(255, 255, 255, 0.7); /* Thicker, more prominent border */
            padding: 2.5rem; /* Consistent padding */
            margin: 0 auto 2rem; /* Centering and spacing */
            width: 100%;
            max-width: 1200px; /* Wider for content */
            animation: fadeInScale 1.2s cubic-bezier(0.23, 1, 0.32, 1) forwards;
            transform: scale(0.95) translateY(30px);
            opacity: 0;
            position: relative;
        }
        @keyframes fadeInScale {
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }


        /* Book Card Styles (from user_interface.php) */
        .card-book {
            background-color: white; /* Inner card solid white */
            border-radius: 1.25rem; /* More rounded */
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1); /* Deeper initial shadow */
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.3s ease-out, box-shadow 0.3s ease-out;
            position: relative;
            overflow: hidden;
            border: 1px solid #E5E7EB; /* Light border */
            opacity: 0; /* Initial state for animation */
            transform: translateY(20px);
        }
        .card-book.animate-in {
            animation: cardFadeIn 0.6s ease-out forwards;
        }

        @keyframes cardFadeIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card-book:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.25);
        }
        .book-image-container {
            width: 100%;
            height: 250px;
            overflow: hidden;
            border-radius: 0.75rem;
            margin-bottom: 1rem;
            position: relative;
        }
        .book-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease-out;
        }
        .card-book:hover .book-image {
            transform: scale(1.08);
        }
        .book-image-fallback {
            background-color: #CBD5E0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: #6B7280;
        }

        .book-title {
            font-size: 1.6rem;
            font-weight: 800;
            color: #1F2937;
            margin-bottom: 0.5rem;
            line-height: 1.2;
        }
        .book-meta {
            font-size: 0.95rem;
            color: #4B5563;
            margin-bottom: 0.3rem;
            line-height: 1.4;
        }
        .book-stok {
            margin-top: 1rem;
            font-weight: 700;
            padding: 0.4rem 1rem;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            font-size: 0.9rem;
            gap: 0.5rem;
        }
        .stok-available {
            background-color: #D1FAE5;
            color: black;
        }
        .stok-empty {
            background-color: #FEE2E2;
            color: #991B1B;
        }
        .btn-pinjam {
            /* Match btn-login from index.html: Blue */
            background-color: #6A82FB; /* Blue */
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 0.75rem;
            text-decoration: none;
            font-weight: 700;
            transition: background-color 0.3s ease, transform 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: relative; /* For ripple effect */
            overflow: hidden;
        }
        .btn-pinjam:hover {
            background-color: #5A6EBE; /* Darker Blue */
            transform: translateY(-2px);
        }

        /* Message Boxes */
        .message-box {
            padding: 1.2rem; /* More padding */
            border-radius: 0.75rem; /* Rounded corners for messages */
            margin-bottom: 2rem; /* More space below */
            font-weight: 600;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            display: flex; /* For icon alignment */
            align-items: center;
            gap: 0.75rem;
            margin-left: auto; /* Center with auto margins */
            margin-right: auto;
            max-width: 800px; /* Max width for message box */
        }
        .message-box.error {
            background-color: #9fb7e3; /* Red-100 */
            border: 1px solid #9fb7e3; /* Red-300 */
            color: #991B1B; /* Red-800 */
        }
        .message-box.success {
            background-color: #9295d0; /* Green-100 */
            border: 1px solid #9295d0; /* Green-300 */
            color: #0a111a; /* Green-800 */
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.6); /* Darker overlay */
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            background-color: rgba(255, 255, 255, 0.98); /* Near opaque white */
            border-radius: 1.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(25px) saturate(1.5);
            border: 1px solid rgba(255, 255, 255, 0.8);
            padding: 2.5rem;
            width: 90%;
            max-width: 500px; /* Wider for forms */
            position: relative;
            text-align: center;
        }
        .close-button {
            color: #9CA3AF; /* Gray-400 */
            position: absolute;
            top: 1rem;
            right: 1.5rem;
            font-size: 2rem; /* Larger close button */
            font-weight: bold;
            cursor: pointer;
            transition: color 0.2s ease;
        }
        .close-button:hover,
        .close-button:focus {
            color: #4B5563; /* Darker gray on hover */
        }
        .modal-icon {
            color: black; /* Coral color for modal icon (matching header) */
            font-size: 4rem; /* Large icon */
            margin-bottom: 1.5rem;
        }
        .modal-title-text { /* Renamed to avoid conflict */
            font-size: 2.25rem; /* Larger title */
            font-weight: 700;
            color: #1F2937;
            margin-bottom: 1rem;
        }
        .modal-text {
            color: #4B5563;
            font-size: 1rem;
            margin-bottom: 2rem;
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
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            font-size: 1rem;
            outline: none;
            transition: all 0.3s ease;
            color: #1F2937;
        }
        .modal-form-input:focus {
            box-shadow: 0 4px 15px rgba(0,0,0,0.15), 0 0 0 4px rgba(255, 111, 145, 0.3); /* Focus ring matching main theme */
        }
        .modal-button {
            padding: 0.8rem 1.8rem;
            border-radius: 0.75rem;
            font-weight: 700;
            transition: all 0.3s ease;
            position: relative; /* For ripple */
            overflow: hidden;
        }
        .modal-button.cancel {
            background-color: #E5E7EB; /* Gray-200 */
            color: #4B5563;
        }
        .modal-button.cancel:hover {
            background-color: #D1D5DB; /* Gray-300 */
            transform: translateY(-2px);
        }
        .modal-button.confirm {
            /* Match btn-member-login from index.html: Yellow */
            background-color: #294a8f;
            color: white;
        }
        .modal-button.confirm:hover {
            background-color: #294a8f; /* Darker Yellow */
            transform: translateY(-2px);
        }

        /* Responsive Adjustments */
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
            .card-book {
                padding: 2rem;
            }
            .book-image-container {
                height: 200px;
            }
            .book-title {
                font-size: 1.4rem;
            }
            .book-meta {
                font-size: 0.9rem;
            }
            .book-stok {
                font-size: 0.8rem;
            }
            .btn-pinjam {
                padding: 0.7rem 1.2rem;
                font-size: 1rem;
            }
            .message-box {
                padding: 1rem;
                font-size: 0.9rem;
                gap: 0.5rem;
            }
            .modal-content {
                padding: 2rem;
            }
            .modal-title-text {
                font-size: 1.75rem;
            }
            .modal-text {
                font-size: 0.9rem;
            }
            .modal-form-input {
                padding: 0.8rem 1rem;
                font-size: 0.9rem;
            }
            .modal-button {
                padding: 0.6rem 1.2rem;
                font-size: 0.9rem;
            }
        }
        @media (max-width: 640px) {
            body {
                padding: 0.5rem;
            }
            .header-action-button {
                flex-direction: column;
                width: 100%;
                margin-bottom: 0.5rem;
            }
            .header-action-button:last-child {
                margin-bottom: 0;
            }
            .card-book {
                padding: 1.5rem;
                border-radius: 1.5rem;
            }
            .book-image-container {
                height: 180px;
            }
        }
    </style>
</head>
<body class="p-8">
    <header class="header-section">
        <h1 class="header-title">Pinjam Buku Baru</h1>
        <p class="header-subtitle">Katalog buku yang tersedia untuk dipinjam</p>
        <div class="mt-6 flex flex-col sm:flex-row justify-center gap-4">
            <a href="dashboard_anggota.php" class="header-action-button btn-dashboard-back">
                <i class="fas fa-tachometer-alt mr-2"></i> Kembali ke Dashboard
            </a>
            <a href="logout_anggota.php" class="header-action-button btn-logout">
                <i class="fas fa-sign-out-alt mr-2"></i> Logout
            </a>
        </div>
    </header>

    <main class="container mx-auto px-4">
        <?php if ($message): ?>
            <div class="message-box <?php echo $message_type; ?>">
                <?php if ($message_type == 'success'): ?><i class="fas fa-check-circle text-2xl"></i><?php else: ?><i class="fas fa-exclamation-circle text-2xl"></i><?php endif; ?>
                <span><?php echo $message; ?></span>
            </div>
        <?php endif; ?>

        <!-- Main content area with glassmorphism effect -->
        <section class="main-content-wrapper grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
            <?php if (empty($buku_tersedia)): ?>
                <div class="col-span-full text-center py-12 text-gray-500 text-lg">
                    Tidak ada buku yang tersedia untuk dipinjam saat ini.
                </div>
            <?php else: ?>
    <?php foreach ($buku_tersedia as $index => $buku): ?>
        <?php
        $gambar = !empty($buku['gambar']) && file_exists("uploads/" . $buku['gambar'])
            ? "uploads/" . $buku['gambar']
            : "https://placehold.co/400x500/CBD5E0/6B7280?text=No+Image";
        ?>
        <div class="card-book animate-in" style="animation-delay: <?= $index * 0.1; ?>s;">
            <div class="book-image-container">
                <img src="<?= htmlspecialchars($gambar); ?>"
                     alt="<?= htmlspecialchars($buku['judul']); ?>"
                     class="book-image"
                     onerror="this.onerror=null; this.src='https://placehold.co/400x500/CBD5E0/6B7280?text=No+Image'; this.classList.add('book-image-fallback');">
            </div>
            <div>
                <h2 class="book-title"><?= htmlspecialchars($buku['judul']); ?></h2>
                <p class="book-meta">Oleh: <span class="font-semibold"><?= htmlspecialchars($buku['penulis']); ?></span></p>
                <p class="book-meta">Penerbit: <?= htmlspecialchars($buku['penerbit']); ?>, Tahun: <?= htmlspecialchars($buku['tahun_terbit']); ?></p>
                <p class="book-meta">Kategori: <span class="font-semibold"><?= htmlspecialchars($buku['kategori']); ?></span></p>
            </div>
            <div class="mt-4 flex flex-col items-center">
                <span class="book-stok stok-available mb-4">
                    <i class="fas fa-cubes mr-1"></i> Stok: <?= htmlspecialchars($buku['stok']); ?>
                </span>
                <button class="btn-pinjam w-full"
                        data-id="<?= $buku['id_buku']; ?>"
                        data-judul="<?= htmlspecialchars($buku['judul']); ?>"
                        onclick="openBorrowModal(this)">
                    <i class="fas fa-hand-holding-usd mr-2"></i> Pinjam Buku Ini
                </button>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

            
        </section>
    </main>

    <!-- Modal Konfirmasi Peminjaman -->
    <div id="borrowModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closeModal()">&times;</span>
            <img src="uploads/logo1.png"
     alt="Book Icon"
     class="modal-icon mx-auto block"
     style="width: 100px; height: 100px; object-fit: contain;">
            <h2 id="modalTitle" class="modal-title-text">Konfirmasi Peminjaman</h2>
            <p class="modal-text">Anda akan meminjam buku "<span id="modalJudulBukuConfirm" class="font-semibold"></span>".</p>
            <form id="borrowForm" action="proses_peminjaman.php" method="POST" class="space-y-4 text-left">
                <input type="hidden" name="action" value="tambah">
                <input type="hidden" name="id_buku" id="modalIdBuku">
                <input type="hidden" name="id_anggota" value="<?php echo $id_anggota_session; ?>">
                <!-- id_petugas akan diatur menjadi NULL di proses_peminjaman.php jika peminjam adalah anggota -->

                <div>
                    <label for="tanggal_pinjam" class="modal-form-label">Tanggal Pinjam</label>
                    <input type="date" id="tanggal_pinjam" name="tanggal_pinjam" class="modal-form-input" required>
                </div>
                <div>
                    <label for="tanggal_kembali_rencana" class="modal-form-label">Tanggal Kembali (Rencana)</label>
                    <input type="date" id="tanggal_kembali_rencana" name="tanggal_kembali" class="modal-form-input">
                </div>

                <div class="flex justify-center space-x-4 mt-6">
                    <button type="button" onclick="closeModal()" class="modal-button cancel">Batal</button>
                    <button type="submit" class="modal-button confirm">Konfirmasi Pinjam</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const borrowModal = document.getElementById('borrowModal');
        const modalJudulBukuConfirm = document.getElementById('modalJudulBukuConfirm'); // Updated ID
        const modalIdBuku = document.getElementById('modalIdBuku');
        const tanggalPinjamInput = document.getElementById('tanggal_pinjam');

        // Set tanggal pinjam default ke hari ini
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date();
            const year = today.getFullYear();
            let month = today.getMonth() + 1; // Months start at 0!
            let day = today.getDate();

            if (day < 10) day = '0' + day;
            if (month < 10) month = '0' + month;

            const formattedToday = year + '-' + month + '-' + day;
            tanggalPinjamInput.value = formattedToday;
        });

        function openBorrowModal(button) {
            const idBuku = button.dataset.id;
            const judulBuku = button.dataset.judul;

            modalJudulBukuConfirm.textContent = judulBuku; // Updated ID
            modalIdBuku.value = idBuku;

            // Pastikan tanggal pinjam selalu hari ini saat modal dibuka
            const today = new Date();
            const year = today.getFullYear();
            let month = today.getMonth() + 1;
            let day = today.getDate();
            if (day < 10) day = '0' + day;
            if (month < 10) month = '0' + month;
            const formattedToday = year + '-' + month + '-' + day;
            tanggalPinjamInput.value = formattedToday;

            borrowModal.style.display = 'flex';
        }

        function closeModal() {
            borrowModal.style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == borrowModal) {
                closeModal();
            }
        }

        // Ripple effect for all interactive buttons
        document.querySelectorAll('.header-action-button, .btn-pinjam, .modal-button').forEach(button => {
            button.addEventListener('click', function(e) {
                const x = e.clientX - e.target.getBoundingClientRect().left;
                const y = e.clientY - e.target.getBoundingClientRect().top;

                const ripple = document.createElement('span');
                ripple.classList.add('ripple');
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                this.appendChild(ripple);

                // Remove ripple after animation
                ripple.addEventListener('animationend', () => {
                    ripple.remove();
                });
            });
        });

        // Initial fade-in for card books
        document.addEventListener('DOMContentLoaded', () => {
            const bookCards = Array.from(document.querySelectorAll('.card-book'));
            bookCards.forEach((card, index) => {
                setTimeout(() => {
                    card.classList.add('animate-in');
                }, 100 * index); // Stagger animation for each card
            });
        });
    </script>
</body>
</html>

<?php
// Tutup koneksi database setelah semua operasi selesai
$koneksi->close();
?>
