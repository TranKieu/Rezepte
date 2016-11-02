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

	include("autoload.php");
	include("include/config.php");
	include_once ('include/header.inc.php');
	include_once ('include/footer.inc.php');
	include_once ('include/sidebar.inc.php');
	
	htmlHeader('Rezept Anlegen',$mainNav,$_SESSION['benutzerName'],$_SESSION['BeID']);
	
	$db = new dbExec(UDB_HOST, UDB_USER	, UDB_PASS, UDB_NAME);
	
	$newRez = newRezArray($db);
	htmlSidebarNewRez($newRez);
	$merkRez = merktArray($_SESSION['BeID'],$db);
	htmlSidebarMerkt($merkRez);
	htmlSidebarEnd();
	
	
	// Fehlerarray anlegen
	$fehler = array();
	//messenger ob fertig ist
	$messenger = null;
	//eingabe Daten
	$rezept = array(); //für Rezept
	
	$rezBilder = array(); //rezBilder
	$zutaten = array(); //Zutaten
	
	
	if(isset($_POST['neueRezept']) AND $_POST['neueRezept']=='Rezept speichern'){
		
		// Prüft eingegengen Daten 
		
		if(!isset($_POST['rezeptName']) || trim($_POST['rezeptName']=='')){
			$fehler[]='Bitte geben Sie Rezeptname ein.';
		}else{
			$rezept['rezeptName'] = trim($_POST['rezeptName']);
		}
		
		if(!isset($_POST['zubereitGerate']) || trim($_POST['zubereitGerate']=='')){
			$fehler[]='Bitte geben Sie Zubereitungsmethoden/Geräte ein.';
		}else{
			$zubereitGerate = trim($_POST['zubereitGerate']);
		}
		
		if(!isset($_POST['rezeptZubereitung']) || trim($_POST['rezeptZubereitung']=='')){
			$fehler[]='Bitte geben Sie Rezeptzubereitung ein.';
		}else{
			$rezept['rezeptZubereitung'] = trim($_POST['rezeptZubereitung']);
		}
		
		if(!is_numeric($_POST['zutatMenge'][0]) ||trim($_POST['zutatName'][0])==''||($_POST['massEinheit'][0]=='Leer')){
			$fehler[]='Bitte geben Sie Zutat ein. Jedes Rezept braucht mindesten ein Zutat';
		}else{
			$zutatMenge  = $_POST['zutatMenge'];
			$massEinheit = $_POST['massEinheit'];
			$zutatName	 = $_POST['zutatName'];
			$bemerkung	 = $_POST['bemerkung'];
		}
		
		if(!isset($_POST['VStunden'], $_POST['VMinuten']) ||(!is_numeric($_POST['VStunden'])&&!is_numeric($_POST['VMinuten']))){
			$fehler[] ='Bitte geben Sie Zubereitungszeit ein. Jedes Rezept braucht Zeit zum Kochen';
		} else{
				$rezept['Zubereitungszeit'] = ($_POST['VStunden'])*60 + $_POST['VMinuten'];
		}
		
		if(!isset($_POST['RStunden'], $_POST['RMinuten']) ||(!is_numeric($_POST['RStunden'])&&!is_numeric($_POST['RMinuten']))){
			$fehler[] ='Bitte geben Sie Ruhezeit ein. Jedes Rezept braucht Zeit zum Kochen';
		} else{
				$rezept['Ruhezeit']			= ($_POST['RMinuten']) + $_POST['RStunden']*60 ;	
		}
		
		if(!isset($_POST['KStunden'], $_POST['KMinuten']) ||(!is_numeric($_POST['KStunden'])&&!is_numeric($_POST['KMinuten']))){
			$fehler[] ='Bitte geben Sie Koch-/Backzeit ein. Jedes Rezept braucht Zeit zum Kochen';
		} else{
				$rezept['KochBackzeit'] = ($_POST['KStunden'])*60 + ($_POST['KMinuten']);
		}
		
		if(isset($_POST['rezeptPortionen'], $_POST['rezeptKcal'],$_POST['schwierigkeitsgrad'],$_POST['kosten'])){
			
			$rezept['rezeptPortionen'] = is_numeric($_POST['rezeptPortionen'])?trim($_POST['rezeptPortionen']):1;
			$rezept['rezeptKcal'] = trim($_POST['rezeptKcal']) + 0 ;//konvertiren nach INT
			$rezept['schwierigkeitsgrad'] = trim($_POST['schwierigkeitsgrad']);
			$rezept['kosten'] = trim($_POST['kosten']);
			
		}	

		
		if(isset($_FILES['bildEin'])&&!empty($_FILES['bildEin']['name'])){
			//bildhocladen und eine thumbnail erstellen
			$up = new uploadFoto($_FILES['bildEin'],PFAD_BILD);
			
			if($up->schreiben()){
				$rezBilder[] = $up->name;
			}else{
				$fehler[] = $up->fehler;
			}
			
			
		}
			
		if(isset($_FILES['bildZwei'])&&!empty($_FILES['bildZwei']['name'])){
			
			$up2 = new uploadFoto($_FILES['bildZwei'],PFAD_BILD);
				
				if($up2->schreiben()){
					$rezBilder[] = $up2->name;
				}else{
					$fehler[] = $up2->fehler;
				}
			
		}
			
		if(isset($_FILES['bildDrei'])&&!empty($_FILES['bildDrei']['name'])){
			
					
			$up3 = new uploadFoto($_FILES['bildDrei'],PFAD_BILD);
				
				if($up3->schreiben()){
					$rezBilder[] = $up3->name;
				}else{
					$fehler[] = $up3->fehler;
				}
		}
			
		//Fehlerfrei => Rezept zu datenbank speichen
		if(empty($fehler)){
		
		//abfrage
		// Rezept
		$sqlRezept = 'INSERT INTO Rezept( 	BeID, 
											Name,
											Zubereitungsbeschreibung,
											Zubereitungszeit,
											Ruhezeit,
											KochBackzeit,
											Portion,
											Kalorien,
											Schwierigkeitsgrad,
											Kosten,
											Erstellungsdatum ) VALUES (?,?,?,?,?,?,?,?,?,?,NOW())';

		//zubereitGerate
		$sqlGeraet = 'INSERT INTO zubereitGerate(ReID,name) VALUES (?,?)';

		//Bilder
		$sqlInBild = 'INSERT INTO Bilder(name )VALUES (?)';
		//rezBilder
		$sqlBild ='INSERT INTO rezBilder(FID,ReID) VALUES(?,?)';
		
		//Zutaten
		$sqlZutat ='INSERT INTO Zutaten(ReID, name, menge , masseinheit, bemerkung) VALUES(?,?,?,?,?) ';

		//Rezept parameter
		array_unshift($rezept,'isssssiiss' ,$_SESSION['BeID']);
		
		
		for($i= 0;$i<10; $i++){
			if(is_numeric($zutatMenge[$i]) &&($zutatName[$i]!='')&&($massEinheit[$i]!='Leer')){
				$zutaten[] = array($zutatName[$i],$zutatMenge[$i],$massEinheit[$i],$bemerkung[$i]);
			}	
		}
		
		/* disable autocommit */
		$db->autocommit(FALSE);
		$wieder = TRUE;
		$ausfuhrt = $db->bindAbfrage($sqlRezept, $rezept);
		
		if ($ausfuhrt['ausfuhrt']==1) {
			
			$gePara = array('is',$ausfuhrt['insert'],$zubereitGerate);
			$ob1 = $db->bindAbfrage($sqlGeraet, $gePara);
			
			if($ob1['ausfuhrt']==1){
			
			
			for($i=0;$i<count($zutaten);$i++){
				
				$pramas = $zutaten[$i];
				
				array_unshift($pramas,'isiss',$ausfuhrt['insert']);
				
				$obaf = $db->bindAbfrage($sqlZutat, $pramas);
				if($obaf['ausfuhrt']!=1){ //wenn fehler treffen
					$wieder=FALSE;
					break;
				}				
			}// end Zutaten hinzufügen
	
			if($wieder && !empty($rezBilder)){
				
				for($i=0;$i < count($rezBilder);$i++){
					
					//Bilder in Datenbank speichen
					$params = array('s',$rezBilder[$i]);

					$ob = $db->bindAbfrage($sqlInBild, $params);
					
					if($ob['ausfuhrt']==1){ 
						//table rezBilder
						$arr = array('ii',$ob['insert'],$ausfuhrt['insert']);
						$db->bindAbfrage($sqlBild, $arr);
						
					}else{
						$wieder=FALSE;
						
					}
				}
			}
			
			} //endif $sqlGeraet
			else{ $wieder = FALSE;}
		}// end if $sqlRezept
		
		
			
		if($wieder){
			$db->commit();
			$messenger ="<br>Vielen Dank!<br>\n Ihre Rezept wurde erfolgreich erstellt.";
		}else{
			$db->rollback(); //wenn fehler treffen alles lösen
			$fehler[]='Datenbank fehler';
			
		}

		
		
	
			
		
		}//END if Fehler
		
	}// END if($_POST['neueRezept']) = button drücken 

	if(empty($messenger)){ 
		//formular ausgeben
		
		if(!empty($fehler)){ //fehler ausgeben
		
			//bild löschen
			for($i=0;$i < count($rezBilder);$i++){
				$filename =PFAD_BILD.$rezBilder[$i];
				
				unlink($filename);
				
			}
			
			echo "<br><span>Ihr Rezept konnte nicht angelegt werden.</span><br>\n";
			echo "<ul id =\"fehler\">";
			foreach($fehler as $error) {
				echo "<li>{$error}</li>";
				
			}
			echo "</ul>";
					} 
					
		$zutatFrom = '<tr>
						<td>
							<input name="zutatMenge[]" size="10" type="Text">
						</td>
						<td>
						<select name="massEinheit[]">
								<option value="Leer">Bitte wählen</option>
								<option value="gam">gam</option>
								<option value="kgam">kg</option>
								<option value="ml">ml</option>
								<option value="lit">Liter</option>
								<option value="el">EL</option>
								<option value="tl">TL</option>
								<option value="pck">Pck.</option>
								<option value="stk">Stk.</option>
								<option value="bund">Bund</option>
								<option value="sprit">Spritzer</option>
								<option value="wurf">Würfel</option>
								<option value="scheib">Scheibe/n</option>
								<option value="zehe">Zehe/n</option>
							</select>
						</td>
						<td>
							<input name="zutatName[]" size="15" type="Text">
						</td>
						<td>
							<input name="bemerkung[]" size="35" type="Text">
						</td>
				</tr>
		';
		$link =$_SERVER['PHP_SELF'];
		
		echo '
		<div id="rezeptAnlegen"> 			
			<h1>Rezepteingabe </h1>				
				<form name="rezept" action="'.$link.' " method="POST" enctype="multipart/form-data">
					
					<h2 class="line" >Rezeptname: <strong id="stern" >*</strong> </h2>		
					<span class="zeich">z.B. Adventsbrote mit Äpfeln, Trockenfrüchten und Nüssen</span><br>		
					<input name="rezeptName" size="40" style="width: 500px; margin-left:10px;" maxlength="350" type="text">
					<br>
					
					<h2 class="line">Portionen</h2>
					<span class="zeich">Das Rezept ist ausgelegt für <input name="rezeptPortionen" size="2" type="Text"> Personen / Portionen</span>
					<br>
					
					<h2 class="line">Zubereitungsmethoden/Geräte <strong id="stern" >*</strong> </h2>
					<span class="zeich">wie z.B. Grill, Dünsten, Mikrowelle, Wok</span>
					<input name="zubereitGerate" size="20" style="margin-left:10px;"  type="Text">
					<br>
						
					<h2 class="line">Zutaten und Mengenangaben<strong id="stern" >*</strong></h2>					
					<table class="reTable">
						<tr>
							<td  valign="top" width="15%">
								<h2 class="line">Anzahl</h2>	
							</td>
							<td  valign="top" width="15%">
								<h2 class="line">Maßeinheit</h2>	
							</td>
							<td  valign="top" width="20%">
								<h2 class="line">Zutat</h2>	
							</td>
							<td  valign="top" width="50%">
								<h2 class="line">Bemerkungen</h2>	
							</td>
						</tr>			
		';
		
		for($i=0; $i<10;$i++){
			
			echo $zutatFrom;
		}
		
		
		echo '
					</table>
						<hr  class ="line">
						<br>
						
						<h2 class="line">Rezeptzubereitung<strong id="stern" >*</strong></h2>
						<span class="zeich">Wie kochen Sie?</span><br>
						<textarea name="rezeptZubereitung" rows="15" cols="40" ></textarea>
						<br>
											
						<table class="reTable" cellpadding="0" cellspacing="0">
							<tr>
								<td valign="top" width="33%">
									<h2 class="line">Arbeitszeit<strong id="stern" >*</strong></h2>
									Geben Sie die Zeit in Stunden und Minuten ein die das Rezept zur Vorbereitung braucht:
									<br>
									<b>Zeit: </b><input name="VStunden" size="2" type="Text"> Std.<input name="VMinuten" size="2" type="Text"> Min.
								</td>
								<td valign="top" width="33%">
									<h2 class="line">Koch-/Backzeit<strong id="stern" >*</strong></h2>
									Geben Sie die Zeit in Stunden und Minuten ein die das Rezept zur <strong>reinen</strong> Zubereitung braucht:
									<br>
									<b>Zeit: </b><input name="KStunden" size="2" type="Text"> Std.<input name="KMinuten" size="2" type="Text"> Min.
								</td>
								<td valign="top" width="33%">
									<h2 class="line">Ruhezeit<strong id="stern" >*</strong></h2>
									Geben Sie hier die Gesamtzeit der Ruhezeit ein. z.B. die Dauer des Einziehens bei Bohnen.
									<br>
									<b>Zeit: </b><input name="RStunden" size="2" type="Text"> Std.<input name="RMinuten" size="2" type="Text"> Min.	
								</td>
							</tr>
						</table>
						<hr  class ="line"><br>
								
						<table class="reTable">
							<tr>
								<td  valign="top" width="33%">
									<h2 class="line">Kalorien</h2>
									Anzahl der Kalorien pro Portion (z.B. "378" Kcal)
									<input name="rezeptKcal" size="5" type="Text"> Kcal pro Portion	
								</td>
								<td  valign="top" width="33%">
									<h2 class="line">Schwierigkeitsgrad</h2> Die Zubereitung ist:
									<select name="schwierigkeitsgrad">
										<option selected="selected" value="simpel">simpel</option>
										<option value="normal">normal</option>
										<option value="pfiffig">pfiffig</option>
									</select>	
								</td>
								<td  valign="top" width="33%">
									<h2 class="line">Kosten</h2> Die Kosten ist:
									<select name="kosten">
										<option selected="selected" value="preiswert">preiswert</option>
										<option value="angemessen">angemessen</option>
										<option value="teuer">teuer</option>
									</select>	
								</td>
							</tr>
						</table>
						<hr  class ="line"><br>
						<h2 class="line">Rezeptbild hochladen</h2>
						<span class="zeich">Wählen sie die gewünschten Bilder und laden Sie.</span><br>
						<input type="hidden" name="MAX_FILE_SIZE" value="3000000" > 
						<table class="reTable"  width="100%">
							<tr>
								<td><input type="file" name="bildEin" ></td>
							</tr>
							<tr>
								<td><input type="file" name="bildZwei" ></td>
							</tr>
							<tr>
								<td><input type="file" name="bildDrei" ></td>
							</tr>
						</table><br>
						<hr  class ="line"><br>
						<table class="reTable"  width="100%">
							<tr>
								<td align="center">
									<input name="abort" value="Anlegen abbrechen" style="width: 160px; font: bold 12px Arial; color: rgb(160, 0, 0);"type="reset">
									&nbsp;
									<input name="neueRezept" value="Rezept speichern" alt="Speichern des Rezeptes" style="color: rgb(0, 96, 0); width: 160px; font: bold 12px Arial;" type="submit">
									</td>
							</tr>	
						</table>
				</form> <!--end Form = rezeptAnlegen-->
			</div><!-- END #rezeptAnlegen  -->
		';
		
		
	}else{// 
		//messenger ausgeben 
		
		echo $messenger;
	}

	htmlFooter();
	// test inject  vi ko co htmlspecialchars(
?>