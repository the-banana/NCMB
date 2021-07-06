# NCMB
NCMB PHP Library

## 使い方

```php
<?php

    require_once("ncmb.php");

    $app_key = "アプリケーションキーを設定してください。";
    $client_key = "クライアントキーを設定してください。";
    $ncmb = new NCMB($app_key, $client_key);

    $foo = "";
    $where = "{\"hoge\": \"" . $foo . "\"}";
    $result = $ncmb->query("bar", $where);

    $json = json_decode($result, true);
    $results = $json["results"];
    if(!empty($results)){
        $object_id = $results[0]["objectId"];
    }
?>
```