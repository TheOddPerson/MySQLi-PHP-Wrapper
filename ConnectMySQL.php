<?php
/**
  *Author: Nick Bolhuis
  *File: ConnectMySQL.php
  *Version: 8.0.1
**/

//This File Requires that Smarty DIR has already been defined and Smarty Class has already been required.
$db = "";

function MySQLi_Begin () {
	global $db;
	if ($db == "") {
		global $smarty;
		$db = new mysqli($smarty->getConfigVars("MySQLAddress"), 
			$smarty->getConfigVars("MySQLUser"), 
			$smarty->getConfigVars("MySQLPass"), 
			$smarty->getConfigVars("MySQLDB"));
		if($db->connect_errno > 0){
			die('Unable to connect to database [' . $db->connect_error . ']');		
		}
	} else {		
		MySQLi_ping($db);
	}
	
} 
function MySQLi_Sanitize($string) {
	MySQLi_Begin();
	global $db;
	
	if (is_array($string)) {
		$array = array();
		foreach( $string as $key => $value ) {
			$array[$key] = mysqli_real_escape_string($db,$value);
		}
		return $array;
	} else {
		return mysqli_real_escape_string($db,$string);
	}
}

/*function HasPermission ($PermissionName) {
	return true;
}*/


function NoAccess() {
		//Handle your user permission error here
		/*
		global $smarty;
		$smarty->assign('Redirect','index.php');
		$smarty->assign('Message','Your account does not have access to perform this action.<br>If you think you are seeing this message in error please consult the administrator.<br>Error:No DB Access');
		$smarty->display('Confirmation.tpl');
		*/
}

function MySQLi_Select ($table, $columns, $orderby = '', $direction = 'ASC', $skip = 0, $limit = 'a') {     

	MySQLi_Begin();
	global $db;
	$sql = 	'SELECT ';
	foreach ($columns as $column) {
		$sql .= '`'.$column.'`,';
	}
	$sql = chop($sql, ',');
	$sql .= ' FROM `'.$table.'`';
	
	if ($orderby <> '') {
		$sql .= ' ORDER BY ' . $orderby . ' ' . $direction ;
	}
	if (is_integer($limit)) {
	$sql .= ' LIMIT ' . $skip . ', '. $limit;
	}
	if(!$result = $db->query($sql)){
		die('There was an error running the query [' . $db->error . ']');
	}
	else {
        while($row = $result->fetch_assoc()){    							
				$Output[]  = $row;
			}
	    }
    if (!isset($Output)) { //if no results
		$Output = array();
	}
	#$db->close();
	return $Output;
	$Output->free();	
}

