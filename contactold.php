<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$config = require 'config.php';

$formMessage = '';
$formSuccess = false;

function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCsrfToken(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function sanitizeInput(string $input): string {
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    return $input;
}

function sanitizeForHeader(string $input): string {
    return preg_replace('/[\r\n]/', '', sanitizeInput($input));
}

function validateEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function isGibberish(string $text): bool {
  $text = strtolower(trim($text));
  
  if (strlen($text) < 3) {
      return false;
  }
  
  $dominated = preg_match('/(.)\1{4,}/', $text);
  if ($dominated) {
      return true;
  }
  
  $keyboardPatterns = ['qwerty', 'asdf', 'zxcv', 'qazwsx', 'asdfgh', 'jkl;', 'uiop', 'hjkl', '12345', '!@#$%'];
  foreach ($keyboardPatterns as $pattern) {
      if (stripos($text, $pattern) !== false) {
          return true;
      }
  }
  
  $letters = preg_replace('/[^a-z]/', '', $text);
  if (strlen($letters) >= 10) {
      $vowels = preg_match_all('/[aeiou]/', $letters);
      $vowelRatio = $vowels / strlen($letters);
      if ($vowelRatio < 0.15 || $vowelRatio > 0.70) {
          return true;
      }
  }
  
  if (preg_match('/[bcdfghjklmnpqrstvwxz]{6,}/i', $letters)) {
      return true;
  }
  
  $alphaNum = preg_replace('/[^a-z0-9\s]/', '', $text);
  $specialRatio = 1 - (strlen($alphaNum) / max(strlen($text), 1));
  if ($specialRatio > 0.4 && strlen($text) > 10) {
      return true;
  }
  
  return false;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    
    if (!empty($_POST['website_url'])) {
        $formMessage = 'Thank you for your submission.';
        $formSuccess = true;
    } else {
        if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
            $formMessage = 'Security validation failed. Please refresh and try again.';
        } else {
            $firstname = sanitizeForHeader($_POST['firstname'] ?? '');
            $lastname = sanitizeForHeader($_POST['lastname'] ?? '');
            $company = sanitizeInput($_POST['company'] ?? '');
            $email = sanitizeForHeader($_POST['email'] ?? '');
            $reason = sanitizeInput($_POST['reason'] ?? '');
            $comment = sanitizeInput($_POST['comment'] ?? '');
            
            $errors = [];
            
            if (empty($firstname)) {
                $errors[] = 'First name is required.';
            }
            if (empty($lastname)) {
                $errors[] = 'Last name is required.';
            }
            if (empty($company)) {
                $errors[] = 'Company is required.';
            }
            if (empty($email)) {
                $errors[] = 'Email is required.';
            } elseif (!validateEmail($email)) {
                $errors[] = 'Please enter a valid email address.';
            }
            
            if (!empty($errors)) {
                $formMessage = implode('<br>', $errors);
            } else {
                $mail = new PHPMailer(true);
                
                try {
                    $mail->isSMTP();
                    $mail->SMTPDebug  = 0;
                    $mail->Host       = $config['smtp_host'];
                    $mail->SMTPAuth   = $config['smtp_auth'];
                    $mail->Username   = $config['smtp_username'];
                    $mail->Password   = $config['smtp_password'];
                    $mail->Port       = $config['smtp_port'];
                    $mail->Timeout    = 30;
                    
                    if ($config['smtp_secure'] === 'ssl') {
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    } else {
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    }
                    
                    $mail->setFrom($config['from_email'], $config['from_name']);
                    $mail->addAddress($config['to_email'], $config['to_name']);
                    $mail->addReplyTo($email, "$firstname $lastname");
                    
                    $mail->isHTML(true);
                    $mail->Subject = "Contact Form: $reason - $firstname $lastname";
                    
                    $mail->Body = "
                        <html>
                        <body style='font-family: Arial, sans-serif;'>
                            <h2>New Contact Form Submission</h2>
                            <table style='border-collapse: collapse; width: 100%; max-width: 600px;'>
                                <tr>
                                    <td style='padding: 10px; border: 1px solid #ddd; background: #f5f5f5;'><strong>Name:</strong></td>
                                    <td style='padding: 10px; border: 1px solid #ddd;'>$firstname $lastname</td>
                                </tr>
                                <tr>
                                    <td style='padding: 10px; border: 1px solid #ddd; background: #f5f5f5;'><strong>Company:</strong></td>
                                    <td style='padding: 10px; border: 1px solid #ddd;'>$company</td>
                                </tr>
                                <tr>
                                    <td style='padding: 10px; border: 1px solid #ddd; background: #f5f5f5;'><strong>Email:</strong></td>
                                    <td style='padding: 10px; border: 1px solid #ddd;'><a href='mailto:$email'>$email</a></td>
                                </tr>
                                <tr>
                                    <td style='padding: 10px; border: 1px solid #ddd; background: #f5f5f5;'><strong>Reason:</strong></td>
                                    <td style='padding: 10px; border: 1px solid #ddd;'>$reason</td>
                                </tr>
                                <tr>
                                    <td style='padding: 10px; border: 1px solid #ddd; background: #f5f5f5;'><strong>Message:</strong></td>
                                    <td style='padding: 10px; border: 1px solid #ddd;'>" . nl2br($comment) . "</td>
                                </tr>
                            </table>
                            <p style='color: #666; font-size: 12px; margin-top: 20px;'>
                                Submitted on " . date('F j, Y \a\t g:i A') . "
                            </p>
                        </body>
                        </html>
                    ";
                    
                    $mail->AltBody = "New Contact Form Submission\n\n"
                        . "Name: $firstname $lastname\n"
                        . "Company: $company\n"
                        . "Email: $email\n"
                        . "Reason: $reason\n"
                        . "Message:\n$comment\n\n"
                        . "Submitted on " . date('F j, Y \a\t g:i A');
                    
                    $mail->send();
                    $formMessage = 'Thank you! Your message has been sent successfully.';
                    $formSuccess = true;
                    
                    unset($_SESSION['csrf_token']);
                    
                } catch (Exception $e) {
                    error_log("PHPMailer Error: " . $mail->ErrorInfo);
                    $formMessage = 'Error: ' . $mail->ErrorInfo;
                }
            }
        }
    }
}

