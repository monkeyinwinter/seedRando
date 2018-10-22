<?php

/////////////////////////////////////////////////debut de la liste multiselect
$sql = 'SELECT t.rowid, t.name';//requette pour permettre l'affichage des waypoints dans la creation de la rando
$sql.= ' FROM '.MAIN_DB_PREFIX.'wayPoint t ';
//$sql.= ' WHERE 1=1';
$dataresult = $db->query($sql);


$TlistSelectWayPoint = array();
$TlistSelectWayPoint[] = 'selectionner';

while ($display = $db->fetch_object($dataresult)) {
	$TlistSelectWayPoint[$display->rowid] =  $display->name;
}//fin de la recherche des waypoint pour la list select

//var_dump($TlistSelectWayPoint);

if (!empty($id))
{
	?>
	<select name="test[]" multiple size=<?php echo count($TlistSelectWayPoint)?>>
<?php 
	foreach($TlistSelectWayPoint as $value)
	{
?>
		<option value="<?php echo $value ?>"><?php echo $value ?></option><!--  affichage de la liste des noms des waypoints en multiselect -->
		
<?php 	
	}
?>
	</select>
<?php
}
/////////////////////////////////////fin de la recherche et de l'affichage de la liste multiselect




/////////////////////////////////////////////////debut de la liste multiselect
$sql = 'SELECT t.rowid, t.name';//requette pour permettre l'affichage des waypoints dans la creation de la rando
$sql.= ' FROM '.MAIN_DB_PREFIX.'wayPoint t ';
//$sql.= ' WHERE 1=1';
$dataresult = $db->query($sql);


$TlistSelectWayPoint = array();
$TlistSelectWayPoint[] = 'selectionner';

while ($display = $db->fetch_object($dataresult)) {
	$TlistSelectWayPoint[$display->rowid] =  $display->name;
}//fin de la recherche des waypoint pour la list select


function get_wayPoint($TlistSelectWayPoint)
{
	
	$string = '';
	
	foreach($TlistSelectWayPoint as $value)
	{
		$string .='<option value="'.$value.'">'.$value.'</option>';
	}
	return $string;
}



if (!empty($id))
{
	
	?>

<form action="http://localhost/dolibarr/htdocs/custom/seedrando/card.php?id=1" method="post">


	<select multiple name="test[]" size=<?php echo count($TlistSelectWayPoint)?>>
	<?php
	
		echo get_wayPoint($TlistSelectWayPoint);
	
	?>
	</select>
<button type="submit">enregistrer</button>
</form>
	
<?php

	if (isset($_POST['test']))
	{
		
		$tableauWayPoint = array();
		
		foreach ($_POST['test'] as $val)
		{
			
	 		echo $val .'<br>';
			
			$tableauWayPoint[] = $val;
			
		}
		
		print_r($tableauWayPoint);
	}

}
/////////////////////////////////////fin de la recherche et de l'affichage de la liste multiselect





$sql = 'SELECT t.rowid, t.name';//requette pour permettre l'affichage des waypoints dans la creation de la rando
$sql.= ' FROM '.MAIN_DB_PREFIX.'wayPoint t ';
//$sql.= ' WHERE 1=1';$form->selectarray
$dataresult = $db->query($sql);

$TlistSelectWayPoint = array();
// $TlistSelectWayPoint[] = 'selectionner';

// $wayPoint = new wayPoint();

// $out = new wayPoint($db);
// var_dump($out);

while ($display = $db->fetch_object($dataresult)) {

	$TlistSelectWayPoint[$display->name] =  $di				
	$test = get_multiselectarray($TlistSelectWayPoint, $objectWayPoint, $db);
	display->name;
}//fin de la recherche des waypoint pour la list select
// var_dump($TlistSelectWayPoint);








,'showDifficulte' => $form->selectarray('difficulte', $selectDifficulte, $object->difficulte)



($id != '')? $object->WayPoint : 








