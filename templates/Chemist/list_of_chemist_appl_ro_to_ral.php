<div class="container">
  <div class="col-lg-12 mx-auto text-center">
      <p class="fontSize26"><b>Chemist Forwarded to RAL for Training</b></p>
       <hr/>
    </div>
<div class="row">
 <table class="table table-bordered">
  <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">Chemist ID</th>
      <th scope="col">Chemist Name</th>
      <th scope="col">RO Office</th>
      <th scope="col">RAL/CAL Office</th>
      <th scope="col">Forwarded On</th>
      <th scope="col">Training End On</th>
      <th scope="col">Action</th>
      
    </tr>
  </thead>
  <tbody>
    
      <?php $i = 0;  
      if(!empty($listOfChemistApp)){ 
      foreach ($listOfChemistApp as $key => $list) {
       $shedule_to = date('d-m-Y', strtotime(str_replace('/','.', $list['shedule_to'])));
       $forwarded = date('d-m-Y', strtotime(str_replace('/','.', $list['created'])));
      ?>

      	<tr>
      <th scope="row"><?php echo $i+1; ?></th>
      	 <td><?php echo $list['chemist_id'];?></td>
      	 <td><?php echo $list['chemist_first_name']."&nbsp".$list['chemist_last_name'];?></td>
         <td><?php echo $ro_office[$i]; ?></td>
      	 <td><?php echo $ral_offices[$i] ;?></td>
      	 <td><?php echo $forwarded;?></td>
         <td><?php echo $shedule_to;?></td>
         <td><?php if(!empty($ral_schedule_pdf[$i])) { ?> <a href="<?php echo $ral_schedule_pdf[$i] ;?>" target="_blank" type="application/pdf" rel="alternate">View Letter</a> | <?php }?> <a href="<?php echo '../scrutiny/form_scrutiny_fetch_id/'.$chemisttblId[$i]['id'].'/view/'.  $list['appliaction_type'];?>">View Application</a></td> 
     </tr>
     <?php $i++; 
   } 

   }?>
    
  </tbody>
</table>	
</div>
	
</div>