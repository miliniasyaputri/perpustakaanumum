<?php
session_start();
include 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nik = $_POST['nik'];
    $password = $_POST['password_anggota'];

    // Query untuk mencocokkan NIK dan password (tanpa hash)
    $sql = "SELECT id_anggota, nama, nik, password FROM anggota WHERE nik = ? AND password = ?";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param("ss", $nik, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $anggota = $result->fetch_assoc();

        // Debug (boleh dihapus nanti)
        // echo '<pre>'; print_r($anggota); echo '</pre>'; exit;

        // ✅ Set session dengan data yang benar
        $_SESSION['anggota_loggedin'] = true;
        $_SESSION['id_anggota'] = $anggota['id_anggota'];
        $_SESSION['nama_anggota'] = $anggota['nama'];

        header("Location: dashboard_anggota.php");
        exit();
    } else {
        // Jika login gagal
        $_SESSION['login_anggota_error'] = "NIK atau Password salah.";
        header("Location: login_anggota.php");
        exit();
    }
}
?>
