<?php

declare(strict_types=1);

namespace Codemacher\TileProxy\Form\Element;

use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;
use TYPO3\CMS\Core\Information\Typo3Version;

class BoundingBoxMapElement extends MapElement
{
    protected string $mapClassName = "bboxmap";

    protected function createAttributes(array $parameterArray): array
    {
        $attributes = parent::createAttributes($parameterArray);
        $decimalPlaces = $parameterArray['fieldConf']['config']['parameters']['decimalPlaces'] ?? 2;
        $attributes['data-decimalplaces']  = $decimalPlaces;
        return $attributes;
    }

    protected function enrichResultArray(array $resultArray): array
    {
        $resultArray = parent::enrichResultArray($resultArray);
        $typo3Version = new Typo3Version();
        if ($typo3Version->getMajorVersion() < 12) {
            $resultArray['requireJsModules'][] = JavaScriptModuleInstruction::forRequireJS(
                'TYPO3/CMS/TileProxy/BoundingBoxMapElement'
            );
        } else {
            /** @phpstan-ignore-next-line */
            $resultArray['javaScriptModules'][] = JavaScriptModuleInstruction::create('@codemacher/tile_proxy/BoundingBoxMapElement.js');
        }

        $resultArray['stylesheetFiles'][] = 'EXT:tile_proxy/Resources/Public/Css/MapElement.css';
        return $resultArray;
    }
}
