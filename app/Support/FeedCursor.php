<?php

namespace App\Support;

/**
 * Encodes/decodes the opaque "Load 12 More" continuation token: which
 * listing and advertisement ids have already been shown in this loaded
 * feed, plus the marketplace mode the feed was built for. HMAC-signed so a
 * casually edited token is detected and discarded, but this is a
 * correctness safeguard only — access is always re-derived server-side
 * from the authenticated viewer on every request, never from the cursor,
 * so a forged cursor can at worst cause a duplicate/repeat card, never an
 * authorization bypass.
 */
class FeedCursor
{
    private const MAX_TRACKED_IDS = 400;

    /**
     * @param  array<int, int>  $shownListingIds
     * @param  array<int, int>  $shownAdIds
     */
    public static function encode(string $mode, array $shownListingIds, array $shownAdIds): string
    {
        $payload = [
            'v' => 1,
            'mode' => $mode,
            'listings' => array_slice(array_values(array_unique($shownListingIds)), -self::MAX_TRACKED_IDS),
            'ads' => array_slice(array_values(array_unique($shownAdIds)), -self::MAX_TRACKED_IDS),
        ];

        $json = json_encode($payload);
        $signature = hash_hmac('sha256', $json, config('app.key'));

        return base64_encode($json).'.'.$signature;
    }

    /**
     * @return array{mode: ?string, listings: array<int, int>, ads: array<int, int>}
     */
    public static function decode(?string $cursor): array
    {
        $empty = ['mode' => null, 'listings' => [], 'ads' => []];

        if (blank($cursor) || ! str_contains($cursor, '.')) {
            return $empty;
        }

        [$encoded, $signature] = explode('.', $cursor, 2);
        $json = base64_decode($encoded, true);

        if ($json === false || ! hash_equals(hash_hmac('sha256', $json, config('app.key')), $signature)) {
            return $empty;
        }

        $payload = json_decode($json, true);

        if (! is_array($payload)) {
            return $empty;
        }

        return [
            'mode' => is_string($payload['mode'] ?? null) ? $payload['mode'] : null,
            'listings' => array_values(array_filter((array) ($payload['listings'] ?? []), 'is_int')),
            'ads' => array_values(array_filter((array) ($payload['ads'] ?? []), 'is_int')),
        ];
    }
}
