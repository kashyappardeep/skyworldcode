<?php
class Pin extends CI_Controller{
    public function __construct()
    {
        parent::__construct();

        if($this->conn->plan_setting('pin_section')!=1){
            $panel_path=$this->conn->company_info('panel_path');
            redirect(base_url($panel_path.'/dashboard'));
            $this->currency=$this->conn->company_info('currency');
        }
        $this->panel_url=$this->conn->company_info('panel_path');
        $this->limit=10;
    }

    public function index(){ 
        $this->show->user_panel('pin_generate');
    }

          
    public function pin_generate(){ 
        if(isset($_POST['generate_btn'])){
                    
            $this->form_validation->set_rules('selected_pin', 'Pin Type', 'required');
            $this->form_validation->set_rules('no_of_pins', 'No of Pin', 'required|numeric|greater_than[0]');
            
            $this->form_validation->set_rules('selected_wallet', 'Wallet', 'required|callback_check_wallet_useable'); 

            if($this->form_validation->run() != False){
				$csrf_test_name=json_encode($_POST);
				$check_ex=$this->conn->runQuery('id','form_request',"request='$csrf_test_name'");
				if($check_ex){
					$this->session->set_flashdata("error", "You can not submit same request within 5 minutes.");
				}else{               
					$request['u_code']=$u_code=$this->session->userdata('user_id');;
					$request['request']=$csrf_test_name;
					$this->db->insert('form_request',$request);
				 $tx_charge=26;
                $profile=$this->session->userdata('profile');
                $username=$profile->username;
                $name=$profile->name;
                $no_of_pins=$_POST['no_of_pins'];
                $transaction['u_code']=$this->session->userdata('user_id');
                $transaction['tx_type']="pin_purchase";
                $transaction['debit_credit']="debit";
                $transaction['wallet_type']=$_POST['selected_wallet'];
                $transaction['amount']=$this->pin->pin_details($_POST['selected_pin'])->pin_rate* $_POST['no_of_pins'];
                $transaction['tx_charge']=$tx_charge*$_POST['no_of_pins'];
                $transaction['date']=date('Y-m-d H:i:s');
                $transaction['status']=1;
                $transaction['open_wallet']=$this->update_ob->wallet($this->session->userdata('user_id'),$_POST['selected_wallet']);
                $transaction['closing_wallet']=$transaction['open_wallet']-$transaction['amount'];
                $transaction['remark']="$name ($username) create $no_of_pins pin(s)";

                if($this->db->insert('transaction',$transaction)){
                    $amnt=$transaction['amount']+$transaction['tx_charge'];
                    $this->update_ob->add_amnt($this->session->userdata('user_id'),$_POST['selected_wallet'],-$amnt);
                    
                    for($n=0;$n<$_POST['no_of_pins'];$n++){
                        $epin['pin']=random_string($this->conn->setting('pin_gen_fun'), $this->conn->setting('pin_gen_digit'));
                        $epin['u_code']=$this->session->userdata('user_id');
                        $epin['created_by']='user';//$this->session->userdata('user_id');
                        $epin['status']=1;
                        $epin['use_status']=0;
                        $epin['pin_type']=$_POST['selected_pin'];
                        $this->db->insert('epins',$epin);
                    }
                    $this->update_ob->add_pin($this->session->userdata('user_id'),$_POST['no_of_pins']);
                    
                    $selected_pin=$_POST['selected_pin'];
                    $u_code=$this->session->userdata('user_id');
                    $pre_pins=$this->pin->user_pins_by_type($u_code,$selected_pin);
                    
                    $cnt_pre_pins = ($pre_pins ? count($pre_pins):0);

                    $pin_history['prev_pin']=$cnt_pre_pins;
                    $pin_history['curr_pin']=$cnt_pre_pins+$_POST['no_of_pins'];
                    $pin_history['user_id']=$this->session->userdata('user_id');
                    $pin_history['credit']=$_POST['no_of_pins'];
                    $pin_history['pin_type']=$_POST['selected_pin'];
                    $pin_history['tx_type']='credit';
                    $name=$this->session->userdata('profile')->name;
                    $username=$this->session->userdata('profile')->username;
                    $pin_history['remark']="$name ( $username ) Create ".$_POST['no_of_pins']." pin.";
                    $this->db->insert('pin_history',$pin_history);

                    $this->session->set_flashdata("success", " Pin Generated successfully.");
                    redirect(base_url(uri_string()));
                }else{
                    $this->session->set_flashdata("error", " Something Wrong. Please try again.");
                }
				}
            }
        
        }
      
        $this->show->user_panel('pin_generate');
    }

