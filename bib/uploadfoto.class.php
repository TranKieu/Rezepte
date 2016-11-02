<?php
/**
 * @author Viet Dung Tran
 *
 * created on 13.02.2012
 */

class uploadFoto{

	/**
	 * @var String Name der Uploadform $_FILE['datei']
	 */
	private $datei;

	/**
	 * @var String Name der Bild
	 */
	public	$name;

	/**
	 * @var String Pfad wo die Bild verschieben
	 */
	private $pfadFoto;

	/**
	 * @var String nur für Benutzer, wenn eine Benutzer,
	 *  der schon eine Bild hat, dann dieser Bild über schrieben wird
	 */
	private $alteName;
	
	/** 
	 * @var String pfad für thumbnail
	 */
	private $thumbnail;

	/**
	 * @var String Fehlermeldung
	 */
	public $fehler ;
	
	private $extension ;
	
	private $width;
	private $height;

	/**
	 * 
	 * @param FILE $datei
	 * @param String $pfadFoto
	 * @param String $thumbnail
	 * @param String $alteName
	 */
	public function __construct($datei,$pfadFoto=NULL,$alteName=NULL,$thumbnail = Null){

		$this->datei = $datei;

		if (isset($pfadFoto)){
			
			if (substr($pfadFoto, -1) != '/')
				$pfadFoto .= '/'; // add slash für Pfad
		}
		$this->pfadFoto = $pfadFoto;

		$this->alteName = $alteName;
		
		if (isset($thumbnail)){
			if (substr($thumbnail, -1) != '/')
				$thumbnail .= '/'; // add slash 
		}		
		$this->thumbnail = $thumbnail;
		
	}

	/**
	* @desc ein zufällige neue Name für die eingehende Bild erstellen
	*
	* @return String
	*/
	private function neueName($extension){
		
		$this->name = substr(md5(microtime()),0,6).".".$extension;
		
		while (file_exists($this->pfadFoto.$this->name)){ //ob die Name schon breit ist
			$this->name = substr(md5(microtime()),0,6).".".$extension;
		}
		
		return $this->name;
		
	}
	
	private function thumbnailErstell(){
		
		if($this->thumbnail){
				
			//xem lai thuat toan chuyen do dai
				$w = round($this->width/3);
				$h = round($this->height/3);
				
				$resourc = imagecreatetruecolor($w, $h);
				
				switch($this->extension){
					case "jpg":
						$image = imagecreatefromjpeg($this->pfadFoto.$this->name);
						break;
							
					case "gif":
						$image = imagecreatefromgif($this->pfadFoto.$this->name);
						break;
							
					case "png":
						$image = imagecreatefrompng($this->pfadFoto.$this->name);
						break;
							
					default :
						$image = imagecreatefromjpeg($this->pfadFoto.$this->name);
					break;
				}
				imagecopyresampled($resourc, $image, 0, 0, 0, 0, $w, $h, $this->width, $this->height);
				$thumb = $this->pfadFoto.$this->thumbnail."thum_".$this->name;
				imagejpeg($resourc, $thumb, 100);
		}
	}
	
	/**
	* @desc Prüft, ob die eingehende Datei auf Fehler existiert oder nicht.
	*
	* @return String|boolean
	*/
	private function fehlerHalden(){
		
		switch ($this->datei['error']){
	
			case 1:
				$this->fehler = 'UPLOAD_ERR_INI_SIZE; Die hochgeladene Datei überschreitet die in der Anweisung upload_max_filesize in php.ini festgelegte Größe';
				return true;
				break;
	
			case 2:
				$this->fehler = 'UPLOAD_ERR_FORM_SIZE; Die hochgeladene Datei überschreitet die in dem HTML Formular mittels der Anweisung MAX_FILE_SIZE angegebene maximale Dateigröße.';
				return true;
				break;
	
			case 3:
				$this->fehler = 'UPLOAD_ERR_PARTIAL; Die Datei wurde nur teilweise hochgeladen.';
				return true;
				break;
	
			case 4:
				$this->fehler = 'UPLOAD_ERR_NO_FILE; Es wurde keine Datei hochgeladen.';
				return true;
				break;
	
			case 6:
				$this->fehler = 'UPLOAD_ERR_NO_TMP_DIR; Fehlt ein temporärer Ordner';
				return true;
				break;
	
			case 7:
				$this->fehler = 'UPLOAD_ERR_CANT_WRITE; Konnte die Datei auf die Festplatte schreiben.';
				return true;
				break;
	
			case 8:
				$this->fehler = 'UPLOAD_ERR_EXTENSION; PHP-Erweiterung stoppte die Upload-Datei.';
				return true;
				break;
	
			default:
				return false; // keine Fehler
			break;
		}
	
	}
	
	/**
	 * prüf, ob die eingehende Datei eine Images ist
	 * @return boolean
	 */
	
	private function pruefBild() {
		$flag = FALSE;
		
		$tmp = getimagesize($this->datei['tmp_name']);
		if($tmp === false) {
			$this->fehler =  'keine Bild';
		} else {
			$mimetype = array ( "image/jpeg" => "jpg",
								"image/gif" => "gif",
								"image/png" => "png" 
								);
			
			$mime = $tmp["mime"];
			
			if (!isset($mimetype[$mime])){// prüf, ob die eingehende Datei eine Images ist
				
				$this->fehler ='nicht richtig Format';
				$flag = FALSE;
			} else {
				$flag = TRUE;
				$this->extension = $mimetype[$mime];
				$this->width= $tmp[0];
				$this->height= $tmp[1];
			}
		}
		return $flag;
	}
	
	
	public function schreiben(){
		
		if($this->fehlerHalden()==FALSE){ // keine fehler bei upload 
			
			if($this->pruefBild()){
				if (move_uploaded_file($this->datei["tmp_name"], $this->pfadFoto.$this->neueName($this->extension))){
				
					$this->thumbnailErstell();
					if($this->alteName){
						unlink($this->pfadFoto.$this->alteName); //löschen die alte Foto
					}
					return TRUE;
				
				} 
					$this->fehler = 'Können nicht Bild schreiben. vlt die Ordner hat keine Schreibrechte';
				return  FALSE;
			} return  FALSE;
		}
		return FALSE;
	}


}

?>