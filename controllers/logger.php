
<?php
    include_once 'BaseController.php';

    class Logger extends BaseController {
        private $db;
        private $logged="";

        public function getLogger($userid,$pwd,$dblog){
            $_SESSION['dblog']=$dblog;
            $db = parent::db();
            if (gettype($db)!="NULL") {
              $req = "SELECT _MATR,_USERNAME,_PWD AS password,_PRIORITY,_CODE_DIRECTION,_ANASCO,_NAME,_LOCKED AS locked FROM t_login login
                      JOIN t_agent agent ON login._MATR_AGENT=agent._MATR
                      WHERE login._USERNAME=?";

              $Login = queryDB($req,[$userid])->fetch();

              switch ($Login->locked) {
                case 0:
                  if ($Login && password_verify($pwd, $Login->password)) {

                      $QueryTerm = "SELECT * FROM t_terms WHERE _ANASCO = ?";
                      $SQL_PREPARE = $db->prepare($QueryTerm);
                      $SQL_PREPARE->execute([$Login->_ANASCO]);
                      $TermPayment = $SQL_PREPARE->fetchAll();

                      $users = $this->getCounterStats($Login->_CODE_DIRECTION);
                      $pupils = $this->getPupils($Login->_CODE_DIRECTION, $Login->_PRIORITY, $Login->_ANASCO);
                      $agents = $this->getAgents($Login->_CODE_DIRECTION, $Login->_PRIORITY);
                      $logged = array
                          (
                          'login' => $Login,
                          'terms' => $TermPayment,
                          'users' => $users,
                          'pupils' => $pupils,
                          'agents' => $agents,
                          'years' => $this->getYears()
                      );
                  } else {
                      return false;
                  }
                  return $logged;

                  break;

                default:
                  return 'locked';
                  break;
              }
            }else{
              return [];
            }

        }
        public function getCounterStats($direction){

          $Query = "SELECT _MATR,_USERNAME,_PRIORITY,_CODE_DIRECTION,_ANASCO FROM t_login
                    login JOIN t_agent agent ON login._MATR_AGENT=agent._MATR
                    WHERE agent._CODE_DIRECTION=:direction";
          $sql = parent::db()->prepare($Query);
          $sql->execute(['direction' => $direction]);
          $response = $sql->fetchAll();
          return $response;
        }

        public function getPupils($direction,$priority,$anasco){
            $Query="SELECT DISTINCT(students._MAT),students._NAME,students._SEX,students._PICTURE
                    FROM t_students students
                    JOIN t_payment pay ON students._MAT=pay._MATR
                    WHERE pay._DEPARTMENT = :direction AND pay._ANASCO = :anasco";
            // " GROUP BY students._MAT";
            $sql=parent::db()->prepare($Query);
            $sql->execute([
                'direction'=>$direction,
                'anasco'=>$anasco
            ]);
            $response=$sql->fetchAll();

            return $response;
        }

        public function getAgents($direction,$priority){
            $db=parent::db();
            switch ($priority) {
                case 'user':
                    $Query="SELECT * FROM t_agent WHERE _CODE_DIRECTION=:direction";
                    $sql=$db->prepare($Query);
                    $sql->execute(['direction'=>$direction]);
                    $response=$sql->fetchAll();
                    break;

                default:
                    $Query="SELECT * FROM t_agent WHERE _CODE_DIRECTION=:direction";
                    $sql=$db->prepare($Query);
                    $sql->execute(['direction'=>$direction]);
                    $response=$sql->fetchAll();
                break;
            }
            return $response;
        }
        public function getYears(){
            $query="SELECT * FROM t_years_school ORDER BY year DESC";
            $query_execute=parent::db()->prepare($query);
            $query_execute->execute();
            $response=$query_execute->fetchAll();
            return $response;
        }
    }
?>
