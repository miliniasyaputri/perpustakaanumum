<?php
session_start(); // Mulai sesi

// Memuat file koneksi database
include 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form dan bersihkan
    $username = $koneksi->real_escape_string($_POST['username']);
    $password = $koneksi->real_escape_string($_POST['password']); // karena langsung dibandingkan, harus di-escape

    // Query untuk mencari petugas berdasarkan username
    $sql = "SELECT id_petugas, nama_petugas, username, password FROM petugas WHERE username = ?";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        // Verifikasi password secara langsung (tanpa hash)
        if ($password === $row['password']) {
            // Login berhasil
            $_SESSION['loggedin'] = true;
            $_SESSION['id_petugas'] = $row['id_petugas'];
            $_SESSION['nama_petugas'] = $row['nama_petugas'];
            $_SESSION['username'] = $row['username'];

            header("Location: dashboard.php");
            exit();
        } else {
            // Password salah
            header("Location: index.php?error=" . urlencode("Username atau password salah!"));
            exit();
        }
    } else {
        // Username tidak ditemukan
        header("Location: index.php?error=" . urlencode("Username atau password salah!"));
        exit();
    }

    $stmt->close();
    $koneksi->close();
} else {
    // Akses langsung tanpa POST
    header("Location: index.php");
    exit();
}
?>
