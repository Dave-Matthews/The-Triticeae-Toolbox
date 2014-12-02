This directory contains scripts for loading data into the database that are designed to be run from the command line. 

Loading GBS data with over 100K markers

load_gbs_bymarker.php database input_file trial_code
example
  "php load_gbs_bymarker.php T3wheat all_chr_300acc_SNPs_min50acc_rmMultiAllele_addHeader.vcf.tassel HWWAMP_GBS_2014"
  "php load_gbs_bymarker.php T3wheat all_chr_300acc_SNPs_min50acc_rmMultiAllele_addHeader.vcf.rrblup HWWAMP_GBS_2014"

load_gbs_frequencies.php database input_file trial_code
example
  "php load_gbs_frequencies.php T3wheat all_chr_300acc_SNPs_min50acc_rmMultiAllele_addHeader.vcf.frequencies HWWAMP_GBS_2014"
