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
            $nama_kategori = $koneksi->real_escape_string($_POST['nama_kategori']);

            // Cek apakah nama kategori sudah ada
            $check_kategori_sql = "SELECT COUNT(*) FROM kategori WHERE nama_kategori = ?";
            $stmt_check = $koneksi->prepare($check_kategori_sql);
            $stmt_check->bind_param("s", $nama_kategori);
            $stmt_check->execute();
            $stmt_check->bind_result($count_kategori);
            $stmt_check->fetch();
            $stmt_check->close();

            if ($count_kategori > 0) {
                $_SESSION['message'] = 'Gagal menambah kategori: Nama kategori sudah ada.';
                $_SESSION['message_type'] = 'error';
            } else {
                $sql = "INSERT INTO kategori (nama_kategori) VALUES (?)";
                $stmt = $koneksi->prepare($sql);
                $stmt->bind_param("s", $nama_kategori);

                if ($stmt->execute()) {
                    $_SESSION['message'] = 'Kategori berhasil ditambahkan!';
                    $_SESSION['message_type'] = 'success';
                } else {
                    $_SESSION['message'] = 'Gagal menambahkan kategori: ' . $stmt->error;
                    $_SESSION['message_type'] = 'error';
                }
                $stmt->close();
            }
            break;

        case 'edit':
            $id_kategori = (int)$_POST['id_kategori'];
            $nama_kategori = $koneksi->real_escape_string($_POST['nama_kategori']);

            // Cek apakah nama kategori sudah ada pada kategori lain
            $check_kategori_sql = "SELECT COUNT(*) FROM kategori WHERE nama_kategori = ? AND id_kategori != ?";
            $stmt_check = $koneksi->prepare($check_kategori_sql);
            $stmt_check->bind_param("si", $nama_kategori, $id_kategori);
            $stmt_check->execute();
            $stmt_check->bind_result($count_kategori);
            $stmt_check->fetch();
            $stmt_check->close();

            if ($count_kategori > 0) {
                $_SESSION['message'] = 'Gagal mengedit kategori: Nama kategori sudah terdaftar pada kategori lain.';
                $_SESSION['message_type'] = 'error';
            } else {
                $sql = "UPDATE kategori SET nama_kategori = ? WHERE id_kategori = ?";
                $stmt = $koneksi->prepare($sql);
                $stmt->bind_param("si", $nama_kategori, $id_kategori);

                if ($stmt->execute()) {
                    $_SESSION['message'] = 'Kategori berhasil diperbarui!';
                    $_SESSION['message_type'] = 'success';
                } else {
                    $_SESSION['message'] = 'Gagal memperbarui kategori: ' . $stmt->error;
                    $_SESSION['message_type'] = 'error';
                }
                $stmt->close();
            }
            break;

        case 'hapus':
            $id_kategori = (int)$_GET['id'];
            $sql = "DELETE FROM kategori WHERE id_kategori = ?";
            $stmt = $koneksi->prepare($sql);
            $stmt->bind_param("i", $id_kategori);

            if ($stmt->execute()) {
                $_SESSION['message'] = 'Kategori berhasil dihapus!';
                $_SESSION['message_type'] = 'success';
            } else {
                // Cek jika error karena foreign key constraint
                // Jika ada buku yang menggunakan kategori ini, maka tidak bisa dihapus
                if ($koneksi->errno == 1451) {
                    $_SESSION['message'] = 'Gagal menghapus kategori: Terdapat buku yang masih menggunakan kategori ini. Harap ubah kategori buku tersebut terlebih dahulu.';
                    $_SESSION['message_type'] = 'error';
                } else {
                    $_SESSION['message'] = 'Gagal menghapus kategori: ' . $stmt->error;
                    $_SESSION['message_type'] = 'error';
                }
            }
            $stmt->close();
            break;

        default:
            $_SESSION['message'] = 'Aksi tidak dikenal.';
            $_SESSION['message_type'] = 'error';
            break;
    }
    $koneksi->close();
    header("Location: data_kategori.php");
    exit();

} else {
    // Jika akses langsung atau method tidak valid
    $_SESSION['message'] = 'Metode request tidak valid.';
    $_SESSION['message_type'] = 'error';
    header("Location: data_kategori.php");
    exit();
}
?>
