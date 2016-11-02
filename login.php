<?php
/**
 * @author Viet Dung Tran
 *
 * created on 12.02.2012
 */

	session_start();
	// Prüft, ob schon eingeloggt
	if(isset($_SESSION['benutzerName'])) {
		header('location:index.php');
		exit();
	}
	
	include("autoload.php");
	include("include/config.php");
	include_once ('include/header.inc.php');
	include_once ('include/footer.inc.php');
	include_once ('include/sidebar.inc.php');	
	
	$db = new dbExec(UDB_HOST, UDB_USER	, UDB_PASS, UDB_NAME);
	
	// Fehlerarray anlegen
	$fehler = array();
	// LoginDatenarray anlegen
	$daten  = array();
	
	if(isset($_POST['einloggen']) AND $_POST['einloggen']=='Einloggen'){

		// Prüft, ob ein benutzerName eingegeben wurde
		if(!isset($_POST['benutzerName']) || empty($_POST['benutzerName'])){
			$fehler[]= "Bitte geben Sie Ihr BenutzerName ein.";
		} else {
			$daten['benutzerName'] = addslashes(trim($_POST['benutzerName']));
		}
			
		// Prüft, ob ein kennwort eingegeben wurde
		if(!isset($_POST['kennwort']) || empty($_POST['kennwort'])){
			$fehler[]= "Bitte geben Sie Ihr kennwort ein.";
		} else {
			$tmp= addslashes(trim($_POST['kennwort']));
			$daten['kennwort']= md5($tmp);
		}
			
		if(empty($fehler)){ // keine Fehler
			
			$sql ='SELECT BeID FROM Benutzer WHERE benutzerName = ? AND kennwort = ? ';
			array_unshift($daten, 'ss');
			$result= $db->bindAbfrageResult($sql, $daten);
			if(!empty($result)){//login korrekt
				 $_SESSION['BeID']	= $result[0]->BeID;
				 $_SESSION['benutzerName'] = $daten['benutzerName'];
				 header("location: index.php");	
			}else {
				$fehler[] = 'Du hast einen falschen Benutzernamen oder ein falsches Kennwort eingegeben.<br>'.
							'<a href="kennwort.php">Hast du vielleicht dein Kennwort vergessen?</a>';
			}
			
		}
	} // END if($_POST['submit'])
	
	$mainnav =array('registriert.php'=>'Anmeldung');
	htmlHeader("Einloggen",$mainnav);
	
	htmlSidebarNewRez();
	htmlSidebarMerkt();
	htmlSidebarEnd();
	
	if(!empty($fehler)){
		echo "<ul id =\"fehler\">";
		foreach($fehler as $error) {
			echo "<li>{$error}</li>";
			}
			echo "</ul>";
	}
	
	//Login form
	printf('
				<div id ="form">
					<form action="%s" method="post">
						<table border="0" cellspacing="2" cellpadding="2">
							<tr>
								<td><label >Benutzername</label></td>
								<td><input type="text" name="benutzerName" /></td>
							</tr>
							<tr>
								<td><label >Kennwort</label></td>
								<td><input type="password" name="kennwort" /></td>
							</tr>
						</table>
						<p><input type="submit" name="einloggen" value="Einloggen" /></p>						
					</form>
		        <div class="link">
		            <a href="kennwort.php">Passwort vergessen</a>
		        </div>
		        <div class="link">
		            <a href="registriert.php">Registrieren</a>
		        </div>
		     </div><!-- END #loginForm -->
			', $_SERVER['PHP_SELF']);
 
	htmlFooter();

?>