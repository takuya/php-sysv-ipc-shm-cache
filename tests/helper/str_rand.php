<?php

namespace Takuya\Helpers;

if( ! function_exists('str_rand') ) {
  function str_rand( $length = 16 ):string {
    return substr(
      str_shuffle(
        str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)))),
      1,
      $length);
  }
}