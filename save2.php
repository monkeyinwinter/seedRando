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

if(empty($user->rights->seedrando->read)) accessforbidden();


$langs->load('seedrando@seedrando');

// $TreturnRelationTable = GETPOST('wayPoint');

$action = GETPOST('action');
$id = GETPOST('id', 'int');
$ref = GETPOST('ref');

$ref = GETPOST('ref');
$ref = GETPOST('ref');

$mode = 'view';
if (empty($user->rights->seedrando->write)) $mode = 'view'; // Force 'view' mode if can't edit object
else if ($action == 'create' || $action == 'edit') $mode = 'edit';

$object = new seedrando($db);

$objectRelationTable = new relationTable($db);

$SelectContact = new contact($db);

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
			//$_REQUEST['wayPoint'] = $TreturnRelationTable;
			
			
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



/////////////////////////////////////fin de la recherche pour l'affichage de la liste multiselect
$selectDifficulte = array('selectionner'=>'selectionner','facile'=>'Facile','moyen'=>'Moyen','difficile'=>'Difficile');//drop list select pour la difficulte




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
						,'showWayPoint' => _get_showWayPoint($objectWayPoint, $TlistSelectWayPoint,  $mode, $id, $db)//modification pour utiliser la drop list difficulte
						,'showDifficulte' => _get_showDifficulte($object, $selectDifficulte,  $mode)//modification pour utiliser la drop list difficulte
						,'showStatus' => $object->getLibStatut(1)
						,'showListContact' => _get_showContact($objectContact, $selectContact, $db)
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

function _get_showContact ($objectContact, $selectContact, $db)
	{
		global $form;
		global $action;
		global $id;
		
		//recuperer laliste complete des wayPoints
		$sql = 'SELECT t.rowid, t.lastname';//requette pour permettre l'affichage des waypoints dans la creation de la rando
		$sql.= ' FROM '.MAIN_DB_PREFIX.'socpeople t ';
		
		$dataresult = $db->query($sql);
		
		$SelectContact = array();
		
		while ($display = $db->fetch_object($dataresult)) {
			$TlistSelectContact[$display->rowid] =  $display->lastname;
		}
		
		$test = $form->selectarray('wayPoint', $TlistSelectContact, $objectWayPoint->name); //modification pour utiliser la drop list difficulte
		return $test;
	}


	function _get_showWayPoint($objectWayPoint, $TlistSelectWayPoint, $mode = 'view', $id, $db)
	{
		global $form;
		
		$test = 'tottoo';
		
		if(!empty($id))
		{
			if($mode == 'view')
			{
				$test = get_stringOut($id, $db);
				return $test;
			}
			elseif ($mode == 'edit')
			{
				$test = get_multiselectarray($TlistSelectWayPoint, $objectWayPoint, $db);
				return $test;
				
			}
		}
		return;
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
			return $form->selectarray('difficulte', $selectDifficulte, $object->difficulte); //modification pour utiliser la drop list difficulte
		}
		
		return '';
	}
	
	function get_multiselectarray ($TlistSelectWayPoint, $objectWayPoint, $db)
	{
		global $form;
		global $action;
		global $id;
		
		//recuperer laliste complete des wayPoints
		$sql = 'SELECT t.rowid, t.name';//requette pour permettre l'affichage des waypoints dans la creation de la rando
		$sql.= ' FROM '.MAIN_DB_PREFIX.'wayPoint t ';
		
		$dataresult = $db->query($sql);
		
		$TlistSelectWayPoint = array();
		
		while ($display = $db->fetch_object($dataresult)) {
			$TlistSelectWayPoint[$display->rowid] =  $display->name;
		}
		
		$test = $form->multiselectarray('wayPoint', $TlistSelectWayPoint, $objectWayPoint->name); //modification pour utiliser la drop list difficulte
		return $test;
	}
	
	function get_stringOut($id, $db)
	{
		$sql = 'SELECT t.fk_wayPoint';//requette pour permettre l'affichage des waypoints dans la creation de la rando
		$sql .= ' FROM '.MAIN_DB_PREFIX.'relationTable t ';
		$sql .= ' WHERE fk_seedRando =  ' .$id;
		
		$dataresult = $db->query($sql);
		
		$TlistOfMyWayPoint = array();

		while ($display = $db->fetch_object($dataresult)) {
			
			$TlistOfMyWayPoint[] = $display->fk_wayPoint;
		}
		//j'ai recuperer les fk_wayPoint de la table relationTable

		//j'itere sur mon tableau $listOfMyWayPoint pour recuperer mes objets wayPoint
		$stringOut = array();
		foreach ($TlistOfMyWayPoint AS $value)
		{
			$sql = 'SELECT t.name';//requette pour permettre l'affichage des waypoints dans la creation de la rando
			$sql.= ' FROM '.MAIN_DB_PREFIX.'wayPoint t WHERE rowid = ' .$value;
						
			$dataresult = $db->query($sql);
						
			$display = $db->fetch_object($dataresult);
							
			$stringOut[] = $display->name;
		}
		return implode(", ", $stringOut);
	}
	

if ($mode == 'edit') echo $formcore->end_form();

// if ($mode == 'view' && $object->id) $somethingshown = $form->showLinkedObjectBlock($object);



llxFooter();








if(!empty($this->wayPoint))// Sauvegarde de tous les waypoints si il y en @author thibault
{
	foreach($this->wayPoint as $value)
	{
		//si donnée present dans this->TWaypoint et present dans this->wayPoint alors save sinon delete
		for ($t = 0 ; $t < $count ; $t++)
		{
			if(!in_array($this->TWaypoint[$t]->id, $this->wayPoint))
			{
				$id = $this->id;
				$idToDelete = $this->TWaypoint[$t]->id;
				
				$this->deleteWayPoint($id, $idToDelete, '');
				// 						//echo 'on supprime';//on supprime la valeur dans la table relationnel
				// 						$sqlDelete = 'DELETE FROM ' .MAIN_DB_PREFIX. 'relationTable';
				// 						$sqlDelete .= ' WHERE fk_seedRando = '.$this->id;
				// 						$sqlDelete .= ' AND fk_wayPoint = '.$this->TWaypoint[$t]->id;
				// 						$this->db->query($sqlDelete);
			}
		}
		
		$In = new relationTable($this->db);
		$In -> fk_seedRando = $this->id;
		$In -> fk_wayPoint = $value;
		
		$sql = 'SELECT fk_wayPoint FROM ' .MAIN_DB_PREFIX. 'relationTable ';
		$sql .= 'WHERE fk_wayPoint = ' . $value;
		$sql .= ' and fk_seedRando = ' . $this->id;
		
		$test = array();
		$test[] = $this->db->query($sql);
		
		if ($test[0]->num_rows > 0)
		{
			//echo 'present dans la liste !!';
		}
		else
		{
			$In -> create($user);//echo 'absent dans la liste';
		}
	}
}
else
{
	//c est vide il faut vider la table de ces relations avec la rando
	$id = $this->id;
	$all = 'all';
	
	$this->deleteWayPoint($id, '' , $all);
}
return $res;
}

public function deleteWayPoint($id, $idToDelete, $all = '')
{
	$sqlDelete = 'DELETE FROM ' .MAIN_DB_PREFIX. 'relationTable';
	$sqlDelete .= ' WHERE fk_seedRando = '.$id;
	
	if ($all != 'all' )
	{
		$sqlDelete .= ' AND fk_wayPoint = '.$idToDelete;
	}
	$this->db->query($sqlDelete);
}


