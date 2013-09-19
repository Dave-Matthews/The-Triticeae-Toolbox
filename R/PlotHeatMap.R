x <- as.matrix(read.delim("plotMap.txt", header=TRUE, sep="\t"))
heatmap(x, scale = "none",
              xlab = "column", ylab =  "row",
              Rowv = NA, Colv = NA )
dev.off()
