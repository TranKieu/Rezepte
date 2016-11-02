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
	
	$einkauflist = array();
	include("autoload.php");
	include_once ('include/config.php');
	include_once ('include/header.inc.php');
	include_once ('include/footer.inc.php');
	include_once ('include/sidebar.inc.php');
	include_once ('include/function.php');

	htmlHeader("DBS Abschlussprojekt",$mainNav,$_SESSION['benutzerName'],$_SESSION['BeID']);
	
	$db = new dbExec(UDB_HOST, UDB_USER	, UDB_PASS, UDB_NAME);
	htmlSidebarBenuBild($_SESSION['BeID'],$db);
	$newRez = newRezArray($db);
	htmlSidebarNewRez($newRez);
	htmlSidebarEnd();
	
	if(isset($_POST['loeschen']) AND $_POST['loeschen']=='Löschen'){
		
		if(isset($_POST['mekrtcheck'])){
			
			$mekrtcheck = $_POST['mekrtcheck'];
			
			for ($i = 0; $i < count($mekrtcheck); $i++) {
				
				$sqlDelMe = 'DELETE FROM merkt WHERE ReID=? AND BeID= ?';
				$arrDelMe = array('ii',$mekrtcheck[$i],$_SESSION['BeID']);
				$db->bindAbfrage($sqlDelMe, $arrDelMe);
			}
			
			
		}
	}
	//einkauflist Erstellen
	if(isset($_POST['einkauflist']) AND $_POST['einkauflist']=='Einkauflist'){
		
		if(isset($_POST['mekrtcheck'])){
				
			$mekrtcheck = $_POST['mekrtcheck'];
			$sqlEinkauf = '';
			$var = '';
			
			for ($i = 0; $i < count($mekrtcheck); $i++) {
				
				if($i== 0){
					$sqlEinkauf .=' SELECT DISTINCT name FROM Zutaten WHERE ReID = ?';
					$var .='i';
				}else{
					$sqlEinkauf .=' UNION SELECT DISTINCT name FROM Zutaten WHERE ReID = ?';
					$var .='i';
				}	
			}
			
			array_unshift($mekrtcheck, $var);
			
			$einkauflist = $db->bindAbfrageResult($sqlEinkauf, $mekrtcheck);
			
		}
	}
	
	
	
	
	
	if(empty($einkauflist)){
	
	$sqlMerktze = 'SELECT ReID, Name FROM Rezept WHERE ReID IN (SELECT ReID FROM merkt WHERE BeID =?) ';
	
	$arrMerktze = array('i',$_SESSION['BeID']);
	
	$result = $db->bindAbfrageResult($sqlMerktze, $arrMerktze);
	
	if($result){
		
		
		echo '<div class="profil_kochbuch">
				
				<div class="listen_suche"> 
				    <div class="ergebnisliste"> <!--tao cot dau tien-->
				        <div class="ueberschrift kb_rezept tt">
				            <div class="element table0" align = "center"></div>
				            <div class="element table1" align = "center"><a href="">Bild</a></div>
				            <div class="element table2"align = "center"><a href="">Name</a></div>
				    </div>  <!-- class="ueberschrift" -->
					
					<form action ="" method ="post">';
		
		for ($i = 0; $i < count($result); $i++) {
			
			$merName = $result[$i]->Name;
			$merReID = $result[$i]->ReID;
			$temp	 = 	showBild($db, $merReID);
			$bild	 = 	$temp[0];

			printf('<div class="suchelement kb_rezept" >
						   <div class="element table0">    
								<div class="inner">
								  <div ><input type ="checkbox" name="mekrtcheck[]" value="%d"></div>
								</div>
							</div>
							<div class=" element table1">    
								<div class="inner">
								  <div class="img"><a href="showrezept.php?reid=%d"><img src="images/%s" border="0" height="60" width="90"></a></div>
								</div>
							</div>
							<div class="element table2">
								<div class="inner">
									<a href="showrezept.php?reid=%d">%s</a>
								</div>
							</div>
						</div>  <!-- class="suchelement" -->
						<div class="zeilentrenner"></div>			
			',$merReID,$merReID,$bild,$merReID,$merName);
			
		}	
		
		echo '<div ><input type ="submit" name="loeschen" value ="Löschen"><input type ="submit" name="einkauflist" value="Einkauflist"></div>
					</form>
				</div>	
				<div class="lisu_navi"> <div class="blaettern">	
				</div></div></div><!-- listen_suche --></div>';
		
	}else{
		echo "<ul id =\"fehler\"><li> Momment existiert Keine Merktzettel in DatenBank </li></ul>";
	}
	
	} else{// End if Einkauflist
		
		echo'<h2 class="line">Einkauflist</h2>
					<table class="zutaten">			
						<tbody>';
		for ($i = 0; $i < count($einkauflist); $i++) {
			printf('<tr>
						<td align="right">%s</td>
							</tr>
					', $einkauflist[$i]->name);
		}
		
		
		echo '</tbody></table>';

	}

	htmlFooter();
?>