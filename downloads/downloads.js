var php_self = "downloads/downloads.php";
var breeding_programs_str = "";
var years_str = "";
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
    url = php_self + "?function=type1&bp=" + breeding_programs_str + '&yrs=' + years_str;
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
    if (select1_str == "Lines")
        load_lines();
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

function update_phenotype_programs(options) {
    phenotype_programs_str = "";
    experiments_str = "";
    $A(options)
            .each(
                    function(phenotype_program) {
                        if (phenotype_program.selected) {
                            phenotype_programs_str += (phenotype_programs_str == "" ? ""
                                    : ",") + phenotype_program.value;
                        }
                    });
    load_phenotypes2();
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
				$('step1').hide();
				url=php_self + "?function=step1phenotype&bp=" + breeding_programs_str + "&yrs=" + years_str;
				document.title='Loading Step1...';
					new Ajax.Updater($('step11'),url,
	                    { 
	                        	onComplete: function() {
	                            	$('step1').show();
	                            	document.title=title;
	                    		}
	                    }
	           		);
								document.getElementById('experiments_loader').innerHTML = "";
                                document.getElementById('traits_loader').innerHTML = "";
                                document.getElementById('experiments_loader').innerHTML = "";
				}

			function load_phenotypes2() {
                                $('step2').hide();
                                url=php_self + "?function=step1phenotype&bp=" + breeding_programs_str + "&yrs=" + years_str;
                                document.title='Loading Step1...';
                                        new Ajax.Updater($('step21'),url,
                            { 
                                        onComplete: function() {
                                        $('step2').show();
                                        document.title=title;
                                        }
                            }
                                );
                                }

			function load_lines() {
                                $('step1').hide();
                                url=php_self + "?function=enterlines&bp=" + breeding_programs_str + '&yrs=' + years_str;
                                document.title='Loading Step1...';
                                        new Ajax.Updater($('step11'),url,
                            {
                                        onComplete: function() {
                                        $('step1').show();
                                        document.title=title;
                                        }
                            }
                                );
				document.getElementById('experiments_loader').innerHTML = "";
				document.getElementById('traits_loader').innerHTML = "";
				document.getElementById('experiments_loader').innerHTML = "";
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
                                }

			function load_experiments() {
                		$('step1').hide();
                		url=php_self + "?function=type1experiments&bp=" + breeding_programs_str + '&yrs=' + years_str;
                		document.title='Loading Trials...';
					new Ajax.Updater($('experiments_loader'),url,
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
                $('traits_loader').hide();
                url=php_self + "?function=type1traits&exps=" + experiments_str;
                document.title='Loading Traits...';
				new Ajax.Updater(
				    $('traits_loader'),url,
					{onComplete: function() {
                        $('traits_loader').show();  
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
                         $('markers_loader').show();
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
				$('markers_loader').hide();
				url=php_self + "?function=type1markers&bp=" + breeding_programs_str + '&yrs=' + years_str + '&exps=' + experiments_str + '&mm=' + mm + '&mmaf=' + mmaf;
                document.title='Loading Markers...';
				//changes are right here
                new Ajax.Updater($('markers_loader'),url,
					{onComplete: function() {
                         $('markers_loader').show();
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
