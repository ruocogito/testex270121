<?php
 require("postgree.php");
 require("mariadb.php");

 $headers = getallheaders();
 if ($headers["Content-Type"] == "application/json")
   $_POST = json_decode(file_get_contents("php://input"), true) ?: []; 

if(isset($_POST['show']))
	echo ShowAdresses();
else if(!isset($_POST['region']))
	echo GetRegions();
else if(!isset($_POST['city']))
	echo GetCities();
else if(!isset($_POST['street']))
	echo GetStreets();
else if(!isset($_POST['house']))
	echo GetHouses();
else if(isset($_POST['save']))
	echo SaveAdress();


function ShowAdresses()
{
	$r = Mariadb::DoQuery('select full_adress from adress_str');
	return json_encode(['show' => $r]);  
}

function SaveAdress()
{
	//43909681-d6e1-432d-b61f-ddac393cb5da
	//7b6de6a5-86d0-4735-b11a-499081111af8
	//8ba0131d-84d8-4028-8463-17859512a897
	//4a58de5d-9715-41d8-ab98-10944c03d255
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
	return json_encode(['stored' => $r]);
}

function GetRegions()
{ 

	$res = Postgre::DoQuery("SELECT aoguid, addrobj.formalname, addrobj.shortname from addrobj where addrobj.aolevel=".Postgre::LV_REGION);
	$r = [];
	foreach($res as $row)
		$r[] = ['guid' => $row['aoguid'], 'name' => $row['formalname'].' '.$row['shortname']];
	return json_encode( ['id' => 'regions','data'=> $r] );
}

function GetCities()
{ 
	global $_POST;
	$res = Postgre::DoQuery("SELECT aoguid, addrobj.formalname, addrobj.shortname from addrobj where addrobj.parentguid='".$_POST['region']."' AND addrobj.aolevel=".Postgre::LV_CITY);
	$r = [];
	foreach($res as $row)
		$r[] = ['guid' => $row['aoguid'], 'name' => $row['shortname'].'. '.$row['formalname']];
	return json_encode( ['id' => 'cities','data'=> $r] );
}

function GetStreets()
{ 
	global $_POST;
	$res = Postgre::DoQuery("SELECT aoguid, addrobj.formalname, addrobj.shortname from addrobj where addrobj.parentguid='".$_POST['city']."' AND addrobj.aolevel=".Postgre::LV_STREET);
	$r = [];
	foreach($res as $row)
		$r[] = ['guid' => $row['aoguid'], 'name' => $row['shortname'].' '.$row['formalname']];
	return json_encode( ['id' => 'streets','data'=> $r] );
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
	return json_encode( ['id' => 'houses','data'=> $r] );
	
}

function existvalue($val)
{
	return $val!="" && $val!=null;
}
?>

