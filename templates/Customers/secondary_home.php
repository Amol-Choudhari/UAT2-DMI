<div class="content-wrapper">
	<section id="applicanthome" class="content">
		<div class="applhome container-fluid">

			<!-- This new block is added on 28-04-2023 to show In-process Application Message by Amol -->
			<?php if (!empty($InprocessMsg)) { ?>
				<div class="row">
					<div class="col-lg-12">
						<div class="alert alert-info alert-dismissible">
							<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
							<h5><i class="icon fas fa-info"></i> Please Note !</h5>
							<?php echo $InprocessMsg; ?>
						</div>
					</div>
				</div>

			<?php } ?>

			<div id="accordion">
				<div class="card bsc">
					<div class="card-header" id="headingOne">
						<h5 class="mb-0">
							<button class="btn btn-link" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
								Applications / Information
							</button>
						</h5>
					</div>

					<div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordion">
						<div class="card-body">
							<?php
								$customer_id = $_SESSION['username'];

								#check the application is Cancelled . if it is Cancelled  block all will appear and hiding the things on Secondary Dashboard
								if (!empty($cancelled_record)) {

									echo $this->element('customer_elements/dash_messages/cancellation_message');
									echo $this->element('customer_elements/pdf_table_view/grant/cancellation_grant');

								} else {

									#check the application is Suspended . if it is surrender block all the things on Secondary Dashboard
									if (!empty($suspension_record)) {

										echo $this->element('customer_elements/dash_messages/suspension_message');
										echo $this->element('customer_elements/pdf_table_view/grant/suspension_grant');

									} else {

                                        #check the application is surrendered . if it is surrender block all the things on Secondary Dashboard
                                        if ($soc_final_submit_status == 'approved' && $soc_final_submit_level == 'level_3') {
                                            echo $this->element('customer_elements/dash_messages/surrender_appl_msg');
                                        } else {

											/* For Misgrading Actions - Dashboard Messages
											if (!empty($actionSubmitted)) {
												echo $this->element('customer_elements/dash_messages/action_taken_msg');
											}
											*/

											#For Show Cause Actions - Dashboard Messages
											if (!empty($showCauseNotice)) {
												echo "<p class='alert alert-info'>Please Click on the Show Cause Notice Tab for the Details</p>";
											}


											#For Advance Payment - Dashboard Messages
											if (!empty($advance_payment_status)) {
												echo $this->element('customer_elements/dash_messages/advance_payment');
											}

                                            #For Surrender - Dashboard Messages & Application PDF
                                            if ($soc_final_submit_status != 'no_final_submit') {
                                                echo $this->element('customer_elements/dash_messages/surrender_appl_msg');
                                            }

											#For General Applications - Messages & PDFs
											if ($final_submit_status == 'no_final_submit') {
												echo $this->element('customer_elements/dash_messages/new_or_old');
											} else {

												if ($is_already_granted == 'no') {

													#This Below Block is added to Show the Message when the application is rejected -= Akash [25-11-2022]
													if($is_appl_rejected != NULL){
														echo $this->element('customer_elements/dash_messages/for_rejected');
													}

													#For Displaying the Application PDF Table#
													echo $this->element('customer_elements/pdf_table_view/application/general_application');

												} else if (!($final_submit_status == 'approved' && $final_submit_level == 'level_3')) {

													#This Below Block is added to Show the Message when the application is rejected - Akash [25-11-2022]
													if($is_appl_rejected != NULL){
														echo $this->element('customer_elements/dash_messages/for_rejected');
													}else{
														echo $this->element('customer_elements/dash_messages/for_old_appl_saved');
													}
												}
											}

											$show_grant_table = null;

											//check if primary application approved
											if ($final_submit_status == 'approved' && $final_submit_level == 'level_3') {

												//check if old application
												if ($is_already_granted == 'yes') {

													//check if old application online renewal granted
													if ($renewal_final_submit_status == 'approved' && $renewal_final_submit_level == 'level_3') {
														$show_grant_table = 'yes';
													} else {
														$show_grant_table = 'no';
													}

												} else { //if new application
													$show_grant_table = 'yes';
												}

												#For Displaying the Grant PDF Table#
												if ($show_grant_table == 'yes') {
													echo $this->element('customer_elements/pdf_table_view/grant/gen_grant');
												}

												#For Displaying the Renewal PDF Table#
												if (!empty($renewal_final_submit_details)) {
													echo $this->element('customer_elements/pdf_table_view/grant/renewal');
												}

											}

											#To Displaying Message of renewal status
											if ($is_already_granted == 'yes' && $show_grant_table == 'no' && empty($renewal_final_submit_details)) {

												echo $this->element('customer_elements/dash_messages/for_renewal_stats');

												//this condition is added to show table for old appl esigned certificate pdf, if generated by RO/SO
												//on 21-06-2026 by Amol
												if(!empty($checkOldCertEsigned)){ ?>
													<table class="table m-0 table-bordered">
														<thead>
															<th>Applicant Id</th>
															<th>Certificate Pdf</th>
															<th>Date</th>
														</thead>

														<tbody>
															<td><?php echo $checkOldCertEsigned['customer_id']; ?></td>
															<td><a target="_blank" href="<?php echo $checkOldCertEsigned['pdf_file']; ?>">Certificate</td>
															<td><?php echo $checkOldCertEsigned['created']; ?></td>
														</tbody>
													</table>
												<?php }

											}

											#To Displaying Message of applied for renewal
											if (!empty($renewal_final_submit_details)) {
												if ($show_renewal_btn == 'yes') {
													echo $this->element('customer_elements/dash_messages/if_renewal_applied');
												}
											}

											if ($show_applied_to_popup == 'yes') {
												echo $this->element('firm_applying_to_view/applying_to_view');
											}
										}
									}
								}



							?>
						</div>
					</div>
				</div>
				<!--For Surrender Applications by Akash [16-08-2023]-->
				<?php if(!empty($soc_pdfs_records) || !empty($surrender_grant_certificate)) { ?>

					<div class="card bsc">
						<div class="card-header" id="headingTwo">
							<h5 class="mb-0">
								<button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
								Surrender Application
								</button>
							</h5>
						</div>
						<div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
							<div class="card-body">
								<?php
									#For Surrender Application PDF Table View - Akash [2022]
									if(!empty($soc_pdfs_records)) {
										echo $this->element('customer_elements/pdf_table_view/application/surrender_application_pdf');
									}

									#For Surrender Grant PDF Table View - Akash [2022]
									if(!empty($surrender_grant_certificate)) {
										echo $this->element('customer_elements/pdf_table_view/grant/surr_grant');
									}
								?>
							</div>
						</div>
					</div>

				<?php } ?>

				<!--For 15 Digit Applications-->
				<?php if(!empty($appl_15_digit_pdfs) || !empty($cert_15_digit_pdfs)) { ?>

					<div class="card bsc">
						<div class="card-header" id="headingTwo">
							<h5 class="mb-0">
								<button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
								15-Digit Code Application
								</button>
							</h5>
						</div>
						<div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
							<div class="card-body">
								<?php
									#For 15-Digit Code Application PDF Table View - Amol [2022]
									if(!empty($appl_15_digit_pdfs)) {
										echo $this->element('customer_elements/pdf_table_view/application/fdc_application');
									}

									#For 15-Digit Code Grant PDF Table View - Amol [2022]
									if(!empty($cert_15_digit_pdfs)) {
										echo $this->element('customer_elements/pdf_table_view/grant/fdc_grant');
									}
								?>
							</div>
						</div>
					</div>

				<?php } ?>

				<!--For E Code Applications-->
				<?php if(!empty($appl_e_code_pdfs) || !empty($cert_e_code_pdfs)) { ?>

					<div class="card bsc">
						<div class="card-header" id="headingThree">
							<h5 class="mb-0">
								<button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
								E-Code Application
								</button>
							</h5>
						</div>
						<div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
							<div class="card-body">
								<?php
									#For E-Code Application PDF Table View - Amol [2022]
									if(!empty($appl_e_code_pdfs)) {
										echo $this->element('customer_elements/pdf_table_view/application/ecode_application');
									}

									#For E-Code Grant PDF Table View - Amol [2022]
									if(!empty($cert_e_code_pdfs)) {
										echo $this->element('customer_elements/pdf_table_view/grant/ecode_grant');
									}
								?>
							</div>
						</div>
					</div>

				<?php } ?>

				<!--For ADP Applications-->
				<?php if(!empty($appl_adp_pdfs_records) || !empty($appl_adp_grant_pdfs)) { ?>

					<div class="card bsc">
						<div class="card-header" id="headingThree">
							<h5 class="mb-0">
								<button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
								Approval of Designated Person Application
								</button>
							</h5>
						</div>
						<div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
							<div class="card-body">
								<?php
									#For ADP Application PDF Table View - Shankhpal [18/11/2022]
									if(!empty($appl_adp_pdfs_records)) {
										echo $this->element('customer_elements/pdf_table_view/application/adp_application');
									}

									#For ADP Grant PDF Table View - Shankhpal [18/11/2022]
									if(!empty($appl_adp_grant_pdfs)) {
										echo $this->element('customer_elements/pdf_table_view/grant/adp_grant');
									}
								?>
							</div>
						</div>
					</div>

				<?php } ?>

				<!--For  Show Cause Notices-->
				<?php if(!empty($showCauseNotice)) { ?>

					<div class="card bsc">
						<div class="card-header" id="headingThree">
							<h5 class="mb-0">
								<button class="btn btn-link collapsed cRed" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
								Show Cause Notice
								</button>
							</h5>
						</div>
						<div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
							<div class="card-body">
								<?php
									#For Show Cause Actions - Dashboard Messages
									if (!empty($showCauseNotice)) {
										echo $this->element('customer_elements/dash_messages/show_cause_notice');
									}
							 		echo $this->element('customer_elements/pdf_table_view/application/show_cause_notice');
							 	?>
							</div>
						</div>
					</div>

				<?php } ?>

				<!--For Change Applications-->
				<?php if(!empty($appl_change_records) || !empty($appl_change_grant_pdfs)) { ?>

					<div class="card bsc">
						<div class="card-header" id="headingThree">
							<h5 class="mb-0">
								<button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
								Change Request Application
								</button>
							</h5>
						</div>
						<div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
							<div class="card-body">
								<?php
									if(!empty($appl_change_records)) {
										echo $this->element('customer_elements/pdf_table_view/application/mod_application');
									}

									if(!empty($appl_change_grant_pdfs)) {
										echo $this->element('customer_elements/pdf_table_view/grant/mod_grant');
									}
								?>
							</div>
						</div>
					</div>

				<?php } ?>

				<?php
					/*Comment: Added as per suggestion:
					Suggestion: One Copy of inspection report needs to be sent to
					packer for information and compliance of shortcomings after submission by inspection Officer.
					Author: Shankhpal Shende
					Date:17/05/2023*/
					if(!empty($approved_routine_inspection_pdf)){

						echo $this->element('customer_elements/pdf_table_view/grant/rti_grant');
					}
				?>
			</div>
		</div>
	</section>
</div>

<?php echo $this->element('line_track'); ?>
