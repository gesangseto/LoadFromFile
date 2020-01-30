<?php
include('connection.php');
include('list_api.php');
?>
<h3><a href="delete_tmp.php">Click here for clear DB Log</a></h3>
<?php

$files = glob('directory/*.csv');
echo "Listing Files Ok";
echo "<br>";

foreach ($files as $file) {
    $filenames = explode('/', $file);
    $filename = $filenames[1];
    $time = substr(preg_replace('/[^0-9]/', '', $filename), 0, -4);
    $check_duplicate_successrate = mysqli_query($mysqli, "SELECT * FROM tokoxl_event_successrate WHERE `time` = '" . $time . "'");

    if (mysqli_num_rows($check_duplicate_successrate) > 0) {
        echo "Data " . $filename . " has been save before";
        echo "<br>";
    } else {
        $check_duplicate = mysqli_query($mysqli, "SELECT `api_name` FROM `tokoxl_event` WHERE `file_name` = '" . $filename . "' LIMIT 1");
        echo "Check filename for duplicate " . $filename . "";
        echo "<br>";
        if (mysqli_num_rows($check_duplicate) > 0) {
            echo "Dulicate " . $filename . ", Skip insert Ok";
            echo "<br>";
        } else {
            echo "Prepare insert file " . $filename . " to database";
            echo "<br>";
            $query = "LOAD DATA LOCAL INFILE '" . $file . "'  
            INTO TABLE tokoxl_event  
            FIELDS TERMINATED BY '|' 
            ( @channel, @event_id, @ip, @actor, @api_name, @url, @access_time, @http_status, @elapsed_time )
            SET 
            `channel`=@channel,
            `event_id`=@event_id,
            `ip`=@ip,
            `actor`=@actor,
            `api_name`=@api_name,
            `url`=@url,
            `access_time`=@access_time, 
            `http_status`=@http_status,
            `elapsed_time`=@elapsed_time,
            `file_name` = '" . $filename . "'
            ;";
            // echo $query;
            // die;
            mysqli_query($mysqli, $query);
            echo "Insert file " . $filename . " to database OK";
            echo "<br>";
            echo "Check to converting " . $filename;
            echo "<br>";
            foreach ($list_api as $api_name) {
                $count_200 = 0;
                $count_500 = 0;
                $count_401 = 0;
                $count = 0;
                $elapsed_time = 0;
                $average_time = 0;
                $longest_time = 0;
                $check_duplicate_successrate = mysqli_query($mysqli, "SELECT * FROM tokoxl_event_successrate WHERE `time` = '" . $time . "' AND `api_name` = '" . $api_name . "' LIMIT 1");
                if (mysqli_num_rows($check_duplicate_successrate) > 0) {
                    echo "Duplicate api " . $api_name . " with time " . $time;
                    echo "<br>";
                } else {
                    $result = mysqli_query($mysqli, "SELECT * FROM tokoxl_event WHERE `file_name` = '" . $filename . "' AND api_name = '" . $api_name . "'");
                    if (mysqli_num_rows($result) == 0) {
                        echo "Not found api " . $api_name . " on " . $api_name;
                        echo "<br>";
                    } else {
                        // echo "Insert " . $filename . " and " . $api_name . "";
                        // echo "<br>";
                        while ($row = mysqli_fetch_assoc($result)) {
                            $api = $api_name;
                            if ($row['http_status'] == 200) {
                                $count_200 = $count_200 + 1;
                            } else if ($row['http_status'] == 500) {
                                $count_500 = $count_500 + 1;
                            } else if ($row['http_status'] == 401) {
                                $count_401 = $count_401 + 1;
                            }
                            $count = $count + 1;
                            $max_time[] = $row['elapsed_time'];
                            $elapsed_time = $elapsed_time + $row['elapsed_time'];
                        }
                        @$longest_time = max($max_time);
                        @$average_time = $elapsed_time / $count;
                        @$success_rate = $count_200 / ($count_200 + $count_500) * 100;
                        mysqli_query($mysqli, "INSERT INTO `tokoxl_event_successrate` 
                            (`time`,`api_name`,`200`,`500`,`401`,`success_rate`,`average_time`,`longest_time`)
                            VALUES
                            ('" . $time . "','" . $api . "','" . $count_200 . "','" . $count_500 . "','" . $count_401 . "','" . $success_rate . "','" . $average_time . "','" . $longest_time . "');
                            ");
                        echo "Insert api " . $api_name . " to database";
                        echo "<br>";
                        unset($max_time);
                    }
                }
            }
        }
    }

    // $delete_log = mysqli_query($mysqli, "DELETE FROM tokoxl_event WHERE from_file = '" . $filename . "'");
    // echo $filename;
};

echo "Insert All files OK";
echo "<br>";

// echo "Prepare list_api";
// echo "<br>";
// foreach ($files as $file) {
//     $filenames = explode('/', $file);
//     $filename = $filenames[1];
//     $time = substr(preg_replace('/[^0-9]/', '', $filename), 0, -4);
//     echo "Check to converting " . $filename;
//     echo "<br>";
//     foreach ($list_api as $api_name) {
//         $count_200 = 0;
//         $count_500 = 0;
//         $count_401 = 0;
//         $count = 0;
//         $elapsed_time = 0;
//         $average_time = 0;
//         $longest_time = 0;
//         $check_duplicate_successrate = mysqli_query($mysqli, "SELECT * FROM tokoxl_event_successrate WHERE `time` = '" . $time . "' AND `api_name` = '" . $api_name . "' LIMIT 1");
//         if (mysqli_num_rows($check_duplicate_successrate) > 0) {
//             echo "Duplicate api " . $api_name . " with time " . $time;
//             echo "<br>";
//         } else {
//             $result = mysqli_query($mysqli, "SELECT * FROM tokoxl_event WHERE `file_name` = '" . $filename . "' AND api_name = '" . $api_name . "'");
//             if (mysqli_num_rows($result) == 0) {
//                 echo "Not found api " . $api_name . " on " . $api_name;
//                 echo "<br>";
//             } else {
//                 // echo "Insert " . $filename . " and " . $api_name . "";
//                 // echo "<br>";
//                 while ($row = mysqli_fetch_assoc($result)) {
//                     $api = $api_name;
//                     if ($row['http_status'] == 200) {
//                         $count_200 = $count_200 + 1;
//                     } else if ($row['http_status'] == 500) {
//                         $count_500 = $count_500 + 1;
//                     } else if ($row['http_status'] == 401) {
//                         $count_401 = $count_401 + 1;
//                     }
//                     $count = $count + 1;
//                     $max_time[] = $row['elapsed_time'];
//                     $elapsed_time = $elapsed_time + $row['elapsed_time'];
//                 }
//                 @$longest_time = max($max_time);
//                 @$average_time = $elapsed_time / $count;
//                 @$success_rate = $count_200 / ($count_200 + $count_500) * 100;
//                 mysqli_query($mysqli, "INSERT INTO `tokoxl_event_successrate` 
//                     (`time`,`api_name`,`200`,`500`,`401`,`success_rate`,`average_time`,`longest_time`)
//                     VALUES
//                     ('" . $time . "','" . $api . "','" . $count_200 . "','" . $count_500 . "','" . $count_401 . "','" . $success_rate . "','" . $average_time . "','" . $longest_time . "');
//                     ");
//                 echo "Insert api " . $api_name . " to database";
//                 echo "<br>";
//                 unset($max_time);
//             }
//         }
//     }
// }
