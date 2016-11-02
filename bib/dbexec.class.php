<?php
/**
 * @author Viet Dung Tran
 *
 * created on 07.02.2012
 */

class dbExec { //ko lay cai nay nua ma lay trong PHPAPI
	
	private $mysqlhost;
	private $mysqluser;
	private $mysqlpasswd;
	private $mysqldb;
	private $connection = FALSE;
	private $showerror = TRUE;//true wenn Fehler ausgeben moechten
	private $umleitung = "index.php";//wo moechten Sie umleiten

	public function __construct($mysqlhost, $mysqluser, $mysqlpasswd, $mysqldb){
		
		$this->mysqlhost = $mysqlhost;
		$this->mysqluser =$mysqluser;
		$this->mysqlpasswd= $mysqlpasswd;
		$this->mysqldb=$mysqldb;
		$this->dbConnection();
	}

	/**
	 * automatik verbindung abschliessen
	 */
	function __destruct(){
		$this->close();
	}
	/**
	 * verbindung zur Datenbank aufbau
	 */
	private function dbConnection (){
		
		$this->connection = new mysqli( $this->mysqlhost, $this->mysqluser, $this->mysqlpasswd, $this->mysqldb);
		if($this->connection->connect_error){
			//kontrollieren, ob verbindung oki
			$this->printerror("Fehler bei der Verbindung ".$this->connection->connect_error);
			exit();
		}
	}
	
	/**
	 *  Verbindung abschliessen
	 */
	
	private function close(){
		if($this->connection){
			$this->connection->close();
			$this->connection = FALSE;
		}
	}
		
	/**
	 * 
	 * @param String $sql
	 * @return
	 */
	function execute($sql,$escape=true){
		
		if($escape){
			$sql = $this->escape($sql);
		}
		$result = $this->connection->real_query($sql);
		if($result==FALSE){
			$this->printerror($this->connection->error);
		} 
		return $result;
	}

	/**
	 * Abfrage mit Parrameter aber ohne result zb INSERT
	 * @param String $sql die prepare Abfrage
	 * @param Array $arrParameter mit [0] = die String,die an der Stelle man den Typ der einzelnen Werte definiert.
	 * @return ob die Abfrage ausführen wird
	 */
	function bindAbfrage($sql, $arrParameter ) {
		
		$obAusfuehr = array();
		if ($stmt = $this->connection->prepare($sql)) {

			$method = new ReflectionMethod('mysqli_stmt', 'bind_param');
			$arrParameter = $this->getReferPar($arrParameter);
			
			$method->invokeArgs($stmt, $arrParameter); //$stmt->bind_param wird aufführen = $stmt->bind_param($arrParameter[0],...)
			
			$stmt->execute();
			$obAusfuehr['ausfuhrt'] =$stmt ->affected_rows;
			$obAusfuehr['insert'] = $stmt->insert_id;
		}else{
			$this->printerror("Die Abfragen wird nicht auführen... ".$this->connection->error);
		}

		return $obAusfuehr;
	 }

	 /**
	  * Abfrage mit Parrameter 
	  * @param String $sql
	  * @param array $arrParameter
	  * @return array ergebnis = $result[i]->rowName
	  */
	 function bindAbfrageResult($sql, $arrParameter ) {
	 
	 	$result = array();
	 	if ($stmt = $this->connection->prepare($sql)) {
	 
	 		$method = new ReflectionMethod('mysqli_stmt', 'bind_param');
	 		
	 		$arrParameter = $this->getReferPar($arrParameter);
	 		
	 		$method->invokeArgs($stmt, $arrParameter); //$stmt->bind_param wird aufführen = $stmt->bind_param($arrParameter[0],...)
	 			
	 		$stmt->execute();
	 		/*
	 		
	 		$result = $stmt ->get_result();
	 		$tmp = array();
	 		if($result){
	 			
	 			while ($row = $result -> fetch_object()){
	 				$tmp[] = $row;	//ergebnis = $result[$i]->rowName
	 			}
	 			$result->free_result();
	 			
	 		} 
	 		
	 		$stmt->close();
	 	}else{
	 		$this->printerror("Die Abfragen wird nicht aufuehren... ".$this->connection->error);
	 		
	 	}
	 	
	 	return $tmp;*/
	 		$meta = $stmt->result_metadata();
	 		if ($meta){
	 			$stmt->store_result();
	 			$params = array();
	 			$row = array();
	 			
	 				while ($field = $meta->fetch_field()) {
	 					$params[] = &$row[$field->name];
	 				}
	 			
	 			$meta->close();
	 			$method = new ReflectionMethod('mysqli_stmt', 'bind_result');
	 			$method->invokeArgs($stmt, $params);
	 			while ($stmt->fetch()) {
	 				$obj = new stdClass();
	 				foreach($row as $key => $val) {
	 					$obj->{$key} = $val;
	 				}
	 				$result[] = $obj;
	 				
	 			}
	 			$stmt->free_result();
	 		}
	 		$stmt->close();
	 		}
	 		
	 		return $result;
	 }
	 
	 /**
	  * Select abfrage aber ohne Parameter
	  * @param unknown_type $sql
	  */
	 public function queryObject($sql){
	 	$result = $this->query($sql);
	 	if($result){
	 		if($result->num_rows){
	 			while($row = $result->fetch_object()){
	 				$tmp[] = $row;
	 			}
	 			return $tmp;
	 		}   else return false;
	 	}
	 }
	 
	 /**
	  * Nur ein Ergebnis
	  * @param unknown_type $sql
	  * @return unknown|number
	  */
	 function querySingleItem($sql){
	 	$result = $this->query($sql);
	 	if($result){
	 		if($row = $result->fetch_object()){
	 			return $row[0];
	 		} else return -1; //wenn keine Result=>False ? if($db->querySingleItem($sql)!=-1){ }            
	 	 }
	 }
	 
	 
	 private function query($sql){
	 	
	 	$dbResult = $this->connection->query($sql);
	 	
	 	if($dbResult==FALSE){
	 		$this->printerror($this->connection->error);
	 	}
	 	return $dbResult;
	 }
	 	
	 
	 /**
	  * addslashes
	  * @param String $txt
	  * @return string
	  */
	 function escape($txt) {
	 	return trim($this->connection->real_escape_string($txt));
	 }
	 
	 /**
	  * Fehler ausgeben
	  * @param unknown_type $txt
	  */
	 private function printerror($txt) {
	 	if($this->showerror) {
	 		echo("<font color=\"#ff0000\">".htmlentities($txt)."</font><br \>\n");
	 	}  else {
	 		header("Location: ".$this->umleitung);
	 	}
	 }
	 
	 /**
	  * 
	  * @param array $a
	  * @return multitype:unknown |unknown
	  */
	 function getReferPar($parameters) {
		 if (strcmp(phpversion(),'5.3')>=0) {
		 	
		 	$ref = array();
		 	foreach($parameters as $key => $val) {
		 		$ref[$key] = &$parameters[$key];
		 	}
		 return $ref;
		 }
		 return $Parameters;
	 }
	 
	 //transaktion
	 
	 function autocommit($param) {
	 	
	 	return $this->connection->autocommit($param);
	 }
	 
	 function commit() {
	 	return $this->connection->commit();
	 }
	 function rollback() {
	 	return $this->connection->rollback();
	 }
}

?>