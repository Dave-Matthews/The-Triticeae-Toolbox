<?php
/**
 * import CSR System file
 *
 * PHP version 5.3
 * Prototype version 1.5.0
 *
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/curator_data/input_csr_spect.php
 *
 */

require 'config.php';
/*
 * Logged in page initialization
 */
require $config['root_dir'] . 'includes/bootstrap_curator.inc';

$mysqli = connecti();
loginTest();
$row = loadUser($_SESSION['username']);

ob_start();
authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();

new Instrument($_GET['function']);

/** Using a PHP class to import CSR System file
 *
 * @author   Clay Birkett <claybirkett@gmail.com>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/curator_data/input_csr_spect.php
 *
 */
class Instrument
{
    public $delimiter = "\t";
    /**
     * Using the class's constructor to decide which action to perform
     *
     * @param string $function action to perform
     */
    public function __construct($function = null)
    {
        switch($function) {
        default:
            $this->_typeInstruments(); /* initial case*/
            break;
        }	
    }

    /** add header and footer to page
     *
     * @return NULL
     */
     
    private function _typeInstruments()
    {
        global $config;
        include $config['root_dir'] . 'theme/admin_header.php';
        echo "<h2>Add CSR System Description</h2>"; 
        echo "Required before loading CSR results.<br>";
        $this->_typeInstrumentName();
        $footer_div = 1;
        include $config['root_dir'].'theme/footer.php';
    }

    /** display input form
     *
     * @return NULL
     **/ 
    private function _typeInstrumentName()
    {
        ?>
        <style type="text/css">
        th {background: #5B53A6 !important; color: white !important; border-left: 2px solid #5B53A6}
        table {background: none; border-collapse: collapse}
        td {border: 0px solid #eee !important;}
        h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
        </style>

        <br>
        <form action="curator_data/input_csr_spect_check.php" method="post" enctype="multipart/form-data">
        <table>
        <tr><td><strong>CSR System Description File:</strong><td><input id="file[]" type="file" name="file[]" size="50%" />
        <td><a href="<?php echo $config['base_url']; ?>curator_data/examples/T3/CSRinT3_SpectrometerSystem.xlsx">Example File</a> JAZ<br>
        <a href="<?php echo $config['base_url']; ?>curator_data/examples/T3/CSRinT3_SpectrometerSystemCropScan.xlsx">Example File</a> CropScan
        <td><font color=red>Updated 02/15/2013</font></tr>
        </table>
        <p><input type="submit" value="Upload" /></p>
        </form>

        <a href=login/edit_csr_system.php>Edit CSR System Table</a>
		
        <?php
    } /* end of type_Instrument_Name function*/
} /* end of class */
?>
