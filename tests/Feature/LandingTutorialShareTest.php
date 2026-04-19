<?php

use App\Models\Setting;
use App\Support\LandingTutorialDefaults;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

test('home page shares default landing tutorial props', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Index')
            ->has('landingTutorial', fn (Assert $prop) => $prop
                ->where('videoUrl', LandingTutorialDefaults::VIDEO_URL)
                ->where('durationCaption', '1:37')
                ->where('previewUrl', null)
            )
        );
});

test('home page uses stored video url as-is', function () {
    $custom = 'https://www.youtube.com/watch?v=JPce512312ZED8RY11123&feature=youtu.be';
    Setting::set('landing_tutorial_video_url', $custom);
    Setting::set('landing_tutorial_duration_caption', '3:33');

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Index')
            ->has('landingTutorial', fn (Assert $prop) => $prop
                ->where('videoUrl', $custom)
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
                ->where('videoUrl', LandingTutorialDefaults::VIDEO_URL)
                ->where('durationCaption', '1:37')
                ->where('previewUrl', '/storage/'.$relative)
            )
        );
});
