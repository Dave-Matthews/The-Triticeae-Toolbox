/*
 * THT Core Javascript Functions v.1.0
 */

function toggleRow(rowNum)
{
    var form = document.getElementById(rowNum);
    var i;

    disableRows(rowNum);

    if (form[1].disabled) {
		for (i=0; i<form.length; i++) {
			form[i].disabled = false;
		}
	} else {
		for (i=1; i<form.length; i++) {
			form[i].disabled = true;
		}
	}

	return true;
}

function disableRows(name) {

	var forms = document.forms;
	var i;
	var j;

	for(i=0; i<forms.length; i++) {

		if( (forms[i][0].type == "checkbox") && (forms[i].id != name) ) {	//this is one of our rows

			forms[i][0].checked = false;
			for(j=1; j<forms[i].length; j++) {
				forms[i][j].disabled = true;
			}
		}

	}

	return true;
}

function mapsetChange(value) {

	if(value != "Select" && value != "NewMapSet") {
		document.getElementById("file").disabled = false;
		showMapsetContents(value, "MapsetInfo");
	}
	else {
		document.getElementById("file").disabled = true;
		document.getElementById("MapsetInfo").innerHTML = "";
	}

	if(value == "NewMapSet") {
		document.getElementById("NewMapsetForm").style.display = "block";
	}
	else {
		document.getElementById("NewMapsetForm").style.display = "none";
	}

	document.getElementById("mapsetID").value = value;
}

function showMapsetContents(id, response) {

  	var req = getXMLHttpRequest();
	var resp = document.getElementById(response);
	var qs = "?func=showMapsetContents&id=" + id;

	if(!req) {
		resp.innerHTML = "This function requires Ajax.\nPlease update your browser.\nhttp://www.getfirefox.com";
	}

	/*
	 * Return Function
	 */
  	req.onreadystatechange = function(){
 		if(req.readyState == 4){
	    		resp.innerHTML = req.responseText;
		}
  	}

  	req.open("GET", "includes/ajaxlib.php"+qs, true);
  	req.send(null);
}

