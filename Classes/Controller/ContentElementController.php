<?php

namespace Codemacher\TileProxy\Controller;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class ContentElementController extends ActionController
{

  public function genericAction()
  {
    $contentObj = $this->configurationManager->getContentObject();
    $this->view->assign("data", $contentObj->data);
  }

  protected function resolveView()
  {
    /** @var TYPO3\CMS\Fluid\View\TemplateView $view */
    $view = parent::resolveView();
    $actionName = $this->request->getControllerActionName();
    if ($actionName == "generic") {
      $view->setTemplate($this->request->getPluginName());
    }
    return $view;
  }
}
