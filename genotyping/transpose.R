# transpose a tsv file
# fileIN - input
# fileOUT - output

args <- commandArgs(TRUE)
if (length(args)==0) {
    stop("At least two arguements must be supplied");
} else {
    fileIN <- args[1]
    fileOUT <- args[2]
}

snp <- read.table(fileIN, header=TRUE , check.names=FALSE)
snpT <- t(snp)

write.table(snpT, file=fileOUT, quote=FALSE, sep="\t")
