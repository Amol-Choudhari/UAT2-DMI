<thead>
	<tr>
		<th>SR.No</th>
		<th>Subject</th>
		<th>Template Text</th>
		<th>Created By</th>
		<th>For</th>
		<th>Module</th>
		<th>Status</th>
		<th>Action</th>
	</tr>
</thead>
<tbody>
	<?php
		if (!empty($all_records)) {
			$sr_no=1;
			foreach ($all_records as $each_record) { ?>
			<tr>
				<td><?php echo $sr_no; ?></td>
				<td><?php echo $each_record['email_subject'];?></td>
				<td><?php echo $each_record['sms_message'];?></td>
				<td><?php echo base64_decode($each_record['user_email_id']); //for email encoding ?></td>
				<td class="fw700"><?php echo strtoupper($each_record['template_for']); ?></td>
				<td>
					<?php 
						if($each_record['table_name'] == 'dmi_sms_email_templates'){
							$module = 'General Templates';
						} elseif ($each_record['table_name'] == 'dmi_chemist_sms_templates') {
							$module = 'Chemist Templates';
						} elseif ($each_record['table_name'] == 'dmi_mmr_sms_templates') {
							$module = 'Misgrading Templates';
						}
						
						echo $module;
					?>
				</td>

				<td>
					<?php 
						$remark = $each_record['status'];
						if ($remark == 'active') {
							$badge = "success";
						} else if ($remark == 'disactive') {
							$badge = "danger";
						} else {
							$badge = "info";
						}
						echo "<span class='badge bg-".$badge."'>".$remark."</span>";
					?>
				</td>
				<td>
					<?php 
					echo $this->Html->link('', array('controller' => 'masters', 'action'=>'editfetchAndRedirect', $each_record['id'],$module),array('class'=>'far fa-edit','title'=>'Edit')); ?> |
					<?php
					if ($each_record['status'] == 'active') {
						echo $this->Html->link('', array('controller' => 'masters', 'action'=>'change_template_status_redirect', $each_record['id'],$module),array('class'=>'glyphicon glyphicon-remove deactivate_template','title'=>'Deactivate'));
					} else {
						echo $this->Html->link('', array('controller' => 'masters', 'action'=>'change_template_status_redirect', $each_record['id'],$module),array('class'=>'glyphicon glyphicon-ok activate_template','title'=>'Activate'));
					} ?>
				</td>
			</tr>
	<?php $sr_no++; } } ?>
</tbody>

<?php echo $this->Html->script('element/masters_management_elements/list_master_elements/all_templates'); ?>
