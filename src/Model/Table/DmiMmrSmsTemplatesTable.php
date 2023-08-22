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
use Cake\Datasource\ConnectionManager;
use Cake\Utility\Text;

class DmiMmrSmsTemplatesTable extends Table{

	var $name = "DmiMmrSmsTemplates";

	public function sendMessage($message_id, $customer_id, $sample_code) {

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



		$_SESSION['flow_table'] = '';
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

				$DmiMmrAllocations = TableRegistry::getTableLocator()->get('DmiMmrAllocations');
				$find_allocated_mo = $DmiMmrAllocations->find('all',array('conditions'=>array('customer_id IS'=>$customer_id, 'sample_code IS'=>$sample_code, 'level_3 IS'=>$ro_email_id),'order' => array('id' => 'desc')))->first();
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



			//Dy.AMA
			if (in_array(4,$destination_array)) {

				$find_dy_ama_user = $DmiUserRoles->find('all',array('fields'=>'user_email_id','conditions'=>array('dy_ama'=>'yes')))->first();
				$dy_ama_email_id = $find_dy_ama_user['user_email_id'];

				$fetch_dy_ama_data = $DmiUsers->find('all',array('conditions'=>array('email IS'=>$dy_ama_email_id)))->first();
				$dy_ama_mob_no = $fetch_dy_ama_data['phone'];

				$destination_mob_nos[$m] = '91'.base64_decode($dy_ama_mob_no);//This is addded on 27-04-2021 for base64decoding by AKASH
				$log_dest_mob_nos[$m] = '91'.$dy_ama_mob_no;
				$destination_email_ids[$e] = base64_decode($dy_ama_email_id);//This is addded on 01-03-2022 for base64decoding by AKASH

				$m=$m+1;
				$e=$e+1;

			}



			//Jt.AMA
			if (in_array(5,$destination_array)) {

				$find_jt_ama_user = $DmiUserRoles->find('all',array('fields'=>'user_email_id','conditions'=>array('jt_ama'=>'yes')))->first();
				$jt_ama_email_id = $find_jt_ama_user['user_email_id'];

				$fetch_jt_ama_data = $DmiUsers->find('all',array('conditions'=>array('email IS'=>$jt_ama_email_id)))->first();
				$jt_ama_mob_no = $fetch_jt_ama_data['phone'];

				$destination_mob_nos[$m] = '91'.base64_decode($jt_ama_mob_no);//This is addded on 27-04-2021 for base64decoding by AKASH
				$log_dest_mob_nos[$m] = '91'.$jt_ama_mob_no;
				$destination_email_ids[$e] = base64_decode($jt_ama_email_id);//This is addded on 01-03-2022 for base64decoding by AKASH

				$m=$m+1;
				$e=$e+1;

			}

			//for HO MO/SMO
			if (in_array(6,$destination_array)) {

				$find_dy_ama_user = $DmiUserRoles->find('all',array('fields'=>'user_email_id','conditions'=>array('dy_ama'=>'yes')))->first();
				$dy_ama_email_id = $find_dy_ama_user['user_email_id'];

				$DmiMmrHoComments = TableRegistry::getTableLocator()->get('DmiMmrHoComments');
				$find_allocated_ho_mo = $DmiMmrHoComments->find('all',array('conditions'=>array('customer_id IS'=>$customer_id, 'dy_ama IS'=>$dy_ama_email_id),'order' => array('id' => 'desc')))->first();
				$ho_mo_email_id = $find_allocated_ho_mo['ho_mo_smo'];

				$fetch_ho_mo_data = $DmiUsers->find('all',array('conditions'=>array('email IS'=>$ho_mo_email_id)))->first();
				$ho_mo_mob_no = $fetch_ho_mo_data['phone'];

				$destination_mob_nos[$m] = '91'.base64_decode($ho_mo_mob_no);//This is addded on 27-04-2021 for base64decoding by AKASH
				$log_dest_mob_nos[$m] = '91'.$ho_mo_mob_no;
				$destination_email_ids[$e] = base64_decode($ho_mo_email_id);//This is addded on 01-03-2022 for base64decoding by AKASH

				$m=$m+1;
				$e=$e+1;

			}



			//for AMA
			if (in_array(7,$destination_array)) {

				$find_ama_user = $DmiUserRoles->find('all',array('fields'=>'user_email_id','conditions'=>array('ama'=>'yes')))->first();
				$ama_email_id = $find_ama_user['user_email_id'];


				$fetch_ama_data = $DmiUsers->find('all',array('conditions'=>array('email IS'=>$ama_email_id)))->first();
				$ama_mob_no = $fetch_ama_data['phone'];

				$destination_mob_nos[$m] = '91'.base64_decode($ama_mob_no);//This is addded on 27-04-2021 for base64decoding by AKASH
				$log_dest_mob_nos[$m] = '91'.$ama_mob_no;
				$destination_email_ids[$e] = base64_decode($ama_email_id);//This is addded on 01-03-2022 for base64decoding by AKASH

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


			$sms_message = $find_message_record['sms_message'];
			$destination_mob_nos_values = implode(',',$destination_mob_nos);
			$log_dest_mob_nos_values = implode(',',$log_dest_mob_nos);

			$email_message = $find_message_record['email_message'];
			$destination_email_ids_values = implode(',',$destination_email_ids);

			$email_subject = $find_message_record['email_subject'];

			$template_id = $find_message_record['template_id'];//added on 12-05-2021 by Amol, new field


			//replacing dynamic values in the email message
			$sms_message = $this->replaceDynamicValuesFromMessage($sms_message,$customer_id,$sample_code);

			//replacing dynamic values in the email message
			$email_message = $this->replaceDynamicValuesFromMessage($sms_message,$customer_id,$sample_code);



			$textToAppend = $sms_message;  // Replace this with the text generated by the model
			$filePath = 'D:/test_sms.txt';  // Replace this with the actual file path
			// Open the file in append mode and write the text
			$file = fopen($filePath, 'a');
			fwrite($file, $textToAppend . PHP_EOL);
			fclose($file);

			//Calling Component Function
			$CustomersController = new CustomersController;

			//To send SMS on list of mobile nos.
			if (!empty($find_message_record['sms_message'])) {

				$CustomersController->SmsEmail->sendSms($message_id,$destination_mob_nos_values,$sms_message,$template_id,'DmiMmrSentSmsLogs');
			}


			//To send Email on list of Email ids.
			if (!empty($find_message_record['email_message'])) {

				$CustomersController->SmsEmail->sendEmail($message_id,$email_message,$destination_email_ids_values,$email_subject,$template_id,'DmiMmrSentEmailLogs');
			}


		}//end of 1st if condition 24-07-2018

	}


