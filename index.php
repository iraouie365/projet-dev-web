<?php
session_start();
require_once __DIR__ . '/classes/User.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

  $user = User::findByEmail($email);
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;
        if ($user['role'] === 'demandeur') {
            header('Location: dashboard_demandeur.php');
        } elseif ($user['role'] === 'validateur') {
            header('Location: dashboard_validateur.php');
        } else {
            header('Location: dashboard_admin.php');
        }
        exit;
    } else {
        $error = "Email ou mot de passe incorrect";
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Connexion - Gestion des Besoins</title>
  <link rel="icon" type="image/svg+xml" href="assets/favicon.svg">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    body {
      background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      overflow: hidden;
    }
    
    /* Animated Background */
    .bg-animation {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 0;
      overflow: hidden;
    }
    
    .bg-animation span {
      position: absolute;
      display: block;
      width: 20px;
      height: 20px;
      background: rgba(102, 126, 234, 0.2);
      animation: float 25s linear infinite;
      bottom: -150px;
      border-radius: 50%;
    }
    
    .bg-animation span:nth-child(1) { left: 25%; width: 80px; height: 80px; animation-delay: 0s; }
    .bg-animation span:nth-child(2) { left: 10%; width: 20px; height: 20px; animation-delay: 2s; animation-duration: 12s; }
    .bg-animation span:nth-child(3) { left: 70%; width: 20px; height: 20px; animation-delay: 4s; }
    .bg-animation span:nth-child(4) { left: 40%; width: 60px; height: 60px; animation-delay: 0s; animation-duration: 18s; }
    .bg-animation span:nth-child(5) { left: 65%; width: 20px; height: 20px; animation-delay: 0s; }
    .bg-animation span:nth-child(6) { left: 75%; width: 110px; height: 110px; animation-delay: 3s; }
    .bg-animation span:nth-child(7) { left: 35%; width: 150px; height: 150px; animation-delay: 7s; }
    .bg-animation span:nth-child(8) { left: 50%; width: 25px; height: 25px; animation-delay: 15s; animation-duration: 45s; }
    .bg-animation span:nth-child(9) { left: 20%; width: 15px; height: 15px; animation-delay: 2s; animation-duration: 35s; }
    .bg-animation span:nth-child(10) { left: 85%; width: 150px; height: 150px; animation-delay: 0s; animation-duration: 11s; }
    
    @keyframes float {
      0% {
        transform: translateY(0) rotate(0deg);
        opacity: 1;
        border-radius: 50%;
      }
      100% {
        transform: translateY(-1000px) rotate(720deg);
        opacity: 0;
        border-radius: 50%;
      }
    }
    
    /* Login container */
    .login-container {
      width: 100%;
      max-width: 450px;
      padding: 20px;
      z-index: 1;
    }
    
    /* Login card */
    .login-card {
      background: rgba(255, 255, 255, 0.95);
      border-radius: 24px;
      box-shadow: 
        0 25px 50px rgba(0, 0, 0, 0.5),
        0 0 0 1px rgba(255, 255, 255, 0.1),
        inset 0 1px 0 rgba(255, 255, 255, 0.5);
      overflow: hidden;
      transition: box-shadow 0.3s ease;
      animation: cardEntrance 1s ease-out;
    }
    
    .login-card:hover {
      box-shadow: 
        0 35px 70px rgba(0, 0, 0, 0.6),
        0 0 30px rgba(102, 126, 234, 0.3),
        inset 0 1px 0 rgba(255, 255, 255, 0.5);
    }
    
    @keyframes cardEntrance {
      0% {
        opacity: 0;
        transform: translateY(-100px) scale(0.8);
      }
      50% {
        opacity: 1;
      }
      100% {
        transform: translateY(0) scale(1);
      }
    }
    
    /* Header */
    .login-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      padding: 45px 30px;
      text-align: center;
      color: white;
      position: relative;
      overflow: hidden;
    }
    
    .login-header::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: linear-gradient(
        45deg,
        transparent,
        rgba(255, 255, 255, 0.1),
        transparent
      );
      animation: shimmer 3s infinite;
    }
    
    @keyframes shimmer {
      0% { transform: translateX(-100%) rotate(45deg); }
      100% { transform: translateX(100%) rotate(45deg); }
    }
    
    .login-logo {
      width: 90px;
      height: 90px;
      background: rgba(255, 255, 255, 0.2);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 20px;
      font-size: 2.8rem;
      backdrop-filter: blur(10px);
      animation: logoFloat 3s ease-in-out infinite;
      box-shadow: 
        0 10px 30px rgba(0, 0, 0, 0.2),
        inset 0 -5px 20px rgba(0, 0, 0, 0.1);
    }
    
    @keyframes logoFloat {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-10px); }
    }
    
    .login-header h2 {
      margin: 0;
      font-size: 1.9rem;
      font-weight: 700;
      text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
    }
    
    .login-header p {
      margin: 12px 0 0;
      opacity: 0.9;
      font-size: 1rem;
    }
    
    /* Body */
    .login-body {
      padding: 40px 35px;
    }
    
    .form-floating {
      margin-bottom: 22px;
    }
    
    .form-floating > .form-control {
      border-radius: 14px;
      border: 2px solid #e9ecef;
      padding: 1rem 1rem 1rem 3rem;
      height: calc(3.5rem + 2px);
      transition: all 0.3s ease;
      background: rgba(255, 255, 255, 0.9);
    }
    
    .form-floating > .form-control:focus {
      border-color: #667eea;
      box-shadow: 
        0 0 0 0.2rem rgba(102, 126, 234, 0.15),
        0 10px 30px rgba(102, 126, 234, 0.2);
      transform: scale(1.02);
    }
    
    .form-floating > label {
      padding-left: 3rem;
      color: #6c757d;
    }
    
    .input-icon {
      position: absolute;
      left: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: #667eea;
      font-size: 1.3rem;
      z-index: 4;
      pointer-events: none;
      transition: all 0.3s ease;
    }
    
    .form-control:focus + label + .input-icon,
    .form-floating:focus-within .input-icon {
      transform: translateY(-50%) scale(1.2);
      color: #764ba2;
    }
    
    /* Button */
    .btn-login {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border: none;
      border-radius: 14px;
      padding: 16px;
      font-size: 1.15rem;
      font-weight: 600;
      color: white;
      width: 100%;
      transition: all 0.3s ease;
      margin-top: 15px;
      position: relative;
      overflow: hidden;
      box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
    }
    
    .btn-login::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(
        90deg,
        transparent,
        rgba(255, 255, 255, 0.3),
        transparent
      );
      transition: left 0.5s ease;
    }
    
    .btn-login:hover::before {
      left: 100%;
    }
    
    .btn-login:hover {
      transform: translateY(-4px) scale(1.02);
      box-shadow: 
        0 15px 35px rgba(102, 126, 234, 0.5),
        0 0 20px rgba(118, 75, 162, 0.3);
      background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
    }
    
    .btn-login:active {
      transform: translateY(0) scale(0.98);
    }
    
    /* Alert */
    .alert {
      border-radius: 14px;
      border: none;
      padding: 15px;
      margin-bottom: 25px;
      display: flex;
      align-items: center;
      gap: 10px;
      animation: shake 0.5s ease-in-out;
    }
    
    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      25% { transform: translateX(-10px); }
      75% { transform: translateX(10px); }
    }
    
    .alert-danger {
      background: linear-gradient(135deg, #ff6b6b 0%, #ee5a5a 100%);
      color: white;
      box-shadow: 0 5px 20px rgba(238, 90, 90, 0.3);
    }
    
    /* Floating elements decoration */
    .floating-shapes {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      pointer-events: none;
      z-index: 0;
    }
    
    .shape {
      position: absolute;
      border: 2px solid rgba(102, 126, 234, 0.3);
      border-radius: 50%;
      animation: pulse 4s ease-in-out infinite;
    }
    
    .shape:nth-child(1) { top: 10%; left: 10%; width: 100px; height: 100px; animation-delay: 0s; }
    .shape:nth-child(2) { top: 70%; left: 80%; width: 60px; height: 60px; animation-delay: 1s; }
    .shape:nth-child(3) { top: 30%; right: 10%; width: 80px; height: 80px; animation-delay: 2s; }
    
    @keyframes pulse {
      0%, 100% { transform: scale(1); opacity: 0.5; }
      50% { transform: scale(1.1); opacity: 1; }
    }
  </style>
</head>
<body>
<!-- Animated Background -->
<div class="bg-animation">
  <span></span><span></span><span></span><span></span><span></span>
  <span></span><span></span><span></span><span></span><span></span>
</div>

<!-- Floating Shapes -->
<div class="floating-shapes">
  <div class="shape"></div>
  <div class="shape"></div>
  <div class="shape"></div>
</div>

<div class="login-container">
  <div class="login-card">
    <div class="login-header">
      <div class="login-logo">
        <i class="bi bi-shield-lock-fill"></i>
      </div>
      <h2>Gestion des Besoins</h2>
      <p>Connectez-vous à votre compte</p>
    </div>
    <div class="login-body">
      <?php if ($error): ?>
        <div class="alert alert-danger">
          <i class="bi bi-exclamation-circle-fill"></i>
          <span><?php echo htmlspecialchars($error); ?></span>
        </div>
      <?php endif; ?>
      <form method="post">
        <div class="form-floating position-relative">
          <input type="email" name="email" class="form-control" id="floatingEmail" placeholder="nom@exemple.com" required autofocus>
          <label for="floatingEmail">Adresse email</label>
          <i class="bi bi-envelope-fill input-icon"></i>
        </div>
        <div class="form-floating position-relative">
          <input type="password" name="password" class="form-control" id="floatingPassword" placeholder="Mot de passe" required>
          <label for="floatingPassword">Mot de passe</label>
          <i class="bi bi-lock-fill input-icon"></i>
        </div>
        <button class="btn btn-login" type="submit">
          <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
        </button>
      </form>
    </div>
  </div>
</div>

</body>
</html>
