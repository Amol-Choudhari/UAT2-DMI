//  Comment:This file updated as per change and suggestions for UAT module after test run
// 	Reason: updated as per change and suggestions for UAT module after test run
// 	Name of person : shankhpal shende
// 	Date: 11-05-2023 - 16-05-2023
// */ -->
$(document).ready(function () {
  // the following logic is check the date of present inspection is less than to last inspection
  // added by shankhpal on 20/06/2023
  var date_last_inspection = $("#date_last_inspection").val();

  if (date_last_inspection !== "") {
    $("#date_last_inspection").addClass("readOnly");
  }
  $("#date_p_inspection").change(function () {
    var date_p_inspection = $("#date_p_inspection").val();

    // Check if both dates are not empty
    if (date_last_inspection !== "" && date_p_inspection !== "") {
      // Parse the dates using Moment.js
      var moment_last_inspection = moment(date_last_inspection, "DD/MM/YYYY");
      var moment_p_inspection = moment(date_p_inspection, "DD/MM/YYYY");

      // Compare the last inspection date with the present inspection date
      if (moment_p_inspection.isBefore(moment_last_inspection)) {
        alert(
          "Present inspection date should be greater than or equal to the last inspection date."
        );
        $("#date_p_inspection").val(""); // Clear the selected date
      }
    }
  });

  var application_mode = $("#application_mode").val();
  var current_level = $("#current_level").val();
  // alert(current_level);
  if (application_mode == "view") {
    $("#section_form_id :input").prop("disabled", false);
    $(document).ready(function () {
      $("#form_inner_main :input").prop("disabled", true);
      $("#form_inner_main :input[type='radio']").prop("disabled", true);
      $("#form_inner_main :input[type='select']").prop("disabled", true);
      $("#form_inner_main :input[type='submit']").prop("disabled", true);
      $("#form_inner_main :input[type='reset']").prop("disabled", true);
      $("#form_inner_main :input[type='button']").prop("disabled", true);
      $("#form_inner_main :input[type='submit']").hide();
      $(".glyphicon-edit").css("display", "none");
      $(".glyphicon-remove-sign").css("display", "none");
    });
  }

  var form_section_id = $("#form_section_id").val();

  add_function();
  edit_function();
  delete_function();
  save_function();

  function add_function() {
    $("#add_sample_details").click(function (e) {
      e.preventDefault();
      var commodity_name = $("#commodity_name").val();
      var pack_size = $("#pack_size").val();
      var lot_no = $("#lot_no").val();
      var date_of_packing = $("#date_of_packing").val();
      var best_before = $("#best_before").val();
      var replica_si_no = $("#replica_si_no").val();
      var form_data = $("#" + form_section_id).serializeArray();

      form_data.push(
        { name: "commodity_name", value: commodity_name },
        { name: "pack_size", value: pack_size },
        { name: "lot_no", value: lot_no },
        { name: "date_of_packing", value: date_of_packing },
        { name: "best_before", value: best_before },
        { name: "replica_si_no", value: replica_si_no }
      );

      if (sample_table_validation() == true) {
        $.ajax({
          type: "POST",
          url: "../AjaxFunctions/add_sample_details",
          data: form_data,
          beforeSend: function (xhr) {
            // Add this line
            xhr.setRequestHeader(
              "X-CSRF-Token",
              $('[name="_csrfToken"]').val()
            );
          },
          success: function (response) {
            $("#sample_table").html(response);
            $("#sample_table :input[type='text']").val("");
            $('select[name="commodity_name"]').val("");
            add_function();
            edit_function();
            delete_function();
            save_function();
            $("#date_of_packing").datepicker({
              format: "dd/mm/yyyy",
              autoclose: true,
            });
          },
        });
      }
    });
  }
  function edit_function() {
    $(".edit_sample_id").click(function (e) {
      e.preventDefault();
      var sample_id = $(this).attr("id");
      var form_data = $("#" + form_section_id).serializeArray();
      form_data.push({ name: "edit_sample_id", value: sample_id });

      $.ajax({
        type: "POST",
        url: "../AjaxFunctions/edit_sample_id",
        data: form_data,
        beforeSend: function (xhr) {
          // Add this line
          xhr.setRequestHeader("X-CSRF-Token", $('[name="_csrfToken"]').val());
        },
        success: function (response) {
          $("#sample_table").html(response);
          add_function();
          edit_function();
          delete_function();
          save_function();
          $("#date_of_packing").datepicker({
            format: "dd/mm/yyyy",
            autoclose: true,
          });
        },
      });
    });
  }
  function delete_function() {
    $(".delete_sample_id").click(function (e) {
      e.preventDefault();

      var sample_id = $(this).attr("id");
      var form_data = $("#" + form_section_id).serializeArray();

      form_data.push({ name: "delete_sample_id", value: sample_id });

      $.ajax({
        type: "POST",
        url: "../AjaxFunctions/delete_sample_id",
        data: form_data,
        beforeSend: function (xhr) {
          // Add this line
          xhr.setRequestHeader("X-CSRF-Token", $('[name="_csrfToken"]').val());
        },
        success: function (response) {
          $("#sample_table").html(response);

          add_function();
          edit_function();
          delete_function();
          save_function();
          $("#date_of_packing").datepicker({
            format: "dd/mm/yyyy",
            autoclose: true,
          });
        },
      });
    });
  }
  // SAVE FUNCTION
  // Description : this is used to on the click of SAVE button
  // @AUTHOR : AKASH THAKRE
  // DATE : 24-03-2022 (M)

  function save_function() {
    $("#save_sample_details").click(function (e) {
      e.preventDefault();

      var edit_sample_id = "";
      var save_sample_id = $(this).attr("id");
      var commodity_name = $("#commodity_name").val();
      var pack_size = $("#pack_size").val();
      var lot_no = $("#lot_no").val();
      var date_of_packing = $("#date_of_packing").val();
      var best_before = $("#best_before").val();
      var replica_si_no = $("#replica_si_no").val();
      var form_data = $("#" + form_section_id).serializeArray();

      form_data.push(
        { name: "save_sample_id", value: save_sample_id },
        { name: "commodity_name", value: commodity_name },
        { name: "pack_size", value: pack_size },
        { name: "lot_no", value: lot_no },
        { name: "date_of_packing", value: date_of_packing },
        { name: "best_before", value: best_before },
        { name: "replica_si_no", value: replica_si_no },
        { name: "edit_sample_id", value: edit_sample_id }
      );

      if (sample_table_validation() == true) {
        $.ajax({
          type: "POST",
          url: "../AjaxFunctions/edit_sample_id",
          data: form_data,
          beforeSend: function (xhr) {
            // Add this line
            xhr.setRequestHeader(
              "X-CSRF-Token",
              $('[name="_csrfToken"]').val()
            );
          },
          success: function (response) {
            $("#sample_table").html(response);
            $("#sample_table :input[type='text']").val("");
            $('select[name="commodity_name"]').val("");

            add_function();
            edit_function();
            delete_function();
            save_function();
            $("#date_of_packing").datepicker({
              format: "dd/mm/yyyy",
              autoclose: true,
            });
          },
        });
      }
    });
  }

  var minLength = 5;
  var maxLength = 50;

  // Added for max length of input box as per change request
  // added by : shankhpal
  // on 16/05/2023
  $("#shortcomings_noticed").on("keyup", function () {
    var char = $(this).val();
    var charLength = $(this).val().length;
    if (charLength < minLength) {
      $("#error_shortcomings_noticed")
        .show()
        .text("Length is short, minimum " + minLength + " required.")
        .css("color", "blue");
      setTimeout(function () {
        $("#error_shortcomings_noticed").fadeOut();
      }, 8000);
    } else if (charLength > maxLength) {
      $("#error_shortcomings_noticed")
        .show()
        .text("Length is not valid, maximum " + maxLength + " allowed.")
        .css("color", "blue");
      setTimeout(function () {
        $("#error_shortcomings_noticed").fadeOut();
      }, 8000);
      $(this).val(char.substring(0, maxLength));
    } else {
      $("#error_shortcomings_noticed")
        .show()
        .text("Length is valid")
        .css("color", "blue");
      setTimeout(function () {
        $("#error_shortcomings_noticed").fadeOut();
      }, 8000);
    }
  });
});

