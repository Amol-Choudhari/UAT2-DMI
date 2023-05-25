// file created by laxmi on 23-12-22
$(document).ready(function(){

  //datepicker added by laxmi on 28-12-2022
  // The Calender
  $('#sheduleTo').datepicker({
    
    autoclose: true,
    setDate: new Date(),
    startDate:'+0d',
    format: 'dd/mm/yyyy'
  });

  $('#sheduleFrom').datepicker({
    setDate: new Date(),
    autoclose: true,
    startDate:'+0d',
    format: 'dd/mm/yyyy'
  });

	$('#submitbtn').on('click', function() {
    var roOffice = $('#roOffice').val();
    var remark = $('#remark').val();
     var scheduleDateFrom = $('#sheduleFrom').val();
      var scheduleDateTo= $('#sheduleTo').val();
    var value_return = 'true';
    if(roOffice == ""){
     $('.err_cv_ro_office').html("Please select RO Office");
      value_return = 'false';
     }
      if(remark == ""){
     $('.err_cv_remark').html("Please enter any Remark");
       value_return = 'false';
		}
    if(scheduleDateFrom == ""){
     $('.err_cv_shedule_from').html("Please select schedule Training From date");
       value_return = 'false';
    }
    
    if(scheduleDateTo == ""){
     $('.err_cv_shedule_to').html("Please select schedule Training To date");
       value_return = 'false';
    }
    return;
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