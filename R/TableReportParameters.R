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
  return(list(lsmeans=lineLSmeans$lsmean, leastSigDiff=leastSigDiff, tukeysHSD=tukeysHSD, trialMeans=trialLSmeans$lsmean))
}

analyzeTrait <- function(trait){
  fitLM <- fitLMbyTrait(trait, oneCol)
  return(calcLSmeanParms(fitLM))
}

traitsVec <- unique(oneCol$trait)
tableReportParms <- sapply(traitsVec, analyzeTrait)
# outFile is defined by the calling script as "TableReportOut.txt".$time.
write.table(tableReportParms, file=outFile, quote=FALSE, sep="\t")