public function save($addprov=false)
{
	global $user;
	if (!$this->id) $this->fk_user_author = $user->id;
	$res = $this->id>0 ? $this->updateCommon($user) : $this->createCommon($user);
	if ($addprov || !empty($this->is_clone))
	{
		$this->ref = '(PROV'.$this->id.')';
		if (!empty($this->is_clone)) $this->status = self::STATUS_DRAFT;
		$wc = $this->withChild;
		$this->withChild = false;
		$res = $this->id>0 ? $this->updateCommon($user) : $this->createCommon($user);
		$this->withChild = $wc;
	}
	
	$this->UpdateOrSaveWay();
	
	if(GETPOST('action') == 'save')
	{
		if(empty($this->wayPoint))
		{
			$this->deleteWay();
		}
	}
	return $res;
}

public function deleteWay()
{
	$sqlDelete2 = 'DELETE FROM ' .MAIN_DB_PREFIX. 'relationTable';
	$sqlDelete2 .= ' WHERE fk_seedRando = '.$this->id;
	$this->db->query($sqlDelete2);
}

public function UpdateOrSaveWay()
{
	global $user;
	$this->loadRelation('TWaypoint', 'fk_wayPoint', 'relationTable', 'fk_seedRando', 'wayPoint', '$TWaypoint', 'load');
	
	$count = count($this->TWaypoint);
	
	if(!empty($this->wayPoint))// Sauvegarde de tous les waypoints si il y en @author thibault
	{
		foreach($this->wayPoint as $value)
		{
			for ($t = 0 ; $t < $count ; $t++)//si donnée present dans this->TWaypoint et present dans this->wayPoint alors save sinon delete
			{
				if(!in_array($this->TWaypoint[$t]->id, $this->wayPoint))
				{
					//on supprime la valeur dans la table relationnel
					$sqlDelete = 'DELETE FROM ' .MAIN_DB_PREFIX. 'relationTable';
					$sqlDelete .= ' WHERE fk_seedRando = '.$this->id;
					$sqlDelete .= ' AND fk_wayPoint = '.$this->TWaypoint[$t]->id;
					$this->db->query($sqlDelete);
				}
			}
			
			$In = new relationTable($this->db);
			$In -> fk_seedRando = $this->id;
			$In -> fk_wayPoint = $value;
			
			$sql = 'SELECT fk_wayPoint FROM ' .MAIN_DB_PREFIX. 'relationTable ';
			$sql .= 'WHERE fk_wayPoint = ' . $value;
			$sql .= ' and fk_seedRando = ' . $this->id;
			
			$res = $this->db->query($sql);
			
			if ($this->db->num_rows($res) == 0)
			{
				$In -> create($user);//echo 'absent dans la liste';
			}
		}
	}
}

public function saveContact($addprov=false)
{
	//remplacer la requette par interogation du $this->Tcontact
	$sql = 'SELECT fk_socpeople_target FROM ' .MAIN_DB_PREFIX. 'relationRandoContact ';
	$sql .= 'WHERE fk_socpeople_target = ' . $this->listSelectContact;
	$sql .= ' and fk_seedRando_source = ' . $this->id;
	
	$res = $this->db->query($sql);
	
	if ($this->db->num_rows($res) == 0)
	{
		$sql = 'INSERT INTO ' .MAIN_DB_PREFIX. 'relationRandoContact (fk_seedRando_source, fk_socpeople_target)';
		$sql .= 'VALUES ('.$this->id.','.$this->listSelectContact.')';
		$this->db->query($sql);
	}
}

public function loadRelation($ThisArray, $fk_objectTarget, $tableSelect, $fk_objectSource, $newObject, $Tobject, $fctToCall)
{//donner des parametres generique pour contact et waypoint
	$this->$ThisArray = array();
	$sql = 'SELECT ' .$fk_objectTarget . ', rowid FROM ';
	$sql .= MAIN_DB_PREFIX . $tableSelect;
	$sql .= ' WHERE ' .$fk_objectSource. ' = '.$this->id;
	
	$resql = $this->db->query($sql);
	if ($resql)
	{
		while($return = $this->db->fetch_object($resql)){
			$relObject = new $newObject($this->db);
			$relObject->$fctToCall($return->$fk_objectTarget, '');
			$this->$ThisArray[] = $relObject;
		}
	}
	
	return $Tobject;
}

