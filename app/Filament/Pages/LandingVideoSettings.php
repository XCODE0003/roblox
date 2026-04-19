<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Support\LandingTutorialDefaults;
use App\Support\LandingTutorialPreviewPath;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class LandingVideoSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedPlayCircle;

    protected static ?string $navigationLabel = 'Tutorial video';

    protected static ?string $title = 'Landing tutorial video';

    protected static ?int $navigationSort = 15;

    protected string $view = 'filament-panels::pages.page';

    public ?array $data = [];

    public function mount(): void
    {
        $url = Setting::get('landing_tutorial_video_url');
        if (! is_string($url) || trim($url) === '') {
            $url = LandingTutorialDefaults::VIDEO_URL;
        }

        $previewPath = LandingTutorialPreviewPath::normalize(Setting::get('landing_tutorial_preview_path'));

        $this->form->fill([
            'video_url' => trim($url),
            'duration_caption' => Setting::get('landing_tutorial_duration_caption', '1:37') ?? '1:37',
            'preview_image' => $previewPath,
        ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            EmbeddedSchema::make('form'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Tutorial video')->schema([
                    TextInput::make('video_url')
                        ->label('Video link')
                        ->placeholder('https://www.youtube.com/watch?v=…')
                        ->helperText('Paste the full link as it should open for visitors. It is stored and used as-is (no parsing).')
                        ->maxLength(2048)
                        ->columnSpanFull(),
                    TextInput::make('duration_caption')
                        ->label('Duration label')
                        ->placeholder('1:37')
                        ->helperText('Short text shown next to “Watch on YouTube” (e.g. video length).')
                        ->maxLength(32)
                        ->columnSpanFull(),
                    FileUpload::make('preview_image')
                        ->label('Custom preview image')
                        ->disk('public')
                        ->directory(LandingTutorialPreviewPath::DIRECTORY)
                        ->visibility('public')
                        ->image()
                        ->maxSize(3072)
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->imagePreviewHeight('200')
                        ->helperText('Optional. Stored on this server under storage/app/public (served as /storage/…). If empty, a local placeholder image is shown. JPG, PNG or WebP, max 3 MB.')
                        ->nullable()
                        ->columnSpanFull(),
                ]),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save')
                ->action('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $url = isset($data['video_url']) && is_string($data['video_url']) ? trim($data['video_url']) : '';

        $validator = Validator::make(
            ['video_url' => $url],
            [
                'video_url' => ['required', 'string', 'max:2048', 'regex:/^https?:\/\/.+/i'],
            ],
            [
                'video_url.required' => 'Enter a link to the video.',
                'video_url.regex' => 'The link must start with http:// or https://',
            ]
        );

        if ($validator->fails()) {
            Notification::make()
                ->title('Could not save')
                ->body($validator->errors()->first('video_url') ?? 'Invalid link.')
                ->danger()
                ->send();

            return;
        }

        $caption = $data['duration_caption'] ?? '1:37';
        $caption = is_string($caption) ? trim($caption) : '1:37';
        if ($caption === '') {
            $caption = '1:37';
        }

        Setting::set('landing_tutorial_video_url', $url);
        Setting::set('landing_tutorial_duration_caption', $caption);

        $previousPreviewPath = LandingTutorialPreviewPath::normalize(Setting::get('landing_tutorial_preview_path'));
        $incomingPreview = $data['preview_image'] ?? null;
        $incomingPreview = is_string($incomingPreview) && $incomingPreview !== ''
            ? LandingTutorialPreviewPath::normalize($incomingPreview)
            : null;

        if ($incomingPreview === null) {
            if ($previousPreviewPath !== null) {
                Storage::disk('public')->delete($previousPreviewPath);
            }
            Setting::set('landing_tutorial_preview_path', null);
        } else {
            if ($previousPreviewPath !== null && $previousPreviewPath !== $incomingPreview) {
                Storage::disk('public')->delete($previousPreviewPath);
            }
            Setting::set('landing_tutorial_preview_path', $incomingPreview);
        }

        Notification::make()
            ->title('Tutorial video saved')
            ->success()
            ->send();
    }
}
