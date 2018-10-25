<?php

if (!class_exists('SeedObject'))
{
	/**
	 * Needed if $form->showLinkedObjectBlock() is call
	 */
	define('INC_FROM_DOLIBARR', true);
	require_once dirname(__FILE__).'/../config.php';
	
	
}


class relationTable extends SeedObject
{
	/**
	 * Draft status
	 */
	const STATUS_DRAFT = 0;
	/**
	 * Validated status
	 */
	const STATUS_VALIDATED = 1;
	/**
	 * Refused status
	 */
	const STATUS_REFUSED = 3;
	/**
	 * Accepted status
	 */
	const STATUS_ACCEPTED = 4;
	
	public static $TStatus = array(
		self::STATUS_DRAFT => 'Draft'
		,self::STATUS_VALIDATED => 'Validate'
		,self::STATUS_REFUSED => 'Refuse'
		,self::STATUS_ACCEPTED => 'Accept'
	);
	
	public $table_element = 'relationTable';

	public $element = 'relationTable';
	
	public $noteRando = null;
	
	public function __construct($db)
	{
		global $conf,$langs;
		
		$this->db = $db;
		
		$this->fields=array('ref'=>array('type'=>'string','length'=>50,'index'=>true)
							,'source_type_object'=>array('type'=>'string')
							,'target_type_object'=>array('type'=>'string')
							,'fk_source'=>array('type'=>'string')
							,'fk_target'=>array('type'=>'string')
							,'noteRando'=>array('type'=>'string')
							,'status'=>array('type'=>'integer','index'=>true) // date, integer, string, float, array, text
							,'entity'=>array('type'=>'integer','index'=>true));
		
		$this->init();
		
		$this->status = self::STATUS_DRAFT;
		$this->entity = $conf->entity;
	}
	
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
		
