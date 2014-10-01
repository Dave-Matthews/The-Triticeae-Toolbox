/*global alert,$,$$,$A,$H,Prototype,Ajax,Template,Element,getXMLHttpRequest*/
var php_self = document.location.href;
var breeding_programs_str = "";
var phenotype_categories_str = "";
var phenotype_items_str = "";
var locations_str = "";
var years_str = "";
var lines_str = "";
var experiments_str = "";
var experiments_loaded = false;
var traits_loaded = false;
var markers_loaded = false;
var select1_str = "";
var select2_str = "";
var subset = "";
var trait_cmb = "";
var traits_remove = "";
var trials_remove = "";
var lines_within = "";

var markerids = null;
var selmarkerids = [];

var markers_loading = false;
var traits_loading = false;

var title = document.title;

function load_title(command) {
    var url = php_self + "?function=refreshtitle&lines=" + lines_str + "&exps=" + experiments_str + '&pi=' + phenotype_items_str + '&subset=' + subset + '&cmd=' + command;
    var tmp = new Ajax.Updater($('title'), url, {
        onComplete : function() {
            $('title').show();
            document.title = title;
        }
    });
    url = "side_menu.php";
    tmp = new Ajax.Updater($('quicklinks'), url, {
    onComplete : function() {
      $('quicklinks').show();
      document.title = title;
    }
    });
}

function use_normal() {
    breeding_programs_str = "";
    years_str = "";
    lines_str = "";
    subset = "";
    var url = php_self + "?function=type1&bp=" + breeding_programs_str + '&yrs=' + years_str;
    var tmp = new Ajax.Updater($('step1'), url, {
        onComplete : function() {
            $('step1').show();
            document.title = title;
            load_title();
        }
    });
    document.getElementById('step2').innerHTML = "";
    document.getElementById('step3').innerHTML = "";
    document.getElementById('step4').innerHTML = "";
    document.getElementById('step4b').innerHTML = "";
    document.getElementById('step5').innerHTML = "";
}

function phenotype_save() {
    traits_remove = "";
    trials_remove = "";
    var url = php_self + "?function=refreshtitle&pi=" + phenotype_items_str + "&exps=" + experiments_str + '&cmd=save';
    var tmp = new Ajax.Updater($('title'), url, {asynchronous:false}, {
        onComplete : function() {
            $('title').show();
            document.title = title;
        }
    });
    url = "side_menu.php";
    tmp = new Ajax.Updater($('quicklinks'), url, {
    onComplete : function() {
      $('quicklinks').show();
      document.title = title;
    }
    });
    document.getElementById('step5').innerHTML = "";
}

function phenotype_deselect() {
    var url = php_self + "?function=refreshtitle&pi=" + traits_remove + '&cmd=deselect';
    var tmp = new Ajax.Updater($('title'), url, {
        onComplete : function() {
            $('title').show();
            document.title = title;
            load_title();
        }
    });
}

function trials_deselect() {
    var url = php_self + "?function=refreshtitle&exp=" + trials_remove + '&cmd=deselect';
    var tmp = new Ajax.Updater($('title'), url, {
        onComplete : function() {
            $('title').show();
            document.title = title;
            load_title();
        }
    });
}


function load_experiments() {
    $('step3').hide();
    var url = php_self + "?function=type1experiments&bp=" + breeding_programs_str + '&yrs=' + years_str;
    document.title='Loading Trials...';
    var tmp = new Ajax.Updater($('step3'),url,
    { 
                onComplete: function() {
                    $('step3').show();
                document.title=title;
                }
            }
        );
        experiments_loaded = true;
        //if (traits_loaded == true)
        //load_traits();
        //if (markers_loaded == true)
        //load_markers(100, 0);
    }

function load_programs5() {
    $('step4b').hide();
    var url = php_self + "?function=step5programs&exps=" + experiments_str + '&pi=' + phenotype_items_str + '&subset=' + subset;
    document.title = 'Loading Step1...';
    var tmp = new Ajax.Updater($('step4b'), url, {
        onComplete : function() {
            $('step4b').show();
            document.title = title;
        }
    });
}

function load_phenotypes2() {
    if (document.getElementById("selectwithin").checked) {
      lines_within = "yes";
    } else {
      lines_within = "no";
    }
    $('step2').hide();
    var url = php_self + "?function=step2phenotype&pc=" + phenotype_categories_str + "&lw=" + lines_within;
    document.title = 'Loading Step1...';
    var tmp = new Ajax.Updater($('step2'), url, {
        onComplete : function() {
            $('step2').show();
            document.title = title;
        }
    });
}

