/*function img_pathUrl(input){
    $('#profile_photo_prev')[0].src = (window.URL ? URL : webkitURL).createObjectURL(input.files[0]);
}

$("#profile_photo").change(function(){
    img_pathUrl(this);
});*/

/*$(document).ready(()=>{
    $('#profile_photo').change(function(){
      const file = this.files[0];
      console.log(file);
      if (file){
        let reader = new FileReader();
        reader.onload = function(event){
          console.log(event.target.result);
          $('#profile_photo_prev').attr('src', event.target.result);
        }
        reader.readAsDataURL(file);
      }
    });
  });*/

  /*$(document).ready(function() {
    // When the file input value changes (user selects an image)
    $("#profile_photo").change(function() {
      // Get the selected file from the input element
      const file = this.files[0];

      if (file) {
        // Create a FileReader object to read the selected file
        const reader = new FileReader();

        // When the FileReader finishes loading the file
        reader.onload = function(e) {
          // Create a new image element to display the preview
          const img = new Image();
          img.src = e.target.result;
          img.alt = "Preview Image";
          img.style.maxWidth = "100px";
          img.style.maxHeight = "100px";
          // Append the image element to the preview container
          $("#profile_photo_prev").replaceWith(img);
        };

        // Read the selected file as a data URL (base64 encoded)
        reader.readAsDataURL(file);
      } else {
        // If no file was selected, hide the image tag
        $("#profile_photo_prev").hide();
      }
    });
  });*/

  $(document).ready(function() {
    // When the file input value changes (user selects an image)
    // $("#profile_photo").change(function() {
    //   // Get the selected file from the input element
    //   const file = this.files[0];

    //   if (file) {
    //     // Create a blob URL for the selected file
    //     const blobURL = URL.createObjectURL(file);

    //     // Create a new image element to display the preview
    //     const img = new Image();
    //     img.src = blobURL;
    //     img.alt = "Preview Image";
    //     img.style.maxWidth = "300px";
    //     img.style.maxHeight = "300px";

    //     // Remove the previous preview image (if any)
    //     $("#profile_photo_prev").remove();

    //     // Append the image element to the preview container
    //     $("#profile_photo").after(img);
    //   } else {
    //     // If no file was selected, remove the preview image
    //     $("#profile_photo_prev").remove();
    //   }
    // });



    // onchange of profile and sign save in db and preview image added by laxmi on 06-09-2023
    $('#profile_photo_prev').hide();
    $('input[type="file"][name="profile_photo"]').change(function(){
      var photo = $(this).val();
      var file = this.files[0];
      var formData = new FormData();
      formData.append('file', file);
        $.ajax({
          method: 'POST',
          url : '../chemist/chemist_photo_preview/',
          data : formData,
          mimeType: "multipart/form-data",
          processData: false,
          contentType: false,
          cache: false,
          beforeSend: function (xhr){
              xhr.setRequestHeader('X-CSRF-Token', $('[name="_csrfToken"]').val());
          }, 
          success: function(data){
            $('#profile_photo_prev').show();
            var profileImg = $('.profileImg').attr('src');
            if(profileImg == '' || profileImg == undefined){
              $('.profileImg').hide();
            }
            
            let object = JSON.parse(data);
            $('#profile_photo_prev').attr('src', object.profile_photo);
            $('#profile_photo_prev').attr('class', object.chemist_id);
           

          },
        });
      });

        // onchange of profile and sign save in db and preview image added by laxmi on 06-09-2023
    $('#profile_sign_prev').hide();
    $('input[type="file"][name="signature_photo"]').change(function(){
      var photo = $(this).val();
      var file = this.files[0];
      var formData = new FormData();
      formData.append('file', file);
        $.ajax({
          method: 'POST',
          url : '../chemist/chemist_sign_preview/',
          data : formData,
          mimeType: "multipart/form-data",
          processData: false,
          contentType: false,
          cache: false,
          beforeSend: function (xhr){
              xhr.setRequestHeader('X-CSRF-Token', $('[name="_csrfToken"]').val());
          }, 
          success: function(data){
            $('#profile_sign_prev').show();
            var sign = $('.profilesign').attr('src');
            if(sign == '' || sign == undefined){
              $('.profilesign').hide();
            }
            
            let object = JSON.parse(data);
            $('#profile_sign_prev').attr('src', object.sign);
            $('#profile_sign_prev').attr('class', object.chemist_id);
           

          },
        });
      }); 
   

    
  });