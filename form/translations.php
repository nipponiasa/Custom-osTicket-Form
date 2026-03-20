<?php
// Translation layer for the custom form.
// To add a new language: add its code to $form_supported_langs and create form/lang/{code}.php.

$form_supported_langs = ['en', 'es'];
$form_translations    = [];
$form_lang_current    = 'en';

// Loads translation strings for the given language code into global state.
function form_load_language(string $lang): void {
    global $form_translations, $form_lang_current, $form_supported_langs;

    if (!in_array($lang, $form_supported_langs, true)) {
        $lang = 'en';
    }

    $file = __DIR__ . '/lang/' . $lang . '.php';
    $form_translations = file_exists($file) ? require $file : [];
    $form_lang_current = $lang;
}

// Returns the translated, HTML-escaped string for $key.
// Supports {placeholder} substitution via the $params array.
function t(string $key, array $params = []): string {
    global $form_translations;

    $text = $form_translations[$key] ?? $key;

    foreach ($params as $name => $value) {
        $text = str_replace('{' . $name . '}', $value, $text);
    }

    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

// Resolves the active language from GET param or session; persists choice to session.
function form_resolve_language(): string {
    global $form_supported_langs;

    if (isset($_GET['lang']) && in_array($_GET['lang'], $form_supported_langs, true)) {
        $_SESSION['form_lang'] = $_GET['lang'];
    }

    return $_SESSION['form_lang'] ?? 'en';
}
