# t3/.../R/Clust3D.R
# called by cluster3d.php, which prepends several commands in setupclust3d.txt

# from Jean-Luc Jannink 9feb11, ./VisualCluster.R.orig
# Clusters the input lines and marker alleles for the user to select cluster of interest.
# The marker data represents the full set of lines that a user has selected.

# Delete the prepended command file.
#system("rm setupclust3d.txt*")

mrkData <- read.csv(mrkDataFile)
# DEM oct11: Now this line is breaking the script. Wonder why.
#system2("rm", mrkDataFile)

# DEM 6mar12: Allow up to 10% missing data.
fracNA <- apply(mrkData, 2, function(vec) return(sum(is.na(vec)))) / nrow(mrkData)
mrkData <- mrkData[, fracNA <= 0.1]

library(cluster)
scaledMrk <- scale(mrkData, TRUE, FALSE)
# Replace remaining NA's with 0, the mean.
scaledMrk[is.na(scaledMrk)] <- 0
#write.table(scaledMrk, "scaledMrk", sep=",", quote=FALSE)  # DEBUG

# R runs a cluster analysis and a principal components analysis for display
whichClust <- pam(scaledMrk, nClust, metric="manhattan", cluster.only=TRUE)
write.table(whichClust, clustertableFile, sep="\t", quote=FALSE)

gotit <- F
try ({threePCs <- svd(scaledMrk, 3, 3); gotit<-T}, silent = FALSE )
if(gotit) {
  print("using svd(x)")
  eigVec1 <- threePCs$u[,1]
  eigVec2 <- threePCs$u[,2]
  eigVec3 <- threePCs$u[,3]
}
try ({threePCs <- svd(t(scaledMrk), 3, 3); gotit<-T}, silent = FALSE )
if(gotit) {
  print("using svd(t(x))")
  eigVec1 <- threePCs$v[,1]
  eigVec2 <- threePCs$v[,2]
  eigVec3 <- threePCs$v[,3]
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

if (exists("email")) {
  command <- paste("echo \"cluster analysis is done\n", result_url, "\" | mail -s \"Results from T3 cluster pam\"", email)
  system(command)
}