function MySQLi_SelectWhere ($table, $columns, $where, $orderby = '0', $direction = 'ASC', $skip = 0, $limit = 'a') { //
    /*Make sure when defining columns you enter the Primary Key 
    or ID column as the first column*/
	MySQLi_Begin();
	global $db;
	$sql = 	'SELECT ';
	  foreach ($columns as $column) {
		  $sql .= '`'.$column.'`,';
	  }
	  $sql = chop($sql, ',');
	$sql .= ' FROM `'.$table.'`';
	$sql .= ' WHERE '.$where;
	if (!$orderby == '0') {
	$sql .= ' ORDER BY ' . $orderby . ' ' . $direction ;
	}
	if (is_integer($limit)) {
	$sql .= ' LIMIT ' . $skip . ', '. $limit;
	}
	//print $sql."<br>";
	if(!$result = $db->query($sql)){
		die('There was an error running the query [' . $db->error . ']');
	}
	else {
            while($row = $result->fetch_assoc()){
				$Output[]  = $row;
			}
		}			
	#$db->close();
	if(isset($Output)) {
	return $Output;
	} else {
	return array();
	}	
	$Output->free();	
}
function MySQLi_Count ($table, $where, $group = '') { 
    /*Make sure when defining columns you enter the Primary Key 
	or ID column as the first column*/
	MySQLi_Begin();
	global $db;

	$sql = 	'SELECT Count(*) FROM `'.$table.'`';
	if  ($where<>'') {
		$sql .= ' WHERE '.$where;
	}
	if ($group<>'') {
		$sql .= ' GROUP BY '.$group;
	}
	if(!$result = $db->query($sql)){
		die('There was an error running the query [' . $db->error . ']');
	}
	else {
        while($row = $result->fetch_assoc()){    							
				$Output[]  = $row;
			}
	    }
	#$db->close();
	return $Output[0]['Count(*)'];
	$Output->free();	
}
#columnstocompare is for an indexed group of columns
#othercolumnstosearch is for columns individually indexed
function MySQLi_Search  ($table, $columns, $columnstocompare, $string, $searchtype = 'NATURAL LANGUAGE MODE', $skip = 0, $limit = 'a', $othercolumnstosearch = array()) {

	MySQLi_Begin();
	global $db;

	$sql = 	'SELECT ';
	foreach ($columns as $column) {
		$sql .= '`'.$column.'`,';
	}
	$sql = chop($sql, ',');
	$sql .= ' FROM `'.$table.'`';
	$sql .= ' WHERE MATCH(';
	foreach ($columnstocompare as $column) {
		$sql .= '`'.$column.'`,';
	}
	$sql = chop($sql, ',') . ') ' ;
	$sql .= 'AGAINST("'. $string . '" IN ' . $searchtype . ')';
	foreach ($othercolumnstosearch as $column) {
		$sql .= ' OR MATCH(`' .$column.'`) AGAINST("'. $string . '" IN ' . $searchtype . ')';
	}
	if (is_integer($limit)) {
			$sql .= ' LIMIT ' . $skip . ', '. $limit;
	}	
    if(!$result = $db->query($sql)){
		die('There was an error running the query [' . $db->error . ']');
	}
	else {
        while($row = $result->fetch_assoc()){    							
				$Output[]  = $row;
			}
	    }
    if (!isset($Output)) { //if no results
		$Output = array();
	}
	#$db->close();
	return $Output;
	$Output->free();	
}
function MySQLi_Insert ($table, $columns, $values) {
	if (count($columns) <> count($values)) {
		die('Columns does not match values');
	}
    //THIS FUNCTION CAN ONLY INSERT 1 ROW AT A TIME
	global $smarty;
	if (!HasPermission('CanAdd')) {
		NoAccess();
		break;
	}
	MySQLi_Begin();
	global $db;

	$sql = 'INSERT INTO `'.$table.'` (';	
	foreach ($columns as $column) {
		$sql .= '`'.$column.'`,';
		}
	$sql = chop($sql,',');
	$sql .= ') VALUES (';
	foreach ($values as $value) {
		if (is_string($value)) {
			$value = mysqli_real_escape_string($db,$value);		
		} elseif (is_array($value)) {
			echo '<br>Error in MySQLi Connector - an array was passed as a value<br>';
			echo 'the value was:<br>';
			print_r ($value);
			exit;
		}
		$sql .= "'".$value."',";
	}	
	$sql = chop($sql,',');
	$sql .=')';	
	$result = $db->query($sql);
    $InsertedID = $db->insert_id;
	$User = $_SESSION['UserID'];
	MySQLi_UpdateLog ($db, $table, 'Insert', $sql, $User, $InsertedID, $values[0]);	
	return $InsertedID;
}
function MySQLi_Update ($table, $columns, $values, $ID, $primaryKey = 'ID') {
    if (!HasPermission('CanAlter')) {
		NoAccess();
		break;
	}
	MySQLi_Begin();
	global $db;

	$User = $_SESSION['UserID'];
	//Make the SQL query statement	
	$sql = 'UPDATE `'.$table.'` SET ';	
	foreach ($columns as $index=>$column) {		
		$sql .= $column."='".mysqli_real_escape_string($db,$values[$index])."',";
	}
	$sql = chop($sql,',');
	$sql .=' WHERE ' . $primaryKey .'='.$ID;
	
	if (!$db->query($sql)){
        printf("DB Error: %s\n", mysqli_error($db));
		printf($sql);
    }
    //Find out what ID was assigned to the inserted row
    $InsertedID = $db->insert_id;
	//Update the Log
	
		MySQLi_UpdateLog ($db, $table, 'Update', $sql, $User, $InsertedID, $values[0]);
	
	//return last inserted id
	return $InsertedID;
}
function MySQLi_Delete ($table, $where, $Name) {
    if (!HasPermission('CanDelete')) {
		NoAccess();
		break;
	}
	MySQLi_Begin();
	global $db;
	//Make the SQL query statement
	$sql = 'DELETE FROM `'.$table.'` WHERE '.$where;	
	//run the query
	if (!$db->query($sql)){
        printf("Error: %s\n", mysqli_error($db));
    }
		$User = $_SESSION['UserID'];
		MySQLi_UpdateLog ($db, $table, 'Delete', $sql, $User, $where, $Name);
	#$db->close();
}

function MySQLi_Compare ($table1, $columns, $table2, $compare1, $compare2, $where, $orderby = 'ID', $direction = 'ASC', $skip = 0, $limit = 'a') {
	MySQLi_Begin();
	global $db;
	$sql = 	'SELECT ';
	foreach ($columns as $column) {
	  $sql .= '`'.$column.'`,';
	}
	$sql = chop($sql, ',');
	$sql .= ' FROM `'.$table1.'`';
    $sql .= ' WHERE '.$table1.".".$compare1;
    $sql .= ' NOT IN ';
    $sql .= '(SELECT '.$compare2.' FROM '.$table2;
	$sql .= ' WHERE '.$where.')';
	$sql .= ' ORDER BY ' . $orderby . ' ' . $direction ;
	if (is_integer($limit)) {
	$sql .= ' LIMIT ' . $skip . ', '. $limit;
	}
	if(!$result = $db->query($sql)){
		die('There was an error running the query [' . $db->error . ']');
	}
	else {
            while($row = $result->fetch_assoc()){
				$Output[]  = $row;
			}
		}			
	#$db->close();
	if(isset($Output)) {
		return $Output;
	} else {
		return 'Nothing';
	}
	$Output->free();	
}

?>
