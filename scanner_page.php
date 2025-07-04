<?php
// scanner_page.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>QR Code Scanner - AATC Visitor Management</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
  <!-- QR Scanner Library -->
  <link rel="icon" href="assets/favicon.ico" type="image/x-icon">
  <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

  <style>
    body {
      background-color: #f8f9fa;
      font-family: 'Inter', sans-serif;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    .scanner-container {
      max-width: 600px;
      margin: auto;
      padding: 3rem 1rem;
      background-color: white;
      border-radius: 1.5rem;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
      margin-top: 5rem;
      text-align: center;
    }

    .scanner-title {
      color: #07AF8B;
      font-weight: 800;
      font-size: 1.75rem;
      margin-bottom: 1.5rem;
    }

    #reader {
      width: 100%;
      max-width: 400px;
      margin: 0 auto 1.5rem;
    }

    #result {
      font-size: 1rem;
      color: #212529;
      background-color: #e9f7ef;
      padding: 1rem;
      border-radius: 0.5rem;
      margin-top: 1rem;
      min-height: 60px;
      word-wrap: break-word;
    }

    .footer {
      text-align: center;
      margin-top: auto;
      padding: 1rem;
      font-size: 0.875rem;
      color: #6c757d;
    }

    .logo {
      width: 60px;
      margin-bottom: 1rem;
    }
    
    .visitor-info {
      margin-top: 1rem;
      padding: 1rem;
      background-color: #f8f9fa;
      border-radius: 0.5rem;
    }
  </style>
</head>
<body>
  <div class="scanner-container">
    <img src="assets/logo-green-yellow.png" alt="AATC Logo" class="logo">
    <div class="scanner-title">Scan Visitor QR Code</div>
    <div class="mb-3">
  <input type="text" id="manualCode" class="form-control" placeholder="Enter Unique Code Manually">
  <button id="manualSearchBtn" class="btn btn-success mt-2" style="background-color: #07AF8B; border-color: #07AF8B;">Search</button>
