<?php
//session_start();
require 'Logger.php';

function getLogger(){
    if (!empty($_POST['user']) && !empty($_POST['pwd']) && !empty($_POST['instance'])) {

          if (isset($_POST['user']) && isset($_POST['pwd']) && isset($_POST['instance'])) {
             $log=new Logger();

             $response=$log->getLogger($_POST['user'],$_POST['pwd'],$_POST['instance']);

             if($response == 'locked')
             {
               return 6;
             }else if ($response == 'denied'){
               return 2;
             }else if ($response) {

                 $login=$response['login'];
                 $terms=$response['terms'];
                 $users=$response['users'];
                 $pupils=$response['pupils'];
                 $agents=$response['agents'];
                 $years=$response['years'];
                 $_SESSION['usrid']=$login->_USERNAME;
                 $_SESSION['username']=$login->_NAME;
                 $_SESSION['direction']=$login->_CODE_DIRECTION;
                 $_SESSION['priority']=$login->_PRIORITY;
                 $_SESSION['anasco']=$login->_ANASCO;
                 $_SESSION['terms']=$terms;
                 $_SESSION['counter_users']=sizeof($users);
                 $_SESSION['list_users']=$users;
                 $_SESSION['pupils']=$pupils;
                 $_SESSION['counter_pupil']=sizeof($pupils);
                 $_SESSION['counter_agents']=sizeof($agents);
                 $_SESSION['agents']=$agents;
                 $_SESSION['years_list']=$years;

                switch (strtolower($_POST['instance'])) {
                  case 'yolo':
                    $_SESSION['currency'] = '$';
                    break;
                  case 'Kimbanseke':
                    $_SESSION['currency'] = '$';
                    break;
                  case 'N\'djili':
                    $_SESSION['currency'] = 'FC';
                    break;
                  default:
                    $_SESSION['currency'] = '$';
                    break;
                }

                 echo '<meta http-equiv="refresh" content=0;URL=viewdashboard>';
             }else{
               return 0;
             }
         }
     }
     return 1;
 }

$listener= $_SERVER['REQUEST_METHOD'];
$url=$_SERVER['REQUEST_URI'];

 if ($listener=='POST') {

 }

 if ($listener=='GET' && isset($_SESSION['usrid'])) {
        echo '<meta http-equiv="refresh" content=0;URL=viewdashboard>';
     }
