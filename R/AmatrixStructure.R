library(rrBLUP)
library(multicore) # Installing multicore will make replicated crossvalidations go faster
nCores <- multicore:::detectCores()
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

