<?php
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
    
    if (empty($name) || empty($email) || empty($message)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $to = 'deane@deanenettles.com';
        $email_subject = "Portfolio Contact: " . ($subject ?: 'No Subject');
        $email_body = "Name: $name\n";
        $email_body .= "Email: $email\n\n";
        $email_body .= "Message:\n$message";
        
        $headers = "From: $email\r\n";
        $headers .= "Reply-To: $email\r\n";
        
        if (mail($to, $email_subject, $email_body, $headers)) {
            $success = true;
        } else {
            $error = 'There was an error sending your message. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact — Deane Nettles</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,700;1,9..40,400&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="css/portfolio.css" rel="stylesheet">
    <style>
        .page-content {
            position: relative;
            z-index: 10;
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem;
        }

        .page-title {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 2.5rem;
            font-weight: 600;
            margin-bottom: 2rem;
            color: var(--text-primary);
        }

        .page-title span {
            color: var(--accent);
        }

        .contact-form {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 2.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
            font-weight: 500;
        }

        .form-group label .required {
            color: var(--accent);
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-family: inherit;
            font-size: 1rem;
            color: var(--text-primary);
            background: var(--bg-primary);
            transition: var(--transition);
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-glow);
        }

        .form-group textarea {
            min-height: 150px;
            resize: vertical;
        }

        .submit-btn {
            background: var(--accent);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 100px;
            font-family: inherit;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            width: 100%;
        }

        .submit-btn:hover {
            background: #a81c2a;
            transform: translateY(-2px);
            box-shadow: 0 4px 20px var(--accent-glow);
        }

        .success-message {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 1.5rem;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .error-message {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .contact-info {
            margin-top: 2rem;
            text-align: center;
            color: var(--text-secondary);
        }

        .contact-info a {
            color: var(--accent);
            text-decoration: none;
        }

        .contact-info a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo-section">
            <img src="images/nettle.svg" alt="Deane Nettles Logo" class="logo">
            <div>
                <h1 class="site-title">Deane <span>Nettles</span></h1>
                <p class="tagline">Graphic Design &amp; Illustration</p>
            </div>
        </div>
    </header>

    <nav class="filter-nav">
        <div class="nav-links">
            <a href="index.html" class="nav-link">Portfolio</a>
            <a href="about.html" class="nav-link">About</a>
            <a href="contact.php" class="nav-link" style="color: var(--accent); border-bottom-color: var(--accent);">Contact</a>
        </div>
    </nav>

    <main class="page-content">
        <h2 class="page-title">Get in <span>Touch</span></h2>
        
        <div class="contact-form">
            <?php if ($success): ?>
                <div class="success-message">
                    <strong>Thank you!</strong> Your message has been sent. I'll get back to you soon.
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (!$success): ?>
            <form method="POST" action="contact.php">
                <div class="form-group">
                    <label for="name">Name <span class="required">*</span></label>
                    <input type="text" id="name" name="name" required 
                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email <span class="required">*</span></label>
                    <input type="email" id="email" name="email" required
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="subject">Subject</label>
                    <input type="text" id="subject" name="subject"
                           value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="message">Message <span class="required">*</span></label>
                    <textarea id="message" name="message" required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                </div>
                
                <button type="submit" class="submit-btn">Send Message</button>
            </form>
            <?php endif; ?>
            
            <div class="contact-info">
                <p>Or email directly: <a href="mailto:deane@deanenettles.com">deane@deanenettles.com</a></p>
            </div>
        </div>
    </main>

    <footer>
        <p>
            <button class="footer-btn"><a href="index.html">Home</a></button>
            <button class="footer-btn"><a href="resume.html">Resume</a></button>
            <button class="footer-btn"><a href="contact.php">Contact</a></button>
            <button class="footer-btn"><a href="charcoal.html" target="_blank">Charcoal</a></button>
            <button class="footer-btn"><a href="paintings.html" target="_blank">Paintings</a></button>
            <button class="footer-btn"><a href="training.html" target="_blank">Training</a></button>
            <button class="footer-btn"><a href="historyofdesign.html" target="_blank">History of Design</a></button>
            <button class="footer-btn"><a href="https://www.baltimoreindustrytours.com" target="_blank">Baltimore Industry Tours</a></button>
            <button class="footer-btn"><a href="https://www.deanenettles.com/baltimorebauhaus" target="_blank">Baltimore Bauhaus</a></button>
        </p>
        <p class="copy">&copy; 2026 Deane Nettles. All rights reserved.</p>
    </footer>
</body>
</html>
