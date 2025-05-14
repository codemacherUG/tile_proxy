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
use Codemacher\TileProxy\Controller\ProxyController;
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
        return $this->performProxy(TileProxyController::class, $request, $handler, $pageRecord);
    }

    protected function performNominatimProxy(ServerRequestInterface $request, RequestHandlerInterface $handler, array $pageRecord): ResponseInterface
    {
        return $this->performProxy(NominatimProxyController::class, $request, $handler, $pageRecord);
    }

    /**
     * @param class-string<ProxyController> $classname
     */
    protected function performProxy(string $classname, ServerRequestInterface $request, RequestHandlerInterface $handler, array $pageRecord): ResponseInterface
    {
        if (!$this->fulfilsHostRestrictions()) {
            return new JsonResponse(['error' => Constants::ERROR_INVALID_HOST], 403);
        }

        $flexform = array_key_exists('tx_tileproxy_flexform', $pageRecord) ? $pageRecord['tx_tileproxy_flexform'] : "";
        /** @var FlexFormService $ffs */
        $ffs = GeneralUtility::makeInstance(FlexFormService::class);
        $flex = $ffs->convertFlexFormContentToArray($flexform);
        $flexSettings = $flex != null && array_key_exists("settings", $flex) ? $flex["settings"] : [];

        /** @var ProxyController $proxy */
        $proxy = GeneralUtility::makeInstance($classname);
        return $proxy->process($flexSettings, $request, $handler);
    }


    protected function getHostname(string $fullhost): ?string
    {
        if(preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $fullhost, $regs)) {
            return $regs['domain'];
        }
        return null;
    }

    protected function fulfilsHostRestrictions(): bool
    {
        $referrer = @$_SERVER['HTTP_REFERER'];
        $host = @$_SERVER['HTTP_HOST'];
        if(empty($referrer) || empty($host)) return false;
        $referrerPieces = parse_url($referrer);
        $referrerDomain = $this->getHostname($referrerPieces["host"]);
        $hostDomain = $this->getHostname($host);
        $allowedDomainsList = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['tile_proxy']['allowedDomains'] ?? '';
        $allowedDomains = GeneralUtility::trimExplode(',', $allowedDomainsList, true);
        if ($referrerDomain == $hostDomain) {
            return true;
        }

        foreach ($allowedDomains as $allowedDomain) {
            $allowedDomain = $this->getHostname($allowedDomain);
            if ($referrerDomain == $allowedDomain) {
                return true;
            }
        }
        return false;
    }
}
