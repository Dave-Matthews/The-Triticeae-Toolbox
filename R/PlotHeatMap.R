x <- as.matrix(read.delim("plotMap.txt", header=TRUE, sep="\t"))
cc <- rainbow(ncol(x), start = 0, end = 0.5)
heatmap(x, scale = "none",  ColSideColors = cc,
              xlab = "column", ylab =  "row",
              Rowv = NA, Colv = NA )
dev.off()
