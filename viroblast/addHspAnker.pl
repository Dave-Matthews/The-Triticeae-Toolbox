#!/usr/bin/perl

use strict;
use warnings;

my $jobid=$ARGV[0];
# my $dataPath = "data";
my $dataPath = "/tmp/tht/blast";
my $infile = "$dataPath/$jobid.out";

open(IN, $infile);
open (OUT, ">$dataPath/$jobid.html");
my $hit_num = 0;
my $hsp_num;
	while (my $line = <IN>) {
        	chomp $line;
		 if($line =~ /script>><a/ ||$line =~/^>/) {
                        $hit_num++;
			$hsp_num=0;
               }
		   if($line =~ /Score =/) {
                        $hsp_num++ ;
			my $nline=$line."<a name=\"".$hit_num."_".$hsp_num."\"></a>";
                	print OUT $nline,"\n";
                }else{
                	print OUT $line,"\n";
		}
        }
close OUT;

