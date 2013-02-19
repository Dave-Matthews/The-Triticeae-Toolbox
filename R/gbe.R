wavelength <- csrData[,1]

#find index for wavelength, distribution is not linear so pick the closest one
min <- max(wavelength)
for (i in 1:length(wavelength)) {
  diff <- abs(W1wav - wavelength[i])
  if (diff < min) {
     min <- diff
     W1idx <- i
  }
}
min <- max(wavelength)
for (i in 1:length(wavelength)) {
  diff <- abs(W2wav - wavelength[i])
  if (diff < min) {
     min <- diff
     W2idx <- i
  }
}

source(file_for)

for (i in 2:ncol(csrData)) {
  if (i == 2) {
    xrange <- c(W1wav-20,W2wav+20)
    y1 <- rowMeans(csrData[W1idx,]) - 20
    y2 <- rowMeans(csrData[W2idx,]) + 20

    yrange <- c(y1, y2)
    plot(csrData[,1], csrData[,i], xlim=xrange, ylim=yrange, type="n", xlab="wavelength", ylab="CSR value")
    lines(csrData[,1], csrData[,i])
  } else {
    lines(csrData[,1], csrData[,i])
  }
}

csrData <- csrData[,-(1)]
results <- apply(csrData, 2, calIndex,idx1= W1idx, idx2=W2idx);
pltData <- pltData[-(1)]
pltData <- t(pltData)
results2 <- data.frame(plot=pltData, index=results)
write.csv(results2, file=file_out, quote=FALSE, row.names = FALSE)

#plot index vs plot number
dev.set(dev.next())
xrange <- range(pltData)
yrange <- range(results)
plot(pltData, results, xlim=xrange, ylim=yrange, xlab="plot", ylab="CSR Index")
dev.off()
