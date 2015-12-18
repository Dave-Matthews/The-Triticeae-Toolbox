#!/usr/bin/perl -w
# ~/THT/Data/Genotyping/AB_to_ATCG/AB_to_ATCG.pl, dem 1nov10, from:
# ~/Wheat/Me,wheat-maps/Sourdille/parsemaps.pl, dem 14oct09, from:
# ~/Wheat/AppelsMap/Traitloci/parse.pl, template for parsing MASgene-locations-More.txt

# Invoke with "AB_to_ATCG.pl <input-file>.  Outputs to <input-file>.txt.

# Sample input:
#Ilmn ID,Name,oligo1,oligo2,oligo3,IllumiCode name,Illumicode Seq,Ilmn Strand,SNP,CHR,Ploidy,Species,MapInfo,TopGenomicSeq,CustomerStrand
#1375-2534-20070122_B_R_2133872,1375-2534,ACTTCGTCAGTAACGGACTCTACGAACGAAAAAGCACAGGAG,GAGTCGAGGTCATATCGTTCTACGAACGAAAAAGCACAGGAC,AGGATTTAACTATATAAATCATCATCACTGCCAATCGAGCGCGATACATGTCTGCCTATAGTGAGTC,0003,CTGCCAATCGAGCGCGATACAT,BOT,[G/C],0,diploid,hordeum vulgare,0,GGGAGCGAGTTCCGGTTCGCTGACCAGCCGGCTGCGGGCGCTGCCTCTGCCGCTGCGGCCGACCCTTTTGCATCCGCTGCCTCAGCAGCCGATGATGATGATTTATATAGTTAAATCCTT[C/G]TCCTGTGCTTTTTCGTTCGTAGATGTGGTGCTTTGGTATGTACACGGGAAAACTATGTTCTTAAGTTAATATACTGTGATGCACAGTTAATCGTTGTGCCGGTTAAAAAAAAAAAAAAAA,TOP
#5019-879-20070122_B_R_2133873,5019-879,ACTTCGTCAGTAACGGACGACAGAGACTCCTACTATGCGCCGT,GAGTCGAGGTCATATCGTGACAGAGACTCCTACTATGCGCCGC,CCATTTGTAGGACTCCATTGTTGCGTTGCGACTACCGATACGTGTCTGCCTATAGTGAGTC,0010,TGCGTTGCGACTACCGATACGT,BOT,[T/C],0,diploid,hordeum vulgare,0,TGATGAATTCTGTGGGAAGATTGTTGCTTCCACTACCAGCATTCCGTGTGACATTGTGCTACATGATAGCAAAGTGAGTGGTGCCCTAACTGCAGGTGACAATGGAGTCCTACAAATGGC[A/G]CGGCGCATAGTAGGAGTCTCTGTGGATGAGATGCTGGTGTTGACTGTTGCGGCTGCTGTCGGTGATGATGCTTTATCTGCCTGCACTGTTCAGTTTACTCCAAGGCGCAATGGTTACGAT,TOP

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
	$ilmn_strand = $values[7];
	$snp = $values[8];
	$a_allele = substr($snp,1,1);
	$b_allele = substr($snp,3,1);
	$TopGenomicSeq = $values[13];
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



 
