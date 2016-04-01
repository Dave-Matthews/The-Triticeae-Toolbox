/*global $, alert , window, jQuery, Element*/

jQuery.noConflict(); //when prototype.js is removed then this is not necessary

        function load_tab_delimiter(experiment_uid, max_missing, min_maf) {
            //alert (experiment_uid);
            var url= 'display_genotype.php?function=typeTabDelimiter'+ '&expuid=' + experiment_uid+ '&mm='+max_missing+'&mmaf='+min_maf;
            // Opens the url in the same window
            window.open(url, "_self");
        }
        function load_tab_delimiter_GBS(experiment_uid, max_missing, min_maf) {
            document.getElementById('status').innerHTML = "Creating download file";
            Element.show('spinner');
            var url='display_genotype.php?function=typeTabDelimiterGBS'+ '&expuid=' + experiment_uid+ '&mm='+max_missing+'&mmaf='+min_maf;
            jQuery.ajax({
              type: "GET",
              url: "display_genotype.php",
              data: 'function=typeTabDelimiterGBS'+ '&expuid=' + experiment_uid+ '&mm='+max_missing+'&mmaf='+min_maf,
              success: function(data, results) {
                jQuery("#results").html(data);
                document.getElementById('status').innerHTML = "";
              },
              error: function() {
                alert('Error in getting download file');
              }
           });
        }

        function mrefresh(trial_code) {
            var mm = $('mm').getValue();
            var mmaf = $('mmaf').getValue();
            var url='display_genotype.php?function=typeData'+ '&mm='+mm+'&mmaf='+mmaf+ '&trial_code='+trial_code;
            // Opens the url in the same window
            window.open(url, "_self");
        }

function filterDesc(min_maf, max_missing, max_miss_line) {
  alert("1. Marker allele frequency is calculated for the selected lines.\n2. Markers are removed that have MAF less than " +  min_maf + "% or are missing in more than " + max_missing + "% of the lines.\n3. Lines are removed if they are missing more than " + max_miss_line + "% of the marker data.\nAfter changing the default settings for the filter, select Download to use the new paramaters");
}

