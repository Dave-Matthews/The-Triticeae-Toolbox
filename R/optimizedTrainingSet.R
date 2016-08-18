########################################################################################
###Optimized Training Sets selected via the 'STPGA' R package###
########################################################################################

#setwd("/tmp/tht")
library(rrBLUP)
library(STPGA)

###This was just used to time the run time of the script, can be removed in production
strt<-Sys.time()

#snpData <- read.table("genotype.hmp.txt", sep="\t", header=TRUE, stringsAsFactors=FALSE, na.strings="NA", row.names=1, check.names=FALSE)

###Removing first 3 columns of dataset ("alleles", "chrom", "pos"). This depends on the format from T3
snpData <- snpData[, -(1:3), drop=FALSE]

mrkData_all <- as.matrix(snpData)

###Create Imputed Genotype Data
mrkRelMat <- A.mat(t(mrkData_all), return.imputed=TRUE)
mrkData.imputed <- mrkRelMat$imputed

####Create the Covariance Matrix
Kmat <- cov((mrkData.imputed))
Kmat <- Kmat/mean(diag(Kmat))

LambdaTrait <- 1/nrow(mrkData.imputed)

###This gives a better result (improves about 15%) if tol is set to 0.01, but the calculation takes too long.
###tol used in the selection of the number of Principal Components. Calculates a threshold = Stdev of first PC * tol. Any subsequent PCs with a stdev greater than this threshold are included
pca.eg <- prcomp(mrkData.imputed, scale=FALSE, tol=0.05)
PCAs <- Kmat%*%pca.eg$rotation

###Test set are the ones we want to predict. Candidates are ones from which training set can be selected.
####"test" is USER defined. The USER must define their test set (the ones they want to predict). Should be a vector of line names. I entered some as an example. This can be blank if they desire and the code would just be test<-data.frame(y=c()).
#test <- data.frame(y=c("MURI", "HABB", "TAUTE", "S718", "SPELMAR", "F6-1", "CAR1938", "VASC", "ND574", "STEWART", "KYLE", "ROSETA", "CALI", "BERBERN", "DUBBIE", "BRANT"))
candidates <- setdiff(rownames(Kmat), test$y)

####These should be USER DEFINED.  "notoselect" is the number of lines you want to select for your training set.  "npop" is the number of solutions at each iteration.  "niterations" is the number of desired iterations.  More "npop" and "niterations" can be better, but the computation time will be extremely high.  There is also is a certain point where it plateaus and significantly better solutions are not achieved.  Through my testing I suggest "npop" equals 300 and "niterations" equals 150.
###For quick testing of the script you can make npop=30 and niterations=15
###For live use npop > 100, niteration > 100
#notoselect <-25 
npop <- 100
niterations <- 1000

###Distinguishing between examples where a test set is defined or where one is not specified. (Two different functions in STPGA package)
out_flag <- ifelse(length(unique(test$y)) > 0, TRUE, FALSE)

# library(doParallel)
# library(foreach)
library(parallel)

###"Register" the number of cores we want to use.
# cl <- makeCluster(12)
# registerDoParallel(cl)

###Going to need to insert a command here to email the user when this completes?  Can typically take from 5mins to 4hours.
	
if (out_flag == TRUE){

###I made the "npop" and "niterations" parameters user defined.  Perhaps for simplicity we should not make this definable by the user?
  TrainList <- GenAlgForSubsetSelection(P=PCAs,Candidates=candidates,Test=test$y, ntoselect=notoselect, npop=100, nelite=5, mutprob=0.8, niterations=niterations, lambda=LambdaTrait, plotiters=FALSE, mc.cores=12,  errorstat=errorstat)
} else {
  TrainList <- GenAlgForSubsetSelectionNoTest(P=PCAs, ntoselect=notoselect, npop=100, nelite=5, mutprob=0.8, niterations=niterations, lambda=LambdaTrait, plotiters=FALSE, mc.cores=12, errorstat=errorstat)
}

FinalBestList <- TrainList[[1]]

###Plot PC
whichBest <- colnames(snpData) %in% FinalBestList
whichColor <- rep("red", times = length(snpData))
whichColor[whichBest] <- "blue"
pca = prcomp(Kmat, scale=T)
scores <- pca$x[,1:2]
plot(scores, col = whichColor, main = "PCA plot, Blue = selected lines")
dev.set(dev.next())

###Write text file of germplasm lines selected for inclusion in the optimized training set.  This training set can then be used with the existing Genomic Prediction toll on T3.
write.table(FinalBestList, "OptimizedTrainingList.txt", sep="\t", row.names=FALSE, quote=FALSE, col.names=FALSE)

if (exists("email")) {
  command <- paste("echo \"Optimize training set  analysis is done\n", result_url, "\" | mail -s \"Results from T3 \"", email)
  system(command)
}

##########################################################################
##########################################################################
