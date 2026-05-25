<?php

require_once __DIR__ . '/main_helper.php';

if (! function_exists('admin_url')) {
    function admin_url($path): string
    {
        return base_url(trim(ADMIN_CONTROLLER . '/' . ltrim((string) $path, '/'), '/'));
    }
}
