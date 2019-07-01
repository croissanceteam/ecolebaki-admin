<?php

session_start();
include_once 'PaymentController.php';

if (isset($_SESSION['usrid'])) {
    $payment = new PaymentController();
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {


    } else {
      $current_page=$_SERVER['REQUEST_URI'];
      if (strpos($current_page,'listyears')) {
        echo json_encode($payment->getListOfYears());
      }elseif(strpos($current_page,'getdailyincome')){
        echo $payment->getDailyIncome($_GET['year'],$_GET['day'],$_SESSION['direction']);
      }elseif(strpos($current_page,'getmonthlyincome')){
        echo $payment->getMonthlyIncome($_GET['start'],$_GET['end'],$_GET['year'],$_SESSION['direction']);
      }elseif(strpos($current_page,'getinsolventslist') && sizeof($_GET) == 4){
        echo $payment->getinsolvents($_GET['y'],$_GET['l'],$_GET['o'],$_GET['t'],$_SESSION['direction']);
      }elseif(strpos($current_page,'payreport') && sizeof($_GET) == 5){
        echo $payment->getPayReport($_GET['y'],$_GET['l'],$_GET['o'],$_GET['t'],$_GET['r'],$_SESSION['direction']);
      }elseif(strpos($current_page,'getdaypayupdates') && sizeof($_GET) == 2){
        echo $payment->getDaylyPayUpdates($_GET['d'],$_GET['y'],$_SESSION['direction']);
        // echo json_encode([
        //   'content'    =>  $_GET,
        //   'withError'  =>  false,
        // ]);
      }elseif(strpos($current_page,'getmonthpayupdates') && sizeof($_GET) == 3){
        echo $payment->getMonthlyPayUpdates($_GET['s'],$_GET['e'],$_GET['y'],$_SESSION['direction']);
        // echo json_encode([
        //   'content'    =>  $_GET,
        //   'withError'  =>  false,
        // ]);
      }

    }
} else {
    echo '<meta http-equiv="refresh" content=0;URL=login>';
}
