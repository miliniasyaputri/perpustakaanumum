<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Anggota Baru - Perpustakaan</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* --- General Body and Layout --- */
        body {
            font-family: 'Inter', sans-serif;
            background: #9295d0;
            min-height: 100vh;
            padding: 1.5rem;
            overflow-x: hidden;
            overflow-y: auto;
            display: flex;
            justify-content: center;
            align-items: center;
            box-sizing: border-box;
            margin: 0;
        }

        /* --- Enhanced Glassmorphism Card --- */
        .card-register {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 2.5rem;
            box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.35), 0 20px 40px -10px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(30px) saturate(1.8);
            border: 2px solid rgba(255, 255, 255, 0.7);
            padding: 3.5rem;
            text-align: center;
            max-width: 600px;
            width: 100%;
            animation: fadeInScale 1.2s cubic-bezier(0.23, 1, 0.32, 1) forwards;
            transform: scale(0.95) translateY(30px);
            opacity: 0;
            position: relative;
            overflow: hidden;
            margin: 2rem auto;
        }

        @keyframes fadeInScale {
            0% { opacity: 0; transform: scale(0.95) translateY(30px); }
            100% { opacity: 1; transform: scale(1) translateY(0); }
        }

        /* --- Typography --- */
        .card-title {
            font-size: 3.5rem;
            font-weight: 800;
            color: #1F2937;
            margin-bottom: 1.5rem;
            line-height: 1.1;
            letter-spacing: -0.02em;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.1);
        }
        .card-subtitle {
            font-size: 1.25rem;
            font-weight: 600;
            color: #4B5563;
            margin-bottom: 2.5rem;
        }

        /* --- Input Fields and Form --- */
        .form-container {
            display: flex;
            flex-direction: column;
            gap: 1.5rem; /* Equivalent to space-y-6 */
        }
        .form-group {
            text-align: left;
        }
        .label {
            display: block;
            font-size: 0.875rem; /* Equivalent to text-sm */
            font-weight: 500; /* Equivalent to font-medium */
            color: #4B5563; /* Equivalent to text-gray-700 */
            margin-bottom: 0.25rem; /* Equivalent to mb-1 */
        }
        .input-field {
            width: 100%;
            padding: 1.1rem 1.5rem;
            border-radius: 0.75rem;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            font-size: 1.1rem;
            outline: none;
            transition: all 0.3s ease;
            color: #1F2937;
        }
        .input-field:focus {
            box-shadow: 0 4px 15px rgba(0,0,0,0.15), 0 0 0 4px rgba(255, 111, 145, 0.3);
        }
        .input-field::placeholder {
            color: #6B7280;
            opacity: 0.8;
        }
        textarea.input-field {
            min-height: 80px;
            resize: vertical;
        }

        /* --- Primary Button --- */
        .btn-primary {
            background-color: #294a8f;
            color: white;
            width: 100%;
            padding: 1.3rem 2.8rem;
            border-radius: 1rem;
            font-size: 1.35rem;
            font-weight: 800;
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            position: relative;
            overflow: hidden;
            border: none;
            cursor: pointer;
            margin-top: 1.5rem;
        }
        .btn-primary:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            background-color: #294a8f;
        }
        .btn-primary i {
            margin-right: 0.5rem;
        }
        /* Ripple effect */
        .btn-primary .ripple {
            position: absolute;
            border-radius: 50%;
            transform: scale(0);
            animation: ripple 0.6s linear;
            background-color: rgba(255, 255, 255, 0.7);
        }
        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }

        /* --- Back Button --- */
        .back-button-container {
            display: flex;
            justify-content: flex-start;
            margin-bottom: 1.5rem;
        }
        .back-button {
            color: #4B5563;
            text-decoration: none;
            display: flex;
            align-items: center;
            transition: all 0.2s ease;
            font-size: 1.1rem;
            font-weight: 500;
        }
        .back-button:hover {
            color: #1F2937;
            transform: translateX(-5px);
        }
        .back-button i {
            margin-right: 0.5rem;
        }

        /* --- Login Link --- */
        .login-link-container {
            text-align: center;
            color: #4B5563; /* Equivalent to text-gray-600 */
            font-size: 0.875rem; /* Equivalent to text-sm */
            margin-top: 1.5rem; /* Equivalent to mt-6 */
        }
        .login-link {
            color: #294a8f;
            font-weight: 700;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        .login-link:hover {
            color: #1f3a74;
        }

        /* --- Message Boxes --- */
        .message-box {
            padding: 1rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            font-weight: 600;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .message-box.error {
            background-color: #9fb7e3;
            border: 1px solid #9fb7e3;
            color: #991B1B;
        }
        .message-box.success {
            background-color: #9fb7e3;
            border: 1px solid #9fb7e3;
            color: #0a111a;
        }

        /* Responsive adjustments */
        @media (max-width: 640px) {
            body { padding: 1rem; }
            .card-register {
                padding: 2rem;
                border-radius: 2rem;
                margin: 1rem auto;
            }
            .card-title { font-size: 2.5rem; }
            .card-subtitle { font-size: 1rem; }
            .input-field { padding: 0.9rem 1.2rem; font-size: 1rem; }
            .btn-primary { padding: 1.1rem 2rem; font-size: 1.1rem; }
        }
    </style>
</head>
<body>
    <div class="card-register">
        <div class="back-button-container">
            <a href="index.php" class="back-button">
                <i class="fas fa-arrow-left"></i> Kembali ke Beranda
            </a>
        </div>
        <h2 class="card-title">Daftar Anggota Baru</h2>
        <p class="card-subtitle">Isi formulir di bawah untuk mendaftar sebagai anggota perpustakaan</p>

        <?php
        // Menampilkan pesan error jika ada
        if (isset($_SESSION['register_anggota_error'])) {
            echo '<div class="message-box error">' . htmlspecialchars($_SESSION['register_anggota_error']) . '</div>';
            unset($_SESSION['register_anggota_error']);
        }
        ?>

        <form action="proses_register_anggota.php" method="POST" class="form-container">
            <div class="form-group">
                <label for="nama" class="label">Nama Lengkap</label>
                <input type="text" id="nama" name="nama" class="input-field" required>
            </div>
            <div class="form-group">
                <label for="nik" class="label">NIK (Nomor Induk Kependudukan)</label>
                <input type="text" id="nik" name="nik" class="input-field" required maxlength="16">
            </div>
            <div class="form-group">
                <label for="alamat" class="label">Alamat</label>
                <textarea id="alamat" name="alamat" rows="3" class="input-field" required></textarea>
            </div>
            <div class="form-group">
                <label for="no_hp" class="label">Nomor Telepon</label>
                <input type="tel" id="no_hp" name="no_hp" class="input-field" maxlength="15" required>
            </div>
            <div class="form-group">
                <label for="email" class="label">Email (Opsional)</label>
                <input type="email" id="email" name="email" class="input-field">
            </div>
            <div class="form-group">
                <label for="password_anggota" class="label">Password</label>
                <input type="password" id="password_anggota" name="password_anggota" class="input-field" required>
            </div>
            <div class="form-group">
                <label for="confirm_password" class="label">Konfirmasi Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="input-field" required>
            </div>
            <button type="submit" class="btn-primary">
                <i class="fas fa-user-plus"></i> Daftar Sekarang
            </button>
        </form>
        <p class="login-link-container">Sudah punya akun? <a href="login_anggota.php" class="login-link">Login di sini</a></p>
    </div>

    <script>
        // JavaScript for Button Ripple Effect
        const registerButton = document.querySelector('.btn-primary');

        registerButton.addEventListener('click', function(e) {
            const x = e.clientX - e.target.getBoundingClientRect().left;
            const y = e.clientY - e.target.getBoundingClientRect().top;

            const ripple = document.createElement('span');
            ripple.classList.add('ripple');
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            this.appendChild(ripple);

            // Remove ripple after animation
            ripple.addEventListener('animationend', () => {
                ripple.remove();
            });
        });
    </script>
</body>
</html>