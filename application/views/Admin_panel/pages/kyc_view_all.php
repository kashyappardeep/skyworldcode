<?php
$kyc_details=$this->conn->runQuery('*','user_accounts',"id='$wd_id'");
if(!$kyc_details){
    redirect($admin_path.'kyc/pending');
}
$t_data=$kyc_details[0];
?>
<div class="row pt-2 pb-2">
        <div class="col-sm-9">
		    <h4 class="page-title">  KYC Details </h4>
		    <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= $admin_path.'dashboard';?>">home</a></li>            
            <li class="breadcrumb-item"><a href="#"> Kyc</a></li>            
            <li class="breadcrumb-item"><a href="#"> View</a></li>            
            <li class="breadcrumb-item active" aria-current="page">  Kyc Detail </li>
         </ol>
	   </div>
	   <div class="col-sm-3">
       
     </div>
</div>

<h6 class="text-uppercase">Personal Kyc Detail</h6>
<hr>

<div class="row">
    <?php if($t_data->kyc_status_personal!="pending"){?>
    
    <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
       

<div class="table-responsive">
<table class="table table-hover">
        <?php
          $tx_profile=$this->profile->profile_info($t_data->u_code);
          //$bank_details=json_decode($t_data->bank_details); 
        ?>
            <tr>               
                <th> User</th><td>:</td><td><?= $tx_profile->name.'( '.$tx_profile->username.' )';?></td>
            </tr>
            
            
            <tr>
                <th>Full Name</th><td>:</td><td><?= $t_data->name;?></td> 
                </tr>
            <tr>
                <th>Email</th><td>:</td><td><?= $t_data->email;?></td> 
                </tr>
            <tr>
                <th>Distribute Number</th><td>:</td><td><?= $t_data->disribute_no;?></td> 
                </tr>
            
                <th>Status </th><td>:</td><td><span class="badge badge-warning badge-sm"><?= $t_data->kyc_status_personal;?></span></td> 
                </tr>
            <tr>
                <th>Date </th><td>:</td><td><?= $t_data->added_on;?></td>  
                 
            </tr>
    </table>
    
</div>



   
    </div>
    <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
      
        <?php if($t_data->kyc_status_personal=='submitted'){?>
        <form action="" method="post">
            <div class="form-group">
              <label for="">Reason (Give Reason on cancellation)</label>
              <textarea name="reason" id="" class="form-control"></textarea>
              <small class="text-muted"><?= form_error('reason');?></small>
            </div>
             <input type="hidden" name="type" value="personal">
            
            <div class="form-group">  
        <button type="submit" name="approve_btn" class="btn btn-success">Approve</button>
        <button type="submit" name="cancel_btn" class="btn btn-danger">Cancel</button>
            </div>
        </form>
        <?php }elseif($t_data->kyc_status_personal=='approved'){
            echo "<br>";    
            echo '<h3 style="color:green">Kyc Approved</h3>';
        ?>
         <!--<form action="" method="post">
            <div class="form-group">
              <label for="">Reason (Give Reason on cancellation)</label>
              <textarea name="reason" id="" class="form-control"></textarea>
              <small class="text-danger"><?= form_error('reason');?></small>
            </div>
           
            
            <div class="form-group">  
        
        <button type="submit" name="cancel_btn" class="btn btn-danger">Cancel</button>
            </div>
        </form>-->
        
        <?php
          
          
            
        }elseif($t_data->kyc_status_personal=='rejected'){
            echo "<br>";  
            echo '<h3 style="color:red"><center>Kyc rejected</center></h3>';
            echo "<br>";
            echo '<h4 style="color:red">Reason</h4>'.$t_data->kyc_remark;
        } ?>
    </div>
   <?php   }else{
       echo "<br>";
         echo "<br>";
      echo '<center><h5>Not Upload Yet</h5></center>';
     echo "<br>";
         echo "<br>";
    }?>
</div>



<h6 class="text-uppercase">Identity Kyc Detail</h6>
<hr>

