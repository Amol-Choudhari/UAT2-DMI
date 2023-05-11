<?php // chemist application pdf template file created by laxmi BHADADE ON 10-1-2023 ?>
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
        <tr><td align="center" style="padding:5px;"><h4>Letter from RO To schedule Training</h4></td></tr>
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
            	
                   The Senior Chemist,<br>
                   Regional Agmark Laboratory,<br>
                   <?php echo $ral_office_address; ?> <br> 
            </td>
        </tr>

        <tr>    
            <td><br>Subject: Impart training to <?php echo $chemist_fname."&nbsp;". $chemist_lname ;?>, <?php echo $firmName; ?>, <?php echo $firm_address; ?> for analysis of <?php echo $sub_commodities_list; ?> to be graded under Agmark-reg.</td>
        </tr>
                    
        <tr>
            <td><br>Dear Sir,</td><br>
        </tr>   

        <tr>
            <td>I am to refer to above cited subject & inform that <?php echo $firmName; ?>, <?php echo $firm_address; ?> has sponsored to <?php echo $chemist_fname."&nbsp;". $chemist_lname ;?> for training in <?php echo $sub_commodities_list; ?> to be graded under Agmark.<br>
			
			The training charges of Rs. <?php echo $charges; ?> & necessary documents have been submitted in
            Regional office, [RO/SO Office]. In this connection it is requested to impart necessary
            training to  <?php echo $chemist_fname."&nbsp;". $chemist_lname ;?> for analysis, grading, marketing of <?php echo $sub_commodities_list; ?> to be
            	graded under Agmark.<br>
           The training has been scheduled from the <?php echo $schedule_from;?> to <?php echo $schedule_to;?>.
	
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
				RO/SO Office.<br>
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
			1. <?php echo $firmName; ?>, <?php echo $firm_address; ?> with this instruction to depute your chemist for necessary
                training in Regional Agmark Laboratory <?php echo $ral_office; ?>.<br>
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
				Directorate of Marketing and Inspection<br> 
				RO/SO Office.<br>
			</td>
		</tr>
		<tr>
            <td><br></td>
        </tr>
	</table>


    
	
	
        