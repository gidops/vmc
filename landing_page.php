<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>AATC Visitor Management System</title>
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Raleway:wght@300;400;600&display=swap" rel="stylesheet"/>
  <link rel="icon" href="assets/favicon.ico" type="image/x-icon">
  
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body, html {
      height: 100%;
      font-family: 'Raleway', sans-serif;
      background-color: #f4f6f9;
    }

    .vip-bar {
      background-color: #004225;
      color: #f8f8f8;
      padding: 12px 0;
      font-size: 15px;
      font-weight: 500;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .vip-bar i {
      margin-right: 6px;
      color: #ffc107;
    }

    .hero {
      position: relative;
      height: calc(100vh - 48px); /* full height minus VIP bar */
      background: linear-gradient(135deg, #004225 0%, #007f5f 100%);
      color: white;
      display: flex;
      justify-content: center;
      align-items: center;
      text-align: center;
      padding: 40px 20px;
    }

    .hero .container {
      max-width: 700px;
      width: 100%;
    }

    .hero-logo img {
      width: 160px;
      margin-bottom: 35px;
    }

    .hero h1 {
      font-size: 40px;
      font-weight: 700;
      margin-bottom: 15px;
      font-family: 'Playfair Display', serif;
    }

    .hero-subtitle {
      font-size: 18px;
      font-weight: 300;
      margin-bottom: 40px;
      letter-spacing: 1px;
    }

    .btn {
      padding: 14px 30px;
      border: none;
      border-radius: 6px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }

    .btn-vip {
      background-color: #ffc107;
      color: #1e1e1e;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    }

    .btn-vip:hover {
      background-color: #e0aa00;
    }

    .btn i {
      margin-right: 8px;
    }

    @media (max-width: 600px) {
      .hero h1 {
        font-size: 28px;
      }

      .hero-subtitle {
        font-size: 16px;
      }

      .btn {
        padding: 12px 24px;
        font-size: 15px;
      }
    }
  </style>
</head>
<body>

  <!-- VIP Header Bar -->
  <div class="vip-bar">
    <span><i class="fas fa-building-shield"></i> By Pentagon Securities Ltd</span>
  </div>

  <!-- Hero Section -->
  <header class="hero">
    <div class="container">
      <div class="hero-logo">
        <img src="assets/logo-green-yellow.png" alt="AATC Logo" />
      </div>
      <h1>Abuja-AATC</h1>
      <h1>Visitor Management Portal</h1>
      <!-- <p class="hero-subtitle"> Visitor Management Portal</p> -->
      <a href="employee_login.php" class="btn btn-vip">
        <i class="fas fa-lock"></i> Login
      </a>
    </div>
  </header>

</body>
</html>
