#!/usr/bin/perl -w
#
# Program:   parseBlastReport.pl
# 
# Author:    Ralf Sigmund
#            sigmund@ipk-gatersleben.de
# Institute: PGRC BI, IPK Gatersleben
# Date:      18.07.2004
#
# Changes:   - BUG: "Can't call method "algorithm" on an undefined value"
#                   , when first BLAST report in input file has no hit.
#                   -> ELIMINATED. hm, 2005-07-22
#
#
#
# Purpose:
# This program reads one output file containing several Blast Results
# obtained from ncbi blast excecution (see Remarks)
# and generates an EXCEL-readable table from all lines (if available) of
# blastn-results.
# 
#
# after successfull excecution of blast You use this script to parse the output
#and format it for later usage in EXCEL 
#
# Remarks:
# HOW TO RUN BLAST:
#   Windows: C:\blast\blastall.exe -p blastn [-e 000.1] -d <DatabaseName> -i <INPUTFILE> -o <OUTFILE>
#     where: DatabaseName denotes a previously formatted database 
#            optional -e realnumber denotes only hits with an expectation Value below this will be considered
#            INPUTFILE a collection of input sequences in Fasta Format
#            -o <OUTFILE> the outputfile for the balst run which will then be used as input for this script
# HOW to prepare a DATABASE for BLASTING with formatdb (required befor running blast against this database)
#    Windows: C:\blast>formatdb -i <INPUTFILE> -p F -o T -n <DATABASENAME>}
#    where   INPUTFILE a collection of input sequences in Fasta Format which will become Your blastable Database
#            DATABASENAME the name of this new Database
#
#
#
#
# Invocation: parseBlastReport.pl -i infile -o outfile [-v]
# -v the output will have one line for each HSP
#    and various Information about this HSP will be given in additional columns
#    otherwise the output will only have one line per Hit
# -s output only one single hit per query -- this results in one
#    Excel Line per matching query Seq (if you als do not use -v)
# -m <number> output a Maximum of <number> best hits

################################################################################
use strict;
use Getopt::Std;
use IO::File;
use Bio::SearchIO;
use File::Basename;
use File::Spec;
use Bio::Index::Fasta;
use Cwd;
use XML::DOM;
use Data::Dumper;

#use lib (dirname($0));

use IPK::hspSummary;

my $usage = "\nInvocation: parseBlastReport.pl [-s|-m <number>] [-v] -i infile -o outfile "."\n".
"-v the output will have one line for each HSP"."\n".
"   and various Information about this HSP will be given in additional columns"."\n".
"   otherwise the output will only have one line per Hit"."\n".
"-s output only one single hit per query -- this results in one"."\n".
"   Excel Line per matching query Seq (if you also do not use -v)"."\n".
"-m <number> output a Maximum of <number> best hits"."\n";

my $DEBUG = 0;

################################################################################
### specific subs
sub handleHeaders ($$$$){
  #handle headers 
  my ($outstream, $baseElement, $attributes, $verbose) = @_;
  print $outstream generateHeader($baseElement,'result',$attributes) . "\t"; #result columns
  print $outstream generateHeader($baseElement,'hit',$attributes)  . "\t"; #hit columns
  my $hspHeaders;
  if($verbose){
    $hspHeaders = generateHeader($baseElement,'hsp',$attributes);
    print $outstream $hspHeaders . "\n" ;
  } else {
    $hspHeaders = generateHeader($baseElement,'hspSummary',$attributes);
    print $outstream $hspHeaders . "\n";
  }
}



sub genValueList ($$$$){
  #work with the first element that has a matching name attribute
  #the result $r must have an accesible hash
  my ($el,$s,$r,$attributes) = @_;
  my @a;
  my $sel = getElementByNameAttr($el,$s);
  # now work with the selected element $sel
  
  # handle all <el></el> children 
  my $li = getElList($sel);
  for my $c (@$li){
    if ($c eq 'significance'){
      my $signi = $r->significance;
      if($signi =~ /^e/i){
        {$signi = "1".$signi};
      }
      push @a, $signi;
    }
    else {
      if(not defined($r->$c)) {
        print "undefined value for $c\n";  
      }
      push @a,  $r->$c;
    }
  }
  #handle categories
  my $categoryVals = genCatValueList($sel,$r,$attributes);
  push @a, @$categoryVals;
  return \@a;
}
sub genCatValueList ($$$){
  # find categories for the given node
  my ($node,$r,$attributes) = @_; 
  
  my @b =();
  for my $kid ($node->getChildNodes){
    next if ($kid->getNodeType != ELEMENT_NODE);
    next if ($kid->getTagName ne "category");
    my $categoryType = $kid->getAttributeNode ("type")->getValue;    
    my $li = getElList($kid);
    for my $c (@$li){
      my $na = $kid->getAttributeNode ("name")->getValue;
      
      next if (not included($kid,$attributes));
      
      if($categoryType eq "methpar"){
        push @b, $r->$na($c);
      } elsif ($categoryType eq "field") {
        push @b, $r->$c->$na;
      } else {
        die "unknown category type!";  
      }
    }
  }
  return \@b;
  }
