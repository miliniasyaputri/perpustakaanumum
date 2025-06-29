<?php
session_start();
include 'koneksi.php';

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    $_SESSION['message'] = 'Anda harus login untuk mengakses halaman ini.';
    $_SESSION['message_type'] = 'error';
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" || (isset($_GET['action']) && $_GET['action'] == 'hapus')) {
    $action = isset($_POST['action']) ? $_POST['action'] : $_GET['action'];

    switch ($action) {
        case 'tambah':
    // Validasi input
    $isbn = $koneksi->real_escape_string($_POST['isbn']);
    $judul = $koneksi->real_escape_string($_POST['judul']);
    $penulis = $koneksi->real_escape_string($_POST['penulis']);
    $penerbit = $koneksi->real_escape_string($_POST['penerbit']);
    $tahun_terbit = (int)$_POST['tahun_terbit'];
    $kategori = $koneksi->real_escape_string($_POST['kategori']);
    $stok = (int)$_POST['stok'];

    // Proses upload gambar
    $gambar = $_FILES['gambar']['name'];
    $tmp_name = $_FILES['gambar']['tmp_name'];
    $folder_upload = "uploads/";

    // Buat nama unik agar tidak bentrok
    $ext = pathinfo($gambar, PATHINFO_EXTENSION);
    $nama_file_unik = uniqid('buku_') . '.' . $ext;
    $path_simpan = $folder_upload . $nama_file_unik;

    if (move_uploaded_file($tmp_name, $path_simpan)) {
        // Cek apakah ISBN sudah ada
        $check_isbn_sql = "SELECT COUNT(*) FROM buku WHERE isbn = ?";
        $stmt_check = $koneksi->prepare($check_isbn_sql);
        $stmt_check->bind_param("s", $isbn);
        $stmt_check->execute();
        $stmt_check->bind_result($count_isbn);
        $stmt_check->fetch();
        $stmt_check->close();

        if ($count_isbn > 0) {
            $_SESSION['message'] = 'Gagal menambah buku: ISBN sudah terdaftar.';
            $_SESSION['message_type'] = 'error';
        } else {
            // Simpan ke database (dengan gambar)
            $sql = "INSERT INTO buku (isbn, judul, penulis, penerbit, tahun_terbit, kategori, stok, gambar)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $koneksi->prepare($sql);
            $stmt->bind_param("ssssisds", $isbn, $judul, $penulis, $penerbit, $tahun_terbit, $kategori, $stok, $nama_file_unik);

            if ($stmt->execute()) {
                $_SESSION['message'] = 'Buku berhasil ditambahkan!';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Gagal menambahkan buku: ' . $stmt->error;
                $_SESSION['message_type'] = 'error';
            }
            $stmt->close();
        }
    } else {
        $_SESSION['message'] = 'Upload gambar gagal.';
        $_SESSION['message_type'] = 'error';
    }

    break;


        case 'edit':
            $id_buku = (int)$_POST['id_buku'];
            $isbn = $koneksi->real_escape_string($_POST['isbn']);
            $judul = $koneksi->real_escape_string($_POST['judul']);
            $penulis = $koneksi->real_escape_string($_POST['penulis']);
            $penerbit = $koneksi->real_escape_string($_POST['penerbit']);
            $tahun_terbit = (int)$_POST['tahun_terbit'];
            $kategori = $koneksi->real_escape_string($_POST['kategori']);
            $stok = (int)$_POST['stok'];

            // Cek apakah ISBN sudah ada pada buku lain
            $check_isbn_sql = "SELECT COUNT(*) FROM buku WHERE isbn = ? AND id_buku != ?";
            $stmt_check = $koneksi->prepare($check_isbn_sql);
            $stmt_check->bind_param("si", $isbn, $id_buku);
            $stmt_check->execute();
            $stmt_check->bind_result($count_isbn);
            $stmt_check->fetch();
            $stmt_check->close();

            if ($count_isbn > 0) {
                $_SESSION['message'] = 'Gagal mengedit buku: ISBN sudah terdaftar pada buku lain.';
                $_SESSION['message_type'] = 'error';
            } else {
                $sql = "UPDATE buku SET isbn = ?, judul = ?, penulis = ?, penerbit = ?, tahun_terbit = ?, kategori = ?, stok = ? WHERE id_buku = ?";
                $stmt = $koneksi->prepare($sql);
                $stmt->bind_param("ssssisii", $isbn, $judul, $penulis, $penerbit, $tahun_terbit, $kategori, $stok, $id_buku);

                if ($stmt->execute()) {
                    $_SESSION['message'] = 'Buku berhasil diperbarui!';
                    $_SESSION['message_type'] = 'success';
                } else {
                    $_SESSION['message'] = 'Gagal memperbarui buku: ' . $stmt->error;
                    $_SESSION['message_type'] = 'error';
                }
                $stmt->close();
            }
            break;
case 'hapus':
    $id_buku = (int)$_GET['id'];

    // Ambil semua id_peminjaman yang terkait dengan buku ini
    $stmt_peminjaman = $koneksi->prepare("SELECT id_peminjaman FROM peminjaman WHERE id_buku = ?");
    $stmt_peminjaman->bind_param("i", $id_buku);
    $stmt_peminjaman->execute();
    $result_peminjaman = $stmt_peminjaman->get_result();

    // Hapus semua pengembalian yang terkait dengan peminjaman tersebut
    while ($row = $result_peminjaman->fetch_assoc()) {
        $id_peminjaman = $row['id_peminjaman'];

        $stmt_hapus_pengembalian = $koneksi->prepare("DELETE FROM pengembalian WHERE id_peminjaman = ?");
        $stmt_hapus_pengembalian->bind_param("i", $id_peminjaman);
        $stmt_hapus_pengembalian->execute();
        $stmt_hapus_pengembalian->close();
    }
    $stmt_peminjaman->close();

    // Hapus semua peminjaman yang terkait buku ini
    $stmt_hapus_peminjaman = $koneksi->prepare("DELETE FROM peminjaman WHERE id_buku = ?");
    $stmt_hapus_peminjaman->bind_param("i", $id_buku);
    $stmt_hapus_peminjaman->execute();
    $stmt_hapus_peminjaman->close();

    // Terakhir, hapus bukunya
    $stmt_hapus_buku = $koneksi->prepare("DELETE FROM buku WHERE id_buku = ?");
    $stmt_hapus_buku->bind_param("i", $id_buku);
    if ($stmt_hapus_buku->execute()) {
        $_SESSION['message'] = 'Buku beserta data peminjaman & pengembalian berhasil dihapus!';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Gagal menghapus buku: ' . $stmt_hapus_buku->error;
        $_SESSION['message_type'] = 'error';
    }
    $stmt_hapus_buku->close();
    break;



        default:
            $_SESSION['message'] = 'Aksi tidak dikenal.';
            $_SESSION['message_type'] = 'error';
            break;
    }
    $koneksi->close();
    header("Location: data_buku.php");
    exit();

} else {
    // Jika akses langsung atau method tidak valid
    $_SESSION['message'] = 'Metode request tidak valid.';
    $_SESSION['message_type'] = 'error';
    header("Location: data_buku.php");
    exit();
}
?>
