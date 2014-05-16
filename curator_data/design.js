/*global, alert $*/

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

function select_trial() {
  select1_str = "select";
  program = "";
  trial = "";
  $(".step2").html("");
  $(".step3").html("");
  $.ajax({
      type: "GET",
      url: php_self,
      data: "function=selectProg",
      success: function(data, textStatus) {
          $(".step1").html(data);
      },
      error: function() {
          alert('Error in selecting design type');
      }
  });
}

function upload_trial() {
    location.href = "curator_data/input_annotations_upload_excel.php"; 
}

function update_trial() {
  trial = $("#trial").val();
  $.ajax({
      type: "GET",
      url: php_self,
      data: "function=displayTrial&prog=" + program + "&trial=" + trial,
      success: function(data, textStatus) {
          $(".step3").html(data);
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
  $(".step2").html("");
  $(".step3").html("");
  $.ajax({
      type: "GET",
      url: php_self,
      data: "function=designTrial",
      success: function(data, textStatus) {
          $(".step1").html(data);
      },
      error: function() {
          alert('Error in selecting design type');
      }
  });
  $(".step2").html("<input type=submit value='Create trial' onclick='javascript: create_trial()'><br>");
}

function update_type(options) {
  design_type = $("#type").val();
  $(".step3").html("");
  $.ajax({
      type: "GET",
      url: php_self,
      data: "function=designField&type=" + design_type,
      success: function(data, textStatus) {
          $(".step3").html(data);
      },
      error: function() {
          alert('Error in selecting design type');
      }
  });
  $(".step4").html("");
  $.ajax({
      type: "GET",
      url: php_self,
      data: "function=design_results&type=" + design_type,
      success: function(data, textStatus) {
          $(".step4").html(data);
      },
      error: function() {
          alert('Error in selecting design type');
      }
  });
}

function update_step1() {
  if (select1_str == "select") {
      program = $("#program").val();
      $(".step3").html("");
      $.ajax({
          type: "GET",
          url: php_self,
          data: "function=selectTrial&prog=" + program,
          success: function(data, textStatus) {
              $(".step2").html(data);
          },
          error: function() {
              alert('Error in selecting trial');
          }
      });
  } else {
      $(".step2").html("<input type=submit value='Create trial' onclick='javascript: create_trial()'><br>");
  }
}

function update_step3() {
  $(".step4").html("<input type=submit value='Create field layout' onclick='javascript: create_field()'>");
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

  $.ajax({
      type: "GET",
      url: php_self,
      data: "function=create_trial&prg=" + program + "&trial_name=" + trial_name + "&year=" + year + "&exp_name=" + exp_name + "&location=" + loc + "&lat=" + lat + "&long=" + longit + "&collab=" + collab + "&desc=" + desc + "&pdate=" + pdate, 
      success: function(data, textStatus) {
          $(".step2").html(data);
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
  var msg = num_replicates + " " + num_blocks;
  $("#test").html(msg);
  var url = php_self;
  $.ajax({
      type: "GET",
      url: php_self,
      data: "function=create_field&trial_name=" + trial_name + "&trt=" + trt + "&type=" + design_type + "&num_rep=" + num_rep + "&size_blk=" + size_blk + "&unq=" + unq_dir,
      success: function(data, textStatus) {
          $(".step4").html(data);
      },
      error: function() {
          alert('Error in creating field layout');
      }
  }); 
}

function saveChecks() {
  $.ajax({
      type: "GET",
      url: php_self,
      data: "function=saveChecks",
      success: function(data, textStatus) {
          $(".step4").html(data);
      },
      error: function() {
          alert('Error in saving check lines');
      }
  });
}
