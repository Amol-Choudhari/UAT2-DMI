<?php
//To access the properties of main controller used initialize function.
namespace app\Controller\Component;
use Cake\Controller\Controller;
use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\ConnectionManager;


class SmsEmailComponent extends Component {

	public $components= array('Session');
	public $controller = null;
	public $session = null;

	public function initialize(array $config): void {
		parent::initialize($config);
		$this->Controller = $this->_registry->getController();
		$this->Session = $this->getController()->getRequest()->getSession();
	}


	public function sendSms($message_id,$mobile_no,$sms_message,$template_id,$log_table){

		if (!empty($sms_message)) {

				/*
				$sender=urlencode("AGMARK");

				//$uname=urlencode("aqcms.sms");
				$uname="aqcms.sms";

				//$pass=urlencode("Y&nF4b#7q");
				$pass="Y%26nF4b%237q";

				$send=urlencode("AGMARK");

				$dest=$destination_mob_nos_values;

				$msg=urlencode($sms_message);

				// Initialize the URL variable
				//$URL="https://smsgw.sms.gov.in/failsafe/HttpLink";
				$URL="https://smsgw.sms.gov.in/failsafe/MLink";

				// Create and initialize a new cURL resource
				$ch = curl_init();
				// Set URL to URL variable
				curl_setopt($ch, CURLOPT_URL,$URL);
				// Set URL HTTPS post to 1
				curl_setopt($ch, CURLOPT_POST, true);
				// Set URL HTTPS post field values

				// Set URL HTTPS post field values
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

				$entity_id = '1101424110000041576'; //updated on 18-11-2020

				// if message lenght is greater than 160 character then add one more parameter "concat=1" (Done by pravin 07-03-2018)
				if(strlen($msg) <= 160 ){

					curl_setopt($ch, CURLOPT_POSTFIELDS,"username=$uname&pin=$pass&signature=$send&mnumber=$dest&message=$msg&dlt_entity_id=$entity_id&dlt_template_id=$template_id");

				}else{

					curl_setopt($ch, CURLOPT_POSTFIELDS,"username=$uname&pin=$pass&signature=$send&mnumber=$dest&message=$msg&concat=1&dlt_entity_id=$entity_id&dlt_template_id=$template_id");
				}

				// Set URL return value to True to return the transfer as a string
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				// The URL session is executed and passed to the browser
				$curl_output =curl_exec($ch);
				//echo $curl_output;

				//code to send sms ends here

				*/

			#SMS LOGS
			$DmiSentSmsLogs = TableRegistry::getTableLocator()->get($log_table);
			$DmiSentSmsLogs->saveLog($message_id,base64_encode($mobile_no),$sms_message,$template_id);
		}
	}




	// Send Email
	// Description : To send the email
	// @AUTHOR : Amol Chaudhari (c)
	// #CONTRIBUTER : Akash Thakre (u) (m)
	// DATE : 27-04-2021

	public function sendEmail($message_id,$email_message,$email_id,$email_subject,$template_id,$for_table){

		//email format to send on mail with content from master
		$email_format = 'Dear Sir/Madam' . "\r\n\r\n" .$email_message. "\r\n\r\n" .
		'Thanks & Regards,' . "\r\n" .
		'Directorate of Marketing & Inspection,' . "\r\n" .
		'Ministry of Agriculture and Farmers Welfare,' . "\r\n" .
		'Government of India.';

		$to = $email_id;
		$subject = $email_subject;
		$txt = $email_format;
		$headers = "From: dmiqc@nic.in";

		//mail($to,$subject,$txt,$headers, '-f dmiqc@nic.in');

		//commented above line and added below code with new email setting on 17-03-2023
		//require_once(ROOT . DS .'vendor' . DS . 'phpmailer' . DS . 'mail.php');
		//$from = "dmiqc@nic.in";
		//send_mail($from, $to, $subject, $txt);

		$DmiSentEmailLogs = TableRegistry::getTableLocator()->get($for_table);
		$DmiSentEmailLogs->saveLog($message_id,base64_encode($email_id),$email_message,$template_id);

	}



	public function getLogTable($application_type) {

		if (empty($application_type)) {
			$application_type = $_SESSION['application_type'];
		}

		if (!empty($application_type)) {

			#For New
			if ($application_type == 1) {

				$smsmodel = 'DmiNewSentSmsLogs';
				$emailmodel = 'DmiNewSentEmailLogs';
			}
			#For Renewal
			elseif ($application_type == 2) {

				$smsmodel = 'DmiRenewalSentSmsLogs';
				$emailmodel = 'DmiRenewalSentEmailLogs';
			}
			#For Change Request
			elseif ($application_type == 3) {

				$smsmodel = 'DmiChangeRequestSentSmsLogs';
				$emailmodel = 'DmiChangeRequestSentEmailLogs';
			}
			#For Chemist
			elseif ($application_type == 4) {

				$smsmodel = 'DmiChemistSentSmsLogs';
				$emailmodel = 'DmiChemistSentEmailLogs';
			}
			#For 15 Digit Code
			elseif ($application_type == 5) {

				$smsmodel = 'DmiFdcSentSmsLogs';
				$emailmodel = 'DmiFdcSentEmailLogs';
			}
			#For E Code
			elseif ($application_type == 6) {

				$smsmodel = 'DmiEcodeSentSmsLogs';
				$emailmodel = 'DmiEcodeSentEmailLogs';
			}
			#For Advance Payment
			elseif ($application_type == 7) {

				$smsmodel = 'DmiGeneralSentSmsLogs';
				$emailmodel = 'DmiGeneralSentEmailLogs';
			}
			#For Approval of Designated Person
			elseif ($application_type == 8) {

				$smsmodel = 'DmiAdpSentSmsLogs';
				$emailmodel = 'DmiAdpSentEmailLogs';
			}
			#For Surrender
			elseif ($application_type == 9) {

				$smsmodel = 'DmiSurrenderSentSmsLogs';
				$emailmodel = 'DmiSurrenderSentEmailLogs';
			}
			#For Routine Inspection
			elseif ($application_type == 10) {

				$smsmodel = 'DmiRtiSentSmsLogs';
				$emailmodel = 'DmiRtiSentEmailLogs';
			}
			#For Bianually Grading
			elseif ($application_type == 11) {

				$smsmodel = 'DmiBgrSentSmsLogs';
				$emailmodel = 'DmiBgrSentEmailLogs';
			}
			#For Appeal
			elseif ($application_type == 12) {

				$smsmodel = 'DmiAplSentSmsLogs';
				$emailmodel = 'DmiAplSentEmailLogs';
			}

		} else {
			$smsmodel = 'DmiGeneralSentSmsLogs';
			$emailmodel = 'DmiGeneralSentEmailLogs';
		}

		return array('sms_log_table'=>$smsmodel,'email_log_table'=>$emailmodel);
	}
}
?>
