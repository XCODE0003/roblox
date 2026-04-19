<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Support\LandingTutorialPreviewPath;
use App\Support\YoutubeIdParser;
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
        $id = Setting::get('landing_tutorial_youtube_id', 'JPce5ZED8RY') ?? 'JPce5ZED8RY';
        $previewPath = LandingTutorialPreviewPath::normalize(Setting::get('landing_tutorial_preview_path'));

        $this->form->fill([
            'youtube_url_or_id' => "https://youtu.be/{$id}",
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
                Section::make('YouTube tutorial')->schema([
                    TextInput::make('youtube_url_or_id')
                        ->label('YouTube link or video ID')
                        ->placeholder('https://youtu.be/… or dQw4w9WgXcQ')
                        ->helperText('Used on the home page tutorial block (thumbnail opens YouTube).')
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
                        ->helperText('Optional. If empty, the YouTube thumbnail is used. JPG, PNG or WebP, max 3 MB.')
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
        $raw = $data['youtube_url_or_id'] ?? '';
        $videoId = YoutubeIdParser::extract(is_string($raw) ? $raw : '');

        if ($videoId === null) {
            Notification::make()
                ->title('Could not read a valid YouTube video')
                ->body('Paste a youtu.be link, a watch?v= URL, or the 11-character video ID.')
                ->danger()
                ->send();

            return;
        }

        $caption = $data['duration_caption'] ?? '1:37';
        $caption = is_string($caption) ? trim($caption) : '1:37';
        if ($caption === '') {
            $caption = '1:37';
        }

        Setting::set('landing_tutorial_youtube_id', $videoId);
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
