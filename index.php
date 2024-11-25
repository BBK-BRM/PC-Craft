<?php include 'includes/header.php'; ?>
<section class="banner">
    <div class="banner-text">
        <h1>Welcome to PC Craft</h1>
        <p>Your one-stop solution for custom PC builds.</p>
        <a href="build.php" class="btn-primary">Browse Components</a>
    </div>
    <div class="banner-img">
        <img src="images/pc-set-1.png" alt="pc">
    </div>
</section>

<section class="features">
    <div class="container">
        <h2>Products</h2>
        <div class="products" style='display: grid; grid-template-columns: 1fr 1fr 1fr;'>
            <?php
            // Connect to the database
            require 'includes/database.php';
            $conn = getDB();

            // Fetch products from the database
            $stmt = $conn->prepare("SELECT * FROM components ORDER BY RAND() LIMIT 6"); // Limit the number of displayed products
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($product = $result->fetch_assoc()) {
                    echo "
                    <div class='product-item' style='margin:10px'>
                        <img src='../uploads/{$product['images']}' alt='{$product['name']}' height='100px' width='100px'>
                        <h3>{$product['name']}</h3>
                        <p>Type: {$product['type']}</p>
                        <p>Brand: {$product['brand']}</p>
                        <p>Price: NRS.{$product['price']}</p>
                    </div>
                    ";
                }
            } else {
                echo "<p>No products available at the moment.</p>";
            }

            $stmt->close();
            $conn->close();
            ?>
        </div>
        <div class="feature-item">
            <h2>Custom Builds</h2>
            <p>Choose from a variety of components to build your perfect PC.</p>
        </div>
        <div class="feature-item">
            <h2>Expert Advice</h2>
            <p>Get guidance from experts to make the best choices for your build.</p>
        </div>
        <div class="feature-item">
            <h2>Fast Delivery</h2>
            <p>Enjoy quick and reliable delivery right to your door.</p>
        </div>
    </div>
</section>

<section class="cta">
    <h2>Ready to build your dream PC?</h2>
    <a href="build.php" class="btn-primary">Start Building</a>
</section>
<?php include 'includes/footer.php'; ?>