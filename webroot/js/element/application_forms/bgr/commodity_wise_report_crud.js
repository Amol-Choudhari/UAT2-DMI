$(document).ready(function () {
  function updateOverallTotalCharges() {
    $.ajax({
      type: "GET",
      url: "../AjaxFunctions/get_total_replica_charge",
      success: function (response) {
        // Update the overall_total_chrg element with the response value
        $("#overall_total_chrg").val(response);
        $("#totalRevenueHeader").html(response);
        var totalRevenueValue = response;
        $("#totalRevenueHidden").val(totalRevenueValue);
      },
    });
    $.ajax({
      type: "GET",
      url: "../AjaxFunctions/get_total_progressive_revenue",
      success: function (response) {
        // Update the overall_total_chrg element with the response value
        $("#progresiveRevenue").html(response);

        var progresiveRevenue = response;
        $("#progresiveRevenueHidden").val(progresiveRevenue);
      },
    });
  }
  updateOverallTotalCharges();

  $(".delete_bgr_id").click(function (e) {
    e.preventDefault();
    var id = $(this).attr("id");
    if (confirm("Are you sure you want to delete this record?")) {
      $.ajax({
        url: "../AjaxFunctions/delete_bgr_details",
        method: "POST",
        data: { id: id },
        success: function (response) {
          // alert(response);
          if (response == "success") {
            var custom_row = document.getElementById("custom_row" + id);
            custom_row.parentNode.removeChild(custom_row);
            renderToast("success", "Record deleted successfully.");
            // location.reload(); // Reload the page
          } else {
            alert("An error occurred while deleting the record."); // Display an error message
          }
        },
      });
      updateOverallTotalCharges();
    }
  });

  $("#add_bgr_details, #update_bgr_details").on("click", function (e) {
    e.preventDefault();
    let labNablAccreditedInput = document.getElementById(
      "lab_nabl_accredited"
    ).value;

    var isValid = true;
    function validateField(fieldId, errorMessage) {
      var $field = $("#" + fieldId);
      var $errorField = $("#error-" + fieldId);
      var fieldValue = $field.val();

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

    validateField("ta-commodity-", "Please Select Commodity"); // Validate Commodity
    validateField("lot_no_tf_no_m_no", "Please Enter Lot No.TF No./M. No."); // Validate Lot No.TF No./M. No.
    validateField("date_of_sampling", "Please Select Date of Sampling."); // Validate Date of sampling field
    validateField("date_of_packing", "Please Select Date of packing."); // Validate Date of date of packing field
    validateField("grade", "Please Select grade."); // Validation for grade
    validateField("ta-packet_size_unit-", "Please Select Unit"); // Validation for Estimated value (in Rs.)
    validateField("ta-packet_size-", "Please Enter Size");

    validateField(
      "ta-no_of_packets-",
      "Please Enter Total Qty. graded in Quintal"
    ); // Validation for Total Qty. graded in Quintal

    validateField("estimated_value", "Please Enter Estimated value (in Rs.)."); // Validate report date
    validateField("rpl_remarks_", "Please Enter Remark."); // Validate remarks

    validateField(
      "agmark_replica_from",
      "Please Enter No. of Agmark Replica From."
    ); // Validate remarks
    validateField(
      "agmark_replica_to",
      "Please Enter No. of Agmark Replica To."
    ); // Validate remarks
    validateField(
      "agmark_replica_total",
      "Please Enter No. of Agmark Replica Total."
    ); // Validate remarks

    validateField("replica_charges", "Please Enter Replica Charges."); // Validate remarks
    if (labNablAccreditedInput.trim() !== "") {
      validateField("laboratory_name", "Please Select laboratory.");
      validateField("report_no", "Please Enter Report no.");
      validateField("report_date", "Please Select Report Date.");
      validateField("remarks", "Please Enter Remark.");
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
        commodity: $("#ta-commodity-").val(),
        lot_no_tf_no_m_no: $("#lot_no_tf_no_m_no").val(),
        date_of_sampling: $("#date_of_sampling").val(),
        date_of_packing: $("#date_of_packing").val(),
        grade: $("#grade").val(),
        packet_size: $("#ta-packet_size-").val(),
        packet_size_unit: $("#ta-packet_size_unit-").val(),
        no_of_packets: $("#ta-no_of_packets-").val(),
        total_qty_graded_quintal: $("#total_qty_graded_quintal").val(),
        estimated_value: $("#estimated_value").val(),
        agmark_replica_from: $("#agmark_replica_from").val(),
        agmark_replica_to: $("#agmark_replica_to").val(),
        agmark_replica_total: $("#agmark_replica_total").val(),
        replica_charges: $("#replica_charges").val(),
        laboratory_name: $("#laboratory_name").val(),
        report_no: $("#report_no").val(),
        report_date: $("#report_date").val(),
        remarks: $("#remarks").val(),
        record_id: $("#record_id").val(),
        // Add more fields as needed
      };

      $.ajax({
        url: "../AjaxFunctions/add_bgr_details",
        type: "POST",
        data: formData,
        success: function (response) {
          if (response == "added") {
            // Handle success response here
            renderToast("success", "Data inserted successfully!");
          } else if (response === "updated") {
            // Handle success response here
            renderToast("success", "Data Updated successfully!");
          }

          location.reload(); // Reload the page
          // Optionally, you might want to reset form fields here
        },
        error: function (error) {
          // Handle error response here
          renderToast("error", "Error inserting data.");
        },
      });
    }
  });

  $(".edit_bgr_id").click(function (e) {
    e.preventDefault();
    $("#update_bgr_details").show(); // Show Edit button
    $("#add_bgr_details").hide();

    var id = $(this).attr("id");

    $.ajax({
      url: "../AjaxFunctions/edit_bgr_details",
      method: "POST",
      dataType: "json",
      data: { id: id }, // send ID in the request data
      beforeSend: function (xhr) {
        // Add this line
        xhr.setRequestHeader("X-CSRF-Token", $('[name="_csrfToken"]').val());
      },
      success: function (response) {
        console.log(response);
        const {
          id,
          commodity,
          lotno,
          datesampling,
          dateofpacking,
          gradeasign,
          packetsize,
          packetsizeunit,
          totalnoofpackets,
          totalqtyquintal,
          estimatedvalue,
          agmarkreplicafrom,
          agmarkreplicato,
          agmarkreplicatotal,
          replicacharges,
          laboratoryname,
          reportno,
          reportdate,
          remarks,
        } = response;

        $("#record_id").val(id);
        $("#update_bgr_details").val(id);
        $("#ta-commodity-").val(commodity);
        $("#lot_no_tf_no_m_no").val(lotno);
        $("#date_of_sampling").val(datesampling);
        $("#date_of_packing").val(dateofpacking);
        $("#grade").val(gradeasign);
        $("#ta-packet_size-").val(packetsize);
        $("#ta-packet_size_unit-").val(packetsizeunit);

        $("#ta-no_of_packets-").val(totalnoofpackets);
        $("#total_qty_graded_quintal").val(totalqtyquintal);
        $("#estimated_value").val(estimatedvalue);
        $("#agmark_replica_from").val(agmarkreplicafrom);
        $("#agmark_replica_to").val(agmarkreplicato);
        $("#agmark_replica_total").val(agmarkreplicatotal);
        $("#replica_charges").val(replicacharges);
        $("#laboratory_name").val(laboratoryname);
        $("#report_no").val(reportno);
        $("#report_date").val(reportdate);
        $("#remarks").val(remarks);
      },
      error: function (error) {
        // Handle any errors that occur during the AJAX request
        console.error("Error:", error);
      },
    });
    updateOverallTotalCharges();
  });
});
