//Create new file for reject chemist application at ro side  added by laxmi Bhadade on 16-05-2023 for chemist_training module

$('document').ready(function(){
   $('.ral_to_ro').DataTable();
var span = document.getElementsByClassName("close")[0];

var reject_id = document.getElementsByClassName("rejectModel");

var id = $(reject_id).attr('id');

$('.rejectModel').click(function(){
  var rejectID = $(this).attr('id');
  $('#'+rejectID).click(function(){
  var rejectVal = $(this).attr('value');
  var appl_type = $(this).attr('appl_type');
  $('.chemistId').val(rejectVal);
  $('.applicationType').val(appl_type);
    $('.modal').show();
});
});
$('.close').click(function(){
    $('.modal').hide();
    $('#remark').val('');
    $('.errorClass').hide();
});


//After click on reject button from popup take a parameter as input and pass it to the contrller to save
$('.reject').click(function(e){
    e.preventDefault(); 
$('.errorClass').hide();
});
$('.modal #rejectBtn').click( function(){

    
var app_type = $('.applicationType').val();
var chemist_id = $('.chemistId').val();
var remark     = $('.reject').val();


if(remark == undefined || remark ==''){
    $('.errorClass').show();
    $('.errorClass').text('Please add application reject reason.');
}else{
$.ajax({
        type: "POST",
        url: '../chemist/chemistApplicationReject/',
        data: {appl_type:app_type, chemist_id:chemist_id, remark:remark},
        cache: false,
        beforeSend: function (xhr){
            xhr.setRequestHeader('X-CSRF-Token', $('[name="_csrfToken"]').val());
        }, 
        success: function(data)
        {
           
            $.alert({
                content:"Application Rejected Successfully.",
                onClose: function(){
                location.reload();
                }
                
            }); 
            $('#remark').val('');
        },
        error: function () {
            $.alert({
                content:"Something went wrong, Please try again.",
                onClose: function(){
                location.reload();
                }
                
            }); 
            $('#remark').val('');
           }
     });
   }
  });


  // for confirm button click automatacally reschedule button click

  $('#triningDatesConfirm').click(function(e){
       e.preventDefault();
      window.localStorage.setItem('confirmClick','yes');
      $('#RescheduleTrainingDates')[0].click();
  });

});