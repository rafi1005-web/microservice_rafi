<?php

return [

    'product' => [
        'url' => env('PRODUCT_SERVICE_URL', 'http://localhost:3000'),
        'timeout' => (int) env('HTTP_TIMEOUT', 5),
    ],

    'cart' => [
        'url' => env('CART_SERVICE_URL', 'http://localhost:3001'),
        'timeout' => (int) env('HTTP_TIMEOUT', 5),
    ],

];
