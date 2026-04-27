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
            $csrfToken = $this->getCsrfToken($cookie);

            if (! $csrfToken) {
                Log::warning('Roblox refresh: csrf token not obtained');

                return null;
            }

            $ticket = null;

            for ($i = 0; $i < 3; $i++) {
                $ticketResponse = Http::withHeaders([
                    'Cookie' => ".ROBLOSECURITY={$cookie}",
                    'User-Agent' => self::USER_AGENT,
                    'X-CSRF-TOKEN' => $csrfToken,
                    'Content-Type' => 'application/json',
                    'RBXauthenticationNegotiation' => '1',
                    'Referer' => 'https://www.roblox.com/',
                ])->post('https://auth.roblox.com/v1/authentication-ticket');

                $ticket = $ticketResponse->header('rbx-authentication-ticket');

                if ($ticket) {
                    break;
                }

                sleep(1);
            }

            if (! $ticket) {
                Log::warning('Roblox refresh: authentication ticket not obtained');

                return null;
            }

            $redeemResponse = Http::withHeaders([
                'rbxauthenticationnegotiation' => '1',
                'User-Agent' => self::USER_AGENT,
            ])->post('https://auth.roblox.com/v1/authentication-ticket/redeem', [
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

    private function getCsrfToken(string $cookie): ?string
    {
        $response = Http::withHeaders([
            'Cookie' => ".ROBLOSECURITY={$cookie}",
            'User-Agent' => self::USER_AGENT,
        ])->post('https://friends.roblox.com/v1/users/1/unfriend');

        return $response->header('x-csrf-token');
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
