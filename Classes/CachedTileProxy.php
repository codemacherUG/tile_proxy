<?php

namespace Codemacher\TileProxy;

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

class CachedTileProxy
{
  private StreamFactory $streamFactory;
  private string $errorTileUrl;
  private string $emptyTilePath;
  private string $cacheDir;
  private array $bbox;
  private int $cacheHeaderTime;
  private int $cacheFileTime;

  const VALID_TYPES = ["osm"];

  public function __construct()
  {
    $this->streamFactory = GeneralUtility::makeInstance(StreamFactory::class);

    $this->cacheDir = Environment::getVarPath() . '/tileproxy/cache';

    $extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class);
    $this->bbox = explode(',', $extConf->get('tile_proxy', 'bbox'));

    $this->errorTileUrl = $extConf->get('tile_proxy', 'errorTilePath');
    if (empty($this->errorTileUrl)) {
      $this->errorTileUrl  = ExtensionManagementUtility::extPath('tile_proxy') . "Resources/Public/Images/tile-error.png";
    }
    $this->emptyTilePath = $extConf->get('tile_proxy', 'emptyTilePath');
    if (empty($this->emptyTilePath)) {
      $this->emptyTilePath  = ExtensionManagementUtility::extPath('tile_proxy') . "Resources/Public/Images/tile-empty.png";
    }

    $this->cacheHeaderTime = intval($extConf->get('tile_proxy', 'cacheHeaderTime') ?? "31536000");
    $this->cacheFileTime = intval($extConf->get('tile_proxy', 'cacheFileTime') ?? "5256000");
  }

  protected function isInBoundingBox(int $z, int $x, int $y): bool
  {
    if (count($this->bbox) != 4) return true;
    $minTileX     = LatLngToTile::lngToTileX($this->bbox[0], $z);
    $maxTileX     = LatLngToTile::lngToTileX($this->bbox[2], $z);
    $minTileY     = LatLngToTile::latToTileY($this->bbox[3], $z);
    $maxTileY     = LatLngToTile::latToTileY($this->bbox[1], $z);
    return !(!LatLngToTile::inRange($x, $minTileX, $maxTileX) || !LatLngToTile::inRange($y, $minTileY, $maxTileY));
  }

  protected function createResponse($filename): ResponseInterface
  {
    $imgData = file_get_contents($filename);
    return (new Response())
      ->withHeader('content-type', 'image/png')
      ->withHeader('cache-control', "public, max-age=$this->cacheHeaderTime, s-maxage=$this->cacheHeaderTime")
      ->withBody($this->streamFactory->createStream($imgData));
  }

  public function buildUrlByType($type, $s, $z, $x, $y): string
  {
    switch ($type) {
      case "osm":
        return "https://$s.tile.openstreetmap.org/$z/$x/$y.png";
    }
    return null;
  }

  public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
  {
    $parms = $request->getQueryParams();

    $s = $parms['s'];
    $x = intval($parms['x']);
    $y = intval($parms['y']);
    $z = intval($parms['z']);
    $tileproxytype = $parms['tileproxytype'];


    if ($s != 'a' && $s != 'b' && $s != 'c') {
      return new JsonResponse(["error" => 1002], 403);
    }

    if (!in_array($tileproxytype,CachedTileProxy::VALID_TYPES)) {
      return new JsonResponse(["error" => 1003], 403);
    }

    $cacheTileFile = "";
    if (!$this->isInBoundingBox($z, $x, $y)) {
      return $this->createResponse($this->emptyTilePath);
    } else {
      $fullUrl = $this->buildUrlByType($tileproxytype, $s, $z, $x, $y);
      if(empty($fullUrl)) {
        return new JsonResponse(["error" => 1004], 403);
      }
      $cacheTileFile = $this->cacheDir . "/$tileproxytype/$z/$x/$y.png";

      $isChachValid = true;
      if (!file_exists($cacheTileFile)) {
        $isChachValid = $this->loadTileAndCacheIt($fullUrl, $cacheTileFile);
      } else {
        $fileAge = time() - filemtime($cacheTileFile);
        if ($fileAge > $this->cacheFileTime) {
          $isChachValid = $this->loadTileAndCacheIt($fullUrl, $cacheTileFile);
        }
      }
      if (!$isChachValid) {
        $cacheTileFile = $this->errorTileUrl;
      }
    }

    return $this->createResponse($cacheTileFile);
  }

  private function loadTileAndCacheIt($tileURL, $cacheTileFile)
  {

    if (!file_exists(dirname($cacheTileFile))) {
      mkdir(dirname($cacheTileFile), 0777, true);
    }

    $ch = curl_init($tileURL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $user_agent = 'CM Tile-Proxy-PHP/0.1';
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
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
