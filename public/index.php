<?php

// Completely suppress all deprecated warnings and notices
error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set('display_errors', '0');
ini_set('log_errors', '0');
ini_set('error_log', '/dev/null');

// Suppress assert warnings completely
ini_set('assert.warning', '0');
ini_set('assert.exception', '0');

// Set custom error handler to suppress all deprecated warnings
set_error_handler(function($severity, $message, $file, $line) {
    if ($severity === E_DEPRECATED || 
        $severity === E_USER_DEPRECATED || 
        $severity === E_STRICT || 
        $severity === E_NOTICE || 
        $severity === E_USER_NOTICE ||
        strpos($message, 'deprecated') !== false ||
        strpos($message, 'Deprecated') !== false ||
        strpos($message, 'E_STRICT') !== false) {
        return true;
    }
    return false;
}, E_ALL);

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
