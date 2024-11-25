<?php include 'includes/header.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About Us - PC Craft</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    .about-container {
      max-width: 1200px;
      margin: 50px auto;
      padding: 20px;
      background-color: #f9f9f9;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      border-radius: 8px;
      text-align: center;
    }

    .about-header {
      font-size: 2.5rem;
      color: #007bff;
      margin-bottom: 30px;
    }

    .about-content {
      font-size: 1.2rem;
      color: #333;
      line-height: 1.8;
      text-align: justify;
    }

    .about-content p {
      margin-bottom: 20px;
    }

    .about-mission {
      background-color: #e9ecef;
      padding: 20px;
      border-radius: 8px;
      margin-top: 30px;
    }

    .about-mission h3 {
      font-size: 1.8rem;
      color: #343a40;
    }

    .about-mission p {
      font-size: 1.1rem;
    }

    .about-image {
      margin-top: 30px;
      width: 100%;
      max-height: 400px;
      object-fit: cover;
    }
  </style>
</head>

<body>
  <div class="about-container">
    <h1 class="about-header">About PC Craft</h1>
    <div class="about-content">
      <p>Welcome to PC Craft, your go-to platform for building custom PCs tailored to your exact needs. Whether you're a
        gamer, designer, or just need a high-performance machine for work, we are here to help you craft the perfect PC.
      </p>

      <p>Our platform allows you to choose from a wide range of components, ensuring that every part of your build is
        fully compatible. We provide expert advice and real-time compatibility checks, powered by advanced algorithms,
        to ensure you get the most out of your custom build without any guesswork.</p>

      <p>At PC Craft, we believe that building a PC should be a fun and educational experience. That's why we offer a
        user-friendly interface, detailed component descriptions, and a seamless checkout process to make your building
        journey as smooth as possible.</p>
    </div>

    <div class="about-mission">
      <h3>Our Mission</h3>
      <p>To empower users with the tools and knowledge to build custom PCs confidently, ensuring compatibility,
        performance, and value every step of the way.</p>
    </div>

    <img src="images/about-us.jpeg" alt="PC Craft Team" class="about-image">
  </div>

  <?php include 'includes/footer.php'; ?>
</body>

</html>