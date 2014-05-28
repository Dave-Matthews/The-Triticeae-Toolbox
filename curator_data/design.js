/*global, alert, jQuery, $ */

var select1_str = "";	//select/add
var num_replicates = "";
var num_blocks = "";
var max_block_size = "";
var program = "";
var trial = "";
var design_type = "";
var loc = "";
var lat = "";
var longit = "";
var collab = "";
var unq_dir = "";
var php_self = document.location.href;
$.noConflict(); //when prototype.js is removed then this is not necessary

function select_trial() {
  select1_str = "select";
  program = "";
  trial = "";
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

function search_line() {
  var lines = document.getElementById("LineSearchInput").value;
  jQuery.ajax({
    type: "POST",
    url: php_self,
    data: "function=searchLine&LineSearchInput=" + lines,
    success: function(data, textStatus) {
        jQuery(".dialog_r").html(data);
    },
    error: function() {
        alert('Error in selecting design type');
      }
  });
}

function save_line(options) {
  var lines = document.getElementById("selLines[]").value;
  var lines_str = "";
  $(options).each(function(lines)
  {
     lines_str += (lines_str === ''?'' : ',') + lines.value; 
  });
  jQuery.ajax({
    type: "POST",
    url: php_self,
    data: "function=saveLine&LineSearchInput=" + lines,
    success: function(data, textStatus) {
        jQuery(".dialog_r").html(data);
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
          jQuery(".step3").html(data);
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

function update_type(options) {
  design_type = jQuery("#design").val();
  jQuery(".step3").html("");
  jQuery.ajax({
      type: "GET",
      url: php_self,
      data: "function=designField&type=" + design_type,
      success: function(data, textStatus) {
          jQuery(".step3").html(data);
      },
      error: function() {
          alert('Error in selecting design type');
      }
  });
  jQuery(".step4").html("");
  jQuery.ajax({
      type: "GET",
      url: php_self,
      data: "function=design_results&type=" + design_type,
      success: function(data, textStatus) {
          jQuery(".step4").html(data);
      },
      error: function() {
          alert('Error in selecting design type');
      }
  });
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
              jQuery(".step2").html(data);
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
  var trial_name = "";
  var exp_name = "";
  var year = "2014";
  if (document.getElementById("program")) {
      program = document.getElementById("program").value;
  }
  if (document.getElementById("name")) {
      trial_name = document.getElementById("name").value;
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
  var greenhouse = document.getElementById("greenhouse").value;
  var seed = document.getElementById("seed").value;
  design_type = document.getElementById("design").value;

  jQuery.ajax({
      type: "GET",
      url: php_self,
      data: "function=create_trial&prg=" + program + "&trial_name=" + trial_name + "&year=" + year + "&exp_name=" + exp_name + "&location=" + loc + "&lat=" + lat + "&long=" + longit + "&collab=" + collab + "&desc=" + desc + "&pdate=" + pdate + "&hdate=" + hdate + "&bwdate=" + bwdate, 
      success: function(data, textStatus) {
          jQuery(".step2").html(data);
      },
      error: function() {
          alert('Error in creating trial');
      }
  });

}

function create_field() {
  var trial_name = "";
  var trt = "";
  var size_blk = "";
  var num_rep = "";
  var num_blk = "";
  var num_row = "";
  var num_col = "";
  unq_dir = "download_" + Date.now();
  if (document.getElementById("name")) {
      trial_name = document.getElementById("name").value;
  } 
  if (trial_name === "") {
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
  var msg = num_replicates + " " + num_blocks;
  jQuery("#test").html(msg);
  var url = php_self;
  jQuery.ajax({
      type: "GET",
      url: php_self,
      data: "function=create_field&trial_name=" + trial_name + "&trt=" + trt + "&type=" + design_type + "&num_rep=" + num_rep + "&size_blk=" + size_blk + "&unq=" + unq_dir + "&num_row=" + num_row + "&num_col=" + num_col,
      success: function(data, textStatus) {
          jQuery(".step4").html(data);
      },
      error: function() {
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

