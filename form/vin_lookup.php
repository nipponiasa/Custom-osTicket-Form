<?php
define('NIPPONIA_FORM', true);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Origin check — same approach as submit.php.
$_request_host = $_SERVER['HTTP_HOST'];
$_origin_host  = isset($_SERVER['HTTP_ORIGIN'])  ? parse_url($_SERVER['HTTP_ORIGIN'],  PHP_URL_HOST) : null;
$_referer_host = isset($_SERVER['HTTP_REFERER']) ? parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) : null;
$_trusted_host = $_origin_host ?? $_referer_host;

if (!$_trusted_host || $_trusted_host !== $_request_host) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/utils.php';

$authenticated = true;
if (REQUIRE_AUTH) {
    $role = (isset($_GET['role']) && $_GET['role'] === 'agent') ? 'agent' : 'client';

    require_once __DIR__ . '/../main.inc.php';  // bootstraps osTicket and starts the session
    require_once __DIR__ . '/auth.php';

    $authenticated = ($role === 'agent') ? form_auth_get_agent() : form_auth_get_client();
}

// Session is now available (started by main.inc.php, or start manually if REQUIRE_AUTH is false).
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/translations.php';
form_load_language($_SESSION['form_lang'] ?? 'en');

if (!$authenticated) {
    http_response_code(401);
    echo json_encode(['error' => t('error.auth_required')]);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);
$vin  = trim($body['vin'] ?? '');

if ($vin === '') {
    http_response_code(400);
    echo json_encode(['error' => t('error.vin_required')]);
    exit;
}

// DEV: Set to true to simulate a VIN not found in the ERP.
$mock_not_found = false;

// TODO: Replace with real ERP lookup.
if ($mock_not_found) {
    http_response_code(404);
    echo json_encode(['error' => t('error.vin_not_found')]);
    exit;
}

echo json_encode([
    'model'    => 'dummy-model',
    'color'    => 'dummy-color',
    'order_no' => encryptValue('dummy-order_no'),
]);