sub included ($$){
  #support restrictions to outputted fields
  my ($node,$attributes) = @_;
  my $restrictionAttrNode = $node->getAttributeNode ("restriction");
  return 1 if ( not $restrictionAttrNode);
  
  my $restrictionStr = $restrictionAttrNode->getValue;
  my @restrictionLi = split /,/, $restrictionStr;
  my $is = intersection($attributes,\@restrictionLi);
  return $is;
}
sub intersection($$){
  my ($ar1,$ar2) = @_;
  #my @union = my @isect = my @diff = ();
  my %union = my %isect = ();
  my %count = ();
  foreach my $e (@$ar1) { $union{$e} = 1 }

  foreach my $e (@$ar2) {
    if ( $union{$e} ) { $isect{$e} = 1 }
    $union{$e} = 1;
  }
  my @union = keys %union;
  my @isect = keys %isect;
  my $isl = @isect;
  return ($isl > 0);
}

#xml subs
sub getElList ($$){
  my ($node,$attributes) = @_;
  my @a =();
  for my $kid ($node->getChildNodes){
    if ($kid->getNodeType == ELEMENT_NODE){
      next if (not included($kid,$attributes));
        if ($kid->getTagName eq "el"){
            push @a,  $kid->getFirstChild->getNodeValue;
        }
    }
  }
  return \@a;
}


sub generateHeader{
  my ($el,$name,$attributes) = @_;
  my @a;
  my $sel = getElementByNameAttr($el,$name);
  return if (not included($sel,$attributes));
  #print $sel->getAttributeNode ("name")->getValue;
  my $prefix = $sel->getAttributeNode ("prefix")->getValue;
  my $li = getElList($sel,$attributes);
  for my $c (@$li){
    push @a, $prefix . $c;
}
  
  #handle categories
  $li = handleCategories($sel);
  for my $c (@$li){
    push @a, $prefix . $c;
  }
  return join "\t",@a;
}
sub handleCategories ($$){
  my ($node,$attributes) = @_;
  my $li;
  my @b =();
  for my $kid ($node->getChildNodes){
    next if ($kid->getNodeType != ELEMENT_NODE);
    next if ($kid->getTagName ne "category");
    my $postfix = $kid->getAttributeNode ("postfix")->getValue;
    next if (not included($kid,$attributes));	    
    $li = getElList($kid,$attributes);
    for my $c (@$li){
      push @b, $c . $postfix ;
    } 
  }
  return \@b;
}


sub getElementByNameAttr ($$) {
  #returns first element Node with matching name attribute
    my ($el,$s) = @_;
    for my $kid ($el->getChildNodes){
	    if ($kid->getNodeType == ELEMENT_NODE){
		if($kid->getAttributeNode ("name")->getValue eq $s){
		    return $kid;
		}
	    }
    }
}
sub handleChilds($); #prototype wg recursion
sub handleChilds($){
  #returns hash reference
  #the hash contains a "els" key pointing to a list of elment tag values
  #and references to sub-hashes with the sub-elements name-attribute as key 
  my ($node) = @_;
  my @a =();
  my %fieldsL = ();
  for my $kid ($node->getChildNodes){
    next if (not $kid->getNodeType == ELEMENT_NODE);
    if ($kid->getTagName ne "el"){
      #echtes element
      #build array of all child elements
      $fieldsL{$kid->getAttributeNode ("name")->getValue}  = handleChilds($kid);
    } else {
      push @a,  $kid->getFirstChild->getNodeValue;    
    }
  }
  $fieldsL{"els"}=\@a;
  return \%fieldsL;
}
sub printN {
  my ($node) = @_;
  if ($node->getNodeType == ELEMENT_NODE){
      if ($node->getTagName ne "el"){ 
          print $node->getTagName ." \n";
      }
      my $name = $node->getAttributeNode ("name");
      if ($name){
          print $name->getValue . "\n";
      }
  }
}

