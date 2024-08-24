<?php
isset($_CONFIG) or die;   

$txId = get_post('txId');  //change

        $addresses=$db->prepare('select id,payment_wallet from payments where id= ? and status = ?');
        $addresses->execute([$txId,3]);
        $addresses = $addresses->fetch(PDO::FETCH_ASSOC);
        $address=$addresses['payment_wallet'];
    
        $findAnother=$db->prepare('select id from payments where id > ? and payment_wallet = ?');
        $findAnother->execute([$txId,$address]);
        $findAnother = $findAnother->fetch(PDO::FETCH_ASSOC);
        if(empty($findAnother) and !empty($addresses)){
            $status=true;
            $msg="Review request has been submitted.";
             $update=$db->prepare("UPDATE payments SET hits = ?, status = ? WHERE id = ? ");
             $update->execute([0,0,$txId]);
        }else{
             $status=false;
             $msg="Review request could't be submitted, Please contract your API provider.";
        }
      json_response([
        'success' => $status,
        'message' => $msg."<br>address: ".$address."<br>id ".$txId
         ]);
      
?>