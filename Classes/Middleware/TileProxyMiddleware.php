<?php

declare(strict_types=1);

namespace Codemacher\TileProxy\Middleware;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Service\FlexFormService;

use Codemacher\TileProxy\Constants;
use Codemacher\TileProxy\Controller\CachedTileProxyController;

class TileProxyMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $params = $request->getQueryParams();
        if (array_key_exists('provider', $params)) {
            $pageArguments = $request->getAttribute('routing', null);
            $pageRecord = BackendUtility::getRecord("pages", $pageArguments['pageId']);
            if ($pageRecord['doktype'] == Constants::DOKTYPE) {

                $flexform = array_key_exists('tx_tileproxy_flexform', $pageRecord) ? $pageRecord['tx_tileproxy_flexform'] : "";
                $ffs = GeneralUtility::makeInstance(FlexFormService::class);
                $flex = $ffs->convertFlexFormContentToArray($flexform);
                $flexSettings = $flex != null && array_key_exists("settings", $flex) ? $flex["settings"] : [];

                if (!$this->parametersComplet($request)) {
                    return new JsonResponse(['error' => Constants::ERROR_INVALID_PARAMETERS], 403);
                }

                if (!$this->fulfilsHostRestrictions()) return new JsonResponse(['error' => Constants::ERROR_INVALID_HOST], 403);

                $proxy =  GeneralUtility::makeInstance(CachedTileProxyController::class);
                return $proxy->process($flexSettings, $request, $handler);
            }
        }
        return $handler->handle($request);
    }

    protected function parametersComplet(ServerRequestInterface $request): bool
    {
        $params = $request->getQueryParams();
        return isset($params['provider'], $params['s'], $params['x'], $params['y'], $params['z']);
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
