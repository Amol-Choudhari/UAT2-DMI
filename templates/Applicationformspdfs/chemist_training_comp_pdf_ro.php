<?php //chemist training completed at Ro side pdf letter added new template file by laxmi on 25-12-2022 ?>
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
<!-- for multiple commodities added by laxmi on 10-1-2023 -->
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
        <tr><td align="center" style="padding:5px;"><h4>Letter from RO to Completion of  Training</h4></td></tr>
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
            <td>  
            	
                  <?php echo $firmName; ?>,<br>
                    <?php echo $firm_address; ?>,<br>
                   <?php echo $district?>, <?php echo $state?> – <?php echo $pin_code?> <br> 
            </td>
        </tr>

        <tr>    
            <td><br>Subject: Relieving of <?php echo $chemist_fname."&nbsp;". $chemist_lname ;?> of <?php echo $firmName; ?>  <?php echo $firm_address; ?> after completion of training for analysis of  <?php echo $sub_commodities_list; ?> to be graded under Agmark –reg.</td>
        </tr>
                    
        <tr>
            <td><br>Dear Sir,</td><br>
        </tr>   

        <tr>
            <td>I am to refer to above cited subject & inform that of <?php echo $chemist_fname."&nbsp;". $chemist_lname ;?> sponsored chemist undergone analysis training in Regioanl Agmark Laboratory, <?php echo $ro_office;?> from <?php echo $schedule_from;?> to <?php echo $schedule_to;?> for analysis, grading, marking of <?php echo $sub_commodities_list; ?> to be graded under Agmark.<br>
			
			He/she is relieved from the Regional office, <?php echo $ro_office;?> with this request to join his duty at <?php echo $firmName; ?> <?php echo $firm_address; ?>. Send a copy of his joining report in this office, so that a approval letter of <?php echo $chemist_fname."&nbsp;". $chemist_lname ;?> may be issued as early as possible<br>
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
				 <?php echo $ro_fname."&nbsp;". $ro_lname ."<br>".$role ;?><br>
				Directorate of Marketing and Inspection<br> 
				<?php echo $ro_office;?><br>
				RO/SO Office .<br>
			</td>
		</tr>
		<tr>
            <td><br></td>
        </tr>
	</table>


	<br>

	<table align="left">	
					
		<tr>
			<td>Copy to:<br> 
			1.<?php echo $chemist_fname."&nbsp;". $chemist_lname ;?> of <?php echo $firmName; ?>, <?php echo $firm_address; ?> with this instruction to join your duty at
           above unit & send a copy of your joining report, so that an approval letter may be issued for analysis, grading, marking of <?php echo $sub_commodities_list; ?> to be graded under Agmark.<br>
			</td>
		</tr>
		<tr>
            <td><br></td>
        </tr>
	</table>

	<br>
	<table align="right">	
					
		<tr>
			<td>
				 <?php echo $ro_fname."&nbsp;". $ro_lname ."<br>".$role ;?><br>
				Directorate of Marketing and Inspection <br><?php echo $ro_office;?><br> 
				RO/SO Office.<br>
			</td>
		</tr>
		<tr>
            <td><br></td>
        </tr>
	</table>


    
	
	
        