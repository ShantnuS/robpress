<?php
//YOU MUST NOT CHANGE THIS FILE

//Load framework
$f3=require('lib/base.php');
$f3->config('config/config.cfg');
$f3->set('AUTOLOAD','controllers/; models/; helpers/; utility/;');

//Load configuration
$f3->config('config/db.cfg');

//Load global functions
include_once("bootstrap.php");
include_once("functions.php");
?>
