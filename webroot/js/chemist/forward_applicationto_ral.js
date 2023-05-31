// file created by laxmi on 23-12-22
$(document).ready(function(){
  
  //datepicker added by laxmi on 28-12-2022
  // The Calender
  $('#sheduleFrom').datepicker({
    setDate: new Date(),
    autoclose: true,
    startDate:'+0d',
    format: 'dd/mm/yyyy',
  })
  .on('changeDate', function (selected) {
    startDate = new Date(selected.date.valueOf());
    startDate.setDate(startDate.getDate(new Date(selected.date.valueOf())));
   
    $('#sheduleTo').datepicker('setStartDate', startDate);
});


  $('#sheduleTo').datepicker({
    
    autoclose: true,
    setDate: new Date(),
    startDate:'+0d',
    format: 'dd/mm/yyyy',
    
  })
  .on('changeDate', function (selected) { 
    FromEndDate = new Date(selected.date.valueOf());
    FromEndDate.setDate(FromEndDate.getDate(new Date(selected.date.valueOf())));
    $('#sheduleFrom').datepicker('setEndDate', FromEndDate);
});



	$('#btnSubmit').on('click', function() {
    var roOffice = $('#roOffice').val();
     var scheduleDateFrom = $('#sheduleFrom').val();
      var scheduleDateTo= $('#sheduleTo').val();
     // alert(roOffice);
    
    if(roOffice == ""  || roOffice == null){
     
     $('.err_cv_ro_office').html("Please select RAL Office");
      return false;
     
     }
      
		
    if(scheduleDateFrom == ""){
     $('.err_cv_shedule_from').html("Please select  From date");
     return false;
    }
    
    if(scheduleDateTo == ""){
     $('.err_cv_shedule_to').html("Please select To date");
     return false;
    }

    return true;
});
});

jQuery(document).ready(function($) {
  $(function() {
  $("#trainingCompleted").on("click",function() {
    if($('#trainingCompleted').is(':checked')){

      $('#submitbtn').show();
    }else{
      $('#submitbtn').hide();
    }
    
  });
});

});


// $(document).ready( function(){
// $('#sheduleTo').focusin(function(){
// var scheduleF = $('#sheduleFrom').val();
// var date = new Date();
// var toDate =  date.getMonth();
// alert(toDate);
// });
// });