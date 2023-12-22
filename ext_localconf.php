<?php
defined('TYPO3') or die('Access denied.');

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
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

  $iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
  $iconRegistry
    ->registerIcon(
      'tile-proxy',
      SvgIconProvider::class,
      [
        'source' => 'EXT:' . $extKey . '/Resources/Public/Icons/doktype-tileproxy.svg',
      ]
    );

  $iconRegistry
    ->registerIcon(
      'nominatim-proxy',
      SvgIconProvider::class,
      [
        'source' => 'EXT:' . $extKey . '/Resources/Public/Icons/doktype-nominatimproxy.svg',
      ]
    );

  // Allow backend users to drag and drop the new page type:
  ExtensionManagementUtility::addUserTSConfig(
    'options.pageTree.doktypesToShowInNewPageDragArea := addToList(' . Constants::DOKTYPE_TILE_PROXY . ',' .  Constants::DOKTYPE_NOMINATIM_PROXY . ')'
  );



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
