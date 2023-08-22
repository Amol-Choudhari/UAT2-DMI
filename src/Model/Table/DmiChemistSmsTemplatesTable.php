<?php
namespace app\Model\Table;
use Cake\ORM\Table;
use App\Model\Model;
use App\Controller\AppController;
use App\Controller\CustomersController;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Filesystem\File;
use Cake\Routing\Router;
use Cake\Utility\Text;

class DmiChemistSmsTemplatesTable extends Table{

	var $name = "DmiChemistSmsTemplates";
	
	public function sendMessage($message_id, $customer_id) {
		
		$application_type = 4;
	
		//Load Models
		$DmiFlowWiseTablesLists = TableRegistry::getTableLocator()->get('DmiFlowWiseTablesLists');
		$DmiFinalSubmitTable = $DmiFlowWiseTablesLists->find('all',array('conditions'=>array('application_type IS'=>$application_type)))->first();
		
		$DmiCustomers = TableRegistry::getTableLocator()->get('DmiCustomers');
		$DmiFirms = TableRegistry::getTableLocator()->get('DmiFirms');
		$DmiRoOffices = TableRegistry::getTableLocator()->get('DmiRoOffices');
		$DmiUsers = TableRegistry::getTableLocator()->get('DmiUsers');
		$DmiUserRoles = TableRegistry::getTableLocator()->get('DmiUserRoles');
		$DmiSentSmsLogs = TableRegistry::getTableLocator()->get('DmiSentSmsLogs');
		$DmiSentEmailLogs = TableRegistry::getTableLocator()->get('DmiSentEmailLogs');
		$DmiPaoDetails = TableRegistry::getTableLocator()->get('DmiPaoDetails');
		$DmiChemistRegistrations = TableRegistry::getTableLocator()->get('DmiChemistRegistrations');

		$find_message_record = $this->find('all',array('conditions'=>array('id IS'=>$message_id, 'status'=>'active')))->first();//'status'condition inserted on 24-07-2018
		
		if (preg_match("/^[CHM]+\/[0-9]+\/[0-9]+$/", $customer_id,$matches)==1) {

			$get_packer_id = $DmiChemistRegistrations->find('all',array('fields'=>'created_by','conditions'=>array('chemist_id IS'=>$customer_id)))->first();
			$packer_id = $get_packer_id['created_by'];
			$_SESSION['chemistId'] = $customer_id;
			$customer_id = $packer_id;
		}

		//added this if condition on 24-07-2018 by Amol
		if (!empty($find_message_record)) {

			$destination_values = $find_message_record['destination'];
			$destination_array = explode(',',$destination_values);

			//checking applicant id pattern ex.102/2017 if primary Applicant, then dont split
			//added on 23-08-2017 by Amol
			if (!preg_match("/^[0-9]+\/[0-9]+$/",$customer_id,$matches)==1) {

				$split_customer_id = explode('/',$customer_id);
				$district_ro_code = $split_customer_id[2];
				
				$CustomersController = new CustomersController;
				$firmType = $CustomersController->Customfunctions->firmType($customer_id);
				//updated and added code to get Office table details from appl mapping Model
				$DmiApplWithRoMappings = TableRegistry::getTableLocator()->get('DmiApplWithRoMappings');
				$find_ro_email_id = $DmiApplWithRoMappings->getOfficeDetails($customer_id);

				$get_office_id = $DmiRoOffices->find('all',array('conditions'=>array('id IS'=>$find_ro_email_id['id'])))->first();

				#This Condtional Block is for checking if the SMS for lab and if the office type is so - AKASH [17-03-2023]
				if ($firmType == '3' && $get_office_id['office_type'] == 'SO') {
					$find_ro_id = $DmiRoOffices->find('all',array('conditions'=>array('id IS'=>$get_office_id['ro_id_for_so'],'OR'=>array('delete_status IS NULL','delete_status'=>'no'))))->first();
					$ro_email_id = $find_ro_id['ro_email_id'];
				} else {
					$ro_email_id = $find_ro_email_id['ro_email_id'];
				}
				
				
			}

			$m=0;
			$e=0;
			$destination_mob_nos = array();
			$log_dest_mob_nos = array();
			$destination_email_ids = array();



			//Applicant
			if (in_array(0,$destination_array)) {
				//checking applicant id pattern ex.102/2017 if primary Applicant added on 23-08-2017 by Amol
				if (preg_match("/^[0-9]+\/[0-9]+$/",$customer_id,$matches)==1) {

					$fetch_applicant_data = $DmiCustomers->find('all',array('conditions'=>array('customer_id IS'=>$customer_id)))->first();
					$applicant_mob_no = $fetch_applicant_data['mobile'];
					$applicant_email_id = $fetch_applicant_data['email'];

				} else {

					$fetch_applicant_data = $DmiFirms->find('all',array('conditions'=>array('customer_id IS'=>$customer_id)))->first();
					$applicant_mob_no = $fetch_applicant_data['mobile_no'];
					$applicant_email_id = $fetch_applicant_data['email'];

				}

				$destination_mob_nos[$m] = '91'.base64_decode($applicant_mob_no); //This is addded on 27-04-2021 for base64decoding by AKASH
				$log_dest_mob_nos[$m] = '91'.$applicant_mob_no;
				$destination_email_ids[$e] = base64_decode($applicant_email_id);//This is addded on 01-03-2022 for base64decoding by AKASH

				$m=$m+1;
				$e=$e+1;
			}




			//for MO/SMO (Nodal Officer)
			if (in_array(1,$destination_array)) {

				$DmiAllocations = TableRegistry::getTableLocator()->get($DmiFinalSubmitTable['allocation']);
				$find_allocated_mo = $DmiAllocations->find('all',array('conditions'=>array('customer_id IS'=>$customer_id,'level_3 IS'=>$ro_email_id),'order' => array('id' => 'desc')))->first();
				$mo_email_id = $find_allocated_mo['level_1'];

				//check if MO is allocated or not //added on 04-10-2017
				if (!empty($mo_email_id)) {

					$fetch_mo_data = $DmiUsers->find('all',array('conditions'=>array('email IS'=>$mo_email_id)))->first();
					$mo_mob_no = $fetch_mo_data['phone'];

					$destination_mob_nos[$m] = '91'.base64_decode($mo_mob_no); //This is addded on 27-04-2021 for base64decoding by AKASH
					$log_dest_mob_nos[$m] = '91'.$mo_mob_no;
					$destination_email_ids[$e] = base64_decode($mo_email_id);//This is addded on 01-03-2022 for base64decoding by AKASH

				} else {

					$destination_mob_nos[$m] = null;
					$log_dest_mob_nos[$m] = null;
					$destination_email_ids[$e] = null;
				}


				$m=$m+1;
				$e=$e+1;

			}

			//RO/SO
			if (in_array(3,$destination_array)) {

				$fetch_ro_data = $DmiUsers->find('all',array('conditions'=>array('email IS'=>$ro_email_id)))->first();
				$ro_mob_no = $fetch_ro_data['phone'];

				$destination_mob_nos[$m] = '91'.base64_decode($ro_mob_no);//This is addded on 27-04-2021 for base64decoding by AKASH
				$log_dest_mob_nos[$m] = '91'.$ro_mob_no;
				$destination_email_ids[$e] = base64_decode($ro_email_id);//This is addded on 01-03-2022 for base64decoding by AKASH

				$m=$m+1;
				$e=$e+1;

			}


			//for Accounts  (Done by pravin 20-07-2018)
			if (in_array(8,$destination_array)) {

				//for chemist get chemist id from session added by laxmi on 09-02-2023
				if($application_type == 4){
					$customer_id = $_SESSION['chemistId'];
				}
				$DmiApplicantPaymentDetails = TableRegistry::getTableLocator()->get($DmiFinalSubmitTable['payment']);//added on 20-07-2017 by Pravin
				$find_pao_id = $DmiApplicantPaymentDetails->find('all',array('conditions'=>array('customer_id IS'=>$customer_id),'order' => array('id' => 'desc')))->first();

				$pao_id =  $find_pao_id['pao_id'];
				$find_user_id =  $DmiPaoDetails->find('all',array('conditions'=>array('id IS'=>$pao_id)))->first();
				$user_id =  $find_user_id['pao_user_id'];


				$fetch_pao_data = $DmiUsers->find('all',array('conditions'=>array('id IS'=>$user_id)))->first();
				$pao_mob_no = $fetch_pao_data['phone'];
				$pao_email = $fetch_pao_data['email'];

				$destination_mob_nos[$m] = '91'.base64_decode($pao_mob_no);//This is addded on 27-04-2021 for base64decoding by AKASH
				$log_dest_mob_nos[$m] = '91'.$pao_mob_no;
				$destination_email_ids[$e] = base64_decode($pao_email);//This is addded on 01-03-2022 for base64decoding by AKASH

				$m=$m+1;
				$e=$e+1;

			}


			//RO Incharge
			if (in_array(9,$destination_array)) {

				$fetch_ro_data = $DmiUsers->find('all',array('conditions'=>array('email IS'=>$ro_email_id)))->first();
				$ro_mob_no = $fetch_ro_data['phone'];

				$destination_mob_nos[$m] = '91'.base64_decode($ro_mob_no);//This is addded on 27-04-2021 for base64decoding by AKASH
				$log_dest_mob_nos[$m] = '91'.$ro_mob_no;
				$destination_email_ids[$e] = base64_decode($ro_email_id);//This is addded on 01-03-2022 for base64decoding by AKASH

				$m=$m+1;
				$e=$e+1;

			}

			//for Chemist User
			if (in_array(10,$destination_array)) {

				$find_chemist_user= $DmiChemistRegistrations->find('all',array('conditions'=>array('chemist_id IS'=>$_SESSION['chemistId']),'order'=>'id desc'))->first();
				
				if (!empty($find_chemist_user)) {

					$chemist_id =  $find_chemist_user['chemist_id'];
					$chemist_mob_no = $find_chemist_user['mobile'];
					$chemist_email = $find_chemist_user['email'];

					$destination_mob_nos[$m] = '91'.base64_decode($chemist_mob_no);
					$log_dest_mob_nos[$m] = '91'.$chemist_mob_no;
					$destination_email_ids[$e] = base64_decode($chemist_email);

				} else {

					$destination_mob_nos[$m] = null;
					$log_dest_mob_nos[$m] = null;
					$destination_email_ids[$e] = null;
				}

				$m=$m+1;
				$e=$e+1;
			}

			
			
			$sms_message = $find_message_record['sms_message'];
			$destination_mob_nos_values = implode(',',$destination_mob_nos);
			$log_dest_mob_nos_values = implode(',',$log_dest_mob_nos);

			$email_message = $find_message_record['email_message'];
			$destination_email_ids_values = implode(',',$destination_email_ids);

			$email_subject = $find_message_record['email_subject'];

			$template_id = $find_message_record['template_id'];//added on 12-05-2021 by Amol, new field

			//replacing dynamic values in the email message
			$sms_message = $this->replaceDynamicValuesFromMessage($customer_id,$sms_message);
			
			//replacing dynamic values in the email message
			$email_message = $this->replaceDynamicValuesFromMessage($customer_id,$email_message);

			$textToAppend = array(
				'sms_message' => $sms_message,
				'destination_email_ids_values' => $destination_email_ids_values
			  );
			  
			  $filePath = 'D:/test_sms.txt';  // Replace this with the actual file path
			  
			  // Open the file in append mode and write the text
			  $file = fopen($filePath, 'a');
			  
			  // Convert the array to a string representation
			  $textToWrite = var_export($textToAppend, true) . PHP_EOL;
			  
			  fwrite($file, $textToWrite);
			  fclose($file);
			  
			//Calling Component Function
			$CustomersController = new CustomersController;


			//To send SMS on list of mobile nos.
			if (!empty($find_message_record['sms_message'])) {
				$CustomersController->SmsEmail->sendSms($message_id,$destination_mob_nos_values,$sms_message,$template_id,'DmiChemistSentSmsLogs');
			}

			//To send Email on list of Email ids.
			if (!empty($find_message_record['email_message'])) {
				$CustomersController->SmsEmail->sendEmail($message_id,$email_message,$destination_email_ids_values,$email_subject,$template_id,'DmiChemistSentEmailLogs');
			}

		}//end of 1st if condition 24-07-2018

	}


