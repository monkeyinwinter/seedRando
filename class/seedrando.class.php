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

		$this->loadWaypoints();

		$count = count($this->TWaypoint);
		
		if(!empty($this->wayPoint))// Sauvegarde de tous les waypoints si il y en @author thibault
		{
			foreach($this->wayPoint as $value)
			{
				//si donnée present dans this->TWaypoint et present dans this->wayPoint alors save sinon delete
				for ($t = 0 ; $t < $count ; $t++)
				{
					if(!in_array($this->TWaypoint[$t]->id, $this->wayPoint))
					{
						echo 'on supprime';//on supprime la valeur dans la table relationnel
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
		
// 		if(empty($this->wayPoint) )
// 			{
// 				$sqlDelete2 = 'DELETE FROM ' .MAIN_DB_PREFIX. 'relationTable';
// 				$sqlDelete2 .= ' WHERE fk_seedRando = '.$this->id;
// 				$this->db->query($sqlDelete2);
// 			}
			
			
// 		else
// 		{
// 			$sqlDelete2 = 'DELETE FROM ' .MAIN_DB_PREFIX. 'relationTable';
// 			$sqlDelete2 .= ' WHERE fk_seedRando = '.$this->id;
// 			$this->db->query($sqlDelete2);
// 			echo $sqlDelete2;exit;
// 		}
		return $res;
	}
	
	public function saveContact($addprov=false)
	{
		
		$sql = 'SELECT fk_socpeople_target FROM ' .MAIN_DB_PREFIX. 'relationRandoContact ';
		$sql .= 'WHERE fk_socpeople_target = ' . $this->listSelectContact;
		$sql .= ' and fk_seedRando_source = ' . $this->id;
		
		$test = $this->db->query($sql);
		
		if ($test->num_rows > 0)
		{
			// echo 'present dans la liste !!';
		}
		else// echo 'absent dans la liste';
		{
			$sql = 'INSERT INTO ' .MAIN_DB_PREFIX. 'relationRandoContact (fk_seedRando_source, fk_socpeople_target)';
			$sql .= 'VALUES ('.$this->id.','.$this->listSelectContact.')';	
			$this->db->query($sql);
		}
	}
	
	public function loadWaypoints()
	{
		$this->TWaypoint = array();
		$sql = 'SELECT fk_wayPoint FROM '.MAIN_DB_PREFIX.'relationTable WHERE fk_seedRando = '.$this->id;
		$resql = $this->db->query($sql);
		if ($resql)
		{		
			while($return = $this->db->fetch_object($resql)){
				$way = new wayPoint($this->db);
				$way->load($return->fk_wayPoint, '');
				$this->TWaypoint[] = $way;
			}
		}
// 		var_dump($TWaypoint);
		return $TWaypoint;
	}
	
	public function loadBy($value, $field, $annexe = false)
	{
		$res = parent::loadBy($value, $field, $annexe);
		$this->loadWaypoints();
		return $res;
	}
	
	public function load($id, $ref, $loadChild = true)
	{	
		global $db;
		$res = parent::fetchCommon($id, $ref);
		if ($loadChild) 
		{
			$this->fetchObjectLinked();
// 			$sql = 'SELECT t.name, t.ref';//requette pour permettre l'affichage des waypoints dans la creation de la rando
// 			$sql.= ' FROM '.MAIN_DB_PREFIX.'wayPoint t WHERE rowid = ' .$res;
// 			$dataresult = $db->query($sql);
// 			$display = $db->fetch_object($dataresult);
		}
		return $res;
	}
		
	public function loadContacts()
	{
		$TtempContact = array();
		$sql = 'SELECT fk_socpeople_target FROM '.MAIN_DB_PREFIX.'relationRandoContact WHERE fk_seedRando_source = '.$this->id;
		$resql = $this->db->query($sql);
		
		if ($resql)
		{
			while($return = $this->db->fetch_object($resql)){
				$contact = new contact($this->db);
				$contact->fetch($return->fk_socpeople_target, '');
				$this->TContact[] = $contact;
			}
		}
// 		var_dump($TContact);
		return $TContact;
	}
	
	public function loadByContact($value, $field, $annexe = false)
	{
		$res = parent::loadBy($value, $field, $annexe);
		$this->loadContacts();
		return $res;
	}
	
	public function loadContact($id, $ref, $loadChild = true)
	{
		global $db;
		$res = parent::fetchCommon($id, $ref);
		if ($loadChild)
		{
			$sql = 'SELECT t.firstname t.lastname';//requette pour permettre l'affichage des waypoints dans la creation de la rando
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
		
		$mask = !empty($conf->global->MYMODULE_REF_MASK) ? $conf->global->MYMODULE_REF_MASK : 'SR{yy}{mm}-{0000}';
		$numero = get_next_value($db, $mask, 'seedrando', 'ref');
		
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

