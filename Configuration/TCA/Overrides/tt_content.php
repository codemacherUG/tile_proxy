<?php

defined('TYPO3') or die();

use Codemacher\TileProxy\Utils\PluginRegisterFacade;

call_user_func(function () {
  PluginRegisterFacade::registerAllPlugins();
});
