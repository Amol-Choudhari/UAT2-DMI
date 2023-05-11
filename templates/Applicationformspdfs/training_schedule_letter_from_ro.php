<?php // added new template file for chemist training approval certificate pdf by laxmi Bhadade on 10-1-2023 ?>
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
            	  <?php echo $chemist_fname."&nbsp;". $chemist_lname ;?>, <br>
                  <?php echo $customer_firm_data['firm_name']; ?>,<br>
                  <?php echo $customer_firm_data['street_address']; ?>,<br>
                  <?php echo $firm_district_name;?>,
                  <?php echo $firm_state_name; ?> – <?php echo $customer_firm_data['postal_code']; ?>
            </td>
        </tr>

        <tr>    
            <td><br>Subject: Chemist Training Scheduled of  <?php echo $chemist_fname."&nbsp;". $chemist_lname ;?> in the analysis of <?php echo $sub_commodities_list; ?>.</td>
        </tr>
          <br>


        <tr>
            <td><br>Dear Sir,</td><br>
        </tr>   

        <tr>
            <td>I am to refer to above cited subject & inform that the chemist <b> <?php echo $chemist_fname."&nbsp;". $chemist_lname ;?> </b> for the firm <b><?php echo $customer_firm_data['firm_name']; ?>, <?php echo $customer_firm_data['street_address']; ?> </b> has sponsored for training in <?php echo $sub_commodities_list; ?> to be graded under Agmark.<br>
			
			In this connection it is requested to <b><?php echo $chemist_fname."&nbsp;". $chemist_lname ;?> </b> to impart necessary training from <?php echo $schedule_from;?> to <?php echo $shedule_to;?> in RO/SO Office <?php echo $ro_office; ?>.<br>
           The training has been scheduled from  <?php echo $schedule_from;?> to <?php echo $shedule_to;?> in RO/SO Office <?php echo $ro_office; ?>.
	
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
				RO/SO Office.<br>
			</td>
		</tr>
		<tr>
            <td><br></td>
        </tr>
	</table>


	


    
	
	
        