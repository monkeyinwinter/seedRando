<?php

require 'config.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';

dol_include_once('/seedrando/class/seedrando.class.php');
dol_include_once('/seedrando/class/wayPoint.class.php');
dol_include_once('/seedrando/lib/seedrando.lib.php');

if(empty($user->rights->seedrando->read)) accessforbidden();

$langs->load('seedrando@seedrando');

$action = GETPOST('action');
$id = GETPOST('id', 'int');
$ref = GETPOST('ref');

$mode = 'view';
if (empty($user->rights->seedrando->write)) $mode = 'view'; // Force 'view' mode if can't edit object
else if ($action == 'create' || $action == 'edit') $mode = 'edit';

$object = new seedrando($db);

if (!empty($id)) $object->load($id, '');
elseif (!empty($ref)) $object->loadBy($ref, 'ref');

$hookmanager->initHooks(array('seedrandocard', 'globalcard'));

/*
 * Actions
 */

$parameters = array('id' => $id, 'ref' => $ref, 'mode' => $mode);
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

// Si vide alors le comportement n'est pas remplacé
if (empty($reshook))
{
	$error = 0;
	switch ($action) {
		case 'save':
			$object->setValues($_REQUEST); // Set standard attributes
			
//			$object->date_other = dol_mktime(GETPOST('starthour'), GETPOST('startmin'), 0, GETPOST('startmonth'), GETPOST('startday'), GETPOST('startyear'));

			// Check parameters
//			if (empty($object->date_other))
//			{
//				$error++;
//				setEventMessages($langs->trans('warning_date_must_be_fill'), array(), 'warnings');
//			}
			
			// ... 
			
			if ($error > 0)
			{
				$mode = 'edit';
				break;
			}
			
			$object->save(empty($object->ref));
			
			header('Location: '.dol_buildpath('/seedrando/card.php', 1).'?id='.$object->id);
			exit;
			
			break;
		case 'confirm_clone':
			$object->cloneObject();
			
			header('Location: '.dol_buildpath('/seedrando/card.php', 1).'?id='.$object->id);
			exit;
			break;
		case 'modif':
			if (!empty($user->rights->seedrando->write)) $object->setDraft();
				
			break;
		case 'confirm_validate':
			if (!empty($user->rights->seedrando->write)) $object->setValid();
			
			header('Location: '.dol_buildpath('/seedrando/card.php', 1).'?id='.$object->id);
			exit;
			break;
		case 'confirm_delete':
			if (!empty($user->rights->seedrando->write)) $object->delete();
			
			header('Location: '.dol_buildpath('/seedrando/list.php', 1));
			exit;
			break;
		// link from llx_element_element
		case 'dellink':
			$object->generic->deleteObjectLinked(null, '', null, '', GETPOST('dellinkid'));
			header('Location: '.dol_buildpath('/seedrando/card.php', 1).'?id='.$object->id);
			exit;
			break;
	}
}


/**
 * View
 */

$title=$langs->trans("seedrando");
llxHeader('',$title);

if ($action == 'create' && $mode == 'edit')
{
	load_fiche_titre($langs->trans("Newseedrando"));
	dol_fiche_head();
}
else
{
	$head = seedrando_prepare_head($object);
	$picto = 'generic';
	dol_fiche_head($head, 'card', $langs->trans("seedrando"), 0, $picto);
}

$selectDifficulte = array('selectionner'=>'selectionner','facile'=>'Facile','moyen'=>'Moyen','difficile'=>'Difficile');//drop list select pour la difficulte



/////////////////////////////////////////////////debut de la liste multiselect
$sql = 'SELECT t.rowid, t.name';//requette pour permettre l'affichage des waypoints dans la creation de la rando
$sql.= ' FROM '.MAIN_DB_PREFIX.'wayPoint t ';
//$sql.= ' WHERE 1=1';
$dataresult = $db->query($sql);

$TlistSelectWayPoint = array();
// $TlistSelectWayPoint[] = 'selectionner';

// $wayPoint = new wayPoint();

while ($display = $db->fetch_object($dataresult)) {
	$TlistSelectWayPoint[$display->name] =  $display->name;
}//fin de la recherche des waypoint pour la list select
// var_dump($TlistSelectWayPoint);


/////////////////////////////////////fin de la recherche et de l'affichage de la liste multiselect


$formcore = new TFormCore;
$formcore->Set_typeaff($mode);

$form = new Form($db);

$formconfirm = getFormConfirmseedrando($PDOdb, $form, $object, $action);
if (!empty($formconfirm)) echo $formconfirm;

$TBS=new TTemplateTBS();
$TBS->TBS->protect=false;
$TBS->TBS->noerr=true;

if ($mode == 'edit') echo $formcore->begin_form($_SERVER['PHP_SELF'], 'form_seedrando');

$linkback = '<a href="'.dol_buildpath('/seedrando/list.php', 1).'">' . $langs->trans("BackToList") . '</a>';
print $TBS->render('tpl/card.tpl.php'
	,array() // Block
	,array(
		'object'=>$object
		,'view' => array(
			'mode' => $mode
			,'action' => 'save'
			,'urlcard' => dol_buildpath('/seedrando/card.php', 1)
			,'urllist' => dol_buildpath('/seedrando/list.php', 1)
			,'showRef' => ($action == 'create') ? $langs->trans('Draft') : $form->showrefnav($object, 'ref', $linkback, 1, 'ref', 'ref', '')
			,'showLabel' => $formcore->texte('', 'label', $object->label, 80, 255)
			,'showDistance' => $formcore->texte('', 'distance', $object->distance, 80, 255)
			,'showDifficulte' => $form->selectarray('difficulte', $selectDifficulte, $object->difficulte)//modification pour utiliser la drop list difficulte
			,'showWayPoint' => $form->multiselectarray('wayPoint', $TlistSelectWayPoint, $object->wayPoint)
//			,'showNote' => $formcore->zonetexte('', 'note', $object->note, 80, 8)
			,'showStatus' => $object->getLibStatut(1)
		)
		,'langs' => $langs
		,'user' => $user
		,'conf' => $conf
		,'seedrando' => array(
			'STATUS_DRAFT' => seedrando::STATUS_DRAFT
			,'STATUS_VALIDATED' => seedrando::STATUS_VALIDATED
			,'STATUS_REFUSED' => seedrando::STATUS_REFUSED
			,'STATUS_ACCEPTED' => seedrando::STATUS_ACCEPTED
		)
	)
);




if ($mode == 'edit') echo $formcore->end_form();

if ($mode == 'view' && $object->id) $somethingshown = $form->showLinkedObjectBlock($object);


llxFooter();