package hspSummary;
use strict;
use IPK::hspSummarySubitem;
require Exporter;
our @ISA = qw(Exporter);
our @EXPORT_OK = qw();
my $DEBUG=1;
sub new {
    my $type = shift;
    my %params = @_;
    my $self = {};
    $self->{query} = hspSummarySubitem->new();
    $self->{hit} = hspSummarySubitem->new();
    $self->{first} = 1;
    $self->{query_accession} = $params{query_accession};
    
    return bless $self, $type;
    }
sub hit {
    my $self = shift;
    return $self->{hit};
    }
sub query {
    my $self = shift;
    return $self->{query};
    }

sub addHsp {
   
    my $self = shift;
    my ($hsp) = @_;
    
    my $DEBUG = 0;
    if($self->{first}){
        # actions only for first HSP
        $self->{query}->{strand}= $hsp->query->strand;
        $self->{hit}->{strand}= $hsp->hit->strand;
        $self->{query}->{frame}= $hsp->query->frame;
        $self->{hit}->{frame}= $hsp->hit->frame;
        $self->{first} = 0;
        
    } else {
        # actions only for additional HSP
        if( $self->{query}->{strand} != $hsp->query->strand){
            print "unexpected change query strand in ",$self->{query_accession},"\n" if($DEBUG);
            $self->{query}->{strandChanges} = 1;
        }
        if( $self->{query}->{frame} != $hsp->query->frame){
            print "unexpected change query frame in ",$self->{query_accession},"\n" if($DEBUG);
            $self->{query}->{frameChanges} = 1;
        }
        
        if(  $self->{hit}->{strand} != $hsp->hit->strand){
            print "change hit strand in ",$self->{query_accession},"\n" if($DEBUG);
            $self->{hit}->{strandChanges} = 1
        }
            
    }
    # actions on any HSP
    # replace this with left_hsp_boundrary adn right hsp_boundrary
    
    push @{$self->{hit}->{starts}} , $hsp->start('hit');# collect all $hsp->start('hit')
    push @{$self->{hit}->{ends}} , $hsp->end('hit');# collect all $hsp->end('hit')
    push @{$self->{query}->{starts}}  , $hsp->start('query');# collect all $hsp->start('query')
    push @{$self->{query}->{ends}} , $hsp->end('query');# collect all $hsp->end('query')
                 
}




1;
