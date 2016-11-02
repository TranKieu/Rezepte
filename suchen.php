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
	include_once ('include/function.php');
	
	htmlHeader('Rezept Anlegen',$mainNav,$_SESSION['benutzerName'],$_SESSION['BeID']);
	
	$db = new dbExec(UDB_HOST, UDB_USER	, UDB_PASS, UDB_NAME);
	
	$newRez = newRezArray($db);
	htmlSidebarNewRez($newRez);
	$merkRez = merktArray($_SESSION['BeID'],$db);
	htmlSidebarMerkt($merkRez);
	htmlSidebarEnd();
	$ergebnis = array();
	$fehle ='';
	
	$geraete = getGraet($db);
	
	printf('<div class="profil_kochbuch">
					<div id="">
						<form action="%s" method="post" >
						<table border="0" cellpadding="0" cellspacing="0">
							<tbody><tr>
								<td style ="padding-left: 5px; padding-top: 10px;">
									Name des Rezept: <br><input type= "text" name="suchenname">
							    </td>
							    <td  style ="padding-left: 10px; padding-top: 10px;">
									<br><input type="checkbox"  value = "mitbild" name="mitbild" >
								</td>
								<td  style ="padding-top: 10px;padding-right: 5px;"><br> Mit Bild
								</td>
								<td style ="padding-top: 10px;"><br> <select name="geraet" >
								<option value="null">Geräte verwenden</option> 
		',$_SERVER['PHP_SELF']);
	
	for ($i = 0; $i < count($geraete); $i++) {
		printf('<option value="%s">%s</option> ',$geraete[$i]->name,$geraete[$i]->name);
	}
	
	echo'				</select ></td>
								<td style ="padding-left: 5px; padding-top: 10px;">
									Zutat :<br><input type= "text" name="suchzutat" size="10">
							    </td>
							    <td style ="padding-left: 5px; padding-top: 10px;">
									Dauer kleine als:<br><input type= "text" name="suchdauer" size="10"> Min
							    </td>
								<td style ="padding-left: 5px; padding-top: 10px;"><br>
									<input type="submit" name="suchen" value="Suchen" style="color:#4F910D">
								</td>
							</tr></tbody>
						</table>
						</form>	
					</div>
				';
		
	$sqlSuchen = 'SELECT DISTINCT	re.ReID, re.Name
					FROM Rezept re , zubereitGerate ge, Zutaten zu 
					WHERE re.ReID = ge.ReID AND re.ReID = zu.ReID   
					';
	
	if(isset($_POST['suchen']) AND $_POST['suchen']=='Suchen') {
		
		//name
		if (isset($_POST['suchenname']) AND trim($_POST['suchenname'])!='') {
			$suchname = htmlspecialchars($_POST['suchenname']);
			$sqlSuchen .= ' AND re.Name LIKE \'%'.$suchname.'%\'';
		}
		
		//zutate
		if (isset($_POST['suchzutat']) AND trim($_POST['suchzutat'])!='') {
			$suchzutat = htmlspecialchars($_POST['suchzutat']);
			$sqlSuchen .= ' AND re.name LIKE \'%'.$suchzutat.'%\'';
		}
		
		// Zeit
		if(isset($_POST['suchdauer']) AND is_numeric($_POST['suchdauer'])){
			$suchdauer = $_POST['suchdauer'];
			$sqlSuchen .=' AND (re.Zubereitungszeit + re.Ruhezeit + re.KochBackzeit ) < '.$suchdauer ;
		}
		
		//gerate
		if (isset($_POST['geraet']) AND $_POST['geraet']!='null') {
			$suchgeraet = htmlspecialchars($_POST['geraet']);
			$sqlSuchen .= ' AND ge.name =\''.$suchgeraet.'\'' ;
		}
		
		//Bild
		if (isset($_POST['mitbild']) AND $_POST['mitbild']=='mitbild') {
			$sqlSuchen .= ' AND EXISTS (SELECT * FROM rezBilder bi WHERE bi.ReID = re.ReID ) ';
		}
		 
		$ergebnis = $db->queryObject($sqlSuchen);
		
	}
	
	
	
	
	
	
	
	
	
	
	
	if(!empty($ergebnis)){
	
		echo '<div class="listen_suche"> 
				    <div class="ergebnisliste"> <!--tao cot dau tien-->
				        <div class="ueberschrift kb_rezept tt">
				            <div class="element tab0s" ><a href="#" >Bild</a></div>
				            <div class="element tab1s"><a href="#">Name</a></div>
				            
				    </div>  <!-- class="ueberschrift" -->
		';

		for ($i = 0; $i < count($ergebnis); $i++) {
			
			$Name = $ergebnis[$i]->Name;
			$ReID = $ergebnis[$i]->ReID;
			$temp	 = 	showBild($db, $ReID);
			$bild	 = 	$temp[0];
			
			printf('<div class="suchelement kb_rezept" >
											   <div class="element tab0s">    
													<div class="inner">
													  <div class="img"><a href="showrezept.php?reid=%d"><img src="images/%s" border="0" height="60" width="90"></a></div>
													</div>
												</div>
												<div class="element tab1s">
													<div class="inner">
														<a href="showrezept.php?reid=%d">%s</a>
													</div>
												</div>							
											</div>  <!-- class="suchelement" -->
											<div class="zeilentrenner"></div>
								',$ReID,$bild,$ReID,$Name);
		}

		
		
	echo '</div><div class="lisu_navi"> <div class="blaettern"></div> </div></div><!-- listen_suche -->';
	
	}//end Ergebnis ausgeben
	
	if(!empty($fehle)){// keine Ergebnis
		echo "<ul id =\"fehler\"><li> Deine Suchanfrage erzielte keine Treffer </li></ul>";
	}
	
	echo'</div>';

	htmlFooter();
?>