	//this function is created on 08-07-2017 by Amol to replace dynamic values in message
	public function replaceDynamicValuesFromMessage($message,$customer_id,$sample_code) {

		//getting count before execution
		$total_occurrences = substr_count($message,"%%");

		while($total_occurrences > 0){

			$matches = explode('%%',$message);//getting string between %% & %%

			if (!empty($matches[1])) {

				switch ($matches[1]) {

					case "firm_name":

						$message = str_replace("%%firm_name%%",(string) $this->getReplaceDynamicValues('firm_name',$customer_id,$sample_code),$message);
						break;

					case "commodities":

						$message = str_replace("%%commodities%%",(string) $this->getReplaceDynamicValues('commodities',$customer_id,$sample_code),$message);
						break;

					case "applicant_name":

						$message = str_replace("%%applicant_name%%",(string) $this->getReplaceDynamicValues('applicant_name',$customer_id,$sample_code),$message);
						break;

					case "applicant_mobile_no":

						$message = str_replace("%%applicant_mobile_no%%",(string) $this->getReplaceDynamicValues('applicant_mobile_no',$customer_id,$sample_code),$message);
						break;

					case "company_id":

						$message = str_replace("%%company_id%%",(string) $this->getReplaceDynamicValues('company_id',$customer_id,$sample_code),$message);
						break;


					case "premises_id":

						$message = str_replace("%%premises_id%%",(string) $this->getReplaceDynamicValues('premises_id',$customer_id,$sample_code),$message);
						break;

					case "firm_email":

						$message = str_replace("%%firm_email%%",(string) $this->getReplaceDynamicValues('firm_email',$customer_id,$sample_code),$message);
						break;

					case "ro_name":

						$message = str_replace("%%ro_name%%",(string) $this->getReplaceDynamicValues('ro_name',$customer_id,$sample_code),$message);
						break;

					case "ro_mobile_no":

						$message = str_replace("%%ro_mobile_no%%",(string) $this->getReplaceDynamicValues('ro_mobile_no',$customer_id,$sample_code),$message);
						break;

					case "ro_office":

						$message = str_replace("%%ro_office%%",(string) $this->getReplaceDynamicValues('ro_office',$customer_id,$sample_code),$message);
						break;

					case "ro_email_id":

						$message = str_replace("%%ro_email_id%%",(string) $this->getReplaceDynamicValues('ro_email_id',$customer_id,$sample_code),$message);
						break;

					case "mo_name":

						$message = str_replace("%%mo_name%%",(string) $this->getReplaceDynamicValues('mo_name',$customer_id,$sample_code),$message);
						break;

					case "mo_mobile_no":

						$message = str_replace("%%mo_mobile_no%%",(string) $this->getReplaceDynamicValues('mo_mobile_no',$customer_id,$sample_code),$message);
						break;

					case "mo_office":

						$message = str_replace("%%mo_office%%",(string) $this->getReplaceDynamicValues('mo_office',$customer_id,$sample_code),$message);
						break;

					case "mo_email_id":

						$message = str_replace("%%mo_email_id%%",(string) $this->getReplaceDynamicValues('mo_email_id',$customer_id,$sample_code),$message);
						break;


					case "dyama_name":

						$message = str_replace("%%dyama_name%%",(string) $this->getReplaceDynamicValues('dyama_name',$customer_id,$sample_code),$message);
						break;

					case "dyama_mobile_no":

						$message = str_replace("%%dyama_mobile_no%%",(string) $this->getReplaceDynamicValues('dyama_mobile_no',$customer_id,$sample_code),$message);
						break;

					case "dyama_email_id":

						$message = str_replace("%%dyama_email_id%%",(string) $this->getReplaceDynamicValues('dyama_email_id',$customer_id,$sample_code),$message);
						break;

					case "jtama_name":

						$message = str_replace("%%jtama_name%%",(string) $this->getReplaceDynamicValues('jtama_name',$customer_id,$sample_code),$message);
						break;

					case "jtama_mobile_no":

						$message = str_replace("%%jtama_mobile_no%%",(string) $this->getReplaceDynamicValues('jtama_mobile_no',$customer_id,$sample_code),$message);
						break;

					case "jtama_email_id":

						$message = str_replace("%%jtama_email_id%%",(string) $this->getReplaceDynamicValues('jtama_email_id',$customer_id,$sample_code),$message);
						break;

					case "ama_name":

						$message = str_replace("%%ama_name%%",(string) $this->getReplaceDynamicValues('ama_name',$customer_id,$sample_code),$message);
						break;

					case "ama_mobile_no":

						$message = str_replace("%%ama_mobile_no%%",(string) $this->getReplaceDynamicValues('ama_mobile_no',$customer_id,$sample_code),$message);
						break;

					case "ama_email_id":

						$message = str_replace("%%ama_email_id%%",(string) $this->getReplaceDynamicValues('ama_email_id',$customer_id,$sample_code),$message);
						break;


					case "applicant_email":

						$message = str_replace("%%applicant_email%%",(string) $this->getReplaceDynamicValues('applicant_email',$customer_id,$sample_code),$message);
						break;


					case "ho_mo_name":

						$message = str_replace("%%ho_mo_name%%",(string) $this->getReplaceDynamicValues('ho_mo_name',$customer_id,$sample_code),$message);
						break;

					case "ho_mo_mobile_no":

						$message = str_replace("%%ho_mo_mobile_no%%",(string) $this->getReplaceDynamicValues('ho_mo_mobile_no',$customer_id,$sample_code),$message);
						break;

					case "ho_mo_email_id":

						$message = str_replace("%%ho_mo_email_id%%",(string) $this->getReplaceDynamicValues('ho_mo_email_id',$customer_id,$sample_code),$message);
						break;

					case "sample_code":

						$message = str_replace("%%sample_code%%",(string) $this->getReplaceDynamicValues('sample_code',$customer_id,$sample_code),$message);
						break;

					case "scn_date":

						$message = str_replace("%%scn_date%%",(string) $this->getReplaceDynamicValues('scn_date',$customer_id,$sample_code),$message);
						break;

					case "suspended_date":

						$message = str_replace("%%suspended_date%%",(string) $this->getReplaceDynamicValues('suspended_date',$customer_id,$sample_code),$message);
						break;

					case "time_period":

						$message = str_replace("%%time_period%%",(string) $this->getReplaceDynamicValues('time_period',$customer_id,$sample_code),$message);
						break;

					case "cancelled_date":

						$message = str_replace("%%cancelled_date%%",(string) $this->getReplaceDynamicValues('cancelled_date',$customer_id,$sample_code),$message);
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
	public function getReplaceDynamicValues($replace_variable_value,$customer_id,$sample_code){


		//Load Models
		$CustomersController = new CustomersController;

		//Firm Type
		$firmType = $CustomersController->Customfunctions->firmType($customer_id);

		//Below Application Type = 7 condtion is added to by pass if the SMS is for Advance Payment -  AKASH [31-10-2022]
		$DmiMmrAllocations = TableRegistry::getTableLocator()->get('DmiMmrAllocations');
		$DmiMmrHoAllocations = TableRegistry::getTableLocator()->get('DmiMmrHoAllocations');
		$DmiMmrHoComments = TableRegistry::getTableLocator()->get('DmiMmrHoComments');
		$DmiMmrActionFinalSubmits = TableRegistry::getTableLocator()->get('DmiMmrActionFinalSubmits');
		$DmiCustomers = TableRegistry::getTableLocator()->get('DmiCustomers');
		$DmiFirms = TableRegistry::getTableLocator()->get('DmiFirms');
		$DmiRoOffices = TableRegistry::getTableLocator()->get('DmiRoOffices');
		$DmiUsers = TableRegistry::getTableLocator()->get('DmiUsers');
		$DmiUserRoles = TableRegistry::getTableLocator()->get('DmiUserRoles');
		$MCommodity = TableRegistry::getTableLocator()->get('MCommodity');
		$DmiMmrFinalSubmits = TableRegistry::getTableLocator()->get('DmiMmrFinalSubmits');


		if (preg_match("/^[0-9]+\/[0-9]+$/",$customer_id,$matches)==1) {

			$fetch_applicant_data = $DmiCustomers->find('all',array('conditions'=>array('customer_id IS'=>$customer_id)))->first();
			$fetch_applicant_data = $fetch_applicant_data;

		} else {

			$fetch_firm_data = $DmiFirms->find('all',array('conditions'=>array('customer_id IS'=>$customer_id)))->first();
			$firm_data = $fetch_firm_data;

			$get_commodity_id = explode(',',$fetch_firm_data['sub_commodity']);
			$get_commodity_name = $MCommodity->find('list',array('keyField'=>'commodity_code','valueField'=>'commodity_name','conditions'=>array('commodity_code IN'=>$get_commodity_id)))->toArray();

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

			$find_dy_ama_user = $DmiUserRoles->find('all',array('fields'=>'user_email_id','conditions'=>array('dy_ama'=>'yes')))->first();
			$dy_ama_email_id = $find_dy_ama_user['user_email_id'];

			$dy_ama_user_data = $DmiUsers->find('all',array('conditions'=>array('email IS'=>$dy_ama_email_id)))->first();
			$dy_ama_user_data = $dy_ama_user_data;

			$find_jt_ama_user = $DmiUserRoles->find('all',array('fields'=>'user_email_id','conditions'=>array('jt_ama'=>'yes')))->first();
			$jt_ama_email_id = $find_jt_ama_user['user_email_id'];

			$jt_ama_user_data = $DmiUsers->find('all',array('conditions'=>array('email IS'=>$jt_ama_email_id)))->first();
			$jt_ama_user_data = $jt_ama_user_data;

			$find_ama_user = $DmiUserRoles->find('all',array('fields'=>'user_email_id','conditions'=>array('ama'=>'yes')))->first();
			$ama_email_id = $find_ama_user['user_email_id'];

			$ama_user_data = $DmiUsers->find('all',array('conditions'=>array('email IS'=>$ama_email_id)))->first();
			$ama_user_data = $ama_user_data;





            $DmiMmrAllocations = TableRegistry::getTableLocator()->get('DmiMmrAllocations');
            $find_allocated_mo = $DmiMmrAllocations->find('all',array('conditions'=>array('customer_id IS'=>$customer_id,'sample_code' => $sample_code,'level_3 IS'=>$ro_email_id),'order' => array('id' => 'desc')))->first();

            if (!empty($find_allocated_mo)) {

                $mo_email_id = $find_allocated_mo['level_1'];
                $mo_user_data = $DmiUsers->find('all',array('conditions'=>array('email IS'=>$mo_email_id)))->first();

                if (!empty($mo_user_data)) {

                    $mo_user_data = $mo_user_data;

                }
            }




            //Get ho_mo_details (Done by pravin 23-07-2018)
            $find_allocated_ho_mo = $DmiMmrHoAllocations->find('all',array('conditions'=>array('customer_id IS'=>$customer_id,'sample_code'=> $sample_code,'dy_ama IS'=>$dy_ama_email_id),'order' => array('id' => 'desc')))->first();

            if (!empty($find_allocated_ho_mo)) {

                $ho_mo_email_id = $find_allocated_ho_mo['ho_mo_smo'];

                $fetch_ho_mo_data = $DmiUsers->find('all',array('conditions'=>array('email IS'=>$ho_mo_email_id)))->first();

                if (!empty($fetch_ho_mo_data)) {

                    $ho_mo_mob_no = $fetch_ho_mo_data['phone'];

                    $ho_mo_name = $fetch_ho_mo_data['f_name']." ".$fetch_ho_mo_data['l_name'];

                }

            }

			$details =	$DmiMmrFinalSubmits->find()->where(['customer_id' => $customer_id,'sample_code'=>$sample_code])->order('id DESC')->first();

			//sample code
			$sample_code = $details['sample_code'];

			//Show Cause Notice
			$conn = ConnectionManager::get('default');
			$showCauseNotice = $conn->execute("SELECT dsl.id,dsl.customer_id,dsl.reason,
										dsl.date,dsl.end_date,dsnp.pdf_file,dsl.status,dsl.sample_code,dsl.modified
										FROM dmi_mmr_showcause_logs AS dsl
										INNER JOIN dmi_mmr_showcause_notice_pdfs AS dsnp ON dsnp.customer_id = dsl.customer_id
										WHERE dsl.customer_id='$customer_id'")->fetchAll('assoc');
			$lastRecord = null;
			for ($i = count($showCauseNotice) - 1; $i >= 0; $i--) {
				$lastRecord = $showCauseNotice[$i];
				break;
			}


			//SCN DATE
			if($lastRecord !== null){
				$formattedDate = $lastRecord['date'];
				$dateTime = \DateTime::createFromFormat('d/m/Y H:i:s', $formattedDate);
				$scn_date = $dateTime->format('d/m/Y');
			}else{
				$scn_date = null;
			}

			//Suspension Details
			$currentDate = date('Y-m-d H:i:s');
			$DmiMmrSuspensions = TableRegistry::getTableLocator()->get('DmiMmrSuspensions');
			$suspension_record = $DmiMmrSuspensions->find('all')->where(['customer_id IS' => $customer_id,'to_date >=' => $currentDate])->order('id DESC')->first();

			if (!empty($suspension_record)) {

				$formattedDate = $suspension_record['suspended_on'];
				$dateTime = \DateTime::createFromFormat('d/m/Y H:i:s', $formattedDate);
				$suspended_date = $dateTime->format('d/m/Y');

				$time_period = $suspension_record['time_period'];

			} else {
				$suspended_date = null;
				$time_period = null;
			}


			#For Cancellation
			$DmiMmrCancelledFirms = TableRegistry::getTableLocator()->get('DmiMmrCancelledFirms');
			$cancellation_record = $DmiMmrCancelledFirms->find('all')->where(['customer_id IS' => $customer_id])->order('id DESC')->first();
			if (!empty($cancellation_record)) {

				$formattedDate = $cancellation_record['date'];
				$dateTime = \DateTime::createFromFormat('d/m/Y H:i:s', $formattedDate);
				$cancelled_date = $dateTime->format('d/m/Y');

			}else{
				$cancelled_date = null;
			}


		}

		switch ($replace_variable_value) {

			case "applicant_name":

				//This new truncate function is applied to the below line in order to trim down the charateer that exceeds the 34 Character - Akash [19-05-2023]
				$applicant_name = Text::truncate($fetch_applicant_data['f_name'].' '.$fetch_applicant_data['l_name'], 34, ['ellipsis' => '', 'exact' => true]);
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

				//This new truncate function is applied to the below line in order to trim down the charateer that exceeds the 34 Character - Akash [19-05-2023]
				$firm_name = Text::truncate($firm_data['firm_name'], 34, ['ellipsis' => '', 'exact' => true]);
				return $firm_name;
				break;


			case "firm_email":

				//This new truncate function is applied to the below line in order to trim down the charateer that exceeds the 34 Character - Akash [19-05-2023]
				$firm_email = Text::truncate(base64_decode($firm_data['email']), 34, ['ellipsis' => '', 'exact' => true]);
				return $firm_email;
				break;

			case "commodities":

				//This new truncate function is applied to the below line in order to trim down the charateer that exceeds the 34 Character - Akash [19-05-2023]
				return Text::truncate($get_commodity_name, 34, ['ellipsis' => '', 'exact' => true]);
				break;


			case "ro_name":

				//This new truncate function is applied to the below line in order to trim down the charateer that exceeds the 34 Character - Akash [19-05-2023]
				$ro_name = Text::truncate($ro_user_data['f_name']." ".$ro_user_data['l_name'], 34, ['ellipsis' => '', 'exact' => true]);
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

				//This new truncate function is applied to the below line in order to trim down the charateer that exceeds the 34 Character - Akash [19-05-2023]
				$ro_email_id = Text::truncate(base64_decode($find_ro_email_id['ro_email_id']), 34, ['ellipsis' => '', 'exact' => true]);
				return $ro_email_id;
				break;

			case "mo_name":

				//This new truncate function is applied to the below line in order to trim down the charateer that exceeds the 34 Character - Akash [19-05-2023]
				$mo_name = Text::truncate($mo_user_data['f_name']." ".$mo_user_data['l_name'], 34, ['ellipsis' => '', 'exact' => true]);
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

				//This new truncate function is applied to the below line in order to trim down the charateer that exceeds the 34 Character - Akash [19-05-2023]
				$mo_email_id = Text::truncate(base64_decode($mo_email_id), 34, ['ellipsis' => '', 'exact' => true]);
				return $mo_email_id;
				break;

			case "dyama_name":

				$dyama_name = $dy_ama_user_data['f_name']." ".$dy_ama_user_data['l_name'];
				return $dyama_name;
				break;

			case "dyama_mobile_no":

				$dyama_mobile_no = $dy_ama_user_data['phone'];
				return $dyama_mobile_no;
				break;

			case "dyama_email_id":
					$dy_ama_email_id;
				return $dy_ama_email_id;
				break;

			case "jtama_name":

				$jtama_name = $jt_ama_user_data['f_name']." ".$jt_ama_user_data['l_name'];
				return $jtama_name;
				break;

			case "jtama_mobile_no":

				$jtama_mobile_no = $jt_ama_user_data['phone'];
				return $jtama_mobile_no;
				break;

			case "jtama_email_id":

				return $jt_ama_email_id;
				break;

			case "ama_name":

				$ama_name = $ama_user_data['f_name']." ".$ama_user_data['l_name'];
				return $ama_name;
				break;

			case "ama_mobile_no":

				$ama_mobile_no = $ama_user_data['phone'];
				return $ama_mobile_no;
				break;

			case "ama_email_id":

				return $ama_email_id;
				break;



			case "applicant_email":  // Add new paramerter list (done by pravin 07-03-2018)

				$applicant_email = $fetch_applicant_data['email'];
				return $applicant_email;
				break;



			case "ho_mo_email_id":  // Add new paramerter list (done by pravin 23-07-2018)

				return $ho_mo_email_id;
				break;

			case "ho_mo_mob_no":  // Add new paramerter list (done by pravin 23-07-2018)

				return $ho_mo_mob_no;
				break;

			case "ho_mo_name":  // Add new paramerter list (done by pravin 23-07-2018)

				return $ho_mo_name;
				break;

			case "sample_code":

				return $sample_code;
				break;

			case "scn_date":

				return $scn_date;
				break;

			case "suspended_date":

				return $suspended_date;
				break;

			case "time_period":

				return $time_period;
				break;

			case "cancelled_date":

				return $cancelled_date;
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
