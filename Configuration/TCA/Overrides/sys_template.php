<?php

defined('TYPO3') or die();
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

call_user_func(function () {
  ExtensionManagementUtility::addStaticFile("tile_proxy", "Configuration/TypoScript/Frontend", "Tile Proxy Frontend");
});
