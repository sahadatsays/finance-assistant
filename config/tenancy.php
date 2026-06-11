<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Trial Period
    |--------------------------------------------------------------------------
    |
    | Number of days a new tenant remains in trial status before requiring
    | an active subscription (payment gateway not yet integrated).
    |
    */

    'trial_days' => (int) env('TENANCY_TRIAL_DAYS', 14),

    /*
    |--------------------------------------------------------------------------
    | Default Plan
    |--------------------------------------------------------------------------
    |
    | Slug of the plan assigned when no plan is specified at tenant creation.
    |
    */

    'default_plan_slug' => env('TENANCY_DEFAULT_PLAN', 'free'),

];
