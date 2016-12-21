library(lsmeans)

#oneCol is read in from a file by the calling script, table.php.
colnames(oneCol) <- c("trait", "trial", "lineName", "value")

fitLMbyTrait <- function(trait, data){
  data <- data[data$trait == trait,]
  fitLM <- lm(value ~ trial + lineName, data=data)
  return(fitLM)
}

calcLSmeanParms <- function(fitLM){
  lineLSmeans <- summary(lsmeans(fitLM, "lineName"))
  meanSE <- mean(lineLSmeans$SE)
  meanDF <- mean(lineLSmeans$df)
  leastSigDiff <- sqrt(2)*meanSE*qt(1 - 0.025, meanDF)
  tukeysHSD <- meanSE*qtukey(1 - 0.05, nrow(lineLSmeans), meanDF) # Tukey is already two-sided
  trialLSmeans <- summary(lsmeans(fitLM, "trial"))
  return(list(lsmeans=lineLSmeans$lsmean, leastSigDiff=leastSigDiff, tukeysHSD=tukeysHSD, trialNames=levels(trialLSmeans$trial), trialMeans=trialLSmeans$lsmean))
}

analyzeTrait <- function(trait){
  fitLM <- fitLMbyTrait(trait, oneCol)
  return(calcLSmeanParms(fitLM))
}

summaryTrait <- function(trait){
  data <- oneCol[oneCol$trait == trait,]
  trialMeans <- mean(data$value)
  return(list(trialNames=levels(data$trial), trialMeans=trialMeans))
}

traitsVec <- unique(oneCol$trait)
trialVec <- unique(oneCol$trial)
if (length(trialVec) > 1) {
  tableReportParms <- sapply(traitsVec, analyzeTrait)
} else {
  tableReportParms <- sapply(traitsVec, summaryTrait)
}
# outFile is defined by the calling script as "TableReportOut.txt".$time.
write.table(tableReportParms, file=outFile, quote=FALSE, sep="\t")

