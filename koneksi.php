<?php
$host = "localhost"; // Ganti jika host database Anda berbeda
$user = "root";      // Ganti dengan username database Anda
$pass = "";          // Ganti dengan password database Anda (kosong jika tidak ada)
$db   = "perpustakaan"; // Ganti dengan nama database Anda

// Membuat koneksi ke database
$koneksi = new mysqli($host, $user, $pass, $db);

// Memeriksa koneksi
if ($koneksi->connect_error) {
    die("Koneksi database gagal: " . $koneksi->connect_error);
}

// Set karakter set ke utf8mb4 untuk mendukung berbagai karakter
$koneksi->set_charset("utf8mb4");

//echo "Koneksi database berhasil!"; // Anda bisa menghapus baris ini setelah memastikan koneksi berhasil

?>
