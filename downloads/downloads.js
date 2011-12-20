var php_self = "downloads/downloads.php";
var breeding_programs_str = "";
var years_str = "";
var lines_str = "";
var experiments_str = "";
var experiments_loaded = false;
var traits_loaded = false;
var markers_loaded = false;

var markerids = null;
var selmarkerids = new Array();

var markers_loading = false;
var traits_loading = false;

var title = document.title;

function use_normal() {
    breeding_programs_str = "";
    years_str = "";
    var url = php_self + "?function=type1&bp=" + breeding_programs_str + '&yrs=' + years_str;
    new Ajax.Updater($('step1'), url, {
        onComplete : function() {
            $('step1').show();
            document.title = title;
        }
    });
}
            
function update_select1(options) {
    select1_str = "";
    breeding_programs_str = "";
    experiments_str = "";
    $A(options).each(function(select1) {
        if (select1.selected) {
            select1_str = select1.value;
        }
    });
    if (select1_str == "BreedingProgram")
        load_breedprog();
    if (select1_str == "Years")
        load_yearprog();
    if (select1_str == "Lines") {
        load_lines();
        load_lines2();
    }
    if (select1_str == "Markers")
        load_markers_select();
    if (select1_str == "Phenotypes")
        load_phenotypes();
}	

function update_lines(options) {
    load_lines();
}

function update_line_programs(options) {
    line_programs_str = "";
    experiments_str = "";
    $A(options).each(
            function(breeding_program) {
                if (line_program.selected) {
                    line_programs_str += (line_programs_str == "" ? "" : ",") + line_program.value;
                }
            });
    if (line_programs_str != "")
        load_lines();
}

function update_breeding_programs(options) {
    breeding_programs_str = "";
    experiments_str = "";
    $A(options).each(
            function(breeding_program) {
                if (breeding_program.selected) {
                    breeding_programs_str += (breeding_programs_str == "" ? ""
                            : ",") + breeding_program.value;
                }
            });
    if (breeding_programs_str != "" && years_str != "")
        load_experiments();
}
function update_phenotype_categories(options) {
    phenotype_categories_str = "";
    phenotype_item_str = "";
    experiments_str = "";
    lines_str = "";
    experiments_str = "";
    $A(options).each(
                function(phenotype_categories) {
            if (phenotype_categories.selected) {
                phenotype_categories_str += (phenotype_categories_str == "" ? ""
                		  : ",") + phenotype_categories.value;
            }
        });
    load_phenotypes2();
    document.getElementById('step3').innerHTML = "";
    document.getElementById('step4').innerHTML = "";
    document.getElementById('step5').innerHTML = "";
}

function update_phenotype_items(options) {
    phenotype_items_str = "";
    experiments_str = "";
    lines_str = "";
    $A(options)
            .each(
                    function(phenotype_items) {
                        if (phenotype_items.selected) {
                            phenotype_items_str += (phenotype_items_str == "" ? ""
                                    : ",") + phenotype_items.value;
                        }
                    });
    load_phenotypes3();
    document.getElementById('step4').innerHTML = "";
    document.getElementById('step5').innerHTML = "";
}
function update_phenotype_trial(options) {
	experiments_str = ""; 
	lines_str = "";
	$A(options).each(function(experiment) {
		if (experiment.selected) {
			experiments_str += (experiments_str == "" ? "" : ",") + experiment.value;
		}
	});
	load_phenotypes4();
	load_phenotypes5();
}

function update_line_trial(options) {
    experiments_str = ""; 
    $A(options).each(function(experiment) {
        if (experiment.selected) {
            experiments_str += (experiments_str == "" ? "" : ",") + experiment.value;
        }
    });
    
}

