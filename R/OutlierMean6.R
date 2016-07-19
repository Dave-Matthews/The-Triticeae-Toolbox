################################################################################################################
########### Outlier Detection performed with a Bonferroni-Holm test using independent MAD calculated ###########
########### for each half/tail of the data to correct for uneven data distribution.  MAD values are ############
###################################### re-scaled to standardize residuals ######################################
################################################################################################################

# Set working directory
setwd("/tmp/tht")

# Libraries
## Package "lsmeans" requires pre-loaded package "estimability" and packages: "mvtnorm", "multcomp", "sandwich", "zoo", "TH.data", "coda", "xtable"
library(lme4)
library(lsmeans)
library(pbkrtest)
library(reshape)

# Install package "multtest" from bioconductor
#source("http://bioconductor.org/biocLite.R")
#biocLite("multtest")
library(multtest)


#OutlierThreshold <- 0.05
###User defines what "OutlierThreshold" is equal to in the below function
###OutlierThreshold <- equal to entry in field on T3 page.

#Bonferroni-Holm Test Function
BonfHolmOutlier <- function(resi){
  median <- median(resi, na.rm=TRUE)
  abs.dev   <- abs(resi - median)
  #Calculate left and right MAD values.
  left.MAD  <- median(abs.dev[resi<=median], na.rm=TRUE)*1.4826
  right.MAD <- median(abs.dev[resi>=median], na.rm=TRUE)*1.4826
 
if(OK[1,i] == TRUE){ 
  # MAD standardized residuals.  Including the values on the median in the "left.MAD".  They need to be on one side or the other.  It really will not matter because values on the median will not be outliers.
  res_MAD <- ifelse((resi<=median), (resi/left.MAD), (resi/right.MAD))
    
  # Calculate adjusted p-values  
  rawp <- (1-pnorm(abs(res_MAD)))
  
  # Do the actual test. Test returns p-values adjusted for multiple testing
  res2 <- mt.rawp2adjp(rawp,proc=c("Holm"), na.rm=TRUE)
    
  #Create vectors/matrices out of the list of the BH tests
  bholm <- cbind(res2[[1]][,2])
  index <- cbind(res2[[2]])
  
  #CANCEL THE BUILT IN SORT FROM mt.rawpadjp!!!
  bholm <- bholm[order(res2$index),]
    
  ###FLAG OUTLIERS 
  out_flag <- ifelse(bholm<=OutlierThreshold, "",dataCols[,i]) 
  
  }else{
  	out_flag <- dataCols[,i]
  	  }
  	  }

# Read file
# For entry-mean data, residuals should just be deviations from the mean
#trialData <- read.table("Idahotraits.txt", sep="\t", header=TRUE, stringsAsFactors=FALSE, check.names=FALSE)

trialData2 <- trialData
trialData2[,2]<-paste("and", trialData2[,2], sep="_")
mdata <- melt(trialData2, id=c("line","trial")) 
castdata <- cast(mdata, line~variable+trial)


####Will use "columnname" at end as a column heading.
castdata2<-castdata
colnames(castdata2)[1]<-"line_and_line"

newcolumnheading<-strsplit(colnames(castdata2), "_and_")
columnname <- data.frame(matrix(unlist(newcolumnheading), nrow=2, byrow=F),stringsAsFactors=FALSE)

colnames(columnname)<-colnames(castdata)
columnname[2,1]<-NA
columnname[is.na(columnname)]<-""


###Some data setup

dataColumns <- castdata[(!(names(castdata) %in% c("line")))]

c <- ncol(dataColumns)
n <- nrow(dataColumns)

dataCols <- as.data.frame(matrix(nrow = n, ncol= 0))

for (i in 1:c) {
	Cols <- as.numeric(as.character(dataColumns[,i]))
	newline <- data.frame(Cols, stringsAsFactors=FALSE)
	dataCols <- cbind(dataCols, newline)
}	

### Bonferroni-Holm Test Calculation and Results Output
resi <- as.data.frame(matrix(nrow = n, ncol= 0))
BHresult <- as.data.frame(matrix(nrow = n, ncol= 0))
OK <- as.data.frame(matrix(nrow = n, ncol= 0))

### Calculate residuals
for (i in 1:c) {
  resCALC <- (dataCols[1:n,i]) - colMeans(dataCols[i], na.rm=TRUE)
  newline <- data.frame(resCALC, stringsAsFactors=FALSE)
  resi <- cbind(resi, newline)
}

