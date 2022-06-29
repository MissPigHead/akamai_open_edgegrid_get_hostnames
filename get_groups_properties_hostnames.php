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
echo "start => ".microtime(true)."<hr>";

// echo "<pre>";


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
    
    // raw data format from json to array
    if ($response_groups) {
        $arr_groups_raw = json_decode($response_groups->getBody(),true);
    }

    // prepare variables of group items
    // foreach ($arr_groups_raw['groups']['items'] as $key => $item) {
    //     $item['contractId']=$item['contractIds'][0];
    //     $arr_group_items[]=$item;
    // }
    
    // print_r($arr_group_layer);
    // print_r($arr_group_items);
    echo "s1 end => ".microtime(true)."<hr>";
    
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
                $p_item['groupName']=$item['groupName'];
                $p_item['properties'] = $result['properties']['items'];
                $arr_group_item_props[]=$p_item;
            }
        }        
    }

    echo "s2 end => ".microtime(true)."<hr>";


    // ===== 3.get hostnames by property & versions =====
    // https://{hostname}/papi/v1/properties/{propertyId}/versions/{propertyVersion}/hostnames
    // https://.luna.akamaiapis.net/papi/v1/properties/".$property['propertyId']."/versions/".$property['productionVersion']."/hostnames?contractId=".$contractId."&groupId=".$item['groupId']."&validateHostnames=false&includeCertStatus=true";
    
    $arr_cnameFrom=[];
    // exit();
    // print_r($arr_group_item_props);


    foreach ($arr_group_item_props as $p_item) {
        foreach ($p_item['properties'] as $property) {
                // print_r($property);
                // echo "<br>";
            // if(is_numeric($property['productionVersion']) && $i<50){
            if(is_numeric($property['productionVersion'])){
                // echo $property['productionVersion'];
                $url="/papi/v1/properties/".$property['propertyId']."/versions/".$property['productionVersion']."/hostnames?contractId=".$p_item['contractId']."&groupId=".$p_item['groupId']."&validateHostnames=false&includeCertStatus=true";
                // echo " => ".$url;
                // echo "<br>";
                $response = $client->get($url);
                if($response){
                    $result = json_decode($response->getBody(),true);

                    $hostnames=$result['hostnames']['items'];
                    foreach ($result['hostnames']['items'] as $item){
                        $item['contractId']=$p_item['contractId'];
                        $item['groupId']=$p_item['groupId'];
                        $item['groupName']=$p_item['groupName'];
                        $arr_group_prop_hostnames[]=$item;
                        $arr_cnameFrom[]=$item['cnameFrom'];
                    }
                }
            }
        }
    }

    // print_r($arr_cnameFrom);
    // echo "<hr>";
    // print_r($arr_group_prop_hostnames);

    $fp = fopen('cnameFrom_'.date('Y-m-d').'.json', 'w');
    fwrite($fp, json_encode($arr_cnameFrom));
    fclose($fp);
 
//     echo json_encode(['status'=>200,'result'=>$arr_hostnames]);

    echo "<hr>s3 end => ".microtime(true)."<hr>";


// echo "</pre>";

    ob_end_flush();
?>
