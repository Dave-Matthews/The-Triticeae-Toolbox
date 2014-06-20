library(rrBLUP)
library(parallel, warn.conflicts = FALSE)
nCores <- detectCores()
if (nCores > 12) {
  nCores = 12
}
options(cores=nCores)

# The data files and configuration setup are read in dymanically from files created in /tmp/tht

# Read and parse snp file
#snpData <- read.table("$dir$filename1", header=TRUE, stringsAsFactors=FALSE, sep="\t", row.names=1)
#snpData_t <- read.table("$dir$filename8", header=TRUE, stringsAsFactors=FALSE, sep="\t", row.names=1)
mrkData <- as.matrix(snpData_t)
if (exists("snpData_p")) {
  mrkData_p <- as.matrix(snpData_p)
  mrkData_all <- rbind(mrkData, mrkData_p)
} else {
  mrkData_all <- mrkData
}
rowNamesMarker <- rownames(mrkData)
mrkRelMat <- A.mat(mrkData_all, return.imputed=TRUE)
if (class(mrkRelMat) == "list"){ # Do this if you have missing marker data
        mrkData.imputed <- mrkRelMat$imputed # We will use the imputed markers later on
        mrkRelMat <- mrkRelMat$A
}

#Plot of first two axes of the PCA
#can not use different colors for trials because the plots they will be hidden
relMatPCA <- prcomp(mrkRelMat)
uniqueTrials <- unique(phenoData$trial) # Third column of phenoData has trial names
mainTitle <- "Principal Components Analysis"
for (trial in 1:length(uniqueTrials)){
	trialLines <- phenoData$gid[phenoData$trial == uniqueTrials[trial]]
	if (trial == 1){
		plot(relMatPCA$x[trialLines, 1:2], pch=16, xlim=range(relMatPCA$x[, 1]), ylim=range(relMatPCA$x[, 2]), main=mainTitle)
	} else{
		points(relMatPCA$x[trialLines, 1:2], pch=16)
	}
}
dev.set(dev.next())
