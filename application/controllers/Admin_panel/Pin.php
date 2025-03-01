<?php
class Pin extends CI_Controller{
    public function __construct()
    {
        parent::__construct();
         $this->admin_url=$this->conn->company_info('admin_path');
        $this->limit=10;
        /*if($this->conn->plan_setting('pin_section')!=1){
            $admin_path=$this->conn->company_info('admin_path');
            redirect(base_url($admin_path.'/dashboard'));
            $this->currency=$this->conn->company_info('currency');
        }*/
        $this->admin_url=$this->conn->company_info('admin_path');
    }

    public function index(){ 
        $this->pin_history();
    }

    public function send(){
        if(isset($_POST['pin_transfer_btn'])){
            if(isset($_SESSION['form_submitted']))
            {
                
                $this->session->set_flashdata("error", " <i class='fa fa-spinner fa-spin'></i> Please wait...");                        
                redirect(base_url(uri_string()));
                die('You have already submitted the form.');
            }
            else
            {
                
                $_SESSION['form_submitted'] = TRUE;
                $this->form_validation->set_rules('tx_username', 'Username', 'required|callback_valid_username');
                $this->form_validation->set_rules('selected_pin', 'Pin type', 'required|callback_pin_available');
                $this->form_validation->set_rules('no_of_pins', 'No of pins', 'required|greater_than[0]');
                if($this->form_validation->run() != False){
                    $tx_username=$_POST['tx_username'];
                    $tx_u_code =  $this->profile->id_by_username($tx_username);                
                    $no_of_pins = $_POST['no_of_pins'];
                    $pin_type = $_POST['selected_pin'];
                    $tx_pre_pins=$this->pin->user_pins_by_type($tx_u_code,$pin_type);
                    $cnt_tx_pre_pins = ($tx_pre_pins ? count($tx_pre_pins):0);

                    $pin_history = array(
                            'user_id'  => $tx_u_code,
                            'tx_user'  => null,
                            'debit'  => 0,
                            'prev_pin'  => $cnt_tx_pre_pins,
                            'curr_pin'  => ($cnt_tx_pre_pins+$no_of_pins),
                            'credit'  => $no_of_pins,
                            'pin_type'  => $pin_type,
                            'tx_type'  => 'credit',
                            'remark'  => "$tx_username recieve $no_of_pins pin(s) from Admin ."                  
                        
                    );

                    if($this->db->insert('pin_history', $pin_history)){
                        
                        for($n=0;$n<$_POST['no_of_pins'];$n++){               
                            
                                                

                            $epin['pin']=random_string($this->conn->setting('pin_gen_fun'), $this->conn->setting('pin_gen_digit'));
                            $epin['u_code']=$tx_u_code;                        
                            $epin['status']=1; 
                            $epin['created_by']='admin';                                               
                            $epin['pin_type']=$pin_type;
                            $this->db->insert('epins',$epin);

                        }
                        
                        $this->update_ob->add_pin($tx_u_code,$_POST['no_of_pins']);
                        //$this->update_ob->add_amnt($tx_u_code,'unused_pins',1);
                        
                        $this->session->set_flashdata("success", "Pin(s) Transfer success to $tx_username.");
                        
                        redirect(base_url(uri_string()));
                    }else{
                        $this->session->set_flashdata("error", " Something Wrong. Please try again.");
                    }

                }

                
            }

            

        }
        unset($_SESSION['form_submitted']);
        $this->show->admin_panel('pin_send');
    }
    
