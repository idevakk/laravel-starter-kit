<?php

use Filament\Support\Colors\Color;

return [
    'route' => env('FILAMENT_ROUTE', 'dash'),
    'colors' => [
        'primary' => Color::Teal,
        'danger' => Color::Red,
        'gray' => Color::Zinc,
        'info' => Color::Blue,
        'success' => Color::Green,
        'warning' => Color::Amber,
    ],
    'enable_login' => env('FILAMENT_ENABLE_LOGIN', true),
    'enable_registration' => env('FILAMENT_ENABLE_REGISTER', false),
    'enable_profile' => env('FILAMENT_ENABLE_PROFILE', false),
];
