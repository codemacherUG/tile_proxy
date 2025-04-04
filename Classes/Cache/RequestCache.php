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

    /**
     * @return mixed
     */
    public function getData(string $url)
    {
        $url_hash = md5($url);
        $record = $this->repo->findByHash($url_hash);
        if($record) {
            $seriData = gzuncompress($record['data']);
            if(!empty($seriData)) {
                return unserialize($seriData);
            }
        }
        return null;
    }

    public function setData(string $url, array $contentInfo, int $maxDbRecordsToCache): void
    {

        $url_hash = md5($url);
        if($this->repo->count() < $maxDbRecordsToCache) {
            $compData = gzcompress(serialize($contentInfo));
            if(!empty($compData)) {
                $this->repo->insert($url_hash, $compData);
            }
        }
    }

    public function cleanUp(int $cacheTime): void
    {
        $minCacheTime = time() - $cacheTime;
        $this->repo->deleteRecordsOlderThan($minCacheTime);
    }
}
