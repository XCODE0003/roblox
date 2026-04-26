<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RobloxAuthService
{
    private const USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

    public function refreshCookie(string $cookie): ?string
    {
        try {
            $homeResponse = Http::withHeaders([
                'Cookie' => ".ROBLOSECURITY={$cookie}",
                'User-Agent' => self::USER_AGENT,
            ])->post('https://www.roblox.com/home/');

            $csrfToken = $homeResponse->header('x-csrf-token');
            $cookieAfterHome = $this->extractRoblosecurity($homeResponse->header('set-cookie')) ?? $cookie;

            if (! $csrfToken) {
                $tokenResponse = Http::withHeaders([
                    'Cookie' => ".ROBLOSECURITY={$cookieAfterHome}",
                    'User-Agent' => self::USER_AGENT,
                ])->post('https://auth.roblox.com/v2/logout');
                $csrfToken = $tokenResponse->header('x-csrf-token');
            }

            if (! $csrfToken) {
                Log::warning('Roblox refresh: csrf token not obtained');

                return null;
            }

            $ticketResponse = Http::withHeaders([
                'x-csrf-token' => $csrfToken,
                'Cookie' => ".ROBLOSECURITY={$cookieAfterHome}",
                'Referer' => 'https://www.roblox.com/',
                'User-Agent' => self::USER_AGENT,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])->post('https://auth.roblox.com/v1/authentication-ticket');

            $ticket = $ticketResponse->header('rbx-authentication-ticket');

            if (! $ticket) {
                Log::warning('Roblox refresh: authentication ticket not obtained', [
                    'status' => $ticketResponse->status(),
                ]);

                return null;
            }

            $redeemResponse = Http::withHeaders([
                'RBXauthenticationNegotiation' => '1',
                'User-Agent' => self::USER_AGENT,
            ])->asForm()->post('https://auth.roblox.com/v1/authentication-ticket/redeem', [
                'authenticationTicket' => $ticket,
            ]);

            $newCookie = $this->extractRoblosecurity($redeemResponse->header('set-cookie'));

            if (! $newCookie) {
                Log::warning('Roblox refresh: new .ROBLOSECURITY not found in redeem response', [
                    'status' => $redeemResponse->status(),
                ]);
            }

            return $newCookie;
        } catch (\Throwable $e) {
            Log::error('Roblox cookie refresh failed', ['error' => $e->getMessage()]);

            return null;
        }
    }

    private function extractRoblosecurity(?string $setCookieHeader): ?string
    {
        if (! $setCookieHeader) {
            return null;
        }

        if (preg_match('/\.ROBLOSECURITY=([^;,]+)/', $setCookieHeader, $matches)) {
            $value = trim($matches[1]);

            return $value !== '' ? $value : null;
        }

        return null;
    }
}
