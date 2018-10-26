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
	

	
	public function save($addprov=false, $target_type_object = '')
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
		
		//echo $target_type_object;exit;
		
		if($target_type_object == 'wayPoint')
		{
			$target_type_object = 'wayPoint';
		}
		else
		{
			//echo 'toto';exit;
			$target_type_object = 'socpeople';
		}

		//echo $target_type_object;exit;
		$this->UpdateOrSaveWay($target_type_object);
	
		if(GETPOST('action') == 'save')
		{
			if(empty($this->$target_type_object))
			{
				$this->deleteRelation('relationTable', 'fk_seedRando_source', '',$target_type_object);
			}
		}
		return $res;
	}
	
	public function UpdateOrSaveWay($target_type_object)
	{
		global $user;

		//echo $target_type_object;exit;
		
		if($target_type_object == 'wayPoint')
		{
			$ArrayToSave = 'TWaypoint';
			$VarArrayToSave = '$TWaypoint';
			$fctToCall = 'load';
			$listSelectIn = 'wayPoint';
		}
		else
		{
			//echo 'toto';exit;
			$ArrayToSave = 'TContact';
			$VarArrayToSave = '$TContact';
			$fctToCall = 'fetch';
			$listSelectIn = 'listSelectContact';
		}

		$this->loadRelation($ArrayToSave, 'fk_target', 'relationTable', 'fk_seedRando_source', $target_type_object, $VarArrayToSave, $fctToCall);

		$count = count($this->$ArrayToSave);
		
		if(!empty($this->$listSelectIn))// Sauvegarde de tous les waypoints
		{
			//echo 'toto1';exit;
			//echo $this->$listSelectIn;exit;
			
			if($target_type_object == 'wayPoint')
			{
				foreach($this->$listSelectIn as $value)
				{
					for ($t = 0 ; $t < $count ; $t++)//si donnée present dans this->TWaypoint et present dans this->wayPoint alors save sinon delete
					{
						if(!in_array($this->$ArrayToSave[$t]->id, $this->$listSelectIn))
						{
							$idToSave = $this->$ArrayToSave[$t]->id;
							$this->deleteRelation('relationTable', 'fk_seedRando_source', $idToSave, $target_type_object);
						}
					}
					$this->saveRelation('relationTable', 'fk_seedRando_source', 'fk_target', $value, $target_type_object, false);
				}
			}
			else
			{
				//echo 'toto';exit;
				$this->saveRelation('relationTable', 'fk_seedRando_source', 'fk_target', $this->$listSelectIn, $target_type_object, true);
			}

			//echo 'tata';exit;
		}
	}

	public function saveRelation($tableRelation, $fk_source, $fk_target, $listToSave, $target_type_object, $ifContact = false)
	{
		global $user;

		//echo $tableRelation;
		
		
		//echo 'tata';exit;
		
		
		$In = new $tableRelation($this->db);
		$In -> $fk_source = $this->id;
		//echo $In -> $fk_source . '<br>';
		$In -> source_type_object = 'seedRando';
		//echo $In -> source_type_object . '<br>';
		$In -> target_type_object = $target_type_object;
		//echo $In -> target_type_object . '<br>';
		
		if($ifContact == true)
		{
			$In -> $fk_target = $this->$listToSave;
			//echo $In -> $fk_target;
		}
		else
		{
			$In -> $fk_target = $listToSave;
		}

		$sql = 'SELECT ' . $fk_target . ' FROM ' . MAIN_DB_PREFIX . $tableRelation;//remplacer la requette par interogation du $this->Tcontact
		$sql .= ' WHERE ' . $fk_target . ' = ';
		
		if($ifContact == true)
		{
			$sql .= $this->$listToSave;
		}
		else
		{
			$sql .= $listToSave;
		}
		
		$sql .= ' and ' . $fk_source . ' = ' . $this->id;
		$sql .= ' and target_type_object = "' . $target_type_object . '"';
		
		//var_dump($In -> $fk_source);exit;
		
// 		echo $target_type_object;exit;
		//SELECT fk_target FROM llx_relationTable WHERE fk_target = 5 and fk_seedRando_source = 1 and target_type_object = "contact" 
		$res = $this->db->query($sql);
		
		if ($this->db->num_rows($res) == 0)
		{
			$In -> create($user);
		}
		
		if($target_type_object == 'socpeople')
		{
// 			echo 'toto';
// 			exit;
			$In -> create($user);
		}
	}

	
	public function loadRelation($ThisArray, $fk_objectTarget, $tableSelect, $fk_objectSource, $newObject, $Tobject, $fctToCall)
	{
		if($newObject == 'socpeople')
		{
			$newObject = 'contact';
		}
		
		$this->$ThisArray = array();
		$sql = 'SELECT ' .$fk_objectTarget . ', rowid FROM ';
		$sql .= MAIN_DB_PREFIX . $tableSelect;
		$sql .= ' WHERE ' .$fk_objectSource. ' = '.$this->id;
		$sql .= ' AND target_type_object = "' . $newObject . '"';

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
		
	public function deleteRelation($TableRelation, $fk_source, $fk_target = '', $target_type_object)
	{
		$sqlDelete = 'DELETE FROM ' . MAIN_DB_PREFIX . $TableRelation;
		$sqlDelete .= ' WHERE ' . $fk_source .' = '.$this->id;
		
		if($fk_target != '')
		{
			$sqlDelete .= ' AND fk_target = ' .$fk_target;
		}
		$sqlDelete .= ' AND target_type_object = "' . $target_type_object . '"';
		
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