$csrfToken = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact - Deane Nettles Associates</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link href="css/normalize.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,700;1,9..40,400&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/normalize.css">
  <link rel="stylesheet" href="css/portfolio.css">


  <style>

    .form-message {
      padding: 15px;
      margin: 20px 0;
      border-radius: 5px;
    }
    .form-message.success {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
      padding: 30px;
      text-align: center;
      animation: fadeIn 0.5s ease-in-out;
    }
    .form-message.success .success-icon {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 60px;
      height: 60px;
      background-color: #28a745;
      border-radius: 50%;
      margin: 0 auto 15px;
      animation: scaleIn 0.4s ease-out;
    }
    .form-message.success .success-icon svg {
      width: 30px;
      height: 30px;
      fill: white;
    }
    .form-message.success h3 {
      margin: 0 0 10px;
      font-size: 1.4em;
      color: #155724;
    }
    .form-message.success p {
      margin: 0;
      color: #155724;
    }
    .form-message.error {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }
    .honeypot {
      position: absolute;
      left: -9999px;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    @keyframes scaleIn {
      from { transform: scale(0); }
      to { transform: scale(1); }
    }

  </style>
</head>
<body>

<header>
        <div class="logo-section">
            <img src="images/nettle.svg" alt="Deane Nettles Logo" class="logo">
            <div>
                <h1 class="site-title">Deane <span>Nettles Associates</span></h1>
                <p class="tagline">Graphic Design, Web Design &amp; Illustration</p>
            </div>
        </div>
    </header>

    <main class="page-content">

    <nav class="filter-nav">
        <div class="nav-links">
            <a href="index.html" class="nav-link">Portfolio</a>
            <a href="about.html" class="nav-link">About</a>
            <a href="contact.php" class="nav-link" style="color: var(--accent); border-bottom-color: var(--accent);">Contact</a>
        </div>
    </nav>

    <h2 class="page-title">Contact <span>Deane Nettles</span></h2>

    <div class="page2-content">

      <p>You want people to know about your company or your mission. We're happy to help you come up with exciting designs and innovative ways to communicate.</p>
      <p>Contact us today to discuss your project and objectives.</p>

      <?php if ($formMessage): ?>
        <div class="form-message <?php echo $formSuccess ? 'success' : 'error'; ?>">
          <?php if ($formSuccess): ?>
            <div class="success-icon">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
            </div>
            <h3>Message Sent!</h3>
            <p><?php echo $formMessage; ?></p>
          <?php else: ?>
            <?php echo $formMessage; ?>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <?php if (!$formSuccess): ?>
      <form action="contact.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
        
        <div class="honeypot">
          <label for="website_url">Leave this empty</label>
          <input type="text" name="website_url" id="website_url" tabindex="-1" autocomplete="off">
        </div>

        <input type="text" name="firstname" required autofocus placeholder="First Name*" 
               value="<?php echo isset($_POST['firstname']) && !$formSuccess ? htmlspecialchars($_POST['firstname']) : ''; ?>">
        <input type="text" name="lastname" required placeholder="Last Name*"
               value="<?php echo isset($_POST['lastname']) && !$formSuccess ? htmlspecialchars($_POST['lastname']) : ''; ?>">
        <input type="text" name="company" required placeholder="Company*"
               value="<?php echo isset($_POST['company']) && !$formSuccess ? htmlspecialchars($_POST['company']) : ''; ?>">
        <input type="email" name="email" required placeholder="Email*"
               value="<?php echo isset($_POST['email']) && !$formSuccess ? htmlspecialchars($_POST['email']) : ''; ?>">
        <p class="hilight">* is required</p>

        <p>I'm contacting you about:</p>
        <select name="reason">
          <option value="Project Management" <?php echo (isset($_POST['reason']) && $_POST['reason'] === 'Project Management') ? 'selected' : ''; ?>>Project Management</option>
          <option value="Newsletter" <?php echo (isset($_POST['reason']) && $_POST['reason'] === 'Newsletter') ? 'selected' : ''; ?>>Logo Design</option>         <option value="Newsletter" <?php echo (isset($_POST['reason']) && $_POST['reason'] === 'Newsletter') ? 'selected' : ''; ?>>Marketing Materials</option>
          <option value="Other" <?php echo (isset($_POST['reason']) && $_POST['reason'] === 'Other') ? 'selected' : ''; ?>>Magazines</option>
          <option value="Website Error" <?php echo (isset($_POST['reason']) && $_POST['reason'] === 'Website Error') ? 'selected' : ''; ?>>Web Design</option>
          <option value="Website Error" <?php echo (isset($_POST['reason']) && $_POST['reason'] === 'Website Error') ? 'selected' : ''; ?>>Illustration</option>        
        </select>

        <textarea name="comment" cols="45" rows="6" placeholder="Specifics"><?php echo isset($_POST['comment']) && !$formSuccess ? htmlspecialchars($_POST['comment']) : ''; ?></textarea>

        <button type="submit" value="submit">Submit</button>
        <button type="reset" value="reset">Reset</button>
      </form>
      <?php endif; ?>
      </div>

  </div>
</main>
<footer>
    <p>© 2026 Deane Nettles Associates LLC. All Rights Reserved.</p>
  </footer>
  <script>
  function myFunction() {
    var x = document.getElementById("myTopnav");
    if (x.className === "topnav") {
      x.className += " responsive";
    } else {
      x.className = "topnav";
    }
  }
  </script>

</body>
</html>
