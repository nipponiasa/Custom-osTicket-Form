<?php
// Must be included from within the form, not accessed directly.
if (!defined('NIPPONIA_FORM')) { http_response_code(403); exit; }

$_res = htmlspecialchars($base, ENT_QUOTES, 'UTF-8') . '/form/resources';
?><!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') ?>" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= t('page.title') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/form/css/style.css">

    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> -->
    <script src="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/form/js/form.js" defer></script>
</head>
<body class="d-flex flex-column min-vh-100">
    <header class="site-header">
        <div class="container p-2 d-flex align-items-center justify-content-between">
                    <a href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/new.php">
                        <img src="<?= $_res ?>/nipponia-logo.png" alt="Nipponia" class="site-logo">
                    </a>
                    <div class="lang-flags d-flex align-items-center gap-2">
                        <img src="<?= $_res ?>/gb.svg" alt="English" class="flag-icon" title="English">
                        <img src="<?= $_res ?>/es.svg" alt="Español" class="flag-icon" title="Español">
                    </div>
        </div>
    </header>
