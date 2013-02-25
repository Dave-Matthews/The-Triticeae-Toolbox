#calculates canopy spectral reflectance(CSR) index
#formula is dynamically generated function stored in file "file_for"
#inputs
# csrData - array of csr data, skip the first 5 lines of CSR file, first column is wavelength
# pltData - array containing plot number (2nd line of CSR file)
# W1wav, W2wav - wavelengths used in index formula

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
if (W1idx == 1) {
  stop("Error: W1 wavelength is too small")
}

min <- max(wavelength)
for (i in 1:length(wavelength)) {
  diff <- abs(W2wav - wavelength[i])
  if (diff < min) {
     min <- diff
     W2idx <- i
  }
}
if (W2idx == length(wavelength)) {
  stop("Error: W2 wavelength is too large")
}

#filter data set
if (smooth > 0) {
  flt2 <- (2*smooth) + 1
  csrFilt <- apply(csrData, 2, runmed,k=flt2)
} else {
  csrFilt <- csrData
}

#read in formula to calculate index
source(file_for)

#plot csr data for all plots
#scale the axis to zoom in on the selected wavelengths
for (i in 2:ncol(csrData)) {
  if (i == 2) {
    xrange <- c(W1wav-20,W2wav+20)
    yrange <- range(csrData[W1idx:W2idx,-(1)])
    plot(csrData[,1], csrFilt[,i], xlim=xrange, ylim=yrange, type="n", xlab="wavelength", ylab="CSR value")
    lines(csrData[,1], csrFilt[,i])
  } else {
    lines(csrData[,1], csrFilt[,i])
  }
}

# apply formula to calculate index for each column then write to file
csrFilt <- csrData[,-(1)]      
results <- apply(csrFilt, 2, calIndex,idx1= W1idx, idx2=W2idx);
pltData <- pltData[-(1)]
pltData <- t(pltData)
results2 <- data.frame(plot=pltData, index=results)
write.csv(results2, file=file_out, quote=FALSE, row.names = FALSE)

#plot index vs plot number
dev.set(dev.next())
xrange <- range(pltData)
yrange <- range(results, na.rm=TRUE)
plot(pltData, results, xlim=xrange, ylim=yrange, xlab="plot", ylab="CSR Index")
dev.off()
