$(document).ready(function () {
  $(".replica_allotment_btn").each(function () {
    var buttonID = $(this).attr("id");
    var index = buttonID.split("_")[3];
    initializeDatepickers(index);
    var rpl_no_of_packets = $("#rpl_no_of_packets_" + index).val();
    if (rpl_no_of_packets != "") {
      calculateQty(index);
    }

    var labNablAccreditedInput = document.getElementById(
      "lab_nabl_accredited"
    ).value;

    const laboratory_id = document.getElementById("rpl_displayed_lab_" + index);

    const rpl_reportno = document.getElementById("rpl_reportno_" + index);
    const rpl_reportdate = document.getElementById("rpl_reportdate_" + index);
    const rpl_remarks = document.getElementById("rpl_remarks_" + index);
    const rpl_grading_lab = document.getElementById("rpl_grading_lab_" + index);
    if (labNablAccreditedInput === "" || labNablAccreditedInput === null) {
      laboratory_id.style.display = "none";
      rpl_reportno.style.display = "none";
      rpl_reportdate.style.display = "none";
      rpl_remarks.style.display = "none";
      rpl_grading_lab.style.display = "none";
    } else {
      laboratory_id.style.display = "block"; // Or "initial" depending on your CSS
      rpl_reportno.style.display = "block";
      rpl_reportdate.style.display = "block";
      rpl_remarks.style.display = "block";
      rpl_grading_lab.style.display = "block";
    }
  });

  $(".replica_allotment_btn").each(function () {
    $(this).click(function () {
      var buttonID = $(this).attr("id");
      var index = buttonID.split("_")[3];

      var isValid = true;

      function validateField(fieldId, errorMessage) {
        var $field = $("#" + fieldId);
        var $errorField = $("#error-" + fieldId);
        var fieldValue = $field.val().trim();

        if (fieldValue === "") {
          $errorField.show().text(errorMessage).css({
            color: "red",
            "font-size": "14px",
            "font-weight": "500",
            "text-align": "left",
          });

          setTimeout(function () {
            $errorField.fadeOut();
          }, 8000);

          $field.addClass("is-invalid");

          $field.click(function () {
            $errorField.hide().text("");
            $field.removeClass("is-invalid");
          });

          isValid = false;
        }
      }

      validateField("rpl_lotno_" + index, "Please Enter Lot No.TF No./M. No."); // Validate Lot No./M. No. field
      validateField(
        "rpl_datesampling_" + index,
        "Please Select Date of Sampling."
      ); // Validate Date of sampling field
      validateField(
        "rpl_dateofpacking_" + index,
        "Please Select Date of packing."
      ); // Validate Date of date of packing field
      validateField(
        "rpl_qty_quantal_" + index,
        "Please Enter Total Qty. graded in Quintal."
      ); // Validation for Total Qty. graded in Quintal field
      validateField(
        "rpl_estimatedvalue_" + index,
        "Please Enter Estimated value (in Rs.)"
      ); // Validation for Estimated value (in Rs.)

      var labNablAccreditedInput = document.getElementById(
        "lab_nabl_accredited"
      ).value;

      if (labNablAccreditedInput === "") {
        console.log("labNablAccreditedInput is empty");
      } else {
        validateField("rpl_reportno_" + index, "Please Enter Report no.");
        validateField("rpl_reportdate_" + index, "Please Select report date.");
        validateField("rpl_remarks_" + index, "Please Enter Remark.");
      }

      if (!isValid) {
        renderToast(
          "error",
          "Please check some fields are missing or not proper."
        );
        return false;
      } else {
        var formData = {
          // Construct your data object with field values here
          rpl_commodity: $("#rpl_commodity_" + index).val(),
          rpl_lotno: $("#rpl_lotno_" + index).val(),
          rpl_datesampling: $("#rpl_datesampling_" + index).val(),
          rpl_dateofpacking: $("#rpl_dateofpacking_" + index).val(),
          rpl_grade: $("#rpl_grade_" + index).val(),
          rpl_packet_size: $("#rpl_packet_size_" + index).val(),
          rpl_packet_size_unit: $("#rpl_packet_size_unit_" + index).val(),
          rpl_no_of_packets: $("#rpl_no_of_packets_" + index).val(),
          rpl_qty_quantal: $("#rpl_qty_quantal_" + index).val(),
          rpl_estimatedvalue: $("#rpl_estimatedvalue_" + index).val(),
          rpl_alloted_rep_from: $("#rpl_alloted_rep_from_" + index).val(),
          rpl_alloted_rep_to: $("#rpl_alloted_rep_to_" + index).val(),
          rpl_total_quantity: $("#rpl_total_quantity_" + index).val(),
          rpl_replicacharges: $("#rpl_replicacharges_" + index).val(),
          rpl_grading_lab: $("#rpl_grading_lab_" + index).val(),
          rpl_reportno: $("#rpl_reportno_" + index).val(),
          rpl_reportdate: $("#rpl_reportdate_" + index).val(),
          rpl_remarks: $("#rpl_remarks_" + index).val(),

          // Add more fields as needed
        };

        if (labNablAccreditedInput === "") {
          delete formData.rpl_grading_lab;
        }

        $.ajax({
          url: "../AjaxFunctions/add_replica_allotment_data",
          type: "POST",
          data: formData,
          success: function (response) {
            if (response == "added")
              // Handle success response here
              renderToast("success", "Data inserted successfully!");
            // location.reload(); // Reload the page
            // Optionally, you might want to reset form fields here
          },
          error: function (error) {
            // Handle error response here
            renderToast("error", "Error inserting data.");
          },
        });
      }
    });
  });

  // Function to display toast messages
  function renderToast(theme, msgTxt) {
    $("#toast-msg-" + theme).html(msgTxt);
    $("#toast-msg-box-" + theme).fadeIn("slow");
    $("#toast-msg-box-" + theme)
      .delay(3000)
      .fadeOut("slow");
  }

  function initializeDatepickers(index) {
    $("#rpl_datesampling_" + index).datepicker({
      format: "dd/mm/yyyy",
      autoclose: true,
    });
    $("#rpl_dateofpacking_" + index).datepicker({
      format: "dd/mm/yyyy",
      autoclose: true,
    });
    $("#rpl_reportdate_" + index).datepicker({
      format: "dd/mm/yyyy",
      autoclose: true,
    });
  }

  function calculateQty(index) {
    var packSize = parseFloat($("#rpl_packet_size_" + index).val());
    var unit = $("#rpl_packet_size_unit_" + index).val();

    var totalPackages = parseFloat($("#rpl_no_of_packets_" + index).val());

    if (isNaN(packSize) || isNaN(totalPackages)) {
      $("#rpl_qty_quantal_" + index).text("Invalid input");
      return;
    }

    var unitConversions = {
      quintal: 1,
      kg: 0.01,
      gm: 0.00001,
      ml: 0.000001,
      ltr: 0.1,
      Nos: 0.01,
      // Add more units and their conversion factors if needed
    };

    if (unitConversions[unit] === undefined) {
      $("#rpl_qty_quantal_" + index).text("Invalid unit");
      return;
    }

    var conversionFactor = unitConversions[unit];
    totalQty = packSize * totalPackages * conversionFactor; // Assign to the outer totalQty

    var decimalPlaces = 3; // Change this to 2 if you want 2 decimal places
    var formattedQty = totalQty.toFixed(decimalPlaces);

    $("#rpl_qty_quantal_" + index).val(formattedQty);
  }
});
