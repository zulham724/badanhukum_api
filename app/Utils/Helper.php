<?php

namespace App\Utils;

class Helper{

  public static function convertSize($size)
  {
    $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
    return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
  }
  
  public static function isJson($string, $return_data = false)
  {
    $data = json_decode($string, true, 512, JSON_OBJECT_AS_ARRAY);
    return (json_last_error() == JSON_ERROR_NONE) ? ($return_data ? $data : true) : false;
  }

  /**
   * Validate date range from left to right
   *
   * @param DateTime $date1 mindate
   * @param DateTime $date2 maxdate
   * 
   * @return boolean
   */
  public static function validateRangeDate(\DateTime $date1, \DateTime $date2)
  {
    if ($date1 > $date2) {
      return false;
    }
    return true;
  }
}