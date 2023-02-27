<?php

declare(strict_types=1);

namespace Codemacher\TileProxy\Controller;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use Codemacher\TileProxy\Constants;
use Codemacher\TileProxy\Cache\RequestCache;

class NominatimProxyController extends ProxyController
{

  const VALID_APITYPES = ['search','reverse','lookup'];
  protected int $maxDbRecordsToCache;
  protected RequestCache $requestCache;
  public function __construct()
  {
    parent::__construct();
    $this->requestCache = GeneralUtility::makeInstance((RequestCache::class));
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
        return null;
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

    $cachedData = $this->requestCache->getData($fullUrl);
    if ($this->requestCache->getData($fullUrl)) {
      return $this->createResponse($cachedData);
    }

    $contentInfo = $this->loadContentFormExternal($fullUrl);
    if ($contentInfo) {
      if ($this->requestCache->setData($fullUrl, $contentInfo,$this->maxDbRecordsToCache)) {
      }
      return $this->createResponse($contentInfo);
    } else {
      return $this->createErrorResponse(Constants::ERROR_INVALID_ANSWER);
    }
  }

  private function loadContentFormExternal($url): array
  {

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_USERAGENT, Constants::CURL_USER_AGENT);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $data = curl_exec($ch);
    $header = curl_getinfo($ch);

    if ($header['http_code'] == "200") {
      curl_close($ch);
      return [
        'content-type' => $header['content_type'],
        'data' => $data
      ];
    } else {
      curl_close($ch);
      return null;
    }
  }
}
