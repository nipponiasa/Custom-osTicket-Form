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

if (REQUIRE_AUTH) {
    $role = (isset($_GET['role']) && $_GET['role'] === 'agent') ? 'agent' : 'client';

    require_once __DIR__ . '/../main.inc.php';

    if ($role === 'agent') {
        require_once INCLUDE_DIR . 'class.staff.php';
        $thisstaff = StaffAuthenticationBackend::getUser();
        if (!$thisstaff || !$thisstaff->getId() || !$thisstaff->isValid()) {
            http_response_code(401);
            echo json_encode(['error' => 'Authentication required']);
            exit;
        }
    } else {
        require_once INCLUDE_DIR . 'class.client.php';
        $thisclient = UserAuthenticationBackend::getUser();
        if (!$thisclient || !$thisclient->getId() || !$thisclient->isValid()) {
            http_response_code(401);
            echo json_encode(['error' => 'Authentication required']);
            exit;
        }
    }
}

$body = json_decode(file_get_contents('php://input'), true);
$vin  = trim($body['vin'] ?? '');

if ($vin === '') {
    http_response_code(400);
    echo json_encode(['error' => 'VIN is required']);
    exit;
}

// TODO: Replace with real ERP lookup.
echo json_encode([
    'model'    => 'dummy-model',
    'color'    => 'dummy-color',
    'order_no' => 'dummy-order_no',
]);
