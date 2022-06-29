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
    foreach ($arr_groups_raw['groups']['items'] as $key => $item) {
        $arr_group_layer[$item['contractIds'][0]][$key]=$item;
        // ==> just for the group item structure....key code is the above one, following 3 lines is not necessary
        $arr_group_grp_pos[$item['contractIds'][0]][$key]=$item['groupId'];
        if(isset($item['parentGroupId'])) {
            $arr_group_parent_pos[$item['contractIds'][0]][$key]=$item['parentGroupId'];
        }
    }
    
    // ==> just for the group item structure....assign layer to each group item, not necessary
    foreach ($arr_group_grp_pos as $contractId => $group_items) {
        while (count($group_items) >0) {
            $layer = 1;
            $temp=[];
            
            $current_key = array_search(current($group_items), $arr_group_grp_pos[$contractId]);        

            // unset the group item from $group_items which is assign to $temp
            unset($group_items[$current_key]);
            
            // assign layer to parent item & sibling items
            while (isset($arr_group_layer[$contractId][$current_key]['parentGroupId'])) {
                // find  group id of the parent item
                $current_parent_id = $arr_group_layer[$contractId][$current_key]['parentGroupId'];                    
                // find  key of the parent item
                $current_parent_key = array_search($current_parent_id, $arr_group_grp_pos[$contractId]);
                // unset the parent item from $group_items which is assign to $temp
                $temp[$layer]['parent_key']=$current_parent_key;
                $temp[$layer+1][]=$current_parent_key;
                unset($group_items[$current_parent_key]);
                
                // get the keys of sibling items
                $current_sibling_keys = array_keys($arr_group_parent_pos[$contractId],$current_parent_id);
                foreach ($current_sibling_keys as $k){
                        // unset the sibling item from $group_items which is assign to $temp
                        $temp[$layer][]=$k;
                        unset($group_items[$k]);
                }
                                
                $layer++;
                $current_key = $current_parent_key;
            }   
            
            // assign layer to group items according to the temporary array
            foreach ($temp as $l => $keys) {
                foreach ($keys as $k){
                    if(is_numeric($k)){
                        $arr_group_by_layer[$contractId][$layer-$l][$k]=$arr_group_layer[$contractId][$k];
                        if($layer!=$l){
                            $arr_group_by_layer[$contractId][$layer-$l][$k]['parent_key']=$temp[$l]['parent_key'];
                        }
                    }
                }
            }
        }
    }    
    
    // save group items to json file
    $fp = fopen('Groups_'.date('Y-m-d').'.json', 'w');
	fwrite($fp, json_encode($arr_group_layer));
	fclose($fp);

    echo json_encode(['status'=>200,'result'=>$arr_group_by_layer]);

    ob_end_flush();
?>
