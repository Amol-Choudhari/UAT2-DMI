<div class="content-wrapper">
	<section class="content form-middle">
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-8">
					<?php echo $this->Form->create(null, array('type'=>'file', 'id'=>'sms_test', 'enctype'=>'multipart/form-data')); ?>
						<div class="card card-secondary">
							<div class="card-header"><h3 class="card-title-new">SMS TEST</h3></div>
							<div class="form-horizontal">
								<div class="card-body">
									<div class="form-group row">
										<div class="col-sm-6">
											<label for="emailsignup" class="youmail">Email <span class="cRed">*</span></label>
											<?php echo $this->Form->control('email', array('label'=>false, 'id'=>'email', 'escape'=>false, 'class'=>'input-field form-control', 'placeholder'=>'Please enter email id')); ?>
											<span class="error invalid-feedback" id="error_email"></span>
										</div>
										<div class="col-sm-6">
											<label for="passwordsignup" class="youpasswd">Mobile No <span class="cRed">*</span></label>
											<?php echo $this->Form->control('mobile', array('type'=>'tel', 'escape'=>false, 'id'=>'mobile', 'label'=>false, 'class'=>'input-field form-control', 'minlength'=>'10', 'maxlength'=>'10','placeholder'=>'Please enter mobile no.')); ?>
											<span class="error invalid-feedback" id="error_mobile"></span>
										</div>
										<div class="col-sm-6">
											<label>Template With Variable <span class="cRed">*</span></label>
											<?php echo $this->Form->control('template', array('type'=>'textarea', 'escape'=>false, 'id'=>'template', 'label'=>false, 'class'=>'input-field form-control','placeholder'=>'Enter the desired template with temp variables without #var#')); ?>
											<span class="error invalid-feedback" id="error_mobile"></span>
										</div>
									</div>
								</div>
								<div class="card-body cardFooterBackground">
									<?php echo $this->Form->control('Send SMS', array('type'=>'button', 'id'=>'send_sms', 'label'=>false, 'class'=>'btn btn-success mtminus12pb7')); ?>
								</div>
							</div>
						</div>
					<?php echo $this->Form->end(); ?>
				</div>
			</div>
		</div>
	</section>
</div>
