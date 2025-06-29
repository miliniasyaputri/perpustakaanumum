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
            $nik = $koneksi->real_escape_string($_POST['nik']);
            $nama = $koneksi->real_escape_string($_POST['nama']);
            $alamat = $koneksi->real_escape_string($_POST['alamat']);
            $no_hp = $koneksi->real_escape_string($_POST['no_hp']);
            $email = $koneksi->real_escape_string($_POST['email']);

            // Cek apakah NIK sudah ada
            $check_nik_sql = "SELECT COUNT(*) FROM anggota WHERE nik = ?";
            $stmt_check = $koneksi->prepare($check_nik_sql);
            $stmt_check->bind_param("s", $nik);
            $stmt_check->execute();
            $stmt_check->bind_result($count_nik);
            $stmt_check->fetch();
            $stmt_check->close();

            if ($count_nik > 0) {
                $_SESSION['message'] = 'Gagal menambah anggota: NIK sudah terdaftar.';
                $_SESSION['message_type'] = 'error';
            } else {
                $sql = "INSERT INTO anggota (nik, nama, alamat, no_hp, email) VALUES (?, ?, ?, ?, ?)";
                $stmt = $koneksi->prepare($sql);
                $stmt->bind_param("sssss", $nik, $nama, $alamat, $no_hp, $email);

                if ($stmt->execute()) {
                    $_SESSION['message'] = 'Anggota berhasil ditambahkan!';
                    $_SESSION['message_type'] = 'success';
                } else {
                    $_SESSION['message'] = 'Gagal menambahkan anggota: ' . $stmt->error;
                    $_SESSION['message_type'] = 'error';
                }
                $stmt->close();
            }
            break;

        case 'edit':
            $id_anggota = (int)$_POST['id_anggota'];
            $nik = $koneksi->real_escape_string($_POST['nik']);
            $nama = $koneksi->real_escape_string($_POST['nama']);
            $alamat = $koneksi->real_escape_string($_POST['alamat']);
            $no_hp = $koneksi->real_escape_string($_POST['no_hp']);
            $email = $koneksi->real_escape_string($_POST['email']);

            // Cek apakah NIK sudah ada pada anggota lain
            $check_nik_sql = "SELECT COUNT(*) FROM anggota WHERE nik = ? AND id_anggota != ?";
            $stmt_check = $koneksi->prepare($check_nik_sql);
            $stmt_check->bind_param("si", $nik, $id_anggota);
            $stmt_check->execute();
            $stmt_check->bind_result($count_nik);
            $stmt_check->fetch();
            $stmt_check->close();

            if ($count_nik > 0) {
                $_SESSION['message'] = 'Gagal mengedit anggota: NIK sudah terdaftar pada anggota lain.';
                $_SESSION['message_type'] = 'error';
            } else {
                $sql = "UPDATE anggota SET nik = ?, nama = ?, alamat = ?, no_hp = ?, email = ? WHERE id_anggota = ?";
                $stmt = $koneksi->prepare($sql);
                $stmt->bind_param("sssssi", $nik, $nama, $alamat, $no_hp, $email, $id_anggota);

                if ($stmt->execute()) {
                    $_SESSION['message'] = 'Anggota berhasil diperbarui!';
                    $_SESSION['message_type'] = 'success';
                } else {
                    $_SESSION['message'] = 'Gagal memperbarui anggota: ' . $stmt->error;
                    $_SESSION['message_type'] = 'error';
                }
                $stmt->close();
            }
            break;

        case 'hapus':
            $id_anggota = (int)$_GET['id'];
            $sql = "DELETE FROM anggota WHERE id_anggota = ?";
            $stmt = $koneksi->prepare($sql);
            $stmt->bind_param("i", $id_anggota);

            if ($stmt->execute()) {
                $_SESSION['message'] = 'Anggota berhasil dihapus!';
                $_SESSION['message_type'] = 'success';
            } else {
                // Cek jika error karena foreign key constraint
                if ($koneksi->errno == 1451) {
                    $_SESSION['message'] = 'Gagal menghapus anggota: Anggota ini memiliki data peminjaman terkait. Hapus data peminjaman terlebih dahulu.';
                    $_SESSION['message_type'] = 'error';
                } else {
                    $_SESSION['message'] = 'Gagal menghapus anggota: ' . $stmt->error;
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
    header("Location: data_anggota.php");
    exit();

} else {
    // Jika akses langsung atau method tidak valid
    $_SESSION['message'] = 'Metode request tidak valid.';
    $_SESSION['message_type'] = 'error';
    header("Location: data_anggota.php");
    exit();
}
?>
