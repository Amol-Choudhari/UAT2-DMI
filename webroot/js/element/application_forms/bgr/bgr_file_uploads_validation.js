$(document).ready(function () {
  $("#other_upload_docs").change(function () {
    file_browse_onclick("other_upload_docs");
  });
});

function file_browse_onclick(field_id) {
  var selected_file = $("#".concat(field_id)).val();
  var ext_type_array = ["jpg", "pdf"];

  var get_file_size = $("#".concat(field_id))[0].files[0].size;
  var get_file_ext = selected_file.split(".");

  var value_return = "true";

  get_file_ext = get_file_ext[get_file_ext.length - 1].toLowerCase();

  if (get_file_size > 2097152) {
    $("#error_size_".concat(field_id))
      .show()
      .text("Please select file below 2mb");
    setTimeout(function () {
      $("#error_type_".concat(field_id)).fadeOut();
    }, 8000);
    $("#error_size_".concat(field_id)).addClass("is-invalid");
    $("#".concat(field_id)).click(function () {
      $("#error_size_".concat(field_id)).hide().text;
      $("#".concat(field_id)).removeClass("is-invalid");
    });
    $("#".concat(field_id)).val("");
    value_return = "false";
  }

  if (ext_type_array.lastIndexOf(get_file_ext) == -1) {
    $("#error_type_".concat(field_id))
      .show()
      .text("Please select file of jpg, pdf type only");
    setTimeout(function () {
      $("#error_type_".concat(field_id)).fadeOut();
    }, 8000);
    $("#error_type_".concat(field_id)).addClass("is-invalid");
    $("#".concat(field_id)).click(function () {
      $("#error_type_".concat(field_id)).hide().text;
      $("#".concat(field_id)).removeClass("is-invalid");
    });
    $("#".concat(field_id)).val("");
    value_return = "false";
  }

  if (value_return == "false") {
    return false;
  } else {
    exit();
  }
}
