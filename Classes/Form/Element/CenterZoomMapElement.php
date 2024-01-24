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
        $typo3Version = new Typo3Version();
        if ($typo3Version->getMajorVersion() < 12) {
            $resultArray['requireJsModules'][] = JavaScriptModuleInstruction::forRequireJS(
                'TYPO3/CMS/TileProxy/CenterZoomMapElement'
            );
        } else {
            /* @phpstan-ignore-next-line */
            $resultArray['javaScriptModules'][] = JavaScriptModuleInstruction::create('@codemacher/tile_proxy/CenterZoomMapElement.js');
        }

        $resultArray['stylesheetFiles'][] = 'EXT:tile_proxy/Resources/Public/Css/MapElement.css';
        return $resultArray;
    }
}
