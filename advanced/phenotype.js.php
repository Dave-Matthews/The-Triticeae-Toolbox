<?php
require 'config.php';
?>
visiblePhenotypeSelect = null;
function getPhenotypeSelect(id)
{
    $('descript').update();
    if (visiblePhenotypeSelect != null)
    {
        visiblePhenotypeSelect.hide();
    }
    if (id == '-1')
    {
        $('value_is').hide();
        return;
    }
    //toShow = $('category_' + id);
     $('category_' + id).show();
    visiblePhenotypeSelect =  $('category_' + id);
}

function getValueIs(id)
{

    //value_is = $('value_is'); // doesn't work in ie

    if (id == '-1')
    {
        $('value_is').update();
        $('descript').update();
        return;
    }

    id = id.toLowerCase();

    $('descript').update('<b>Description:</b> ' + $F('desc_' + id) + '<br /><b>Data Range:</b> ' + $F('min_' + id) + ' - ' + $F('max_' + id));
    html = 'Where the value is: <select name="value_is" onchange="getValueIsInput(this.value)"><option value="-1">(Select an option)</option>';
    html += '<option value="0">equal to</option><option value="1">between</option>';
    html += '</select><div id="value_is_input"></div>'
    $('value_is').show();
    $('value_is').update(html);
}

function getValueIsInput(value)
{
    if (value == '-1')
    {
        $('value_is_input').update();
    }
    else if (value == '0')
    {
        html = '<br /><input type="text" name="equalto" size="10" />';
        $('value_is_input').update(html);
    }
    else if (value == '1')
    {
        html = '<br /><input type="text" name="lower" size="10" /> and <input type="text" name="upper" size="10" />';
        $('value_is_input').update(html);
    }
}

function deleteFilter(id)
{
    /*new Ajax.Request('http://lab.bcb.iastate.edu/sandbox/yhames04/advanced/phenotype.php?function=delete_filter&arg0='+id, {onComplete: function(){new Effect.Fade('temp_filter_'+id)}});*/
    document.location = '<?php echo $config['base_url']; ?>advanced/phenotype.php?function=delete_filter&arg0='+id;
}

function sl(checkbox, id)
{
	if(checkbox.checked){
		new Ajax.Request('<?php echo $config['base_url']; ?>advanced/phenotype.php?function=sl&arg0='+id, {onFailure:function(){checkbox.checked = false}});
	} else {
		new Ajax.Request('<?php echo $config['base_url']; ?>advanced/phenotype.php?function=dl&arg0='+id, {onFailure:function(){checkbox.checked = true}});
	}
}

function reorder_results(offset, limit, column, direction)
{
	new Ajax.Updater(
		'results',
		'<?php echo $config['base_url']; ?>advanced/phenotype.php?function=get_results&arg0='+offset+'&arg1='+limit+'&arg2='+column+'&arg3='+direction,
		{}
		);
}