		return $res;
	}
	
	function _get_showWayPoint($object, $mode)
	{
		global $form;
		$html = '';
		$object->loadRelation('TWaypoint', 'fk_target', 'relationTable', 'fk_source', 'wayPoint', '$TWaypoint', 'load', 'wayPoint');
		
		$count = count($object->TWaypoint);
		if ($mode == 'view')
		{
			for ($i = 0; $i<$count ;$i++)
			{
				if($i > 0) $html .= ' - ';
				$html .= $object->TWaypoint[$i]->name;
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
				$TallWayPoint = get_listSelectArray($TlistSelectWayPoint, 'rowid', 'name', 'wayPoint');
				$html = $form->multiselectarray('listSelectWayPoint', $TallWayPoint, $TlistSelectWayPoint);
			}
			else
			{
				$temp = get_listSelectArray($TlistSelectWayPoint, 'rowid', 'name', 'wayPoint');
				$html = $form->multiselectarray('listSelectWayPoint', $temp, $objectWayPoint->name);
			}
		}
		return $html;
	}
	
	
	public function get_noteRando($object, $idContact)
	{
		global $db;
		$sql = 'SELECT noteRando FROM '. MAIN_DB_PREFIX . 'relationTable';
		$sql .= ' WHERE fk_source = ' . $object->id;
		$sql .= ' AND fk_target = ' . $idContact;
		$sql .= ' AND target_type_object = "socpeople"';

		$test = new relationTable($db);
		
		$resql = $test->db->query($sql);
		
		if($resql)
		{
			while($return = $test->db->fetch_object($resql))
			{
				$relObject = new relationTable($db);
				$relObject->noteRando = $return->noteRando;
			}
		}
		return $relObject;
	}
	
	public function _get_listContact($object, $action)
	{
		global $db;
		$object->loadRelation('TContact', 'fk_target', 'relationTable', 'fk_source', 'contact', '$TContact', 'fetch', 'socpeople');
		
		$count = count($object->TContact);
		if ($action == "create" || $action == "edit")
		{
			$result = "aucun";
		}
		else//mode=view
		{
			for ($i = 0; $i<$count ;$i++)
			{
				$relObject = new relationTable($object->db);
				
				$relObject = $relObject::get_noteRando($object, $object->TContact[$i]->id);
				
				$html .= 	'<tr><td>' . $object->TContact[$i]->firstname;
				$html .= 	' ' . $object->TContact[$i]->lastname . '</td>';
				$html .= 	'<td style="text-align: center;">' . $relObject->noteRando . '</td>';
				$html .= 	'<td style="width: 200px; text-align: center; margin:0px; padding: 0px;">';
				$html .= 	'<form action="http://localhost/dolibarr/htdocs/custom/seedrando/card.php?id=';
				$html .= 	$object->id . '&action=saveNote&idContact=';
				$html .= 	$object->TContact[$i]->id . '" method="post">';
				$html .= 	'<input type="hidden" name="action" value="saveNote">';
				$html .= 	'<select id="note" name="note">';
				$html .= 	'<option value="'. $relObject->noteRando . '">' . $relObject->noteRando . '</option>
							<option value="1">1</option>
							<option value="2">2</option>
							<option value="3">3</option>
							<option value="4">4</option>
							<option value="5">5</option></select>';
				$html .= 	'<input class="button" type="submit" value="save" style="margin-left:30px;"></form></td>';
				$html .= 	'<td style="width: 100px; text-align: center; margin:0px; padding: 0px;">';
				$html .= 	'<a href="http://localhost/dolibarr/htdocs/custom/seedrando/card.php?id=';
				$html .= 	$object->id . '&action=deleteContact&idContact=';
				$html .= 	$object->TContact[$i]->id . '" >';
				$html .= 	'<img src="/dolibarr/htdocs/theme/eldy/img/delete.png"';
				$html .= 	'title="supprimer le contact de cette rando"></a></td></tr>';
			}
		}
		return $html;
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
		
		if ($loadChild) $this->fetchObjectLinked();
		
		return $res;
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
//		global $user;
		
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
		
		$mask = !empty($conf->global->MYMODULE_REF_MASK) ? $conf->global->MYMODULE_REF_MASK : 'RT{yy}{mm}-{0000}';
		$numero = get_next_value($db, $mask, 'relationTable', 'ref');
		
		return $numero;
	}
	
	public function setRefused()
	{
//		global $user;
		
		$this->status = self::STATUS_REFUSED;
		$this->withChild = false;
		
		return self::save();
	}
	
	public function setAccepted()
	{
//		global $user;
		
		$this->status = self::STATUS_ACCEPTED;
		$this->withChild = false;
		
		return self::save();
	}
	
	public function getNomUrl($withpicto=0, $get_params='')
	{
		global $langs;

        $result='';
        $label = '<u>' . $langs->trans("ShowrelationTable") . '</u>';
        if (! empty($this->ref)) $label.= '<br><b>'.$langs->trans('Ref').':</b> '.$this->ref;
        
        $linkclose = '" title="'.dol_escape_htmltag($label, 1).'" class="classfortooltip">';
        $link = '<a href="'.dol_buildpath('/seedrando/card_relationTable.php', 1).'?id='.$this->id. $get_params .$linkclose;
       
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
		
		$object = new relationTable($db);
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
		$langs->load('relationTable@relationTable');

		if ($status==self::STATUS_DRAFT) { $statustrans='statut0'; $keytrans='relationTableStatusDraft'; $shortkeytrans='Draft'; }
		if ($status==self::STATUS_VALIDATED) { $statustrans='statut1'; $keytrans='relationTableStatusValidated'; $shortkeytrans='Validate'; }
		if ($status==self::STATUS_REFUSED) { $statustrans='statut5'; $keytrans='relationTableStatusRefused'; $shortkeytrans='Refused'; }
		if ($status==self::STATUS_ACCEPTED) { $statustrans='statut6'; $keytrans='relationTableStatusAccepted'; $shortkeytrans='Accepted'; }

		
		if ($mode == 0) return img_picto($langs->trans($keytrans), $statustrans);
		elseif ($mode == 1) return img_picto($langs->trans($keytrans), $statustrans).' '.$langs->trans($keytrans);
		elseif ($mode == 2) return $langs->trans($keytrans).' '.img_picto($langs->trans($keytrans), $statustrans);
		elseif ($mode == 3) return img_picto($langs->trans($keytrans), $statustrans).' '.$langs->trans($shortkeytrans);
		elseif ($mode == 4) return $langs->trans($shortkeytrans).' '.img_picto($langs->trans($keytrans), $statustrans);
	}
	
}



class relationTableDet extends TObjetStd
{
	public $table_element = 'relationTabledet';

	public $element = 'relationTabledet';
	
	public function __construct($db)
	{
		global $conf,$langs;
		
		$this->db = $db;
		
		$this->init();
		
		$this->user = null;
	}
	
	
}

