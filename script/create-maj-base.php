<?php
/*
 * Script créant et vérifiant que les champs requis s'ajoutent bien
 */

if(!defined('INC_FROM_DOLIBARR')) {
	define('INC_FROM_CRON_SCRIPT', true);

	require('../config.php');
} else {
	global $db;
}


// uncomment


dol_include_once('/seedrando/class/seedrando.class.php');
dol_include_once('/seedrando/class/wayPoint.class.php');
dol_include_once('/seedrando/class/relationTable.class.php');

$o=new seedrando($db);
$o->init_db_by_vars();

$o=new wayPoint($db);
$o->init_db_by_vars();

$o=new relationTable($db);
$o->init_db_by_vars();
//
