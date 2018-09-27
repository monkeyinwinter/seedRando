<?php

require 'config.php';
dol_include_once('/seedrando/class/seedrando.class.php');

if(empty($user->rights->seedrando->read)) accessforbidden();

$langs->load('abricot@abricot');
$langs->load('seedrando@seedrando');


$object = new seedrando($db);

$hookmanager->initHooks(array('seedrandolist'));

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

llxHeader('',$langs->trans('seedrandoList'),'','');

//$type = GETPOST('type');
//if (empty($user->rights->seedrando->all->read)) $type = 'mine';

// TODO ajouter les champs de son objet que l'on souhaite afficher
$sql = 'SELECT t.rowid, t.ref, t.label, t.distance, t.difficulte, t.date_creation, t.tms, \'\' AS action';

$sql.= ' FROM '.MAIN_DB_PREFIX.'seedrando t ';

$sql.= ' WHERE 1=1';
//$sql.= ' AND t.entity IN ('.getEntity('seedrando', 1).')';
//if ($type == 'mine') $sql.= ' AND t.fk_user = '.$user->id;


$formcore = new TFormCore($_SERVER['PHP_SELF'], 'form_list_seedrando', 'GET');

$nbLine = !empty($user->conf->MAIN_SIZE_LISTE_LIMIT) ? $user->conf->MAIN_SIZE_LISTE_LIMIT : $conf->global->MAIN_SIZE_LISTE_LIMIT;

$r = new Listview($db, 'seedrando');
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
		,'label' => array('search_type' => true, 'table' => array('t', 't'), 'field' => array('label')) // input text de recherche sur plusieurs champs
		,'distance'=> array('search_type' => true, 'table' => array('t', 't'), 'field' => array('distance'))
		,'difficulte' => array('search_type' => true, 'table' => array('t', 't'), 'field' => array('difficulte'))
		,'status' => array('search_type' => seedrando::$TStatus, 'to_translate' => true) // select html, la clé = le status de l'objet, 'to_translate' à true si nécessaire
	)
	,'translate' => array()
	,'hide' => array(
		'rowid'
	)
	,'list' => array(
		'title' => $langs->trans('seedrando Liste')
		,'image' => 'title_generic.png'
		,'picto_precedent' => '<'
		,'picto_suivant' => '>'
		,'noheader' => 0
		,'messageNothing' => $langs->trans('Noseedrando')
		,'picto_search' => img_picto('','search.png', '', 0)
	)
	,'title'=>array(
		'ref' => $langs->trans('Ref.')
		,'label' => $langs->trans('Label')
		,'distance' => $langs->trans('Distance en km')
		,'difficulte' => $langs->trans('Difficulte')
		,'date_creation' => $langs->trans('Date Création')
		,'tms' => $langs->trans('Date Maj')
	)
	,'eval'=>array(
		'ref' => '_getObjectNomUrl(\'@val@\')'
//		,'fk_user' => '_getUserNomUrl(@val@)' // Si on a un fk_user dans notre requête
		,'wayPoint' => '_printWayPoint("@val@")'//envoi vers methode qui retourne une string, ne pas oublier les doubles codes autour d'eval
	)
));

$parameters=array('sql'=>$sql);
$reshook=$hookmanager->executeHooks('printFieldListFooter', $parameters, $object);    // Note that $action and $object may have been modified by hook
print $hookmanager->resPrint;

$formcore->end_form();

llxFooter('');


// function _printWayPoint($list =''){//fonction qui permet de recuperer une liste à partir d'un tableau
// 	if(!empty($list))
// 	{
// 		$list = stripslashes($list);//enleve l'antislash sinon ça plante
// 		$test = unserialize($list);
// 		$test = implode(" , ", $test);//separe les differend elements par un espace après la virgule

// 		if (strlen($test)<35)//verifie la longueur de la chaine pour eviter d'avoir une liste trop longue
// 		{
// 			return $test;
// 		}
// 		elseif (strlen($test)>35)
// 		{
// 			$test = substr($test, 0, 35);//n'affiche que les caractere de 0 à 35
// 			return $test .'...';
// 		}
// 	}
// }

/**
 * TODO remove if unused
 */
function _getObjectNomUrl($ref)
{
	global $db;

	$o = new seedrando($db);
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