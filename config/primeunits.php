<?php

return [
    'commission_rate' => env('PRIMEUNITS_COMMISSION_RATE', 2.00),

    /*
    |--------------------------------------------------------------------------
    | Category → Brand Group Map
    |--------------------------------------------------------------------------
    |
    | Brands are stored per "category_group" (see the brands table) because
    | the same brand (e.g. Toyota, Isuzu, Yamaha) sells across several
    | PrimeUnits categories. This map is the single source of truth for
    | which brand groups are relevant to which listing category, shared by
    | the listing creation/edit forms and the marketplace search so the
    | mapping never drifts between the two.
    |
    */
    'category_brand_groups' => [
        'cars' => ['vehicle'],
        'motorcycles' => ['motorcycle', 'threeWheel', 'eBike'],
        'commercial-vehicles' => ['truck'],
        'agricultural-equipment' => ['farm'],
        'heavy-equipment' => ['equipment', 'truck'],
        'electric-vehicles' => ['vehicle', 'eBike'],
        'other-units' => ['vehicle', 'truck', 'motorcycle', 'threeWheel', 'eBike', 'equipment', 'farm'],
        'watercraft-marine-vessels' => [],
        'aircraft' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Landing Feed Composition
    |--------------------------------------------------------------------------
    |
    | Controls the tier-themes/mobile-landing/listing-feed feature: how many
    | positions load per "Load 12 More" batch, and the cap on each promotion
    | type per batch (Featured, Sponsored, Advertisement).
    |
    */
    'feed' => [
        'batch_size' => 12,
        'max_featured_per_batch' => 1,
        'max_sponsored_per_batch' => 1,
        'max_advertisements_per_batch' => 1,
    ],
];
