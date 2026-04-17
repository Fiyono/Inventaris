<?php
session_start();
include "koneksi.php";

/*
|--------------------------------------------------------------------------
| CEK JIKA SUDAH LOGIN
|--------------------------------------------------------------------------
*/
if(isset($_SESSION['id_user'])){
    header("Location:index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| AUTO LOGIN VIA USER AGENT
|--------------------------------------------------------------------------
*/
if(isset($_SERVER['HTTP_USER_AGENT'])){

    $agent = mysqli_real_escape_string($koneksi,$_SERVER['HTTP_USER_AGENT']);

    $cek = mysqli_query($koneksi,"
        SELECT ua.*, u.id_level
        FROM user_agent ua
        JOIN tb_user u ON u.id_user = ua.id_user
        WHERE ua.name_user_agent='$agent'
        LIMIT 1
    ");

    if(mysqli_num_rows($cek) > 0){
        $data = mysqli_fetch_assoc($cek);

        $_SESSION['id_user'] = $data['id_user'];
        $_SESSION['agent']   = $agent;

        header("Location:index.php");
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| PROSES LOGIN
|--------------------------------------------------------------------------
*/
$error = "";

if(isset($_POST['login'])){

    $user = mysqli_real_escape_string($koneksi,$_POST['user']);
    $pass = mysqli_real_escape_string($koneksi,$_POST['pass']);

    $query = mysqli_query($koneksi,"
        SELECT * FROM tb_user
        WHERE user='$user' AND pass='$pass'
        LIMIT 1
    ");

    if(mysqli_num_rows($query) > 0){

        $data = mysqli_fetch_assoc($query);
        $id_user = $data['id_user'];
        $agent   = $_SERVER['HTTP_USER_AGENT'];
        $date    = date('Y-m-d H:i:s');

        // hapus agent lama biar tidak numpuk
        mysqli_query($koneksi,"
            DELETE FROM user_agent WHERE id_user='$id_user'
        ");

        // simpan agent baru
        mysqli_query($koneksi,"
            INSERT INTO user_agent(id_user,tgl_login,name_user_agent)
            VALUES('$id_user','$date','$agent')
        ");

        // set session
        $_SESSION['id_user'] = $id_user;
        $_SESSION['agent']   = $agent;

        // redirect sesuai level
        if($data['id_level']=='admin'){
            header("Location:admin.php");
        }else{
            header("Location:anggota.php");
        }
        exit;

    }else{
        $error = "Username atau Password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Inventaris - Login</title>
    
    <link rel="icon" href="dist/img/logoinventaris.jpg">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Font Awesome 6 (lebih modern) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Google Fonts - Inter (font modern) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 (lebih modern) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Animate.css untuk animasi -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }
        
        body {
            background: url('dist/img/background.png') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        body::after {
            content: "";
            position: absolute;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.35);
            top: 0;
            left: 0;
            z-index: 1;
        }


        .password-box{
            position: relative;
        }

        .password-box input{
            padding-right: 40px; /* ruang untuk ikon */
        }

        .eye-icon{
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            font-size: 16px;
        }

.login-wrapper {
    position: relative;
    z-index: 2;
}
        
        
        @keyframes moveBg {
            0% { transform: translate(-10%, -10%) rotate(0deg); }
            100% { transform: translate(10%, 10%) rotate(10deg); }
        }
        
        
        
        .login-wrapper {
            width: 100%;
            max-width: 420px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
            animation: fadeInUp 0.8s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-card {
            background: rgba(10, 10, 10, 0.92);
            backdrop-filter: blur(15px);
            border-radius: 24px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.8);
            overflow: hidden;
            border: 1px solid rgba(212, 175, 55, 0.4);
        }
        
        .login-header {
            padding: 40px 40px 20px;
            text-align: center;
            background: linear-gradient(135deg, #000000, #1a1a1a);
            color: #d4af37;
            border-bottom: 1px solid rgba(212, 175, 55, 0.3);
        }

        .login-header h1 {
            font-size: 30px;
            font-weight: 700;
            letter-spacing: 1px;
            color: #d4af37;
        }

        .login-header p {
            color: #c9b037;
            opacity: 0.9;
        }

        .login-header i {
            font-size: 50px;
            color: #d4af37;
        }
        
        .login-body {
            padding: 40px;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-label {
            font-weight: 500;
            font-size: 14px;
            color: #4a5568;
            margin-bottom: 8px;
            display: block;
        }
        
        .input-group {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .input-icon {
            position: absolute;
            left: 16px;
            color: #a0aec0;
            font-size: 18px;
            z-index: 10;
        }
        
        .form-control {
            background: #111;
            border: 1px solid rgba(212, 175, 55, 0.3);
            color: #fff;
        }

        .form-control:focus {
            border-color: #d4af37;
            box-shadow: 0 0 10px rgba(212, 175, 55, 0.4);
        }

        .form-label {
            color: #d4af37;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #d4af37, #b8860b);
            color: black;
            font-weight: 700;
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(212, 175, 55, 0.4);
            transition: 0.3s;
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #ffd700, #c9a227);
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(212, 175, 55, 0.6);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .btn-login i {
            margin-right: 8px;
        }
        
        .alert {
            padding: 16px 20px;
            border-radius: 16px;
            margin-bottom: 24px;
            border: none;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: shake 0.5s ease;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        .alert-danger {
            background: #fff5f5;
            color: #c53030;
            border-left: 4px solid #f56565;
        }
        
        .alert i {
            font-size: 20px;
        }
        
        .alert .message {
            flex: 1;
        }
        
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 20px 0;
            font-size: 14px;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #4a5568;
            cursor: pointer;
        }
        
        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #667eea;
        }
        
        .forgot-password {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .forgot-password:hover {
            color: #764ba2;
            text-decoration: underline;
        }
        
        .login-footer {
            text-align: center;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #e2e8f0;
            color: #718096;
            font-size: 13px;
        }
        
        .login-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        
        .login-footer a:hover {
            text-decoration: underline;
        }
        
        /* Loading Spinner */
        #loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            transition: opacity 0.5s;
        }
        
        .loader-spinner {
            width: 60px;
            height: 60px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Responsive */
        @media (max-width: 480px) {
            .login-body {
                padding: 30px 20px;
            }
            
            .login-header {
                padding: 30px 20px 15px;
            }
            
            .login-header h1 {
                font-size: 28px;
            }
            
            .form-control {
                padding: 12px 16px 12px 44px;
            }
        }
        
        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            body {
                background: linear-gradient(135deg, #1a202c 0%, #2d3748 100%);
            }
            
            .login-card {
                background: rgba(26, 32, 44, 0.95);
            }
            
            .form-label {
                color: #e2e8f0;
            }
            
            .form-control {
                background: #2d3748;
                border-color: #4a5568;
                color: #f7fafc;
            }
            
            .form-control:focus {
                border-color: #667eea;
            }
            
            .remember-me {
                color: #e2e8f0;
            }
            
            .login-footer {
                border-top-color: #4a5568;
                color: #a0aec0;
            }
        }
    </style>
</head>
<body>

    <!-- Floating Shapes -->
    <div class="floating-shape shape1 animate__animated animate__fadeIn"></div>
    <div class="floating-shape shape2 animate__animated animate__fadeIn animate__delay-1s"></div>

    <!-- Loading Spinner -->
    <div id="loader">
        <div class="loader-spinner"></div>
    </div>

    <!-- Login Wrapper -->
    <div class="login-wrapper">
        <div class="login-card">
            
            <!-- Header dengan icon -->
            <div class="login-header">
                <i class="fas fa-cubes animate__animated animate__bounceIn"></i>
                <h1 class="animate__animated animate__fadeInDown">APLIKASI INVENTARIS</h1>
                <p class="animate__animated animate__fadeInUp animate__delay-0.5s">Masuk ke dashboard inventaris PPLG</p>
            </div>
            
            <!-- Body -->
            <div class="login-body">
                
                <?php if($error != ""): ?>
                <div class="alert alert-danger animate__animated animate__headShake">
                    <i class="fas fa-exclamation-circle"></i>
                    <span class="message"><?= $error ?></span>
                </div>
                <?php endif; ?>
                
                <form method="post" class="animate__animated animate__fadeIn animate__delay-0.5s">
                    
                    <!-- Username Field -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="far fa-user-circle"></i> Username
                        </label>
                        <div class="input-group">
                            <input type="text" 
                                   name="user" 
                                   class="form-control" 
                                   placeholder="Masukkan username" 
                                   required 
                                   autofocus>
                        </div>
                    </div>
                    
                    <!-- Password Field -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-lock"></i> Password
                        </label>

                        <div class="password-box">
                            <input type="password" 
                                name="pass" 
                                id="password"
                                class="form-control"
                                placeholder="Masukkan password"
                                required>

                            <i class="fas fa-eye eye-icon" id="eyeIcon" onclick="togglePassword()"></i>
                        </div>
                    </div>
                    
                    <!-- Login Button -->
                    <button type="submit" name="login" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i>
                        Sign In
                    </button>
                    
                </form>
                
                <!-- Footer -->
                <div class="login-footer">
                    <p>&copy; <?= date('Y') ?> Aplikasi Inventaris PPLG. All rights reserved.</p>
                    <p style="margin-top: 8px;">
                        <i class="fas fa-shield-alt"></i> 
                        <span>Aman & Terpercaya</span>
                    </p>
                </div>
                
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Loading screen
        function myFunction() {
            setTimeout(function() {
                document.getElementById("loader").style.opacity = "0";
                setTimeout(function() {
                    document.getElementById("loader").style.display = "none";
                }, 500);
            }, 800);
        }
        
        // Panggil fungsi loading
        myFunction();
        
        // Tambahkan efek focus pada input
        $(document).ready(function() {
            $('.form-control').on('focus', function() {
                $(this).parent().find('.input-icon').css('color', '#667eea');
            }).on('blur', function() {
                $(this).parent().find('.input-icon').css('color', '#a0aec0');
            });
        });

       
        function togglePassword() {
            var pass = document.getElementById("password");
            var icon = document.getElementById("eyeIcon");

            if (pass.type === "password") {
                pass.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                pass.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        }
    </script>

</body>
</html>