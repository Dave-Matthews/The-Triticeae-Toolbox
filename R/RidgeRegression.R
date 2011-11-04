# from Jeffrey Endelman, 26oct11
#this script will run ridge regression to calculate GEBVs
library(rrBLUP)

#first column of genotype file is GID
#each row is genotype for different line
#Genotypes encoded as {-1,0,1} for {aa,Aa,AA}
#Missing data should be encoded as NA
gen <- read.csv("genotypes.csv",header=T,row.names=1)

#impute using population mean
G <- impute(as.matrix(gen))
K <- tcrossprod(G)
GID <- row.names(gen)
n.GID <- length(GID)

#phenotype file has three columns with the following headers
# (1) GID
# (2) Trial
# (3) Y
phenotypes <- read.csv("phenotypes.csv",header=T,colClasses=c("factor","factor","numeric"))

train <- which(!is.na(phenotypes$Y))
n.train <- length(train)
unique.trial <- unique(phenotypes$Trial[train])
n.fix <- length(unique.trial)

X <- matrix(rep(0,n.train*n.fix),n.train,n.fix)
Z <- matrix(rep(0,n.train*n.GID),n.train,n.GID)

for (i in 1:n.train) {
        X[i,match(phenotypes$Trial[train[i]],unique.trial)] <- 1
        Z[i,match(phenotypes$GID[train[i]],GID)] <- 1
}

soln <- mixed.solve(y=phenotypes$Y[train],X=X,Z=Z,K=K,SE=TRUE)

# first column of output file is GID, 2nd col is GEBV, and 3rd col is SE
output <- data.frame(GID=GID,GEBV=soln$u,SE=soln$u.SE)
write.table(output,file="GEBV.csv",sep=",",row.names=FALSE)
