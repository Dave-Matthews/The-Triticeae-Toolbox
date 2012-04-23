package hspSummarySubitem;
use strict;

require Exporter;
our @ISA = qw(Exporter);
our @EXPORT_OK = qw();
sub new {
    my $type = shift;
    my %params = @_;
    my $self = {};
    $self->{start} = undef;
    $self->{end} = undef;
    $self->{frame} = undef;
    $self->{strand} = undef;
    $self->{strandChanges} = 0;
    $self->{frameChanges} = 0;
    #$self->{length} = undef;
    $self->{starts} = [];
    $self->{ends} = [];
    $self->{summarized} = 0;
    return bless $self, $type;
    }

    sub start {
        my $self = shift;
        $self->generateSummary if(! $self->{summarized});
        return $self->{start};
    }
    sub end {
        my $self = shift;
        $self->generateSummary if(! $self->{summarized});
        return $self->{end};
    }
    sub frame {
        my $self = shift;
        $self->generateSummary if(! $self->{summarized});
        return $self->{frame};
    }
    sub strand {
        my $self = shift;
        $self->generateSummary if(! $self->{summarized});
        return $self->{strand};
    }
    sub frameChanges {
        my $self = shift;
        $self->generateSummary if(! $self->{summarized});
        return $self->{frameChanges};
    }
    sub strandChanges {
        my $self = shift;
        $self->generateSummary if(! $self->{summarized});
        return $self->{strandChanges};
    }
    
    sub generateSummary {
        my $self =  shift;
        $self->{summarized} = 1;
        if (! $self->{strandChanges}){
            if ($self->{strand} >= 0){
                $self->plusRes();
            } else {
                $self->minusRes();
            }
        }
        else {
            #as the strand changes there is no senseful value for start/end
            $self->{start} = 0;
            $self->{end} = 0;
            $self->{frame} = 0;
            $self->{strand} = 0;    
        }
    }
    
    sub validate {
        my $self =  shift;
        
        #if the first start element is smaller than the first end element than we have a negative frame match
        #dieOup( "unexpected hit Start > than Hit End\n") if($hitStarts[0] > $hitEnds[0]);
        #dieOup( "unexpected query Start > than query End\n") if($queryStarts[0] > $queryEnds[0]);
    }
    sub plusRes {
        #normal plus -- look for smallest StartIndex and biggest EndIndex
        my $self =  shift;
        $self->doSort(sub {$a<=>$b}, sub {$b<=>$a}) ;
    }
    sub minusRes {
        #minus -- look for biggest StartIndex and smallest EndIndex
        my $self =  shift;
        $self->doSort(sub {$b<=>$a}, sub {$a<=>$b}) ;
    }
    sub doSort {
        my $self =  shift;
        my ($opStart, $opEnd) = @_ ;
       
        my @sortedStarts = sort $opStart @{$self->{starts}};
        my @sortedEnds = sort $opEnd @{$self->{ends}};
        
        $self->{start} = $sortedStarts[0];
        $self->{end} = $sortedEnds[0];       
    }

1;