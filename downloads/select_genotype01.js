/*global $,$$,$A,$H,Prototype,Ajax,Template,Element*/

/* used for ajax calls in select lines by genotype experiment */

var select1_str = "";
var platform_str = "";
var experiments_str = "";
var breeding_programs_str = "";
var years_str = "";
var lines_str = "";
var subset = "";
var php_self = document.location;
var title = document.title;

var traits_loaded = false;
var markers_loaded = false;
var markers_loading = false;
var traits_loading = false;

function update_side() {
    var url = "side_menu.php";
    var tmp = new Ajax.Updater($('quicklinks'), url, {
    onComplete : function() {
      $('quicklinks').show();
      document.title = title;
      document.getElementById('step5').innerHTML = "Selection saved";
    }
    });
}

function update_combine(options) {
    subset = options;
    load_lines3();
}

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
            update_side();
        }
    });
    document.getElementById('step2').innerHTML = "";
    document.getElementById('step3').innerHTML = "";
    document.getElementById('step4').innerHTML = "";
}

function load_title(command) {
    var url = php_self + "?function=refreshtitle&lines=" + lines_str + "&exps=" + experiments_str + '&cmd=' + command + '&subset=' + subset;
    var tmp = new Ajax.Updater($('title'), url, {
        onComplete : function() {
            $('title').show();
            document.title = title;
            update_side();
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

function load_platform() {
    $('step11').hide();
    var url = php_self + "?function=step1platform&bp=" + breeding_programs_str + '&yrs=' + years_str;
    document.title = 'Loading Step1...';
    var tmp = new Ajax.Updater($('step11'), url,
    {
        onComplete : function() {
            $('step11').show();
            document.title = title;
        }
    });
}

function load_yearprog() {
  $('step2').hide();
  var url=php_self + "?function=step1yearprog&bp=" + breeding_programs_str + "&yrs=" + years_str;
  document.title='Loading Step1...';
  var tmp = new Ajax.Updater($('step2'),url,
  {
      onComplete: function() {
        $('step2').show();
        document.title=title;
  }
      }
      );
      document.getElementById('step3').innerHTML = "";
      document.getElementById('step4').innerHTML = "";
      document.getElementById('step5').innerHTML = "";
}

function load_lines3() {
    $('step5').hide();
    var url = php_self + "?function=type1markers&lines=" + lines_str + '&exps=' + experiments_str + '&subset=' + subset;
    document.title = 'Loading Step1...';
    var tmp = new Ajax.Updater($('step5'), url, {
        onComplete : function() {
            $('step5').show();
            document.title = title;
        }
    });
}

function load_markers() {
  markers_loading = true;
    $('step5').hide();
    var url=php_self + "?function=type1markers&bp=" + breeding_programs_str + '&lines=' + lines_str + '&exps=' + experiments_str;
    document.title='Loading Markers...';
    var tmp = new Ajax.Updater($('step5'),url,
         {  onComplete: function() {
             $('step5').show();
            if (traits_loading === false) {
                document.title = title;
            }
            markers_loading = false;
            markers_loaded = true;
            //load_title();
        }}
    );
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
    document.getElementById('step5').innerHTML = "";
}       

function load_experiments2()
{
    $('step2').hide();
    var url = php_self + "?function=type2experiments&platform=" + platform_str;
    var tmp = new Ajax.Updater($('step2'), url,
    {
        onComplete: function() {
            $('step2').show();
            document.title = title;
        }
    });
    document.getElementById('step3').innerHTML = "";
}

function update_breeding_programs(options) {
    breeding_programs_str = "";
    experiments_str = "";
    years_str = "";
		
    $A(options).each(
            function(breeding_program) {
		if (breeding_program.selected) {
			breeding_programs_str += (breeding_programs_str === "" ? "" : ",") + breeding_program.value;
		}
            });
   load_yearprog();
   document.getElementById('step3').innerHTML = "";
   document.getElementById('step4').innerHTML = "";
   document.getElementById('step5').innerHTML = "";
}

function update_platform(options) {
    platform_str = "";
    experiments_str = "";

    $A(options).each(
        function(platform) {
            if (platform.selected) {
                platform_str += (platform_str === "" ? "" : ",") + platform.value;
            }
        });
    load_experiments2();
    document.getElementById('step3').innerHTML = "";
    document.getElementById('step4').innerHTML = "";
    document.getElementById('step5').innerHTML = "";
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
    document.getElementById('step4').innerHTML = "";
    document.getElementById('step5').innerHTML = "";
}

function update_line_trial(options) {
    select1_str = "Lines";
    experiments_str = "";
    $A(options).each(function(experiment) {
        if (experiment.selected) {
            experiments_str += (experiments_str === "" ? "" : ",") + experiment.value;
        }
    });
    load_lines3();
    document.getElementById('step5').innerHTML = "";
}

function update_lines(options) {
    lines_str = "";
    $A(options).each(function(lines) {
        if (lines.selected) {
            lines_str += (lines_str === "" ? "" : ",") + lines.value;
        }
    });
    load_markers();
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

function update_select1(options) {
  select1_str = "";
  lines_str = "";
  $A(options).each(function(select1) {
      if (select1.selected) {
         select1_str = select1.value;
      }
  });
  document.getElementById('step2').innerHTML = "";
  document.getElementById('step3').innerHTML = "";
  document.getElementById('step4').innerHTML = "";
  document.getElementById('step5').innerHTML = "";
  if (select1_str == "DataProgram") {
    load_breedprog();
  } else if (select1_str == "Platform") {
    load_platform();
  }
}

function update_experiments(options) 
{
    experiments_str = "";
    var target = "";

    $A(options).each(function(experiments) {
        if (experiments.selected) {
            experiments_str +=  (experiments_str === "" ? "" : ",") + experiments.value;
        }
    });

    var url = php_self + "?function=step3lines&exps=" + experiments_str;
    if (select1_str == "DataProgram") {
      target = 'step4'; 
    } else {
      target = 'step3';
    }
    var tmp = new Ajax.Updater($(target), url, {
            onComplete: function() {
                $('step3').show();
                load_markers();
            }
        }
    );
}
