<?php

namespace Codemacher\TileProxy\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;

use Codemacher\TileProxy\CachedTileProxy;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TileProxyMiddleware implements MiddlewareInterface
{

    /** @var string */
    private $slug;

    public function __construct( ) {

        $extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class);
        $this->slug = $extConf->get('tile_proxy', 'slug') ?? "/tile-proxy";
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (str_starts_with($request->getRequestTarget(), $this->slug)) {

            $parms = $request->getQueryParams();
            if (!isset($parms['type'], $parms['s'], $parms['x'], $parms['y'], $parms['z'])) {
                return $handler->handle($request);
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

            $proxy =  GeneralUtility::makeInstance(CachedTileProxy::class);
            return $proxy->process($request, $handler);
        }
        return $handler->handle($request);
    }
}
