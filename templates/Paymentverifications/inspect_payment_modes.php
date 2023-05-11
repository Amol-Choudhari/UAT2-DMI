<?php ?>
<div class="content-wrapper">
	<div class="content-header">
		<div class="container-fluid">
			<div class="row mb-2">
				<div class="col-sm-6">
					<label class="badge badge-primary">Verify Payment</label>
				</div>
				<div class="col-sm-6">
					<ol class="breadcrumb float-sm-right">
						<li class="breadcrumb-item"><?php echo $this->Html->link('Dashboard', array('controller' => 'dashboard', 'action'=>'home'));?></li>
						<li class="breadcrumb-item active">Verify Payment Details</li>
					</ol>
				</div>
			</div>
		</div>
	</div>

	<section class="content form-middle">
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-10">
					<?php echo $this->Form->create(null,array('type'=>'file', 'enctype'=>'multipart/form-data', 'id'=>'payment_modes')); ?>
						<div class="card card-success">
							<div class="card-header"><h3 class="card-title-new">Inspect Payment</h3></div>
							<div class="form-horizontal">
								<div class="card-body">
									<div class="row">
										<!-- in chemist payment verification , apply condition for firm name is not visible  added by laxmi on 15-12-2022 -->
											<?php if($_SESSION['application_type'] != 4){ ?>

										<div class="col-md-12"><label class="alert alert-dark offset-1">Applicant ID: <?php echo $customer_id; ?> - <?php echo $firm_name['firm_name']; ?></label></div>
										<?php } ?>
										
										<div class="col-md-12 mt-2">
											<div id="payment_details" class="row">
												<div class="col-sm-6">
													<div class="form-group row">
														<label class="col-sm-3 col-form-label">Payment Amount <span class="cRed">*</span></label>
														<div class="col-sm-9">
														<?php echo $this->Form->control('payment_amount', array('type'=>'text', 'escape'=>false, 'value'=>$payment_confirmation_query['amount_paid'], 'id'=>'payment_amount', 'label'=>false, 'disabled'=>true,'class'=>'form-control')); ?>
														</div>
													</div>
													<div class="form-group row">
														<label class="col-sm-3 col-form-label">Transaction ID/Receipt NO.<span class="cRed">*</span></label>
														<div class="col-sm-9">
															<?php echo $this->Form->control('payment_transaction_id', array('type'=>'text', 'escape'=>false, 'value'=>$payment_confirmation_query['transaction_id'], 'id'=>'payment_transaction_id', 'label'=>false, 'disabled'=>true,'class'=>'form-control')); ?>
															<!-- Below block is added to show existed application details if transaction id already used. added on 15-10-2019 by Amol, script also added below-->
															<?php if (!empty($existed_appl_details)) { ?>

																<p class="recipt-1">Wait.. This Transaction/Receipt Id is already used. <span  class="text-info badge" data-toggle="modal" data-target="#staticBackdrop">View Details</span></p>
																<div class="modal fade" id="staticBackdrop" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
																	<div class="modal-dialog">
																		<div class="modal-content">
																			<div class="modal-header">
																				<h5 class="modal-title text-info font-weight-bold" id="staticBackdropLabel">Existing Application Details</h5>
																				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
																				<span aria-hidden="true">&times;</span>
																				</button>
																			</div>
																			<div class="modal-body">
																					Applicant Id 	: <?php echo $existed_appl_details['customer_id'];?></br></br>
																					Firm name 		: <?php echo $existed_appl_details['firm_name'];?></br></br>
																					Amount 			: <?php echo $existed_appl_details['total_charges'];?></br></br>
																					Created On 		: <?php echo $existed_appl_details['created'];?>
																			</div>
																			<div class="modal-footer">
																				<button type="button" class="btn btn-secondary" data-dismiss="modal">OK</button>
																			</div>
																		</div>
																	</div>
																</div>

															<?php } ?>
														</div>
													</div>
												</div>
												<div class="col-sm-6">
													<div class="form-group row">
														<label class="col-sm-3 col-form-label">PAO/DDO Name <span class="cRed">*</span></label>
														<div class="col-sm-9">
															<?php echo $this->Form->control('pao_name', array('type'=>'text', 'escape'=>false, 'value'=>$selected_pao_alias_name['pao_alias_name'], 'id'=>'pao_name', 'label'=>false, 'disabled'=>true,'class'=>'form-control')); ?>
														</div>
													</div>
													<div class="form-group row">
														<label class="col-sm-3 col-form-label">Date of Transaction <span class="cRed">*</span></label>
														<div class="col-sm-9">
															<?php echo $this->Form->control('payment_trasaction_date', array('type'=>'text', 'escape'=>false, 'value'=>$payment_trasaction_date[0], 'id'=>'payment_trasaction_date', 'label'=>false,'disabled'=>true,'class'=>'form-control')); ?>
														</div>
													</div>
												</div>
												<div class="col-sm-6">
													<div class="form-group row">
														<label class="col-sm-4 col-form-label">Payment Receipt <span class="cRed">*</span></label>
														<div class="col-sm-6">
															<a target="blank" id="premises_belongs_to_docs_value" href="<?php echo str_replace("D:/xampp/htdocs","",$payment_confirmation_query['payment_receipt_docs']); ?>"><i class="fas fa-eye"></i><?=$str2 = substr(array_values(array_slice((explode("/",$payment_confirmation_query['payment_receipt_docs'])), -1))[0],23);?></a>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div id="form_outer_main">
								<div class="card-header"><h3 class="card-title-new"></h3></div>
								<div class="form-horizontal">
									<div class="card-body">
										<div class="row">
											<div class="col-md-6">
												<div class="form-group row">
													<label class="col-sm-3 col-form-label">Actions <span class="cRed">*</span></label>
													<div class="col-sm-9">
														<?php 
															$options = array('0'=>'Confirmed','1'=>'Not Confirmed');
															echo $this->Form->control('action', array('type'=>'select', 'value'=>$action_value, 'id'=>'action', 'empty'=>'---Select---','options'=>$options, 'escape'=>false, 'label'=>false, 'class'=>'form-control'));
														?>
													</div>
												</div>
											</div>

											<div id="not_confirmed_reason" class="col-md-6">
												<div class="form-group row">
													<label class="col-sm-3 col-form-label">Reason <span class="cRed">*</span></label>
													<div class="col-sm-9">
														<?php 
															$options = array('0'=>'Payment amount does not match','1'=>'Transaction ID Invalid','2'=>'PAO/DDO Name Invalid', '3'=>'Transaction Date Invalid', '4'=>'Payment Receipt Invalid');
															echo $this->Form->control('reasone_list_comment', array('type'=>'select', 'id'=>'reasone_list_comment','empty'=>'---Select---','options'=>$options, 'escape'=>false, 'label'=>false,'class'=>'form-control'));
														?>
														<span id="error_reasone_list_comment" class="error invalid-feedback"></span>
													</div>
												</div>
												<div class="form-group row">
													<label class="col-sm-3 col-form-label">Comment <span class="cRed">*</span></label>
													<div class="col-sm-9">
														<?php echo $this->Form->control('reasone_comment', array('type'=>'textarea', 'escape'=>false,'id'=>'reasone_comment', 'label'=>false,'class'=>'form-control' )); ?>
														<span id="error_reasone_comment" class="error invalid-feedback"></span>
													</div>
												</div>
											</div>
											
											<div class="col-md-12 pd10">
												<div id="referred_back">
													<div class="card-header bg-dark"><h3 class="card-title-new">Referred Back History</h3></div>
													<div class="remark-history">
														<table class="table table-bordered">
															<tr>
																<th class="tablehead">Date</th>
																<th class="tablehead">Reason</th>
																<th class="tablehead">Comment</th>
															</tr>
															<!-- change variable fetch_comment_reply to fetch_applicant_communication(by pravin 03/05/2017)-->
															<?php
																$options = array('0'=>'Payment amount does not match','1'=>'Transaction ID Invalid',
																'2'=>'PAO/DDO Name Invalid', '3'=>'Transaction Date Invalid', '4'=>'Payment Receipt Invalid');

																foreach($fetch_pao_referred_back as $comment_reply){ ?>

																	<tr>
																	<td><?php echo $comment_reply['modified']; ?></td>
																	<td><?php echo $options[$comment_reply['reason_option_comment']]; ?></td>
																	<td><?php echo $comment_reply['reason_comment']; ?></td>
																	</tr>

															<?php } ?>
														</table>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="icon"><i class="ion ion-bag"></i></div>
							<div class="card-footer">
								<?php echo $this->Form->submit('Submit', array('name'=>'payment_verificatin_action', 'id'=>'payment_verificatin_action', 'label'=>false,'class'=>'btn btn-success float-left')); ?>
								<a href="<?php echo $this->request->getAttribute('webroot');?>dashboard/home" class="btn btn-secondary float-right">Back</a>
							</div>
						</div>
					<?php echo $this->Form->end(); ?>
				</div>
			</div>
		</div>
	</section>
</div>
<input type="hidden" name="hidden1" value="<?php echo $action_value; ?>" id="actionvalue">
<input type="hidden" name="hidden2" value="<?php echo $payment_confirmation_query['payment_confirmation']; ?>" id="paymentstatus">
<?php echo $this->Html->script('paymentverification/paymentverification'); ?>
