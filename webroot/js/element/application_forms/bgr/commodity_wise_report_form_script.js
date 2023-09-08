$(document).ready(function () {
  const form_status = $("#status").val();
  var application_mode = $("#application_mode").val();
  if (application_mode == "view") {
    $("#section_form_id :input").prop("disabled", false);
    $(document).ready(function () {
      $(".glyphicon-edit").css("display", "none");
      $(".glyphicon-remove-sign").css("display", "none");
    });
  }

  if (form_status === "Granted") {
    $("#comment_reply_box").hide();
  } else {
    $("#date_of_sampling").datepicker({
      format: "dd/mm/yyyy",
      autoclose: true,
    });
    $("#date_of_packing").datepicker({
      format: "dd/mm/yyyy",
      autoclose: true,
    });

    $("#report_date").datepicker({
      format: "dd/mm/yyyy",
      autoclose: true,
    });

    $current_level = $("#current_level").val();
    if ($current_level === "level_3") {
      $("#form_inner_main .glyphicon-edit").css("display", "none");
      $("#form_inner_main .glyphicon-remove-sign").css("display", "none");
      $("#form_inner_main #add_new_row").css("display", "none");
    }
    $("#dataTable").on("change", ".commodity", function () {
      let id_No = this.id.split("-"); // Corrected method name from splite() to split()

      id_No = id_No[2];

      let commodity_id = $("#ta-commodity-" + id_No).val();
      console.log(commodity_id);
      $.ajax({
        type: "POST",
        url: "../AjaxFunctions/get_commodity_wise_charge",
        data: { commodity_id: commodity_id },
        beforeSend: function (xhr) {
          xhr.setRequestHeader("X-CSRF-Token", $('[name="_csrfToken"]').val());
        },
        success: function (response) {
          //
          console.log(response);
          if (response === "No Charge") {
            // $("#replica_charges" + id_No).val("");
            alert("Please Select Comodity");
            // location.reload();
          } else {
            var response = response.match(/~([^']+)~/)[1]; //getting data bitween ~..~ from response
            response = JSON.parse(response); //response is JSOn encoded to parse JSON
            // Assuming response is a valid JSON object containing the "unit" property
            //$("#ta-label_charge-" + id_No).val(response["charge"]);

            let unit_list = response["unit_list"];
            let unit_option = "<option value=''>--Select--</option>";
            $.each(unit_list, function (index, value) {
              unit_option +=
                "<option value='" + value + "'>" + value + "</option>";
            });

            // Assuming id_No is set correctly and refers to the correct element ID
            $("#ta-packet_size_unit-" + id_No).html(unit_option);
            $("#replica_charges" + id_No).val(response["charge"]);
          }
        },
      });
    });

    // if lab is not NABL Accredited then dissabled the field
    const labNablAccreditedInput = document.getElementById(
      "lab_nabl_accredited"
    ).value;

    const reportNoInput = document.getElementById("report_no");
    const reportDateInput = document.getElementById("report_date");
    const remarksInput = document.getElementById("remarks");
    const laboratorynameInput = document.getElementById("laboratory_name");
    const rpl_reportno = document.getElementsByClassName("rpl_reportno");
    if (labNablAccreditedInput === "" || labNablAccreditedInput === null) {
      reportNoInput.style.display = "none";
      reportDateInput.style.display = "none";
      remarksInput.style.display = "none";
      laboratorynameInput.style.display = "none";
      rpl_reportno.style.display = "none";
    } else {
      reportNoInput.style.display = "block"; // Or "initial" depending on your CSS
      reportDateInput.style.display = "block"; // Or "initial" depending on your CSS
      remarksInput.style.display = "block"; // Or "initial" depending on your CSS
      laboratorynameInput.style.display = "block";
    }
  }

  $("#downloadButton").click(function (e) {
    e.preventDefault();
    alert();
  });
});
