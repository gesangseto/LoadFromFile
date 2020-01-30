<?php
include('connection.php');
include('list_api.php');
?>
<!-- <h3><a href="delete_tmp.php">Click here for clear DB Log</a></h3> -->
<?php
ini_set('memory_limit', '-1');
$files = glob('directory/*.csv');
echo "<font color='green'>Listing Files Ok</font>";
echo "<br>";
foreach ($files as $file) {
    $filenames = explode('/', $file);
    $filename = $filenames[1];
    $times = substr(preg_replace('/[^0-9]/', '', $filename), 0, -4);
    $year = substr($times, 0, -6);
    $month = substr($times, 4, -4);
    $day = substr($times, 6, -2);
    $hour = substr($times, 8, 2);
    $time = $year . "-" . $month . "-" . $day . " " . $hour . ":00";
    $date_time = date('Y-m-d H:i:s', strtotime($time));
    $check_duplicate_successrate = mysqli_query($mysqli, "SELECT * FROM tokoxl_event_hourly WHERE `file_name` = '" . $filename . "'");

    if (mysqli_num_rows($check_duplicate_successrate) > 0) {
        echo "<font color='red'>Data " . $filename . " has been save before</font>";
        echo "<br>";
    } else {
        $csvFile = file($file);
        $data = [];
        foreach ($csvFile as $lines) {
            // $data[] = str_getcsv($lines);
            $line = explode('|', $lines);
            $api_name = $line[4];
            $x = 0;
            // $data['LongestTime'][] = 0;
            foreach ($list_api as $api) {

                if ($api == $api_name) {
                    // $data[$x] = $lines;
                    @$data['count'][$x] = $data['count'][$x] + 1;
                    $data['ApiName'][$x] = $line[4];
                    if ($line[7] == 200) {
                        @$data['http_200'][$x] =  @$data['http_200'][$x] + 1;
                    } else if ($line[7] == 500) {
                        @$data['http_500'][$x] =  @$data['http_500'][$x] + 1;
                    } else if ($line[7] == 401) {
                        @$data['http_401'][$x] =  @$data['http_401'][$x] + 1;
                    }
                    if (empty($data['LongestTime'][$x])) {
                        (float) $data['LongestTime'][$x] = $line[8];
                    } else if ((float) $data['LongestTime'][$x] < (float) $line[8]) {
                        (float) $data['LongestTime'][$x] = (float) $line[8];
                    }
                    // echo $data['LongestTime'][$x] . "<br>";
                    // echo $line[8] . "<br>";
                    // @$data['LongestTime'][$x] = $line[0];
                    @$data['TotalTime'][$x] = $data['TotalTime'][$x] + $line[8];
                    $data['AverageTime'][$x] = $data['TotalTime'][$x] / $data['count'][$x];
                }
                $x = $x + 1;
            }
            // die;
        }
        // echo $data['LongestTime'][20] . "<br>";
        $r = 0;
        foreach ($list_api as $api) {
            if (!empty($data['ApiName'][$r])) {
                // print_r($data);
                @$success_rate = $data['http_200'][$r] * 100 / ($data['http_200'][$r] + $data['http_500'][$r]);
                $query = "INSERT INTO `tokoxl_event_hourly` (`date_time`,`file_name`,`api_name`,`http_200`,`http_500`,`http_401`,`success_rate`,`average_time`,`longest_time`)
                        VALUES ('" . $date_time . "','" . @$filename . "','" . @$data['ApiName'][$r] . "','" . @$data['http_200'][$r] . "','" . @$data['http_500'][$r] . "',
                        '" . @$data['http_401'][$r] . "','" . @$success_rate . "','" . @$data['AverageTime'][$r] . "','" . @$data['LongestTime'][$r] . "')";

                mysqli_query($mysqli, $query);
            }
            $r = $r + 1;
        }
        unset($data);
        // print_r($ApiName[19]);
        echo "<font color='green'>Insert " . $filename . " to database success </font>";
        echo "<br>";
    }
}