public function loadBy($value, $field, $annexe = false)
{
	$res = parent::loadBy($value, $field, $annexe);
	return $res;
}

public function load($id, $ref, $loadChild = true)
{
	global $db;
	$res = parent::fetchCommon($id, $ref);
	if ($loadChild)
	{
		$this->fetchObjectLinked();
	}
	return $res;
}

// 	public function loadContacts()
// 	{
// 		$TtempContact = array();
// 		$sql = 'SELECT fk_socpeople_target FROM '.MAIN_DB_PREFIX.'relationRandoContact WHERE fk_seedRando_source = '.$this->id;
// 		$resql = $this->db->query($sql);

// 		if ($resql)
	// 		{
	// 			while($return = $this->db->fetch_object($resql)){
	// 				$contact = new contact($this->db);
	// 				$contact->fetch($return->fk_socpeople_target, '');
	// 				$this->TContact[] = $contact;
	// 			}
	// 		}
	// 		return $TContact;
	// 	}
	
	// 	public function loadByContact($value, $field, $annexe = false)
	// 	{
	// 		$res = parent::loadBy($value, $field, $annexe);
	// 		$this->loadRelation('TContact', 'fk_socpeople_target', 'relationRandoContact', 'fk_seedRando_source', 'contact', '$TContact', 'fetch');
	// 		return $res;
	// 	}
	
	public function loadContact($id, $ref, $loadChild = true)
	{
		global $db;
		$res = parent::fetchCommon($id, $ref);
		if ($loadChild)
		{
			$sql = 'SELECT t.firstname t.lastname';//requette pour permettre l'affichage des contacts
			$sql.= ' FROM '.MAIN_DB_PREFIX.'socpeople t WHERE rowid = ' .$res;
			$dataresult = $db->query($sql);
			$display = $db->fetch_object($dataresult);
		}
		return $display;
	}
	
	
	
	
	public function delete(User &$user)
	{
		$this->generic->deleteObjectLinked();
		
		parent::deleteCommon($user);
	}
	
	public function setDraft()
	{
		if ($this->status == self::STATUS_VALIDATED)
		{
			$this->status = self::STATUS_DRAFT;
			$this->withChild = false;
			
			return self::save();
		}
		return 0;
	}
	
	public function setValid()
	{
		$this->ref = $this->getNumero();
		$this->status = self::STATUS_VALIDATED;
		return self::save();
	}
	
	public function getNumero()
	{
		if (preg_match('/^[\(]?PROV/i', $this->ref) || empty($this->ref))
		{
			return $this->getNextNumero();
		}
		return $this->ref;
	}
	
	private function getNextNumero()
	{
		global $db,$conf;
		
		require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
		
		$mask = !empty($conf->global->MYMODULE_REF_MASK) ? $conf->global->MYMODULE_REF_MASK : 'SR{yy}{mm}-{0000}';
		$numero = get_next_value($db, $mask, 'seedrando', 'ref');
		return $numero;
	}
	
	public function setRefused()
	{
		$this->status = self::STATUS_REFUSED;
		$this->withChild = false;
		return self::save();
	}
	
	public function setAccepted()
	{
		$this->status = self::STATUS_ACCEPTED;
		$this->withChild = false;
		return self::save();
	}
	
	public function getNomUrl($withpicto=0, $get_params='')
	{
		global $langs;
		$result='';
		$label = '<u>' . $langs->trans("Showseedrando") . '</u>';
		if (! empty($this->ref)) $label.= '<br><b>'.$langs->trans('Ref').':</b> '.$this->ref;
		
		$linkclose = '" title="'.dol_escape_htmltag($label, 1).'" class="classfortooltip">';
		$link = '<a href="'.dol_buildpath('/seedrando/card.php', 1).'?id='.$this->id. $get_params .$linkclose;
		
		$linkend='</a>';
		
		$picto='generic';
		
		if ($withpicto) $result.=($link.img_object($label, $picto, 'class="classfortooltip"').$linkend);
		if ($withpicto && $withpicto != 2) $result.=' ';
		
		$result.=$link.$this->ref.$linkend;
		return $result;
	}
	
	public static function getStaticNomUrl($id, $withpicto=0)
	{
		global $db;
		
		$object = new seedrando($db);
		$object->load($id, '',false);
		return $object->getNomUrl($withpicto);
	}
	
	public function getLibStatut($mode=0)
	{
		return self::LibStatut($this->status, $mode);
	}
	
	public static function LibStatut($status, $mode)
	{
		global $langs;
		$langs->load('seedrando@seedrando');
		
		if ($status==self::STATUS_DRAFT) { $statustrans='statut0'; $keytrans='seedrandoStatusDraft'; $shortkeytrans='Draft'; }
		if ($status==self::STATUS_VALIDATED) { $statustrans='statut1'; $keytrans='seedrandoStatusValidated'; $shortkeytrans='Validate'; }
		if ($status==self::STATUS_REFUSED) { $statustrans='statut5'; $keytrans='seedrandoStatusRefused'; $shortkeytrans='Refused'; }
		if ($status==self::STATUS_ACCEPTED) { $statustrans='statut6'; $keytrans='seedrandoStatusAccepted'; $shortkeytrans='Accepted'; }
		
		if ($mode == 0) return img_picto($langs->trans($keytrans), $statustrans);
		elseif ($mode == 1) return img_picto($langs->trans($keytrans), $statustrans).' '.$langs->trans($keytrans);
		elseif ($mode == 2) return $langs->trans($keytrans).' '.img_picto($langs->trans($keytrans), $statustrans);
		elseif ($mode == 3) return img_picto($langs->trans($keytrans), $statustrans).' '.$langs->trans($shortkeytrans);
		elseif ($mode == 4) return $langs->trans($shortkeytrans).' '.img_picto($langs->trans($keytrans), $statustrans);
	}
	
	}
	
	
	
	class seedrandoDet extends TObjetStd
	{
		public $table_element = 'seedrandodet';
		public $element = 'seedrandodet';
		public function __construct($db)
		{
			global $conf,$langs;
			$this->db = $db;
			$this->init();
			$this->user = null;
		}
		





		
		
		

function _get_showWayPoint($object, $mode)
{
	global $form;
	$html = '';
	$object->loadRelation('TWaypoint', 'fk_wayPoint', 'relationTable', 'fk_seedRando', 'wayPoint', '$TWaypoint', 'load');
	
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
			$TallWayPoint = get_multiselectarray($TlistSelectWayPoint, $objectWayPoint, $db);
			$html = $form->multiselectarray('wayPoint', $TallWayPoint, $TlistSelectWayPoint);
		}
		else {
			$temp = get_multiselectarray($TlistSelectWayPoint, $objectWayPoint, $db);
			$html = $form->multiselectarray('wayPoint', $temp, $objectWayPoint->name);
		}
	}
	return $html;
}

