<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class TelegramSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedPaperAirplane;

    protected static ?string $navigationLabel = 'Telegram';

    protected static ?string $title = 'Telegram Settings';

    protected string $view = 'filament-panels::pages.page';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'telegram_bot_token' => Setting::get('telegram_bot_token'),
            'telegram_chat_id' => Setting::get('telegram_chat_id'),
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
                Section::make('Telegram Bot')->schema([
                    TextInput::make('telegram_bot_token')
                        ->label('Bot Token')
                        ->placeholder('123456789:AABBCCDDEEFFaabbccddeeff...')
                        ->password()
                        ->revealable()
                        ->helperText('Get this from @BotFather in Telegram')
                        ->columnSpanFull(),
                    TextInput::make('telegram_chat_id')
                        ->label('Chat ID')
                        ->placeholder('-100123456789 or @username')
                        ->helperText('The chat or channel ID to receive notifications')
                        ->columnSpanFull(),
                ]),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Settings')
                ->action('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::set('telegram_bot_token', $data['telegram_bot_token'] ?? null);
        Setting::set('telegram_chat_id', $data['telegram_chat_id'] ?? null);

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }
}
