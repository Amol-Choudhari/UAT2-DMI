<!-- Chemist application pdf template new file added by laxmi Bhadade on 12-12-2022 -->
<?php ?>
<style>
	h4 {
		padding: 5px;
		font-family: times;
		font-size: 13pt;					
	}
							 

	table{
		padding: 5px;
		font-size: 12pt;
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
		<td align="center" style="padding:5px;">		
			<h4> Chemist Application Form </h4>
		</td>
		</tr>
</table>

<table width="100%"><br><br>	
		<tr>
			<td><br>To,</td>
		</tr>	
</table>	
<table  width="100%">
    <tr>
        <td>
         The Asstt. Agriculture Marketing Adviser,<br>
         Directorate of Marketing & Inspection,<br>
         RO/SO Office,<br>
         <?php echo $ro_office_address ?>.<br><br>
        </td>
	 </tr>

    <tr>
		<td><br>Subject: Application for approval of chemist for grading and marking of <?php echo $sub_commodities_list;?> under Agmark.</td><br><br>
	</tr>

    <tr>
		<td><br>Dear Sir,</td><br>
	</tr>

    <tr>
		<td><br>I am to inform that,<br>
            I am chemist, <?php echo $fname. "&nbsp;". $lname ;?> who has undergone necessary training in the analysis and grading of <?php echo $sub_commodities_list;?> under Agmark at the Regional Agmark Laboaratory.<br>
            Please approved the pplication and permitted to take up the work
            relating to the analysis, grading and marking of <?php echo $sub_commodities_list;?> under Agmark in
            accordance with the provisions in Agriculture Produce (Grading and Marking) Act, 1937,
            <?php echo $sub_commodities_list;?> Grading & Marking (Amendment) Rules and the instructions
            issued in this connection from time to time by Agriculture Marketing Adviser to the Govt. of
            India</td>

	  
      <td>
	         I will be responsible, for safe custody of labels, replica bearing
            containers, maintenance of label account and label charges accounts, submission of regular
            It may be noted that as per the relevant instructions, the services of the approved I
            the Government of India or any person duly authorized by me.
	  </td>  
    </tr>
       
      </table>

      <table>	
	    <tr>
		<td align="right"><br><strong>Your’s faithfully</strong><br>
                           <?php echo $firm_name;?><br>
                           <?php echo $firm_address; ?><br>
				               <?php echo $district; ?>,<?php echo $state; ?> - <?php echo $pin_code;?>	<br>			
		</td>
	   </tr>
      </table>	
     
      <table>
      	<tr>
	   	<td>
	   		Copy to:<br>
            1.The Agriculture Marketing Adviser to the Govt. of India, DMI, Head Office, Faridabad for
              favour of information.<br>
            2.<?php echo $fname. "&nbsp;". $lname ;?>, <?php echo $chemist_address; ?>, for necessary action.
	   	</td>
	   </tr>
      </table>