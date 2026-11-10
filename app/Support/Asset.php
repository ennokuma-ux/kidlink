<?php

namespace App\Support;

/**
 * Public asset URL with a ?v=<file modified time> suffix.
 *
 * The suffix changes whenever the file changes, so the web server can tell
 * browsers to cache CSS and JavaScript for a year (see public/.htaccess)
 * without anyone ever seeing a stale copy after a deploy.
 */
class Asset
{
    public static function v(string $path): string
    {
        $file = public_path($path);
        $version = is_file($file) ? @filemtime($file) : false;

        return asset($path).($version ? '?v='.$version : '');
    }
}
