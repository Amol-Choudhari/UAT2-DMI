<?php // NEW FILE FOR CHEMIST TRAINING APPROVAL CERTIFICATE TEMPLATE ADDED BY LAXMI BHADADE ON 10-01-23 ?>
<style>
    h4 {
        padding: 5px;
        font-family: times;
        font-size: 12pt;
    }

    table{
        padding: 5px;
        font-size: 10pt;
        font-family: times;
    }
</style>
<?php
    $i=0;
    $sub_commodities_array = array();   
    foreach($sub_commodity_data as $sub_commodity){
        
        $sub_commodities_array[$i] = $sub_commodity['commodity_name'];
    $i=$i+1;
    } 
    
    $sub_commodities_list = implode(',',$sub_commodities_array);
?>

	<table width="100%" border="1">		
		<tr>					
			<td width="12%" align="center">
				<img width="35" src="img/logos/emblem.png">
			</td>
			<td width="76%" align="center">
				<h4>Government of India <br> Ministry of Agriculture and Farmers Welfare<br>
				Department of Agriculture & Farmers Welfare<br>
				Directorate of Marketing & Inspection</h4>
				
			</td>
			<td width="12%" align="center">
				<img src="img/logos/agmarklogo.png">
			</td>				
		</tr>
	</table>

    <table width="100%" border="1">
        <tr><td align="center" style="padding:5px;"><h4>Certificate of Approval to Chemist Training</h4></td></tr>
    </table>

    <table width="100%" border="1">
        <tr><td>Applicant Id: <?php echo $customer_id; ?></td>
            <td align="right">Date: <?php echo date('d/m/Y'); ?></td>
        </tr>
    </table>

    <table width="100%">
        <tr><td></td></tr>
        <tr>
            <td><br>To,</td><br>
        </tr>   
    </table>

    <table  width="100%">
        <tr>
            <td> <?php echo $customer_firm_data['firm_name']; ?>,<br>
                <?php echo $customer_firm_data['street_address']; ?>,<br>
                 <?php echo $firm_district_name;?>,
                  <?php echo $firm_state_name; ?> – <?php echo $customer_firm_data['postal_code']; ?>

            </td>
        </tr>

        <tr>    
            <td><br>Subject: Application for approval of chemist for grading and marking of <?php echo $sub_commodities_list;?> under Agmark.</td>
        </tr>
                    
        <tr>
            <td><br>Dear Sir,</td><br>
        </tr>   

        <tr>
            <td>I am to inform that,<br>
               Your chemist, <?php echo $chemist_fname;?> <?php echo $chemist_lname;?> who has undergone necessary training in the analysis and grading of <?php echo $sub_commodities_list;?> under Agmark at the Regional Agmark Laboaratory, <?php echo $ro_first_name;?> <?php echo $ro_last_name;?> for the period from <?php echo $schedule_from;?> to <?php echo $shedule_to;?> and procedural training in sampling, grading, packing and maintainance of records at the Regional Office, DMI, <?php echo $ro_office;?> from the period from <?php echo $ro_schedule_from;?>  to <?php echo $ro_shedule_to;?> is hereby approved as chemist and permitted to take up the work relating to the analysis, grading and marking of <?php echo $sub_commodities_list;?> under Agmark in accordance with the provisions in Agriculture Produce (Grading and Marking) Act, 1937,
               <?php echo $sub_commodities_list;?> Grading & Marking (Amendment) Rules, [year] and the instructions issued in this connection from time to time by Agriculture Marketing Adviser to the Govt. of India.<br>

               <?php echo $chemist_fname;?> <?php echo $chemist_lname;?> chemist shall be responsible, for safe custody of labels, replica bearing containers, maintenance of label account and label charges accounts, submission of regular monthly returns etc in the absence of chemist In-charge.<br>

               It may be noted that as per the relevant instructions, the services of the approved chemist shall not be dispensed with without prior consent of the Agriculture Marketing Adviser to the Government of India or any person duly authorized by him.<br>	
			</td>
        </tr>
                    
        <tr>
            <td><br></td>
        </tr>
              
    </table>


	<br>
    <table align="right">	
					
		<tr>
			<td>Your’s faithfully<br> 
				<?php echo $ro_fname;?>  <?php echo $ro_lname;?>,<br> <?php echo $role;?><br> 
				<?php echo $ro_office; ?>.<br>
			</td>
		</tr>
	</table>
	<table width="100%">
  <tr>
  	<td>
  		Copy to:<br>
  		1.The Agriculture Marketing Adviser to the Govt. of India, DMI, Head Office, Faridabad for favour of information.<br>
  		2.<?php echo $chemist_fname;?> <?php echo $chemist_lname;?>, <?php echo $chemist_address;?>, for necessary action.<br>

  	</td>
  </tr>
   <table>

	
	
        