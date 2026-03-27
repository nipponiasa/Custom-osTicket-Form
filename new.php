<?php
// Detect role before loading the form; auth is handled inside form-bootstrap.php.
$_form_role = (isset($_GET['role']) && $_GET['role'] === 'agent') ? 'agent' : 'client';
define('FORM_ROLE', $_form_role);

require __DIR__ . '/form/form.php';
