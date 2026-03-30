<?php
// Initialization entry point for the custom form.
// Loaded by form.php and result.php; configures auth, session, and translations.

define('NIPPONIA_FORM', true);

require_once __DIR__ . '/config.php';

// FORM_ROLE is defined by the entry point (new.php or result.php) before this file is loaded.
if (!defined('FORM_ROLE')) {
    define('FORM_ROLE', 'client');
}

if (REQUIRE_AUTH) {
    if (FORM_ROLE === 'agent') {
        // Agent auth: bootstrap osTicket and verify a valid staff session.
        require_once __DIR__ . '/../main.inc.php';
        require_once __DIR__ . '/auth.php';

        $thisstaff = form_auth_get_agent();
        if (!$thisstaff) {
            $_SESSION['_staff']['auth']['dest'] = '/' . ltrim($_SERVER['REQUEST_URI'], '/');
            header('Location: ' . ROOT_PATH . 'scp/login.php');
            exit;
        }
        $thisstaff->refreshSession();
    } else {
        // Client auth: use osTicket's native client authentication.
        require __DIR__ . '/../secure.inc.php';
    }
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/translations.php';

$lang = form_resolve_language();
form_load_language($lang);
