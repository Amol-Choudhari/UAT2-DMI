										<?php ?>
                    <div class="table-format">
											<table id="sample_table" class="table table-bordered table-striped table-responsive">
													<tr>
														<th>S.No</th>
														<th>Commodity</th>
														<th>Pack Size</th>
                            <th>Lot No</th>
														<th>Date of Packing</th>
                            <th>Best Before</th>
														<th>Replica Sl. No</th>
														<th>Action</th>
													</tr>
                           <div id="sample_details_each_row">
                            	<?php 
								            $i=1;  
                             
                              foreach($section_form_details[1] as $sample_detail){  
                                
                                ?>
                              <tr>
                                <td><?php echo $i; ?></td>
                                <td><?php echo isset($section_form_details[3][$sample_detail['commodity_name']])?$section_form_details[3][$sample_detail['commodity_name']]:"-"; ?></td>
                                <td><?php echo $sample_detail['pack_size'];?></td>
                                <td><?php echo $sample_detail['lot_no'];?></td>
                                <td><?php echo $sample_detail['date_of_packing'];?></td>
                                <td><?php echo $sample_detail['best_before'];?></td>
                                <td><?php echo $sample_detail['replica_si_no'];?></td>
                                <td>                                 
                                  <a href="#" class="edit_sample_id glyphicon glyphicon-edit machine_edit" id="<?php echo $sample_detail['id']; ?>" ></a> |
								                  <a href="#" class="delete_sample_id glyphicon glyphicon-remove-sign machine_delete" id="<?php echo $sample_detail['id']; ?>" ></a>
                                </td>
                              </tr>
                               <?php $i=$i+1; }?>
                           </div>
                          <div id="error_sample" class="text-red float-right text-sm"></div>
													<!-- for edit sample details -->
 
                          <?php if ($this->getRequest()->getSession()->read('edit_sample_id') != null) {  ?>
                             <tr>
                               <td></td>
                               <td><?php echo $this->Form->control('commodity_name', array('type'=>'select', 'empty'=>'Select Commodity', 'id'=>'commodity_name','options'=>$section_form_details[3],'value'=>isset($find_sample_details['commodity_name'])?$find_sample_details['commodity_name']:"", 'label'=>false, 'class'=>'form-control wd120')); ?></td>
	                              <span id="" class="error invalid-feedback"></span>
                                <td>
                                    <?php echo $this->Form->control('pack_size', array('type'=>'text', 'value'=>isset($find_sample_details['pack_size'])?$find_sample_details['pack_size']:"", 'escape'=>false,  'label'=>false, 'id'=>'pack_size', 'class'=>'form-control input-field')); ?>
																		<span id="" class="error invalid-feedback"></span>
                                </td>
                                <td>
                                    <?php echo $this->Form->control('lot_no', array('type'=>'text', 'value'=>isset($find_sample_details['lot_no'])?$find_sample_details['lot_no']:"", 'escape'=>false,  'label'=>false, 'id'=>'lot_no', 'class'=>'form-control input-field')); ?>
																		<span id="" class="error invalid-feedback"></span>
                                </td>
                                <td>
                                    <?php echo $this->Form->control('date_of_packing', array('type'=>'text', 'value'=>isset($find_sample_details['date_of_packing'])?$find_sample_details['date_of_packing']:"",'placeholder'=>'Enter DD/MM/YYYY', 'escape'=>false,  'label'=>false, 'id'=>'date_of_packing', 'class'=>'form-control input-field')); ?>
																		<span id="" class="error invalid-feedback"></span>
                                </td>
                                <td>
                                    <?php echo $this->Form->control('best_before', array('type'=>'text', 'value'=>isset($find_sample_details['best_before'])?$find_sample_details['best_before']:"", 'escape'=>false,  'label'=>false, 'id'=>'best_before', 'class'=>'form-control input-field')); ?>
																		<span id="" class="error invalid-feedback"></span>
                                </td>
                                <td>
                                    <?php echo $this->Form->control('replica_si_no', array('type'=>'text', 'value'=>isset($find_sample_details['replica_si_no'])?$find_sample_details['replica_si_no']:"", 'escape'=>false,  'label'=>false, 'id'=>'replica_si_no', 'class'=>'form-control input-field')); ?>
																		<span id="" class="error invalid-feedback"></span>
                                </td>
                                
                                <td>
                                 <div class="form-buttons"><a href="#" id="save_sample_details" class="btn btn-info btn-sm">Save</a></div>
                                </td>
                             </tr>
                             <!-- To add new sample details -->
                          <?php }else { ?>
                              <div id="add_new_row">
                                	<tr>
                                      <td></td>
                                      <td>
                                        <?php 
                                           echo $this->Form->control('commodity_name', array('type'=>'select', 'empty'=>'Select Commodity', 'id'=>'commodity_name','options'=>isset($section_form_details[3])?$section_form_details[3]:$section_form_details[2], 'label'=>false, 'class'=>'form-control wd120')); ?>
																		      <span id="error_commodity_name_addmore" class="error invalid-feedback"></span>
                                      </td>
                                      <td>
                                          <?php echo $this->Form->control('pack_size', array('type'=>'text', 'escape'=>false,  'label'=>false, 'id'=>'pack_size','placeholder'=>'Enter Pack Size','value'=>'', 'class'=>'form-control wd100')); ?>
																		      <span id="error_pack_size" class="error invalid-feedback"></span>
                                      </td>
                                      <td>
                                          <?php echo $this->Form->control('lot_no', array('type'=>'text', 'escape'=>false,  'label'=>false, 'id'=>'lot_no','value'=>'','placeholder'=>'Enter Lot No.', 'class'=>'form-control wd100')); ?>
																		      <span id="error_lot_no" class="error invalid-feedback"></span>
                                      </td>
                                      <td>
                                          <?php echo $this->Form->control('date_of_packing', array('type'=>'text', 'escape'=>false,'value'=>'',  'label'=>false, 'placeholder'=>'Enter DD/MM/YYYY','id'=>'date_of_packing', 'class'=>'form-control wd100')); ?>
																		      <span id="error_date_of_packing" class="error invalid-feedback"></span>
                                      </td>
                                      <td>
                                          <?php echo $this->Form->control('best_before', array('type'=>'text', 'escape'=>false,'value'=>'',  'label'=>false,'placeholder'=>'Enter Best Before.', 'id'=>'best_before', 'class'=>'form-control wd100')); ?>
																		      <span id="error_best_before" class="error invalid-feedback"></span>
                                      </td>
                                      <td>
                                          <?php echo $this->Form->control('replica_si_no', array('type'=>'text', 'escape'=>false,'value'=>'',  'label'=>false,'placeholder'=>'Enter Replica Si No.', 'id'=>'replica_si_no', 'class'=>'form-control wd100')); ?>
																		      <span id="error_replica_si_no" class="error invalid-feedback"></span>
                                      </td>
                                      <td>
                                          <div class="form-buttons"><a href="#" id="add_sample_details" class='table_record_add_btn btn btn-info btn-sm'><i class="fa fa-plus"></i> Add</a></div>
                                      </td>
                                  </tr>
                              </div>
                          <?php } ?>  
                          </div> 
											</table>
										</div>
										
                    <?php //echo $this->Html->script('routininspection/routin_inspection'); ?>
								
				
					


