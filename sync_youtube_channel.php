<?php

require_once 'vendor/autoload.php'; 
require_once 'db_config.php'; 

function syncChannel($channelId, $api_key, $db) {
    
    $client = new Google_Client();
    $client->setDeveloperKey($api_key);
    $youtube = new Google_Service_YouTube($client);

  
    $channelResponse = $youtube->channels->listChannels('snippet', ['id' => $channelId]);
    $channel = $channelResponse->getItems()[0];

 
    if ($channel) {
        
        $channelTitle = $channel->getSnippet()->getTitle();
        $channelDescription = $channel->getSnippet()->getDescription();
        $channelThumbnail = $channel->getSnippet()->getThumbnails()->getDefault()->getUrl();

        $stmt = $db->prepare("INSERT INTO youtube_channels (channel_id, name, description, thumbnail) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $channelId, $channelTitle, $channelDescription, $channelThumbnail);
        $stmt->execute();

        
        $pageToken = '';
        $videoCount = 0;


        do {
            $videosResponse = $youtube->search->listSearch('id,snippet', [
                'channelId' => $channelId,
                'maxResults' => min(50, 100 - $videoCount), 
                'order' => 'date',
                'pageToken' => $pageToken, 
            ]);

            $videos = $videosResponse->getItems();

       
            foreach ($videos as $video) {
                $videoId = $video->getId()->getVideoId();
                $videoTitle = $video->getSnippet()->getTitle();
                $videoDescription = $video->getSnippet()->getDescription();
                $videoThumbnail = $video->getSnippet()->getThumbnails()->getDefault()->getUrl();

                $stmt = $db->prepare("INSERT INTO youtube_channel_videos (channel_id, video_id, title, description, thumbnail) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $channelId, $videoId, $videoTitle, $videoDescription, $videoThumbnail);
                $stmt->execute();

                $videoCount++; 
            }

          
            $pageToken = $videosResponse->getNextPageToken();

        } while ($pageToken && $videoCount < 100);
    } else {
   
        die(json_encode(['message' => 'Invalid channel ID']));
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $postData = json_decode(file_get_contents('php://input'), true);

   
    if (isset($postData['channelId'])) {
        
        $channelId = $postData['channelId'];

      
        $checkStmt = $db->prepare("SELECT channel_id FROM youtube_channels WHERE channel_id = ?");
        $checkStmt->bind_param("s", $channelId);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
           
            
            die(json_encode(['message' => 'ChannelId already exists in the database: ' . $channelId]));
        } else {
            http_response_code(201); 
            echo json_encode(['message' => 'Channel data synchronized successfully']);
            $apiKey = 'AIzaSyA2r8SqMxTwkRYrYdaCUbjWbuqojr7VswA'; 
            syncChannel($channelId, $apiKey, $db);
           
        }
    } else {
       
         
        echo json_encode(['message' => 'ChannelId not provided']);
    }
} else {
   
   
    echo json_encode(['message' => 'Invalid request method']);
}


$db->close();
?>