	//this function is created on 08-07-2017 by Amol to replace dynamic values in message
	public function replaceDynamicValuesFromMessage($customer_id,$message) {

		//getting count before execution
		$total_occurrences = substr_count($message,"%%");

		while($total_occurrences > 0){

			$matches = explode('%%',$message);//getting string between %% & %%

			if (!empty($matches[1])) {

				switch ($matches[1]) {

					case "firm_name":

						$message = str_replace("%%firm_name%%",(string) $this->getReplaceDynamicValues('firm_name',$customer_id),$message);
						break;

					case "amount":

						$message = str_replace("%%amount%%",(string) $this->getReplaceDynamicValues('amount',$customer_id),$message);
						break;

					case "commodities":

						$message = str_replace("%%commodities%%",(string) $this->getReplaceDynamicValues('commodities',$customer_id),$message);
						break;

					case "applicant_name":

						$message = str_replace("%%applicant_name%%",(string) $this->getReplaceDynamicValues('applicant_name',$customer_id),$message);
						break;

					case "applicant_mobile_no":

						$message = str_replace("%%applicant_mobile_no%%",(string) $this->getReplaceDynamicValues('applicant_mobile_no',$customer_id),$message);
						break;

					case "premises_id":

						$message = str_replace("%%premises_id%%",(string) $customer_id,$message);
						break;

					case "firm_email":

						$message = str_replace("%%firm_email%%",(string) $this->getReplaceDynamicValues('firm_email',$customer_id),$message);
						break;

					case "ro_name":

						$message = str_replace("%%ro_name%%",(string) $this->getReplaceDynamicValues('ro_name',$customer_id),$message);
						break;

					case "ro_mobile_no":

						$message = str_replace("%%ro_mobile_no%%",(string) $this->getReplaceDynamicValues('ro_mobile_no',$customer_id),$message); 
						break;

					case "ro_office":

						$message = str_replace("%%ro_office%%",(string) $this->getReplaceDynamicValues('ro_office',$customer_id),$message);
						break;

					case "ro_email_id":

						$message = str_replace("%%ro_email_id%%",(string) $this->getReplaceDynamicValues('ro_email_id',$customer_id),$message);
						break;

					case "mo_name":

						$message = str_replace("%%mo_name%%",(string) $this->getReplaceDynamicValues('mo_name',$customer_id),$message);
						break;

					case "mo_mobile_no":

						$message = str_replace("%%mo_mobile_no%%",(string) $this->getReplaceDynamicValues('mo_mobile_no',$customer_id),$message);
						break;

					case "mo_office":

						$message = str_replace("%%mo_office%%",(string) $this->getReplaceDynamicValues('mo_office',$customer_id),$message);
						break;

					case "mo_email_id":

						$message = str_replace("%%mo_email_id%%",(string) $this->getReplaceDynamicValues('mo_email_id',$customer_id),$message);
						break;

					case "applicant_email":

						$message = str_replace("%%applicant_email%%",(string) $this->getReplaceDynamicValues('applicant_email',$customer_id),$message);
						break;

					case "pao_name":

						$message = str_replace("%%pao_name%%",(string) $this->getReplaceDynamicValues('pao_name',$customer_id),$message);
						break;

					case "pao_email_id":

						$message = str_replace("%%pao_email_id%%",(string) $this->getReplaceDynamicValues('pao_email_id',$customer_id),$message);
						break;

					case "pao_mobile_no":

						$message = str_replace("%%pao_mobile_no%%",(string) $this->getReplaceDynamicValues('pao_mobile_no',$customer_id),$message);
						break;

					case "home_link":

						$message = str_replace("%%home_link%%",(string) $_SERVER['HTTP_HOST'],$message);
						break;

					case "chemist_name":

						$message = str_replace("%%chemist_name%%",(string) $this->getReplaceDynamicValues('chemist_name',$customer_id),$message);
						break;

					case "chemist_id":

						$message = str_replace("%%chemist_id%%",(string) $this->getReplaceDynamicValues('chemist_id',$customer_id),$message);
						break;

					case "replica_commodities":

						$message = str_replace("%%replica_commodities%%",(string) $this->getReplaceDynamicValues('replica_commodities',$customer_id),$message);
						break;
					
					case "packer_name":

						$message = str_replace("%%packer_name%%",(string) $this->getReplaceDynamicValues('packer_name',$customer_id),$message);
						break;
						
					case "lab_name":

						$message = str_replace("%%lab_name%%",(string) $this->getReplaceDynamicValues('lab_name',$customer_id),$message);
						break;
						
					case "printerName":

						$message = str_replace("%%printerName%%",(string) $this->getReplaceDynamicValues('printerName',$customer_id),$message);
						break;

					default:

						$message = $this->replaceBetween($message, '%%', '%%', '');
						$default_value = 'yes';
						break;
				}

			}

			if (empty($default_value)) {
				$total_occurrences = substr_count($message,"%%");//getting count after execution
			} else {
				$total_occurrences = $total_occurrences - 1;
			}

		}

		return $message;
	}

	


