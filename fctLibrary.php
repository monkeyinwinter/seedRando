<?php


function _get_showWayPoint($object, $mode)
{
	global $form;
	$html = '';
	$object->loadRelation('TWaypoint', 'fk_wayPoint_target', 'relationTable', 'fk_seedRando_source', 'wayPoint', '$TWaypoint', 'load');
	
	$count = count($object->TWaypoint);
	if ($mode == 'view')
	{
		for ($i = 0; $i<$count ;$i++)
		{
			$html .= $object->TWaypoint[$i]->name.' - ';
		}
	}
	else// edit ou add
	{
		$TallWayPoint = array();
		$TlistSelectWayPoint = array();
		if ($count > 0)
		{
			for ($i = 0; $i<$count ; $i++)
			{
				$TlistSelectWayPoint[] = $object->TWaypoint[$i]->id;
			}
			$TallWayPoint = get_listSelectArray($TlistSelectWayPoint, 'rowid', 'name', 'wayPoint', true);
			$html = $form->multiselectarray('wayPoint', $TallWayPoint, $TlistSelectWayPoint);
		}
		else {
			$temp = get_listSelectArray($TlistSelectWayPoint, 'rowid', 'name', 'wayPoint', true);
			$html = $form->multiselectarray('wayPoint', $temp, $objectWayPoint->name);
		}
	}
	return $html;
}


function _get_listContact($object, $action)
{
	global $db;
	$object->loadRelation('TContact', 'fk_socpeople_target', 'relationRandoContact', 'fk_seedRando_source', 'contact', '$TContact', 'fetch');
	$count = count($object->TContact);
	if ($action == "create" || $action == "edit")
	{
		$result = "aucun";
	}
	else//mode=view
	{
		for ($i = 0; $i<$count ;$i++)
		{
			$html .= '<a href="http://localhost/dolibarr/htdocs/custom/seedrando/card.php?id=';
			$html .= $object->id;
			$html .= '&action=deleteContact&idContact=';
			$html .= $object->TContact[$i]->id;
			$html .= '" title="supprimer le contact de cette rando">';
			$html .= $object->TContact[$i]->firstname;
			$html .= ' ';
			$html .= $object->TContact[$i]->lastname;
			$html .= '</a><br>';
		}
	}
	return $html;
}

function get_listSelectArray ($TlistSelect, $field1, $field2 = '', $table, $ifContact = false)
{
	global $form, $action, $id, $db;
	
	$sql = 'SELECT t.rowid, t.' . $field1 . ',t.' . $field2 ;//requette pour permettre l affichage des waypoints ou des contacts
	$sql.= ' FROM '.MAIN_DB_PREFIX . $table . ' t ';
	
	$dataresult = $db->query($sql);
	$TlistSelect = array();
	while ($display = $db->fetch_object($dataresult)) {
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
		$TlistSelect = $form->selectarray('listSelectContact', $TlistSelect, $objectContact->firstname, $objectContact->lastname ); //modification pour utiliser la drop list
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
		return $form->selectarray('difficulte', $selectDifficulte, $object->difficulte); //modification pour utiliser la drop list
	}
	
	return '';
}