function sample_table_validation(e) {
  // variable declaration for add more table
  var commodity_name = $("#commodity_name").val();
  var pack_size = $("#pack_size").val();
  var lot_no = $("#lot_no").val();
  var date_of_packing = $("#date_of_packing").val();
  var best_before = $("#best_before").val();
  var replica_si_no = $("#replica_si_no").val();

  var value_return = "true";

  if (commodity_name == "") {
    $("#error_commodity_name_addmore")
      .show()
      .text("Please Enter Commodity Name");
    setTimeout(function () {
      $("#error_commodity_name_addmore").fadeOut();
    }, 8000);
    $("#commodity_name").addClass("is-invalid");
    $("#commodity_name").click(function () {
      $("#error_commodity_name_addmore").hide().text;
      $("#commodity_name").removeClass("is-invalid");
    });
    value_return = "false";
  }
  // updated error message by shankhpal on 24/05/2023
  if (best_before == "") {
    $("#error_best_before").show().text("Best Before cannot be blank");
    setTimeout(function () {
      $("#error_best_before").fadeOut();
    }, 8000);
    $("#best_before").addClass("is-invalid");
    $("#best_before").click(function () {
      $("#error_best_before").hide().text;
      $("#best_before").removeClass("is-invalid");
    });
    value_return = "false";
  }

  if (lot_no == "") {
    $("#error_lot_no").show().text("Please Enter Lot No");
    setTimeout(function () {
      $("#error_lot_no").fadeOut();
    }, 8000);
    $("#lot_no").addClass("is-invalid");
    $("#lot_no").click(function () {
      $("#error_lot_no").hide().text;
      $("#lot_no").removeClass("is-invalid");
    });
    value_return = "false";
  }

  // updated error message by shankhpal on 24/05/2023
  if (replica_si_no == "") {
    $("#error_replica_si_no").show().text("Please enter replica si. no");
    setTimeout(function () {
      $("#error_replica_si_no").fadeOut();
    }, 8000);
    $("#replica_si_no").addClass("is-invalid");
    $("#replica_si_no").click(function () {
      $("#error_replica_si_no").hide().text;
      $("#replica_si_no").removeClass("is-invalid");
    });
    value_return = "false";
  }

  if (pack_size == "") {
    $("#error_pack_size").show().text("Please Enter Pack Size");
    setTimeout(function () {
      $("#error_pack_size").fadeOut();
    }, 8000);
    $("#pack_size").addClass("is-invalid");
    $("#pack_size").click(function () {
      $("#error_pack_size").hide().text;
      $("#pack_size").removeClass("is-invalid");
    });
    value_return = "false";
  }

  if (last_lot_no == "") {
    $(".error_last_lot_no").show().text("Please enter Last lot No");
    setTimeout(function () {
      $(".error_last_lot_no").fadeOut();
    }, 8000);
    $("#last_lot_no").addClass("is-invalid");
    $("#last_lot_no").click(function () {
      $(".error_last_lot_no").hide().text;
      $("#last_lot_no").removeClass("is-invalid");
    });
    value_return = "false";
  }

  if (date_of_packing == "") {
    $("#error_date_of_packing").show().text("Please Select date");
    setTimeout(function () {
      $("#error_date_of_packing").fadeOut();
    }, 8000);
    $("#date_of_packing").addClass("is-invalid");
    $("#date_of_packing").click(function () {
      $("#error_date_of_packing").hide().text;
      $("#date_of_packing").removeClass("is-invalid");
    });
    value_return = "false";
  }

  if (value_return == "false") {
    var msg = "Please check some fields are missing or not proper.";
    renderToast("error", msg);
    return false;
  } else {
    return true;
  }
}

