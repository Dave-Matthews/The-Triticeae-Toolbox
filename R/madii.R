## Function for generating a randomized, incomplete block experimental design
### Tyler Tiede. University of Minnesota. May 22, 2014

# The original flexible design.dma from Tyler had three different scenerios:
# 1) user-defined: the user enters the number of field rows and columns, as well as the number of rows and columns per block
# 2) semi-naive: the user only knows the number of field rows and the number of rows per blk
# 3) naive: the user only knows the number of field rows
# fullSpec retains only 1) though it does not require the number of field columns

# List of checks starting with the primary; assumes you have a primary check
design.mad <- function(enviro=format(Sys.Date(), "%x"), entries=NULL, nEntries=NULL, chk.names=NULL, nSecChk=NULL, nFieldRows=NULL, nFieldCols=NULL, nRowsPerBlk=NULL, nColsPerBlk=NULL, nChksPerBlk=2,  plot.start=1001, fillWithChk=TRUE, serpentinePlotNumbers=TRUE){
  
  ## QC of function inputs
  if(is.null(nFieldRows) | is.null(nRowsPerBlk) | is.null(nColsPerBlk)) stop("Must define number of rows (=ranges or beds) in the field and the row and column size of blocks")
    
  if(is.null(entries) & is.null(nEntries)) stop("Must provide an entry list (entries=) OR the number of entries desired (nEntries=).")
  
  if(is.null(chk.names) & is.null(nSecChk)) stop("Must provide a list of check names (chk.names=) with the primary check listed first\n OR the number of SECONDARY checks desired (nSecChk=).")
 
  # Set up entry names or numbers
  if(is.null(entries)){
    entries <- as.matrix(paste("entry", 1:nEntries, sep="_"))
  } else {
    #fixed - should be passed to function
    #nEntries <- nrow(entries)
    entries <- as.matrix(entries) # If user input of entries was a list, will convert it to matrix
  }
  
  # Set up check names or numbers
  if(is.null(chk.names)){
    chk.names <- paste("chk", 1:(nSecChk + 1), sep="_") # Generic check names
  } else {
    nSecChk <- length(chk.names) - 1
  }
  
  # if nFieldCols not given, assume the field can extend in that direction sufficiently
  nPlotsPerBlk <- nRowsPerBlk * nColsPerBlk
  nEntriesPerBlk <- nPlotsPerBlk - nChksPerBlk
  nBlks <- ceiling(nEntries / nEntriesPerBlk)
  nBlkRows <- nFieldRows %/% nRowsPerBlk
  expSize <- nBlks * nPlotsPerBlk
  if(is.null(nFieldCols)){
    nFieldCols <- ceiling(nBlks / nBlkRows) * nColsPerBlk
  } else { # Check if the size of the field is large enough to accomdate all the entries + checks
    maxNblks <- nBlkRows * floor(nFieldCols / nColsPerBlk)
    if(nEntries > maxNblks * nEntriesPerBlk) stop("The field is too small to accomodate the number of entries given block size and checks.")
  }
  # The expt might be prefectly rectangular OR there might be one or more rows of blocks that have
  # an extra block relative to the rest to fit in nBlks
  nBlkCols <- ceiling(nBlks / nBlkRows)
  blkRowsExtra <- nBlks %% nBlkRows

  ### Now the field dimension are set
  nChks <- nBlks*nChksPerBlk
  nFill <- expSize - (nEntries + nChks)
  
  nSecChkPerBlk <- nChksPerBlk - 1 # Always have a primary check in block, the rest are secundary
  nSecChkPlots <- nBlks * nSecChkPerBlk
  if(fillWithChk){
    nSecChkPlots <- nSecChkPlots + nFill
    nSecChkPerBlk <- nSecChkPlots %/% nBlks
    nBlksExtraChk <- nSecChkPlots %% nBlks
    nSecChkEachBlk <- sample(c(rep(nSecChkPerBlk, nBlks - nBlksExtraChk), rep(nSecChkPerBlk+1, nBlksExtraChk)))
  } else {
    nSecChkEachBlk <- rep(nSecChkPerBlk, nBlks)
    nFillPerBlk <- nFill %/% nBlks # Hopefully this is always zero
    nBlksExtraFill <- nFill %% nBlks
    nFillEachBlk <- sample(c(rep(nFillPerBlk, nBlks - nBlksExtraFill), rep(nFillPerBlk+1, nBlksExtraFill)))
  }
  nSecChkReps <- nSecChkPlots %/% nSecChk # Min number of times each secondary check will be observed in the field  
  secChkNums <- 1 + 1:nSecChk
  secChkPool <- rep(secChkNums, times=nSecChkReps)
  secChkPool <- c(secChkPool, sample(secChkNums, size=nSecChkPlots-length(secChkPool), replace=F)) 
  secChkPool <- sample(secChkPool)
  
  #################################################################
  ############### Build Field Data Frame ##########################
  #################################################################
  # This is trickier if the field is not rectangular
  # Then you have to make the field out of two rectangular fields
  
  # Function to make rectangular field data frame
  makeFieldLayout <- function(plot.start=1, blk.start=1, blkRow.start=1, nRowsPerBlk, nColsPerBlk, nBlkRows, nBlkCols){
    nFieldRows <- nRowsPerBlk * nBlkRows
    nFieldCols <- nColsPerBlk * nBlkCols
    fieldSize <- nFieldRows * nFieldCols
    toRepForCols <- 1:nFieldCols
    if (serpentinePlotNumbers) toRepForCols <- c(toRepForCols, nFieldCols:1)
    
    toRepForBlkCols <- 1:nBlkCols
    if (serpentinePlotNumbers) toRepForBlkCols <- c(toRepForBlkCols, nBlkCols:1)
    BlkCol <- rep(toRepForBlkCols, each=nColsPerBlk, length.out=fieldSize)
    BlkRow <- rep(blkRow.start - 1 + 1:nBlkRows, each=(fieldSize/nBlkRows))
    blkNums <- blk.start - 1 + BlkCol + (BlkRow - blkRow.start) * nBlkCols
    
    layout <- data.frame(Enviro=enviro, Plot=plot.start - 1 + 1:fieldSize, Row=rep((blkRow.start - 1) * nRowsPerBlk + 1:nFieldRows, each=nFieldCols), Col=rep(toRepForCols, length.out=fieldSize), Blk=blkNums, BlkRow=BlkRow, BlkCol=BlkCol)
  }
  
  # Put the whole field together, out of two rectangular fields if needed
  #fixed blkRowsExtra is not boolean
  if (blkRowsExtra > 0){ # Yes: two rectangular fields small on top of large
    layout1 <- makeFieldLayout(plot.start, blk.start=1, blkRow.start=1, nRowsPerBlk, nColsPerBlk, blkRowsExtra, nBlkCols)
    plot.start2 <- plot.start + nrow(layout1)
    blk.start2 <- nBlkCols * blkRowsExtra + 1
    blkRow.start2 <- blkRowsExtra + 1
    layout2 <- makeFieldLayout(plot.start2, blk.start=blk.start2, blkRow.start=blkRow.start2, nRowsPerBlk, nColsPerBlk, nBlkRows - blkRowsExtra, nBlkCols - 1)
    layout <- rbind(layout1, layout2)
  } else {
    #fixed by adding blkRow.start=1
    layout <- makeFieldLayout(plot.start, blk.start=1, blkRow.start=1, nRowsPerBlk, nColsPerBlk, nBlkRows, nBlkCols)
  }
  
  ## Assign plots to blocks
  # Start with a matrix, columns are blocks and rows are plots within blocks
  # Check numbers will be negative
  # Filler will be zero
  plotMat <- matrix(NA, nPlotsPerBlk, nBlks)
  plotMat[(nPlotsPerBlk+1) %/% 2,] <- -1 # Put in the primary check
  # Put in the secondary checks
  secChkCount <- 0
  for (blk in 1:nBlks){
    secChkPlots <- sample(which(is.na(plotMat[,blk])), nSecChkEachBlk[blk])
    plotMat[secChkPlots, blk] <- -secChkPool[secChkCount + 1:nSecChkEachBlk[blk]]
    secChkCount <- secChkCount + nSecChkEachBlk[blk]
  }
  # Put in fill plots
  if (!fillWithChk){
    for (blk in 1:nBlks) plotMat[sample(which(is.na(plotMat[,blk])), nFillEachBlk[blk]), blk] <- 0
  }
  # Put in entries
  plotMat[is.na(plotMat)] <- sample(nEntries)
  plotVec <- c(plotMat)
  
  layout$Chk <- ifelse(plotVec < 0, 1, 0)
  layout$LineCode <- plotVec
  layout$Entry <- ifelse(plotVec, NA, "Fill")
  layout$Entry[layout$Chk == 1] <- chk.names[-plotMat[layout$Chk == 1]]
  layout$Entry[plotMat > 0] <- entries[plotMat[plotMat > 0]]
  
  return(layout)
} ## End of design.mad function

