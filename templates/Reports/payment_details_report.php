
<?php echo $this->Html->css('Reports/payment_details_report') ?>


<?php  // Change on 5/11/2018 : Assign class attribute to all search filter field and comment the value attribute   - By Pravin Bhakare?>
<div class="content-wrapper bg-bg">
	<div class="content-header page-header" id="page-load">
		<div class="container-fluid">
			<div class="row mb-2">
				<div class="col-sm-6">
					<h6 class="m-0 ml-3"><?php echo 'Payment Details Report'; ?></h6>
				</div>
				<div class="col-sm-6 my-auto">
					<ol class="breadcrumb float-sm-right">
						<span class="badge bg-light my-auto"><?php echo $this->Html->link('Dashboard', array('controller' => 'dashboard', 'action'=>'home'));?></a></span>
						<span class="my-auto"><i class="fas fa-chevron-right px-2 fs80"></i><span class="badge bg-light"><?php echo $this->Html->link('All Reports', array('controller' => 'reports', 'action'=>'report_types'));?></a></span></span>
						<span class=""><i class="fas fa-chevron-right px-2 fs80"></i><span class="badge page-header">
							<?php echo 'Payment Details Report'; ?></span>
						</span>
					</ol>
				</div>
				<div class="clearfix"></div>
			</div>
    	</div>
  	</div>

	 <!-- <section class="content form-middle"> -->
	  <div class="container-fluid">
      		<div class="row">
        		<div class="col-md-12 bg-bg">

					<div class="px-4 page-header" > <!-- style="background:#F9F1F1;" -->
						<?php echo $this->Form->create(null,array('type'=>'file', 'enctype'=>'multipart/form-data', 'id'=>'newly_added_firm')); ?>
						<div class="bg-transparent">
							<div id="search_by_options" class="">
								<div class="row report-filter pt-2">
									<div class="col-sm-3">
                    					<div class="form-group">
											<!-- all lables are uncommented by shreeya-->
											<label>Application Type</label> 
											<?php 

												echo $this->Form->control('report_for', ['type' => 'select','options' => $application_array , 
												'label' => false,
												'id' => 'report_for',
												'empty' => 'All',
												'class' => 'form-control form-control-sm'
												]);

											?> 
											

										</div>
								  	</div>
									
									<div class="col-sm-3" id="office_all">
										<div class="form-group">
											<label>Certificate Type</label> 
											<?php
											echo $this->form->input(	
												'application_type',
												array(
													'type' => 'select',
													'options' => $all_certificate_type,
													'label' => false,
													'empty' => 'All',
													'escape' => false,
													'class' => 'form-control form-control-sm search_field'
												)
											);
											?>
										</div>
									</div>

									<div class="col-sm-3">
                    					<div class="form-group">
											<label>From Date</label>
											<?php echo $this->form->input('from_date', array('type'=>'text', /*'value'=>$search_from_date,*/'label'=>false, 'id'=>'fromdate', 'empty'=>'select', 'escape'=>false, 'class'=>'form-control form-control-sm search_field', 'placeholder'=>'From Date')); ?>
										</div>
								  	</div>
									<div class="col-sm-3">
                    					<div class="form-group">
											<label id="to_date_label">To Date</label> 
											<?php echo $this->form->input('to_date', array('type'=>'text', /*'value'=>$search_to_date,*/'label'=>false, 'id'=>'todate', 'empty'=>'select', 'escape'=>false, 'class'=>'form-control form-control-sm search_field', 'placeholder'=>'To Date')); ?>
										</div>
								  	</div>

									  <div class="col-sm-2">
										<button id="search_btn" type="submit" name="search_logs" class="btn text-light option-menu-btn" value="Search" data-bs-toggle="tooltip" data-bs-placement="top" title="Search">
											<i class="fas fa-search"></i>
										</button>
										
									</div>

									<!-- <div class="col-sm-3">
                    					<div class="form-group">
											 <label>Office</label> 
											<?php// echo $this->form->input('office', array('type'=>'select', /*'value'=>$search_office,*/ 'options'=>$all_ro_office, 'label'=>false, 'id'=>'office', 'empty'=>'All', 'escape'=>false, 'class'=>'form-control form-control-sm search_field')); ?>
									  	</div>
								  	</div>
									<div class="col-sm-3">
                    					<div class="form-group">
											 <label>State</label>
											<?php// echo $this->form->input('state', array('type'=>'select', 'options'=>$all_states, 'label'=>false, 'id'=>'state', 'empty'=>'All', 'escape'=>false, 'class'=>'form-control form-control-sm search_field')); ?>
									  	</div>
								  	</div> -->

								</div>

								<div class="row report-filter ro_report-filter pt-2">
									<!-- <div class="col-sm-3">
                    					<div class="form-group">
											<label>District</label>
											<?php// echo $this->form->input('district', array('type'=>'select', 'label'=>false, 'id'=>'district',  'empty'=>'All', 'escape'=>false, 'class'=>'form-control form-control-sm search_field')); ?>
									  	</div>
								  	</div> -->
									<!-- <div class="col-sm-3">
                    					<div class="form-group">
											<label>From Date</label>
											<?php// echo $this->form->input('from_date', array('type'=>'text', /*'value'=>$search_from_date,*/'label'=>false, 'id'=>'fromdate', 'empty'=>'select', 'escape'=>false, 'class'=>'form-control form-control-sm search_field', 'placeholder'=>'From Date')); ?>
										</div>
								  	</div>
									<div class="col-sm-3">
                    					<div class="form-group">
											<label id="to_date_label">To Date</label> 
											<?php// echo $this->form->input('to_date', array('type'=>'text', /*'value'=>$search_to_date,*/'label'=>false, 'id'=>'todate', 'empty'=>'select', 'escape'=>false, 'class'=>'form-control form-control-sm search_field', 'placeholder'=>'To Date')); ?>
										</div>
								  	</div> -->
									<!-- <div class="col-sm-1"> -->
										<!-- <button id="search_btn" type="submit" name="search_logs" class="btn text-light option-menu-btn" value="Search" data-bs-toggle="tooltip" data-bs-placement="top" title="Search">
											<i class="fas fa-search"></i>
										</button> -->
										<!-- <input style="background:#747474; color:#fff;" id="search_btn" type="submit" name="search_logs" class="form-control" value="Search" > -->
									<!-- </div> -->
									<div class="col-sm-1">
										<!-- Call the Downloading Report Button Element (Done by Pravin 13/3/2018) -->
										<?php // echo $this->element('download_report_excel_format/report_download_button'); ?>
									</div>
								</div>

							</div>
						</div>
					</div>

				</div>
      		</div>
    	</div>
  	<!-- </section> -->
	<!-- added new section for shw list and count by shankhpal shinde -->
		<div class="d-flex align-items-center justify-content-center">
			<fieldset>
					<legend>Please select your preferred method:</legend>
					<div class="d-flex align-items-center justify-content-center">
						<label class="radio-inline" style="">
						<input type="radio" name="optradio" class="rep-option" checked="true" value="yes" id="radioList" ><span> List</span>
						</label>
						<label class="radio-inline" style="padding-left:15px;">
						<input type="radio" name="optradio" class="rep-option" value="no" id="radioCount"><span> Count</span>
						</label>
					</div>
			</fieldset>
		</div>


	<div class="bg-bg">
	 	<div class="container-fluid">
      		<div class="row">
        		<div class="col-md-12">
					<div class="mx-5">
						<?php ?> <span class="badge bg-light shadow">RESULT</span><i class="fas fa-chevron-right px-2 fs80"></i> <?php
							
							if(!empty($application_type)) {
								if (isset($all_application_type[$application_type])) {
								?>  <span class="badge rounded-pill bg-grad1 shadow">Certificate Type</span>
										<i class="fas fa-caret-right"></i>
										<span class="badge bg-grad2 mr-3 shadow"> <?php echo $all_application_type[$application_type];   $search_value = 'yes';?> </span>
									<?php
								}
							}
							 
							
							if(!empty($ro_office)) {
							?>  <span class="badge rounded-pill bg-grad1 shadow">RO Office</span>
									<i class="fas fa-caret-right"></i>
									<span class="badge bg-grad2 mr-3 shadow"> <?php echo $all_ro_office[$ro_office];   $search_value = 'yes'; ?> </span>
								<?php
							}
							if(!empty($state)) {
							?>  <span class="badge rounded-pill bg-grad1 shadow">State</span>
									<i class="fas fa-caret-right"></i>
									<span class="badge bg-grad2 mr-3 shadow"> <?php echo $all_states[$state];  $search_value = 'yes'; ?> </span>
								<?php
							}
							if(!empty($district)) {
							?>  <span class="badge rounded-pill bg-grad1 shadow">District</span>
									<i class="fas fa-caret-right"></i>
									<span class="badge bg-grad2 mr-3 shadow"> <?php echo $all_district[$district];  $search_value = 'yes'; ?> </span>
								<?php
							}
							if(!empty($search_from_date)) {
							?> 	<span class="badge rounded-pill bg-grad1 shadow">From Date</span>
									<i class="fas fa-caret-right"></i>
									<span class="badge bg-grad2 mr-3 shadow"> <?php $explode_f_date = explode(' ',$search_from_date);
									$explode_f_date = explode('-',$explode_f_date[0]);
									echo $explode_f_date[2].'-'.$explode_f_date[1].'-'.$explode_f_date[0];  $search_value = 'yes'; ?> </span>

								<span class="badge rounded-pill bg-grad1 shadow">To Date</span>
									<i class="fas fa-caret-right"></i>
									<span class="badge bg-grad2 shadow"> <?php $explode_t_date = explode(' ',$search_to_date);
									$explode_t_date = explode('-',$explode_t_date[0]);
									echo $explode_t_date[2].'-'.$explode_t_date[1].'-'.$explode_t_date[0];  $search_value = 'yes'; ?> </span>
								<?php
							}
							if(empty($search_value)) {
							?>  <span class="badge bg-grad2 mr-3 shadow"> <?php echo 'ALL'; ?> </span>
								<?php
							}
						?>
					</div>
				</div>
      		</div>
    	</div>

		<section class="content form-middle">
		<!-- add new class reportlst by shankhpal shinde -->
			<div class="container-fluid border rounded-lg border-light bg-light p-3 shadow reportlst">
				<div class="row">
					<div class="col-md-12">
						<div class="table-responsive report-table-format">
							<table class="table table-sm rounded" id="payment_details_report_table">
								<thead class="table-light">
									<tr class="rounded">
										<th><span class="table-heading">Sr.No</span></th>
										<th><span class="table-heading">Date</span></th>
										<th><span class="table-heading">Certificate Type</span></th>
										<th><span class="table-heading">Application Type</span></th>
										<th><span class="table-heading">RO Office</span></th>
										<th class="text-right"><span class="table-heading">State</span></th>
										<th><span class="table-heading">District</span></th>
										<th><span class="table-heading">Payment Amount</span></th>
									</tr>
								</thead>
								<tbody class="">
                					 
								<?php 
								//add for each loop for show the array of all records by shreeya on [15-06-2023]
								$k = 0;
								$j = 0;
								
								foreach ($flowwise_table_data as $eachflow) { 

									if (!empty($firms_details[$j])) {
										 
										?>
										<!-- <tr>
											<td colspan="6" class="table-heading bg-dark">New Application Payment Details</td>
										</tr> -->
									
										<?php  for ($i = 0; $i < sizeof($firms_details[$j]); $i++) { ?>
											 
											<tr id="table_row" class="row-hover border border-light">
												<td><span class="badge title mb-1 borderless hover-border"><?php  echo $k+1; ?></span></td>
												<td><span class="badge title borderless"><?php  $explode_date = explode(' ', $customer_payment_details[$j][$i]['created']);
														echo $explode_date[0]; ?></span></td>
												<td>
													<!-- commented by shreeya on date [13-06-2023]-->
													<!-- added if else according to record null or not by shreeya on date [13-06-2023]-->
													<?php if (!empty($all_certificate_type[$firms_details[$j][$i]['certification_type']])) { ?>
													<span class="badge subtitle borderless">
														<?php echo $all_certificate_type[$firms_details[$j][$i]['certification_type']];
													} else { ?>
													</span>
													<span class="badge subtitle borderless">
														<?php echo '--' ;
														
													} ?>
													</span>
												</td>
											
												<td>
													<?php if (!empty($apl_type_res)) { ?>
													<span class="badge subtitle borderless">
														<?php echo $apl_type_res[$j][$i];
													} else { ?>
													</span>
													<span class="badge subtitle borderless">
														<?php echo $report_for;
													} ?>
													</span>

												</td>
												<!-- <td>
												<span class="badge subtitle borderless">
													<?php// echo $customer_payment_details[$j][$i]['customer_id']; ?>
												</span>
												</td> -->
												
												<td>
												<!-- added if else according to record null or not by shreeya on date [13-06-2023]-->
												<?php if (!empty($all_ro_office[$ro_id[$j][$i]['ro_id']])) { ?>
													<span class="badge subtitle borderless">
														<?php echo $all_ro_office[$ro_id[$j][$i]['ro_id']];
													} else { ?>
													</span>
													<span class="badge subtitle borderless">
														<?php echo '--';
													} ?>
													</span>

												</td>
												<td class="text-right">
													<!-- commented by shreeya on date [13-06-2023]-->
													<!-- added if else according to record null or not by shreeya on date [13-06-2023]-->
													<?php if (!empty($all_states[$firms_details[$j][$i]['state']])) { ?>
													<span class="badge subtitle borderless">
														<?php echo $all_states[$firms_details[$j][$i]['state']];
													} else { ?>
													</span>
													<span class="badge subtitle borderless">
														<?php echo '--';
													} ?>
													</span>
												</td>
												<td>
													<!-- commented by shreeya on date [13-06-2023]-->
													<!-- added if else according to record null or not by shreeya on date [13-06-2023]-->
													<?php if (!empty($all_district[$firms_details[$j][$i]['district']])) { ?>
														<span class="badge subtitle borderless">
															<?php echo $all_district[$firms_details[$j][$i]['district']];
														} else { ?>
														</span>
														<span class="badge subtitle borderless">
															<?php echo '--';
														} ?>
														</span>
												</td>
												<td><span class="badge subtitle borderless"><?php echo number_format($customer_payment_details[$j][$i]['amount_paid'], 2); ?></span></td>				
											</tr>
										<?php $k++; } 
									}
									$j++;
								}
								?>
								</tbody>
							</table>
							<!-- show total grant added by shreeya on date[21-06-2023]-->
							<table class="table" id="payment_details_report_table">
								<tbody class="">
                					<tr>
										<td>
											<span class="title borderless">
												<?php echo $sum_of_all;?><br><br>
												<?php echo $sum_of_total;?>
												
											</span>
										</td>
										
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</section>

		<section class="content form-middle"> 
			<div class="container-fluid border rounded-lg border-light bg-light p-3 shadow hidden  reportCnt">
				<!-- hidden -->
				<div class="row">
					<div class="col-md-12">
						<div class="table-responsive report-table-format">
							<!-- <table class="table table-bordered" id="payment_details_report_table">
								<tbody class="">
                    
								</tbody>
							</table> -->
							<table class="table tree table-inverse">
								<thead>
										<tr class="sm-text">
											<th><span class="table-heading">All Application Payment Details</span></th>
										</tr>
								</thead>
								<tbody>
				
									<?php
										$report_for_array_new = array_keys($application_array); //for show only key
										$report_for_array_new1 = array_values($application_array);
										$arrlength = count($report_for_array_new);
										$arrlength = count($report_for_array_new1);

										for($x = 0; $x < $arrlength; $x++) { ?>
											<tr>
												<td>
													<span class="glyphicon glyphicon-plus plusIcon" id="list_<?php echo $x?>"></span>
													<span class="glyphicon glyphicon-minus plusIcon" style="display:none"></span>
													<?php echo $report_for_array_new1[$x]." "."Application Payment Details"; ?>
												</td>  
											</tr>
										<?php
										//	change application type name "New" -> 1	by shreeya[19-06-2023]	
										if($report_for_array_new[$x] == 1){?>
											<tr class="hidden">
												<td>
													<table class="table table-bordered">
														<tr>
															<td><span class=""></span>New Certificate of Authorisation Payment Total :</td>
															<td><span class=""></span> <?php echo $new_ca_total;?></td>
														</tr>
														<tr>
															<td><span class=""></span>New Printing Applications Payment Total :</td>
															<td><span class=""></span> <?php echo $new_pp_total; ?> </td>
														</tr>
														<tr>
															<td><span class=""></span>New Laboratory Applications Payment Total :</td>
															<td><span class=""></span> <?php echo $new_lab_total; ?> </td>
														</tr>
														<tr>
															<td><span class=""></span>Grant Total :</td>
															<td><span class=""></span> <?php echo $total_new_ca_pp_lab; ?> </td>
														</tr>
													</table>    
												</td>
											</tr>
											<?php }elseif($report_for_array_new[$x] == 2){ ?>
												<tr class="hidden">
													<td>
														<table class="table table-bordered">
															<tr>
																<td><span class=""></span>Renewal Certificate of Authorisation Payment Total :</td>
																<td><span class=""></span> <?php echo $renewal_ca_total; ?></td>
															</tr>
															<tr>
																<td><span class=""></span>Renewal Printing Applications Payment Total :</td>
																<td><span class=""></span> <?php echo $renewal_pp_total; ?> </td>
															</tr>
															<tr>
																<td><span class=""></span>Renewal Laboratory Applications Payment Total :</td>
																<td><span class=""></span> <?php echo $renewal_lab_total; ?> </td>
															</tr>
															<tr>
																<td><span class=""></span>Grant Total :</td>
																<td><span class=""></span> <?php echo $total_renewal_ca_pp_lab; ?> </td>
															</tr>
														</table>    
													</td>
												</tr>
											<?php }elseif($report_for_array_new[$x] == 3){ ?>
												<tr class="hidden">
													<td>
														<table class="table table-bordered">
															<tr>
																<td><span class=""></span>Change Certificate of Authorisation Payment Total :</td>
																<td><span class=""></span> <?php echo $change_ca_total; ?></td>
															</tr>
															<tr>
																<td><span class=""></span>Change Printing Applications Payment Total :</td>
																<td><span class=""></span> <?php echo $change_pp_total; ?> </td>
															</tr>
															<tr>
																<td><span class=""></span>Change Laboratory Applications Payment Total :</td>
																<td><span class=""></span> <?php echo $change_lAB_total; ?> </td>
															</tr>
															<tr>
																<td><span class=""></span>Grant Total :</td>
																<td><span class=""></span> <?php echo $total_change_ca_pp_lab; ?> </td>
															</tr>
														</table>    
													</td>
												</tr>
											<?php }elseif($report_for_array_new[$x] == 4) {?>
												<tr class="hidden">
													<td>
														<table class="table table-bordered">
															<tr>
																<td><span class=""></span>Chemist Approval Applications Payment Total : </td>
																<td><span class=""></span> <?php echo $chemist_total; ?></td>
															</tr>
														</table>    
													</td>
												</tr>
											<?php }elseif($report_for_array_new[$x] == 7) {?>	
												<tr class="hidden">
													<td>
														<table class="table table-bordered">
																<tr>
																	<td><span class=""></span>Advance Payment Applications Payment Total :</td>
																	<td><span class=""></span> <?php echo $adv_total; ?></td>
																</tr>
														</table>    
													</td>
												</tr>
											
										<?php  }	}?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</section>
		

		<div class="ml-3 mt-3">
			<h5><a href="<?php echo $this->request->getAttribute('webroot');?>reports/<?php echo $backAction; ?>" class="report-back-button btn back-btn" role="button">Back to All Reports</a>
				<?php //echo $this->Html->link('Back', array('controller' => 'reports', 'action'=>'report_types')); ?>
			</h5>
		</div>
	</div>
	<?php echo $this->Form->end(); ?>
</div>


<?php echo $this->Html->script('Reports/payment_details_report'); ?>