    public function check_wallet_useable($str){
        $available_wallets=$this->conn->setting('generate_pin_wallets');
        $useable_wallet=json_decode($available_wallets);
        if(array_key_exists($str,$useable_wallet)){
            if(isset($_POST['selected_pin']) && isset($_POST['no_of_pins']) && is_numeric($_POST['no_of_pins'])){
                
                $pin_details=$this->pin->pin_details($_POST['selected_pin']);
                if($pin_details){
                    $pin_rate=$pin_details->pin_rate;
                    $no_of_pins=$_POST['no_of_pins'];
                    $ttl=$pin_rate*$no_of_pins;
                    $wallet_balance=$this->update_ob->wallet($this->session->userdata('user_id'),$str);
                    if($wallet_balance>=$ttl){
                      return true;  
                    }else{
                        $this->form_validation->set_message('check_wallet_useable', "Insufficient fund in wallet.");
                        return false;
                    }

                }else{
                    $this->form_validation->set_message('check_wallet_useable', "Invalid pin type.");
                    return false;
                }
                
            }else{
                $this->form_validation->set_message('check_wallet_useable', "Fill valid value of pin type and no of pins.");
                return false;
            }
            
        }else{
            $this->form_validation->set_message('check_wallet_useable', "You can not Generate pin from this wallet");
            return false;
        }
    }
     
    

