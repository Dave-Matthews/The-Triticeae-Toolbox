/*global $,Ajax,Element,window*/

var php_self = document.location.href;

function update_database(filename, mapsetname, mapsetprefix, comments, species, map_type, map_unit)
{
    var url = php_self + '?function=typeDatabase&file_name=' + filename + '&mapset_name=' + mapsetname + '&mapset_prefix=' + mapsetprefix + '&comments=' + comments + '&species=' + species + '&map_type=' + map_type + '&map_unit=' + map_unit;
    window.open(url, "_self");
}
