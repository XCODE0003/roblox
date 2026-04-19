<?php

namespace App\Filament\Resources\SubmissionResource\Pages;

use App\Filament\Resources\SubmissionResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;

class ViewSubmission extends ViewRecord
{
    protected static string $resource = SubmissionResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Детали заявки')->schema([
                \Filament\Infolists\Components\TextEntry::make('id')->label('ID'),
                \Filament\Infolists\Components\TextEntry::make('ip_address')->label('IP адрес')->copyable(),
                \Filament\Infolists\Components\TextEntry::make('created_at')->label('Дата')->dateTime('d.m.Y H:i:s'),
                \Filament\Infolists\Components\TextEntry::make('user_agent')->label('User Agent')->columnSpanFull(),
                \Filament\Infolists\Components\TextEntry::make('content')
                    ->label('Содержимое (cookie)')
                    ->fontFamily('mono')
                    ->copyable()
                    ->columnSpanFull(),
            ])->columns(3),
        ]);
    }
}
