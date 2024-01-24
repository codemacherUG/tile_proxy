<?php

declare(strict_types=1);

namespace Codemacher\TileProxy\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Http\RequestFactory;

use Codemacher\TileProxy\Constants;
use Codemacher\TileProxy\Cache\RequestCache;

class NominatimProxyController extends ProxyController
{
    public const VALID_APITYPES = ['search','reverse','lookup'];
    protected int $maxDbRecordsToCache;
    protected RequestCache $requestCache;

    private RequestFactory $requestFactory;
    public function __construct(
        RequestFactory $requestFactory
    ) {
        parent::__construct();
        $this->requestFactory = $requestFactory;
        /** @phpstan-ignore-next-line */
        $this->requestCache = GeneralUtility::makeInstance((RequestCache::class));
        /** @var ExtensionConfiguration $extConf */
        $extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class);
        $maxDbRecordsToCacheStr = $extConf->get('tile_proxy', 'maxDbRecordsToCache');
        if (empty($maxDbRecordsToCacheStr)) {
            $this->maxDbRecordsToCache = 10000;
        } else {
            $this->maxDbRecordsToCache = intval($maxDbRecordsToCacheStr);
        }
    }

    protected function createResponse(array $contentInfo): ResponseInterface
    {
        return (new Response())
          ->withHeader('content-type', $contentInfo['content-type'])
          ->withBody($this->streamFactory->createStream($contentInfo['data']));
    }

    public function buildUrlByType(string $provider, string $apitype, array $parms): string
    {
        $url = "";
        switch ($provider) {
            case "osm":
                $url = "https://nominatim.openstreetmap.org/$apitype";
                break;
            default:
                return "";
        }
        $getParameters = http_build_query($parms);

        return $url . "?" . $getParameters;
    }

    protected function parametersComplet(ServerRequestInterface $request): bool
    {
        $params = $request->getQueryParams();
        return isset($params['provider'], $params['apitype']);
    }

    public function process(array $flexSettings, ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {

        if (!$this->parametersComplet($request)) {
            return $this->createErrorResponse(Constants::ERROR_INVALID_PARAMETERS);
        }

        $parms = $request->getQueryParams();

        $cacheTimeStr =  array_key_exists('cacheTime', $flexSettings) ? $flexSettings['cacheTime'] : '1209600';
        $cacheTime = intval($flexSettings['cacheTime'] ??  $cacheTimeStr);

        $provider = $parms['provider'];
        if (!in_array($provider, self::VALID_TYPES)) {
            return $this->createErrorResponse(Constants::ERROR_INVALID_PROVIDER);
        }

        $apitype = $parms['apitype'];

        if (!in_array($apitype, self::VALID_APITYPES)) {
            return $this->createErrorResponse(Constants::ERROR_INVALID_APITYPE);
        }

        $this->requestCache->cleanUp($cacheTime);
        unset($parms['provider']);
        $fullUrl = $this->buildUrlByType($provider, $apitype, $parms);
        if(empty($fullUrl)) {
            return $this->createErrorResponse(Constants::ERROR_INVALID_PROVIDER);
        }
        $cachedData = $this->requestCache->getData($fullUrl);
        if ($this->requestCache->getData($fullUrl)) {
            return $this->createResponse($cachedData);
        }

        $contentInfo = $this->loadContentFormExternal($fullUrl);
        if ($contentInfo) {
            $this->requestCache->setData($fullUrl, $contentInfo, $this->maxDbRecordsToCache);
            return $this->createResponse($contentInfo);
        } else {
            return $this->createErrorResponse(Constants::ERROR_INVALID_ANSWER);
        }
    }

    private function loadContentFormExternal(string $url): array
    {
        $additionalOptions = [
          'headers' => [
            'Cache-Control' => 'no-cache',
            'User-Agent' => Constants::REQUEST_USER_AGENT,
          ],
          'allow_redirects' => true,
        ];

        $response = $this->requestFactory->request(
            $url,
            'GET',
            $additionalOptions
        );

        if ($response->getStatusCode() === 200) {
            return [
              'content-type' => $response->getHeader('Content-Type'),
              'data' => $response->getBody()->getContents()
            ];
        }
        return [];
    }
}
