<?php

if (!class_exists('SeedObject'))
{
	/**
	 * Needed if $form->showLinkedObjectBlock() is call
	 */
	define('INC_FROM_DOLIBARR', true);
	require_once dirname(__FILE__).'/../config.php';
	
	dol_include_once('/seedrando/class/wayPoint.class.php');
	dol_include_once('/seedrando/class/relationTable.class.php');
	dol_include_once('../contact/class/contact.class.php');
}


class seedrando extends SeedObject
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
	
	public $table_element = 'seedrando';

	public $element = 'seedrando';
	
	public $TWaypoint = array();
	
	public $Ttemp = array();
	
	public $TContact = array();
	
	public function __construct($db)
	{
		global $conf,$langs;
		
		$this->db = $db;
		
		$this->fields=array(
				'ref'=>array('type'=>'string','length'=>50,'index'=>true)
				,'label'=>array('type'=>'string')
				,'distance'=>array('type'=>'string')
				,'difficulte'=>array('type'=>'string')
				,'status'=>array('type'=>'integer','index'=>true) // date, integer, string, float, array, text
				,'entity'=>array('type'=>'integer','index'=>true)
		);
		
		$this->init();
		
		$this->status = self::STATUS_DRAFT;
		$this->entity = $conf->entity;
	}
	
	public function saveNote($idContact, $note)
	{
		global $user;
		
		$sql = 'UPDATE ' . MAIN_DB_PREFIX . 'relationTable ';
		$sql .= 'SET noteRando = ' . $note;
		$sql .= ' WHERE fk_source = ' . $this->id;
		$sql .= ' AND fk_target = ' . $idContact;
		$sql .= ' AND target_type_object = "socpeople"';
		
		$this->db->query($sql);
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
		
		$this->UpdateOrSaveWay();
	
		if(GETPOST('action') == 'save')
		{
			if(empty($this->listSelectWayPoint))
			{
				$this->deleteRelation('relationTable', 'fk_source');
			}
		}
		return $res;
	}
	
	public function UpdateOrSaveWay()
	{
		global $user;
		$this->loadRelation('TWaypoint', 'fk_target', 'relationTable', 'fk_source', 'wayPoint', '$TWaypoint', 'load');

		$count = count($this->TWaypoint);
		
		if(!empty($this->listSelectWayPoint))// Sauvegarde de tous les waypoints
		{
			foreach($this->listSelectWayPoint as $value)
			{
				for ($t = 0 ; $t < $count ; $t++)//si donnée present dans this->TWaypoint et present dans this->wayPoint alors save sinon delete
				{
					if(!in_array($this->TWaypoint[$t]->id, $this->listSelectWayPoint))
					{
						//on supprime la valeur dans la table relationnel
						$idWayPoint = $this->TWaypoint[$t]->id;
						$this->deleteRelation('relationTable', 'fk_source', 'fk_target', $idWayPoint, '');
					}
				}
				$this->saveRelation('relationTable', 'seedRando', 'wayPoint', 'fk_source', 'fk_target', $value, false);
			}
		}
	}

	public function saveRelation($tableRelation, $source_type_object, $target_type_object, $fk_source, $fk_target, $listToSave, $ifContact = false)
	{
		global $user;
		
		$In = new $tableRelation($this->db);
		$In -> source_type_object = $source_type_object;
		$In -> target_type_object = $target_type_object;
		$In -> fk_source = $this->id;
		
		if($ifContact == true)
		{
			$In -> fk_target = $this->$listToSave;
		}
		else
		{
			$In -> fk_target = $listToSave;
		}

		$sql = 'SELECT fk_target  FROM ' . MAIN_DB_PREFIX . 'relationTable';//remplacer la requette par interogation du $this->Tcontact
		$sql .= ' WHERE fk_target  = ';
		
		if($ifContact == true)
		{
			$sql .= $this->$listToSave;
		}
		else
		{
			$sql .= $listToSave;
		}
		
		$sql .= ' and fk_source = ' . $this->id;
		
		//$sql .= ' AND target_type_object = "' . $target_type_object .'"';
		
		$res = $this->db->query($sql);
		
		if ($this->db->num_rows($res) == 0)
		{
			$In -> create($user);
		}
	}

	
	public function loadRelation($ThisArray, $fk_Target, $tableSelect, $fk_Source, $newObject, $Tobject, $fctToCall, $target_type_object = '')
	{
		$this->$ThisArray = array();
		$sql = 'SELECT fk_Target, rowid FROM ';
		$sql .= MAIN_DB_PREFIX . 'relationTable';
		$sql .= ' WHERE fk_Source = '. $this->id;
		$sql .= ' AND target_type_object = "'. $target_type_object .'"';
		
		$resql = $this->db->query($sql);
		if ($resql)
		{		
			while($return = $this->db->fetch_object($resql)){
				$relObject = new $newObject($this->db);
				$relObject->$fctToCall($return->fk_Target, '');
				$this->$ThisArray[] = $relObject;
			}
		}
		return $Tobject;
	}
		
	public function deleteRelation($TableRelation, $fk_source, $fk_target = '', $x= '', $z='', $idContact = '', $target_type_object = '')
	{
		$sqlDelete = 'DELETE FROM ' . MAIN_DB_PREFIX . 'relationTable';
		$sqlDelete .= ' WHERE fk_source = '.$this->id;
		
		if($fk_target != '')
		{
			$sqlDelete .= ' AND fk_target = ' .$idContact;
		}
		$sql .= ' AND target_type_object = "' . $target_type_object .'"';
		
		$this->db->query($sqlDelete);
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

	
	public function delete(User &$user)
	{
// 		global $db;

		$this->deleteObjectLinked();
		
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
	
	
}