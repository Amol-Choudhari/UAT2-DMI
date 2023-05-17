//Create new file for reject chemist application at ro side  added by laxmi Bhadade on 16-05-2023 for chemist_training module
alert(123);
$('document').ready(function(){
    alert(123);
var span = document.getElementsByClassName("close")[0];
var reject_id = document.getElementsByClassName('rejectModel');
alert($reject_id); 
$('.rejectModel').click(function(){
   console.log($(this).val('id'));
    $('#myModal').show();
});
$('.close').click(function(){
    $('#myModal').hide();
});



});