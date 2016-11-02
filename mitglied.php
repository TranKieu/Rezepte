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
	include_once ('include/config.php');
	include_once ('include/header.inc.php');
	include_once ('include/footer.inc.php');
	include_once ('include/sidebar.inc.php');
	include_once ('include/function.php');
	
	htmlHeader("DBS Abschlussprojekt",$mainNav,$_SESSION['benutzerName'],$_SESSION['BeID']);
	
	$db = new dbExec(UDB_HOST, UDB_USER	, UDB_PASS, UDB_NAME);
	if(isset($_GET['beid'])&&is_numeric($_GET['beid'])){
		$BeID = $_GET['beid'];
		htmlSidebarBenuBild($BeID,$db);
	} else{
		header('Location:index.php');
	}
	
	$newRez = newRezArray($db);
	htmlSidebarNewRez($newRez);
	htmlSidebarEnd();

	$sqlRezept ='SELECT re.ReID, re.Name, 
								(re.Zubereitungszeit + re.Ruhezeit + re.KochBackzeit ) as Dauer, 
								re.Schwierigkeitsgrad, re.Erstellungsdatum,
								ROUND(AVG(be.punkt),1) as Punkt 
						FROM Rezept re LEFT JOIN bewertet be ON re.ReID = be.ReID 
						WHERE re.BeID = ?
						GROUP BY re.ReID ';
	$arrRezept = array('i',$BeID);
	$result = $db->bindAbfrageResult($sqlRezept, $arrRezept);
	
	printf('<div class="profil_kochbuch">
				<div class="listen_suche"> 
				    <div class="ergebnisliste"> <!--tao cot dau tien-->
				        <div class="ueberschrift kb_rezept tt"><!-- class="ueberschrift" = lam no cang ngang ra -->
				            <div class="element tab0" align = "center"><a href="" >Bild</a></div>
				            <div class="element tab1"align = "center"><a href="">Name</a></div>
				            <div class="element tab2"align = "center"><a href="">Wertung</a></div>
				            <div class="element tab3"><a href="">Schwierigkeit</a></div>
				            <div class="element tab4"align = "center"><a href="">Dauer</a></div>
				            <div class="element tab5"><a href="">erstellt am/ von</a></div>
				    </div>  <!-- class="ueberschrift" -->
		');
	

	if($result){
		
		for($i= 0 ;$i<count($result);$i++){
		
			$reName 	=	$result[$i]->Name;
			$reDauer 	= 	$result[$i]->Dauer;
			$reSchwie	=	$result[$i]->Schwierigkeitsgrad;
			$reErstel	=	substr($result[$i]->Erstellungsdatum, 0,9);
			$rePunkt	=	empty($result[$i]->Punkt)? 0 : $result[$i]->Punkt;
			
			$auto		=	showBeID($db, $BeID);
		
			$reid		=	$result[$i]->ReID;
			$temp		= 	showBild($db, $reid);
			$bild		= 	$temp[0];
		
		
			printf('<div class="suchelement kb_rezept" >
						   <div class="element tab0">    
								<div class="inner">
								  <div class="img"><a href="showrezept.php?reid=%d"><img title="%s" src="images/%s" border="0" height="60" width="90"></a></div>
								</div>
							</div>
							<div class="element tab1">
								<div class="inner">
									<a href="showrezept.php?reid=%d">%s</a>
								</div>
							</div>
							<div class="element tab2">
								<div class="inner">%01.1 Punkt</div>
							</div>
							<div class="element tab3">
								<div class="inner">%s</div>
							</div>
							<div class="element tab4">
								<div class="inner">%d Min</div>
							</div>
							<div class="element tab5">
								<div class="inner"> %s 
									<a style="text-decoration: underline;" class="zutatenlink" href="mitglied.php?beid=%d">%s</a> 
								</div>
							</div>
						</div>  <!-- class="suchelement" -->
						<div class="zeilentrenner"></div><!--chia 2 hang-->
			',$reid,$reName,$bild,$reid,$reName,$rePunkt,$reSchwie,$reDauer,$reErstel,$BeID,$auto);
				
		}
	}else{
		echo "<ul id =\"fehler\"><li> User mit BeID ={$BeID} haben keine Rezept angelegt </li></ul>";
	}
	
	echo '</div>
				<div class="lisu_navi"> <div class="blaettern">';
	
	echo'</div> </div></div><!-- listen_suche --></div>';
	htmlFooter();
?>