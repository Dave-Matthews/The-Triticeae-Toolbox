pheno <- as.matrix(phenoData[,3])
trial <- as.matrix(phenoData[,2])

#remove missing
hasPheno <- !is.na(pheno)
pheno <- pheno[hasPheno]
trial <- trial[hasPheno]
unq.trial <- unique(trial)
if (length(unq.trial) > 1) {
  div <- length(unq.trial)
  par(mfrow=c(1,div))
  for (i in 1:length(unq.trial)) {
    exper <- unq.trial[i]
    phenos <- NULL
    for (j in 1:length(trial)) {
      if (trial[j] == exper) {
        phenos <- c(phenos, pheno[j])
      } 
    }
    trialname <- triallabel[exper]
    mainlabel <- paste(trialname,"\n",phenolabel)
    hist(phenos, main=mainlabel, xlab=phenounit)
  }
} else {
  exper <- unq.trial[1]
  trialname <- triallabel[exper]
  mainlabel <- paste(trialname,"\n",phenolabel)
  hist(pheno, main=mainlabel, xlab=phenounit)
}
