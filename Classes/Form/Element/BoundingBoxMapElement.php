<?php

declare(strict_types=1);

namespace Codemacher\TileProxy\Form\Element;

use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;

class BoundingBoxMapElement extends AbstractFormElement
{
    public function render(): array
    {
        $parameterArray = $this->data['parameterArray'];

        $fieldInformationResult = $this->renderFieldInformation();
        $fieldInformationHtml = $fieldInformationResult['html'];
        $resultArray = $this->mergeChildReturnIntoExistingResult($this->initializeResultArray(), $fieldInformationResult, false);

        $fieldId = StringUtility::getUniqueId('formengine-textarea-');

        $attributes = [
            'id' => $fieldId,
            'name' => htmlspecialchars($parameterArray['itemFormElName']),
            'size' => '30',
            'data-formengine-input-name' => htmlspecialchars($parameterArray['itemFormElName'])
        ];


        $classes = [
            'form-control',
            't3js-formengine-textarea',
            'formengine-textarea',
        ];
        $itemValue = $parameterArray['itemFormElValue'];
        $attributes['class'] = implode(' ', $classes);

        $html = [];
        $html[] = '<div class="formengine-field-item t3js-formengine-field-item bboxmap" style="padding: 5px;">';
        $html[] = $fieldInformationHtml;
        $html[] =   '<div class="form-wizards-wrap">';
        $html[] =      '<div class="form-wizards-element">';
        $html[] =         '<div class="form-control-wrap">';
        $html[] =            '<input type="text" value="' . htmlspecialchars($itemValue, ENT_QUOTES) . '" ';
        $html[] =               GeneralUtility::implodeAttributes($attributes, true);
        $html[] =            ' />';
        $html[] =         '</div>';
        $html[] =         '<div class="map" style="width:100%; height:400px"></div>';
        $html[] =      '</div>';
        $html[] =   '</div>';
        $html[] = '</div>';
        $resultArray['html'] = implode(LF, $html);
        $resultArray['requireJsModules'] = array(
            'TYPO3/CMS/TileProxy/BoundingBoxMapElement'
        );
        $resultArray['stylesheetFiles'][] = 'EXT:tile_proxy/Resources/Public/Css/BoundingBoxMapElement.css';
        return $resultArray;
    }
}
