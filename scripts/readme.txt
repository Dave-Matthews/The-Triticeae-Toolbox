This directory contains scripts that are designed to be run from the command line. 

1. Loading GBS data with over 100K markers

load_gbs_bymarker.php database input_file trial_code
example
  "php load_gbs_bymarker.php T3wheat all_chr_300acc_SNPs_min50acc_rmMultiAllele_addHeader.vcf.tassel HWWAMP_GBS_2014"
  "php load_gbs_bymarker.php T3wheat all_chr_300acc_SNPs_min50acc_rmMultiAllele_addHeader.vcf.rrblup HWWAMP_GBS_2014"

load_gbs_frequencies.php database input_file trial_code
example
  "php load_gbs_frequencies.php T3wheat all_chr_300acc_SNPs_min50acc_rmMultiAllele_addHeader.vcf.frequencies HWWAMP_GBS_2014"

2. Creating physical map from Chromosome Survey Sequence assembled by the International Wheat Genome Sequencing Consurtium (IWGSC).

blast-iwgsc.php - BLAST all the markers against reference downloaded from IWGSC
parse-blast.php - create a BLAST file of the best matches for each marker
load-blast.php - create a map file from the previous file
