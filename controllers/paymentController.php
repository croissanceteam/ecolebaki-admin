<?php
include_once 'BaseController.php';

class PaymentController extends BaseController {

  /*
  * get all updates on payments during a specific day
  *
  */
  public function getDaylyPayUpdates($day,$year,$direction){
    try {
      $db = parent::db();
      $db->beginTransaction();

      $query = "SELECT pl.*, p._MATR,p._DATEPAY FROM _payments_listenner pl JOIN t_payment p ON p._IDPAY=pl._payment_id
                WHERE pl._updated_at = :day AND p._ANASCO = :anasco AND p._DEPARTMENT = :direction";
      $query_execute = $db->prepare($query);
      $query_execute->execute([
        'day'       => $day,
        'anasco'    => $year,
        'direction' => $direction
      ]);

      $queryPayLines = "SELECT pupil._MAT AS matricule,UPPER(pupil._NAME) AS pupil_name, pay._IDPAY AS id_pay, term._LABELTERM AS term,pay._AMOUNT AS amount_payed, LOWER(CONCAT(sub._CODE_CLASS,'è ',sub._CODE_SECTION)) AS class,agent._NAME AS percepteur
                      FROM t_students pupil JOIN t_subscription sub ON pupil._MAT=sub._MATR_PUPIL
                      JOIN t_payment pay ON pay._MATR=pupil._MAT
                      JOIN t_terms term ON term._CODETERM=pay._CODETERM
                      JOIN t_login user ON user._USERNAME=pay._USER_AGENT
                      JOIN t_agent agent ON agent._MATR=user._MATR_AGENT
                      WHERE _datepay = :day AND pay._anasco = :anasco AND pay._department = :direction";
      $execPayLines = $db->prepare($queryPayLines);
      $execPayLines->execute([
        'day'       => $day,
        'anasco'    => $year,
        'direction' => $direction
      ]);
      $PayLines = $execPayLines->fetchAll();

      $totalGlobal = 0;
      $response = [];
      $label = "";
      while ($data = $query_execute->fetch()) {
        $totalGlobal += $data->recette;
        if($data->trimestre == "1TRIM")
          $label = "Premier trimestre";
        elseif($data->trimestre == "2TRIM")
          $label = "Deuxième trimestre";
        elseif($data->trimestre == "3TRIM")
          $label = "Troisième trimestre";
        $response[] = [
          'recette'   =>  $data->recette,
          'trim'      =>  $label
        ];
      }

      $db->commit();

      return json_encode([
        'totalGlobal'    =>  $totalGlobal,
        'totalbyTerm'    =>  $response,
        'payLines'  =>  $PayLines,
        'withError'  =>  false,
      ]);

    } catch (\Exception $e) {
      $db->rollBack();
      return json_encode([
        'content' =>  $e->getMessage(),
        'withError'  =>  true
      ]);
    }
  }

  public function getPayReport($year,$level,$option,$term,$report,$direction)
  {
    try {
      $db = parent::db();
      $db->beginTransaction();


      $queryPupils = "SELECT student._MAT,student._NAME FROM t_students student
      JOIN t_subscription sub ON sub._MATR_PUPIL=student._MAT
      WHERE sub._ANASCO = :anasco AND sub._CODE_CLASS = :level AND sub._CODE_SECTION = :option AND sub._DEPARTMENT = :department AND student._STATUS = 1";
      $pupils = $db->prepare($queryPupils);
      $pupils->execute([
        'anasco'       => $year,
        'level'    => $level,
        'option' => $option,
        'department' => $direction
      ]);

      $queryTerm = "SELECT _AMOUNT FROM t_terms WHERE _CODETERM = :term AND _ANASCO = :anasco";
      $termAmount = $db->prepare($queryTerm);
      $termAmount->execute([ 'term' => $term, 'anasco' => $year]);
      // $dataTerm = $termAmount->fetch();
      $amount = $termAmount->fetch()->_AMOUNT;

      $queryPay = "SELECT SUM(_AMOUNT) AS somme FROM t_payment WHERE _MATR = :mat AND _ANASCO = :anasco AND _CODETERM = :term AND _DEPARTMENT = :department";
      $pupilList = [];
      $totalPupils = 0;

      if($report == 'solde'){
        while ($pupil = $pupils->fetch()) {
          $pay = $db->prepare($queryPay);
          $pay->execute([
            'mat'         =>  $pupil->_MAT,
            'anasco'      =>  $year,
            'term'        =>  $term,
            'department'  =>  $direction
          ]);
          $res = $pay->fetch()->somme;
          $paid = ($res == null) ? 0 : $res;
          if($amount == $paid){
            $pupilList[] = [
              'matricule' =>  $pupil->_MAT,
              'pupilname' =>  $pupil->_NAME,
              'sumpaid'   =>  $paid,
              'remaining' =>  bcsub($amount,$paid,2)
            ];
          }
          $totalPupils++;
        }
      }else if($report == 'partiel'){
        while ($pupil = $pupils->fetch()) {
          $pay = $db->prepare($queryPay);
          $pay->execute([
            'mat'         =>  $pupil->_MAT,
            'anasco'      =>  $year,
            'term'        =>  $term,
            'department'  =>  $direction
          ]);
          $res = $pay->fetch()->somme;
          $paid = ($res == null) ? 0 : $res;
          if($paid > 0 && $paid < $amount){
            $pupilList[] = [
              'matricule' =>  $pupil->_MAT,
              'pupilname' =>  $pupil->_NAME,
              'sumpaid'   =>  $paid,
              'remaining' =>  bcsub($amount,$paid,2)
            ];
          }
          $totalPupils++;
        }
      }else {
        while ($pupil = $pupils->fetch()) {
          $pay = $db->prepare($queryPay);
          $pay->execute([
            'mat'         =>  $pupil->_MAT,
            'anasco'      =>  $year,
            'term'        =>  $term,
            'department'  =>  $direction
          ]);
          $res = $pay->fetch()->somme;
          $paid = ($res == null) ? 0 : $res;
          if($paid == 0){
            $pupilList[] = [
              'matricule' =>  $pupil->_MAT,
              'pupilname' =>  $pupil->_NAME,
              'sumpaid'   =>  $paid,
              'remaining' =>  bcsub($amount,$paid,2)
            ];
          }
          $totalPupils++;
        }
      }
      $db->commit();

      return json_encode([
        'pupilList' =>  $pupilList,
        'termAmount' =>  $amount,
        'pupilFound' =>  sizeof($pupilList),
        'totalPupils' =>  $totalPupils,
        'withError'  =>  false
      ]);

    } catch (\Exception $e) {
      $db->rollBack();
      return json_encode([
        'exception' =>  $e->getMessage(),
        'line' =>  $e->getLine(),
        'withError'  =>  true
      ]);
    }

  }

