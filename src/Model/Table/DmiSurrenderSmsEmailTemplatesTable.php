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

class DmiSurrenderSmsEmailTemplatesTable extends Table{

	public function sendMessage($message_id, $customer_id) {

		$application_type = '9';

		$DmiCustomers = TableRegistry::getTableLocator()->get('DmiCustomers');
		$DmiFirms = TableRegistry::getTableLocator()->get('DmiFirms');
		$DmiRoOffices = TableRegistry::getTableLocator()->get('DmiRoOffices');
		$DmiUsers = TableRegistry::getTableLocator()->get('DmiUsers');
		$DmiChemistRegistrations = TableRegistry::getTableLocator()->get('DmiChemistRegistrations');

		$find_message_record = $this->find('all',array('conditions'=>array('id IS'=>$message_id, 'status'=>'active')))->first();//'status'condition inserted on 24-07-2018

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


			//for Chemist User
			if (in_array(10,$destination_array)) {

				$find_chemist_user= $DmiChemistRegistrations->find('all',array('conditions'=>array('created_by IS' => $customer_id),'order'=>'id desc'))->first();

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

			$email_message = $find_message_record['email_message'];
			$destination_email_ids_values = implode(',',$destination_email_ids);

			$email_subject = $find_message_record['email_subject'];

			$template_id = $find_message_record['template_id'];//added on 12-05-2021 by Amol, new field

			//replacing dynamic values in the email message
			$sms_message = $this->replaceDynamicValuesFromMessage($customer_id,$sms_message);

			//replacing dynamic values in the email message
			$email_message = $this->replaceDynamicValuesFromMessage($customer_id,$email_message);


			$CustomersController = new CustomersController;

			$getLogTable = $CustomersController->SmsEmail->getLogTable($application_type);
			$smsLogTable = $getLogTable['sms_log_table'];
			$emailLogTable = $getLogTable['email_log_table'];

			//To send SMS on list of mobile nos.
			if (!empty($find_message_record['sms_message'])) {

				$CustomersController->SmsEmail->sendSms($message_id,$destination_mob_nos_values,$sms_message,$template_id,$smsLogTable);

			}


			//To send Email on list of Email ids.
			if (!empty($find_message_record['email_message'])) {

				$CustomersController->SmsEmail->sendEmail($message_id,$email_message,$destination_email_ids_values,$email_subject,$template_id,$emailLogTable);
			}
		}

	}


