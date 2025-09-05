<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function boot(): void
    {
        // Completely suppress all deprecated warnings, notices and strict warnings
        error_reporting(E_ERROR | E_WARNING | E_PARSE);
        ini_set('display_errors', '0');
        ini_set('log_errors', '0');
        ini_set('error_log', '/dev/null');
        
        // Suppress assert warnings completely
        ini_set('assert.warning', '0');
        ini_set('assert.exception', '0');
        assert_options(ASSERT_WARNING, 0);
        assert_options(ASSERT_BAIL, 0);
        
        // Set custom error handler to suppress all deprecated and strict warnings
        set_error_handler(function($severity, $message, $file, $line) {
            // Suppress deprecated, strict, and notice warnings
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
        
        parent::boot();
    }
}
