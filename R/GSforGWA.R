# These are the two packages I want.  The multicore package makes crossvalidation run faster
library(rrBLUP)
library(multicore) # Installing multicore will make replicated crossvalidations go faster
nCores <- multicore:::detectCores()
if (nCores > 12) {
  nCores = 12
} 
options(cores=nCores)

# The data files and configuration setup are read in dymanically from files created in /tmp/tht
#snpData <- read.table("/tmp/tht/THTdownload_snp_t_MGLI.txt", header=TRUE, stringsAsFactors=FALSE, sep="\t", row.names=1)
#phenoData <- read.table("/tmp/tht/THTdownload_traits_MGLI.txt", header=TRUE, na.strings="-999", stringsAsFactors=FALSE, sep="\t", row.names=NULL)
#hmpData <- read.table("/tmp/tht/THTdownload_hmp_MGLI.txt", header=TRUE, stringsAsFactors=FALSE, sep="\t", check.names = FALSE)

# Read and parse snp file
mrkData <- hmpData[,-2]

# Read and parse traits file
experData <- as.matrix(phenoData[,3])
pheno <- as.matrix(phenoData[,2])
rowNames <- as.matrix(phenoData[,1])
unqExper <- length(unique(experData))
if (unqExper > 1) {
  pheno <- data.frame(gid=rowNames, y=pheno, trial=experData, stringsAsFactors = FALSE)
} else {
   pheno <- data.frame(gid=rowNames, y=pheno, stringsAsFactors = FALSE)
}

rowNames <- rownames(hmpData)
numMarkers <- ncol(mrkData)

rowNames <- as.matrix(rownames(hmpData))
geno <- data.frame(gid=rowNames, chrom=hmpData[,2], pos=hmpData[,3], mrkData, check.names = FALSE)

# Are there > 1 trials?
moreThan1Trial <- length(unique(phenoData$trial)) > 1
if (moreThan1Trial) {
  results <- GWAS(pheno, mrkData, n.core=nCores, fixed="trial")
} else {
  results <- GWAS(pheno, mrkData, n.core=nCores, P3D=FALSE)
}
