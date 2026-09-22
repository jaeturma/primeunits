<?php

namespace App\Support;

use App\Models\ResourceAttachment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

trait StoresResourceAttachments
{
    public const AttachmentRules = ['array', 'max:10'];

    public const AttachmentFileRules = ['file', 'mimes:pdf', 'max:5120'];

    protected function storeResourceAttachments(Request $request, Model $resource, string $directory): void
    {
        foreach ($request->file('attachments', []) as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $resource->attachments()->create([
                'user_id' => $request->user()?->id,
                'name' => $file->getClientOriginalName(),
                'path' => $file->store($directory, 'local'),
                'mime_type' => $file->getMimeType() ?? 'application/pdf',
                'size' => $file->getSize(),
            ]);
        }
    }

    /**
     * @return array<int, array{id: int, name: string, url: string, size: int}>
     */
    protected function serializeAttachments(Model $resource): array
    {
        return $resource->attachments
            ->map(fn (ResourceAttachment $attachment): array => [
                'id' => $attachment->id,
                'name' => $attachment->name,
                'url' => $attachment->url(),
                'size' => $attachment->size,
            ])
            ->values()
            ->all();
    }
}
