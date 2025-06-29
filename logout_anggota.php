<?php
session_start(); // Mulai sesi

// Hapus semua variabel sesi anggota
unset($_SESSION['anggota_loggedin']);
unset($_SESSION['id_anggota']);
unset($_SESSION['nama_anggota']);
unset($_SESSION['nik_anggota']);

// Hancurkan sesi jika tidak ada sesi lain yang aktif
if (empty($_SESSION)) {
    session_destroy();
}

// Redirect ke halaman utama
header("Location: index.php");
exit();
?>
