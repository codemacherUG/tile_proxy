<?php


use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\ArrayUtility;

use Codemacher\TileProxy\Constants;

(function ($extKey = 'tile_proxy', $table = 'pages') {

  // Add new page type as possible select item:
  ExtensionManagementUtility::addTcaSelectItem(
    $table,
    'doktype',
    [
      'LLL:EXT:' . $extKey . '/Resources/Private/Language/locallang.xlf:api_dok_type',
      Constants::DOKTYPE,
      'EXT:' . $extKey . '/Resources/Public/Icons/apps-pagetree-page-tileproxy.svg',
      'special'

    ],
    '1',
    'after'
  );

  ArrayUtility::mergeRecursiveWithOverrule(
    $GLOBALS['TCA'][$table],
    [
      'columns' => [
        'tx_tileproxy_flexform' => [
          'label' => 'LLL:EXT:tile_proxy/Resources/Private/Language/locallang.xlf:tx_tileproxy_flexform',
          'exclude' => 1,
          'config' => [
            'type' => 'flex',
            'ds' => [
              'default' => 'FILE:EXT:tile_proxy/Configuration/FlexForms/Main.xml',
            ],
          ],
        ],
      ],
      // add icon for new page type:
      'ctrl' => [
        'typeicon_classes' => [
          Constants::DOKTYPE => 'tile-proxy',
          Constants::DOKTYPE . '-contentFromPid' => "tile-proxy",
          Constants::DOKTYPE . '-root' => "tile-proxy",
          Constants::DOKTYPE . '-hideinmenu' => "tile-proxy",
        ],
      ],
      'palettes' => [
        'title_tileproxy' => [
          'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.palettes.title',
          'showitem' => 'title;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.title_formlabel, --linebreak--, slug',
        ],
      ],
      // add all page standard fields and tabs to your new page type
      'types' => [
        Constants::DOKTYPE => [
          'showitem' => '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                    --palette--;;standard, 
                    --palette--;;title_tileproxy,
                    tx_tileproxy_flexform,
                   --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.tabs.access, 
                    --palette--;;visibility, 
                    --palette--;;access, 
                  --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes, rowDescription, 
                  '
        ]
      ]
    ]
  );
})();
