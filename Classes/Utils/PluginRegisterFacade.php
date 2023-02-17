<?php

namespace Codemacher\TileProxy\Utils;

use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\ArrayUtility;

use Codemacher\TileProxy\Domain\Model\Plugin;


class PluginRegisterFacade
{
  static protected $pluginsToConfigure = [];
  static protected $pluginsToRegister = [];

  public static function configureAllPlugins()
  {
    /** @var Plugin $plugin */
    foreach (self::$pluginsToConfigure as $plugin) {
      ExtensionUtility::configurePlugin(
        $plugin->getExtensionKey(),
        $plugin->getPluginName(),
        $plugin->getControllerActions(),
        $plugin->getNonCacheableControllerActions(),
        $plugin->getPluginType()
      );
      self::addToWizard($plugin);
    }
    self::$pluginsToConfigure = [];
  }

  private static function buildDefValues(Plugin $plugin): string
  {
    $typeId = self::getPluginSignature($plugin);
    if ($plugin->getPluginType() == ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT) {
      return "
        tt_content_defValues {
          CType = $typeId
        }";
    }
    return "
      tt_content_defValues {
        CType = list
        list_type = $typeId
      }";
  }

  private static function buildPreViewRenderingDefinition(Plugin $plugin): string
  {
    $underscoreName = GeneralUtility::camelCaseToLowerCaseUnderscored($plugin->getExtensionKey());
    $contentType =  self::getExtensionShortName($plugin)  . '_' . self::getPluginId($plugin);
    if (!empty($plugin->getPreViewTemplateName())) {
      $preViewTemplateName = $plugin->getPreViewTemplateName();
      return "web_layout.tt_content.preview.$contentType = EXT:$underscoreName/Resources/Private/Templates/ContentElementsPreview/$preViewTemplateName";
    }
    return  "";
  }

  private static function getIconIdentifier(Plugin $plugin): string
  {
    $underscoreName = GeneralUtility::camelCaseToLowerCaseUnderscored($plugin->getExtensionKey());
    return "ext-$underscoreName-content-" . self::getPluginId($plugin) . '-icon';
  }

  private static function registerIconsForPlugin(Plugin $plugin)
  {
    $iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
    $iconRegistry->registerIcon(
      self::getIconIdentifier($plugin),
      SvgIconProvider::class,
      ['source' => self::getIconFilePath($plugin)]
    );
  }

  private static function getSpeakingNameDefinition(Plugin $plugin): string
  {
    $underscoreName = GeneralUtility::camelCaseToLowerCaseUnderscored($plugin->getExtensionKey());
    return "LLL:EXT:$underscoreName/Resources/Private/Language/locallang_be.xlf:content_element." . self::getPluginId($plugin);
  }

  private static function getSpeakingDescriptionDefinition(Plugin $plugin): string
  {
    $underscoreName = GeneralUtility::camelCaseToLowerCaseUnderscored($plugin->getExtensionKey());
    return "LLL:EXT:$underscoreName/Resources/Private/Language/locallang_be.xlf:content_element." . self::getPluginId($plugin) . ".description";
  }

  private static function addToWizard(Plugin $plugin)
  {
    $wizardGroupId = $plugin->getWizardGroupId();
    if (empty($wizardGroupId)) return;
    $typeId = self::getPluginSignature($plugin);
    self::registerIconsForPlugin($plugin);
    $tsconfig = "
    mod {
        wizards.newContentElement.wizardItems.$wizardGroupId {
        elements {
            $typeId {
                iconIdentifier = " . self::getIconIdentifier($plugin) . "
                title = " . self::getSpeakingNameDefinition($plugin) . "
                description = " . self::getSpeakingDescriptionDefinition($plugin) . "
                " . self::buildDefValues($plugin) . "
            }   
        }
        show := addToList($typeId)
      }
      " . self::buildPreViewRenderingDefinition($plugin) . "
    }
    ";
    ExtensionManagementUtility::addPageTSConfig($tsconfig);
  }

