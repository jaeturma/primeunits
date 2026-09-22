<?php

namespace App\Support;

/**
 * Advertisement destination-URL policy (spec: "Advertisement Safety"). A
 * destination is safe when it is either a same-site relative path (starts
 * with "/") or an absolute http(s) URL — never `javascript:`, `data:`,
 * `vbscript:`, or any other executable/unsafe scheme.
 */
class SafeUrl
{
    public static function isSafeDestination(?string $url): bool
    {
        if (blank($url)) {
            return true;
        }

        if (str_starts_with($url, '/')) {
            return ! str_starts_with($url, '//');
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);

        return in_array(mb_strtolower((string) $scheme), ['http', 'https'], true);
    }

    public static function isExternal(string $url): bool
    {
        return ! str_starts_with($url, '/');
    }
}
