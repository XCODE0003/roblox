<?php

use App\Models\Setting;
use Inertia\Testing\AssertableInertia as Assert;

test('home page shares default landing tutorial props', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Index')
            ->has('landingTutorial', fn (Assert $prop) => $prop
                ->where('youtubeId', 'JPce5ZED8RY')
                ->where('durationCaption', '1:37')
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
            )
        );
});