    public function pin_transfer(){
		if(isset($_POST['pin_transfer_btn'])){
            //sleep(5);
            $pin_transfer_with_otp=$this->conn->setting('pin_transfer_with_otp');
            if($pin_transfer_with_otp=='yes'){
                $this->form_validation->set_rules('otp_input1', 'OTP', 'required|trim|callback_check_otp_valid');
            }
            $user_id=$this->session->userdata('user_id');
			$this->form_validation->set_rules('tx_username', 'Username', 'required|callback_valid_username');
			$this->form_validation->set_rules('selected_pin', 'Pin type', 'required|callback_pin_available');
			$this->form_validation->set_rules('no_of_pins', 'No of pins', 'required|callback_pins_exists|greater_than[0]');
            if($this->form_validation->run() != False){
				$csrf_test_name=json_encode($_POST);
				$check_ex=$this->conn->runQuery('id','form_request',"request='$csrf_test_name'");
				if($check_ex){
					$this->session->set_flashdata("error", "You can not submit same request within 5 minutes.");
				}else{               
					$request['u_code']=$u_code=$this->session->userdata('user_id');;
					$request['request']=$csrf_test_name;
					$this->db->insert('form_request',$request);
					$tx_username=$_POST['tx_username'];
					$tx_u_code =  $this->profile->id_by_username($tx_username);
					$u_code = $this->session->userdata('user_id');
					$no_of_pins = $_POST['no_of_pins'];
					$pin_type = $_POST['selected_pin'];
					$username = $this->session->userdata('profile')->username;

					$my_pre_pins=$this->pin->user_pins_by_type($u_code,$pin_type);
					$tx_pre_pins=$this->pin->user_pins_by_type($tx_u_code,$pin_type);
						
					$cnt_my_pre_pins = ($my_pre_pins ? count($my_pre_pins):0);
					$cnt_tx_pre_pins = ($tx_pre_pins ? count($tx_pre_pins):0);

					$pin_history = array(
						array(
							'user_id'  => $tx_u_code,
							'tx_user'  => $u_code,
							'debit'  => 0,
							'credit'  => $no_of_pins,
							'prev_pin'  => $cnt_tx_pre_pins,
							'curr_pin'  => ($cnt_tx_pre_pins+$no_of_pins),
							'pin_type'  => $pin_type,
							'tx_type'  => 'credit',
							'remark'  => "$tx_username recieve $no_of_pins pin(s) from $username ."
						),
						array(
							'user_id'  => $u_code,
							'tx_user'  => $tx_u_code,
							'debit'  => $no_of_pins,
							'credit'  => 0,
							'prev_pin'  => $cnt_my_pre_pins,
							'curr_pin'  => ($cnt_my_pre_pins-$no_of_pins),
							'pin_type'  => $pin_type,
							'tx_type'  => 'debit',
							'remark'  => "$username sent $no_of_pins pin(s) to $tx_username ."
						)                            
					);
					if($this->db->insert_batch('pin_history', $pin_history)){
						$pin_update_details=$this->pin->user_pins_by_type($u_code,$pin_type);
						for($n=0;$n<$_POST['no_of_pins'];$n++){                                
							$pin_id=$pin_update_details[$n]->id;
							$update_details['use_status']=1;
							$update_details['used_in']='transfer';
							$update_details['usefor']=$tx_u_code;
							$this->db->where('id',$pin_id);
							$this->db->update('epins',$update_details);
							$epin['pin']=random_string($this->conn->setting('pin_gen_fun'), $this->conn->setting('pin_gen_digit'));
							$epin['u_code']=$tx_u_code;                        
							$epin['status']=1;                        
							$epin['pin_type']=$pin_type;
							$this->db->insert('epins',$epin);
						}                             
						/* $requestu['status']=1;
						$this->db->where('u_code',$user_id);
						$this->db->update('form_request',$requestu); */
						$this->update_ob->used_pin($this->session->userdata('user_id'),$_POST['no_of_pins']);
						//$this->update_ob->add_pin($tx_u_code,$_POST['no_of_pins']);
						$this->update_ob->add_pin($tx_u_code,$_POST['no_of_pins']);
						
						$this->session->set_flashdata("success", "Pin(s) Transfer success to $tx_username.");
						redirect(base_url(uri_string()));
					}else{
						$this->session->set_flashdata("error", " Something Wrong. Please try again.");
					}
				}
            }
        }

       //       pin request //

       $userid=$this->session->userdata('user_id');
       if(isset($_POST['epin_btn'])){
          
           $this->form_validation->set_rules('utr_number', 'Utr Number', 'required');
           $this->form_validation->set_rules('number_of_pins', 'Number Of Pins', 'required');
           $this->form_validation->set_rules('remark', 'Remark');
           $this->form_validation->set_rules('selected_pin', 'Selected Pin','required');
                  if($this->form_validation->run() != False){
                   $param['upload_path']='slip';
                   $upload_product=$this->upload_file->upload_image('slip_img',$param);
                   if($upload_product['upload_error']==false){
                      $register['slip']=$upload_product['file_name'];
                   }
                   $register['user_id']=$userid;
                   $register['utr_number']=$this->input->post('utr_number');
                    $register['pin_type']=$this->input->post('selected_pin');
                   $register['number_of_pins']=$this->input->post('number_of_pins');
                   $register['remark']=$this->input->post('remark');
                   $register['status']=0;
                   
                  $details=$this->conn->get_insert_id('epin_requests',$register);
             
               $this->session->set_flashdata("success", "Epin Request submitted successfully.");
               redirect(base_url(uri_string()));   
           }
           
       }
       
        $this->show->user_panel('pin_transfer');
    }











