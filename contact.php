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
    <link href="css/contact.css" rel="stylesheet">
    <style>

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
        <a href="index.html" class="back-link">← Back to Portfolio</a>
    </nav>

    <main class="contact-container">
        <div class="contact-intro">
            <h2>Get in Touch</h2>
            <p>Have a project in mind? I'd love to hear from you.</p>
        </div>

        <form class="contact-form" action="https://formsubmit.co/e176a9df9fdcb43ce3669e8734bb5359" method="POST" enctype="multipart/form-data">
            <input type="text" name="firstname" required autofocus placeholder="First Name*">
            <input type="text" name="lastname" required placeholder="Last Name*">
            <input type="text" name="company" required placeholder="Company*">
            <input type="email" name="email" required placeholder="Email*">
            <p class="hilight">* is required</p>

            <p>I'm contacting you about:</p>
            <select name="reason">
                <option value="Project Management">Project Management</option>
                <option value="newsletter">Newsletter</option>
                <option value="other">Other Reason</option>
                <option value="specifics">Specifics/Additional Comments</option>
                <option value="error">Website Error</option>
            </select>

            <textarea name="comment" placeholder="Specifics or additional comments..."></textarea>

            <div class="form-buttons">
                <button type="submit">Submit</button>
                <button type="reset">Reset</button>
            </div>
        </form>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Deane Nettles. All rights reserved.</p>
        <p><a href="index.html">Home</a> · <a href="resume.html">Resume</a></p>
    </footer>
</body>
</html>
