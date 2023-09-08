
<!--    Comment:This file updated as per change and suggestions for UAT module after test run
	    Reason: updated as per change and suggestions for UAT module after test run
	    Name of person : shankhpal shende
	    Date: 13-05-2023
*/ -->
<?php //pr($section_form_details);die;
echo $this->Form->create(null, array('type'=>'file', 'enctype'=>'multipart/form-data', 'id'=>$section)); ?>
<section id="form_outer_main" class="content form-middle">
    <div class="container-fluid" id="form_outer_main">
        <h5 class="mt-1 mb-2">Routine Inspection Report (CA-Packer)</h5>
            <div id='form_inner_main'>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-success" >
                            <!-- Initial Details -->
                            <div class="card-header sub-card-header-firm"><h3 class="card-title">Initial Details</h3></div>
                                <div class="form-horizontal">
                                    <div class="card-body border">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                     <label for="inputEmail3" class="col-sm col-form-label">Date of Last Inspection <span class="cRed">*</span></label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                               <?php echo $this->Form->control('date_last_inspection', array('type'=>'text', 'id'=>'date_last_inspection', 'value'=>$section_form_details[0]['date_last_inspection'], 'class'=>'form-control input-field', 'placeholder'=>'Enter DD/MM/YYYY', 'label'=>false)); ?>
                                                <span id="error_date_last_inspection" class="error invalid-feedback"></span>
                                                 </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-horizontal">
                                    <div class="card-body ">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                     <label for="inputEmail3" class="col-sm col-form-label">Date & Time of present Inspection <span class="cRed">*</span></label>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                              <?php echo $this->Form->control('date_p_inspection', array('type'=>'text', 'id'=>'date_p_inspection', 'value'=>$section_form_details[0]['date_p_inspection'], 'class'=>'form-control input-field', 'placeholder'=>'Enter DD/MM/YYYY', 'label'=>false)); ?>
                                                <span id="error_date_p_inspection" class="error invalid-feedback"></span>
                                            </div>
                                            <div class="col-md-3">
                                               <!-- Added time field as per change req and suggestions on date 27/06/2023 added by shankhpal shende -->
                                                <?php
                                                echo $this->Form->control('time_p_inspection', [
                                                    'type' => 'select',
                                                    'id' => 'time_p_inspection',
                                                    'options' => $section_form_details[9],
                                                    'default' => $section_form_details[0]['time_p_inspection'],
                                                    'class' => 'form-control input-field',
                                                    'label' => false
                                                ]);
                                                ?>
                                                <span id="error_time_p_inspection" class="error invalid-feedback"></span>
                                                 </div>
                                            </div>
                                        </div>
                                    </div>

                                     <!-- 1. Name of Authorized Packer -->
                            <div class="card-header sub-card-header-firm"><h3 class="card-title">1. Name of Authorized Packer</h3></div>
                                <div class="form-horizontal">
                                    <div class="card-body border">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                     <label for="inputEmail3" class="col-sm col-form-label">Firm Name <span class="cRed">*</span></label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                               <?php echo $this->Form->control('name_authorized_packer', array('type'=>'text', 'id'=>'name_authorized_packer','value'=>$firm_details['firm_name'],'class'=>'form-control','label'=>false)); ?>
                                                <span id="error_name_authorized_packer" class="error invalid-feedback"></span>
                                                 </div>
                                            </div>
                                        </div>
                                    </div>
                                <!-- 2. Address of the Authorized Premises -->
                            <div class="card-header sub-card-header-firm"><h3 class="card-title">2. Address of the Authorized Premises</h3></div>
                                <div class="form-horizontal">
                                    <div class="card-body border">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                     <label for="inputEmail3" class="col-sm col-form-label">Address </label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <?php echo $this->Form->control('street_address', array('type'=>'textarea', 'id'=>'street_address','value'=>$firm_details['street_address'],  'class'=>'form-control input-field readOnly', 'label'=>false)); ?>
                                                <span id="error_street_address" class="error invalid-feedback"></span>
                                                 </div>
                                            </div>
                                        </div>
                                    </div>
                            <!-- 3. Contact Details of the packer -->
                            <div class="card-header sub-card-header-firm"><h3 class="card-title">3. Contact Details of the packer</h3></div>
                                <div class="form-horizontal">
                                    <div class="card-body border">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                     <label for="inputEmail3" class="col-sm col-form-label">Email ID<span class="cRed">*</span></label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <?php echo $this->Form->control('email', array('type'=>'email', 'id'=>'email', 'class'=>'form-control', 'label'=>false,'value'=>base64_decode($firm_details['email']))); ?>
                                                    <span id="error_email" class="error invalid-feedback"></span>
                                                 </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-horizontal">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                     <label for="inputEmail3" class="col-sm col-form-label">Mobile No.<span class="cRed">*</span></label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <?php echo $this->Form->control('mobile_no', array('type'=>'text','value'=>base64_decode($firm_details['mobile_no']),'id'=>'mobile_no', 'class'=>'form-control', 'label'=>false)); ?>
                                                <span id="error_mobile_no" class="error invalid-feedback"></span>
                                                 </div>
                                            </div>
                                        </div>
                                    </div>
                            <!-- 4. Certificate of Authorization No. and valid upto -->
                            <div class="card-header sub-card-header-firm"><h3 class="card-title">4. Certificate of Authorization No. and valid upto</h3></div>
                                <div class="form-horizontal">
                                    <div class="card-body border">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                     <label for="inputEmail3" class="col-sm col-form-label">Certificate No.</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <?php echo $this->Form->control('certificate_no', array('type'=>'text', 'id'=>'certificate_no','value'=>$firm_details['customer_id'],  'class'=>'form-control readOnly', 'label'=>false)); ?>
                                                <span id="error_certificate_no" class="error invalid-feedback"></span>
                                                 </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-horizontal">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                     <label for="inputEmail3" class="col-sm col-form-label">Valid Upto.</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                               <?php echo $this->Form->control('valid_upto', array('type'=>'text', 'id'=>'valid_upto', 'class'=>'form-control readOnly','value'=>$section_form_details[2],'label'=>false)); ?>
                                                <span id="error_valid_upto" class="error invalid-feedback"></span>
                                                 </div>
                                            </div>
                                        </div>
                                    </div>

                                    
                                       
                            <!-- 5. Commodity (ies) for which CA is granted -->
                            <div class="card-header sub-card-header-firm"><h3 class="card-title">5. Commodity (ies) for which CA is granted</h3></div>
                                <div class="form-horizontal">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                     <label for="inputEmail3" class="col-sm col-form-label">Commodities</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                        <?php echo $this->Form->control('sub_commodity', array('type'=>'select', 'options'=>$sub_commodity_value, 'multiple'=>'multiple', 'label'=>false, 'class'=>'form-control readOnly')); ?>
                                                 </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                            <!-- 6. Name of the grading Laboratory -->
                             <div class="card-header sub-card-header-firm"><h3 class="card-title">6. Name of the grading Laboratory</h3></div>
                                <div class="form-horizontal">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                     <label for="inputEmail3" class="col-sm col-form-label">Grading laboratory</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                  <?php $i=1;
                                                        if($section_form_details[4] != null){ ?>
                                                        <table  class="table m-0 table-bordered table-striped table-hover">
                                                        <thead class="">
                                                            <tr>
                                                                <th>Sr.no.</th>
                                                                <th>Grading laboratory</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php 
                                                            foreach ($section_form_details[4] as $value) { ?>
                                                                <tr>
                                                                    <td><?php echo $i; ?></td>
                                                                    <td><?php echo $value['firm_name']; ?></td>
                                                                </tr>
                                                            <?php $i++; }}else{ ?>
                                                            <div class="colorWarning margin5 header-text">Grading laboratory must mapped by CA on the System</div>
                                                     <?php } ?>    
                                                        </tbody>
                                                    </table>
                                                     <ol class="badge mt-2">
                                                    <a target="_blank" href="/testdocs/DMI/manuals/applicant/Manual_mapping_lab_pp.pdf">Manual for Mapping of CA With Lab/Printing Press</a>
                                                </ol>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 7. Name of approved Printing Press -->
                                <div class="card-header sub-card-header-firm"><h3 class="card-title">7. Name of approved Printing Press</h3></div>
                                <div class="form-horizontal">
                                    <div class="card-body border">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                     <label for="inputEmail3" class="col-sm col-form-label">Approved Printing press</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                 <?php $i=1;
                                                        if($section_form_details[5] != null){ ?>
                                                        <table  class="table m-0 table-bordered table-striped table-hover">
                                                        <thead class="">
                                                            <tr>
                                                                <th>Sr.no.</th>
                                                                <th>Printing press</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php 
                                                            foreach ($section_form_details[5] as $value) { ?>
                                                                <tr>
                                                                    <td><?php echo $i; ?></td>
                                                                    <td><?php echo $value['firm_name']; ?></td>
                                                                </tr>
                                                            <?php $i++; }}else{ ?>
                                                            <div class="colorWarning margin5 header-text">Printing Press must by mapped by CA on the System</div>
                                                            <?php } ?>    
                                                        </tbody>
                                                    </table>
                                                     <ol class="badge mt-2">
                                                    <a target="_blank" href="/testdocs/DMI/manuals/applicant/Manual_mapping_lab_pp.pdf">Manual for Mapping of CA With Lab/Printing Press</a>
                                                </ol>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-horizontal">
                                    <div class="card-body ">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                     <label for="inputEmail3" class="col-sm col-form-label">a) Record of invoice of print Agmark replica is upto date or not?<span class="cRed">*</span></label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                     <?php 
                                                            $i=1;
                                                            $record_of_invice = $section_form_details[0]['record_of_invice'];
                                                    
                                                                if($record_of_invice == 'yes'){
                                                                    $checked_yes = 'checked';
                                                                    $checked_no = '';
                                                                } else {

                                                                    $checked_yes = '';
                                                                    $checked_no = 'checked';
                                                                }
                                                            $i++;
                                                        ?>
                                                    <div class="col-sm-7">
                                                        <div class=" d-inline">
                                                            <input type="radio" name="record_of_invice" checked="" id="record_of_invice-yes" value="yes"<?php echo $checked_yes; ?>>
                                                            <label for="record_of_invice-yes">Yes
                                                            </label>
                                                        </div>
                                                        <div class=" d-inline">
                                                            <input type="radio" name="record_of_invice" id="record_of_invice-no" value="no" <?php echo $checked_no; ?>>
                                                            <label for="record_of_invice-no">No
                                                            </label>
                                                        </div>
                                                    <span id="error_record_of_invice" class="error invalid-feedback"></span>
                                                     </div>
                                                 </div>
                                                 
                                            </div>
                                        </div>
                                    </div>
                                    
                                <!-- 8. Name of the chemist Incharge Whether present at the time of Inspection -->
                            <div class="card-header sub-card-header-firm"><h3 class="card-title">8. Name of the chemist Incharge Whether present at the time of Inspection</h3></div>
                                <div class="form-horizontal">
                                    <div class="card-body border">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                       <label for="inputEmail3" class="col-sm col-form-label">Chemist Incharge</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <?php if($section_form_details[6] != null){?>    
                                                    <?php echo $this->Form->control('chemist_incharge', array('type'=>'text', 'placeholder'=>'Enter name of the chemist incharge','value'=>$section_form_details[6][0]['chemist_fname']." ".$section_form_details[6][0]['chemist_lname'],'readonly'=>true, 'id'=>'chemist_incharge', 'class'=>'form-control input-field', 'label'=>false)); ?>
                                                <?php }else{ ?>
                                                    <?php //echo $this->Form->control('chemist_incharge', array('type'=>'text', 'readonly'=>true, 'id'=>'fax_no', 'class'=>'form-control input-field', 'label'=>false)); ?>
                                                        <div class="colorWarning margin5 header-text col-form-label"> Chemist Register and mapped by CA on the System</div>
                                                    <span id="error_chemist_incharge" class="error invalid-feedback"></span>
                                                <?php } ?>
                                                           
                                                <ol class="badge">
                                                    <a target="_blank" href="/testdocs/DMI/manuals/applicant/Chemist Registration.pdf">Manual for Chemist Registeration</a>
                                                </ol>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-horizontal">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                     <label for="inputEmail3" class="col-sm col-form-label">Present at the time of Inspection<span class="cRed">*</span></label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                      <?php 
                                                            $i=1;
                                                            $present_time_of_inspection = isset($section_form_details[0]['present_time_of_inspection'])?$section_form_details[0]['present_time_of_inspection']:"";
                                                    
                                                                if($present_time_of_inspection == 'yes'){
                                                                    $checked_yes = 'checked';
                                                                    $checked_no = '';
                                                                } else {

                                                                    $checked_yes = '';
                                                                    $checked_no = 'checked';
                                                                }
                                                            $i++;
                                                        ?>
                                                       
                                                            <div class=" d-inline">
                                                                <input type="radio" name="present_time_of_inspection" checked="" id="present_time_of_inspection-yes" value="yes"<?php echo $checked_yes; ?>>
                                                                <label for="present_time_of_inspection-yes">Yes
                                                                </label>
                                                            </div>
                                                        <div class=" d-inline">
                                                            <input type="radio" name="present_time_of_inspection" id="present_time_of_inspection-no" value="no" <?php echo $checked_no; ?>>
                                                            <label for="present_time_of_inspection-no">No
                                                            </label>
                                                        </div>
                                                        <span id="error_present_time_of_inspection" class="error invalid-feedback"></span>
                                                 </div>
                                            </div>
                                        </div>
                                    </div>
                               
                                <!-- 9. Is the premises adequately lighted, ventilated & hygienic -->
                                <div class="card-header sub-card-header-firm"><h3 class="card-title">9. Is the premises adequately lighted, ventilated & hygienic</h3></div>
                                <div class="form-horizontal">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                     <label for="inputEmail3" class="col-sm col-form-label">Is the premises adequately lighted, ventilated & hygienic<span class="cRed">*</span></label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                      <?php
                                                            $i=1;
                                                            $premises_adequately = $section_form_details[0]['premises_adequately'];
                                                            
                                                            if($premises_adequately == 'yes'){
                                                                $checked_yes = 'checked';
                                                                $checked_no = '';
                                                            } else {

                                                                $checked_yes = '';
                                                                $checked_no = 'checked';
                                                            }
                                                            $i++;
                                                            ?>
                                                    
                                                            <div class=" d-inline">
                                                                <input type="radio" name="premises_adequately" checked="" id="premises_adequately-yes" value="yes" <?php echo $checked_yes; ?>>
                                                                <label for="premises_adequately-yes">Yes
                                                                </label>
                                                            </div>
                                                            <div class=" d-inline">
                                                                <input type="radio" name="premises_adequately" id="premises_adequately-no" value="no" <?php  echo $checked_no; ?>>
                                                                <label for="premises_adequately-no">No
                                                                </label>
                                                            </div>
                                                        <span id="error_premises_adequately" class="error invalid-feedback"></span>
                                                     </div>
                                                 </div>
                                            </div>
                                        </div>
                                    </div>
                               
                                <!-- 10. Is the laboratory properly equipped -->

                            <div class="card-header sub-card-header-firm"><h3 class="card-title">10. Is the laboratory properly equipped</h3></div>
                                <div class="form-horizontal">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                     <label for="inputEmail3" class="col-sm col-form-label">Is the laboratory properly equipped<span class="cRed">*</span></label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                       <?php
                                                            $i=1;
                                                            $lab_properly_equipped = $section_form_details[0]['lab_properly_equipped'];
                                                            
                                                            if($lab_properly_equipped == 'yes'){
                                                                $checked_yes = 'checked';
                                                                $checked_no = '';
                                                            } else {

                                                                $checked_yes = '';
                                                                $checked_no = 'checked';
                                                            }
                                                            $i++;
                                                            ?>
                                                            <div class=" d-inline">
                                                                <input type="radio" name="lab_properly_equipped" checked="" id="lab_properly_equipped-yes" value="yes" <?php echo $checked_yes; ?>>
                                                                <label for="lab_properly_equipped-yes">Yes
                                                                </label>
                                                            </div>
                                                            <div class=" d-inline">
                                                                <input type="radio" name="lab_properly_equipped" id="lab_properly_equipped-no" value="no" <?php echo $checked_no; ?>>
                                                                <label for="lab_properly_equipped-no">No
                                                                </label>
                                                            </div>
                                                        <span id="error_lab_properly_equipped" class="error invalid-feedback"></span>
                                                     </div>
                                                 </div>
                                            </div>
                                        </div>
                                    </div>
                            <!-- 11. Grading Records -->
                            <div class="card-header sub-card-header-firm"><h3 class="card-title">11. Grading Records</h3></div>
                                <div class="form-horizontal">
                                    <div class="card-body border">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                      <label for="inputEmail3" class="col-sm col-form-label">a) Are they up to date<span class="cRed">*</span></label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                      <?php
                                                        $i=1;
                                                        $are_you_upto_date = $section_form_details[0]['are_you_upto_date'];
                                                        
                                                            if($are_you_upto_date == 'yes'){
                                                                $checked_yes = 'checked';
                                                                $checked_no = '';
                                                            } else {

                                                                $checked_yes = '';
                                                                $checked_no = 'checked';
                                                            }
                                                            $i++;
                                                        ?>
                                                            <div class=" d-inline">
                                                                <input type="radio" name="are_you_upto_date" checked="" id="are_you_upto_date-yes" value="yes" <?php echo $checked_yes; ?>>
                                                                <label for="are_you_upto_date-yes">Yes
                                                                </label>
                                                            </div>
                                                            <div class=" d-inline">
                                                                <input type="radio" name="are_you_upto_date" id="are_you_upto_date-no" value="no" <?php  echo $checked_no; ?>>
                                                                <label for="are_you_upto_date-no">No
                                                                </label>
                                                            </div>
                                                            <span id="error_are_you_upto_date" class="error invalid-feedback"></span>
                                                     </div>
                                                 </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <div class="form-horizontal">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                      <label for="inputEmail3" class="col-sm col-form-label">b) Are they being forwarded to the concerned offices in time?<span class="cRed">*</span></label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                       <?php
                                                $i=1;
                                                $concerned_offices = $section_form_details[0]['concerned_offices'];
                                                
                                                    if($concerned_offices == 'yes'){
                                                        $checked_yes = 'checked';
                                                        $checked_no = '';
                                                    } else {

                                                        $checked_yes = '';
                                                        $checked_no = 'checked';
                                                    }
                                                    $i++;
                                                ?>
                                                    <div class=" d-inline">
                                                        <input type="radio" name="concerned_offices" checked="" id="concerned_offices-yes" value="yes" <?php echo $checked_yes; ?>>
                                                        <label for="concerned_offices-yes">Yes
                                                        </label>
                                                    </div>
                                                    <div class=" d-inline">
                                                        <input type="radio" name="concerned_offices" id="concerned_offices-no" value="no" <?php echo $checked_no; ?>>
                                                        <label for="concerned_offices-no">No
                                                        </label>
                                                    </div>
                                                <span id="error_concerned_offices" class="error invalid-feedback"></span>
                                                     </div>
                                                 </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <!-- 12. Last Lot No. Dated And its Analytical Results -->
                          <div class="card-header sub-card-header-firm ><h3 class="card-title">12. Last Lot No. Dated And its Analytical Results</h3></div>
                            <div class="form-horizontal ">
                                <div class="card-body border">
                                    <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label for="inputEmail3" class="col-sm-5 col-form-label">Last lot No.<span class="cRed">*</span></label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <div class="col-sm">
                                                  <?php echo $this->Form->control('last_lot_no', array('type'=>'text', 'id'=>'last_lot_no', 'class'=>'form-control input-field', 'value '=>$section_form_details[0]['last_lot_no'],'placeholder'=>'Last lot No.', 'label'=>false)); ?>
                                                 <span id="error_last_lot_no" class="error invalid-feedback error_last_lot_no"></span>
                                            </div> 
                                        </div>
                                    </div>
                                </div>
                            </div>
                             <div class="form-horizontal">
                                <div class="card-body border">
                                    <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label for="inputEmail3" class="col-sm-5 col-form-label">Date<span class="cRed">*</span></label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <div class="col-sm">
                                                 <?php echo $this->Form->control('last_lot_date', array('type'=>'text', 'id'=>'last_lot_date', 'value'=>$section_form_details[0]['last_lot_date'], 'class'=>'form-control input-field', 'placeholder'=>'Enter DD/MM/YYYY', 'label'=>false)); ?>
                                                <span id="error_last_lot_date" class="error invalid-feedback"></span>
                                            </div> 
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-horizontal">
                                <div class="card-body border">
                                    <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label for="inputEmail3" class="col-sm-5 col-form-label">Analytical Results<span class="cRed">*</span></label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <div class="col-sm">
                                                <?php echo $this->Form->control('analytical_results', array('type'=>'text', 'id'=>'analytical_results', 'class'=>'form-control', 'placeholder'=>'Enter Analytical Results','value '=>isset($section_form_details[0]['analytical_results'])?$section_form_details[0]['analytical_results']:"", 'label'=>false)); ?>
                                                <span id="error_analytical_results" class="error invalid-feedback"></span>
                                            </div> 
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-horizontal">
                                    <div class="card-body border">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                      <label for="field3" class="col-sm col-form-label"><span><?php if ($current_level == 'level_2' && $application_mode == 'edit' ) { echo 'Analytical Results Doc'; } else { echo 'Analytical Results Doc'; } ?></span></label>

                                                    <span class="float-left"><?php if ($current_level == 'level_2' && $application_mode == 'edit' && empty($section_form_details[0]['analytical_result_docs'])) { echo 'Attach doc'; }else{ echo 'Attached doc'; } ?> :
                                                    <?php if (!empty($section_form_details[0]['analytical_result_docs'])) { ?>
                                                        <a id="analytical_result_docs_value" target="blank" href="<?php echo str_replace("D:/xampp/htdocs","",$section_form_details[0]['analytical_result_docs']); ?>"><?=$str2 = substr(array_values(array_slice((explode("/",$section_form_details[0]['analytical_result_docs'])), -1))[0],23);?></a>
                                                    <?php }else{ echo "No Document Provided" ;} ?>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                    <div class="custom-file col-sm">
                                                        <input type="file" name="analytical_result_docs" class="form-control" id="analytical_result_docs" multiple='multiple'>
                                                        <span id="error_analytical_result_docs" class="error invalid-feedback"></span>
                                                        <span id="error_type_analytical_result_docs" class="error invalid-feedback"></span>
                                                        <span id="error_size_analytical_result_docs" class="error invalid-feedback"></span>
                                                     </div>
                                                </div> 
                                              <p class="lab_form_note float-right mt-3"><i class="fa fa-info-circle"></i> File type: PDF, jpg &amp; max size upto 2 MB</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 13. Quantity Graded During Current Month Upto -->
                            <div class="card-header sub-card-header-firm">
                            <h3 class="card-title">13. Quantity Graded During Current Month Upto</h3>
                            </div>
                            <div class="form-horizontal">
                            <div class="card-body">
                                <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                     <label for="inputEmail3" class="col-form-label">Quantity Graded During Current Month Upto<span class="cRed">*</span></label>
                                   <?php echo $this->Form->control('month_upto', array('type'=>'text', 'id'=>'month_upto', 'value'=>$section_form_details[0]['month_upto'], 'class'=>'form-control input-field', 'placeholder'=>'Enter DD/MM/YYYY', 'label'=>false)); ?>
                                    <span id="error_month_upto" class="error invalid-feedback"></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                    <label for="inputEmail2" class="col-form-label">Quantity<span class="cRed">*</span></label>
                                    <?php echo $this->Form->control('quantity', array('type'=>'text', 'id'=>'quantity', 'value'=>$section_form_details[0]['quantity'], 'class'=>'form-control input-field', 'placeholder'=>'Enter Quantity', 'label'=>false)); ?>
                                    <span id="error_quantity" class="error invalid-feedback"></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                    <label for="inputEmail3" class="col-form-label">Units</label>
                                     <?php echo $this->Form->control('grade_units', array('type'=>'select', 'options'=>$section_form_details[8], 'default' => $section_form_details[0]['grade_units'], 'label'=>false, 'class'=>'form-control')); ?>
                                    <span id="error_grade_units" class="error invalid-feedback"></span>
                                    </div>
                                </div>
                                </div>
                            </div>
                            </div>



                            <!-- 14. Is the Agmark Replica account correct? -->
                          
                        <div class="card-header sub-card-header-firm"><h3 class="card-title">14. Is the Agmark Replica account correct?</h3></div>
                            <div class="form-horizontal">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label for="inputEmail3" class="col-sm col-form-label">Is the Agmark Replica account correct?<span class="cRed">*</span></label>
                                            </div>
                                        </div>
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <div class="col-sm">
                                                <?php
                                                    $i=1;
                                                    $replica_account_correct = $section_form_details[0]['replica_account_correct'];
                                                
                                                    if($replica_account_correct == 'yes'){
                                                        $checked_yes = 'checked';
                                                        $checked_no = '';
                                                    } else {

                                                        $checked_yes = '';
                                                        $checked_no = 'checked';
                                                    }
                                                    $i++;
                                                ?>
                                                <div class="d-inline">
                                                    <input type="radio" name="replica_account_correct" checked="" id="replica_account_correct-yes" value="yes" <?php echo $checked_yes; ?>>
                                                    <label for="replica_account_correct-yes">Yes
                                                    </label>
                                                </div>
                                                <div class=" d-inline">
                                                    <input type="radio" name="replica_account_correct" id="replica_account_correct-no" value="no" <?php echo $checked_no; ?>>
                                                    <label for="replica_account_correct-no">No
                                                    </label>
                                                </div> 
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                            </div>

                        <div class="form-horizontal">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6" ></div>
                                    <div class="col-md-6" id="hide_disc_replica">
                                        <div class="form-group row">
                                            <div class="col-sm">
                                                 <?php echo $this->Form->control('discrepancies_replica_aco', array('type'=>'textarea', 'id'=>'discrepancies_replica_aco', 'escape'=>false, 'value'=>$section_form_details[0]['discrepancies_replica_aco'], 'class'=>'form-control input-field', 'label'=>false, 'placeholder'=>'Enter discrepancies here...')); ?>
                                                <span id="error_discrepancies_replica_aco" class="error invalid-feedback"></span>
                                                </div> 
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                            </div>
                            <!-- 15. Is the packer getting its lots tested by FSSAI approved Lab for food safety parameters every 6 months? -->
                            <div class="card-header sub-card-header-firm"><h3 class="card-title">15. Is the packer getting its lots tested by FSSAI approved Lab for food safety parameters every 6 months?</h3></div>
                                <div class="form-horizontal">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                      <label for="inputEmail3" class="col-sm col-form-label">Is the packer getting its lots tested by FSSAI approved Lab for food safety parameters every 6 months?<span class="cRed">*</span></label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                      <?php
                                                    $i=1;
                                                    $fssai_approved = isset($section_form_details[0]['fssai_approved'])?$section_form_details[0]['fssai_approved']:"";
                                                    
                                                        if($fssai_approved == 'yes'){
                                                            $checked_yes = 'checked';
                                                            $checked_no = '';
                                                        } else {

                                                            $checked_yes = '';
                                                            $checked_no = 'checked';
                                                        }
                                                        $i++;
                                                    ?>
                                                    <div class="d-inline">
                                                        <input type="radio" name="fssai_approved" checked="" id="fssai_approved-yes" value="yes" <?php echo $checked_yes; ?>>
                                                        <label for="fssai_approved-yes">Yes
                                                        </label>
                                                    </div>
                                                    <div class=" d-inline">
                                                        <input type="radio" name="fssai_approved" id="fssai_approved-no" value="no" <?php echo $checked_no; ?>>
                                                        <label for="fssai_approved-no">No
                                                        </label>
                                                     </div>
                                                 </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- 16.Collection of check samples -->
                            <div class="card-header sub-card-header-firm"><h3 class="card-title">16. Collection of check samples </h3></div>
                                <?php echo $this->element('rti_addmore_element/rti_addmore_element'); ?>
                            </div>
                            <!-- 17.Enumerate briefly suggestions given during last inspection and state, if carried out -->
                            <div class="card-header sub-card-header-firm"><h3 class="card-title">17. Enumerate briefly suggestions given during last inspection and state, if carried out</h3></div>
                                <div class="form-horizontal">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="inputEmail3" class="col-sm col-form-label">Enumerate briefly suggestions given during last inspection and state, if carried out<span class="cRed">*</span></label>
                                                <button class="m-3" id="last-sugeesion-popup">Get all previous suggestions</button>
                                                    <!-- pop up for display last suggestions -->
                                                <section class="popup">
                                                    <div class="popup__content">
                                                        <div class="close">
                                                        <span></span>
                                                        <span></span>
                                                        </div>
                                                    <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                        <th scope="col">#</th>
                                                        <th scope="col">IO Users</th>
                                                        <th scope="col">suggestions</th>
                                                        <th scope="col">Date</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php $total_suggestions = $section_form_details[7];
                                                        
                                                        $sr=1;
                                                        if(!empty($total_suggestions)){
                                                        foreach ($total_suggestions as $each_sugg){?>
                                                        <tr>
                                                            <th scope="row"><?php echo $sr; ?></th>
                                                            <td class="text-center"><?php echo $each_sugg['io_user_name']; ?></td>
                                                            <td class="text-center"><?php
                                                                if (empty($each_sugg['enumerate_briefly_suggestions'])) {
                                                                    echo "NA";
                                                                } else {
                                                                    echo $each_sugg['enumerate_briefly_suggestions'];
                                                                }
                                                                ?></td>
                                                             <td class="text-center"><?php echo $each_sugg['approved_date']; ?></td>
                                                        </tr>
                                                        <?php $sr++; }}else{
                                                             echo "<tr><td colspan='4' class='text-center'>No suggestions found.</td></tr>";
                                                        } ?>
                                                    </tbody>
                                                    </table>
                                                    </div>
                                                </section>
                                                </div>
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                         <?php
                                                            $i=1;
                                                            $e_briefly_suggestions_radio = isset($section_form_details[0]['e_briefly_suggestions_radio'])?$section_form_details[0]['e_briefly_suggestions_radio']:"";
                                                            
                                                                if($e_briefly_suggestions_radio == 'yes'){
                                                                    $checked_yes = 'checked';
                                                                    $checked_no = '';
                                                                } else {

                                                                    $checked_yes = '';
                                                                    $checked_no = 'checked';
                                                                }
                                                                $i++;
                                                            ?>
                                                            <div class="d-inline">
                                                                <input type="radio" name="e_briefly_suggestions_radio" checked="" id="e_briefly_suggestions_radio-yes" value="yes" <?php echo $checked_yes; ?>>
                                                                <label for="e_briefly_suggestions_radio-yes">Yes
                                                                </label>
                                                            </div>
                                                            <div class=" d-inline">
                                                                <input type="radio" name="e_briefly_suggestions_radio" id="e_briefly_suggestions_radio-no" value="no" <?php echo $checked_no; ?>>
                                                                <label for="e_briefly_suggestions_radio-no">No
                                                                </label>
                                                            </div>
                                                            <div class="col-sm-9">
                                                                <?php 
                                                                 echo $this->Form->control('enumerate_briefly_suggestions', array('type'=>'textarea', 'id'=>'enumerate_briefly_suggestions', 'escape'=>false,'value'=>isset($section_form_details[0]['enumerate_briefly_suggestions'])?$section_form_details[0]['enumerate_briefly_suggestions']:"", 'class'=>'form-control input-field', 'label'=>false, 'placeholder'=>'Enter discrepancies here...')); ?>
                                                                <span id="error_enumerate_briefly_suggestions" class="error invalid-feedback"></span>
                                                            </div>    
                                                        </div>
                                                     </div>    
                                                 </div>
                                            </div>
                                        </div>
                                    </div>
                            <!-- 18. Shortcomings noticed in present inspection -->
                            <div class="card-header sub-card-header-firm"><h3 class="card-title">18. Shortcomings noticed in present inspection</h3></div>
                                <div class="form-horizontal">
                                    <div class="card-body border">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                     <label for="inputEmail3" class="col-sm col-form-label">Shortcomings noticed in present inspection:<span class="cRed"> * </span></label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                        <div class="col-sm">
                                                    <?php echo $this->Form->control('shortcomings_noticed', array('type'=>'textarea', 'id'=>'shortcomings_noticed','placeholder'=>'Shortcomings noticed','class'=>'form-control', 'label'=>false,'value'=>isset($section_form_details[0]['shortcomings_noticed'])?$section_form_details[0]['shortcomings_noticed']:"")); ?>
                                                    <span id="error_shortcomings_noticed" class="error invalid-feedback"></span> 
                                                </div> 
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-horizontal">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                     <label for="field3" class="col-sm col-form-label"><span><?php if ($current_level == 'level_2' && $application_mode == 'edit' ) { echo 'Shortcomings noticed Docs'; } else { echo 'Shortcomings noticed Docs'; } ?></span></label>

                                                    <span class="float-right"><?php if ($current_level == 'level_2' && $application_mode == 'edit' && empty($section_form_details[0]['shortcomings_noticed_docs'])) { echo 'Attach docs'; }else{ echo 'Attached docs'; } ?> :
                                                    <?php if (!empty($section_form_details[0]['shortcomings_noticed_docs'])) { ?>
                                                        <a id="shortcomings_noticed_docs_value" target="blank" href="<?php echo str_replace("D:/xampp/htdocs","",$section_form_details[0]['shortcomings_noticed_docs']); ?>"><?=$str2 = substr(array_values(array_slice((explode("/",$section_form_details[0]['shortcomings_noticed_docs'])), -1))[0],23);?></a>
                                                    <?php }else{ echo "No Document Provided" ;} ?>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                    <div class="custom-file col-sm">
                                                         <input type="file" name="shortcomings_noticed_docs" class="form-control" id="shortcomings_noticed_docs" multiple='multiple'>
                                                         <span id="error_shortcomings_noticed_docs" class="error invalid-feedback"></span>
                                                         <span id="error_type_shortcomings_noticed_docs" class="error invalid-feedback"></span>
                                                         <span id="error_size_shortcomings_noticed_docs" class="error warning"></span>
                                                     </div>
                                                </div> 
                                                 <p class="lab_form_note float-right mt-3"><i class="fa fa-info-circle"></i> File type: PDF, jpg &amp; max size upto 2 MB</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <!-- 18. end -->
                            
                        <!-- 19.Suggestions -->
                            <div class="card-header sub-card-header-firm"><h3 class="card-title">19. Suggestions</h3></div>
                            <div class="form-horizontal">
                                <div class="card-body">
                                    <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label for="inputEmail3" class="col-sm-5 col-form-label">Suggestions<span class="cRed">*</span></label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <div class="col-sm">
                                                <?php echo $this->Form->control('suggestions', array('type'=>'textarea', 'id'=>'suggestions', 'value'=>isset($section_form_details[0]['suggestions'])?$section_form_details[0]['suggestions']:"", 'class'=>'form-control input-field', 'placeholder'=>'Enter Suggestions', 'label'=>false)); ?>
                                                <span id="error_suggestions" class="error invalid-feedback"></span>
                                            </div> 
                                        </div>
                                    </div>
                                </div>
                            </div>
                <!-- Signnature and Name of the Packer or his representative-->          
                    <div class="card-header sub-card-header-firm"><h3 class="card-title">Signature and Name of the Packer or his representative</h3></div>
                            <div class="form-horizontal">
                                <div class="card-body border">
                                    <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label for="inputEmail3" class="col-sm col-form-label">Name of the Packer or his representative<span class="cRed">*</span></label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <div class="col-sm">
                                                 <?php echo $this->Form->control('name_packer_representative', array('type'=>'text', 'id'=>'name_packer_representative','value'=>isset($section_form_details[0]['name_packer_representative'])?$section_form_details[0]['name_packer_representative']:"", 'placeholder'=>'Enter Name of the Packer or his representative', 'class'=>'form-control', 'label'=>false)); ?>
                                                <span id="error_name_packer_representative" class="error invalid-feedback"></span>
                                            </div> 
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-horizontal">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                     <label for="field3" class="col-sm col-form-label"><span ><?php if ($current_level == 'level_2' && $application_mode == 'edit' ) { echo 'Signature'; } else { echo 'Signature'; } ?> <span class="cRed">*</span></label>

                                                    <span class="float-left"><?php if ($current_level == 'level_2' && $application_mode == 'edit' && empty($section_form_details[0]['signnature_of_packer_docs'])) { echo 'Attach doc'; }else{ echo 'Attached doc'; } ?> :
                                                    <?php if (!empty($section_form_details[0]['signnature_of_packer_docs'])) { ?>
                                                        <a id="signnature_of_packer_docs_value" target="blank" href="<?php echo str_replace("D:/xampp/htdocs","",$section_form_details[0]['signnature_of_packer_docs']); ?>"><?=$str2 = substr(array_values(array_slice((explode("/",$section_form_details[0]['signnature_of_packer_docs'])), -1))[0],23);?></a>
                                                    <?php }else{ echo "No Document Provided" ;} ?>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                    <div class="custom-file col-sm">
                                                         <input type="file" name="signnature_of_packer_docs" class="form-control" id="signnature_of_packer_docs" multiple='multiple'>
                                                        <span id="error_signnature_of_packer_docs" class="error invalid-feedback"></span>
                                                        <span id="error_type_signnature_of_packer_docs" class="error invalid-feedback"></span>
                                                        <span id="error_size_signnature_of_packer_docs" class="error invalid-feedback"></span>
                                                     </div>
                                                </div> 
                                              <p class="lab_form_note float-right mt-3"><i class="fa fa-info-circle"></i> File type: PDF, jpg &amp; max size upto 2 MB</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                       
                    <!-- Signature and Name of the Inspecting Officer -->     
                    <div class="card-header sub-card-header-firm"><h3 class="card-title">Signature and Name of the Inspecting Officer</h3></div>
                        <div class="form-horizontal">
                            <div class="form-horizontal">
                                <div class="card-body border">
                                    <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                           <label for="inputEmail3" class="col-sm col-form-label">Name of the Inspecting Officer<span class="cRed"> * </span></label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <div class="col-sm">
                                                <?php echo $this->Form->control('name_of_inspecting_officer', array('type'=>'text', 'id'=>'name_of_inspecting_officer','placeholder'=>'Enter Name of the Inspecting Officer', 'class'=>'form-control', 'label'=>false,'value'=>isset($section_form_details[0]['name_of_inspecting_officer'])?$section_form_details[0]['name_of_inspecting_officer']:"")); ?>
                                                 <span id="error_name_of_inspecting_officer" class="error invalid-feedback"></span>
                                            </div> 
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-horizontal">
                            <div class="form-horizontal">
                                <div class="card-body border">
                                    <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                           <label for="inputEmail3" class="col-sm col-form-label">Designation<span class="cRed"> * </span></label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <div class="col-sm">
                                                <?php echo $this->Form->control('designation_inspecting_officer', array('type'=>'text', 'id'=>'designation_inspecting_officer', 'class'=>'form-control','value '=>isset($section_form_details[0]['designation_inspecting_officer'])?$section_form_details[0]['designation_inspecting_officer']:"", 'placeholder'=>'Please Enter Designation', 'label'=>false)); ?>
                                                <span id="error_designation_inspecting_officer" class="error invalid-feedback"></span>
                                            </div> 
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-horizontal">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                    <label for="field3" class="col-sm col-form-label"><span><?php if ($current_level == 'level_2' && $application_mode == 'edit' ) { echo 'Signature'; } else { echo 'Signature'; } ?><span class="cRed"> *</span></label>

                                                    <span class="float-left"><?php if ($current_level == 'level_2' && $application_mode == 'edit' && empty($section_form_details[0]['signnature_of_inspecting_officer_docs'])) { echo 'Attach docs'; }else{ echo 'Attached docs'; } ?> :
                                                    <?php if (!empty($section_form_details[0]['signnature_of_inspecting_officer_docs'])) { ?>
                                                        <a id="signnature_of_inspecting_officer_docs_value" target="blank" href="<?php echo str_replace("D:/xampp/htdocs","",$section_form_details[0]['signnature_of_inspecting_officer_docs']); ?>"><?=$str2 = substr(array_values(array_slice((explode("/",$section_form_details[0]['signnature_of_inspecting_officer_docs'])), -1))[0],23);?></a>
                                                    <?php }else{ echo "No Document Provided" ;} ?>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                    <div class="custom-file col-sm">
                                                          <input type="file" name="signnature_of_inspecting_officer_docs" class="form-control" id="signnature_of_inspecting_officer_docs" multiple='multiple'>
                                                        <span id="error_signnature_of_inspecting_officer_docs" class="error invalid-feedback"></span>
                                                        <span id="error_type_signnature_of_inspecting_officer_docs" class="error invalid-feedback"></span>
                                                        <span id="error_size_signnature_of_inspecting_officer_docs" class="error invalid-feedback"></span>
                                                     </div>
                                                </div> 
                                              <p class="lab_form_note float-right mt-3"><i class="fa fa-info-circle"></i> File type: PDF, jpg &amp; max size upto 2 MB</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</section>
<?php

?>
<input type="hidden" id="final_submit_status_id" value="<?php echo $final_submit_status; ?>">
<input type="hidden" id="application_mode" value="<?php echo $_SESSION['application_mode']; ?>">
<input type="hidden" id="current_level" value="<?php echo $_SESSION['current_level']; ?>">
<input type="hidden" id="application_type_id" value="<?php echo $_SESSION['application_type']; ?>">
<input type="hidden" id="firm_type" value="<?php echo $firm_type; ?>">

<!-- allocated_record_rti -->

<!-- //firm type is use to validate form fields added by shankhpal on 25/05/023 -->
<?php 
echo $this->Html->css('RoutineInspection/routine_inspection_style');
echo $this->Html->script('routininspection/routin_inspection');
echo $this->Html->script('routininspection/rti_file_uploads_validation');
echo $this->Html->script('routininspection/rti_other_validation');

?>



