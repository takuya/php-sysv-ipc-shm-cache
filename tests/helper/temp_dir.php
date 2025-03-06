<?php

namespace Takuya\Helpers;

if( ! function_exists('temp_dir') ) {
  
  function temp_dir( $strlen = 30, $auto_remove = true ):string {
    $temp_dir = sys_get_temp_dir().DIRECTORY_SEPARATOR.str_rand($strlen);
    mkdir($temp_dir, 0777, true);
    $auto_remove
    && register_shutdown_function(function () use ( $temp_dir ) {
      proc_open(['rm', '-rf', $temp_dir], [], $io);
    });
    
    return $temp_dir;
  }
}