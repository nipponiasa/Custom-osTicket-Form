<?php
// Utility functions for the custom form.
defined('NIPPONIA_FORM') or die('Direct access not allowed.');

// Encrypt a value using AES-256-CBC. Returns base64-encoded IV + ciphertext.
// The key is derived from OSTICKET_API_KEY (must be defined before calling).
function encryptValue(string $value): string {
    $key = substr(hash('sha256', OSTICKET_API_KEY, true), 0, 32);
    $iv  = random_bytes(16);
    $enc = openssl_encrypt($value, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    return base64_encode($iv . $enc);
}

// Decrypt a value previously encrypted with encryptValue. Returns '' on failure.
function decryptValue(string $encrypted): string {
    $key  = substr(hash('sha256', OSTICKET_API_KEY, true), 0, 32);
    $data = base64_decode($encrypted, true);
    if ($data === false || strlen($data) < 17) {
        return '';
    }
    $iv  = substr($data, 0, 16);
    $enc = substr($data, 16);
    return openssl_decrypt($enc, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv) ?: '';
}
