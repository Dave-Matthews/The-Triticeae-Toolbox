<?php
// 18feb13 dem: New, replacing the old ugly file of the same name

require 'config.php';
include $config['root_dir'] . 'includes/bootstrap.inc';
include $config['root_dir'] . 'theme/admin_header.php';

loginTest();
$row = loadUser($_SESSION['username']);
ob_start();
authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();

?>

<style type="text/css">
  p {width: 80%}
  th {background: #5B53A6 !important; color: white !important; border-left: 2px solid #5B53A6}
  table {background: none; border-collapse: collapse}
  td {border: 0px solid #eee !important;}
  h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
</style>

<h2>Curate Traits and Genetic Characters </h2>

<h3>Traits</h3>

<p><b>Traits</b> are environment-dependent characteristics of a line,
usually measured in several trials and replicated.  Their values are
usually quantitative and expressed in defined units.  Examples are
yield, height, grain protein.  In this database each trait must be
assigned to a <i>Category</i>, e.g. Agronomic, Quality.</p>

<li><a href="<?php echo $config['base_url'] ?>curator_data/traitAdd.php">Upload</a> a file of traits
<li><a href="<?php echo $config['base_url'] ?>curator_data/traitAdd.php?add=single">Enter</a> a single trait interactively
<li><a href="<?php echo $config['base_url'] ?>login/edit_traits.php">Edit/delete</a> existing traits
<li><a href="<?php echo $config['base_url'] ?>login/edit_units.php">Edit/delete</a> existing traits units
<li><a href="<?php echo $config['base_url'] ?>curator_data/traitAdd.php?add=category">Add</a> a new Category
<li><a href="<?php echo $config['base_url'] ?>curator_data/traitAdd.php?add=unit">Add</a> a new Unit

<h3>Genetic characters</h3>

<p><b>Genetic characters</b>, or <i>properties</i>, are
environment-insensitive and usually have a small set of discrete values.
Examples are the allele states of major genes affecting phenotype, such
as <i>Rht1</i> for reduced height and <i>Lr</i> genes for leaf rust
resistance.  Categories for these characters are the same as for
traits.</p>

<li><a href="<?php echo $config['base_url'] ?>curator_data/propertyAdd.php">Upload</a> a file of properties
<li><a href="<?php echo $config['base_url'] ?>login/edit_properties.php">Edit/delete</a> existing properties
<li><a href="<?php echo $config['base_url'] ?>login/edit_genchars.php">Edit</a> the properties of a line

<?php
    $footer_div = 1;
    include $config['root_dir'].'theme/footer.php';
?>
