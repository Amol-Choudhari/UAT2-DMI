//Create new file for reject chemist application at ro side  added by laxmi Bhadade on 16-05-2023 for chemist_training module

$('document').ready(function(){
   
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
});


//After click on reject button from popup take a parameter as input and pass it to the contrller to save

$('.modal #rejectBtn').click( function(){
    debugger;
    alert(123);
var app_type = $('#application_type').attr('value');
var chemist_id = $('#application_id').attr('val');
var remark     = $('#remark').attr('value');
console.log($(this).attr('value'));
});


});