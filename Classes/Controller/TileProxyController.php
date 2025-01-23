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

    private RequestFactory $requestFactory;

    public function __construct(
        RequestFactory $requestFactory
    ) {
        parent::__construct();
        $this->requestFactory = $requestFactory;
        $this->cacheDir = Environment::getVarPath() . Constants::CACHE_DIR;

        /** @var ExtensionConfiguration $extConf */
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
        if (count($bbox) != 4) {
            return true;
        }
        $minTileX = LatLngToTile::lngToTileX(intval($bbox[0]), $z);
        $maxTileX = LatLngToTile::lngToTileX(intval($bbox[2]), $z);
        $minTileY = LatLngToTile::latToTileY(intval($bbox[3]), $z);
        $maxTileY = LatLngToTile::latToTileY(intval($bbox[1]), $z);
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
        return $this->createResponse($imgData ? $imgData : "", $cacheHeaderTime);
    }

    protected function createResponse(string $content, int $cacheHeaderTime): ResponseInterface
    {
        
        return (new Response())
          ->withHeader('Content-Type', 'image/png')
          ->withHeader('Cache-Control', "public, max-age=$cacheHeaderTime, s-maxage=$cacheHeaderTime")
          ->withHeader('Access-Control-Allow-Origin', "*")
          ->withHeader('X-Robots-Tag', 'noindex')
          ->withBody($this->streamFactory->createStream($content));
    }

    public function buildUrlByType(string $provider, string $s, int $z, int $x, int $y): string
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

        $maxCachingZoomStr = array_key_exists('maxCachingZoom', $flexSettings) ? $flexSettings['maxCachingZoom'] : 18;
        $maxCachingZoom = intval($flexSettings['maxCachingZoom'] ?? $maxCachingZoomStr);

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
        if ($z > $maxCachingZoom) {
            if ($passthrough > 0) {
                return $this->passThrough($fullUrl, $cacheTime);
            }
            return $this->createResponseByFilename($this->emptyTilePath, $cacheTime);
        }

        $cacheTileFile = "";
        if ($z > $cachingUntilZoom && !$this->isInBoundingBox($bbox, $z, $x, $y)) {
            if ($passthrough > 0) {
                return $this->passThrough($fullUrl, $cacheTime);
            }
            return $this->createResponseByFilename($this->emptyTilePath, $cacheTime);
        }

        $cacheTileFile = $this->cacheDir . "/$provider/$z/$x/$y.png";

        $isChachValid = true;
        $folderSize = FolderUtil::getFolderInfo($this->cacheDir)["size"];
        if (!file_exists($cacheTileFile)) {
            if ($folderSize > $this->maxTileFileCacheSize) {
                return $this->passThrough($fullUrl, $cacheTime);
            }
            $isChachValid = $this->loadTileAndCacheIt($fullUrl, $cacheTileFile);
        } else {
            $fileAge = time() - filemtime($cacheTileFile);
            if ($fileAge > $cacheTime) {

                if ($folderSize > $this->maxTileFileCacheSize) {
                    return $this->passThrough($fullUrl, $cacheTime);
                }
                $isChachValid = $this->loadTileAndCacheIt($fullUrl, $cacheTileFile);
            }
        }
        if (!$isChachValid) {
            $cacheTileFile = $this->errorTileUrl;
        }


        return $this->createResponseByFilename($cacheTileFile, $cacheTime);
    }

    private function passThrough(string $fullUrl, int $cacheTileFile): ResponseInterface
    {
        return $this->createResponse($this->loadTile($fullUrl), $cacheTileFile);
    }

    private function loadTile(string $tileURL): ?string
    {
        $additionalOptions = [
          'headers' => [
            'User-Agent' => Constants::REQUEST_USER_AGENT,
          ],
          'allow_redirects' => true
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

    private function loadTileAndCacheIt(string $tileURL, string $cacheTileFile): bool
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
