<?php
define('NIPPONIA_FORM', true);

// Form submission endpoint — validates input and creates a ticket via the osTicket API.

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

// Quick origin check before bootstrapping osTicket.
$_request_host  = $_SERVER['HTTP_HOST'];
$_origin_host   = isset($_SERVER['HTTP_ORIGIN'])  ? parse_url($_SERVER['HTTP_ORIGIN'],  PHP_URL_HOST) : null;
$_referer_host  = isset($_SERVER['HTTP_REFERER']) ? parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) : null;
$_trusted_host  = $_origin_host ?? $_referer_host;

if (!$_trusted_host || $_trusted_host !== $_request_host) {
    http_response_code(403);
    exit('Forbidden');
}

// Bootstrap osTicket (session, DB, auth classes) without triggering
// the CSRF middleware that lives in client.inc.php (deferred to Step 8).

$_ost_root = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
require_once __DIR__ . '/config.php';

if (REQUIRE_AUTH) {
    require_once $_ost_root . '/main.inc.php';
    require_once INCLUDE_DIR . 'class.client.php';

    $thisclient = UserAuthenticationBackend::getUser();
    if (!$thisclient || !$thisclient->getId() || !$thisclient->isValid()) {
        http_response_code(403);
        exit('Unauthorized');
    }
}

// Collect and sanitize required fields.
$fields = ['name', 'email', 'subject', 'message', 'vin'];
$data = [];
foreach ($fields as $field) {
    $value = trim($_POST[$field] ?? '');
    if ($value === '') {
        http_response_code(400);
        exit('Missing required field: ' . $field);
    }
    $data[$field] = $value;
}

if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    exit('Invalid email address.');
}

// Build attachments array in RFC 2397 format expected by the osTicket API.
$attachments = [];
if (!empty($_FILES['attachments']['name'][0])) {
    foreach ($_FILES['attachments']['tmp_name'] as $i => $tmpName) {
        if ($_FILES['attachments']['error'][$i] !== UPLOAD_ERR_OK) {
            continue;
        }
        $originalName = basename($_FILES['attachments']['name'][$i]);
        $mimeType     = mime_content_type($tmpName) ?: 'application/octet-stream';
        $encoded      = base64_encode(file_get_contents($tmpName));
        $attachments[] = [$originalName => 'data:' . $mimeType . ';base64,' . $encoded];
    }
}

$payload = [
    'name'        => $data['name'],
    'email'       => $data['email'],
    'subject'     => $data['subject'],
    'message'     => 'data:text/plain,' . rawurlencode($data['message']),
    'vin'         => $data['vin'],
    'ip'          => $_SERVER['REMOTE_ADDR'] ?? '',
    'source'      => 'API',
    'attachments' => $attachments,
];

$ch = curl_init(OSTICKET_API_URL);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'X-API-Key: ' . OSTICKET_API_KEY,
    ],
    CURLOPT_SSL_VERIFYPEER => OSTICKET_VERIFY_SSL,
    CURLOPT_SSL_VERIFYHOST => OSTICKET_VERIFY_SSL ? 2 : false,
]);

$response   = curl_exec($ch);
$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError  = curl_error($ch);
curl_close($ch);

if ($curlError) {
    http_response_code(502);
    exit('Connection error: ' . $curlError);
}

if ($statusCode === 201) {
    // $response contains the external ticket id.
    http_response_code(201);
    exit('Ticket created: ' . $response);
}

http_response_code($statusCode ?: 500);
exit('API error: ' . $response);
