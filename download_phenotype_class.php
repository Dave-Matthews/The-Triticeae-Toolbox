<?php
/**
 * Download phenotype information for experiment
 *
 */

namespace T3;

class Downloads
{
    public function __construct($function = null)
    {
        switch ($function) {
            case 'downloadMean':
                $this->downloadMean();
                break;
            case 'downloadPlot':
                $this->downloadPlot();
                break;
            default:
                echo "Error: bad option\n";
                break;
        }
    }

    private function downloadMean()
    {
        global $mysqli;
        $delimiter = ",";
        if (isset($_GET['pi'])) {
            $experiment_uid = $_GET['pi'];
        } else {
            echo "Error: experiment uid not set\n";
            return;
        }

        // get all line data for this experiment
        $sql="SELECT tht_base_uid, line_record_uid, check_line FROM tht_base WHERE experiment_uid='$experiment_uid'";
        $result_thtbase=mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
        while ($row_thtbase=mysqli_fetch_array($result_thtbase)) {
            $thtbase_uid[] = $row_thtbase['tht_base_uid'];
            $linerecord_uid[] = $row_thtbase['line_record_uid'];
            $check_line[] = $row_thtbase['check_line'];
        }
        $num_lines = count($linerecord_uid);
        $titles=array('Line Name'); //stores the titles for the display table with units
        $titles[]="GRIN Accession";//add CAP Code column to titles

        if (empty($thtbase_uid)) {
            echo "Error: experiment not found\n";
            return;
        }
        $thtbasestring = implode(",", $thtbase_uid);
        $sql1="SELECT DISTINCT p.phenotypes_name as name, p.phenotype_uid as uid, units.unit_name as unit,
                units.sigdigits_display as sigdig
                FROM phenotype_data as pd, phenotypes as p, units
                WHERE p.phenotype_uid = pd.phenotype_uid
                AND units.unit_uid = p.unit_uid
                AND pd.tht_base_uid IN ($thtbasestring)";
        //echo $sql1."<br>";
        $result1=mysqli_query($mysqli, $sql1) or die(mysqli_error($mysqli));
        $num_phenotypes = mysqli_num_rows($result1);

        while ($row1=mysqli_fetch_array($result1)) {
            $phenotype_data_name[]=$row1['name'];
            $phenotype_uid[]=$row1['uid'];
            $unit_sigdigits[]=$row1['sigdig'];
            $unit_name[]=$row1['unit'];
            $titles[]=ucwords($row1['name'])." (".strtolower($row1['unit']).")";
        }
      
        $titles[]="Check"; //add the check column to the display table

        $all_rows=array(); //2D array that will hold the values in table format to be displayed
        $all_rows_long=array(); // For the full unrounded values
        $single_row=array(); //1D array which will hold each row values in the table format to be displayed
        $single_row_long=array();

        $stringData = implode($delimiter, $titles);
        $stringData .= "\n";
        header('Content-type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="trial_data.csv"');
        echo "$stringData";

         //---------------------------------------------------------------------------------------------------------------
        //Go through lines to create a data table for display
        for ($lr_i=0; $lr_i<$num_lines; $lr_i++) {
            $thtbaseuid=$thtbase_uid[$lr_i];
            $linerecorduid=$linerecord_uid[$lr_i];
            //echo $linerecorduid."  ".$thtbaseuid."<br>";

            $sql_lnruid="SELECT line_record_name FROM line_records WHERE line_record_uid='$linerecorduid'";
            $result_lnruid=mysqli_query($mysqli, $sql_lnruid) or die(mysqli_error($mysqli));
            $row_lnruid=mysqli_fetch_assoc($result_lnruid);
            $lnrname=$row_lnruid['line_record_name'];
            $single_row[0]=$lnrname;
            $single_row_long[0]=$lnrname;

            $sql_gr="select barley_ref_number
            from barley_pedigree_catalog bc, barley_pedigree_catalog_ref bcr
            where barley_pedigree_catalog_name = 'GRIN'
            and bc.barley_pedigree_catalog_uid = bcr.barley_pedigree_catalog_uid
            and bcr.line_record_uid = '$linerecorduid'";
            $result_gr=mysqli_query($mysqli, $sql_gr) or die(mysqli_error($mysqli));
            $row_gr=mysqli_fetch_assoc($result_gr);
            $single_row[1]=$row_gr['barley_ref_number'];
            $single_row_long[1]=$row_gr['barley_ref_number'];
   
            for ($i=0; $i<$num_phenotypes; $i++) {
                $puid=$phenotype_uid[$i];
                $sigdig=$unit_sigdigits[$i];
                $sql_val="SELECT value FROM phenotype_data
                    WHERE tht_base_uid='$thtbaseuid'
                    AND phenotype_uid = '$puid'";
                //echo $sql_val."<br>";
                $result_val=mysqli_query($mysqli, $sql_val);
                if (mysqli_num_rows($result_val) > 0) {
                    $row_val=mysqli_fetch_assoc($result_val);
                    $val=$row_val['value'];
                    $val_long=$val;
                    if ($sigdig >= 0) {
                          $val = floatval($val);
                          $val=number_format($val, $sigdig);
                    }
                } else {
                    $val = "--";
                    $val_long = "--";
                }
                if (empty($val)) {
                    $val = "--";
                    $val_long = "--";
                }
                $single_row[$i+2]=$val;
                $single_row_long[$i+2]=$val_long;
            }

         //-----------------------------------------check line addition

            if ($check_line[$lr_i]=='yes') {
                $check=1;
            } else {
                $check=0;
            }
            $single_row[$num_phenotypes+2]=$check;
            $single_row_long[$num_phenotypes+2]=$check;
            //-----------------------------------------
            //var_dump($single_row_long);
            $stringData= implode($delimiter, $single_row_long);
            //echo $stringData."<br>";
            $stringData.="\n";

            echo "$stringData";
            $all_rows[]=$single_row;
            $all_rows_long[]=$single_row_long;
        }

        //-----------------------------------------get statistics
        $mean_arr=array('Mean','');
        $se_arr=array('Standard Error','');
        // Unformatted mean and SE
        $unformat_mean_arr=array('Mean','');
        $unformat_se_arr=array('Standard Error','');

        $nr_arr=array('Number Replicates','');
        $prob_arr=array('Prob > F','');

        $fmean="Mean,";
        $fse="SE,";
        $fnr="Number Replicates,";
        $fprob="Prob gt F,";

        for ($i=0;$i<$num_phenotypes;$i++)
        {
            $puid=$phenotype_uid[$i];
            $sigdig=$unit_sigdigits[$i];

            $sql_mdata="SELECT mean_value,standard_error,number_replicates,prob_gt_F
                FROM phenotype_mean_data
                WHERE phenotype_uid='$puid'
                AND experiment_uid='$experiment_uid'";
            $res_mdata=mysqli_query($mysqli, $sql_mdata) or die(mysqli_error($mysqli));
            $row_mdata=mysqli_fetch_array($res_mdata);
            $mean=$row_mdata['mean_value'];
            $se=$row_mdata['standard_error'];
            $nr=$row_mdata['number_replicates'];
            $prob=$row_mdata['prob_gt_F'];

            if($mean!=0) {
                $unformat_mean_arr[] = $mean;
                if ($sigdig>=0) $mean=number_format($mean,$sigdig);
                $mean_arr[] = $mean;
            } else {
                $unformat_mean_arr[] = "--";
                $mean_arr[]="--";
            }

            if($se!=0) {
                $unformat_se_arr[] = $se;
                if ($sigdig>=0) $se=number_format($se,$sigdig);
                $se_arr[] = $se;
            } else {
                $se_arr[]="--";
                $unformat_se_arr[] = "--";
            }

            if($nr==0) {
                $nr="--";
            }
            $nr_arr[]=$nr;

            if($prob!="" && $prob!="NULL") {
                $prob_arr[]=$prob;
             } else {
                $prob_arr[]="--";
            }

        }
      
        $fmean= implode($delimiter, $mean_arr)."\n";
        $fse= implode($delimiter, $se_arr)."\n";
        $fnr= implode($delimiter, $nr_arr)."\n";
        $fprob= implode($delimiter, $prob_arr)."\n";

        $ufmean= implode($delimiter, $unformat_mean_arr)."\n";
        $ufse= implode($delimiter, $unformat_se_arr)."\n";

        echo "$ufmean";
        echo "$ufse";
        echo "$fnr";
        echo "$fprob";

    }
  