</div>

    <div id="reader"></div>
    <div id="result">Waiting for scan...</div>
    <div id="visitorInfo" class="visitor-info" style="display: none;"></div>
    <button id="checkinBtn" class="btn btn-success mt-3" style="display: none;">Check-In Visitor</button>
  </div>

  <!-- Commented out because it is not yet functional
  <div class="mt-3 text-center">
    <button id="notifyBtn" class="btn btn-primary" style="display: none;">Notify Host</button>
  </div> -->

  <div class="footer">
    &copy; <?php echo date("Y"); ?> AATC Visitor Management System
  </div>

  <script>
      // Paste the JavaScript code here
      function notifyEmployee() {
        if (!currentVisitorData) return;
        
        const notifyBtn = document.getElementById("notifyBtn");
        notifyBtn.disabled = true;
        notifyBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...';
        
        fetch("notify_employee.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                visitor_id: currentVisitorData.visitor_id,
                employee_id: currentVisitorData.employee_id
            }),
        })
        .then(response => response.json())
        .then(data => {
            const resultDiv = document.getElementById("result");
            if (data.success) {
                notifyBtn.innerHTML = '<i class="bi bi-check-circle"></i> Notified';
                resultDiv.innerHTML += `
                    <div class="alert alert-success mt-3">
                        <i class="bi bi-envelope-check"></i> 
                        Notification sent to ${data.employee} (${data.email})
                    </div>`;
            } else {
                notifyBtn.disabled = false;
                notifyBtn.textContent = "Notify Again";
                resultDiv.innerHTML += `
                    <div class="alert alert-danger mt-3">
                        <i class="bi bi-envelope-x"></i> 
                        ${data.message}
                    </div>`;
            }
        })
        .catch(error => {
            console.error("Error:", error);
            notifyBtn.disabled = false;
            notifyBtn.textContent = "Notify Employee";
            document.getElementById("result").innerHTML += `
                <div class="alert alert-warning mt-3">
                    <i class="bi bi-exclamation-triangle"></i> 
                    Connection error - please try again
                </div>`;
        });
    }
    let currentVisitorData = null;
    
    
    
 document.getElementById("manualSearchBtn").addEventListener("click", function () {
  const code = document.getElementById("manualCode").value.trim(); // <-- fixed here

  if (!code) {
    alert("Please enter a valid code.");
    return;
  }

  document.getElementById("result").innerHTML = "Searching...";

  fetch("search_code.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded"
    },
    body: "code=" + encodeURIComponent(code)
  })
  .then(response => response.json())
  .then(data => {
    if (data.status === "FOUND") {
      const visitor = data.visitor;
      currentVisitorData = visitor; // don't forget to set this so checkin/notify works
      displayVisitorInfo(visitor);
      document.getElementById("checkinBtn").style.display = "inline-block";
      document.getElementById("notifyBtn").style.display = "inline-block";
    } else {
      document.getElementById("result").innerHTML = data.message;
      document.getElementById("visitorInfo").style.display = "none";
      document.getElementById("checkinBtn").style.display = "none";
      document.getElementById("notifyBtn").style.display = "none";
    }
  })
  .catch(err => {
    console.error(err);
    document.getElementById("result").innerHTML = "Done!";
  });
});


    

    function onScanSuccess(decodedText, decodedResult) {
      // Stop scanning after successful scan
      html5QrcodeScanner.clear().then(() => {
        console.log("QR Scanner stopped");
      }).catch(err => {
        console.error("Failed to stop scanner", err);
      });

      document.getElementById("result").innerHTML = "Processing QR code...";
      
      fetch("verify_qr.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: "qr_data=" + encodeURIComponent(decodedText),
      })
      .then(response => response.json())
      .then(data => {
        if (data.status === "FOUND") {
          currentVisitorData = data;
          displayVisitorInfo(data);
          document.getElementById("checkinBtn").style.display = "inline-block";
          document.getElementById("notifyBtn").style.display = "inline-block";
        } else {
          document.getElementById("result").innerHTML = data.message;
          document.getElementById("visitorInfo").style.display = "none";
          document.getElementById("checkinBtn").style.display = "none";
          document.getElementById("notifyBtn").style.display = "none";
        }
      })
      .catch(error => {
        console.error("Error:", error);
        document.getElementById("result").innerHTML = "Done!";
      });
    }

    function displayVisitorInfo(data) {
      const visitorInfoDiv = document.getElementById("visitorInfo");
      visitorInfoDiv.style.display = "block";
      visitorInfoDiv.innerHTML = `
        <h5>Visitor Information</h5>
        <p><strong>Name:</strong> ${data.visitor_name}</p>
        <p><strong>Company:</strong> ${data.company}</p>
        <p><strong>Host:</strong> ${data.host_name}</p>
        <p><strong>Purpose:</strong> ${data.purpose}</p>
      `;
      document.getElementById("result").innerHTML = "Visitor verified successfully!";
    }

    function checkInVisitor() {
      if (!currentVisitorData) return;
      
      fetch("checkin_visitor.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          visitor_id: currentVisitorData.visitor_id,
          qr_data: currentVisitorData.qr_data
        }),
      })
      .then(response => response.json())
      .then(data => {
        alert(data.message);
        if (data.success) {
          document.getElementById("checkinBtn").style.display = "none";
        }
      })
      .catch(error => {
        console.error("Error:", error);
        alert("");
      });
    }

    function notifyHost() {
      if (!currentVisitorData) return;
      
      fetch("notify_host.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          visitor_id: currentVisitorData.visitor_id,
          host_id: currentVisitorData.host_id
        }),
      })
      .then(response => response.json())
      .then(data => {
        alert(data.message);
        if (data.success) {
          document.getElementById("notifyBtn").disabled = true;
          document.getElementById("notifyBtn").textContent = "Host Notified";
        }
      })
      .catch(error => {
        console.error("Error:", error);
        alert("Error notifying host");
      });
    }

    const html5QrcodeScanner = new Html5QrcodeScanner("reader", {
      fps: 10,
      qrbox: 250
    });

    html5QrcodeScanner.render(onScanSuccess);
    
    // Add event listeners
    document.getElementById("checkinBtn").addEventListener("click", checkInVisitor);
    document.getElementById("notifyBtn").addEventListener("click", notifyHost);
    
   document.getElementById("manualSearchBtn").addEventListener("click", handleManualSearch);

  </script>
</body>
</html>