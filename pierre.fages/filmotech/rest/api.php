<?php
    
	/* 
			Filmotech publishing API
			(c) 2013-2015 by Pascal PLUCHON
			http://www.filmotech.fr
 	*/
	
	require_once("rest.inc.php");
	require_once("json.inc.php");
	require_once("../include/config.inc.php");	
		
	class API extends REST {
			
		public $data = "";
		private $cfg;
		private $db = NULL;
		
		// List of allowed methods
		private $services = array("check_server", "check_code", "get_config", "create_poster_directory",
		 "get_movie_list", "create_table", "update_publishing_date", "publish" );

		public function __construct(){
			parent::__construct();				// Init parent contructor
			$this->cfg = new CONFIG(); 			// Init database parameters
			$this->dbConnect();					// Initiate Database connection			
		}
		
		// Database connection
		private function dbConnect(){
			error_reporting(0); // Disable this to see PHP errors
			try
			{
				if ( $this->cfg->DB_TYPE == 'sqlite' ) {
					$db_init = new PDO('sqlite:../'.$this->cfg->DB_NAME.'.sqlite3');
					// $db_init->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);  // Enable this to see PDO errors
				} else {
				    $db_init = new PDO('mysql:host='.$this->cfg->DB_SERVER.';dbname='.$this->cfg->DB_NAME, 
				    $this->cfg->DB_USER, $this->cfg->DB_PASSWORD);
					$db_init->query("SET NAMES UTF8"); 
				}
			}
			catch (Exception $e)
			{
				$error = array( 'error_msg' => $e->getMessage() );
				$this->response($this->json($error), 412);
			}
			$this->db = $db_init;
		}
		
		// Public method for access api.
		// This method dynmically call the method based on the query string
		public function processApi(){
			$func = strtolower(trim(str_replace("/","",$_REQUEST['rquest'])));
			// if((int)method_exists($this,$func) > 0)
			if (in_array($func,$this->services))
				$this->$func();
			else
				$this->response('',404); 	// If the method not exist with in this class, 
											// response would be "Page not found".
		}
		
		// Check if the service is available
		private function check_server(){
			$success = array('status' => "OK" );
			$this->response($this->json($success),200);
		}
		
		// Check the security code (API_ACCESS_CODE) and the access method (POST)
		private function check_code(){
			if($this->get_request_method() != "POST")
			{
				$error = array('error_code' => "100" );
				$this->response($this->json($error),401);
			}

			if (!$this->_request['code']) {
				$error = array('error_code' => "101" );
				$this->response($this->json($error),401);
			}
			
			$code = $this->_request['code'];
			if ($code!=$this->cfg->API_ACCESS_CODE) { 
				$error = array('error_code' => "102" );
				$this->response($this->json($error),401);
			}
		}

		// Get the configuration of the API and some parameters
		protected function get_config(){		
			$this->check_code();
			
			$tableau = array('status' => 'OK' );
			$tableau["API_VERSION"] = $this->cfg->API_VERSION;
			$tableau["POSTERS_DIRECTORY"] = $this->cfg->POSTERS_DIRECTORY;
			$tableau["DB_TABLE"] = $this->cfg->DB_TABLE;
			$tableau["PHP_VERSION"] = PHP_VERSION;
			$this->response($this->json($tableau),200);
		}
		
		// Create poster directory
		private function create_poster_directory(){
			$this->check_code();

			$result = false;
			$repertoire_affiches = '../' . $this->cfg->POSTERS_DIRECTORY;
			if (!is_dir($repertoire_affiches)) {
				$result = mkdir($repertoire_affiches);
				if (!$result)
				{
					$error = array( 'error_code' => '201'  );
					$this->response($this->json($error),424);
				}
			}
			if (isset($_POST['forceCHMOD'])) chmod( $repertoire_affiches , 0777 );

			$success = array('status' => 'OK' );
			$this->response($this->json($success),200);
		}
		
		// Empty poster directory
		private function empty_poster_directory(){
			$this->check_code();

			$repertoire_affiches = '../' . $this->cfg->POSTERS_DIRECTORY;
			foreach (glob($repertoire_affiches.'/Filmotech*.jpg') as $filename) {
				unlink($filename);
			}
		}
		
		// Return ID/Update date from the database
		private function get_movie_list(){		
			$this->check_code();
			$tableau = array('status' => 'OK' );		
			$res = $this->db->query("SELECT ID, DateHeureMAJ FROM " . $this->cfg->DB_TABLE );
			foreach ($res as $row) {
			    $tableau[$row['ID']] = $row['DateHeureMAJ'];				
			}		
			$this->response($this->json($tableau),200);
		}

		private function create_table_sqlite() {
			$sql = 
			  "CREATE TABLE " . $this->cfg->DB_TABLE . " ("
	        . "ID integer NOT NULL PRIMARY KEY,"
	        . "DateHeureMAJ TimeStamp NOT NULL default '0000-00-00 00:00:00',"
	        . "TitreVF varchar(255) NOT NULL default '',"
	        . "TitreVO varchar(255) default '',"
	        . "Genre varchar(50) default '',"
	        . "Pays varchar(255) default '',"
	        . "Annee varchar(10) default '',"
	        . "Duree int(11) default '0',"
	        . "Note int(11) default '0',"
	        . "Synopsis text ,"
	        . "Acteurs text ,"
	        . "Realisateurs text ,"
	        . "Commentaires text ,"
	        . "Support varchar(50) default '',"
	        . "NombreSupport int(11) default '0',"
	        . "Edition varchar(255) default '',"
	        . "Zone varchar(10) default '',"
	        . "Langues varchar(255) default '',"
	        . "SousTitres varchar(255) default '',"
	        . "Audio varchar(255) default '',"
	        . "Bonus text ,"
	        . "EntreeType varchar(255) default '',"
	        . "EntreeSource varchar(255) default '',"
	        . "EntreeDate date default '0000-00-00',"
	        . "EntreePrix float default '0',"
	        . "Sortie varchar(10) default '',"
	        . "SortieType varchar(255) default '',"
	        . "SortieDestinataire varchar(255) default '',"
	        . "SortieDate date default '0000-00-00',"
	        . "SortiePrix float default '0',"
	        . "PretEnCours varchar(10) default '',"
	        . "FilmVu varchar(5) default 'NON',"
	        . "Reference varchar(255) default '',"
	        . "BAChemin varchar(255) default '',"
	        . "BAType varchar(10) default '',"
	        . "MediaChemin varchar(255) default '',"
	        . "MediaType varchar(10) default '');"
	        . "CREATE INDEX films_idx ON " . $this->cfg->DB_TABLE . " (TitreVF ASC);";

			try
			{
				$this->db->query($sql); 
				$success = array('status' => "OK" );
				$this->response($this->json($success),200);
			}
			catch (Exception $e)
			{
				$this->db->query($sql); 
				$success = array('status' => "KO" );
				$this->response($this->json($success),200);
			}
	
		}		

		private function create_table_mysql() {
			$sql = 'CREATE TABLE IF NOT EXISTS `' . $this->cfg->DB_TABLE . '` ('
	        . ' `ID` bigint(20) NOT NULL,'
	        . ' `DateHeureMAJ` datetime NOT NULL default \'0000-00-00 00:00:00\','
	        . ' `TitreVF` varchar(255) NOT NULL default \'\','
	        . ' `TitreVO` varchar(255) NOT NULL default \'\','
	        . ' `Genre` varchar(50) NOT NULL default \'\','
	        . ' `Pays` varchar(255) NOT NULL default \'\','
	        . ' `Annee` varchar(10) NOT NULL default \'\','
	        . ' `Duree` int(11) NOT NULL default \'0\','
	        . ' `Note` int(11) NOT NULL default \'0\','
	        . ' `Synopsis` text,'
	        . ' `Acteurs` text,'
	        . ' `Realisateurs` text,'
	        . ' `Commentaires` text,'
	        . ' `Support` varchar(50) NOT NULL default \'\','
	        . ' `NombreSupport` int(11) NOT NULL default \'0\','
	        . ' `Edition` varchar(255) NOT NULL default \'\','
	        . ' `Zone` varchar(10) NOT NULL default \'\','
	        . ' `Langues` varchar(255) NOT NULL default \'\','
	        . ' `SousTitres` varchar(255) NOT NULL default \'\','
	        . ' `Audio` varchar(255) NOT NULL default \'\','
	        . ' `Bonus` text,'
	        . ' `EntreeType` varchar(255) NOT NULL default \'\','
	        . ' `EntreeSource` varchar(255) NOT NULL default \'\','
	        . ' `EntreeDate` date NOT NULL default \'0000-00-00\','
	        . ' `EntreePrix` float NOT NULL default \'0\','
	        . ' `Sortie` varchar(10) NOT NULL default \'\','
	        . ' `SortieType` varchar(255) NOT NULL default \'\','
	        . ' `SortieDestinataire` varchar(255) NOT NULL default \'\','
	        . ' `SortieDate` date NOT NULL default \'0000-00-00\','
	        . ' `SortiePrix` float NOT NULL default \'0\','
	        . ' `PretEnCours` varchar(10) NOT NULL default \'\','
	        . ' `FilmVu` varchar(5) NOT NULL default \'NON\','
	        . ' `Reference` varchar(255) NOT NULL default \'\','
	        . ' `BAChemin` varchar(255) NOT NULL default \'\','
	        . ' `BAType` varchar(10) NOT NULL default \'\','
	        . ' `MediaChemin` varchar(255) NOT NULL default \'\','
	        . ' `MediaType` varchar(10) NOT NULL default \'\','
	        . ' PRIMARY KEY (`ID`),'
	        . ' KEY `TitreVF` (`TitreVF`)'
	        . ' ) ENGINE=MyISAM DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci;';

			try
			{
				$this->db->query($sql); 
			}
			catch (Exception $e)
			{
				$error = array('error_code' => "200" , 'error_msg' => $e->getMessage() );
				$this->response($this->json($error),424);
			}
			$success = array('status' => "OK" );
			$this->response($this->json($success),200);
		}
		
		// Create the table in the database
		private function create_table() {		
			$this->check_code();
			
			if ( $this->cfg->DB_TYPE == 'sqlite' ) 
				{ $this->create_table_sqlite(); }
			else 
				{ $this->create_table_mysql(); }
			
		}

		// Remove a record and his poster (if any)
		private function del_record() {			
			$this->check_code();
			$sql = "DELETE FROM " . $this->cfg->DB_TABLE . " WHERE ID = " . $this->_request['ID'];
			try { 
				$this->db->query($sql); 
			} catch (Exception $e) {
				$error = array('error_code' => '500' , 'error_msg' => $e->getMessage()  );
				$this->response($this->json($error),424);
			}
			$repertoire_affiches = '../' . $this->cfg->POSTERS_DIRECTORY;
			$filename = sprintf($repertoire_affiches.'/Filmotech_%05d.jpg' , $this->_request['ID'] );
			if (file_exists($filename)) { unlink($filename); }
		}
			
		// Add a poster
		private function add_poster() {
			$this->check_code();
			$repertoire_affiches = '../' . $this->cfg->POSTERS_DIRECTORY;
			if (isset($this->_request['Affiche'])) {
				$affiche = base64_decode($this->_request['Affiche']);
				$filename = sprintf($repertoire_affiches.'/Filmotech_%05d.jpg' , $this->_request['ID'] );
				if (!$handle = fopen($filename, 'wb')) {
					$error = array( 'error_code' => '301' );
					$this->response($this->json($error),424);
				}
				if (fwrite($handle, $affiche) === FALSE) {
					$error = array( 'error_code' => '302' );
					$this->response($this->json($error),424);
				}
				fclose($handle);
				if (isset($this->_request['forceCHMOD'])) chmod( $filename , 0777 );
			}
			
		}

		// Prepare SQL statement according to db type
		private function sql_escape($field) {
			if ( $this->cfg->DB_TYPE == 'sqlite' )
				{ return str_replace('\'','\'\'',$field); }
			else
				{ return addslashes($field); }

		}
		
		// Add a record and the poster (if any)
		private function add_record() {			
			$this->check_code();
			$champs = array( "DateHeureMAJ", "TitreVF", "TitreVO", "Genre", "Pays", "Annee", "Duree", "Note", "Synopsis", "Acteurs", "Realisateurs", "Commentaires", "Support", "NombreSupport", "Edition", "Zone", "Langues", "SousTitres", "Audio", "Bonus", "EntreeType", "EntreeSource", "EntreeDate", "EntreePrix", "Sortie", "SortieType", "SortieDestinataire", "SortieDate", "SortiePrix", "PretEnCours", "FilmVu", "Reference", "BAChemin", "BAType", "MediaChemin", "MediaType" );


			$sql = 'INSERT INTO ' . $this->cfg->DB_TABLE . '(ID'; 
			foreach ($champs as $value) {
				$sql .= ', ' . $value;
			}
			$sql .= ') VALUES(\''.$this->_request['ID'].'\'';

			foreach ($champs as $value) {
				$sql .= ', \'' . $this->sql_escape($this->_request[$value]) . '\'';
			}
			
			$sql .= ");";

			try { $data = $this->db->query($sql); 
			}
			catch (Exception $e)
			{
				$tableau = array('error_code' => '300' , 'error_msg' => $e->getMessage() );
				$this->response($this->json($tableau),424);
			} 
 			$this->add_poster();
		}
		
		// Update the last publishing date (shown in the movie list page)
		private function update_publishing_date(){
			$this->check_code();
			$filename = '../update.txt';
			if (!$handle = fopen($filename, 'w')) {
				$error = array('error_code' , '400' );
				$this->response($this->json($$error),424);
			}
			if (fwrite($handle, $_POST['DateMAJ'] ) === FALSE) {
				$error = array('error_code' , '401' );
				$this->response($this->json($error),424);
			}
			fclose($handle);
			$success = array('status' => 'OK' );
			$this->response($this->json($success),200);
		}
		
		// Main processs, add, update or remove records
		private function publish(){
			$this->check_code();
			
			if (isset($this->_request['ForceUpdate'])) $this->empty_poster_directory();
			
			if ($this->_request['ACTION']=='ADD') {
				$this->add_record();
			}
			if ($this->_request['ACTION']=='UPDATE') {
				$this->del_record();
				$this->add_record();
			}
			if ($this->_request['ACTION']=='DELETE') {
				$this->del_record();
			}

			$tableau = array("action" => $this->_request['ACTION'] , 
				"TitreVF" => $this->_request['TitreVF'] , "ID" => $this->_request['ID'] );
			$this->response($this->json($tableau),200);
		}

		// Encode array into JSON
		private function json($data){
		// create a new instance of Services_JSON
			$json = new Services_JSON();
			if(is_array($data)){
				return $json->encode($data);
			}
		}
	}

	// Initiiate Library
	
	$api = new API;
	$api->processApi();
?>