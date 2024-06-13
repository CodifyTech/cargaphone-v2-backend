<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default user role
    |--------------------------------------------------------------------------
    |
    | This value is the default user role id that will be assigned to new users
    | when they register.
    |
    | admin = Admin role, user = User role, customer = Customer Role - Check RoleSeeder for more
    |
    */

    'default_user_role_slug' => env('DEFAULT_ROLE_SLUG', 'user'),

    /*
    |--------------------------------------------------------------------------
    | Delete old access tokens when logged in
    |--------------------------------------------------------------------------
    |
    | This value determines whether or not to delete old access tokens when
    | the users are logged in.
    |
    */

    'delete_previous_access_tokens_on_login' => env('DELETE_PREVIOUS_ACCESS_TOKENS_ON_LOGIN', false),

    'api_version' => env('API_VERSION', 'v1'),

    'locales' => [
        'pt_BR',
        'en',
        'es'
    ],

    'permission_list' => [
        'auth' => [
            'Auth read'
        ],
        'manage' => [
            'all manage'
        ],
        'roles' => [
            'roles list',
            'roles create',
            'roles edit',
            'roles delete',
        ],
        'user' => [
            'user list',
            'user create',
            'user edit',
            'user delete',
        ],
        'permission' => [
            'permission list',
            'permission create',
            'permission edit',
            'permission delete',
        ],
        'product' => [
            'product list',
            'product create',
            'product edit',
            'product delete',
        ],
    ],
];