    public function epin_request(){
        $data=array();
        $userid=$this->session->userdata('user_id');
        if(isset($_POST['epin_btn'])){
           
            $this->form_validation->set_rules('utr_number', 'Utr Number', 'required');
            $this->form_validation->set_rules('number_of_pins', 'Number Of Pins', 'required');
            $this->form_validation->set_rules('remark', 'Remark');
            $this->form_validation->set_rules('selected_pin', 'Selected Pin','required');
                   if($this->form_validation->run() != False){
	                $param['upload_path']='slip';
	                $upload_product=$this->upload_file->upload_image('slip_img',$param);
	                if($upload_product['upload_error']==false){
	                   $register['slip']=$upload_product['file_name'];
	                }
	                $register['user_id']=$userid;
	                $register['utr_number']=$this->input->post('utr_number');
	                 $register['pin_type']=$this->input->post('selected_pin');
	                $register['number_of_pins']=$this->input->post('number_of_pins');
	                $register['remark']=$this->input->post('remark');
	                $register['status']=0;
	                
                   $details=$this->conn->get_insert_id('epin_requests',$register);
              
                $this->session->set_flashdata("success", "Epin Request submitted successfully.");
                redirect(base_url(uri_string()));   
            }
            
        }
            $this->show->user_panel('epin_request',$data);
    }
    public function check_session_set(){
        if(isset($_SESSION['form_submitted'])){
            $this->form_validation->set_message('check_session_set', "Already submitted.");
            return false; 
        }else{
            $_SESSION['form_submitted']=true;
            return true; 
        }
    }

    public function valid_username($str){
        $check_username=$this->conn->runQuery("id",'users',"username='$str'");
        if($check_username){
            return true;
        }else{
              $this->form_validation->set_message('valid_username', "Invalid Username! Please check username.");
               return false;
        }
    }

    public function pin_available($str){
        if($str!=''){
            if($this->pin->pin_details($str)){
                return true;
            }else{
                $this->form_validation->set_message('pin_available', "Pin Not Exists.Please Select valid pin Type.");
                return false;
            }
        }else{
            $this->form_validation->set_message('pin_available', "Please Select pin Type.");
            return false;
        }
    }

    public function pins_exists($str){
        if(isset($_POST['selected_pin']) && $str!='' && is_numeric($str)){
            $user_pins=$this->pin->user_pins_by_type($this->session->userdata('user_id'),$_POST['selected_pin']);
            if($user_pins && count($user_pins)>=$str){
                return true;
            }else{
                $this->form_validation->set_message('pins_exists', "Insufficient pin in account .");
                return false;
            }
        }else{
            $this->form_validation->set_message('pins_exists', "Fill valid value of pin type and no of pins.");
            return false;
        }
    }

