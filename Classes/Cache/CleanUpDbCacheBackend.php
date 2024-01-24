<?php

declare(strict_types=1);

namespace Codemacher\TileProxy\Cache;

use TYPO3\CMS\Core\Cache\Backend\NullBackend;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use Codemacher\TileProxy\Records\RequestCacheRecordRepository;

class CleanUpDbCacheBackend extends NullBackend
{
    protected string $cacheType = '';

    public function setCacheType(string $cacheType): void
    {
        $this->cacheType = $cacheType;
    }

    public function flush(): void
    {
        /** @var RequestCacheRecordRepository $repo  */
        $repo = GeneralUtility::makeInstance(RequestCacheRecordRepository::class);
        $repo->truncate();
    }
}
