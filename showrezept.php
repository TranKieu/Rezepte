<?php
/**
 * @author Viet Dung Tran
 *
 * created on 15.02.2012
 */
	session_start();
	// Prüft, ob schon eingeloggt
	if(!isset($_SESSION['benutzerName'])) {
		header('location:login.php');
		exit();
	}
	if(isset($_GET['reid'])&&is_numeric($_GET['reid'])){
		$ReID = $_GET['reid'];
	} else{
		header('Location:index.php');
	}
	include("autoload.php");
	include_once ('include/config.php');
	include_once ('include/header.inc.php');
	include_once ('include/footer.inc.php');
	include_once ('include/sidebar.inc.php');
	include_once ('include/function.php');

	htmlHeader("DBS Abschlussprojekt",$mainNav,$_SESSION['benutzerName'],$_SESSION['BeID']);
	
	$db = new dbExec(UDB_HOST, UDB_USER	, UDB_PASS, UDB_NAME);
	$newRez = newRezArray($db);
	htmlSidebarNewRez($newRez);
	$merkRez = merktArray($_SESSION['BeID'],$db);
	htmlSidebarMerkt($merkRez);
	htmlSidebarEnd();

	
	// es gibt POST für bewertet und kommentiert
	
	if(isset($_POST['kommentiert']) && $_POST['kommentiert']=='Kommentieren'){
		
		if(isset($_POST['kommentar']) && trim($_POST['kommentar'])!=''){
			
			$kommentieren = htmlspecialchars($_POST['kommentar']);
			
			$sqlKomm 	= 'INSERT INTO kommentiert(BeID, ReID, kommentar, zeit) VALUES(?,?,?, NOW())';
			$arrKomm	= array('iis', $_SESSION['BeID'], $ReID,$kommentieren);
			
			$db->bindAbfrage($sqlKomm, $arrKomm);	
		}
		
	}
	
	if(isset($_POST['bewertet']) && $_POST['bewertet']=='Bewerten'){
		
		if(isset($_POST['punkt']) && is_numeric($_POST['punkt'])){
			
			$p		= $_POST['punkt'];
			$sqlBewert	= 'INSERT INTO bewertet(BeID, ReID,punkt) VALUES(?,?,?)';
			$arrBewert	= array('iii',$_SESSION['BeID'], $ReID, $p );
			
			$db->bindAbfrage($sqlBewert, $arrBewert);
		}
	}

	if(isset($_POST['merktet']) && $_POST['merktet']=='Merken'){
		
		$sqlMerkt = 'INSERT INTO merkt(BeID, ReID ) VALUES(?,?)';
		$arrMerkt = array('ii',$_SESSION['BeID'], $ReID);
		
		$db->bindAbfrage($sqlMerkt, $arrMerkt);
	}
	//END IF POST
	
	$sqlRezept ="	SELECT 	re.BeID, re.Name, re.Zubereitungsbeschreibung,re.Portion,
							re.Zubereitungszeit, re.Ruhezeit, re.KochBackzeit,
							re.Schwierigkeitsgrad, re.Kosten, 
							IFNULL(re.Kalorien,'nicht angeben') AS Kalorien,
							SUBSTRING(re.Erstellungsdatum,1,10) as Erstellungsdatum,
							ge.name , 
							ROUND(AVG(be.punkt),1) as Punkt
					FROM (Rezept re LEFT JOIN bewertet be ON re.ReID = be.ReID) , zubereitGerate ge 
					WHERE re.ReID = ge.ReID AND re.ReID =? 
					GROUP BY re.ReID ";
	$arrRezept = array('i',$ReID);
	
	$result = $db->bindAbfrageResult($sqlRezept, $arrRezept);
	
	if($result){ //wenn eine Rezept in Datenbank existiert
	
		//ausgabe daten
		$reName 	=	$result[0]->Name;
		$reSchwie	=	$result[0]->Schwierigkeitsgrad;
		$reErstel	=	$result[0]->Erstellungsdatum;
		$rePunkt	=	empty($result[0]->Punkt)? 0 : $result[0]->Punkt;
		$rePortion	= 	$result[0]->Portion;
		$reZuBschrei=	$result[0]->Zubereitungsbeschreibung;
		$reZubeZeit =	$result[0]->Zubereitungszeit;
		$reRuZeit	=	$result[0]->Ruhezeit;
		$reKockZeit	=	$result[0]->KochBackzeit;
		$reKosten	= 	$result[0]->Kosten;
		$reKalorin	=	$result[0]->Kalorien==0?'nicht angaben':$result[0]->Kalorien;
		$reGeraet	=	$result[0]->name;
		
		
		$beid		=	$result[0]->BeID;
		$auto		=	showBeID($db, $beid);
		
		$bilde		= 	showBild($db, $ReID);
		
		//ob show button
		$showBewert = obBewertet($db, $_SESSION['BeID'], $ReID);
		$showMerktet= obMerktet($db, $_SESSION['BeID'], $ReID);

		//html aus geben
		
		printf('
			<div id ="rezeptshow">
					<h1>%s</h1>
					<div class ="comment-username" ><a href="mitglied.php?beid=%d">%s</a></div><br>
					<hr class="line">
					<div id ="bild"> 
		',$reName,$beid,$auto);
					
					for ($i = 0; $i < count($bilde); $i++) {
						printf('<img alt="%s" src="images/%s" width ="200" style="margin-left: 5px" >',$bilde[$i],$bilde[$i]);
					}
		
		printf( '</div> <!--END #bild--><div id="zutaten">	
					<h2 class="line">Zutaten für %d Portionen</h2>
					<table class="zutaten">			
						<tbody>
		',$rePortion);
		$zutaten = getZutat($db, $ReID);
		
		for ($i = 0; $i < count($zutaten); $i++) {
			printf('<tr>
						<td align="right">%d %s</td>
						<td>%s  %s</td>
					</tr>
			',$zutaten[$i]->menge, $zutaten[$i]->masseinheit, $zutaten[$i]->name, $zutaten[$i]->bemerkung);
		}
		
		printf('				</tbody></table>	<div id="bewertet">
									<form action ="" method="post" >
										<table cellpadding="0" cellspacing="0" >
											<tr>
												<td width = "70px">Punkt: </td>
												<td width = "60px" align="left"> %01.1f </td>
											</tr><tr><td>	
				',$rePunkt);
		//ob schon Bewertet
		if(!$showBewert){
			printf('
								<select name="punkt">
														<option value="null">Bitte wählen</option>
														<option value="1"> 1 </option>
														<option value="2"> 2 </option>
														<option value="3"> 3 </option>
														<option value="4"> 4 </option>
														<option value="5"> 5 </option>
													</select>
												</td>
												<td><input type="submit" name="bewertet" value="Bewerten" >
					');
		}else{
			echo 'bewertet';
		}
		
		echo '</td></tr><tr><td>';
		//ob schon merken
		
		if(!$showMerktet){
			echo '<input type="submit" name="merktet" value="Merken" >';
				
		}else{
			echo 'gemekrtet';
		}
				
				//,
				
		echo nl2br("</td></tr></table></form></div><!--END #bewertet--></div><!--END #zutaten-->
				<h2 class=\"line\">Zubereitung</h2>
				<div >{$reZuBschrei}</div>");
		printf ('<hr class="line">	
				<table cellpadding="0" cellspacing="0">
					<!-- Arbeitszeit -->
					<tbody><tr>
						<td><span class="n"><strong>Arbeitszeit:</strong></span></td>
						<td style="padding-left: 10px;" >ca. %d Min.</td>
					</tr>
					<!-- Koch-/Backzeit -->
					<tr>
						<td><span class="n"><strong>Koch-/Backzeit:</strong></span></td>
						<td style="padding-left: 10px;" >ca. %d Min.</td>
					</tr>
					<!-- Ruhezeit -->
					<tr>
						<td><span class="n"><strong>Ruhezeit:</strong></span></td>
						<td style="padding-left: 10px;" >ca. %d Min.</td>
					</tr>
					<!-- Schwierigkeitsgrad -->
					<tr>
						<td><span class="n"><strong>Schwierigkeitsgrad:</strong></span></td>
						<td style="padding-left: 10px;" >%s</span></td>
					</tr>
					<!-- Brennwert = kalorin-->
					<tr>
						<td><span class="n"><strong>Brennwert p. P.:</strong></span></td>
						<td style="padding-left: 10px;">%s</td>
					</tr>
					<tr>
						<td><span class="n"><strong>Kosten :</strong></span></td>
						<td style="padding-left: 10px;">%s</td>
					</tr>
					<!-- Erstellen-->
					<tr>
						<td><span class="n"><strong>Freischaltung:</strong></span></td>
						<td style="padding-left: 10px;">%s</td>
					</tr>
				</tbody></table>
			<hr class="line">
			<!--komentiert-->
			<br>	
			<form action ="" method="post" >
			<h2 class="line">Kommentieren</h2>
			<textarea name="kommentar" rows="10" cols="65" ></textarea>
			<br>
			<input type="submit" name="kommentiert" value="Kommentieren" ><br>
			</form>
		<div id="kommentare" style="margin-top: 5px;">
			<h2 class="line" style="margin-bottom: 15px;">Kommentare anderer Nutzer</h2>
		',$reZubeZeit,$reKockZeit,$reRuZeit,$reSchwie,$reKalorin,$reKosten, $reErstel);

		//show Komenntar
		$kommentar = getKommentar($db, $ReID);
		
		for ($i = 0; $i < count($kommentar); $i++) {
			$KomAuto		=	showBeID($db, $kommentar[$i]->BeID);
			
			printf('<div class="comment-username"><a href="mitglied.php?beid=%d">%s</a>&nbsp; sagt: &nbsp;</div>
				<div class="comment-date">%s</div>
				<div class="clear"></div>
				<div class="comment-body-inner">%s</div><br>
			',$kommentar[$i]->BeID,$KomAuto,$kommentar[$i]->zeit,$kommentar[$i]->kommentar );
		}
		
		echo '	</div>	</div>';
		
		
		
	}//END if result
	else {
		echo "<ul id =\"fehler\"><li>die Rezept ={$ReID} existiert nicht in DatenBank </li></ul>";
	}

	htmlFooter();
?>