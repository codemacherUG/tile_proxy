<?php

defined('TYPO3') or die('Access denied.');

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;

use Codemacher\TileProxy\Cache\CacheBackend;
use Codemacher\TileProxy\Cache\CleanUpDbCacheBackend;
use Codemacher\TileProxy\Constants;

(static function ($extKey = 'tile_proxy'): void {

    $extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class);

    $deleteTileOnCacheCleanUp = intval($extConf->get('tile_proxy', 'deleteTileOnCacheCleanUp') ?? 0);

    if ($deleteTileOnCacheCleanUp) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['tile-proxy-cache'] = [
          'frontend' => VariableFrontend::class,
          'backend' => CacheBackend::class,
          'options' => ['cacheType' => 'cache'],
          'groups' => ['all'], 
        ];
    }

    $deleteNominatimCacheOnCacheCleanUp = intval($extConf->get('tile_proxy', 'deleteNominatimCacheOnCacheCleanUp') ?? 0);
    if ($deleteNominatimCacheOnCacheCleanUp) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['nominatim-proxy-cache'] = [
          'frontend' => VariableFrontend::class,
          'backend' => CleanUpDbCacheBackend::class,
          'options' => ['cacheType' => 'cache'],
          'groups' => ['all'],
        ];
    }


    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1676502776] = [
      'nodeName' => 'boundingboxmap',
      'priority' => 40,
      'class' => \Codemacher\TileProxy\Form\Element\BoundingBoxMapElement::class,
    ];

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1677664804] = [
      'nodeName' => 'centerzoommap',
      'priority' => 40,
      'class' => \Codemacher\TileProxy\Form\Element\CenterZoomMapElement::class,
    ];

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1703249840] = [
      'nodeName' => 'cacheinfo',
      'priority' => 40,
      'class' => \Codemacher\TileProxy\Form\Element\CacheInfoElement::class,
    ];
})();