	//this function is created on 08-07-2017 by Amol to replace dynamic values in message
	public function replaceDynamicValuesFromMessage($customer_id,$message) {

		//getting count before execution
		$total_occurrences = substr_count($message,"%%");

		while($total_occurrences > 0){

			$matches = explode('%%',$message);//getting string between %% & %%

			if (!empty($matches[1])) {

				switch ($matches[1]) {

					case "surrender_date":
						$message = str_replace("%%surrender_date%%",(string) $this->getReplaceDynamicValues('surrender_date',$customer_id),$message);
                    break;

					case "firm_name":
						$message = str_replace("%%firm_name%%",(string) $this->getReplaceDynamicValues('firm_name',$customer_id),$message);
                    break;

					case "company_id":
						$message = str_replace("%%company_id%%",(string) $this->getReplaceDynamicValues('company_id',$customer_id),$message);
                    break;

					case "premises_id":
						$message = str_replace("%%premises_id%%",(string) $customer_id,$message);
                    break;

					case "ro_name":
						$message = str_replace("%%ro_name%%",(string) $this->getReplaceDynamicValues('ro_name',$customer_id),$message);
                    break;

					//For Replica And Chemist Module
					case "chemist_name":
						$message = str_replace("%%chemist_name%%",(string) $this->getReplaceDynamicValues('chemist_name',$customer_id),$message);
                    break;

					case "chemist_id":
						$message = str_replace("%%chemist_id%%",(string) $this->getReplaceDynamicValues('chemist_id',$customer_id),$message);
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

					case "firm_certification_type":
						$message = str_replace("%%firm_certification_type%%",(string) $this->getReplaceDynamicValues('firm_certification_type',$customer_id),$message);
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


		$CustomersController = new CustomersController;

		//Firm Type
		$firmType = $CustomersController->Customfunctions->firmType($customer_id);

		$DmiCustomers = TableRegistry::getTableLocator()->get('DmiCustomers');
		$DmiFirms = TableRegistry::getTableLocator()->get('DmiFirms');
		$DmiRoOffices = TableRegistry::getTableLocator()->get('DmiRoOffices');
		$DmiUsers = TableRegistry::getTableLocator()->get('DmiUsers');
		$DmiCertificateTypes = TableRegistry::getTableLocator()->get('DmiCertificateTypes');
		$DmiChemistRegistrations = TableRegistry::getTableLocator()->get('DmiChemistRegistrations');
		$DmiCaPpLabMapings = TableRegistry::getTableLocator()->get('DmiCaPpLabMapings');
        $DmiFlowWiseTablesLists = TableRegistry::getTableLocator()->get('DmiFlowWiseTablesLists');
        $DmiFinalSubmitTable = $DmiFlowWiseTablesLists->find('all',array('conditions'=>array('application_type IS'=>'9')))->first();
        $DmiFinalSubmits = TableRegistry::getTableLocator()->get($DmiFinalSubmitTable['application_form']);
        $DmiGrantCertificatesPdfs = TableRegistry::getTableLocator()->get($DmiFinalSubmitTable['grant_pdf']);
		$DmiCertificateTypes = TableRegistry::getTableLocator()->get('DmiCertificateTypes');


		if (preg_match("/^[0-9]+\/[0-9]+$/",$customer_id,$matches)==1) {

			$fetch_applicant_data = $DmiCustomers->find('all',array('conditions'=>array('customer_id IS'=>$customer_id)))->first();
			$fetch_applicant_data = $fetch_applicant_data;

		} else {

			$fetch_firm_data = $DmiFirms->find('all',array('conditions'=>array('customer_id IS'=>$customer_id)))->first();
			$firm_data = $fetch_firm_data;

			$firm_certification_type_id = $firm_data['certification_type'];
			$firm_certification_type = $DmiCertificateTypes->find('all',array('conditions'=>array('id IS'=>$firm_certification_type_id)))->first();

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


			if (!empty($DmiFinalSubmitTable)) {


				$final_submit_data = $DmiFinalSubmits->find('all',array('conditions'=>array('customer_id IS'=>$customer_id, 'status'=>'pending'),'order' => array('id' => 'desc')))->first();

				if (!empty($final_submit_data)) {
					$final_submit_data = $final_submit_data['created'];
				} else {
					$final_submit_data = null;
				}
			}


			#CHEMIST
			$get_chemist_name = $DmiChemistRegistrations->find()->where(['created_by IS' => $customer_id,'delete_status IS NULL'])->order('id DESC')->first();


            #Surrender Date
            $surrenderDate = date('Y-m-d H:i:s');

		}

		switch ($replace_variable_value) {

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

			case "firm_certification_type":

				return $firm_certification_type->certificate_type;
            break;

			case "ro_name":
				//This new truncate function is applied to the below line in order to trim down the charateer that exceeds the 34 Character - Akash [19-05-2023]
				$ro_name = Text::truncate($ro_user_data['f_name']." ".$ro_user_data['l_name'], 34, ['ellipsis' => '', 'exact' => true]);
				return $ro_name;
            break;

			case "ro_office":
				$ro_office = $find_ro_email_id['ro_office'];
				return $ro_office;
            break;
            /*
			//for replica
			case "chemist_name":
				//This new truncate function is applied to the below line in order to trim down the charateer that exceeds the 34 Character - Akash [19-05-2023]
				return Text::truncate($chemist_name, 34, ['ellipsis' => '', 'exact' => true]);
            break;

			case "chemist_id":
				return $chemist_id;
            break;

			case "packer_name":
				//This new truncate function is applied to the below line in order to trim down the charateer that exceeds the 34 Character - Akash [19-05-2023]
				return Text::truncate($packer_name, 34, ['ellipsis' => '', 'exact' => true]);
            break;

			case "printerName":
				//This new truncate function is applied to the below line in order to trim down the charateer that exceeds the 34 Character - Akash [19-05-2023]
				return Text::truncate($printerName, 34, ['ellipsis' => '', 'exact' => true]);
            break;

			case "lab_name":
				//This new truncate function is applied to the below line in order to trim down the charateer that exceeds the 34 Character - Akash [19-05-2023]
				return Text::truncate($lab_name, 34, ['ellipsis' => '', 'exact' => true]);
            break;
                */
			case "surrender_date":
				return $surrenderDate;
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
