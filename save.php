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
//$sql.= ' WHERE 1=1';
$dataresult = $db->query($sql);

$TlistSelectWayPoint = array();
// $TlistSelectWayPoint[] = 'selectionner';

// $wayPoint = new wayPoint();

// $out = new wayPoint($db);
// var_dump($out);

while ($display = $db->fetch_object($dataresult)) {

	$TlistSelectWayPoint[$display->name] =  $display->name;
}//fin de la recherche des waypoint pour la list select
// var_dump($TlistSelectWayPoint);