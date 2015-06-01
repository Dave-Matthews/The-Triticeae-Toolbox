# These are the two packages I want.  The multicore package makes crossvalidation run faster
library(rrBLUP)
library(parallel)
# if not R 3.0 or above, multicore library is needed
if (! exists('mclapply')) {
    library(multicore) # Installing multicore will make replicated crossvalidations go faster
}
nCores <- 3
options(cores=nCores)

# The data files and configuration setup are read in dymanically from files created in /tmp/tht

# Read and parse snp file
#snpData <- read.table("$dir$filename1", header=TRUE, stringsAsFactors=FALSE, sep="\t", row.names=1)\n
mrkData <- as.matrix(snpData)

# Read and parse traits file
#phenoData <- as.matrix(read.table("$dir$filename2", header=TRUE, na.strings="-999", stringsAsFactors=FALSE, sep="\t", row.names=1))
#phenoData <- rowSums(phenoData, na.rm=TRUE)
#ignore the experiment column for now
experData <- phenoData[,2]
phenoData <- phenoData[,1]
phenoylab <- colnames(phenoData)[1]
#set 50 percent to missing and use this for prediction
#predSet <- (length(phenoData))/2
#phenoData[predSet:length(phenoData)] <- NA

# tell the code whether or not to do cross validation
#doCrossValidation <- 1

# It might make more sense for you to set files up as in what's below
# in the if (FALSE) brackets
# This code is not run here
if (FALSE){
# setwd("/tmp/tht/")
suffix <- ""
mrkDataFile <- paste("mrkData.csv", suffix, sep="")
phenoDataFile <- paste("phenoData.csv", suffix, sep="")

warningFile <- paste("warningLines.csv", suffix, sep="")

mrkData <- read.csv(mrkDataFile)
phenoData <- read.csv(phenoDataFile)[,1] # the [,1] turns this into a vector
}

############### Function to discover if a large set of markers is missing from a set of lines
# May not be necessary if we keep tabs of what lines were genotyped with what platforms
# Go line by line in order of most missing data points
findBigRectangle <- function(isnaMat){
	nTrue <- apply(isnaMat, 1, sum)
	ordNTrue <- order(nTrue, decreasing=TRUE)
	newColTrue <- rep(TRUE, ncol(isnaMat))
	newRecArea <- 0
	idx <- 0
	newRecBigger <- TRUE
	while(newRecBigger){
		idx <- idx + 1
		oldColTrue <- newColTrue
		oldRecArea <- newRecArea
		newColTrue <- oldColTrue & isnaMat[ordNTrue[idx],]
		newRecArea <- sum(newColTrue) * idx
		newRecBigger <- newRecArea >= oldRecArea
	}
	return(list(sort(ordNTrue[1:(idx - 1)]), which(oldColTrue)))
}

############### Function to run cross validation with a given number of folds
# and a given number of repeats of those folds
# Uses multicore to make all these run in parallel  
# pheno is a vector. No missing data allowed.
# geno is a matrix.  Rows in same order as the pheno vector
runCrossVal <- function(pheno, geno, nFolds, nTimes){
	# Function to run CV on one fold in order to use multicore:lapply
	# fold is a vector that says which of the lines are in the validation fold
	runOneFold <- function(fold){
		train <- (1:length(pheno))[-fold]
		phenoTrain <- pheno[train]
		genoTrain <- geno[train,]
		genoPred <- geno[fold,]
		return(kinship.BLUP(phenoTrain, genoTrain, genoPred)$g.pred)
	}
	nObs <- length(pheno)
	# set up the list of folds
	foldList <- list()
	for (time in 1:nTimes){
		folds <- sample(rep(1:nFolds, length.out=nObs))
		for (fold in 1:nFolds){
			foldList <- c(foldList, list(which(folds == fold)))
		}
	}
	predOut <- mclapply(foldList, runOneFold)
	saveAcc <- 0
	savePred <- numeric(nObs)
	processFolds <- function(foldOutput, fold){
		saveAcc <<- saveAcc + cor(foldOutput, pheno[fold])
		savePred[fold] <<- savePred[fold] + foldOutput
	}
	dummy <- mapply(processFolds, predOut, foldList)
	return(list(meanAcc=saveAcc / nTimes / nFolds, meanPred=savePred / nTimes))
}