function _get_listContact($object, $db, $id, $action)
{
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
			$html .= $object->TContact[$i]->firstname.' '.$object->TContact[$i]->lastname.'<br>';
		}
	}
	return $html;
}

function get_multiselectarray ($TlistSelectWayPoint, $objectWayPoint, $db)
{
	global $form;
	global $action;
	global $id;
	global $db;
	//recuperer la liste complete des wayPoints
	$sql = 'SELECT t.rowid, t.name';//requette pour permettre l'affichage des waypoints dans la creation de la rando
	$sql.= ' FROM '.MAIN_DB_PREFIX.'wayPoint t ';
	$dataresult = $db->query($sql);
	$TlistSelectWayPoint = array();
	while ($display = $db->fetch_object($dataresult)) {
		$TlistSelectWayPoint[$display->rowid] =  $display->name;
	}
	return $TlistSelectWayPoint;
}

function _get_showContact ($objectContact, $db)
{
	global $form;
	global $action;
	global $id;
	//recuperer laliste complete des contacts
	$sql = 'SELECT t.rowid, t.lastname, t.firstname';//requette pour permettre l'affichage des contacts dans les objets liès de la rando
	$sql.= ' FROM '.MAIN_DB_PREFIX.'socpeople t ';
	$dataresult = $db->query($sql);
	$TlistSelectContact = array();
	while ($display = $db->fetch_object($dataresult)) {
		$TlistSelectContact[$display->rowid] =  $display->firstname . " ". $display->lastname;
	}
	$TlistSelectContact = $form->selectarray('listSelectContact', $TlistSelectContact, $objectContact->firstname, $objectContact->lastname ); //modification pour utiliser la drop list difficulte
	return $TlistSelectContact;
}



