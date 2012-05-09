#!/usr/bin/perl -w

use strict;
use warnings;

use GD;

use DBI;
use IO::File;
use POSIX;

my $dataPath = "/tmp/tht/blast";
my $jobid = $ARGV[0];
my $imagemap = new IO::File( "$dataPath/$jobid.imap", 'w' );
print $imagemap "\n<map name=\"" . $jobid . "\">\n";
my $image = new IO::File( "$dataPath/$jobid.png", 'w' );

my $imgh;
my $maximgh   = 600;
# my $imgw      = 800;
my $imgw      = 600;
my $hitxstart = 40;
my $hitxend   = $imgw - 10;
my $trackgap  = 10;
my $maxhits   = 50;
my $querylength;
my $queryname;
my $im;

my $black;
my $red;
my $blue;
my $white;
my $green;
my $pink;
# my $dbh0 =
#   DBI->connect( "DBI:CSV:f_dir=data;csv_eol=\n;"
#       . "csv_sep_char=\t;"
#       . "csv_escape_char=" );
my $dbh0 =
  DBI->connect( "DBI:CSV:f_dir=$dataPath;csv_eol=\n;"
      . "csv_sep_char=\t;"
      . "csv_escape_char=" );
my ($query0) = "SELECT distinct hit_name FROM pbr_$jobid.csv";
my ($sth0)   = $dbh0->prepare($query0);
$sth0->execute();
my $hitcount=$sth0->rows; 
$sth0->finish();
$dbh0->disconnect();

my $dbh =
  DBI->connect( "DBI:CSV:f_dir=$dataPath;csv_eol=\n;"
      . "csv_sep_char=\t;"
      . "csv_escape_char=" );
my ($query) = "SELECT query_length,query_name FROM pbr_$jobid.csv";
my ($sth)   = $dbh->prepare($query);
$sth->execute();

my $rowcount = $sth->rows;

if ( $hitcount > $maxhits ) {
    $imgh = 100 + $trackgap * $maxhits + 10;
    $im = new GD::Image( $imgw, $imgh );
    $white = $im->colorAllocate( 255, 255, 255 );
    $red   = $im->colorAllocate( 255, 0,   0 );
    $im->transparent($white);
    $im->interlaced('true');
    $im->filledRectangle( 0, 0, $imgw, $imgh, $white );
    $im->string( gdSmallFont, 0, 0, "Showing only the first $maxhits hits out of $hitcount.", $red );
}
else {
    $imgh = 100 + $trackgap * $hitcount + 10;
    $im = new GD::Image( $imgw, $imgh );
    $white = $im->colorAllocate( 255, 255, 255 );
    $black = $im->colorAllocate( 0,   0,   0 );
    $im->transparent($white);
    $im->interlaced('true');
    $im->filledRectangle( 0, 0, $imgw, $imgh, $white );
  $im->string( gdSmallFont, 0, 0, "Distribution of ".$hitcount." Blast Hits on the Query Sequence", $black);
}
    $black = $im->colorAllocate( 0,   0,   0 );
    $red   = $im->colorAllocate( 255, 0,   0 );
    $blue  = $im->colorAllocate( 0,   0,   255 );
    $green = $im->colorAllocate( 0,   255, 0 );
    $pink  = $im->colorAllocate( 255, 0,   255 );


    $im->string( gdSmallFont, 100, 10, "Color keys for alignment scores",
        $black );

    # scorecolors
    $im->filledRectangle( 0, 30, $imgw / 5, 40, $black );
    $im->string( gdSmallFont, 10 + $imgw / 5 / 2, 28, "<40", $white );

    $im->filledRectangle( $imgw / 5, 30, 2 * $imgw / 5, 40, $blue );
    $im->string( gdSmallFont, 2 * $imgw / 5 / 2 + $imgw / 5 / 2,
        28, "40-50", $white );

    $im->filledRectangle( 2 * $imgw / 5, 30, 3 * $imgw / 5, 40, $green );
    $im->string( gdSmallFont, 4 * $imgw / 5 / 2 + $imgw / 5 / 2,
        28, "50-80", $black );

    $im->filledRectangle( 3 * $imgw / 5, 30, 4 * $imgw / 5, 40, $pink );
    $im->string( gdSmallFont, 6 * $imgw / 5 / 2 + $imgw / 5 / 2,
        28, "80-200", $black );

    $im->filledRectangle( 4 * $imgw / 5, 30, 5 * $imgw / 5, 40, $red );
    $im->string( gdSmallFont, 8 * $imgw / 5 / 2 + $imgw / 5 / 2,
        28, ">=200", $black );

    # divider
    $im->line( 10, 50, $imgw - 10, 50, $black );

    while ( my $row = $sth->fetchrow_hashref ) {
        $querylength = $row->{'query_length'};
        $queryname   = $row->{'query_name'};
        last;
    }
    $sth->finish();
    $dbh->disconnect();
    if ( !defined $queryname ) {
        $queryname = 'NON';
    }

    $im->string( gdSmallFont, $hitxstart, 55, $queryname, $black );
    
    # ruler
    $im->filledRectangle( $hitxstart, 70, $hitxend, 80, $black );


    # ruler ticks
    for ( my $i = $hitxstart ; $i <= $hitxend ; $i = $i + 20 ) {
        $im->line( $i, 80, $i, 90, $black );
    }

    # ruler numbers
    my $rulerlen    = $hitxend - $hitxstart;
    my $queryfactor = $rulerlen / $querylength;
    my $numberticks = 10;
    if ( $querylength > 50 && $querylength < 100 ) {
        $numberticks = 25;
    }
    if ( $querylength > 100 && $querylength < 500 ) {
        $numberticks = 50;
    }
    if ( $querylength > 500 && $querylength < 1000 ) {
        $numberticks = 100;
    }
    if ( $querylength > 1000 && $querylength < 10000 ) {
        $numberticks = 1000;
    }

    for ( my $j = 0 ; $j < $querylength ; $j = $j + $numberticks ) {
        $im->string( gdSmallFont, $hitxstart + $j * $queryfactor,
            90, $j, $blue );
    }

    my $dbh1 =
      DBI->connect( "DBI:CSV:f_dir=$dataPath;csv_eol=\n;"
          . "csv_sep_char=\t;"
          . "csv_escape_char=" );

    my ($query1) =
