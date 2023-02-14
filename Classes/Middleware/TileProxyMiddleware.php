<?php

namespace Codemacher\TileProxy\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Service\FlexFormService;

use Codemacher\TileProxy\Controller\CachedTileProxyController;

class TileProxyMiddleware implements MiddlewareInterface
{

    const DOKTYPE = 601;
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {

        $params = $request->getQueryParams();
        if (array_key_exists('provider', $params)) {
            $pageArguments = $request->getAttribute('routing', null);
            $pageRecord = BackendUtility::getRecord("pages", $pageArguments['pageId']);
            if ($pageRecord['doktype'] == TileProxyMiddleware::DOKTYPE) {
                $flexform = array_key_exists('tx_tileproxy_flexform', $pageRecord) ? $pageRecord['tx_tileproxy_flexform'] : "";
                $ffs = GeneralUtility::makeInstance(FlexFormService::class);
                $flex = $ffs->convertFlexFormContentToArray($flexform);
                $flexSettings = $flex != null && array_key_exists("settings", $flex) ? $flex["settings"] : [];
                $params = $request->getQueryParams();
                if (!isset($params['provider'], $params['s'], $params['x'], $params['y'], $params['z'])) {
                    return new JsonResponse(['error' => 1000], 403);
                }
                $referrer = @$_SERVER['HTTP_REFERER'];
                $host = @$_SERVER['HTTP_HOST'];

                $valids = ["://$host", "://www.$host", "://localhost"];

                $isValid = false;
                if (null != $referrer) {
                    foreach ($valids as $valid) {
                        if (strpos($referrer, $valid) >= 1) {
                            $isValid = true;
                            break;
                        }
                    }
                }
                if (!$isValid) return new JsonResponse(['error' => 1001], 403);

                $proxy =  GeneralUtility::makeInstance(CachedTileProxyController::class);
                return $proxy->process($flexSettings, $request, $handler);
            }
        }
        return $handler->handle($request);
    }
}
