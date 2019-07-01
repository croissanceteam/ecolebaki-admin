<?php
session_start();
if (!isset($_SESSION['usrid'])) {
    header('Location:login');
}
?>
<!DOCTYPE html>
<html>

    <head>

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">

        <title>Audit des paiements</title>

        <!-- Bootstrap Core CSS -->
        <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

        <!-- MetisMenu CSS -->
        <link href="vendor/metisMenu/metisMenu.min.css" rel="stylesheet">

        <!-- DataTables CSS -->
        <link href="vendor/datatables-plugins/dataTables.bootstrap.css" rel="stylesheet">

        <!-- DataTables Responsive CSS -->
        <link href="vendor/datatables-responsive/dataTables.responsive.css" rel="stylesheet">
        <!-- Alertify -->
        <link rel="stylesheet" href="vendor/alertify/themes/alertify.css" />
        <!-- Custom CSS -->
        <link href="dist/css/sb-admin-2.css" rel="stylesheet">
        <link href="dist/css/custom.css" rel="stylesheet" type="text/css">

        <!-- Custom Fonts -->
        <link href="vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
        <link href="dist/css/bootstrap-datepicker.standalone.min.css" rel="stylesheet" type="text/css">
        <link href="dist/css/bootstrap-datetimepicker.css" rel="stylesheet" type="text/css">


        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
            <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->

        <style type="text/css">
            .loader{
                width: 100%;
                text-align: center;
                margin: 0 auto;
            }
            .hide-day, .hide-month{
              display: none;
            }
            .show-day, .show-month{
              text-align: center;
              display: block;
            }
            .ng-hide:not(.ng-hide-animate) {
              /* These are just alternative ways of hiding an element */
              display: block!important;
              position: absolute;
              top: -9999px;
              left: -9999px;
            }
            .label-head{
                /* margin-right:2mm; */
                padding: 1mm;
                padding-right: 2mm;
                text-align: right;
                width:50%;
            }
            .label-value{
              text-align: left;
              width:50%;
            }
            table,th{
              font-size: 12px;
              text-align: center;
            }
        </style>
    </head>

    <body ng-app='app' ng-controller="PaymentsAuditCtrl">

        <div id="wrapper">

            <?php
            require_once 'partials/menu-bar.php';
            ?>
            <div id="page-wrapper">
                <span style="display:none" id="userpriority"><?= $_SESSION['priority']  ?></span>
                <div class="loader ng-show" ng-hide="hideLoader">
                    <img src="dist/images/loader/spinner.gif">
                </div>
                <div class="row ng-hide" ng-show="showPrintView">
                    <div class="col-lg-12" style="padding: 0mm 8mm;">
                        <div>
                            <table style="width: 100%;">
                                <tr>
                                    <td style="text-align: left;width: 67%;"><h5 style="margin-bottom:22px;"> ECOLE BAKI / <?= $_SESSION['direction'] ?></h5>Nº9, Av. M'fimi <br>Q/YOLO-NORD<br>C/KALAMU.<br>Site internet: www.ecolebaki.com </td>
                                    <td style="text-align: right; width: 33%"><img src="dist/images/logo-reverse.png" width="150" alt="Logo"> </td>
                                </tr>

                            </table>
                        </div>
                        <div style="text-align: center; border-top:2px solid grey; border-bottom:2px solid grey;">
                            <h4>{{ reportTitle }}</h4>
                        </div>
                    </div>
                </div>
                <!-- /.row -->
                <div class="row ng-hide" ng-show="showCriteria">

                    <div class="col-lg-12">
                        <label class="page-header" style="width: 100%;font-size: 16px;">
                            Audit des modifications des paiements

                            <span style='float:right;font-size: 16px;' id='lbl_year'>
                                <?= $_SESSION['anasco'] ?>
                            </span>
                        </label>
                      <div class="panel panel-default">
                        <div class="panel-heading" style="height:3.3em">
                          Critères d'éditon
                          <button type="button" class="btn btn-success" ng-click="sendRequestTab()" style="float:right;margin-top:-0.3em">
                              Editer <i class="fa fa-search"></i>
                          </button>
                          <button class="btn btn-success ng-hide" ng-click="seePrintView()" ng-show="showResults" style="float:right;margin-top:-0.3em;margin-right:0.56em;" type="button">
                              Aperçu avant impression <i class="fa fa-eye"></i>
                          </button>
                        </div>
                        <!--/.panel-heading-->
                        <div class="panel-body">
                          <form>
                              <div class="form-group col-sm-4" style="text-align:center;">
                                  <label for="year">ANNEE SCOLAIRE</label>
                                  <select class="form-control" name="year" id="year" ng-model="cbo_year" ng-options="item.year for item in years" ng-change="criteriaChanged()" class="chzn-select" style="width:100%">

                                  </select>
                              </div>
                              <div class="form-group col-sm-4" style="text-align:center;">
                                  <label for="periodicity">PERIODICITE</label>
                                  <select class="form-control" id="periodicity" ng-model="cbo_periodicity" ng-change="periodicityChanged()" class="chzn-select" style="width:100%">
                                    <option value="daily">Journalière</option>
                                    <option value="monthly">Mensuelle</option>
                                  </select>
                              </div>
                              <div id="monthlistdiv" class="form-group col-sm-4 hide-month">
                                <label for="">MOIS</label>
                                <select id="month" class="form-control" ng-model="cbo_month" ng-options="item.month for item in months" ng-change="criteriaChanged()" name="month">

                                </select>
                              </div>
                              <div id="datepickerdiv" class="form-group col-sm-4 hide-day">
                                  <label for="day">JOUR</label>
                                  <div class="input-group date dp" style="" data-provider="datepicker">

                                      <input id="day" type="text" name="day" ng-model="day" ng-change="criteriaChanged()" class="form-control" />
                                      <div class="input-group-addon">
                                          <span class="fa fa-calendar"></span>
                                      </div>
                                  </div>
                              </div>
                          </form>
                        </div>
                        <!--/.panel-body-->
                      </div>

                    </div>
                    <!-- /.col-lg-12 -->
                </div>
                <!-- /.row -->


                <div class="row ng-hide" ng-show="showResults">
                  <div class="col-lg-12">
                    <div class="panel panel-default">
                      <div class="panel-heading" style="height:3.7em">
                          Table des données
                          <div class="controls" style="float:right;width:50%;">
                              <input ng-model="req" ng-change="queryTab()" class="form-control" placeholder="Rechercher un élève sur cette liste"  type="text" style="width:100%;" value="">
                          </div>
                      </div>
                      <div class="panel-body">
                        <table style="width:100%">
                          <tr>
                            <th class="label-head">Année scolaire </th>
                            <th class="label-value"> : {{ cbo_year.year }}</th>
                          </tr>
                          <tr>
                            <th class="label-head">Classe </th>
                            <th class="label-value"> : {{ cbo_promotion }}</th>
                          </tr>
                          <tr>
                            <th class="label-head">Trimestre </th>
                            <th class="label-value"> : {{ cbo_trim }}</th>
                          </tr>
                          <tr>
                            <th class="label-head">Montant à payer </th>
                            <th class="label-value"> : {{ currency }} {{ amountopay }}</th>
                          </tr>
                          <tr>
                            <th class="label-head">Nombre </th>
                            <th class="label-value"> : {{pupilFound}} sur {{totalPupils}} élèves</th>
                          </tr>
                        </table>
                        <hr>
                        <table class="table" style="display:none">
                          <thead>
                            <tr>
                              <th scope="col">Trimestre</th>
                              <th scope="col">Montant</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr class="success" ng-repeat="row in totalPayByTerm">
                              <td>{{row.trim}} </td>
                              <td>{{row.recette}} {{ currency }}</td>
                            </tr>
                            <tr>
                              <th>{{ labelTotal}}</th>
                              <th>{{ income }} {{ currency }}</th>
                            </tr>
                          </tbody>
                        </table>
                        <hr>
                        <table class="table table-striped">
                          <thead>
                            <tr>
                              <th scope="col">MATRICULE</th>
                              <th scope="col">NOM COMPLET</th>
                              <th scope="col">TOTAL PAYE</th>
                              <th scope="col">RESTE A PAYER</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr ng-repeat="pupil in pupilList | filter:req">
                              <td>{{pupil.matricule}} </td>
                              <td>{{pupil.pupilname}} </td>
                              <td>{{ currency }} {{pupil.sumpaid}}</td>
                              <td>{{ currency }} {{pupil.remaining}}</td>
                            </tr>
                            <tr>
                              <th>TOTAL</th>
                              <th></th>
                              <th>{{totalPaid}}</th>
                              <th>{{totalRemaining}}</th>
                            </tr>
                          </tbody>
                        </table>
                      </div>

                    </div>
                  </div>
                </div>
                <!-- /.row -->
                <div class="row ng-hide" ng-show="showPrintView">
                  <div class="col-lg-12">
                    <div class="inner-block">
                      <table style="width:100%">
                        <tr>
                          <th class="label-head">Année scolaire </th>
                          <th class="label-value"> : {{ cbo_year.year }}</th>
                        </tr>
                        <tr>
                          <th class="label-head">Classe </th>
                          <th class="label-value"> : {{ cbo_promotion }}</th>
                        </tr>
                        <tr>
                          <th class="label-head">Trimestre </th>
                          <th class="label-value"> : {{ cbo_trim }}</th>
                        </tr>
                        <tr>
                          <th class="label-head">Montant à payer </th>
                          <th class="label-value"> : {{ currency }} {{ amountopay }}</th>
                        </tr>
                        <tr>
                          <th class="label-head">Insolvables </th>
                          <th class="label-value"> : {{pupilFound}} sur {{totalPupils}} élèves</th>
                        </tr>
                      </table>
                      <hr>
                      <table class="table" style="display:none">
                        <thead>
                          <tr>
                            <th scope="col">Trimestre</th>
                            <th scope="col">Montant</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr ng-repeat="row in totalPayByTerm">
                            <td>{{row.trim}} </td>
                            <td>{{row.recette}} {{ currency }}</td>
                          </tr>
                          <tr>
                            <th>{{ labelTotal}}</th>
                            <th>{{ income }} {{ currency }}</th>
                          </tr>
                        </tbody>
                      </table>
                      <hr>
                      <table class="table">
                        <thead>
                          <tr>
                            <th scope="col">MATRICULE</th>
                            <th scope="col">NOM COMPLET</th>
                            <th scope="col">TOTAL PAYE</th>
                            <th scope="col">RESTE A PAYER</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr ng-repeat="pupil in pupilList">
                            <td>{{pupil.matricule}} </td>
                            <td>{{pupil.pupilname}} </td>
                            <td>{{ currency }} {{pupil.sumpaid}}</td>
                            <td>{{ currency }} {{pupil.remaining}}</td>
                          </tr>
                          <tr>
                            <th>TOTAL</th>
                            <th></th>
                            <th>{{totalPaid}}</th>
                            <th>{{totalRemaining}}</th>
                          </tr>
                        </tbody>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
                <!-- /.row -->
                <div class="ng-hide" ng-show="showPrintView">
                    <button id="btnprint" type="button" ng-click="print()" class="btn btn-success print">
                        <i class="fa fa-print"></i> Imprimer
                    </button>
                    <button type="button" ng-click="quitPrint()" id="btnqprint" class="btn btn-default print">
                        <i class="fa fa-print"></i> Quitter l'aperçu
                    </button>
                </div>
            </div>
            <!-- /#page-wrapper -->

        </div>
        <!-- /#wrapper -->

        <!-- jQuery -->
        <script src="vendor/jquery/jquery.min.js"></script>

        <!-- Bootstrap Core JavaScript -->
        <script src="vendor/bootstrap/js/bootstrap.min.js"></script>

        <!-- Metis Menu Plugin JavaScript -->
        <script src="vendor/metisMenu/metisMenu.min.js"></script>

        <!-- DataTables JavaScript -->
        <script src="vendor/datatables/js/jquery.dataTables.min.js"></script>
        <script src="vendor/datatables-plugins/dataTables.bootstrap.min.js"></script>
        <script src="vendor/datatables-responsive/dataTables.responsive.js"></script>

        <!-- Alertify -->
        <script src="vendor/alertify/lib/alertify.min.js"></script>

        <script src="vendor/moment/moment.js"></script>
        <script src="vendor/moment/locale/fr.js"></script>
        <!-- Custom Theme JavaScript -->
        <script src="dist/js/sb-admin-2.js"></script>
        <script src="dist/js/angular.min.js"></script>
        <script src="dist/js/init.js"></script>
        <script src="dist/js/paymentAuditController.js"></script>
        <script src="dist/js/bootstrap-datepicker.min.js"></script>
        <script src="dist/js/app.js"></script>
        <script>
        $('.dp').datepicker({
            format: "dd/mm/yyyy"
        });

          </script>

    </body>

</html>
