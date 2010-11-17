<?

require 'config.php';
include($config['root_dir'] . 'includes/bootstrap.inc');
//require_once 'Spreadsheet/Excel/Writer.php';
connect();
include($config['root_dir'].'theme/normal_header.php');


	//	$firephp = FirePHP::getInstance(true);
		$outputheader = '';
		$output = '';
		$doneheader = false;
		$delimiter ="\t";
      
		 
			$max_missing = 100;
		
			//$firephp->log("in sort markers2");
        $min_maf = 0;//IN PERCENT
       
			//$firephp->log("in sort markers".$max_missing."  ".$min_maf);

	 //get lines and filter to get a list of markers which meet the criteria selected by the user
          $sql_mstat = "SELECT af.marker_uid as marker, m.marker_name as name, SUM(af.aa_cnt) as sumaa, SUM(af.missing)as summis, SUM(af.bb_cnt) as sumbb,
					SUM(af.total) as total, SUM(af.ab_cnt) AS sumab
					FROM allele_frequencies AS af, markers as m
					WHERE m.marker_uid = af.marker_uid
						AND af.experiment_uid =79
					group by af.marker_uid"; 

			$res = mysql_query($sql_mstat) or die(mysql_error());
			$num_maf = $num_miss = 0;

			while ($row = mysql_fetch_array($res)){
                $maf = round(100*min((2*$row["sumaa"]+$row["sumab"])/$row["total"],($row["sumab"]+2*$row["sumbb"])/$row["total"]),1);
                $miss = round(100*$row["summis"]/$row["total"],1);
					
						$marker_names[] = $row["name"];
						$outputheader .= $delimiter.$row["name"];
						$marker_uid[] = $row["marker"];
					
			}
			$marker_uid = implode(",",$marker_uid);

        
		
		  $lookup = array(
			  'AA' => '1',
			  'BB' => '-1',
			  '--' => 'NA',
			  'AB' => '0'
		  );
	    
		
		// get a list of marker names which meet the criteria selected by the user
          $sql_mstat = "SELECT marker_name as name
					FROM markers
					WHERE marker_uid IN ($marker_uid)"; 
			$res = mysql_query($sql_mstat) or die(mysql_error());

			while ($row = mysql_fetch_array($res)){
						$marker_names[] = $row["name"];
			}
			$nelem = count($marker_uid);

			// make an empty line with the markers as array keys, set default value
			//  to the default missing value for either qtlminer or tassel
			// places where the lines may have different values
			
		  
			
			
         $sql1 = " SELECT lr.line_record_name, m.marker_name AS name,
                    CONCAT(a.allele_1,a.allele_2) AS value
			FROM
            markers as m,
            line_records as lr,
            alleles as a,
            tht_base as tb,
            genotyping_data as gd
			WHERE
            a.genotyping_data_uid = gd.genotyping_data_uid
				AND m.marker_uid = gd.marker_uid
				AND gd.marker_uid IN ($marker_uid)
				AND tb.line_record_uid = lr.line_record_uid
				AND gd.tht_base_uid = tb.tht_base_uid
				AND tb.experiment_uid IN ($experiments)
		  ORDER BY lr.line_record_name, m.marker_uid  ";


		$last_line = "some really silly name that noone would call a plant";
		$res = mysql_query($sql1) or die(mysql_error());
		
		$outarray = $empty;
		$cnt = $num_lines = 0;
		while ($row = mysql_fetch_array($res)){
				//first time through loop
				if ($cnt==0) {
					$last_line = $row['line_record_name'];
				}
				
			if ($last_line != $row['line_record_name']){  
					// Close out the last line
					$output .= "$last_line\t";
					$outarray = implode($delimiter,$outarray);
					$output .= $outarray."\n";
					//reset output arrays for the next line
					$outarray = $empty;
					$mname = $row['name'];				
					$outarray[$mname] = $lookup[$row['value']];
					$last_line = $row['line_record_name'];
					$num_lines++;
			} else {
					 $mname = $row['name'];				
					 $outarray[$mname] = $lookup[$row['value']];
			}
			$cnt++;
		}
		
		  //save data from the last line
		  $output .= "$last_line$delimiter";
		  $outarray = implode($delimiter,$outarray);
		  $output .= $outarray."\n";
		  $num_lines++;
		  
		
		  return $outputheader."\n".$output;
	
		//  return $num_lines.$delimiter.$nelem."\n".$outputheader."\n".$output;
	   
	
?>
