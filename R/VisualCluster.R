# tht/R/VisualCluster.R
# called by tht/cluster_show.php, which prepends several commands in setupcluster.txt

# from Jean-Luc Jannink 9feb11, ./VisualCluster.R.orig
# Clusters the input lines and marker alleles for the user to select cluster of interest.
# The marker data represents the full set of lines that a user has selected.

# Delete the prepended command file.
system("rm setupcluster*")

mrkData <- read.csv(mrkDataFile)

library(cluster)
scaledMrk <- scale(mrkData, TRUE, FALSE)

# R runs a cluster analysis and a principal components analysis for display
whichClust <- pam(scaledMrk, nClust, metric="manhattan", cluster.only=TRUE)
write.table(whichClust, clustertableFile, sep="\t", quote=FALSE)
twoPCs <- svd(scaledMrk, 2, 2)
PCA1 <- twoPCs$u[,1]
PCA2 <- twoPCs$u[,2]

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

mygraph = cbind(whichClust, PCA1, PCA2)
# Draw the graph.  Function iPlot() defined in ./iPlot.R.
iPlot(mygraph, "/tmp/tht", "linecluster")

# The legend says where those lines are that the user is interested
clustInfo <- (paste(lineCol, lineNames, nInClust, sep=", "))
write(clustInfo, clustInfoFile)

