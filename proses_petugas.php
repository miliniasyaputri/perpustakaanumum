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
            $nama_petugas = $koneksi->real_escape_string($_POST['nama_petugas']);
            $username = $koneksi->real_escape_string($_POST['username']);
            $password = $koneksi->real_escape_string($_POST['password']); // Ambil password tanpa hashing

            // Cek apakah username sudah ada
            $check_username_sql = "SELECT COUNT(*) FROM petugas WHERE username = ?";
            $stmt_check = $koneksi->prepare($check_username_sql);
            $stmt_check->bind_param("s", $username);
            $stmt_check->execute();
            $stmt_check->bind_result($count_username);
            $stmt_check->fetch();
            $stmt_check->close();

            if ($count_username > 0) {
                $_SESSION['message'] = 'Gagal menambah petugas: Username sudah terdaftar.';
                $_SESSION['message_type'] = 'error';
            } else {
                // Simpan password dalam bentuk teks biasa
                $sql = "INSERT INTO petugas (nama_petugas, username, password) VALUES (?, ?, ?)";
                $stmt = $koneksi->prepare($sql);
                $stmt->bind_param("sss", $nama_petugas, $username, $password); // Menggunakan variabel $password langsung

                if ($stmt->execute()) {
                    $_SESSION['message'] = 'Petugas berhasil ditambahkan!';
                    $_SESSION['message_type'] = 'success';
                } else {
                    $_SESSION['message'] = 'Gagal menambahkan petugas: ' . $stmt->error;
                    $_SESSION['message_type'] = 'error';
                }
                $stmt->close();
            }
            break;

        case 'edit':
            $id_petugas = (int)$_POST['id_petugas'];
            $nama_petugas = $koneksi->real_escape_string($_POST['nama_petugas']);
            $username = $koneksi->real_escape_string($_POST['username']);
            $password = $_POST['password']; // Bisa kosong jika tidak diubah

            // Cek apakah username sudah ada pada petugas lain
            $check_username_sql = "SELECT COUNT(*) FROM petugas WHERE username = ? AND id_petugas != ?";
            $stmt_check = $koneksi->prepare($check_username_sql);
            $stmt_check->bind_param("si", $username, $id_petugas);
            $stmt_check->execute();
            $stmt_check->bind_result($count_username);
            $stmt_check->fetch();
            $stmt_check->close();

            if ($count_username > 0) {
                $_SESSION['message'] = 'Gagal mengedit petugas: Username sudah terdaftar pada petugas lain.';
                $_SESSION['message_type'] = 'error';
            } else {
                if (!empty($password)) {
                    // Update password dalam bentuk teks biasa
                    $sql = "UPDATE petugas SET nama_petugas = ?, username = ?, password = ? WHERE id_petugas = ?";
                    $stmt = $koneksi->prepare($sql);
                    $stmt->bind_param("sssi", $nama_petugas, $username, $password, $id_petugas);
                } else {
                    // Jika password kosong, jangan update password
                    $sql = "UPDATE petugas SET nama_petugas = ?, username = ? WHERE id_petugas = ?";
                    $stmt = $koneksi->prepare($sql);
                    $stmt->bind_param("ssi", $nama_petugas, $username, $id_petugas);
                }

                if ($stmt->execute()) {
                    $_SESSION['message'] = 'Petugas berhasil diperbarui!';
                    $_SESSION['message_type'] = 'success';
                } else {
                    $_SESSION['message'] = 'Gagal memperbarui petugas: ' . $stmt->error;
                    $_SESSION['message_type'] = 'error';
                }
                $stmt->close();
            }
            break;

        case 'hapus':
            $id_petugas = (int)$_GET['id'];
            $id_petugas_session = $_SESSION['id_petugas'];

            // Pastikan petugas tidak menghapus dirinya sendiri
            if ($id_petugas == $id_petugas_session) {
                $_SESSION['message'] = 'Anda tidak dapat menghapus akun Anda sendiri!';
                $_SESSION['message_type'] = 'error';
            } else {
                $sql = "DELETE FROM petugas WHERE id_petugas = ?";
                $stmt = $koneksi->prepare($sql);
                $stmt->bind_param("i", $id_petugas);

                if ($stmt->execute()) {
                    $_SESSION['message'] = 'Petugas berhasil dihapus!';
                    $_SESSION['message_type'] = 'success';
                } else {
                    // Cek jika error karena foreign key constraint
                    if ($koneksi->errno == 1451) {
                        $_SESSION['message'] = 'Gagal menghapus petugas: Petugas ini memiliki data peminjaman terkait. Hapus data peminjaman terlebih dahulu.';
                        $_SESSION['message_type'] = 'error';
                    } else {
                        $_SESSION['message'] = 'Gagal menghapus petugas: ' . $stmt->error;
                        $_SESSION['message_type'] = 'error';
                    }
                }
                $stmt->close();
            }
            break;

        default:
            $_SESSION['message'] = 'Aksi tidak dikenal.';
            $_SESSION['message_type'] = 'error';
            break;
    }
    $koneksi->close();
    header("Location: data_petugas.php");
    exit();

} else {
    // Jika akses langsung atau method tidak valid
    $_SESSION['message'] = 'Metode request tidak valid.';
    $_SESSION['message_type'] = 'error';
    header("Location: data_petugas.php");
    exit();
}
?>