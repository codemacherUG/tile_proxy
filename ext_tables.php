<?php

use TYPO3\CMS\Core\DataHandling\PageDoktypeRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

defined('TYPO3') or die();

(static function (): void {

  $dokTypeRegistry = GeneralUtility::makeInstance(PageDoktypeRegistry::class);
  $dokTypeRegistry->add(
    Codemacher\TileProxy\Constants::DOKTYPE_TILE_PROXY,
    [
          'allowedTables' => '*',
      ],
  );

  $dokTypeRegistry->add(
    Codemacher\TileProxy\Constants::DOKTYPE_NOMINATIM_PROXY,
    [
          'allowedTables' => '*',
      ],
  );

})();
