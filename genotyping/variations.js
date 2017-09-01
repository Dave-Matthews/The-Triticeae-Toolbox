var php_self = document.location.href;

function reload()
{
    var $assembly = document.getElementById("assembly").value;
    window.location.href = "genotyping/variations.php?assembly=" + $assembly;
}
