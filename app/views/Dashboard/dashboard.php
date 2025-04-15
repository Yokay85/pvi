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
    <title>Dashboard</title>

</head>

<body>
<a class="skipmain" href="#header" target="_self">
    <h1>Student manager</h1>
</a>

<header id="header">
    <div class="header">
        <div class="logo">
            <button class="cms-btn" onclick="window.location.href='./index.html'">CMS</button>
        </div>
        <div class="user-controls">
            <div class="bell" id="bell">
                <svg xmlns="http://www.w3.org/2000/svg" height="40px" viewBox="0 -960 960 960" width="25px"
                     fill="#ffffff">
                    <path
                            d="M160-200v-66.67h80v-296q0-83.66 49.67-149.5Q339.33-778 420-796v-24q0-25 17.5-42.5T480-880q25 0 42.5 17.5T540-820v24q80.67 18 130.33 83.83Q720-646.33 720-562.67v296h80V-200H160Zm320-301.33ZM480-80q-33 0-56.5-23.5T400-160h160q0 33-23.5 56.5T480-80ZM306.67-266.67h346.66v-296q0-72-50.66-122.66Q552-736 480-736t-122.67 50.67q-50.66 50.66-50.66 122.66v296Z" />
                </svg>
                <span class="notification-dot" id="notificationDot"></span>

                <div class="notification-popup" id="notificationPopup">
                    <div class="notification-item">
                        <div class="user-info-notification">
                            <svg xmlns="http://www.w3.org/2000/svg" height="40px" viewBox="0 -960 960 960"
                                 width="40px" fill="#e3e3e3">
                                <path
                                        d="M480-480.67q-66 0-109.67-43.66Q326.67-568 326.67-634t43.66-109.67Q414-787.33 480-787.33t109.67 43.66Q633.33-700 633.33-634t-43.66 109.67Q546-480.67 480-480.67ZM160-160v-100q0-36.67 18.5-64.17T226.67-366q65.33-30.33 127.66-45.5 62.34-15.17 125.67-15.17t125.33 15.5q62 15.5 127.28 45.3 30.54 14.42 48.96 41.81Q800-296.67 800-260v100H160Zm66.67-66.67h506.66V-260q0-14.33-8.16-27-8.17-12.67-20.5-19-60.67-29.67-114.34-41.83Q536.67-360 480-360t-111 12.17Q314.67-335.67 254.67-306q-12.34 6.33-20.17 19-7.83 12.67-7.83 27v33.33ZM480-547.33q37 0 61.83-24.84Q566.67-597 566.67-634t-24.84-61.83Q517-720.67 480-720.67t-61.83 24.84Q393.33-671 393.33-634t24.84 61.83Q443-547.33 480-547.33Zm0-86.67Zm0 407.33Z" />
                            </svg>
                            <strong>Студент 1</strong>
                        </div>

                        <div>
                            <p>Ваше повідомлення тут.</p>
                        </div>
                    </div>
                    <div class="notification-item">
                        <div class="user-info-notification">
                            <svg xmlns="http://www.w3.org/2000/svg" height="40px" viewBox="0 -960 960 960"
                                 width="40px" fill="#e3e3e3">
                                <path
                                        d="M480-480.67q-66 0-109.67-43.66Q326.67-568 326.67-634t43.66-109.67Q414-787.33 480-787.33t109.67 43.66Q633.33-700 633.33-634t-43.66 109.67Q546-480.67 480-480.67ZM160-160v-100q0-36.67 18.5-64.17T226.67-366q65.33-30.33 127.66-45.5 62.34-15.17 125.67-15.17t125.33 15.5q62 15.5 127.28 45.3 30.54 14.42 48.96 41.81Q800-296.67 800-260v100H160Zm66.67-66.67h506.66V-260q0-14.33-8.16-27-8.17-12.67-20.5-19-60.67-29.67-114.34-41.83Q536.67-360 480-360t-111 12.17Q314.67-335.67 254.67-306q-12.34 6.33-20.17 19-7.83 12.67-7.83 27v33.33ZM480-547.33q37 0 61.83-24.84Q566.67-597 566.67-634t-24.84-61.83Q517-720.67 480-720.67t-61.83 24.84Q393.33-671 393.33-634t24.84 61.83Q443-547.33 480-547.33Zm0-86.67Zm0 407.33Z" />
                            </svg>
                            <strong>Студент 2</strong>
                        </div>

                        <div>
                            <p>Інше повідомлення тут.</p>
                        </div>
                    </div>
                    <div class="notification-item">
                        <div class="user-info-notification">
                            <svg xmlns="http://www.w3.org/2000/svg" height="40px" viewBox="0 -960 960 960"
                                 width="40px" fill="#e3e3e3">
                                <path
                                        d="M480-480.67q-66 0-109.67-43.66Q326.67-568 326.67-634t43.66-109.67Q414-787.33 480-787.33t109.67 43.66Q633.33-700 633.33-634t-43.66 109.67Q546-480.67 480-480.67ZM160-160v-100q0-36.67 18.5-64.17T226.67-366q65.33-30.33 127.66-45.5 62.34-15.17 125.67-15.17t125.33 15.5q62 15.5 127.28 45.3 30.54 14.42 48.96 41.81Q800-296.67 800-260v100H160Zm66.67-66.67h506.66V-260q0-14.33-8.16-27-8.17-12.67-20.5-19-60.67-29.67-114.34-41.83Q536.67-360 480-360t-111 12.17Q314.67-335.67 254.67-306q-12.34 6.33-20.17 19-7.83 12.67-7.83 27v33.33ZM480-547.33q37 0 61.83-24.84Q566.67-597 566.67-634t-24.84-61.83Q517-720.67 480-720.67t-61.83 24.84Q393.33-671 393.33-634t24.84 61.83Q443-547.33 480-547.33Zm0-86.67Zm0 407.33Z" />
                            </svg>
                            <strong>Студент 3</strong>
                        </div>

                        <div>
                            <p>Ще одне повідомлення тут.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="profile">
                <img src="<?= URL_ROOT ?>/public/sourse/avatar.png" alt="Avatar">
                <span style="font-family: 'Roboto', sans-serif;">PATREGO</span>
                <div class="profile-popup">
                    <button>Profile</button>
                    <button>Log Out</button>
                </div>
            </div>
        </div>
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
                <li><a href="<?= URL_ROOT ?>/public/index.php?action=dashboard"style="font-weight: bold">Dashboard</a></li>
                <li><a href="<?= URL_ROOT ?>/public/index.php?action=index">Students</a></li>
                <li><a href="<?= URL_ROOT ?>/public/index.php?action=task">Tasks</a></li>
            </ul>
        </nav>
    </aside>

    <section class="main-content">
        <div class="table-controls">
            <h2>Dashboard</h2>
        </div>
    </section>
</main>

<script src="scripts/nottification.js"></script>
<script src="scripts/burger-menu.js"></script>
</body>

</html>