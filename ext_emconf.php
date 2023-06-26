
<?php
$EM_CONF[$_EXTKEY] = [
	'title' => 'Tile Proxy',
	'description' => 'With Tile Proxy you can integrate OpenStreetMap maps GDPR in a compliant way without any third party requests from the client. For a given area, the data is loaded from OpenStreetMap and cached, therefore you do not need a cookie banner or blocker to use OpenStreetMap cookie banner or blocker to use OpenStreetMap.	A proxy is also provided for geocoding nominatim.',
	'author' => 'Thomas Rokohl (codemacher)',
	'author_email' => 'mail@codemacher.de',
	'category' => 'plugin',
	'author_company' => 'codemacher',
	'state' => 'stable',
	'clearCacheOnLoad' => 1,
	'version' => '1.1.9',
	'constraints' => [
		'depends' => [
			'typo3' => '11.5.0-12.4.99'
		],
		'conflicts' => [],
		'suggests' => []
	]
];
