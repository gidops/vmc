<?php
// gate_dashboard.php
session_start();

// Check if user is authorized (add your authentication logic here)
if (!isset($_SESSION['gate_operative'])) {
    header("Location: gate_login.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "aatcabuj_admin", "Sgt.pro@501", "aatcabuj_visitors_version_2");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gate Scanner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="assets/favicon.ico" type="image/x-icon">
    <style>
        .scanner-container {
            max-width: 600px;
            margin: 0 auto;
            position: relative;
        }
        #qr-video {
            width: 100%;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .scan-overlay {
            position: absolute;
            width: 80%;
            height: 200px;
            border: 4px solid #fff;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            box-shadow: 0 0 0 100vmax rgba(0,0,0,0.5);
        }
        .status-card {
            display: none;
        }
    </style>
</head>
<body class="bg-dark">
    <div class="container py-5">
        <div class="text-center mb-4">
            <h1 class="text-white mb-3"><i class="fas fa-qrcode"></i> Gate Scanner</h1>
            <button id="startButton" class="btn btn-lg btn-success">
                <i class="fas fa-video me-2"></i>Start Camera
            </button>
        </div>

        <div class="scanner-container">
            <video id="qr-video"></video>
            <div class="scan-overlay"></div>
        </div>

        <div id="statusCard" class="card status-card mt-4">
            <div class="card-body text-center">
                <h2 id="statusText" class="card-title"></h2>
                <p id="visitorDetails" class="card-text"></p>
            </div>
        </div>
    </div>

    <!-- Include jsQR library -->
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
    <script>
        const video = document.getElementById("qr-video");
        const startButton = document.getElementById("startButton");
        const statusCard = document.getElementById("statusCard");
        const statusText = document.getElementById("statusText");
        const visitorDetails = document.getElementById("visitorDetails");

        let scanning = false;

        startButton.addEventListener("click", () => {
            if (!scanning) {
                startCamera();
                startButton.innerHTML = '<i class="fas fa-stop me-2"></i>Stop Camera';
                scanning = true;
            } else {
                stopCamera();
                startButton.innerHTML = '<i class="fas fa-video me-2"></i>Start Camera';
                scanning = false;
            }
        });

        async function startCamera() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: "environment" }
                });
                video.srcObject = stream;
                video.play();
                requestAnimationFrame(tick);
            } catch (err) {
                alert("Error accessing camera: " + err);
            }
        }

        function stopCamera() {
            video.srcObject.getTracks().forEach(track => track.stop());
            statusCard.style.display = "none";
        }

        function tick() {
            if (video.readyState === video.HAVE_ENOUGH_DATA) {
                const canvas = document.createElement("canvas");
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                const ctx = canvas.getContext("2d");
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                
                const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                const code = jsQR(imageData.data, imageData.width, imageData.height);
                
                if (code) {
                    handleQRCode(code.data);
                }
            }
            requestAnimationFrame(tick);
        }

        function handleQRCode(data) {
            // Extract visitor ID from QR code data
            const visitorId = data.split('=')[1];
            
            fetch(`approve_visitor.php?id=${visitorId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        statusCard.style.display = "block";
                        statusCard.className = "card status-card mt-4 bg-success text-white";
                        statusText.innerHTML = '<i class="fas fa-check-circle me-2"></i>Approved!';
                        visitorDetails.innerHTML = `
                            Visitor: ${data.visitor.name}<br>
                            Host: ${data.visitor.host_name}<br>
                            Time: ${new Date().toLocaleTimeString()}
                        `;
                        
                        // Reset after 5 seconds
                        setTimeout(() => {
                            statusCard.style.display = "none";
                        }, 5000);
                    } else {
                        statusCard.style.display = "block";
                        statusCard.className = "card status-card mt-4 bg-danger text-white";
                        statusText.innerHTML = '<i class="fas fa-times-circle me-2"></i>Invalid QR Code';
                        visitorDetails.innerHTML = "";
                    }
                });
        }
    </script>
</body>
</html>