sub fileNameExpand {
  # expand tilde and relative paths
  my ($filename) = @_;
  if($filename =~ /^~/){
      $filename =~ s{ ^ ~ ( [^/]* ) }
            { $1
                  ? (getpwnam($1))[7]
                  : ( $ENV{HOME} || $ENV{LOGDIR}
                       || (getpwuid($>))[7]
                     )
          }ex;
  } else {
      if ( not $filename =~ /^\//){
          $filename = Cwd->cwd() . '/' .$filename
      }
  }
  return $filename;
}

################################################################################

################## main ##########
#xml stuff
my ($parser, $document);
$parser = new XML::DOM::Parser;

my $xmlconfig = 'blastfields.xml';
if (-f $xmlconfig){
  print "using local xmlconfig: $xmlconfig \n";
} else {
  $xmlconfig = "$ENV{HOME}/.blastfields.xml";
  if (-f $xmlconfig){
    print "using private xmlconfig: $xmlconfig \n";
  } else {
    $xmlconfig = "/usr/share/parseBlastReport/blastfields.xml";
    die "could not find an xml configuration file blastfields.xml -- exiting" if (
                                  not -f $xmlconfig);
    print "using default xmlconfig: " . $xmlconfig ."\n";
  }
}
$document = $parser->parsefile($xmlconfig);
my $nodes = $document->getElementsByTagName ('blast');
#my $fields2 = handleChilds($nodes->item(0)); #aufruf mit blast element entfernt: , \%fields
my $fastaOutFile;

#sub openFastaIndx($){
#  my $Index_File_Name = shift;
#  $inx = Bio::Index::Fasta->new($Index_File_Name);
#}


#options...
my %opts = ();
getopts('i:d:o:vsm:uh', \%opts);
my $blastreport = $opts{'i'} || die " -i inputBlastFileName needed..\n$usage";
$blastreport = fileNameExpand($blastreport);
die "$blastreport is not the path to a blast-output file " if (not -f $blastreport);
### protocolling
sub oup($);
sub dieOup($);
my $protcl = $blastreport;
$protcl =~ s/blast_output\///;
$protcl =~ s/\.\w+$/.prtcl/;
open PROTCL, ">>$protcl" || die "could not open protocol file $protcl"; 
oup(`date`. " $0 " );
oup("parsing $blastreport\n");
my $brOrig = $blastreport; # original name kept..

my $maxHitsToReport = -1;
if($opts{'m'}){
    $maxHitsToReport = $opts{'m'};
    dieOUP( "-m <number> should give a positive number of max hits to report for each query\n".
    "$opts{'m'} is an ivalid input parameter") if ($maxHitsToReport <= 0);
}    
$maxHitsToReport = 1 if($opts{'s'});



die $usage if($opts{'u'} || $opts{'h'} );
#my $outfile = $opts{'o'} || die "target File Parameter -o required";


### the report to parse this is an mandatory user input -i
if(-f $blastreport ){
  oup (" Parsing $blastreport");
} else {
  dieOup( "$blastreport is no valid blast output file\n");  
}
##### at this point blastreporthas been resolved to a valid file
##### target directory
my $targetDir = undef;
if($opts{'d'}){
  $targetDir = fileNameExpand($opts{'d'});
  mkdir $targetDir if(not -d $targetDir);
  dieOup( "invalid target directory [$targetDir]" )  if(not -d $targetDir);
}
##### outfile
my $csvOut = undef;
if ($opts{'o'}){
  $csvOut = fileNameExpand($opts{'o'});
  my ($base,$ext);
  ($base, $targetDir, $ext) = fileparse($csvOut);
  mkdir $targetDir if(not -d $targetDir);
  dieOup( "invalid target directory [$targetDir]" )  if(not -d $targetDir);
} else {
  dieOup("No file name for output given.");
}

oup( " will write parse output to csv formatted file: $csvOut\n");

#my $blastreportClean = undef;
#
##do preparsing of unsuccessful blast queries
#if(not $blastreport =~ /.*\.clean/){
#    oup( " removing reports without results...\n");
#    my $preParseScript ="preParseNCBIBlast.pl";
#    my $preParseDir = undef;
#    if (-d $PERLSCRIPTS){
#      $preParseDir = $PERLSCRIPTS;
#    } else {
#      oup("the environment variable PERLSCRIPTS should point to the perl_scripts directory");
#      $preParseDir = '/vol01/BioinfoTools/OwnTools' if (-d '/vol01/BioinfoTools/OwnTools');
#      $preParseDir = '/data/perl_scripts' if (-d '/data/perl_scripts');
#      $preParseDir = '/data-pdw-20/perl_scripts' if (-d '/data-pdw-20/perl_scripts');
#      dieOup("cannot find perl_scripts please adjust PERLSCRIPTS environment var") if (
#                                                            not -d $preParseDir
#                                                                                   );
#      oup ("used perl script dir: $preParseDir \n");
#    }
#    $preParseScript = File::Spec->catfile($preParseDir,$preParseScript);
#    dieOup( "script: $preParseScript could not been found") if(not -e $preParseScript);
#    my $result = `$preParseScript -i $blastreport`;
#    oup( "pre parse result: $result\n" );
#    $blastreportClean = $blastreport . ".clean";
#    dieOup( "\n$preParseScript could not be found or failed as".
#           " file $blastreportClean was not generated") if (not -e $blastreportClean);
#}



