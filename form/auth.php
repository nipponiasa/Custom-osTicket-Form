<?php
// Authentication helpers — pure functions, no side effects on load.
// Callers must bootstrap osTicket (main.inc.php) before calling these.
defined('NIPPONIA_FORM') or die('Direct access not allowed.');

// Returns a valid Staff object, or null if unauthenticated/invalid.
function form_auth_get_agent(): ?object {
    require_once INCLUDE_DIR . 'class.staff.php';
    $staff = StaffAuthenticationBackend::getUser();
    return ($staff && $staff->getId() && $staff->isValid()) ? $staff : null;
}

// Returns a valid client User object, or null if unauthenticated/invalid.
function form_auth_get_client(): ?object {
    require_once INCLUDE_DIR . 'class.client.php';
    $client = UserAuthenticationBackend::getUser();
    return ($client && $client->getId() && $client->isValid()) ? $client : null;
}
