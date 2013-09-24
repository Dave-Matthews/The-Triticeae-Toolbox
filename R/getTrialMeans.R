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
hasRep <- all(!is.na(plotData$replication)) & (length(unique(plotData$replication)) > 1)
hasBlk <- all(!is.na(plotData$block)) & (length(unique(plotData$block)) > 1)
result <- NULL
stdErr <- NULL
trialMean <- NULL
trialReps <- NULL
if (!hasBlk){ # Fixed effects models
  if (!hasRep){
    message("CRD model: use simple averages");
    for (trait in dataCols){
      notMiss <- tapply(df[,trait], df$line, function(vec) !all(is.na(vec)))
      notMiss <- levels(df$line)[notMiss]
      
      model <- paste(trait, "~ line")
      test <- lm(model, data=df)
      beta <- test$coefficients
      names(beta) <- gsub("line", "", names(beta))
      nLine <- length(notMiss)
      grandMean <- beta[1] + sum(beta[2:nLine]) / nLine
      resTrait <- rep(NA, nlevels(df$line))
      names(resTrait) <- levels(df$line)
      resTrait[c(min(notMiss), names(beta)[2:nLine])] <- beta[1] + c(0, beta[2:nLine])
      result <- cbind(result, resTrait)

      averageReps <- sum(!is.na(df[,trait])) / nLine
      stdErr <- c(stdErr, sqrt(anova(test)[2,"Mean Sq"] / averageReps))
      trialMean <- c(trialMean, grandMean)
      trialReps <- c(trialReps, round(averageReps, 1))
    }
  } else{
    message("RCBC model: use fixed effects and return LS means");
    for (trait in dataCols){
      notMiss <- tapply(df[,trait], df$line, function(vec) !all(is.na(vec)))
      notMiss <- levels(df$line)[notMiss]

      model <- paste(trait, "~ rep + line")
      test <- lm(model, data=df)
      beta <- test$coefficients
      names(beta) <- gsub("line", "", names(beta))
      # Adjust to a recognizable mean
      nRep <- length(test$xlevels$rep)
      nLine <- length(notMiss)
      grandMean <- beta[1] + sum(beta[2:nRep]) / nRep + sum(beta[(nRep + 1):length(beta)]) / nLine
      resTrait <- rep(NA, nlevels(df$line))
      names(resTrait) <- levels(df$line)
      resTrait[c(min(notMiss), names(beta)[(nRep + 1):length(beta)])] <- beta[1] + sum(beta[2:nRep]) / nRep + c(0, beta[(nRep + 1):length(beta)])
      result <- cbind(result, resTrait)

      averageReps <- sum(!is.na(df[, trait])) / nLine
      stdErr <- c(stdErr, sqrt(anova(test)[3,"Mean Sq"] / averageReps))
      trialMean <- c(trialMean, grandMean)
      trialReps <- c(trialReps, round(averageReps, 1))
    }
  }
} else{
  library(lme4)
  if (!hasRep){
    message("no-name model: blocks as random effects with no replication effect")
    for (trait in dataCols){
      notMiss <- tapply(df[,trait], df$line, function(vec) !all(is.na(vec)))
      notMiss <- levels(df$line)[notMiss]

      model <- paste(trait, "~ line + (1 | block)")
      test <- lmer(model, data=df)
      beta <- fixef(test)
      names(beta) <- gsub("line", "", names(beta))
      # Adjust to a recognizable mean
      nLine <- length(notMiss)
      grandMean <- beta[1] + sum(beta[2:nLine]) / nLine
      resTrait <- rep(NA, nlevels(df$line))
      names(resTrait) <- levels(df$line)
      resTrait[c(min(notMiss), names(beta)[2:nLine])] <- beta[1] + c(0, beta[2:nLine])
      result <- cbind(result, resTrait)

      averageReps <- sum(!is.na(df[, trait])) / nLine
      stdErr <- c(stdErr, attr(VarCorr(test), "sc")^2 / averageReps)
      trialMean <- c(trialMean, grandMean)
      trialReps <- c(trialReps, round(averageReps, 1))
    }
  } else{
    message("Incomplete block model: Replications fixed and blocks random")
    for (trait in dataCols){
      notMiss <- tapply(df[,trait], df$line, function(vec) !all(is.na(vec)))
      notMiss <- levels(df$line)[notMiss]

      model <- paste(trait, "~ rep + line + (1 | block)")
      test <- lmer(model, data=df)
      beta <- fixef(test)
      names(beta) <- gsub("line", "", names(beta))
      # Adjust to a recognizable mean
      nRep <- length(grep("rep", names(beta)))+1
      nLine <- length(notMiss)
      grandMean <- beta[1] + sum(beta[2:nRep]) / nRep + sum(beta[(nRep + 1):length(beta)]) / nLine
      resTrait <- rep(NA, nlevels(df$line))
      names(resTrait) <- levels(df$line)
      resTrait[c(min(notMiss), names(beta)[(nRep + 1):length(beta)])] <- beta[1] + sum(beta[2:nRep]) / nRep + c(0, beta[(nRep + 1):length(beta)])
      result <- cbind(result, resTrait)

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
