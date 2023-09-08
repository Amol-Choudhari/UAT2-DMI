
	<?php
  define('INPUT_FIELD_CLASSES', 'form-control input-field');
  $class1 = INPUT_FIELD_CLASSES;
  ?>
	<section class="content form-middle">
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-8">
					<?php echo $this->Form->create(null,array('type'=>'file', 'enctype'=>'multipart/form-data')); ?>
				
						<div class="card card-secondary mt-5">
							<div class="card-header"><h3 class="card-title-new">Select Packer and Financial Year</h3></div>
							<div class="form-horizontal">
								<div class="card-body">
									<div class="form-group row">
										<label for="inputEmail3" class="col-sm-3 col-form-label">Select Packer: <span class="cRed">*</span></label>
										<div class="col-sm-6">
											<?php echo $this->Form->control('packerid', array(
                        'type'=>'select',
                        'empty'=>'Select Packer',
                        'id'=>'owner',
                        'options'=>$alloted_chemist,
                        'label'=>false,
                        'class'=>$class1,
                        )); ?>
											<span id="error_oldpass" class="error invalid-feedback"></span>
										</div>
									</div>

									<div class="form-group row">
										<label for="financialYear" class="col-sm-3 col-form-label">Select Financial Year: <span class="cRed">*</span></label>
									<div class="col-sm-6">
											<?php 
												echo $this->Form->control('financialYear', array(
                        'type'=>'select',
                        'empty'=>'Select Financial Year',
                        'id'=>'co-owner',
                        'options'=>$finacialYearsArray,
                        'label'=>false,
                        'class'=>$class1,
                        )); ?>
											<div class="invalid-feedback">Please select a financial year.</div>
										</div>
									</div>

								<div class="card-footer cardFooterBackground">
                  <?php echo $this->Form->control('Continue', array('type' => 'submit', 'name' => 'continue-btn', 'label' => false, 'class' => 'btn btn-success', 'id' => 'financialYear')); ?>
								</div>
							</div>
						</div>
					<?php 
					echo $this->Form->end(); 
					echo $this->Html->script('element/application_forms/bgr/bgr_calculation');
					?>
					
				</div>
			</div>
		</div>
	</section>
</div>


		