### Test if the data is OK - meaning that half the values are not identical.
for (i in 1:c) {
   m         <- median(resi[,i], na.rm=TRUE)
   abs.dev   <- abs(resi[,i] - m)
   left.mad  <- median(abs.dev[resi[,i]<=m], na.rm=TRUE)
   right.mad <- median(abs.dev[resi[,i]>=m], na.rm=TRUE)
  test <- (left.mad >= 0.4 & right.mad >= 0.4)
  newline <- data.frame(test, stringsAsFactors=FALSE)
  OK <- cbind(OK, newline)
}
OK[is.na(OK)]<-FALSE

### Perform Outlier Detection with our function from above
for (i in 1:c) {
  bholmOut <- BonfHolmOutlier(resi[,i])
  newline <- data.frame(bholmOut, stringsAsFactors=FALSE)
  BHresult <- cbind(BHresult, newline)
  }
  
colnames(BHresult) <- colnames(dataColumns)

LineHeader <- castdata[1]
colnames(LineHeader) <- colnames(castdata[1])

###########################################################
###########################################################

####This is the filtered data with NA and Outliers removed.  This should be used for analysis if selected by user.

Filtered <- as.data.frame(matrix(nrow = n, ncol= 0), stringsAsFactors=FALSE)
	
for (i in 1:c) {
  newframe <- (as.numeric(as.vector(BHresult[,i])))
  ###Clay had to change this to as. character to get it to work.  Not sure why this does not work for me.
  Filtered <- cbind(Filtered, newframe)
}
colnames(Filtered) <-colnames (dataColumns)
Filtered[is.na(Filtered)] <- ""
FilteredTable<- cbind(LineHeader, Filtered)

DisplayFilteredTable<-rbind(columnname, FilteredTable)

###########################################################
###########################################################

####Data frame only showing outliers in their original dataset position

CleanOutput <- as.data.frame(matrix(nrow = n, ncol= 0), stringsAsFactors=FALSE)

for (i in 1:c) {
	newframe <- (as.numeric(as.vector(BHresult[,i])))
	RemoveNA <- ifelse(is.na(newframe), dataCols[,i], "") 
	qwerty<-as.numeric(RemoveNA)
	filter <- data.frame(qwerty, stringsAsFactors=FALSE)
	CleanOutput <- cbind(CleanOutput, filter)
}

colnames(CleanOutput) <-colnames (dataColumns)
OutlierDataset <- cbind(LineHeader, CleanOutput)
OutlierTable <- OutlierDataset

OutlierTable[is.na(OutlierTable)]<-""

DisplayOutlierTable<-rbind(columnname, OutlierTable)

###########################################################
###########################################################

####Now need to select only lines containing outliers for at least one trait and display a short list of ONLY outliers

cro <- c+1

RawOutliers <- OutlierDataset[rowSums(is.na(OutlierDataset[2:cro]))<length(OutlierDataset[2:cro]),]
RawOutliers[is.na(RawOutliers)]<-""

c2 <- ncol(RawOutliers)
n2 <- nrow(RawOutliers)

Outliers <- as.data.frame(matrix(nrow = n2, ncol= 0), stringsAsFactors=FALSE)


for (i in 1:c2) {
newframe <- (as.character(RawOutliers[,i]))
Oframe <- data.frame(newframe, stringsAsFactors=FALSE)
Outliers <- cbind(Outliers, Oframe)
}
colnames(Outliers)<-colnames(castdata)

DisplayOutliers<-rbind(columnname, Outliers)

###########################################################
###########################################################

#Write text file of data that has been filtered to only show Outliers in original positions
write.table(DisplayOutlierTable, file = fileout1, sep="\t", row.names=FALSE, quote=FALSE, col.names=FALSE)


#Write text file list of Outliers.  This is the one you want to save
write.table(DisplayOutliers, file = fileout2, sep="\t", row.names=FALSE, quote=FALSE, col.names=FALSE)


#Write text file of data filtered for NA and outliers.  THIS SHOULD BE USED IN ANALYSIS
write.table(DisplayFilteredTable, file = fileout3, sep="\t", row.names=FALSE, quote=FALSE, col.names=FALSE)
#############################################################################################
