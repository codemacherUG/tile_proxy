<?php

declare(strict_types=1);

namespace Codemacher\TileProxy\Cache;

use TYPO3\CMS\Core\Cache\Backend\NullBackend;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;

class CacheBackend extends NullBackend
{

	protected string $cacheType = '';

	public function setCacheType(string $cacheType): void
	{
		$this->cacheType = $cacheType;
	}

	public function flush(): void
	{
		// just remove all files
		$directory = Environment::getVarPath() . '/tileproxy';
		if (file_exists($directory)) {
			$temporaryDirectory = rtrim($directory, '/') . '.' . StringUtility::getUniqueId('remove');
			// rename to prevent race-conditions with other processes which write to cache
			if (rename($directory, $temporaryDirectory)) {
				GeneralUtility::rmdir($temporaryDirectory, true);
			}
		}
	}
}