  public function getInsolvents($year,$level,$option,$term,$direction)
  {
    try {
      $db = parent::db();
      $db->beginTransaction();
      $queryPupils = "SELECT student._MAT,student._NAME FROM t_students student
      JOIN t_subscription sub ON sub._MATR_PUPIL=student._MAT
      WHERE sub._ANASCO = :anasco AND sub._CODE_CLASS = :level AND sub._CODE_SECTION = :option AND sub._DEPARTMENT = :department AND student._STATUS = 1";
      $pupils = $db->prepare($queryPupils);
      $pupils->execute([
        'anasco'       => $year,
        'level'    => $level,
        'option' => $option,
        'department' => $direction
      ]);

      $queryTerm = "SELECT _AMOUNT FROM t_terms WHERE _CODETERM = :term AND _ANASCO = :anasco";
      $termAmount = $db->prepare($queryTerm);
      $termAmount->execute([ 'term' => $term, 'anasco' => $year]);
      // $dataTerm = $termAmount->fetch();
      $amount = $termAmount->fetch()->_AMOUNT;

      $queryPay = "SELECT SUM(_AMOUNT) AS somme FROM t_payment WHERE _MATR = :mat AND _ANASCO = :anasco AND _CODETERM = :term AND _DEPARTMENT = :department";
      $insolventList = [];
      $pupilsCounter = 0;
      while ($pupil = $pupils->fetch()) {
        $pay = $db->prepare($queryPay);
        $pay->execute([
          'mat'         =>  $pupil->_MAT,
          'anasco'      =>  $year,
          'term'        =>  $term,
          'department'  =>  $direction
        ]);
        $res = $pay->fetch()->somme;
        $paid = ($res == null) ? 0 : $res;

        $remaining = bcsub($amount,$paid,2);
        if($amount > $paid){
          $insolventList[] = [
            'matricule' =>  $pupil->_MAT,
            'pupilname' =>  $pupil->_NAME,
            'sumpaid'   =>  $paid,
            'remaining' =>  bcsub($amount,$paid,2)
          ];
        }

        $pupilsCounter++;
      }
      $db->commit();

      return json_encode([
        'insolventList' =>  $insolventList,
        'termAmount' =>  $amount,
        'insolventsCounter' =>  sizeof($insolventList),
        'pupilsCounter' =>  $pupilsCounter,
        'withError'  =>  false
      ]);

    } catch (\Exception $e) {
      $db->rollBack();
      return json_encode([
        'exception' =>  $e->getMessage(),
        'line' =>  $e->getLine(),
        'withError'  =>  true
      ]);
    }

  }

  public function getListOfYears() {
    $query = "SELECT * FROM t_years_school ORDER BY year DESC LIMIT 0,3";
    $query_execute = parent::DB()->prepare($query);
    $query_execute->execute();
    $response = $query_execute->fetchAll();
    return $response;
  }

