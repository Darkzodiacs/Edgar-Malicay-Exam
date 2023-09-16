<?php
require_once 'db_config.php'; 

$db = new mysqli($db_host, $db_username, $db_password, $db_name);


if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}


function generateChannelJSON($channelId, $db) {
   
	$sql = "SELECT * FROM youtube_channels";
$result = $db->query($sql);

$data = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}



    $sql = "SELECT
                c.id AS channel_id,
                c.name AS channel_name,
                c.description AS channel_description,
                c.thumbnail AS channel_thumbnail,
               
                v.video_id AS video_id,
                v.title AS video_title,
                v.description AS video_description,
                v.thumbnail AS video_thumbnail
            FROM
                youtube_channels c
            JOIN
                youtube_channel_videos v
            ON
                c.channel_id = v.channel_id
            WHERE
                c.channel_id = ?";

    $stmt = $db->prepare($sql);
    $stmt->bind_param('s', $channelId); 
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result) {
        die("Query failed: " . $db->error);
    }

    $channelDetails = [];
    $videoDetails = [];

    while ($row = $result->fetch_assoc()) {
        $channelDetails = [
            'channel_id' => $row['channel_id'],
            'title' => $row['channel_name'],
            'description' => $row['channel_description'],
            'thumbnail' => $row['channel_thumbnail'],
        ];

        $videoDetails[] = [
            'video_id' => $row['video_id'],
            'title' => $row['video_title'],
            'description' => $row['video_description'],
            'thumbnail' => $row['video_thumbnail'],
        ];
    }

    $channelData = [
		'channel'=>$data,
        'channel_info' => $channelDetails,
        'videos' => $videoDetails,
    ];


    header('Content-Type: application/json');
    echo json_encode($channelData);
}


if (isset($_GET['channel_id'])) {
    $channelId = $_GET['channel_id'];
    
} else {
    $channelId="ChannelBasicQuery";
}



generateChannelJSON($channelId, $db);

$db->close();
?>
