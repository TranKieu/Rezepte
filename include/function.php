<?php
/**
 * @author Viet Dung Tran
 *
 * created on 17.02.2012
 */
	/**
	 * Name der Verfasser von Rezept ausgeben
	 * @param dbExec $db
	 * @param int $BeID
	 * @return String benutzerName
	 */
	function showBeID(dbExec $db, $BeID){
		$sqlBeId = 'SELECT benutzerName FROM Benutzer WHERE BeID=?';	
		$arrBeID = array('i',$BeID);
		$result = $db->bindAbfrageResult($sqlBeId, $arrBeID);
		
		return $result[0]->benutzerName;	
	}
	/**
	 * Bilde von Rezept ausgeben
	 * @param dbExec $db
	 * @param int $ReID
	 * @return multitype:string NULL
	 */
	function showBild(dbExec $db, $ReID) {
		$reBild = array();
		$sqlBild='SELECT name FROM Bilder WHERE FID IN (SELECT FID FROM rezBilder WHERE ReID =?)';
		$arrBild = array('i',$ReID);
		
		$result = $db->bindAbfrageResult($sqlBild, $arrBild);
		if($result){
			
			foreach ($result as $value) {
					$reBild[ ] = $value->name;
				}

		} else{
			$reBild[] ='keinBild.jpg';
		}
		return $reBild;
	}

	/**
	 * ob dieser Benutzer schon dieser Rezepet bewertet
	 * @param dbExec $db
	 * @param unknown_type $BeID
	 * @param unknown_type $ReID
	 * @return boolean
	 */
	
	function obBewertet(dbExec $db, $BeID, $ReID){
		
		$sqlBewertet	= 'SELECT * FROM bewertet WHERE BeID=? AND ReID=?';
		$arrBewrtet		= array('ii',$BeID,$ReID);

		$result		= $db->bindAbfrageResult($sqlBewertet, $arrBewrtet);
		
		if($result){
			return TRUE;
		} else return FALSE;
	}

	/**
	 * ob dieser Rezept schon gemerktet ist
	 * @param dbExec $db
	 * @param unknown_type $BeID
	 * @param unknown_type $ReID
	 * @return boolean
	 */
	function obMerktet(dbExec $db, $BeID, $ReID) {
		
		$sqlMerktet	= 'SELECT * FROM merkt WHERE BeID=? AND ReID=?';
		$arrMerktet	= array('ii',$BeID,$ReID);
		
		$result		= $db->bindAbfrageResult($sqlMerktet, $arrMerktet);
		
		if($result){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	
	/**
	 * 
	 * @param dbExec $db
	 * @param unknown_type $BeID
	 * @return multitype:
	 */
	function getZutat(dbExec $db, $ReID){
		$zutaten = array();
		
		$sqlZutat	= 'SELECT name, menge, masseinheit, bemerkung FROM Zutaten WHERE ReID =?';
		$arrZatat	= array('i',$ReID);
		
		$result		= $db->bindAbfrageResult($sqlZutat, $arrZatat);
		
		if($result){
	
			$zutaten = $result;
		}
		
		return $zutaten;
	}
	
	function getGraet(dbExec $db){
		$gerate = array();
		$sqlGeraet = 'SELECT DISTINCT name FROM zubereitGerate';
		$result = $db->queryObject($sqlGeraet);
		if($result ){
			$gerate = $result;
		}
		return $gerate;
	}
	
	/**
	 * 
	 * @param dbExec $db
	 * @param unknown_type $BeID
	 * @return multitype:
	 */
	function getKommentar(dbExec $db, $ReID){
		$kommentar = array();
		
		$sqlKomm	= 'SELECT BeID, kommentar, zeit FROM kommentiert WHERE ReID =?';
		$arrKomm	= array('i',$ReID);
		
		$result 	= $db->bindAbfrageResult($sqlKomm, $arrKomm);
		
		if($result){
			$kommentar = $result;
		}
		
		return $kommentar;
	}
	
?>