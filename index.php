<?php
// home page. $root is empty here since this file is at the project root
session_start();
$root      = "";
$pageTitle = "sgCar — Buy & Sell Cars in Singapore";

// pulls the 6 most recent available listings. left join gets the cover photo in the same query
include "inc/db.inc.php";

$stmt = $conn->prepare("
    SELECT
        c.car_id,
        c.brand,
        c.model,
        c.year,
        c.price,
        c.mileage,
        c.transmission,
        c.fuel_type,
        c.type,
        ci.image_path AS primary_image
    FROM cars c
    LEFT JOIN car_images ci
        ON c.car_id = ci.car_id AND ci.is_primary = 1
    WHERE c.status = 'available'
    ORDER BY c.created_at DESC
    LIMIT 6
");
$stmt->execute();
$featured_cars = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <?php include "inc/head.inc.php"; ?>
</head>

<body class="d-flex flex-column min-vh-100">

    <?php include "inc/nav.inc.php"; ?>

    <main id="main-content">

        <!-- hero section -->
        <section class="hero-section" aria-label="Search for your next car">
            <div class="hero-overlay">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-12 col-lg-9 text-center">

                            <h1 class="hero-title">Find Your Perfect Car in Singapore</h1>
                            <p class="hero-subtitle">
                                Browse thousands of used and new cars from trusted sellers islandwide
                            </p>

                            <!-- search card sitting on top of the hero image -->
                            <div class="hero-search-card card shadow-lg">
                                <div class="card-body p-4">
                                    <form action="listings.php" method="get"
                                          class="row g-2 align-items-center"
                                          aria-label="Car search form">

                                        <div class="col-6 col-md">
                                            <select name="brand" class="form-select" aria-label="Brand">
                                                <option value="">All Brands</option>
                                                <option>Toyota</option>
                                                <option>Honda</option>
                                                <option>BMW</option>
                                                <option>Mercedes-Benz</option>
                                                <option>Mazda</option>
                                                <option>Hyundai</option>
                                                <option>Volkswagen</option>
                                                <option>Subaru</option>
                                                <option>Mitsubishi</option>
                                                <option>Kia</option>
                                            </select>
                                        </div>

                                        <div class="col-6 col-md">
                                            <select name="type" class="form-select" aria-label="Vehicle Type">
                                                <option value="">Vehicle Type</option>
                                                <option>Sedan</option>
                                                <option>SUV</option>
                                                <option>Hatchback</option>
                                                <option>MPV</option>
                                                <option>Coupe</option>
                                                <option>Electric</option>
                                            </select>
                                        </div>

                                        <div class="col-6 col-md">
                                            <select name="min_price" class="form-select" aria-label="Minimum Price">
                                                <option value="">Min Price</option>
                                                <option value="30000">S$ 30,000</option>
                                                <option value="50000">S$ 50,000</option>
                                                <option value="80000">S$ 80,000</option>
                                                <option value="100000">S$ 100,000</option>
                                                <option value="130000">S$ 130,000</option>
                                                <option value="160000">S$ 160,000</option>
                                            </select>
                                        </div>

                                        <div class="col-6 col-md">
                                            <select name="max_price" class="form-select" aria-label="Maximum Price">
                                                <option value="">Max Price</option>
                                                <option value="50000">S$ 50,000</option>
                                                <option value="80000">S$ 80,000</option>
                                                <option value="100000">S$ 100,000</option>
                                                <option value="130000">S$ 130,000</option>
                                                <option value="160000">S$ 160,000</option>
                                                <option value="200000">S$ 200,000</option>
                                                <option value="300000">S$ 300,000</option>
                                            </select>
                                        </div>

                                        <div class="col-12 col-md-auto">
                                            <button type="submit" class="btn btn-sgcar w-100 px-4 py-2">
                                                <span class="material-icons btn-icon" aria-hidden="true">search</span>
                                                Search Cars
                                            </button>
                                        </div>

                                    </form>
                                </div>
                            </div>
                            <!-- end hero search card -->

                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- end hero -->


        <!-- brand strip -->
        <section class="brands-section py-5" aria-label="Browse cars by brand">
            <div class="container">
                <h2 class="section-title text-center mb-4">Browse by Brand</h2>
                <div class="brands-grid">

                    <?php
                    $brands = [
                        'Toyota', 'Honda', 'BMW', 'Mercedes-Benz',
                        'Mazda', 'Hyundai', 'Volkswagen', 'Subaru',
                        'Mitsubishi', 'Kia', 'Nissan', 'Audi',
                        'Porsche', 'Lexus', 'Ford',
                    ];
                    foreach ($brands as $brand):
                        $logoFile = strtolower(str_replace(' ', '-', $brand)) . '.png';
                    ?>

                        <a href="listings.php?brand=<?= urlencode($brand) ?>"
                           class="brand-card"
                           aria-label="Browse <?= htmlspecialchars($brand) ?> cars">
                            <div class="brand-icon-wrap">
                                <img src="<?= $root ?>assets/brands/<?= htmlspecialchars($logoFile) ?>"
                                     alt="<?= htmlspecialchars($brand) ?> logo"
                                     class="brand-logo">
                            </div>
                            <span class="brand-name"><?= htmlspecialchars($brand) ?></span>
                        </a>

                    <?php endforeach; ?>

                </div>
            </div>
        </section>
        <!-- end brand strip -->


        <!-- stats bar -->
        <section class="stats-section py-4" aria-label="Platform statistics">
            <div class="container">
                <div class="row text-center gy-3">

                    <div class="col-6 col-md-3">
                        <p class="stat-number">10,000+</p>
                        <p class="stat-label">Cars Listed</p>
                    </div>
                    <div class="col-6 col-md-3">
                        <p class="stat-number">5,000+</p>
                        <p class="stat-label">Happy Buyers</p>
                    </div>
                    <div class="col-6 col-md-3">
                        <p class="stat-number">200+</p>
                        <p class="stat-label">Verified Dealers</p>
                    </div>
                    <div class="col-6 col-md-3">
                        <p class="stat-number">4.8 ★</p>
                        <p class="stat-label">Average Rating</p>
                    </div>

                </div>
            </div>
        </section>
        <!-- end stats bar -->


        <!-- featured listings -->
        <section class="featured-section py-5" aria-label="Featured car listings">
            <div class="container">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="section-title mb-0">Featured Listings</h2>
                    <a href="listings.php" class="btn btn-outline-dark btn-sm">View All Cars &rarr;</a>
                </div>

                <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4">

                    <?php if (empty($featured_cars)): ?>
                        <div class="col-12 text-center text-muted py-4">
                            <p>No listings yet — <a href="post-listing.php">be the first to post a car!</a></p>
                        </div>
                    <?php else: ?>
                    <?php foreach ($featured_cars as $car): ?>
                        <?php
                        $imgSrc  = !empty($car['primary_image'])
                            ? htmlspecialchars($car['primary_image'])
                            : 'https://placehold.co/800x500/1e293b/94a3b8?text=No+Photo';
                        $altText = htmlspecialchars($car['year'] . ' ' . $car['brand'] . ' ' . $car['model']);
                        ?>
                        <article class="col">
                            <div class="car-card card h-100">

                                <!-- image + type badge overlay -->
                                <div class="car-card-img-wrap">
                                    <img src="<?= $imgSrc ?>"
                                         alt="<?= $altText ?>"
                                         class="car-card-img"
                                         loading="lazy">
                                    <span class="car-type-badge"><?= htmlspecialchars($car['type']) ?></span>
                                </div>

                                <div class="card-body">
                                    <!-- car name -->
                                    <h3 class="car-card-title">
                                        <?= htmlspecialchars($car['year'] . ' ' . $car['brand'] . ' ' . $car['model']) ?>
                                    </h3>

                                    <!-- price -->
                                    <p class="car-card-price">S$ <?= number_format($car['price']) ?></p>

                                    <!-- specs row: mileage, transmission, fuel -->
                                    <div class="car-card-specs" role="region" aria-label="Car specifications">
                                        <span>
                                            <span class="material-icons spec-icon" aria-hidden="true">speed</span>
                                            <?= number_format($car['mileage']) ?> km
                                        </span>
                                        <span>
                                            <span class="material-icons spec-icon" aria-hidden="true">settings</span>
                                            <?= htmlspecialchars($car['transmission']) ?>
                                        </span>
                                        <span>
                                            <span class="material-icons spec-icon" aria-hidden="true">local_gas_station</span>
                                            <?= htmlspecialchars($car['fuel_type']) ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="card-footer bg-transparent border-0 pb-3">
                                    <a href="car-detail.php?id=<?= (int)$car['car_id'] ?>" class="btn btn-sgcar w-100">View Details</a>
                                </div>

                            </div>
                        </article>

                    <?php endforeach; ?>
                    <?php endif; ?>

                </div>
            </div>
        </section>
        <!-- end featured listings -->


        <!-- how it works -->
        <section class="how-section py-5" aria-label="How sgCar works">
            <div class="container">
                <h2 class="section-title text-center mb-5">How It Works</h2>
                <div class="row g-4 text-center">

                    <div class="col-12 col-md-4">
                        <div class="how-step">
                            <div class="how-icon-wrap">
                                <span class="material-icons how-icon" aria-hidden="true">search</span>
                                <span class="how-number" aria-hidden="true">1</span>
                            </div>
                            <h3 class="how-title">Browse Listings</h3>
                            <p class="how-desc">
                                Search thousands of cars by brand, body type, price or mileage.
                                Filter down to exactly what you need.
                            </p>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="how-step">
                            <div class="how-icon-wrap">
                                <span class="material-icons how-icon" aria-hidden="true">chat</span>
                                <span class="how-number" aria-hidden="true">2</span>
                            </div>
                            <h3 class="how-title">Contact the Seller</h3>
                            <p class="how-desc">
                                Send an enquiry directly to the seller.
                                No middlemen, no hidden fees — direct communication only.
                            </p>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="how-step">
                            <div class="how-icon-wrap">
                                <span class="material-icons how-icon" aria-hidden="true">directions_car</span>
                                <span class="how-number" aria-hidden="true">3</span>
                            </div>
                            <h3 class="how-title">Drive Away Happy</h3>
                            <p class="how-desc">
                                Arrange a viewing, seal the deal, and drive off in your new car.
                                It's that simple.
                            </p>
                        </div>
                    </div>

                </div>
            </div>
        </section>
        <!-- end how it works -->


        <!-- cta banner -->
        <section class="cta-section py-5" aria-label="Sell your car on sgCar">
            <div class="container">
                <div class="cta-card">
                    <div class="cta-content">
                        <h2 class="cta-title">Ready to sell your car?</h2>
                        <p class="cta-subtitle">
                            List your car in minutes and reach thousands of buyers across Singapore.
                            It's free to get started.
                        </p>
                    </div>
                    <a href="<?= isset($_SESSION['user_id']) ? 'post-listing.php' : 'register.php' ?>"
                       class="btn btn-sgcar btn-lg text-nowrap">
                        <span class="material-icons btn-icon" aria-hidden="true">add_circle</span>
                        Sell My Car
                    </a>
                </div>
            </div>
        </section>
        <!-- end cta -->

    </main>

    <?php include "inc/footer.inc.php"; ?>

</body>
</html>
