x <- as.matrix(read.delim("plotMap.txt", header=TRUE, sep="\t", check.names = FALSE))

#remove columns that only contain NA
xfilt <- x
isnaMat <- !is.na(x)
nFalse <- apply(isnaMat, 2, sum)
for (i in ncol(x):2) {
  if (nFalse[i] == 0) {
    #print(paste("Column ",i," removed"))
    xfilt <- xfilt[,-i]
  }
}

library(pheatmap)
pheatmap(xfilt, cluster_rows = FALSE, cluster_cols = FALSE)
dev.off()
