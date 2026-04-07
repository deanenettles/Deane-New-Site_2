<?php
if (!isset($_SERVER['REQUEST_METHOD'])) {
    $_SERVER['REQUEST_METHOD'] = 'POST'; // or 'GET', depending on the expected behavior
}

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
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
                    $mail->Host       = $config['smtp_host'];
                    $mail->SMTPAuth   = $config['smtp_auth'];
                    $mail->Username   = $config['smtp_username'];
                    $mail->Password   = $config['smtp_password'];
                    $mail->Port       = $config['smtp_port'];
                    
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
                    $formMessage = 'Sorry, there was an error sending your message. Please try again later.';
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
  <title>Contact - Occupied Flooring</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link href="css/normalize.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@100..900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/ocs.css">
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

    .headImage {background-image:url(images/blueGlassx800.jpg); background-position:center;}
    @media screen and (min-width: 800px) {
  	  .headImage {background-image:url(images/blueGlassx2400.jpg)}
  	}
  </style>
</head>
<body>

  <div class="topnav" id="myTopnav">
    <div id="myLinks">
        <a href="index.html" class="active"><h1>Occupied Flooring Solutions</h1><img src="images/OccupiedFlooringLogo2024Rev.png" alt="Occupied Flooring Solutions logo"></a>
        <a href="services.html">Services</a>
        <a href="process.html">Process</a>
        <a href="progbenefit.html">Program Benefits</a>
        <a href="clients.html">Clients</a>
        <a href="contact.php">Contact</a>
    </div>
    <a href="javascript:void(0);" class="icon" onclick="myFunction()" aria-label="click for navigation dropdown">
        <i class="fa fa-bars"></i>
    </a>
  </div>

  <div class="headImage">
  </div>

  <div class="wrapper">
    <main>
      <h2>Contact</h2>
      <h3>Let's Plan Your Project with Precision</h3>

      <p>Whether you are managing a single occupied renovation or a national flooring program, OFS provides the planning discipline, execution control, and reporting transparency required for success.</p>
      <p>Contact us today to discuss your project and objectives.</p>

      <?php if ($formMessage): ?>
        <div class="form-message <?php echo $formSuccess ? 'success' : 'error'; ?>">
          <?php echo $formMessage; ?>
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
          <option value="Newsletter" <?php echo (isset($_POST['reason']) && $_POST['reason'] === 'Newsletter') ? 'selected' : ''; ?>>Newsletter</option>
          <option value="Other" <?php echo (isset($_POST['reason']) && $_POST['reason'] === 'Other') ? 'selected' : ''; ?>>Other Reason</option>
          <option value="Website Error" <?php echo (isset($_POST['reason']) && $_POST['reason'] === 'Website Error') ? 'selected' : ''; ?>>Website Error</option>
        </select>

        <textarea name="comment" cols="45" rows="6" placeholder="Specifics"><?php echo isset($_POST['comment']) && !$formSuccess ? htmlspecialchars($_POST['comment']) : ''; ?></textarea>

        <button type="submit" value="submit">Submit</button>
        <button type="reset" value="reset">Reset</button>
      </form>
      <?php endif; ?>
    </main>

  </div>
  <footer>
    <p>© 2026 Occupied Flooring Solutions LLC. All Rights Reserved.</p>
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
