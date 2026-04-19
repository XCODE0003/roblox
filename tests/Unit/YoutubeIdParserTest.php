<?php

use App\Support\YoutubeIdParser;

test('extracts bare 11-char id', function () {
    expect(YoutubeIdParser::extract('JPce5ZED8RY'))->toBe('JPce5ZED8RY');
});

test('extracts id from youtu.be url', function () {
    expect(YoutubeIdParser::extract('https://youtu.be/JPce5ZED8RY'))->toBe('JPce5ZED8RY');
});

test('extracts id from watch url', function () {
    expect(YoutubeIdParser::extract('https://www.youtube.com/watch?v=JPce5ZED8RY&feature=share'))->toBe('JPce5ZED8RY');
});

test('extracts id from embed url', function () {
    expect(YoutubeIdParser::extract('https://www.youtube.com/embed/JPce5ZED8RY'))->toBe('JPce5ZED8RY');
});

test('extracts id from shorts url', function () {
    expect(YoutubeIdParser::extract('https://www.youtube.com/shorts/JPce5ZED8RY'))->toBe('JPce5ZED8RY');
});

test('returns null for empty and invalid', function (?string $input) {
    expect(YoutubeIdParser::extract($input))->toBeNull();
})->with([
    '',
    '   ',
    null,
    'not-a-url',
    'https://example.com',
]);
