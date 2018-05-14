<?php
$access_token ='sXpXtvMAPlqVYu1YfXHtLdzSK7Htz3QrYfYEcGgxGbbe40JiwH0/qI5ymEmgJGjVWnK5YNMcajf6hIJVnmE6dN/ggw9pIL7QS+azlJvgH6/jiq9texZe+UZq30OZgAby4E2oY1xrKsFtD4otzZKEawdB04t89/1O/w1cDnyilFU=';
//define('TOKEN', '你的Channel Access Token');
 
$json_string = file_get_contents('php://input');
 
$json_obj = json_decode($json_string);
 
$event = $json_obj->{"events"}[0];
$type = $event->{"message"}->{"type"};
$message = $event->{"message"};
$reply_token = $event->{"replyToken"};
 
// Google Sheet Keyword Decode
// https://docs.google.com/spreadsheets/d/<<Google試算表編號>>/edit#gid=0
$url = "https://spreadsheets.google.com/feeds/list/12WnD6wVlQwlmLiHzPVX8fIg9Vdc6P5VIfB-b_aKqIVU/od6/public/values?alt=json";
 
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$html = curl_exec($ch);
curl_close($ch);
 
$data = json_decode($html, true);
 
$result = array();
 
foreach ($data['feed']['entry'] as $item) {
$keywords = explode(',', $item['gsx$keyword']['$t']);
foreach ($keywords as $keyword) {
if (mb_strpos($message->{'text'}, $keyword) !== false) {
if ($item['gsx$title']['$t']!=""){
$candidate = array(
"type" => "text",
"text" => $item['gsx$title']['$t'],
);
array_push($result, $candidate);
}
 
if ($item['gsx$previewimageurl']['$t']!="" && $item['gsx$originalcontenturl']['$t']!="") {
$candidate_image = array(
"type" => "image",
"previewImageUrl" => $item['gsx$previewimageurl']['$t'],
"originalContentUrl" => $item['gsx$originalcontenturl']['$t']
);
array_push($result, $candidate_image);
}
 
}
}
}
// END Google Sheet Keyword Decode
 
$post_data = array(
"replyToken" => $reply_token,
"messages" => $result
);
 
$ch = curl_init("https://api.line.me/v2/bot/message/reply");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
'Content-Type: application/json',
'Authorization: Bearer '.$access_token
//'Authorization: Bearer '. TOKEN
));
$result = curl_exec($ch);
curl_close($ch);
?>