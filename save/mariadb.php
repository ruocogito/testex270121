<?php

Mariadb::CheckTableExisting();

class Mariadb
{
	private static $connection;
	private static function connect()
	{
        	$params = parse_ini_file('mariadb.ini');
	        if ($params === false)
		    throw new \Exception("Error reading database configuration file");
        

	        $connection = new mysqli($params['host'],$params['user'],$params['password'],$params['database']);
	        if($connection->connect_error)
			throw new \Exception($connection->connect_error);

	        static::$connection=$connection;
	}
        public static function DoQuery($query)
	{
		if(static::$connection===null)	static::connect();
		$res = mysqli_query(static::$connection,$query);

		if($res===false)
			return mysqli_error(static::$connection);	
		else if($res===true)     
			return true;
		$r = [];
		while($row = mysqli_fetch_array($res))
			$r[] = $row;
		return $r;
	}
	public static function CheckTableExisting()
	{

		$r = Mariadb::DoQuery("show tables");
		if(!isset($r[0]['Tables_in_dbname']))
		{
			$q = "CREATE TABLE adress_str
			(
				id INT NOT NULL AUTO_INCREMENT,
				full_adress VARCHAR(1024) NOT NULL,
				CONSTRAINT adress_pk PRIMARY KEY (id),
				INDEX full_adress_idx (full_adress)
			)";
			Mariadb::DoQuery($q);
		}

	}
	protected function __construct() {
        
	}

	private function __clone() {
        
	}

	private function __wakeup() {
        
	}
}



?>