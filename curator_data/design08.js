/*global, alert, jQuery, $, $A, Element */

var select1_str = "";	//select/add
var num_replicates = "";
var num_blocks = "";
var max_block_size = "";
var program = "";
var trial = "";
var trial_name = "";
var design_type = "";
var loc = "";
var lat = "";
var longit = "";
var collab = "";
var unq_dir = "";
var php_self = document.location.href;

function select_trial() {
  select1_str = "select";
  program = "";
  trial = "";
  if (jQuery(".step1a")) {
      jQuery(".step1a").html("");
  }
  if (jQuery(".step1b")) {
      jQuery(".step1b").html("");
  }
  jQuery(".step2").html("");
  jQuery(".step3").html("");
  jQuery(".step4").html("");
  jQuery(".step5").html("");
  jQuery.ajax({
      type: "GET",
      url: php_self,
      data: "function=selectProg",
      success: function(data, textStatus) {
          jQuery(".step1").html(data);
      },
      error: function() {
          alert('Error in selecting design type');
      }
  });
}

function updateSecChk(nChkLines) {
  var nSecChk = document.getElementById("nSecChk").value;
  var nPriChk = nChkLines - nSecChk;
  document.getElementById("nPriChk").innerHTML = nPriChk;
}

function search_line() {
  var lines = document.getElementById("LineSearchInput").value;
  jQuery.ajax({
    type: "POST",
    url: php_self,
    data: "function=searchLine&LineSearchInput=" + lines,
    success: function(data, textStatus) {
        jQuery("#dialog_r").html(data);
    },
    error: function() {
        alert('Error in selecting design type');
      }
  });
}

function update_type(options) {
  design_type = jQuery("#design").val();
  jQuery(".step3").html("");
  jQuery.ajax({
      type: "GET",
      url: php_self,
      data: "function=designField&type=" + design_type + "&trial=" + trial,
      success: function(data, textStatus) {
          jQuery(".step3").html(data);
      },
      error: function() {
          alert('Error in selecting design type');
      }
  });
  if (design_type == "alpha") {
    jQuery("#design_desc").html("The number of treatments must be multiple of k (size block)");
  } else if (design_type == "bib") {
    jQuery("#design_desc").html("Randomized Balanced Incomplete Block Design");
  } else if (design_type == "lattice") {
    jQuery("#design_desc").html("SIMPLE and TRIPLE lattice designs. It randomizes treatments in k x k lattice.");
  } else if (design_type == "madii") {
    jQuery("#design_desc").html("Modified augmented design.");
  } else {
    jQuery("#design_desc").html("");
  }
  jQuery(".step4").html("");
  if (design_type !== "") {
    jQuery.ajax({
      type: "GET",
      url: php_self,
      data: "function=design_results&type=" + design_type + "&trial=" + trial,
      success: function(data, textStatus) {
          jQuery(".step4").html(data);
      },
      error: function() {
          alert('Error in selecting design type');
      }
    });
  }
}

function save_line() {
  var lines_str = "";
  jQuery('#selLines').each(function(){
     lines_str += (lines_str === ''?'' : ',') + jQuery(this).val(); 
  });
  jQuery.ajax({
    type: "POST",
    url: php_self,
    data: "function=saveLine&LineSearchInput=" + lines_str,
    success: function(data, textStatus) {
        jQuery("#dialog-form-checks").dialog( "close" );
        update_type(); 
    },
    error: function() {
        alert('Error in selecting design type');
      }
  });
}

function upload_trial() {
    jQuery( "#dialog-form" ).dialog( "open" );
}

function upload_field() {
    jQuery( "#dialog-form-field" ).dialog( "open" );
}

function select_check() {
    jQuery( "#dialog-form-checks" ).dialog( "open" );
}