//  Reason: this function are updated validation added
// 	Name of person : shankhpal shende
// 	Date: 11-05-2023 - 16-05-2023
function routineInspectionFormValidation() {
  var firm_type = $("#firm_type").val(); // firm type check which type of form is open

  // firm_type = 1 added by shankhpal on 25/05/2023
  if (firm_type == 1) {
    var last_lot_no = $("#last_lot_no").val();
    var quantity_graded = $("#quantity_graded").val();
    var shortcomings_noticed = $("#shortcomings_noticed").val();
    var suggestions = $("#suggestions").val();
    var signature = $("#signature").val();
    var signature_name = $("#signature_name").val();
    var doinspection = $("#doi").val();
    var discrepancies_replica_aco = $("#discrepancies_replica_aco").val();
    var date_last_inspection = $("#date_last_inspection").val();
    var com_approved = $("#com_approved").val();
    var approved_chemist = $("#approved_chemist").val();
    var ach_present = $("#ach_present").val();
    var commodity = $("#commodity").val();
    var p_analytical_reg = $("#p_analytical_reg").val();
    var short_noticed = $("#short_noticed").val();
    var date = $("#date").val();
    var suggestion_during_last = $("#suggestion_during_last").val();
    var date_p_inspection = $("#date_p_inspection").val();
    var name_of_packer = $("#name_of_packer").val();
    var name_inspecting_officer = $("#name_inspecting_officer").val();
    var name_packer_representative = $("#name_packer_representative").val();
    var name_of_inspecting_officer = $("#name_of_inspecting_officer").val();
    var analytical_result_docs = $("#analytical_result_docs").val();
    var signnature_of_packer_docs = $("#signnature_of_packer_docs").val();
    var signnature_of_inspecting_officer_docs = $(
      "#signnature_of_inspecting_officer_docs"
    ).val();
    var last_lot_date = $("#last_lot_date").val();

    var analytical_results = $("#analytical_results").val();
    var designation_inspecting_officer = $(
      "#designation_inspecting_officer"
    ).val();

    var month_upto = $("#month_upto").val();
    var shortcomings_noticed = $("#shortcomings_noticed").val();
    var time_p_inspection = $("#time_p_inspection").val(); // for present time of inspection added on 27/06/2023 by shankhpal
    var quantity = $("#quantity").val();
    var value_return = "true";

    if ($("#fssai_approved-yes").is(":checked")) {
      if (up_to_date_docs_value == "") {
        $("#error_up_to_date_docs").show().text("Please upload docs");
        setTimeout(function () {
          $("#error_up_to_date_docs").fadeOut();
        }, 8000);
        $("#up_to_date_docs").addClass("is-invalid");
        $("#up_to_date_docs").click(function () {
          $("#error_up_to_date_docs").hide().text;
          $("#up_to_date_docs").removeClass("is-invalid");
        });
        value_return = "false";
      }
    } else if ($("#fssai_approved-no").is(":checked")) {
      $("#hide_fssai_approved").hide();
    }

    if ($("#fssai_approved-yes").is(":checked")) {
      if (fssai_approved_docs_value == "") {
        $("#error_fssai_approved_docs").show().text("Please upload docs");
        setTimeout(function () {
          $("#error_fssai_approved_docs").fadeOut();
        }, 8000);
        $("#error_fssai_approved_docs").addClass("is-invalid");
        $("#error_fssai_approved_docs").click(function () {
          $("#error_fssai_approved_docs").hide().text;
          $("#error_fssai_approved_docs").removeClass("is-invalid");
        });
        value_return = "false";
      }
    } else if ($("#fssai_approved-no").is(":checked")) {
      $("#hide_fssai_approved").hide();
    }

    if (name_of_packer == "") {
      $("#error_name_of_packer").show().text("Please Enter name of Packer");
      setTimeout(function () {
        $("#error_name_of_packer").fadeOut();
      }, 8000);
      $("#name_of_packer").addClass("is-invalid");
      $("#name_of_packer").click(function () {
        $("#error_name_of_packer").hide().text;
        $("#name_of_packer").removeClass("is-invalid");
      });
      value_return = "false";
    }

    if (quantity == "") {
      $("#error_quantity").show().text("Please Enter quantity");
      setTimeout(function () {
        $("#error_quantity").fadeOut();
      }, 8000);
      $("#quantity").addClass("is-invalid");
      $("#quantity").click(function () {
        $("#error_quantity").hide().text;
        $("#quantity").removeClass("is-invalid");
      });
      value_return = "false";
    }

    if (analytical_results == "") {
      $("#error_analytical_results")
        .show()
        .text("Please Enter analytical results");
      setTimeout(function () {
        $("#error_analytical_results").fadeOut();
      }, 8000);
      $("#analytical_results").addClass("is-invalid");
      $("#analytical_results").click(function () {
        $("#error_analytical_results").hide().text;
        $("#analytical_results").removeClass("is-invalid");
      });
      value_return = "false";
    }

    // validation to check time field is empty or not added by shankhpal on 27/06/2023
    if (time_p_inspection == "") {
      $("#error_time_p_inspection").show().text("Please Enter time");
      setTimeout(function () {
        $("#error_time_p_inspection").fadeOut();
      }, 8000);
      $("#time_p_inspection").addClass("is-invalid");
      $("#time_p_inspection").click(function () {
        $("#error_time_p_inspection").hide().text;
        $("#time_p_inspection").removeClass("is-invalid");
      });
      value_return = "false";
    }

    if (month_upto == "") {
      $("#error_month_upto")
        .show()
        .text("Select Quantity Graded During Current Month Upto");
      setTimeout(function () {
        $("#error_month_upto").fadeOut();
      }, 8000);
      $("#month_upto").addClass("is-invalid");
      $("#month_upto").click(function () {
        $("#error_month_upto").hide().text;
        $("#month_upto").removeClass("is-invalid");
      });
      value_return = "false";
    }

    if (designation_inspecting_officer == "") {
      $("#error_designation_inspecting_officer")
        .show()
        .text("Please Enter Designation");
      setTimeout(function () {
        $("#error_designation_inspecting_officer").fadeOut();
      }, 8000);
      $("#designation_inspecting_officer").addClass("is-invalid");
      $("#designation_inspecting_officer").click(function () {
        $("#error_designation_inspecting_officer").hide().text;
        $("#designation_inspecting_officer").removeClass("is-invalid");
      });
      value_return = "false";
    }

    if (shortcomings_noticed == "") {
      // Check if shortcomings_noticed is an empty string
      $("#error_shortcomings_noticed") // Select the error message element
        .show() // Show the error message
        .text("Shortcomings noticed cannot be empty!"); // Set the error message text
      setTimeout(function () {
        // Set a timeout to hide the error message after 8 seconds
        $("#error_shortcomings_noticed").fadeOut();
      }, 8000);
      $("#shortcomings_noticed").addClass("is-invalid"); // Add the is-invalid class to the input field
      $("#shortcomings_noticed").click(function () {
        // Set a click event listener on the input field
        $("#error_shortcomings_noticed").hide().text; // Hide the error message and clear its text
        $("#shortcomings_noticed").removeClass("is-invalid"); // Remove the is-invalid class from the input field
      });
      value_return = "false"; // Set the value_return variable to false
    }

    if (name_packer_representative == "") {
      $("#error_name_packer_representative")
        .show()
        .text("Please Enter Name of Packer or his Representative");
      setTimeout(function () {
        $("#error_name_packer_representative").fadeOut();
      }, 8000);
      $("#name_packer_representative").addClass("is-invalid");
      $("#name_packer_representative").click(function () {
        $("#error_name_packer_representative").hide().text;
        $("#name_packer_representative").removeClass("is-invalid");
      });
      value_return = "false";
    }

    if (name_of_inspecting_officer == "") {
      $("#error_name_of_inspecting_officer")
        .show()
        .text("Please Enter Name of Inspecting Officer");
      setTimeout(function () {
        $("#error_name_of_inspecting_officer").fadeOut();
      }, 8000);
      $("#name_of_inspecting_officer").addClass("is-invalid");
      $("#name_of_inspecting_officer").click(function () {
        $("#error_name_of_inspecting_officer").hide().text;
        $("#name_of_inspecting_officer").removeClass("is-invalid");
      });
      value_return = "false";
    }

    // Change Condition for validation and error message by shankhpal 11-05-2023
    if ($("#analytical_result_docs_value").text() == "") {
      if (
        check_file_upload_validation(analytical_result_docs).result == false
      ) {
        $("#error_analytical_result_docs")
          .show()
          .text(
            check_file_upload_validation(analytical_result_docs).error_message
          );
        $("#analytical_result_docs").addClass("is-invalid");
        $("#analytical_result_docs").click(function () {
          $("#error_analytical_result_docs").hide().text;
          $("#analytical_result_docs").removeClass("is-invalid");
        });
        value_return = "false";
      }
    }

    if ($("#signnature_of_inspecting_officer_docs_value").text() == "") {
      if (
        check_file_upload_validation(signnature_of_inspecting_officer_docs)
          .result == false
      ) {
        $("#error_signnature_of_inspecting_officer_docs")
          .show()
          .text(
            check_file_upload_validation(signnature_of_inspecting_officer_docs)
              .error_message
          );
        $("#signnature_of_inspecting_officer_docs").addClass("is-invalid");
        $("#signnature_of_inspecting_officer_docs").click(function () {
          $("#error_signnature_of_inspecting_officer_docs").hide().text;
          $("#signnature_of_inspecting_officer_docs").removeClass("is-invalid");
        });
        value_return = "false";
      }
    }

    if ($("#signnature_of_packer_docs_value").text() == "") {
      if (
        check_file_upload_validation(signnature_of_packer_docs).result == false
      ) {
        $("#error_signnature_of_packer_docs")
          .show()
          .text(
            check_file_upload_validation(signnature_of_packer_docs)
              .error_message
          );
        $("#signnature_of_packer_docs").addClass("is-invalid");
        $("#signnature_of_packer_docs").click(function () {
          $("#error_signnature_of_packer_docs").hide().text;
          $("#signnature_of_packer_docs").removeClass("is-invalid");
        });
        value_return = "false";
      }
    }

    if (name_inspecting_officer == "") {
      $("#error_name_inspecting_officer")
        .show()
        .text("Please Enter name inspecting officer");
      setTimeout(function () {
        $("#error_name_inspecting_officer").fadeOut();
      }, 8000);
      $("#name_inspecting_officer").addClass("is-invalid");
      $("#name_inspecting_officer").click(function () {
        $("#error_name_inspecting_officer").hide().text;
        $("#name_inspecting_officer").removeClass("is-invalid");
      });
      value_return = "false";
    }

    if (street_address == "") {
      $("#error_street_address").show().text("Please Enter street address");
      setTimeout(function () {
        $("#error_street_address").fadeOut();
      }, 8000);
      $("#street_address").addClass("is-invalid");
      $("#street_address").click(function () {
        $("#error_street_address").hide().text;
        $("#street_address").removeClass("is-invalid");
      });
      value_return = "false";
    }
    if (valid_upto == "") {
      $("#error_valid_upto").show().text("Please Enter Permission valid upto");
      setTimeout(function () {
        $("#error_valid_upto").fadeOut();
      }, 8000);
      $("#valid_upto").addClass("is-invalid");
      $("#valid_upto").click(function () {
        $("#error_valid_upto").hide().text;
        $("#valid_upto").removeClass("is-invalid");
      });
      value_return = "false";
    }
    if (date_last_inspection == "") {
      $("#error_date_last_inspection").show().text("Please Select date");
      setTimeout(function () {
        $("#error_date_last_inspection").fadeOut();
      }, 8000);
      $("#date_last_inspection").addClass("is-invalid");
      $("#date_last_inspection").click(function () {
        $("#error_date_last_inspection").hide().text;
        $("#date_last_inspection").removeClass("is-invalid");
      });
      value_return = "false";
    }
    if (last_lot_date == "") {
      $("#error_last_lot_date").show().text("Please Select date");
      setTimeout(function () {
        $("#error_last_lot_date").fadeOut();
      }, 8000);
      $("#last_lot_date").addClass("is-invalid");
      $("#last_lot_date").click(function () {
        $("#error_last_lot_date").hide().text;
        $("#last_lot_date").removeClass("is-invalid");
      });
      value_return = "false";
    }

    if (quantity_graded == "") {
      $("#error_quantity_graded").show().text("Please Enter Quantity graded");
      setTimeout(function () {
        $("#error_quantity_graded").fadeOut();
      }, 8000);
      $("#quantity_graded").addClass("is-invalid");
      $("#quantity_graded").click(function () {
        $("#error_quantity_graded").hide().text;
        $("#quantity_graded").removeClass("is-invalid");
      });
      value_return = "false";
    }
    if (last_lot_no == "") {
      $("#error_last_lot_no").show().text("Please Enter Lat Lot No.");
      setTimeout(function () {
        $("#error_last_lot_no").fadeOut();
      }, 8000);
      $("#last_lot_no").addClass("is-invalid");
      $("#last_lot_no").click(function () {
        $("#error_last_lot_no").hide().text;
        $("#last_lot_no").removeClass("is-invalid");
      });
      value_return = "false";
    }
    if (date_p_inspection == "") {
      $("#error_date_p_inspection").show().text("Please Select date");
      setTimeout(function () {
        $("#error_date_p_inspection").fadeOut();
      }, 8000);
      $("#date_p_inspection").addClass("is-invalid");
      $("#date_p_inspection").click(function () {
        $("#error_date_p_inspection").hide().text;
        $("#date_p_inspection").removeClass("is-invalid");
      });
      value_return = "false";
    }
    if ($("#signature_docs_value").text() == "") {
      // Change Condition for validation and error message by pravin 11-07-2017
      if (check_file_upload_validation(signature).result == false) {
        $("#error_signature")
          .show()
          .text(check_file_upload_validation(signature).error_message);
        setTimeout(function () {
          $("#error_error_signature").fadeOut();
        }, 8000);
        $("#signature").addClass("is-invalid");
        $("#signature").click(function () {
          $("#error_signature").hide().text;
          $("#signature").removeClass("is-invalid");
        });
        value_return = "false";
      }
    }
    if ($("#signature_name_docs_value").text() == "") {
      // Change Condition for validation and error message by pravin 11-07-2017
      if (check_file_upload_validation(signature_name).result == false) {
        $("#error_signature_name")
          .show()
          .text(check_file_upload_validation(signature_name).error_message);
        setTimeout(function () {
          $("#error_signature_name").fadeOut();
        }, 8000);
        $("#signature_name").addClass("is-invalid");
        $("#signature_name").click(function () {
          $("#error_signature_name").hide().text;
          $("#signature_name").removeClass("is-invalid");
        });
        value_return = "false";
      }
    }

    if (doinspection == "") {
      $("#error_doi").show().text("Please Select Date");
      setTimeout(function () {
        $("#error_doi").fadeOut();
      }, 8000);
      $("#doi").addClass("is-invalid");
      $("#doi").click(function () {
        $("#error_doi").hide().text;
        $("#doi").removeClass("is-invalid");
      });
      value_return = "false";
    }
    if (discrepancies_replica_aco == "") {
      $("#error_discrepancies_replica_aco")
        .show()
        .text("Please Enter discrepancies");
      setTimeout(function () {
        $("#error_discrepancies_replica_aco").fadeOut();
      }, 8000);
      $("#discrepancies_replica_aco").addClass("is-invalid");
      $("#discrepancies_replica_aco").click(function () {
        $("#error_discrepancies_replica_aco").hide().text;
        $("#discrepancies_replica_aco").removeClass("is-invalid");
      });
      value_return = "false";
    }
    if (discrepancies_replica_aco != "") {
      $("#discrepancies_replica_aco").show();
    }
    if (shortcomings_noticed == "") {
      $("#error_shortcomings_noticed")
        .show()
        .text("Please Enter Shortcomings Noticed");
      setTimeout(function () {
        $("#error_shortcomings_noticed").fadeOut();
      }, 8000);
      $("#shortcomings_noticed").addClass("is-invalid");
      $("#shortcomings_noticed").click(function () {
        $("#error_shortcomings_noticed").hide().text;
        $("#shortcomings_noticed").removeClass("is-invalid");
      });
      value_return = "false";
    }
    if (suggestions == "") {
      $("#error_suggestions").show().text("Please Enter suggestions");
      setTimeout(function () {
        $("#error_suggestions").fadeOut();
      }, 8000);
      $("#suggestions").addClass("is-invalid");
      $("#suggestions").click(function () {
        $("#error_suggestions").hide().text;
        $("#suggestions").removeClass("is-invalid");
      });
      value_return = "false";
    }
    if (registered_office == "") {
      $("#error_registered_office")
        .show()
        .text("Please Enter registered office");
      setTimeout(function () {
        $("#error_registered_office").fadeOut();
      }, 8000);
      $("#registered_office").addClass("is-invalid");
      $("#registered_office").click(function () {
        $("#error_registered_office").hide().text;
        $("#registered_office").removeClass("is-invalid");
      });
      value_return = "false";
    }
    if (press_premises == "") {
      $("#error_press_premises").show().text("Please Enter press premises");
      setTimeout(function () {
        $("#error_press_premises").fadeOut();
      }, 8000);
      $("#press_premises").addClass("is-invalid");
      $("#press_premises").click(function () {
        $("#error_press_premises").hide().text;
        $("#press_premises").removeClass("is-invalid");
      });
      value_return = "false";
    }
    if (if_any_sugg == "") {
      $("#error_if_any_sugg").show().text("Please Enter any sugg");
      setTimeout(function () {
        $("#error_if_any_sugg").fadeOut();
      }, 8000);
      $("#if_any_sugg").addClass("is-invalid");
      $("#if_any_sugg").click(function () {
        $("#error_if_any_sugg").hide().text;
        $("#if_any_sugg").removeClass("is-invalid");
      });
      value_return = "false";
    }

    if (com_approved == "") {
      $("#error_com_approved").show().text("Please Enter Commodities");
      setTimeout(function () {
        $("#error_com_approved").fadeOut();
      }, 8000);
      $("#com_approved").addClass("is-invalid");
      $("#com_approved").click(function () {
        $("#error_com_approved").hide().text;
        $("#com_approved").removeClass("is-invalid");
      });
      value_return = "false";
    }
    // if (approved_chemist == "") {
    //   $("#error_approved_chemist").show().text("Please Enter Commodities");
    //   setTimeout(function () {
    //     $("#error_approved_chemist").fadeOut();
    //   }, 8000);
    //   $("#approved_chemist").addClass("is-invalid");
    //   $("#approved_chemist").click(function () {
    //     $("#error_approved_chemist").hide().text;
    //     $("#approved_chemist").removeClass("is-invalid");
    //   });
    //   value_return = "false";
    // }
    if (ach_present == "") {
      $("#error_ach_present")
        .show()
        .text(
          "Please Enter Name of the approved chemist Present at the time of inspection"
        );
      setTimeout(function () {
        $("#error_ach_present").fadeOut();
      }, 8000);
      $("#ach_present").addClass("is-invalid");
      $("#ach_present").click(function () {
        $("#error_ach_present").hide().text;
        $("#ach_present").removeClass("is-invalid");
      });
      value_return = "false";
    }
    if (commodity == "") {
      $("#error_commodity").show().text("Please Enter Commodities");
      setTimeout(function () {
        $("#error_commodity").fadeOut();
      }, 8000);
      $("#commodity").addClass("is-invalid");
      $("#commodity").click(function () {
        $("#error_commodity").hide().text;
        $("#commodity").removeClass("is-invalid");
      });
      value_return = "false";
    }
    if (p_analytical_reg == "") {
      $("#error_p_analytical_reg")
        .show()
        .text("Please Enter Name of the Packers and it’s Analytical results");
      setTimeout(function () {
        $("#error_p_analytical_reg").fadeOut();
      }, 8000);
      $("#p_analytical_reg").addClass("is-invalid");
      $("#p_analytical_reg").click(function () {
        $("#error_p_analytical_reg").hide().text;
        $("#p_analytical_reg").removeClass("is-invalid");
      });
      value_return = "false";
    }
    if (short_noticed == "") {
      $("#error_short_noticed")
        .show()
        .text("Please Shortcomings noticed in present Inspection");
      setTimeout(function () {
        $("#error_short_noticed").fadeOut();
      }, 8000);
      $("#short_noticed").addClass("is-invalid");
      $("#short_noticed").click(function () {
        $("#error_short_noticed").hide().text;
        $("#short_noticed").removeClass("is-invalid");
      });
      value_return = "false";
    }
    if (date == "") {
      $("#error_date").show().text("Please select date");
      setTimeout(function () {
        $("#error_date").fadeOut();
      }, 8000);
      $("#date").addClass("is-invalid");
      $("#date").click(function () {
        $("#error_date").hide().text;
        $("#date").removeClass("is-invalid");
      });
      value_return = "false";
    }
    if (suggestion_during_last == "") {
      $("#error_suggestion_during_last")
        .show()
        .text("Please Shortcomings noticed in present Inspection");
      setTimeout(function () {
        $("#error_suggestion_during_last").fadeOut();
      }, 8000);
      $("#suggestion_during_last").addClass("is-invalid");
      $("#suggestion_during_last").click(function () {
        $("#error_suggestion_during_last").hide().text;
        $("#suggestion_during_last").removeClass("is-invalid");
      });
      value_return = "false";
    }
    if (email == "") {
      $("#error_email").show().text("Please Enter Email ID");
      setTimeout(function () {
        $("#error_email").fadeOut();
      }, 8000);
      $("#email").addClass("is-invalid");
      $("#email").click(function () {
        $("#error_email").hide().text;
        $("#email").removeClass("is-invalid");
      });
      value_return = "false";
    }
    if (mobile_no == "") {
      $("#error_mobile_no").show().text("Please Enter Mobile no");
      setTimeout(function () {
        $("#error_mobile_no").fadeOut();
      }, 8000);
      $("#mobile_no").addClass("is-invalid");
      $("#mobile_no").click(function () {
        $("#error_mobile_no").hide().text;
        $("#mobile_no").removeClass("is-invalid");
      });
      value_return = "false";
    }
    if (packaging_material == "") {
      $("#error_packaging_material")
        .show()
        .text("Please Enter Packaging Material");
      setTimeout(function () {
        $("#error_packaging_material").fadeOut();
      }, 8000);
      $("#packaging_material").addClass("is-invalid");
      $("#packaging_material").click(function () {
        $("#error_packaging_material").hide().text;
        $("#packaging_material").removeClass("is-invalid");
      });
      value_return = "false";
    }
    if (value_return == "false") {
      // alert("Please check some fields are missing or not proper.");
      var msg = "Please check some fields are missing or not proper.";
      $.alert(msg);
      return false;
    } else {
      return true;
    }
    if (value_return == "false") {
      var msg = "Please check some fields are missing or not proper.";
      renderToast("error", msg);
      return false;
    } else {
      exit();
    }
  }
  // firm_type =2 added by shankhpal on 25/05/2023
  if (firm_type == 2) {
    var email = $("#email").val();
    var mobile_no = $("#mobile_no").val();
    var packaging_material = $("#packaging_material").val();
    var valid_upto = $("#valid_upto").val();
    var street_address = $("#street_address").val();
    var signature = $("#signature").val();
    var signature_name = $("#signature_name").val();
    var registered_office = $("#registered_office").val();
    var press_premises = $("#press_premises").val();
    var if_any_sugg = $("#if_any_sugg").val();
    var date_last_inspection = $("#date_last_inspection").val();
    var date_p_inspection = $("#date_p_inspection").val();
    const name_of_inspecting_officer = $("#name_of_inspecting_officer").val(); // added on 23/05/2023 by shankhpal
    const signnature_io_docs = $("#signnature_io_docs").val();
    const shortcomings_noticed_docs = $("#shortcomings_noticed_docs").val();
    var time_p_inspection = $("#time_p_inspection").val(); // for present time of inspection added on 27/06/2023 by shankhpal
    var value_return = "true";

    if (name_of_inspecting_officer == "") {
      $("#error_name_of_inspecting_officer")
        .show()
        .text("Input field is required");
      setTimeout(function () {
        $("#error_name_of_inspecting_officer").fadeOut();
      }, 8000);
      $("#name_of_inspecting_officer").addClass("is-invalid");
      $("#name_of_inspecting_officer").click(function () {
        $("#error_name_of_inspecting_officer").hide().text;
        $("#name_of_inspecting_officer").removeClass("is-invalid");
      });
      value_return = "false";
    }

    // Change Condition for validation and error message by shankhpal 23-05-2023
    if ($("#signnature_io_docs_value").text() == "") {
      if (check_file_upload_validation(signnature_io_docs).result == false) {
        $("#error_signnature_io_docs")
          .show()
          .text(check_file_upload_validation(signnature_io_docs).error_message);
        $("#signnature_io_docs").addClass("is-invalid");
        $("#signnature_io_docs").click(function () {
          $("#error_signnature_io_docs").hide().text;
          $("#signnature_io_docs").removeClass("is-invalid");
        });
        value_return = "false";
      }
    }

    // Change Condition for validation and error message by shankhpal 23-05-2023
    if ($("#shortcomings_noticed_docs_value").text() == "") {
      if (
        check_file_upload_validation(shortcomings_noticed_docs).result == false
      ) {
        $("#error_shortcomings_noticed_docs")
          .show()
          .text(
            check_file_upload_validation(shortcomings_noticed_docs)
              .error_message
          );
        $("#shortcomings_noticed_docs").addClass("is-invalid");
        $("#shortcomings_noticed_docs").click(function () {
          $("#error_shortcomings_noticed_docs").hide().text;
          $("#shortcomings_noticed_docs").removeClass("is-invalid");
        });
        value_return = "false";
      }
    }

    if (street_address == "") {
      $("#error_street_address").show().text("Please Enter street address");
      setTimeout(function () {
        $("#error_street_address").fadeOut();
      }, 8000);
      $("#street_address").addClass("is-invalid");
      $("#street_address").click(function () {
        $("#error_street_address").hide().text;
        $("#street_address").removeClass("is-invalid");
      });
      value_return = "false";
    }

    if (valid_upto == "") {
      $("#error_valid_upto").show().text("Please Enter Permission valid upto");
      setTimeout(function () {
        $("#error_valid_upto").fadeOut();
      }, 8000);
      $("#valid_upto").addClass("is-invalid");
      $("#valid_upto").click(function () {
        $("#error_valid_upto").hide().text;
        $("#valid_upto").removeClass("is-invalid");
      });
      value_return = "false";
    }

    if (date_last_inspection == "") {
      $("#error_date_last_inspection").show().text("Please Select date");
      setTimeout(function () {
        $("#error_date_last_inspection").fadeOut();
      }, 8000);
      $("#date_last_inspection").addClass("is-invalid");
      $("#date_last_inspection").click(function () {
        $("#error_date_last_inspection").hide().text;
        $("#date_last_inspection").removeClass("is-invalid");
      });
      value_return = "false";
    }

    // validation to check time field is empty or not added by shankhpal on 27/06/2023
    if (time_p_inspection == "") {
      $("#error_time_p_inspection").show().text("Please Enter time");
      setTimeout(function () {
        $("#error_time_p_inspection").fadeOut();
      }, 8000);
      $("#time_p_inspection").addClass("is-invalid");
      $("#time_p_inspection").click(function () {
        $("#error_time_p_inspection").hide().text;
        $("#time_p_inspection").removeClass("is-invalid");
      });
      value_return = "false";
    }

    if (date_p_inspection == "") {
      $("#error_date_p_inspection").show().text("Please Select date");
      setTimeout(function () {
        $("#error_date_p_inspection").fadeOut();
      }, 8000);
      $("#date_p_inspection").addClass("is-invalid");
      $("#date_p_inspection").click(function () {
        $("#error_date_p_inspection").hide().text;
        $("#date_p_inspection").removeClass("is-invalid");
      });
      value_return = "false";
    }
    if ($("#signature_docs_value").text() == "") {
      // Change Condition for validation and error message by pravin 11-07-2017
      if (check_file_upload_validation(signature).result == false) {
        $("#error_signature")
          .show()
          .text(check_file_upload_validation(signature).error_message);
        setTimeout(function () {
          $("#error_error_signature").fadeOut();
        }, 8000);
        $("#signature").addClass("is-invalid");
        $("#signature").click(function () {
          $("#error_signature").hide().text;
          $("#signature").removeClass("is-invalid");
        });
        value_return = "false";
      }
    }

    if ($("#signature_name_docs_value").text() == "") {
      // Change Condition for validation and error message by pravin 11-07-2017
      if (check_file_upload_validation(signature_name).result == false) {
        $("#error_signature_name")
          .show()
          .text(check_file_upload_validation(signature_name).error_message);
        setTimeout(function () {
          $("#error_signature_name").fadeOut();
        }, 8000);
        $("#signature_name").addClass("is-invalid");
        $("#signature_name").click(function () {
          $("#error_signature_name").hide().text;
          $("#signature_name").removeClass("is-invalid");
        });
        value_return = "false";
      }
    }

    if (registered_office == "") {
      $("#error_registered_office")
        .show()
        .text("Please Enter registered office");
      setTimeout(function () {
        $("#error_registered_office").fadeOut();
      }, 8000);
      $("#registered_office").addClass("is-invalid");
      $("#registered_office").click(function () {
        $("#error_registered_office").hide().text;
        $("#registered_office").removeClass("is-invalid");
      });
      value_return = "false";
    }
    if (press_premises == "") {
      $("#error_press_premises").show().text("Please Enter press premises");
      setTimeout(function () {
        $("#error_press_premises").fadeOut();
      }, 8000);
      $("#press_premises").addClass("is-invalid");
      $("#press_premises").click(function () {
        $("#error_press_premises").hide().text;
        $("#press_premises").removeClass("is-invalid");
      });
      value_return = "false";
    }
    if (if_any_sugg == "") {
      $("#error_if_any_sugg").show().text("Please Enter any sugg");
      setTimeout(function () {
        $("#error_if_any_sugg").fadeOut();
      }, 8000);
      $("#if_any_sugg").addClass("is-invalid");
      $("#if_any_sugg").click(function () {
        $("#error_if_any_sugg").hide().text;
        $("#if_any_sugg").removeClass("is-invalid");
      });
      value_return = "false";
    }
    if (email == "") {
      $("#error_email").show().text("Please Enter Email ID");
      setTimeout(function () {
        $("#error_email").fadeOut();
      }, 8000);
      $("#email").addClass("is-invalid");
      $("#email").click(function () {
        $("#error_email").hide().text;
        $("#email").removeClass("is-invalid");
      });
      value_return = "false";
    }

    if (mobile_no == "") {
      $("#error_mobile_no").show().text("Please Enter Mobile no");
      setTimeout(function () {
        $("#error_mobile_no").fadeOut();
      }, 8000);
      $("#mobile_no").addClass("is-invalid");
      $("#mobile_no").click(function () {
        $("#error_mobile_no").hide().text;
        $("#mobile_no").removeClass("is-invalid");
      });
      value_return = "false";
    }

    if (packaging_material == "") {
      $("#error_packaging_material")
        .show()
        .text("Please Enter Packaging Material");
      setTimeout(function () {
        $("#error_packaging_material").fadeOut();
      }, 8000);
      $("#packaging_material").addClass("is-invalid");
      $("#packaging_material").click(function () {
        $("#error_packaging_material").hide().text;
        $("#packaging_material").removeClass("is-invalid");
      });
      value_return = "false";
    }

    if (value_return == "false") {
      // alert("Please check some fields are missing or not proper.");
      var msg = "Please check some fields are missing or not proper.";
      $.alert(msg);
      return false;
    } else {
      return true;
    }

    function renderToast(theme, msgTxt) {
      $("#toast-msg-" + theme).html(msgTxt);
      $("#toast-msg-box-" + theme).fadeIn("slow");
      $("#toast-msg-box-" + theme)
        .delay(3000)
        .fadeOut("slow");
    }
  }
  // firm_type = 3 added by shankhpal on 25/05/2023
  if (firm_type == 3) {
    var date_last_inspection = $("#date_last_inspection").val();
    var date_p_inspection = $("#date_p_inspection").val();
    var approved_chemist = $("#approved_chemist").val();
    var last_lot_no = $("#last_lot_no").val();
    var date = $("#date").val();
    var short_noticed = $("#short_noticed").val();
    var suggestions = $("#suggestions").val();
    var signature = $("#signature").val();
    var signature_name = $("#signature_name").val();
    var p_analytical_reg = $("#p_analytical_reg").val();
    var suggestion_during_last = $("#suggestion_during_last").val();
    var commodity = $("#commodity").val();
    const shortcomings_noticed = $("#shortcomings_noticed").val();
    const authorized_persion_name = $("#authorized_persion_name").val();
    const authorized_signature_docs = $("#authorized_signature_docs").val();
    const name_of_inspecting_officer = $("#name_of_inspecting_officer").val();
    const designation_inspecting_officer = $(
      "#designation_inspecting_officer"
    ).val();
    const signnature_of_inspecting_officer_docs = $(
      "#signnature_of_inspecting_officer_docs"
    ).val();

    var time_p_inspection = $("#time_p_inspection").val(); // for present time of inspection added on 27/06/2023 by shankhpal
    const name_of_packers = $("#name_of_packers").val();

    // if (name_of_packers == "") {
    //   $("#error_name_of_packers").show().text("Please select packer name");
    //   setTimeout(function () {
    //     $("#error_name_of_packers").fadeOut();
    //   }, 8000);
    //   $("#name_of_packers").addClass("is-invalid");
    //   $("#name_of_packers").click(function () {
    //     $("#error_name_of_packers").hide().text;
    //     $("#name_of_packers").removeClass("is-invalid");
    //   });
    //   value_return = "false";
    // }

    // Change Condition for validation and error message by shankhpal 23-05-2023
    if ($("#signnature_of_inspecting_officer_docs_value").text() == "") {
      if (
        check_file_upload_validation(signnature_of_inspecting_officer_docs)
          .result == false
      ) {
        $("#error_signnature_of_inspecting_officer_docs")
          .show()
          .text(
            check_file_upload_validation(signnature_of_inspecting_officer_docs)
              .error_message
          );
        $("#signnature_of_inspecting_officer_docs").addClass("is-invalid");
        $("#signnature_of_inspecting_officer_docs").click(function () {
          $("#error_signnature_of_inspecting_officer_docs").hide().text;
          $("#signnature_of_inspecting_officer_docs").removeClass("is-invalid");
        });
        value_return = "false";
      }
    }

    if (designation_inspecting_officer == "") {
      $("#error_designation_inspecting_officer")
        .show()
        .text("Please Enter Designation");
      setTimeout(function () {
        $("#error_designation_inspecting_officer").fadeOut();
      }, 8000);
      $("#designation_inspecting_officer").addClass("is-invalid");
      $("#designation_inspecting_officer").click(function () {
        $("#error_designation_inspecting_officer").hide().text;
        $("#designation_inspecting_officer").removeClass("is-invalid");
      });
      value_return = "false";
    }

    if (name_of_inspecting_officer == "") {
      $("#error_name_of_inspecting_officer")
        .show()
        .text("Please Enter Name of the Inspecting Officer");
      setTimeout(function () {
        $("#error_name_of_inspecting_officer").fadeOut();
      }, 8000);
      $("#name_of_inspecting_officer").addClass("is-invalid");
      $("#name_of_inspecting_officer").click(function () {
        $("#error_name_of_inspecting_officer").hide().text;
        $("#name_of_inspecting_officer").removeClass("is-invalid");
      });
      value_return = "false";
    }

    // Change Condition for validation and error message by shankhpal 23-05-2023
    if ($("#authorized_signature_docs_value").text() == "") {
      if (
        check_file_upload_validation(authorized_signature_docs).result == false
      ) {
        $("#error_authorized_signature_docs")
          .show()
          .text(
            check_file_upload_validation(authorized_signature_docs)
              .error_message
          );
        $("#authorized_signature_docs").addClass("is-invalid");
        $("#authorized_signature_docs").click(function () {
          $("#error_authorized_signature_docs").hide().text;
          $("#authorized_signature_docs").removeClass("is-invalid");
        });
        value_return = "false";
      }
    }

    if (authorized_persion_name == "") {
      $("#error_authorized_persion_name")
        .show()
        .text("Please Enter Name of the Office Authorized person");
      setTimeout(function () {
        $("#error_authorized_persion_name").fadeOut();
      }, 8000);
      $("#authorized_persion_name").addClass("is-invalid");
      $("#authorized_persion_name").click(function () {
        $("#error_authorized_persion_name").hide().text;
        $("#authorized_persion_name").removeClass("is-invalid");
      });
      value_return = "false";
    }

    if (shortcomings_noticed == "") {
      $("#error_shortcomings_noticed")
        .show()
        .text("Please Enter shortcomings noticed");
      setTimeout(function () {
        $("#error_shortcomings_noticed").fadeOut();
      }, 8000);
      $("#shortcomings_noticed").addClass("is-invalid");
      $("#shortcomings_noticed").click(function () {
        $("#error_shortcomings_noticed").hide().text;
        $("#shortcomings_noticed").removeClass("is-invalid");
      });
      value_return = "false";
    }

    // validation to check time field is empty or not added by shankhpal on 27/06/2023
    if (time_p_inspection == "") {
      $("#error_time_p_inspection").show().text("Please Enter time");
      setTimeout(function () {
        $("#error_time_p_inspection").fadeOut();
      }, 8000);
      $("#time_p_inspection").addClass("is-invalid");
      $("#time_p_inspection").click(function () {
        $("#error_time_p_inspection").hide().text;
        $("#time_p_inspection").removeClass("is-invalid");
      });
      value_return = "false";
    }
    if (commodity == "") {
      $("#error_commodity").show().text("Please Enter commodity");
      setTimeout(function () {
        $("#error_commodity").fadeOut();
      }, 8000);
      $("#commodity").addClass("is-invalid");
      $("#commodity").click(function () {
        $("#error_commodity").hide().text;
        $("#commodity").removeClass("is-invalid");
      });
      value_return = "false";
    }

    if (date_last_inspection == "") {
      $("#error_date_last_inspection").show().text("Please Select date");
      setTimeout(function () {
        $("#error_date_last_inspection").fadeOut();
      }, 8000);
      $("#date_last_inspection").addClass("is-invalid");
      $("#date_last_inspection").click(function () {
        $("#error_date_last_inspection").hide().text;
        $("#date_last_inspection").removeClass("is-invalid");
      });
      value_return = "false";
    }
    if (suggestion_during_last == "") {
      $("#error_suggestion_during_last")
        .show()
        .text("Please Enter suggestion during last");
      setTimeout(function () {
        $("#error_suggestion_during_last").fadeOut();
      }, 8000);
      $("#suggestion_during_last").addClass("is-invalid");
      $("#suggestion_during_last").click(function () {
        $("#error_suggestion_during_last").hide().text;
        $("#suggestion_during_last").removeClass("is-invalid");
      });
      value_return = "false";
    }

    if (date_p_inspection == "") {
      $("#error_date_p_inspection").show().text("Please Select date");
      setTimeout(function () {
        $("#error_date_p_inspection").fadeOut();
      }, 8000);
      $("#date_p_inspection").addClass("is-invalid");
      $("#date_p_inspection").click(function () {
        $("#error_date_p_inspection").hide().text;
        $("#date_p_inspection").removeClass("is-invalid");
      });
      value_return = "false";
    }

    // if (approved_chemist == "") {
    //   $("#error_approved_chemist").show().text("Please select chemist");
    //   setTimeout(function () {
    //     $("#error_approved_chemist").fadeOut();
    //   }, 8000);
    //   $("#approved_chemist").addClass("is-invalid");
    //   $("#approved_chemist").click(function () {
    //     $("#error_approved_chemist").hide().text;
    //     $("#approved_chemist").removeClass("is-invalid");
    //   });
    //   value_return = "false";
    // }

    if (last_lot_no == "") {
      $("#error_last_lot_no").show().text("Please Enter last lot no");
      setTimeout(function () {
        $("#error_last_lot_no").fadeOut();
      }, 8000);
      $("#last_lot_no").addClass("is-invalid");
      $("#last_lot_no").click(function () {
        $("#error_last_lot_no").hide().text;
        $("#last_lot_no").removeClass("is-invalid");
      });
      value_return = "false";
    }
    if (date == "") {
      $("#error_date").show().text("Please Select date.");
      setTimeout(function () {
        $("#error_date").fadeOut();
      }, 8000);
      $("#date").addClass("is-invalid");
      $("#date").click(function () {
        $("#error_date").hide().text;
        $("#date").removeClass("is-invalid");
      });
      value_return = "false";
    }
    if (short_noticed == "") {
      $("#error_short_noticed").show().text("Please ENter Short noticed.");
      setTimeout(function () {
        $("#error_short_noticed").fadeOut();
      }, 8000);
      $("#short_noticed").addClass("is-invalid");
      $("#short_noticed").click(function () {
        $("#error_short_noticed").hide().text;
        $("#short_noticed").removeClass("is-invalid");
      });
      value_return = "false";
    }
    if (suggestions == "") {
      $("#error_suggestions").show().text("Please Enter Suggestions.");
      setTimeout(function () {
        $("#error_suggestions").fadeOut();
      }, 8000);
      $("#suggestions").addClass("is-invalid");
      $("#suggestions").click(function () {
        $("#error_suggestions").hide().text;
        $("#suggestions").removeClass("is-invalid");
      });
      value_return = "false";
    }
    if ($("#signature_docs_value").text() == "") {
      // Change Condition for validation and error message by pravin 11-07-2017
      if (check_file_upload_validation(signature).result == false) {
        $("#error_signature")
          .show()
          .text(check_file_upload_validation(signature).error_message);
        setTimeout(function () {
          $("#error_error_signature").fadeOut();
        }, 8000);
        $("#signature").addClass("is-invalid");
        $("#signature").click(function () {
          $("#error_signature").hide().text;
          $("#signature").removeClass("is-invalid");
        });
        value_return = "false";
      }
    }

    if ($("#signature_name_docs_value").text() == "") {
      // Change Condition for validation and error message by pravin 11-07-2017
      if (check_file_upload_validation(signature_name).result == false) {
        $("#error_signature_name")
          .show()
          .text(check_file_upload_validation(signature_name).error_message);
        setTimeout(function () {
          $("#error_signature_name").fadeOut();
        }, 8000);
        $("#signature_name").addClass("is-invalid");
        $("#signature_name").click(function () {
          $("#error_signature_name").hide().text;
          $("#signature_name").removeClass("is-invalid");
        });
        value_return = "false";
      }
    }

    if (p_analytical_reg == "") {
      $("#error_p_analytical_reg")
        .show()
        .text("Please Enter Name of the Packers.");
      setTimeout(function () {
        $("#error_p_analytical_reg").fadeOut();
      }, 8000);
      $("#p_analytical_reg").addClass("is-invalid");
      $("#p_analytical_reg").click(function () {
        $("#error_p_analytical_reg").hide().text;
        $("#p_analytical_reg").removeClass("is-invalid");
      });
      value_return = "false";
    }

    if (value_return == "false") {
      // alert("Please check some fields are missing or not proper.");
      var msg = "Please check some fields are missing or not proper.";
      $.alert(msg);
      return false;
    } else {
      return true;
    }
  }

  function renderToast(theme, msgTxt) {
    $("#toast-msg-" + theme).html(msgTxt);
    $("#toast-msg-box-" + theme).fadeIn("slow");
    $("#toast-msg-box-" + theme)
      .delay(3000)
      .fadeOut("slow");
  }
}

