<?php
defined('TYPO3') or die('Access denied.');

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;

use Codemacher\TileProxy\Domain\Model\Plugin;
use Codemacher\TileProxy\Utils\PluginRegisterFacade;
use Codemacher\TileProxy\Cache\CacheBackend;
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

  $iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
  $iconRegistry
    ->registerIcon(
      'tile-proxy',
      SvgIconProvider::class,
      [
        'source' => 'EXT:' . $extKey . '/Resources/Public/Icons/apps-pagetree-page-tileproxy.svg',
      ]
    );

  // Allow backend users to drag and drop the new page type:
  ExtensionManagementUtility::addUserTSConfig(
    'options.pageTree.doktypesToShowInNewPageDragArea := addToList(' . Constants::DOKTYPE . ')'
  );

  $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1676502776] = [
    'nodeName' => 'boundingboxmap',
    'priority' => 40,
    'class' => \Codemacher\TileProxy\Form\Element\BoundingBoxMapElement::class,
  ];


  PluginRegisterFacade::definePlugin(new Plugin(
    'TileProxy',
    'Map',
  ))
  ->setIconFileName("map.svg")
    ->addShowItemConfig([
      '--palette--;;headers',
    ]);

  PluginRegisterFacade::configureAllPlugins();
})();
