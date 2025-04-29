<?php

namespace Tests\Units\SysvIPC\SimpleCache\MultiProcess;

use Tests\TestCase;
use Takuya\SysV\IPC\Cache\SysvShmCache;
use function Takuya\Helpers\str_rand;
use function Takuya\Helpers\child_fork;

class MultiProcessSharedCacheTest extends TestCase {
  
  
  public function test_interlock_cache_by_2_process() {
    foreach (array_fill(0, 100, null) as $_cnt => $n) {
      $cpids = [];
      $cache_name = str_rand(20);
      $key = str_rand();
      $cpids[] = child_fork(function ( $pid ) use ( $cache_name, $key ) {
        $cache = new SysvShmCache($cache_name);
        foreach (range(1, 10) as $idx) {
          $cache->runWithLock(function ( $cache ) use ( $key, $idx ) {
            $cache->set($key, ( $cache->get($key) ?? 0 ) + $idx);
          });
          usleep(rand(100, 200));
        }
      }, fn( $cpid ) => $cpid);
      $cpids[] = child_fork(function ( $pid ) use ( $cache_name, $key ) {
        $cache = new SysvShmCache($cache_name);
        foreach (range(1, 10) as $idx) {
          $cache->runWithLock(function ( $cache ) use ( $key, $idx ) {
            $cache->set($key, ( $cache->get($key) ?? 0 ) + $idx*1000);
          });
          usleep(rand(100, 200));
        }
      }, fn( $cpid ) => $cpid);
      foreach ($cpids as $pid) {
        pcntl_waitpid($pid, $st);
      }
      $cache = new SysvShmCache($cache_name);
      $result = $cache->get($key);
      // dump([$_cnt=>$result]);
      $cache->destroy();
      $this->assertEquals(55055, $result);
    }
  }
}