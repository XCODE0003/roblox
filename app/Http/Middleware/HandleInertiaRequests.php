<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use App\Support\LandingTutorialDefaults;
use App\Support\LandingTutorialPreviewPath;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $storedVideoUrl = Setting::get('landing_tutorial_video_url');
        $tutorialVideoUrl = is_string($storedVideoUrl) && trim($storedVideoUrl) !== ''
            ? trim($storedVideoUrl)
            : LandingTutorialDefaults::VIDEO_URL;
        $tutorialDurationCaption = Setting::get('landing_tutorial_duration_caption', '1:37') ?? '1:37';
        $previewPath = LandingTutorialPreviewPath::normalize(Setting::get('landing_tutorial_preview_path'));
        $tutorialPreviewUrl = null;
        if ($previewPath !== null && Storage::disk('public')->exists($previewPath)) {
            // Same-origin path only (no third-party image hosts).
            $tutorialPreviewUrl = '/storage/'.$previewPath;
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'landingTutorial' => [
                'videoUrl' => $tutorialVideoUrl,
                'durationCaption' => $tutorialDurationCaption,
                'previewUrl' => $tutorialPreviewUrl,
            ],
        ];
    }
}
