# t3/.../R/Clust4D.R
# called by cluster4d.php, which prepends several commands in setupclust3d.txt

# from Jean-Luc Jannink 9feb11, ./VisualCluster.R.orig
# Clusters the input lines and marker alleles for the user to select cluster of interest.
# The marker data represents the full set of lines that a user has selected.

###################################################################
# For now, this will work for a single phenotype from a single trial.  
# We will discuss how to expand.
###################################################################

###################################################################
# I think we should allow the user in the web interface to determine
# the level of missingness for markers and lines
###################################################################

# The multicore package enables parallelization
library("parallel")
# if not R 3.0 or above, the multicore library is needed
if (! exists('mclapply')) {
	library(multicore)
}
nCores <- detectCores() / 2 # Use half the available cores
nCores <- min(nCores, 16) # But no more than 16 cores
options(cores=nCores)
# The rpart package makes tree predictors for clustering
library("rpart")

mrkData <- as.matrix(read.csv(mrkDataFile))
mrkData <- scale(mrkData, TRUE, FALSE) # Center but don't scale the markers
# Replace remaining NAs with 0, the mean. Need to put in place better missing data imputation
mrkData[is.na(mrkData)] <- 0
phenoData <- as.matrix(read.csv(phenoDataFile))
phenoData <- rowSums(phenoData, na.rm=TRUE)
phenoData <- scale(phenoData)

# Will the clustering be supervised by a phenotype or not?
nullPheno <- length(phenoData) < 1

nObs <- nrow(mrkData)
nPred <- ncol(mrkData)
nPheno <- ncol(phenoData)
nProjections <- 1000

###################################################################
# These parameters control properties of the tree construction
# Not sure what impact they have.  The outcome is hopefully pretty
# robust to their value.
###################################################################
proprow <- 0.3
propcol <- 0.1
maxdepth <- 2

############################################################
############################# DENIZ ISCA Clustering Programs
############################################################
# Make random projections.  If there are phenotypes, these
# projections are of the phenotypes. If not, they are of the
# predictors (markers)
#if (nullPheno){
	projectGeno <- function(i){
		rcoef <- runif(nPred)
		rcoef <- rcoef/sqrt(crossprod(rcoef))
		return(mrkData %*% rcoef)
	}
	projections <- mclapply(1:nProjections, projectGeno)
#} else{
#	projectPheno <- function(i){
#		rcoef <- runif(nPheno)
#		rcoef <- rcoef/sqrt(crossprod(rcoef))
#		return(phenoData %*% rcoef)
#	}
#	projections <- mclapply(1:nProjections, projectPheno)
#}

# Function to make a tree to predict a projection then extract rules from prediction
rulesFromProjection <- function(projection){
	ntt <- sample(nObs, nObs*proprow)
	ptt <- sample(nPred, nPred*propcol)
	Xtrain0 <- mrkData[, ptt]
	data0 <- data.frame(projection=projection, X0=I(Xtrain0))
	Xtrain1 <- mrkData[ntt, ptt]
	data1 <- data.frame(projection=projection[ntt], X0=I(Xtrain1))
	tree <- rpart(projection ~ X0, data=data1 , cp = 0, minsplit = 1, minbucket = 2, maxdepth=maxdepth)
	aaa <- predict(tree, newdata = data0)
	featuretrain <- as.factor(aaa)
	featuretrain <- as.matrix(model.matrix(~ featuretrain - 1), nrow=nObs)
	return(featuretrain)
}

allRules <- matrix(unlist(mclapply(projections, rulesFromProjection)), nrow=nObs)

######## Distance matrix from the rules
distanceinv <- tcrossprod(allRules) / nObs
distancees1 <- matrix(1, nObs, nObs) - distanceinv
distancees1 <- as.dist(distancees1)
######## Hierarchical Clustering
fit <- hclust(distancees1)
whichClust <- cutree(fit, k=nClust)
names(whichClust) <- rownames(mrkData)
write.table(whichClust, clustertableFile, sep="\t", quote=FALSE)

# If you have to use the transpose, I'm thinking you should look at $v not $u
svdwrapper <- function(x, nu=3, nv=3){
	gotit <- F
	try ({threePCs <- svd(x, nu, nv); gotit <- T}, silent = FALSE )
	if(gotit) return(threePCs)
	try ({threePCs <- svd(t(x), nu, nv); gotit <- T}, silent = FALSE )
	if(gotit) return(threePCs)
	stop("both svd(x) and svd(t(x)) failed.")
}

threePCs <- svdwrapper(allRules)
eigVec1 <- scale(threePCs$u[, 1])
eigVec2 <- scale(threePCs$u[, 2])
eigVec3 <- scale(threePCs$u[, 3])

# Output coordinates and cluster number for X3DOM
x3domCoords <- cbind(whichClust, eigVec1, eigVec2, eigVec3)
rownames(x3domCoords) <- rownames(mrkData)
write.table(x3domCoords, file=clust3dCoords, col.names = FALSE, sep = "\t", quote=FALSE)

# The user would specify a limited number of lines to see into what cluster they fall
#lineNames <- c("06MN-02", "06AB-49", "08UT-15", "08BA-36", "08N6-39")
# lineNames is now loaded by cluster_show.php from the web form.
# If the user supplies a line name that is NOT among the
# selected lines, this will just boot it out.
lineNames <- intersect(lineNames, names(whichClust))
lineCol <- whichClust[lineNames]
extraLegend <- 1:nClust
if (length(lineCol) > 0){ # Garbage out if there were no valid lineNames
	uniCol <- sort(unique(lineCol))
	extraLegend <- (1:nClust)[-uniCol]
}
if (length(extraLegend) > 0){ # Garbage out if no extra legend needed
	lineCol <- c(lineCol, extraLegend)
# Get the name of the first line in any cluster for which
# the user did not supply a name.
	extraNames <- sapply(extraLegend, function(num) return(names(whichClust[whichClust == num])[1]))
	lineNames <- c(lineNames, extraNames)
}
nInClust <- sapply(lineCol, function(clustNum) return(sum(whichClust == clustNum)))
clustInfo <- paste(lineCol, lineNames, nInClust, sep=", ")
write(clustInfo, clustInfoFile)

if (exists("email")) {
  command <- paste("echo \"cluster analysis is done\n", result_url, "\" | mail -s \"Results from T3 cluster hclust\"", email)
  system(command)
}
