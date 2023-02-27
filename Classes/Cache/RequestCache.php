<?php

declare(strict_types=1);

namespace Codemacher\TileProxy\Cache;

use TYPO3\CMS\Core\Utility\GeneralUtility;

use Codemacher\TileProxy\Records\RequestCacheRecordRepository;

class RequestCache
{

  private RequestCacheRecordRepository $repo;

  public function __construct()
  {
    $this->repo = GeneralUtility::makeInstance(RequestCacheRecordRepository::class);
  }

  public function getData(string $url) : ?array {
    $url_hash = md5($url);
    $record = $this->repo->findByHash($url_hash);
    if($record) {
      return unserialize(gzuncompress($record['data']));
    }
    return null;
  }

  public function setData(string $url, array $contentInfo) : void {
  
    $url_hash = md5($url);
    $this->repo->insert($url_hash, gzcompress(serialize($contentInfo)));
  }

  public function cleanUp(int $cacheTime) : void {
    $minCacheTime = time() - $cacheTime;
    $this->repo->deleteRecordsOlderThan($minCacheTime);
  }
}