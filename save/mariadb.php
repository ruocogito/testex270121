<?php

Mariadb::CheckTableExisting();

class Mariadb
{
	private static $connection;
	private static function connect()
	{
        	$params = parse_ini_file('mariadb.ini');
	        if ($params === false)
		    throw new Exception("Error reading database configuration file");
        

	        $connection = new mysqli($params['host'],$params['user'],$params['password'],$params['database']);
	        if($connection->connect_error)
			throw new Exception($connection->connect_error);

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
		$q = "CREATE TABLE IF NOT EXISTS adress_parts
		(
			id INT NOT NULL AUTO_INCREMENT,
			region_name VARCHAR(256) NOT NULL,
			city_name VARCHAR(256) NOT NULL,
			street_name VARCHAR(256) NOT NULL,
			house_name VARCHAR(256) NOT NULL,
			CONSTRAINT adress_parts_pk PRIMARY KEY (id),
			INDEX region_idx (region_name),
			INDEX city_idx (city_name),
			INDEX street_idx (street_name),
			INDEX house_idx (house_name)
		)";
		Mariadb::DoQuery($q);
	}
	protected function __construct() {
        
	}

	private function __clone() {
        
	}

	private function __wakeup() {
        
	}
}



?>