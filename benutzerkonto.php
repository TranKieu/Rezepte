<?php
/**
 * @author Viet Dung Tran
 *
 * created on 14.02.2012
 */
	session_start();
	// Prüft, ob schon eingeloggt
	if(!isset($_SESSION['benutzerName'])) {
		header('location:login.php');
		exit();
	}
	
	$action= array(		'aedern'=>'Kennwort & email ändern',
						'bild'=>'ProfilBild ändern',
						'profil'=>'Profil bearbeiten',
						'losen'=>'Benutzerkonto lösen'
						);			
	
	include("autoload.php");
	include_once ('include/config.php');
	include_once ('include/header.inc.php');
	include_once ('include/footer.inc.php');
	include_once ('include/sidebar.inc.php');
	
	htmlHeader("Benutzerkontrollzentrum",$mainNav,$_SESSION['benutzerName'],$_SESSION['BeID']);
	
	$db = new dbExec(UDB_HOST, UDB_USER	, UDB_PASS, UDB_NAME);
	
	
	htmlSidebarBenuBild($_SESSION['BeID'],$db);
	htmlSidebarManager($action);
	htmlSidebarEnd();

	$fehler = array();
	$messenger = null;
	$daten = array();
	if(isset($_GET['act'])&& isset($action[$_GET['act']])){
		
	// standart Site benutzerkonto.php?act=aedern 
	if (isset($_GET['act'])&& $_GET['act']=='aedern') {
		
		$params = array('i',$_SESSION['BeID']);
		
		$sql = "SELECT email FROM Benutzer WHERE BeID =?";
		
		$result = $db->bindAbfrageResult($sql, $params);
		
		$email = $result[0]->email;
		
		if (isset($_POST['speichern'])&& $_POST['speichern']=='Änderung speichern') {
		
			if(!isset($_POST['kennwortAk']) || trim($_POST['kennwortAk'])=='')
				$fehler[]= "Bitte geben Sie Ihr aktuelles Kennwort ein.";
			else 	
				$kennwort = md5(htmlspecialchars(trim($_POST['kennwortAk'])));	
			
			// Prüft, ob ein kennwort eingegeben wurde
			if(!isset($_POST['kennwort']) || trim($_POST['kennwort'])=='')
				$fehler[]= "Bitte geben Sie Ihr neues Kennwort ein.";
			// Prüft, ob das kennwort mindestens 6 Zeichen enthält
			elseif (strlen(trim($_POST['kennwort'])) < 6)
				$fehler[]= "Ihr Kennwort muss mindestens 6 Zeichen lang sein.";
			// Prüft, ob eine kennwortWiederholung eingegeben wurde
			elseif(!isset($_POST['kennwortWieder']) ||trim($_POST['kennwortWieder'])=='')
				$fehler[]= "Bitte wiederholen Sie Ihr Kennwort.";
			// Prüft, ob das kennwort und die kennwortWiederholung übereinstimmen
			elseif (trim($_POST['kennwort']) != trim($_POST['kennwortWieder']))
				$fehler[]= "Ihre Kennwortwiederholung war nicht korrekt.";
			else
				$daten['kennwort'] = md5(htmlspecialchars(trim($_POST['kennwort'])));
			
			// Prüft, ob eine Email-Adresse eingegeben wurde
			if(!isset($_POST['email']) ||trim($_POST['email']) =='')
				$fehler[] ='Bitte fülle das erforderliche Feld Email aus';
			// Prüft, ob die Email-Adresse gültig ist
			elseif(!preg_match('$^[\w\.-]+@[\w\.-]+\.[\w]{2,4}$', trim($_POST['email'])))
				$fehler[] = "Ihre Email Adresse hat eine falsche Syntax.";
			
			// Prüft, ob eine emailWiederholung eingegeben wurde
			else if(!isset($_POST['emailWieder']) ||trim($_POST['emailWieder'])=='')
				$fehler[]= "Bitte wiederholen Sie Ihr Email.";
			// Prüft, ob das Email und die emailWiederholung übereinstimmen
			elseif (trim($_POST['email']) != trim($_POST['emailWieder']))
					$fehler[]= "Ihre emailWiederholung war nicht korrekt.";
			else
				$daten['email'] = htmlspecialchars(trim($_POST['email']));
		
			if(empty($fehler)){
				//bearbeiten
					$sql ='SELECT BeID FROM Benutzer WHERE benutzerName = ? AND kennwort = ?';
					$params = array('ss',$_SESSION['benutzerName'],$kennwort);
					$result = $db->bindAbfrageResult($sql, $params);
					
					$sql2 = 'SELECT BeID FROM Benutzer WHERE email =?';
					$arr = array('s',$daten['email']);
					$result2 = $db->bindAbfrageResult($sql2,$arr);
					
					if(empty($result)){
						$fehler[] ='Ihre Kennwort ist ungültig'; 
					}
					elseif(!empty($result2)){
						$fehler[] ="Dieser Email-Adresser ist bereits vergeben.";
					}else{
						
						
						$sqlandern = 'UPDATE Benutzer SET kennwort = ?,email = ?  WHERE BeID = ?';
						$params = array('ssi', $daten['kennwort'] ,$daten['email'],$_SESSION['BeID']);
						$db->bindAbfrage($sqlandern, $params);
						
						$messenger[] ='Ihr Kennwort würde schon geändert!';
					}
					
			} 
		}// end if post = speichern
		
		if(empty($messenger)){
			//fehler treffen, wieder Form ausgeben und fehler ausgebn
			if(!empty($fehler)){
		
				echo "<ul id =\"fehler\">";
				foreach($fehler as $error) {
					echo "<li>{$error}</li>";
				}
				echo "</ul>";
				}
		
				
				aedernAusgeben($email);
		
			}else{
				echo '<br><br>'.$messenger[0];
			}
			
	}// end if act = aedern
	
	//ProfilBild ändern site
	if (isset($_GET['act'])&& $_GET['act']=='bild') {
		
		if (isset($_POST['hochladen'])&& $_POST['hochladen']=='Bild Hochladen') {
			
			//daten validierung
			if(!isset($_FILES['bildpfofil'])||empty($_FILES['bildpfofil']['name']))		
				$fehler[]='Geben Sie eine Bild ein!';
			else 
				$bild = $_FILES['bildpfofil'];
			
			if(empty($fehler)){
				//bearbeiten
				$sqlName = 'SELECT FID,name FROM Bilder WHERE FID IN (SELECT FID FROM benutzerBilder WHERE BeID =?)';
				$params = array('i',$_SESSION['BeID']);
				$result = $db->bindAbfrageResult($sqlName, $params);
				$altename = $result[0]->name=='m1781852.jpg'? NULL: $result[0]->name;
				
				$idBild 	= $result[0]->FID;
				
				//bildhocladen 
				$up = new uploadFoto($bild,PFAD_BILD);
					
				if($up->schreiben()){
					$neuBild = $up->name;
					if(!$altename){//user haben immoment nur Standart bild
						$sqlInBild = 'INSERT INTO Bilder(name)VALUES (?)';
						$sqlUp ='UPDATE benutzerBilder SET FID =? WHERE BeID= ?';
						
						$paramInBild = array('s',$neuBild);
						$db->autocommit(FALSE);
						$wieder = FALSE;
						$ob = $db->bindAbfrage($sqlInBild, $paramInBild);
						if($ob['ausfuhrt']==1){
							//table rezBilder
							$upBild = array('ii',$ob['insert'],$_SESSION['BeID']);
							$aus = $db->bindAbfrage($sqlUp, $upBild);
							if($aus['ausfuhrt']!=1){
								$wieder = TRUE;
							}
						} else { 
							$wieder = TRUE;
						}
						
						if($wieder){
							$db->rollback();
							unlink(PFAD_BILD.$neuBild);
							$fehler[] = 'datenbank Fehler';
						}else{
							$db->commit();
														
							$messenger[]='Ihre Bild würde schon geändert';
						}
						
					}else{ // user hat nur stard bild m1781852.jpg

						$sqlUpZ ='UPDATE Bilder SET name = ? WHERE FID =?';
						
						$arrB =array('si',$neuBild,$idBild);
						
						$db->bindAbfrage($sqlUpZ, $arrB);
						//die alte bildprofil werden löschen
						unlink(PFAD_BILD.$altename);
						$messenger[]='Ihre Bild würde schon geändert';
					}
					
				}else{
					$fehler[] = $up->fehler;
				}
				
			}
			
		}//end if post hochladen	
			if(empty($messenger)){
				//fehler treffen, wieder Form ausgeben und fehler ausgebn
				if(!empty($fehler)){
			
					echo "<ul id =\"fehler\">";
					foreach($fehler as $error) {
						echo "<li>{$error}</li>";
					}
					echo "</ul>";
				}
			
				bildAusgben();
					
			}else{
			echo '<br><br>'.$messenger[0];
			}
	
		//goi bild ra neu name = m1781852.jpg thi cho alteName = null
	}//end if bild
	
	//Profil arbeiten site
	 if (isset($_GET['act'])&& $_GET['act']=='profil') {
		
	 	if (isset($_POST['profil'])&& $_POST['profil']=='Änderung speichern') {
	 		
	 		//daten validierung
	 		if(isset($_POST['geschlecht'], $_POST['PLZ'],$_POST['ort'],$_POST['land'],$_POST['tag'],$_POST['monate'],$_POST['jahr'])){
	 				
	 			if(!preg_match('/^\d+$/', trim($_POST['PLZ']))&& trim($_POST['PLZ'])!='') //PLZ muss eine Zahl sein
	 				$fehler[] ="Ihre PLZ hat eine falsche Syntax";
	 		
	 			$profil['geschlecht'] = htmlspecialchars(trim($_POST['geschlecht']));
	 			$profil['PLZ'] = htmlspecialchars(trim($_POST['PLZ']));
	 			$profil['ort'] = htmlspecialchars(trim($_POST['ort']));
	 			$profil['land'] = htmlspecialchars(trim($_POST['land']));
	 			$profil['gebuDatum'] = htmlspecialchars(trim($_POST['jahr'])) ."-".htmlspecialchars(trim($_POST['monate']))."-".htmlspecialchars(trim($_POST['tag']));
	 		}	
	 			
	 		if(empty($fehler)){
	 		//bearbeiten
	 		$sqlUpdate ='UPDATE Benutzer SET geschlecht =? ,PLZ =?,	Ort =?, Land =?,gebuDatum=? WHERE BeID =?';
			$profil['BeID'] =$_SESSION['BeID'];

			array_unshift($profil, 'sssssi');
			$arr=$db->bindAbfrage($sqlUpdate, $profil);
			if($arr['ausfuhrt']!=1){
				$fehler[]= 'datenbank Fehler';
			}else{
				$messenger[]='<br>Ihre Profil würde schon geändert';
			}
									        	
	 		
	 		}		
	 	}//end if post profil
	 	if(empty($messenger)){
	 		//fehler treffen, wieder Form ausgeben und fehler ausgebn
	 		if(!empty($fehler)){
	 				
	 			echo "<ul id =\"fehler\">";
	 			foreach($fehler as $error) {
	 				echo "<li>{$error}</li>";
	 			}
	 			echo "</ul>";
	 		}
	 				
	 			profilAusgben();
	 				
	 	}else{
	 			echo '<br><br>'.$messenger[0];
	 	}
	}//end if get frofil
	
	
	//Benutzerkonto Lösen site   loeschen
	if (isset($_GET['act'])&& $_GET['act']=='losen') {
		
		if (isset($_POST['loeschen'])&& $_POST['loeschen']=='Löschen') {
		
			//daten validierung
			if(!isset($_POST['kennwort']) || trim($_POST['kennwort'])=='')
				$fehler[]= "Bitte geben Sie Ihr aktuelles Kennwort ein.";
			else
				$kennwort = md5(htmlspecialchars(trim($_POST['kennwort'])));
				
			if(empty($fehler)){
				//bearbeiten
				$sql ='SELECT BeID FROM Benutzer WHERE benutzerName = ? AND kennwort = ?';
				$params = array('ss',$_SESSION['benutzerName'],$kennwort);
				$result = $db->bindAbfrageResult($sql, $params);
		
				if(empty($result)){
					$fehler[]='Ihre Kennwort ist ungültig';
				}else{ //benutzer lösen
					//benutzer lösen
					$sqldel ='DELETE FROM Benutzer WHERE BeID = ?';
					//rezept löschen
					$sqlRez ='UPDATE Rezept SET BeID =? WHERE BeID = ?';//BeID von standart User
					//bild Löschen
					$sqlBil ='DELETE FROM benutzerBilder WHERE BeID=?';
					//merkzettel löschen
					$sqlMerk ='DELETE FROM merkt WHERE BeID=?';
					//bewerten löschen
					$sqlBewe = 'DELETE FROM bewertet WHERE BeID=?';
					//kommentar löschen
					$sqlKomm = 'DELETE FROM kommentiert WHERE BeID=?';
					
					$arrDel =array('i',$_SESSION['BeID']);
					$arrUp =array('ii',1,$_SESSION['BeID']);// BeID= 1 = standart User
					$arrdelBild=array('i',$_SESSION['BeID']);
					$arrMerk =array('i',$_SESSION['BeID']);
					$arrBewe = array('i',$_SESSION['BeID']);
					$arrKomm = array('i',$_SESSION['BeID']);
					
					//$db->autocommit(FALSE);
					$wieder = TRUE;
					$a = $db->bindAbfrage($sqlRez,$arrUp);
					$b = $db->bindAbfrage($sqlBil, $arrdelBild);
					$c = $db->bindAbfrage($sqlMerk, $arrMerk);
					$e = $db->bindAbfrage($sqlBewe, $arrBewe);
					$g = $db->bindAbfrage($sqlKomm, $arrKomm);
					
					$d = $db->bindAbfrage($sqldel, $arrDel);
					
					
					if($a['ausfuhrt']==-1||$b['ausfuhrt']==-1||$c['ausfuhrt']==-1||$d['ausfuhrt']==-1||$e['ausfuhrt']==-1||$g['ausfuhrt']==-1){
						$wieder = FALSE;
					}
					if ($wieder) {
						$db->commit();
						header('Location:logout.php');
						$messenger[]="oki";
					}else{
						$db->rollback();
						$fehler[]='datenbank Fehler';
					}
					
				}
				
			}
		}//end if post hochladen
		if(empty($messenger)){
			//fehler treffen, wieder Form ausgeben und fehler ausgebn
			if(!empty($fehler)){
		
				echo "<ul id =\"fehler\">";
				foreach($fehler as $error) {
					echo "<li>{$error}</li>";
				}
				echo "</ul>";
		}
		
		losenAusgben();
		
		}else{
		echo '<br><br>'.$messenger[0];
		}

	}//end if get losen
	
	}else { //end if Get
		header('Location:benutzerkonto.php?act=aedern');
	}
	
	htmlFooter();
	
	function aedernAusgeben($email) {
		
		
		printf('<div id="form">
				<form action="" method="post">
					<fieldset class="fieldset" >
						<label for="">Geben Sie zuerst Ihre aktuelles Kennwort ein:</label><br>
						<input type="password" size="50" maxlength="20" name="kennwortAk">
					</fieldset><br>
					
					<fieldset class="fieldset">
					<legend>Kennwort ändern</legend>
						
						<label for="email">Neues Kennwort:</label><br>
						<input type="password" size="50" maxlength="80" name="kennwort" ><br>
						<span class="zeich">Bitte geben Sie Ihre neues Kennwort ein!</span><br>
						<br>
						<label for="email2">Neues Kennwort erneut eingeben:</label><br>
						<input type="password" size="50" maxlength="80" name="kennwortWieder"><br>
						<span class="zeich">Bitte wiederholen Sie zur Sicherheit die Eingabe Ihres Kennworts!</span><br>
					</fieldset><br>
					
					<fieldset class="fieldset">
					<legend>E-Mail-Adresse ändern </legend>
						
						<label for="email">E-mail Adresse</label><br>
						<input type="text" size="50" maxlength="80" name="email" value="%s"><br>
						<span class="zeich">Bitte geben Sie Ihre E-Mail Adresse ein!</span><br>
						<br>
						<label for="email2">E-Mail Adresse wiederholen</label><br>
						<input type="text" size="50" maxlength="80" name="emailWieder"value="%s"><br>
						<span class="zeich">Bitte wiederholen Sie zur Sicherheit die Eingabe Ihrer E-Mail-Adresse!</span><br>
					</fieldset>
			
					<br>
					
					<input type="submit" name="speichern" value="Änderung speichern" style="color: rgb(0, 96, 0); width: 160px; font: bold 12px Arial;">
					&nbsp;
					<input type="reset" value="Zurücksetzen" style="width: 160px; font: bold 12px Arial; color: rgb(160, 0, 0);">
					
				</form>			
			</div>',$email,$email);
	}
	
	function bildAusgben() {
		printf('
		<div id="form">
			<form action="" method="post" enctype="multipart/form-data" >
			<fieldset class="fieldset" >
				<legend>Bildprofil ändern</legend>
				<span class="zeich">Wählen sie die gewünschten Bilder und laden Sie.</span><br><br>
					<input type="hidden" name="MAX_FILE_SIZE" value="3000000" > 
				<table class="reTable" >
					<tr>
						<td><input type="file" name="bildpfofil" ></td>
					</tr>
					<tr>
						<td><br>
						<input name="hochladen" value="Bild Hochladen" style="color: red; width: 160px; font: bold 12px Arial;" type="submit">
						</td>
					</tr>
				</table>
			</fieldset>
		</form>			
	</div>	');
	}
	
	
	function losenAusgben() {
		printf('		<div id="form">
			<form action="" method="post">
			<fieldset class="fieldset" >
				<legend>Konto Löschen</legend>
				<span class="zeich">Geben Sie Ihre Kennwort ein.</span><br>
				<table class="reTable" >
					<tr>
						<td><input type="password" name="kennwort" ></td>
					</tr>
					<tr>
						<td><span id="loes">Nach dem Löschen können Sie nicht Ihr Konto wiederhestellen</span></td>
					</tr>
					<tr>
						<td>
						<input name="loeschen" value="Löschen" style="width: 160px; font: bold 12px Arial; color: rgb(160, 0, 0);" type="submit">
						</td>
					</tr>
				</table>
			</fieldset>
		</form>			
	</div>');
	}
	
	
	function profilAusgben() {
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
		        	<form id="form" action="" name="Registrierung" method="post" enctype="multipart/form-data">
					<fieldset>
						<legend>Benutzer Profil </legend>	
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
					<br>
					<input type="submit" name="profil" value="Änderung speichern" class="button" />
					<input type="reset" value="Zurücksetzen">
				</form>',$gebuForm );
	}
?>