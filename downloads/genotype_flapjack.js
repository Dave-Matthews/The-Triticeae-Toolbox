/*global $,$$,$A,$H,Prototype,Ajax,Template,Element*/

var select1_str = "";
var experiments_str = "";
var breeding_programs_str = "";
var years_str = "";
var lines_str = "";
var php_self = document.location;
var title = document.title;

function use_normal() {
    breeding_programs_str = "";
    years_str = "";
    lines_str = "";
    experiments_str = "";
    var url = php_self + "?function=type1&bp=" + breeding_programs_str + '&yrs=' + years_str;
    var tmp = new Ajax.Updater($('step1'), url, {
        onComplete : function() {
            $('step1').show();
            document.title = title;
        }
    });
    document.getElementById('step2').innerHTML = "";
    document.getElementById('step3').innerHTML = "";
    document.getElementById('step4').innerHTML = "";
}

function load_title() {
    var url = php_self + "?function=refreshtitle";
    var tmp = new Ajax.Updater($('title'), url, {
        onComplete : function() {
            $('title').show();
            document.title = title;
        }
    });
}

function load_breedprog() {
    $('step11').hide();
    var url = php_self + "?function=step1breedprog&bp=" + breeding_programs_str + '&yrs=' + years_str;
    document.title = 'Loading Step1...';
    var tmp = new Ajax.Updater($('step11'), url,
    {
        onComplete : function() {
            $('step11').show();
            document.title = title;
        }
    });
}

function load_experiments()
{       
    $('step3').hide();
    var url = php_self + "?function=type1experiments&bp=" + breeding_programs_str + '&yrs=' + years_str;
    var tmp = new Ajax.Updater($('step3'), url,
    {
        onComplete: function() {
            $('step3').show();
            document.title = title;
        }
    });
    document.getElementById('step4').innerHTML = "";
}       

function update_breeding_programs(options) {
    breeding_programs_str = "";
    experiments_str = "";
				
				$A(options).each(function(breeding_program) {
					if (breeding_program.selected) {
						breeding_programs_str += (breeding_programs_str === "" ? "" : ",") + breeding_program.value;
					}
				});
				
				
				if (breeding_programs_str !== "" && years_str !== "")
				{				
					load_experiments();
				}
}
			
function update_years(options) {
				years_str = "";
				
				$A(options).each(function(year) {
					if (year.selected) {
						years_str += (years_str === "" ? "" : ",") + year.value;
					}
				});
				if ((breeding_programs_str !== "") && (years_str !== ""))
				{	
					load_experiments();
				}		
}

function load_lines() {
    $('step11').hide();
    var url = php_self + "?function=step1lines";
    document.title = 'Loading Step1...';
    var tmp = new Ajax.Updater($('step11'), url, {
        onComplete : function() {
            $('step11').show();
            document.title = title;
        }
    });
}

function load_lines2() {
    $('step2').hide();
    var url = php_self + "?function=step2lines";
    document.title = 'Loading Step1...';
    var tmp = new Ajax.Updater($('step2'), url, {
        onComplete : function() {
            $('step2').show();
            document.title = title;
        }
    });
}

function load_lines3() {
    $('step4').hide();
    var url = php_self + "?function=typeFlapJack2";
    document.title = 'Loading Step1...';
    var tmp = new Ajax.Updater($('step4'), url, {
        onComplete : function() {
            $('step4').show();
            document.title = title;
        }
    });
}

function update_select1(options) {
  select1_str = "";
  $A(options).each(function(select1) {
      if (select1.selected) {
         select1_str = select1.value;
      }
  });
  document.getElementById('step2').innerHTML = "";
  document.getElementById('step3').innerHTML = "";
  document.getElementById('step4').innerHTML = "";
  if (select1_str == "BreedingProgram") {
    load_breedprog();
  } else if (select1_str == "Lines") {
    load_lines();
    load_lines2();
    load_lines3();
  }
  /*load_title();*/
}

function load_tab_delimiter(options)
{
    experiments_str = "";
        
    $A(options).each(function(experiments) {    
        if (experiments.selected) {
            experiments_str +=  (experiments_str === "" ? "\"" : ",\"") + experiments.value + "\""  ;
        }
    });
    
    var url = php_self + "?function=typeFlapJack&trialcode=" + experiments_str;
    var tmp = new Ajax.Updater($('step4'), url, { 
            onComplete: function() {
                $('step4').show();       
            }
        }
    );    
}

function download_tab_delimiter()
{
  var url = php_self + "?function=typeDownload&trialcode=" + experiments_str; 
  var tmp = new Ajax.Updater($('step4'), url, {
            onCreate: function() { Element.show('spinner'); },
            onComplete: function() {
                $('step4').show();
                Element.hide('spinner');
            }
        }
    ); 
}

function download_tab_delimiter2()
{
  var url = php_self + "?function=typeDownload2"; 
  var tmp = new Ajax.Updater($('step4'), url, {
            onCreate: function() { Element.show('spinner'); },
            onComplete: function() {
                $('step4').show();
                Element.hide('spinner');
            }
        }
    ); 
}
