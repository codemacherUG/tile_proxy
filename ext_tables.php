<?php

defined('TYPO3') or die();

(function () {
  // Add new page type:
  $GLOBALS['PAGES_TYPES'][Codemacher\TileProxy\Middleware\TileProxyMiddleware::DOKTYPE] = [
      'type' => 'web',
      'allowedTables' => '',
  ];

})();