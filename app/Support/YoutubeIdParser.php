<?php

namespace App\Support;

final class YoutubeIdParser
{
    /**
     * Extract an 11-character YouTube video id from a URL or raw id string.
     */
    public static function extract(?string $input): ?string
    {
        if ($input === null) {
            return null;
        }

        $input = trim($input);

        if ($input === '') {
            return null;
        }

        if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $input) === 1) {
            return $input;
        }

        if (preg_match('~(?:youtu\.be/|youtube\.com/embed/|youtube\.com/shorts/)([a-zA-Z0-9_-]{11})~', $input, $matches) === 1) {
            return $matches[1];
        }

        if (preg_match('~[?&]v=([a-zA-Z0-9_-]{11})~', $input, $matches) === 1) {
            return $matches[1];
        }

        return null;
    }
}
