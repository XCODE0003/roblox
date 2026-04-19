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

    /**
     * True when the pasted URL or path segment contains more than 11 id characters
     * (everything after the 11th is ignored; YouTube video ids are always 11 chars).
     */
    public static function hadIgnoredTrailingCharacters(?string $input, string $extractedElevenCharId): bool
    {
        if ($input === null || trim($input) === '') {
            return false;
        }

        $input = trim($input);

        if (preg_match('~youtu\.be/([^?#]+)~', $input, $matches) === 1) {
            $segment = rtrim($matches[1], '/');

            return strlen($segment) > 11;
        }

        if (preg_match('~[?&]v=([^&]+)~', $input, $matches) === 1) {
            return strlen($matches[1]) > 11;
        }

        if (preg_match('~(?:youtube\.com/embed/|youtube\.com/shorts/)([^?#]+)~', $input, $matches) === 1) {
            $segment = rtrim($matches[1], '/');

            return strlen($segment) > 11;
        }

        if (! preg_match('~[/:?]~', $input) && strlen($input) > 11) {
            return str_starts_with($input, $extractedElevenCharId);
        }

        return false;
    }
}
