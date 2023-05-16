<?php 	$split_user_name = explode('/',$_SESSION['username']); ?>
<?php echo $this->Html->css('customers/get_all_chemist_list'); ?>
<div class="content-wrapper">
	<div class="content-header">
		<div class="container-fluid">
			<div class="row mb-2">
				<div class="col-sm-6"><label class="badge badge-info">Registered Chemist</label></div>
				<div class="col-sm-6">
					<ol class="breadcrumb float-sm-right">
						<li class="breadcrumb-item"><?php echo $this->Html->link('Dashboard', array('controller' => 'customers', 'action'=>'secondary_home'));?></a></li>
						<li class="breadcrumb-item active">Log History</li>
					</ol>
				</div>
			</div>
		</div>
	</div>
	<body>
		<div class="tab">
			<button class="tablinks active" id="self_registered_chemist_tab" >Self - Registered Chemist</button>
			<?php if ($split_user_name[1] == 1) { ?>
			<button class="tablinks" id="lab_registered_chemist_tab" >Lab Registered Chemist</button> <?php } ?>
			<button class="tablinks" id="allocated_chemist_tab" >Allocated Chemist</button>
		</div>

		<div id="self_registered_chemist" class="tabcontent">
			<div class="card card-dark">
				<div class="card-header blu"><h3 class="card-title-new">Self-Registered Chemist</h3></div>
					<table id="applicant_logs_table" class="table m-0 table-bordered table-hover table-striped border border-primary ">
						<thead class="tablehead">
							<tr>
							<th>Sr.No</th>
							<th>Name</th>
							<th>Chemist ID</th>
							<th>Email</th>
							<th>Registered On</th>
								<!-- to set heding of all pdf letter added by laxmi on 29-11-2022 -->
							<th>View Letter</th>
							<th>Certificate PDF</th>
							<!--  End by Laxmi  -->
						</tr>
					</thead>
					<tbody>
					<?php if (!empty($self_registered_chemist)) { 

						$i=0; $sr_no = 1;

						foreach ($self_registered_chemist as $each) { ?>
						<tr>
							<td><?php echo $sr_no; ?></td>
							<td><?php echo $each['chemist_fname']." ".$each['chemist_lname']; ?></td>
							<td><?php echo $each['chemist_id']; ?></td>
							<td><?php echo base64_decode($each['email']); ?></td>
							<td><?php echo $each['created']; ?></td>
							<!-- RO side schedule letter pdf added by laxmi on 29-11-2022 -->
							<td>
							<?php if(!empty($viewLetterFromRo[$i])){ ?>
							<a href="<?php echo $viewLetterFromRo[$i];?>" target="_blank"><?php echo "RAL Schedule Letter"; ?></a> |
						    <?php } ?>
                            
							<!-- Ral side training completed letter from Ral added by laxmi on 27-1-23 -->
							<?php if(!empty($ral_trainingCom_letter[$i])){ ?>
								<a href="<?php echo $ral_trainingCom_letter[$i];?>" target="_blank"><?php echo "RAL Reliving Letter"; ?></a> |
							<?php } ?>
						    <!-- RO side training scheduled letter pdf added by laxmi on 027-01-2023 -->
                               <?php if(!empty($ro_side_schedule_letter[$i])){ ?>
							     <a href="<?php echo $ro_side_schedule_letter[$i];?>" target="_blank"><?php echo "Ro Schedule Letter"; ?></a> |
							
						      <?php } ?>

                              <!-- RO side reliving letter pdf added by laxmi on 03-01-2023 -->
                               <?php if(!empty($reliving_pdf[$i])){ ?>
							     <a href="<?php echo $reliving_pdf[$i];?>" target="_blank"><?php echo "Ro Relieving Letter"; ?></a>
							
						      <?php } ?></td>



						      <!-- grant certificate pdf added by laxmi on 5-1-2022 -->
						      <?php if(!empty($cetificatePdf[$i])){ ?>
							   <td><a href="<?php echo '../../'.$cetificatePdf[$i];?>" target="_blank"><?php echo "Certificate PDF"; ?></a>
							  </td>
						       <?php } ?>

						</tr>
					<?php $sr_no++; $i=$i+1;	} } ?>
				</tbody>
			</table>
		</div>
	</div>
	<?php if ($split_user_name[1] == 1) { ?>
	<div id="lab_registered_chemist" class="tabcontent">
		<div class="card-header cool_blues"><h3 class="card-title-new">Lab Registered Chemist</h3></div>
			<table id="applicant_logs_table" class="table m-0 table-bordered table-striped border border-dark">
				<thead class="tablehead">
					<tr>
						<th>Sr.No</th>
						<th>Name</th>
						<th>Chemist ID</th>
						<th>Email</th>
						<th>Action</th>
					</tr>
				</thead>
				<tbody>
				<?php if (!empty($lab_registered_chemist)) {
						$i=0;$sr_no = 1;
						foreach($lab_registered_chemist as $each){ ?>
						<tr>
							<td><?php echo $sr_no; ?></td>
							<td><?php echo $chemist_name[$i]; ?></td>
							<td><?php echo $each['chemist_id']; ?></td>
							<td><?php echo base64_decode($chemist_email[$i]); ?></td>
							<td><?php echo $this->Html->link('Allocate', array('controller' => 'customers','action'=>'fetch_chemist_id',$each['id'])); ?> </td>
						</tr>
					<?php $sr_no++; $i=$i+1;	} } ?>
				</tbody>
			</table>
		</div>
	<?php } ?>
		<div id="allocated_chemist" class="tabcontent">
			<div class="card-header pacific_dream"><h3 class="card-title-new">Allocated Chemist</h3></div>
        		<table id="applicant_logs_table" class="table m-0 table-bordered table-striped">
					<thead class="tablehead">
						<tr>
							<th>Sr.No</th>
							<th>Name</th>
							<th>Chemist ID</th>
							<th>Email</th>
							<th>Action</th>
						</tr>
					</thead>
					<tbody>
					<?php if (!empty($alloc_allocated_chemists)) {
							$i=0; $sr_no = 1;
							foreach ($alloc_allocated_chemists as $each) { ?>

								<tr>
									<td><?php echo $sr_no; ?></td>
									<td><?php echo $alloc_chemist_name[$i]; ?></td>
									<td><?php echo $each['chemist_id']; ?></td>
									<td><?php echo base64_decode($alloc_chemist_email[$i]); ?></td>
									<td><?php
										if (empty($chemist_incharge_id)) {
											echo $this->Html->link('Set Incharge', array('controller' => 'customers','action'=>'set_chemist_incharge',$each['id']));
										} else {
											if ($chemist_incharge_id == $each['id']) {
												echo $this->Html->link('Unset Incharge', array('controller' => 'customers','action'=>'unset_chemist_incharge',$each['id']));
											}
										}
										?>
									</td>
								</tr>
							<?php $sr_no++; $i=$i+1; } } ?>
						</tbody>
					</table>
				</div>
			</body>
		</div>

	<?php echo $this->Html->script('customers/get_all_chemist_list'); ?>