message("MADII design")

nEntries <- length(trt)
num.checks <- length(trt2)
outdesign<- design.mad(entries=trt, nEntries=nEntries, nFieldRows=num_row, nFieldCols=num_col, chk.names=trt2, nRowsPerBlk=nRowsPerBlk, nColsPerBlk=nColsPerBlk)

plot<-rownames(outdesign)
trial <- 1:nrow(outdesign)
replication <- 1:nrow(outdesign)
subblock <- 1:nrow(outdesign)
treatment <- 1:nrow(outdesign)
block_tmt <- 1:nrow(outdesign)
subblock_tmt <- 1:nrow(outdesign)
trial[] <- exp
replication[] <- 1
subblock <- ""
treatment <- "" 
block_tmt <- "" 
subblock_tmt <- "" 
results <- data.frame(plot, trial, outdesign$Entry, outdesign$Row, outdesign$Col, outdesign$Plot, replication, outdesign$Blk, subblock, treatment, block_tmt, subblock_tmt, outdesign$Chk, stringsAsFactors = FALSE)
names(results) <- c("plot","trial","line_name","row","column","entry","replication","block","subblock","treatment","block_tmt","subblock_tmt","check")
write.table(results, file="fieldlayout.csv", sep=",", row.names = FALSE)

#write.table(outdesign, file="fieldlayout.csv", sep=",", row.names = FALSE)
