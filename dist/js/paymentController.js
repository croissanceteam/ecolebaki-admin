app.controller('PaymentsCtrl', function ($scope, $http) {

  $http.get('listyears').then(function (response) {
    console.log('listyears : ',response.data);
    $scope.years = response.data;
    //for initialising the list by the first value : $scope.cbo_year = $scope.years[0];
  }, function (error) {
      console.log('Erreur ',error)
  })


  $scope.hideLoader = true;
  $scope.showCriteria = true;

  $scope.currency = $('#currency').text();

  var now = moment();
  var startMonth = moment('2019-01-01','YYYY-MM-DD');

  var nbrmonths = moment().diff(startMonth,'months');
  console.log('Nombre des mois écoulés :',nbrmonths);

  var monthsArray = []; //declare un tableau avec un 1er item vide
  for (var i = 1; i <= nbrmonths ; i++) {
    //I add objects into my array
    monthsArray[i] = {
        'month' : moment().subtract(i,'month').format('MMMM YYYY')
    };
  }
  monthsArray.shift(); //supprime le 1er item
  console.log('tableau :',monthsArray);
  $scope.months = monthsArray;


  $scope.periodicityChanged = function(){
    // console.log('Périodicité :',$scope.cbo_periodicity);
    dayField = document.getElementById('datepickerdiv');
    monthField = document.getElementById('monthlistdiv');
    if($scope.cbo_periodicity == 'daily'){
      dayField.className = 'form-group col-sm-4 show-day';
      monthField.className = 'form-group col-sm-4 hide-day';
      document.getElementById('month').selectedIndex = -1;
      // alert($('#month option:selected').text());
    }else{
      dayField.className = 'form-group col-sm-4 hide-day';
      monthField.className = 'form-group col-sm-4 show-day';
      document.getElementById('day').value = "";
    }
    $scope.showResults = false;
  }
  $scope.criteriaChanged = function(){
    $scope.showResults = false;
  }

  $scope.sendRequestTab = function () {

    var day = ($('#day').val()).trim();
    var month = $('#month option:selected').text();
    // console.log('cbo_periodicity :',$scope.cbo_periodicity);
    // console.log('cbo_month :',$scope.cbo_month);
    console.log('day :',day);
    $scope.reportTitle = "";
    if($scope.cbo_year != undefined && $scope.cbo_periodicity != undefined && (month != "" || day != "")){
      $scope.hideLoader = false;
      $scope.showCriteria = false;
      $scope.income = 0;
      if(month != ""){
          $scope.reportTitle = "RECETTE MENSUELLE";
          $scope.labelTotal = "TOTAL MENSUEL";
          $scope.period = month;
          console.log($scope.reportTitle);
          var startOfMonth = moment(month,'MMMM YYYY').startOf('month').format('YYYY-MM-DD');
          var endOfMonth = moment(month,'MMMM YYYY').endOf('month').format('YYYY-MM-DD');
          console.log(startOfMonth,endOfMonth);
          var url = "getmonthlyincome/" + startOfMonth + "/" + endOfMonth + "/" + $scope.cbo_year.year;
          console.log('URL :',url);
          $http.get(url).then(function (response) {
            console.log('withError :',response.data.withError);
            console.log('Data :',response.data);
            $scope.hideLoader = true;
            $scope.showCriteria = true;
            if(response.data.withError || response.data.withError == undefined){
              alertify.error('Impossible de recupérer les données.');
            }else{
              // $scope.income = parseFloat(response.data.totalGlobal).toFixed(2);
              // console.log(typeof(response.data.totalGlobal));
              $scope.income = $scope.formatMoney(response.data.totalGlobal);
              // $scope.totalPayByTerm = response.data.totalbyTerm;
              var payTab = response.data.totalbyTerm;
              $scope.totalPayByTerm = [];
              angular.forEach(response.data.totalbyTerm,function(value,key){
                $scope.totalPayByTerm[key] = {
                  'recette' : $scope.formatMoney(value.recette),
                  'trim' : value.trim
                };
              });
              // console.log(payTab);
              $scope.payTable = response.data.payLines;
              if(response.data.totalGlobal == null || response.data.totalGlobal == 0){
                alertify.alert('Aucun paiement retrouvé en <span style="font-weight:bold">' + month + '</span> pour l\'année scolaire <span style="font-weight:bold">' + $scope.cbo_year.year + '</span>');
              }else{
                $scope.showResults = true;
              }
            }

          }, function (error) {
              console.log('Error :',error);

          });
      } else if(day != "") {
          var correctday = moment(day,'DD/MM/YYYY').format('YYYY-MM-DD');
          console.log('day formated:',correctday);
          $scope.reportTitle = "RECETTE JOURNALIERE";
          $scope.labelTotal = "TOTAL JOURNALIER";
          $scope.period = day;
          console.log($scope.reportTitle);
          var url = 'getdailyincome/' + correctday + '/' + $scope.cbo_year.year;
          console.log('URL :',url);
          $http.get(url).then(function (response) {
            console.log('withError :',response.data.withError);
            console.log('Data :',response.data);
            $scope.hideLoader = true;
            $scope.showCriteria = true;
            if(response.data.withError || response.data.withError == undefined){
              alertify.error('Impossible de recupérer les données.');
            }else{
              // $scope.income = parseFloat(response.data.totalGlobal).toFixed(2);
              $scope.income = $scope.formatMoney(parseFloat(response.data.totalGlobal));
              // $scope.totalPayByTerm = response.data.totalbyTerm;
              var payTab = response.data.totalbyTerm;
              $scope.totalPayByTerm = [];
              angular.forEach(response.data.totalbyTerm,function(value,key){
                $scope.totalPayByTerm[key] = {
                  'recette' : $scope.formatMoney(value.recette),
                  'trim' : value.trim
                };
              });
              $scope.payTable = response.data.payLines;
              $scope.hideLoader = true;
              $scope.showCriteria = true;
              if(response.data.totalGlobal == null || response.data.totalGlobal == 0){
                alertify.alert('Aucun paiement retrouvé à la date du <span style="font-weight:bold">' + day + '</span> pour l\'année scolaire <span style="font-weight:bold">' + $scope.cbo_year.year + '</span>');
              }else{
                $scope.showResults = true;
              }
            }


          }, function (error) {
              console.log('Error :',error);
          });
      }

    } else {
      console.log('Mauvais');
      alertify.alert('Veuillez remplir tous les critères d\'édition');
    }

  }
  $scope.getInsolvents = function(){
    if($scope.cbo_year != undefined || $scope.cbo_class != undefined || $scope.cbo_trim != undefined){
      $scope.hideLoader = false;
      $scope.showCriteria = false;
      $scope.reportTitle = "LISTE DES INSOLVABLES";
      var promotion = $scope.cbo_promotion.toString().trim().split(" ");
      var level = promotion[0].substring(0, 1);
      var option = promotion[1];
      var termSplited = $scope.cbo_trim.toString().trim().split(" ");
      var term = termSplited[0].substring(0, 1)+'TRIM';
      console.log(term);
      var url = 'getinsolventslist/'+ $scope.cbo_year.year +'/'+ level + '/' + option + '/' + term;
      $http.get(url).then(function (response){
        console.log('Response :',response.data);
        $scope.hideLoader = true;
        $scope.showCriteria = true;
        if(response.data.withError || response.data.withError == undefined){
          alertify.error('Il y a une petite erreur. Veuillez contacter l\'administrateur.');
        }else{
          $scope.amountopay = response.data.termAmount;
          $scope.insolventList = response.data.insolventList;
          $scope.pupilsCounter = response.data.pupilsCounter;
          $scope.insolventsCounter = response.data.insolventsCounter;
          if ($scope.pupilsCounter == 0) {
            alertify.alert('Cette classe n\'a aucun élève');
          }else if($scope.insolventsCounter == 0){
            alertify.alert('Cette classe est en ordre pour le trimestre sélectonné');
          }else{
            $scope.totalPaid = 0;
            $scope.totalRemaining = 0;
            angular.forEach(response.data.insolventList,function(value,key){
              $scope.totalPaid += parseFloat(value.sumpaid);
              $scope.totalRemaining += parseFloat(value.remaining);
            });

            if ($scope.totalPaid == 0) {
              alertify.alert('Aucun élève de cette classe n\'a payé pour ce trimestre');
            }else {
              $scope.totalPaid = $scope.formatMoney($scope.totalPaid);
              $scope.totalRemaining = $scope.formatMoney($scope.totalRemaining);
              $scope.showResults = true;
            }

          }

        }
      }, function (error) {
          console.log('Error :',error);
          alertify.error('Il y a une petite erreur. Veuillez contacter l\'administrateur.');
      });

    }else{
      console.log('Mauvais');
      alertify.alert('Veuillez remplir tous les critères d\'édition');
    }
  }
  $scope.getReport = function(){
    if($scope.cbo_year != undefined || $scope.cbo_class != undefined || $scope.cbo_trim != undefined || $scope.cbo_report){
      $scope.hideLoader = false;
      $scope.showCriteria = false;

      switch ($scope.cbo_report) {
        case 'solde':
          $scope.reportTitle = "LISTE DES ELEVES EN ORDRE DE PAIEMENT";
          break;
        case 'partiel':
          $scope.reportTitle = "LISTE DES ELEVES AYANT PAYE AU MOINS UN ACOMPTE";
          break;
        default:
          $scope.reportTitle = "LISTE DES ELEVES N'AYANT RIEN PAYE";
          break;
      }

      var promotion = $scope.cbo_promotion.toString().trim().split(" ");
      var level = promotion[0].substring(0, 1);
      var option = promotion[1];
      var termSplited = $scope.cbo_trim.toString().trim().split(" ");
      var term = termSplited[0].substring(0, 1)+'TRIM';
      console.log(term);
      var url = 'payreport/'+ $scope.cbo_year.year +'/'+ level + '/' + option + '/' + term +'/'+ $scope.cbo_report;
      $http.get(url).then(function (response){
        console.log('Response :',response.data);
        $scope.hideLoader = true;
        $scope.showCriteria = true;
        if(response.data.withError || response.data.withError == undefined){
          alertify.error('Il y a une petite erreur. Veuillez contacter l\'administrateur.');
        }else{
          $scope.amountopay = response.data.termAmount;
          $scope.pupilList = response.data.pupilList;
          $scope.totalPupils = response.data.totalPupils;
          $scope.pupilFound = response.data.pupilFound;
          if ($scope.totalPupils == 0) {
            alertify.alert('Cette classe n\'a aucun élève');
          }else{
            $scope.totalPaid = 0;
            $scope.totalRemaining = 0;
            angular.forEach(response.data.pupilList,function(value,key){
              $scope.totalPaid += parseFloat(value.sumpaid);
              $scope.totalRemaining += parseFloat(value.remaining);
            });

            if ($scope.totalPaid == 0 && $scope.pupilFound == 0 && $scope.cbo_report == 'solde') {
              alertify.alert('Aucun élève de cette classe n\'a soldé pour ce trimestre');
            }else if ($scope.totalPaid == 0 && $scope.pupilFound == 0 && $scope.cbo_report == 'partiel') {
              alertify.alert('Aucun élève de cette classe n\'a payé en partie pour ce trimestre');
            }else if ($scope.totalPaid == 0 && $scope.pupilFound == 0 && $scope.cbo_report == 'aucun') {
              alertify.alert('Tous les élèves de cette classe ont au moins payé quelque chose pour ce trimestre');
            }else if ($scope.totalPaid == 0 && $scope.pupilFound == $scope.totalPupils && $scope.cbo_report == 'aucun') {
              alertify.alert('Tous n\'ont rien payé pour ce trimestre');
            }else{
              $scope.totalPaid = $scope.formatMoney($scope.totalPaid);
              $scope.totalRemaining = $scope.formatMoney($scope.totalRemaining);
              $scope.showResults = true;
            }

          }

        }
      }, function (error) {
          console.log('Error :',error);
          alertify.error('Il y a une petite erreur. Veuillez contacter l\'administrateur.');
      });

    }else{
      console.log('Mauvais');
      alertify.alert('Veuillez remplir tous les critères d\'édition');
    }
  }
  $scope.seePrintView = function (){
    // $scope.reportTitle = $scope.reportTitle.toUpperCase();
    document.getElementById('menuBar').style.display="none";
    $scope.showCriteria = false;
    $scope.showResults = false;
    $scope.showPrintView = true;
  }
  $scope.print=function(){
      document.querySelector('#btnprint').style.display="none";
      document.querySelector('#btnqprint').style.display="none";
      window.print();
      window.location.reload();

  }
  $scope.quitPrint = function(){
      window.location.reload();
  }
  $scope.formatMoney = function(amount, currency = $scope.currency, decimalCount = 2, decimal = ".", thousands = " ") {
      try {
          decimalCount = Math.abs(decimalCount);
          decimalCount = isNaN(decimalCount) ? 2 : decimalCount;

          //const negativeSign = amount < 0 ? "-" : "";

          let i = parseInt(amount = Math.abs(Number(amount) || 0).toFixed(decimalCount)).toString();
          let j = (i.length > 3) ? i.length % 3 : 0;

          return currency + ' ' + (j ? i.substr(0, j) + thousands : '') + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thousands) + (decimalCount ? decimal + Math.abs(amount - i).toFixed(decimalCount).slice(2) : "");
      } catch (e) {
          console.log('format money error :',e);
      }
  }


});
