<?php
//    error_reporting(0); //delete the display of warning when $country = null
    $location = $_POST['city'];
    $country = '';

    //Get Ip adress to know the country code of the user
    function getRealIpAddr()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
        {
            $ip=$_SERVER['HTTP_CLIENT_IP'];
        }
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
        {
            $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        else
        {
            $ip=$_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
    $xml = simplexml_load_file("http://www.geoplugin.net/xml.gp?ip=".getRealIpAddr());
    echo $xml->geoplugin_countryName ;
    foreach ($xml as $key => $value)
    {
        if($key === 'geoplugin_countryCode'){
            $country = $value;
        }
    }

   /* function weatherToday($location, $country)
    {
        //Acces to API and see the temperature
        $url = 'https://api.weatherbit.io/v2.0/current?&city='.$location.'&country='.$country.'&key=b5d472c53beb4bb99cd6aabecb449ffd&include=minutely';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $json = curl_exec($ch);
        curl_close($ch);
        $api_result = json_decode($json, true);

        return $api_result;
    }
    $weatherTodayData = weatherToday($location, $country);
   */

    function weatherForecast6Day($location, $country){
        // in case there is a space in the name(space break the program)
        $location = str_replace(' ', '-', $location);

        $url  ='https://api.weatherbit.io/v2.0/forecast/daily?city='.$location.'&country='.$country.'&key=b5d472c53beb4bb99cd6aabecb449ffd&days=7';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $json = curl_exec($ch);
        curl_close($ch);
        return json_decode($json, true);
    }
    $weatherForecast6Day = weatherForecast6Day($location, $country);

    function formatDate($addDay){
        $date = date_create('now');
        date_add($date, date_interval_create_from_date_string($addDay.' days'));
        echo date_format($date, 'd/m');
    }



?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Météo</title>
        <link rel="stylesheet" href="index.css?v=<?php echo time(); ?>">
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
            <h2 id="cityName"> Metéo à <?php echo $weatherForecast6Day['city_name'] ?> </h2>
            <div id="gridPrevision">
                <div id="currentDay">
                    <h3>Aujourd'hui</h3>
                </div>

                <div id="day2">
                    <h3>Demain</h3>
                </div>

                <div id="day3">
                    <h3>le <?php formatDate(2); ?></h3>
                </div>

                <div id="day4">
                    <h3>le <?php formatDate(3); ?></h3>
                </div>

                <div id="day5">
                    <h3>le <?php formatDate(4); ?></h3>
                </div>

                <div id="day6">
                    <h3>le <?php formatDate(5); ?></h3>
                </div>

                <div id="day7">
                    <h3>le <?php formatDate(6); ?></h3>
                </div>
            </div>
        </div>
    </main>
    </body>
    </html>
