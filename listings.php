<?php
// shows the browse cars page. reads filter values from the url and queries matching available listings
session_start();
$root      = "";
$pageTitle = "Browse Cars — sgCar";

// read filter values from the url
$filter_brand = isset($_GET['brand'])        ? trim($_GET['brand'])        : '';
$filter_type  = isset($_GET['type'])         ? trim($_GET['type'])         : '';
$filter_trans = isset($_GET['transmission']) ? trim($_GET['transmission']) : '';
$filter_fuel  = isset($_GET['fuel'])         ? trim($_GET['fuel'])         : '';
$filter_max   = isset($_GET['max_price'])    ? (int)$_GET['max_price']     : 0;
$filter_sort  = isset($_GET['sort'])         ? trim($_GET['sort'])         : 'newest';
$filter_min_year    = isset($_GET['min_year'])     ? (int)$_GET['min_year']     : 0;
$filter_max_year    = isset($_GET['max_year'])      ? (int)$_GET['max_year']     : 0;
$filter_max_mileage = isset($_GET['max_mileage'])   ? (int)$_GET['max_mileage']  : 0;

// connect to the database
include "inc/db.inc.php";

// build the WHERE clause from whichever filters are active
$conditions = ["c.status = 'available'"];
$types      = "";
$params     = [];

if ($filter_brand !== '') {
    $conditions[] = "c.brand = ?";
    $types       .= "s";
    $params[]     = $filter_brand;
}
if ($filter_type !== '') {
    $conditions[] = "c.type = ?";
    $types       .= "s";
    $params[]     = $filter_type;
}
if ($filter_trans !== '') {
    $conditions[] = "c.transmission = ?";
    $types       .= "s";
    $params[]     = $filter_trans;
}
if ($filter_fuel !== '') {
    $conditions[] = "c.fuel_type = ?";
    $types       .= "s";
    $params[]     = $filter_fuel;
}
if ($filter_max > 0) {
    $conditions[] = "c.price <= ?";
    $types       .= "i";
    $params[]     = $filter_max;
}
if ($filter_min_year > 0) {
    $conditions[] = "c.year >= ?";
    $types       .= "i";
    $params[]     = $filter_min_year;
}
if ($filter_max_year > 0) {
    $conditions[] = "c.year <= ?";
    $types       .= "i";
    $params[]     = $filter_max_year;
}
if ($filter_max_mileage > 0) {
    $conditions[] = "c.mileage <= ?";
    $types       .= "i";
    $params[]     = $filter_max_mileage;
}

$whereClause = implode(" AND ", $conditions);

// order by uses a whitelist so user input never touches the sql. unknown sort keys fall back to newest
$sortMap = [
    'newest'     => 'c.created_at DESC',
    'price_asc'  => 'c.price ASC',
    'price_desc' => 'c.price DESC',
    'mileage'    => 'c.mileage ASC',
];
$orderBy = $sortMap[$filter_sort] ?? $sortMap['newest'];

// run the query
// left join so cars without photos still show up in the results
$sql = "
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
        c.color,
        ci.image_path AS primary_image
    FROM cars c
    LEFT JOIN car_images ci
        ON c.car_id = ci.car_id AND ci.is_primary = 1
    WHERE $whereClause
    ORDER BY $orderBy
";

$stmt = $conn->prepare($sql);

// only bind params if there are active filters
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result    = $stmt->get_result();
$cars      = $result->fetch_all(MYSQLI_ASSOC);
$car_count = count($cars);

$stmt->close();
$conn->close();