	// This function find and return the value of replace variable value that are used in sms/email message templete
	// Created By Pravin on 24-08-2017
	public function getReplaceDynamicValues($replace_variable_value,$customer_id){

		$application_type = 4;
	
		//Load Models
		$DmiApplicationTypes = TableRegistry::getTableLocator()->get('DmiApplicationTypes');
		$DmiFlowWiseTablesLists = TableRegistry::getTableLocator()->get('DmiFlowWiseTablesLists');
		$DmiFinalSubmitTable = $DmiFlowWiseTablesLists->find('all',array('conditions'=>array('application_type IS'=>$application_type)))->first();
		$DmiAllocations = TableRegistry::getTableLocator()->get($DmiFinalSubmitTable['allocation']);
		$DmiFinalSubmits = TableRegistry::getTableLocator()->get($DmiFinalSubmitTable['application_form']);
		$DmiGrantCertificatesPdfs = TableRegistry::getTableLocator()->get($DmiFinalSubmitTable['grant_pdf']);
		$DmiApplicantPaymentDetails = TableRegistry::getTableLocator()->get($DmiFinalSubmitTable['payment']);//added on 20-07-2017 by Pravin
		$DmiCustomers = TableRegistry::getTableLocator()->get('DmiCustomers');
		$DmiFirms = TableRegistry::getTableLocator()->get('DmiFirms');
		$DmiRoOffices = TableRegistry::getTableLocator()->get('DmiRoOffices');
		$DmiUsers = TableRegistry::getTableLocator()->get('DmiUsers');
		$DmiUserRoles = TableRegistry::getTableLocator()->get('DmiUserRoles');
		$MCommodity = TableRegistry::getTableLocator()->get('MCommodity');
		$DmiCertificateTypes = TableRegistry::getTableLocator()->get('DmiCertificateTypes');
		$DmiPaoDetails = TableRegistry::getTableLocator()->get('DmiPaoDetails');//added on 20-07-2017 by Pravin
		$DmiChemistRegistrations = TableRegistry::getTableLocator()->get('DmiChemistRegistrations');
	
		$get_application_type = $DmiApplicationTypes->find('all')->select(['application_type'])->where(['id IS'=>(int) $application_type,'delete_status IS NULL'])->first();
		$application_type_text = $get_application_type['application_type'];
		
		$CustomersController = new CustomersController;

		//Firm Type
		$firmType = $CustomersController->Customfunctions->firmType($customer_id);
		
		//Below Application Type = 7 condtion is added to by pass if the SMS is for Advance Payment -  AKASH [31-10-2022]
		$amount = '';
		$amount_paid = $DmiApplicantPaymentDetails->find()->select(['amount_paid'])->where([['customer_id IS' => $_SESSION['chemistId']]])->order('id desc')->first();
		$amount = $amount_paid['amount_paid'];
	

		


		// Description : for chemist training set packer id as customer id for temporary to get firm details
		// Author : Laxmi Bhadade
		// Date : 04-05-2023
		// For Module : Chemist Training
		if($application_type == 4) {
			$customer_id = $_SESSION['packer_id'];
		}
			  
		


		if (preg_match("/^[0-9]+\/[0-9]+$/",$customer_id,$matches)==1) {

			$fetch_applicant_data = $DmiCustomers->find('all',array('conditions'=>array('customer_id IS'=>$customer_id)))->first();
			$fetch_applicant_data = $fetch_applicant_data;

			if(!empty($fetch_applicant_data)){
				//This new truncate function is applied to the below line in order to trim down the charateer that exceeds the 34 Character - Akash [19-05-2023]
				$applicant_name = Text::truncate($fetch_applicant_data['f_name'].' '.$fetch_applicant_data['l_name'], 34, ['ellipsis' => '', 'exact' => true]);
			}else{
				$applicant_name = null;
			}
				
		} else {

			$fetch_firm_data = $DmiFirms->find('all',array('conditions'=>array('customer_id IS'=>$customer_id)))->first();
			$firm_data = $fetch_firm_data;

			if(!empty($firm_data)){
				//This new truncate function is applied to the below line in order to trim down the charateer that exceeds the 34 Character - Akash [19-05-2023]
				$firm_name = Text::truncate($firm_data['firm_name'], 34, ['ellipsis' => '', 'exact' => true]);
				//This new truncate function is applied to the below line in order to trim down the charateer that exceeds the 34 Character - Akash [19-05-2023]
				$firm_email = Text::truncate(base64_decode($firm_data['email']), 34, ['ellipsis' => '', 'exact' => true]);
			}else{
				$firm_name = null;
				$firm_email = null;
			}

			$get_commodity_id = explode(',',$fetch_firm_data['sub_commodity']);
			$get_commodity_name = $MCommodity->find('list',array('keyField'=>'commodity_code','valueField'=>'commodity_name','conditions'=>array('commodity_code IN'=>$get_commodity_id)))->toArray();

			$firm_certification_type_id = $firm_data['certification_type'];
			$firm_certification_type = $DmiCertificateTypes->find('all',array('conditions'=>array('id IS'=>$firm_certification_type_id)))->first();


			$split_customer_id = explode('/',$customer_id);
			$district_ro_code = $split_customer_id[2];

			//updated and added code to get Office table details from appl mapping Model
			$DmiApplWithRoMappings = TableRegistry::getTableLocator()->get('DmiApplWithRoMappings');
			$find_ro_email_id = $DmiApplWithRoMappings->getOfficeDetails($customer_id);

			$get_office_id = $DmiRoOffices->find('all',array('conditions'=>array('id IS'=>$find_ro_email_id['id'])))->first();

			#This Condtional Block is for checking if the SMS for lab and if the office type is so - AKASH [17-03-2023]
			if ($firmType == '3' && $get_office_id['office_type'] == 'SO') {
				$find_ro_id = $DmiRoOffices->find('all',array('conditions'=>array('id IS'=>$get_office_id['ro_id_for_so'],'OR'=>array('delete_status IS NULL','delete_status'=>'no'))))->first();
				$ro_email_id = $find_ro_id['ro_email_id'];
				$find_ro_email_id['ro_office'] = $find_ro_id['ro_office'];
			} else {
				$ro_email_id = $find_ro_email_id['ro_email_id'];
			}
			
			$ro_user_data = $DmiUsers->find('all',array('conditions'=>array('email IS'=>$ro_email_id)))->first();
			$ro_user_data = $ro_user_data;

			if(!empty($ro_user_data)){
				//This new truncate function is applied to the below line in order to trim down the charateer that exceeds the 34 Character - Akash [19-05-2023]
				$ro_name = Text::truncate($ro_user_data['f_name']." ".$ro_user_data['l_name'], 34, ['ellipsis' => '', 'exact' => true]);

				//This new truncate function is applied to the below line in order to trim down the charateer that exceeds the 34 Character - Akash [19-05-2023]
				$ro_email_id = Text::truncate(base64_decode($ro_user_data['email']), 34, ['ellipsis' => '', 'exact' => true]);
			} else {
				$ro_name = null;
				$ro_email_id = null;
			}


			if (!empty($DmiFinalSubmitTable)) {

                // Description : for chemist application type 4 use customer id as chemist  id 
				// Author : Laxmi Bhadade
				// Date : 04-05-2023
				// For Module : Chemist Training

				if($application_type == 4){
					$customer_id = $_SESSION['chemistId'];
				}
				$final_submit_data = $DmiFinalSubmits->find('all',array('conditions'=>array('customer_id IS'=>$customer_id, 'status'=>'pending'),'order' => array('id' => 'desc')))->first();
				//Check empty condition (Done by pravin 13/2/2018)

				if (!empty($final_submit_data)) {
					$final_submit_data = $final_submit_data['created'];
				} else {
					$final_submit_data = null;
				}

				$find_allocated_mo = $DmiAllocations->find('all',array('conditions'=>array('customer_id IS'=>$customer_id,'level_3 IS'=>$ro_email_id),'order' => array('id' => 'desc')))->first();

				if (!empty($find_allocated_mo)) {	

					$mo_email_id = $find_allocated_mo['level_1'];
					$mo_user_data = $DmiUsers->find('all',array('conditions'=>array('email IS'=>$mo_email_id)))->first();

					if (!empty($mo_user_data)) {

						$mo_user_data = $mo_user_data;
				
						//This new truncate function is applied to the below line in order to trim down the charateer that exceeds the 34 Character - Akash [19-05-2023]
						$mo_name = Text::truncate($mo_user_data['f_name']." ".$mo_user_data['l_name'], 34, ['ellipsis' => '', 'exact' => true]);
						//This new truncate function is applied to the below line in order to trim down the charateer that exceeds the 34 Character - Akash [19-05-2023]
						$mo_email_id = Text::truncate(base64_decode($mo_email_id), 34, ['ellipsis' => '', 'exact' => true]);
	
					}
				}


				//Get pao_name and pao_email (Done by pravin 20-07-2018)
				$find_pao_id = $DmiApplicantPaymentDetails->find('all',array('conditions'=>array('customer_id IS'=>$customer_id),'order' => array('id' => 'desc')))->first();

					if (!empty($find_pao_id)) {

						$pao_id =  $find_pao_id['pao_id'];

						$find_user_id =  $DmiPaoDetails->find('all',array('conditions'=>array('id IS'=>$pao_id)))->first();

						$user_id =  $find_user_id['pao_user_id'];

						$fetch_pao_data = $DmiUsers->find('all',array('conditions'=>array('id IS'=>$user_id)))->first();

						$pao_mobile_no = $fetch_pao_data['phone'];

						$pao_email_id = $fetch_pao_data['email'];

						$pao_name = $fetch_pao_data['f_name']." ".$fetch_pao_data['l_name'];

					}

			}
			
			#CHEMIST
			$get_chemist_name = $DmiChemistRegistrations->find('all',array('conditions'=>array('chemist_id IS'=>$_SESSION['chemistId'],'delete_status IS NULL'),'order'=>'id desc'))->first();
					
			if (!empty($get_chemist_name)) {
				
				$get_chemist_name = $DmiChemistRegistrations->find('all',array('conditions'=>array('chemist_id IS'=>$_SESSION['chemistId'],'delete_status IS NULL'),'order'=>'id desc'))->first();
				$chemist_name_view = $get_chemist_name['chemist_fname']." ".$get_chemist_name['chemist_lname']; 
				$chemist_id = $get_chemist_name['chemist_id'];
				
				$chemist_name = Text::truncate($chemist_name_view, 34, ['ellipsis' => '', 'exact' => true]);
			}
		
		}

		switch ($replace_variable_value) {

			case "applicant_name":
				
				return $applicant_name;
				break;

			case "applicant_mobile_no":

				$applicant_mobile_no = $fetch_applicant_data['mobile'];
				return $applicant_mobile_no;
				break;

			case "company_id":

				$company_id = $fetch_applicant_data['customer_id'];
				return $company_id;
				break;

			case "premises_id":

				$premises_id = $firm_data['customer_id'];
				return $premises_id;
				break;

			case "firm_name":

				return $firm_name;
				break;

			case "firm_certification_type":

				return $firm_certification_type;
				break;

			case "firm_email":

				return $firm_email;
				break;

			case "submission_date":

				$submission_date = $final_submit_data;
				return $submission_date;
				break;

			
			case "amount":

				return $amount;
				break;

			case "ro_name":

				return $ro_name;
				break;

			case "ro_mobile_no":

				$ro_mobile_no = $ro_user_data['phone'];
				return $ro_mobile_no;
				break;

			case "ro_office":

				$ro_office = $find_ro_email_id['ro_office'];
				return $ro_office;
				break;

			case "ro_email_id":

				
				return $ro_email_id;
				break;

			case "mo_name":

				return $mo_name;
				break;

			case "mo_mobile_no":

				$mo_mobile_no = $mo_user_data['phone'];
				return $mo_mobile_no;
				break;

			case "mo_office":

				$mo_office = $find_ro_email_id['ro_office'];
				return $mo_office;
				break;

			case "mo_email_id":

				return $mo_email_id;
				break;

			case "applicant_email":  // Add new paramerter list (done by pravin 07-03-2018)

				$applicant_email = $fetch_applicant_data['email'];
				return $applicant_email;
				break;

			case "pao_name":  // Add new paramerter list (done by pravin 20-07-2018)

				return $pao_name;
				break;

			case "pao_email_id":  // Add new paramerter list (done by pravin 20-07-2018)

				return $pao_email_id;
				break;

			case "pao_mobile_no":  // Add new paramerter list (done by pravin 20-07-2018)

				return $pao_mobile_no;
				break;

			//for replica
			case "chemist_name":

				return $chemist_name; 
				break;

			case "chemist_id":

				return $chemist_id;
				break;


			case "application_type":

				return $application_type_text;
				break;

				
			default:

			$message = '%%';
			break;

		}

		//Destroy the Application Type Session
		$_SESSION['application_type']=null;


	}


	// This function replace the value between two character  (Done By pravin 9-08-2018)
	function replaceBetween($str, $needle_start, $needle_end, $replacement) {

		$pos = strpos($str, $needle_start);
		$start = $pos === false ? 0 : $pos + strlen($needle_start);

		$pos = strpos($str, $needle_end, $start);
		$end = $start === false ? strlen($str) : $pos;

		return substr_replace($str,$replacement,$start);
	}

	//This function is created for convert the month no to month name
	function getMonthName($value){
		$monthName = date("F", mktime(0, 0, 0, $value, 10));
		return $monthName;
	}
	
}
?>