<div class="row">
    <?php if($t_data->kyc_status_identity!="pending"){?>
    
    <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
       

<div class="table-responsive">
<table class="table table-hover">
        <?php
          $tx_profile=$this->profile->profile_info($t_data->u_code);
          //$bank_details=json_decode($t_data->bank_details); 
        ?>
            <tr>               
                <th> User</th><td>:</td><td><?= $tx_profile->name.'( '.$tx_profile->username.' )';?></td>
            </tr>
            
            
            <tr>
                <th>Select Service</th><td>:</td><td><?= $t_data->attached_doc;?></td> 
                </tr>
            <tr>
                <th>Document No.</th><td>:</td><td><?= $t_data->tax_id;?></td> 
                </tr>
           
                <th>Status </th><td>:</td><td><span class="badge badge-warning badge-sm"><?= $t_data->kyc_status_identity;?></span></td> 
                </tr>
            <tr>
                <th>Date </th><td>:</td><td><?= $t_data->added_on;?></td>  
                 
            </tr>
    </table>
    
</div>



   
    </div>
    <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
    <a href="<?= $t_data->front_image;?>" target="_blank">
        <img src="<?= $t_data->front_image;?>" style="height:150px;width:150px">
        </a>
        <a href="<?= $t_data->back_image;?>" target="_blank">
        <img src="<?= $t_data->back_image;?>" style="height:150px;width:150px">
        </a>
        <br>
        <?php if($t_data->kyc_status_identity=='submitted'){?>
        <form action="" method="post">
            <div class="form-group">
              <label for="">Reason (Give Reason on cancellation)</label>
              <textarea name="reason" id="" class="form-control"></textarea>
              <small class="text-muted"><?= form_error('reason');?></small>
            </div>
            <input type="hidden" name="type" value="identity">
            
            <div class="form-group">  
        <button type="submit" name="approve_btn" class="btn btn-success">Approve</button>
        <button type="submit" name="cancel_btn" class="btn btn-danger">Cancel</button>
            </div>
        </form>
        <?php }elseif($t_data->kyc_status_identity=='approved'){
            echo "<br>";    
            echo '<h3 style="color:green">Kyc Approved</h3>';
        ?>
         <!--<form action="" method="post">
            <div class="form-group">
              <label for="">Reason (Give Reason on cancellation)</label>
              <textarea name="reason" id="" class="form-control"></textarea>
              <small class="text-danger"><?= form_error('reason');?></small>
            </div>
           
            
            <div class="form-group">  
        
        <button type="submit" name="cancel_btn" class="btn btn-danger">Cancel</button>
            </div>
        </form>-->
        
        <?php
          
          
            
        }elseif($t_data->kyc_status_identity=='rejected'){
            echo "<br>";  
            echo '<h3 style="color:red"><center>Kyc rejected</center></h3>';
            echo "<br>";
            echo '<h4 style="color:red">Reason</h4>'.$t_data->kyc_remark;
        } ?>
    </div>
   <?php   }else{
       echo "<br>";
         echo "<br>";
      echo '<center><h5>Not Upload Yet</h5></center>';
     echo "<br>";
         echo "<br>";
    }?>
</div>



<h6 class="text-uppercase">Pan Card Kyc Detail</h6>
<hr>

