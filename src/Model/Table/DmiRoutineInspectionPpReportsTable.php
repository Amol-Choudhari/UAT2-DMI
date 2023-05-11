<?php

	namespace app\Model\Table;
	use Cake\ORM\Table;
	use App\Model\Model;
	use App\Controller\AppController;
	use App\Controller\CustomersController;
	use Cake\ORM\TableRegistry;
	use Cake\Datasource\ConnectionManager;

class DmiRoutineInspectionPpReportsTable extends Table{

	var $name = "DmiRoutineInspectionPpReports";

	public $validate = array(

			'referred_back_comment'=>array(
					'rule'=>array('maxLength',200),
				),
			'io_reply'=>array(
					'rule'=>array('maxLength',200),
				),


	);



	public function sectionFormDetails($customer_id)
	{
		$latest_id = $this->find('list', array('valueField'=>'id', 'conditions'=>array('customer_id IS'=>$customer_id)))->toArray();

		if($latest_id != null){
			$report_fields = $this->find('all', array('conditions'=>array('id'=>MAX($latest_id))))->first();
			$form_fields_details = $report_fields;

		}else{
			$form_fields_details = Array (  'id' =>"", 'customer_id' =>"",'date_last_inspection'=>"",'date_p_inspection'=>"",
			'email'=>"",'mobile_no'=>"",'packaging_material'=>"",'valid_upto'=>"",'street_address'=>"",
			'registered_office'=>"",'press_premises'=>"",'physical_check'=>"",'is_printing'=>"",'storage_facilities'=>"",'lab_properly_equipped'=>"",'maintains_proper'=>"",'right_quality_of_printing'=>"",'press_is_marking_logo'=>"",'last_insp_suggestion'=>"",'short_obserd'=>"",'if_any_sugg'=>"",'signature'=>"",'signature_name'=>"",'io_reply_once_no' =>"", 'user_email_id' =>"",'user_once_no' =>"", 'referred_back_comment' =>"", 'referred_back_date' =>"", 'io_reply' =>"", 'io_reply_date' =>"", 'form_status' =>"",'approved_date' =>"",'referred_back_by_email' =>"", 'referred_back_by_once' =>"", 'current_level' =>"", 'constituent_oil_mill_docs' =>"", 'separate_pipe_lines' =>"no", 'delete_ro_referred_back' =>"");

		}


		$user_email_id = $_SESSION['username'];

		$DmiCaPpLabMapings = TableRegistry::getTableLocator()->get('DmiCaPpLabMapings');
		$DmiFirms = TableRegistry::getTableLocator()->get('DmiFirms');

		$conn = ConnectionManager::get('default');

		$users = "SELECT
				tbl.customer_id, dff.firm_name,dff.sub_commodity,tbl.tbl_name
				FROM dmi_firms AS df
				INNER JOIN dmi_ca_pp_lab_mapings AS map ON map.pp_id=df.id::varchar
				INNER JOIN dmi_firms AS dff ON dff.customer_id = map.customer_id
				INNER JOIN dmi_all_tbls_details AS tbl ON tbl.customer_id = map.customer_id
				WHERE df.customer_id = '$customer_id'";


		$q = $conn->execute($users);

		$all_packers_records = $q->fetchAll('assoc');
  
	  $MCommodity = TableRegistry::getTableLocator()->get('MCommodity');

	  $i=0;
		$all_packers_value=array();
		
		foreach($all_packers_records as $value) // use for show list of CA id's
		{
			$all_packers_value[$i]['customer_id'] = $value['customer_id'];
			$all_packers_value[$i]['firm_name'] = $value['firm_name'];
			
			
      $Dmi_grant_certificates_pdfs = TableRegistry::getTableLocator()->get('DmiGrantCertificatesPdfs');
		  $get_last_grant_date = $Dmi_grant_certificates_pdfs->find('all',array('conditions'=>array('customer_id IS'=>$value['customer_id']),'order'=>array('id desc')))->first();
     
		  $last_grant_date = $get_last_grant_date['date'];
      
			$CustomersController = new CustomersController;
		  $certificate_valid_upto = $CustomersController->Customfunctions->getCertificateValidUptoDate($value['customer_id'],$last_grant_date);
     
			$all_packers_value[$i]['validupto'] = $certificate_valid_upto;
			
      $DmiAllTblsDetails = TableRegistry::getTableLocator()->get('DmiAllTblsDetails');
			
			$tbl_list = $DmiAllTblsDetails->find('list',array('keyField'=>'tbl_code','valueField'=>'tbl_name', 'conditions'=>array('customer_id IN'=>$value['customer_id'])))->toList();

			$all_packers_value[$i]['tbl_name'] = $tbl_list;
     
			$sub_commodity_value = $MCommodity->find('list',array('keyField'=>'commodity_code','valueField'=>'commodity_name', 'conditions'=>array('commodity_code IN'=>explode(',',$value['sub_commodity']))))->toList();
			$all_packers_value[$i]['sub_commodity'] = $sub_commodity_value;

			$i=$i+1;
		}
  
		$firm_data = $DmiFirms->find('all',array('keyField'=>'commodity_code','valueField'=>'commodity_name', 'conditions'=>array('customer_id IN'=> $customer_id)))->toArray();
    //pr($firm_data);die;
		$firm_id = $firm_data[0]['id'];
	
		$find_ca_list = $DmiCaPpLabMapings->find('list',array('keyField'=>'customer_id','valueField'=>'customer_id', 'conditions'=>array('pp_id'=>$firm_id)))->toArray();
   //pr($find_ca_list);die;
	

		$DmiRtiPackerDetails = TableRegistry::getTableLocator()->get('DmiRtiPackerDetails');
    $packaging_details = $DmiRtiPackerDetails->packagingDetails();	
	
	  $added_packaging_details = $packaging_details[1];
   
		return array($form_fields_details,$added_packaging_details,$find_ca_list,$all_packers_value,);
	}


