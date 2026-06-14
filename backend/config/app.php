<?php

use Illuminate\Support\Facades\Facade;

return [
    'name'            => env('APP_NAME', 'SundaLearn'),
    'env'             => env('APP_ENV', 'production'),
    'debug'           => (bool) env('APP_DEBUG', false),
    'url'             => env('APP_URL', 'http://localhost'),
    'frontend_url'    => env('FRONTEND_URL', 'http://localhost:5173'),
    'timezone'        => 'Asia/Jakarta',
    'locale'          => 'id',
    'fallback_locale' => 'en',
    'faker_locale'    => 'id_ID',
    'key'             => env('APP_KEY'),
    'cipher'          => 'AES-256-CBC',
    'maintenance'     => ['driver' => 'file'],
    'providers'       => Illuminate\Support\AggregateServiceProvider::defaultProviders()->toArray(),
    'aliases'         => Facade::defaultAliases()->toArray(),
];
