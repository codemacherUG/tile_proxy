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


    private function getDirectorySize($path)
    {
        $bytestotal = 0;

        if ($path !== false && $path != '' && file_exists($path)) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)) as $object) {
                $bytestotal += $object->getSize();
            }
        }
        return $bytestotal;
    }


    public function render(): array
    {
        $cacheDir = Environment::getVarPath() . Constants::CACHE_DIR;
        $fieldInformationResult = $this->renderFieldInformation();
        $fieldInformationHtml = $fieldInformationResult['html'];
        $resultArray = $this->mergeChildReturnIntoExistingResult($this->initializeResultArray(), $fieldInformationResult, false);
        $folderInfo = FolderUtil::getFolderInfo($cacheDir);

        $infoStr = LocalizationUtility::translate("cacheinfo", "tile_proxy",  [FolderUtil::formatFilesize($folderInfo["size"]), $folderInfo["files"]]);

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
        $resultArray['html'] = implode(LF, $html);

        return $resultArray;
    }
}
