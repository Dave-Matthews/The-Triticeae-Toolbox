library(agricolae)

#defined dynamically
#desing_type
#trt, size_block, seed, num_repl, num_block
#outfile

NumLines <- length(trt)
if (NumLines < 100) {
  serie <- 2
} else if (NumLines < 1000) {
  serie <- 3
} else if (NumLines < 10000) {
  serie <- 4
}

MADIIdgn <- function(enviro="Eretz", entries= NULL, num.entries= NULL, chk.names= NULL, num.sec.chk= NULL, num.rows= NULL, num.cols= NULL,  plot.start = 1001, designID=NULL, annoy=T){

  unlink(designID, recursive=T)
  dir.create(path=designID, recursive=F)
  
  ## Load necessary packages
  require("grid", quietly=T) ; library(grid)

  ## QC of function inputs
  #JL why are these only warnings and not errors that cause the function to stop?
  if(is.null(entries) & is.null(num.entries)){
    warning("Must provide an entry list (entries=) OR the number of entries desired (num.entries=).")
  }

  if(is.null(chk.names) & is.null(num.sec.chk)){
    warning("Must provide a list of check names (chk.names=) with the primary check listed first\n OR the number of SECONDARY checks desired (num.sec.chk=).")
  }

  if(is.null(num.rows)){
    warning("Provide the number of rows (sometimes called ranges or beds) (num.rows=).")
  }

  if(num.rows %% 3 != 0){
    warning("The MADII design requires that the number of rows be a multiple of 3.")
  }


  ## Develop other non-input function parameters
  if(is.null(entries)){
    entries <- as.matrix(paste("entry", 1:num.entries, sep="_"))
  }

  if(is.null(num.entries)){
    entries <- as.matrix(entries) # If user input of entries was a list, will convert it to matrix
    num.entries <- nrow(entries)
  }


  ## This warning is dependent on the number of entries
  if(is.null(num.cols)==F){
    if(((num.cols / 5) * (num.rows / 3) + num.entries) > num.cols*num.rows){
      warning("The minimum number of plots has not been met by the given row/column dimensions.")
    }
  }

  if(is.null(chk.names)){
    sec.chks <- as.character(2:(num.sec.chk+1)) ## Do need as seperate object for later on in function
    chk.names <- paste("chk", c(1,sec.chks), sep="") ## All generic check names
  }

  if(is.null(num.sec.chk)){
    sec.chks <- chk.names[-1] ## The primary check must be listed first in the function input
    num.sec.chk <- length(sec.chks)
  }
 
  blk.rows <- num.rows / 3 # This is the standard for the MADII design

  ## If the number of columns is provided then it is straight forward, otherwise a set of algorithms will develop and optimize the paramaters
  if(is.null(num.cols)==F){

    if(num.cols%%5 != 0){
      warning("The MADII design requires that the number of columns be a multiple of 5.")
    }
   
    blk.cols <- num.cols / 5 # This is the standard for the MADII design

    num.blks <- blk.rows * blk.cols
    exp.size <- num.blks * 15
    num.chks <- exp.size - num.entries
    #JL is there a rule that if a block has secondary checks, then it must have _all_ secondary checks?
    # That would not seem like a good rule.  Maybe it is required by some MADII analyses but it would not be
    # required by the moving average analysis or by an AR1 analysis, and my guess is those are better.
    num.sec.chk.blks <- floor((num.chks - num.blks) / num.sec.chk) # one primary check in each block
    # Number of checks to total number of plots: I think per.chks gets awfully high depending on the design.  I think there
    # should be no more than two check plots per block of 15.  It's just too much effort otherwise.
    per.chks <- (num.blks + (num.sec.chk.blks*num.sec.chk)) / (num.entries + num.blks + (num.sec.chk.blks*num.sec.chk))
    num.fill <- exp.size - (num.blks + num.entries + num.sec.chk.blks*num.sec.chk) # Fill lines are empty plots at the end of the experiment
    #JL do Fill plots actually get planted to something?  I am thinking that they would be.  If they are, it would seem better to put secondary checks in them and distribute them randomly around the experiment

    if(is.null(designID)==F){
      write.table(per.chks, paste(designID, "/", "%checks_in_", designID, ".txt", sep=""))
    }else{
      write.table(per.chks, "%checks.txt")
    }
   

  }else{
    ## If the number of columns is not specified, below algorithms will develop the necessary design
    ### Calculate starting (non-optimized paramaters)
    per.chks <- 0.10 #JL yes, this is a good number

    ## Number of total checks in experiment (primary + secondary) ; calculated as percent of entries
    num.chks <- ceiling((per.chks * num.entries) / (1-per.chks))
    entries.plus.chks <- num.entries + num.chks

    num.cols <- ceiling(entries.plus.chks / num.rows)
    #JL num.cols <- ceiling(num.cols / 5) * 5
    # so the statement above could just be "num.cols <- ceiling(entries.plus.chks / num.rows / 5) * 5"
    while(num.cols %% 5 != 0){
      num.cols <- num.cols + 1
    }
   
    blk.cols <- num.cols / 5 # This is the standard for the MADII design
    num.blks <- blk.rows * blk.cols
    exp.size <- num.blks*15 # 15 plots per block
    num.sec.chk.blks <- ceiling((num.chks - num.blks) / num.sec.chk) # one primary check in each block

    ## If the ratio of blk.cols to num.sec.chk.blks does not allow each blk.col to have a sec.chk blk in it, then optimize per.chks
    while((blk.cols > num.sec.chk.blks) & (num.sec.chk.blks <  num.blks)){
      per.chks <- per.chks + 0.0001
      num.chks <- ceiling((per.chks * num.entries) / (1-per.chks))
      entries.plus.chks <- num.entries + num.chks

      num.cols <- ceiling(entries.plus.chks / num.rows)

      while(num.cols %% 5 != 0){
        num.cols <- num.cols + 1
      }

      blk.cols <- num.cols / 5 # This is the standard for the MADII design
      num.blks <- blk.rows * blk.cols
      exp.size <- num.blks*15 # 15 plots per block
      num.sec.chk.blks <- ceiling((num.chks - num.blks) / num.sec.chk) # one primary check in each block

    }
   
    num.fill <- num.blks*15 - (num.blks + num.entries + num.sec.chk.blks*num.sec.chk) # Fill lines are empty plots at the end of the experiment

    ## Increase number of checks to minimize number of Fill plots
    while(num.fill >= num.sec.chk & (num.sec.chk.blks <  num.blks)){
      per.chks <- per.chks + 0.0001
      num.chks <- ceiling((per.chks * num.entries) / (1-per.chks))
      entries.plus.chks <- num.entries + num.chks

      num.sec.chk.blks <- floor((num.chks - num.blks) / num.sec.chk) # one primary check in each block

      num.fill <-num.blks*15 - (num.blks + num.entries + num.sec.chk.blks*num.sec.chk) # Fill lines are empty plots at the end of the experiment

    }
   
    if(is.null(designID)==F){
      write.table(per.chks, paste(designID, "/", "%checks_in_", designID, ".txt", sep=""))
    }else{
      write.table(per.chks, "%checks.txt")
    }
 
  }

  
}

