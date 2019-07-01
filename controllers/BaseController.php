<?php
session_start();
include_once 'db.php';

class BaseController{

  protected function DB()
  {
    return getDB($_SESSION['dblog']);
  }

  protected function tango($format =  'Y-m-d H:i:s')
  {
    $tz = "Africa/Kinshasa";
    $date = new DateTime($tz);
    $date->setTimezone(new DateTimeZone($tz));
    return $date->format($format);
  }

  
  /**
 * Undocumented function
 *
 * @param \String  $format
 * @return \DateTime
 */
protected function myLocalDate($format = 'd-M-Y')
{
    $timezone  = 1; //(GMT -5:00) EST (U.S. & Canada)
    $time = time() + 3600*($timezone+date("I"));
    $newdate = gmdate($format, $time);
    return $newdate;
}
}