  private static function buildFlexFormPathKey(Plugin $plugin) : string {
    $underscoreName = GeneralUtility::camelCaseToLowerCaseUnderscored($plugin->getExtensionKey());
    return "FILE:EXT:" . $underscoreName . "/Configuration/FlexForms/" . $plugin->getPluginName() . ".xml";
  }

  private static function registerListPlugin(Plugin $plugin)
  {
    $underscoreName = GeneralUtility::camelCaseToLowerCaseUnderscored($plugin->getExtensionKey());
    ExtensionUtility::registerPlugin(
      $plugin->getExtensionKey(),
      $plugin->getPluginName(),
      self::getSpeakingNameDefinition($plugin),
      self::getIconFilePath($plugin)
    );

    if ($plugin->isFlexFromEnabled()) {
      $pluginSignature =  self::getExtensionShortName($plugin)  . '_' . self::getPluginId($plugin);
      $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
      ExtensionManagementUtility::addPiFlexFormValue(
        $pluginSignature,
        self::buildFlexFormPathKey($plugin)
      );
    }
  }

  private static function registerContentType(Plugin $plugin)
  {

    $contentType =  self::getExtensionShortName($plugin)  . '_' . self::getPluginId($plugin);
    $underscoreName = GeneralUtility::camelCaseToLowerCaseUnderscored($plugin->getExtensionKey());
    $customConfig = $plugin->getCustomConfig();
    $config = array_merge(
      [
        'showitem' => implode(',', $plugin->getShowItemsConfiguration()),
      ],
      $customConfig
    );

    ArrayUtility::mergeRecursiveWithOverrule($GLOBALS['TCA']['tt_content'], [
      'ctrl' => [
        'typeicon_classes' => [
          $contentType => self::getIconIdentifier($plugin),
        ],
      ],
      'types' => [
        $contentType => $config,
      ],
    ]);

    if ($plugin->isFlexFromEnabled()) {
      $flexFormDefinition = [
        'columns' => [
          'pi_flexform' => [
            'config' => [
              'ds' => [
                '*,' . $contentType => self::buildFlexFormPathKey($plugin),
              ],
            ],
          ],
        ],
      ];
      ArrayUtility::mergeRecursiveWithOverrule($GLOBALS['TCA']['tt_content'], $flexFormDefinition);
    }


    ExtensionManagementUtility::addTcaSelectItem(
      'tt_content',
      'CType',
      [
        self::getSpeakingNameDefinition($plugin),
        $contentType,
        self::getIconIdentifier($plugin),
      ]
    );
  }

  public static function registerAllPlugins()
  {

    /** @var Plugin $plugin */
    foreach (self::$pluginsToRegister as $plugin) {
      if ($plugin->getPluginType() == ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT) {
        self::registerContentType($plugin);
      } else {
        self::registerListPlugin($plugin);
      }
    }
    self::$pluginsToRegister = [];
  }

  public static function definePlugin(Plugin $plugin): Plugin
  {
    self::$pluginsToConfigure[] = $plugin;
    self::$pluginsToRegister[] = $plugin;
    return $plugin;
  }

  private static function getPluginId(Plugin $plugin): string
  {
    return strtolower($plugin->getPluginName());
  }

  private static function getIconFilePath(Plugin $plugin): string
  {
    $fileName = $plugin->getIconFileName();
    $underscoreName = GeneralUtility::camelCaseToLowerCaseUnderscored($plugin->getExtensionKey());
    $result = 'EXT:' . $underscoreName . '/Resources/Public/Icons/' .  $fileName;
    return $result;
  }

  private static function getExtensionShortName(Plugin $plugin): string
  {
    $extensionName = preg_replace('/[\s,_]+/', '', $plugin->getExtensionKey());
    return strtolower($extensionName);
  }

  private static function getPluginSignature(Plugin $plugin): string
  {
    return self::getExtensionShortName($plugin) . '_' . self::getPluginId($plugin);
  }
}