################ begin of parsing ##############################################
open(OUT, ">$csvOut") || dieOup( " Can't open \"$csvOut\":\n $!\n");
 
##my $searchio = new Bio::SearchIO(-format => 'blast', -file => $blastreportClean);
my $searchio = new Bio::SearchIO(-format => 'blast', -file => $blastreport);
my $globalDbName = "";
oup( "will skip reporting further hits after " . $maxHitsToReport . 
                  " reported for each query\n") if $maxHitsToReport > 0;
#see if aa or nuc
my $result = $searchio->next_result;
my $hit = $result->next_hit;

# Search for the first BLAST report with a hit.
while( ! $hit ) {
    $result  = $searchio->next_result;
    die "No BLAST hits found in input file. Exiting.\n" if (! $result); 
    $hit =  $result->next_hit;
}

# assume that we parse an alignment on AminoAcid level of (translated) sequences
  # the only exception for blast would be BLASTN
my $attributes = [] ;
push @$attributes, "aa" if (not $hit->algorithm eq "BLASTN");

#write column headers to the out stream
  
handleHeaders(\*OUT, $nodes->item(0), $attributes, $opts{v});

### before entering main loop fo a rewind!   
$result->rewind;    
#moved fasta-file export of db sequences with at least one hit
#into generateFastaFromIDList.pl (CURRENTLY BROKEN)
#hauptschleife fuer jedes Resultat

while( $result  ) {
	print "result ". $result->query_name . "\n" if $DEBUG;
	my $resultfields = join "\t" ,@{genValueList($nodes->item(0),
												  'result',$result,$attributes)};
    my $i = 0;
    my $tooMuchHits = 0;
	#schleife fuer jeden hit des resultats
	my $hit = $result->next_hit; #internal preloading 
	while( $hit and not $tooMuchHits )  {
		print "   hit ". $hit->name . "\n" if $DEBUG;
	
		if ($maxHitsToReport > 0 && $i >= $maxHitsToReport){
			$tooMuchHits = 1;
                        next;
			# skip all further results if -s Parameter was set or
			# $maxHitsToReport hits have allready been reported
		}
		$i++;
		
		my $hitfields = join "\t" ,
						@{genValueList($nodes->item(0),'hit',$hit,$attributes)};
					
		if($opts{v}){
			# verbose mode one line per HSP
			while( my $hsp = $hit->next_hsp ) {
				# schleife fuer jeden HSP des Hits 
				my $hspfields = join "\t"
						,@{genValueList($nodes->item(0),'hsp',$hsp,$attributes)};
				print OUT $resultfields . "\t" . $hitfields . "\t" . $hspfields . "\n";
			}
		} else {
			# non verbose mode:  one line per hit
			my $hspSum = hspSummary->new(
									query_accession => $result->query_accession);  
			while( my $hsp = $hit->next_hsp ) {
				$hspSum->addHsp($hsp);
			}
			#all hsps have been processed - now generate summary
            my $valLi = genValueList($nodes->item(0),'hspSummary',$hspSum,$attributes);
            foreach my $v (@$valLi) {
                if (not defined($v)){
                    dieOup "ERROR:undefined value for hit $hit->accession exiting\n";  
                }                      
            }
			my $hspSumfields = join "\t" , @$valLi;
                        print OUT $resultfields . "\t" . $hitfields . "\t" . $hspSumfields . "\n";
                }
		$hit = $result->next_hit;
		} # while ( $hit )
	$result = $searchio->next_result;
} # while result
close(OUT);
##unlink $blastreportClean; # delete $blastreportClean cleaned from unsuccesful queries

oup(`date`. " $0 " );
oup("successfully parsed $brOrig\n");
###protocolling 2
close PROTCL;

sub oup($){
   my ($s) = @_;
   print $s;
   print PROTCL $s;    
}

sub dieOup($){
   my ($s) = @_;
   print PROTCL $s;
   close PROTCL;
   die $s;
}