<div class="row">
    <?php if($t_data->kyc_status_pan!="pending"){?>
    
    <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
       

<div class="table-responsive">
<table class="table table-hover">
        <?php
          $tx_profile=$this->profile->profile_info($t_data->u_code);
          //$bank_details=json_decode($t_data->bank_details); 
        ?>
            <tr>               
                <th> User</th><td>:</td><td><?= $tx_profile->name.'( '.$tx_profile->username.' )';?></td>
            </tr>
            
            
            <tr>
                <th>Pan No.</th><td>:</td><td><?= $t_data->pan_no;?></td> 
                </tr>
           
           
                <th>Status </th><td>:</td><td><span class="badge badge-warning badge-sm"><?= $t_data->kyc_status_pan;?></span></td> 
                </tr>
            <tr>
                <th>Date </th><td>:</td><td><?= $t_data->added_on;?></td>  
                 
            </tr>
    </table>
    
</div>



   
    </div>
    <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
    <a href="<?= $t_data->front_image_pan;?>" target="_blank">
        <img src="<?= $t_data->front_image_pan;?>" style="height:150px;width:150px">
        </a>
       
        <br>
        <?php if($t_data->kyc_status_pan=='submitted'){?>
        <form action="" method="post">
            <div class="form-group">
              <label for="">Reason (Give Reason on cancellation)</label>
              <textarea name="reason" id="" class="form-control"></textarea>
              <small class="text-muted"><?= form_error('reason');?></small>
            </div>
            <input type="hidden" name="type" value="pan">
            
            <div class="form-group">  
        <button type="submit" name="approve_btn" class="btn btn-success">Approve</button>
        <button type="submit" name="cancel_btn" class="btn btn-danger">Cancel</button>
            </div>
        </form>
        <?php }elseif($t_data->kyc_status_pan=='approved'){
            echo "<br>";    
            echo '<h3 style="color:green">Kyc Approved</h3>';
        ?>
         <!--<form action="" method="post">
            <div class="form-group">
              <label for="">Reason (Give Reason on cancellation)</label>
              <textarea name="reason" id="" class="form-control"></textarea>
              <small class="text-danger"><?= form_error('reason');?></small>
            </div>
           
            
            <div class="form-group">  
        
        <button type="submit" name="cancel_btn" class="btn btn-danger">Cancel</button>
            </div>
        </form>-->
        
        <?php
          
          
            
        }elseif($t_data->kyc_status_pan=='rejected'){
            echo "<br>";  
            echo '<h3 style="color:red"><center>Kyc rejected</center></h3>';
            echo "<br>";
            echo '<h4 style="color:red">Reason</h4>'.$t_data->kyc_remark;
        } ?>
    </div>
   <?php   }else{
       echo "<br>";
         echo "<br>";
      echo '<center><h5>Not Upload Yet</h5></center>';
     echo "<br>";
         echo "<br>";
    }?>
</div>




<h6 class="text-uppercase">Bank Kyc Detail</h6>
<hr>

<div class="row">
    <?php if($t_data->kyc_status_bank!="pending"){?>
    
    <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
       

<div class="table-responsive">
<table class="table table-hover">
        <?php
          $tx_profile=$this->profile->profile_info($t_data->u_code);
          //$bank_details=json_decode($t_data->bank_details); 
        ?>
            <tr>               
                <th> User</th><td>:</td><td><?= $tx_profile->name.'( '.$tx_profile->username.' )';?></td>
            </tr>
            
            
            <tr>
                <th>Account Holder Name</th><td>:</td><td><?= $t_data->account_holder_name;?></td> 
            </tr>
            <tr>
                <th>IFSC Code</th><td>:</td><td><?= $t_data->ifsc_code;?></td> 
            </tr>
            <tr>
                <th>Bank Name</th><td>:</td><td><?= $t_data->bank_name;?></td> 
            </tr>
            <tr>
                <th>Bank Branch</th><td>:</td><td><?= $t_data->bank_branch;?></td> 
            </tr>
            <tr>
                <th>Account Number</th><td>:</td><td><?= $t_data->account_no;?></td> 
            </tr>
           
           
                <th>Status </th><td>:</td><td><span class="badge badge-warning badge-sm"><?= $t_data->kyc_status_bank;?></span></td> 
                </tr>
            <tr>
                <th>Date </th><td>:</td><td><?= $t_data->added_on;?></td>  
                 
            </tr>
    </table>
    
</div>



   
    </div>
    <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
    <a href="<?= $t_data->front_image_bank;?>" target="_blank">
        <img src="<?= $t_data->front_image_bank;?>" style="height:150px;width:150px">
        </a>
       
        <br>
        <?php if($t_data->kyc_status_bank=='submitted'){?>
        <form action="" method="post">
            <div class="form-group">
              <label for="">Reason (Give Reason on cancellation)</label>
              <textarea name="reason" id="" class="form-control"></textarea>
              <small class="text-muted"><?= form_error('reason');?></small>
            </div>
            <input type="hidden" name="type" value="bank">
            
            <div class="form-group">  
        <button type="submit" name="approve_btn" class="btn btn-success">Approve</button>
        <button type="submit" name="cancel_btn" class="btn btn-danger">Cancel</button>
            </div>
        </form>
        <?php }elseif($t_data->kyc_status_bank=='approved'){
            echo "<br>";    
            echo '<h3 style="color:green">Kyc Approved</h3>';
        ?>
         <!--<form action="" method="post">
            <div class="form-group">
              <label for="">Reason (Give Reason on cancellation)</label>
              <textarea name="reason" id="" class="form-control"></textarea>
              <small class="text-danger"><?= form_error('reason');?></small>
            </div>
           
            
            <div class="form-group">  
        
        <button type="submit" name="cancel_btn" class="btn btn-danger">Cancel</button>
            </div>
        </form>-->
        
        <?php
          
          
            
        }elseif($t_data->kyc_status_bank=='rejected'){
            echo "<br>";  
            echo '<h3 style="color:red"><center>Kyc rejected</center></h3>';
            echo "<br>";
            echo '<h4 style="color:red">Reason</h4>'.$t_data->kyc_remark;
        } ?>
    </div>
   <?php   }else{
       echo "<br>";
         echo "<br>";
      echo '<center><h5>Not Upload Yet</h5></center>';
     echo "<br>";
         echo "<br>";
    }?>