# I don't think any of the quality control issues are triggered by the example data
# I have tried so far, so there may be a bug in there...
# Ah: there are actually three lines missing a bunch of markers so at least the "chunk" part works
############### Cutoffs based on missing marker scores
# markers allowed to have up to 10% missing data
# lines allowed to have up to 80% missing data
# NOTE: once we start using GBS, that has lots of missing data, we will need to review these cutoffs
mrkNACutoff <- 0.1
lineNACutoff <- 0.8
fracMrkNA <- apply(mrkData, 2, function(vec) return(sum(is.na(vec)))) / nrow(mrkData)
mrkData <- mrkData[, fracMrkNA <= mrkNACutoff]
linesFracNA <- apply(mrkData, 1, function(vec) return(sum(is.na(vec)))) / ncol(mrkData)
if (any(linesFracNA > lineNACutoff)){
	linesTooManyNA <- which(linesFracNA > lineNACutoff)
	linesFracNA <- linesFracNA[linesTooManyNA]
	# You can use write.table to save linesFracNA
        write("<br>lines allowed to have up to 80% missing data",fileerr)
        write(names(linesFracNA),fileerr, append = TRUE)
        write("had too many missing markers to be clustered\n",fileerr, append = TRUE)
	print(paste("Lines [fraction missing]", paste(paste(names(linesFracNA), " [", round(linesFracNA, 3), "]", sep=""), collapse=" "), "had too many missing markers to be clustered"))
	mrkData <- mrkData[-linesTooManyNA,]
}

############### Warning if a big chunk is missing
# Look for whether a chunk of lines is missing for a large set of markers
warningLines <- NULL
chunkMissing <- findBigRectangle(is.na(mrkData))
mrkInChunkCutoff <- 0.25 # 0.25 Arbitrary cutoff
#write.table(chunkMissing[[1]], file="linesMissingChunk.txt")
#write.table(chunkMissing[[2]], file="markersMissingChunk.txt")
if (length(chunkMissing[[2]]) > mrkInChunkCutoff * ncol(mrkData)){
	# Need to warn about these lines because predictions for them may have more
	# to do with the missing data than with their present data.
	warningLines <- rownames(mrkData)[chunkMissing[[1]]]
	#write.table(warningLines, file="warningFile")
}
##########################################################################################
############### Done with checks. Start analysis here
# Make sure no missing marker data.  There are better ways to do this, which I will get to
replNAbyMean <- function(vec){
	vec[is.na(vec)] <- mean(vec, na.rm=TRUE)
	return(vec)
}
mrkData <- apply(mrkData, 2, replNAbyMean)

# Separate lines that have phenotypes from lines that do not
hasPheno <- !is.na(phenoData)
pheno <- phenoData[hasPheno]
exper <- experData[hasPheno]
phenoNo <- phenoData[!hasPheno]
mrkTrain <- mrkData[hasPheno,]
mrkPred <- mrkData[!hasPheno,]

# Actually run the genomic prediction
rrOut <- kinship.BLUP(pheno, mrkTrain, mrkPred)
#rrOut <- kinship.BLUP(pheno, mrkTrain, mrkPred, exper)
predForTrain <- rrOut$g.train # Predictions for lines that had phenotypes
predForPred <- rrOut$g.pred # Predictions for lines that had NO phenotypes.  These are the important ones...

if (doCrossValidation){
	foldsForCrossVal <- 5 # Five fold cross validation is pretty standard
	repeatsForCrossVal <- 5 # Arbitrary.  I wouldn't do > 5.  If data big, wouldn't do > 1.
	crossValOut <- runCrossVal(pheno, mrkTrain, foldsForCrossVal, repeatsForCrossVal)
	accuracy <- crossValOut$meanAcc
        mainlabel <- paste("accuracy = ",accuracy);
	plot(crossValOut$meanPred, pheno, ylab="pheno training set", main=mainlabel) # should give a nice visual of the accuracy
	dev.off() # You might need this statement to get the plot saved properly
        r1 <- cbind(c(predForTrain))
        r2 <- cbind(c(predForPred))
        result <- rbind(r1,r2)
        write.csv(result,file=fileout)
} else {
  plot(predForPred)
}
