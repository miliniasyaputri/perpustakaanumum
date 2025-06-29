<?php
include 'koneksi.php';

// Ambil data buku dari database
// Pastikan kolom 'gambar' ada di tabel 'buku'
$sql = "SELECT id_buku, isbn, judul, penulis, penerbit, tahun_terbit, kategori, stok, gambar FROM buku ORDER BY judul ASC";
$result = $koneksi->query($sql);

$buku_data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $buku_data[] = $row;
    }
}

$koneksi->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Buku Perpustakaan | LibSys</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* --- General Body and Layout --- */
        body {
            font-family: 'Inter', sans-serif;
            background: #9295d0;
            min-height: 100vh;
            margin: 0;
            padding: 2rem; /* Equivalent to p-8 */
            box-sizing: border-box;
        }

        /* --- Header Section --- */
        .header-section {
            background: white;
            color: black;
            padding: 3rem 2rem;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            text-align: center;
            border-radius: 2rem; /* Equivalent to rounded-[2rem] */
            margin-bottom: 2rem; /* Added margin to separate from content */
        }
        .header-title {
            font-size: 3.5rem;
            font-weight: 800;
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
        .search-container {
            max-width: 600px;
            margin: 0 auto 2rem;
            position: relative;
        }
        .search-input {
            width: 100%;
            padding: 1rem 1.5rem 1rem 3.5rem;
            border-radius: 9999px; /* Equivalent to rounded-full */
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            font-size: 1.1rem;
            outline: none;
            transition: all 0.3s ease;
            color: black;
            background-color: #50698d;
        }
        .search-input::placeholder {
            color: black;
            opacity: 0.7;
        }
        .search-input:focus {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2), 0 0 0 4px rgba(255, 111, 145, 0.3);
        }
        .search-icon {
            position: absolute;
            left: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            color: black;
        }
        .btn-home {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: white;
            color: #4e7ab1; /* Adjusted color */
            padding: 0.75rem 1.5rem;
            border-radius: 9999px; /* Equivalent to rounded-full */
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); /* Equivalent to shadow-md */
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            margin-top: 1.5rem; /* Equivalent to mt-6 */
        }
        .btn-home:hover {
            background-color: #f3f4f6; /* Equivalent to hover:bg-gray-100 */
        }
        .btn-home i {
            margin-right: 0.5rem; /* Equivalent to mr-2 */
        }

        /* --- Main Content and Book Cards --- */
        .container {
            width: 100%;
            max-width: 1280px; /* Equivalent to max-w-7xl */
            margin-left: auto;
            margin-right: auto;
            padding-left: 1rem;
            padding-right: 1rem; /* Equivalent to px-4 */
            padding-top: 2rem;
            padding-bottom: 2rem; /* Equivalent to py-8 */
        }
        .book-list-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 2rem; /* Equivalent to gap-8 */
        }
        @media (min-width: 640px) { /* sm */
            .book-list-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (min-width: 768px) { /* md */
            .book-list-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }
        @media (min-width: 1024px) { /* lg */
            .book-list-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }

        .card-book {
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
            border: 1px solid #1F2937;
            opacity: 0;
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
            aspect-ratio: 3 / 4;
            width: 100%;
            overflow: hidden;
            border-radius: 0.75rem;
            margin-bottom: 1rem;
            background-color: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .book-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 0.5rem;
            transition: transform 0.3s ease;
        }
        .card-book:hover .book-image {
            transform: scale(1.04);
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
        .book-meta span {
            font-weight: 600;
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
            color: #065F46;
        }
        .stok-empty {
            background-color: #FEE2E2;
            color: #991B1B;
        }
        .detail-button {
            background-color: #102b53;
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
            width: 100%;
            box-sizing: border-box;
        }
        .detail-button i {
            margin-right: 0.5rem; /* Equivalent to mr-2 */
        }
        .detail-button:hover {
            background-color: #50698d;
            transform: translateY(-2px);
        }
        .no-results-message {
            grid-column: span 4 / span 4; /* span-full in lg */
            text-align: center;
            padding-top: 3rem;
            padding-bottom: 3rem;
            color: #6b7280;
            font-size: 1.125rem;
            display: none; /* Hide by default */
        }
        @media (min-width: 768px) {
             .no-results-message {
                grid-column: span 3 / span 3;
            }
        }
        @media (min-width: 640px) {
             .no-results-message {
                grid-column: span 2 / span 2;
            }
        }
        .no-results-message.visible {
            display: block;
        }
        .hidden {
            display: none !important;
        }

        /* --- Footer --- */
        footer {
            background-color: white;
            padding: 2rem;
            margin-top: 3rem;
            border-radius: 1.25rem;
            box-shadow: 0 -4px 15px rgba(0, 0, 0, 0.05);
            font-size: 0.9rem;
            color: #6B7280;
            text-align: center;
        }

        /* --- Modal Styles --- */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 50;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            background-color: white;
            border-radius: 0.5rem; /* Equivalent to rounded-lg */
            padding: 1.5rem; /* Equivalent to p-6 */
            position: relative;
            max-width: 28rem; /* Equivalent to max-w-md */
            width: 100%;
            text-align: center;
        }
        .modal-close-button {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            color: #6b7280;
            font-size: 1.125rem;
            line-height: 1.75rem; /* Equivalent to text-lg */
            cursor: pointer;
            border: none;
            background: none;
            padding: 0.5rem;
            transition: color 0.2s ease;
        }
        .modal-close-button:hover {
            color: #ef4444; /* Equivalent to hover:text-red-500 */
        }
        .modal-image {
            width: 10rem; /* Equivalent to w-40 */
            height: 14rem; /* Equivalent to h-56 */
            object-fit: cover;
            margin-left: auto;
            margin-right: auto;
            margin-bottom: 1rem;
            border-radius: 0.25rem; /* Equivalent to rounded */
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); /* Equivalent to shadow */
        }
        .modal-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .modal-content p {
            margin-bottom: 0.5rem;
            color: #4b5563;
        }
        .modal-content strong {
            font-weight: 700;
        }

        /* --- Responsive Adjustments (from original) --- */
        @media (max-width: 640px) {
            .header-title {
                font-size: 2.5rem;
            }
            .header-subtitle {
                font-size: 1.2rem;
            }
            .header-section {
                padding: 2rem 1rem;
            }
            .search-input {
                padding: 0.8rem 1rem 0.8rem 3rem;
                font-size: 1rem;
            }
            .search-icon {
                left: 0.8rem;
            }
            .card-book {
                padding: 1rem;
                border-radius: 1rem;
            }
            .book-image-container {
                height: 200px;
            }
            .book-title {
                font-size: 1.3rem;
            }
            .book-meta {
                font-size: 0.85rem;
            }
            .book-stok {
                font-size: 0.8rem;
                padding: 0.3rem 0.8rem;
            }
            .detail-button {
                padding: 0.6rem 1.2rem;
                font-size: 0.9rem;
            }
        }

    </style>
