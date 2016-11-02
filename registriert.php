<?php
/**
 * @author Viet Dung Tran
 *
 * created on 14.02.2012
 */
	session_start();
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
	//messenger ob registierung erfolgen
	$messenger = null;
	//eingabe Daten
	$registrieren = array();

	if(isset($_POST['submit']) AND $_POST['submit']=='Registrieren'){
		 
	
		// Prüft, ob ein benutzerName eingegeben wurde
		if(!isset($_POST['benutzerName']) || trim($_POST['benutzerName']) ==''){
			 
			$fehler[] ='Bitte fülle das erforderliche Feld "Benutzername" aus.';
		} else {
			$registrieren['benutzerName'] = htmlspecialchars(trim($_POST['benutzerName']));
		}
	
		// Prüft, ob ein kennwort eingegeben wurde
		if(!isset($_POST['kennwort']) || trim($_POST['kennwort'])=='')
			$fehler[]= "Bitte geben Sie Ihr kennwort ein.";
		// Prüft, ob das kennwort mindestens 6 Zeichen enthält
		elseif (strlen(trim($_POST['kennwort'])) < 6)
			$fehler[]= "Ihr Kennwort muss mindestens 6 Zeichen lang sein.";
		// Prüft, ob eine kennwortWiederholung eingegeben wurde
		elseif(!isset($_POST['kennwortWiederholung']) ||trim($_POST['kennwortWiederholung'])=='')
			$fehler[]= "Bitte wiederholen Sie Ihr Kennwort.";
		// Prüft, ob das kennwort und die kennwortWiederholung übereinstimmen
		elseif (trim($_POST['kennwort']) != trim($_POST['kennwortWiederholung']))
			$fehler[]= "Ihre Kennwortwiederholung war nicht korrekt.";
		else
		$registrieren['kennwort'] = md5(htmlspecialchars(trim($_POST['kennwort'])));
	
		// Prüft, ob eine Email-Adresse eingegeben wurde
		if(!isset($_POST['email']) ||trim($_POST['email']) =='')
			$fehler[] ='Bitte fülle das erforderliche Feld "Email" aus';
		// Prüft, ob die Email-Adresse gültig ist
		elseif(!preg_match('$^[\w\.-]+@[\w\.-]+\.[\w]{2,4}$', trim($_POST['email'])))
			$fehler[] = "Ihre Email Adresse hat eine falsche Syntax.";
	
		// Prüft, ob eine emailWiederholung eingegeben wurde
		else if(!isset($_POST['emailWiederholung']) ||trim($_POST['emailWiederholung'])=='')
			$fehler[]= "Bitte wiederholen Sie Ihr Email.";
		// Prüft, ob das Email und die emailWiederholung übereinstimmen
		elseif (trim($_POST['email']) != trim($_POST['emailWiederholung']))
			$fehler[]= "Ihre emailWiederholung war nicht korrekt.";
		else
			$registrieren['email'] = htmlspecialchars(trim($_POST['email']));
	
	
		// Prüft, ob eine name eingegeben wurde
		if(isset($_POST['name']) && !is_array($_POST['name']) && trim($_POST['name']) !=''){
	
			$registrieren['name'] = htmlspecialchars(trim($_POST['name']));
		} else {
			$fehler[] ='Bitte fülle das erforderliche Feld "Name" aus';
		}
	
		// Prüft, ob eine vorname eingegeben wurde
		if(isset($_POST['vorname']) && !is_array($_POST['vorname']) && trim($_POST['vorname']) !=''){
	
			$registrieren['vorname'] = htmlspecialchars(trim($_POST['vorname']));
		} else {
			$fehler[] ='Bitte fülle das erforderliche Feld "Vorname" aus';
		}
		//Freiwillige Angaben
		if(isset($_POST['geschlecht'], $_POST['PLZ'],$_POST['ort'],$_POST['land'],$_POST['tag'],$_POST['monate'],$_POST['jahr'])){
			
			if(!preg_match('/^\d+$/', trim($_POST['PLZ']))&& trim($_POST['PLZ'])!='') //PLZ muss eine Zahl sein
				$fehler[] ="Ihre PLZ hat eine falsche Syntax";
			 
			$registrieren['geschlecht'] = htmlspecialchars(trim($_POST['geschlecht']));
			$registrieren['PLZ'] = htmlspecialchars(trim($_POST['PLZ']));
			$registrieren['ort'] = htmlspecialchars(trim($_POST['ort']));
			$registrieren['land'] = htmlspecialchars(trim($_POST['land']));
			$registrieren['gebuDatum'] = htmlspecialchars(trim($_POST['jahr'])) ."-".htmlspecialchars(trim($_POST['monate']))."-".htmlspecialchars(trim($_POST['tag']));
		}
	
		if(empty($fehler)){
			//Prüft, ob Benutzer eindeutig ist
			$sql ="SELECT benutzerName FROM Benutzer WHERE benutzerName = ?";
			$params = array('s',$registrieren['benutzerName']);
			if($db->bindAbfrageResult($sql, $params))
				$fehler[] ="Dieser Benutzer ist bereits vergeben.";
		
			//Prüft, ob Email eindeutig ist
			$sql ="SELECT email FROM Benutzer WHERE email = ?";
				$params = array('s',$registrieren['email']);
			if($db->bindAbfrageResult($sql, $params))
				$fehler[] ="Dieser Email-Adresser ist bereits vergeben.";
		} 
		
		// Prüft, ob Fehler aufgetreten sind
		if(empty($fehler)){
			
			// Daten in die Datenbanktabelle einfügen
			 
			$sql = 'INSERT INTO Benutzer(	benutzerName,
	        									kennwort,
	        									email,
												name ,
												vorname , 
												geschlecht ,											
												PLZ,
												Ort, 
												Land,
									        	gebuDatum, 
									        	mitliegdSeit) VALUES(?,?,?,?,?,?,?,?,?,STR_TO_DATE(?,"%Y-%m-%d"),CURDATE()	)';
			array_unshift($registrieren, 'ssssssssss');
			$ausfuhrt = $db->bindAbfrage($sql, $registrieren);
			if($ausfuhrt['ausfuhrt']==1){
				$messenger= "<br>  Vielen Dank!\n<br>".
	        		     	"Ihr Accout wurde erfolgreich erstellt.\n<br>".
	        		     	"Sie können sich nun Ihren Profil ändern.\n<br>".
	        		     	"<a href=\"benutzerkonto.php?act=aedern\">Kontrollzentrum</a>\n".
							"";
				$_SESSION['BeID']	= $ausfuhrt['insert'];
				$_SESSION['benutzerName'] = $registrieren['benutzerName'];
				$sqlbild ='INSERT INTO benutzerBilder( FID, BeID) VALUES(1,?) ';
				$bild = array('i',$ausfuhrt['insert']);
				$db->bindAbfrage($sqlbild, $bild); //standartBild hinzufügen
			}
		}
	}// END if($_POST['submit'])
	
	// Registerung Form ausgeben		
		
		htmlHeader("Registrierung");
		
		htmlSidebarNewRez();
		htmlSidebarMerkt();
		htmlSidebarEnd();
		
		if(empty($messenger)){
			//fehler ausgeben
			if(!empty($fehler)){
				
					echo "<br><span>Ihr Account konnte nicht erstellt werden.</span><br>\n";
					echo "<ul id =\"fehler\">";
					foreach($fehler as $error) {
						echo "<li>{$error}</li>";
					}
					echo "</ul>";
			}
			
			$Monate = array(0  => "Monate",
							1  => "Januar",
							2  => "Februar",
							3  => "März",
							4  => "April",
							5  => "Mai",
							6  => "Juni",
							7  => "Juli",
							8  => "August",
							9  => "September",
							10 => "Oktober",
							11 => "November",
							12 => "Dezember");
				
			$gebuForm ="";
			//für Jahr
			$gebuForm .= "\t<select name=\"jahr\">\n";
			$gebuForm .= "\t<option value=\"0\">Jahr</option>\n";
			for($i=1970;$i<=2000;$i++) {
				$gebuForm .= "\t<option value=\"". $i ."\">". $i ."</option>\n";
			}
			$gebuForm .="</select>\n";
			//für Monate
			$gebuForm .="\n<select name=\"monate\">\n";
			for($i=0;$i<=12;$i++) {
				$gebuForm .="\t<option value=\"". $i ."\">". $Monate[$i] ."</option>\n";
			}
			$gebuForm .= "</select>\n";
			//für Tag
			$gebuForm .= "\t<select name=\"tag\">\n";
			$gebuForm .= "\t<option value=\"0\">Tag</option>\n";
			for($i=1;$i<=31;$i++) {
				$gebuForm .= "\t<option value=\"". $i ."\">". $i ."</option>\n";
			}
			$gebuForm .="</select>\n";
			
			printf('
		        	<form id="form" action="%s" name="Registrierung" method="post" enctype="multipart/form-data">
				
							<label for="benutzerName" >Benutzername:<strong id="stern" >*</strong></label>
							<input  maxlength="50" size="45" name="benutzerName" >					
					<fieldset>
						<legend>Kennwort <strong id="stern" >*</strong></legend>
						<table border="0" cellspacing="2" cellpadding="2">
							<tr>
								<td><label for="kennwort" title="das Kennwort muss mindestens 6 Zeichen enthälten">Kennwort:</label></td>
								<td><label for="kennwortWiederholung">Kennwort erneut eingeben:</label></td>
							</tr>
							<tr>
								<td><input  type="password" name="kennwort" maxlength="50" size="32" ></td>
								<td><input  type="password" name="kennwortWiederholung" maxlength="50" size="32" ></td>
							</tr>
					 	</table>																					
					</fieldset>
			
					<fieldset>
						<legend> EMail <strong id="stern" >*</strong></legend>
							<table border="0" cellspacing="2" cellpadding="2">
								<tr>
									<td><label for="email">EMail-Adresse:</label></td>
									<td><label for="emailWiederholung">EMail-Adresse erneut eingeben:</label></td>
								</tr>
								<tr>
									<td><input id="email" name="email" maxlength="50" size="32"></td>
									<td><input id="emailWiederholung" name="emailWiederholung" maxlength="50" size="32" ></td>
								</tr>
							</table>
					</fieldset>
					<fieldset>
						<legend>Benutzer Profil </legend>	
							<table border="0" cellspacing="2" cellpadding="2">
								<tr>
									<td><label for="name">Name:<strong id="stern" >*</strong></label></td>
									<td><label for="vorname">Vorame:<strong id="stern" >*</strong></label></td>	
								</tr>
								<tr>
									<td><input id="name" name="name" maxlength="50" size="32"></td>
									<td><input id="vorname" name="vorname" maxlength="50" size="32"></td>
								</tr>
							</table>
							<table>
								<tr>
									<td><label for="geschlecht" size="25">Geschlecht : </label></td>
									<td>
									<select name="geschlecht">
										<option value="0">Bitte wählen</option>
										<option value="ma">Männlich</option>
										<option value="we">Weiblich</option>
									</select>
									</td>
								</tr>
								<tr>
									<td ><label for="gebuDatum">Geburtsdatum :</label></td>
									<td>
										%s
									</td>
								</tr>
								<tr>
									<td ><label for="PLZ">PLZ:</label></td>
									<td>
										<input maxlength="5" name="PLZ" size="7" type="text" />
									</td>
								</tr>
								<tr>
									<td ><label for="ort">Ort:</label></td>
									<td>
										<input maxlength="50" name="ort" size="45" type="text" />
									</td>
								</tr>
								<tr>
									<td ><label for="land">Land:</label></td>
									<td>
										<input name="land" size="45" type="text" value="Deutschland" />
									</td>
								</tr>
							</table>
					</fieldset>
					
					<strong id="stern" >*</strong> Bitte alle Felder ausfüllen!<br><br>
					<input type="submit" name="submit" value="Registrieren" class="button" />
					<input type="reset" value="Zurücksetzen">
				</form>',$_SERVER['PHP_SELF'],$gebuForm );
		} else{ // Registeirung erfolgen
			echo $messenger;
		}
	
	htmlFooter();

?>