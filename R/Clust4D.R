# t3/.../R/Clust4D.R
# called by cluster4d.php, which prepends several commands in setupclust3d.txt

# from Jean-Luc Jannink 9feb11, ./VisualCluster.R.orig
# Clusters the input lines and marker alleles for the user to select cluster of interest.
# The marker data represents the full set of lines that a user has selected.

mrkData <- read.csv(mrkDataFile)
lineNames2 <- as.matrix(rownames(mrkData))

mrkData<-as.matrix(mrkData,nrow=dim(mrkData)[1])

phenoData<-read.csv(phenoDataFile)
phenoData <- rowSums(phenoData, na.rm=TRUE)

##########################################################


####################################################

if (length(phenoData) < 1) {
nulpheno=TRUE
} else {
nulpheno=FALSE
}
phenoData<-as.matrix(phenoData, nrow=dim(mrkData)[1])


n<-dim(mrkData)[1]
m=1000
phenoData<-scale(phenoData)

# DEM 6mar12: Allow up to 10% missing data.
fracNA <- apply(mrkData, 2, function(vec) return(sum(is.na(vec)))) / nrow(mrkData)
mrkData <- mrkData[, fracNA <= 0.1]

#library(cluster)
library(doSNOW)
library(doParallel)

numOfCores <- parallel:::detectCores()/2

scaledMrk <- scale(mrkData, TRUE, FALSE)
# Replace remaining NAs with 0, the mean.
scaledMrk[is.na(scaledMrk)] <- 0
#write.table(scaledMrk, "scaledMrk", sep=",", quote=FALSE)  # DEBUG

# R runs a cluster analysis and a principal components analysis for display
library("foreach")


cl <- makeCluster(numOfCores)
registerDoParallel(cl)


#############################DENIZ ISCA Clustering Programs
if (nulpheno) {

################################################################################################################################################
################################################################################################################################################

######ONE RANDOM TREE

oneclustering<-function(mciterator){
library("rpart")
library("corpcor")

p<-dim(X)[2]
n<-dim(X)[1]
	
	
rcoef<-runif(dim(X)[2])
rcoef<-rcoef/sqrt(t(rcoef)%*%rcoef)
P<-X%*%rcoef

ntt<-sample(1:n,n*proprow)
ptt<-sample(1:p,p*propcol)
Xtrain0<-X[,ptt]
Xtrain1<-X[ntt,ptt]
P1<-P[ntt]
data1<-data.frame(p=P1, X0=I(Xtrain1))
data0<-data.frame(p=P, X0=I(Xtrain0))
tree <- rpart(p~X0, data=data1 , cp = 0, minsplit = 1, minbucket = 2, maxdepth=maxdepth)
aaa<-predict(tree,newdata = data0)

featuretrain<-as.factor(aaa)
featuretrain<-as.matrix(model.matrix(~featuretrain-1), nrow=n)
return(featuretrain)
}
	

X=scaledMrk
proprow=.3
propcol=.1
maxdepth=2
mtr<- foreach(i=1:m, .combine='cbind') %dopar% oneclustering(i)

##Run Onetree m times to obtain Ftrain1,...FtrainM, Ftest1,...,Ftestm

	n<-dim(scaledMrk)[1]

########   DISTANCE MATRIX



	
distanceinv<-mtr%*%t(mtr)
	
distanceinvmax<-dim(distanceinv)[2]

distanceinv<-distanceinv/distanceinvmax

distancees1<-matrix(1,nrow=dim(distanceinv)[1],ncol=dim(distanceinv)[2])-(distanceinv)
distancees1<-as.dist(distancees1)
###############Hierarchical Clustering
fit <- hclust(distancees1)
whichClust <- cutree(fit, k=nClust)
write.table(whichClust, clustertableFile, sep="\t", quote=FALSE)
}

