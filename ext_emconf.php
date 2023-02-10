
<?php
$EM_CONF[$_EXTKEY] = [
	'title' => 'TileProxy',
	'description' => 'Integrates an OpenStreetMap tile proxy for GDPR compliant integration.',
	'author' => 'Thomas Rokohl',
	'author_email' => 'webmaster@codemacher.de',
	'category' => 'plugin',
	'author_company' => 'codemacher',
	'state' => 'stable',
	'clearCacheOnLoad' => 1,
	'version' => '1.0.2',
	'constraints' => [
		'depends' => [
			'typo3' => '11.5.0-11.5.99'
		],
		'conflicts' => [],
		'suggests' => []
	]
];
