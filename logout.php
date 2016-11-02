<?php
/**
 * @author Viet Dung Tran
 *
 * created on 12.02.2012
 */

	session_start();
	include_once ('include/header.inc.php');
	include_once ('include/footer.inc.php');
	include_once ('include/sidebar.inc.php');
	
	// $_SESSION leeren
	$_SESSION = array();
	// Session löschen
	session_destroy();
	htmlHeader("DBS Abschlussprojekt");
	
	htmlSidebarNewRez();
	htmlSidebarMerkt();
	htmlSidebarEnd();
	echo "<h2>Sie wurden erfolgreich ausgeloggt.<br></h2>\n".
	     "<div class=\"link\"> <a href=\"login.php\"> Zur: Einloggen</a></div>\n"; 
	
	htmlFooter();

?>