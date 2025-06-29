<?php
session_start();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Anggota - Perpustakaan</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* --- General Body and Layout --- */
        body {
            font-family: 'Inter', sans-serif;
            background: #9295d0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1.5rem;
            overflow-y: auto;
            box-sizing: border-box;
        }

        /* --- Glassmorphism Card --- */
        .card-login {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 2.5rem;
            box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.35), 0 20px 40px -10px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(30px) saturate(1.8);
            border: 2px solid rgba(255, 255, 255, 0.7);
            padding: 3.5rem;
            text-align: center;
            max-width: 500px;
            width: 100%;
            animation: fadeInScale 1.2s cubic-bezier(0.23, 1, 0.32, 1) forwards;
            transform: scale(0.95) translateY(30px);
            opacity: 0;
            position: relative;
            overflow: hidden;
            margin-top: auto; /* To center vertically in a flex container */
            margin-bottom: auto; /* To center vertically in a flex container */
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

        /* --- Input Fields --- */
        .form-group {
            margin-bottom: 1.5rem; /* Equivalent to space-y-6, applied to parent */
        }
        .label {
            display: block;
            text-align: left;
            font-size: 0.875rem; /* Equivalent to text-sm */
            font-weight: 500; /* Equivalent to font-medium */
            color: #4B5563; /* Equivalent to text-gray-700 */
            margin-bottom: 0.25rem; /* Equivalent to mb-1 */
        }
        .input-field {
            width: 100%;
            max-width: 450px; /* Tambahkan ini agar tidak terlalu lebar */
    margin: 0 auto;   /* Tengahin input */
            padding: 1.1rem 1.5rem;
            border-radius: 0.75rem;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            font-size: 1.1rem;
            outline: none;
            transition: all 0.3s ease;
            color: #1F2937;
        }

        form {
    max-width: 500px;
    margin: 0 auto;
}

        .input-field:focus {
            box-shadow: 0 4px 15px rgba(0,0,0,0.15), 0 0 0 4px rgba(255, 111, 145, 0.3);
        }
        .input-field::placeholder {
            color: #6B7280;
            opacity: 0.8;
        }

        /* --- Primary Button --- */
        .btn-primary {
            background-color: #50698d;
            color: white;
            width: 100%; /* Full width */
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
            margin-top: 1.5rem; /* Add margin top to separate from inputs */
        }
        .btn-primary:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            background-color: #50698d;
        }
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
        .btn-primary i {
            margin-right: 0.5rem; /* Equivalent to mr-2 */
        }

        /* --- Back Button --- */
        .back-button-container {
            display: flex;
            justify-content: flex-start; /* Equivalent to justify-start */
            margin-bottom: 1.5rem; /* Equivalent to mb-6 */
        }
        .back-button {
            color: #4B5563;
            text-decoration: none;
            display: flex;
            align-items: center; /* Equivalent to items-center */
            transition: all 0.2s ease;
            font-size: 1.1rem;
            font-weight: 500;
        }
        .back-button:hover {
            color: #1F2937;
            transform: translateX(-5px);
        }
        .back-button i {
            margin-right: 0.5rem; /* Equivalent to mr-2 */
        }

        /* --- Register Link --- */
        .register-text {
            text-align: center;
            color: #4B5563; /* Equivalent to text-gray-600 */
            font-size: 0.875rem; /* Equivalent to text-sm */
            margin-top: 1.5rem; /* Equivalent to mt-6 */
        }
        .register-link {
            color: #4DB6AC;
            font-weight: 700;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        .register-link:hover {
            color: #009688;
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
            background-color: #FEE2E2;
            border: 1px solid #FCA5A5;
            color: #991B1B;
        }
        .message-box.success {
            background-color: #D1FAE5;
            border: 1px solid #A7F3D0;
            color: #065F46;
        }

        /* --- Responsive Adjustments --- */
        @media (max-width: 640px) {
            .card-login {
                padding: 2rem;
                border-radius: 2rem;
            }
            .card-title {
                font-size: 2.5rem;
            }
            .card-subtitle {
                font-size: 1rem;
            }
            .input-field {
                padding: 0.9rem 1.2rem;
                font-size: 1rem;
            }
            .btn-primary {
                padding: 1.1rem 2rem;
                font-size: 1.1rem;
            }
        }
    </style>
</head>
<body>
    <div class="card-login">
        <div class="back-button-container">
            <a href="index.php" class="back-button">
                <i class="fas fa-arrow-left"></i> Kembali ke Beranda
            </a>
        </div>
        <h2 class="card-title">Login Anggota</h2>
        <p class="card-subtitle">Masuk untuk melihat riwayat peminjaman Anda</p>

        <?php
        // Menampilkan pesan error jika ada dari proses_login_anggota.php
        if (isset($_SESSION['login_anggota_error'])) {
            echo '<div class="message-box error">' . htmlspecialchars($_SESSION['login_anggota_error']) . '</div>';
            unset($_SESSION['login_anggota_error']);
        }
        // Menampilkan pesan sukses pendaftaran
        if (isset($_SESSION['register_anggota_success'])) {
            echo '<div class="message-box success">' . htmlspecialchars($_SESSION['register_anggota_success']) . '</div>';
            unset($_SESSION['register_anggota_success']);
        }
        ?>

        <form action="proses_login_anggota.php" method="POST" class="form-login">
            <div class="form-group">
                <label for="nik" class="label">NIK (Nomor Induk Kependudukan)</label>
                <input type="text" id="nik" name="nik" required
                       class="input-field"
                       placeholder="Masukkan NIK Anda">
            </div>
            <div class="form-group">
                <label for="password_anggota" class="label">Password</label>
                <input type="password" id="password_anggota" name="password_anggota" required
                       class="input-field"
                       placeholder="Masukkan Password Anda">
            </div>
            <button type="submit" class="btn-primary">
                <i class="fas fa-sign-in-alt"></i> Login Anggota
            </button>
        </form>
        <p class="register-text">Belum punya akun? <a href="register_anggota.php" class="register-link">Daftar di sini</a></p>
    </div>

    <script>
        // JavaScript for Button Ripple Effect
        const loginButton = document.querySelector('.btn-primary');

        loginButton.addEventListener('click', function(e) {
            // Check if the ripple element already exists and remove it
            const existingRipple = this.querySelector('.ripple');
            if (existingRipple) {
                existingRipple.remove();
            }

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