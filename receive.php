<?php
  $json_str = file_get_contents('php://input'); //接收request的body
  $json_obj = json_decode($json_str); //轉成json格式
  
  $myfile = fopen("log.txt", "w+") or die("Unable to open file!"); //設定一個log.txt來印訊息
  fwrite($myfile, "\xEF\xBB\xBF".$json_str); //在字串前面加上\xEF\xBB\xBF轉成utf8格式
  
  $sender_userid = $json_obj->events[0]->source->userId; //取得訊息發送者的id
  $sender_txt = $json_obj->events[0]->message->text; //取得訊息內容
  $sender_replyToken = $json_obj->events[0]->replyToken; //取得訊息的replyToken
  
  $sender_txt=rawurlencode($sender_txt); //因為使用get的方式呼叫luis api，所以需要轉碼
  $ch = curl_init('https://westus.api.cognitive.microsoft.com/luis/v2.0/apps/c72546f8-8ea9-403d-a4fb-7203217e4b54?subscription-key=f3ede71b589e4eacbf35f3f7b28cc212&staging=true&verbose=true&timezoneOffset=0&q='.$sender_txt);                                                                      
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");                                                                                                                          
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $result_str = curl_exec($ch);
  fwrite($myfile, "\xEF\xBB\xBF".$result_str); //在字串前加上\xEF\xBB\xBF轉成utf8格式
  $result = json_decode($result_str);
  $ans_txt = $result -> topScoringIntent -> intent;
  $response = array (
    "to" => $sender_userid,
    "messages" => array (
      array (
        "type" => "text",
        "text" => $ans_txt
      )
    )
  );
  
  
 fwrite($myfile, "\xEF\xBB\xBF".json_encode($response)); //在字串前面加上\xEF\xBB\xBF轉成utf8格式
  $header[] = "Content-Type: application/json";
  $header[] = "Authorization: Bearer 5VC2skUsCqRPhgbtIdJR171xKitIUPCROMrSmPoTL6rcAELaj519b1mqiuzBTyOrVWdProJ0CgVXoNhxXRAeN9sclBj4qErZdy8eHINRarkxgDqlrhqTMzGNPMr7uaTXy5+LqdvmlB1Bngjal9jLCAdB04t89/1O/w1cDnyilFU=";
  $ch = curl_init("https://api.line.me/v2/bot/message/push");
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));                                                                  
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
  curl_setopt($ch, CURLOPT_HTTPHEADER, $header);                                                                                                   
  $result = curl_exec($ch);
  curl_close($ch);
?>
