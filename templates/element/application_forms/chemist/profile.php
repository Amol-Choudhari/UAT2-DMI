<?php // added class below form-for-file-too by laxmi 06-07-2023?>
<?php echo $this->Form->create(null, array('type'=>'file', 'enctype'=>'multipart/form-data', 'id'=>'profile','class'=>'form_name form-for-file-too')); ?>

<div id="form_outer_main" class="card card-success form_outer_class">
	<div class="card-header"><h3 class="card-title-new">Profile</h3></div>
	<div class="form-horizontal">
		<div class="card-body">
			<div class="row">
				<div class="col-md-12 row">
					<div class="col-md-2">
						<label for="field3"><span>First Name <span class="cRed">*</span></span></label>
					</div>
					<div class="col-md-4">
						<?php echo $this->Form->control('first_name', array('type'=>'text', 'id'=>'firstname', 'escape'=>false, 'value'=>$section_form_details[0]['first_name'], 'placeholder'=>'Enter First Name', 'class'=>'cvOn cvReq cvAlphaNum form-control', 'maxlength'=>255, 'disabled'=>true, 'label'=>false)); ?>
						<div class="err_cv"></div>
					</div>
					<div class="col-md-2">
						<label for="field3"><span>Last Name <span class="cRed">*</span></span></label>
					</div>
					<div class="col-md-4">
						<?php echo $this->Form->control('last_name', array('type'=>'text', 'id'=>'lastname', 'escape'=>false, 'value'=>$section_form_details[0]['last_name'], 'placeholder'=>'Enter Last Name', 'class'=>'cvOn cvReq cvAlphaNum form-control', 'maxlength'=>255, 'disabled'=>true, 'label'=>false)); ?>
						<div class="err_cv"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="form-horizontal">
		<div class="card-body">
			<div class="row">
				<div class="col-md-12 row">
                     <!-- added middle name type and middle name by laxmi on 04-07-2023 -->
                    <div class="col-md-2">
						<label for="field3"><span>Parent Name<span class="cRed">*</span></span>	</label>
					</div>
					<div class="col-md-2">
						<?php echo $this->Form->control('middle_name_type', array('type'=>'select', 'id'=>'middle_name_type', 'escape'=>false, 'options'=>$middle_type, 'empty'=>'Select type','value'=>$section_form_details[0]['middle_name_type'], 'class'=>'cvOn cvReq form-control', 'label'=>false)); ?>
						<div class="err_cv"></div>
					</div>
					<div class="col-md-2">
					<?php echo $this->Form->control('middle_name', array('type'=>'text', 'id'=>'middle_name', 'escape'=>false, 'value'=>$section_form_details[0]['middle_name'], 'placeholder'=>'Enter Parent Name', 'class'=>'cvOn cvReq cvAlphaNum cvAlpha form-control', 'maxlength'=>255,  'label'=>false)); ?>
						<div class="err_cv"></div>
					</div>



					<div class="col-md-2">
						<label for="field3"><span>State/Region <span class="cRed">*</span></span>	</label>
					</div>
					<div class="col-md-4">
						<?php echo $this->Form->control('state', array('type'=>'select', 'id'=>'state', 'escape'=>false, 'options'=>$state_list, 'empty'=>'Select State','value'=>$section_form_details[0]['state'], 'class'=>'cvOn cvReq form-control', 'label'=>false)); ?>
						<div class="err_cv"></div>
					</div>
					
				</div>
			</div>
		</div>
	</div>
	<div class="form-horizontal">
		<div class="card-body">
			<div class="row">
				<div class="col-md-12 row">
                      
				<div class="col-md-2">
						<label for="field3"><span>District <span class="cRed">*</span></span></label>
					</div>
					<div class="col-md-4">
						<div>
							<select name="district" id="district" class="cvOn cvReq form-control">
								<option value="">Select District</option>
								<?php if (!empty($distict_list)) { foreach ($distict_list as $key => $value) { ?>
									<option value="<?php echo $key; ?>" <?php if ($section_form_details[0]['district'] == $key) { echo 'selected'; } ?> ><?php echo $value; ?></option>
								<?php } } ?>
							</select>
							<?php //echo $this->Form->control('district', array('type'=>'select', 'id'=>'district', 'option'=>$districtarray, 'escape'=>false,  'empty'=>'Select District', 'class'=>'cvOn cvReq form-input', 'label'=>false)); ?>
						</div>
						<div class="err_cv"></div>
					</div>



					<div class="col-md-2">
						<label for="field3"><span>Email Id <span class="cRed">*</span></span></label>
					</div>
					<div class="col-md-4">
						<?php echo $this->Form->control('email', array('type'=>'email', 'id'=>'email', 'escape'=>false, 'disabled'=>true, 'value'=>base64_decode($section_form_details[0]['email']), 'placeholder'=>'Enter Email', 'class'=>'cvOn cvReq cvEmail form-control', 'label'=>false)); //for email encoding ?>
						<div class="err_cv"></div>
					</div>

					
				</div>
			</div>
		</div>
	</div>
	<div class="form-horizontal">
		<div class="card-body">
			<div class="row">
				<div class="col-md-12 row">

				<div class="col-md-2">
						<label for="field3"><span>Pin Code <span class="cRed">*</span></span>	</label>
					</div>
					<div class="col-md-4">
						<?php echo $this->Form->control('pin_code', array('type'=>'text', 'id'=>'pin_code', 'escape'=>false, 'placeholder'=>'Enter Pincode','maxlength'=>6, 'minlength'=>6,'value'=>$section_form_details[0]['pin_code'], 'class'=>'cvOn cvReq cvNum form-control', 'label'=>false)); ?>
						<div class="err_cv"></div>
					</div>

					<div class="col-md-2">
						<label for="field3"><span>Mobile No. <span class="cRed">*</span></span></label>
					</div>
					<div class="col-md-4">
						<?php echo $this->Form->control('mobile', array('type'=>'text', 'id'=>'mobile', 'escape'=>false, 'placeholder'=>'Enter Mobile Number', 'maxlength'=>'10', 'minlength'=>'10','value'=>base64_decode($section_form_details[0]['mobile_no']), 'class'=>'cvOn cvReq cvNum form-control', 'disabled'=>true, 'label'=>false)); ?>
						<div class="err_cv"></div>
					</div>

					
				</div>
			</div>
		</div>
	</div>
	<div class="form-horizontal">
		<div class="card-body">
			<div class="row">
				<div class="col-md-12 row">

				<div class="col-md-2">
						<label for="field3"><span>DOB <span class="cRed">*</span></span></label>
					</div>
					<div class="col-md-4">
						<?php echo $this->Form->control('dob', array('type'=>'text', 'id'=>'dob', 'escape'=>false, 'placeholder'=>'Enter DD/MM/YYYY', 'maxlength'=>'10', 'minlength'=>'10','value'=>chop($section_form_details[0]['dob'],"00:00:00"), 'class'=>'cvOn cvReq cvDate form-control', 'disabled'=>true, 'label'=>false)); ?>
						<div class="err_cv"></div>
					</div>


					<div class="col-md-2">
						<label for="field3"><span>Gender <span class="cRed">*</span></span></label>
					</div>
					<div class="col-md-4">
						<?php $options = array('male' => 'Male',  'female' => 'Female');
							  $attributes = array('legend' => false,'name'=>'gender','value' =>$section_form_details[0]['gender'],'class'=>'cvOn cvReq');
							  echo $this->Form->radio('type', $options, $attributes); ?>
						<div class="err_cv"></div>
					</div>
					
				</div>
			</div>
		</div>
	</div>
	<div class="form-horizontal">
		<div class="card-body">
			<div class="row">
				<div class="col-md-12 row">
					<div class="col-md-2">
					<label for="field3"><span>Address1 <span class="cRed">*</span></span></label>
					</div>
					<div class="col-md-4">
						<?php echo $this->Form->control('address', array('type'=>'text', 'id'=>'address1', 'escape'=>false, 'value'=>$section_form_details[0]['address'], 'placeholder'=>'Enter Address', 'class'=>'cvOn cvReq form-control', 'label'=>false)); ?>
						<div class="err_cv"></div>
					</div>


					<div class="col-md-2">
						<label for="field3"><span>Address2 </span></label>
					</div>
					<div class="col-md-4">
						<?php echo $this->Form->control('address_1', array('type'=>'text', 'id'=>'address1', 'escape'=>false, 'value'=>$section_form_details[0]['address_1'], 'placeholder'=>'Enter Address', 'class'=>' cvNotReq form-control', 'label'=>false)); ?>
					</div>

					
				</div>
			</div>
		</div>
	</div>

	<div class="form-horizontal">
		<div class="card-body">
			<div class="row">
				<div class="col-md-12 row">

				<div class="col-md-2">
						<label for="field3"><span>ID (Select any one). <span class="cRed">*</span></span></label>
					</div>
					<div class="col-md-2">
						<?php echo $this->Form->control('document', array('type'=>'select', 'id'=>'document', 'options'=>$document_lists, 'value'=>$section_form_details[0]['document'],'empty'=>'Select Document Type', 'label'=>false, 'class'=>'cvOn cvReq form-control')); ?>
						<div class="err_cv"></div>
					</div>
					<div class="col-md-2">
						<?php echo $this->Form->control('document_id_no', array('type'=>'text', 'id'=>'document_id_no', 'escape'=>false, 'placeholder'=>'Enter ID Number','value'=>$section_form_details[0]['document_id_no'], 'class'=>'cvOn cvReq cvAlphaNum form-control', 'label'=>false)); ?>
						<div class="err_cv"></div>
					</div>
				 
				 
				 
				 <?php //below condtion added for chemist to upload document receipt and in all file type added class filetype by laxmi [04-07-2023]
					 if(!empty($_SESSION['application_type']) && $_SESSION['application_type'] == 4 ){?>
					<div class="col-md-2">
						<label for="field3"><span> Upload ID<span class="cRed">*</span></span>	</label>
					</div>
					<div class="col-md-4">
						<?php echo $this->Form->control('document_receipt',array('type'=>'file', 'id'=>'document_receipt' ,'multiple'=>'multiple','class'=>'cvOn cvReq form-control filetype', 'label'=>false)); ?>
						<?php echo $this->Form->control('document_receipt',array('type'=>'hidden', 'class'=>'hidden_doc selectedFile', 'value'=>$section_form_details[0]['document_receipt'], 'label'=>false)); ?>
						<div class="err_cv"></div>
						<p class="file_limits">File type: pdf & jpg & Max-size:2mb</p>
						<?php // if document uploaded then it visible added by laxmi [07-07-2023]
						  if(!empty($section_form_details[0]['document_receipt'])){
							$reciept = $section_form_details[0]['document_receipt'];?>
                            <a href = "<?php echo $reciept; ?>" target = "_blank" > Uploaded ID </a>
						 <?php }
						?>
					</div>

                     <?php } ?>
				</div>
			</div>
		</div>
	</div>






	<div class="form-horizontal">
		<div class="card-body">
			<div class="row">
				<div class="col-md-12 row">
					<div class="col-md-2">
						<label for="field3"><span>Profile Photo Upload<span class="cRed">*</span></span>	</label>
					</div>
					<div class="col-md-4">
						<?php echo $this->Form->control('profile_photo',array('type'=>'file', 'id'=>'profile_photo', 'class'=>'cvOn cvReq form-control filetype file_profile', 'label'=>false)); ?>
						<?php echo $this->Form->control('profile_photo_hidden',array('type'=>'hidden', 'id'=>'profile_img','class'=>'hidden_doc selectedFile', 'value'=>$section_form_details[0]['profile_photo'], 'label'=>false)); ?>
						<div class="err_cv"></div>
						<p class="file_limits">File type: jpg & Max-size:2mb</p>
					</div>

					<div class="col-md-2">
						<label for="field3"><span>Signature Upload <span class="cRed">*</span></span>	</label>
					</div>
					<div class="col-md-4">
						<?php echo $this->Form->control('signature_photo',array('type'=>'file', 'id'=>'signature_photo', 'class'=>'cvOn cvReq form-control filetype file_sign', 'label'=>false)); ?>
						<?php echo $this->Form->control('signature_photo_hidden',array('type'=>'hidden', 'id'=>'sign_img','class'=>'hidden_doc selectedFile', 'value'=>$section_form_details[0]['signature_photo'], 'label'=>false)); ?>
						<div class="err_cv"></div>
						<p class="file_limits">File type: jpg & Max-size:2mb</p>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="form-horizontal">
		<div class="card-body mb-2">
			<div class="row">
				<div class="col-md-12 row">
					<div class="col-md-2">
						<label for="field3"><span>Upload View</span>	</label>
					</div>
					<div class="col-md-4 chemist_doc_div">
						<!-- added below line by laxmi to preview photo 08-09-2023 -->
					<img src="" id="profile_photo_prev" alt="photo image" width="auto" height="80px" class=" " />
					<img src="<?php echo $section_form_details[0]['profile_photo'] ?>" width="auto" height="80px" class="chemist_doc profileImg">
					</div>

					<div class="col-md-2">
						<label for="field3"><span>Upload View</span>	</label>
					</div>
					<div class="col-md-4 chemist_doc_div">
						<!-- added below line by laxmi to preview photo 08-09-2023 -->
					 <img src="" id="profile_sign_prev" alt="sign image" width="auto" height="80px" class=" " />
					<img src="<?php echo $section_form_details[0]['signature_photo'] ?>" width="auto" height="80px" class="chemist_doc profilesign">
					</div>
				</div>
			</div>
		</div>
	</div>

		<?php echo $this->Form->control('application_dashboard', array('type'=>'hidden', 'id'=>'application_dashboard', 'value'=>$_SESSION['application_dashboard'])); ?>

</div>
<?php echo $this->Html->script('element/application_forms/chemist/chemist_profile'); ?>