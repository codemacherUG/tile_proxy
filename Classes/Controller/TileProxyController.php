<?php

declare(strict_types=1);

namespace Codemacher\TileProxy\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Http\RequestFactory;

use Codemacher\TileProxy\LatLngToTile;
use Codemacher\TileProxy\Constants;
use Codemacher\TileProxy\Utils\FolderUtil;

class TileProxyController extends ProxyController
{
  private string $errorTileUrl;
  private string $emptyTilePath;
  private string $cacheDir;
  private int $maxTileFileCacheSize;

  public function __construct(
    private readonly RequestFactory $requestFactory
  ) {
    parent::__construct();
    $this->cacheDir = Environment::getVarPath() . '/tileproxy/cache';

    $extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class);

    $this->errorTileUrl = $extConf->get('tile_proxy', 'errorTilePath');
    if (empty($this->errorTileUrl)) {
      $this->errorTileUrl = ExtensionManagementUtility::extPath('tile_proxy') . "Resources/Public/Images/tile-error.png";
    }
    $this->emptyTilePath = $extConf->get('tile_proxy', 'emptyTilePath');
    if (empty($this->emptyTilePath)) {
      $this->emptyTilePath = ExtensionManagementUtility::extPath('tile_proxy') . "Resources/Public/Images/tile-empty.png";
    }

