library(agricolae)

#defined dynamically
#type
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

if (type == "alpha") {
  message("alpha design")
  t<- length(trt)
  s<- t/k
  message("t=",t)
  message("s=",s)
  outdesign<-design.alpha(trt, k, r, serie=serie)
} else if (type == "bib") {
  message("randomized balanced incomplete block design")
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
  rowNum[i] <- ""
  colNum[i]<- ""
}

#get check lines and set others to empty
checkLine <- c()
subblock <- c()
treatment <- c()
block_tmt <- c()
subblock_tmt <- c()
for (i in 1:length(outdesign$book$plot)) {
  LineName <- line_name[i]
  if (exists("trt2")) {
    if (LineName %in% trt2) {
      checkLine[i] <- 1
    } else {
      checkLine[i] <- 0
    } 
  } else {
      checkLine[i] <- 0
  }
  subblock[i] <- "" 
  treatment[i] <- "" 
  block_tmt[i] <- "" 
  subblock_tmt[i] <- ""
}

replication<-outdesign$book[,2]
if (is.null(outdesign$book$block)) {
  block<-1:dim(outdesign$book)[1]
} else {
  block<-outdesign$book$block
}
results <- data.frame(plot, trial, line_name, rowNum, colNum, entry, replication, block, subblock, treatment, block_tmt, subblock_tmt, checkLine, stringsAsFactors = FALSE)
names(results) <- c("plot","trial","line_name","row","column","entry","replication","block","subblock","treatment","block_tmt","subblock_tmt","check")
write.table(results, file=outfile, quote=FALSE, sep = ",", row.names = FALSE)

