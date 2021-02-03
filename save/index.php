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
	$r = Mariadb::DoQuery('select concat(region_name,", ",city_name,", ",street_name, " ",house_name) as full_adress from adress_parts');
	return json_encode(['act' => 'show','show' => $r]);  
}

function SaveAdress()
{
	//43909681-d6e1-432d-b61f-ddac393cb5da
	//7b6de6a5-86d0-4735-b11a-499081111af8
	//8ba0131d-84d8-4028-8463-17859512a897       
	//4a58de5d-9715-41d8-ab98-10944c03d255
	$q= "select concat(a.formalname,' ',a.shortname) as region_name,
		    concat(a2.shortname, ' ',a2.formalname) as city_name,
		    concat(a3.shortname,' ',a3.formalname) as street_name,
		    housenum  from addrobj a, addrobj a2, addrobj a3,house h
	 where a.aoguid='".$_POST["region"]."' and a2.aoguid='".$_POST["city"]."'
	 and a3.aoguid='".$_POST["street"]."' and h.houseguid='".$_POST["house"]."'";
        $res = Postgre::DoQuery($q);
	if(!isset($res[0]) || count($res[0])<4) return json_encode(['act' => 'log', 'data'=>$res]);
	
	$q = sprintf("insert into adress_parts(region_name, city_name, street_name, house_name)
			 values('%s','%s','%s','%s')",$res[0]['region_name'],$res[0]['city_name'],
							$res[0]['street_name'],$res[0]['housenum']);
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
			$name .= " корп.".print_r($row['strucnum'],true);

		$r[] = ['guid' => $row['houseguid'], 'name' => $name];
	}
	return json_encode( ['act' => 'filldatalist','id' => 'houses','data'=> $r] );
	
}

function existvalue($val)
{
	return $val!="" && $val!=null;
}
?>

