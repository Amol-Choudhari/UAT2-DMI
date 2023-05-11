
	<?php if (in_array($final_submit_status,array('pending','replied','referred_back'))) { ?>

		 	<div class="col-lg-8">
				<div class="alert alert-info alert-dismissible">
					<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
					<h5><i class="icon fas fa-info"></i> Please Note !</h5>
					<p>Your application for registration has been saved and finally submitted, to check status please click on "Registration Status" button. Thankyou</p>
				</div>
			</div>

	<?php } elseif ($final_submit_status == 'approved') { ?>

			<div class="col-lg-8">
				<div class="alert alert-info alert-dismissible">
					<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
					<h5><i class="icon fas fa-info"></i> Please Note !</h5>
					<p>Your application for registration has been successfully verified. Thankyou</p>
				</div>
			</div>

	<?php } elseif ($final_submit_status == '') { ?>

		<div class="col-lg-8">
			<div class="alert alert-info alert-dismissible">
				<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
				<h5><i class="icon fas fa-info"></i> Please Note !</h5>
				<p class="">You need to register your application as a chemist on online system, so please click "Register Application" button to fill your details and apply. Thankyou</p>
			</div>
		</div>

	<?php } ?>
	<!-- training schedule letter from RO added by laxmi B. on 30-12-2022-->
    <?php if(!empty($pdf_file)){?>
    	<div class="col-lg-2  float-left ">
     <h3 class=" text-white1  text-center btn btn-success"><a href="<?php echo $pdf_file; ?>" class =" text-white"target="_blank"><b>Training Schedule At RAL</b></a></h3>
    </div>
     <?php }?>

     <!-- training completed letter from RAL added by laxmi B. on 30-12-2022-->
    <?php if(!empty($ral_letter)){?>
    	<div class="col-lg-2  float-left ">
     <h3 class=" text-white1  text-center btn btn-success"><a href="<?php echo $ral_letter; ?>" class =" text-white"target="_blank"><b>Reliving Letter From RAL	</b></a></h3>
    </div>
     <?php }?>

      <!-- Ro side Schedule letter added by laxmi B. on 27-01-2023-->
     <?php if(!empty($ro_side_schedule_letter)){ ?>
    	<div class="col-lg-2  float-left">
     <h3 class=" text-white1 text-center btn btn-success"><a href="<?php echo'../../'.$ro_side_schedule_letter; ?>" class =" text-white"target="_blank"><b>Training Schedule At RO</b></a></h3>
    </div>
     <?php } ?>
     

      <!-- training reliving letter from RO added by laxmi B. on 03-01-2023-->
     <?php if(!empty($relivingLetter)){ ?>
    	<div class="col-lg-2  float-left">
     <h3 class=" text-white1 text-center btn btn-success"><a href="<?php echo $relivingLetter; ?>" class =" text-white"target="_blank"><b>Relieving Letter From RO</b></a></h3>
    </div>
     <?php } ?>

     <!-- grant certificate added by laxmi B. on 05-01-2023-->
     <?php if(!empty($certificate)){ ?>
    	<div class="col-lg-2  float-left">
     <h3 class=" text-white1 text-center btn btn-success"><a href="<?php echo'../../'.$certificate; ?>" class =" text-white"target="_blank"><b>Certificate</b></a></h3>
    </div>
     <?php } ?>

     