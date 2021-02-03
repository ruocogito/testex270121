document.addEventListener("DOMContentLoaded", prepare);
mapChoiced ={};
function prepare()
{
 ids = ['regions','cities', 'streets','houses']
 ids.forEach(function(id, i, arr){ 
 	var option = newelm('datalist');
	option.id = id; 
	byid('content').appendChild(option);
 });
 SendAoRequest();
}

function SendAoRequest()
{
	httpPostAsync(mapChoiced);	
}

function httpPostAsync(mapToSend)  //, callback
{
    var xmlHttp = new XMLHttpRequest();
    xmlHttp.onreadystatechange = function() { 
        if (xmlHttp.readyState == 4 && xmlHttp.status == 200)
	{
		try {
 			var json = JSON.parse(xmlHttp.responseText);
		    } catch (e) {
			
			console.log(e); 
	        	return;
		    }

	        if(json.show!=null) FillShow(json.show);
		else if(json.stored===undefined && json.data!==null && json.id!==null)
			FillDataList(json.data, json.id);
		else if(json.stored!==null)
			console.log('stored: '+json.stored);
			
  	}
    }
    xmlHttp.open("POST", 'save/', true); // true for asynchronous 
    xmlHttp.setRequestHeader("Content-Type", "application/json");	   
    var data = JSON.stringify(mapToSend);  
    xmlHttp.send(data);
}

function FillShow(arr)
{
	var showdiv = byid('showtable');	
	showdiv.innerHTML = '';	
 arr.forEach(function(adress, i, arr){
  let p = newelm('p');	
  p.innerHTML = adress['full_adress'];	
  showdiv.appendChild(p);
 });
}

function FillDataList(arr, id)
{

 var dl = byid(id);
// for (let option of dl.options) 
 //  if(dl!=option) dl.remove(option);
       dl.innerHTML='';
 arr.forEach(function(mapRegion, i, arr){
  let option = newelm('option');	
  option.value = mapRegion['name'];	
  option.id = mapRegion['guid'];	
  dl.appendChild(option);
 });

}

function byid(id)
{
 return document.getElementById(id);
}
function newelm(elm)
{
 return document.createElement(elm);
}
String.prototype.capitalize = function() {
    return this.charAt(0).toUpperCase() + this.slice(1);
}
function onChangeRegion(inpRegion)
{
	var RegionName = inpRegion.value.trim().capitalize(); //first word
	indexofRType = RegionName.indexOf(' '); //if two word exist cut them
	if(indexofRType>=0) RegionName = RegionName.substring(0,indexofRType+1).trim();

	var BreakException = {'guid':''};
	var region_guid = ''; 
	try{
	inpRegion.list.childNodes.forEach(
	  function(option, currentIndex, listObj) {
		if(option.value.indexOf(RegionName)>-1)
		{
			//global region_guid = option.id;
			throw {'guid':option.id};
		}
	  });
        }catch (e) {
	if (e.guid===undefined) throw e;
	region_guid = e.guid;
	}
	if(region_guid=='')
	{
		byid('region-error').className = 'error';
		byid('region-error').innerHTML = 'Не найден регион '+inpRegion.value;
		inpRegion.value = '';	
			
	}	                    	
	else
	{
		byid('region-error').className = 'hide';
		//to do: clear city, street and house
		byid('inpCities').value='';
		byid('inpStreets').value='';
		byid('inpHouses').value='';
		mapChoiced = {'region':region_guid};
		SendAoRequest();
		
	}
}

function onChangeCity(inpCity)
{
        var CityName = inpCity.value.trim(); 
	if(CityName.substring(0,2)=='г.')
		CityName = 'г. '+ CityName.substring(2).trim(); //add 1 space or remove excess spaces
	else if(CityName.substring(0,2)=='г ')
		CityName = 'г. '+ CityName.substring(1).trim(); //add 1 space or remove excess spaces
	else CityName = 'г. '+CityName.capitalize();

	var BreakException = {'guid':''};
	var city_guid = ''; 
	try{
		inpCity.list.childNodes.forEach(
		function(option, currentIndex, listObj) {
		if(option.value.indexOf(CityName)>-1)
		{
			//global region_guid = option.id;
			throw {'guid':option.id};
		}
	  });
        }catch (e) {
	if (e.guid===undefined) throw e;
		city_guid = e.guid;
	}
	if(city_guid=='')
	{
		byid('city-error').className = 'error';
		byid('city-error').innerHTML = 'Не найден город '+inpCity.value;
		city_guid.value = '';	
			
	}	                    	
	else
	{
		byid('city-error').className = 'hide';
		mapChoiced['city'] = city_guid;
		//clear street and house
		delete mapChoiced.house;
		delete mapChoiced.street;
		byid('inpStreets').value='';
		byid('inpHouses').value='';
		SendAoRequest();
	
	}
}

function onChangeStreet(inpStreet)
{
	var StreetName = inpStreet.value.trim();
	indexofSType = StreetName.indexOf(' '); //if first word exist cut them
	if(indexofSType>0) StreetName = StreetName.substring(indexofSType).trim();

	var BreakException = {'guid':''};
	var street_guid = ''; 
	try{
	inpStreet.list.childNodes.forEach(
	  function(option, currentIndex, listObj) {
		if(option.value.indexOf(StreetName)>-1)
		{
			//global region_guid = option.id;
			throw {'guid':option.id};
		}
	  });
        }catch (e) {
	if (e.guid===undefined) throw e;
		street_guid = e.guid;
	}
	if(street_guid=='')
	{
		byid('street-error').className = 'error';
		byid('street-error').innerHTML = 'Не найдена улица '+inpStreet.value;
		inpStreet.value = '';	
			
	}	                    	
	else
	{
		byid('street-error').className = 'hide';
		//house
		byid('inpHouses').value='';
		delete mapChoiced.house;
		mapChoiced['street'] = street_guid;
		SendAoRequest();
		
	}
}

function onChangeHouse(inpHouse)
{
	var HouseNum = inpHouse.value.trim();

	var BreakException = {'guid':''};
	var house_guid = ''; 
	try{
	inpHouse.list.childNodes.forEach(
	  function(option, currentIndex, listObj) {
		if(option.value.localeCompare(HouseNum, undefined, { sensitivity: 'accent' }) === 0)
		{
			throw {'guid':option.id};
		}
	  });
        }catch (e) {
	if (e.guid===undefined) throw e;
		house_guid = e.guid;
	}
	if(house_guid=='')
	{
		byid('house-error').className = 'error';
		byid('house-error').innerHTML = 'Не найден дом '+inpHouse.value;
		inpHouse.value = '';	
			
	}	                    	
	else
	{
		byid('house-error').className = 'hide';
		mapChoiced['house'] = house_guid;
		SendAoRequest();
	}
}

function OnSave()
{
	mapChoiced['save'] = true;
	SendAoRequest();	
	delete mapChoiced.save;
}

function OnShow()
{
	mapChoiced['show'] = true;	
	SendAoRequest();
	delete mapChoiced.show;	
}