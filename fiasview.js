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
 SendAoRequest('getregions');
}

function SendAoRequest(act)
{
	mapChoiced['act'] = act;
	httpPostAsync(mapChoiced);	
	delete mapChoiced.act;
}

function httpPostAsync(mapToSend)  //, callback
{
    var xmlHttp = new XMLHttpRequest();
    xmlHttp.onreadystatechange = function() { 
        if (xmlHttp.readyState == 4 && xmlHttp.status == 200)
	{
		try{ var json = JSON.parse(xmlHttp.responseText);}
		catch (e) { 	console.log(e); 
		        	return;	    }
		let funcs = {'filldatalist':FillDataList,'stored':slog,'show':FillShow,'loh':elog };
		funcs[json.act](json);
  	}
    }
    xmlHttp.open("POST", 'save/', true); // true for asynchronous 
    xmlHttp.setRequestHeader("Content-Type", "application/json");	   
    var data = JSON.stringify(mapToSend);  
    xmlHttp.send(data);
}

function slog(json)
{
	console.log('stored: '+json.stored);
}

function elog(json)
{
	console.log('error on select full adress: '+json.data);
}

function FillShow(json)
{     
	var arr = json.show;
	var showdiv = byid('showtable');	
	showdiv.innerHTML = '';	
	arr.forEach(function(adress, i, arr){
		let p = newelm('p');	
		p.innerHTML = adress['full_adress'];	
		showdiv.appendChild(p);
	});
}

function FillDataList(json)
{
	var arr = json.data;
	var id = json.id;

	var dl = byid(id);

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

function commonProcessOnChange(fOptionCmp,errorid,errormsg,inputobj)
{
        var BreakException = {'guid':''};
	var guid = ''; 
	try{
		inputobj.list.childNodes.forEach(fOptionCmp);
        }catch (e) {
		if (e.guid===undefined) throw e;
		guid = e.guid;
	}
	if(guid=='')
	{
		byid(errorid).className = 'error';
		byid(errorid).innerHTML = errormsg+inputobj.value;
		inputobj.value = '';	
		return false;			
	}	                    	
	byid(errorid).className = 'hide';
	return guid;		             
	
}  

function ProcessOnChange(niddleName,errorid,errormsg,inputobj)
{
	return commonProcessOnChange( function(option, currentIndex, listObj) {
		if(option.value.indexOf(niddleName)>-1)
				throw {'guid':option.id};  },errorid,errormsg,inputobj);
}


function onChangeRegion(inpRegion)
{
	var RegionName = inpRegion.value.trim().capitalize(); //first word
	indexofRType = RegionName.indexOf(' '); //if two word exist cut them
	if(indexofRType>=0) RegionName = RegionName.substring(0,indexofRType+1).trim();

        guid = ProcessOnChange(RegionName,'region-error','Не найден регион ',inpRegion);
	if(guid===false) return;
	
	byid('inpCities').value='';
	byid('inpStreets').value='';
	byid('inpHouses').value='';
	mapChoiced = {'region':guid};
	SendAoRequest('getcities');
}

function onChangeCity(inpCity)
{
        var CityName = inpCity.value.trim(); 
	if(CityName.substring(0,2)=='г.')
		CityName = 'г. '+ CityName.substring(2).trim(); //add 1 space or remove excess spaces
	else if(CityName.substring(0,2)=='г ')
		CityName = 'г. '+ CityName.substring(1).trim(); //add 1 space or remove excess spaces
	else CityName = 'г. '+CityName.capitalize();

	guid = ProcessOnChange(CityName ,'city-error','Не найден город ',inpCity);
	if(guid===false) return;	
	mapChoiced['city'] = guid;
	//clear street and house
	delete mapChoiced.house;
	delete mapChoiced.street;
	byid('inpStreets').value='';
	byid('inpHouses').value='';
	SendAoRequest('getstreets');
	

}

function onChangeStreet(inpStreet)
{
	var StreetName = inpStreet.value.trim();
	indexofSType = StreetName.indexOf(' '); //if first word exist cut them
	if(indexofSType>0) StreetName = StreetName.substring(indexofSType).trim();
	
	guid = ProcessOnChange(StreetName ,'street-error','Не найдена улица ',inpStreet);
	if(guid===false) return;	
	//house
	byid('inpHouses').value='';
	delete mapChoiced.house;
	mapChoiced['street'] = guid;
	SendAoRequest('gethouses');
}

function onChangeHouse(inpHouse)
{
	var HouseNum = inpHouse.value.trim();

        guid = commonProcessOnChange(function(option, currentIndex, listObj) {
		if(option.value.localeCompare(HouseNum, undefined, { sensitivity: 'accent' }) === 0)
				throw {'guid':option.id};  },'house-error','Не найден дом ',inpHouse);
	if(guid===false) return;	

	mapChoiced['house'] = guid;
}

function OnSave()
{
	SendAoRequest('save');	
}

function OnShow()
{
	SendAoRequest('show');
}