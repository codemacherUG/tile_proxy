
<?php
$EM_CONF[$_EXTKEY] = [
	'title' => 'TileProxy',
	'description' => 'Integrates an OpenStreetMap tile proxy for GDPR compliant integration.',
	'author' => 'Thomas Rokohl (codemacher)',
	'author_email' => 'webmaster@codemacher.de',
	'category' => 'plugin',
	'author_company' => 'codemacher',
	'state' => 'stable',
	'clearCacheOnLoad' => 1,
	'version' => '1.0.4',
	'constraints' => [
		'depends' => [
			'typo3' => '11.5.0-12.2.99'
		],
		'conflicts' => [],
		'suggests' => []
	]
];