    public function pin_history(){ 
      
    $conditions['user_id'] = $this->session->userdata('user_id');   
     $searchdata['from_table']='pin_history';
        if(isset($_REQUEST['name']) && $_REQUEST['name']!=''){
           $spo=$this->profile->column_like($_REQUEST['name'],'name'); 
            if($spo){
                $conditions['user_id'] = $spo;
            }
        }
       if(isset($_REQUEST['username']) && $_REQUEST['username']!=''){
            $spo=$this->profile->column_like($_REQUEST['username'],'username'); 
            if($spo){
                $conditions['user_id'] = $spo;
            }
        }
        if(isset($_REQUEST['pin_type']) && $_REQUEST['pin_type']!=''){
            $conditions['pin_type'] = $_REQUEST['pin_type'];
        } 
       
        
        if(isset($_REQUEST['tx_type']) && $_REQUEST['tx_type']!=''){
            $conditions['tx_type'] = $_REQUEST['tx_type'];
        }
        if(isset($_REQUEST['start_date']) && isset($_REQUEST['end_date']) && $_REQUEST['start_date']!='' && $_REQUEST['end_date']!='' ){
			$start_date=date('Y-m-d 00:00:00',strtotime($_REQUEST['start_date']));
			$end_date=date('Y-m-d 23:59:00',strtotime($_REQUEST['end_date']));
			$where="(updated_on BETWEEN '$start_date' and '$end_date')";
            $searchdata['where'] = $where;
		}
        
        if(isset($_REQUEST['tx_type']) && $_REQUEST['tx_type']!=''){
            $conditions['tx_type'] = $_REQUEST['tx_type'];
        }
        
        
        if(!empty($likeconditions)){
            $searchdata['likecondition'] = $likeconditions;
        }
        
        if(!empty($conditions)){
            $searchdata['conditions'] = $conditions;
        }
        
        $data = $this->paging->search_response($searchdata,$this->limit,$this->panel_url.'/pin/pin-history');
       
        $this->show->user_panel('pin_history',$data);    
        
        
    }

    
    public function pin_box(){ 
     $conditions['u_code'] = $this->session->userdata('user_id');   
     $searchdata['from_table']='epins';
        if(isset($_REQUEST['name']) && $_REQUEST['name']!=''){
           $spo=$this->profile->column_like($_REQUEST['name'],'name'); 
            if($spo){
                $conditions['u_code'] = $spo;
            }
        }
       if(isset($_REQUEST['username']) && $_REQUEST['username']!=''){
            $spo=$this->profile->column_like($_REQUEST['username'],'username'); 
            if($spo){
                $conditions['u_code'] = $spo;
            }
        }
     
        if(isset($_REQUEST['use_status']) && $_REQUEST['use_status']!=''){
            $conditions['use_status'] = $_REQUEST['use_status'];
        } 
       if(isset($_REQUEST['start_date']) && isset($_REQUEST['end_date']) && $_REQUEST['start_date']!='' && $_REQUEST['end_date']!='' ){
			$start_date=date('Y-m-d 00:00:00',strtotime($_REQUEST['start_date']));
			$end_date=date('Y-m-d 23:59:00',strtotime($_REQUEST['end_date']));
			$where="(updated_on BETWEEN '$start_date' and '$end_date')";
            $searchdata['where'] = $where;
		}
		
		if(isset($_REQUEST['limit']) && $_REQUEST['limit']!=''){
            $limit=$_REQUEST['limit'];
            $this->limit= $limit;
        }
		if(!empty($likeconditions)){
            $searchdata['likecondition'] = $likeconditions;
        }
        
        if(!empty($conditions)){
            $searchdata['conditions'] = $conditions;
        }
        
        $data = $this->paging->search_response($searchdata,$this->limit,$this->panel_url.'/pin/pin-box');
       
        $this->show->user_panel('pin_box',$data);        
        
    }

 /////////////////////////// ajax code /////////////////////////////

