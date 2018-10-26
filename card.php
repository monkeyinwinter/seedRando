<?php

require 'config.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';

dol_include_once('/seedrando/class/seedrando.class.php');
dol_include_once('/seedrando/class/wayPoint.class.php');
dol_include_once('/seedrando/class/relationTable.class.php');
dol_include_once('/seedrando/class/relationRandoContact.class.php');
dol_include_once('../contact/class/contact.class.php');
dol_include_once('/seedrando/lib/seedrando.lib.php');
dol_include_once('/seedrando/fctLibrary.php');

if(empty($user->rights->seedrando->read)) accessforbidden();


$langs->load('seedrando@seedrando');

$action = GETPOST('action');
$id = GETPOST('id', 'int');

$idContact = GETPOST('idContact');

$ref = GETPOST('ref');

$ref = GETPOST('ref');
$ref = GETPOST('ref');

$note = GETPOST('note');

$saveContact = GETPOST('saveContact');

$idContact = GETPOST('idContact');

$saveType = GETPOST('saveType');

$confirm = GETPOST('confirm');

$mode = 'view';
if (empty($user->rights->seedrando->write)) $mode = 'view'; // Force 'view' mode if can't edit object
else if ($action == 'create' || $action == 'edit') $mode = 'edit';

$object = new seedrando($db);

$objectRelationTable = new relationTable($db);

$objectContact = new contact($db);

// $objectRandoContact = new relationRandoContact($db);

if (!empty($id)) $object->load($id, '');
elseif (!empty($ref)) $object->loadBy($ref, 'ref');

//$object->loadRelation('TWaypoint', 'fk_target', 'relationTable', 'fk_seedRando_source', 'wayPoint', '$TWaypoint', 'load');

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
	switch ($action)
	{
		case 'save':
			$object->setValues($_REQUEST); // Set standard attributes
			
			if ($error > 0)
			{
				$mode = 'edit';
				break;
			}

			if($saveType == 'saveWay&Rando' || $confirm == 'yes')
			{
				$target_type_object = 'wayPoint';
			}
			else
			{
				//echo 'toto';exit;
				$target_type_object = 'contact';
			}
			
			
			$object->save(empty($object->ref), $target_type_object);
			
			header('Location: '.dol_buildpath('/seedrando/card.php', 1).'?id='.$object->id);
			exit;
			break;
			
		case 'confirm_clone':
			//$object->cloneObject();
			
			header('Location: '.dol_buildpath('/seedrando/card.php', 1).'?id='.$object->id);
			exit;
			break;
			
		case 'modif':
			if (!empty($user->rights->seedrando->write)) $object->setDraft();
			break;
			
		case 'confirm_validate':
			if (!empty($user->rights->seedrando->write))
				$object->setValid();
			
			header('Location: '.dol_buildpath('/seedrando/card.php', 1).'?id='.$object->id);
			exit;
			break;
			
		case 'confirm_delete':
			if (!empty($user->rights->seedrando->write)) $object->delete($user);
			
			header('Location: '.dol_buildpath('/seedrando/list.php', 1));
			exit;
			break;

		case 'dellink':
			$object->generic->deleteObjectLinked(null, '', null, '', GETPOST('dellinkid'));
			header('Location: '.dol_buildpath('/seedrando/card.php', 1).'?id='.$object->id);
			exit;
			break;
			
// 		case 'saveContact':
// 			$object->setValues($_REQUEST);
			
// 			if ($error > 0)
// 			{
// 				$mode = 'edit';
// 				break;
// 			}
// 			$object->saveRelation('relationRandoContact', 'fk_seedRando_source', 'fk_target', 'listSelectContact', true
// 					);
// 			header('Location: '.dol_buildpath('/seedrando/card.php', 1).'?id='.$object->id);
// 			exit;
// 			break;
			
		case 'deleteContact':
			$object->setValues($_REQUEST);
			
			if ($error > 0)
			{
				$mode = 'edit';
				break;
			}
			$object->deleteRelation('relationRandoContact', 'fk_seedRando_source', 'fk_socpeople_target', $idContact
					);
			header('Location: '.dol_buildpath('/seedrando/card.php', 1).'?id='.$object->id);
			exit;
			break;
			
		case 'saveNote':
			$object->setValues($_REQUEST);
			
			if ($error > 0)
			{
				$mode = 'edit';
				break;
			}
			$object->saveNote($idContact, $note);
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

$selectDifficulte = array('selectionner'=>'selectionner', 'facile'=>'Facile', 'moyen'=>'Moyen', 'difficile'=>'Difficile');

$objectWayPoint = new wayPoint($db);

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
						,'showWayPoint' => $objectRelationTable::_get_showWayPoint($object, $mode)//modification pour utiliser la drop list difficulte
						,'showDifficulte' => _get_showDifficulte($object, $selectDifficulte,  $mode)//modification pour utiliser la drop list difficulte
						,'showContact' => get_listSelectArray($TContact, 'lastname', 'firstname', 'socpeople', true)
						,'showListContact' => $objectRelationTable::_get_listContact($object, $action)
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

function get_listSelectArray ($TlistSelect, $field1, $field2 = '', $table, $ifContact = false)
{
	global $form, $action, $id, $db;
	
	$sql = 'SELECT t.rowid, t.' . $field1 . ',t.' . $field2 ;//requette pour permettre l affichage des waypoints ou des contacts
	$sql.= ' FROM '.MAIN_DB_PREFIX . $table . ' t ';
	
	$dataresult = $db->query($sql);
	$TlistSelect = array();
	while ($display = $db->fetch_object($dataresult))
	{
		if($table == 'socpeople')
		{
			$TlistSelect[$display->rowid] =  $display->firstname . " ". $display->lastname;
		}
		else
		{
			$TlistSelect[$display->rowid] =  $display->name;
		}
	}
	if ($table == 'socpeople')
	{
		$TlistSelect = $form->selectarray('listSelectContact', $TlistSelect, $objectContact->firstname, $objectContact->lastname);
	}
	return $TlistSelect;
}


function _get_showDifficulte($object, $selectDifficulte, $mode = 'view')
{
	global $form;
	if($mode == 'view')
	{
		return $object->difficulte;
	}
	elseif ($mode == 'edit')
	{
		return $form->selectarray('difficulte', $selectDifficulte, $object->difficulte);
	}
	return '';
}

if ($mode == 'edit') echo $formcore->end_form();

// if ($mode == 'view' && $object->id) $somethingshown = $form->showLinkedObjectBlock($object);

llxFooter();