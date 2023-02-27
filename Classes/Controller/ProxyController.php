<?php

declare(strict_types=1);

namespace Codemacher\TileProxy\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Http\StreamFactory;
use TYPO3\CMS\Core\Http\JsonResponse;

abstract class ProxyController
{

  protected StreamFactory $streamFactory;
  const VALID_TYPES = ["osm"];

  public function __construct()
  {
    $this->streamFactory = GeneralUtility::makeInstance(StreamFactory::class);
  }


  protected function createErrorResponse(int $code): ResponseInterface
  {
    return new JsonResponse(['error' => $code], 403);
  }

  
  public abstract function process(array $flexSettings, ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface;

}
