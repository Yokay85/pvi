<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Author: Hideo Kojima,
    Illustrator: Hideo Kojima, Category: Hideo Kojima, Price: Priceless,
    Length: Genious">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="PVI Project">
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="stylesheet" href="<?= URL_ROOT ?>/public/styles/skipmain.css">
    <link rel="stylesheet" href="<?= URL_ROOT ?>/public/styles/header.css">
    <link rel="stylesheet" href="<?= URL_ROOT ?>/public/styles/profile.css">
    <link rel="stylesheet" href="<?= URL_ROOT ?>/public/styles/notifications.css">
    <link rel="stylesheet" href="<?= URL_ROOT ?>/public/styles/navigation.css">
    <link rel="stylesheet" href="<?= URL_ROOT ?>/public/styles/main.css">
    <link rel="stylesheet" href="<?= URL_ROOT ?>/public/styles/table.css">
    <link rel="stylesheet" href="<?= URL_ROOT ?>/public/styles/modal.css">
    <title>Tasks</title>

</head>

<body>
<a class="skipmain" href="#header" target="_self">
    <h1>Student manager</h1>
</a>

<header id="header">
    <div class="header">
        <div class="logo">
            <button class="cms-btn" onclick="window.location.href='<?= URL_ROOT ?>/public/index.php'">CMS</button> <!-- Corrected link -->
        </div>
         <?php if (isset($_SESSION['user_id'])): ?>
        <div class="user-controls">
            <div class="bell" id="bell">
                <svg xmlns="http://www.w3.org/2000/svg" height="40px" viewBox="0 -960 960 960" width="25px"
                     fill="#ffffff">
                    <path
                        d="M160-200v-66.67h80v-296q0-83.66 49.67-149.5Q339.33-778 420-796v-24q0-25 17.5-42.5T480-880q25 0 42.5 17.5T540-820v24q80.67 18 130.33 83.83Q720-646.33 720-562.67v296h80V-200H160Zm320-301.33ZM480-80q-33 0-56.5-23.5T400-160h160q0 33-23.5 56.5T480-80ZM306.67-266.67h346.66v-296q0-72-50.66-122.66Q552-736 480-736t-122.67 50.67q-50.66 50.66-50.66 122.66v296Z" />
                </svg>
                <span class="notification-dot" id="notificationDot"></span>

                <div class="notification-popup" id="notificationPopup">
                   <!-- ... existing notification items ... -->
                </div>
            </div>
            <div class="profile">
                <img src="<?= URL_ROOT ?>/public/sourse/avatar.png" alt="Avatar">
                 <span style="font-family: 'Roboto', sans-serif;"><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></span> <!-- Display username -->
                <div class="profile-popup">
                    <button>Profile</button>
                    <button onclick="window.location.href='<?= URL_ROOT ?>/public/index.php?action=logout'">Log Out</button> <!-- Logout Link -->
                </div>
            </div>
        </div>
         <?php else: ?>
        <div class="user-controls">
             <button class="login-btn" onclick="window.location.href='<?= URL_ROOT ?>/public/index.php?action=login'">Login</button>
        </div>
        <?php endif; ?>
    </div>
</header>

<main class="content-wrapper">
    <aside class="sidebar">
        <button class="burger-btn" id="burger-btn" aria-label="Main navigation">
            <svg xmlns="http://www.w3.org/2000/svg" height="40px" viewBox="0 -960 960 960" width="40px"
                 fill="#000000">
                <path
                    d="M120-240v-66.67h720V-240H120Zm0-206.67v-66.66h720v66.66H120Zm0-206.66V-720h720v66.67H120Z" />
            </svg>
        </button>

        <nav id="navigation" class="navigation">
            <ul>
                <?php if (isset($_SESSION['user_id'])): // Show Dashboard and Tasks only if logged in ?>
                <li><a href="<?= URL_ROOT ?>/public/index.php?action=dashboard">Dashboard</a></li>
                <?php endif; ?>
                <li><a href="<?= URL_ROOT ?>/public/index.php?action=index">Students</a></li>
                <?php if (isset($_SESSION['user_id'])): // Show Dashboard and Tasks only if logged in ?>
                <li><a href="<?= URL_ROOT ?>/public/index.php?action=task" style="font-weight: bold">Tasks</a></li>
                 <?php endif; ?>
            </ul>
        </nav>
    </aside>

    <section class="main-content">
        <div class="table-controls">
            <h2>Tasks</h2>
        </div>
    </section>
</main>

<div class="modal-content" id="modal">
    <div class="modal-header">
        <h3>Add task</h3>
        <button class="close-btn" aria-label="Close modal" id="close-btn">X</button>
    </div>
    <div class="modal-body">
        <form id="task-form" novalidate>
            <!-- Task form content will go here -->
            <div class="form-buttons">
                <button type="submit" class="submit-btn" id="submit-btn">Save</button>
                <button type="button" class="cancel-btn" id="cancel-btn">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-content" id="confirmModal" style="display: none;">
    <div class="modal-header">
        <h3>Confirm Deletion</h3>
        <button class="close-btn" aria-label="Close confirmation modal" id="close-confirm-btn">X</button>
    </div>
    <div class="modal-body">
        <p id="confirm-message">Are you sure you want to delete this task?</p>
        <div class="form-buttons">
            <button type="button" class="submit-btn" id="confirm-yes-btn">Yes</button>
            <button type="button" class="cancel-btn" id="confirm-no-btn">No</button>
        </div>
    </div>
</div>

<div id="modal-overlay" class="modal-overlay"></div>

<?php if (isset($_SESSION['user_id'])): ?>
<script src="<?= URL_ROOT ?>/public/scripts/nottification.js"></script>
<?php endif; ?>
<script src="<?= URL_ROOT ?>/public/scripts/burger-menu.js"></script>
</body>

</html>