#####################################################################################
if (!nulpheno){



##########################################################

######ONE RANDOM TREE

oneclusteringSS<-function(mciterator,Y){
library("rpart")
library("corpcor")
p<-dim(X)[2]
n<-dim(X)[1]
Y<-scale(Y, TRUE,TRUE)
	
rcoef<-runif(dim(Y)[2])
rcoef<-rcoef/sqrt(t(rcoef)%*%rcoef)
P<-Y%*%rcoef
	ntt<-sample(1:n,n*proprow)
	ptt<-sample(1:p,p*propcol)
(Xtrain0<-X[,ptt])
(Xtrain1<-X[ntt,ptt])
(P1<-P[ntt])
data1<-data.frame(p=P1, X0=I(Xtrain1))
data0<-data.frame(p=P, X0=I(Xtrain0))
tree <- rpart(p~X0, data=data1 , cp = 0, minsplit = 1, minbucket = 2, maxdepth=maxdepth)
aaa<-predict(tree,newdata = data0)

featuretrain<-as.factor(aaa)
featuretrain<-as.matrix(model.matrix(~featuretrain-1), nrow=n)
return(featuretrain)
}

	X=scaledMrk
	Y=phenoData
	proprow=.3
	propcol=.1
	maxdepth=2
	mtr<- foreach(i=1:m, .combine='cbind') %dopar% oneclusteringSS(i,Y)
	
##Run Onetree m times to obtain Ftrain1,...FtrainM, Ftest1,...,Ftestm
	
	n<-dim(scaledMrk)[1]

########   DISTANCE MATRIX

distanceinv<-mtr%*%t(mtr)

distanceinvmax<-dim(distanceinv)[2]

distanceinv<-distanceinv/distanceinvmax
distancees1<-matrix(1,nrow=dim(distanceinv)[1],ncol=dim(distanceinv)[2])-(distanceinv)
distancees1<-as.dist(distancees1)
###############Hierarchical Clustering
fit <- hclust(distancees1)
whichClust <- cutree(fit, k=nClust)
write.table(whichClust, clustertableFile, sep="\t", quote=FALSE)
}

stopCluster(cl)

distancees12<-dist(mrkData, diag=T, upper=T)
fit2 <- hclust(distancees12)
whichClust2 <- cutree(fit2, k=nClust)


#############################################################################################################################


ftrainmatrix<-matrix(unlist(mtr), nrow=dim(mtr)[1])

gotit <- F
try ({threePCs <- svd(ftrainmatrix, 3, 3); gotit<-T}, silent = FALSE )
if(gotit) {
  eigVec1 <- scale(threePCs$u[,1])
  eigVec2 <- scale(threePCs$u[,2])
  eigVec3 <- scale(threePCs$u[,3]) 
}
try ({threePCs <- svd(t(ftrainmatrix), 3, 3); gotit<-T}, silent = FALSE )
if(gotit) {
  eigVec1 <- scale(threePCs$v[,1])
  eigVec2 <- scale(threePCs$v[,2])
  eigVec3 <- scale(threePCs$v[,3]) 
} else {
  stop("svd(x) and svd(t(x)) both failed.")
}

# The user would specify a limited number of lines to see into what cluster they fall
#lineNames <- c("06MN-02", "06AB-49", "08UT-15", "08BA-36", "08N6-39")
# lineNames is now loaded by cluster_show.php from the web form.
# If the user supplies a line name that is NOT among the
# selected lines, this will just boot it out.
lineNames <- lineNames[lineNames %in% names(whichClust)]
lineCol <- whichClust[lineNames]
extraLegend <- 1:nClust
if (length(lineCol) > 0){ # This condition needed because garbage out if there were no valid lineNames
uniCol <- sort(unique(lineCol))
extraLegend <- (1:nClust)[-uniCol]
}
if (length(extraLegend) > 0){ # This condition needed because garbage out if no extra legend needed
lineCol <- c(lineCol, extraLegend)
# Get the name of the first line in any cluster for which the user did
# not supply a name.
extraNames <- sapply(extraLegend, function(num) return(names(whichClust[whichClust == num])[1]))
lineNames <- c(lineNames, extraNames)
}

nInClust <- sapply(lineCol, function(clustNum) return(sum(whichClust == clustNum)))

plot(eigVec1, eigVec2, pch=16, col=whichClust)

# Look for an empty-ish space on the graph where you can put the graph legend
minDots <- 1e30
pc1seq <- seq(min(eigVec1), max(eigVec1), length.out=5)
pc2seq <- seq(min(eigVec2), max(eigVec2), length.out=5)
for (i in 1:4){
	for (j in 1:4){
		nDots <- sum(eigVec1 > pc1seq[i] & eigVec1 < pc1seq[i + 1] & eigVec2 > pc2seq[j] & eigVec2 < pc2seq[j + 1])
		if (minDots > nDots) {
		  putLegend <- c(i, j)
		  minDots <- nDots
		}
	}
}

# Output coordinates and cluster number for X3DOM.
whichClust <- as.matrix(whichClust)
rownames(whichClust) <- lineNames2
write.table(cbind(whichClust, eigVec1, eigVec2, eigVec3), file = clust3dCoords, col.names = FALSE, sep = "\t")

# The legend says where those lines are that the user is interested
# This doesn't work, gets the colors mismatched with the clusters:
##clustInfo <- sort(paste(lineCol, lineNames, nInClust, sep=", "))
clustInfo <- (paste(lineCol, lineNames, nInClust, sep=", "))
legend(pc1seq[putLegend[1]], pc2seq[putLegend[2] + 1], clustInfo, lty=0, pch=16, col=lineCol)

write(clustInfo, clustInfoFile)

# By looking at the graph and the legend, the user would be able to specify which cluster
# They actually wanted to download out of the full set of lines they had selected...

# Flush the output graph to the file.
dev.off()
