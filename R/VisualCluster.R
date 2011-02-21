# from Jean-Luc Jannink 9feb11, ./VisualCluster.R.orig
# Clusters the input lines and marker alleles for the user to select cluster of interest.

# The marker data represents the full set of lines that a user has selected
#load("/www/htdocs/matthews/tht/R/mrkData.RData")
mrkData <- read.csv("temp/mrkData.csv")

library(cluster)
scaledMrk <- scale(mrkData, TRUE, FALSE)

# R runs a cluster analysis and a principal components analysis for display
whichClust <- pam(scaledMrk, nClust, metric="manhattan", cluster.only=TRUE)
write.table(whichClust, "temp/clustertable.txt", sep="\t", quote=FALSE)
twoPCs <- svd(scaledMrk, 2, 2)
eigVec1 <- twoPCs$u[,1]
eigVec2 <- twoPCs$u[,2]

# The user would specify a limited number of lines to see into what cluster they fall
#lineNames <- c("06MN-02", "06AB-49", "08UT-15", "08BA-36", "08N6-39")
# lineNames is now loaded by cluster_show.php from the web form.
lineCol <- whichClust[lineNames]

# Color Fix Here
uniCol <- sort(unique(lineCol))
extraLegend <- (1:nClust)[-uniCol]
lineCol <- c(lineCol, extraLegend)
lineNames <- c(lineNames, palette()[extraLegend])

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

# The legend says where those lines are that the user is interested
legend(pc1seq[putLegend[1]], pc2seq[putLegend[2] + 1], paste(lineCol, ":", lineNames), lty=0, pch=16, col=lineCol)

# By looking at the graph and the legend, the user would be able to specify which cluster
# They actually wanted to download out of the full set of lines they had selected...

# Flush the output graph to the file.
dev.off()
