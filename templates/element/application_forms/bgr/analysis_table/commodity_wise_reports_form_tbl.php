 <?php
//  <!--
// File: bianually_grading_report.php
// Created by: Shankhpal Shende
// Creation Date: 28/07/2023
// Module: Bianually Grading Report

// Description:
// This file contains the code for generating a Bianually Grading Report table.
//The table includes details related to regional offices, states,
//packer information, period, revenue, and grading data for different commodities.
//The table is designed to display and manage grading reports on a biannual basis.

// Please note that this is a dynamic table with editable fields for
//adding, editing, and deleting grading report entries.
//The table utilizes PHP functions to optimize code reusability and maintainability.

// Developed by Shankhpal Shende on 28/07/2023 for the Bianually Grading Report module of the application.
// -->

//  pr($checkIfgrant);die;
 // Define the constant with the message
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
define('DATE_FORMAT_MESSAGE', 'Enter DD/MM/YYYY');
define('INPUT_FIELD_CLASSES', 'form-control input-field wd120');
$placeholder = DATE_FORMAT_MESSAGE;
$class1 = INPUT_FIELD_CLASSES;?>
 

<div class="row">
  <div class="col-md-12 ">
    <div class="table-format">
      <div class="table-container">
       <table id="dataTable" class="table table-bordered table-striped table-responsive">
          <thead>
         <caption></caption>
              <tr>
                  <th
                    colspan="6"
                    scope="col"
                    class="border-bottom">
                    Regional Office/Sub-Office:
                    <?php echo isset($section_form_details[5])?$section_form_details[5]:"NA"; ?></th>
                  <th
                    colspan="8"
                    class="border-bottom"
                    scope="col">State :
                    <?php echo isset($section_form_details[4])?$section_form_details[4]:"NA"; ?></th>
                  <th colspan="6" class="border-bottom" scope="col"></th>
              </tr>
              <tr>
                  <th colspan="3" class="border-bottom" scope="col">Name of Packer with address and e-mail id:</th>
                  <th
                    colspan="3"
                    class="border-bottom"
                    scope="col">Name :
                    <?php echo isset($section_form_details[1])?$section_form_details[1]:"NA"; ?></th>
                  <th
                    colspan="3"
                    class="border-bottom"
                    scope="col">Email :
                    <?php echo base64_decode(isset($section_form_details[2])?$section_form_details[2]:"NA"); ?></th>
                  <th
                    colspan="6"
                    class="border-bottom"
                    scope="col">Address :
                    <?php echo isset($section_form_details[3])?$section_form_details[3]:"NA" ?></th>
              </tr>
              <tr>
                    <td colspan="6" class="border-bottom" scope="col">
                         
                        <?php
                            echo $this->Form->control('period', array(
                              'type'=>'text',
                              'id'=>'period',
                              'value'=>$section_form_details[18],
                              'label'=>'Period:',
                              'readonly'=>'readonly',
                              'class'=>'form-control'
                            )); ?>

                    </td>
                  <th
                    colspan="7"
                    class="border-bottom"
                    scope="col">Type:
                    <?php echo ($section_form_details[6] == "yes") ? "Export" : "Domestic"; ?>
                  </th>
                  </th>
                  <th scope="col"></th>
                  <th scope="col"></th>
                  <th scope="col"></th>
                  <th scope="col"></th>
                  <th scope="col"></th>
                  <th scope="col"></th>
                  <th scope="col"></th>
                 
              </tr>
               <tr>
                  <th colspan="6" class="border-bottom" scope="col">
                    Total Revenue (In. Rs.): <span id="totalRevenueHeader"></span>
                  </th>
                  <th colspan="6" class="border-bottom" scope="col">Progressive Revenue (In Rs.): <?php echo $section_form_details[17]; ?>
                  </th>
                  <th scope="col"></th>
                  <th scope="col"></th>
                  <th scope="col"></th>
                  <th scope="col"></th>
                  <th scope="col"></th>
                  <th scope="col"></th>
                 
              </tr>
              
              <tr>
                <th class="tablehead" scope="col">Sr.No.</th>
                <th class="tablehead wdth" scope="col">Commodity</th>
                <th class="tablehead wdth" scope="col">Lot No.TF No./M. No.</th>
                <th class="tablehead wdth" scope="col">Date of sampling</th>
                <th class="tablehead wdth" scope="col">Date of packing</th>
                <th class="tablehead wdth" scope="col">Grade assigned</th>
                <th class="tablehead wdth" scope="col" colspan="2">Pack Size</th>
                <th class="tablehead wdth" scope="col">Total No. of packages</th>
                <th class="tablehead wdth" scope="col">Total Qty. graded in Quintal</th>
                <th class="tablehead wdth" scope="col">Estimated value (in Rs.)</th>
                <th class="tablehead wdth" scope="col" colspan="3">No. of Agmark Replica/labels issued</th>
                <th class="tablehead wdth" scope="col">Replica Charges</th>
                <th class="tablehead wdth" scope="col">Name of Laboratory which tested the samples</th>
                <th class="tablehead wdth" scope="col" colspan="2">Report no. and Date</th>
                <th class="tablehead wdth" scope="col">Remark</th>
                <th class="tablehead wdth" scope="col">Action</th>
              </tr>
              <tr>
                <th class="tablehead wdth" scope="col"></th>
                <th class="tablehead wdth" scope="col"></th>
                <th class="tablehead wdth" scope="col"></th>
                <th class="tablehead wdth" scope="col"></th>
                <th class="tablehead wdth" scope="col"></th>
                <th class="tablehead wdth" scope="col"></th>
                 <th class="tablehead wdth" scope="col">Size</th>
                <th class="tablehead wdth" scope="col">Unit</th>
                <th class="tablehead wdth" scope="col"></th>
                <th class="tablehead wdth" scope="col"></th>
                <th class="tablehead wdth" scope="col"></th>
                <th class="tablehead wdth" scope="col">From</th>
                <th class="tablehead wdth" scope="col">To</th>
                <th class="tablehead wdth" scope="col">Total</th>
                <th class="tablehead wdth" scope="col"></th>
                <th class="tablehead wdth" scope="col"></th>
                <th class="tablehead wdth" scope="col">Report no</th>
                <th class="tablehead wdth" scope="col">Report Date</th>
                <th class="tablehead wdth" scope="col"></th>
                <th class="tablehead wdth" scope="col"></th>
              </tr>
             </thead>
              <tbody>
               
                  <?php
                    // if(empty($section_form_details[12]) || !empty($section_form_details[16])){
                          $i=1;
                          if(!empty($section_form_details[12])){

                            foreach ($section_form_details[12] as  $eachrow) {
                             
                              $conn = ConnectionManager::get('default');
                             
                              $commodity_code = $eachrow['commodity'];

                              $gradeasign = $eachrow['gradeasign'];

                              $lab_id = $eachrow['laboratoryname'];
                              $lab_id = intval($lab_id);
                              
                              $query2 = "SELECT grade_desc from m_grade_desc WHERE grade_code = $gradeasign";
                              $q2 = $conn->execute($query2);
                              $row2 = $q2->fetch();
                              $gradename = '';
                              if (isset($row2[0])) { // Check if index 0 exists
                                $gradename = $row2[0]; // Use index 0 to access the value
                              }

                              $commo_query = "SELECT commodity_name from m_commodity WHERE commodity_code = $commodity_code";
                              $q3 = $conn->execute($commo_query);
                              $row3 = $q3->fetch();
                              if (isset($row3[0])) { // Check if index 0 exists
                                $Commodityname = $row3[0]; // Use index 0 to access the value
                              }
                              
                             $query = $conn->newQuery()
                            ->select(['laboratory_name'])
                            ->from('dmi_customer_laboratory_details')
                            ->where(['id' => $lab_id]);
                            // Execute the query
                            $result = $query->execute();
                             
                            // Fetch the row
                            $row4 = $result->fetch('assoc'); // Fetch as an associative array
                            $labname = '';
                            if(!empty($row4)){
                               $labname = $row4['laboratory_name']; // Use index 0 to access the value
                            }
                           

                              ?>
                              <tr  id="custom_row<?php echo $eachrow['id'];?>">
                                <td><?php echo $i; ?></td>
                                <td><?php echo $Commodityname;?></td>
                                <td><?php echo $eachrow['lotno'];?></td>
                                <td><?php echo $eachrow['datesampling'];?></td>
                                <td><?php echo $eachrow['dateofpacking'];?></td>
                                <td><?php echo $gradename; ?></td>
                                <td><?php echo $eachrow['packetsize'];?></td>
                                <td><?php echo $eachrow['packetsizeunit']; ?></td>
                                <td><?php echo $eachrow['totalnoofpackets'];?></td>
                                <td><?php echo $eachrow['totalqtyquintal'];?></td>
                                <td><?php echo $eachrow['estimatedvalue'];?></td>
                                <td><?php echo $eachrow['agmarkreplicafrom'];?></td>
                                <td><?php echo $eachrow['agmarkreplicato'];?></td>
                                <td><?php echo $eachrow['agmarkreplicatotal'];?></td>
                                <td><?php echo $eachrow['replicacharges'];?></td>
                                <td><?php echo $labname;?></td>
                                <td><?php echo $eachrow['reportno'];?></td>
                                <td><?php echo $eachrow['reportdate'];?></td>
                                <td><?php echo $eachrow['remarks'];?></td>
                                <td>
                                <a href="#"
                                class="edit_bgr_id glyphicon glyphicon-edit"
                                id="<?php echo $eachrow['id']; ?>" ></a> |
                                  <a href="#"
                                  class="delete_bgr_id glyphicon glyphicon-remove-sign machine_delete"
                                  id="<?php echo $eachrow['id']; ?>" ></a>
                                </td>
                              </tr>
                            <?php $i++; 
                          }}?>


                    <?php
                        
                     
                     
                      if(!empty($section_form_details[16])){
                        
                          $j = 0;
                          $k=1; 
                          foreach ($section_form_details[16] as $eachrow1) {
                           
                           
                            $replica_allotment_btn_id = "replica_allotment_btn_" . $j;
                            $update_bgr_details_id = "update_bgr_details_" . $j;
                            ?>
                          <tr id="add_new_row">
                              <td><?php echo $k; ?></td>
                              <td>
                                <?php
                                
                                  echo $this->Form->control('rpl_commodity', array(
                                    'type'=>'select',
                                    'empty'=>'Select Commodity',
                                    'id'=>'rpl_commodity_'. $j,
                                    'options'=>$section_form_details[9],
                                    'default' => $eachrow1['commodity'],
                                    'label'=>false,
                                    'class'=>'form-control wd120 commodity'
                                  )); ?>
                                  <span id="error-rpl_commodity_<?php echo $j; ?>"></span>
                                </td>

                              <td>
                                <?php echo $this->Form->control('rpl_lotno', array(
                                    'type'=>'text',
                                    'id'=>'rpl_lotno_'. $j,
                                    'label'=>false,
                                    'value'=>$eachrow1['lotno'],
                                    'class'=>'form-control wd120'
                                  )); ?>
                                <span id="error-rpl_lotno_<?php echo $j; ?>"></span>
                              </td>

                              <td>
                                 <?php echo $this->Form->control('rpl_datesampling', array(
                                    'type'=>'text',
                                    'id'=>'rpl_datesampling_'. $j,
                                    'label'=>false,
                                    'value'=>$eachrow1['datesampling'],
                                    'class'=>'form-control wd120'
                                  )); ?>
                                <span id="error-rpl_datesampling_<?php echo $j; ?>"></span>
                              </td>

                              <td>
                                 <?php echo $this->Form->control('rpl_dateofpacking', array(
                                    'type'=>'text',
                                    'id'=>'rpl_dateofpacking_'. $j,
                                    'label'=>false,
                                    'value'=>$eachrow1['dateofpacking'],
                                    'class'=>'form-control wd120'
                                  )); ?>
                                <span id="error-rpl_dateofpacking_<?php echo $j; ?>"></span>
                              </td>

                              <td>
                                  <?php
                                
                                  echo $this->Form->control('rpl_grade', array(
                                    'type'=>'select',
                                    'empty'=>'Select grade',
                                    'id'=>'rpl_grade_'.$j,
                                    'options'=>$section_form_details[10],
                                    'default' => $eachrow1['grade'],
                                    'label'=>false,
                                    'class'=>'form-control wd120'
                                  )); ?>
                                  <span id="error-rpl_grade_<?php echo $j; ?>"></span>
                              </td>

                              <td>
                                <?php echo $this->Form->control('rpl_packet_size', array(
                                    'type'=>'text',
                                    'id'=>'rpl_packet_size_'. $j,
                                    'label'=>false,
                                    'value'=>$eachrow1['packet_size'],
                                    'class'=>'form-control wd120'
                                  )); ?>
                                  <span id="error-rpl_packet_size_<?php echo $j; ?>"></span>
                              <td>
                                 <?php echo $this->Form->control('rpl_packet_size_unit', array(
                                    'type'=>'select',
                                    'id'=>'rpl_packet_size_unit_'. $j,
                                    'label'=>false,
                                    'options'=>$section_form_details[13],
                                    'default'=>$eachrow1['packet_size_unit'],
                                    'class'=>'form-control wd120'
                                  )); ?>
                                  <span id="error-rpl_packet_size_unit_<?php echo $j; ?>"></span>
                              </td>

                              <td>
                                 <?php echo $this->Form->control('rpl_no_of_packets', array(
                                    'type'=>'text',
                                    'id'=>'rpl_no_of_packets_'. $j,
                                    'label'=>false,
                                    'value'=>$eachrow1['no_of_packets'],
                                    'class'=>'form-control wd120'
                                  )); ?>
                                  <span id="error-rpl_no_of_packets_<?php echo $j; ?>"></span>
                              </td>

                              <td>
                                 <?php echo $this->Form->control('rpl_qty_quantal', array(
                                    'type'=>'text',
                                    'id'=>'rpl_qty_quantal_'. $j,
                                    'label'=>false,
                                    'value'=>$eachrow1['rpl_qty_quantal'],
                                    'class'=>'form-control wd120'
                                  )); ?>
                                 <span id="error-rpl_qty_quantal_<?php echo $j; ?>"></span>
                              </td>
                              
                              <td>
                                 <?php echo $this->Form->control('rpl_estimatedvalue', array(
                                    'type'=>'text',
                                    'id'=>'rpl_estimatedvalue_'. $j,
                                    'label'=>false,
                                    'value'=>$eachrow1['estimatedvalue'],
                                    'class'=>'form-control wd120'
                                  )); ?>
                                  <span id="error-rpl_estimatedvalue_<?php echo $j; ?>"></span>
                              </td>
                          
                              <td>
                                 <?php echo $this->Form->control('rpl_alloted_rep_from', array(
                                    'type'=>'text',
                                    'id'=>'rpl_alloted_rep_from_'. $j,
                                    'label'=>false,
                                    'value'=>$eachrow1['alloted_rep_from'],
                                    'class'=>'form-control wd120'
                                  )); ?>
                                  <span id="error-rpl_alloted_rep_from_<?php echo $j; ?>"></span>
                              </td>

                              <td>
                                 <?php echo $this->Form->control('rpl_alloted_rep_to', array(
                                    'type'=>'text',
                                    'id'=>'rpl_alloted_rep_to_'. $j,
                                    'label'=>false,
                                    'value'=>$eachrow1['alloted_rep_to'],
                                    'class'=>'form-control wd120'
                                  )); ?>
                                  <span id="error-rpl_alloted_rep_to_<?php echo $j; ?>"></span>
                              </td>

                              <td>
                                <?php echo $this->Form->control('rpl_total_quantity', array(
                                    'type'=>'text',
                                    'id'=>'rpl_total_quantity_'. $j,
                                    'label'=>false,
                                    'value'=>$eachrow1['total_quantity'],
                                    'class'=>'form-control wd120'
                                  )); ?>
                                 <span id="error-rpl_total_quantity_<?php echo $j; ?>"></span>
                              </td>

                              <td>
                                 <?php echo $this->Form->control('rpl_replicacharges', array(
                                    'type'=>'text',
                                    'id'=>'rpl_replicacharges_'. $j,
                                    'label'=>false,
                                    'value'=>$eachrow1['label_charge'],
                                    'class'=>'form-control wd120'
                                  )); ?>
                                  <span id="error-rpl_replicacharges_<?php echo $j; ?>"></span>
                              </td>

                              
                              <td>
                                <?php
                                
                                  $DmiCustomerLaboratoryDetails = TableRegistry::getTableLocator()->get('DmiCustomerLaboratoryDetails');
                                  $fetch_laboratory_detail_data = $DmiCustomerLaboratoryDetails->find('all',array('keyField'=>'id','valueField'=>'laboratory_name','conditions'=>array('id'=>$eachrow1['grading_lab'])))->first();
                                  if(!empty($fetch_laboratory_detail_data)){
                                      $laboratory_id = $eachrow1['grading_lab'];
                                    $laboratory_name = $fetch_laboratory_detail_data['laboratory_name'];
                                  }else{
                                    $laboratory_id = '';
                                    $laboratory_name = '';
                                  }
                                  
                                  echo $this->Form->hidden('rpl_grading_lab', array(
                                      'id' => 'rpl_grading_lab_'.$j,
                                      'value' => $laboratory_id
                                  ));

                                  echo $this->Form->text('rpl_displayed_lab', array(
                                      'id' => 'rpl_displayed_lab_'.$j,
                                      'value' => $laboratory_name,
                                      'class' => 'form-control rpl_displayed_lab',
                                      'readonly' => true
                                  ));

                                ?>

                                
                              </td>
                              <td>
                                 <?php echo $this->Form->control('rpl_reportno', array(
                                    'type'=>'text',
                                    'id'=>'rpl_reportno_'. $j,
                                    'label'=>false,
                                    'value'=>$eachrow1['reportno'],
                                    'class'=>'form-control wd120 rpl_reportno'
                                  )); ?>
                                 <span id="error-rpl_reportno_<?php echo $j; ?>"></span>
                            </td>

                              <td>
                                <?php echo $this->Form->control('rpl_reportdate', array(
                                    'type'=>'text',
                                    'id'=>'rpl_reportdate_'. $j,
                                    'label'=>false,
                                    'value'=>$eachrow1['reportdate'],
                                    'class'=>'form-control wd120 rpl_reportdate'
                                  )); ?>
                                <span id="error-rpl_reportdate_<?php echo $j; ?>"></span>
                              </td>

                              <td>
                                <?php echo $this->Form->control('rpl_remarks', array(
                                    'type'=>'text',
                                    'id'=>'rpl_remarks_'. $j,
                                    'label'=>false,
                                    'value'=>$eachrow1['remarks'],
                                    'class'=>'form-control wd120 rpl_remarks'
                                  )); ?>
                                   <span id="error-rpl_remarks_<?php echo $j; ?>"></span>
                                 
                            </td>
                            
                            <td>
                              <div class="form-buttons">
                                <a href="#" id="<?php echo $replica_allotment_btn_id; ?>"  class='btn btn-info btn-sm replica_allotment_btn'>
                                <i class="fa fa-plus"></i> Add</a>
                              </div>
                            </td>
                          </tr>
                          <?php $j++;$k++;
                    }
                  }else{
                    ?>
                    <tr id="add_new_row">
                              <td>
                                <?php echo $this->Form->control('record_id', array(
                                      'type' => 'hidden',
                                      'id' => 'record_id',
                                      'value' => '',
                                      'label' => false,
                                      'class' => 'form-control wd120'
                                  )); ?>
                              </td>
                              <td >
                                  <?php
                                    echo $this->Form->control('ta-commodity-', array(
                                      'type'=>'select',
                                      'empty'=>'Select Commodity',
                                      'id'=>'ta-commodity-',
                                      'options'=>$section_form_details[9],
                                      'label'=>false,
                                      'class'=>'form-control wd120 commodity'
                                    )); ?>
                                   <span id="error-ta-commodity-"></span>
                              </td>
                              <td>
                                    <?php echo $this->Form->control('lot_no_tf_no_m_no', array(
                                      'type'=>'text',
                                      'escape'=>false,
                                      'id'=>'lot_no_tf_no_m_no',
                                      'label'=>false,
                                      'class'=>$class1,
                                      // 'placeholder'=>'Enter Lot No.TF No./M. No.'
                                    )); ?>
                                    <span id="error-lot_no_tf_no_m_no"></span>
                              </td>
                              <td>
                                    <?php echo $this->Form->control('date_of_sampling', array(
                                      'type'=>'text',
                                      'escape'=>false,
                                      'id'=>'date_of_sampling',
                                      'label'=>false,
                                      'class'=>$class1,
                                      'placeholder'=>$placeholder
                                    )); ?>
                                    <span id="error-date_of_sampling"></span>
                              </td>
                              <td>
                                    <?php echo $this->Form->control('date_of_packing', array(
                                      'type'=>'text',
                                      'escape'=>false,
                                      'id'=>'date_of_packing',
                                      'label'=>false,
                                      'class'=>$class1,
                                      'placeholder'=>$placeholder
                                    )); ?>
                                    <span id="error-date_of_packing"></span>
                              </td>
                              <td>
                                  <?php
                                    echo $this->Form->control('grade', array(
                                      'type'=>'select',
                                      'empty'=>'Select grade',
                                      'id'=>'grade',
                                      'options'=>$section_form_details[10],
                                      'label'=>false,
                                      'class'=>'form-control wd120'
                                    )); ?>
                                    <span id="error-grade"></span>
                              </td>
                              <td>
                                  <?php echo $this->Form->control('ta-packet_size-', array(
                                    'type'=>'number',
                                    'escape'=>false,
                                    'id'=>'ta-packet_size-',
                                    'options'=>$section_form_details[10],
                                    'label'=>false,
                                    'class'=>$class1,
                                    // 'placeholder'=>'Enter Pack Size'
                                  )); ?>
                                  <span id="error-ta-packet_size-"></span>
                              </td>
                                  
                              <td>


                              <?php echo $this->Form->control('ta-packet_size_unit-', array(
                                'type'=>'select',
                                'empty'=>'Select Unit',
                                'id'=>'ta-packet_size_unit-',
                                'options'=>$section_form_details[13],
                                'label'=>false,
                                'class'=>$class1,
                              )); ?>
                              <span id="error-ta-packet_size_unit-"></span>
                            </td>
                              
                            
                              <td>
                                  <?php echo $this->Form->control('ta-no_of_packets-', array(
                                    'type'=>'number',
                                    'escape'=>false,
                                    'id'=>'ta-no_of_packets-',
                                    'label'=>false,
                                    'class'=>'total_no_packages form-control input-field wd120',
                                    // 'placeholder'=>'Enter Total No. of packages'
                                  )); ?>
                                  <span id="error-ta-no_of_packets-"></span>
                              </td>
                              <td>
                                  <?php echo $this->Form->control('total_qty_graded_quintal', array(
                                    'type'=>'text',
                                    'escape'=>false,
                                    'id'=>'total_qty_graded_quintal',
                                    'label'=>false,
                                    'class'=>'form-control input-field',
                                    'readonly'=>'readonly',
                                  
                                  )); ?>
                                  <span id="totalQty"></span>
                                <span id="error-total_qty_graded_quintal"></span>
                              </td>
                              <td>
                                    <?php echo $this->Form->control('estimated_value', array(
                                      'type'=>'text',
                                      'escape'=>false,
                                      'id'=>'estimated_value',
                                      'label'=>false,
                                      'class'=>$class1,
                                      // 'placeholder'=>'0.00'
                                    )); ?>
                                  <span id="error-estimated_value"></span>
                              </td>
                              <td>
                                  <?php echo $this->Form->control('agmark_replica_from', array(
                                      'type'=>'text',
                                      'escape'=>false,
                                      'id'=>'agmark_replica_from',
                                      'label'=>false,
                                      // 'placeholder'=>'',
                                      'class'=>$class1,
                                    )); ?>
                                  <span id="error-agmark_replica_from"></span>
                              </td>
                              <td>
                                  <?php echo $this->Form->control('agmark_replica_to', array(
                                      'type'=>'text',
                                      'escape'=>false,
                                      'id'=>'agmark_replica_to',
                                      'placeholder'=>'',
                                      'label'=>false,
                                      'class'=>$class1,
                                    )); ?>
                                    <span id="error-agmark_replica_to"></span>
                              </td>
                              <td>
                                  <?php echo $this->Form->control('agmark_replica_total', array(
                                      'type'=>'text',
                                      'escape'=>false,
                                      'id'=>'agmark_replica_total',
                                      'label'=>false,
                                      'class'=>$class1,
                                    )); ?>
                                  <span id="error-agmark_replica_total"></span>
                              </td>
                              <td>
                                  <?php echo $this->Form->control('replica_charges', array(
                                      'type'=>'text',
                                      'escape'=>false,
                                      'id'=>'replica_charges',
                                      // 'readonly'=>'readonly',
                                      'label'=>false,
                                      'class'=>$class1,
                                      // 'placeholder'=>'0.00'
                                    )); ?>
                                  <span id="error-replica_charges"></span>
                              </td>
                              
                              <td>
                                <?php echo $this->Form->control('laboratory_name', array(
                                      'type'=>'select',
                                      'escape'=>false,
                                      'id'=>'laboratory_name',
                                      'empty'=>'Select Laboratory name',
                                      'options'=>$section_form_details[11],
                                      'label'=>false,
                                      'class'=>$class1,
                                    )); ?>
                                 <span id="error-laboratory_name"></span>
                              </td>

                              <td>
                                  <?php echo $this->Form->control('report_no', array(
                                    'type'=>'text',
                                    'escape'=>false,
                                    'id'=>'report_no',
                                    'label'=>false,
                                    'class'=>$class1,
                                    // 'placeholder'=>'Enter Report No.'
                                  )); ?>
                                  <span id="error-report_no"></span>
                              </td>
                              <td>
                                  <?php echo $this->Form->control('report_date', array(
                                      'type'=>'text',
                                      'escape'=>false,
                                      'id'=>'report_date',
                                      'label'=>false,
                                      'class'=>$class1,
                                      'placeholder'=>$placeholder
                                    )); ?>
                                  <span id="error-report_date"></span>
                              </td>
                              <td>
                                  <?php echo $this->Form->control('remarks', array(
                                    'type'=>'textarea',
                                    'escape'=>false,
                                    'id'=>'remarks',
                                    'label'=>false,
                                    'class'=>$class1,
                                    // 'placeholder'=>'Enter Remarks'
                                  )); ?>
                                  <span id="error-remarks"></span>
                              </td>
                              <td>
                                <div class="form-buttons">
                                  <a href="#" id="add_bgr_details" class='table_record_add_btn btn btn-info btn-sm'>
                                  <i class="fa fa-plus"></i> Add</a>
                                  <a href="#" id="update_bgr_details" class="btn btn-info btn-sm">Save</a>
                                </div>
                              </td>
                            </tr>

                            <?php }
                          ?>
        
                  
                
                          
                        
                      </tbody>
                    </table>
                  </div>
                    <div class="col-md-3 float-right replica_charge">
                        <?php echo $this->Form->control('overall_total_chrg',
                        array(
                            'type'=>'number',
                            'id'=>'overall_total_chrg',
                            'value'=>'0',
                            'class'=>'form-control',
                            'readonly'=>true,
                            'label'=>'Total Replica Charges (Rs.):',
                            'required'=>true
                        )); ?>
                        <span id="bal_amt_exceeds_msg"></span>
                    </div>


          </div>
        </div>
      </div>
      
      <div class="form-horizontal border fileupload">
										<div class="card-body">
												<div class="row">
														<div class="col-md-3">
																<div class="form-group row">
																		<label for="field3" class="col-sm col-form-label">
																			<span>
																				<?php
																					if ($_SESSION['current_level'] == 'level_2'
																					 && $application_mode == 'edit' )
																					 {echo 'Other Upload Docs'; }
																					else { echo 'Other Upload Docs'; }
																					?>
																				</span><span class="cRed">*</span>
																		</label>

																				<span class="float-left">
																					<?php if ($_SESSION['current_level'] == 'level_2'
																					&& $application_mode == 'edit'
																					&& empty($section_form_details[0]
																					['other_upload_docs']))
																					{ echo 'Attach docs'; }else{ echo 'Attached docs'; } ?> :
																				<?php
																				if (!empty($section_form_details[0]['other_upload_docs'])) { ?>
																						<a id="other_upload_docs_value"
																						target="blank"
																						href="<?php
																						echo str_replace("D:/xampp/htdocs","",
																						$section_form_details[0]
																						['other_upload_docs']);
																						?>">
																						<?=$str2 = substr(array_values(array_slice((explode("/",$section_form_details[0]
																						['other_upload_docs'])), -1))[0],23);?></a>
																				<?php }else{ echo "No Document Provided" ;} ?>
																		</div>
																</div>
																<div class="col-md-3">
																		<div class="form-group row">
																				<div class="custom-file col-sm">
																							<input
																							type="file" name="other_upload_docs"
																							class="form-control" id="other_upload_docs" multiple='multiple'>
																							<span id="error_other_upload_docs" class="error invalid-feedback"></span>
																							<span id="error_type_other_upload_docs" class="error invalid-feedback"></span>
																							<span id="error_size_other_upload_docs" class="error warning"></span>
																					</div>
																		</div>
																			<p class="lab_form_note float-right mt-3">
																				<i class="fa fa-info-circle"></i> File type: PDF, jpg &amp; max size upto 2 MB</p>
														</div>
										</div>
								</div>
						</div>
           

<?php if(!empty($_SESSION['packer_id']) || isset($_SESSION['packer_id'])){
			$customer_id = $_SESSION['packer_id'];
		}else{
			$customer_id = $_SESSION['customer_id'];
		} ?>
<input type="hidden" id="custemer_id" value='<?php echo $customer_id; ?>'>
<input type="hidden" id="application_mode" value='<?php echo isset($_SESSION['application_mode'])?$_SESSION['application_mode']:""; ?>'>
<input type="hidden" id="lab_nabl_accredited" value='<?php echo $section_form_details[15]; ?>'>
<input type="hidden" name="total_revenue" id="totalRevenueHidden">
<input type="hidden" name="progresive_revenue" id="progresiveRevenueHidden">
<input type="hidden" name="current_level" id="current_level" value='<?php echo $_SESSION['current_level'];?>'>


<!-- end table -->