function load_phenotypes3() {
    $('step3').hide();
    var url = php_self + "?function=step3phenotype&pi=" + phenotype_items_str + "&trait_cmb=" + trait_cmb + "&lw=" + lines_within;
    document.title = 'Loading Step1...';
    var tmp = new Ajax.Updater($('step3'), url, {
        onComplete : function() {
            $('step3').show();
            document.title = title;
        }
    });
}

function load_phenotypes4() {
    $('step4').hide();
    var url = php_self + "?function=step4phenotype&pi=" + phenotype_items_str + "&e=" + experiments_str + "&lines=" + lines_str + '&subset=' + subset;
    document.title = 'Loading Step1...';
    var tmp = new Ajax.Updater($('step4'), url, {
        onComplete : function() {
            $('step4').show();
            document.title = title;
        }
    });
}

function load_phenotypes5() {
    $('step5').hide();
    var url = php_self + "?function=step5phenotype&pi=" + phenotype_items_str + "&e=" + experiments_str + "&lines=" + lines_str + '&subset=' + subset;
    document.title = 'Loading Step1...';
    var tmp = new Ajax.Updater($('step5'), url, {
        onCreate: function() { Element.show('spinner'); },
        onComplete : function() {
            $('step5').show();
            document.title = title;
            load_title();
        }
    });
}

function save_pheno_selection() {
    $('step5').hide();
    var url = php_self + "?function=step5phenotype&pi=" + phenotype_items_str + "&e=" + experiments_str + '&lw=' + lines_within;
    document.title = 'Loading Step1...';
    var tmp = new Ajax.Updater($('step5'), url, {
        onComplete : function() {
            $('step5').show();
            document.title = title;
            load_title();
        }
    });
}
function load_lines() {
    $('step11').hide();
    var url = php_self + "?function=step1lines&bp=" + breeding_programs_str + '&lw=' + lines_within;
    document.title = 'Loading Step1...';
    var tmp = new Ajax.Updater($('step11'), url, {
        onComplete : function() {
            $('step11').show();
            document.title = title;
        }
    });
    document.getElementById('step2').innerHTML = "";
    document.getElementById('step3').innerHTML = "";
    document.getElementById('step4').innerHTML = "";
    document.getElementById('step5').innerHTML = "";
}
function load_lines2() {
    $('step2').hide();
    var url = php_self + "?function=step2lines&bp=" + breeding_programs_str + '&yrs=' + years_str;
    document.title = 'Loading Step1...';
    var tmp = new Ajax.Updater($('step2'), url, {
        onComplete : function() {
            $('step2').show();
            document.title = title;
        }
    });
    document.getElementById('step3').innerHTML = "";
}

function load_lines3() {
    $('step3').hide();
    var url = php_self + "?function=step3lines&e=" + experiments_str + '&pi=' + phenotype_items_str;
    document.title = 'Loading Step1...';
    var tmp = new Ajax.Updater($('step3'), url, {
        onComplete : function() {
            $('step3').show();
            document.title = title;
        }
    });
    document.getElementById('step4').innerHTML = "";
}

function load_lines4() {
    $('step4').hide();
    var url = php_self + "?function=step4lines&e=" + experiments_str + '&pi=' + phenotype_items_str;
    document.title = 'Loading Step1...';
    var tmp = new Ajax.Updater($('step5'), url, {
        onComplete : function() {
            $('step4').show();
            document.title = title;
        }
    });
}

function load_traits()
{		
    traits_loading = true;		
    $('step4').hide();
    var url=php_self + "?function=type1traits&exps=" + experiments_str;
    document.title='Loading Traits...';
	var tmp = new Ajax.Updater(
	    $('step4'),url,
		{onComplete: function() {
            $('step4').show();  
            if (markers_loading === false) {
                document.title = title;
            }
            traits_loading = false;
            traits_loaded = true;
        }}
	);
}

