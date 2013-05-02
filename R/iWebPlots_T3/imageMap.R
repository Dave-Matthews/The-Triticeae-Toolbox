setGeneric("imageMap", function(object, con, tags, imgname, ...)
           standardGeneric("imageMap"))


setOldClass(c("file", "connection"))
setMethod("imageMap",
  signature=signature(object="matrix", con="connection", tags="list",
    imgname="character"),
  definition=function(object, con, tags, imgname) {
    
  if(!is.matrix(object)||ncol(object)!=4)
    stop("'object' must be a matrix with 4 columns.")

  for(i in seq(along=tags))
    if(length(tags[[i]])!=nrow(object))
      stop(paste("'tags[[", i, "]] must have as many elements as 'object' has rows (",
                 nrow(object),").", sep=""))

  mapname <- paste("map", gsub(" |/|#", "_", imgname), sep="_")
  base::writeLines(paste("<IMG SRC=\"", imgname, "\" USEMAP=\"#", mapname, "\" BORDER=0/>", 
                   "<MAP NAME=\"", mapname, "\">", sep=""), con)
  for(i in 1:nrow(object)) {
    out = paste("<AREA SHAPE=\"rect\" COORDS=\"", paste(object[i,], collapse=","),
                "\"", sep="")
    for(t in seq(along=tags))
      out = paste(out, " ", names(tags)[t], "=\"", tags[[t]][i], "\"", sep="")
    out = paste(out, ">", sep="")
    base::writeLines(out, con)
  }
  base::writeLines("</MAP>", con)
} ## end of definition
) ## end of setMethod
