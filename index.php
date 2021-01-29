<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="author" content="Esra Öz">
    <meta name="description" content="Stable Mobile Challenge">
    <meta name="keywords" content="web,html,php">
    <title> Stable Mobile </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
</head>
<body >

<nav class="navbar navbar-dark bg-info">
    <div class="container-fluid">
        <a class="navbar-brand m-4" href="#">Stable Mobile</a>
    </div>
</nav>

<div class="card bg-light container-xxl" style="max-width: 18rem; background-color: #eee !important;">
    <div class="card-body">
        <div class="container-fluid ">
            <div class="row" >

                <?php
                $ipaddress = get_client_ip_server();
                ?>
                <div class="col-md-12 "> <?php echo "IP: " . $ipaddress ?></div>
                <div class="col-md-12"> <?php echo "Address: " . ip_info($ipaddress, "address") ?></div>
                <div class="col-md-12"> <?php echo "City: " . ip_info($ipaddress, "city") ?></div>
                <div class="col-md-12"> <?php echo "State: " . ip_info($ipaddress, "state") ?></div>
                <div class="col-md-12"> <?php echo "Region: " . ip_info($ipaddress, "region") ?></div>
                <div class="col-md-12"> <?php echo "County: " . ip_info($ipaddress, "country") ?></div>
                <div class="col-md-12"> <?php echo "Countrycode: " . ip_info($ipaddress, "countrycode") ?></div>

                <?php $flag = "flags/" . strtolower(ip_info($ipaddress, "countrycode")) . ".png" ?>
                <img style="width: 250px; margin-top: 25px" src=<?php echo $flag ?>>

                <?php
                $country = ip_info($ipaddress, "country") . ", " . ip_info($ipaddress, "countrycode");
                saveHistory($country)
                ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>

<?php

function ip_info($ip = NULL, $purpose = "countrycode", $deep_detect = TRUE)
{
    $output = NULL;
    if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE) {
        $ip = $_SERVER["REMOTE_ADDR"];
        if ($deep_detect) {
            if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP))
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP))
                $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
    }
    $purpose = str_replace(array("name", "\n", "\t", " ", "-", "_"), NULL, strtolower(trim($purpose)));
    $support = array("country", "countrycode", "state", "region", "city", "location", "address");
    $continents = array(
        "AF" => "Africa",
        "AN" => "Antarctica",
        "AS" => "Asia",
        "EU" => "Europe",
        "OC" => "Australia (Oceania)",
        "NA" => "North America",
        "SA" => "South America"
    );
    if (filter_var($ip, FILTER_VALIDATE_IP) && in_array($purpose, $support)) {
        $ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));
        if (@strlen(trim($ipdat->geoplugin_countryCode)) == 2) {
            switch ($purpose) {
                case "location":
                    $output = array(
                        "city" => @$ipdat->geoplugin_city,
                        "state" => @$ipdat->geoplugin_regionName,
                        "country" => @$ipdat->geoplugin_countryName,
                        "country_code" => @$ipdat->geoplugin_countryCode,
                        "continent" => @$continents[strtoupper($ipdat->geoplugin_continentCode)],
                        "continent_code" => @$ipdat->geoplugin_continentCode
                    );
                    break;
                case "address":
                    $address = array($ipdat->geoplugin_countryName);
                    if (@strlen($ipdat->geoplugin_regionName) >= 1)
                        $address[] = $ipdat->geoplugin_regionName;
                    if (@strlen($ipdat->geoplugin_city) >= 1)
                        $address[] = $ipdat->geoplugin_city;
                    $output = implode(", ", array_reverse($address));
                    break;
                case "city":
                    $output = @$ipdat->geoplugin_city;
                    break;
                case "state":
                    $output = @$ipdat->geoplugin_regionName;
                    break;
                case "region":
                    $output = @$ipdat->geoplugin_regionName;
                    break;
                case "country":
                    $output = @$ipdat->geoplugin_countryName;
                    break;
                case "countrycode":
                    $output = @$ipdat->geoplugin_countryCode;
                    break;
            }
        }
    }
    return $output;
}

function get_client_ip_server()
{
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if (isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if (isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if (isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';

    return $ipaddress;
}

function saveHistory($ulke = null)
{
    $ip = get_client_ip_server();

    define('HOSTNAME', 'ni-safari.guzelhosting.com');
    define('DB_USERNAME', 'yesraozs_admin');
    define('DB_PASSWORD', 'hNUpmrtrl38-');
    define('DB_NAME', 'yesraozs_challenge');
    $con = mysqli_connect(HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_NAME) or die ("error");
    // Check connection
    if (mysqli_connect_errno($con)) echo "Failed to connect MySQL: " . mysqli_connect_error();

    $query = "INSERT INTO `CountryFlag` set 
        `ip`  = '" . $ip . "', `ulke`  = '" . $ulke . "', `tarih`   = '" . date('d-m-y H:i:s') . "';";

    //Execute Mutliple query
    if (mysqli_multi_query($con, $query)) {

    } else {
        $msg = "Something wrong! Please try again.";
    }
    mysqli_close($con);
}

?>