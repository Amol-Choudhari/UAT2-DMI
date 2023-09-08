<?php
	echo $this->Html->css('../multiselect/jquery.multiselect');
	echo $this->Html->script('../multiselect/jquery.multiselect');
	
?>

<?php echo $this->Form->create(null,array('type'=>'file', 'enctype'=>'multipart/form-data', 'id'=>$section)); ?>
 <input type="hidden" id="status" value='<?php echo isset($checkIfgrant['status'])?$checkIfgrant['status']:""; ?>'>
	<div id="form_outer_main" class="col-md-12 form-middle">
		<?php if(!empty($checkIfgrant) && $checkIfgrant['status'] == 'Granted'){ ?>
				<h5 class="mt-1 mb-2 tacfw700">Report Details Section</h5>
			<?php }else {?>
		<h5 class="mt-1 mb-2 tacfw700">Commodity wise Grading Data Entery Form</h5>
		<?php } ?>
			<div id="form_inner_main" class="card card-success">
				<?php if(!empty($checkIfgrant) && $checkIfgrant['status'] == 'Granted'){ ?>
						<div class="card-header"><h3 class="card-title">Note: Your Report is Already Applied</h3></div>
					<?php }else {?>
					<div class="card-header"><h3 class="card-title">Commodity wise Grading Data Entery Form</h3></div>
					<?php } ?>
						 <div class="tank_table form-horizontal " >
							<div class="card-body">
								<?php
									if(!isset($checkIfgrant)){ ?>
										<!-- call table view form element with ajax call -->
											<?php echo $this->element('application_forms/bgr/analysis_table/commodity_wise_reports_form_tbl'); ?>
								<?php }elseif(isset($checkIfgrant) && $checkIfgrant['status'] == 'Granted'){ ?>
										<table class="table">
												<thead>
													<tr>
														<th scope="col">#</th>
														<th scope="col">Packer ID</th>
														<th scope="col">Tenure</th>
														<th scope="col">Action</th>
													</tr>
												</thead>
												<tbody>
													
													<tr>
														<?php if(!empty($checkIfgrant)){ ?>
																<th scope="row">1</th>
																<td><?php echo $checkIfgrant['customer_id']; ?></td>
																<td>
																	<?php 
																		$period_from = $checkIfgrant['period_from'];
																		$period_to = $checkIfgrant['period_to'];
																		$finacialYear = $period_from.' - '.$period_to;
																		echo $finacialYear;
																	?>
																</td>
																<td><?php
																	if (!empty($checkIfgrant['pdf_file'])) { ?>
																			<a id="other_upload_docs_value"
																				target="_blank"
																				href="<?php echo str_replace("D:/xampp/htdocs", "", $checkIfgrant['pdf_file']); ?>">
																				Download Report
																			</a>
																	<?php } else {
																			echo "No Document Provided";
																	} ?>
																	</td>
															<?php }else{?>
																<td>No Record Found!</td>
															<?php } ?>
													</tr>
												</tbody>
											</table>
								<?php } ?>
            </div>
					</div>
						<!-- Modal -->
							<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
								<div class="modal-dialog modal-lg" role="document">
									<div class="modal-content">
										<div class="modal-header">
											<h5 class="modal-title" id="exampleModalLabel">Help</h5>
										</div>
										<div class="modal-body">
											<div class="note">
											<div class="note">
												<p class="important"><strong>Note for Users:</strong> </p>
												<h5>Food Safety Parameters Report System</h5>
												<p>This system follows specific criteria for displaying certain fields based on the laboratory's NABL accreditation status.</p>
													<p>If the laboratory is not NABL accredited, then the system is not allowed to enter data of food safety parameters report. The system will not display fields:</p>
											<ul>
													<li>"Name of Laboratory which tested the sample"</li>
													<li>"Report no. and Date"</li>
													<li>"Remarks"</li>
											</ul>
											<p>When the laboratory is NABL accredited, the system will display these fields.</p>
											</div>
										</div>
										<div class="modal-footer">
											<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
										</div>
									</div>
								</div>
							</div>

      </div>
    </div>
  <?php
	
		echo $this->Html->script('element/application_forms/bgr/commodity_wise_report_crud');
    echo $this->Html->script('element/application_forms/bgr/commodity_wise_report_form_script');
		echo $this->Html->script('element/application_forms/bgr/bgr_calculation');
    echo $this->Html->css('element/application_forms/bgr/bianually_report_style');
		echo $this->Html->script('element/application_forms/bgr/bgr_file_uploads_validation');
		echo $this->Html->script('element/application_forms/bgr/bgr_replica_allotment_validation');
		
  ?>
