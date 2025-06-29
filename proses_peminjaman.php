<?php
session_start();
include 'koneksi.php';

// Cek sesi login
$is_petugas = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
$is_anggota = isset($_SESSION['anggota_loggedin']) && $_SESSION['anggota_loggedin'] === true;

// Redirect jika belum login
if (!$is_petugas && !$is_anggota) {
    $_SESSION['message'] = 'Anda harus login untuk melakukan aksi ini.';
    $_SESSION['message_type'] = 'error';
    header("Location: index.php");
    exit();
}

$redirect_url = $is_petugas ? "data_peminjaman.php" : "dashboard_anggota.php";

// Tangani request
if ($_SERVER["REQUEST_METHOD"] == "POST" || (isset($_GET['action']) && ($_GET['action'] == 'kembalikan' || $_GET['action'] == 'hapus'))) {
    $action = $_POST['action'] ?? $_GET['action'];

    switch ($action) {
        case 'tambah':
            // Ambil input
            $id_anggota = $is_petugas ? (int)$_POST['id_anggota'] : (int)$_SESSION['id_anggota'];
            $id_buku = (int)$_POST['id_buku'];
            $tanggal_pinjam = $koneksi->real_escape_string($_POST['tanggal_pinjam']);
            $tanggal_kembali_rencana = empty($_POST['tanggal_kembali']) ? NULL : $koneksi->real_escape_string($_POST['tanggal_kembali']);
            $id_petugas = $is_petugas ? $_SESSION['id_petugas'] : NULL;

            // Validasi anggota ada
            $cek_anggota = $koneksi->prepare("SELECT 1 FROM anggota WHERE id_anggota = ?");
            $cek_anggota->bind_param("i", $id_anggota);
            $cek_anggota->execute();
            $cek_anggota->store_result();
            if ($cek_anggota->num_rows === 0) {
                $_SESSION['message'] = "ID anggota ($id_anggota) tidak ditemukan di database.";
                $_SESSION['message_type'] = 'error';
                header("Location: $redirect_url");
                exit();
            }
            $cek_anggota->close();

            // 1. Cek stok buku
            $sql_check_stok = "SELECT stok FROM buku WHERE id_buku = ?";
            $stmt_check_stok = $koneksi->prepare($sql_check_stok);
            $stmt_check_stok->bind_param("i", $id_buku);
            $stmt_check_stok->execute();
            $result_stok = $stmt_check_stok->get_result();
            $buku_stok = $result_stok->fetch_assoc();
            $stmt_check_stok->close();

            if (!$buku_stok || $buku_stok['stok'] <= 0) {
                $_SESSION['message'] = 'Gagal mencatat peminjaman: Stok buku tidak tersedia atau buku tidak ditemukan.';
                $_SESSION['message_type'] = 'error';
                header("Location: $redirect_url");
                exit();
            }

            // 2. Cek duplikat peminjaman
            $sql_check_duplicate_loan = "SELECT COUNT(*) FROM peminjaman WHERE id_anggota = ? AND id_buku = ? AND status = 'Dipinjam'";
            $stmt_check_duplicate = $koneksi->prepare($sql_check_duplicate_loan);
            $stmt_check_duplicate->bind_param("ii", $id_anggota, $id_buku);
            $stmt_check_duplicate->execute();
            $stmt_check_duplicate->bind_result($count_active_loans);
            $stmt_check_duplicate->fetch();
            $stmt_check_duplicate->close();

            if ($count_active_loans > 0) {
                $_SESSION['message'] = 'Gagal mencatat peminjaman: Anggota ini sudah meminjam buku yang sama dan belum mengembalikannya.';
                $_SESSION['message_type'] = 'error';
                header("Location: $redirect_url");
                exit();
            }

            // 3. Insert peminjaman
            $sql_insert_peminjaman = "INSERT INTO peminjaman (id_anggota, id_buku, id_petugas, tanggal_pinjam, tanggal_kembali, status) VALUES (?, ?, ?, ?, ?, 'Dipinjam')";
            $stmt_insert_peminjaman = $koneksi->prepare($sql_insert_peminjaman);
            $stmt_insert_peminjaman->bind_param("iiiss", $id_anggota, $id_buku, $id_petugas, $tanggal_pinjam, $tanggal_kembali_rencana);

            if ($stmt_insert_peminjaman->execute()) {
                // 4. Kurangi stok buku
                $sql_update_stok = "UPDATE buku SET stok = stok - 1 WHERE id_buku = ?";
                $stmt_update_stok = $koneksi->prepare($sql_update_stok);
                $stmt_update_stok->bind_param("i", $id_buku);
                $stmt_update_stok->execute();
                $stmt_update_stok->close();

                $_SESSION['message'] = 'Peminjaman berhasil dicatat!';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Gagal mencatat peminjaman: ' . $stmt_insert_peminjaman->error;
                $_SESSION['message_type'] = 'error';
            }
            $stmt_insert_peminjaman->close();
            break;

        case 'kembalikan':
            $id_peminjaman = (int)$_GET['id'];
            $tanggal_dikembalikan = date('Y-m-d');
            $id_petugas_for_return = $is_petugas ? $_SESSION['id_petugas'] : NULL;

            // Ambil info peminjaman
            $sql_get_peminjaman = "SELECT id_buku, id_anggota, tanggal_pinjam, tanggal_kembali FROM peminjaman WHERE id_peminjaman = ? AND status = 'Dipinjam'";
            $stmt_get_peminjaman = $koneksi->prepare($sql_get_peminjaman);
            $stmt_get_peminjaman->bind_param("i", $id_peminjaman);
            $stmt_get_peminjaman->execute();
            $result_peminjaman = $stmt_get_peminjaman->get_result();
            $peminjaman_info = $result_peminjaman->fetch_assoc();
            $stmt_get_peminjaman->close();

            if (!$peminjaman_info) {
                $_SESSION['message'] = 'Peminjaman tidak ditemukan atau sudah dikembalikan.';
                $_SESSION['message_type'] = 'error';
                header("Location: $redirect_url");
                exit();
            }

            // Jika anggota login, validasi hak akses
            if ($is_anggota && $peminjaman_info['id_anggota'] != $_SESSION['id_anggota']) {
                $_SESSION['message'] = 'Anda tidak memiliki izin untuk mengembalikan buku ini.';
                $_SESSION['message_type'] = 'error';
                header("Location: $redirect_url");
                exit();
            }

            $id_buku = $peminjaman_info['id_buku'];
            $tanggal_kembali_rencana_asli = $peminjaman_info['tanggal_kembali'];

            // Hitung denda jika terlambat
            $denda = 0;
            if ($tanggal_kembali_rencana_asli && $tanggal_dikembalikan > $tanggal_kembali_rencana_asli) {
                $date1 = new DateTime($tanggal_kembali_rencana_asli);
                $date2 = new DateTime($tanggal_dikembalikan);
                $interval = $date1->diff($date2);
                $denda = $interval->days * 1000;
            }

            // Update status peminjaman
            $sql_update_peminjaman = "UPDATE peminjaman SET tanggal_kembali = ?, status = 'Dikembalikan', id_petugas = ? WHERE id_peminjaman = ?";
            $stmt_update_peminjaman = $koneksi->prepare($sql_update_peminjaman);
            $stmt_update_peminjaman->bind_param("sii", $tanggal_dikembalikan, $id_petugas_for_return, $id_peminjaman);

            if ($stmt_update_peminjaman->execute()) {
                // Tambahkan ke tabel pengembalian
                $sql_insert_pengembalian = "INSERT INTO pengembalian (id_peminjaman, tanggal_dikembalikan, denda) VALUES (?, ?, ?)";
                $stmt_insert_pengembalian = $koneksi->prepare($sql_insert_pengembalian);
                $stmt_insert_pengembalian->bind_param("isi", $id_peminjaman, $tanggal_dikembalikan, $denda);
                $stmt_insert_pengembalian->execute();

                // Tambah stok buku
                $sql_update_stok = "UPDATE buku SET stok = stok + 1 WHERE id_buku = ?";
                $stmt_update_stok = $koneksi->prepare($sql_update_stok);
                $stmt_update_stok->bind_param("i", $id_buku);
                $stmt_update_stok->execute();
                $stmt_update_stok->close();

                $_SESSION['message'] = 'Buku berhasil dikembalikan!';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Gagal mengembalikan buku: ' . $stmt_update_peminjaman->error;
                $_SESSION['message_type'] = 'error';
            }
            $stmt_update_peminjaman->close();
            break;

        default:
            $_SESSION['message'] = 'Aksi tidak dikenal.';
            $_SESSION['message_type'] = 'error';
            break;
    }

    $koneksi->close();
    header("Location: $redirect_url");
    exit();
} else {
    $_SESSION['message'] = 'Metode request tidak valid.';
    $_SESSION['message_type'] = 'error';
    header("Location: $redirect_url");
    exit();
}
