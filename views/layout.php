<!DOCTYPE html>
<html dir="<?= ($_SESSION['lang'] ?? '') === 'ar' ? 'rtl' : 'ltr' ?>" lang="<?= $_SESSION['lang'] ?? 'en' ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? $t->trans('SITE_TITLE') ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Global Styles -->
    <link rel="stylesheet" href="/assets/css/style.css">

    <?php if (!empty($extra_css)): ?>
        <?php foreach ($extra_css as $css): ?>
            <link rel="stylesheet" href="<?= \htmlspecialchars($css) ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>

<body>
    <?php if ($layout): ?>
        <?php include 'partials/header.phtml'; ?>
    <?php endif; ?>
    <main>
        <?= $content ?? '' ?>
    </main>
    <?php if ($layout): ?>
        <?php include 'partials/footer.phtml'; ?>
    <?php endif; ?>

    <?php if (!empty($extra_js)): ?>
        <?php foreach ($extra_js as $js): ?>
            <script src="<?= \htmlspecialchars($js) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

</body>

</html>