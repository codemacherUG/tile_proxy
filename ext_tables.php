<?php

defined('TYPO3') or die();

(static function (): void {
  // Add new page types:
  $GLOBALS['PAGES_TYPES'][Codemacher\TileProxy\Constants::DOKTYPE_TILE_PROXY] = [
      'type' => 'web',
      'allowedTables' => '',
  ];
  $GLOBALS['PAGES_TYPES'][Codemacher\TileProxy\Constants::DOKTYPE_NOMINATIM_PROXY] = [
    'type' => 'web',
    'allowedTables' => '',
];

})();