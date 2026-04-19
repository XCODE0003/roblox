<?php

use App\Support\LandingTutorialPreviewPath;

test('normalizes allowed preview paths', function (string $path, string $expected) {
    expect(LandingTutorialPreviewPath::normalize($path))->toBe($expected);
})->with([
    ['landing/tutorial-previews/abc.jpg', 'landing/tutorial-previews/abc.jpg'],
    ['landing/tutorial-previews/sub/x.webp', 'landing/tutorial-previews/sub/x.webp'],
]);

test('rejects invalid preview paths', function (?string $path) {
    expect(LandingTutorialPreviewPath::normalize($path))->toBeNull();
})->with([
    '',
    '   ',
    null,
    'other-dir/file.jpg',
    'landing/tutorial-previews/../secrets.env',
    'landing/wrong-previews/x.jpg',
]);