function load_markers( mm, mmaf) {
    markers_loading = true;
    $('step5').hide();
    var url=php_self + "?function=type1markers&bp=" + breeding_programs_str + '&lines=' + lines_str + '&exps=' + experiments_str + '&mm=' + mm + '&mmaf=' + mmaf + '&subset=' + subset;
    document.title='Loading Markers...';
    var tmp = new Ajax.Updater($('step5'),url,
        {onCreate: function() { Element.show('spinner'); },
            onComplete: function() {
             $('step5').show();
            if (traits_loading === false) {
                document.title = title;
            }
            markers_loading = false;
            markers_loaded = true;
            load_title();
        }}
    );
}

function load_markers_loc( mm, mmaf) {
    markers_loading = true;
    $('step5').hide();
    var url=php_self + "?function=type2markers&exps=" + experiments_str + '&pi=' + phenotype_items_str + '&lines=' + lines_str + '&mm=' + mm + '&mmaf=' + mmaf + '&subset=' + subset;
    document.title='Loading Markers...';
    var tmp = new Ajax.Updater($('step5'),url,
        {onCreate: function() { Element.show('spinner'); },
            onComplete: function() {
             $('step5').show();
            if (traits_loading === false) {
                document.title = title;
            }
            markers_loading = false;
            markers_loaded = true;
            load_title();
        }}
    );
}

function load_breedprog() {
    $('step1').hide();
    var url = php_self + "?function=step1breedprog&bp=" + breeding_programs_str + '&yrs=' + years_str;
    document.title = 'Loading Step1...';
    var tmp = new Ajax.Updater($('step11'), url,
    {
        onComplete : function() {
            $('step1').show();
            document.title = title;
        }
    });
    document.getElementById('step2').innerHTML = "";
    document.getElementById('step3').innerHTML = "";
}

function update_breeding_programs(options) {
    breeding_programs_str = "";
    experiments_str = "";
    select1_str = "BreedingProgram";
    $A(options).each(
            function(breeding_program) {
                if (breeding_program.selected) {
                    breeding_programs_str += (breeding_programs_str === "" ? "" : ",") + breeding_program.value;
                }
            });
    if (breeding_programs_str !== "" && years_str !== "") {
        load_experiments();
    }
    document.getElementById('step3').innerHTML = "";
    document.getElementById('step4').innerHTML = "";
    document.getElementById('step5').innerHTML = "";
}

function update_phenotype_categories(options) {
    phenotype_categories_str = "";
    phenotype_items_str = "";
    experiments_str = "";
    lines_str = "";
    select1_str = "Phenotypes";
    $A(options).each(
                function(phenotype_categories) {
            if (phenotype_categories.selected) {
                phenotype_categories_str += (phenotype_categories_str === "" ? "" : ",") + phenotype_categories.value;
            }
        });
    load_phenotypes2();
    document.getElementById('step3').innerHTML = "";
    document.getElementById('step4').innerHTML = "";
    document.getElementById('step5').innerHTML = "";
}

function update_phenotype_items(options) {
    phenotype_items_str = "";
    lines_str = "";
    $A(options)
            .each(
                    function(phenotype_items) {
                        if (phenotype_items.selected) {
                            phenotype_items_str += (phenotype_items_str === "" ? ""
                                    : ",") + phenotype_items.value;
                        }
                    });
    load_phenotypes3();
    document.getElementById('step4').innerHTML = "";
    document.getElementById('step5').innerHTML = "";
}

function remove_phenotype_items(options) {
  traits_remove = "";
  $A(options)
            .each(
                    function(phenotype_items) {
                        if (phenotype_items.selected) {
                            traits_remove += (traits_remove === "" ? ""
                                    : ",") + phenotype_items.value;
                        }
                    });
}

function remove_trial_items(options) {
  trials_remove = "";
  $A(options)
            .each(
                    function(phenotype_items) {
                        if (phenotype_items.selected) {
                            trials_remove += (trials_remove === "" ? ""
                                    : ",") + phenotype_items.value;
                        }
                    });
}

function update_phenotype_trial(options) {
	experiments_str = ""; 
	lines_str = "";
	$A(options).each(function(experiment) {
		if (experiment.selected) {
			experiments_str += (experiments_str === "" ? "" : ",") + experiment.value;
		}
	});
	//load_phenotypes4();
        save_pheno_selection();
	//load_title();
	//load_phenotypes5();
}

function update_phenotype_trialb(options) {
/*used to update the trials with any/all traits */
    trait_cmb = options;
    load_phenotypes3();
    document.getElementById('step4').innerHTML = "";
}

