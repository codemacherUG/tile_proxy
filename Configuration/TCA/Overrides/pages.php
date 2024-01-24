<?php


use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\ArrayUtility;

use Codemacher\TileProxy\Constants;

(function ($extKey = 'tile_proxy', $table = 'pages') {

    // Add new page types as possible select item:
    ExtensionManagementUtility::addTcaSelectItem(
        $table,
        'doktype',
        [
        'LLL:EXT:' . $extKey . '/Resources/Private/Language/locallang.xlf:doktype_tile_proxy',
        Constants::DOKTYPE_TILE_PROXY,
        'EXT:' . $extKey . '/Resources/Public/Icons/doktype-tileproxy.svg',
        'special'

    ],
        '1',
        'after'
    );

    ExtensionManagementUtility::addTcaSelectItem(
        $table,
        'doktype',
        [
        'LLL:EXT:' . $extKey . '/Resources/Private/Language/locallang.xlf:doktype_nominatim_proxy',
        Constants::DOKTYPE_NOMINATIM_PROXY,
        'EXT:' . $extKey . '/Resources/Public/Icons/doktype-nominatimproxy.svg',
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
              'ds_pointerField' => 'doktype',
              'ds' => [
                Constants::DOKTYPE_TILE_PROXY => 'FILE:EXT:tile_proxy/Configuration/FlexForms/TileProxy.xml',
                Constants::DOKTYPE_NOMINATIM_PROXY => 'FILE:EXT:tile_proxy/Configuration/FlexForms/NominatimProxy.xml',
               'default' => 'FILE:EXT:tile_proxy/Configuration/FlexForms/Empty.xml',
              ],
            ],
          ],
        ],
        // add icon for new page type:
        'ctrl' => [
          'typeicon_classes' => [
            Constants::DOKTYPE_TILE_PROXY => 'tile-proxy',
            Constants::DOKTYPE_TILE_PROXY . '-contentFromPid' => "tile-proxy",
            Constants::DOKTYPE_TILE_PROXY . '-root' => "tile-proxy",
            Constants::DOKTYPE_TILE_PROXY . '-hideinmenu' => "tile-proxy",
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
          Constants::DOKTYPE_TILE_PROXY => [
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

    ArrayUtility::mergeRecursiveWithOverrule(
        $GLOBALS['TCA'][$table],
        [
        // add icon for new page type:
        'ctrl' => [
          'typeicon_classes' => [
            Constants::DOKTYPE_NOMINATIM_PROXY => 'nominatim-proxy',
            Constants::DOKTYPE_NOMINATIM_PROXY . '-contentFromPid' => "nominatim-proxy",
            Constants::DOKTYPE_NOMINATIM_PROXY . '-root' => "nominatim-proxy",
            Constants::DOKTYPE_NOMINATIM_PROXY . '-hideinmenu' => "nominatim-proxy",
          ],
        ],
        // add all page standard fields and tabs to your new page type
        'types' => [
          Constants::DOKTYPE_NOMINATIM_PROXY => [
            'showitem' => '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                    --palette--;;standard, 
                    --palette--;;title_tileproxy,
                    tx_tileproxy_flexform,
                   --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.tabs.access, 
                    --palette--;;visibility, 
                    --palette--;;access, 
                  --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes, rowDescription, 
                  ',
          ]
        ]
    ]
    );
})();