    $maxTileFileCacheSizeMbStr = $extConf->get('tile_proxy', 'maxTileFileCacheSizeMb');
    if (empty($maxTileFileCacheSizeMbStr)) {
      $this->maxTileFileCacheSize = 120 * 1024 * 1024;
    } else {
      $this->maxTileFileCacheSize = intval($maxTileFileCacheSizeMbStr) * 1024 * 1024;
    }
  }

  protected function isInBoundingBox(array $bbox, int $z, int $x, int $y): bool
  {
    if (count($bbox) != 4)
      return true;
    $minTileX = LatLngToTile::lngToTileX($bbox[0], $z);
    $maxTileX = LatLngToTile::lngToTileX($bbox[2], $z);
    $minTileY = LatLngToTile::latToTileY($bbox[3], $z);
    $maxTileY = LatLngToTile::latToTileY($bbox[1], $z);
    return !(!LatLngToTile::inRange($x, $minTileX, $maxTileX) || !LatLngToTile::inRange($y, $minTileY, $maxTileY));
  }


  protected function parametersComplet(ServerRequestInterface $request): bool
  {
    $params = $request->getQueryParams();
    return isset($params['provider'], $params['s'], $params['x'], $params['y'], $params['z']);
  }

  protected function createResponseByFilename(string $filename, int $cacheHeaderTime): ResponseInterface
  {
    $imgData = file_get_contents($filename);
    return $this->createResponse($imgData, $cacheHeaderTime);
  }

  protected function createResponse($content, int $cacheHeaderTime): ResponseInterface
  {
    return (new Response())
      ->withHeader('content-type', 'image/png')
      ->withHeader('cache-control', "public, max-age=$cacheHeaderTime, s-maxage=$cacheHeaderTime")
      ->withBody($this->streamFactory->createStream($content));
  }

  public function buildUrlByType($provider, $s, $z, $x, $y): string
  {
    switch ($provider) {
      case "osm":
        return "https://$s.tile.openstreetmap.org/$z/$x/$y.png";
    }
    return "";
  }


  public function process(array $flexSettings, ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
  {


    if (!$this->parametersComplet($request)) {
      return $this->createErrorResponse(Constants::ERROR_INVALID_PARAMETERS);
    }

    $parms = $request->getQueryParams();
    $bboxStr = array_key_exists('bbox', $flexSettings) ? $flexSettings['bbox'] : '11.86,51.41,12.07,51.55';
    $bbox = explode(',', $bboxStr);
    $cacheTimeStr = array_key_exists('cacheTime', $flexSettings) ? $flexSettings['cacheTime'] : '31536000';
    $cacheTime = intval($flexSettings['cacheTime'] ?? $cacheTimeStr);

    $passthroughStr = array_key_exists('passthrough', $flexSettings) ? $flexSettings['passthrough'] : 0;
    $passthrough = intval($flexSettings['passthrough'] ?? $passthroughStr);

    $cachingUntilZoomStr = array_key_exists('cachingUntilZoom', $flexSettings) ? $flexSettings['cachingUntilZoom'] : 6;
    $cachingUntilZoom = intval($flexSettings['cachingUntilZoom'] ?? $cachingUntilZoomStr);

    $s = $parms['s'];
    $x = intval($parms['x']);
    $y = intval($parms['y']);
    $z = intval($parms['z']);
    $provider = $parms['provider'];

    if ($s != 'a' && $s != 'b' && $s != 'c') {
      return $this->createErrorResponse(Constants::ERROR_INVALID_SUBDOMAIN);
    }

    if (!in_array($provider, self::VALID_TYPES)) {
      return $this->createErrorResponse(Constants::ERROR_INVALID_PROVIDER);
    }

    $fullUrl = $this->buildUrlByType($provider, $s, $z, $x, $y);
    if (empty($fullUrl)) { // can't be happen
      return $this->createErrorResponse(Constants::ERROR_INVALID_PROVIDER);
    }

    $cacheTileFile = "";
    if ($z > $cachingUntilZoom && !$this->isInBoundingBox($bbox, $z, $x, $y)) {
      if ($passthrough > 0) {
        return $this->passThrough($fullUrl, $cacheTime);
      }
      return $this->createResponseByFilename($this->emptyTilePath, $cacheTime);
    } else {

      $cacheTileFile = $this->cacheDir . "/$provider/$z/$x/$y.png";

      $isChachValid = true;
      if (!file_exists($cacheTileFile)) {
        if (FolderUtil::calcSize($this->cacheDir) > $this->maxTileFileCacheSize) {
          return $this->passThrough($fullUrl, $cacheTime);
        }
        $isChachValid = $this->loadTileAndCacheIt($fullUrl, $cacheTileFile);
      } else {
        $fileAge = time() - filemtime($cacheTileFile);
        if ($fileAge > $cacheTime) {
          if (FolderUtil::calcSize($this->cacheDir) > $this->maxTileFileCacheSize) {
            return $this->passThrough($fullUrl, $cacheTime);
          }
          $isChachValid = $this->loadTileAndCacheIt($fullUrl, $cacheTileFile);
        }
      }
      if (!$isChachValid) {
        $cacheTileFile = $this->errorTileUrl;
      }
    }

    return $this->createResponseByFilename($cacheTileFile, $cacheTime);
  }

  private function passThrough($fullUrl, $cacheTileFile): ResponseInterface
  {
    return $this->createResponse($this->loadTile($fullUrl), $cacheTileFile);
  }

  private function loadTile($tileURL): ?string
  {
    $additionalOptions = [
      'headers' => [
        'Cache-Control' => 'no-cache',
        'User-Agent' => Constants::REQUEST_USER_AGENT,
      ],
      'allow_redirects' => true,
      'stream' => true
    ];

    $response = $this->requestFactory->request(
      $tileURL,
      'GET',
      $additionalOptions
    );

    if ($response->getStatusCode() === 200) {
      $contentTypes = $response->getHeader('Content-Type');
      if (count($contentTypes) > 0 && $contentTypes[0] === 'image/png') {
        return $response->getBody()->getContents();
      }
    }
    return null;

  }

  private function loadTileAndCacheIt($tileURL, $cacheTileFile)
  {
    if (!file_exists(dirname($cacheTileFile))) {
      @mkdir(dirname($cacheTileFile), 0777, true);
    }
    $data = $this->loadTile($tileURL);
    if ($data) {
      file_put_contents($cacheTileFile, $data);
      return true;
    }

    return false;
  }
}
