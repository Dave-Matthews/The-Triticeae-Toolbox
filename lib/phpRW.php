<?php
/**
 * PHP R wrapper
 *
 * PHP version 5.3
 *
 * @category External 
 * @package  R_Wrapper
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @version  GIT: 2
 * @link     http://triticeaetoolbox.org/wheat/lib/RWrap.php
 */

/**
 * PHP R interface
 *
 * @category External 
 * @package  R_Wrapper 
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/lib/RWrap.php
 */
class RWrap
{
    /**
     * create command file
     *
     * @return null
     */
    function __construct()
    {
        $this->unique_str = $_GET['unq'];
        $this->dir = "/tmp/tht/$this->unique_str";
        $this->log_file = "$this->dir/run.log";
        $this->cmd_file = "$this->dir/commands.R";
        if (!mkdir("$this->dir")) {
            die("Can not create $this->dir<br>\n");
        }
        $this->h = fopen($this->cmd_file, "w+");
        if (!$this->h) {
            die("Can not open file $this->cmd<br>\n");
        } else {
            $cmd = "setwd(\"/tmp/tht/$this->unique_str\")\n";
            fwrite ($this->h, $cmd);
        }
    }

    /**
     * add command
     *
     * @param string $cmd command
     *
     * @return null
     */
    function addCommand($cmd)
    {
        fwrite($this->h, $cmd);
    }

    /**
     * close command file
     *
     * @return null
     */
    function close()
    {
        fclose($this->h);
    }
  
    /**
     * return results file
     *
     * @param string $result_file file name
     *
     * @return null
     */
    function getResults($result_file)
    {
        $file = "$this->dir/$result_file";
        $h = fopen($file, "r");
        if (!$h) {
            die("Can not open file $file<br>\n");
        }
        while ($line=fgets($h)) {
            $results .= "$line<br>\n";
        }
        fclose($h);
        return $results;
    }

    function getLink($result_file)
    {
        $file = "$this->dir/$result_file";
        if (file_exists($file)) {
          return $file;
        } else {
          return null;
        }
    }

    /**
     * return log file
     *
     * @return null
     */
    function getLog()
    {
    }

    /**
     * run commands
     *
     * @param string $pkg_file file name
     *
     * @return null
     */
    function runCommand($pkg_file)
    {
        $cmd = "cat $this->cmd_file ../R/$pkg_file | R --vanilla > /dev/null 2> $this->log_file";
        exec($cmd);
        return $cmd;
    }
}