</head>

<body>
    <header class="header-section">
        <h1 class="header-title">Jelajahi Koleksi The Pixel Library</h1>
        <p class="header-subtitle">Temukan Jatidiri mu disini!</p>
        <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="searchInput" placeholder="Cari judul, penulis, atau kategori..." class="search-input">
        </div>
        <div class="header-buttons">
            <a href="index.php" class="btn-home">
                <i class="fas fa-home"></i> Kembali ke Beranda
            </a>
        </div>
    </header>

    <main class="container">
        <section id="book-list" class="book-list-grid">
            <?php if (empty($buku_data)): ?>
                <div class="no-results-message visible">
                    Tidak ada buku yang tersedia di katalog saat ini.
                </div>
            <?php else: ?>
                <?php foreach ($buku_data as $index => $buku): ?>
                    <?php
                    $gambar = !empty($buku['gambar']) && file_exists("uploads/" . $buku['gambar'])
                        ? "uploads/" . $buku['gambar']
                        : "https://placehold.co/400x500/CBD5E0/6B7280?text=No+Image";
                    ?>
                    <div class="card-book" data-index="<?= $index; ?>">
                        <div class="book-image-container">
                            <img src="<?= $gambar ?>"
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
                        <div style="display: flex; flex-direction: column; align-items: center;">
                            <?php if ($buku['stok'] > 0): ?>
                                <span class="book-stok stok-available">
                                    <i class="fas fa-check-circle"></i> Tersedia (<?= htmlspecialchars($buku['stok']); ?>)
                                </span>
                            <?php else: ?>
                                <span class="book-stok stok-empty">
                                    <i class="fas fa-times-circle"></i> Tidak Tersedia
                                </span>
                            <?php endif; ?>
                            <a href="#" class="detail-button" onclick="openModal('modalDetail<?= $index ?>')">
                                <i class="fas fa-info-circle"></i> Lihat Detail
                            </a>
                        </div>
                    </div>

                    <div id="modalDetail<?= $index ?>" class="modal hidden">
                        <div class="modal-content">
                            <button onclick="closeModal('modalDetail<?= $index ?>')" class="modal-close-button">✕</button>
                            <div>
                                <img src="<?= $gambar ?>" class="modal-image">
                                <h2 class="modal-title"><?= htmlspecialchars($buku['judul']) ?></h2>
                                <p><strong>Penulis:</strong> <?= htmlspecialchars($buku['penulis']) ?></p>
                                <p><strong>Penerbit:</strong> <?= htmlspecialchars($buku['penerbit']) ?></p>
                                <p><strong>Tahun:</strong> <?= htmlspecialchars($buku['tahun_terbit']) ?></p>
                                <p><strong>Kategori:</strong> <?= htmlspecialchars($buku['kategori']) ?></p>
                                <p><strong>Stok:</strong> <?= htmlspecialchars($buku['stok']) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

        <div id="no-results" class="no-results-message hidden">
            Tidak ada buku yang cocok dengan pencarian Anda.
        </div>
    </main>

    <footer>
        &copy; <?php echo date("Y"); ?> LibSys - Sistem Informasi Perpustakaan Digital.
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('searchInput');
            const bookListContainer = document.getElementById('book-list');
            const noResultsMessage = document.getElementById('no-results');
            const bookCards = Array.from(document.querySelectorAll('.card-book')); // Convert NodeList to Array

            // Add animation class for initial fade-in
            bookCards.forEach((card, index) => {
                setTimeout(() => {
                    card.classList.add('animate-in');
                }, 100 * index); // Stagger animation for each card
            });

            searchInput.addEventListener('keyup', () => {
                const searchTerm = searchInput.value.toLowerCase();
                let resultsFound = false;

                bookCards.forEach(card => {
                    const title = card.querySelector('.book-title').textContent.toLowerCase();
                    const authorElement = card.querySelector('.book-meta:nth-of-type(1) span');
                    const categoryElement = card.querySelector('.book-meta:nth-of-type(3) span');

                    const author = authorElement ? authorElement.textContent.toLowerCase() : '';
                    const category = categoryElement ? categoryElement.textContent.toLowerCase() : '';

                    if (title.includes(searchTerm) || author.includes(searchTerm) || category.includes(searchTerm)) {
                        card.style.display = ''; // Show card
                        resultsFound = true;
                    } else {
                        card.style.display = 'none'; // Hide card
                    }
                });

                if (!resultsFound && searchTerm !== '') {
                    noResultsMessage.classList.remove('hidden');
                    // Ensure the 'No Results' message spans across the grid columns
                    noResultsMessage.style.gridColumn = '1 / -1';
                } else {
                    noResultsMessage.classList.add('hidden');
                }

                // If no search term, check if there are any books in the first place
                if (searchTerm === '' && bookCards.length === 0) {
                     noResultsMessage.classList.remove('hidden');
                }
            });
        });

        // Modal Functions
        function openModal(id) {
            document.getElementById(id).classList.remove("hidden");
        }

        function closeModal(id) {
            document.getElementById(id).classList.add("hidden");
        }
    </script>
</body>
</html>