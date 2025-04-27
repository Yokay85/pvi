<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CMS</title>
    <link rel="stylesheet" href="<?= URL_ROOT ?>/public/styles/header.css">
    <link rel="stylesheet" href="<?= URL_ROOT ?>/public/styles/login.css">
</head>
<body>

<header id="header">
    <div class="header">
        <div class="logo">
             <button class="cms-btn" onclick="window.location.href='<?= URL_ROOT ?>/public/index.php'">CMS</button>
        </div>
    </div>
</header>

<div class="login-container">
    <h2>Login</h2>

    <?php if (isset($error) && $error): ?>
        <div class="error-message"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="<?= URL_ROOT ?>/public/index.php?action=login" method="POST">
        <div class="form-group">
            <label for="identifier">Username or Email</label>
            <input type="text" id="identifier" name="identifier" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="login-btn">Login</button>
    </form>
</div>

</body>
</html>
