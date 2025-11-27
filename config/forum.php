<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Forum Settings
    |--------------------------------------------------------------------------
    |
    | This file is for storing the configuration for the forum functionality.
    | You can override these values in your .env file by using the same key
    | with a FORUM_ prefix (e.g., FORUM_ENABLE_EMAIL_VERIFICATION).
    |
    */


    /*
    |--------------------------------------------------------------------------
    | General Settings
    |--------------------------------------------------------------------------
    */

    'enable_email_verification' => env('FORUM_ENABLE_EMAIL_VERIFICATION', true),
    'enable_recaptcha' => env('FORUM_ENABLE_RECAPTCHA', false),
    'recaptcha_site_key' => env('RECAPTCHA_SITE_KEY'),
    'recaptcha_secret_key' => env('RECAPTCHA_SECRET_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Posting Settings
    |--------------------------------------------------------------------------
    */
    'posts_per_page' => 15,
    'replies_per_page' => 10,
    'max_upload_size' => 10240, // 10MB in KB
    'allowed_file_types' => [
        'image/jpeg',
        'image/png',
        'image/gif',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/plain',
    ],

    /*
    |--------------------------------------------------------------------------
    | Group Settings
    |--------------------------------------------------------------------------
    */
    'max_groups_per_user' => 5,
    'max_members_per_group' => 100,
    'default_group_visibility' => 'public', // 'public' or 'private'
    'allow_group_creation' => true,


    /*
    |--------------------------------------------------------------------------
    | User Settings
    |--------------------------------------------------------------------------
    */
    'min_username_length' => 3,
    'max_username_length' => 25,
    'min_password_length' => 8,

    /*
    |--------------------------------------------------------------------------
    | Search Settings
    |--------------------------------------------------------------------------
    */
    'search_enabled' => true,
    'search_min_length' => 3,
    'search_max_results' => 50,

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    */
    'cache_lifetime' => 60, // in minutes
    'enable_caching' => env('FORUM_ENABLE_CACHING', true),

    /*
    |--------------------------------------------------------------------------
    | Performance Settings
    |--------------------------------------------------------------------------
    */
    'eager_load' => [
        'user',
        'replies',
        'replies.user',
        'group',
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    */
    'notifications' => [
        'email' => [
            'new_reply' => true,
            'new_follower' => true,
            'mention' => true,
        ],
        'database' => [
            'new_reply' => true,
            'new_follower' => true,
            'mention' => true,
        ],
    ],
];