 public function ajax_pin_transfer(){ 

    $resp['error']=true;
    if(isset($_POST['pin_transfer_btn'])){
        if(isset($_SESSION['form_submitted'])){ 
            $resp['msg'] ="<i class='fa fa-spinner fa-spin'></i> Please wait...";        
            //$this->session->set_flashdata("error", " ");
        }else{            
            $_SESSION['form_submitted'] = TRUE;
            $this->form_validation->set_rules('tx_username', 'Username', 'required|callback_valid_username');
            $this->form_validation->set_rules('selected_pin', 'Pin type', 'required|callback_pin_available');
            $this->form_validation->set_rules('no_of_pins', 'No of pins', 'required|callback_pins_exists|greater_than[0]');
            if($this->form_validation->run() != False){
                $tx_username=$_POST['tx_username'];
                $tx_u_code =  $this->profile->id_by_username($tx_username);
                $u_code = $this->session->userdata('user_id');
                $no_of_pins = $_POST['no_of_pins'];
                $pin_type = $_POST['selected_pin'];
                $username = $this->session->userdata('profile')->username;

                $my_pre_pins=$this->pin->user_pins_by_type($u_code,$pin_type);
                $tx_pre_pins=$this->pin->user_pins_by_type($tx_u_code,$pin_type);
                    
                $cnt_my_pre_pins = ($my_pre_pins ? count($my_pre_pins):0);
                $cnt_tx_pre_pins = ($tx_pre_pins ? count($tx_pre_pins):0);                

                $pin_history = array(
                    array(
                        'user_id'  => $tx_u_code,
                        'tx_user'  => $u_code,
                        'debit'  => 0,
                        'credit'  => $no_of_pins,
                        'prev_pin'  => $cnt_tx_pre_pins,
                        'curr_pin'  => ($cnt_tx_pre_pins+$no_of_pins),
                        'pin_type'  => $pin_type,
                        'tx_type'  => 'credit',
                        'remark'  => "$tx_username recieve $no_of_pins pin(s) from $username ."
                    ),
                    array(
                        'user_id'  => $u_code,
                        'tx_user'  => $tx_u_code,
                        'debit'  => $no_of_pins,
                        'credit'  => 0,
                        'prev_pin'  => $cnt_my_pre_pins,
                        'curr_pin'  => ($cnt_my_pre_pins-$no_of_pins),
                        'pin_type'  => $pin_type,
                        'tx_type'  => 'debit',
                        'remark'  => "$username sent $no_of_pins pin(s) to $tx_username ."
                    )
                    
                );

                if($this->db->insert_batch('pin_history', $pin_history)){
                    $pin_update_details=$this->pin->user_pins_by_type($u_code,$pin_type);
                    for($n=0;$n<$_POST['no_of_pins'];$n++){
                        
                        $pin_id=$pin_update_details[$n]->id;
                        $update_details['use_status']=1;
                        $update_details['used_in']='transfer';
                        $update_details['usefor']=$tx_u_code;
                        $this->db->where('id',$pin_id);
                        $this->db->update('epins',$update_details);

                        $epin['pin']=random_string($this->conn->setting('pin_gen_fun'), $this->conn->setting('pin_gen_digit'));
                        $epin['u_code']=$tx_u_code;                        
                        $epin['status']=1;                        
                        $epin['pin_type']=$pin_type;
                        $this->db->insert('epins',$epin);
                    }
                    $resp['error']=false;

                    $resp['msg']='<div class="alert alert-success">
                                    <div class="alert-message" id="message_success">
                                        Pin(s) Transfer success to '.$tx_username.'.
                                    </div>
                                </div>'; 
                                              
                }else{
                    $resp['msg']=" Something Wrong. Please try again.";                 
                }
            }else{
                $resp['msg'] =" Something Wrong. Please try again.";
                $resp['tx_username'] = form_error('tx_username');
                $resp['selected_pin'] = form_error('selected_pin');
                $resp['no_of_pins']= form_error('no_of_pins');
                 
            }
        }    
    }
    echo json_encode($resp);
}
  
  
   public function epin_history(){ 
        $searchdata['from_table']='epin_requests';
        if(isset($_REQUEST['name']) && $_REQUEST['name']!=''){
           $spo=$this->profile->column_like($_REQUEST['name'],'name'); 
            if($spo){
                $conditions['user_id'] = $spo;
            }
        }
       if(isset($_REQUEST['username']) && $_REQUEST['username']!=''){
            $spo=$this->profile->column_like($_REQUEST['username'],'username'); 
            if($spo){
                $conditions['user_id'] = $spo;
            }
        }
        if(!empty($likeconditions)){
            $searchdata['likecondition'] = $likeconditions;
        }
        
        if(!empty($conditions)){
            $searchdata['conditions'] = $conditions;
        }
        
        $data = $this->paging->search_response($searchdata,$this->limit,$this->panel_url.'/pin/epin-history');
       
        $this->show->user_panel('epin_history',$data);
    }
    
    
    public  function check_otp_valid($str){
        if(isset($_SESSION['otp'])){
            if($_SESSION['otp']==$str){
                return true;
            }else{
                $this->form_validation->set_message('check_otp_valid', "Incorect OTP. Please try again.");
                return false;
            }
        }else{
            $this->form_validation->set_message('check_otp_valid', "OTP not Valid.");
            return false;
        }        
    }
    
    
   
}