   public function retrieve(){

        if(isset($_POST['pin_retrieve_btn'])){

            if(isset($_SESSION['form_submitted']))
            {
                
                $this->session->set_flashdata("error", " <i class='fa fa-spinner fa-spin'></i> Please wait...");                        
                redirect(base_url(uri_string()));
                die('You have already submitted the form.');
            }
            else
            {
                
                $_SESSION['form_submitted'] = TRUE;
                $this->form_validation->set_rules('tx_username', 'Username', 'required|callback_valid_username');
                $this->form_validation->set_rules('selected_pin', 'Pin type', 'required|callback_pin_available');
                $this->form_validation->set_rules('no_of_pins', 'No of pins', 'required|greater_than[0]|callback_pins_exists');
                if($this->form_validation->run() != False){
                    $tx_username=$_POST['tx_username'];
                    $tx_u_code =  $this->profile->id_by_username($tx_username);                
                    $no_of_pins = $_POST['no_of_pins'];
                    $pin_type = $_POST['selected_pin'];
                    $tx_pre_pins=$this->pin->user_pins_by_type($tx_u_code,$pin_type);
                    $cnt_tx_pre_pins = ($tx_pre_pins ? count($tx_pre_pins):0);
                    
                    $reqid=$this->conn->runQuery('*','epins',"use_status='0' and u_code='$tx_u_code'");
                    $total_pin=count($reqid);
                    if($total_pin>=$no_of_pins){
                        $pin_history = array(
                                'user_id'  => $tx_u_code,
                                'tx_user'  => null,
                                'credit'  => 0,
                                'prev_pin'  => $cnt_tx_pre_pins,
                                'curr_pin'  => ($cnt_tx_pre_pins-$no_of_pins),
                                'debit'  => $no_of_pins,
                                'pin_type'  => $pin_type,
                                'tx_type'  => 'debit',
                                'retrieve_status'  => '1',
                                'remark'  => "$tx_username retrieve $no_of_pins pin(s) from Admin ."
                                
                            
                        );
    
                        if($this->db->insert('pin_history', $pin_history)){
                            for($n=0;$n<$_POST['no_of_pins'];$n++){
                               
                                $epin['created_by']='admin';                                               
                                $epin['pin_type']=$pin_type;
                                $epin['use_status']=1;
                                $epin['retrieve_status']=1;
                                
                                $pid=$reqid[$n]->id;
                                
                                $this->db->where('use_status',0);
                                $this->db->where('id',$pid); 
                                //$this->db->where('u_code',$tx_u_code); 
                                $this->db->update('epins',$epin);
                                
    
                            }
                            
                            $this->update_ob->used_pin($tx_u_code,$no_of_pins);
                            $this->session->set_flashdata("success", "Pin(s) Retrieve success to $tx_username.");
                            
                            redirect(base_url(uri_string()));
                        }else{
                            $this->session->set_flashdata("error", " Something Wrong. Please try again.");
                        }
                    }else{
                        $this->session->set_flashdata("error", " Insufficent Pins. Please try again.");
                    }
                }

                
            }

            

        }
        unset($_SESSION['form_submitted']);
        $this->show->admin_panel('pin_debit');
    }
    