function update_line_trial(options) {
    select1_str = "Lines";
    experiments_str = ""; 
    phenotype_items_str = "";
    $A(options).each(function(experiment) {
        if (experiment.selected) {
            experiments_str += (experiments_str === "" ? "" : ",") + experiment.value;
        }
    });
    load_lines3();
    load_lines4();
}

function update_line_pheno(options) {
    phenotype_items_str = "";
    $A(options).each(function(traits) {
        if (traits.selected) {
            phenotype_items_str += (phenotype_items_str === "" ? "" : ",") + traits.value;
        }
    });
    load_lines4();
}

function update_phenotype_lines(options) {
    lines_str = "";
    $A(options).each(function(lines) {
        if (lines.selected) {
            lines_str += (lines_str === "" ? "" : ",") + lines.value;
        }
    });
    if (select1_str == "Phenotypes") {
        load_phenotypes5();
    } else if (select1_str == "Locations") {
        load_markers_loc();
    } else if (select1_str == "BreedingProgram") {
        load_markers();
    }
}
function update_lines_within(options) {
/*used when updateing radio button for traits and trials*/
    if (document.getElementById("selectwithin").checked) {
      lines_within = "yes";
    } else {
      lines_within = "no";
    }
    select1_str = "Phenotypes";
    if (phenotype_categories_str === "") {
    } else {
      load_phenotypes2();
      document.getElementById('step3').innerHTML = "";
      document.getElementById('step4').innerHTML = "";
      document.getElementById('step5').innerHTML = "";
    }
}
function update_phenotype_linesb(options) {
/*used when updating radio button for line selection (session, all, combine) */
    subset = options;
    if (select1_str == "Phenotypes") {
        load_phenotypes4();
        load_phenotypes5();
    } else if (select1_str == "BreedingProgram") {
        load_programs5();
        load_markers('', '', 100, 0);
    }
}
		
			function update_experiments(options) {
				experiments_str = ""; // clears experiment setting to avoid trait perserverance
				$A(options).each(function(experiment) {
					if (experiment.selected) {
						experiments_str += (experiments_str === "" ? "" : ",") + experiment.value;
					}
				});
                                document.getElementById('step4').innerHTML = "";
                                document.getElementById('step4b').innerHTML = "";
                                document.getElementById('step5').innerHTML = "";
				load_traits();
			}

			function load_phenotypes() {
			    $('step11').hide();
			    var url = php_self + "?function=step1phenotype&bp=" + breeding_programs_str + "&lw=" + lines_within;
			    document.title = 'Loading Step1...';
			    var tmp = new Ajax.Updater($('step11'), url, {
			        onComplete : function() {
			            $('step11').show();
			            document.title = title;
			        }
			    });
			    document.getElementById('step2').innerHTML = "";
			    document.getElementById('step3').innerHTML = "";
			    document.getElementById('step4').innerHTML = "";
                            document.getElementById('step5').innerHTML = "";
			}

			function load_yearprog() {
			    $('step11').hide();
                var url=php_self + "?function=step1yearprog&bp=" + breeding_programs_str + "&yrs=" + years_str;
                document.title='Loading Step1...';
                var tmp = new Ajax.Updater($('step11'),url,
                        { 
                                onComplete: function() {
                                    $('step11').show();
                                    document.title=title;
                                }
                        }
                    );
                                document.getElementById('step2').innerHTML = "";
                                document.getElementById('step3').innerHTML = "";
                                document.getElementById('step4').innerHTML = "";
                }						
			
			function use_session(version) {
			    var mm = $('mm').getValue();
                var mmaf = $('mmaf').getValue();
                var url=php_self + "?function=download_session_" + version + "&bp=" + breeding_programs_str+'&yrs='+ years_str+'&e='+experiments_str+'&mm='+mm+'&mmaf='+mmaf;
                document.location = url;
            }

			function get_download_loc(version) {
			    var mm = $('mm').getValue();
                var mmaf = $('mmaf').getValue();
                var url=php_self + "?function=type2_build_tassel_" + version + "&lines=" + lines_str+'&yrs='+ years_str+'&e='+experiments_str+'&pi='+phenotype_items_str+'&subset='+subset+'&mm='+mm+'&mmaf='+mmaf;
                document.location = url;
			}
			
			function load_markers_pheno( mm, mmaf) {
                markers_loading = true;
                $('step5').hide();
                var url=php_self + "?function=step5phenotype&bp=" + breeding_programs_str + '&yrs=' + years_str + '&exps=' + experiments_str + '&mm=' + mm + '&mmaf=' + mmaf;
                document.title='Loading Markers...';
                //changes are right here
                var tmp = new Ajax.Updater($('step5'),url,
                    {onCreate: function() { Element.show('spinner'); },
                        onComplete: function() {
                        $('step5').show();
                        if (traits_loading === false) {
                            document.title = title;
                        }
                        markers_loading = false;
                        markers_loaded = true;
                        load_title();
                    }}
                );
            }

			function load_markers_lines( mm, mmaf) {
			    select1_str = "Lines";
                markers_loading = true;
                $('step5').hide();
                var url=php_self + "?function=step4lines&bp=" + breeding_programs_str + '&yrs=' + years_str + '&exps=' + experiments_str + '&mm=' + mm + '&mmaf=' + mmaf;
                document.title='Loading Markers...';
                //changes are right here
                var tmp = new Ajax.Updater($('step5'),url,
                    {onCreate: function() { Element.show('spinner'); },
                    onComplete: function() {
                         $('step5').show();
                        if (traits_loading === false) {
                            document.title = title;
                        }
                        markers_loading = false;
                        markers_loaded = true;
                        load_title();
                    }}
                );
            }
		
			function selectedTraits() {
				var ret = '';
				$A($('traitsbx').options).each(function(trait){
                                if (trait.selected)
                                        {
						ret += (ret === '' ? '' : ',') + trait.value;
					}			 
				});
				return ret;
			}
			
			function getdownload_qtlminer()
			{
				if (selectedTraits() === '') {
					alert('Please select at least one trait!');
					return false;
				}
			    var mm = $('mm').getValue();
                var mmaf = $('mmaf').getValue();
                var url=php_self + "?function=type1build_qtlminer&bp=" + breeding_programs_str+'&yrs='+ years_str+'&t='+selectedTraits()+'&e='+experiments_str+'&mm='+mm+'&mmaf='+mmaf;
				
					document.location = url;
				
			}

			function getdownload_tassel(version)
			{
				if (selectedTraits() === '') {
					alert("Please select at least one trait!");
					return false;
				}
			    var mm = $('mm').getValue();
                var mmaf = $('mmaf').getValue();
			    //var subset = $('subset').getValue();
			    var url=php_self + "?function=type2_build_tassel_" + version + "&lines=" + lines_str+'&yrs='+ years_str+'&e='+experiments_str+'&pi='+phenotype_items_str+'&subset='+subset+'&mm='+mm+'&mmaf='+mmaf;
               document.location = url;
			}

            function mrefresh() {
                var mm = $('mm').getValue();
                var mmaf = $('mmaf').getValue();
                if (select1_str == "Phenotypes") {
                    load_markers_pheno( mm, mmaf);
                } else if (select1_str == "Locations") {
                    load_markers_loc( mm, mmaf);
                } else if (select1_str == "Lines") {
                    load_markers_lines( mm, mmaf);
                } else {
                    load_markers( mm, mmaf);
                }
            }
            
