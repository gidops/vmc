<?php
session_start();
require 'db_connection.php';

// Redirect if not logged in
if (!isset($_SESSION['employee_id'])) {
    header("Location: index.html");
    exit();
}

// Check if profile is already completed (optional, depending on your flow)
$employee_id = $_SESSION['employee_id'];
$stmt = $conn->prepare("SELECT profile_completed FROM employees WHERE id = ?");
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$stmt->bind_result($profile_completed);
$stmt->fetch();
$stmt->close();

// Get current user data
$stmt = $conn->prepare("SELECT * FROM employees WHERE id = ?");
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Handle form submission
$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $country_code = $conn->real_escape_string($_POST['country_code']);
    $designation = $conn->real_escape_string($_POST['designation']);
    $organization = $conn->real_escape_string($_POST['organization']);
    
    // Password change logic
    $password_update = '';
    if (!empty($_POST['new_password'])) {
        if ($_POST['new_password'] !== $_POST['confirm_password']) {
            $error = "New passwords don't match";
        } else {
            $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            $password_update = ", password = ?";
        }
    }

    if (empty($error)) {
        // Prepare the query based on whether password is being updated
        $query = "UPDATE employees SET name = ?, email = ?, phone = ?, country_code = ?, designation = ?, organization = ?, profile_completed = 1";
        $query .= $password_update;
        $query .= " WHERE id = ?";
        
        $update_stmt = $conn->prepare($query);
        
        // Bind parameters based on whether password is being updated
        if (!empty($password_update)) {
            $update_stmt->bind_param("sssssssi", $name, $email, $phone, $country_code, $designation, $organization, $new_password, $employee_id);
        } else {
            $update_stmt->bind_param("ssssssi", $name, $email, $phone, $country_code, $designation, $organization, $employee_id);
        }
        
        if ($update_stmt->execute()) {
            $_SESSION['name'] = $name;
            $success = "Profile updated successfully!";
            header("Refresh: 2; url=staff_dashboard.php");
        } else {
            $error = "Error updating profile: " . $conn->error;
        }
        $update_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Complete Your Profile</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="icon" href="assets/favicon.ico" type="image/x-icon">
  <style>
    :root {
      --primary-green: #004225;
      --accent-green: #007f5f;
      --yellow: #ffc107;
      --bg-light: #f4f6f9;
      --error-red: #dc3545;
      --success-green: #28a745;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', sans-serif;
      background-color: var(--bg-light);
      padding: 2rem;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
    }

    .profile-container {
      width: 100%;
      max-width: 500px;
      background: white;
      border-radius: 12px;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
      padding: 2rem;
    }

    .logo {
      text-align: center;
      margin-bottom: 1.5rem;
    }

    .logo img {
      height: 60px;
    }

    h1 {
      color: var(--primary-green);
      text-align: center;
      margin-bottom: 1.5rem;
      font-size: 1.5rem;
    }

    .form-group {
      margin-bottom: 1.25rem;
      position: relative;
    }

    label {
      display: block;
      margin-bottom: 0.5rem;
      color: #444;
      font-weight: 500;
      font-size: 0.9rem;
    }

    input, select {
      width: 100%;
      padding: 0.75rem;
      border: 1px solid #ddd;
      border-radius: 8px;
      font-size: 1rem;
    }

    input:focus, select:focus {
      outline: none;
      border-color: var(--accent-green);
      box-shadow: 0 0 0 3px rgba(0, 127, 95, 0.15);
    }

    .btn {
      background-color: var(--accent-green);
      color: white;
      border: none;
      padding: 0.75rem;
      border-radius: 8px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      width: 100%;
      transition: background 0.3s ease;
      margin-top: 0.5rem;
    }

    .btn:hover {
      background-color: var(--primary-green);
    }

    .error-message {
      color: var(--error-red);
      font-size: 0.875rem;
      margin-top: 0.25rem;
      display: block;
    }

    .success-message {
      color: var(--success-green);
      font-size: 0.875rem;
      margin-top: 0.25rem;
      display: block;
      text-align: center;
      margin-bottom: 1rem;
    }

    .required:after {
      content: " *";
      color: var(--error-red);
    }

    .password-toggle {
      position: absolute;
      right: 10px;
      top: 38px;
      cursor: pointer;
      color: #777;
    }

    .section-title {
      font-size: 1.1rem;
      color: var(--primary-green);
      margin: 1.5rem 0 1rem;
      padding-bottom: 0.5rem;
      border-bottom: 1px solid #eee;
    }

    .phone-input-group {
      display: flex;
      gap: 0.5rem;
    }

    .country-code-select {
      width: 120px;
    }

    .phone-number-input {
      flex: 1;
    }
  </style>
</head>
<body>
  <div class="profile-container">
    <div class="logo">
      <a href="staff_dashboard.php"><img src="assets/logo-green-yellow.png" alt="Company Logo"></a>
    </div>
    
    <h1>Update Your Profile</h1>
    <p style="text-align: center; margin-bottom: 1.5rem; color: #666;">Please complete your profile information to continue</p>
    
    <?php if (!empty($error)): ?>
      <div class="error-message" style="text-align: center; margin-bottom: 1rem;"><?= $error ?></div>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
      <div class="success-message"><?= $success ?></div>
    <?php endif; ?>
    
    <form method="POST" action="update_profile.php">
      <div class="section-title">Basic Information</div>
      
      <div class="form-group">
        <label for="name" class="required">Full Name</label>
        <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
      </div>
      
      <div class="form-group">
        <label for="organization" class="required">Organization</label>
        <input type="text" id="organization" name="organization" value="<?= htmlspecialchars($user['organization'] ?? '') ?>" required>
      </div>
      
      <div class="form-group">
        <label for="designation" class="required">Designation</label>
        <input type="text" id="designation" name="designation" value="<?= htmlspecialchars($user['designation'] ?? '') ?>" required>
      </div>
      
      <div class="form-group">
        <label for="email" class="required">Email</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required readonly style="background-color: #f4f6f9;">
      </div>
      
      <div class="form-group">
        <label for="phone" class="required">Phone Number</label>
        <div class="phone-input-group">
          <select id="country_code" name="country_code" class="country-code-select" required>
            <option value="+234" selected>🇳🇬 Nigeria (+234)</option>
  <option value="+93">🇦🇫 Afghanistan (+93)</option>
  <option value="+355">🇦🇱 Albania (+355)</option>
  <option value="+213">🇩🇿 Algeria (+213)</option>
  <option value="+1-684">🇦🇸 American Samoa (+1-684)</option>
  <option value="+376">🇦🇩 Andorra (+376)</option>
  <option value="+244">🇦🇴 Angola (+244)</option>
  <option value="+1-264">🇦🇮 Anguilla (+1-264)</option>
  <option value="+672">🇦🇶 Antarctica (+672)</option>
  <option value="+1-268">🇦🇬 Antigua and Barbuda (+1-268)</option>
  <option value="+54">🇦🇷 Argentina (+54)</option>
  <option value="+374">🇦🇲 Armenia (+374)</option>
  <option value="+297">🇦🇼 Aruba (+297)</option>
  <option value="+61">🇦🇺 Australia (+61)</option>
  <option value="+43">🇦🇹 Austria (+43)</option>
  <option value="+994">🇦🇿 Azerbaijan (+994)</option>
  <option value="+1-242">🇧🇸 Bahamas (+1-242)</option>
  <option value="+973">🇧🇭 Bahrain (+973)</option>
  <option value="+880">🇧🇩 Bangladesh (+880)</option>
  <option value="+1-246">🇧🇧 Barbados (+1-246)</option>
  <option value="+375">🇧🇾 Belarus (+375)</option>
  <option value="+32">🇧🇪 Belgium (+32)</option>
  <option value="+501">🇧🇿 Belize (+501)</option>
  <option value="+229">🇧🇯 Benin (+229)</option>
  <option value="+1-441">🇧🇲 Bermuda (+1-441)</option>
  <option value="+975">🇧🇹 Bhutan (+975)</option>
  <option value="+591">🇧🇴 Bolivia (+591)</option>
  <option value="+387">🇧🇦 Bosnia and Herzegovina (+387)</option>
  <option value="+267">🇧🇼 Botswana (+267)</option>
  <option value="+55">🇧🇷 Brazil (+55)</option>
  <option value="+246">🇮🇴 British Indian Ocean Territory (+246)</option>
  <option value="+1-284">🇻🇬 British Virgin Islands (+1-284)</option>
  <option value="+673">🇧🇳 Brunei (+673)</option>
  <option value="+359">🇧🇬 Bulgaria (+359)</option>
  <option value="+226">🇧🇫 Burkina Faso (+226)</option>
  <option value="+257">🇧🇮 Burundi (+257)</option>
  <option value="+855">🇰🇭 Cambodia (+855)</option>
  <option value="+237">🇨🇲 Cameroon (+237)</option>
  <option value="+1">🇨🇦 Canada (+1)</option>
  <option value="+238">🇨🇻 Cape Verde (+238)</option>
  <option value="+1-345">🇰🇾 Cayman Islands (+1-345)</option>
  <option value="+236">🇨🇫 Central African Republic (+236)</option>
  <option value="+235">🇹🇩 Chad (+235)</option>
  <option value="+56">🇨🇱 Chile (+56)</option>
  <option value="+86">🇨🇳 China (+86)</option>
  <option value="+61">🇨🇽 Christmas Island (+61)</option>
  <option value="+61">🇨🇨 Cocos Islands (+61)</option>
  <option value="+57">🇨🇴 Colombia (+57)</option>
  <option value="+269">🇰🇲 Comoros (+269)</option>
  <option value="+682">🇨🇰 Cook Islands (+682)</option>
  <option value="+506">🇨🇷 Costa Rica (+506)</option>
  <option value="+385">🇭🇷 Croatia (+385)</option>
  <option value="+53">🇨🇺 Cuba (+53)</option>
  <option value="+357">🇨🇾 Cyprus (+357)</option>
  <option value="+420">🇨🇿 Czech Republic (+420)</option>
  <option value="+243">🇨🇩 DR Congo (+243)</option>
  <option value="+45">🇩🇰 Denmark (+45)</option>
  <option value="+253">🇩🇯 Djibouti (+253)</option>
  <option value="+1-767">🇩🇲 Dominica (+1-767)</option>
  <option value="+1-809">🇩🇴 Dominican Republic (+1-809)</option>
  <option value="+593">🇪🇨 Ecuador (+593)</option>
  <option value="+20">🇪🇬 Egypt (+20)</option>
  <option value="+503">🇸🇻 El Salvador (+503)</option>
  <option value="+240">🇬🇶 Equatorial Guinea (+240)</option>
  <option value="+291">🇪🇷 Eritrea (+291)</option>
  <option value="+372">🇪🇪 Estonia (+372)</option>
  <option value="+268">🇸🇿 Eswatini (+268)</option>
  <option value="+251">🇪🇹 Ethiopia (+251)</option>
  <option value="+679">🇫🇯 Fiji (+679)</option>
  <option value="+358">🇫🇮 Finland (+358)</option>
  <option value="+33">🇫🇷 France (+33)</option>
  <option value="+594">🇬🇫 French Guiana (+594)</option>
  <option value="+689">🇵🇫 French Polynesia (+689)</option>
  <option value="+241">🇬🇦 Gabon (+241)</option>
  <option value="+220">🇬🇲 Gambia (+220)</option>
  <option value="+995">🇬🇪 Georgia (+995)</option>
  <option value="+49">🇩🇪 Germany (+49)</option>
  <option value="+233">🇬🇭 Ghana (+233)</option>
  <option value="+350">🇬🇮 Gibraltar (+350)</option>
  <option value="+30">🇬🇷 Greece (+30)</option>
  <option value="+299">🇬🇱 Greenland (+299)</option>
  <option value="+1-473">🇬🇩 Grenada (+1-473)</option>
  <option value="+590">🇬🇵 Guadeloupe (+590)</option>
  <option value="+1-671">🇬🇺 Guam (+1-671)</option>
  <option value="+502">🇬🇹 Guatemala (+502)</option>
  <option value="+44">🇬🇧 United Kingdom (+44)</option>
  <option value="+91">🇮🇳 India (+91)</option>
  <option value="+62">🇮🇩 Indonesia (+62)</option>
  <option value="+98">🇮🇷 Iran (+98)</option>
  <option value="+964">🇮🇶 Iraq (+964)</option>
  <option value="+353">🇮🇪 Ireland (+353)</option>
  <option value="+972">🇮🇱 Israel (+972)</option>
  <option value="+39">🇮🇹 Italy (+39)</option>
  <option value="+81">🇯🇵 Japan (+81)</option>
  <option value="+962">🇯🇴 Jordan (+962)</option>
  <option value="+7">🇰🇿 Kazakhstan (+7)</option>
  <option value="+254">🇰🇪 Kenya (+254)</option>
  <option value="+965">🇰🇼 Kuwait (+965)</option>
  <option value="+996">🇰🇬 Kyrgyzstan (+996)</option>
  <option value="+856">🇱🇦 Laos (+856)</option>
  <option value="+371">🇱🇻 Latvia (+371)</option>
  <option value="+961">🇱🇧 Lebanon (+961)</option>
  <option value="+231">🇱🇷 Liberia (+231)</option>
  <option value="+218">🇱🇾 Libya (+218)</option>
  <option value="+423">🇱🇮 Liechtenstein (+423)</option>
  <option value="+370">🇱🇹 Lithuania (+370)</option>
  <option value="+352">🇱🇺 Luxembourg (+352)</option>
  <option value="+261">🇲🇬 Madagascar (+261)</option>
  <option value="+60">🇲🇾 Malaysia (+60)</option>
  <option value="+960">🇲🇻 Maldives (+960)</option>
<option value="+223">🇲🇱 Mali (+223)</option>
<option value="+356">🇲🇹 Malta (+356)</option>
<option value="+692">🇲🇭 Marshall Islands (+692)</option>
<option value="+596">🇲🇶 Martinique (+596)</option>
<option value="+222">🇲🇷 Mauritania (+222)</option>
<option value="+230">🇲🇺 Mauritius (+230)</option>
<option value="+262">🇾🇹 Mayotte (+262)</option>
<option value="+52">🇲🇽 Mexico (+52)</option>
<option value="+691">🇫🇲 Micronesia (+691)</option>
<option value="+373">🇲🇩 Moldova (+373)</option>
<option value="+377">🇲🇨 Monaco (+377)</option>
<option value="+976">🇲🇳 Mongolia (+976)</option>
<option value="+382">🇲🇪 Montenegro (+382)</option>
<option value="+1664">🇲🇸 Montserrat (+1664)</option>
<option value="+212">🇲🇦 Morocco (+212)</option>
<option value="+258">🇲🇿 Mozambique (+258)</option>
<option value="+95">🇲🇲 Myanmar (+95)</option>
<option value="+264">🇳🇦 Namibia (+264)</option>
<option value="+674">🇳🇷 Nauru (+674)</option>
<option value="+977">🇳🇵 Nepal (+977)</option>
<option value="+31">🇳🇱 Netherlands (+31)</option>
<option value="+687">🇳🇨 New Caledonia (+687)</option>
<option value="+64">🇳🇿 New Zealand (+64)</option>
<option value="+505">🇳🇮 Nicaragua (+505)</option>
<option value="+227">🇳🇪 Niger (+227)</option>
<option value="+234">🇳🇬 Nigeria (+234)</option>
<option value="+683">🇳🇺 Niue (+683)</option>
<option value="+672">🇳🇫 Norfolk Island (+672)</option>
<option value="+850">🇰🇵 North Korea (+850)</option>
<option value="+389">🇲🇰 North Macedonia (+389)</option>
<option value="+47">🇳🇴 Norway (+47)</option>
<option value="+968">🇴🇲 Oman (+968)</option>
<option value="+92">🇵🇰 Pakistan (+92)</option>
<option value="+680">🇵🇼 Palau (+680)</option>
<option value="+970">🇵🇸 Palestine (+970)</option>
<option value="+507">🇵🇦 Panama (+507)</option>
<option value="+675">🇵🇬 Papua New Guinea (+675)</option>
<option value="+595">🇵🇾 Paraguay (+595)</option>
<option value="+51">🇵🇪 Peru (+51)</option>
<option value="+63">🇵🇭 Philippines (+63)</option>
<option value="+48">🇵🇱 Poland (+48)</option>
<option value="+351">🇵🇹 Portugal (+351)</option>
<option value="+1">🇵🇷 Puerto Rico (+1)</option>
<option value="+974">🇶🇦 Qatar (+974)</option>
<option value="+262">🇷🇪 Réunion (+262)</option>
<option value="+40">🇷🇴 Romania (+40)</option>
<option value="+7">🇷🇺 Russia (+7)</option>
<option value="+250">🇷🇼 Rwanda (+250)</option>
<option value="+590">🇧🇱 Saint Barthélemy (+590)</option>
<option value="+290">🇸🇭 Saint Helena (+290)</option>
<option value="+1">🇰🇳 Saint Kitts and Nevis (+1)</option>
<option value="+1">🇱🇨 Saint Lucia (+1)</option>
<option value="+590">🇲🇫 Saint Martin (+590)</option>
<option value="+508">🇵🇲 Saint Pierre and Miquelon (+508)</option>
<option value="+1">🇻🇨 Saint Vincent and the Grenadines (+1)</option>
<option value="+685">🇼🇸 Samoa (+685)</option>
<option value="+378">🇸🇲 San Marino (+378)</option>
<option value="+239">🇸🇹 São Tomé and Príncipe (+239)</option>
<option value="+966">🇸🇦 Saudi Arabia (+966)</option>
<option value="+221">🇸🇳 Senegal (+221)</option>
<option value="+381">🇷🇸 Serbia (+381)</option>
<option value="+248">🇸🇨 Seychelles (+248)</option>
<option value="+232">🇸🇱 Sierra Leone (+232)</option>
<option value="+65">🇸🇬 Singapore (+65)</option>
<option value="+1">🇸🇽 Sint Maarten (+1)</option>
<option value="+421">🇸🇰 Slovakia (+421)</option>
<option value="+386">🇸🇮 Slovenia (+386)</option>
<option value="+677">🇸🇧 Solomon Islands (+677)</option>
<option value="+252">🇸🇴 Somalia (+252)</option>
<option value="+27">🇿🇦 South Africa (+27)</option>
<option value="+82">🇰🇷 South Korea (+82)</option>
<option value="+211">🇸🇸 South Sudan (+211)</option>
<option value="+34">🇪🇸 Spain (+34)</option>
<option value="+94">🇱🇰 Sri Lanka (+94)</option>
<option value="+249">🇸🇩 Sudan (+249)</option>
<option value="+597">🇸🇷 Suriname (+597)</option>
<option value="+47">🇸🇯 Svalbard and Jan Mayen (+47)</option>
<option value="+268">🇸🇿 Eswatini (+268)</option>
<option value="+46">🇸🇪 Sweden (+46)</option>
<option value="+41">🇨🇭 Switzerland (+41)</option>
<option value="+963">🇸🇾 Syria (+963)</option>
<option value="+886">🇹🇼 Taiwan (+886)</option>
<option value="+992">🇹🇯 Tajikistan (+992)</option>
<option value="+255">🇹🇿 Tanzania (+255)</option>
<option value="+66">🇹🇭 Thailand (+66)</option>
<option value="+670">🇹🇱 Timor-Leste (+670)</option>
<option value="+228">🇹🇬 Togo (+228)</option>
<option value="+690">🇹🇰 Tokelau (+690)</option>
<option value="+676">🇹🇴 Tonga (+676)</option>
<option value="+1">🇹🇹 Trinidad and Tobago (+1)</option>
<option value="+216">🇹🇳 Tunisia (+216)</option>
<option value="+90">🇹🇷 Turkey (+90)</option>
<option value="+993">🇹🇲 Turkmenistan (+993)</option>
<option value="+1">🇹🇨 Turks and Caicos Islands (+1)</option>
<option value="+688">🇹🇻 Tuvalu (+688)</option>
<option value="+256">🇺🇬 Uganda (+256)</option>
<option value="+380">🇺🇦 Ukraine (+380)</option>
<option value="+971">🇦🇪 United Arab Emirates (+971)</option>
<option value="+44">🇬🇧 United Kingdom (+44)</option>
<option value="+1">🇺🇸 United States (+1)</option>
<option value="+598">🇺🇾 Uruguay (+598)</option>
<option value="+998">🇺🇿 Uzbekistan (+998)</option>
<option value="+678">🇻🇺 Vanuatu (+678)</option>
<option value="+379">🇻🇦 Vatican City (+379)</option>
<option value="+58">🇻🇪 Venezuela (+58)</option>
<option value="+84">🇻🇳 Vietnam (+84)</option>
<option value="+1">🇻🇬 British Virgin Islands (+1)</option>
<option value="+1">🇻🇮 U.S. Virgin Islands (+1)</option>
<option value="+681">🇼🇫 Wallis and Futuna (+681)</option>
<option value="+212">🇪🇭 Western Sahara (+212)</option>
<option value="+967">🇾🇪 Yemen (+967)</option>
<option value="+260">🇿🇲 Zambia (+260)</option>
<option value="+263">🇿🇼 Zimbabwe (+263)</option>

          </select>
          <input type="tel" id="phone" name="phone" class="phone-number-input" 
                 pattern="^[0-9]{6,15}$" title="Enter a valid phone number (6-15 digits)" 
                 value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
        </div>
      </div>
      
      <button type="submit" class="btn">Update Profile</button>
    </form>
  </div>

  <script>
    function togglePassword(id) {
      const input = document.getElementById(id);
      const icon = input.nextElementSibling;
      
      if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
      } else {
        input.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
      }
    }

    // Prevent non-numeric input in phone field
    document.getElementById('phone').addEventListener('input', function() {
      this.value = this.value.replace(/[^0-9]/g, '');
    });
  </script>
</body>
</html>