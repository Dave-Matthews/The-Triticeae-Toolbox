##########################################################################
# Script to perform genomic prediction and plot observed agains predicted
##########################################################################
source(common_code)

hasPheno <- !is.na(phenoData$pheno)
trainLines <- phenoData$gid[hasPheno]
trainTrials <- unique(phenoData$trial[phenoData$gid %in% trainLines])

if (yesPredPheno) {
        predLines <- phenoData$gid
        predTrials <- unique(phenoData$trial[phenoData$gid %in% predLines])
        phenoData$phenoTrain <- phenoData$pheno
        moreThan1Trial <- sum(!(unique(phenoData$trial) %in% predTrials)) > 1
        if (moreThan1Trial){
                addBlups <- kin.blup(phenoData, "gid", "phenoTrain", K=mrkRelMat, fixed="trial")
        } else{
                addBlups <- kin.blup(phenoData, "gid", "phenoTrain", K=mrkRelMat)
        }

        whichSet <- ifelse(names(addBlups$g) %in% predLines, "Pred", "Train")
        result <- data.frame(prediction=addBlups$g, set=whichSet, stringsAsFactors=FALSE)
        write.csv(result, file=fileout, quote=FALSE)

# Make a plot of predictions against observations
# First, get correlations between predicted and observed
        allCor <- NULL
        allMeans <- list()
        for (trial in 1:length(trainTrials)){
		trialLines <- unique(phenoData$gid[phenoData$trial == trainTrials[trial]])
		# In case there are > 1 pheno for a line in a trial, which shouldn't happen (yet)
		meanPheno <- tapply(phenoData$pheno[phenoData$trial == trainTrials[trial]], as.factor(phenoData$gid[phenoData$trial == trainTrials[trial]]), mean, na.rm=TRUE)[trialLines]
		allCor <- c(allCor, cor(addBlups$g[trialLines], meanPheno, use="complete.obs"))
		allMeans <- c(allMeans, list(meanPheno))
	}
        if (moreThan1Trial) {
	  mainTitle <- paste("Accuracy (StdDev) = ", round(mean(allCor, na.rm=TRUE), 2), " (", round(sd(allCor,na.rm=TRUE), 2), ")", sep="")
        } else {
          mainTitle <- paste("Accuracy = ", round(mean(allCor, na.rm=TRUE), 2), sep="")
        }
# Second, plot by trial
        for (trial in 1:length(trainTrials)){
		trialLines <- unique(phenoData$gid[phenoData$trial == trainTrials[trial]])
		# Get correlations between predicted
		if (trial == 1){
			plot(addBlups$g[trialLines], allMeans[[trial]], pch=16, xlim=range(addBlups$g), ylim=range(unlist(allMeans),na.rm=TRUE), main=mainTitle, xlab="Prediction", ylab="Observed Phenotype")
		} else{
			points(addBlups$g[trialLines], allMeans[[trial]], pch=16, col=trial)
		}
	}
} else {
# No prediction set (or prediction set all NA)
# If no prediction set, the plot will be on the basis of cross validation
##########################################################################################
# Function to run cross validation on a training population 
#	with a specified number of folds.
# The user can repeat the cross validation for a specified number of times 
#	(folds are sampled independently each time) to determine variability
#	across different fold samples
# data: data frame with phenotypes, fixed effects, and covariates
# geno: name of the column with genotype ids
# pheno: name of the column with the phenotype: 
#	individuals without phenotypes will be dropped
# fixed: name(s) of columns with [categorical] fixed effects
# covariate: names(s) of columns with [continuous] covariate effects
# K: relationship matrix with row and column names that are genotype ids
# nFolds: split the training population into this number of folds.
# nRepeats: how many times to repeat the cross validation
##########################################################################################
	runCrossValidation <- function(data, geno, pheno, K, fixed=NULL, covariate=NULL, nFolds=5, nRepeats=2){
		# Retain only individuals who have phenotypes
		hasPheno <- !is.na(data[, pheno])
		data <- data[hasPheno,]
		allInd <- unique(data[, geno])
		K <- K[allInd, allInd] # If individuals removed, drop out of K too
		nInd <- length(allInd)
		# Matrix to hold cross-validated predictions
		crossValPred <- matrix(NA, nInd, nRepeats)
		rownames(crossValPred) <- allInd
		# Loop to do nRepeat independent cross validations
		for (rep in 1:nRepeats){
			folds <- sample(rep(1:nFolds, length.out=nInd))
			for (fold in 1:nFolds){
				# Identify the individuals associated with this fold
				indInFold <- allInd[folds == fold]
				# Create a phenotype and set those individuals to missing
				data$crossValPheno <- data[, pheno]
				data$crossValPheno[data[,geno] %in% indInFold] <- NA
				# Run genomic prediction removing indInFold from training
				addBlupOut <- kin.blup(data, geno, "crossValPheno", K=K, fixed=fixed, covariate=covariate)
				crossValPred[indInFold, rep] <- addBlupOut$g[indInFold]
				print(c(rep, fold))
			}
		}
		return(crossValPred)
	}#END runCrossVal

# Are there >1 trials?
	moreThan1Trial <- length(trainTrials) > 1
	if (moreThan1Trial){
		# Do a non-crossvalidated prediction for the results
		addBlups <- kin.blup(phenoData, "gid", "pheno", K=mrkRelMat, fixed="trial")
		# Run cross validation to get a true sense of the accuracy
		cvPred <- runCrossValidation(phenoData, "gid", "pheno", mrkRelMat, fixed="trial")
	} else{
		addBlups <- kin.blup(phenoData, "gid", "pheno", K=mrkRelMat)
		cvPred <- runCrossValidation(phenoData, "gid", "pheno", mrkRelMat)
	}
	result <- data.frame(prediction=addBlups$g, set="Train", stringsAsFactors=FALSE)
	write.csv(result, file=fileout, quote=FALSE)
	meanPred <- rowMeans(cvPred)

# Make a plot of predictions against observations
# First, get correlations between predicted and observed
	allCor <- NULL
	allMeans <- list()
	for (trial in 1:length(trainTrials)){
		trialLines <- unique(phenoData$gid[phenoData$trial == trainTrials[trial]])
		# In case there are > 1 pheno for a line in a trial, which shouldn't happen (yet)
		meanPheno <- tapply(phenoData$pheno[phenoData$trial == trainTrials[trial]], as.factor(phenoData$gid[phenoData$trial == trainTrials[trial]]), mean, na.rm=TRUE)[trialLines]
		allCor <- c(allCor, cor(meanPred[trialLines], meanPheno))
		allMeans <- c(allMeans, list(meanPheno))
	}
        if (moreThan1Trial) {
	  mainTitle <- paste("Accuracy (StdDev) = ", round(mean(allCor), 2), " (", round(sd(allCor), 2), ")", sep="")
        } else{
          mainTitle <- paste("Accuracy = ", round(mean(allCor), 2), sep="")
        }
# Second, plot by trial
	for (trial in 1:length(trainTrials)){
		trialLines <- unique(phenoData$gid[phenoData$trial == trainTrials[trial]])
		# Get correlations between predicted
		if (trial == 1){
			plot(meanPred[trialLines], allMeans[[trial]], pch=16, xlim=range(meanPred), ylim=range(unlist(allMeans),na.rm=TRUE), main=mainTitle, xlab="Cross-validated Prediction", ylab="Observed Phenotype")
		} else{
			points(meanPred[trialLines], allMeans[[trial]], pch=16, col=trial)
		}
	}
}#END no prediction set
dev.off()

