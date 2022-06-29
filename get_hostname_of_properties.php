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

    $json = file_get_contents('Properties_'.date('Y-m-d').'.json');
    $json = json_decode($json,true);
        
    // ===== 3.get hostnames by property & versions =====
    // https://{hostname}/papi/v1/properties/{propertyId}/versions/{propertyVersion}/hostnames
    // https://.luna.akamaiapis.net/papi/v1/properties/".$property['propertyId']."/versions/".$property['productionVersion']."/hostnames?contractId=".$contractId."&groupId=".$item['groupId']."&validateHostnames=false&includeCertStatus=true";
    
    
    $arr_hostnames=[];
    
    // for control execution time when validating this program
    // $i=0; 
    
    foreach ($json as $contractId => $group_items) {
        foreach ($group_items as $key => $item){
            foreach ($item['properties'] as $k => $property) {
                // if(is_numeric($property['productionVersion']) && $i<50){
                if(is_numeric($property['productionVersion'])){
                    $url="/papi/v1/properties/".$property['propertyId']."/versions/".$property['productionVersion']."/hostnames?contractId=".$contractId."&groupId=".$item['groupId']."&validateHostnames=false&includeCertStatus=true";
                    $response = $client->get($url);
                    if($response){
                        $result = json_decode($response->getBody(),true);
                        $json[$contractId][$key]['properties'][$k]['hostnames']=$result['hostnames']['items'];
                        $arr_hostnames[$contractId][$key]['groupId']=$item['groupId'];
                        $arr_hostnames[$contractId][$key]['groupName']=$item['groupName'];
                        $arr_hostnames[$contractId][$key]['hostnames'][]=$result['hostnames']['items'];
                        // $i++;
                    }
                }
            }
        }
    }

    $fp = fopen('Hostnames_'.date('Y-m-d').'.json', 'w');
    fwrite($fp, json_encode($json));
    fclose($fp);
 
    echo json_encode(['status'=>200,'result'=>$arr_hostnames]);

    ob_end_flush();
?>
