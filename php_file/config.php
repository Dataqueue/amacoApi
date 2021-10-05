<?php
/**
 * Created by PhpStorm.
 * User: your name
 * Date: todays date
 * Time: todays time
 */
$path = storage_path() . "/json/jsondata.json";

$json = json_decode(file_get_contents($path), true);
ob_start();
session_start();
//=========== database connection variables ====================
// define('DB_SERVER', "127.0.0.1"); // database host name eg. localhost or 127.0.0.1
// define('DB_USER', "root"); // database user name eg. root
// define('DB_DATABASE', "amacotest"); //database name
// define('DB_PASSWORD', "decoresite@12345"); //database user password      decoresite@12345
// define('DB_TYPE', 'mysql'); //database drive eg. mysql, pgsql, mongodb etc


define('DB_SERVER', $json['DB_SERVER']); // database host name eg. localhost or 127.0.0.1
define('DB_USER', $json['DB_USER']); // database user name eg. root
define('DB_DATABASE',$json['DB_DATABASE']); //database name
define('DB_PASSWORD',$json['DB_PASSWORD']); //database user password      decoresite@12345
define('DB_TYPE', 'mysql'); //database drive eg. mysql, pgsql, mongodb etc


//========== site details described here ========================
define('SITE_TITLE', 'Veidya.com');
define('SITE_TAG_LINE', 'A doctor directory');

//contact ifnormation
define('SITE_CONTACT', 'your number');
//email information
define('SITE_EMAIL_INFO', 'your mail id');
//url information
define('BASE_URL', 'http://www.amacoerp.com/');

// included main class
require_once 'app/main.php';
require_once 'app/controller.php';
require_once 'app/admin.php';
//echo "sd";
//exit;
// require_once 'app/main.php';
/**
 * @param $class
 */
function __autoload($class) {
    require_once 'app/'.$class.'.php';
}