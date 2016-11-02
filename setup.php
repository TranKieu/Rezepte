<?php
/**
 * @author Viet Dung Tran
 *
 * created on 05.02.2012
 */

	$salt = "q*rHa>8&";
	$kennwort= "";//MD5(Pass+salt)

	include("autoload.php");
	include("include/config.php");
	

	if(!isset($_POST['pass']) OR !(md5($_POST['pass'].$salt)==$kennwort) ){
		// kennwort für installieren
		echo '
				<form method="POST" action="setup.php">
				Password: <input type="password" name="pass" />
				<input type="submit" value="Submit" />
				</form> 
				';
	} else {
		// database instalieren 
		//Bilder(FID, name)
		$bilderTab= 'CREATE TABLE IF NOT EXISTS Bilder('
										.'FID INT AUTO_INCREMENT NOT NULL,' 
										.'name VARCHAR(50) , '
										.'PRIMARY KEY(FID)'
										.')';
		
		//Benutzer(BeID, benutzerName, kennwort ,name, vorname, geschlecht, gebuDatum, email, mitliegdSeit, PLZ, Ort, Land)
		$benutzerTab ='CREATE TABLE IF NOT EXISTS Benutzer('	
											.'BeID INT AUTO_INCREMENT,' 
											.'benutzerName VARCHAR(30) NOT NULL,' 
											.'kennwort VARCHAR(32) NOT NULL,'
											.'name VARCHAR(20) NOT NULL, '
											.'vorname VARCHAR(20) NOT NULL,' 
											.'geschlecht VARCHAR(2), '
											.'gebuDatum DATE , '
											.'email VARCHAR(70) NOT NULL,'
											.'mitliegdSeit DATE , '
											.'PLZ VARCHAR(10), '
											.'Ort VARCHAR(50),' 
											.'Land VARCHAR(50),'
											.'PRIMARY KEY(BeID),'
											.'UNIQUE(benutzerName, email)'
											.')';
		
		//benutzerBilder(FID,BeID)
		$benutzerBilderTab ='CREATE TABLE IF NOT EXISTS benutzerBilder(' 
														.'FID INT NOT NULL,'
														.'BeID INT NOT NULL,'
														.'PRIMARY KEY( FID, BeID),'
														.'FOREIGN KEY (FID) REFERENCES Bilder(FID),'
														.'FOREIGN KEY (BeID) REFERENCES Benutzer(BeID)'
														.')';
		
		//Rezept(ReID, BeID, Name,Zubereitungsbeschreibung, Schwierigkeitsgrad, Portion, Zubereitungszeit, Ruhezeit, KochBackzeit, Kosten,Kalorien , Erstellungsdatum)
		//co the phai them beschreibung ngan
		$rezeptTab ='CREATE TABLE IF NOT EXISTS Rezept('
										.'ReID INT AUTO_INCREMENT NOT NULL,' 
										.'BeID INT NOT NULL,' 
										.'Name VARCHAR(50) NOT NULL,' 
								        .'Zubereitungsbeschreibung TEXT NOT NULL,'
										.'Schwierigkeitsgrad VARCHAR(10) ,' 
										.'Portion INT, '
										.'Zubereitungszeit VARCHAR(15) NOT NULL,' 
										.'Ruhezeit VARCHAR(15) NOT NULL, '
										.'KochBackzeit VARCHAR(15) NOT NULL,' 
										.'Kosten VARCHAR(10) ,'
										.'Kalorien INT, '
										.'Erstellungsdatum DATETIME ,'
								        .'PRIMARY KEY(ReID),'
										.'FOREIGN KEY (BeID) REFERENCES Benutzer(BeID)'
										.')';
		
		//rezBilder(FID, ReID)
		$rezBilderTab ='CREATE TABLE IF NOT EXISTS rezBilder('
												.' FID INT NOT NULL,'
												.' ReID INT NOT NULL,'
												.' PRIMARY KEY( FID),'
												.' FOREIGN KEY (FID) REFERENCES Bilder(FID),'
												.' FOREIGN KEY (ReID) REFERENCES Rezept(ReID)'
												.' )';
		
		//merkt(BeID, ReID )
		$merktTab ='CREATE TABLE IF NOT EXISTS merkt('
										.'BeID INT NOT NULL, '
										.'ReID INT NOT NULL, '
										.'PRIMARY KEY( BeID,ReID), '
										.'FOREIGN KEY (BeID) REFERENCES Benutzer(BeID), '
										.'FOREIGN KEY (ReID) REFERENCES Rezept(ReID) '
										.')';
		
		//kommentiert(BeID, ReID, kommentar, zeit)
		$kommentiertTab='CREATE TABLE IF NOT EXISTS kommentiert('
													.' BeID INT NOT NULL,'
													.' ReID INT NOT NULL,'
													.' kommentar TEXT NOT NULL,' 
													.' zeit DATETIME NOT NULL,'
													.' FOREIGN KEY (BeID) REFERENCES Benutzer(BeID),'
													.' FOREIGN KEY (ReID) REFERENCES Rezept(ReID)'
													.' )';
					
		//bewertet(BeID, ReID,punkt)
		$bewertetTab='CREATE TABLE IF NOT EXISTS bewertet('
											.'BeID INT NOT NULL, '
											.'ReID INT NOT NULL, '
											.'punkt INT NOT NULL, '
											.'PRIMARY KEY( BeID, ReID), '
											.'FOREIGN KEY (BeID) REFERENCES Benutzer(BeID), '
											.'FOREIGN KEY (ReID) REFERENCES Rezept(ReID) '
											.')';
		
		//zubereitGerate( ReID, name)
		$zubereitGerateTab='CREATE TABLE IF NOT EXISTS zubereitGerate( ' 
														.'ReID INT NOT NULL, '
														.'name VARCHAR(20) NOT NULL, '
														.'PRIMARY KEY(ReID, name), '
														.'FOREIGN KEY (ReID) REFERENCES Rezept(ReID) '
														.')';
		
		//Zutaten(ZuID, ReID, name, menge, maßeinheit, bemerkung)
		$zutatenTab ='CREATE TABLE IF NOT EXISTS Zutaten( '
											.'ZuID INT AUTO_INCREMENT NOT NULL, '
											.'ReID INT NOT NULL, '
											.'name VARCHAR(20) NOT NULL, ' 
											.'menge DECIMAL(8, 2), '
											.'masseinheit VARCHAR(5) NOT NULL, ' 
											.'bemerkung TEXT, '
											.'PRIMARY KEY(ZuID), '
											.'FOREIGN KEY (ReID) REFERENCES Rezept(ReID) '
											.')';
		
		//verbindung zur Datenbank aufbauen
		$db = new dbExec(UDB_HOST, UDB_USER	, UDB_PASS, UDB_NAME);
		
		//alles Tabelle erstellen
		$db->execute($bilderTab);
		$db->execute($benutzerTab);
		$db->execute($benutzerBilderTab);
		$db->execute($rezeptTab);
		$db->execute($rezBilderTab);
		$db->execute($bewertetTab);
		$db->execute($kommentiertTab);
		$db->execute($merktTab);
		$db->execute($zubereitGerateTab);
		$db->execute($zutatenTab);
		
		
		//standard Bilder hinzufügen
		$sql ="INSERT INTO Bilder(name) VALUES (?)";
		$params = array('s','m1781852.jpg');
		$db->bindAbfrage($sql, $params);
		
		//standard benutzer hinzufügen
		$sql = 'INSERT INTO Benutzer(	benutzerName,
		        						kennwort,
		        						email,
										name ,
										vorname , 
										geschlecht,											
										PLZ,
										Ort, 
										Land,
										gebuDatum, 
										mitliegdSeit) VALUES(?,?,?,?,?,?,?,?,?,STR_TO_DATE(?,"%Y-%m-%d"),CURDATE()	)';
		$params= array('ssssssssss','m1781852', '43a517e2237c0cac43164f2e545a4217','Viet.Dung.Tran@uni-duesseldorf.de' ,'Tran', 'Viet Dung', 'ma' , '84', 'HaNoi', 'Viet Nam','1985-10-20');
		//xem lai ngay thang de dua vao
		if($db->bindAbfrage($sql, $params))
		echo "standard benutzer wird hinzufuegen<br>\n"; 
		
		$sqlbild ='INSERT INTO benutzerBilder( FID, BeID) VALUES(?,?) ';
		$bild = array('ii',1,1);
		$db->bindAbfrage($sqlbild, $bild); 
		
		echo "Datenbank wird schon installiert";
	}


//STR_TO_DATE( '2000.10.10', '%Y.%m.%d' )

?>