<!-- added withdraw or rejected application condition with anding by Laxmi on 29-05-2023  -->
	<?php if (in_array($final_submit_status,array('pending','replied','referred_back')) && empty($rejectEntry)) { ?>

		 	<div class="col-lg-8">
				<div class="alert alert-info alert-dismissible">
					<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
					<h5><i class="icon fas fa-info"></i> Please Note !</h5>
					<p>Your application for registration has been saved and finally submitted, to check status please click on "Registration Status" button. Thankyou</p>
				</div>
			</div>
<!-- added withdraw or rejected application condition with anding by Laxmi on 29-05-2023  -->
	<?php } elseif ($final_submit_status == 'approved' && empty($rejectEntry)) { ?>

			<div class="col-lg-8">
				<div class="alert alert-info alert-dismissible">
					<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
					<h5><i class="icon fas fa-info"></i> Please Note !</h5>
					<p>Your application for registration has been successfully verified. Thankyou</p>
				</div>
			</div>
<!-- added withdraw or rejected application condition with anding by Laxmi on 29-05-2023  -->
	<?php } elseif ($final_submit_status == '' && empty($rejectEntry)) { ?>

		<div class="col-lg-8">
			<div class="alert alert-info alert-dismissible">
				<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
				<h5><i class="icon fas fa-info"></i> Please Note !</h5>
				<p class="">You need to register your application as a chemist on online system, so please click "Register Application" button to fill your details and apply. Thankyou</p>
			</div>
		</div>
<!-- added withdraw or rejected application condition to view application reject status by Laxmi on 29-05-2023  -->
	<?php } elseif(!empty($rejectEntry)){ ?>
		<div class="col-lg-8">
			<div class="alert alert-info alert-dismissible">
				<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
				<h5><i class="icon fas fa-info"></i> Please Note !</h5>
				<p class="">Your application as a chemist has been Rejected/Withdrawn, so it is no longer in processing. Thankyou</p>
			</div>
		</div>
		<?php } ?>







<!-- below all certificates letters display on chemist dashboard by laxmi B. on 26-05-2023 -->
<div class="clearfix">&nbsp;</div>
<table class="table table-bordered">
    <thead>
     <tr>
      <th scope="col">#</th>
	  <th scope="col">Chemist Id</th>
      <th scope="col">First Name</th>
      <th scope="col">Last Name</th>
      <th scope="col">Schedule Letters</th>
	  <th scope="col">Relieving Letters</th>
	  <th scope="col">Certificate</th>
	  <th scope="col">Action</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <th scope="row">1</th>
      <td><?php echo $_SESSION['username'] ;?></td>
	  <td><?php echo $_SESSION['f_name'] ;?></td>
      <td><?php echo $_SESSION['l_name'] ;?></td>
	  <td><!-- training schedule letter from RO added by laxmi B. on 30-12-2022-->
        <?php if(!empty($pdf_file)){?> <a href="<?php echo $pdf_file; ?>" target="_blank">Training Schedule At RAL</a><?php } else { echo "In process"; }?>
    <!-- Ro side Schedule letter added by laxmi B. on 27-01-2023-->
	<?php if(!empty($ro_side_schedule_letter)){ ?> | <a href="<?php echo'../../'.$ro_side_schedule_letter; ?>" target="_blank">Training Schedule At RO</a><?php } ?>
	</td>
	  <td>
		<!-- training completed letter from RAL added by laxmi B. on 30-12-2022-->
		<?php if(!empty($ral_letter)){?><a href="<?php echo $ral_letter; ?>" target="_blank">Reliving Letter From RAL</a><?php } else { echo "In process"; }?>
		<!-- training reliving letter from RO added by laxmi B. on 03-01-2023-->
		<?php if(!empty($relivingLetter)){ ?> | <a href="<?php echo $relivingLetter; ?>" target="_blank">Relieving Letter From RO</a><?php } ?>
	  </td>
	  <td>
		<!-- grant certificate added by laxmi B. on 05-01-2023-->
		<?php if(!empty($certificate)){ ?><a href="<?php echo'../../'.$certificate; ?>" target="_blank">Grant Certificate</a><?php } else { echo "In process"; } ?>
	  </td>


	  <!-- if application payment not confirm and each section not approve then withdraw button visible -->
	  <?php  if(((!empty($is_payment_confirm) && $is_payment_confirm != 'confirmed') || (!empty($all_section_status) && $all_section_status != 2)) && empty($rejectEntry)) { ?>
	  <td>
		<!-- for withdraw application if it is no longer process added button by laxmi Bhadade on 26-5-2023 -->
        <a  class="btn btn-warning rejectModel" title="you can withdraw application if it is not processing">Withdraw Application</a>
     </td>
	 <?php } ?>
    </tr>
    
    
  </tbody>
</table>
	

<!-- reject application model body -->
<!-- The Modal -->
<div id="myModal" class="modal">

 

<!--Modal content -->
<div class="modal-content">
 <div class="modal-header">
  
   <h4>Withdraw Application of Chemist Training</h4>
   <span class="close">&times;</span>
 </div>
 <div class="modal-body">
   <table id="rej-appl-table" class="table table-striped table-bordered">
	 <thead>
	   <tr>
		 <th>Application Type</th>
		 <th>Application Id</th>
		 <th>Remark/Reason</th>
		 <th>Action</th>
	 </tr>
	 </thead>
	 <tbody>
	   <tr>
	   <?php  echo $this->Form->create(null, array( 'enctype'=>'multipart/form-data', 'id'=>'rejectApp','class'=>'form_name'));  ?>
		 <td>
		  <?php echo $this->Form->control('application_type', array('type'=>'text', 'readonly'=>true, 'class'=>'cvOn cvReq cvAlphaNum applicationType', 'value'=>'Chemist Approval', 'label'=>false)) ;?>
		 </td>
		 <td><?php echo $this->Form->control('application_id', array('type'=>'text', 'readonly'=>true, 'class'=>'cvOn cvReq cvAlphaNum chemistId ', 'value' =>$_SESSION['username'], 'label'=>false)) ;?>
		 
	   </td>
		 
		 <td><?php  echo $this->Form->control('remark', array('type'=>'textarea', 'id'=>'remark', 'escape'=>false,  'placeholder'=>'Enter Remark/Reason', 'value'=>'','class'=>'cvOn cvReq cvAlphaNum reject',   'label'=>false)); ?>
		 <div><b class="errorClass text-red"></b></div></td>
		 <td><a class="btn btn-primary" type="submit" id="rejectBtn">Reject</a></td>
		 <?php  echo $this->Form->end();  ?>
	   </tr>
	 </tbody>
 </table>
 </div>
 <div class="modal-footer">
 
 </div>
</div> 
	</div>

<?php echo $this->Html->css('chemist/reject_application');?>
<?php echo $this->Html->Script('withdraw_application');?>

      
     

      

     

     