<?php
// osTicket API configuration.
// Guard: must be included via a legitimate entry point, not called directly.
defined('NIPPONIA_FORM') or die('Direct access not allowed.');

// WARNING: Do not commit this file with real credentials to version control.

define('OSTICKET_API_URL', 'https://ticketing.nipponia.com/api/tickets.json');
define('OSTICKET_API_KEY', 'YOUR_API_KEY_HERE');

// Set to false on local dev with self-signed certificate; true on production.
define('OSTICKET_VERIFY_SSL', false);

// Set to false to skip authentication checks (dev/testing only).
define('REQUIRE_AUTH', false);

// Set to true to allow previewing the result page via GET params (dev only).
// example success: /form/result.php?status=success&ticket_id=306621
// example error: /form/result.php?status=error&detail=HTTP+422%3A+Missing+required+field
define('PREVIEW_RESULT', false);
