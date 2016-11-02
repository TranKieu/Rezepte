<?php
/**
 * @author Viet Dung Tran
 *
 * created on 13.02.2012
 */
	session_start();
	if(isset($_SESSION['benutzerName'])) {
		header('location:index.php');
		exit();
	}

	include_once ('include/config.php');
	include_once ('bib/dbexec.class.php');
	include_once ('include/header.inc.php');
	include_once ('include/footer.inc.php');
	include_once ('include/sidebar.inc.php');
	//datenbank verbinden
	$db = new dbExec(UDB_HOST, UDB_USER	, UDB_PASS, UDB_NAME);
	
	htmlHeader("Kennwort vergessen");
	htmlSidebarNewRez();
	htmlSidebarMerkt();
	htmlSidebarEnd();
	// Fehlerarray anlegen
	$fehler = array();
	$email = array();
	$messenger = array();
	
	// Prüft eingegengen Daten
	if(isset($_POST['abschicken']) AND $_POST['abschicken']=='Abschicken'){
		
		if(!isset($_POST['email']) || empty($_POST['email']))
			$fehler[]= "Bitte geben Sie Ihr Email ein.";
		elseif(!preg_match('$^[\w\.-]+@[\w\.-]+\.[\w]{2,4}$', trim($_POST['email'])))
        	$fehler[] = "Ihre Email Adresse hat eine falsche Syntax.";
		else {
			$email['email'] = addslashes(trim($_POST['email']));
		}
		
		// keine Fehler
		if(empty($fehler)){
			
			$sql = 'SELECT BeID, benutzerName FROM Benutzer WHERE email =?';
			
			array_unshift($email, 's');
			$result= $db->bindAbfrageResult($sql, $email);
			
			if(empty($result)){
				//noch nicht regitiert
				$fehler[]= 'Sie habe eine E-Mail-Adresse eingegeben, die nicht wiedererkannt werden kann.';
				
			}else {
				
				// Neues Passwort erstellen
				$kennwort = substr(md5(microtime()),0,6);//zufall neues kennwort
				$sql = "UPDATE Benutzer SET kennwort = ? WHERE benutzerName = ?";
				$params = array('ss',md5($kennwort),$result[0]->benutzerName);
				$db->bindAbfrage($sql, $params);
				
				// Email verschicken
				$empfaenger = $email['email'];
				$titel = "Neues Passwort";
				$mailbody = "Ihr neues Passwort lautet:\n\n".
							$kennwort."\n\n".
				           	"Ihr altes Passwort wurde gelöscht.";
				$header = "From: Viet.Dung.Tran@uni-duesseldorf.de\n";
				
				$abschiken = @mail($empfaenger, $titel, $mailbody, $header);
				
				if($abschiken){ //obsenden
					$messenger[]='Ihr neues Passwort wurde erfolgreich an Ihre Email-Adresse versandt.';
				} else { //server können nicht email senden
					$messenger[]='unbekannt Fehler ';//
				}
			}		
		}
			
	} // End if ($_POST['abschicken'])
	
	if(empty($messenger)){
		// Fehler ausgeben
		if(!empty($fehler)){
			echo "<ul id =\"fehler\">";
			foreach($fehler as $error) {
				echo "<li>{$error}</li>";
			}
			echo "</ul>";
			}
		// Form ausgeben
		printf('<div id ="form">
						<form action="%s" method="post">
							<label >Email : </label>
							<input type="text" name="email" size="25" />
							<input type="submit" name="abschicken" value="Abschicken" />					
						</form>
			        <div class="link">
			            <a href="registriert.php">Registrieren</a>
			        </div>
					</div><!-- END #form-->
				',$_SERVER['PHP_SELF']);
	}else{ //neues Passwort wurde erfolgreich  versandt.
		
		printf('<div id ="form">
					<label >%s</label>
			        <div class="link">
			            <a href="login.php">Einloggen</a>
			        </div>					
				</div>
		',$messenger[0]);
	}
		
	htmlFooter();
?>