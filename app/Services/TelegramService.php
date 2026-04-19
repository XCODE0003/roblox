<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    public function sendSubmissionNotification(string $content, string $ip): void
    {
        $botToken = Setting::get('telegram_bot_token');
        $chatId = Setting::get('telegram_chat_id');

        if (! $botToken || ! $chatId) {
            return;
        }

        $preview = mb_strlen($content) > 300 ? mb_substr($content, 0, 300).'...' : $content;

        $message = "🎮 *Новая заявка — COPYHELPER*\n\n"
            ."📅 *Дата:* `".now()->format('d.m.Y H:i:s')." UTC`\n"
            ."🌐 *IP:* `{$ip}`\n\n"
            ."📋 *Содержимое:*\n"
            ."```\n{$preview}\n```";

        try {
            Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown',
            ]);
        } catch (\Throwable $e) {
            Log::error('Telegram notification failed', ['error' => $e->getMessage()]);
        }
    }
}
