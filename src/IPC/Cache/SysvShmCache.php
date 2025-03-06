<?php

namespace Takuya\SysV\IPC\Cache;

use Takuya\SysV\IPCShmKeyStore;
use Psr\SimpleCache\CacheInterface;

class SysvShmCache implements CacheInterface {
  
  /**
   * @var \Takuya\SysV\IPCShmKeyStore
   */
  protected IPCShmKeyStore $mem;
  
  public function __construct( public string $name, protected int $size = 1024*1024, protected int $perm = 0770 ) {
    $this->mem = new IPCShmKeyStore($this->name, $this->size, $this->perm);
  }
  
  public function destroy():bool {
    return $this->mem->destroy();
  }
  /////////////////////////////////////////////
  /// -- Local shortcut function.
  /////////////////////////////////////////////
  protected function isExpired( $item ):bool {
    // null is permanent cache.
    return ( $item['expires'] ?? time() + 1 ) < time();
  }
  
  protected function cacheEntry( mixed $value, \DateInterval|int|null $ttl = null ):array {
    return ['body' => $value, 'expires' => $this->ttl_to_time($ttl)];
  }
  
  protected function ttl_to_time( \DateInterval|int|null $ttl = null ):?int {
    return ! empty($ttl) ? time() + ( is_int($ttl) ? $ttl : ( new \DateTimeImmutable() )->add($ttl)->getTimestamp() ) :
      null;
  }
  
  /////////////////////////////////////////////
  /// -- delegator functions.
  /////////////////////////////////////////////
  protected function update( string $key, array $cache_entry ):bool {
    return $this->mem->set($key, $cache_entry);
  }
  
  protected function exists( string $key ):bool {
    return $this->mem->has($key);
  }
  
  protected function retrieve( $key ) {
    return $this->mem->run(function () use ( $key ) {
      $this->prune();
      
      return $this->mem->get($key);
    });
  }
  
  protected function clear():bool {
    return $this->mem->clear();
  }
  
  protected function remove( $key ):bool {
    return $this->mem->del($key);
  }
  
  /////////////////////////////////////////////
  /// -- Maintenance functions.
  /////////////////////////////////////////////
  /**
   * remove expired cache.
   * @return bool
   */
  public function prune():bool {
    return $this->mem->run(function ():bool {
      $items = $this->mem->all();
      $items = array_filter($items, fn( $e ) => ! $this->isExpired($e));
      
      return $this->mem->store($items);
    });
  }
  
  public function dump():array {
    return $this->mem->all();
  }
  /////////////////////////////////////////////
  /// -- Override Methods
  /////////////////////////////////////////////
  #[\Override] public function has( string $key ):bool {
    return $this->exists($key);
  }
  
  #[\Override] public function get( string $key, mixed $default = null ):mixed {
    return $this->retrieve($key)['body'] ?? $default;
  }
  
  #[\Override] public function set( string $key, mixed $value, \DateInterval|int|null $ttl = null ):bool {
    return $this->update($key, $this->cacheEntry($value, $ttl));
  }
  
  #[\Override] public function delete( string $key ):bool {
    return $this->remove($key);
  }
  
  #[\Override] public function flush():bool {
    return $this->clear();
  }
  
  #[\Override] public function getMultiple( iterable $keys, mixed $default = null ):iterable {
    return $this->mem->run(function () use ( $keys, $default ) {
      $this->prune();
      $items = $this->mem->all();
      $result = [];
      foreach ($keys as $key) {
        $result[$key] = $items[$key]['body'] ?? $default;
      }
      
      return $result;
    });
  }
  
  #[\Override] public function setMultiple( iterable $values, \DateInterval|int|null $ttl = null ):bool {
    return $this->mem->run(function () use ( $values, $ttl ) {
      $this->prune();
      $items = $this->mem->all();
      foreach ($values as $value) {
        $items[] = $this->cacheEntry($value, $ttl);
      }
      
      return $this->mem->store($items);
    });
  }
  
  #[\Override] public function deleteMultiple( iterable $keys ):bool {
    return $this->mem->run(function () use ( $keys ) {
      $this->prune();
      $items = $this->mem->all();
      foreach ($keys as $key) {
        unset($items[$key]);
      }
      
      return $this->mem->store($items);
    });
  }
}