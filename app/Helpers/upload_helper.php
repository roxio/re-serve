<?php

if (! function_exists('is_uploaded')) {
    function is_uploaded($img, $tmp = false): bool
    {
        if ($tmp) {
            return is_string($img) && is_file($img);
        }

        return isset($_FILES[$img]['tmp_name']) && is_file($_FILES[$img]['tmp_name']);
    }
}

if (! function_exists('is_image')) {
    function is_image($img, $mime = false): bool
    {
        $mimes = [
            'image/png',
            'image/svg+xml',
            'image/svg',
            'image/webp',
            'image/jpeg',
            'image/gif',
        ];

        if ($mime) {
            return in_array($img, $mimes, true);
        }

        return isset($_FILES[$img]['tmp_name'])
            && is_file($_FILES[$img]['tmp_name'])
            && in_array(mime_content_type($_FILES[$img]['tmp_name']), $mimes, true);
    }
}

if (! function_exists('generate_slug_id')) {
    function generate_slug_id(): string
    {
        $letters = '';

        for ($i = 0; $i < 3; $i++) {
            $letters .= chr(random_int(65, 90));
        }

        return strtoupper(uniqid($letters, false));
    }
}
