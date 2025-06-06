<?php

namespace Codemacher\TileProxy\Records;

use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Context\Context;

abstract class BaseRecordRepository
{
    /**
     * @var string
     */
    protected string $table = '';

    /**
     * Returns QueryBuilder
     *
     * @return QueryBuilder
     */
    protected function getQueryBuilder(bool $removeRestrictions = true): QueryBuilder
    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        /* @var QueryBuilder $queryBuilder */
        $queryBuilder = $connectionPool->getQueryBuilderForTable($this->table);
        if ($removeRestrictions) {
            $queryBuilder->getRestrictions()
              ->removeAll();
        }
        return $queryBuilder;
    }

    /**
     * Updates a row
     *
     * @param array $uids
     * @param array $data
     */
    public function update(array $uids, array $data): void
    {
        if (count($uids) == 0) {
            return;
        }

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
          ->update($this->table)
          ->where(
              $queryBuilder->expr()->in('uid', $uids)
          );

        foreach ($data as $column => $value) {
            $queryBuilder->set($column, $value);
        }

        $queryBuilder->executeStatement();
    }

    public static function getExceptionTime(): int
    {
        /** @var Context $context */
        $context = GeneralUtility::makeInstance(Context::class);
        return (int) $context->getPropertyFromAspect('date', 'timestamp');
    }
}