function check_file_upload_validation(field_value) {
  var error_message = "Please upload the required file";

  if (field_value == "") {
    return { result: false, error_message: error_message };
  }

  return true;
}

$(document).ready(function () {
  $("#date_p_inspection").datepicker({
    format: "dd/mm/yyyy",
    autoclose: true,
  });
  $("#last_lot_date").datepicker({
    format: "dd/mm/yyyy",
    autoclose: true,
  });
  $("#month_upto").datepicker({
    format: "dd/mm/yyyy",
    autoclose: true,
  });

  $("#time_p_inspection").datetimepicker({
    format: "hh:mm:ss a",
  });

  $("#date").datepicker({
    format: "dd/mm/yyyy",
    autoclose: true,
  });
  $("#date_last_inspection").datepicker({
    format: "dd/mm/yyyy",
    autoclose: true,
  });
  // $("#valid_upto").datepicker({
  //   format: "dd/mm/yyyy",
  //   autoclose: true,
  // });

  $("#date_of_packing").datepicker({
    format: "dd/mm/yyyy",
    autoclose: true,
  });
  date_last_inspection;
});

$(document).ready(function () {
  $("#sample_table").ready(function () {
    $(".packer_edit").show();
    $(".packer_delete").show();
  });
});

//for replica account correct
//for already checked
if ($("#replica_account_correct-yes").is(":checked")) {
  $("#hide_disc_replica").hide();
} else if ($("#replica_account_correct-no").is(":checked")) {
  $("#hide_disc_replica").show();
}

