<?php

require 'config.php';
dol_include_once('/seedrando/class/seedrando.class.php');
dol_include_once('/seedrando/class/wayPoint.class.php');

if(empty($user->rights->seedrando->read)) accessforbidden();

$langs->load('abricot@abricot');
$langs->load('seedrando@seedrando');
$langs->load('wayPoint@wayPoint');

$object = new wayPoint($db);

$hookmanager->initHooks(array('wayPointlist'));

/*
 * Actions
 */

$parameters=array();
$reshook=$hookmanager->executeHooks('doActions',$parameters,$object);    // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

if (empty($reshook))
{
	// do action from GETPOST ... 
}


/*
 * View
 */

llxHeader('',$langs->trans('wayPointList'),'','');

//$type = GETPOST('type');
//if (empty($user->rights->seedrando->all->read)) $type = 'mine';

// TODO ajouter les champs de son objet que l'on souhaite afficher
$sql = 'SELECT t.rowid, t.name, t.ref, t.lattitude, t.longitude, t.date_creation, t.tms, \'\' AS action';

$sql.= ' FROM '.MAIN_DB_PREFIX.'wayPoint t ';

$sql.= ' WHERE 1=1';
//$sql.= ' AND t.entity IN ('.getEntity('seedrando', 1).')';
//if ($type == 'mine') $sql.= ' AND t.fk_user = '.$user->id;


$formcore = new TFormCore($_SERVER['PHP_SELF'], 'form_list_wayPoint', 'GET');

$nbLine = !empty($user->conf->MAIN_SIZE_LISTE_LIMIT) ? $user->conf->MAIN_SIZE_LISTE_LIMIT : $conf->global->MAIN_SIZE_LISTE_LIMIT;

$r = new Listview($db, 'wayPoint');
echo $r->render($sql, array(
	'view_type' => 'list' // default = [list], [raw], [chart]
	,'limit'=>array(
		'nbLine' => $nbLine
	)
	,'subQuery' => array()
	,'link' => array()
	,'type' => array(
		'date_creation' => 'date' // [datetime], [hour], [money], [number], [integer]
		,'tms' => 'date'
	)
	,'search' => array(
		'date_creation' => array('search_type' => 'calendars', 'allow_is_null' => true)
		,'tms' => array('search_type' => 'calendars', 'allow_is_null' => false)
		,'ref' => array('search_type' => true, 'table' => 't', 'field' => 'ref')
		,'name' => array('search_type' => true, 'table' => array('t', 't'), 'field' => array('name'))
		,'lattitude' => array('search_type' => true, 'table' => array('t', 't'), 'field' => array('lattitude')) // input text de recherche sur plusieurs champs
		,'longitude'=> array('search_type' => true, 'table' => array('t', 't'), 'field' => array('longitude'))
		,'status' => array('search_type' => seedrando::$TStatus, 'to_translate' => true) // select html, la clé = le status de l'objet, 'to_translate' à true si nécessaire
	)
	,'translate' => array()
	,'hide' => array(
		'rowid'
	)
	,'list' => array(
		'title' => $langs->trans('wayPoint Liste')
		,'image' => 'title_generic.png'
		,'picto_precedent' => '<'
		,'picto_suivant' => '>'
		,'noheader' => 0
		,'messageNothing' => $langs->trans('NowayPoint')
		,'picto_search' => img_picto('','search.png', '', 0)
	)
	,'title'=>array(
		'ref' => $langs->trans('Ref.')
		,'name' => $langs->trans('name')
		,'lattitude' => $langs->trans('lattitude')
		,'longitude' => $langs->trans('longitude')
		,'date_creation' => $langs->trans('Date Création')
		,'tms' => $langs->trans('Date Maj')
	)
	,'eval'=>array(
		'ref' => '_getObjectNomUrl(\'@val@\')'
//		,'fk_user' => '_getUserNomUrl(@val@)' // Si on a un fk_user dans notre requête
	)
));


$parameters=array('sql'=>$sql);
$reshook=$hookmanager->executeHooks('printFieldListFooter', $parameters, $object);    // Note that $action and $object may have been modified by hook
print $hookmanager->resPrint;

$formcore->end_form();

llxFooter('');

/**
 * TODO remove if unused
 */
function _getObjectNomUrl($ref)
{
	global $db;

	$o = new wayPoint($db);
	$res = $o->load('', $ref);
	if ($res > 0)
	{
		return $o->getNomUrl(1);
	}

	return '';
}

/**
 * TODO remove if unused
 */
function _getUserNomUrl($fk_user)
{
	global $db;

	$u = new User($db);
	if ($u->fetch($fk_user) > 0)
	{
		return $u->getNomUrl(1);
	}

	return '';
}