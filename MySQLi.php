<?php

class DBi {

  var $host     = "";
  var $username = "";
  var $password = "";
  var $database = "";
  
  var $mysqli;
  
  var $db_error	= "";

  public function __construct( $un , $pw , $h) {
    $this->username = $un;
    $this->password = $pw;
    $this->host     = $h ;
  }

  function connect( $db ) {
    //Set the current database
    $this->database = $db;
		
    //Initiate MySQLi object
    $this->mysqli = mysqli_init();

    //Connect using the MySQLi object
		$this->mysqli->real_connect($this->host, $this->username, $this->password, $db); 
		
    //Incase there is an error
    if (mysqli_connect_error()) {
    	return 'Connect Error (' . mysqli_connect_errno() . ') ' . mysqli_connect_error();
    }
  }

  function select( $query ) {
  	global $_app_settings;
  		
		$results = array();
    
		$stmt = $this->mysqli->prepare( $query );

		$this->db_error = $this->mysqli->error;
		if( $_app_settings['environment'] == 'DEVELOPMENT' )
			$this->throwDBError( );
			
		$row = self::bind_result_array( $stmt );
		$stmt->execute();
		while ( $stmt->fetch() ) {
			$results[] = self::getCopy( $row );
		}

		$stmt->close();
		

		return $results;
  }

  function query( $query ){
    $executed = false;

    $stmt = $this->mysqli->prepare( $query );
    
    $this->db_error = $this->mysqli->error;
	if( $_app_settings['environment'] == 'DEVELOPMENT' )
		$this->throwDBError( );
    
    $executed = $stmt->execute();
    $stmt->close();

    return $executed;

  }

  function last_insert_id() {
    return( mysqli_insert_id( $this->mysqli ) );
  }

  function close(){
    $this->mysqli->close();
  }

	
	private function bind_result_array( $stmt ) {
		$meta = $stmt->result_metadata();
		$result = array();
		while ($field = $meta->fetch_field()) {
			$result[$field->name] = NULL;
			$params[] = &$result[$field->name];
		}

		call_user_func_array(array($stmt, 'bind_result'), $params);

		return $result;
  }

  /** 
   * Returns a copy of an array of references
   */
  private function getCopy( $row ) {
    return array_map( 'self::returnCopy', $row );
  }

  /** 
   * Retuns a copy of result
   */
  private function returnCopy( $obj ) {
    return $obj;
  }
		

  public function throwDBError() {
	  
	  if( $this->db_error ) {
		  echo '<h3 style="margin-bottom: 0px;">Database Error:</h3><br />'.$this->db_error;
		  die();
	  }
  }


}
?>