function getXMLHttpRequest() {

	var req;

  	try{
		// Normal Browsers
		req = new XMLHttpRequest();
  	} catch (e){
		// Internet Explorer Browsers
		try{
			req = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			try{
				req = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (e){
				alert("This function requires Ajax.\nPlease update your browser.\nhttp://www.getfirefox.com");
				return false;
			}
		}
  	}

	return req;

}

function DispSelChg (value, tablename, field) {
	if(value != "Select" && value != "New") {
		DispSelContents(value, tablename, field);
	}
	else {
		document.getElementById(tablename+"_info").style.display = "none";
	}

	if(value == "New") {
		document.getElementById(tablename+"_div").style.display = "block";
	}
	else {
		document.getElementById(tablename+"_div").style.display = "none";
	}

	//document.getElementById(tablename+"_id").value = value;
}

function DispSelContents(value, tablename, field) {
  	var req = getXMLHttpRequest();
	var resp = document.getElementById(tablename+"_info");
	var qs = "?func=DispSelContents&tablename="+tablename+"&id=" + value+"&field="+field;
	if(!req) {
		resp.innerHTML = "This function requires Ajax.\nPlease update your browser.\nhttp://www.getfirefox.com";
	}
  	req.onreadystatechange = function(){
 		if(req.readyState == 4){
 				resp.innerHTML = req.responseText;
	    		resp.style.display="block";
		}
  	}
  	req.open("GET", "includes/ajaxlib.php"+qs, true);
  	req.send(null);
}

function print_r(theObj){
  if(theObj.constructor == Array ||
     theObj.constructor == Object){
    ("<ul>")
    for(var p in theObj){
      if(theObj[p].constructor == Array||
         theObj[p].constructor == Object){
("<li>["+p+"] => "+typeof(theObj)+"</li>");
        ("<ul>")
        print_r(theObj[p]);
        ("</ul>")
      } else {
("<li>["+p+"] => "+theObj[p]+"</li>");
      }
    }
    ("</ul>")
  }
}

function AddDataByAjax(tablename, field) {
	var frmeles=new Array();
	var inputfrm=document.getElementById(tablename+"_inputform");
	for (var i=0; i<inputfrm.length; i++) {
		if (inputfrm.elements[i].name.length>1) {
			frmeles.push(inputfrm.elements[i].name+"="+inputfrm.elements[i].value);
		}
	}
	var urlstr=frmeles.join('&');
	var req = getXMLHttpRequest();
	var resp = document.getElementById(tablename+"_info");
	var chgSel=document.getElementById(tablename+"_sel");
	var qs="?func=InsertByAjax&tablename="+tablename+"&"+urlstr;
	if(!req) {
		resp.innerHTML = "This function requires Ajax.\nPlease update your browser.\nhttp://www.getfirefox.com";
	}
  	req.onreadystatechange = function(){
 		if(req.readyState == 4){
 			    // resp.innerHTML = req.responseText;
 				document.getElementById(tablename+"_div").style.display="none";
	    		resp.style.display="block";
	    		var idx4new;
	    		for (var i=0;i<chgSel.length; i++) {
    				if (chgSel.options[i].text=="New") {
    					idx4new=i;
    				}
 				}
 				var y=document.createElement('option');
 				var xmlDoc=req.responseXML;
 				y.text=xmlDoc.getElementsByTagName("display_text")[0].firstChild.nodeValue;
 				y.value=xmlDoc.getElementsByTagName("add_uid")[0].firstChild.nodeValue;
				try {
    				chgSel.add(y,chgSel.options[idx4new]); // standards compliant
    				chgSel.selectedIndex=idx4new;
    				DispSelChg(xmlDoc.getElementsByTagName("add_uid")[0].firstChild.nodeValue, tablename, field);
       			}
  				catch(e){
    				chgSel.add(y,idx4new); // IE only
    				chgSel.selectedIndex=idx4new;
    				DispSelChg(xmlDoc.getElementsByTagName("add_uid")[0].firstChild.nodeValue, tablename, field);
    			}
		}
  	}
  	req.open("GET", "includes/ajaxlib.php"+qs, true);
  	req.send(null);
}

/**
 * This will validate if all the seletion tags have values
 */
 function validate_foreign_sels () {
 	var selections=document.getElementsByTagName("select");
 	var flag=0;
 	var selstr="";
 	for (var i=0; i<selections.length; i++) {
 		var idx=selections[i].selectedIndex;
 		if (selections[i].options[idx].value=="Select" || selections[i].options[idx].value=="New") {
 			flag++;
 		}
 		else {
 			selstr+=selections[i].id+","+selections[i].options[idx].value+":";
 		}
 	}

	//The new layout always has 1 selection box in the user registration form that's hidden, so that will force the flag to always be AT LEAST 1.
	//So I changed this to flag == 1 instead of flag == 0.  7-12-07 EthanW
 	if (flag==1) {
 		document.getElementById("foreign_references").style.display="none";
 		document.getElementById("processing_tables").style.display="block";
 		InsertTableByAjax(selstr);
 	}
 	else {
 		alert("Not all the foreign tables have been taken care of!");
 	}
 }

 /**
  * This will insert the table information into the database by calling InsertTableByAjax in  ajaxlib.php
  */
 function InsertTableByAjax (selstr) {
   	var req = getXMLHttpRequest();
	var resp = document.getElementById("processing_tables");
	var qs = "?func=InsertTableByAjax&selstr="+selstr;
	if(!req) {
		resp.innerHTML = "This function requires Ajax.\nPlease update your browser.\nhttp://www.getfirefox.com";
	}
  	req.onreadystatechange = function(){
 		if(req.readyState == 4){
 				resp.innerHTML = req.responseText;
	    		resp.style.display="block";
		}
  	}
  	req.open("GET", "includes/ajaxlib.php"+qs, true);
  	req.send(null);
}

/**
 * Handle login in ajax
 */
 function ajaxLogin() {
 	/* get the input in the upperleftlogin form */
 	var formLogin=document.getElementById("upperleftlogin");
 	var loginStr="";
 	for (var i=0; i<formLogin.length; i++) {
 		if (formLogin.elements[i].type=="text" || formLogin.elements[i].type=="password") {
 			loginStr+=formLogin.elements[i].type+","+formLogin.elements[i].value+";";
 		}
 	}
 	/* use ajax function ajaxLogin to verify the username and password*/
 	var req = getXMLHttpRequest();
 	var resp=document.getElementById("thtLoginInfo");
 	var qs = "?func=ajaxLogin&loginstr="+loginStr;
 	req.onreadystatechange = function(){
 		if(req.readyState == 4){
 				resp.innerHTML = req.responseText;
 				window.location="login/index.php";
		}
  	}
  	req.open("GET", "includes/ajaxlib.php"+qs, true);
  	req.send(null);
}


/**
 * Handle logout in ajax
 */
 function ajaxLogout() {
	var req = getXMLHttpRequest();
 	var resp=document.getElementById("thtLoginInfo");
 	var qs = "?func=ajaxLogout";
 	req.onreadystatechange = function(){
 		if(req.readyState == 4){
 			resp.innerHTML = req.responseText;

 			// Gavin's note: This is an hard-coded absolute url, which is bad.
 			// 				Since we no longer use this function, I'll let it slide.
 			window.location="http://lab.bcb.iastate.edu/sandbox/yhames04/login.php?logout=true";
		}
  	}
  	req.open("GET", "includes/ajaxlib.php"+qs, true);
  	req.send(null);
 }

 /**
  * Handle registration in ajax
  */
function ajaxRegister() {

	/* first, disable in upperleft login form */
	var formLogin=document.getElementById("upperleftlogin");
	for (var i=0; i<formLogin.length; i++) {
		formLogin.elements[i].disabled=true;
	}

	/* get the values of the fields in the registration form */
	var formReg=document.getElementById("userReg");
	var regStr="";
 	for (var i=0; i<formReg.length; i++) {
 			regStr+=formReg.elements[i].type+","+formReg.elements[i].value+";";
 	}

 	/* ajax */
 	var req = getXMLHttpRequest();
 	var resp=document.getElementById("regInput");
 	var msgdiv=document.getElementById("noteMsg");
 	var qs = "?func=ajaxRegister&regstr="+regStr;
 	req.onreadystatechange = function(){
 		if(req.readyState == 4){
 			var xmlDoc=req.responseXML;
 			var flag=xmlDoc.getElementsByTagName("register_success")[0].firstChild.nodeValue;
 			var msg=xmlDoc.getElementsByTagName("register_message")[0].firstChild.nodeValue;
 			msgdiv.innerHTML=msg;
 			if (flag>0) {
 				formReg.reset();
 				for (var i=0; i<formReg.length; i++) {
					formReg.elements[i].disabled=true;
				}
				/* Now we re-enable the login form. Because its annoying to register and then have the login form disabled */
				for (var i=0; i<formLogin.length; i++) {
					formLogin.elements[i].disabled = false;
				}
 			}
		}
  	}
  	req.open("GET", "includes/ajaxlib.php"+qs, true);
  	req.send(null);
 }

 /**
  * display the input form for general table input
  */
 function DispTblInputFrm() {
 	/* get the selected table name*/
 	var tableSel=document.getElementById("table_sel");
 	var tblName=tableSel.options[tableSel.selectedIndex].text;
 	var req = getXMLHttpRequest();
 	var qs = "?func=ajaxTableForm&tablename="+tblName;
 	var resp1=document.getElementById("target_table_select");
 	var resp2=document.getElementById("table_input_form");
 	req.onreadystatechange = function(){
 		if(req.readyState == 4){
 			//alert(req.responseText);
 			// var xmlDoc=req.responseXML;
 			// var flag=xmlDoc.getElementsByTagName("form_success")[0].firstChild.nodeValue;
 			// var formcode=xmlDoc.getElementsByTagName("form_code")[0].firstChild.nodeValue;
 			resp1.style.display="none";
 			resp2.style.display="block";
 			resp2.innerHTML=req.responseText;
 			// if (flag==0) {
 				// resp2.innerHTML=formcode;
 			// }
 			// else {
 				//resp2.innerHTML="<p>Error</p>";
 			// }
		}
  	}
  	req.open("GET", "includes/ajaxlib.php"+qs, true);
  	req.send(null);
 }

 /**
  * submit the contents in a table
  */
 function submitFormInput (tbl) {
 	/* get the form content */
 	var tblForm=document.getElementById(tbl+"_inputform");
 	var formSubmitStr="tablename,"+tbl+";";
 	for (var i=0; i<tblForm.length; i++) {
		formSubmitStr+=tblForm.elements[i].name+","+URLencode(tblForm.elements[i].value)+";";
	}
	/* ajax */
	var req = getXMLHttpRequest();
 	var qs = "?func=ajaxSubmitForm&formsubmitstr="+formSubmitStr;
 	var resp=document.getElementById("table_input_form");
 	req.onreadystatechange = function(){
 		if(req.readyState == 4){
 			resp.innerHTML=req.responseText;
 			var xmlDoc=req.responseXML;
 			var flag=xmlDoc.getElementsByTagName("submit_success")[0].firstChild.nodeValue;
 			var submitmsg=xmlDoc.getElementsByTagName("submit_message")[0].firstChild.nodeValue;
 			resp.style.display="block";
 			resp.innerHTML=submitmsg;
 		}
  	}
  	req.open("GET", "includes/ajaxlib.php"+qs, true);
  	req.send(null);
 }

 /* the URLencode code from www.rgagnon.com */
 function URLencode(sStr) {
    return escape(sStr)
       .replace(/\+/g, '%2B')
          .replace(/\"/g,'%22')
             .replace(/\'/g, '%27');
 }


/* display the map range in marker_selection.php */
 function DispMapLin(value) {
        var req = getXMLHttpRequest();
        var qs = "?func=DispMapLin&mapname="+value;
        if(!req) {
                resp.innerHTML = "This function requires Ajax.\nPlease update your browser.\nhttp://www.getfirefox.com";
        }
        req.onreadystatechange = function(){
                if(req.readyState == 4){
                        var resp = document.getElementById("markeSelTab").rows[1].cells[1];
                        resp.innerHTML = req.responseText;
                }
        }
        req.open("GET", "includes/ajaxlib.php"+qs, true);
        req.send(null);
        resp = document.getElementById("markeSelTab").rows[1].cells[2];
        resp.innerHTML = "Choose map.";
}

 /* display the map range in marker_selection.php */
 function DispMapSel(value) {
  	var req = getXMLHttpRequest();
	var qs = "?func=DispMapSel&mapname="+value;
	if(!req) {
		resp.innerHTML = "This function requires Ajax.\nPlease update your browser.\nhttp://www.getfirefox.com";
	}
  	req.onreadystatechange = function(){
 		if(req.readyState == 4){
 			var resp = document.getElementById("markeSelTab").rows[1].cells[2];
 			resp.innerHTML = req.responseText;
		}
  	}
  	req.open("GET", "includes/ajaxlib.php"+qs, true);
  	req.send(null);
}

/* display the marker name in marker_selection.php */
function DispMarkers (mapuid) {
	var mapstt=document.getElementById('mapstt').value;
	var mapend=document.getElementById('mapend').value;
	var req = getXMLHttpRequest();
	var qs = "?func=DispMarkers&mapuid="+mapuid+"&mapstt="+mapstt+"&mapend="+mapend;
	if(!req) {
		resp.innerHTML = "This function requires Ajax.\nPlease update your browser.\nhttp://www.getfirefox.com";
	}
  	req.onreadystatechange = function(){
 		if(req.readyState == 4){
 			var resp = document.getElementById("markeSelTab").rows[1].cells[3];
 			resp.innerHTML = req.responseText;
		}
  	}
  	req.open("GET", "includes/ajaxlib.php"+qs, true);
  	req.send(null);
}

/*
 * This function is the ajax interface between the phenotype table in the advanced_search.php and
 * its php backend.  It's fairly basic ajax.
 */
function DispPhenoSel(value, middle, phenotype_uid) {
  	var req = getXMLHttpRequest();
	var column = 1;
	if(middle == "Phenotype")
	    { column = 2; }
        else if(middle == "Trial")
	    { column = 3; }
	else {
	    document.getElementById("phenotypeSelTab").rows[1].cells[2].innerHTML = "";
	}
 	document.getElementById('phenotypeSelTab').rows[2].cells[1].innerHTML = "<b>Values</b><br>Mean:<br>Range:<p>Search between:<br><input type='text' name='first_value'><br>and<br><input type='text' name='last_value'><br><input type='submit' value='Search'></form>";
	document.getElementById("phenotypeSelTab").rows[2].cells[2].innerHTML = "";

	var resp = document.getElementById("phenotypeSelTab").rows[1].cells[column];
	var qs = "?func=Disp"+middle+"Sel&id="+value;
	if(middle == "Trial") {
	    qs = qs+"&phenotypeid="+phenotype_uid; 
	    var trialsSelected = "";
	    var trialsMenu = document.getElementById("trialoptions");
	    var i;
	    for (i=0; i<trialsMenu.options.length; i++) {
	    	if (trialsMenu.options[i].selected) {
	    	    trialsSelected = trialsSelected+trialsMenu.options[i].value+",";
	    	}
	    }
	    qs = qs+"&trialsSelected="+trialsSelected; 
	}

	if(!req) {
	    resp.innerHTML = "This function requires Ajax. Please update your browser. http://www.getfirefox.com";
	}
  	req.onreadystatechange = function() {
	    if(req.readyState == 4) {
		if(middle != "Trial") {resp.innerHTML = req.responseText;}
		else {
		    document.getElementById("phenotypeSelTab").rows[2].cells[1].innerHTML = req.responseText;
		    // Works on Firefox, not on IE:
		    //document.getElementById("phenotypeSelTab").rows[2].cells[2].innerHTML = '<img src='+<?php echo $config['base_url'] ?>+'downloads/temp/histogram.jpg id=histogram>';
		    // Works on both but hardcoded.
		    //document.getElementById("phenotypeSelTab").rows[2].cells[2].innerHTML = "<img src=http://wheat.pw.usda.gov/t3/wheat/downloads/temp/histogram.jpg id=histogram>";
		    // Use relative URL, sigh.
		    document.getElementById("phenotypeSelTab").rows[2].cells[2].innerHTML = "<img src=/tmp/tht/histogram.jpg id=histogram>";
		    // <img src... won't reload without a page refresh, unless we change the
		    // value.  Appending "?m=..." to it works, in Firefox and IE7.
		    // Tip from www.sitepoint.com/forums/showthread.php?t=688372
		    var el = document.getElementById("histogram");
		    el.setAttribute('src', el.getAttribute('src') + '?m='+Math.random());
		}
	    }
  	};
  	req.open("GET", "includes/ajaxlib.php"+qs, true);
  	req.send(null);
}

/*
 * Modification of DispPhenoSel() for Select Lines by Properties.
 */
function DispPropSel(val, middle) {
    var req = getXMLHttpRequest();
    if(!req) {
	document.getElementById("PropertySelTable").innerHTML = "This function requires Ajax.";
    }
    var column = 1;
    if (middle === "Property") {
	column = 2; 
    } else {
    	document.getElementById("PropertySelTable").rows[1].cells[2].innerHTML = "";
    }
    var resp = document.getElementById("PropertySelTable").rows[1].cells[column];
    var qs = "?func=Disp"+middle+"Sel&id="+val;
    req.onreadystatechange = function() {
	if (req.readyState == 4) {
	    if (middle === "PropValue") {
		// Clear rows[1].cells[1]. Required for Firefox but not Chrome or IE.
		resp.innerHTML = "";  
		// Display the final choice below the menus, appending to previous choices.
		resp = document.getElementById("PropertySelTable").rows[2].cells[0];
		resp.innerHTML += req.responseText;
	    }
	} else {
	    resp.innerHTML = req.responseText;
        }
    };
    req.open("GET", "includes/ajaxlib.php"+qs, true);
    req.send(null);
}

/**
 * This function offers a link to ajax so that php functions may be called through javascript
 */
 function callAjaxFunc(fname, fpara, id) {
 	var req= getXMLHttpRequest();
 	var resp=document.getElementById(id); // id refer to a button
 	var qs = "?func="+fname+fpara;
 	if(!req) {
		alert("Browser not supporting Ajax");
	}
  	req.onreadystatechange = function(){
 		if(req.readyState == 4){
 			resp2=document.getElementById('ajaxMsg');
 			resp2.innerHTML=req.responseText;
 			resp.value="Done";
 			resp.disabled=true;
		}
  	}
  	req.open("GET", "includes/ajaxlib.php"+qs, true);
  	req.send(null);
}