</div>




<h6 class="text-uppercase">Nominee Kyc Detail</h6>
<hr>

<div class="row">
    <?php if($t_data->kyc_status_nominee!="pending"){?>
    
    <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
       

<div class="table-responsive">
<table class="table table-hover">
        <?php
          $tx_profile=$this->profile->profile_info($t_data->u_code);
          //$bank_details=json_decode($t_data->bank_details); 
        ?>
            <tr>               
                <th> User</th><td>:</td><td><?= $tx_profile->name.'( '.$tx_profile->username.' )';?></td>
            </tr>
            
            
            <tr>
                <th>Nominee Name</th><td>:</td><td><?= $t_data->nominee_name;?></td> 
                </tr>
            <tr>
                <th>Nominee D.O.B</th><td>:</td><td><?= $t_data->nominee_dob;?></td> 
                </tr>
            <tr>
                <th>Nominee Relation</th><td>:</td><td><?= $t_data->nominee_relation;?></td> 
                </tr>
            
                <th>Status </th><td>:</td><td><span class="badge badge-warning badge-sm"><?= $t_data->kyc_status_nominee;?></span></td> 
                </tr>
            <tr>
                <th>Date </th><td>:</td><td><?= $t_data->added_on;?></td>  
                 
            </tr>
    </table>
    
</div>



   
    </div>
    <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
      
        <?php if($t_data->kyc_status_nominee=='submitted'){?>
        <form action="" method="post">
            <div class="form-group">
              <label for="">Reason (Give Reason on cancellation)</label>
              <textarea name="reason" id="" class="form-control"></textarea>
              <small class="text-muted"><?= form_error('reason');?></small>
            </div>
            <input type="hidden" name="type" value="nominee">
            
            <div class="form-group">  
        <button type="submit" name="approve_btn" class="btn btn-success">Approve</button>
        <button type="submit" name="cancel_btn" class="btn btn-danger">Cancel</button>
            </div>
        </form>
        <?php }elseif($t_data->kyc_status_nominee=='approved'){
            echo "<br>";    
            echo '<h3 style="color:green">Kyc Approved</h3>';
        ?>
         <!--<form action="" method="post">
            <div class="form-group">
              <label for="">Reason (Give Reason on cancellation)</label>
              <textarea name="reason" id="" class="form-control"></textarea>
              <small class="text-danger"><?= form_error('reason');?></small>
            </div>
           
            
            <div class="form-group">  
        
        <button type="submit" name="cancel_btn" class="btn btn-danger">Cancel</button>
            </div>
        </form>-->
        
        <?php
          
          
            
        }elseif($t_data->kyc_status_nominee=='rejected'){
            echo "<br>";  
            echo '<h3 style="color:red"><center>Kyc rejected</center></h3>';
            echo "<br>";
            echo '<h4 style="color:red">Reason</h4>'.$t_data->kyc_remark;
        } ?>
    </div>
   <?php   }else{
       echo "<br>";
         echo "<br>";
      echo '<center><h5>Not Upload Yet</h5></center>';
     echo "<br>";
         echo "<br>";
    }?>
</div>