//for on clicked
$("#replica_account_correct-yes").click(function () {
  $("#hide_disc_replica").hide();
});

$("#replica_account_correct-no").click(function () {
  $("#hide_disc_replica").show();
});

//for FSSAI approved Lab for food safety
//for already checked
if ($("#fssai_approved-yes").is(":checked")) {
  $("#hide_fssai_approved").show();
} else if ($("#fssai_approved-no").is(":checked")) {
  $("#hide_fssai_approved").hide();
}

//for on clicked
$("#fssai_approved-yes").click(function () {
  $("#hide_fssai_approved").show();
});

$("#fssai_approved-no").click(function () {
  $("#hide_fssai_approved").hide();
});

//for Are they up to date
//for already checked
if ($("#up_to_date-yes").is(":checked")) {
  $("#hide_up_to_date").show();
} else if ($("#up_to_date-no").is(":checked")) {
  $("#hide_up_to_date").hide();
}

//for on clicked
$("#up_to_date-yes").click(function () {
  $("#hide_up_to_date").show();
});

$("#up_to_date-no").click(function () {
  $("#hide_up_to_date").hide();
});

//for enumerate_briefly_suggestions
//for already checked
if ($("#enumerate_briefly_suggestions-yes").is(":checked")) {
  $("#hide_enumerate_briefly_suggestions").show();
} else if ($("#enumerate_briefly_suggestions-no").is(":checked")) {
  $("#hide_enumerate_briefly_suggestions").hide();
}

