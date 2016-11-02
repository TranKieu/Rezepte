<?php
/**
 * @author Viet Dung Tran
 *
 * created on 13.02.2012
 */
	
	function htmlSidebarNewRez($newRez = array()){
		
		printf('		<div id="newRez">
						<h1>Neue Rezepte</h1>
						<ul class="rezept">
		');
		
		if($newRez){
			foreach ($newRez as $key => $value) {
				echo "<li><a href=\"showrezept.php?reid={$key}\">{$value}</a></li>\n";
			}
		}
		printf('	</ul>
					</div><!-- END #newRez -->
		');
		
	}
	function htmlSidebarMerkt($merkt = array()){
	
		printf('<div id="merkRez">
					<h1>Merkzettel</h1>
						<ul>
		');
		
		if($merkt){
			foreach ($merkt as $key => $value) {
				echo "<li><a href=\"showrezept.php?reid={$key}\">{$value}</a></li>\n"; 
			}
		}
		
		printf( '			</ul>
					</div><!-- END #merkRez -->
		');
	}
	

	
	/**
	 * @desc Sidebar manager Account ausgeben
	 */
	function htmlSidebarManager($action){
		
		printf('<div id="manager">
					<h1>Kontrollzentrum</h1>
						<ul>
				');
		
		foreach ($action as $key => $value) {
			echo "<li><a href=\"benutzerkonto.php?act={$key}\">{$value}</a></li>\n";
		}
		
		
		printf('    	</ul>
				</div><!-- END #manager -->
			');
		
	}
	
	/**
	 * 
	 * @param dbExec $db
	 */
	
	function htmlSidebarBenuBild($benutzer = NULL,$db = NULL) {
		$name='m1781852.jpg';
		$sql ='SELECT name FROM Bilder WHERE FID IN (SELECT FID FROM benutzerBilder WHERE BeID =?)';
		
		$params = array('i',$benutzer);
		
		if(!empty($benutzer)){
			$result = $db->bindAbfrageResult($sql, $params);
			if(!empty($result)){
				$name = $result[0]->name;
			}
			
		}
		printf('<div id="benuBild">
					
				<img alt="%s" src="images/%s" width ="159" >
		
				</div><!-- END #benuBild-->
		',$name,$name);
	}
	
	
	function htmlSidebarEnd() {
	
		printf('		</div><!-- END #sidebar -->
					 
					
					<div id="primary"><!-- Inhalt ausgeben -->'
		);
	}
	
	/**
	 * @desc  
	 * @param INT $benutzer BeID von Benutzer
	 * @param  dbExec $db
	 * @return array $merkt 
	 */
	function merktArray($benutzer = NULL, $db = NULL){
		$merkt = array();
		$sql = 'SELECT ReID, Name FROM Rezept WHERE ReID IN (SELECT ReID FROM merkt WHERE BeID =?) ';
		
		$params = array('i',$benutzer);
		
		if(!empty($benutzer)){
			$result = $db->bindAbfrageResult($sql, $params);
			foreach ($result as $value) {
				$merkt[$value->ReID] = $value->Name;
			}
		}
		
		return $merkt;
	}
	
	/**
	 * 
	 * @param dbExec $db
	 * @return multitype:NULL
	 */
	function newRezArray( $db = NULL) {
		$newRez= array();
		
		$sql = 'SELECT ReID, Name,Erstellungsdatum FROM Rezept ORDER BY Erstellungsdatum DESC';
		
		if (!empty($db)) {
			
			 $result= $db->queryObject($sql);
			 
			 for($i=0; $i<6; $i++) { // nur 5 new Rezept
				if(!empty($result[$i]->ReID)){
				 	$newRez[$result[$i]->ReID] = $result[$i]->Name;
				 }
			 }	 
		}
		return $newRez;
	}


?>

