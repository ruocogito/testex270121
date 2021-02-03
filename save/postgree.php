<?php
class Postgre
{
	const LV_REGION = 1;
	const LV_CITY = 4;
	//5 is build in viliges
	const LV_STREET = 7;

	private static $pdo;

	private static function connect()
	{
        	$params = parse_ini_file('postgree.ini');
	        if ($params === false)
		    throw new \Exception("Error reading database configuration file");
        
	        // connect to the postgresql database
        	$conStr = sprintf("pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s", 
                	$params['host'], 
	                $params['port'], 
	                $params['database'], 
	                $params['user'], 
	                $params['password']);

	        $pdo = new \PDO($conStr);
	        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

	        static::$pdo=$pdo;
	}

	public static function DoQuery($query)
	{
		if(static::$pdo===null)	static::connect();
		$res = static::$pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
		return $res;
	}
	protected function __construct() {
        
	}

	private function __clone() {
        
	}

	private function __wakeup() {
        
	}
}
?>
