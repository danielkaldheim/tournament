<?php
/*session_start();
setcookie("TRACKID", '067172cd7d6fe0d55b2c04d0ffba6f2c', time()+3600);
header('X-Playback-Session-Id: 7058F260-6A30-4732-85BF-70CF484FBE16');
header('User-Agent: AppleCoreMedia/1.0.0.11A465 (iPad; U; CPU OS 7_0 like Mac OS X; nb_no)');*/

$ch = curl_init();
curl_setopt($ch, CURLOPT_COOKIEJAR, "cookiejar");
curl_setopt($ch, CURLOPT_URL,"http://live.cdn.getbredband.no/media/10182/3000.m3u8");
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	'X-Playback-Session-Id' => '55694B25-D681-49E8-8C29-FDD4E36B0B3A'
	));
curl_setopt($ch, CURLOPT_COOKIE, array(
	'TRACKID' => '4c3b0ed9b857872ce0f540da96d0fbc6'
	));
$buf2 = curl_exec($ch);
curl_close($ch);

file_put_contents('dump', $buf2)

//var_dump($buf2);
// $ch = curl_init();
// curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
// curl_setopt($ch, CURLOPT_COOKIEFILE, "/tmp/cookieFileName");
// curl_setopt($ch, CURLOPT_URL,"http://www.myterminal.com/list.asp");

// $buf2 = curl_exec ($ch);

// curl_close ($ch);

// echo "<PRE>".htmlentities($buf2);
/*
?>
<html>
<head>
    <title>HTTP Live Streaming Example</title>
</head>
<body>

    <video src="http://live.cdn.getbredband.no/media/10182/3000.m3u8" height="300" width="400"></video>
</body>
</html>

<?php */
?>
