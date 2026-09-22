<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Streams a file from the private `local` disk after the caller has
 * already authorized the request — never call this before checking
 * ownership or an admin permission. See SecureDocumentController for the
 * authorization boundary this backs.
 */
trait ServesPrivateDocuments
{
    private function streamPrivateDocument(?string $path): StreamedResponse
    {
        abort_unless($path !== null && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path);
    }
}
