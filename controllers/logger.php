
<?php
<<<<<<< HEAD
    include_once 'db.php';
    $uid='';
    $pwrd='';
    $dblogger='';
=======
    session_start();
    include_once 'db.php';
>>>>>>> de858115e51748a912198fe39284ab8d201649f1
?>

<?php
    class Logger{
        private $db;
        private $logged="";
<<<<<<< HEAD
  


        public function getLogger($userid,$pwd,$dblog){
            $uid=$userid;
            $pwrd=$pwd;
            $dblogger=$dblog;
            $db=getDB($dblog);
=======



        public function getLogger($userid,$pwd){
            $db=getDB();
>>>>>>> de858115e51748a912198fe39284ab8d201649f1
            $QuerySlice="SELECT * FROM t_slice_payment";
            $SQL_PREPARE=$db->prepare($QuerySlice);
            $SQL_PREPARE->execute();
            $SlicePayment=$SQL_PREPARE->fetchAll(PDO::FETCH_OBJ);
<<<<<<< HEAD
=======

>>>>>>> de858115e51748a912198fe39284ab8d201649f1
            $Query="SELECT _MATR,_USERNAME,_PRIORITY,_CODE_DIRECTION,_ANASCO,_NAME FROM t_login login 
                    JOIN t_agent agent ON login._MATR_AGENT=agent._MATR
                    WHERE login._USERNAME=:userid AND login._PWD=:pwd";

                   $SQL_PREPARE=$db->prepare($Query);
                   $SQL_PREPARE->execute(array(
                       "userid"=>$userid,
                       "pwd"=>md5($pwd)
                   ));
                   $Login=$SQL_PREPARE->fetchAll(PDO::FETCH_OBJ);

                   if (sizeof($Login)==1) {
<<<<<<< HEAD
                    $_SESSION['dblog']=$dblog;
=======

>>>>>>> de858115e51748a912198fe39284ab8d201649f1
                    $users=$this->getCounterStats($Login[0]->_CODE_DIRECTION);
                    $pupils=$this->getPupils
                    (
                        $Login[0]->_CODE_DIRECTION,
                        $Login[0]->_PRIORITY,
                        $Login[0]->_ANASCO
                    );
                    $agents=$this->getAgents($Login[0]->_CODE_DIRECTION,$Login[0]->_PRIORITY);
                       $logged=array
                       (
                        'login'=>$Login,
                        'slices'=>$SlicePayment,
                        'users'=>$users,
                        'pupils'=>$pupils,
                        'agents'=>$agents,
                        'years'=>$this->getYears()
                       );
                   } else {
                       return array();
                   }
                   return $logged;
        }
        public function getCounterStats($direction){
<<<<<<< HEAD
            $db=getDB($_SESSION['dblog']);
=======
            $db=getDB();
>>>>>>> de858115e51748a912198fe39284ab8d201649f1
            $Query="SELECT _MATR,_USERNAME,_PRIORITY,_CODE_DIRECTION,_ANASCO FROM t_login ".
            " login JOIN t_agent agent ON login._MATR_AGENT=agent._MATR".
            " WHERE agent._CODE_DIRECTION=:direction";
            $sql=$db->prepare($Query);
            $sql->execute
            (
                array
                (
                    'direction'=>$direction
                )
            );
            $response=$sql->fetchAll(PDO::FETCH_OBJ);
            return $response;


        }

        public function getPupils($direction,$priority,$anasco){
<<<<<<< HEAD
            $db=getDB($_SESSION['dblog']);
=======
            $db=getDB();
           
>>>>>>> de858115e51748a912198fe39284ab8d201649f1
            $Query="SELECT DISTINCT(students._MAT),students._NAME,students._SEX,students._PICTURE 
                    FROM t_students students
                    JOIN t_payment pay ON students._MAT=pay._MATR
                    WHERE pay._DEPARTMENT = :direction AND pay._ANASCO = :anasco";
            // " GROUP BY students._MAT";
            $sql=$db->prepare($Query);
            $sql->execute([
                'direction'=>$direction,
                'anasco'=>$anasco
            ]);
            $response=$sql->fetchAll(PDO::FETCH_OBJ);

            return $response;
        }

        public function getAgents($direction,$priority){
<<<<<<< HEAD
            $db=getDB($_SESSION['dblog']);
=======
            $db=getDB();
>>>>>>> de858115e51748a912198fe39284ab8d201649f1
            switch ($priority) {
                case 'user':
                    $Query="SELECT * FROM t_agent WHERE _CODE_DIRECTION=:direction";
                    $sql=$db->prepare($Query);
                    $sql->execute
                    (
                        array
                        (
                            'direction'=>$direction
                        )
                    );
                    $response=$sql->fetchAll(PDO::FETCH_OBJ);
                    break;

                default:
                    $Query="SELECT * FROM t_agent WHERE _CODE_DIRECTION=:direction";
                    $sql=$db->prepare($Query);
                    $sql->execute
                    (
                        array
                        (
                            'direction'=>$direction
                        )
                    );
                    $response=$sql->fetchAll(PDO::FETCH_OBJ);
                break;
            }
            return $response;
        }
        public function getYears(){
<<<<<<< HEAD
            $db=getDB($_SESSION['dblog']);
=======
            $db=getDB();
>>>>>>> de858115e51748a912198fe39284ab8d201649f1
            $query="SELECT * FROM t_years_school ORDER BY year DESC";
            $query_execute=$db->prepare($query);
            $query_execute->execute();
            $response=$query_execute->fetchAll(PDO::FETCH_OBJ);
            return $response;
        }
    }
?>
