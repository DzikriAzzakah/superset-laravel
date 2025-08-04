<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Superset Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for connecting to your Superset instance.
    | Update these values according to your Superset setup.
    |
    */

    // Superset instance domain/URL
    'domain' => env('SUPERSET_DOMAIN', 'http://localhost:8088'),

    // Superset admin credentials (used for API authentication)
    'username' => env('SUPERSET_USERNAME', 'admin'),
    'password' => env('SUPERSET_PASSWORD', 'admin'),

    // Default dashboard settings
    'default_dashboard_id' => env('SUPERSET_DEFAULT_DASHBOARD_ID', '1'),

    // Embedding settings
    'embed_settings' => [
        'hide_title' => env('SUPERSET_HIDE_TITLE', false),
        'hide_tab' => env('SUPERSET_HIDE_TAB', false),
        'hide_chart_controls' => env('SUPERSET_HIDE_CHART_CONTROLS', false),
        'filters_expanded' => env('SUPERSET_FILTERS_EXPANDED', true),
        'filters_visible' => env('SUPERSET_FILTERS_VISIBLE', true),
    ],

    // Security settings
    'guest_user' => [
        'username' => env('SUPERSET_GUEST_USERNAME', 'guest_user'),
        'first_name' => env('SUPERSET_GUEST_FIRST_NAME', 'Guest'),
        'last_name' => env('SUPERSET_GUEST_LAST_NAME', 'User'),
    ],
];
