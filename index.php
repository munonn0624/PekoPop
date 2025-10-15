<?php
session_start();
require_once 'backend/dbconnect.php';
include 'backend/product-carousel-service.php';

?>

<!DOCTYPE html>
<html>

<head>
    <title>PEKOPOP</title>
    <link rel="stylesheet" href="/PekoPop/css/header.css">
    <link rel="stylesheet" href="/PekoPop/css/home.css">
    <link rel="stylesheet" href="/PekoPop/css/product-detail.css">

    <link rel="stylesheet" href="/PekoPop/css/footer.css">
    <script src="https://kit.fontawesome.com/a737b063d1.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>

<body>
    <?php include 'include/header.php'; ?>
    <div class="hero">
        <video autoplay muted loop playsinline class="background-video">
            <source src="/PekoPop/static/videos/hero-vid.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>

        <div class="hero-content">
            <a href="/PekoPop/pages/products"><button> Shop Now</button></a>
            <pre>Adorable   &bull;   Collectible   &bull;   Unmissable</pre>

        </div>
    </div>


    <section class="home-product">
        <h2>Unbox Now</h2>
        <div class="home-product-span">
            <?php
            if ($recommendations > 0) {
                foreach ($recommendations as $row) {
                    echo '<a href="/PekoPop/pages/product-detail.php?id=' . $row['product_id'] . '">';
                    echo '<div class="product-card">';
                    echo '<img src="' . htmlspecialchars($row['image_url']) . '" alt="' . htmlspecialchars($row['name']) . '">';
                    echo '<div class="product-desc">';

                    echo '<div class="product-name">';
                    if ($row['is_new']) {
                        echo '<span class="badge rounded-pill text-bg-success">NEW</span> ';
                    }
                    echo htmlspecialchars($row['name']);
                    echo '</div>';

                    echo '<div class="product-price">';
                    echo '<p>Starting From:</p>';
                    echo '<h5>RM ' . number_format($row['price'], 2) . '</h5>';
                    echo '</div>';  // end product-price

                    echo '</div>';  // end product-desc
                    echo '</div>';  // end product-card
                    echo '</a>';
                }
            } else {
                echo "<p>No products found.</p>";
            }
            ?>


        </div>
        <a href="/PekoPop/pages/products"><button onclick="">View All Products</button></a>
    </section>

    <section class="product-story">
        <h2>Collection Story</h2>
        <div class="product-story-div">
            <ul class="product-story-nav">
                <li><button data-character="labubu">Labubu</button></li>
                <li><button data-character="twinkle">Twinkle</button></li>
                <li><button data-character="zimomo">Zimomo</button></li>
            </ul>

            <div class="story-div">
                <h3>Labubu</h3>
                <p class="story-text">
                </p>
            </div>
        </div>
    </section>


    <section class="artist">
        <h2>Artist</h2>
        <div class="home-product-span">
            <div class="home-artist-card">
                <img src="images/blindbox/twinkle/sku1/main.jpeg" alt="">
                <div class="home-artist-desc">
                    <h3>Amanda Wong</h3>
                    <p>Amanda Wong is a sohai. She is originate from Singapore. Her sister is Adeline Chang, She likes to play badminton.</p>
                </div>
            </div>
            <div class="home-artist-card">
                <img src="images/blindbox/twinkle/sku1/main.jpeg" alt="">
                <div class="home-artist-desc">
                    <h3>Amanda Wong</h3>
                    <p>Amanda Wong is a sohai. She is originate from Singapore. Her sister is Adeline Chang, She likes to play badminton.</p>
                </div>
            </div>
            <div class="home-artist-card">
                <img src="images/blindbox/twinkle/sku1/main.jpeg" alt="">
                <div class="home-artist-desc">
                    <h3>Amanda Wong</h3>
                    <p>Amanda Wong is a sohai. She is originate from Singapore. Her sister is Adeline Chang, She likes to play badminton.</p>
                </div>
            </div>
        </div>
    </section>
    <section class="cust-review">
        <h2>Loved by Unboxer</h2>
        <div class="review-row">
            <div class="home-review-card">
                <i class="fa-solid fa-quote-left fa-2xl" style="color: #f8da2e;"></i>
                <blockquote>Lorem ipsum dolor sit amet consectetur adipisicing elit. Soluta mollitia necessitatibus quidem unde vel doloribus libero totam ab? Debitis ut repudiandae quisquam necessitatibus. Sint, dignissimos accusantium error quae animi aspernatur?Lorem, ipsum dolor sit amet consectetur adipisicing elit. Praesentium eos facilis ullam mollitia libero vel officiis excepturi eum, nemo at. Veniam alias dolorum tempora quas quaerat placeat, accusantium necessitatibus excepturi?</blockquote>
                <div class="reviewer">
                    <div class="avatar">
                        <img src="/PekoPop/images/blindbox/twinkle/sku1/main.jpeg" alt="">
                    </div>
                    <h3>Amanda Wong</h3>
                </div>
            </div>
            <div class="home-review-card">
                <i class="fa-solid fa-quote-left fa-2xl" style="color: #f8da2e;"></i>
                <blockquote>Lorem ipsum dolor sit amet consectetur adipisicing elit. Soluta mollitia necessitatibus quidem unde vel doloribus libero totam ab? Debitis ut repudiandae quisquam necessitatibus. Sint, dignissimos accusantium error quae animi aspernatur?Lorem, ipsum dolor sit amet consectetur adipisicing elit. Praesentium eos facilis ullam mollitia libero vel officiis excepturi eum, nemo at. Veniam alias dolorum tempora quas quaerat placeat, accusantium necessitatibus excepturi?</blockquote>
                <div class="reviewer">
                    <div class="avatar">
                        <img src="/PekoPop/images/blindbox/twinkle/sku1/main.jpeg" alt="">
                    </div>
                    <h3>Amanda Wong</h3>
                </div>
            </div>
            <div class="home-review-card">
                <i class="fa-solid fa-quote-left fa-2xl" style="color: #f8da2e;"></i>
                <blockquote>Lorem ipsum dolor sit amet consectetur adipisicing elit. Soluta mollitia necessitatibus quidem unde vel doloribus libero totam ab? Debitis ut repudiandae quisquam necessitatibus. Sint, dignissimos accusantium error quae animi aspernatur?Lorem, ipsum dolor sit amet consectetur adipisicing elit. Praesentium eos facilis ullam mollitia libero vel officiis excepturi eum, nemo at. Veniam alias dolorum tempora quas quaerat placeat, accusantium necessitatibus excepturi?</blockquote>
                <div class="reviewer">
                    <div class="avatar">
                        <img src="/PekoPop/images/blindbox/twinkle/sku1/main.jpeg" alt="">
                    </div>
                    <h3>Amanda Wong</h3>
                </div>
            </div>
        </div>

        <div class="review-row">
            <div class="home-review-card">
                <i class="fa-solid fa-quote-left fa-2xl" style="color: #f8da2e;"></i>
                <blockquote>Lorem ipsum dolor sit amet consectetur adipisicing elit. Soluta mollitia necessitatibus quidem unde vel doloribus libero totam ab? Debitis ut repudiandae quisquam necessitatibus. Sint, dignissimos accusantium error quae animi aspernatur?Lorem, ipsum dolor sit amet consectetur adipisicing elit. Praesentium eos facilis ullam mollitia libero vel officiis excepturi eum, nemo at. Veniam alias dolorum tempora quas quaerat placeat, accusantium necessitatibus excepturi?</blockquote>
                <div class="reviewer">
                    <div class="avatar">
                        <img src="/PekoPop/images/blindbox/twinkle/sku1/main.jpeg" alt="">
                    </div>
                    <h3>Amanda Wong</h3>
                </div>
            </div>
            <div class="home-review-card">
                <i class="fa-solid fa-quote-left fa-2xl" style="color: #f8da2e;"></i>
                <blockquote>Lorem ipsum dolor sit amet consectetur adipisicing elit. Soluta mollitia necessitatibus quidem unde vel doloribus libero totam ab? Debitis ut repudiandae quisquam necessitatibus. Sint, dignissimos accusantium error quae animi aspernatur?Lorem, ipsum dolor sit amet consectetur adipisicing elit. Praesentium eos facilis ullam mollitia libero vel officiis excepturi eum, nemo at. Veniam alias dolorum tempora quas quaerat placeat, accusantium necessitatibus excepturi?</blockquote>
                <div class="reviewer">
                    <div class="avatar">
                        <img src="/PekoPop/images/blindbox/twinkle/sku1/main.jpeg" alt="">
                    </div>
                    <h3>Amanda Wong</h3>
                </div>
            </div>
            <div class="home-review-card">
                <i class="fa-solid fa-quote-left fa-2xl" style="color: #f8da2e;"></i>
                <blockquote>Lorem ipsum dolor sit amet consectetur adipisicing elit. Soluta mollitia necessitatibus quidem unde vel doloribus libero totam ab? Debitis ut repudiandae quisquam necessitatibus. Sint, dignissimos accusantium error quae animi aspernatur?Lorem, ipsum dolor sit amet consectetur adipisicing elit. Praesentium eos facilis ullam mollitia libero vel officiis excepturi eum, nemo at. Veniam alias dolorum tempora quas quaerat placeat, accusantium necessitatibus excepturi?</blockquote>
                <div class="reviewer">
                    <div class="avatar">
                        <img src="/PekoPop/images/blindbox/twinkle/sku1/main.jpeg" alt="">
                    </div>
                    <h3>Amanda Wong</h3>
                </div>
            </div>
        </div>



    </section>
    <?php include 'include/footer.php'; ?>
    <script src="js/home.js"></script>
    <script src="js/home.js"></script>
</body>
</html>