// function _get_showWayPoint($objectWayPoint, $TlistSelectWayPoint, $mode = 'view', $id, $db)
// {
// 	global $form;
// 	$object = new seedrando($db);
	
// 	if(!empty($id))
	// 	{
	// 		if($mode == 'view')
		// 		{
		// 			$test = get_stringOut($id, $db);
		// 			return $test;
		// 		}
		// 		elseif ($mode == 'edit')
		// 		{
		// 			$test = get_multiselectarray($TlistSelectWayPoint, $objectWayPoint, $db);
		// 			return $test;
		
		// 		}
		// 	}
		// 	return;
		// }
		
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
		
		
		
		// function get_stringOut($id, $db)
		// {
		// 	$sql = 'SELECT t.fk_wayPoint';//requette pour permettre l'affichage des waypoints dans la creation de la rando
		// 	$sql .= ' FROM '.MAIN_DB_PREFIX.'relationTable t ';
		// 	$sql .= ' WHERE fk_seedRando =  ' .$id;
		
		// 	$dataresult = $db->query($sql);
		
		// 	$TlistOfMyWayPoint = array();
		
		// 	while ($display = $db->fetch_object($dataresult)) {
		
		// 		$TlistOfMyWayPoint[] = $display->fk_wayPoint;
		// 	}
		// 	//j'ai recuperer les fk_wayPoint de la table relationTable
		
		// 	//j'itere sur mon tableau $listOfMyWayPoint pour recuperer mes objets wayPoint
		// 	$stringOut = array();
		// 	foreach ($TlistOfMyWayPoint AS $value)
			// 	{
			// 		$sql = 'SELECT t.name';//requette pour permettre l'affichage des waypoints dans la creation de la rando
			// 		$sql.= ' FROM '.MAIN_DB_PREFIX.'wayPoint t WHERE rowid = ' .$value;
				
			// 		$dataresult = $db->query($sql);
			
			// 		$display = $db->fetch_object($dataresult);
			
			// 		$stringOut[] = $display->name;
			// 	}
			// 	return implode(", ", $stringOut);
			// }
			
			
			if ($mode == 'edit') echo $formcore->end_form();
			
			// if ($mode == 'view' && $object->id) $somethingshown = $form->showLinkedObjectBlock($object);
			
			




			
			
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
			,'showWayPoint' => _get_showWayPoint($object, $mode)//modification pour utiliser la drop list difficulte
			,'showDifficulte' => _get_showDifficulte($object, $selectDifficulte,  $mode)//modification pour utiliser la drop list difficulte
			,'showContact' => get_listSelectArray($TContact, $db, 'lastname', 'firstname', 'socpeople', true)
			,'showListContact' => _get_listContact($object, $db, $id, $action)
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
			
			//rowid
			//lastname
			//firstname
			//socpeople
			//($TlistSelect, $db, $field1, $field2, $table, $ifContact = false)
			
			function _get_showWayPoint($object, $mode)
			{
				global $form;
				$html = '';
				$object->loadRelation('TWaypoint', 'fk_wayPoint', 'relationTable', 'fk_seedRando', 'wayPoint', '$TWaypoint', 'load');
				
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
						$TallWayPoint = get_listSelectArray($TlistSelectWayPoint, $db, 'rowid', 'name', 'wayPoint', true);
						$html = $form->multiselectarray('wayPoint', $TallWayPoint, $TlistSelectWayPoint);
					}
					else {
						$temp = get_listSelectArray($TlistSelectWayPoint, $db, 'rowid', 'name', 'wayPoint', true);
						$html = $form->multiselectarray('wayPoint', $temp, $objectWayPoint->name);
					}
				}
				return $html;
			}
			
			
			function _get_listContact($object, $db, $id, $action)
			{
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
						$html .= $object->TContact[$i]->firstname.' '.$object->TContact[$i]->lastname.'<br>';
					}
				}
				return $html;
			}
			function _get_showContact ()
			{
				global $form;
				global $action;
				global $id;
				global $db;
				
				//recuperer laliste complete des contacts
				$sql = 'SELECT t.rowid, t.lastname, t.firstname';//requette pour permettre l'affichage des contacts dans les objets liès de la rando
				$sql.= ' FROM '.MAIN_DB_PREFIX.'socpeople t ';
				$dataresult = $db->query($sql);
				$TlistSelectContact = array();
				while ($display = $db->fetch_object($dataresult)) {
					$TlistSelectContact[$display->rowid] =  $display->firstname . " ". $display->lastname;
				}
				$TlistSelectContact = $form->selectarray('listSelectContact', $TlistSelectContact, $objectContact->firstname, $objectContact->lastname ); //modification pour utiliser la drop list difficulte
				return $TlistSelectContact;
			}
			
			//lastname
			//firstname
			//socpeople
			
			//($TContact, $db, 'lastname', 'firstname', 'socpeople', true)
			
			function get_listSelectArray ($TlistSelect, $db, $field1, $field2, $table, $ifContact = false)
			{
				global $form;
				global $action;
				global $id;
				global $db;
				
				//recuperer la liste complete des wayPoints ou des contacts
				
				$sql = 'SELECT t.rowid' . $field1  . ', t.' . $field2;//requette pour permettre l affichage des waypoints et des contacts dans la rando
				$sql.= ' FROM '.MAIN_DB_PREFIX . $table . ' t ';
				$dataresult = $db->query($sql);
				// 	$TlistSelect = array();
				while ($display = $db->fetch_object($dataresult)) {
					if($ifContact)
					{
						$test = $display->firstname . " ". $display->lastname;
					}
					else
					{
						$test = $display->name;
					}
					
					$TlistSelect[$display->rowid] =  $test;
				}
				if ($ifContact)
				{
					$TlistSelectContact = $form->selectarray('listSelectContact', $TlistSelectContact, $objectContact->firstname, $objectContact->lastname ); //modification pour utiliser la drop list difficulte
				}
				return $TlistSelect;
			}
			
			// function get_multiselectarray ($TlistSelectWayPoint, $objectWayPoint, $db)
			// {
			// 	global $form;
			// 	global $action;
			// 	global $id;
			// 	global $db;
			// 	//recuperer la liste complete des wayPoints
			// 	$sql = 'SELECT t.rowid, t.name';//requette pour permettre l'affichage des waypoints dans la creation de la rando
			// 	$sql.= ' FROM '.MAIN_DB_PREFIX.'wayPoint t ';
			// 	$dataresult = $db->query($sql);
			// 	$TlistSelectWayPoint = array();
			// 	while ($display = $db->fetch_object($dataresult)) {
			// 		$TlistSelectWayPoint[$display->rowid] =  $display->name;
			// 	}
			// 	return $TlistSelectWayPoint;
			// }
			
			
			
			
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
			
			if ($mode == 'edit') echo $formcore->end_form();
			
			// if ($mode == 'view' && $object->id) $somethingshown = $form->showLinkedObjectBlock($object);
			
			
			
			
			
			
			
			
			
			
			
			public function save($addprov=false)
			{
				global $user;
				if (!$this->id) $this->fk_user_author = $user->id;
				$res = $this->id>0 ? $this->updateCommon($user) : $this->createCommon($user);
				if ($addprov || !empty($this->is_clone))
				{
					$this->ref = '(PROV'.$this->id.')';
					if (!empty($this->is_clone)) $this->status = self::STATUS_DRAFT;
					$wc = $this->withChild;
					$this->withChild = false;
					$res = $this->id>0 ? $this->updateCommon($user) : $this->createCommon($user);
					$this->withChild = $wc;
				}
				
				$this->UpdateOrSaveWay();
				
				if(GETPOST('action') == 'save')
				{
					if(empty($this->wayPoint))
					{
						$this->deleteWay();
					}
				}
				return $res;
			}
			
			public function deleteWay()
			{
				$sqlDelete2 = 'DELETE FROM ' .MAIN_DB_PREFIX. 'relationTable';
				$sqlDelete2 .= ' WHERE fk_seedRando = '.$this->id;
				$this->db->query($sqlDelete2);
			}
			
			public function UpdateOrSaveWay()
			{
				global $user;
				$this->loadRelation('TWaypoint', 'fk_wayPoint', 'relationTable', 'fk_seedRando', 'wayPoint', '$TWaypoint', 'load');
				
				$count = count($this->TWaypoint);
				
				if(!empty($this->wayPoint))// Sauvegarde de tous les waypoints
				{
					foreach($this->wayPoint as $value)
					{
						for ($t = 0 ; $t < $count ; $t++)//si donnée present dans this->TWaypoint et present dans this->wayPoint alors save sinon delete
						{
							if(!in_array($this->TWaypoint[$t]->id, $this->wayPoint))
							{
								//on supprime la valeur dans la table relationnel
								$sqlDelete = 'DELETE FROM ' .MAIN_DB_PREFIX. 'relationTable';
								$sqlDelete .= ' WHERE fk_seedRando = '.$this->id;
								$sqlDelete .= ' AND fk_wayPoint = '.$this->TWaypoint[$t]->id;
								$this->db->query($sqlDelete);
							}
						}
						
						$In = new relationTable($this->db);
						$In -> fk_seedRando = $this->id;
						$In -> fk_wayPoint = $value;
						
						$sql = 'SELECT fk_wayPoint FROM ' .MAIN_DB_PREFIX. 'relationTable ';//remplacer la requette par interogation du $this->TWaypoint
						$sql .= 'WHERE fk_wayPoint = ' . $value;
						$sql .= ' and fk_seedRando = ' . $this->id;
						
						$res = $this->db->query($sql);
						
						if ($this->db->num_rows($res) == 0)
						{
							$In -> create($user);
						}
					}
				}
			}
			
			public function saveContact($addprov=false)
			{
				$sql = 'SELECT fk_socpeople_target FROM ' .MAIN_DB_PREFIX. 'relationRandoContact ';//remplacer la requette par interogation du $this->Tcontact
				$sql .= 'WHERE fk_socpeople_target = ' . $this->listSelectContact;
				$sql .= ' and fk_seedRando_source = ' . $this->id;
				
				$res = $this->db->query($sql);
				
				if ($this->db->num_rows($res) == 0)
				{
					$sql = 'INSERT INTO ' .MAIN_DB_PREFIX. 'relationRandoContact (fk_seedRando_source, fk_socpeople_target)';
					$sql .= 'VALUES ('.$this->id.','.$this->listSelectContact.')';
					$this->db->query($sql);
				}
			}
			
			public function loadRelation($ThisArray, $fk_objectTarget, $tableSelect, $fk_objectSource, $newObject, $Tobject, $fctToCall)
			{
				$this->$ThisArray = array();
				$sql = 'SELECT ' .$fk_objectTarget . ', rowid FROM ';
				$sql .= MAIN_DB_PREFIX . $tableSelect;
				$sql .= ' WHERE ' .$fk_objectSource. ' = '.$this->id;
				
				$resql = $this->db->query($sql);
				if ($resql)
				{
					while($return = $this->db->fetch_object($resql)){
						$relObject = new $newObject($this->db);
						$relObject->$fctToCall($return->$fk_objectTarget, '');
						$this->$ThisArray[] = $relObject;
					}
				}
				return $Tobject;
			}