    private function downloadPlot()
    {
        global $mysqli;
        if (isset($_GET['pi'])) {
            $puid = $_GET['pi'];
        } else {
            echo "Error: experiment uid not set\n";
            return;
        }
        $sql = "select trial_code from experiments where experiment_uid = ?";
        if ($stmt = mysqli_prepare($mysqli, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $puid);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $trial_code);
            mysqli_stmt_fetch($stmt);
            mysqli_stmt_close($stmt);
        } else {
            echo "Error: bad query\n";
        }
  
        $sql = "select phenotype_uid, phenotypes_name from phenotypes";
        if ($stmt = mysqli_prepare($mysqli, $sql)) {
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $phenotype_uid, $name);
            while (mysqli_stmt_fetch($stmt)) {
                $trait_list[$phenotype_uid] = $name;
            }
            mysqli_stmt_close($stmt);
        }

        header('Content-type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="trial_data_plot.csv"');
        echo "Trial Code,Plot,Trait,Value\n";
        $sql = "select plot, phenotype_uid, value from phenotype_plot_data, fieldbook
            where phenotype_plot_data.plot_uid = fieldbook.plot_uid
            and phenotype_plot_data.experiment_uid = ? order by phenotype_uid, plot";
        if ($stmt = mysqli_prepare($mysqli, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $puid);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $plot_uid, $phenotype_uid, $value);
            while (mysqli_stmt_fetch($stmt)) {
                $trait = $trait_list[$phenotype_uid];
                echo "$trial_code,$plot_uid,$trait,$value\n";
            }
            mysqli_stmt_close($stmt);
        } else {
            echo "Error: bad query\n";
        }
    }
}
