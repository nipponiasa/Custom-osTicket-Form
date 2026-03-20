<?php
// Initialization entry point for the custom form.
// Loaded by form.php; configures auth, session, and translations.

define('NIPPONIA_FORM', true);

require_once __DIR__ . '/config.php';

if (REQUIRE_AUTH) {
    require __DIR__ . '/../secure.inc.php';
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/translations.php';

$lang = form_resolve_language();
form_load_language($lang);
