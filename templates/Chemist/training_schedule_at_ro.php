<!-- new file added by laxmi B. on 21-12-2022 -->
<?php  ?>
<div class="container site-page">

   <div class="row">
    <div class="col-lg-12 mx-auto text-center">
      <?php if($ral_reschedule_status == 'confirm' && empty($reschedule_status) && empty($is_training_scheduled_ro )){?>
      <p class="fontSize26"><b>Training Schedule At RO</b></p>
      <?php }else{ ?>
        <p class="fontSize26"><b>Training Reschedule At RO</b></p>
        <?php } ?>
       <!-- <hr/> -->
    </div>
  </div>
<?php  echo $this->Form->create(null, array( 'enctype'=>'multipart/form-data', 'id'=>'ro_shedule_letter','class'=>'form_name'));  ?>
 <div class="form-horizontal">
    <div class="card-body">
      <div class="row">
        <div class="col-md-12 row">
          <div class="col-md-2">
            <label for="field3"><span>RO First Name <span class="cRed">*</span></span></label>
          </div>
          <div class="col-md-4">
            <?php echo $this->Form->control('ro_first_name', array('type'=>'text', 'id'=>'rofirstname', 'escape'=>false, 'value'=>$ro_fname, 'placeholder'=>'Enter First Name', 'class'=>'cvOn cvReq cvAlphaNum form-control', 'maxlength'=>255, 'readonly'=>true, 'label'=>false)); ?>
            <div class="err_cv"></div>
          </div>
          <div class="col-md-2">
            <label for="field3"><span>Last Name <span class="cRed">*</span></span></label>
          </div>
          <div class="col-md-4">
            <?php echo $this->Form->control('ro_last_name', array('type'=>'text', 'id'=>'rolastname', 'escape'=>false, 'value'=>$ro_lname, 'placeholder'=>'Enter Last Name', 'class'=>'cvOn cvReq cvAlphaNum form-control', 'maxlength'=>255, 'readonly'=>true, 'label'=>false)); ?>
            <div class="err_cv"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="form-horizontal">
    <div class="card-body">
      <div class="row">
        <div class="col-md-12 row">
          <div class="col-md-2">
            <label for="field3"><span>Chemist First Name <span class="cRed">*</span></span></label>
          </div>
          <div class="col-md-4">
            <?php echo $this->Form->control('chemist_first_name', array('type'=>'text', 'id'=>'chemistfirstname', 'escape'=>false, 'value'=>$chemist_fname, 'placeholder'=>'Enter First Name', 'class'=>'cvOn cvReq cvAlphaNum form-control', 'maxlength'=>255, 'readonly'=>true, 'label'=>false)); ?>
            <div class="err_cv"></div>
          </div>
          <div class="col-md-2">
            <label for="field3"><span>Chemist Last Name <span class="cRed">*</span></span></label>
          </div>
          <div class="col-md-4">
            <?php echo $this->Form->control('chemist_last_name', array('type'=>'text', 'id'=>'chemistlastname', 'escape'=>false, 'value'=>$chemist_lname, 'placeholder'=>'Enter Last Name', 'class'=>'cvOn cvReq cvAlphaNum form-control', 'maxlength'=>255, 'readonly'=>true, 'label'=>false)); ?>
            <div class="err_cv"></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="form-horizontal">
    <div class="card-body">
      <div class="row">
        <div class="col-md-12 row">
          <div class="col-md-2">
            <label for="field3"><span>Chemist Id <span class="cRed">*</span></span></label>
          </div>
          <div class="col-md-4">
            <?php echo $this->Form->control('chemist_id', array('type'=>'text', 'id'=>'chemistId', 'escape'=>false, 'value'=>$chemist_id, 'placeholder'=>'Enter First Name', 'class'=>'cvOn cvReq cvAlphaNum form-control', 'maxlength'=>255, 'readonly'=>true, 'label'=>false)); ?>
            <div class="err_cv"></div>
          </div>

           <div class="col-md-2">
            <label for="field3"><span>Schedule Training From Dates <span class="cRed">*</span></span></label>
          </div>
          
          <div class="col-md-4">
          <?php if($ral_reschedule_status == 'confirm' && empty($reschedule_status) && empty($is_training_scheduled_ro )){?>
       
          <?php echo $this->Form->control('shedule_from',['class' => 'form-control datepicker-here', 'label' => false,'id' => 'sheduleFrom', 'type' => 'Text']); ?>
          <?php } else{ ?>
            <?php echo $this->Form->control('shedule_from',['class' => 'form-control datepicker-here', 'label' => false,'id' => 'sheduleFrom', 'type' => 'Text', 'value'=>$ro_schedule_from]); ?>
            <?php } ?>
             <div class="err_cv_shedule_from text-red"></div>
          </div>

        </div>
      </div>
    </div>
  </div>
<div class="form-horizontal">
    <div class="card-body">
      <div class="row">
        <div class="col-md-12 row">
           <div class="col-md-2">
            <label for="field3"><span>Schedule Training To Dates <span class="cRed">*</span></span></label>
          </div>
            <div class="col-md-4">
            <?php if($ral_reschedule_status == 'confirm' && empty($reschedule_status) && empty($is_training_scheduled_ro )){?>
          <?php echo $this->Form->control('shedule_to',['class' => 'form-control datepicker-here', 'label' => false,'id' => 'sheduleTo', 'type' => 'Text']); ?>
          <?php } else{ ?> 
            <?php echo $this->Form->control('shedule_to',['class' => 'form-control datepicker-here', 'label' => false,'id' => 'sheduleTo', 'type' => 'Text', 'value'=>$ro_schedule_to]); ?>
            <?php } ?>
        </div>
             <div class="err_cv_shedule_to text-red"></div>

           
            <?php if($ral_reschedule_status == 'confirm' && empty($reschedule_status) && !empty($is_training_scheduled_ro )){?>
              <div class="col-md-2">
            <label for="field3"><span>Remark</span></label>
            </div>
              <div class="col-md-4">
              <?php echo $this->Form->control('reshedule_remark', array('type'=>'textarea', 'id'=>'remark', 'escape'=>false,  'placeholder'=>'Enter Remark', 'class'=>'cvOn cvReq cvAlphaNum form-control',   'label'=>false)); ?>
            </div>
              <?php } ?>
            <div class="col-md-2"></div>
           
            <div class="col-md-2">
            <?php if($ral_reschedule_status == 'confirm' && empty($reschedule_status) && empty($is_training_scheduled_ro )){?>
            <button type="submit" value="submit" id="submit" class="form-control btn btn-success">Schedule
           </button>
           <?php }else{?>
            <button type="submit" value="submit" id="submit" class="form-control btn btn-success">Confirm Dates
           </button>
           <?php } ?>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <?php  echo $this->Form->end();  ?>
</div>

<?php echo $this->Html->script('chemist/forward_applicationto_ral');?>
