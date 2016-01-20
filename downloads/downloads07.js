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
var mm = 10;
var mmaf = 5;
var mml = 10;

var markerids = null;
var selmarkerids = [];

var markers_loading = false;
var traits_loading = false;

var title = document.title;

function use_normal() {
    breeding_programs_str = "";
    years_str = "";
    lines_str = "";
    subset = "";
    var url = php_self + "?function=type1&bp=" + breeding_programs_str + '&yrs=' + years_str;
    var tmp = new Ajax.Updater($('step1'), url, {asynchronous:false}, {
        onComplete : function() {
            $('step1').show();
            document.title = title;
        }
    });
    url = php_self + "?function=refreshtitle&lines=" + lines_str;
    tmp = new Ajax.Updater($('title'), url, {
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
    document.getElementById('step2').innerHTML = "";
    document.getElementById('step3').innerHTML = "";
    document.getElementById('step4').innerHTML = "";
    document.getElementById('step4b').innerHTML = "";
    document.getElementById('step5').innerHTML = "";
}

function load_title(command) {
    var url = php_self + "?function=refreshtitle&lines=" + lines_str + "&exps=" + experiments_str + '&pi=' + phenotype_items_str + '&subset=' + subset + '&cmd=' + command + '&menu=' + select1_str;
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
    if (command == "save") {
      document.getElementById('step5').innerHTML = "Selection saved";
    }
}

function haplotype_step2(command) {
    var i = document.myForm.elements.length;
    var e = document.myForm.elements;
    var k = 0;
    //var form = new Element('form', {method: 'post', action: php_self});
    var param = 'function=step2';
    //form.insert(new Element('input', {name: 'function', value: 'step2'}));
    for (k=0; k<i; k++) {
      if (document.myForm.elements[k].checked === true) { 
      param += '&' + document.myForm.elements[k].name + '=' + document.myForm.elements[k].value;
      //*form.insert(new Element('input', {name: document.myForm.elements[k].name, value: document.myForm.elements[k].value})); */
      //$(document.body).insert(form);
    } 
    }
    //*form.submit(); */
    var url = php_self;
    var tmp = new Ajax.Updater($('step1'), url, {method: 'post', postBody: param, asynchronous:false}, { 
        onComplete : function() {
            $('step1').show();
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
    param = 'function=step1';
    url = php_self;
    tmp = new Ajax.Updater($('step2'), url, {method: 'post', postBody: param}, {
        onComplete : function() {
            $('step1').show();
            document.title = title;
       }
    });
}

function haplotype_step2_combine() {
    var param = 'function=selLines';
    param += '&selLines=' + document.lines.elements.selLines.value;
    if (document.lines.elements[0].checked) {
      param += '&selectWithin=' + document.lines.elements[0].value;
    }
    if (document.lines.elements[1].checked) {
      param += '&selectWithin=' + document.lines.elements[1].value;
    }
    if (document.lines.elements[2].checked) {
      param += '&selectWithin=' + document.lines.elements[2].value;
    }
    if (document.lines.elements[3].checked) {
      param += '&selectWithin=' + document.lines.elements[3].value;
    }

    var url = php_self;
    var tmp = new Ajax.Updater($('step1'), url, {method: 'post', postBody: param, asynchronous:false}, {
        onComplete : function() {
            $('step1').show();
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
    param = 'function=step1';
    url = php_self;
    tmp = new Ajax.Updater($('step2'), url, {method: 'post', postBody: param}, {
        onComplete : function() {
            $('step1').show();
            document.title = title;
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

function load_locations() {
    $('step11').hide();
    var url = php_self + "?function=step1locations&bp=" + breeding_programs_str + '&yrs=' + years_str;
    document.title='Loading Step1...';
        var tmp = new Ajax.Updater($('step11'),url,
                    {
        onComplete: function() {
        $('step11').show();
        document.title=title;
        }
                }
    );
            }
function load_locations2() {
    $('step2').hide();
    var url = php_self + "?function=step2locations&loc=" + locations_str + '&yrs=' + years_str;
    document.title='Loading Step1...';
        var tmp = new Ajax.Updater($('step2'),url,
                {
        onComplete: function() {
        $('step2').show();
        document.title=title;
        }
                }
    );
        }
function load_locations3() {
    $('step3').hide();
    var url = php_self + "?function=step3locations&loc=" + locations_str + '&yrs=' + years_str;
    document.title='Loading Step1...';
        var tmp = new Ajax.Updater($('step3'),url,
                {
        onComplete: function() {
        $('step3').show();
        document.title=title;
        }
                }
    );
        }

function load_locations5() {
    $('step4b').hide();
    var url = php_self + "?function=step5locations&exps=" + experiments_str + '&pi=' + phenotype_items_str + '&subset=' + subset;
    document.title = 'Loading Step1...';
    var tmp = new Ajax.Updater($('step4b'), url, {
        onComplete : function() {
            $('step4b').show();
            document.title = title;
        }
    });
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
    $('step2').hide();
    var url = php_self + "?function=step2phenotype&pc=" + phenotype_categories_str + "&yrs=" + years_str;
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
    var url = php_self + "?function=step3phenotype&pi=" + phenotype_items_str + "&trait_cmb=" + trait_cmb;
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

function load_lines() {
    $('step11').hide();
    var url = php_self + "?function=step1lines&bp=" + breeding_programs_str + '&yrs=' + years_str;
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
    var url = php_self + "?function=step2lines&bp=" + breeding_programs_str + "&trait_cmb=" + trait_cmb;
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
            document.title = title;
            traits_loading = false;
            traits_loaded = true;
        }}
	);
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

function load_markers( mm, mmaf) {
    markers_loading = true;
    $('step5').hide();
    var url=php_self + "?function=type1markers&bp=" + breeding_programs_str + '&lines=' + lines_str + '&exps=' + experiments_str + '&t=' + selectedTraits() + '&mm=' + mm + '&mmaf=' + mmaf + '&subset=' + subset;
    document.title='Loading Markers...';
    var tmp = new Ajax.Updater($('step5'),url,
        {onCreate: function() { Element.show('spinner'); },
            onComplete: function() {
             $('step5').show();
            document.title = title;
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
            document.title = title;
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
    load_yearprog();
    document.getElementById('step3').innerHTML = "";
    document.getElementById('step4').innerHTML = "";
    document.getElementById('step5').innerHTML = "";
}
function update_locations(options) {
    locations_str = "";
    experiments_str = "";
    years_str = "";
    $A(options).each(
            function(locations){
                if (locations.selected) {
                    locations_str += (locations_str === "" ? "" : ",") + "'" + locations.value + "'";
                }
            }
    );
    load_locations2();
    document.getElementById('step3').innerHTML = "";
    document.getElementById('step4').innerHTML = "";
    document.getElementById('step4b').innerHTML = "";
    document.getElementById('step5').innerHTML = "";
}
function update_phenotype_categories(options) {
    phenotype_categories_str = "";
    phenotype_items_str = "";
    experiments_str = "";
    lines_str = "";
    experiments_str = "";
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
    if (select1_str == "Phenotypes") {
        load_phenotypes3();
        document.getElementById('step4').innerHTML = "";
    } else if (select1_str == "Locations") {
        load_locations5();
        //load_title();
        load_markers_loc('', '', 100, 0);
    } else {
        load_programs5();
        //load_title();
        load_markers('', '', 100, 0);
        document.getElementById('step5').innerHTML = "";
    }
    document.getElementById('step5').innerHTML = "";
}

function update_phenotype_trial(options) {
	experiments_str = ""; 
	lines_str = "";
	$A(options).each(function(experiment) {
		if (experiment.selected) {
			experiments_str += (experiments_str === "" ? "" : ",") + experiment.value;
		}
	});
	load_phenotypes4();
	load_title();
	load_phenotypes5();
}

function update_phenotype_trialb(options) {
/*used to update the trials with any/all traits */
    trait_cmb = options;
    if (select1_str == "Lines") {
        load_lines2();
    } else {
        load_phenotypes3();
    }   
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
    document.getElementById('step4').innerHTML = "";
    document.getElementById('step5').innerHTML = "";
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
function update_phenotype_linesb(options) {
/*used when updating radio button for line selection (session, all, combine) */
    subset = options;
    if (select1_str == "Phenotypes") {
        load_phenotypes4();
        load_phenotypes5();
    } else if (select1_str == "Locations") {
        load_locations5();
        load_markers_loc('', '', 100, 0);
    } else if (select1_str == "BreedingProgram") {
        load_programs5();
        load_markers('', '', 100, 0);
    }
}
			
		function update_years(options) {
			years_str = "";
			experiments_str = "";
			$A(options).each(function(year) {
				if (year.selected) {
						years_str += (years_str === "" ? "" : ",") + year.value;
					}
				});
			if (select1_str == "BreedingProgram") {
                    load_experiments();
            } else if (select1_str == "Locations") {
                    load_locations3();
            }
			document.getElementById('step3').innerHTML = "";
            document.getElementById('step4').innerHTML = "";
            document.getElementById('step4b').innerHTML = "";
            document.getElementById('step5').innerHTML = "";
		
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
			    var url = php_self + "?function=step1phenotype&bp=" + breeding_programs_str + "&yrs=" + years_str;
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
			}

			function update_select1(options) {
			    select1_str = "";
			    breeding_programs_str = "";
			    phenotype_items_str = "";
			    experiments_str = "";
			    locations_str = "";
			    years_str = "";
			    $A(options).each(function(select1) {
			        if (select1.selected) {
			            select1_str = select1.value;
			        }
			    });
			    document.getElementById('step2').innerHTML = "";
                document.getElementById('step3').innerHTML = "";
                document.getElementById('step4').innerHTML = "";
                document.getElementById('step4b').innerHTML = "";
                document.getElementById('step5').innerHTML = "";
			    if (select1_str == "BreedingProgram") {
			      load_breedprog();
			    } else if (select1_str == "Years") {
			      load_yearprog();
			    } else if (select1_str == "Lines") {
			      load_lines();
			      load_lines2();
			    } else if (select1_str == "Locations") {
                              load_locations();
			    } else if (select1_str == "Phenotypes") {
			      load_phenotypes();
			    }
			    load_title();
			}  

			function update_select2(options) {
			    select2_str = "";
			    $A(options).each(function(select2) {
                                if (select2.selected) {
                                  select2_str = select2.value;
                                }
                            });
	        document.getElementById('step3').innerHTML = "";
                document.getElementById('step4').innerHTML = "";
                document.getElementById('step4b').innerHTML = "";
                document.getElementById('step5').innerHTML = "";
			} 

           function create_file(version) {
                Element.show('spinner');
                var typeG=document.getElementById('typeG');
                var typeGE=document.getElementById('typeGE');
                document.getElementById('title2').innerHTML = "Creating download file";
                document.getElementById('step6').innerHTML = "";
                var url=php_self + "?function=download_session_" + version + "&typeG=" + typeG.checked + "&typeGE=" + typeGE.checked + '&mm=' + mm + '&mmaf=' + mmaf;
                document.title='Creating Download file...';
                var tmp = new Ajax.Updater($('step6'), url, {
                    onSuccess: function() {
                        $('step6').show();
                        document.title = title;
                        Element.hide('spinner');
                        document.getElementById('title2').innerHTML = "Select the Download Zip file button to retrieve the results.";
                    },
                    onFailure: function() {
                        Element.hide('spinner');
                        alert("Internal Server Error");
                    }}
                );
            }
	
	function use_session(version) {
	        var mm = $('mm').getValue();
                var mmaf = $('mmaf').getValue();
                var mml = $('mml').getValue();
                var typeGE=document.getElementById('typeGE');
                markers_loading = true;
                Element.show('spinner');
                document.getElementById('title2').innerHTML = "Selecting markers and calculating allele frequency for selected lines";
                document.getElementById('step6').innerHTML = "";
                var url=php_self + "?function=step5lines&pi=" + phenotype_items_str + '&yrs=' + years_str + '&exps=' + experiments_str + '&mm=' + mm + '&mmaf=' + mmaf + '&mml=' + mml + '&use_line=yes&typeGE=' + typeGE.checked;
                document.title='Loading Markers...';
                var tmp = new Ajax.Updater($('step5'), url, {
                    onSuccess: function() {
                         $('step5').show();
                        document.title = title;
                        markers_loading = false;
                        markers_loaded = true;
                        create_file(version);
                    },
                    onException: function(rec, exception) {
                        alert("Error filtering lines and markers: " +  exception);
                    },
                    onFailure: function() {
                        alert('Error filtering lines and markers');
                    }}
                );
            }
     
			function load_markers_pheno( mm, mmaf) {
                markers_loading = true;
                $('step5').hide();
                var url=php_self + "?function=step5phenotype&pi=" + phenotype_items_str + '&yrs=' + years_str + '&e=' + experiments_str + '&mm=' + mm + '&mmaf=' + mmaf;
                document.title='Loading Markers...';
                var tmp = new Ajax.Updater($('step5'),url,
                    {onCreate: function() { Element.show('spinner'); },
                        onComplete: function() {
                        $('step5').show();
                        load_title();
                    }}
                );
            }

        function load_pheno_lines() {
            var url=php_self + "?function=step6lines&pi=" + phenotype_items_str + '&yrs=' + years_str + '&exps=' + experiments_str;
                document.title='Loading Traits...';
                var tmp = new Ajax.Updater($('step5'), url, {
                    insertion: 'bottom',
                    onSuccess: function() {
                         $('step5').show();
                        document.title = title;
                        Element.hide('spinner');
                        load_title();
                    }}
                );
            }

	function load_markers_lines( mm, mmaf, use_line) {
	    select1_str = "Lines";
                var typeGE=document.getElementById('typeGE');
                markers_loading = true;
                Element.show('spinner');
                document.getElementById('step5').innerHTML = "Selecting markers and calculating allele frequency for selected lines";
                var url=php_self + "?function=step5lines&pi=" + phenotype_items_str + '&yrs=' + years_str + '&exps=' + experiments_str + '&mm=' + mm + '&mmaf=' + mmaf + '&use_line=' + use_line + "&typeGE=" + typeGE.checked;
                document.title='Loading Markers...';
                //changes are right here
                var tmp = new Ajax.Updater($('step5'), url, {
                    insertion: 'bottom',
                    onSuccess: function() {
                         $('step5').show();
                        Element.hide('spinner');
                        load_title();
                    },
                    onException: function(rec, exception) {
                        alert("Error filtering lines and markers: " +  exception);
                    },
                    onFailure: function() {
                        alert('Error filtering lines and markers');
                    }}
                );
            }

            function select_download(click_id) {
                var typeP=document.getElementById('typeP');
                var typeG=document.getElementById('typeG');
                var typeGE=document.getElementById('typeGE');
                if (click_id == "typeG") {
                   typeGE.checked = false;
                } else if (click_id == "typeGE") {
                   typeG.checked = false;
                }
                document.getElementById('step6').innerHTML = "";
                var url=php_self + "?function=verifyLines&typeP=" + typeP.checked + "&typeG=" + typeG.checked + "&typeGE=" + typeGE.checked;
                var tmp = new Ajax.Updater($('step5'), url, {
                    onSuccess: function() {
                    if (typeG.checked) {
                        load_markers_lines( mm, mmaf);
                    } else if (typeGE.checked) {
                        load_markers_lines( mm, mmaf);
                    } else {
                        load_pheno_lines();
                    }
                }});
                document.title = title;
            }

            function mrefresh() {
                mm = $('mm').getValue();
                mmaf = $('mmaf').getValue();
                var use_line = "yes";
                if (select1_str == "Phenotypes") {
                    load_markers_pheno( mm, mmaf);
                } else if (select1_str == "Locations") {
                    load_markers_loc( mm, mmaf);
                } else if (select1_str == "Lines") {
                    load_markers_lines( mm, mmaf, use_line);
                } else {
                    load_markers( mm, mmaf);
                }
            }

function filterDesc(min_maf, max_missing, max_miss_line) {
  alert("1. Marker allele frequency is calculated for the selected lines.\n2. Markers are removed that have MAF less than " +  min_maf + "% or are missing in more than " + max_missing + "% of the lines.\n3. Lines are removed if they are missing more than " + max_miss_line + "% of the marker data.\nAfter changing the default settings for the filter, select Download to use the new paramaters");
}

function linesRemoved(lineRemovedName) {
  alert("Lines Removed\n" + lineRemovedName);
}
