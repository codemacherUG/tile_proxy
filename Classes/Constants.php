<?php

declare(strict_types=1);

namespace Codemacher\TileProxy;

class Constants
{
    public const DOKTYPE_TILE_PROXY = 601;
    public const DOKTYPE_NOMINATIM_PROXY = 602;

    public const ERROR_INVALID_PARAMETERS = 1000;
    public const ERROR_INVALID_HOST = 1001;
    public const ERROR_INVALID_SUBDOMAIN = 1002;
    public const ERROR_INVALID_PROVIDER = 1004;
    public const ERROR_INVALID_ANSWER = 1005;
    public const ERROR_INVALID_APITYPE = 1006;

    public const REQUEST_USER_AGENT = 'Tile-Proxy-PHP/1.2';

    public const CACHE_DIR = '/tileproxy/cache';


}
