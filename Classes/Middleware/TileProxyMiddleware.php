<?php

declare(strict_types=1);

namespace Codemacher\TileProxy\Middleware;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Http\JsonResponse;

use Codemacher\TileProxy\Constants;
use Codemacher\TileProxy\Controller\TileProxyController;
use Codemacher\TileProxy\Controller\NominatimProxyController;

class TileProxyMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $params = $request->getQueryParams();
        if (array_key_exists('provider', $params)) {
            $pageArguments = $request->getAttribute('routing', null);
            $pageRecord = BackendUtility::getRecord("pages", $pageArguments['pageId']);
            switch ($pageRecord['doktype']) {
                case Constants::DOKTYPE_TILE_PROXY:
                    return $this->performTileProxy($request, $handler, $pageRecord);
                case Constants::DOKTYPE_NOMINATIM_PROXY:
                    return $this->performNominatimProxy($request, $handler, $pageRecord);
            }
        }
        return $handler->handle($request);
    }

    protected function performTileProxy(ServerRequestInterface $request, RequestHandlerInterface $handler, array $pageRecord): ResponseInterface
    {
        return $this->performProxy(TileProxyController::class, $request,$handler, $pageRecord);
    }

    protected function performNominatimProxy(ServerRequestInterface $request, RequestHandlerInterface $handler, array $pageRecord): ResponseInterface
    {
        return $this->performProxy(NominatimProxyController::class, $request,$handler, $pageRecord);
    }

    protected function performProxy(string $classname, ServerRequestInterface $request, RequestHandlerInterface $handler, array $pageRecord): ResponseInterface
    {
        if (!$this->fulfilsHostRestrictions()) return new JsonResponse(['error' => Constants::ERROR_INVALID_HOST], 403);
        
        $flexform = array_key_exists('tx_tileproxy_flexform', $pageRecord) ? $pageRecord['tx_tileproxy_flexform'] : "";
        $ffs = GeneralUtility::makeInstance(FlexFormService::class);
        $flex = $ffs->convertFlexFormContentToArray($flexform);
        $flexSettings = $flex != null && array_key_exists("settings", $flex) ? $flex["settings"] : [];

        $proxy =  GeneralUtility::makeInstance($classname);
        return $proxy->process($flexSettings, $request, $handler);
    }

    protected function fulfilsHostRestrictions(): bool
    {
        $referrer = @$_SERVER['HTTP_REFERER'];
        $host = @$_SERVER['HTTP_HOST'];

        $valids = ["://$host", "://www.$host", "://localhost"];

        if (null != $referrer) {
            foreach ($valids as $valid) {
                if (strpos($referrer, $valid) >= 1) {
                    return true;
                }
            }
        }
        return false;
    }
}
