<?php
/**
 * @author Viet Dung Tran
 *
 * created  on  05.02.2012
 */

function __autoload($klassenname){
	
	$klassenname = strtolower($klassenname);
	$pfad = "bib/".$klassenname.'.class.php';
	require_once $pfad;
}
?>