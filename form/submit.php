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

// Collect and sanitize required fields.
$_role = (isset($_POST['role']) && $_POST['role'] === 'agent') ? 'agent' : 'client';

if (REQUIRE_AUTH) {
    require_once $_ost_root . '/main.inc.php';

    if ($_role === 'agent') {
        require_once INCLUDE_DIR . 'class.staff.php';
        $thisstaff = StaffAuthenticationBackend::getUser();
        if (!$thisstaff || !$thisstaff->getId() || !$thisstaff->isValid()) {
            http_response_code(403);
            exit('Unauthorized');
        }
    } else {
        require_once INCLUDE_DIR . 'class.client.php';
        $thisclient = UserAuthenticationBackend::getUser();
        if (!$thisclient || !$thisclient->getId() || !$thisclient->isValid()) {
            http_response_code(403);
            exit('Unauthorized');
        }
    }
}

// Collect and sanitize required fields.
$required = ['name', 'email', 'brand', 'vin', 'description', 'km'];
$data = [];
foreach ($required as $field) {
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

if (!in_array($data['brand'], ['Nipponia', 'PGO'], true)) {
    http_response_code(400);
    exit('Invalid brand.');
}

$km = filter_var($data['km'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
if ($km === false) {
    http_response_code(400);
    exit('Invalid kilometers value.');
}
$data['km'] = $km;

// Collect optional fields.
foreach (['model', 'color', 'order_no', 'organization', 'observations', 'parts_required'] as $field) {
    $data[$field] = trim($_POST[$field] ?? '');
}

// Auto-generate ticket subject from model and VIN.
$subject = 'Technical - ' . $data['model'] . ' - ' . $data['vin'];

// Build combined message body from description + optional sections.
$message_parts = ['[DESCRIPTION]', $data['description']];
if ($data['observations'] !== '') {
    $message_parts[] = '';
    $message_parts[] = '[OBSERVATIONS]';
    $message_parts[] = $data['observations'];
}
if ($data['parts_required'] !== '') {
    $message_parts[] = '';
    $message_parts[] = '[PARTS REQUIRED]';
    $message_parts[] = $data['parts_required'];
}
$combined_message = implode("\n", $message_parts);

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
    'name'          => $data['name'],
    'email'         => $data['email'],
    'subject'       => $subject,
    'message'       => 'data:text/plain;base64,' . base64_encode($combined_message),
    'ip'            => $_SERVER['REMOTE_ADDR'] ?? '',
    'source'        => 'API',
    // Custom ticket fields (matched by name in osTicket Form Designer):
    'brand'         => $data['brand'],
    'vin'           => $data['vin'],
    'model'         => $data['model'],
    'color'         => $data['color'],
    'order_no'      => $data['order_no'],
    'km'            => $data['km'],
    'attachments'   => $attachments,
];

if (!empty($_POST['topicId']) && ctype_digit((string) $_POST['topicId'])) {
    $payload['topicId'] = (int) $_POST['topicId'];
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_result_url = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/') . '/result.php';

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

if (!$curlError && $statusCode === 201) {
    $_SESSION['form_flash'] = ['status' => 'success', 'ticket_id' => trim($response)];
} else {
    $error_detail = $curlError ?: ('HTTP ' . $statusCode . ': ' . trim($response));
    $_SESSION['form_flash'] = ['status' => 'error', 'detail' => $error_detail];
}

header('Location: ' . $_result_url . ($_role === 'agent' ? '?role=agent' : ''));
exit;