function update_trial() {
  trial = jQuery("#trial").val();
  jQuery.ajax({
      type: "GET",
      url: php_self,
      data: "function=displayTrial&prog=" + program + "&trial=" + trial,
      success: function(data, textStatus) {
          jQuery(".step1b").html(data);
          //jQuery(data).appendTo(".step1");
      },
      error: function() {
          alert('Error in selecting design type');
      }
  });
}

function add_trial() {
  select1_str = "add";
  program = "";
  trial = "";
  if (jQuery(".step1a")) {
      jQuery(".step1a").html("");
  }
  if (jQuery(".step1b")) {
      jQuery(".step1b").html("");
  }
  jQuery(".step2").html("");
  jQuery(".step3").html("");
  jQuery.ajax({
      type: "GET",
      url: php_self,
      data: "function=designTrial",
      success: function(data, textStatus) {
          jQuery(".step1").html(data);
      },
      error: function() {
          alert('Error in selecting design type');
      }
  });
  jQuery(".step2").html("<input type=submit value='Create trial' onclick='javascript: create_trial()'><br>");
}

function update_step1() {
  if (select1_str == "select") {
      program = jQuery("#program").val();
      jQuery(".step3").html("");
      jQuery.ajax({
          type: "GET",
          url: php_self,
          data: "function=selectTrial&prog=" + program,
          success: function(data, textStatus) {
              jQuery(".step1a").html(data);
              //jQuery(data).appendTo(".step1");
          },
          error: function() {
              alert('Error in selecting trial');
          }
      });
  } else {
      jQuery(".step2").html("<input type=submit value='Create trial' onclick='javascript: create_trial()'><br>");
  }
}

function update_step3() {
  jQuery(".step4").html("<input type=submit value='Create field layout' onclick='javascript: create_field()'>");
}

function create_trial() {
  var program = "";
  var exp_name = "";
  var year = "2014";
  if (document.getElementById("program")) {
      program = document.getElementById("program").value;
  }
  if (document.getElementById("trial_name")) {
      trial_name = document.getElementById("trial_name").value;
  }
  if (document.getElementById("year")) {
      year = document.getElementById("year").value;
  }
  if (document.getElementById("exp_name")) {
      exp_name = document.getElementById("exp_name").value;
  }
  if (document.getElementById("location")) {
      loc = document.getElementById("location").value;
  }
  if (document.getElementById("lat")) {
      lat = document.getElementById("lat").value;
  }
  if (document.getElementById("long")) {
      longit = document.getElementById("long").value;
  }
  if (document.getElementById("collab")) {
      collab = document.getElementById("collab").value;
  }
  var desc = document.getElementById("desc").value;
  var pdate = document.getElementById("plant_date").value;
  var hdate = document.getElementById("harvest_date").value;
  var bwdate = document.getElementById("bwdate").value;
  var radio = document.getElementById("greenhouse");
  var greenhouse = jQuery('input[name="greenhouse"]:checked').val();
  var seed = document.getElementById("seed").value;
  var numEntry, numRepl;
  if (document.getElementById("design")) {
      design_type = document.getElementById("design").value;
  }
  if (document.getElementById("trt")) {
      numEntry = document.getElementByID("trt").value;
  }
  if (document.getElementById("cnt")) {
      numRepl = document.getElementByID("cnt").value;
  }
  var irrigation = jQuery('input[name="irrigation"]:checked').val();

  jQuery.ajax({
      type: "GET",
      url: php_self,
      data: "function=create_trial&prg=" + program + "&trial_name=" + trial_name + "&year=" + year + "&exp_name=" + exp_name + "&location=" + loc + "&lat=" + lat + "&long=" + longit + "&collab=" + collab + "&desc=" + desc + "&pdate=" + pdate + "&hdate=" + hdate + "&bwdate=" + bwdate + "&greenhouse=" + greenhouse + "&seed=" + seed + "&design=" + design_type + "&irrigation=" + irrigation + "&numEnty=" + numEntry + "&numRepl=" + numRepl, 
      success: function(data, textStatus) {
          jQuery(".step2").html(data);
      },
      error: function() {
          alert('Error in creating trial');
      }
  });

}

