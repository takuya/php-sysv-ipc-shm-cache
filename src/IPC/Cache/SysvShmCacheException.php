<?php

namespace Takuya\SysV\IPC\Cache;

use Psr\SimpleCache\CacheException;

class SysvShmCacheException extends \Exception implements CacheException {

}