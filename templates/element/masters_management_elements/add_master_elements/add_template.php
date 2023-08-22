<div class="col-md-12">
	<div class="row">
		<div class="col-md-6">
			<label class="col-form-label">Short Description <span class="cRed">*</span></label>
			<?php echo $this->Form->control('description', array('type'=>'textarea', 'id'=>'description','class'=>'form-control', 'label'=>false, 'placeholder'=>'Enter Short Description Here')); ?>
			<span id="error_description" class="error invalid-feedback"></span>
		</div>

		<div class="col-md-6">
			<label class="col-form-label">SMS Message <span class="cRed">*</span></label>
			<?php echo $this->Form->control('sms_message', array('type'=>'textarea', 'id'=>'sms_message','class'=>'form-control', 'label'=>false, 'placeholder'=>'Enter SMS Message Here')); ?>
			<span id="error_sms_message" class="error invalid-feedback"></span>
		</div>

		<div class="col-md-6 mt-2">
			<div>
				<label class="col-form-label">Subject </label>
				<?php echo $this->Form->control('email_subject', array('type'=>'text', 'id'=>'email_subject', 'class'=>'form-control', 'label'=>false, 'placeholder'=>'Enter Subject Here')); ?>
				<span id="error_email_subject" class="error invalid-feedback"></span>
			</div>

			<div>
				<label class="col-form-label">For Module </label>
				<select name="for_module" id="for_module" class="form-control">
					<option value="0">Select</option>
					<option value="1">General Templates</option>
					<option value="2">Chemist Approval & Training</option>
					<option value="3">Management of Misgrading</option>
				</select>
				<span id="error_for_module" class="error invalid-feedback"></span>
			</div>

			<div>
				<label class="col-form-label">Template ID </label>
				<?php echo $this->Form->control('template_id', array('type'=>'text', 'id'=>'template_id', 'class'=>'form-control', 'label'=>false, 'placeholder'=>'Enter template id Here')); ?>
				<span id="error_template_id" class="error invalid-feedback"></span>
			</div>
		</div>

		

		<div class="col-md-6 mt-2">
			<label class="col-form-label">Email Message </label>
			<?php echo $this->Form->control('email_message', array('type'=>'textarea', 'class'=>'form-control','id'=>'email_message',  'label'=>false, 'placeholder'=>'Enter Email Message Here')); ?>
		</div>

		

		<div class="col-md-12 mt-2">
			<label class="badge badge-info">Send To :
			<?php
				$options = array('dmi'=>'DMI','lmis'=>'LIMS');
				$attributes = array('legend'=>false, 'id'=>'template_for');
				echo $this->Form->radio('template_for',$options,$attributes,);
			?>
			</label>

			<div id="dmi_roles" class="form-horizontal">
				<div class="card-body">
					<div class="boxformenus row color1">
						<div class="col-md-3">
							<?php
								#Applicant
								echo $this->Form->control('applicant', array('type'=>'checkbox', 'id'=>'applicant','label'=>' Applicant', 'escape'=>false));
								#MO/SMO (Scrutiny Officer)
								echo $this->Form->control('mo_smo', array('type'=>'checkbox', 'id'=>'mo_smo', 'label'=>' Scrutinizer', 'escape'=>false));
								#IO (Inspection Officer)
								echo $this->Form->control('io', array('type'=>'checkbox', 'id'=>'io', 'label'=>' Inspection Officer', 'escape'=>false));
							?>
						</div>
						<div class="col-md-3">
							<?php
								#RO/SO (Nodal Officer)
								echo $this->Form->control('ro_so', array('type'=>'checkbox', 'id'=>'ro_so', 'label'=>' Nodal Officer', 'escape'=>false));
								#Dy.AMA
								echo $this->Form->control('dy_ama', array('type'=>'checkbox', 'id'=>'dy_ama', 'label'=>' Dy.AMA', 'escape'=>false));
								#Chemist User
								echo $this->Form->control('chemist_user', array('type'=>'checkbox', 'id'=>'chemist_user', 'label'=>' Chemist User', 'escape'=>false));
							?>
						</div>
						<div class="col-md-3">
							<?php
								#Jt.AMA
								echo $this->Form->control('jt_ama', array('type'=>'checkbox', 'id'=>'jt_ama', 'label'=>' Jt.AMA', 'escape'=>false));
								#HO/MO/SMO
								echo $this->Form->control('ho_mo_smo', array('type'=>'checkbox', 'id'=>'ho_mo_smo', 'label'=>' Scrutiny(HO)', 'escape'=>false));
								#RO-Incharge
								echo $this->Form->control('ro_incharge', array('type'=>'checkbox', 'id'=>'ro_incharge', 'label'=>' RO Incharge', 'escape'=>false));
							?>
						</div>
						<div class="col-md-3">
							<?php
								#AMA
								echo $this->Form->control('ama', array('type'=>'checkbox', 'id'=>'ama', 'label'=>' AMA', 'escape'=>false));
								#DDO
								echo $this->Form->control('accounts', array('type'=>'checkbox', 'id'=>'accounts', 'label'=>' Accounts', 'escape'=>false));
							?>
						</div>
					</div>
				</div>
			</div>

			<div id="lmis_roles" class="form-horizontal">
				<div class="card-body">
					<div class="boxformenus row color1">
						<div class="col-md-3">
							<?php
								#Inward Officer
								echo $this->Form->control('inward_officer', array('type'=>'checkbox', 'id'=>'inward_officer', 'label'=>' Inward Officer', 'escape'=>false));
								#RAL/CAL-OIC
								echo $this->Form->control('ral_cal_oic', array('type'=>'checkbox', 'id'=>'ral_cal_oic', 'label'=>' RAL/CAL-OIC', 'escape'=>false));
								#Chemist
								echo $this->Form->control('chemist', array('type'=>'checkbox', 'id'=>'chemist', 'label'=>' Chemist', 'escape'=>false));
							?>
						</div>
						<div class="col-md-3">
							<?php
								#Chief Chemist
								echo $this->Form->control('chief_chemist', array('type'=>'checkbox', 'id'=>'chief_chemist', 'label'=>' Chief Chemist', 'escape'=>false));
								#Lab Incharge
								echo $this->Form->control('lab_incharge', array('type'=>'checkbox', 'id'=>'lab_incharge', 'label'=>' Lab In-charge', 'escape'=>false));
								#DOL
								echo $this->Form->control('dol', array('type'=>'checkbox', 'id'=>'dol', 'label'=>' DOL', 'escape'=>false));
							?>
						</div>
						<div class="col-md-3">
							<?php 
								#Inward Clerk
								echo $this->Form->control('inward_clerk', array('type'=>'checkbox', 'id'=>'inward_clerk', 'label'=>' Inward Clerk', 'escape'=>false));
								#Outward Clerk
								echo $this->Form->control('outward_clerk', array('type'=>'checkbox', 'id'=>'outword_clerk', 'label'=>' Outward Clerk', 'escape'=>false));
								#RO/SO Officer
								echo $this->Form->control('ro_so_officer', array('type'=>'checkbox', 'id'=>'ro_so_officer', 'label'=>' RO/SO Officer', 'escape'=>false));
							?>
						</div>
						<div class="col-md-3">
							<?php
								#RO/SO-OIC
								echo $this->Form->control('ro_so_oic', array('type'=>'checkbox', 'id'=>'ro_so_oic', 'label'=>' RO/SO-OIC', 'escape'=>false));
								#Accounts
								echo $this->Form->control('accounts', array('type'=>'checkbox', 'id'=>'accounts', 'label'=>' Accounts', 'escape'=>false));
								#Head Office
								echo $this->Form->control('head_office', array('type'=>'checkbox', 'id'=>'head_office', 'label'=>' Head Office', 'escape'=>false));
							?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>