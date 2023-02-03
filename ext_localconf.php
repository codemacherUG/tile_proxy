<?php
defined('TYPO3') or die();

call_user_func(function () {

  $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['tile-proxy-cache'] = [
    'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
    'backend' => \Codemacher\TileProxy\Cache\CacheBackend::class,
    'options' => ['cacheType' => 'cache'],
    'groups' => ['all'],
  ];
});
