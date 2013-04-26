<?php

    /**
     * display minor allele frequence and missing data using selected lines
     * @param array $lines
     * @param floats $min_maf
     * @param floats $max_missing
    */
        function calculate_af($lines, $min_maf, $max_missing, $max_miss_line) {
         //calculate allele frequencies using 2D table

         if (isset($_SESSION['clicked_buttons'])) {
           $tmp = count($_SESSION['clicked_buttons']);
           $saved_session = $saved_session . ", $tmp markers";
           $markers = $_SESSION['clicked_buttons'];
           $marker_str = implode(',',$markers);
         } else {
           $markers_filtered = array();
           $markers = array();
           $marker_str = "";
         }

         //get location information for markers
         $sql = "select marker_uid, marker_name from allele_byline_idx order by marker_uid";
         $res = mysql_query($sql) or die(mysql_error() . "<br>" . $sql);
         $i=0;
         while ($row = mysql_fetch_array($res)) {
          $uid = $row[0];
          $marker_list[$i] = $row[0];
          $marker_list_name[$i] = $row[1];
          $marker_list_loc[$uid] = $i;
          $i++;
         }

         //get location information for lines
         $sql = "select line_record_uid, line_record_name from line_records";
         $res = mysql_query($sql) or die(mysql_error() . "<br>" . $sql);
         while ($row = mysql_fetch_array($res)) {
          $uid = $row[0];
          $line_list_name[$uid] = $row[1];
         }
   
         //calculate allele frequence and missing
         $marker_misscnt = array();
         foreach ($lines as $line_record_uid) {
           $sql = "select alleles from allele_byline where line_record_uid = $line_record_uid";
           $res = mysql_query($sql) or die(mysql_error() . "<br>" . $sql);
           if ($row = mysql_fetch_array($res)) {
             $alleles = $row[0];
             $outarray = explode(',',$alleles);
             $alleles_mem[$line_record_uid] = $alleles;
             $i=0;
             foreach ($outarray as $allele) {
               if ($allele=='AA') { $marker_aacnt[$i]++; }
               elseif (($allele=='AB') or ($allele=='BA')) { $marker_abcnt[$i]++; }
               elseif ($allele=='BB') { $marker_bbcnt[$i]++; }
               elseif (($allele=='--') or ($allele=='')) { $marker_misscnt[$i]++; }
               else { echo "illegal genotype value $allele for marker $marker_list_name[$i]<br>"; }
               $i++;
             }
           } else {
             foreach ($marker_misscnt as $i=>$value) {
               $marker_misscnt[$i]++;
             }
           }
         }
         $i=0;
         $num_mark = 0;
         $num_maf = $num_miss = $num_removed = 0;
         foreach ($marker_list as $marker_uid) {
           $total = $marker_aacnt[$i] + $marker_abcnt[$i] + $marker_bbcnt[$i] + $marker_misscnt[$i];
           $total_af = 2 * ($marker_aacnt[$i] + $marker_abcnt[$i] + $marker_bbcnt[$i]);
           if ($total_af > 0) {
             $maf = 100 * min((2 * $marker_aacnt[$i] + $marker_abcnt[$i]) /$total, ($marker_abcnt[$i] + 2 * $marker_bbcnt[$i]) / $total);
             $miss = 100 * $marker_misscnt[$i]/$total;
             if ($maf < $min_maf) $num_maf++;
             if ($miss > $max_missing) $num_miss++;
             if (($miss > $max_missing) OR ($maf < $min_maf)) {
               $num_removed++;
             } else {
               $markers_filtered[] = $marker_uid;
             }
             $num_mark++;
           }
           $i++;
         }
         $_SESSION['filtered_markers'] = $markers_filtered;
         $count = count($markers_filtered);

         //calculate missing from each line
         foreach ($lines as $line_record_uid) {
           $alleles = $alleles_mem[$line_record_uid];
           $outarray = explode(',',$alleles);
           $line_misscnt[$line_record_uid] = 0;
           foreach ($markers_filtered as $marker_uid) {
              $loc = $marker_list_loc[$marker_uid];
              $allele = $outarray[$loc];
              if (($allele=='--') or ($allele=='')) {
                  $line_misscnt[$line_record_uid]++;
              }
           }
         }
         $lines_removed = 0;
         $lines_removed_name = "";
         $num_line = 0;
         foreach ($lines as $line_record_uid) {
           $total = count($markers_filtered);
           $miss = 100*$line_misscnt[$line_record_uid]/$total;
           if ($miss > $max_miss_line) {
             $lines_removed++;
             if ($lines_removed_name == "") {
               $lines_removed_name = $line_list_name[$line_record_uid];
             } else {
               $lines_removed_name = $lines_removed_name . ", $line_list_name[$line_record_uid]";
             }
           } else {
             $lines_filtered[] = $line_record_uid;
           }
           $num_line++;
         }
         $_SESSION['filtered_lines'] = $lines_filtered;
         if (strlen($lines_removed_name) > 75) {
           $comm = substr($lines_removed_name, 0, 75) . " ...";
         } else {
           $comm = $lines_removed_name;
         }
         $count2 = count($lines_filtered);

          ?>
        <table>
        <tr><td><a onclick="filterDesc( <?php echo ($min_maf) ?>, <?php echo ($max_miss_line) ?>, <?php echo ($max_miss_line) ?>)">Removed by filtering</a><td>Remaining
        <tr><td><b><?php echo ($num_maf) ?></b><i> markers have a minor allele frequency (MAF) less than </i><b><?php echo ($min_maf) ?></b><i>%
        <br><b><?php echo ($num_miss) ?></b><i> markers are missing more than </i><b><?php echo ($max_missing) ?></b><i>% of data
        <td><b><?php echo ("$count") ?></b><i> markers</i>
        <tr><td>
        <?php
        if ($lines_removed == 1) {
          echo ("</i><b>$lines_removed") ?></b><i> line is missing more than </i><b><?php echo ($max_miss_line) ?></b><i>% of data</b></i>
          <?php
        } else {
          echo ("</i><b>$lines_removed") ?></b><i> lines are missing more than </i><b><?php echo ($max_miss_line) ?></b><i>% of data </b></i>
          <?php
        }
        if ($lines_removed_name != "") {
          ?>
          <br>(<a onclick="linesRemoved('<?php echo ($lines_removed_name) ?>')"><?php echo ($comm) ?></a>)
          <?php
        }
        echo "<td><b>$count2</b><i> lines</a>";
        echo ("</table>");
}
