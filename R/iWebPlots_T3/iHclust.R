iHclust <- function(x, fileName, directory, ...) UseMethod("iHclust")


iHclust.default <- function(x, fileName, directory, fpng="dendrogram", labels = NULL, hang = -1, dataPoints=NULL, ...)
{	
	if((missing(directory)) || (is.null(directory)))
	{
		warning("You have to provide a valid path or directory.")
		
	} else if((missing(fileName)) || (is.null(fileName)))
	{
		warning("You have to provide a file name (without the extention \".html\").")
		
	} else
	{
		initialDir = getwd()
		dir.create(directory, recursive = TRUE, mode = "0777", showWarnings = FALSE)
		setwd(directory)
		fullDir = getwd()
		setwd(initialDir)
		
		if(is.null(fpng))
		{
			fpng= "dendrogram"
		}
		
		if(is.null(dataPoints))
		{
			dataPoints = x$labels[x$order]
		}
		
		cx = seq(0.5, (length(x$labels)-0.5), by=1)
		cy = rep.int(0, length(x$labels))
		cxx= rowSums(cbind(cx, rep.int(1, length(x$labels))))
		cyy= apply(as.matrix(x$labels), 1, nchar)
		
		fpng = paste(fpng,".png", sep="")
		png(paste(directory, fpng, sep="/"), width=850, height=570 , bg="transparent")
		plot(x, hang = -1, labels= labels, ...)
#		axis(1, at=seq(1, length(x$labels), by=1))
		cx2d = grconvertX(cx , "user", "device")
		cy2d = grconvertY(cy , "user", "device")
		cxx2d = grconvertX(cxx , "user", "device")
		cyy2d = grconvertY(cy , "user", "device")
		dev.off()
		
		coords2d = cbind(cx2d, cy2d, cxx2d, cyy2d+100)
		
		id = seq(1:nrow(coords2d))
		
		con = openHtmlPage(paste(directory, fileName, sep="/"), title = "Interactive Hierarchical Clustering")
		
		writeLines("<div id='main'>",con)
		writeLines("<div id='content'>",con)
		
		writeLines("<div class='imageMap' id='iHclust'>",con)
		imageMap(coords2d, con, list(ID=id, NAME=dataPoints, HREF=paste("#", dataPoints, sep="")), fpng)
		writeLines("</div>",con)
		
		writeLines("<form id='selection'></form>",con)
		
		writeLines("<div id='classes'>",con)
		writeLines("</div>",con)
		
		writeLines("<div id='tooltip'>",con)
		writeLines("<div class='tipHeader'>Data point</div>",con)
		writeLines("<div class='tipBody'></div>",con)
		writeLines("<div class='tipFooter'></div>",con)
		writeLines("</div>",con)
		
		writeLines("</div>",con)
		writeLines("</div>",con)
		
		writeLines("<script src='js/jquery.js'></script>", con)
		writeLines("<script src='js/imageMaps.js'></script>", con)
		closeHtmlPage(con)
		
		file.copy(system.file("js", package="iWebPlots"), directory, overwrite=TRUE, recursive=TRUE)
		file.copy(system.file("css", package="iWebPlots"), directory, overwrite=TRUE, recursive=TRUE)
		
		message(paste("The generated folder is located at ", fullDir, "",sep="\""))
	}
}



classify.iHclust <- function(x, classes, classColors=NULL, classNames=NULL, fileName, directory, fpng="dendrogram", dataPoints=NULL, ...)
{	
	if((missing(directory)) || (is.null(directory)))
	{
		warning("You have to provide a valid path or directory.")
		
	} else if((missing(fileName)) || (is.null(fileName)))
	{
		warning("You have to provide a file name (without the extention \".html\").")
		
	} else if((missing(classes)) || (is.null(classes)))
	{
		warning("You have to provide a vector containing the classes of x.")
		
	}else
	{
		initialDir = getwd()
		dir.create(directory, recursive = TRUE, mode = "0777", showWarnings = FALSE)
		setwd(directory)
		fullDir = getwd()
		setwd(initialDir)
		
		classes = as.factor(classes)
		classLevels = levels(classes)
		classNum = length(levels(classes))
		
		
		if(is.null(fpng))
		{
			fpng= "dendrogram"
		}
		
		if(is.null(dataPoints))
		{
			dataPoints = x$labels[x$order]
		}
		
		if(is.null(classColors))
		{
			classColors = rainbow(classNum)
		}
		
		if(is.null(classNames))
		{
			for (i in 1:classNum)
			{
				classNames = c(classNames, paste("Class", i, sep=""))
			}
		}
		
		cx = seq(0.5, (length(x$labels)-0.5), by=1)
		cy = rep.int(0, length(x$labels))
		cxx= rowSums(cbind(cx, rep.int(1, length(x$labels))))
		cyy= apply(as.matrix(x$labels), 1, nchar)
		
		fpng = paste(fpng,".png", sep="")
		png(paste(directory, fpng, sep="/"), width=850, height=570 , bg="transparent")
		plot(x, hang = -1, ...)
		cx2d = grconvertX(cx , "user", "device")
		cy2d = grconvertY(cy , "user", "device")
		cxx2d = grconvertX(cxx , "user", "device")
		cyy2d = grconvertY(cy , "user", "device")
		
		for (i in 1:classNum)
		{
			classVec = which(classes == classLevels[i])
			temp = match(classVec,x$order)
			rect(cx[temp], -(max(x$height)/40), cxx[temp], cy[temp], col=classColors[i])
		}	
		
		legend("topright", title="Data", legend=classNames, fill=classColors)
		
		
		dev.off()
		
		coords2d = cbind(cx2d, cy2d, cxx2d, cyy2d+100)
		
		id = seq(1:nrow(coords2d))
		
		con = openHtmlPage(paste(directory, fileName, sep="/"), title = "Interactive Hierarchical Clustering")
		
		writeLines("<div id='main'>",con)
		writeLines("<div id='content'>",con)
		
		writeLines("<div class='imageMap' id='iHclust'>",con)
		imageMap(coords2d, con, list(ID=id, NAME=dataPoints, HREF=paste("#", dataPoints, sep="")), fpng)
		writeLines("</div>",con)
		
		writeLines("<form id='selection'></form>",con)
		
		writeLines("<div id='classes'>",con)
		writeLines("</div>",con)
		
		writeLines("<div id='tooltip'>",con)
		writeLines("<div class='tipHeader'>Data point</div>",con)
		writeLines("<div class='tipBody'></div>",con)
		writeLines("<div class='tipFooter'></div>",con)
		writeLines("</div>",con)
		
		writeLines("</div>",con)
		writeLines("</div>",con)
		
		writeLines("<script src='js/jquery.js'></script>", con)
		writeLines("<script src='js/imageMaps.js'></script>", con)
		closeHtmlPage(con)
		
		file.copy(system.file("js", package="iWebPlots"), directory, overwrite=TRUE, recursive=TRUE)
		file.copy(system.file("css", package="iWebPlots"), directory, overwrite=TRUE, recursive=TRUE)
		
		message(paste("The generated folder is located at ", fullDir, "",sep="\""))
	}
}
