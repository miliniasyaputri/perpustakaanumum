<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi Perpustakaan Umum</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Body and main container styles */
        body {
            font-family: 'Inter', sans-serif;
            background: #9295d0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1.5rem;
            overflow: hidden;
        }
        .welcome-card {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 1.5rem;
            box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.2), 0 10px 20px -5px rgba(0, 0, 0, 0.08);
            backdrop-filter: blur(10px);
            padding: 2.5rem;
            text-align: center;
            max-width: 600px;
            width: 100%;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .welcome-card:hover {
            transform: scale(1.05);
            box-shadow: 0 30px 60px -10px rgba(0, 0, 0, 0.3);
        }

        /* Title and subtitle styles */
        .main-title {
            font-size: 3.5rem;
            font-weight: 700;
            color: black;
            margin-bottom: 1.5rem;
            line-height: 1.2;
            animation: fadeIn 1s ease;
        }
        .subtitle {
            font-size: 1.25rem;
            color: black;
            margin-bottom: 2.5rem;
            animation: fadeIn 1.5s ease;
        }

        /* Logo image styles */
        .logo-img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 9999px; /* Equivalent to rounded-full */
            margin-bottom: 1.5rem; /* Equivalent to mb-6 */
            margin-left: auto;
            margin-right: auto; /* Equivalent to mx-auto */
            display: block; /* Ensures margin auto works */
        }

        /* Button group styles */
        .button-group {
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 1rem; /* Equivalent to gap-4 */
        }
        @media (min-width: 640px) {
            .button-group {
                flex-direction: row;
                flex-wrap: wrap;
                gap: 1rem;
            }
        }

        /* Action button styles */
        .action-button {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem 2rem;
            border-radius: 0.75rem;
            font-size: 1.125rem;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
            text-decoration: none; /* Remove underline from links */
            color: white;
            z-index: 1; /* Ensure text is above pseudo-element */
        }
        .action-button i {
            margin-right: 0.75rem; /* Equivalent to mr-3 */
        }
        .action-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 0.75rem;
            transform: scale(0);
            transition: transform 0.3s ease;
            z-index: -1; /* Behind the text */
        }
        .action-button:hover::before {
            transform: scale(1);
        }
        .action-button:hover {
            color: #fff;
        }

        /* Specific button colors */
        .btn-katalog {
            background-color: #50698d;
        }
        .btn-katalog:hover {
            background-color: #50698d;
        }
        .btn-member-login {
            background-color: #ceb5d4;
        }
        .btn-member-login:hover {
            background-color: #ceb5d4;
        }
        .btn-register {
            background-color: #7d9fc0;
        }
        .btn-register:hover {
            background-color: #7d9fc0;
        }
        .btn-login {
            background-color: #4e7ab1;
        }
        .btn-login:hover {
            background-color: #4e7ab1;
        }

        /* Fade-in animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive adjustments */
        @media (max-width: 640px) {
            .main-title {
                font-size: 2.5rem;
            }
            .subtitle {
                font-size: 1rem;
            }
            .welcome-card {
                padding: 1.5rem;
            }
            .button-group {
                flex-direction: column;
                gap: 1rem;
            }
            .action-button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="welcome-card">
        <img src="uploads/logo1.png" alt="Library Logo" class="logo-img">
        <h1 class="main-title">Selamat Datang di The Pixel Library!</h1>
        <p class="subtitle">Sistem Informasi Perpustakaan Digital</p>
        <div class="button-group">
            <a href="user_interface.php" class="action-button btn-katalog">
                <i class="fas fa-book"></i> Lihat Katalog Buku
            </a>
            <a href="login_anggota.php" class="action-button btn-member-login">
                <i class="fas fa-user"></i> Login Anggota
            </a>
            <a href="register_anggota.php" class="action-button btn-register">
                <i class="fas fa-user-plus"></i> Daftar Anggota Baru
            </a>
            <a href="login_page.php" class="action-button btn-login">
                <i class="fas fa-user-lock"></i> Login Petugas
            </a>
        </div>
    </div>
</body>
</html>