  public function getDailyIncome($year,$day,$direction){
    try {
      $db = parent::db();
      $db->beginTransaction();

      $query = "SELECT _datepay, SUM(_amount) AS recette, _codeterm AS trimestre FROM t_payment WHERE _datepay = :day AND _anasco = :anasco AND _department = :direction GROUP BY _codeterm";
      $query_execute = $db->prepare($query);
      $query_execute->execute([
        'day'       => $day,
        'anasco'    => $year,
        'direction' => $direction
      ]);

      $queryPayLines = "SELECT pupil._MAT AS matricule,UPPER(pupil._NAME) AS pupil_name, pay._IDPAY AS id_pay, term._LABELTERM AS term,pay._AMOUNT AS amount_payed, LOWER(CONCAT(sub._CODE_CLASS,'è ',sub._CODE_SECTION)) AS class,agent._NAME AS percepteur
                      FROM t_students pupil JOIN t_subscription sub ON pupil._MAT=sub._MATR_PUPIL
                      JOIN t_payment pay ON pay._MATR=pupil._MAT
                      JOIN t_terms term ON term._CODETERM=pay._CODETERM
                      JOIN t_login user ON user._USERNAME=pay._USER_AGENT
                      JOIN t_agent agent ON agent._MATR=user._MATR_AGENT
                      WHERE _datepay = :day AND pay._anasco = :anasco AND pay._department = :direction";
      $execPayLines = $db->prepare($queryPayLines);
      $execPayLines->execute([
        'day'       => $day,
        'anasco'    => $year,
        'direction' => $direction
      ]);
      $PayLines = $execPayLines->fetchAll();

      $totalGlobal = 0;
      $response = [];
      $label = "";
      while ($data = $query_execute->fetch()) {
        $totalGlobal += $data->recette;
        if($data->trimestre == "1TRIM")
          $label = "Premier trimestre";
        elseif($data->trimestre == "2TRIM")
          $label = "Deuxième trimestre";
        elseif($data->trimestre == "3TRIM")
          $label = "Troisième trimestre";
        $response[] = [
          'recette'   =>  $data->recette,
          'trim'      =>  $label
        ];
      }

      $db->commit();

      return json_encode([
        'totalGlobal'    =>  $totalGlobal,
        'totalbyTerm'    =>  $response,
        'payLines'  =>  $PayLines,
        'withError'  =>  false,
      ]);

    } catch (\Exception $e) {
      $db->rollBack();
      return json_encode([
        'content' =>  $e->getMessage(),
        'withError'  =>  true
      ]);
    }
  }
  public function getMonthlyIncome($start,$end,$year,$direction){
    try {
      $db = parent::db();
      $db->beginTransaction();

      $query = "SELECT SUM(_amount) AS recette, _codeterm AS trimestre FROM t_payment WHERE _datepay BETWEEN :startperiod AND :endperiod AND _anasco = :anasco AND _department = :direction GROUP BY _codeterm";
      $query_execute = $db->prepare($query);
      $query_execute->execute([
        'startperiod' => $start,
        'endperiod'   => $end,
        'anasco'      => $year,
        'direction'   => $direction
      ]);

      $queryPayLines = "SELECT pupil._MAT AS matricule,UPPER(pupil._NAME) AS pupil_name, pay._IDPAY AS id_pay, term._LABELTERM AS term,pay._AMOUNT AS amount_payed, LOWER(CONCAT(sub._CODE_CLASS,'è ',sub._CODE_SECTION)) AS class,agent._NAME AS percepteur
                      FROM t_students pupil JOIN t_subscription sub ON pupil._MAT=sub._MATR_PUPIL
                      JOIN t_payment pay ON pay._MATR=pupil._MAT
                      JOIN t_terms term ON term._CODETERM=pay._CODETERM
                      JOIN t_login user ON user._USERNAME=pay._USER_AGENT
                      JOIN t_agent agent ON agent._MATR=user._MATR_AGENT
                      WHERE _datepay BETWEEN :startperiod AND :endperiod AND pay._anasco = :anasco AND pay._department = :direction";
      $execPayLines = $db->prepare($queryPayLines);
      $execPayLines->execute([
        'startperiod' => $start,
        'endperiod'   => $end,
        'anasco'      => $year,
        'direction'   => $direction
      ]);
      $PayLines = $execPayLines->fetchAll();

      $totalGlobal = 0;
      $response = [];
      $label = "";
      while ($data = $query_execute->fetch()) {
        $totalGlobal += $data->recette;
        if($data->trimestre == "1TRIM")
          $label = "Premier trimestre";
        elseif($data->trimestre == "2TRIM")
          $label = "Deuxième trimestre";
        elseif($data->trimestre == "3TRIM")
          $label = "Troisième trimestre";
        $response[] = [
          'recette' =>  $data->recette,
          'trim'    =>  $label
        ];
      }
      $db->commit();
      return json_encode([
        'totalGlobal'    =>  $totalGlobal,
        'totalbyTerm'    =>  $response,
        'payLines'  =>  $PayLines,
        'withError'  =>  false,
      ]);
    } catch (\Exception $e) {
      $db->rollBack();
      return json_encode([
        'content' =>  $e->getMessage(),
        'withError'  =>  true
      ]);
    }
  }

}
