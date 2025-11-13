<!doctype html>
<html lang="en">

<head>
    <meta charset="<?= htmlspecialchars($meta['charset'] ?? 'utf-8') ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <meta name="title" content="<?= htmlspecialchars($meta['title']) ?>">
    <meta name="description" content="<?= htmlspecialchars($meta['description']) ?>">
    <meta name="keywords" content="<?= htmlspecialchars($meta['keywords']) ?>">
    <meta name="author" content="<?= htmlspecialchars($meta['author']) ?>">

    <link rel="canonical" href="<?= htmlspecialchars($meta['canonical']) ?>">

    <!-- Theme -->
    <link rel="stylesheet" href="/css/<?= htmlspecialchars(config('THEME')) ?>">
    <link rel="stylesheet" href="/css/style.css">

    <!-- Scripts -->
    <script src="/js/htmx.min.js"></script>

    <title><?= htmlspecialchars($meta['title']) ?></title>

    <!-- Favicon and App Icons -->
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="shortcut icon" href="/favicon.ico">

    <!-- Optional Android PWA icons -->
    <meta name="theme-color" content="#ffffff">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
</head>

<body>
    <main class="container">
        <nav>
            <ul>
                <li>
                    <a href="/" class="lighthouse-brand">
                        <img src="/Logo.png" alt="Lighthouse Logo" class="lighthouse-logo">
                        <strong>Lighthouse</strong>
                        <span class="lighthouse-version">v<?= htmlspecialchars(config('APP_VERSION')) ?></span>
                    </a>
                </li>
            </ul>
            <ul>
                <li><a href="/logout" class="contrast">Logout</a></li>
            </ul>
        </nav>
        <?= $content ?>
    </main>
    <footer class="lighthouse-footer">
        <div class="container">
            <p>&copy; <?= htmlspecialchars(config('APP_NAME')) ?> - <?= date('Y') ?> · 
            Built with PHP <?= PHP_VERSION ?> & Lighthouse Framework</p>
        </div>
    </footer>
</body>

</html>