	public function saveFormDetails($customer_id,$forms_data){

		$CustomersController = new CustomersController;

		$ca_bevo_applicant = $CustomersController->Customfunctions->checkCaBevo($customer_id);

		$Dmi_flow_wise_tables_list = TableRegistry::getTableLocator()->get('DmiFlowWiseTablesLists');
		$final_submit_table = $Dmi_flow_wise_tables_list->getFlowWiseTableDetails($_SESSION['application_type'],'inspection_report');

		$Dmi_siteinspection_final_report = TableRegistry::getTableLocator()->get($final_submit_table);
		$report_final_status = $Dmi_siteinspection_final_report->siteinspectionFinalReportStatus($customer_id);


		$message_id = 1;
		$current_level = $_SESSION['current_level'];

		$section_form_details = $this->sectionFormDetails($customer_id);

		if(!empty($section_form_details[0]['id'])){

			$message_id = 2;

			if(isset($forms_data['io_reply'])){$io_reply = $forms_data['io_reply']; }else{ $io_reply = null; }
			if(isset($report_final_status['status'])){$reportFinalStatusValue = $report_final_status['status']; }else{ $reportFinalStatusValue = null; }

			if($current_level == 'level_2' && empty($io_reply) && $reportFinalStatusValue == 'referred_back'){

				return 4;  //error "comment required"
			}

			if($current_level == 'level_2' && !empty($io_reply) && $reportFinalStatusValue == 'referred_back'){

				$message_id = 3;
			}

		}

		//html encoding post data before saving
		    $htmlencoded_date_last_inspection = htmlentities($forms_data['date_last_inspection'], ENT_QUOTES);
				$htmlencoded_date_p_inspection = htmlentities($forms_data['date_p_inspection'], ENT_QUOTES);
				$htmlencoded_email = htmlentities($forms_data['email'], ENT_QUOTES);
				$htmlencoded_mobile_no = htmlentities($forms_data['mobile_no'], ENT_QUOTES);
				$htmlencoded_packaging_material = htmlentities($forms_data['packaging_material'], ENT_QUOTES);
				$htmlencoded_valid_upto = htmlentities($forms_data['valid_upto'], ENT_QUOTES);
				$htmlencoded_street_address = htmlentities($forms_data['street_address'], ENT_QUOTES);
				$htmlencoded_registered_office = htmlentities($forms_data['registered_office'], ENT_QUOTES);
				$htmlencoded_press_premises = htmlentities($forms_data['press_premises'], ENT_QUOTES);
				$htmlencoded_physical_check = htmlentities($forms_data['physical_check'], ENT_QUOTES);
				$htmlencoded_is_printing = htmlentities($forms_data['is_printing'], ENT_QUOTES);
				$htmlencoded_storage_facilities = htmlentities($forms_data['storage_facilities'], ENT_QUOTES);
				$htmlencoded_lab_properly_equipped = htmlentities($forms_data['lab_properly_equipped'], ENT_QUOTES);
				$htmlencoded_maintains_proper = htmlentities($forms_data['maintains_proper'], ENT_QUOTES);
				$htmlencoded_right_quality_of_printing = htmlentities($forms_data['right_quality_of_printing'], ENT_QUOTES);
				$htmlencoded_press_is_marking_logo = htmlentities($forms_data['press_is_marking_logo'], ENT_QUOTES);
				$htmlencoded_short_obserd = htmlentities($forms_data['short_obserd'], ENT_QUOTES);
				$htmlencoded_if_any_sugg = htmlentities($forms_data['if_any_sugg'], ENT_QUOTES);
        $htmlencoded_last_insp_suggestion = htmlentities($forms_data['last_insp_suggestion'],ENT_QUOTES);

      // pr($forms_data);die;
				if(!empty($forms_data['signature']->getClientFilename())){

				$file_name = $forms_data['signature']->getClientFilename();
				$file_size = $forms_data['signature']->getSize();
				$file_type = $forms_data['signature']->getClientMediaType();
				$file_local_path = $forms_data['signature']->getStream()->getMetadata('uri');

				$signature = $CustomersController->Customfunctions->fileUploadLib($file_name,$file_size,$file_type,$file_local_path); // calling file uploading function

			}else{
				$signature = '';
			}


			if(!empty($forms_data['signature_name']->getClientFilename())){

				$file_name = $forms_data['signature_name']->getClientFilename();
				$file_size = $forms_data['signature_name']->getSize();
				$file_type = $forms_data['signature_name']->getClientMediaType();
				$file_local_path = $forms_data['signature_name']->getStream()->getMetadata('uri');

				$signature_name = $CustomersController->Customfunctions->fileUploadLib($file_name,$file_size,$file_type,$file_local_path); // calling file uploading function

			}else{
				$signature_name = '';
			}

		if(!empty($report_final_status)){
			if($report_final_status['status'] == 'referred_back' && !empty($forms_data['io_reply'])){

				$htmlencoded_io_reply = htmlentities($forms_data['io_reply'], ENT_QUOTES);

				if(!empty($forms_data['ir_comment_ul']->getClientFilename())){

					$file_name = $forms_data['ir_comment_ul']->getClientFilename();
					$file_size = $forms_data['ir_comment_ul']->getSize();
					$file_type = $forms_data['ir_comment_ul']->getClientMediaType();
					$file_local_path = $forms_data['ir_comment_ul']->getStream()->getMetadata('uri');

					$ir_comment_ul = $CustomersController->Customfunctions->fileUploadLib($file_name,$file_size,$file_type,$file_local_path); // calling file uploading function

				}else{ $ir_comment_ul = null; }

				$ioReplyEntity = $this->newEntity(array(
					'id'=>$section_form_details[0]['id'],
					'io_reply_once_no'=>$_SESSION['once_card_no'],
					'io_reply_date'=>date('Y-m-d H:i:s'),
					'io_reply'=>$htmlencoded_io_reply,
					'ir_comment_ul'=>$ir_comment_ul,
					'current_level'=>'level_3',
					'created'=>date('Y-m-d H:i:s'),
					'modified'=>date('Y-m-d H:i:s')
				));

				$this->save($ioReplyEntity);
			}
		}

		$formSavedEntity = $this->newEntity(array(
			'id'=>$section_form_details[0]['id'],
			'customer_id'=>$customer_id,
			'user_email_id'=>$_SESSION['username'],
			'user_once_no'=>$_SESSION['once_card_no'],
			'date_last_inspection'=>$htmlencoded_date_last_inspection,
			'date_p_inspection'=>$htmlencoded_date_p_inspection,
			'email'=>$htmlencoded_email,
			'mobile_no'=>$htmlencoded_mobile_no,
			'packaging_material'=>$htmlencoded_packaging_material,
			'valid_upto'=>$htmlencoded_valid_upto,
			'street_address'=>$htmlencoded_street_address,
			'registered_office'=>$htmlencoded_registered_office,
			'press_premises'=>$htmlencoded_press_premises,
			'last_insp_suggestion'=>$htmlencoded_last_insp_suggestion,
			'physical_check'=>$htmlencoded_physical_check,
			'is_printing'=>$htmlencoded_is_printing,
			'storage_facilities'=>$htmlencoded_storage_facilities,
			'lab_properly_equipped'=>$htmlencoded_lab_properly_equipped,
			'maintains_proper'=>$htmlencoded_maintains_proper,
			'right_quality_of_printing'=>$htmlencoded_right_quality_of_printing,
			'press_is_marking_logo'=>$htmlencoded_press_is_marking_logo,
			'short_obserd'=>$htmlencoded_short_obserd,
			'if_any_sugg'=>$htmlencoded_if_any_sugg,
      'signature'=>$signature,
			'signature_name'=>$signature_name,
			'form_status'=>'saved',
			'created'=>date('Y-m-d H:i:s'),
			'modified'=>date('Y-m-d H:i:s')
		));
		if($this->save($formSavedEntity)){ return $message_id; }else{ $message_id = ""; return $message_id; }
	}