function update_phenotype_lines(options) {
    lines_str = "";
    $A(options).each(function(lines) {
        if (lines.selected) {
            lines_str += (lines_str == "" ? "" : ",") + lines.value;
        }
    });
    load_phenotypes5();
}
			
			function update_years(options) {
				years_str = "";
				experiments_str = "";
				$A(options).each(function(year) {
					if (year.selected) {
						years_str += (years_str == "" ? "" : ",") + year.value;
					}
				});
				if (breeding_programs_str != "" && years_str != "")
					load_experiments();
			}
			
			function update_experiments(options) {
				experiments_str = ""; // clears experiment setting to avoid trait perserverance
				$A(options).each(function(experiment) {
					if (experiment.selected) {
						experiments_str += (experiments_str == "" ? "" : ",") + experiment.value;
					}
				});
				load_traits();
				load_markers('', '', 100, 0);
			}

			function load_phenotypes() {
				$('step11').hide();
				url=php_self + "?function=step1phenotype&bp=" + breeding_programs_str + "&yrs=" + years_str;
				document.title='Loading Step1...';
					new Ajax.Updater($('step11'),url,
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

			function load_yearprog() {
			    $('step11').hide();
                url=php_self + "?function=step1yearprog&bp=" + breeding_programs_str + "&yrs=" + years_str;
                document.title='Loading Step1...';
                    new Ajax.Updater($('step11'),url,
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
			
			function load_phenotypes2() {
                                $('step2').hide();
                                url=php_self + "?function=step2phenotype&pc=" + phenotype_categories_str + "&yrs=" + years_str;
                                document.title='Loading Step1...';
                                        new Ajax.Updater($('step2'),url,
                            { 
                                        onComplete: function() {
                                        $('step2').show();
                                        document.title=title;
                                        }
                            }
                                );
                                }
			
			function load_phenotypes3() {
                $('step3').hide();
                url=php_self + "?function=step3phenotype&pi=" + phenotype_items_str + "&yrs=" + years_str;
                document.title='Loading Step1...';
                        new Ajax.Updater($('step3'),url,
            { 
                        onComplete: function() {
                        $('step3').show();
                        document.title=title;
                        }
            }
                );
                }
			
			function load_phenotypes4() {
                $('step4').hide();
                url=php_self + "?function=step4phenotype&pi=" + phenotype_items_str + "&e=" + experiments_str + "&lines=" + lines_str;
                document.title='Loading Step1...';
                        new Ajax.Updater($('step4'),url,
            { 
                        onComplete: function() {
                        $('step4').show();
                        document.title=title;
                        }
            }
                );
                }
			
			function load_phenotypes5() {
                $('step5').hide();
                url=php_self + "?function=step5phenotype&pi=" + phenotype_items_str + "&e=" + experiments_str + "&lines=" + lines_str;
                document.title='Loading Step1...';
                        new Ajax.Updater($('step5'),url,
            { 
                        onComplete: function() {
                        $('step5').show();
                        document.title=title;
                        }
            }
                );
                }
			

			function load_lines() {
                                $('step11').hide();
                                url=php_self + "?function=step1lines&bp=" + breeding_programs_str + '&yrs=' + years_str;
                                document.title='Loading Step1...';
                                        new Ajax.Updater($('step11'),url,
                            {
                                        onComplete: function() {
                                        $('step11').show();
                                        document.title=title;
                                        }
                            }
                                );
			}
			function load_lines2() {
                                $('step2').hide();
                                url=php_self + "?function=step2lines&bp=" + breeding_programs_str + '&yrs=' + years_str;
                                document.title='Loading Step1...';
                                         new Ajax.Updater($('step2'),url,
                            {
                                         onComplete: function() {
                                         $('step2').show();
                                         document.title=title;
                                         }
                             }
                                 );
				document.getElementById('step3').innerHTML = "";
                                }
	
			function use_session() {
				var mm = 99.9;
                var mmaf = 0.01
			    url=php_self + "?function=download_session&bp=" + breeding_programs_str+'&yrs='+ years_str+'&e='+experiments_str+'&mm='+mm+'&mmaf='+mmaf;
			    document.location = url;
			}
			function use_session_v2() {
                var mm = 99.9;
                var mmaf = 0.01
                url=php_self + "?function=download_session_v2&bp=" + breeding_programs_str+'&yrs='+ years_str+'&e='+experiments_str+'&mm='+mm+'&mmaf='+mmaf;
                document.location = url;
            }
			function use_session_v3() {
                var mm = 99.9;
                var mmaf = 0.01
                url=php_self + "?function=download_session_v3&bp=" + breeding_programs_str+'&yrs='+ years_str+'&e='+experiments_str+'&mm='+mm+'&mmaf='+mmaf;
                document.location = url;
            }

			function load_breedprog() {
                                $('step1').hide();
                                url=php_self + "?function=step1breedprog&bp=" + + breeding_programs_str + '&yrs=' + years_str;
                                document.title='Loading Step1...';
                                        new Ajax.Updater($('step11'),url,

                        {
                                onComplete: function() {
                                $('step1').show();
                                document.title=title;
                                }
                        }
                                );
                        document.getElementById('step2').innerHTML = "";
                        document.getElementById('step3').innerHTML = "";
                                }

			function load_experiments() {
                		$('step1').hide();
                		url=php_self + "?function=type1experiments&bp=" + breeding_programs_str + '&yrs=' + years_str;
                		document.title='Loading Trials...';
					new Ajax.Updater($('step2'),url,
				{ 
                        	onComplete: function() {
                            	$('step1').show();
                            document.title=title;
                    	    }
                    	}
					);
					experiments_loaded = true;
					if (traits_loaded == true)
					load_traits();
					if (markers_loaded == true)
					load_markers(100, 0);
				}
			
			function load_traits()
			{		
                traits_loading = true;		
                $('step3').hide();
                url=php_self + "?function=type1traits&exps=" + experiments_str;
                document.title='Loading Traits...';
				new Ajax.Updater(
				    $('step3'),url,
					{onComplete: function() {
                        $('step3').show();  
                        if (markers_loading == false) {
                            document.title = title;
                        }
                        traits_loading = false;
                        traits_loaded = true;
                    }}
				);
			}
            
			function load_markers_select() {
				url=php_self + "?function=type1markersselect&bp=" + breeding_programs_str + '&yrs=' + years_str + '&exps=' + experiments_str;
				document.title='Loading Markers...';
                                //changes are right here
                new Ajax.Updater($('step11'),url,
				{onComplete: function() {
                         $('step4').show();
                        if (traits_loading == false) {
                            document.title = title;
                        }
                        markers_loading = false;
                        markers_loaded = true;
                    }}
                                );
			}

			function load_markers( mm, mmaf) {
                markers_loading = true;
				$('step4').hide();
				url=php_self + "?function=type1markers&bp=" + breeding_programs_str + '&yrs=' + years_str + '&exps=' + experiments_str + '&mm=' + mm + '&mmaf=' + mmaf;
                document.title='Loading Markers...';
				//changes are right here
                new Ajax.Updater($('step4'),url,
					{onComplete: function() {
                         $('step4').show();
                        if (traits_loading == false) {
                            document.title = title;
                        }
                        markers_loading = false;
                        markers_loaded = true;
                    }}
				);
			}
			
			function selectedTraits() {
				var ret = '';
				$A($('traitsbx').options).each(function(trait){
				 	if (trait.selected)
					{
						ret += (ret == '' ? '' : ',') + trait.value;
					}			 
				});
				return ret;
			}
			
			function getdownload_qtlminer()
			{
				if (selectedTraits() == '') {
					alert('Please select at least one trait!');
					return false;
				}
			    var mm = $('mm').getValue();
                var mmaf = $('mmaf').getValue();
                url=php_self + "?function=type1build_qtlminer&bp=" + breeding_programs_str+'&yrs='+ years_str+'&t='+selectedTraits()+'&e='+experiments_str+'&mm='+mm+'&mmaf='+mmaf;
				
					document.location = url;
				
			}

			function getdownload_tassel()
			{
				if (selectedTraits() == '') {
					alert('Please select at least one trait!');
					return false;
				}
			    var mm = $('mm').getValue();
                var mmaf = $('mmaf').getValue();
			    var subset = $('subset').getValue();
			    url=php_self + "?function=type1build_tassel&bp=" + breeding_programs_str+'&yrs='+ years_str+'&t='+selectedTraits()+'&e='+experiments_str+'&mm='+mm+'&mmaf='+mmaf+'&subset='+subset;
			    document.location = url;
			}

			function getdownload_tassel_v3()
			{
				if (selectedTraits() == '') {
					alert('Please select at least one trait!');
					return false;
				}
			    var mm = $('mm').getValue();
                var mmaf = $('mmaf').getValue();
                var subset = $('subset').getValue();
                url=php_self + "?function=type1build_tassel_v3&bp=" + breeding_programs_str+'&yrs='+ years_str+'&t='+selectedTraits()+'&e='+experiments_str+'&mm='+mm+'&mmaf='+mmaf+'&subset='+subset;
                document.location = url;
			}

            function mrefresh() {
                var mm = $('mm').getValue();
                var mmaf = $('mmaf').getValue();
                load_markers( mm, mmaf);
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
                trialsSelected = trialsSelected + trialsMenu.options[i].value
                        + ",";
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
