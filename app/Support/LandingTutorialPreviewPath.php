<?php

namespace App\Support;

final class LandingTutorialPreviewPath
{
    public const string DIRECTORY = 'landing/tutorial-previews';

    public static function normalize(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        $path = str_replace('\\', '/', $path);

        if (str_contains($path, '..')) {
            return null;
        }

        $prefix = self::DIRECTORY.'/';

        if (! str_starts_with($path, $prefix)) {
            return null;
        }

        return $path;
    }
}