function DispPhenoSel(value, middle, phenotype_uid) {
    var req = getXMLHttpRequest();

    var column = 1;
    if (middle == "Phenotype") {
        column = 2;
    } else if (middle == "Trial") {
        column = 3;
    } else {
        document.getElementById("phenotypeSelTab").rows[1].cells[2].innerHTML = "";
    }

    var resp = document.getElementById("phenotypeSelTab").rows[1].cells[column];
    var qs = "?func=Disp" + middle + "Sel&id=" + value;
    if (middle == "Trial") {
        qs = qs + "&phenotypeid=" + phenotype_uid;
        var trialsSelected = "";
        var trialsMenu = document.getElementById("trialoptions");
        var i;
        for (i = 0; i < trialsMenu.options.length; i++) {
            if (trialsMenu.options[i].selected) {
                trialsSelected = trialsSelected + trialsMenu.options[i].value + ",";
            }
        }
        qs = qs + "&trialsSelected=" + trialsSelected;
    }

    if (!req) {
        resp.innerHTML = "This function requires Ajax. Please update your browser. http://www.getfirefox.com";
    }
    req.onreadystatechange = function() {
        if (req.readyState == 4) {
            if (middle != "Trial") {
                resp.innerHTML = req.responseText;             
            }
        }
    };
    req.open("GET", "includes/ajaxlib.php" + qs, true);
    req.send(null);
}
