<?php

use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use Codemacher\TileProxy\Constants;

(function ($extKey = 'tile_proxy', $table = 'pages') {

    $isV14 = GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() >= 14;

    $GLOBALS['TCA']['pages']['types'][Constants::DOKTYPE_TILE_PROXY]['allowedRecordTypes'] = [
      'pages',
      '*',
    ];

    $GLOBALS['TCA']['pages']['types'][Constants::DOKTYPE_NOMINATIM_PROXY]['allowedRecordTypes'] = [
      'pages',
      '*',
    ];

    ExtensionManagementUtility::addTcaSelectItem(
        $table,
        'doktype',
        [
          'label' => 'LLL:EXT:' . $extKey . '/Resources/Private/Language/locallang.xlf:doktype_tile_proxy',
          'value' => Constants::DOKTYPE_TILE_PROXY,
          'icon' => 'tile-proxy',
          'group' => 'special',
        ],
        '1',
        'after'
    );

    ExtensionManagementUtility::addTcaSelectItem(
        $table,
        'doktype',
        [
          'label' => 'LLL:EXT:' . $extKey . '/Resources/Private/Language/locallang.xlf:doktype_nominatim_proxy',
          'value' => Constants::DOKTYPE_NOMINATIM_PROXY,
          'icon' => 'nominatim-proxy',
          'group' => 'special',
        ],
        '1',
        'after'
    );

    // v14: ds is a string, type-specific DS via columnsOverrides
    // v13: ds is an array, type-specific DS via ds_pointerField
    if ($isV14) {
        $flexConfig = [
          'type' => 'flex',
          'ds' => 'FILE:EXT:tile_proxy/Configuration/FlexForms/TileProxy.xml',
        ];
    } else {
        $flexConfig = [
          'type' => 'flex',
          'ds_pointerField' => 'doktype',
          'ds' => [
            Constants::DOKTYPE_TILE_PROXY => 'FILE:EXT:tile_proxy/Configuration/FlexForms/TileProxy.xml',
            Constants::DOKTYPE_NOMINATIM_PROXY => 'FILE:EXT:tile_proxy/Configuration/FlexForms/NominatimProxy.xml',
            'default' => 'FILE:EXT:tile_proxy/Configuration/FlexForms/TileProxy.xml',
          ],
        ];
    }

    $tileProxyType = [
      'showitem' => '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                    --palette--;;standard, 
                    --palette--;;title_tileproxy,
                    tx_tileproxy_flexform,
                   --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.tabs.access, 
                    --palette--;;visibility, 
                    --palette--;;access, 
                  --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes, rowDescription, 
                  ',
    ];

    $nominatimProxyType = [
      'showitem' => '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                    --palette--;;standard, 
                    --palette--;;title_tileproxy,
                    tx_tileproxy_flexform,
                   --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.tabs.access, 
                    --palette--;;visibility, 
                    --palette--;;access, 
                  --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes, rowDescription, 
                  ',
    ];

    if ($isV14) {
        $tileProxyType['columnsOverrides'] = [
          'tx_tileproxy_flexform' => [
            'config' => [
              'ds' => 'FILE:EXT:tile_proxy/Configuration/FlexForms/TileProxy.xml',
            ],
          ],
        ];
        $nominatimProxyType['columnsOverrides'] = [
          'tx_tileproxy_flexform' => [
            'config' => [
              'ds' => 'FILE:EXT:tile_proxy/Configuration/FlexForms/NominatimProxy.xml',
            ],
          ],
        ];
    }

    ArrayUtility::mergeRecursiveWithOverrule(
        $GLOBALS['TCA'][$table],
        [
        'columns' => [
          'tx_tileproxy_flexform' => [
            'label' => 'LLL:EXT:tile_proxy/Resources/Private/Language/locallang.xlf:tx_tileproxy_flexform',
            'exclude' => 1,
            'config' => $flexConfig,
          ],
        ],
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
        'types' => [
          Constants::DOKTYPE_TILE_PROXY => $tileProxyType,
        ]
    ]
    );

    ArrayUtility::mergeRecursiveWithOverrule(
        $GLOBALS['TCA'][$table],
        [
        'ctrl' => [
          'typeicon_classes' => [
            Constants::DOKTYPE_NOMINATIM_PROXY => 'nominatim-proxy',
            Constants::DOKTYPE_NOMINATIM_PROXY . '-contentFromPid' => "nominatim-proxy",
            Constants::DOKTYPE_NOMINATIM_PROXY . '-root' => "nominatim-proxy",
            Constants::DOKTYPE_NOMINATIM_PROXY . '-hideinmenu' => "nominatim-proxy",
          ],
        ],
        'types' => [
          Constants::DOKTYPE_NOMINATIM_PROXY => $nominatimProxyType,
        ]
    ]
    );
})();
