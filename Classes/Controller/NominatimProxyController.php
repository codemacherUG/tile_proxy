<?php

declare(strict_types=1);

namespace Codemacher\TileProxy\Controller;

use Codemacher\TileProxy\Cache\RequestCache;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\Response;


use Codemacher\TileProxy\Constants;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class NominatimProxyController extends ProxyController
{

  protected RequestCache $requestCache;
  public function __construct()
  {
    parent::__construct();
    $this->requestCache = GeneralUtility::makeInstance((RequestCache::class));
  }

  protected function createResponse(array $contentInfo): ResponseInterface
  {
    return (new Response())
      ->withHeader('content-type', $contentInfo['content-type'])
      ->withBody($this->streamFactory->createStream($contentInfo['data']));
  }

  public function buildUrlByType(string $provider, array $parms): string
  {
    $url = "";
    switch ($provider) {
      case "osm":
        $url = "https://nominatim.openstreetmap.org/search";
        break;
      default:
        return null;
    }
    $getParameters = http_build_query($parms);

    return $url . "?" . $getParameters;
  }

  public function process(array $flexSettings, ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
  {
    $parms = $request->getQueryParams();

    $cacheTimeStr =  array_key_exists('cacheTime', $flexSettings) ? $flexSettings['cacheTime'] : '1209600';
    $cacheTime = intval($flexSettings['cacheTime'] ??  $cacheTimeStr);

    $provider = $parms['provider'];
    if (!in_array($provider, self::VALID_TYPES)) {
      return $this->createErrorResponse(Constants::ERROR_INVALID_PROVIDER);
    }
    $this->requestCache->cleanUp($cacheTime);
    unset($parms['provider']);
    $fullUrl = $this->buildUrlByType($provider, $parms);

    $cachedData = $this->requestCache->getData($fullUrl);
    if ($this->requestCache->getData($fullUrl)) {
      return $this->createResponse($cachedData);
    }


    $contentInfo = $this->loadContentFormExternal($fullUrl);
    if ($contentInfo) {
      if ($this->requestCache->setData($fullUrl, $contentInfo)) {
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
