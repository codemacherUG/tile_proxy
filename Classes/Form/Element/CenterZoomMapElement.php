<?php

declare(strict_types=1);

namespace Codemacher\TileProxy\Form\Element;

use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;
use TYPO3\CMS\Core\Information\Typo3Version;

class CenterZoomMapElement extends MapElement
{
    protected string $mapClassName = "centerzoommap";

    protected function enrichResultArray(array $resultArray): array
    {
        $resultArray = parent::enrichResultArray($resultArray);
        $resultArray['javaScriptModules'][] = JavaScriptModuleInstruction::create('@codemacher/tile_proxy/CenterZoomMapElement.js');
        $resultArray['stylesheetFiles'][] = 'EXT:tile_proxy/Resources/Public/Css/MapElement.css';
        return $resultArray;
    }
}
