<?php

namespace Codemacher\TileProxy\Records;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;



class RequestCacheRecordRepository extends BaseRecordRepository implements SingletonInterface
{

  /**
   * @var string
   */
  protected string $table = 'tx_tileproxy_domain_model_requestcache';

  protected function createSelect(): QueryBuilder
  {
    $queryBuilder = $this->getQueryBuilder(false);
    $queryBuilder
      ->select(
        '*',
      )
      ->from($this->table, "requestcache")
    ;
    return $queryBuilder;
  }

  public function findByHash(string $hash)
  {
    $queryBuilder = $this->createSelect();
    $queryBuilder
      ->andWhere($queryBuilder->expr()->eq('requestcache.url_hash', $queryBuilder->createNamedParameter($hash, Connection::PARAM_STR)));

    return $queryBuilder
      ->execute()
      ->fetchAssociative();
  }
 
  public function count() : int {
    $queryBuilder = $this->getQueryBuilder();
    $count = $queryBuilder
        ->count('url_hash')
        ->from($this->table)
        ->executeQuery()
        ->fetchOne();    
    return $count;
  }

  public function insert(string $hash,string $data): void
  {

    $queryBuilder = $this->getQueryBuilder();
    $queryBuilder
      ->insert($this->table)
      ->values([
        'url_hash' => $hash,
        'data' => $data,
        'created' => time()
      ])
      ->execute();
  }

  public function deleteRecordsOlderThan(int $time) : void {
    $queryBuilder = $this->getQueryBuilder();
    $queryBuilder->delete($this->table)
    ->andWhere($queryBuilder->expr()->lt('created', $queryBuilder->createNamedParameter($time, Connection::PARAM_INT)))
    ->executeStatement();
  }

  public function truncate() : void {
    $queryBuilder = $this->getQueryBuilder();
    $queryBuilder->delete($this->table)
    ->executeStatement();
  }

}
