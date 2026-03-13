<!--
 * left navigation sidebar for all admin pages
 *
 * included inside the Bootstrap row on every admin page
 * highlights the active link by comparing the current filename
-->
<?php $current = basename($_SERVER['PHP_SELF']); ?>

<aside class="col-12 col-md-3 col-xl-2">
    <div class="list-group list-group-flush admin-sidebar mb-4">

        <a href="index.php"
           class="list-group-item list-group-item-action d-flex align-items-center gap-2
                  <?= $current === 'index.php' ? 'active' : '' ?>">
            <span class="material-icons small" aria-hidden="true">dashboard</span>
            Dashboard
        </a>

        <a href="manage-listings.php"
           class="list-group-item list-group-item-action d-flex align-items-center gap-2
                  <?= $current === 'manage-listings.php' ? 'active' : '' ?>">
            <span class="material-icons small" aria-hidden="true">directions_car</span>
            Listings
        </a>

        <a href="manage-enquiries.php"
           class="list-group-item list-group-item-action d-flex align-items-center gap-2
                  <?= $current === 'manage-enquiries.php' ? 'active' : '' ?>">
            <span class="material-icons small" aria-hidden="true">mail</span>
            Enquiries
        </a>

        <a href="manage-users.php"
           class="list-group-item list-group-item-action d-flex align-items-center gap-2
                  <?= $current === 'manage-users.php' ? 'active' : '' ?>">
            <span class="material-icons small" aria-hidden="true">group</span>
            Users
        </a>

        <a href="../index.php"
           class="list-group-item list-group-item-action d-flex align-items-center gap-2 text-muted">
            <span class="material-icons small" aria-hidden="true">arrow_back</span>
            Back to Site
        </a>

    </div>
</aside>
