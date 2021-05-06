<?php

    require_once('geoplugin.class/geoplugin.class.php');
    $geoplugin = new geoPlugin();
    $geoplugin->locate($_SERVER['REMOTE_ADDR']);

    $location = $_POST['city'];
    $country = '';

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
        <meta name="canonical" href="https://WeatherApp.herokuapp.com/">
        <title>Météo</title>
        <link rel="icon" href="img/icons/c02d.png">
        <link rel="stylesheet" href="form.css?v=<?php echo time(); ?>">
    </head>
    <body>
    <header id="search">
        <div id="home">
            <a href="index.php"><img src="img/home1.svg" alt="Home icon" height="40px" width="40px"></a>
        </div>
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
            <h2 id="cityName"> Metéo à <?php echo $weatherForecast6Day['city_name'] ?> (<?php echo $geoplugin->regionCode ?>)</h2>
            <div id="gridPrevision">
                <div id="currentDay">
                    <h3>Aujourd'hui</h3>
                    <div class="iconeWeather">
                        <?php echo getIcons($currentWeather['data'][0]['weather']['icon']); ?>
                        <p><strong> Minimal / Maximal<br> prévue:</strong> <br> <?php echo $weatherForecast6Day['data'][0]['min_temp']; ?> / <?php echo $weatherForecast6Day['data'][1]['max_temp']; ?>°</p>
                        <p><strong> Température Actuel :</strong><br> <?php echo $currentWeather['data'][0]['temp'];?>°</p>
                        <p><strong>Levé /Couché du soleil :</strong><br> <?php getSunriseTime($weatherForecast6Day, 0);?> / <?php getSunsetTime($weatherForecast6Day, 0);?></p>
                        <p><strong>Vent :</strong> <br><?php getWindDirectionIcon($currentWeather['data'][0]['wind_cdir_full']); ?> <?php  formatWindSpeed($currentWeather['data'][0]['wind_spd']); ?>km/h</p>
                    </div>
                </div>

                <div id="day2">
                    <h3>Demain</h3>
                    <div class="iconeWeather">
                        <?php echo getIcons($weatherData[1]); ?>
                        <p><strong>Minimal :</strong> <?php echo $weatherForecast6Day['data'][1]['min_temp'];?>°</p>
                        <p><strong>Maximal :</strong> <?php echo $weatherForecast6Day['data'][1]['max_temp'];?>°</p>
                        <p><strong>Levé /Couché du soleil :</strong><br> <?php getSunriseTime($weatherForecast6Day, 1);?> / <?php getSunsetTime($weatherForecast6Day, 1);?></p>
                        <p><strong>Risque de précipitations :</strong> <br> <?php echo $weatherForecast6Day['data'][1]['pop']; ?>%</p>
                        <p><strong>Couverture nuageuse<br> moyenne :</strong><br><?php echo $weatherForecast6Day['data'][1]['clouds'] ?>%</p>
                    </div>
                </div>

                <div id="day3">
                    <h3>le <?php formatDate(2); ?></h3>
                    <div class="iconeWeather">
                        <?php echo getIcons($weatherData[2]); ?>
                        <p><strong>Minimal : </strong><?php echo $weatherForecast6Day['data'][2]['min_temp'];?>°</p>
                        <p><strong>Maximal :</strong> <?php echo $weatherForecast6Day['data'][2]['max_temp'];?>°</p>
                        <p><strong>Levé /Couché du soleil :</strong><br> <?php getSunriseTime($weatherForecast6Day, 2);?> / <?php getSunsetTime($weatherForecast6Day, 2);?></p>
                        <p><strong>Risque de précipitations :</strong> <br> <?php echo $weatherForecast6Day['data'][2]['pop']; ?>%</p>
                        <p><strong>Couverture nuageuse<br> moyenne :</strong><br><?php echo $weatherForecast6Day['data'][2]['clouds'] ?>%</p>
                    </div>
                </div>

                <div id="day4">
                    <h3>le <?php formatDate(3); ?></h3>
                    <div class="iconeWeather">
                        <?php echo getIcons($weatherData[3]); ?>
                        <p><strong>Minimal :</strong> <?php echo $weatherForecast6Day['data'][3]['min_temp'];?>°</p>
                        <p><strong>Maximal :</strong> <?php echo $weatherForecast6Day['data'][3]['max_temp'];?>°</p>
                        <p><strong>Levé /Couché du soleil :</strong><br> <?php getSunriseTime($weatherForecast6Day, 3);?> / <?php getSunsetTime($weatherForecast6Day, 3);?></p>
                        <p><strong>Risque de précipitations :</strong> <br> <?php echo $weatherForecast6Day['data'][3]['pop']; ?>%</p>
                        <p><strong>Couverture nuageuse<br> moyenne :</strong><br><?php echo $weatherForecast6Day['data'][3]['clouds'] ?>%</p>
                    </div>
                </div>

                <div id="day5">
                    <h3>le <?php formatDate(4); ?></h3>
                    <div class="iconeWeather">
                        <?php echo getIcons($weatherData[4]); ?>
                        <p><strong>Minimal :</strong> <?php echo $weatherForecast6Day['data'][4]['min_temp'];?>°</p>
                        <p><strong>Maximal :</strong> <?php echo $weatherForecast6Day['data'][4]['max_temp'];?>°</p>
                        <p><strong>Levé /Couché du soleil :</strong><br> <?php getSunriseTime($weatherForecast6Day, 4);?> / <?php getSunsetTime($weatherForecast6Day, 4);?></p>
                        <p><strong>Risque de précipitations : </strong><br> <?php echo $weatherForecast6Day['data'][4]['pop']; ?>%</p>
                        <p><strong>Couverture nuageuse<br> moyenne :</strong><br><?php echo $weatherForecast6Day['data'][4]['clouds'] ?>%</p>
                    </div>
                </div>

                <div id="day6">
                    <h3>le <?php formatDate(5); ?></h3>
                    <div class="iconeWeather">
                        <?php echo getIcons($weatherData[5]); ?>
                        <p><strong>Minimal :</strong> <?php echo $weatherForecast6Day['data'][5]['min_temp'];?>°</p>
                        <p><strong>Maximal :</strong> <?php echo $weatherForecast6Day['data'][5]['max_temp'];?>°</p>
                        <p><strong>Levé /Couché du soleil :</strong><br> <?php getSunriseTime($weatherForecast6Day, 5);?> / <?php getSunsetTime($weatherForecast6Day, 5);?></p>
                        <p><strong>Risque de précipitations :</strong> <br> <?php echo $weatherForecast6Day['data'][5]['pop']; ?>%</p>
                        <p><strong>Couverture nuageuse<br> moyenne :</strong><br><?php echo $weatherForecast6Day['data'][5]['clouds'] ?>%</p>
                    </div>
                </div>

                <div id="day7">
                    <h3>le <?php formatDate(6); ?></h3>
                    <div class="iconeWeather">
                        <?php echo getIcons($weatherData[6]); ?>
                        <p><strong>Minimal :</strong> <?php echo $weatherForecast6Day['data'][6]['min_temp'];?>°</p>
                        <p><strong>Maximal :</strong> <?php echo $weatherForecast6Day['data'][6]['max_temp'];?>°</p>
                        <p><strong>Levé /Couché du soleil :</strong><br> <?php getSunriseTime($weatherForecast6Day, 6);?> / <?php getSunsetTime($weatherForecast6Day, 6);?></p>
                        <p><strong>Risque de précipitations :</strong> <br> <?php echo $weatherForecast6Day['data'][6]['pop']; ?>%</p>
                        <p><strong>Couverture nuageuse<br> moyenne :</strong><br><?php echo $weatherForecast6Day['data'][6]['clouds'] ?>%</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
    </body>
    </html>
