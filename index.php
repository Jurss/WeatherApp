<?php
    require_once('geoplugin.class/geoplugin.class.php');

    $geoplugin = new geoPlugin();
    $geoplugin->locate('92.140.59.79');

    $location = $geoplugin->city;
    $country = $geoplugin->countryCode;

    function weatherToday($location, $country)
    {
        //Acces to API
        $url = 'https://api.weatherbit.io/v2.0/current?&city='.$location.'&country='.$country.'&key=b5d472c53beb4bb99cd6aabecb449ffd&include=minutely';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $json = curl_exec($ch);
        curl_close($ch);
        $api_result = json_decode($json, true);

        return $api_result;
    }
    $currentWeather = weatherToday($location, $country);

    function weatherForecast7Day($location, $country){
        // in case there is a space in the name(space break the program)
        $location = str_replace(' ', '-', $location);

        $url  ='https://api.weatherbit.io/v2.0/forecast/daily?city='.$location.'&country='.$country.'&key=b5d472c53beb4bb99cd6aabecb449ffd&days=7';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $json = curl_exec($ch);
        curl_close($ch);
        return json_decode($json, true);
    }
    $weatherForecast6Day = weatherForecast7Day($location, $country);
    $sunrise = $weatherForecast6Day['data'][1]['sunrise_ts'];
    $sunset = $weatherForecast6Day['data'][1]['sunset_ts'];

    function getSunriseTime($data, $i){
        $sunrise = $data['data'][$i]['sunrise_ts'];
        echo date('H:i', $sunrise);
    }
    function getSunsetTime($data, $i){
        $sunset = $data['data'][$i]['sunset_ts'];
        echo date('H:i', $sunset);
    }


    // inject date on HTML
    function formatDate($addDay){
        $date = date_create('now');
        date_add($date, date_interval_create_from_date_string($addDay.' days'));
        echo date_format($date, 'd/m');
    }

    // get array of weather code icons
    function getWeatherCodeIcons(array $data){
        $weatherIcons = [];
        $len = count($data);

        for ($i = 0; $i < $len; $i++) {
            $weatherIcons[$i] = $data['data'][$i]['weather']['icon'];
        }
        return $weatherIcons;
    }
    $weatherData = [];
    $weatherData = getWeatherCodeIcons($weatherForecast6Day);

    //for insert in HTML
    function getIcons($data){
        $codeIcon = 'img/icons/'.$data.'.png';
        return '<img src="'.$codeIcon.'">';
    }

    function getWindDirectionIcon($data){
        switch ($data){
            case "north":
                echo '<img src="img/up.svg" height="20px" width="20px" alt="wind_direction">';
                break;
            case "south":
                echo '<img src="img/down.svg" height="20px" width="20px" alt="wind_direction">';
                break;
            case "east":
                echo '<img src="img/right.svg" height="20px" width="20px" alt="wind_direction">';
                break;
            case "west":
                echo '<img src="img/left.svg" height="20px" width="20px" alt="wind_direction">';
                break;
            case "west-southwest":
            case "southwest":
            case "south-southwest":
                echo '<img src="img/down-left.svg" height="20px" width="20px" alt="wind_direction">';
                break;
            case "west-northwest":
            case "north-northwest":
            case "northwest":
                echo '<img src="img/up-left.svg" height="20px" width="20px" alt="wind_direction">';
                break;
            case "east-southeast":
            case "south-southeast":
            case "southeast":
                echo '<img src="img/down-right.svg" height="20px" width="20px" alt="wind_direction">';
                break;
            case "east-northeast":
            case "north-northeast":
            case "northeast":
                echo '<img src="img/up-right.svg" height="20px" width="20px" alt="wind_direction">';
                break;
        }
    }
    //ex: 3.200154 km/h to 3.20 km/h
    function formatWindSpeed($data){
        $data = strval($data);
        $data = substr($data, 0, 4);
        echo $data;
    }

?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="author" content="Rémi Dutombois">
        <meta name="description" content="7 days weather forecast">
        <meta name="canonical" href="">
        <title>Météo</title>
        <link rel="icon" href="img/icons/c02d.png">
        <link rel="stylesheet" href="index.css">
    </head>
    <body>
        <header id="search">
            <form action="form.php" method="post" enctype="multipart/form-data" id="barSearch">
                <label for="city"></label>
                <input type="text" name="city" id="city" placeholder="Recherchez une ville">
                <button type="submit">
                    <img src="img/loupe.svg" alt="loupe" height="20px" width="20px">
                </button>
            </form>
        </header>
        <main>
            <div id="ResultContainer">
                <h2 id="cityName"> Metéo à <?php echo $geoplugin->city; ?> (<?php echo $geoplugin->regionCode ?>)</h2>
                <div id="gridPrevision">
                    <div id="currentDay">
                        <h3>Aujourd'hui</h3>
                        <div class="iconeWeather">
                            <?php echo getIcons($currentWeather['data'][0]['weather']['icon']); ?>
                            <p>Minimal / Maximal : <br> <?php echo $weatherForecast6Day['data'][0]['min_temp']; ?> / <?php echo $weatherForecast6Day['data'][1]['max_temp']; ?>°</p>
                            <p> Température Actuel :<br> <?php echo $currentWeather['data'][0]['temp'];?>°</p>
                            <p>Levé /Couché du soleil :<br> <?php getSunriseTime($weatherForecast6Day, 0);?> / <?php getSunsetTime($weatherForecast6Day, 0);?></p>
                            <p>Vent : <br><?php getWindDirectionIcon($currentWeather['data'][0]['wind_cdir_full']); ?> <?php  formatWindSpeed($currentWeather['data'][0]['wind_spd']); ?>km/h</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </body>
</html>