    public function pins_exists($str){
        if(isset($_POST['selected_pin']) && $str!='' && is_numeric($str)){
            $tx_username=$_POST['tx_username'];
            $tx_u_code =  $this->profile->id_by_username($tx_username);  
            $user_pins=$this->pin->user_pins_by_type($tx_u_code,$_POST['selected_pin']);
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
    

    public function pin_retreive_detail(){ 
        $data['limit']=25;
        $data['search_string']='pin_retreive_search'; 
        $data['from_table']='pin_history';
        $data['where']="retrieve_status='1'";
        $data['base_url']=$this->admin_url.'/pin/pin_retreive_detail'; 
        $res=$this->paging->searching_data($data);
        $data['table_data']=$res['table_data'];
         $data['sr_no']=$res['sr_no'];
        $this->show->admin_panel('pin_retreive_detail',$data);
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

    public function pin_history(){ 
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
        
        $data = $this->paging->search_response($searchdata,$this->limit,$this->admin_url.'/pin/pin-history');
       
        $this->show->admin_panel('pin_history',$data);
    }
    
    
     public function investment(){
         $searchdata['search_string']='investment_search';
        $searchdata['from_table']='orders';
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
        if(isset($_REQUEST['start_date']) && isset($_REQUEST['end_date']) && $_REQUEST['start_date']!='' && $_REQUEST['end_date']!='' ){
			$start_date=date('Y-m-d 00:00:00',strtotime($_REQUEST['start_date']));
			$end_date=date('Y-m-d 23:59:00',strtotime($_REQUEST['end_date']));
			$where="(updated_on BETWEEN '$start_date' and '$end_date')";
            $searchdata['where'] = $where;
		}     
        
          if(!empty($likeconditions)){
            $searchdata['likecondition'] = $likeconditions;
        }
        
        if(!empty($conditions)){
            $searchdata['conditions'] = $conditions;
        }
        
        $data = $this->paging->search_response($searchdata,$this->limit,$this->admin_url.'/pin/investment');
        
        $this->show->admin_panel('investment',$data);
          if(isset($_POST['export_to_excel'])){
           $get_data=$this->conn->runQuery('*','orders',"status='1'");
        
           if($get_data){
               for($f=0;$f<count($get_data);$f++){
                $tx_profile=$this->profile->profile_info($get_data[$f]->u_code);  
                $dta['Username']=$tx_profile->username;
                $dta['Name']=$tx_profile->name;
                $dta['Amount']=$get_data[$f]->amount;
                $dta['Business Volume']=$get_data[$f]->bv;
                //$dta['Remark']=$get_data[$f]->remark; 
                $u_code=$get_data[$f]->u_code;
                $exdataval[$f]=$dta;
               }
           }
             
            $this->export->export_to_excel($exdataval);

        }      
               
    }

    public function pin_box(){
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
      /* if(isset($_REQUEST['name']) && $_REQUEST['name']!=''){
            $spo=$this->profile->column_like($_REQUEST['name'],'name'); 
            if($spo){
                $conditions['use_for'] = $spo;
            }
        }*/
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
        
        $data = $this->paging->search_response($searchdata,$this->limit,$this->admin_url.'/pin/pin-box');
       
        $this->show->admin_panel('pin_box',$data);    
        
        
    }   
    
     public function pending(){
          $searchdata['search_string']='withdrawal_search';
        
        $conditions['status']=0;      

        $searchdata['from_table']='epin_requests';        
        
        if(!empty($condition)){
            $searchdata['condition']=$condition;
        }
         
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
          if(isset($_REQUEST['amount']) && $_REQUEST['amount']!=''){
            $likeconditions['amount']=$_REQUEST['amount'];
        }
        if(isset($_REQUEST['start_date']) && isset($_REQUEST['end_date']) && $_REQUEST['start_date']!='' && $_REQUEST['end_date']!='' ){
			$start_date=date('Y-m-d 00:00:00',strtotime($_REQUEST['start_date']));
			$end_date=date('Y-m-d 23:59:00',strtotime($_REQUEST['end_date']));
			$where="(added_on BETWEEN '$start_date' and '$end_date')";
            $searchdata['where'] = $where;
		}
          if(!empty($likeconditions)){
            $searchdata['likecondition'] = $likeconditions;
        }
        
        if(!empty($conditions)){
            $searchdata['conditions'] = $conditions;
        }
        $data = $this->paging->search_response($searchdata,$this->limit,$this->panel_url.'/pin/pending'); 
         
            
        $this->show->admin_panel('epin_pending',$data);
              
    }
    
    public function approved(){

       $searchdata['search_string']='withdrawal_search';
        //$conditions['tx_type']='withdrawal';
        $conditions['status']=1;      

        $searchdata['from_table']='epin_requests';
       // $data['base_url']=$this->panel_url.'/withdrawal/approved';  
       
           
          
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
        if(isset($_REQUEST['amount']) && $_REQUEST['amount']!=''){
            $conditions['amount'] = $_REQUEST['amount'];
        }       
         if(isset($_REQUEST['start_date']) && isset($_REQUEST['end_date']) && $_REQUEST['start_date']!='' && $_REQUEST['end_date']!='' ){
			$start_date=date('Y-m-d 00:00:00',strtotime($_REQUEST['start_date']));
			$end_date=date('Y-m-d 23:59:00',strtotime($_REQUEST['end_date']));
			$where="(added_on BETWEEN '$start_date' and '$end_date')";
            $searchdata['where'] = $where;
		}
          if(!empty($likeconditions)){
            $searchdata['likecondition'] = $likeconditions;
        }
        
        if(!empty($conditions)){
            $searchdata['conditions'] = $conditions;
        }
        
       
         $data = $this->paging->search_response($searchdata,$this->limit,$this->panel_url.'/pin/approved'); 
         
            
        $this->show->admin_panel('epin_approved',$data);
    }
    
    public function cancelled(){
          
       $searchdata['search_string']='withdrawal_search';
        //$conditions['tx_type']='withdrawal';
        $conditions['status']=2;      

        $searchdata['from_table']='epin_requests';
       // $data['base_url']=$this->panel_url.'/withdrawal/approved';  
       
          
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
        if(isset($_REQUEST['amount']) && $_REQUEST['amount']!=''){
            $conditions['amount'] = $_REQUEST['amount'];
        }  
        if(isset($_REQUEST['start_date']) && isset($_REQUEST['end_date']) && $_REQUEST['start_date']!='' && $_REQUEST['end_date']!='' ){
			$start_date=date('Y-m-d 00:00:00',strtotime($_REQUEST['start_date']));
			$end_date=date('Y-m-d 23:59:00',strtotime($_REQUEST['end_date']));
			$where="(added_on BETWEEN '$start_date' and '$end_date')";
            $searchdata['where'] = $where;
		}        
         
          if(!empty($likeconditions)){
            $searchdata['likecondition'] = $likeconditions;
        }
        
        if(!empty($conditions)){
            $searchdata['conditions'] = $conditions;
        }
        
       
         $data = $this->paging->search_response($searchdata,$this->limit,$this->panel_url.'/pin/cancelled'); 
         
            
        $this->show->admin_panel('epin_cancelled',$data);
        
        
    }
    public function view(){
    
   
        if(isset($_REQUEST['id'])){
            $this->session->set_userdata('admin_epin_id',$_REQUEST['id']);
        }
        $wd_id=$this->session->userdata('admin_epin_id');

        if(isset($_POST['approve_btn'])){
            $this->approve($wd_id);
            $this->session->set_flashdata("success", "Epin Approved.");
            redirect(base_url($this->conn->company_info('admin_path').'/pin/approved'));
        }

        if(isset($_POST['cancel_btn'])){
            $this->form_validation->set_rules('reason', 'Reason', 'required');
            if($this->form_validation->run() != False){
                $set['status']=2;
                $set['reason']=$_POST['reason'];
                $this->db->where('id',$wd_id);
                $this->db->update('epin_requests',$set);
                redirect(base_url($this->conn->company_info('admin_path').'/pin/cancelled'));
            }
        }

        $data['wd_id']=$wd_id;
        $this->show->admin_panel('epin_view',$data);
        
    }
    
    
      public function approve($wd_id){
        $chk_exists=$this->conn->runQuery('*','epin_requests',"status=0 and id='$wd_id'");
        if($chk_exists){
            $set['status']=1;
            $this->db->where('id',$wd_id);
            if($this->db->update('epin_requests',$set)){
                $number_of_pins=$chk_exists[0]->number_of_pins;
                $user_id=$chk_exists[0]->user_id;
                $pin_type=$chk_exists[0]->pin_type;
                
                
                $tx_pre_pins=$this->pin->user_pins_by_type($user_id,$pin_type);
                $cnt_tx_pre_pins = ($tx_pre_pins ? count($tx_pre_pins):0);
                
                 
                
                    $pin_history = array(
                            'user_id'  => $user_id,
                            'tx_user'  => null,
                            'debit'  => 0,
                            'prev_pin'  => $cnt_tx_pre_pins,
                            'curr_pin'  => ($cnt_tx_pre_pins+$number_of_pins),
                            'credit'  => $number_of_pins,
                            'pin_type'  => $pin_type,
                            'tx_type'  => 'credit',
                            'remark'  => "Recieve $number_of_pins pin(s)."                  
                        
                    );
                    if($this->db->insert('pin_history', $pin_history)){
                        for($n=0;$n<$number_of_pins;$n++){
                            $epin['pin']=random_string($this->conn->setting('pin_gen_fun'), $this->conn->setting('pin_gen_digit'));
                            $epin['u_code']=$user_id;                        
                            $epin['status']=1; 
                            $epin['created_by']='admin';                                               
                            $epin['pin_type']=$pin_type;
                            $this->db->insert('epins',$epin);

                        }
                        $this->update_ob->add_pin($user_id,$number_of_pins);
                    }
            }
            
        }
    }
}