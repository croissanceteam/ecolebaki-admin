<?php
<<<<<<< HEAD
$test="yolo";
define('__INSTANCES__',$instanceDB= array(
	'yolo' => 'db_baki', 
	'ndjili' => 'db_baki_2',
	'kimbanseke' => 'db_baki_3'
));


function getDB($dblog) {
	$dbname='';
	foreach (__INSTANCES__ as $key => $data) {
		if(strtolower($dblog)==$key){
			$dbname=$data;
		}
			
			
	}
	//echo 'Database is :'.$dbname;
	$dbhost="127.0.0.1";
	$dbuser="root";
	$dbpass="root";
=======
function getDB() {
	$dbhost="127.0.0.1";
	$dbuser="root";
	$dbpass="";
	$dbname="db_baki";
>>>>>>> de858115e51748a912198fe39284ab8d201649f1
	$pdo = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass,array(1002=> 'SET NAMES utf8'));
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
	return $pdo;
}

function queryDB($sql, $parameters = NULL) {

	if ($parameters) {
<<<<<<< HEAD
		$req = getDB($_SESSION['dblog'])->prepare($sql);
=======
		$req = getDB()->prepare($sql);
>>>>>>> de858115e51748a912198fe39284ab8d201649f1
		$req->execute($parameters);
	} else
		$req = getDB()->query($sql);

	return $req;
}

?>
<?php
       define("__DATE__", "");
       define("__TIME__", "");
	 	//  define("__COUNTRIES__", file_get_contents("js/countries.json"));
<<<<<<< HEAD
	 //	getDB();
=======
	 	getDB();
>>>>>>> de858115e51748a912198fe39284ab8d201649f1

?>
