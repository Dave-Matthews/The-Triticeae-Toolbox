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

min <- max(wavelength)
for (i in 1:length(wavelength)) {
  diff <- abs(W2wav - wavelength[i])
  if (diff < min) {
     min <- diff
     W2idx <- i
  }
}

if (is.na(W3wav)) {
  W3idx <- ""
} else {
min <- max(wavelength)
for (i in 1:length(wavelength)) {
  diff <- abs(W3wav - wavelength[i])
  if (diff < min) {
     min <- diff
     W3idx <- i
  }
}
}

#remove columns that only contain NA
csrFilt <- csrData
isnaMat <- !is.na(csrData)
nFalse <- apply(isnaMat, 2, sum)
for (i in ncol(csrData):2) {
  if (nFalse[i] == 0){
    csrFilt <- csrFilt[,-i]
    pltData <- pltData[,-i]
    print(paste("Column ",i," removed"))
  }
}

#filter data set
if (smooth > 0) {
  flt2 <- (2*smooth) + 1
  csrFilt <- apply(csrFilt, 2, runmed,k=flt2)
#} else {
#  csrFilt <- csrData
}

#read in formula to calculate index
source(file_for)

#plot csr data for all plots
#scale the axis to zoom in on the selected wavelengths
for (i in 2:ncol(csrFilt)) {
  if (i == 2) {
    if (zoom == "entire") {
      xrange <- c(wavelength[1],wavelength[length(wavelength)])
    } else {
      xrange <- c(min(W1wav,W2wav,W3wav,na.rm=TRUE)-20,max(W1wav,W2wav,W3wav,na.rm=TRUE)+20)
    }
    yrange <- range(csrData[W1idx:W2idx,-(1)], na.rm = TRUE)
    plot(csrData[,1], csrFilt[,i], xlim=xrange, ylim=yrange, type="n", xlab="wavelength", ylab="CSR value")
    lines(csrData[,1], csrFilt[,i])
  } else {
    lines(csrData[,1], csrFilt[,i])
  }
}

#check wavelength range before calculating index
if (W1idx == 1) {
  stop("Error: W1 wavelength is too small")
}
if (W2idx == length(wavelength)) {
  stop("Error: W2 wavelength is too large")
}
if (W3idx == "") {
} else {
if ((W3idx == 1) || (W3idx == length(wavelength))) {
  stop("Error: W3 out of range")
}
}

# apply formula to calculate index for each column then write to file
csrFilt <- csrFilt[,-(1)]      
results <- apply(csrFilt, 2, calIndex,idx1= W1idx, idx2=W2idx,  idx3=W3idx);
pltData <- pltData[-(1)]
pltData <- t(pltData)
pltData2 <- cbind(trial_code, pltData)
results2 <- data.frame(plot=pltData2, index=results)
colnames(results2) <- c("Trial Code", "Plot", paste("CSR_", formula1, sep=""))
write.csv(results2, file=file_out, quote=FALSE, row.names = FALSE)

#plot index vs plot number
dev.set(dev.next())
xrange <- range(pltData, na.rm=TRUE)
yrange <- range(results, na.rm=TRUE)
plot(pltData, results, xlim=xrange, ylim=yrange, xlab="plot", ylab="CSR Index")
dev.off()
