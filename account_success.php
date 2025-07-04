<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Account Created - AATC Visitor Management</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Bootstrap CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
  <link rel="icon" href="assets/favicon.ico" type="image/x-icon">

  <style>
    body {
      background-color: #f8f9fa;
      font-family: 'Inter', sans-serif;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
    }

    .success-box {
      background-color: white;
      border-radius: 2rem;
      box-shadow: 0 10px 25px rgba(67, 238, 160, 0.15); /* Changed blue shadow to greenish */
      padding: 3rem;
      text-align: center;
      max-width: 500px;
      width: 100%;
      animation: fadeIn 0.8s ease-in-out;
    }

    .success-icon {
      font-size: 3rem;
      color: #4ccf88; /* green */
      margin-bottom: 1rem;
    }

    h1 {
      color: #2a9d8f; /* green */
      font-weight: 800;
      font-size: 2rem;
    }

    p {
      color: #6c757d;
      margin-bottom: 2rem;
      font-size: 1rem;
    }

    .btn-custom {
      background-color: #2a9d8f; /* green */
      color: white;
      font-weight: 600;
      padding: 0.75rem 1.5rem;
      border-radius: 1.5rem;
      transition: 0.3s ease;
      text-decoration: none;
    }

    .btn-custom:hover {
      background-color: #21867a; /* darker green */
      color: white;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>

  <div class="success-box">
    <div class="success-icon">✅</div>
    <h1>Account Successfully Created</h1>
    <p>Your account has been created and is ready to use.</p>
    <a href="landing_page.php" class="btn btn-custom">Go to Dashboard</a>
  </div>

</body>
</html>
