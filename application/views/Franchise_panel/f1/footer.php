
    </div>
    <!-- End container-fluid-->
    
    </div><!--End content-wrapper-->
   <!--Start Back To Top Button-->
    <a href="javaScript:void();" class="back-to-top"><i class="fa fa-angle-double-up"></i> </a>
    <!--End Back To Top Button-->
<!--	<footer class="footer">
      <div class="container">
        <div class="text-center">
          Copyright © <?= date('Y');?> <?= $this->conn->company_info('company_name');?>
        </div>
      </div>
    </footer>-->
 
   
  </div> 

 
  <script src="<?= $panel_url;?>assets/js/jquery.min.js"></script>
  <script src="<?= $panel_url;?>assets/js/popper.min.js"></script>
  <script src="<?= $panel_url;?>assets/js/bootstrap.min.js"></script>
	
  <!-- simplebar js -->
  <script src="<?= $panel_url;?>assets/plugins/simplebar/js/simplebar.js"></script>
  <!-- waves effect js -->
  <script src="<?= $panel_url;?>assets/js/waves.js"></script>
  <!-- sidebar-menu js -->
  <script src="<?= $panel_url;?>assets/js/sidebar-menu.js"></script>
  <!-- Custom scripts -->
  <script src="<?= $panel_url;?>assets/js/app-script.js"></script>

  <script src="<?= $panel_url;?>assets/plugins/bootstrap-touchspin/js/jquery.bootstrap-touchspin.js"></script>
  <script src="<?= $panel_url;?>assets/plugins/bootstrap-touchspin/js/bootstrap-touchspin-script.js"></script>

  <!--Select Plugins Js-->
  <script src="<?= $panel_url;?>assets/plugins/select2/js/select2.min.js"></script>
  <!--Inputtags Js-->
  <script src="<?= $panel_url;?>assets/plugins/inputtags/js/bootstrap-tagsinput.js"></script>

  <!--Bootstrap Datepicker Js-->
  <script src="<?= $panel_url;?>assets/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
    <script>
      $('#default-datepicker').datepicker({
        todayHighlight: true
      });
      $('#autoclose-datepicker').datepicker({
        autoclose: true,
        todayHighlight: true
      });

      $('#inline-datepicker').datepicker({
         todayHighlight: true
      });

      $('#dateragne-picker .input-daterange').datepicker({
       });

    </script>


  <!-- Chart js -->
  <script src="<?= $panel_url;?>assets/plugins/Chart.js/Chart.min.js"></script>
  <!--Peity Chart -->
  <script src="<?= $panel_url;?>assets/plugins/peity/jquery.peity.min.js"></script>
  <!-- Index js -->
  <script src="<?= $panel_url;?>assets/js/index.js"></script>
   
 
  <script src="<?= $panel_url;?>assets/plugins/sparkline-charts/jquery.sparkline.min.js"></script>
  <script src="<?= $panel_url;?>assets/js/widgets.js"></script>
   <script>
    $('.check_username_exist').change(function (e) { 
        var ths = $(this);
        var res_area = $(ths).attr('data-response');
        var username = $(this).val();        
        $.ajax({
          type: "post",
          url: "<?= $franchise_path.'users/verify_username';?>",
          data: {username:username},          
          success: function (response) {  
           // alert(response);
            var res = JSON.parse(response); 

            if(res.error==true){
              $('#'+res_area).html(res.msg).css('color','red');              
            }else{
              $('#'+res_area).html(res.msg).css('color','green');              
            }
          }
        });
    });

    $('.check_franchise_exist').change(function (e) { 
        var ths = $(this);
        var res_area = $(ths).attr('data-response');
        var username = $(this).val();        
        $.ajax({
          type: "post",
          url: "<?= $franchise_path.'Products/verify_username';?>",
          data: {username:username},          
          success: function (response) {  
           // alert(response);
            var res = JSON.parse(response); 

            if(res.error==true){
              $('#'+res_area).html(res.msg).css('color','red');              
            }else{
              $('#'+res_area).html(res.msg).css('color','green');              
            }
          }
        });
    });

     $('.get_sl_rank').change(function (e) { 
        var ths = $(this);
        var res_area = $(ths).attr('data-showrank');
        $('#'+res_area).html('Please wait...'); 
        var username = $(this).val();        
        $.ajax({
          type: "post",
          url: "<?= $franchise_path.'users/get_user_ranks';?>",
          data: {user_id_f_rank:username},          
          success: function (response) {  
           // alert(response);
           $('#'+res_area).html(response); 
             
          }
        });
    });

    $('.send_otp').click(function (e) { 
    $(this).html('<i class="fa fa-refresh fa-spin"></i>'); 
    var res_area = $(this).attr('data-response_area');
    
    $.ajax({
            type: "post",
            url: "<?= $franchise_path.'fund/send_otp';?>",
            data: {gen_otp:1},          
            success: function (response) {  
            // alert(response);
              $(this).html('Resend OTP'); 
              $('#'+res_area).css('display','block');
            }
          });

    
  });
  
  
  $('.add_to_cart').click(function (e) { 
    var ths = $(this);
    var productId = $(this).attr('data-productId');    
   
   $.ajax({
     type: "post",
     url:  "<?= $franchise_path.'products/add_to_cart';?>",
     data: {productId:productId},      
     success: function (response) {
       //alert(response);
       $(ths).html('Added');
     }
   });
  });
  
 

  $(document).on('click', '.remove_from_cart', function() {
		var ths = $(this);
		$(ths).html('<i class="fa fa-refresh fa spin"></i>');
		var pr_id = $(ths).attr('data-rwId');		
		$.ajax({
			url : "<?= $franchise_path.'products/remove';?>",
			method : 'POST',
			data : {rowid:pr_id},
			success : function(res){
				$(ths).html('<i class="fa fa-check"></i>');
				location.reload();
			}
		});
	 }); 
	 
	 $(document).on('click', '.update_cart', function() {
		var ths = $(this);
		$(ths).html('<i class="fa fa-refresh fa spin"></i>');
		var pr_id = $(ths).attr('data-rwId');
		var val = $('#'+pr_id).val();
		$.ajax({
			url : "<?= $franchise_path.'products/update';?>",
			method : 'POST',
			data : {rowid:pr_id,qty:val},
			success : function(res){
				$(ths).html('<i class="fa fa-check"></i>');
				location.reload();
			}
		});
	 });

</script>
</body>

</html>