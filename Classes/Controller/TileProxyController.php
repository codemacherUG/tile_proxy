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
use TYPO3\CMS\Core\Http\StreamFactory;
use TYPO3\CMS\Core\Http\JsonResponse;

use Codemacher\TileProxy\LatLngToTile;
use Codemacher\TileProxy\Constants;

class TileProxyController extends ProxyController
{
  private string $errorTileUrl;
  private string $emptyTilePath;
  private string $cacheDir;

  public function __construct()
  {
    parent::__construct();
    $this->cacheDir = Environment::getVarPath() . '/tileproxy/cache';

    $extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class);

    $this->errorTileUrl = $extConf->get('tile_proxy', 'errorTilePath');
    if (empty($this->errorTileUrl)) {
      $this->errorTileUrl  = ExtensionManagementUtility::extPath('tile_proxy') . "Resources/Public/Images/tile-error.png";
    }
    $this->emptyTilePath = $extConf->get('tile_proxy', 'emptyTilePath');
    if (empty($this->emptyTilePath)) {
      $this->emptyTilePath  = ExtensionManagementUtility::extPath('tile_proxy') . "Resources/Public/Images/tile-empty.png";
    }
  }

  protected function isInBoundingBox(array $bbox, int $z, int $x, int $y): bool
  {
    if (count($bbox) != 4) return true;
    $minTileX     = LatLngToTile::lngToTileX($bbox[0], $z);
    $maxTileX     = LatLngToTile::lngToTileX($bbox[2], $z);
    $minTileY     = LatLngToTile::latToTileY($bbox[3], $z);
    $maxTileY     = LatLngToTile::latToTileY($bbox[1], $z);
    return !(!LatLngToTile::inRange($x, $minTileX, $maxTileX) || !LatLngToTile::inRange($y, $minTileY, $maxTileY));
  }


  protected function parametersComplet(ServerRequestInterface $request): bool
  {
    $params = $request->getQueryParams();
    return isset($params['provider'], $params['s'], $params['x'], $params['y'], $params['z']);
  }

  protected function createResponse(string $filename, int $cacheHeaderTime): ResponseInterface
  {
    $imgData = file_get_contents($filename);
    return (new Response())
      ->withHeader('content-type', 'image/png')
      ->withHeader('cache-control', "public, max-age=$cacheHeaderTime, s-maxage=$cacheHeaderTime")
      ->withBody($this->streamFactory->createStream($imgData));
  }

  public function buildUrlByType($provider, $s, $z, $x, $y): string
  {
    switch ($provider) {
      case "osm":
        return "https://$s.tile.openstreetmap.org/$z/$x/$y.png";
    }
    return null;
  }

  public function process(array $flexSettings, ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
  {

    if (!$this->parametersComplet($request)) {
      return new JsonResponse(['error' => Constants::ERROR_INVALID_PARAMETERS], 403);
    }

    $parms = $request->getQueryParams();
    $bboxStr = array_key_exists('bbox', $flexSettings) ? $flexSettings['bbox'] : '11.86,51.41,12.07,51.55';
    $bbox = explode(',', $bboxStr);
    $cacheTimeStr =  array_key_exists('cacheTime', $flexSettings) ? $flexSettings['cacheTime'] : '31536000';
    $cacheTime = intval($flexSettings['cacheTime'] ??  $cacheTimeStr);


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

    $cacheTileFile = "";
    if (!$this->isInBoundingBox($bbox, $z, $x, $y)) {
      return $this->createResponse($this->emptyTilePath, $cacheTime);
    } else {
      $fullUrl = $this->buildUrlByType($provider, $s, $z, $x, $y);
      if (empty($fullUrl)) { // can't be happen
        return $this->createErrorResponse(Constants::ERROR_INVALID_PROVIDER);
      }
      $cacheTileFile = $this->cacheDir . "/$provider/$z/$x/$y.png";

      $isChachValid = true;
      if (!file_exists($cacheTileFile)) {
        $isChachValid = $this->loadTileAndCacheIt($fullUrl, $cacheTileFile);
      } else {
        $fileAge = time() - filemtime($cacheTileFile);
        if ($fileAge > $cacheTime) {
          $isChachValid = $this->loadTileAndCacheIt($fullUrl, $cacheTileFile);
        }
      }
      if (!$isChachValid) {
        $cacheTileFile = $this->errorTileUrl;
      }
    }

    return $this->createResponse($cacheTileFile, $cacheTime);
  }

  private function loadTileAndCacheIt($tileURL, $cacheTileFile)
  {

    if (!file_exists(dirname($cacheTileFile))) {
      @mkdir(dirname($cacheTileFile), 0777, true);
    }

    $ch = curl_init($tileURL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_USERAGENT, Constants::CURL_USER_AGENT);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $data = curl_exec($ch);
    $header = curl_getinfo($ch);
    if ($header['http_code'] == "200") {
      file_put_contents($cacheTileFile, $data);
      curl_close($ch);
      return true;
    } else {
      curl_close($ch);
      return false;
    }
  }
}
