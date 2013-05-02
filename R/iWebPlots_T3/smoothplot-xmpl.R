# Example from the doc, http://cran.r-project.org/web/packages/iWebPlots/iWebPlots.pdf

#library("iWebPlots")
source("openHtmlPage.R")
source("imageMap.R")
source("iSmoothPlot.r")

# Example 1
n <- 500
x1 <- matrix(rnorm(n), ncol=2)
x2 <- matrix(rnorm(n, mean=3, sd=1.5), ncol=2)
x <- rbind(x1,x2)
iSmoothPlot(x, dataPoints=rownames(x),
fileName="smoothPlot1", directory="smoothPlot1",
fpng="smoothPlot1", main="Smooth Scatter Plot")
