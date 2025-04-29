<?php

namespace Takuya\Helpers;

if( ! function_exists('child_fork') ) {
  
  function child_fork( callable $do_func_on_child, ?callable $do_func_on_parent=null ) {
    pcntl_async_signals(true);
    $child_pid = pcntl_fork();
    if( $child_pid < 0 ) {
      throw new \RuntimeException('fork failed');
    }
    if( $child_pid == 0 ) {
      call_user_func($do_func_on_child, posix_getpid());
      exit;
    }
    if( $child_pid > 0 ) {
      $do_func_on_parent && $do_func_on_parent(cpid:$child_pid);
    }
    return $child_pid;
  }
}