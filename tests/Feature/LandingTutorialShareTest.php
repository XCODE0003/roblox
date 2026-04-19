<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

test('home page shares default landing tutorial props', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Index')
            ->has('landingTutorial', fn (Assert $prop) => $prop
                ->where('youtubeId', 'JPce5ZED8RY')
                ->where('durationCaption', '1:37')
                ->where('previewUrl', null)
            )
        );
});

test('home page uses settings for landing tutorial when present', function () {
    Setting::set('landing_tutorial_youtube_id', 'dQw4w9WgXcQ');
    Setting::set('landing_tutorial_duration_caption', '3:33');

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Index')
            ->has('landingTutorial', fn (Assert $prop) => $prop
                ->where('youtubeId', 'dQw4w9WgXcQ')
                ->where('durationCaption', '3:33')
                ->where('previewUrl', null)
            )
        );
});

test('home page shares custom preview url when file exists', function () {
    Storage::fake('public');
    $relative = 'landing/tutorial-previews/card.png';
    Storage::disk('public')->put($relative, 'fake-binary');
    Setting::set('landing_tutorial_preview_path', $relative);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Index')
            ->has('landingTutorial', fn (Assert $prop) => $prop
                ->where('youtubeId', 'JPce5ZED8RY')
                ->where('durationCaption', '1:37')
                ->where('previewUrl', '/storage/'.$relative)
            )
        );
});
