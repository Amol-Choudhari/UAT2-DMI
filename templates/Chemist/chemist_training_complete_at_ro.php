<!-- new file added for view of training completed form by laxmi B. on 21-12-2022 -->
<div class="container site-page">

   <div class="row">
    <div class="col-lg-12 mx-auto text-center">
      <p class="fontSize26"><b>Training Completed At RO</b></p>
       <!-- <hr/> -->
    </div>
  </div>
<?php  echo $this->Form->create(null, array( 'enctype'=>'multipart/form-data', 'id'=>'ro_toral','class'=>'form_name'));  ?>
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
            <?php echo $this->Form->control('ro_last_name', array('type'=>'text', 'id'=>'rolastname', 'escape'=>false, 'value'=>$ro_last_name, 'placeholder'=>'Enter Last Name', 'class'=>'cvOn cvReq cvAlphaNum form-control', 'maxlength'=>255, 'readonly'=>true, 'label'=>false)); ?>
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
            <label for="field3"><span>Remark </span></label>
          </div>
          <div class="col-md-4">
            <?php echo $this->Form->control('remark', array('type'=>'textarea', 'id'=>'remark', 'escape'=>false,  'placeholder'=>'Enter Remark', 'class'=>'cvOn cvReq cvAlphaNum form-control',   'label'=>false)); ?>
            <div class="err_cv_remark1 text-red1"></div>
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
            <label for="field3"><span>Upload </span></label>
          </div>
           <div class="col-md-4">
             <?php echo $this->Form->control('document', array('type'=>'file', 'id'=>'document', 'escape'=>false, 'value'=>'yes','label'=>false)); ?>
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
            <label for="field3"><span>Is Training Completed <span class="cRed">*</span></span></label>
           </div>
            <div class="col-md-4"> <?php echo $this->Form->control('training_completed', array('type'=>'checkbox', 'id'=>'trainingCompleted', 'escape'=>false, 'checked' =>false,'label'=>false)); ?>
            
          </div>
          <div class="col-md-2"></div>
          <div class="col-md-2">
            <button type="submit" value="submit" id="submitbtn" class="form-control btn btn-success">Submit
           </button>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php  echo $this->Form->end();  ?>
</div>

<?php echo $this->Html->script('chemist/forward_applicationto_ral');?>
