<?php

declare(strict_types=1);

namespace Codemacher\TileProxy\Form\Element;

use Codemacher\TileProxy\Constants;
use Codemacher\TileProxy\Utils\FolderUtil;
use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class CacheInfoElement extends AbstractFormElement
{
    public function render(): array
    {
        $cacheDir = Environment::getVarPath() . Constants::CACHE_DIR;
        $fieldInformationResult = $this->renderFieldInformation();
        $fieldInformationHtml = $fieldInformationResult['html'];
        $resultArray = $this->mergeChildReturnIntoExistingResult($this->initializeResultArray(), $fieldInformationResult, false);
        $folderInfo = FolderUtil::getFolderInfo($cacheDir);

        $infoStr = LocalizationUtility::translate("cacheinfo", "tile_proxy", [FolderUtil::formatFilesize($folderInfo["size"]), $folderInfo["files"]]);

        $html = [];
        $html[] = '<div class="formengine-field-item t3js-formengine-field-item">';
        $html[] = $fieldInformationHtml;
        $html[] = '<div class="form-wizards-wrap">';
        $html[] = '<div class="form-wizards-element">';
        $html[] = '<div class="form-control-wrap">';
        $html[] =  $infoStr;
        $html[] = '</div>';
        $html[] = '</div>';
        $html[] = '</div>';
        $html[] = '</div>';
        $resultArray['html'] = implode(chr(10), $html);

        return $resultArray;
    }
}