// helpers — keep filter values selected after the page reloads
function keepSelected($val, $filter) {
    return ($val === $filter) ? 'selected' : '';
}
function keepChecked($val, $filter) {
    return ($val === $filter) ? 'checked' : '';
}
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

        <!-- page header -->
        <div class="bg-light border-bottom py-3">
            <div class="container">
                <h1 class="h4 fw-bold mb-0">Browse Cars</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Browse Cars</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="container py-4">
            <div class="row g-4">


                <!-- filter sidebar
                     uses GET so filter choices stay in the url.
                     values persist because we read $_GET and pre-select. -->
                <aside class="col-12 col-md-3" aria-label="Filter options">

                    <!-- mobile toggle — js in main.js handles this click -->
                    <button id="filterToggleBtn"
                            class="filter-toggle-btn btn btn-outline-dark mb-3"
                            aria-expanded="true"
                            aria-controls="filterSidebar">
                        Hide Filters
                    </button>

                    <div id="filterSidebar" class="filter-sidebar">

                        <form method="get" action="listings.php" aria-label="Filter cars">
                            <h6>Brand</h6>
                            <select name="brand" class="form-select form-select-sm mb-3">
                                <option value="">All Brands</option>
                                <?php foreach (['Toyota','Honda','BMW','Mercedes-Benz','Mazda','Hyundai','Volkswagen','Subaru','Mitsubishi','Kia','Nissan','Audi','Porsche','Lexus','Ford','Other'] as $b): ?>
                                    <option <?= keepSelected($b, $filter_brand) ?>><?= htmlspecialchars($b) ?></option>
                                <?php endforeach; ?>
                            </select>

                            <hr class="filter-divider">
                            <h6>Body Type</h6>
                            <select name="type" class="form-select form-select-sm mb-3">
                                <option value="">All Types</option>
                                <?php foreach (['Sedan','SUV','Hatchback','MPV','Coupe','Electric','Others'] as $t): ?>
                                    <option <?= keepSelected($t, $filter_type) ?>><?= htmlspecialchars($t) ?></option>
                                <?php endforeach; ?>
                            </select>

                            <hr class="filter-divider">
                            <h6>Transmission</h6>
                            <div class="d-flex flex-column gap-1 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="transmission" id="trans-any" value="" <?= keepChecked('', $filter_trans) ?>>
                                    <label class="form-check-label" for="trans-any">Any</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="transmission" id="trans-auto" value="Auto" <?= keepChecked('Auto', $filter_trans) ?>>
                                    <label class="form-check-label" for="trans-auto">Automatic</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="transmission" id="trans-manual" value="Manual" <?= keepChecked('Manual', $filter_trans) ?>>
                                    <label class="form-check-label" for="trans-manual">Manual</label>
                                </div>
                            </div>

                            <hr class="filter-divider">
                            <h6>Fuel Type</h6>
                            <select name="fuel" class="form-select form-select-sm mb-3">
                                <option value="">All Fuel Types</option>
                                <?php foreach (['Petrol','Diesel','Electric','Hybrid'] as $f): ?>
                                    <option <?= keepSelected($f, $filter_fuel) ?>><?= htmlspecialchars($f) ?></option>
                                <?php endforeach; ?>
                            </select>

                            <hr class="filter-divider">
                            <h6>Max Budget</h6>
                            <select name="max_price" class="form-select form-select-sm mb-3">
                                <option value="">Any Budget</option>
                                <?php
                                $budgets = [80000, 100000, 130000, 160000, 200000];
                                foreach ($budgets as $val): ?>
                                    <option value="<?= $val ?>" <?= ($filter_max === $val) ? 'selected' : '' ?>>
                                        Under S$ <?= number_format($val) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <hr class="filter-divider">
                            <h6>Year</h6>
                            <div class="row g-1 mb-3">
                                <div class="col-6">
                                    <select name="min_year" class="form-select form-select-sm">
                                        <option value="">From</option>
                                        <?php for ($y = date('Y'); $y >= 2005; $y--): ?>
                                            <option value="<?= $y ?>" <?= ($filter_min_year === $y) ? 'selected' : '' ?>><?= $y ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <select name="max_year" class="form-select form-select-sm">
                                        <option value="">To</option>
                                        <?php for ($y = date('Y'); $y >= 2005; $y--): ?>
                                            <option value="<?= $y ?>" <?= ($filter_max_year === $y) ? 'selected' : '' ?>><?= $y ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>

                            <hr class="filter-divider">
                            <h6>Max Mileage</h6>
                            <select name="max_mileage" class="form-select form-select-sm mb-3">
                                <option value="">Any Mileage</option>
                                <?php
                                $mileages = [10000, 30000, 50000, 80000, 100000];
                                foreach ($mileages as $val): ?>
                                    <option value="<?= $val ?>" <?= ($filter_max_mileage === $val) ? 'selected' : '' ?>>
                                        Under <?= number_format($val) ?> km
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-sgcar">Apply Filters</button>
                                <a href="listings.php" class="btn btn-outline-secondary btn-sm">Clear All</a>
                            </div>

                        </form>
                    </div><!-- end filter sidebar -->
                </aside>


                <!-- listings grid -->
                <section class="col-12 col-md-9" aria-label="Car listings">

                    <!-- sort bar -->
                    <div class="listings-sort-bar">
                        <span class="listings-count">
                            <strong><?= $car_count ?></strong> car<?= $car_count !== 1 ? 's' : '' ?> found
                            <?= $filter_brand ? ' for <strong>' . htmlspecialchars($filter_brand) . '</strong>' : '' ?>
                        </span>

                        <form method="get" action="listings.php" class="d-flex align-items-center gap-2">
                            <!-- carry active filters forward when sort changes -->
                            <?php foreach (['brand'=>$filter_brand,'type'=>$filter_type,'transmission'=>$filter_trans,'fuel'=>$filter_fuel,'max_price'=>$filter_max,'min_year'=>$filter_min_year,'max_year'=>$filter_max_year,'max_mileage'=>$filter_max_mileage] as $k=>$v): ?>
                                <?php if ($v): ?><input type="hidden" name="<?= $k ?>" value="<?= htmlspecialchars($v) ?>"><?php endif; ?>
                            <?php endforeach; ?>

                            <label for="sort-select" class="form-label mb-0 small text-muted text-nowrap">Sort by:</label>
                            <select id="sort-select" name="sort" class="form-select form-select-sm" onchange="this.form.submit()" style="width:auto;">
                                <option value="newest"     <?= keepSelected('newest',     $filter_sort) ?>>Newest First</option>
                                <option value="price_asc"  <?= keepSelected('price_asc',  $filter_sort) ?>>Price: Low to High</option>
                                <option value="price_desc" <?= keepSelected('price_desc', $filter_sort) ?>>Price: High to Low</option>
                                <option value="mileage"    <?= keepSelected('mileage',    $filter_sort) ?>>Lowest Mileage</option>
                            </select>
                        </form>
                    </div>

                    <!-- car cards -->
                    <?php if ($car_count === 0): ?>
                        <div class="text-center py-5 text-muted">
                            <span class="material-icons" style="font-size:3rem;">search_off</span>
                            <p class="mt-2">No cars match your filters. <a href="listings.php">Clear all filters</a></p>
                        </div>
                    <?php else: ?>
                        <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-3 g-4">
                            <?php foreach ($cars as $car): ?>
                                <?php
                                // use the stored path, or a placeholder if no photo was uploaded
                                $imgSrc = !empty($car['primary_image'])
                                    ? htmlspecialchars($car['primary_image'])
                                    : 'https://placehold.co/800x500/1e293b/94a3b8?text=No+Photo';
                                $altText = htmlspecialchars($car['year'] . ' ' . $car['brand'] . ' ' . $car['model']);
                                ?>
                                <article class="col">
                                    <div class="car-card card h-100">
                                        <div class="car-card-img-wrap">
                                            <img src="<?= $imgSrc ?>"
                                                 alt="<?= $altText ?>"
                                                 class="car-card-img"
                                                 loading="lazy">
                                            <span class="car-type-badge"><?= htmlspecialchars($car['type']) ?></span>
                                        </div>
                                        <div class="card-body">
                                            <h2 class="car-card-title">
                                                <?= htmlspecialchars($car['year'] . ' ' . $car['brand'] . ' ' . $car['model']) ?>
                                            </h2>
                                            <p class="car-card-price">S$ <?= number_format($car['price']) ?></p>
                                            <div class="car-card-specs" aria-label="Car specifications">
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
                                            <a href="car-detail.php?id=<?= (int)$car['car_id'] ?>" class="btn btn-sgcar w-100">
                                                View Details
                                            </a>
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                </section>
                <!-- end listings grid -->

            </div>
        </div>

    </main>

    <?php include "inc/footer.inc.php"; ?>

</body>
</html>
