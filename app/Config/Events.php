<?php

namespace Config;

use CodeIgniter\Events\Events;
use CodeIgniter\Exceptions\FrameworkException;
use CodeIgniter\HotReloader\HotReloader;

/*
 * --------------------------------------------------------------------
 * Application Events
 * --------------------------------------------------------------------
 * Events allow you to tap into the execution of the program without
 * modifying or extending core files. This file provides a central
 * location to define your events, though they can always be added
 * at run-time, also, if needed.
 *
 * You create code that can execute by subscribing to events with
 * the 'on()' method. This accepts any form of callable, including
 * Closures, that will be executed when the event is triggered.
 *
 * Example:
 *      Events::on('create', [$myInstance, 'myMethod']);
 */

Events::on('pre_system', static function (): void {
    if (! is_cli()) {
        \App\Libraries\SessionHelper::configureForRequest((string) ($_SERVER['REQUEST_URI'] ?? '/'));
    }

    if (ENVIRONMENT !== 'testing') {
        if (ini_get('zlib.output_compression')) {
            throw FrameworkException::forEnabledZlibOutputCompression();
        }

        while (ob_get_level() > 0) {
            ob_end_flush();
        }

        ob_start(static function ($buffer) {
            if (! is_string($buffer)) {
                return $buffer;
            }

            // A UTF-8 BOM in a file included on every request (Routes.php)
            // is captured here and would be prefixed onto image responses.
            if (str_starts_with($buffer, "\xEF\xBB\xBF")) {
                $buffer = substr($buffer, 3);
            }

            if (stripos($buffer, '<head>') !== false && ! preg_match('/rel=["\'](?:shortcut )?icon["\']/i', $buffer)) {
                $icon = '<link rel="icon" type="image/png" href="/bacolod.png">' . "\n"
                    . '    <link rel="apple-touch-icon" href="/bacolod.png">';
                $buffer = preg_replace('/<head>/i', "<head>\n    " . $icon, $buffer, 1);
            }

            return $buffer;
        });
    }

    /*
     * --------------------------------------------------------------------
     * Debug Toolbar Listeners.
     * --------------------------------------------------------------------
     * If you delete, they will no longer be collected.
     */
    if (CI_DEBUG && ! is_cli()) {
        Events::on('DBQuery', 'CodeIgniter\Debug\Toolbar\Collectors\Database::collect');
        service('toolbar')->respond();
        // Hot Reload route - for framework use on the hot reloader.
        if (ENVIRONMENT === 'development') {
            service('routes')->get('__hot-reload', static function (): void {
                (new HotReloader())->run();
            });
        }
    }
});
