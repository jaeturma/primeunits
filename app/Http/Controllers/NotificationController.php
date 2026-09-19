<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function index(Request $request): Response
    {
        $preference = $request->user()->notificationPreferenceOrDefault();

        return Inertia::render('notifications/index', [
            'notifications' => $request->user()
                ->notifications()
                ->latest()
                ->paginate(20)
                ->through(fn ($notification): array => [
                    'id' => $notification->id,
                    'title' => $notification->data['title'] ?? 'Notification',
                    'message' => $notification->data['message'] ?? '',
                    'event' => $notification->data['event'] ?? '',
                    'url' => $notification->data['url'] ?? null,
                    'read_at' => $notification->read_at?->toISOString(),
                    'created_at' => $notification->created_at?->toISOString(),
                ]),
            'preferences' => [
                'database_enabled' => $preference->database_enabled,
                'email_enabled' => $preference->email_enabled,
                'sms_enabled' => $preference->sms_enabled,
            ],
        ]);
    }

    public function markRead(Request $request, string $notification): RedirectResponse
    {
        $request->user()
            ->notifications()
            ->where('id', $notification)
            ->firstOrFail()
            ->markAsRead();

        return back();
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back();
    }

    public function updatePreferences(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'database_enabled' => ['boolean'],
            'email_enabled' => ['boolean'],
            'sms_enabled' => ['boolean'],
        ]);

        $request->user()->notificationPreferenceOrDefault()->update($validated);

        return back();
    }
}
