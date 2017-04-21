function output_file(url) {
        window.open(url);
}
function output_file2(puid) {
    url = "download_phenotype.php?function=downloadMean&pi=" + puid;
    window.open(url);
}

function output_file_plot(puid) {
    url = "download_phenotype.php?function=downloadPlot&pi=" + puid;
    window.open(url);
}
