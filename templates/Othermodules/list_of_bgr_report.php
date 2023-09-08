<?php echo $this->Form->create(null, array()); ?>
	<div class="card card-info">
		<div class="card-header"><h3 class="card-title-new">List Granted Biannually Grading Report:</h3></div>
		<table id="cancelled_firms" class="table m-0 table-bordered table-striped table-hover">
			<thead class="tablehead">
				<tr>
					<th>Sr. No.</th>
					<th>Cert. Type</th>
					<th>Firm Name</th>
					<th>Applicant Id</th>
					<th>Grant Date</th>
					<th>Report. | Pdf</th>
				</tr>
			</thead>
			<tbody>
				<?php 
					$i=0;
					 if (!empty($appl_array)) {

						 foreach ($appl_array as $each) { ?>

						<tr>
							<td><?php echo $i+1;?></td>
              <td><?php echo $each['cert_type'];?></td>
							<td><?php echo $each['firm_name'];?></td>
							<td id="customer_id<?php echo $i; ?>"><?php echo $each['customer_id'];?></td>
							<td><?php echo $each['grant_date'];?></td>
							<td><?php $report_pdf_path = explode("/",$each['report_pdf']);
											$report_pdf_name = $report_pdf_path[count($report_pdf_path) - 1]; ?>
											<a target="_blank" href="<?php echo $each['report_form']; ?>">
										<?php echo 'View'; ?>
										</a>|
										<a id="pdf_link<?php echo $i; ?>" target="_blank" href="<?php echo $each['report_pdf']; ?>">
											<?php echo 'Report Pdf'; ?>
										</a>
										
										
							</td>
							
						</tr>
						<?php	$i=$i+1;
						 }
					 } 
				?>
			</tbody>
		</table>
	</div>
<?php
	echo $this->Form->end();
?>