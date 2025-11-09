<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PriMeri - Contact Us</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Custom CSS -->
  <link rel="stylesheet" href="../css/contact.css">

  <link rel="icon" type="image/png" href="../images/favicon.ico">
</head>

<body class="bg-light-bg text-dark">

  <!-- Header / Navbar -->
  <header class="bg-white shadow-sm sticky-top">
    <nav class="navbar navbar-expand-lg navbar-light">
      <div class="container-xl px-4">
        <a href="#" class="navbar-brand fs-4 fw-bold text-primary-custom d-flex align-items-center">
          <img src="../images/logo.jpg" alt="PriMeri Logo" width="48" height="48" class="me-2">
          PriMeri
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
          aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
            <li class="nav-item">
              <a class="nav-link text-dark" href="../html/home.html">Home</a>
            </li>
            <li class="nav-item">
              <a class="nav-link text-dark" href="../html/catalog.php">Catalog</a>
            </li>
            <li class="nav-item">
              <a class="nav-link text-dark" href="../html/stores.php">Stores</a>
            </li>
            <li class="nav-item">
              <a class="nav-link active text-primary-custom fw-semibold" href="./contact.php">Contact</a>
            </li>
          </ul>
          <div class="d-flex align-items-center">
            <a href="../html/login.html">
              <button class="btn btn-link text-dark me-2">Log In</button>
            </a>
            <a href="../html/signup.html">
              <button class="btn bg-primary-custom text-white px-4 py-2 rounded-3 shadow btn-hover-primary">Get Started</button>
            </a>
          </div>
        </div>
      </div>
    </nav>
  </header>

  <!-- Contact Section -->
  <main class="container-xl px-4 py-5">
    <div class="text-center mb-5">
      <h1 class="display-6 fw-bold text-dark">We’d Love to Hear From You</h1>
      <p class="fs-5 text-muted mx-auto" style="max-width: 700px;">
        Whether you’re a manufacturer, buyer, or partner, we’re here to help.  
        Reach out for inquiries, collaborations, or feedback.
      </p>
    </div>

    <div class="row g-5 align-items-start">
      <!-- Contact Form -->
      <div class="col-lg-6">
        <div class="contact-form card border-0 shadow-lg rounded-4 p-4">
          <form action="../php/contact-handler.php" method="POST">
            <div class="mb-3">
              <label for="name" class="form-label fw-semibold">Full Name</label>
              <input type="text" class="form-control rounded-3" id="name" name="name" placeholder="Your Name" required>
            </div>

            <div class="mb-3">
              <label for="email" class="form-label fw-semibold">Email Address</label>
              <input type="email" class="form-control rounded-3" id="email" name="email" placeholder="you@example.com" required>
            </div>

            <div class="mb-3">
              <label for="subject" class="form-label fw-semibold">Subject</label>
              <input type="text" class="form-control rounded-3" id="subject" name="subject" placeholder="How can we help?" required>
            </div>

            <div class="mb-4">
              <label for="message" class="form-label fw-semibold">Message</label>
              <textarea class="form-control rounded-3" id="message" name="message" rows="4" placeholder="Type your message here..." required></textarea>
            </div>

            <button type="submit" class="btn bg-primary-custom text-white px-4 py-2 rounded-3 shadow-sm btn-hover-primary">
              Send Message
            </button>
          </form>

          <!-- Success/Error message area -->
          <?php
          if (isset($_GET['status'])) {
              if ($_GET['status'] == 'success') {
                  echo '<p class="mt-3 text-success fw-semibold">✅ Your message has been sent successfully!</p>';
              } elseif ($_GET['status'] == 'error') {
                  echo '<p class="mt-3 text-danger fw-semibold">❌ There was an error sending your message. Please try again.</p>';
              } elseif ($_GET['status'] == 'empty') {
                  echo '<p class="mt-3 text-warning fw-semibold">⚠️ Please fill in all fields before submitting.</p>';
              }
          }
          ?>
        </div>
      </div>

      <!-- Contact Info -->
      <div class="col-lg-6">
        <div class="contact-info ps-lg-4">
          <h4 class="fw-bold text-dark mb-3">Get in Touch</h4>
          <p class="text-muted mb-3">We’re based in Nairobi and operate globally through trusted partners.</p>

        </div>
      </div>
    </div>
  </main>

  <!-- Footer -->
  <footer class="bg-dark-bg-custom text-white mt-5">
    <div class="container-xl px-4 py-4 text-center">
      <p class="mb-0">&copy; 2025 PriMeri.</p>
    </div>
  </footer>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
