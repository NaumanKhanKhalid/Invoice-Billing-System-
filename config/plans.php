<?php

return [
    'basic' => [
        'name'        => 'Basic',
        'price'       => 1500,
        'max_users'   => 1,
        'features'    => ['dashboard', 'purchases', 'supply', 'expenses', 'udhar', 'reports'],
        'google_backup' => false,
        'staff_module'  => false,
    ],
    'pro' => [
        'name'        => 'Pro',
        'price'       => 3000,
        'max_users'   => 3,
        'features'    => ['dashboard', 'purchases', 'supply', 'expenses', 'udhar', 'reports', 'staff', 'daily-rates'],
        'google_backup' => true,
        'staff_module'  => true,
    ],
    'business' => [
        'name'        => 'Business',
        'price'       => 5000,
        'max_users'   => PHP_INT_MAX,
        'features'    => ['*'],
        'google_backup' => true,
        'staff_module'  => true,
    ],
];
