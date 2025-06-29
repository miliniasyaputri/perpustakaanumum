<?php
session_start();
include 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil dan amankan input dari form
    $nama = $koneksi->real_escape_string($_POST['nama']);
    $nik = $koneksi->real_escape_string($_POST['nik']);
    $alamat = $koneksi->real_escape_string($_POST['alamat']);
    $no_hp = $koneksi->real_escape_string($_POST['no_hp']);
    $email = $koneksi->real_escape_string($_POST['email']);
    $password_anggota = $koneksi->real_escape_string($_POST['password_anggota']);
    $confirm_password = $koneksi->real_escape_string($_POST['confirm_password']);

    // Validasi input kosong
    if (empty($nama) || empty($nik) || empty($password_anggota) || empty($confirm_password)) {
        $_SESSION['register_anggota_error'] = "Semua field wajib diisi.";
        header("Location: register_anggota.php");
        exit();
    }

    // Validasi kesamaan password
    if ($password_anggota !== $confirm_password) {
        $_SESSION['register_anggota_error'] = "Konfirmasi password tidak cocok.";
        header("Location: register_anggota.php");
        exit();
    }

    // Cek apakah NIK sudah terdaftar
    $check_nik_sql = "SELECT COUNT(*) FROM anggota WHERE nik = ?";
    $stmt_check = $koneksi->prepare($check_nik_sql);
    $stmt_check->bind_param("s", $nik);
    $stmt_check->execute();
    $stmt_check->bind_result($count_nik);
    $stmt_check->fetch();
    $stmt_check->close();

    if ($count_nik > 0) {
        $_SESSION['register_anggota_error'] = "NIK sudah terdaftar. Silakan gunakan NIK lain atau login.";
        header("Location: register_anggota.php");
        exit();
    }

    // Simpan data ke tabel anggota (password disimpan tanpa hash)
    $sql = "INSERT INTO anggota (nik, nama, alamat, no_hp, email, password) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param("ssssss", $nik, $nama, $alamat, $no_hp, $email, $password_anggota);

    if ($stmt->execute()) {
        $_SESSION['register_anggota_success'] = "Pendaftaran berhasil! Silakan login.";
        header("Location: login_anggota.php");
        exit();
    } else {
        $_SESSION['register_anggota_error'] = "Pendaftaran gagal: " . $stmt->error;
        header("Location: register_anggota.php");
        exit();
    }

    $stmt->close();
    $koneksi->close();
} else {
    header("Location: register_anggota.php");
    exit();
}
?>
