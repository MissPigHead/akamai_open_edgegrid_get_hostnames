<?php
    // ===== auth =====
    // client_token='',
    // client_secret='',
    // access_token=''
    // ===== Akamai API document =====
    // https://techdocs.akamai.com/property-mgr/reference/api
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

    // ===== 1.get group item list =====
    // https://{hostname}/papi/v1/groups
    // use $client just as you would \Guzzle\Http\Client
    $url = "/papi/v1/groups";
    $response_groups = $client->get($url);
    
    if ($response_groups) {
        $arr_groups_raw = json_decode($response_groups->getBody(),true);
    }

    // ===== 2.get property of each group item =====
    // https://{hostname}/papi/v1/groups
    // https://.luna.akamaiapis.net/papi/v1/properties?contractId=ctr_3-1GFW8XB&groupId=grp_165004
    foreach ($arr_groups_raw['groups']['items'] as $item) {
        $url = "/papi/v1/properties?contractId=".$item['contractIds'][0]."&groupId=".$item['groupId'];
        $response = $client->get($url);    
        if ($response) {
            $result = json_decode($response->getBody(),true);
            if(count($result['properties']['items'])>0){
                $p_item['contractId']=$item['contractIds'][0];
                $p_item['groupId']=$item['groupId'];
                $p_item['properties'] = $result['properties']['items'];
                $arr_group_item_props[]=$p_item;
            }
        }        
    }

    // ===== 3.get hostnames by property & versions =====
    // https://{hostname}/papi/v1/properties/{propertyId}/versions/{propertyVersion}/hostnames
    // https://.luna.akamaiapis.net/papi/v1/properties/".$property['propertyId']."/versions/".$property['productionVersion']."/hostnames?contractId=".$contractId."&groupId=".$item['groupId']."&validateHostnames=false&includeCertStatus=true";
    $arr_cnameFrom=[];
    foreach ($arr_group_item_props as $p_item) {
        foreach ($p_item['properties'] as $property) {
            if(is_numeric($property['productionVersion'])){
                $url="/papi/v1/properties/".$property['propertyId']."/versions/".$property['productionVersion']."/hostnames?contractId=".$p_item['contractId']."&groupId=".$p_item['groupId']."&validateHostnames=false&includeCertStatus=true";
                $response = $client->get($url);
                if($response){
                    $result = json_decode($response->getBody(),true);
                    foreach ($result['hostnames']['items'] as $item){
                        $arr_cnameFrom[]=$item['cnameFrom'];
                    }
                }
            }
        }
    }

    $fp = fopen('cnameFrom_'.date('Y-m-d').'.json', 'w');
    fwrite($fp, json_encode($arr_cnameFrom));
    fclose($fp);

    ob_end_flush();
?>
