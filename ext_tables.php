<?php

defined('TYPO3') or die();

(static function (): void {
  // Add new page type:
  $GLOBALS['PAGES_TYPES'][Codemacher\TileProxy\Constants::DOKTYPE] = [
      'type' => 'web',
      'allowedTables' => '',
  ];

})();