
<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'Tile Proxy',
    'description' => 'Integrate OpenStreepMap without the need for a cookie banner or content blocker - that is the purpose of ext:tile_maps. For a given area, the data is loaded from OpenStreetMap and cached. All request can be routed through the proxy and therefore no more requests to third-party providers are necessary (GDPR compliant). A proxy is also provided for geocoding nominatim.',
    'author' => 'Thomas Rokohl (codemacher)',
    'author_email' => 'mail@codemacher.de',
    'category' => 'plugin',
    'author_company' => 'codemacher',
    'state' => 'stable',
    'clearCacheOnLoad' => 1,
    'version' => '1.2.3',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.0-12.4.99'
        ],
        'conflicts' => [],
        'suggests' => []
    ]
];