//for on clicked
$("#enumerate_briefly_suggestions-yes").click(function () {
  $("#hide_enumerate_briefly_suggestions").show();
});

$("#enumerate_briefly_suggestions-no").click(function () {
  $("#hide_enumerate_briefly_suggestions").hide();
});

//to get replica charge from db
$("#replica_appl_list_table").on("change", ".packer_id", function () {
  var id_No = this.id.split("-"); //to get dynamic id of element for each row, and split to get no.
  //var packer_id = $("#packer_id").val();
  var packer_id = $("#packer_id").find("option:selected").text();

  $.ajax({
    type: "POST",
    url: "../AjaxFunctions/get_packer_wise_data",
    data: { packer_id: packer_id },
    beforeSend: function (xhr) {
      // Add this line
      xhr.setRequestHeader("X-CSRF-Token", $('[name="_csrfToken"]').val());
    },
    success: function (response) {
      var response = response.match(/~([^']+)~/)[1]; //getting data bitween ~..~ from response

      if (response == "No data") {
        alert("No Record available for selected packed");
        $("#ca_no").val("");
        $("#tbl_name").val("");
        $("#sub_commodity").val("");
        return false;
      } else {
        response = JSON.parse(response); //response is JSOn encoded to parse JSON

        $("#ca_no").val(response["ca_no"]);
        $("#tbl_name").val(response["tbl_name"]);
        $("#certificate_valid_upto").val(response["certificate_valid_upto"]);
        $("#sub_commodity").append(
          `<option value="${response["sub_commodity_value"]}"></option>`
        );
        $("#sub_commodity").append(response["sub_commodity"]);
      }
    },
  });
});

// added by shankhpal shende on 23/05/2023
$(document).ready(function () {
  var last_insp_suggestion = $("#last_insp_suggestion");
  var enumerate_briefly_suggestions = $("#enumerate_briefly_suggestions");

  var radioValue = $("input[name='suggestions_last_ins_yes_no']:checked").val();

  var e_briefly_suggestions_radio = $(
    "input[name='e_briefly_suggestions_radio']:checked"
  ).val();

  if (radioValue === "no") {
    last_insp_suggestion.hide();
  } else {
    last_insp_suggestion.show();
  }

  if (e_briefly_suggestions_radio == "yes") {
    enumerate_briefly_suggestions.show();
  } else {
    enumerate_briefly_suggestions.hide();
  }

  $("#suggestions_last_ins-yes").on("click", function () {
    last_insp_suggestion.show();
  });
  $("#suggestions_last_ins-no").on("click", function () {
    last_insp_suggestion.hide();
  });

  $("#e_briefly_suggestions_radio-yes").on("click", function () {
    enumerate_briefly_suggestions.show();
  });
  $("#e_briefly_suggestions_radio-no").on("click", function () {
    enumerate_briefly_suggestions.hide();
  });

  $("#last-sugeesion-popup").on("click", function (e) {
    $(".popup").fadeIn(500);
    e.preventDefault();
  });

  $(".close").click(function () {
    $(".popup").fadeOut(500);
  });
});