	public function saveReferredBackComment($customer_id,$report_details,$reffered_back_comment,$rb_comment_ul){

		$formSavedEntity = $this->newEntity(array(
			'customer_id'=>$customer_id,
			'user_email_id'=>$report_details['user_email_id'],
			'user_once_no'=>$report_details['user_once_no'],
			'date_last_inspection'=>$report_details['date_last_inspection'],
			'date_p_inspection'=>$report_details['date_p_inspection'],
			'registered_office'=>$report_details['registered_office'],
			'press_premises'=>$report_details['press_premises'],
			'physical_check'=>$report_details['physical_check'],
			'is_printing'=>$report_details['is_printing'],
			'storage_facilities'=>$report_details['storage_facilities'],
			'lab_properly_equipped'=>$report_details['lab_properly_equipped'],
			'maintains_proper'=>$report_details['maintains_proper'],
			'right_quality_of_printing'=>$report_details['right_quality_of_printing'],
			'press_is_marking_logo'=>$report_details['press_is_marking_logo'],
			'last_insp_suggestion'=>$report_details['last_insp_suggestion'],
			'short_obserd'=>$report_details['short_obserd'],
			'if_any_sugg'=>$report_details['if_any_sugg'],
			'signature'=>$report_details['signature'],
			'signature_name'=>$report_details['signature_name'],
			'email'=>$report_details['email'],
			'mobile_no'=>$report_details['mobile_no'],
			'packaging_material'=>$report_details['packaging_material'],
			'valid_upto'=>$report_details['valid_upto'],
			'street_address'=>$report_details['street_address'],
			'referred_back_comment'=>$reffered_back_comment,
			'rb_comment_ul'=>$rb_comment_ul,
			'referred_back_date'=>date('Y-m-d H:i:s'),
			'referred_back_by_email'=>$_SESSION['username'],
			'referred_back_by_once'=>$_SESSION['once_card_no'],
			'form_status'=>'referred_back',
			'current_level'=>$_SESSION['current_level'],
			'created'=>date('Y-m-d H:i:s'),
			'modified'=>date('Y-m-d H:i:s')
		));
		if($this->save($formSavedEntity)){

			return 1;
		}else{

			return 0;
		}

	}

}

?>
