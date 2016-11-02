<?php
/**
 * @author Viet Dung Tran
 *
 * created on 12.02.2012
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
	include_once ("include/function.php");

	htmlHeader("DBS Abschlussprojekt",$mainNav,$_SESSION['benutzerName'],$_SESSION['BeID']);
	
	$db = new dbExec(UDB_HOST, UDB_USER	, UDB_PASS, UDB_NAME);
	 //sidebar
	htmlSidebarBenuBild($_SESSION['BeID'],$db);
	$merkRez = merktArray($_SESSION['BeID'],$db);
	htmlSidebarMerkt($merkRez);
	htmlSidebarEnd();
	
	
	$sortiert = Null;
	//button
	if(isset($_POST['sortieren']) AND $_POST['sortieren']=='Los'){
		
		if(isset($_POST['sortNach'])){
			$sortiert = $_POST['sortNach'];
		}
		
	}
	//GET
	if(isset($_GET['sortieren'])){
	
			$sortiert = $_GET['sortieren'];
	
	}
	//show
	
	printf('<div class="profil_kochbuch">
				<div id="">
					<form action="%s" method="post" >
					<table border="0" cellpadding="0" cellspacing="0">
						<tbody><tr>
							<td>
								<select name="sortNach" >
									<option value="null">Sortierungsmöglichkeiten</option>           
									<option value="Name">Alphabetisch</option>
									<option value="wertung">Wertung</option>
									<option value="dauer">Dauer</option>
									<option value="eingestelltam">eingestellt am</option>
									<option value="schwierigkeit">Schwierigkeit</option>
								</select>
						    </td>
							<td style="width: 20px;">
							</td>
							<td>
								<input type="submit" name="sortieren" value="Los" style="color:#4F910D">
							</td>
						</tr></tbody>
					</table>
					</form>	
				</div>
			<div class="listen_suche"> 
			    <div class="ergebnisliste"> <!--tao cot dau tien-->
			        <div class="ueberschrift kb_rezept tt"><!-- class="ueberschrift" = lam no cang ngang ra -->
			            <div class="element tab0" align = "center"><a href="#" >Bild</a></div>
			            <div class="element tab1"align = "center"><a href="index.php?sortieren=alpha">Name</a></div>
			            <div class="element tab2"align = "center"><a href="index.php?sortieren=wertung">Wertung</a></div>
			            <div class="element tab3"><a href="index.php?sortieren=schwierigkeit">Schwierigkeit</a></div>
			            <div class="element tab4"align = "center"><a href="index.php?sortieren=dauer">Dauer</a></div>
			            <div class="element tab5"><a href="index.php?sortieren=eingestelltam">erstellt am/ von</a></div>
			    </div>  <!-- class="ueberschrift" -->
	',$_SERVER['PHP_SELF']);
	
	$ausgeben= ergebnis($db,$sortiert);
	// inhalt
	
	if(!empty($ausgeben)){
		for($i= 0 ;$i<count($ausgeben);$i++){
		
			$reName 	=	$ausgeben[$i]->Name;
			$reDauer 	= 	$ausgeben[$i]->Dauer;
			$reSchwie	=	$ausgeben[$i]->Schwierigkeitsgrad;
			$reErstel	=	substr($ausgeben[$i]->Erstellungsdatum, 0,9);
			$rePunkt	=	empty($ausgeben[$i]->Punkt)? 0.0 : $ausgeben[$i]->Punkt;
			$beid		=	$ausgeben[$i]->BeID;
			$auto		=	showBeID($db, $beid);
		
			$reid		=	$ausgeben[$i]->ReID;
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
								<div class="inner">%01.1f Punkt</div>
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
			',$reid,$reName,$bild,$reid,$reName,$rePunkt,$reSchwie,$reDauer,$reErstel,$beid,$auto);
				
		}//end For
	}else{
		echo "<ul id =\"fehler\"><li> Moment existiert keine Rezept in DatenBank </li></ul>";
	}
	
	
	
	
	//ende, paging de sau, neu muon thi cho vao giua 2 cai div
	echo '</div>
			<div class="lisu_navi"> <div class="blaettern">';
	
	echo'</div> </div></div><!-- listen_suche --></div>';

	/**
	 * 
	 * @param dbExec $dbConnect
	 * @param String $sortiert
	 * @return Array $result
	 */
	function ergebnis(dbExec $dbConnect,$sortiert=NULL ){
		
		$sqlSortiert ='SELECT re.ReID, re.BeID, re.Name, 
								(re.Zubereitungszeit + re.Ruhezeit + re.KochBackzeit ) as Dauer, 
								re.Schwierigkeitsgrad, re.Erstellungsdatum,
								ROUND(AVG(be.punkt),1) as Punkt 
						FROM Rezept re LEFT JOIN bewertet be ON re.ReID = be.ReID 
						GROUP BY re.ReID ';
		switch ($sortiert) {
			case 'Name':
				$sqlSortiert = $sqlSortiert."ORDER BY re.Name";
			break;
			case 'wertung':
				$sqlSortiert = $sqlSortiert."ORDER BY Punkt DESC";
				break;
			case 'dauer':
				$sqlSortiert = $sqlSortiert."ORDER BY Dauer";
				break;
			case 'eingestelltam':
				$sqlSortiert = $sqlSortiert."ORDER BY re.Erstellungsdatum DESC";
				break;
			case 'schwierigkeit':
				$sqlSortiert = $sqlSortiert."ORDER BY re.Schwierigkeitsgrad DESC";
				break;
			default:
				$sqlSortiert = $sqlSortiert;
			break;
		}
		
		return  $dbConnect->queryObject($sqlSortiert);	
	}
	
	//footer
	htmlFooter();
	?>