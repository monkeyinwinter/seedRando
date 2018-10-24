<?php

// function _get_showWayPoint($object, $mode)
// {
// 	global $form;
// 	$html = '';
// 	$object->loadRelation('TWaypoint', 'fk_wayPoint_target', 'relationTable', 'fk_seedRando_source', 'wayPoint', '$TWaypoint', 'load');
	
// 	$count = count($object->TWaypoint);
// 	if ($mode == 'view')
// 	{
// 		for ($i = 0; $i<$count ;$i++)
// 		{
// 			$html .= $object->TWaypoint[$i]->name.' - ';
// 		}
// 	}
// 	else// edit ou add
// 	{
// 		$TallWayPoint = array();
// 		$TlistSelectWayPoint = array();
// 		if ($count > 0)
// 		{
// 			for ($i = 0; $i<$count ; $i++)
// 			{
// 				$TlistSelectWayPoint[] = $object->TWaypoint[$i]->id;
// 			}
// 			$TallWayPoint = get_listSelectArray($TlistSelectWayPoint, 'rowid', 'name', 'wayPoint', true);
// 			$html = $form->multiselectarray('wayPoint', $TallWayPoint, $TlistSelectWayPoint);
// 		}
// 		else {
// 			$temp = get_listSelectArray($TlistSelectWayPoint, 'rowid', 'name', 'wayPoint', true);
// 			$html = $form->multiselectarray('wayPoint', $temp, $objectWayPoint->name);
// 		}
// 	}
// 	return $html;
// }

// function _get_listContact($object, $action)
// {
// 	global $db;
// 	$object->loadRelation('TContact', 'fk_socpeople_target', 'relationRandoContact', 'fk_seedRando_source', 'contact', '$TContact', 'fetch');
// 	$count = count($object->TContact);
// 	if ($action == "create" || $action == "edit")
// 	{
// 		$result = "aucun";
// 	}
// 	else//mode=view
// 	{
// 		for ($i = 0; $i<$count ;$i++)
// 		{
// 			$relObject = new relationRandoContact($object->db);
			
// 			$note = $relObject::get_noteRando($object, $object->TContact[$i]->id);
			
// 			$html .= '<tr><td>' . $object->TContact[$i]->firstname;
// 			$html .= ' ';
// 			$html .= $object->TContact[$i]->lastname;
// 			$html .= '</td>';
// 			$html .= '<td><form action="http://localhost/dolibarr/htdocs/custom/seedrando/card.php?id=';
// 			$html .= $object->id;
// 			$html .= '&action=saveNote&idContact=';
// 			$html .= $object->TContact[$i]->id;
// 			$html .= '" method="post">';
// 			$html .= '<input type="hidden" name="action" value="saveNote">
// 						<select id="note" name="note">
// 							<option value="1">1</option>
// 							<option value="2">2</option>
// 							<option value="3">3</option>
// 							<option value="4">4</option>
// 							<option value="5">5</option>
// 						</select>';
// 			$html .= '<input class="button" type="submit" value="save"></form></td>';
// 			$html .= '<td>';
// 			$html .=  $note . '</td>';
// 			$html .= '<td><a href="http://localhost/dolibarr/htdocs/custom/seedrando/card.php?id=';
// 			$html .= $object->id;
// 			$html .= '&action=deleteContact&idContact=';
// 			$html .= $object->TContact[$i]->id;
// 			$html .= '" >';
// 			$html .= '<img src="/dolibarr/htdocs/theme/eldy/img/delete.png" title="supprimer le contact de cette rando">';			
// 			$html .= '</a></td></tr>';
// 		}
// 	}
// 	return $html;
// }






