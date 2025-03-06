<?php

namespace Tests\Units\SysvIPC\SimpleCache;

use Tests\TestCase;
use Takuya\SysV\IPC\Cache\SysvShmCache;
use function Takuya\Helpers\str_rand;

class ShmSimpleCacheTest extends TestCase {
  
  public function test_shm_cache_access_crud() {
    $key = str_rand();
    $data = [str_rand()];
    $cache = new SysvShmCache(str_rand());
    $ret[] = $cache->set($key, $data);
    $ret[] = $cache->has($key);
    $ret[] = $cache->get($key);
    $ret[] = $cache->delete($key);
    $ret[] = $cache->has($key);
    $ret[] = $cache->get($key);
    $cache->destroy();
    $this->assertEquals([true, true, $data, true, false, null], $ret);
  }
  
  public function test_shm_cache_maintenance_methods() {
    $key = str_rand();
    $data = [str_rand()];
    $cache = new SysvShmCache(str_rand());
    $ret[] = $cache->set($key, $data);
    $ret[] = $cache->has($key);
    $dump = $cache->dump();
    $ret[] = $cache->flush();
    $ret[] = $cache->get($key);
    $cache->destroy();
    $this->assertEquals([true, true, true, null], $ret);
    $this->assertEquals([$key => ['body' => $data, 'expires' => null]], $dump);
  }
  
  public function test_shm_cache_access_cleanup_expired() {
    $key = str_rand();
    $data = [str_rand()];
    $cache = new SysvShmCache(str_rand());
    $cache->set($key, $data, -1000);
    $cache->prune();
    $ret = $cache->get($key);
    $cache->destroy();
    $this->assertEquals(null, $ret);
  }
  
  public function test_shm_cache_access_multiple() {
    $src = array_map(fn() => str_rand(), [0, 0, 0, 0, 0]);
    $cache = new SysvShmCache(str_rand());
    $ret[] = $cache->setMultiple($src);
    $ret[] = $cache->getMultiple([1, 2]);
    $ret[] = $cache->getMultiple([10], $def = str_rand());// not exists should return default
    $ret[] = $cache->deleteMultiple([1, 2]);
    $ret[] = $cache->has(1);
    $ret[] = $cache->has(2);
    $ret[] = $cache->has(0);
    $cache->destroy();
    $this->assertEquals([true, [1 => $src[1], 2 => $src[2]], [10 => $def], true, false, false, true], $ret);
  }
}