function create_field() {
  var trial = "";
  var trial_name = "";
  var trt = "";
  var size_blk = "";
  var num_rep = "";
  var num_blk = "";
  var num_row = "";
  var num_col = "";
  var nRowPerBlk = "";
  var nColPerBlk = "";
  var fillWith = "";
  var nSecChk = "";
  var nChksPerBlk = "";
  unq_dir = "download_" + Date.now();
  if (document.getElementById("trial_name")) {
      trial_name = document.getElementById("trial_name").value;
  }
  if (document.getElementById("trial")) {
      trial = document.getElementById("trial").value;
  } 
  if ((trial_name === "") & (trial === "")) {
      alert('Error: must specify Trial Name');
      return;
  }
  if (document.getElementById("trt")) {
    trt = document.getElementById("trt").value;
  }
  if (document.getElementById("size_blk")) {
    size_blk = document.getElementById("size_blk").value;
  }
  if (document.getElementById("num_rep")) {
    num_rep = document.getElementById('num_rep').value;
  }
  if (document.getElementById("num_blk")) {
    num_blk = document.getElementById('num_blk').value;
  }
  if (document.getElementById("rows")) {
    num_row = document.getElementById("rows").value;
  }
  if (document.getElementById("columns")) {
    num_col = document.getElementById("columns").value;
  }
  if (document.getElementById("nRowPerBlk")) {
    nRowPerBlk = document.getElementById("nRowPerBlk").value;
  }
  if (document.getElementById("nColPerBlk")) {
    nColPerBlk = document.getElementById("nColPerBlk").value;
  }
  if (document.getElementById("fillWith")) {
    fillWith = document.getElementById("fillWith").value;
  }
  if (document.getElementById("nSecChk")) {
    nSecChk = document.getElementById("nSecChk").value;
  }
  if (document.getElementById("nChksPerBlk")) {
    nChksPerBlk = document.getElementById("nChksPerBlk").value;
  }
  var msg = num_replicates + " " + num_blocks;
  jQuery("#test").html(msg);
  var url = php_self;
  Element.show('spinner');
  jQuery.ajax({
      type: "GET",
      url: php_self,
      data: "function=create_field&trial=" + trial +"&trial_name=" + trial_name + "&trt=" + trt + "&type=" + design_type + "&num_rep=" + num_rep + "&size_blk=" + size_blk + "&unq=" + unq_dir + "&num_row=" + num_row + "&num_col=" + num_col + "&trial=" + trial + "&nRowsPerBlk=" + nRowPerBlk + "&nColsPerBlk=" + nColPerBlk + "&fillWith=" + fillWith + "&nChksPerBlk=" + nChksPerBlk,
      success: function(data, textStatus) {
          Element.hide('spinner');
          jQuery(".step4").html(data);
      },
      error: function() {
          Element.hide('spinner');
          alert('Error in creating field layout');
      }
  }); 
}

function saveChecks() {
  jQuery.ajax({
      type: "GET",
      url: php_self,
      data: "function=saveChecks",
      success: function(data, textStatus) {
          jQuery(".step4").html(data);
      },
      error: function() {
          alert('Error in saving check lines');
      }
  });
}

jQuery(function() {

  jQuery( "#dialog-form" ).dialog({
      autoOpen: false,
      height: 250,
      width: 450,
      modal: false,
      buttons: {
        Cancel: function() {
          jQuery( this ).dialog( "close" );
        }
      }
  });

  jQuery( "#dialog-form-field" ).dialog({
      autoOpen: false,
      height: 250,
      width: 450,
      modal: false,
      buttons: {
        Cancel: function() {
          jQuery( this ).dialog( "close" );
        }
      }
  });

  jQuery( "#dialog-form-checks" ).dialog({
      autoOpen: false,
      height: 450,
      width: 450,
      modal: false,
      buttons: {
        Cancel: function() {
          jQuery( this ).dialog( "close" );
        }
      }
  });

});

