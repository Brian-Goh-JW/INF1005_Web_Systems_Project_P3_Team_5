<!-- shared <head>. change dependencies here to update them everywhere -->
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="sgCar - Buy and Sell Cars in Singapore">
<meta name="author" content="sgCar Team">

<!-- bootstrap 5 CSS — handles our grid system and base components -->
<link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css"
    integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9"
    crossorigin="anonymous">

<!-- google Material Icons — used for the small icons in the nav and footer -->
<link rel="stylesheet"
    href="https://fonts.googleapis.com/icon?family=Material+Icons">

<!-- inter font from Google Fonts — clean, modern, works well for a car marketplace -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">

<!-- our own custom CSS — loaded after Bootstrap so our styles can override it where needed -->
<link rel="stylesheet" href="<?= $root ?>css/main.css">

<!-- bootstrap JS — needed for the mobile menu toggle and dropdowns to work -->
<script defer
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm"
    crossorigin="anonymous"></script>

<!-- our own custom JS — defer means it waits for the page to fully load before running -->
<script defer src="<?= $root ?>js/main.js"></script>
