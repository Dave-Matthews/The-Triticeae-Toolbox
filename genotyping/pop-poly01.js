var php_self = document.location.href;

function update_side()
{
    var url = "side_menu.php";
    jQuery.get(url, function( data ) {
        jQuery("#quicklinks").html( data );
    });
}

function select_chrom() {
    var chrom = document.getElementById("chrom").value;
    var start = document.getElementById("start").value;
    var stop = document.getElementById("stop").value;
    var url = php_self + "?function=chrom&value=" + chrom + "&start=" + start + "&stop=" + stop;
    jQuery.get(url, function( data ) {
        jQuery("#step2").html( data );
    });
}

function save() {
    var url = php_self + "?function=save";
    jQuery.get(url, function( data ) {
        jQuery("#step2").html( data );
        update_side();
    });
}
