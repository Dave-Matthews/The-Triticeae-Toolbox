#!/usr/bin/perl -w
# ~/THT/Data/Genotyping/AB_to_ATCG/AB_to_ATCG.pl, dem 1nov10, from:
# ~/Wheat/Me,wheat-maps/Sourdille/parsemaps.pl, dem 14oct09, from:
# ~/Wheat/AppelsMap/Traitloci/parse.pl, template for parsing MASgene-locations-More.txt

# Invoke with "AB_to_ATCG.pl <input-file>.  Outputs to <input-file>.txt.

# Sample input:
#IlmnID,Name,IlmnStrand,SNP,AddressA_ID,AlleleA_ProbeSeq,AddressB_ID,AlleleB_ProbeSeq,GenomeBuild,Chr,MapInfo,Ploidy,Species,Source,SourceVersion,SourceStrand,SourceSeq,TopGenomicSeq,BeadSetID
#BK_01-0_T_R_1867162176,BK_01,TOP,[A/C],62787399,TTGCTCTCCTCTGACTTGCAGTGGGCACAGTCCTTGCACTCGCCGGTGAA,,,0,0,0,diploid,Hordeum vulgare,0,0,BOT,CCTCGTTTCACCAAATAGCTTCTTTATTGCAGMATYGTCGAGAGCGTCGGAGAGGGCGTGACTGAGCTTGTGCCGGGYGACCATGTMCTCCCGGT[T/G]TTCACCGGCGAGTGCAAGGACTGTGCCCACTGCAAGTCAGAGGAGAGCAACCTTTGTGATCTCCTTAGGATCAATG,CATTGATCCTAAGGAGATCACAAAGGTTGCTCTCCTCTGACTTGCAGTGGGCACAGTCCTTGCACTCGCCGGTGAA[A/C]ACCGGGAGKACATGGTCRCCCGGCACAAGCTCAGTCACGCCCTCTCCGACGCTCTCGACRATKCTGCAATAAAGAAGCTATTTGGTGAAACGAGG,448
#BK_02-0_B_F_1867162177,BK_02,BOT,[G/C],67741393,TCTACTGCATCTTCGAGGGCGGCACCCCCGACGCCCGCCTCGACTGGGGG,46675414,TCTACTGCATCTTCGAGGGCGGCACCCCCGACGCCCGCCTCGACTGGGGC,0,0,0,diploid,Hordeum vulgare,0,0,BOT,CATCGCCGACATCGTCATCAACCACCGCACGGCGGAGCACAAGGACGGCCGGGGCATCTACTGCATCTTCGAGGGCGGCACCCCCGACGCCCGCCTCGACTGGGG[C/

# $ARGV[0] is the first argument, the name of the input file.
open(IN, $ARGV[0]);
open(OUT, "> $ARGV[0].txt");
select(OUT);               # Set OUT as default for all print statements,
                           # instead of stdout.
#select(STDOUT);

# Prepend explanatory label to output file's header:
$date = `date "+%e %b %Y"`;
print("-- $ARGV[0].txt, $date");
print("-- Created by $0 from: \n");
print("-- $ARGV[0]\n");
print("\n");
print("marker_name,A_allele,B_allele,sequence\n");

# Read in the file:
# <IN> is the current file, argv[1].
while (<IN>) {		         
    chomp;
    if ((/,TOP/) || (/,BOT/)) {	# Read in the data values.	
	@values = split(/,/);	
	$marker = $values[1];
	$ilmn_strand = $values[2];
	$snp = $values[3];
	$a_allele = substr($snp,1,1);
	$b_allele = substr($snp,3,1);
	$TopGenomicSeq = $values[17];
	$snp_sequence = $TopGenomicSeq;
	# If Illumina used the bottom strand, output reverse-complement.
	if ($ilmn_strand =~ /BOT/) {
	    # First reverse it.
	    $snp_sequence = reverse($snp_sequence);
	    # Now complement it, including ambiguity codes.
	    # From http://blossomassociates.net/molbio/revcomp.sh
	    $snp_sequence =~ tr/ACGTUMRYKVHDB\[\]/TGCAAKYRMBDHV\]\[/;
            $snp_sequence =~ tr/acgtumrykvhdb\[\]/tgcaakyrmbdhv\]\[/;
	}
	print("$marker,$a_allele,$b_allele,$snp_sequence\n");
    }
}



 
