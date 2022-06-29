<?php
    // ===== basic info =====
    // 64378=Delta Productions Limited ION -3-1GFW8XB
    // 67943=Mobile

    // 165004=HTML5_Asia
    // 76639=Live-Dealer
    
    // ===== auth =====
    // client_token='',
    // client_secret='',
    // access_token=''

    // ===== Akamai API document =====
    // https://techdocs.akamai.com/property-mgr/reference/api

    
    // https://.luna.akamaiapis.net/papi/v1/properties?contractId=ctr_3-1GFW8XB&groupId=grp_165004
    // https://.luna.akamaiapis.net/papi/v1/properties/prp_613070/versions/35/hostnames?contractId=ctr_3-1GFW8XB&groupId=grp_165004&validateHostnames=false&includeCertStatus=true

    // ===== Akamai ref: CORS settings =====
    // https://techdocs.akamai.com/api-definitions/docs/cross-origin-resource-sharing-cors
    ob_start();

    require_once 'vendor/autoload.php';

    $client = new Akamai\Open\EdgeGrid\Client([
        'base_uri' => 'https://.luna.akamaiapis.net/'
    ]);

    $client_token = '';
    $client_secret = '';
    $access_token = '';

    $client->setAuth($client_token, $client_secret, $access_token);

    $json = file_get_contents('Groups_'.date('Y-m-d').'.json');
    $json = json_decode($json,true);
        
    // ===== 2.get property of each group item =====
    // https://{hostname}/papi/v1/groups
    // https://.luna.akamaiapis.net/papi/v1/properties?contractId=ctr_3-1GFW8XB&groupId=grp_165004
    foreach ($json as $contractId => $group_items) {
        foreach ($group_items as $key => $item) {
            $url = "/papi/v1/properties?contractId=".$contractId."&groupId=".$item['groupId'];
            $response = $client->get($url);    
            if ($response) {
                $result = json_decode($response->getBody(),true);
                // $arrProperties[$group_item['groupId']]=$result['properties']['items'];
                $json[$contractId][$key]['properties']=$result['properties']['items'];
            }
        }
        
    }

    $fp = fopen('Properties_'.date('Y-m-d').'.json', 'w');
	fwrite($fp, json_encode($json));
	fclose($fp);
    
    echo json_encode(['status'=>200,'result'=>$json]);

    ob_end_flush();
?>
