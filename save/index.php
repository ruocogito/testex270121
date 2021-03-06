<?php
 require("postgree.php");
 require("mariadb.php");

 $headers = getallheaders();
 if ($headers["Content-Type"] == "application/json")
   $_POST = json_decode(file_get_contents("php://input"), true) ?: []; 

$funcs = ['getregions'=>'GetRegions','getcities'=>'GetCities','getstreets'=>'GetStreets',
	  'gethouses'=>'GetHouses','save'=>'SaveAdress','show'=>'ShowAdresses' ];


if(isset($funcs[$_POST['act']]))
	echo $funcs[$_POST['act']]();

function ShowAdresses()
{
	$r = Mariadb::DoQuery('select full_adress from adress_str');
	return json_encode(['act' => 'show','show' => $r]);  
}

function SaveAdress()
{
	$q= "select concat(a.formalname,' ',a.shortname,', ',
			  a2.shortname, ' ',a2.formalname, ', ',
			  a3.shortname,' ',a3.formalname,' ',h.housenum) as full_adress
	 from addrobj a, addrobj a2, addrobj a3,house h
	 where a.aoguid='".$_POST["region"]."' and a2.aoguid='".$_POST["city"]."'
	 and a3.aoguid='".$_POST["street"]."' and h.houseguid='".$_POST["house"]."'";
        $res = Postgre::DoQuery($q);
	if(!isset($res[0]['full_adress'])) return json_encode($res);
	
	$q = "insert into adress_str(full_adress) values('".$res[0]['full_adress']."')";
	$r = Mariadb::DoQuery($q);
	return json_encode(['act' => 'stored', 'stored' => $r]);
}

function GetRegions()
{ 

	$res = Postgre::DoQuery("SELECT aoguid, addrobj.formalname, addrobj.shortname from addrobj where addrobj.aolevel=".Postgre::LV_REGION);
	$r = [];
	foreach($res as $row)                      
		$r[] = ['guid' => $row['aoguid'], 'name' => $row['formalname'].' '.$row['shortname']];
	return json_encode( ['act' => 'filldatalist', 'id' => 'regions','data'=> $r] );
}

function GetCities()
{ 
	global $_POST;
	$res = Postgre::DoQuery("SELECT aoguid, addrobj.formalname, addrobj.shortname from addrobj where addrobj.parentguid='".$_POST['region']."' AND addrobj.aolevel=".Postgre::LV_CITY);
	$r = [];
	foreach($res as $row)
		$r[] = ['guid' => $row['aoguid'], 'name' => $row['shortname'].'. '.$row['formalname']];
	return json_encode( ['act' => 'filldatalist', 'id' => 'cities','data'=> $r] );
}

function GetStreets()
{ 
	global $_POST;
	$res = Postgre::DoQuery("SELECT aoguid, addrobj.formalname, addrobj.shortname from addrobj where addrobj.parentguid='".$_POST['city']."' AND addrobj.aolevel=".Postgre::LV_STREET);
	$r = [];
	foreach($res as $row)
		$r[] = ['guid' => $row['aoguid'], 'name' => $row['shortname'].' '.$row['formalname']];
	return json_encode( ['act' => 'filldatalist','id' => 'streets','data'=> $r] );
}

function GetHouses()
{
	global $_POST;
	$res = Postgre::DoQuery("SELECT house.houseguid, house.housenum, house.buildnum, house.strucnum from house where house.aoguid='".$_POST['street']."'");
	$r = [];
	foreach($res as $row)
	{
		$name = "";
		if(existvalue($row['housenum']))
			$name = $row['housenum'];
		if(isset($row['buildnum']) && existvalue($row['buildnum']))
			$name .= "/".$row['buildnum'];
		if(isset($row['strucnum']) && existvalue($row['strucnum']))
			$name .= " ����.".print_r($row['strucnum'],true);

		$r[] = ['guid' => $row['houseguid'], 'name' => $name];
	}
	return json_encode( ['act' => 'filldatalist','id' => 'houses','data'=> $r] );
	
}

function existvalue($val)
{
	return $val!="" && $val!=null;
}
?>

