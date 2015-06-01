# These are the two packages I want.  The multicore package makes crossvalidation run faster
library(rrBLUP)
library(parallel)
nCores <- detectCores()
if (nCores > 12) {
  nCores = 12
} 
options(cores=nCores)

# The data files and configuration setup are read in dymanically from files created in /tmp/tht
#snpData <- read.table("/tmp/tht/THTdownload_snp_t_MGLI.txt", header=TRUE, stringsAsFactors=FALSE, sep="\t", row.names=1)
#phenoData <- read.table("/tmp/tht/THTdownload_traits_MGLI.txt", header=TRUE, na.strings="-999", stringsAsFactors=FALSE, sep="\t", row.names=NULL)
#hmpData <- read.table("/tmp/tht/THTdownload_hmp_MGLI.txt", header=TRUE, stringsAsFactors=FALSE, sep="\t", check.names = FALSE)

# Read and parse snp file
mrkData <- hmpData[,-(1:4)]
mrkRelMat <- A.mat(t(mrkData), return.imputed=TRUE)
if (class(mrkRelMat) == "list"){ # Do this if you have missing marker data
        mrkData.imputed <- mrkRelMat$imputed # We will use the imputed markers later on
        mrkRelMat <- mrkRelMat$A
}
write.csv(mrkRelMat, file=fileK, quote=FALSE)

eig.result <- eigen(mrkRelMat)
lambda <- eig.result$values
mainTitle <- paste("Principal Component analysis of ", phenolabel)
plot(lambda/sum(lambda), ylab="Fraction Explained", main=mainTitle)
dev.set(dev.next())

# Read and parse traits file
experData <- as.matrix(phenoData$trial)
pheno <- as.matrix(phenoData$pheno)
rowNames <- as.matrix(phenoData$gid)
unqExper <- length(unique(experData))
if (unqExper > 1) {
  pheno <- data.frame(gid=rowNames, y=pheno, trial=experData, stringsAsFactors = FALSE)
} else {
  pheno <- data.frame(gid=rowNames, y=pheno, stringsAsFactors = FALSE)
}

rowNames <- rownames(hmpData)
numMarkers <- ncol(mrkData)

#rowNames <- as.matrix(rownames(hmpData))
#geno <- data.frame(gid=rowNames, chrom=hmpData[,3], pos=hmpData[,4], mrkData, check.names = FALSE)

# Are there > 1 trials?
mrkData <- hmpData[,-2]
moreThan1Trial <- length(unique(phenoData$trial)) > 1
if (moreThan1Trial) {
    results <- GWAS(pheno, mrkData, K=mrkRelMat, n.core=nCores, fixed="trial", n.PC=model_opt, P3D=p3d)
} else {
    results <- GWAS(pheno, mrkData, K=mrkRelMat, n.core=nCores, fixed=NULL, n.PC=model_opt, P3D=p3d)
}
resultSort <- results[order(results["y"], decreasing=TRUE),]
write.csv(resultSort, file=fileout, quote=FALSE)
if (exists("email")) {
  command <- paste("echo \"GWAS analysis is done\n", result_url, "\" | mail -s \"Results from T3 GWAS\"", email)
  system(command)
}
