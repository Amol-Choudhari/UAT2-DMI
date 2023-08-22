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

class DmiSmsEmailTemplatesTable extends Table{

	var $name = "DmiSmsEmailTemplates";

	public $validate = array(

		'sms_message'=>array(

					'rule' => 'notBlank',
				),
		'email_message'=>array(

					'rule' => 'notBlank',
				),
		'description'=>array(

				'rule' => 'notBlank',
			),
		'template_for'=>array(
				'rule'=>array('maxLength',20),
				'allowEmpty'=>false,
			),
		'email_subject'=>array(
				'rule'=>array('maxLength',200),
				'allowEmpty'=>false,
			),

	);


	public function sendMessage($message_id, $customer_id) {

		if (!isset($_SESSION['application_type'])){
			$_SESSION['application_type']=null;
		}

		$application_type = $_SESSION['application_type'];

		//This Session ID is Applied for the temporary Application Type is not present.
		if(Router::getRequest()->getParam('controller') == 'Dashboard'){
			if ($application_type == null) {
				$application_type = $_SESSION['application_type_temp'];
			}
		}

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

		//Replica and Chemist Module
		$_SESSION['chemistId'] = '';

		if (preg_match("/^[CHM]+\/[0-9]+\/[0-9]+$/", $customer_id,$matches)==1) {

			$get_packer_id = $DmiChemistRegistrations->find('all',array('fields'=>'created_by','conditions'=>array('chemist_id IS'=>$customer_id)))->first();
			$packer_id = $get_packer_id['created_by'];
			$_SESSION['chemistId'] = $customer_id;
			$customer_id = $packer_id;
		}

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




			//for IO
			if (in_array(2,$destination_array)) {

				$DmiAllocations = TableRegistry::getTableLocator()->get($DmiFinalSubmitTable['allocation']);
				$find_allocated_io = $DmiAllocations->find('all',array('conditions'=>array('customer_id IS'=>$customer_id,'level_3 IS'=>$ro_email_id),'order' => array('id' => 'desc')))->first();
				$io_email_id = $find_allocated_io['level_2'];

				//check if IO is allocated or not //added on 04-10-2017
				if (!empty($io_email_id)) {

					$fetch_io_data = $DmiUsers->find('all',array('conditions'=>array('email IS'=>$io_email_id)))->first();
					$io_mob_no = $fetch_io_data['phone'];

					$destination_mob_nos[$m] = '91'.base64_decode($io_mob_no);//This is addded on 27-04-2021 for base64decoding by AKASH
					$log_dest_mob_nos[$m] = '91'.$io_mob_no;
					$destination_email_ids[$e] = base64_decode($io_email_id);//This is addded on 01-03-2022 for base64decoding by AKASH

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

				$DmiHoAllocations = TableRegistry::getTableLocator()->get($DmiFinalSubmitTable['ho_level_allocation']);
				$find_allocated_ho_mo = $DmiHoAllocations->find('all',array('conditions'=>array('customer_id IS'=>$customer_id, 'dy_ama IS'=>$dy_ama_email_id),'order' => array('id' => 'desc')))->first();
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



			//for Accounts  (Done by pravin 20-07-2018)
			if (in_array(8,$destination_array)) {

				//for chemist get chemist id from session added by laxmi on 09-02-2023
                if($application_type == 4){
                   $customer_id = $_SESSION['chemistId'];
                }

                if ($_SESSION['advancepayment'] == 'yes') {
                    $DmiApplicantPaymentDetails = TableRegistry::getTableLocator()->get('DmiAdvPaymentDetails');//added on 20-07-2017 by Pravin
                } else {
                    $DmiApplicantPaymentDetails = TableRegistry::getTableLocator()->get($DmiFinalSubmitTable['payment']);//added on 20-07-2017 by Pravin
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




			$filePath = 'D:/test_sms.txt';  // Replace this with the actual file path
			// Open the file in append mode and write the text
			$file = fopen($filePath, 'a');
			fwrite($file, $sms_message . PHP_EOL);
			fclose($file);

			$CustomersController = new CustomersController;

			$getLogTable = $CustomersController->SmsEmail->getLogTable($application_type);
			$smsLogTable = $getLogTable['sms_log_table'];
			$emailLogTable = $getLogTable['email_log_table'];



			//To send SMS on list of mobile nos.
			if (!empty($sms_message)) {

				$CustomersController->SmsEmail->sendSms($message_id,$destination_mob_nos_values,$sms_message,$template_id,$smsLogTable);

			}

			//To send Email on list of Email ids.
			if (!empty($email_message)) {

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

					case "submission_date":

						$message = str_replace("%%submission_date%%",(string) $this->getReplaceDynamicValues('submission_date',$customer_id),$message);
						break;

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

					case "company_id":

						$message = str_replace("%%company_id%%",(string) $this->getReplaceDynamicValues('company_id',$customer_id),$message);
						break;

					case "certificate_valid_upto":

						$message = str_replace("%%certificate_valid_upto%%",(string) $this->getReplaceDynamicValues('certificate_valid_upto',$customer_id),$message);
						break;

					case "premises_id":

						$message = str_replace("%%premises_id%%",(string) $customer_id,$message);
						break;

					case "firm_email":

						$message = str_replace("%%firm_email%%",(string) $this->getReplaceDynamicValues('firm_email',$customer_id),$message);
						break;

					case "firm_certification_type":

						$message = str_replace("%%firm_certification_type%%",(string) $this->getReplaceDynamicValues('firm_certification_type',$customer_id),$message);
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

					case "io_name":

						$message = str_replace("%%io_name%%",(string) $this->getReplaceDynamicValues('io_name',$customer_id),$message);
						break;

					case "io_mobile_no":

						$message = str_replace("%%io_mobile_no%%",(string) $this->getReplaceDynamicValues('io_mobile_no',$customer_id),$message);
						break;

					case "io_office":

						$message = str_replace("%%io_office%%",(string) $this->getReplaceDynamicValues('io_office',$customer_id),$message);
						break;

					case "io_email_id":

						$message = str_replace("%%io_email_id%%",(string) $this->getReplaceDynamicValues('io_email_id',$customer_id),$message);
						break;

					case "dyama_name":

						$message = str_replace("%%dyama_name%%",(string) $this->getReplaceDynamicValues('dyama_name',$customer_id),$message);
						break;

					case "dyama_mobile_no":

						$message = str_replace("%%dyama_mobile_no%%",(string) $this->getReplaceDynamicValues('dyama_mobile_no',$customer_id),$message);
						break;

					case "dyama_email_id":

						$message = str_replace("%%dyama_email_id%%",(string) $this->getReplaceDynamicValues('dyama_email_id',$customer_id),$message);
						break;

					case "jtama_name":

						$message = str_replace("%%jtama_name%%",(string) $this->getReplaceDynamicValues('jtama_name',$customer_id),$message);
						break;

					case "jtama_mobile_no":

						$message = str_replace("%%jtama_mobile_no%%",(string) $this->getReplaceDynamicValues('jtama_mobile_no',$customer_id),$message);
						break;

					case "jtama_email_id":

						$message = str_replace("%%jtama_email_id%%",(string) $this->getReplaceDynamicValues('jtama_email_id',$customer_id),$message);
						break;

					case "ama_name":

						$message = str_replace("%%ama_name%%",(string) $this->getReplaceDynamicValues('ama_name',$customer_id),$message);
						break;

					case "ama_mobile_no":

						$message = str_replace("%%ama_mobile_no%%",(string) $this->getReplaceDynamicValues('ama_mobile_no',$customer_id),$message);
						break;

					case "ama_email_id":

						$message = str_replace("%%ama_email_id%%",(string) $this->getReplaceDynamicValues('ama_email_id',$customer_id),$message);
						break;

					case "io_scheduled_date":

						$message = str_replace("%%io_scheduled_date%%",(string) $this->getReplaceDynamicValues('io_scheduled_date',$customer_id),$message);
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

					case "ho_mo_name":

						$message = str_replace("%%ho_mo_name%%",(string) $this->getReplaceDynamicValues('ho_mo_name',$customer_id),$message);
						break;

					case "ho_mo_mobile_no":

						$message = str_replace("%%ho_mo_mobile_no%%",(string) $this->getReplaceDynamicValues('ho_mo_mobile_no',$customer_id),$message);
						break;

					case "ho_mo_email_id":

						$message = str_replace("%%ho_mo_email_id%%", (string) $this->getReplaceDynamicValues('ho_mo_email_id',$customer_id),$message);
						break;

					case "home_link":

						$message = str_replace("%%home_link%%",(string) $_SERVER['HTTP_HOST'],$message);
						break;

					//For Replica And Chemist Module
					case "chemist_name":

						$message = str_replace("%%chemist_name%%",(string) $this->getReplaceDynamicValues('chemist_name',$customer_id),$message);
						break;

					case "chemist_id":

						$message = str_replace("%%chemist_id%%",(string) $this->getReplaceDynamicValues('chemist_id',$customer_id),$message);
						break;

					case "replica_commodities":

						$message = str_replace("%%replica_commodities%%",(string) $this->getReplaceDynamicValues('replica_commodities',$customer_id),$message);
						break;

					case "application_type":

						$message = str_replace("%%application_type%%",(string) $this->getReplaceDynamicValues('application_type',$customer_id),$message);
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


		if (!isset($_SESSION['application_type'])) { $_SESSION['application_type']=null; }

		$application_type = $_SESSION['application_type'];

		//This Session ID is Applied for the temporary Application Type is not present.
		if(Router::getRequest()->getParam('controller') == 'Dashboard'){
			if ($application_type == null) {
				$application_type = $_SESSION['application_type_temp'];
			}
		}


		//Load Models
		$DmiApplicationTypes = TableRegistry::getTableLocator()->get('DmiApplicationTypes');

		#For Application Type Text
		if ($application_type != null) {
			#Added the type cast INT on below query to resolve the boolean problem - Akash[24-11-2022]
			$get_application_type = $DmiApplicationTypes->find('all')->select(['application_type'])->where(['id IS'=>(int) $application_type,'delete_status IS NULL'])->first();
			$application_type_text = $get_application_type['application_type'];
		}else{
			$application_type_text = '';
		}


		#For Replica
		$chemist_name = null;
		$chemist_id = null;
		$replica_commodities = null;

		$CustomersController = new CustomersController;

		//Firm Type
		$firmType = $CustomersController->Customfunctions->firmType($customer_id);

		//Below Application Type = 7 condtion is added to by pass if the SMS is for Advance Payment -  AKASH [31-10-2022]
		$amount = '';
		if (!empty($application_type) && $application_type != 7) {

			$DmiFlowWiseTablesLists = TableRegistry::getTableLocator()->get('DmiFlowWiseTablesLists');
			$DmiFinalSubmitTable = $DmiFlowWiseTablesLists->find('all',array('conditions'=>array('application_type IS'=>$application_type)))->first();

			$DmiAllocations = TableRegistry::getTableLocator()->get($DmiFinalSubmitTable['allocation']);
			$DmiHoAllocations = TableRegistry::getTableLocator()->get($DmiFinalSubmitTable['ho_level_allocation']);
			$DmiFinalSubmits = TableRegistry::getTableLocator()->get($DmiFinalSubmitTable['application_form']);
			$DmiGrantCertificatesPdfs = TableRegistry::getTableLocator()->get($DmiFinalSubmitTable['grant_pdf']);
			$DmiApplicantPaymentDetails = TableRegistry::getTableLocator()->get($DmiFinalSubmitTable['payment']);//added on 20-07-2017 by Pravin


			#this query is added for varible amount - Akash[13-02-2023]
			if($CustomersController->Customfunctions->isOldApplication($customer_id,$application_type) != 'yes'){
				if($application_type != 8 && $application_type != 6 && $application_type != 5 && $application_type != 4 && $application_type != 9){
					$amount_paid = $DmiApplicantPaymentDetails->find()->select(['amount_paid'])->where([['customer_id IS' => $customer_id]])->order('id desc')->first();
					$amount = $amount_paid['amount_paid'];
				}

			}


		}


		// Description : for chemist training set packer id as customer id for temporary to get firm details
		// Author : Laxmi Bhadade
		// Date : 04-05-2023
		// For Module : Chemist Training
		 if($application_type == 4) {
		    $customer_id = $_SESSION['packer_id'];
		  }

		$DmiCustomers = TableRegistry::getTableLocator()->get('DmiCustomers');
		$DmiFirms = TableRegistry::getTableLocator()->get('DmiFirms');
		$DmiRoOffices = TableRegistry::getTableLocator()->get('DmiRoOffices');
		$DmiUsers = TableRegistry::getTableLocator()->get('DmiUsers');
		$DmiUserRoles = TableRegistry::getTableLocator()->get('DmiUserRoles');
		$MCommodity = TableRegistry::getTableLocator()->get('MCommodity');
		$DmiCertificateTypes = TableRegistry::getTableLocator()->get('DmiCertificateTypes');
		$DmiPaoDetails = TableRegistry::getTableLocator()->get('DmiPaoDetails');//added on 20-07-2017 by Pravin
		$DmiChemistRegistrations = TableRegistry::getTableLocator()->get('DmiChemistRegistrations');
		$dmi_adv_payment_details = TableRegistry::getTableLocator()->get('DmiAdvPaymentDetails');
		$dmi_ca_pp_lab_mapings = TableRegistry::getTableLocator()->get('DmiCaPpLabMapings');
		$dmi_replica_allotment_details = TableRegistry::getTableLocator()->get('DmiReplicaAllotmentDetails');
		$dmi_replica_allotment_pdfs = TableRegistry::getTableLocator()->get('DmiReplicaAllotmentPdfs');


		if (preg_match("/^[0-9]+\/[0-9]+$/",$customer_id,$matches)==1) {

			$fetch_applicant_data = $DmiCustomers->find('all',array('conditions'=>array('customer_id IS'=>$customer_id)))->first();
			$fetch_applicant_data = $fetch_applicant_data;

		} else {

			$fetch_firm_data = $DmiFirms->find('all',array('conditions'=>array('customer_id IS'=>$customer_id)))->first();
			$firm_data = $fetch_firm_data;

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

					}
				}


				$find_allocated_io = $DmiAllocations->find('all',array('conditions'=>array('customer_id IS'=>$customer_id,'level_3 IS'=>$ro_email_id),'order' => array('id' => 'desc')))->first();

				if (!empty($find_allocated_io)) {

					$io_email_id = $find_allocated_io['level_2'];

					$io_user_data = $DmiUsers->find('all',array('conditions'=>array('email IS'=>$io_email_id)))->first();

					if (!empty($io_user_data)) {

						$io_user_data = $io_user_data;

					}

				}

				//Get ho_mo_details (Done by pravin 23-07-2018)
				$find_allocated_ho_mo = $DmiHoAllocations->find('all',array('conditions'=>array('customer_id IS'=>$customer_id, 'dy_ama IS'=>$dy_ama_email_id),'order' => array('id' => 'desc')))->first();

				if (!empty($find_allocated_ho_mo)) {

					$ho_mo_email_id = $find_allocated_ho_mo['ho_mo_smo'];

					$fetch_ho_mo_data = $DmiUsers->find('all',array('conditions'=>array('email IS'=>$ho_mo_email_id)))->first();

					if (!empty($fetch_ho_mo_data)) {

						$ho_mo_mob_no = $fetch_ho_mo_data['phone'];

						$ho_mo_name = $fetch_ho_mo_data['f_name']." ".$fetch_ho_mo_data['l_name'];

					}

				}


				$get_io_scheduled_date = $DmiAllocations->find('all',array('conditions'=>array('customer_id IS'=>$customer_id),'order' => array('id' => 'desc')))->first();

					if (!empty($get_io_scheduled_date)) {//condition added on 11-10-2017 by Amol

						$io_scheduled_date = $get_io_scheduled_date['io_scheduled_date'];

					} else {

						$io_scheduled_date = '---';
					}


				//get renewal valid upto date
				//added on 05-02-2018 by Amol
				$each_application_grant_list = $DmiGrantCertificatesPdfs->find('list',array('conditions'=>array('customer_id IS'=>$customer_id)))->toArray();

					if (!empty($each_application_grant_list)) {


						$last_grant_details = $DmiGrantCertificatesPdfs->find('all',array('conditions'=>array('id'=>max($each_application_grant_list))))->first();

						$last_grant_date = $last_grant_details['date'];

						//get certificate valid upto date

						$certificate_valid_upto = $CustomersController->Customfunctions->getCertificateValidUptoDate($customer_id,$last_grant_date);

					} else {

						$certificate_valid_upto = '';
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


			//[-- For Replica/E-Code/Fifteen --]

			$getUserType = $CustomersController->Customfunctions->getUserType($_SESSION['username']);

			#This Block is added to see if the varibles are for replica is valid or not - Akash[21-11-2022]

			#CHEMIST
			$get_chemist_name = $DmiChemistRegistrations->find('all',array('conditions'=>array('chemist_id IS'=>$_SESSION['chemistId'],'delete_status IS NULL'),'order'=>'id desc'))->first();

			if (!empty($get_chemist_name)) {

				$get_chemist_name = $DmiChemistRegistrations->find('all',array('conditions'=>array('chemist_id IS'=>$_SESSION['chemistId'],'delete_status IS NULL'),'order'=>'id desc'))->first();
				$chemist_name = $get_chemist_name['chemist_fname']." ".$get_chemist_name['chemist_lname'];
				$chemist_id = $get_chemist_name['chemist_id'];

			}else{

				$get_chemist_id = $dmi_replica_allotment_pdfs->find('all')->select(['chemist_id'])->where(['customer_id IS' => $customer_id])->first();

				if(!empty($get_chemist_id)){
					$get_chemist_name = $DmiChemistRegistrations->find('all',array('conditions'=>array('chemist_id IS'=>$get_chemist_id['chemist_id'],'delete_status IS NULL'),'order'=>'id desc'))->first();
					$chemist_name = $get_chemist_name['chemist_fname']." ".$get_chemist_name['chemist_lname'];
					$chemist_id = $get_chemist_name['chemist_id'];
				}
			}


			#ADVANCE PAYMENT
			if ($_SESSION['advancepayment'] == 'yes') {

				$find_pao_id = $dmi_adv_payment_details->find('all',array('conditions'=>array('customer_id IS'=>$customer_id),'order' => array('id' => 'desc')))->first();

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


			#Replica COMMODITIES
			$getReplicaCommodity = $dmi_replica_allotment_details->find('all')->select(['commodity'])->where(['customer_id IS' => $customer_id])->group('commodity')->toArray();

		   /*  for chemist training not neccessary replica details
			i.e. apply ANDing conditon
			by laxmi Bhadade :
			04-05-2023:
			*/
			if(empty($getReplicaCommodity) && $application_type != 4){

				$getTableID = $DmiFirms->find('all')->select(['id'])->where(['customer_id'=>$customer_id])->first();

				$get_packer_customer_id = $dmi_ca_pp_lab_mapings->find()->select(['customer_id'])->where(['OR' => [['pp_id' => $getTableID['id']], ['lab_id' => $getTableID['id']]]])->first();

				#Empty condition is added - Akash [24-11-2022]
				if (!empty($get_packer_customer_id)) {
					$getReplicaCommodity = $dmi_replica_allotment_details->find('all')->select(['commodity'])->where(['customer_id IS' => $get_packer_customer_id['customer_id']])->group('commodity')->toArray();
				}
			}

               /*  for chemist training not neccessary replica details
				i.e. apply ANDing conditon
				by laxmi Bhadade :
				04-05-2023:
				*/
			if(!empty($getReplicaCommodity) && $application_type != 4){

				$i=0;
				$replica_commodities=array();

				foreach($getReplicaCommodity as $each){
					$getCommodity = $MCommodity->find('all',array('conditions'=>array('commodity_code IS'=>$each['commodity'])))->first();
					$replicacommodities[$i] = $getCommodity['commodity_name'];
					$i++;
				}

				$replica_commodities = implode(", ", $replicacommodities);
			}else{
				$replica_commodities=null;
			}


			$get_allotments = $dmi_ca_pp_lab_mapings->find('all')->where(['customer_id IS' => $customer_id])->toArray();

			/*
			if ($firmType != 3) {

				if(empty($get_allotments)){

					$getTableID = $DmiFirms->find('all')->select(['id'])->where(['customer_id'=>$customer_id])->first();
					$get_packer_customer_id = $dmi_ca_pp_lab_mapings->find()->select(['customer_id'])->where(['OR' => [['pp_id' => $getTableID['id']], ['lab_id' => $getTableID['id']]]])->first();
					$get_allotments = $dmi_ca_pp_lab_mapings->find('all')->where(['customer_id IS' => $get_packer_customer_id['customer_id']])->toArray();
				}
			}
			*/

			//For Printer Name and Lab Name
			if(!empty($get_allotments)){

				foreach($get_allotments as $each){

					#PRINTER
					if($each['map_type'] == 'pp'){
						$getPrinterName = $DmiFirms->find('all',array('conditions'=>array('id IS'=>$each['pp_id'])))->first();
						$printerName = $getPrinterName['firm_name'];
					}

					#LAB
					/**
					 * Added for own lab module, split own lab id and get details from dmi_ca_mapping_own_lab_details
					 * table
					 * @author shankhpal shende
					 * @version 15th June 2023
					 */
					if($each['map_type'] == 'lab'){

							if (strpos($each['lab_id'], "/Own") !== false) {
								// Get selected lab name
								$DmiCaMappingOwnLabDetails = TableRegistry::getTableLocator()->get('DmiCaMappingOwnLabDetails');
								$getLabName = $DmiCaMappingOwnLabDetails->find('all',array('conditions'=>array('own_lab_id IS'=>$each['lab_id'])))->first();
								$lab_name = $getLabName['lab_name'];

							}else{
							//get selected lab name
						$getLabName = $DmiFirms->find('all',array('conditions'=>array('id IS'=>$each['lab_id'])))->first();
						$lab_name = $getLabName['firm_name'];
					}


					}

					#PACKER
					$get_packer_name = $DmiFirms->find('all',array('conditions'=>array('customer_id IS'=>$each['customer_id'])))->first();
					$packer_name = $get_packer_name['firm_name'];
				}
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

			case "firm_certification_type":

				return $firm_certification_type;
				break;

			case "firm_email":

				//This new truncate function is applied to the below line in order to trim down the charateer that exceeds the 34 Character - Akash [19-05-2023]
				$firm_email = Text::truncate(base64_decode($firm_data['email']), 34, ['ellipsis' => '', 'exact' => true]);
				return $firm_email;
				break;

			case "submission_date":

				$submission_date = $final_submit_data;
				return $submission_date;
				break;

			case "commodities":

				//This new truncate function is applied to the below line in order to trim down the charateer that exceeds the 34 Character - Akash [19-05-2023]
				return Text::truncate($get_commodity_name, 34, ['ellipsis' => '', 'exact' => true]);
				break;

			case "amount":

				return $amount;
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

			case "io_name":

				//This new truncate function is applied to the below line in order to trim down the charateer that exceeds the 34 Character - Akash [19-05-2023]
				$io_name = Text::truncate($io_user_data['f_name']." ".$io_user_data['l_name'], 34, ['ellipsis' => '', 'exact' => true]);
				return $io_name;
				break;

			case "io_mobile_no":

				$io_mobile_no = $io_user_data['phone'];
				return $io_mobile_no;
				break;

			case "io_office":

				$io_office = $find_ro_email_id['ro_office'];
				return $io_office;
				break;

			case "io_email_id":

				$io_email_id = base64_decode($io_email_id);
				return $io_email_id;
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

			case "io_scheduled_date":

				return $io_scheduled_date;
				break;

			case "certificate_valid_upto"://added on 05-02-2018 by Amol

				return $certificate_valid_upto;
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

			case "ho_mo_email_id":  // Add new paramerter list (done by pravin 23-07-2018)

				return $ho_mo_email_id;
				break;

			case "ho_mo_mob_no":  // Add new paramerter list (done by pravin 23-07-2018)

				return $ho_mo_mob_no;
				break;

			case "ho_mo_name":  // Add new paramerter list (done by pravin 23-07-2018)

				return $ho_mo_name;
				break;

			//for replica
			case "chemist_name":

				//This new truncate function is applied to the below line in order to trim down the charateer that exceeds the 34 Character - Akash [19-05-2023]
				return Text::truncate($chemist_name, 34, ['ellipsis' => '', 'exact' => true]);
				break;

			case "chemist_id":

				return $chemist_id;
				break;

			case "replica_commodities":

				//This new truncate function is applied to the below line in order to trim down the charateer that exceeds the 34 Character - Akash [19-05-2023]
				return Text::truncate($replica_commodities, 34, ['ellipsis' => '', 'exact' => true]);
				break;

			case "application_type":

				return $application_type_text;
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
