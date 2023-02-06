<?php

namespace Codemacher\TileProxy\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Service\FlexFormService;


use Codemacher\TileProxy\Controller\CachedTileProxyController;

class TileProxyMiddleware implements MiddlewareInterface
{

    CONST DOKTYPE = 601;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        
        $controller = $request->getAttribute('frontend.controller');
        
        if ((int)$controller->page['doktype'] === TileProxyMiddleware::DOKTYPE) {
            $flexform = $controller->page['tx_tileproxy_flexform'];
            $ffs = GeneralUtility::makeInstance(FlexFormService::class);
            $flex = $ffs->convertFlexFormContentToArray($flexform);
            $flexSettings = $flex != null && array_key_exists("settings",$flex) ? $flex["settings"] : [];
            $parms = $request->getQueryParams();
            if (!isset($parms['type'], $parms['s'], $parms['x'], $parms['y'], $parms['z'])) {
                return new JsonResponse(["error" => 1000], 403);
            }
            $referrer = @$_SERVER["HTTP_REFERER"];
            $host = @$_SERVER["HTTP_HOST"];

            $valids = ["://$host", "://www.$host", "://localhost"];

            $isValid = false;
            foreach ($valids as $valid) {
                if (strpos($referrer, $valid) >= 1) {
                    $isValid = true;
                    break;
                }
            }
            if (!$isValid) return new JsonResponse(["error" => 1001], 403);

            $proxy =  GeneralUtility::makeInstance(CachedTileProxyController::class);
            return $proxy->process($flexSettings,$request, $handler);
        }
        return $handler->handle($request);
    }
}
