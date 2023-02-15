<?php
declare(strict_types = 1);
namespace Codemacher\TileProxy;

class Constants {
    const DOKTYPE = 601;

    const ERROR_INVALID_PARAMETERS = 1000;
    const ERROR_INVALID_HOST = 1001;
    CONST ERROR_INVALID_SUBDOMAIN = 1002;
    const ERROR_INVALID_PROVIDER = 1004;

    const CURL_USER_AGENT = 'CM Tile-Proxy-PHP/1.0';
}