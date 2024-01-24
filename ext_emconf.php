
<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'Tile Proxy',
    'description' => 'Integrate OpenStreetMap without the need for a cookie banner or content blocker - that is the purpose of ext:tile_maps. All requests for map tiles are routed through a configurable proxy url within your TYPO3-system. Since the client browser does not directly requests the OpenStreetMap-server no user confirmation and no content blockers are necessary (GDPR compliant). A proxy is also provided for geocoding nominatim. For a certain area (as specified in the TYPO3-backend) the tiles are cached to improve performance.',
    'author' => 'Thomas Rokohl (codemacher)',
    'author_email' => 'mail@codemacher.de',
    'category' => 'plugin',
    'author_company' => 'codemacher',
    'state' => 'stable',
    'clearCacheOnLoad' => 1,
    'version' => '1.2.4',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.0-12.4.99'
        ],
        'conflicts' => [],
        'suggests' => []
    ]
];
