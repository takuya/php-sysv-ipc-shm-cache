# php-sysv-ipc-shm-cache

This package is for caching data into SysV SharedMemory.

## Installing

from Packagist

```shell
composer require takuya/php-sysv-ipc-shm-cache
```

from GitHub

```shell
name='php-sysv-ipc-shm-cache'
composer config repositories.$name \
vcs https://github.com/takuya/$name  
composer require takuya/$name:master
composer install
```

## Examples

```php
<?php
$cache = new SysvShmCache('cache_name');
$cache->set($key, $data);
$cache->has($key);
$cache->get($key);
$cache->delete($key);
```

### 'psr/simple-cache' are used.

This package is implementation of `psr/simple-cache` into Shared Memory (`shm_xxx`).

### remove ipc by manually

If unused ipc remains. use SHELL command to remove.

```shell
ipcs -m | grep $USER | grep -oE '0x[a-f0-9]+' | xargs -I@ ipcrm --shmem-key @
ipcs -s | grep $USER | grep -oE '0x[a-f0-9]+' | xargs -I@ ipcrm --semaphore-key @
```