"SELECT hsp_rank, hit_name,hsp_score, hsp_query_start, hsp_query_end FROM pbr_$jobid.csv";
    my ($sth1) = $dbh1->prepare($query1);
    $sth1->execute();
    my $j           = 0;
    my $hitnu       = 0;
    my $lasthitname = '';
    while ( my $row = $sth1->fetchrow_hashref) {
    if ($hitnu>$maxhits){
	last;
    }
        my $score           = $row->{'hsp_score'};
        my $hsp_query_start = $row->{'hsp_query_start'};
        my $hsp_query_end   = $row->{'hsp_query_end'};
        my $hsp_rank        = $row->{'hsp_rank'};
        my $hitname         = $row->{'hit_name'};

        if ( $lasthitname ne $hitname ) {
            $hitnu++;
            $lasthitname = $hitname;
        }
        my $xs = floor( $hitxstart + $hsp_query_start * $queryfactor );
        my $ys = floor( $hitnu * $trackgap + 110 );
        my $xe = floor( $hitxstart + $hsp_query_end * $queryfactor );
        my $ye = floor( $hitnu * $trackgap + 110 );
        $im->line( $xs, $ys, $xe, $ye, getscorecolor($score) );
        $ys = $ys - 2;
        $ye = $ye + 2;

        my $ankername = $hitnu . "_" . $hsp_rank;
        print $imagemap
"<area shape=\"rect\" coords=\"$xs,$ys,$xe,$ye\" href=\"$dataPath/$jobid.html#$ankername\" alt=\"$ankername\" title=\"$ankername\"/>\n";

    }
    $sth1->finish();
    $dbh1->disconnect();

print $imagemap "</map>\n";
binmode $image;

#print "Content-type: image/png\n\n";
print $image $im->png;

sub getscorecolor {
    my ($score) = @_;
    my $black = $im->colorAllocate( 0,   0,   0 );
    my $red   = $im->colorAllocate( 255, 0,   0 );
    my $blue  = $im->colorAllocate( 0,   0,   255 );
    my $green = $im->colorAllocate( 0,   255, 0 );
    my $pink  = $im->colorAllocate( 255, 0,   255 );
    if ( $score < 40 ) {
        return $black;
    }
    if ( $score < 50 ) {
        return $blue;
    }
    if ( $score < 80 ) {
        return $green;
    }
    if ( $score < 200 ) {
        return $pink;
    }
    if ( $score > 200 ) {
        return $red;
    }
    else {
        return $black;
    }
}

