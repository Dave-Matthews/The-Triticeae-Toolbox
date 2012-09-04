The SNP genotype data is stored in Illumina AB format. The GBS genotype data is converted to Illumina AB format when 
during import based on the A_allele and B_allele values in the markers table. For GBS genotype data the Illumina AB rules
are not used becuase flanking sequence is not always available. When downloading the data the following
formats are available
Tassel V3: Illumina AB format
Tassel V4: The genotype data is converted to ACTG based on the A_allele and B_allele values in the markers table. For
Illumina data this will convert the base calls similar to the Design Strand (assay design strand)
