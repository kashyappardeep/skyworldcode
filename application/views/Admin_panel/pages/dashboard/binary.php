 <style>
	  .loading {
         
          opacity: 0.7;
          background-color: #fff;
          z-index: 99;
          text-align: center;
        }
        
        .loading-image {
          
          background-image:url("<?= base_url('images/loader/ajax-loader.gif');?>");
          z-index: 100;
        }
	  </style>
	  
	  
          <div class="card">
            <div class="card-header">
                <span id=" ">Matching Closing Details</span>
                 
                <div class="card-action">
                 <div class="dropdown">
                 <a href="javascript:void();" class="dropdown-toggle dropdown-toggle-nocaret" data-toggle="dropdown">
                  <i class="icon-options"></i>
                 </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item change_binary" href="javascript:void();" data-val="last">Last</a>
                         
                        <a class="dropdown-item change_binary" href="javascript:void();" data-val="lastmonth">Last Month</a>
                   </div>
                  </div>
                 </div>
                </div>
                <div class="card-body">
                     <div id="binarydata" class="" >
                                <?php
                                $admn_path=$this->conn->company_info('admin_directory');
                                $dta['type']='last';
                                $this->load->view($admn_path.'/pages/dashboard/binary_table',$dta);
                                ?>
                            </div>
                  <!--<canvas id="dashboard-chart-1"></canvas>-->
                </div>
          </div>
        <script>
          $('.change_binary').click(function(){
              
              //$("#loader_section").addClass("loading");
              //$("#loader_img_section").addClass("loading-image");
              
              $("#binarydata").html('<div class="loading"><img class="loading-image" src="<?= base_url('images/loader/ajax-loader.gif');?>"> </div>');
              var val =$(this).attr('data-val');
               $.ajax({
                type: "post",
                url: "<?= $admin_path.'dashboard/binary';?>",
                data: {type:val},          
                success: function (response) {
                   $('#binarydata').html(response);
                   //$("#loader_section").removeClass("loading");
                  //$("#loader_img_section").removeClass("loading-image");
                }
              });
              
          });
        </script>