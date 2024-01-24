<?php

return [
    'frontend' => [
        'codemacher/tile-proxy/request-handler' => [
            'target' => \Codemacher\TileProxy\Middleware\TileProxyMiddleware::class,
            'before' => [
                'typo3/cms-frontend/page-argument-validator'
            ]
        ],

    ],
];
