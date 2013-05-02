# iPlot.R, dem 20apr13, from:
# iLines.R, dem 19apr13, from iScatter2D.R

# First define function imageMap().
imageMap = function(object, htfile, tags, imgname) {
  # object: array of [left, top, right, bottom] coordinates for each point.
  # htfile: filepath to write the HTML imagemap markup to.
  # tags: vector of ID information for each point.
  # imgname: path and name of the .png image file.
  for(i in seq(along=tags))
    if(length(tags[[i]]) != nrow(object))
      stop(paste("'tags[[", i, "]] must have as many elements as 'object' has rows (", nrow(object),").", sep=""))
    mapname <- paste("map", gsub(" |/|#", "_", imgname), sep="_")
    writeLines(paste("<IMG SRC='", imgname, "' USEMAP='#", mapname, "' >", 
		     "<MAP NAME='", mapname, "'>", sep=""), htfile)
    for(i in 1:nrow(object)) {
       out = paste("<AREA SHAPE='rect' COORDS='", paste(object[i,], collapse=","), "'", sep="")
       for(t in seq(along=tags))
	 out = paste(out, " ", names(tags)[t], "='", tags[[t]][i], "'", sep="")
       out = paste(out, ">", sep="")
       writeLines(out, htfile)
   }
  writeLines("</MAP>", htfile)
}

  iPlot = function(x, directory, fpng, dataPoints=NULL) {
    # x: array of (color,x,y) coordinates of the points.
    # directory: location of the output files, e.g. /tmp/tht.
    # fpng: name for the .png file, without the .png extension.
    # dataPoints: text label to pop up for each point.
    fileName = paste(fpng, ".html", sep="")
    fpng = paste(fpng,".png", sep="")
    if (is.null(dataPoints)) {
	if (is.null(rownames(x))) 
	    dataPoints = seq(1:nrow(x))
	else 
	    dataPoints = rownames(x)
    }
    # Draw the graph.
    png(paste(directory, fpng, sep="/"), width=600, height=500 , bg="white")
    plot(x[,2], x[,3], col=x[,1], pch=19, xlab=colnames(x)[2], ylab=colnames(x)[3])
    cx2d = grconvertX(x[,2] , "user", "device")
    cy2d = grconvertY(x[,3] , "user", "device")
    dev.off()
    # Create the <map><area> coordinate data.
    coords2d = cbind(cx2d-2, cy2d-2, cx2d+2, cy2d+2)
    id = seq(1:nrow(coords2d))
    outfile = file(paste(directory, fileName, sep="/"), open="wt")
    writeLines("<div class='imageMap' id='imageMap2d'>", outfile)
    # Use HREF to make the clicked points jump to an URL.
    # imageMap(coords2d, outfile, list(ID=id, NAME=dataPoints, HREF=paste("#", dataPoints, sep="")), paste(directory, fpng, sep="/"))
    imageMap(coords2d, outfile, list(ID=id, NAME=dataPoints), paste(directory, fpng, sep="/"))
    writeLines("</div>", outfile)
    writeLines("<div id='tooltip'>", outfile)
    # Prepend a label in the tooltip box.
    # writeLines("<div class='tipHeader'>Data point</div>", outfile)
    writeLines("<div class='tipBody'></div>", outfile)
    writeLines("</div>", outfile)
  }     

