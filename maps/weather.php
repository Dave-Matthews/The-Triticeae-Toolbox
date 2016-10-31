<?php

namespace weather;

require 'config.php';
require_once $config['root_dir'].'includes/bootstrap2.inc';

new Weather($_GET['function']);

class Weather
{
    public function __construct($function = null)
    {
        switch ($function) {
            case 'getWeather':
                $this->getWeather();
                break;
            default:
                $this->displayMap();
                break;
        }
    }

    private function displayMap()
    {
        global $config;
        include $config['root_dir'].'theme/admin_header2.php';
        ?>
        This tool will retrieve weather data using the Weather Underground R API. Select a location by clicking on the map, select a date range, and then select the "Retrieve Weather" button. The results for the nearest weather station will be displayed. Use the Next/Previous button to select the next closest station. If you receive no data or an R script error then use the Next button to select another station. A Station Type of "pws" is a "personal weather station".<br><br>
        <div id="map" style="height:400px; width:600px"></div>
        <img alt="spinner" id="spinner" src="images/ajax-loader.gif" style="display:none;" />
        <script src="maps/weather01.js"></script>
        <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAnJxEsuDGtgqXG27wkA5z7nXxkJCjJwVQ&callback=initMap" async defer></script>
        <link rel="stylesheet" href="//code.jquery.com/ui/1.12.0/themes/base/jquery-ui.css">
        <script>
        $( function() {
          $( "#date1" ).datepicker();
          $( "#date2" ).datepicker();
        } );
        </script><br>
    Start Date: <input type="text" name="start" id="date1"><br>
    Stop Date: <input type="text" name="stop" id="date2"><br>
    Use only airport stations (more complete data): <input type="checkbox" name="airport" id="type"><br><br>
        <button type="button" onclick="getWeather()">Retrieve Weather</button>
        <div id="step2"></div>
        </div>
        <?php
        include $config['root_dir'].'theme/footer.php';
    }

    private function getWeather()
    {
        $dir = '/tmp/tht';
        $unique_str = $_GET['unq'];
        $lat = $_GET['lat'];
        $long = $_GET['long'];
        $date1 = $_GET['date1'];
        $date2 = $_GET['date2'];
        $cnt = $_GET['cnt'];
        $type = $_GET['type'];
        $dir = "/tmp/tht/download_" . $unique_str;
        mkdir("$dir");
        $filename1 = "commands.R";
        $filename2 = "process_error.txt";
        $filename3 = "StationInfo.txt";
        $filename4 = "PrecipGraph.png";
        $filename5 = "TempGraph.png";
        $filename6 = "WeatherData.txt";
        $h = fopen("$dir/$filename1", "w");
        $cmd1 = "setwd(\"$dir\")\n";
        $cmd2 = "lat <- $lat\n";
        $cmd3 = "long <- $long\n";
        $cmd4 = "Srtdate <- as.Date(\"$date1\", format = \"%m/%d/%Y\")\n";
        $cmd5 = "Enddate <- as.Date(\"$date2\", format = \"%m/%d/%Y\")\n";
        $cmd6 = "c <- $cnt\n";
        $cmd7 = "type <- \"$type\"\n";
        fwrite($h, $cmd1);
        fwrite($h, $cmd2);
        fwrite($h, $cmd3);
        fwrite($h, $cmd4);
        fwrite($h, $cmd5);
        fwrite($h, $cmd6);
        fwrite($h, $cmd7);
        fclose($h);
        exec("cat $dir/$filename1 ../R/WeatherTool2.R | R --vanilla > /dev/null 2> $dir/$filename2\n");
        ?>
        <br>
        <button type="button" onclick="getPrev()">Prev Station</button>
        <button type="button" onclick="getNext()">Next Station</button><br>
        <?php
        if (file_exists("$dir/$filename3")) {
            echo "<table>";
            $h = fopen("$dir/$filename3", "r");
            while ($line=fgets($h)) {
                $line = str_replace("\t", "<td>", $line);
                echo "<tr><td>$line\n";
            }
            fclose($h);
            echo "</table>";
        } else {
            echo "<pre>";
            $h = fopen("/$dir/$filename2", "r");
            while ($line=fgets($h)) {
                echo "$line\n";
            }
            fclose($h);
            echo "</pre>";
        }
        if (file_exists("$dir/$filename4")) {
            print "<img src=\"$dir/$filename4\"><br>";
        }
        if (file_exists("$dir/$filename5")) {
            print "<img src=\"$dir/$filename5\"><br>";
        }
        echo "<a href=\"$dir/$filename6\" target=\"_new\">Download Weather Data</a><br>";
        if (file_exists("$dir/$filename6")) {
            echo "<table>";
            $h = fopen("$dir/$filename6", "r");
            while ($line=fgets($h)) {
                $line = str_replace("\t", "<td>", $line);
                echo "<tr><td>$line\n";
            }
            fclose($h);
            echo "</table>";
        }
    }
}
