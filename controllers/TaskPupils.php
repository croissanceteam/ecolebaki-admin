<?php
session_start();
include_once 'PupilController.php';

if (isset($_SESSION['usrid'])) {
    $pupil = new PupilController();

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {


    } else {
      $current_page=$_SERVER['REQUEST_URI'];
      

    }
} else {
    echo '<meta http-equiv="refresh" content=0;URL=login>';
}
