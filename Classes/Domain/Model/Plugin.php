<?php

namespace Codemacher\TileProxy\Domain\Model;

use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use Codemacher\TileProxy\Controller\ContentElementController;

class Plugin
{

  public function __construct(string $extensionKey, string $pluginName)
  {
    $this->extensionKey = $extensionKey;
    $this->pluginName = $pluginName;
  }

  /**
   * @var string
   */
  protected $extensionKey;

  /**
   * @var string
   */
  protected $pluginName;

  /**
   * @var array
   */
  protected $controllerActions = [ContentElementController::class => 'generic'];

  /**
   * @var array
   */
  protected $nonCacheableControllerActions = [ContentElementController::class => ''];

  /**
   * @var string
   */
  protected $pluginType = ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT;

  /**
   * @var string
   */
  protected $iconFileName = "GenericContentElement.svg";

  /**
   * @var string
   */
  protected $wizardGroupId = "common";

  /**
   * @var bool
   */
  protected $enableFlexFrom = false;

  /**
   * @var string
   */
  protected $preViewTemplateName;

  /**
   * @var array
   */
  protected $customConfig = [];

  /**
   * @var array
   */
  protected array $defaultShowItemsConfiguration = [
    '--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance,--palette--;;frames,--palette--;;appearanceLinks,',
    '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,--palette--;;language,',
    '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
            --palette--;;hidden,
            --palette--;;access,
          --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,
               categories,
          --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,
               rowDescription,
          --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended,'
  ];

  /**
   * @var array
   */
  protected array $showItemsGeneralOptions = [
    '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general',
    '--palette--;;general',
  ];

  /**
   * Get the ShowItems Configuration (only for PluginType CType)
   *
   * @return array
   */
  public function getShowItemsConfiguration() : array
  {
    return array_merge($this->showItemsGeneralOptions, $this->defaultShowItemsConfiguration);
  }

  /**
   * Add ShowItem TCA Config
   *
   * @param array $options
   * @return  Plugin
   */
  public function addShowItemConfig(array $options): Plugin
  {
    $this->showItemsGeneralOptions = array_merge($this->showItemsGeneralOptions, $options);
    return $this;
  }

  /**
   * Add custom TCA 
   *
   * @param array $customConfig
   * @return  Plugin
   */
  public function addCustomConfig(array $customConfig): Plugin
  {
    $this->customConfig = array_merge($this->customConfig, $customConfig);
    return $this;
  }

  /**
   * Get custom Config
   *
   * @return  array
   */
  public function getCustomConfig(): array
  {
    return $this->customConfig;
  }


  /**
   * Get the TemplateName for preView
   *
   * @return  string
   */
  public function getPreViewTemplateName() : ?string
  {
    return $this->preViewTemplateName;
  }

  /**
   * Set the TemplateName for preView
   *
   * @param string $preViewTemplateName
   * @return  Plugin
   */
  public function setPreViewTemplateName(string $preViewTemplateName): Plugin
  {
    $this->preViewTemplateName = $preViewTemplateName;
    return $this;
  }


  /**
   * Get the value of pluginType
   *
   * @return  string
   */
  public function getPluginType() : string
  {
    return $this->pluginType;
  }

  /**
   * Set the value of pluginType
   *
   * @param string $pluginType
   * @return  Plugin
   */
  public function setPluginType(string $pluginType): Plugin
  {
    $this->pluginType = $pluginType;
    return $this;
  }

  /**
   * Get the value of nonCacheableControllerActions
   *
   * @return  array
   */
  public function getNonCacheableControllerActions() : array
  {
    return $this->nonCacheableControllerActions;
  }

  /**
   * Set non cacheable controller actions
   *
   * @param array $nonCacheableControllerActions
   * @return  Plugin
   */
  public function setNonCacheableControllerActions(array $nonCacheableControllerActions): Plugin
  {
    $this->nonCacheableControllerActions = $nonCacheableControllerActions;
    return $this;
  }

  /**
   * Get the value of controllerActions
   *
   * @return  array
   */
  public function getControllerActions() : array
  {
    return $this->controllerActions;
  }

  /**
   * Set controller actions
   *
   * @param array $controllerActions
   * @return  Plugin
   */
  public function setControllerActions(array $controllerActions): Plugin
  {
    $this->controllerActions = $controllerActions;
    return $this;
  }


  /**
   * Get the value of pluginName
   *
   * @return  string
   */
  public function getPluginName() : string
  {
    return $this->pluginName;
  }

  /**
   * Get the value of extensionKey
   *
   * @return  string
   */
  public function getExtensionKey() : string
  {
    return $this->extensionKey;
  }
  /**
   * Get the value of iconFileName
   *
   * @return  string
   */
  public function getIconFileName() : string
  {
    return $this->iconFileName;
  }

  /**
   * Set the value of iconFileName
   *
   * @param string $iconFileName
   * @return  Plugin
   */
  public function setIconFileName(string $iconFileName): Plugin
  {
    $this->iconFileName = $iconFileName;
    return $this;
  }

  /**
   * Get the value of enableFlexFrom
   *
   * @return bool
   */
  public function isFlexFromEnabled() : bool
  {
    if($this->enableFlexFrom) return true;
    if(in_array("pi_flexform",$this->showItemsGeneralOptions)) return true;
    return false;
  }

  /**
   * enableFlexFrom
   *
   * @return  Plugin
   */
  public function enableFlexFrom(): Plugin
  {
    $this->enableFlexFrom = true;
    return $this;
  }

  /**
   * disableFlexFrom
   *
   * @return  Plugin
   */
  public function disableFlexFrom(): Plugin
  {
    $this->enableFlexFrom = false;
    return $this;
  }

  /**
   * Get the value of wizardGroupId
   *
   * @return  string
   */
  public function getWizardGroupId() : string
  {
    return $this->wizardGroupId;
  }


  /**
   * Set the value of wizardGroupId
   *
   * @param string $wizardGroupId
   * @return  Plugin
   */
  public function setWizardGroupId(string $wizardGroupId): Plugin
  {
    $this->wizardGroupId = $wizardGroupId;
    return $this;
  }
}
