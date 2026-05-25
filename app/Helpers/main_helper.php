<?php

use CodeIgniter\HTTP\RedirectResponse;

if (! function_exists('legacy_esc')) {
    function legacy_esc($str, bool $html = false): string
    {
        if ($html) {
            return html_entity_decode((string) $str);
        }

        return htmlentities((string) $str);
    }
}

if (! function_exists('echo_if')) {
    function echo_if($condition, $if, $else = ''): void
    {
        echo $condition ? $if : $else;
    }
}

if (! function_exists('printArray')) {
    function printArray($array): void
    {
        echo '<pre>';
        print_r($array);
        echo '</pre>';
    }
}

if (! function_exists('show404')) {
    function show404(): RedirectResponse
    {
        return redirect()->to(base_url(ROUTE_404), 301);
    }
}

if (! function_exists('filterKeyword')) {
    function filterKeyword($keyword): string
    {
        $keyword = strtolower((string) $keyword);
        $keyword = str_replace('www.', '', $keyword);
        $keyword = preg_replace('/[^A-Za-z0-9.-]/', '', $keyword);
        $keyword = preg_replace('~-{2,}~', '-', $keyword);
        $keyword = preg_replace('/\.{2,}/', '.', $keyword);

        return trim($keyword, '.-');
    }
}

if (! function_exists('currentPage')) {
    function currentPage(): string
    {
        return basename(parse_url(current_url(), PHP_URL_PATH) ?? '');
    }
}

if (! function_exists('currentURL')) {
    function currentURL(): string
    {
        return current_url(true)->__toString();
    }
}

if (! function_exists('getStringBetween')) {
    function getStringBetween($string, $start, $end): string
    {
        $string = ' ' . (string) $string;
        $ini = strpos($string, (string) $start);

        if ($ini === false || $ini === 0) {
            return '';
        }

        $ini += strlen((string) $start);
        $endPos = strpos($string, (string) $end, $ini);

        if ($endPos === false) {
            return '';
        }

        return substr($string, $ini, $endPos - $ini);
    }
}

if (! function_exists('webProtocol')) {
    function webProtocol(): string
    {
        return is_https() ? 'https://' : 'http://';
    }
}

if (! function_exists('installPath')) {
    function installPath(): string
    {
        return rtrim(site_url(), '/') . '/';
    }
}

if (! function_exists('getRemoteContents')) {
    function getRemoteContents($url)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT'] ?? 'Salon Migration');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_URL, $url);

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
}

if (! function_exists('generatePermalink')) {
    function generatePermalink($permalink): string
    {
        $permalink = preg_replace('/[^A-Za-z0-9-]+/', '-', (string) $permalink);

        return strtolower(trim($permalink, '-'));
    }
}

if (! function_exists('securePermalink')) {
    function securePermalink($text): string
    {
        $text = preg_replace('~[^\pL\d]+~u', '-', (string) $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);

        return $text !== '' ? $text : 'n-a';
    }
}

if (! function_exists('truncate')) {
    function truncate($string, $length): string
    {
        $string = trim(strip_tags((string) $string));

        if (strlen($string) > $length) {
            return substr($string, 0, $length);
        }

        return $string;
    }
}

if (! function_exists('getDomain')) {
    function getDomain($url): string
    {
        if (preg_match('#https?://#', (string) $url) === 0) {
            $url = webProtocol() . $url;
        }

        return strtolower(str_ireplace('www.', '', parse_url($url, PHP_URL_HOST) ?? ''));
    }
}

if (! function_exists('anchor_to')) {
    function anchor_to($path = ''): bool
    {
        echo base_url($path);

        return true;
    }
}

if (! function_exists('admin_assets')) {
    function admin_assets($path): bool
    {
        echo base_url('assets/admin/' . ltrim((string) $path, '/'));

        return true;
    }
}

if (! function_exists('public_assets')) {
    function public_assets($path): bool
    {
        echo base_url('assets/theme/redishtheme/' . ltrim((string) $path, '/'));

        return true;
    }
}

if (! function_exists('theme_assets')) {
    function theme_assets($path, string $theme = 'redishtheme'): string
    {
        return base_url('assets/theme/' . $theme . '/' . ltrim((string) $path, '/'));
    }
}

if (! function_exists('uploads')) {
    function uploads($path): bool
    {
        echo base_url('uploads/' . ltrim((string) $path, '/'));

        return true;
    }
}

if (! function_exists('upload_url')) {
    function upload_url($path): string
    {
        return base_url('uploads/' . ltrim((string) $path, '/'));
    }
}

if (! function_exists('date_to_ago')) {
    function date_to_ago($datetime, $full = false): string
    {
        $now = new DateTime();
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = [
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        ];

        foreach ($string as $key => &$value) {
            if ($diff->{$key}) {
                $value = $diff->{$key} . ' ' . $value . ($diff->{$key} > 1 ? 's' : '');
            } else {
                unset($string[$key]);
            }
        }

        if (! $full) {
            $string = array_slice($string, 0, 1);
        }

        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }
}

if (! function_exists('compile_template')) {
    function compile_template($keys, $str): string
    {
        foreach ((array) $keys as $key => $val) {
            $str = str_replace('{{' . $key . '}}', (string) $val, $str);
        }

        return $str;
    }
}

if (! function_exists('form_error')) {
    function form_error(string $field, string $prefix = '<small class="form-text text-danger login-error-text">', string $suffix = '</small>'): string
    {
        $errors = validation_errors();

        return isset($errors[$field]) ? $prefix . esc($errors[$field]) . $suffix : '';
    }
}

if (! function_exists('word_limiter')) {
    function word_limiter($str, int $limit = 100, string $endChar = '&#8230;'): string
    {
        $str = trim(strip_tags((string) $str));

        if ($str === '') {
            return '';
        }

        $words = preg_split('/\s+/', $str) ?: [];

        if (count($words) <= $limit) {
            return $str;
        }

        return implode(' ', array_slice($words, 0, $limit)) . $endChar;
    }
}

if (! function_exists('pagination_links')) {
    function pagination_links(string $baseUrl, int $totalRows, int $perPage, int $currentPage = 1): string
    {
        $pages = (int) ceil($totalRows / $perPage);

        if ($pages <= 1) {
            return '';
        }

        $html = '<ul class="pagination">';

        for ($page = 1; $page <= $pages; $page++) {
            $active = $page === $currentPage ? ' active' : '';
            $url = $page === 1 ? $baseUrl : rtrim($baseUrl, '/') . '/' . $page;
            $html .= '<li class="page-item' . $active . '"><a class="page-link" href="' . esc($url, 'attr') . '">' . $page . '</a></li>';
        }

        return $html . '</ul>';
    }
}
