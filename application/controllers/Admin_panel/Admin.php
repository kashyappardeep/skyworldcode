<?php
class Admin extends CI_Controller{
    public function __construct()
    {
        parent::__construct();
    }

    public function subadmin(){
        if(isset($_POST['admin_btn'])){
            $this->form_validation->set_rules('username','Username','required|callback_valid_username');
            $this->form_validation->set_rules('password','Password','required|alpha_numeric');
            if($this->form_validation->run()!= false){
                $insert=array();
                $insert['user']=$_POST['username'];
                $insert['type']='subadmin';
                $insert['password']=md5($_POST['password']);
                $this->db->insert('admin',$insert);
                $this->session->set_flashdata('alert_success',"Subadmin created successfully.");
                $panel_path=$this->conn->company_info('admin_path');
                redirect(base_url($panel_path.'/admin/subadmin'));
            }
        }
        $this->show->admin_panel('admin_create',array());
    }
    
    public function delete_admin(){
        $id=$_GET['id'];
        $this->db->delete('admin',array('id'=>$id));
        $this->session->set_flashdata('alert_success',"Subadmin Deleted successfully.");
        $panel_path=$this->conn->company_info('admin_path');
        redirect(base_url($panel_path.'/admin/subadmin'));
    }
    
    public function edit_admin(){
        $id=$_GET['id'];
        if(isset($_POST['edit_admin_btn'])){
           $this->form_validation->set_rules('username','Username','required');
         if($this->form_validation->run() != False){
            $update=array();
            $update['user']=$_POST['username'];
            $update['type']='subadmin';     
            $this->db->where('id',$id);
            $this->db->update('admin',$update);
            $this->session->set_flashdata('alert_success',"Subadmin Updated successfully.");
            $panel_path=$this->conn->company_info('admin_path');
            redirect(base_url($panel_path.'/admin/subadmin'));
          }
        }
        $data['edit_id']=$id;
     $this->show->admin_panel('admin_edit',$data);
    }
    
    public function valid_username($str){
        $check=$this->conn->runQuery('*','admin',"user='$str'");
        if($check){
            $this->form_validation->set_message('valid_username',"Subadmin Already Exists.");
            return false;
        }else{
            return true;
        }
    }
    public function action_multiple(){
        if(isset($_POST['add_btn'])){
            
            if(isset($_POST['wd_ids'])){
                $sub_admin_name=$_POST['subadmin_name'];
                $check=$this->conn->runQuery('*','admin',"user='$sub_admin_name'");
                if($check){
                $wd_id=$_POST['wd_ids'];
                $arr_list=json_encode($wd_id);
                //die();
                $set['rights']=$arr_list;
                $this->db->where('user',$sub_admin_name);
                $this->db->update('admin',$set);
                /*echo $this->db->last_query();
                die();*/
                $this->session->set_flashdata("success", " Approved.");
                $panel_path=$this->conn->company_info('admin_path');
                redirect(base_url($panel_path.'/admin/subadmin'));
               
                }else{
                   $this->session->set_flashdata("error", "Invalid Username");
                $panel_path=$this->conn->company_info('admin_path');
                redirect(base_url($panel_path.'/admin/subadmin'));
                }
            }else{
                $this->session->set_flashdata("error", "Invalid Request. Please Select User");
                $panel_path=$this->conn->company_info('admin_path');
                redirect(base_url($panel_path.'/admin/subadmin')); 
            }
        }
    	}
    	
   public function login(){
        $id=$_REQUEST['user'];
        $res=$this->conn->runQuery('*','admin',"id='$id'");
        $this->session->set_userdata("admin_login", true);                            
        $this->session->set_userdata("admin_id", $id);
        $this->session->set_userdata("subadmin_id", $id);  
        $this->session->set_userdata("admin_rights", $res[0]->rights);  
        $this->session->set_userdata("admin_type", $res[0]->type);   
        redirect(base_url($this->conn->company_info('admin_path')."/dashboard"), "refresh");
    }
    	
    	
    	
    
}