if (type == "alpha") {
  message("alpha design")
  t<- length(trt)
  s<- t/k
  message("t=",t)
  message("s=",s)
  outdesign<-design.alpha(trt, k, r, serie=serie)
} else if (type == "bib") {
  message("randomized balanced imcompleate block design")
  outdesign<- design.bib(trt, k, serie=serie)
} else if (type == "crd") {
  message("completely randomized design")
  outdesign<- design.crd(trt, r, serie=serie)
} else if (type == "lattice") {
  message("lattice designs")
  outdesign<- design.lattice(trt, r, serie=serie)
} else if (type == "dau") {
  message("Augmented block design")
  outdesign<- design.dau(trt2, trt, r, serie=serie)
} else if (type == "rcbd") {
  message("randomized complete block design")
  outdesign<- design.rcbd(trt, r, serie=serie)
} else if (type == "madii") {
  message("MADII design")
  outdesign<- MADIIdgn(num.entries=90, num.rows=9, num.cols=NULL, num.sec.chk=3, designID="tester1", annoy=T)
} else {
  message("Error: Invalied design type")
}

plot<-rownames(outdesign$book)
trial<-1:dim(outdesign$book)[1]
trial[]<- exp
line_name<- outdesign$book$trt
entry<- outdesign$book$plot

#get row and column from plot column
rowNum <- c() 
colNum <- c()
for (i in 1:length(outdesign$book$plot)) {
  plotval <- outdesign$book$plot[i]
  rowNum[i] <- substr(plotval, 1, nchar(plotval)-serie)
  colNum[i]<- substr(plotval, nchar(plotval)-serie+1, nchar(plotval))
}
replication<-outdesign$book[,2]
if (is.null(outdesign$book$block)) {
  block<-1:dim(outdesign$book)[1]
} else {
  block<-outdesign$book$block
}
results <- data.frame(plot, trial, line_name, rowNum, colNum, entry, replication, block, stringsAsFactors = FALSE)
names(results) <- c("plot","trial","line_name","row","column","entry","replication","block")
write.table(results, file=outfile, quote=FALSE, sep = ",", row.names = FALSE)

