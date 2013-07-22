# NOTE: There cannot be spaces in trait names.
plotData <- read.table("plot-pheno.txt", sep=",", header=TRUE, stringsAsFactors=FALSE)
file_out <- "mean-output.txt"
# Analyses to do as a function of the existence of Replication and Block factors:
# Replication Block   Analysis
# NA          NA      CRD model: use simple averages
# !NA         NA      RCBC model: use fixed effects and return LS means
# NA          !NA     no-name model: blocks as random effects with no replication effect
# !NA         !NA     Incomplete block model: Replications fixed and blocks random
dataCols <- which(!(names(plotData) %in% c("line", "plot", "replication", "block", "subblock", "treatment")))
df <- data.frame(line=as.factor(plotData$line), rep=as.factor(plotData$replication), block=as.factor(paste(plotData$replication, plotData$block, sep=".")), plotData[,dataCols])
dataCols <- names(plotData)[dataCols]
hasRep <- all(!is.na(plotData$replication))
hasBlk <- all(!is.na(plotData$block))
result <- NULL
stdErr <- NULL
trialMean <- NULL
trialReps <- NULL
if (!hasBlk){ # Fixed effects models
  if (!hasRep){
    message("CRD model: use simple averages");
    for (trait in dataCols){
      model <- paste(trait, "~ line")
      test <- lm(model, data=df)
      beta <- test$coefficients
      nLine <- length(test$xlevels$line)
      grandMean <- beta[1] + sum(beta[2:nLine]) / nLine
      result <- cbind(result, beta[1] + c(0, beta[2:nLine]))
      averageReps <- sum(!is.na(df[, trait])) / nLine
      stdErr <- c(stdErr, sqrt(anova(test)[2,"Mean Sq"] / averageReps))
      trialMean <- c(trialMean, grandMean)
      trialReps <- c(trialReps, round(averageReps, 1))
    }
  } else{
    message("RCBC model: use fixed effects and return LS means");
    for (trait in dataCols){
      model <- paste(trait, "~ rep + line")
      test <- lm(model, data=df)
      beta <- test$coefficients
      # Adjust to a recognizable mean
      nRep <- length(test$xlevels$rep)
      nLine <- length(test$xlevels$line)
      grandMean <- beta[1] + sum(beta[2:nRep]) / nRep + sum(beta[(nRep + 1):length(beta)]) / nLine
      result <- cbind(result, beta[1] + sum(beta[2:nRep]) / nRep + c(0, beta[(nRep + 1):length(beta)]))
      averageReps <- sum(!is.na(df[, trait])) / nLine
      stdErr <- c(stdErr, sqrt(anova(test)[3,"Mean Sq"] / averageReps))
      trialMean <- c(trialMean, grandMean)
      trialReps <- c(trialReps, round(averageReps, 1))
    }
  }
} else{
  library(lme4)
  if (!hasRep){
    for (trait in dataCols){
      model <- paste(trait, "~ line + (1 | block)")
      test <- lmer(model, data=df)
      beta <- fixef(test)
      # Adjust to a recognizable mean
      nLine <- length(grep("line", names(beta)))+1
      grandMean <- beta[1] + sum(beta[2:nLine]) / nLine
      result <- cbind(result, beta[1] + c(0, beta[2:nLine]))
      averageReps <- sum(!is.na(df[, trait])) / nLine
      stdErr <- c(stdErr, attr(VarCorr(test), "sc")^2 / averageReps)
      trialMean <- c(trialMean, grandMean)
      trialReps <- c(trialReps, round(averageReps, 1))
    }
  } else{
    for (trait in dataCols){
      model <- paste(trait, "~ rep + line + (1 | block)")
      test <- lmer(model, data=df)
      beta <- fixef(test)
      # Adjust to a recognizable mean
      nRep <- length(grep("rep", names(beta)))+1
      nLine <- length(grep("line", names(beta)))+1
      grandMean <- beta[1] + sum(beta[2:nRep]) / nRep + sum(beta[(nRep + 1):length(beta)]) / nLine
      result <- cbind(result, beta[1] + sum(beta[2:nRep]) / nRep + c(0, beta[(nRep + 1):length(beta)]))
      averageReps <- sum(!is.na(df[, trait])) / nLine
      stdErr <- c(stdErr, attr(VarCorr(test), "sc")^2 / averageReps)
      trialMean <- c(trialMean, grandMean)
      trialReps <- c(trialReps, round(averageReps, 1))
    }
  }
}
rownames(result) <- levels(df$line)
colnames(result) <- dataCols
write.table(result, file=file_out, quote=FALSE)
metaData <- data.frame(trialMean=trialMean, stdError=stdErr, replications=trialReps)
rownames(metaData) <- dataCols
write.table(metaData, file="metaData.txt", quote=FALSE)
