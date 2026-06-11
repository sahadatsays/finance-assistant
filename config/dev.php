<?php

return [

    /*
    |--------------------------------------------------------------------------
    | One-Click Login
    |--------------------------------------------------------------------------
    |
    | Enables quick login buttons on the login page for seeded development
    | accounts. Disabled by default outside the local environment.
    |
    */

    'one_click_login' => (bool) env('DEV_ONE_CLICK_LOGIN', env('APP_ENV') === 'local'),

    /*
    |--------------------------------------------------------------------------
    | Development Accounts
    |--------------------------------------------------------------------------
    |
    | Seeded accounts available for one-click login. Users must exist in the
    | database (see RoleAndPermissionUserSeeder).
    |
    */

    'accounts' => [
        [
            'email' => 'admin@financeassistant.com',
            'label' => 'Super Admin',
            'description' => 'Platform administration',
        ],
        [
            'email' => 'owner@acme.com',
            'label' => 'Tenant Owner',
            'description' => 'Acme Corporation (active)',
        ],
        [
            'email' => 'member@acme.com',
            'label' => 'Tenant User',
            'description' => 'Acme Corporation member',
        ],
        [
            'email' => 'owner@startup.com',
            'label' => 'Tenant Owner',
            'description' => 'Startup Inc (trial)',
        ],
        [
            'email' => 'member@startup.com',
            'label' => 'Tenant User',
            'description' => 'Startup Inc member',
        ],
        [
            'email' => 'owner@suspended.com',
            'label' => 'Tenant Owner',
            'description' => 'Suspended LLC',
        ],
        [
            'email' => 'guest@example.com',
            'label' => 'Guest',
            'description' => 'No tenant membership',
        ],
    ],

];
