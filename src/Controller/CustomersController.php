<?php

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\Network\Session\DatabaseSession;
use App\Network\Email\Email;
use App\Network\Request\Request;
use App\Network\Response\Response;
use Cake\Utility\Hash;
use Cake\Datasource\ConnectionManager;

class CustomersController extends AppController {

	var $name = 'Customers';

	public function initialize(): void {

		parent::initialize();
		//Load Components
		$this->loadComponent('Createcaptcha');
		$this->loadComponent('Customfunctions');
		$this->loadComponent('Authentication');
		$this->loadComponent('Mastertablecontent');
		$this->loadComponent('Randomfunctions');
		$this->loadComponent('SmsEmail');
		//Set Helpers
		$this->viewBuilder()->setHelpers(['Form', 'Html', 'Time']);
	}


	//Before Filter
	public function beforeFilter($event) {

		parent::beforeFilter($event);
		$customer_last_login = $this->Customfunctions->customerLastLogin();
		$this->set('customer_last_login', $customer_last_login);

			//Show button on Side menu
		$this->Beforepageload->showButtonOnSecondaryHome();

		//created and called function to check applicant is valid for renewal or not
		$show_renewal_btn = $this->Customfunctions->checkApplicantValidForRenewal($this->Session->read('username'));
		$this->set('show_renewal_btn', $show_renewal_btn);

		//Find the value of "is_already_granted" flag status to redirect the application on appropriate new application or old application controller
		//Done by pravin 27-09-2017
		$this->loadModel('DmiFirms');
		$is_already_granted = null;
		$get_is_already_granted = $this->DmiFirms->find('all', array('fields' => 'is_already_granted', 'conditions' => array('customer_id IS' => $this->Session->read('username'))))->first();

		if (!empty($get_is_already_granted)) {

			$is_already_granted = $get_is_already_granted['is_already_granted'];
		}

		$this->set('is_already_granted', $is_already_granted);
	}


	//To create captcha code, called from component on 14-07-2017 by Amol
	public function createCaptcha() {

		$this->autoRender = false;
		$this->Createcaptcha->createCaptcha();
	}


	//To Refresh Captcha Code
	public function refreshCaptchaCode() {

		$this->autoRender = false;
		$this->Createcaptcha->refreshCaptchaCode();
		exit;
	}


	//added on 04-08-2017 by Amol to set session variable according to id and used in view file to show heading
	public function loginCustomerRedirect($id) {

		if ($id == 1) {

			$this->getRequest()->getSession()->write('login_to', 'ca');
			$this->redirect('/customers/login-customer');

		} elseif ($id == 2) {

			$this->getRequest()->getSession()->write('login_to', 'printing');
			$this->redirect('/customers/login-customer');

		} elseif ($id == 3) {

			$this->getRequest()->getSession()->write('login_to', 'lab');
			$this->redirect('/customers/login-customer');
		}
	}


	//Login Customer function start
	public function loginCustomer() {

		//Set the Layout
		$this->viewBuilder()->setLayout('form_layout');
		//load model
		$this->loadModel('DmiCustomers');
		//this condition added on 05-08-2017 by Amol to redirect to home
		if ($this->getRequest()->getSession()->read('login_to') == null) {
			$this->redirect('/');
		}

		// set variables to show popup messages from view file
		$message = '';
		$message_theme = '';
		$redirect_to = '';
		$already_loggedin_msg = 'no';

		if ($this->request->is('post')) {

			//check login lockout status, applied on 24-04-2018 by Amol
			$lockout_status = $this->Customfunctions->checkLoginLockout('DmiCustomerLogs', $this->request->getData('customer_id'));

			if ($lockout_status == 'yes') {

				$message = 'Sorry... Your account is disabled for today, on account of 3 login failure.';
				$redirect_to = $this->request->getAttribute('webroot');

			} else {


				if (substr_count($this->request->getData('customer_id'), "/") != 0) {

					$split_customer_id = explode('/',(string) $this->request->getData('customer_id')); #For Deprecations

					//added below conditions on 15-02-2018 by Amol
					if ($split_customer_id[1] == 1 && $this->getRequest()->getSession()->read('login_to') != 'ca') {

						$message = 'Please Login from Certificate Of Authorisation Window. Thankyou';
						$redirect_to = $this->request->getAttribute('webroot');

					} elseif ($split_customer_id[1] == 2 && $this->getRequest()->getSession()->read('login_to') != 'printing') {

						$message = 'Please Login from Certificate of Printing Permission Window. Thankyou';
						$redirect_to = $this->request->getAttribute('webroot');

					} elseif ($split_customer_id[1] == 3 && $this->getRequest()->getSession()->read('login_to') != 'lab') {

						$message = 'Please Login from Certificate of Approval of Laboratory Window. Thankyou';
						$redirect_to = $this->request->getAttribute('webroot');

					} else {

						$randsalt = $this->getRequest()->getSession()->read('randSalt');
						$captchacode1 = $this->getRequest()->getSession()->read('code');
						$logindata = $this->request->getData();
						$username = $this->request->getData('customer_id');
						$password = $this->request->getData('password');
						$captcharequest = $this->request->getData('captcha');

						$countspecialchar = substr_count($username, "/");

						if ($countspecialchar == 1) {

							$table = 'DmiCustomers';
							// calling login library function
							$login_result = $this->Authentication->customerLoginLib($table, $username, $password, $randsalt);
							// show user login failed messgae (by pravin 27/05/2017)
							if ($login_result == 1) {
								//this custom functionn is called on 08-04-2021, to show remaining login attempts
								$remng_attempts_msg = $this->showRemainingLoginAttempts('DmiCustomerLogs', $this->request->getData('customer_id'));
								$message = 'Username or password do not match. <br>' . $remng_attempts_msg;
								$message_theme = 'failed';
								$redirect_to = 'login_customer';

							} elseif ($login_result == 2) {

								//this custom functionn is called on 08-04-2021, to show remaining login attempts
								$remng_attempts_msg = $this->showRemainingLoginAttempts('DmiCustomerLogs', $this->request->getData('customer_id'));
								$message = 'Username or password do not match. <br>' . $remng_attempts_msg;
								$message_theme = 'failed';
								$redirect_to = 'login_customer';

							} elseif ($login_result == 3) {

								$captcha_error_msg = 'Sorry... Wrong Code Entered';
								$message_theme = 'failed';
								$this->set('captcha_error_msg', $captcha_error_msg);
								$this->set('already_loggedin_msg',$already_loggedin_msg);
								return null;
								exit;

							} elseif ($login_result == 4) {

								//get applicant email id and apply masking before showing in message by Amol on 25-02-2021
								$get_email_id = $this->DmiCustomers->find('all', array('fields' => 'email', 'conditions' => array('customer_id' => $username)))->first();
								$email_id = $this->Customfunctions->getMaskedValue(base64_decode($get_email_id['email']), 'email'); #Base64Decode Applied - Akash[27-01-2023]
								$message = 'Your password has been expired, The link to reset password is sent on email id ' . $email_id . ', Please contact the concerned office.';
								$redirect_to = 'login_customer';

							//created/updated/added on 25-06-2021 for multiple logged in check security updates, by Amol
							}elseif($login_result == 5){

								$already_loggedin_msg = 'yes';
							}

						} elseif ($countspecialchar == 3) {

							$table = 'DmiFirms';
							// calling login library function
							$login_result = $this->Authentication->customerLoginLib($table, $username, $password, $randsalt);

							// show user login failed messgae (by pravin 27/05/2017)
							if ($login_result == 1) {

								$remng_attempts_msg = $this->showRemainingLoginAttempts('DmiCustomerLogs', $this->request->getData('customer_id'));
								$message = 'Username or password do not match. <br>' . $remng_attempts_msg;
								$message_theme = 'failed';
								$redirect_to = 'login_customer';

							} elseif ($login_result == 2) {

								$remng_attempts_msg = $this->showRemainingLoginAttempts('DmiCustomerLogs', $this->request->getData('customer_id'));
								$message = 'Username or password do not match. <br>' . $remng_attempts_msg;
								$message_theme = 'failed';
								$redirect_to = 'login_customer';

							} elseif ($login_result == 3) {

								$message = 'Sorry...Wrong Captcha Code Entered';
								$message_theme = 'failed';
								$redirect_to = 'login_customer';

							} elseif ($login_result == 4) {
								//get applicant email id and apply masking before showing in message by Amol on 25-02-2021
								$get_email_id = $this->$table->find('all', array('fields' => 'email', 'conditions' => array('customer_id' => $username)))->first();
								$email_id = $this->Customfunctions->getMaskedValue(base64_decode($get_email_id['email']), 'email'); #Base64Decode Applied - Akash[27-01-2023]
								$message = 'Your password has been expired, The link to reset password is sent on email id ' . $email_id . ', Please contact the concerned office.';
								$redirect_to = 'login_customer';

							//created/updated/added on 25-06-2021 for multiple logged in check security updates, by Amol
							}elseif($login_result == 5){

								$already_loggedin_msg = 'yes';
							}

						} else {

							$message = 'Username or password do not match or your account is freezed';
							$message_theme = 'failed';
							$redirect_to = 'login_customer';
						}

					}

				} else {

					$message = 'User Id entered is not valid';
					$message_theme = 'failed';
					$redirect_to = 'login_customer';
				}

			}
		}

		// set variables to show popup messages from view file
		$this->set('already_loggedin_msg',$already_loggedin_msg);
		$this->set('message', $message);
		$this->set('message_theme', $message_theme);
		$this->set('redirect_to', $redirect_to);
	}



	//Register Customer Fucntion starts
	public function registerCustomer() {

		//Load Models
		$this->loadModel('DmiStates');
		$this->loadModel('DmiDistricts');
		$this->loadModel('DmiDocumentLists');
		$this->loadModel('DmiCustomers');
		$this->loadModel('DmiCustomersHistoryLogs');

		//Set the Layout
		$this->viewBuilder()->setLayout('form_layout');

		// Apply "Order by" clause to get state list by order wise (Done By Pravin 10-01-2018)
		$states = $this->DmiStates->find('list', array('valueField' => 'state_name', 'conditions' => array('OR' => array('delete_status IS NULL', 'delete_status =' => 'no')), 'order' => array('state_name')))->toArray();
		$this->set('states', $states);

		$districts = $this->DmiDistricts->find('list', array('valueField' => 'district_name', 'conditions' => array('state_id' => 1, 'OR' => array('delete_status IS NULL', 'delete_status =' => 'no'))))->toArray();
		$this->set('districts', $districts);

		$document_lists = $this->DmiDocumentLists->find('list', array('valueField' => 'document_name', 'conditions' => array('delete_status IS NULL')))->toArray(); // Changed the query on the 09-08-2022 by Akash
		$this->set('document_lists', $document_lists);

		// set variables to show popup messages from view file
		$message = '';
		$message_theme = '';
		$redirect_to = '';


		if ($this->request->is('post')) {

			//this condition is added on 22-03-2018 by Amol //aadhar authentication will only done when concent is checked
			if (!empty($this->request->getData('once_card_no')) && $this->request->getData('aadhar_auth_check') == 1) {

				//below function is called to authenticate Aadhar no. before registering applicant.//added on 20-11-2017 by Amol
				$esign = new EsignController();
				$aadhar_authentication_result = $esign->requestAadharAuthentication($this->request->getData('once_card_no'), $this->request->getData('aadhar_otp'));//added new parameter $aadhar_otp on 25-08-2018

				if ($aadhar_authentication_result == true) {
					//Process
				} else {
					$this->set('return_error_msg','Sorry.. Aadhar Authentication Failed, Please try again.');
					return null;
					exit;
				}
			}

			if (!$this->Customfunctions->validateUniquePostData($this->request->getData('mobile'), 'mobile') == 1) {

				$this->set('return_error_msg','Please enter proper Mobile no.');
				return null;
				exit;
			}

			if (!$this->Customfunctions->validateUniquePostData($this->request->getData('email'), 'email') == 1) {

				$this->set('return_error_msg','Please enter proper Email id');
				return null;
				exit;
			}

			//this condition is added on 22-03-2018 by Amol //only validate when aadhar & concent provided
			if (!empty($this->request->getData('once_card_no')) && $this->request->getData('aadhar_auth_check') == 1) {

				if (!$this->Customfunctions->validateUniquePostData($this->request->getData('once_card_no'), 'aadhar') == 1) {

					$this->set('return_error_msg','Please enter proper Aadhar Card no.');
					return null;
					exit;
				}
			}

			$randsalt = $this->getRequest()->getSession()->read('randSalt');
			$captchacode1 = $this->getRequest()->getSession()->read('code');
			$usersData = $this->request->getData();
			$Checkemailexist = $this->DmiCustomers->find('all', array('fields' => 'email', 'conditions' => array('email IS' => $usersData['email'])))->first();

			// verifying captcha code
			if ($this->request->getData('captcha') != "" && $captchacode1 == $this->request->getData('captcha')) {

				if ($Checkemailexist == null) {

					$last_record_id_query = $this->DmiCustomers->find('all', array('fields' => 'id', 'order' => array('id desc')))->first();
					$last_record_id = $last_record_id_query['id'];
					$last_customer_id_query = $this->DmiCustomers->find('all', array('fields' => 'customer_id', 'conditions' => array('id' => $last_record_id)))->first();

					if (!empty($last_customer_id_query)) {

						$last_customer_id = $last_customer_id_query['customer_id'];
						$split = explode('/', $last_customer_id);
						$splited_id = $split[0];
					} else {
						$splited_id = 100;
					}

					$new_customer_id = ($splited_id + 1) . '/' . date('Y');

					$htmlencodedfname = htmlentities($this->request->getData('f_name'), ENT_QUOTES);
					$htmlencodedmname = htmlentities($this->request->getData('m_name'), ENT_QUOTES);
					$htmlencodedlname = htmlentities($this->request->getData('l_name'), ENT_QUOTES);
					$htmlencodedstreetaddress = htmlentities($this->request->getData('street_address'), ENT_QUOTES);
					$htmlencodedpostalcode = htmlentities($this->request->getData('postal_code'), ENT_QUOTES);
					$htmlencodedemail = htmlentities($this->request->getData('email'), ENT_QUOTES);
					$htmlencodedmobile = htmlentities($this->request->getData('mobile'), ENT_QUOTES);
					$htmlencodedlandline = htmlentities($this->request->getData('landline'), ENT_QUOTES);
					$htmlencodedphoto_id_no = htmlentities($this->request->getData('photo_id_no'), ENT_QUOTES);

					$table = 'DmiDocumentLists';
					$post_input_request = $this->request->getData('document');
					$document = $this->Customfunctions->dropdownSelectInputCheck($table, $post_input_request);//calling library function


					//for state
					$table = 'DmiStates';
					$post_input_request = $this->request->getData('state');
					$state = $this->Customfunctions->dropdownSelectInputCheck($table, $post_input_request);//calling library function

					//for district
					$table = 'DmiDistricts';
					$post_input_request = $this->request->getData('district');
					$district = $this->Customfunctions->dropdownSelectInputCheck($table, $post_input_request);//calling library function

					$htmlencodedemail = base64_encode($htmlencodedemail);//for email encoding

					$DmiCustomersEntity = $this->DmiCustomers->newEntity(array(
						'customer_id' => $new_customer_id,
						'f_name' => $htmlencodedfname,
						'm_name' => $htmlencodedmname,
						'l_name' => $htmlencodedlname,
						'street_address' => $htmlencodedstreetaddress,
						'state' => $state,
						'district' => $district,
						'postal_code' => $htmlencodedpostalcode,
						'email' => $htmlencodedemail,
						'password' => '91c8559eb34ab5e1ab86f9e80d9753c59b7da0d0e025ec8e7785f19e7852ca428587cdb4f02b5c67d1220ca5bb440b5592cd76b1c13878d7f10a1e568014f4dc', //Agmark123@
						//'password'=>'3c9909afec25354d551dae21590bb26e38d53f2173b8d3dc3eee4c047e7ab1c1eb8b85103e3be7ba613b31bb5c9c36214dc9f14a42fd7a2fdb84856bca5c44c2',//123
						'mobile' => base64_encode($htmlencodedmobile), //This is added on 27-04-2021 for base64encoding by AKASH
						'landline' => base64_encode($htmlencodedlandline),//This is added on 27-04-2021 for base64encoding by AKASH
						//'once_card_no'=>$encrypted_aadhar, //commented on 22-03-2018
						'document' => $document,
						'photo_id_no' => $htmlencodedphoto_id_no,
						//'file'=>$uploadedfile,
						'created' => date('Y-m-d H:i:s'),
						'modified' => date('Y-m-d H:i:s')
					));

					if ($this->DmiCustomers->save($DmiCustomersEntity)) {

						$DmiCustomersHistoryLogsEntity = $this->DmiCustomersHistoryLogs->newEntity(array(
							'customer_id' => $new_customer_id,
							'f_name' => $htmlencodedfname,
							'm_name' => $htmlencodedmname,
							'l_name' => $htmlencodedlname,
							'street_address' => $htmlencodedstreetaddress,
							'state' => $state,
							'district' => $district,
							'postal_code' => $htmlencodedpostalcode,
							'email' => $htmlencodedemail,
							'password' => '91c8559eb34ab5e1ab86f9e80d9753c59b7da0d0e025ec8e7785f19e7852ca428587cdb4f02b5c67d1220ca5bb440b5592cd76b1c13878d7f10a1e568014f4dc', //Agmark123@
							//'password'=>'3c9909afec25354d551dae21590bb26e38d53f2173b8d3dc3eee4c047e7ab1c1eb8b85103e3be7ba613b31bb5c9c36214dc9f14a42fd7a2fdb84856bca5c44c2',//123
							'mobile' => base64_encode($htmlencodedmobile), //This is added on 27-04-2021 for base64encoding by AKASH
							'landline' => base64_encode($htmlencodedlandline), //This is added on 27-04-2021 for base64encoding by AKASH
							//'once_card_no'=>$encrypted_aadhar, //commented on 22-03-2018
							'document' => $document,
							'photo_id_no' => $htmlencodedphoto_id_no,
							//'file'=>$uploadedfile,
							'created' => date('Y-m-d H:i:s'),
							'modified' => date('Y-m-d H:i:s')
						));

						$this->DmiCustomersHistoryLogs->save($DmiCustomersHistoryLogsEntity);

						//called function to send link for reset password on registered email //on 13-02-2018 by Amol
						$this->Authentication->forgotPasswordLib('DmiCustomers', $htmlencodedemail, $new_customer_id);

						$primary_registered = 'done';
						$this->set('primary_registered', $primary_registered);
						$this->set('new_customer_id', $new_customer_id);
						$this->set('htmlencodedemail', $htmlencodedemail);

						#SMS: New Customer Registered
						$this->DmiSmsEmailTemplates->sendMessage(1, $new_customer_id);

						//saving log for passes attempts of aadhar authentication //added on 16-06-2020 by Amol, on suggestion from Tarun Sir to take logs
						if (!empty($this->request->getData('once_card_no')) && $this->request->getData('aadhar_auth_check') == 1) {

							$aadhar_txn_id=null;
							$this->loadModel('DmiOnceTokenDetails');
							$onceTokenEntity = $this->DmiOnceTokenDetails->newEntity(array(
								'user_id' => $new_customer_id,
								'once_token_id' => null,
								'created' => date('Y-m-d H:i:s'),
								'status' => 'Passed',
								'txn_id' => $aadhar_txn_id
							));

							$this->DmiOnceTokenDetails->save($onceTokenEntity);
						}

					} else {

						$message = 'Your details are not saved please check again';
						$message_theme = 'warning';
						$redirect_to = 'register_customer';

					}

				} else {

					//above code commented & below code added on 13-07-2018 by pravin
					$used_email_error_msg = 'This email id is already registered with us. Please register with another email id.';
					$this->set('used_email_error_msg', $used_email_error_msg);
					return null;
					exit;
				}

			} else {

				$captcha_error_msg = 'Sorry... Wrong Code Entered';
				$this->set('captcha_error_msg', $captcha_error_msg);
				return null;
				exit;
			}
		}

		// set variables to show popup messages from view file
		$this->set('message', $message);
		$this->set('message_theme',$message_theme);
		$this->set('return_error_msg',null);
		$this->set('redirect_to', $redirect_to);

	}


	//Primary HOME Method
	public function primaryHome() {

		//Set the Layout
		$this->viewBuilder()->setLayout('corporate_customer');
		//Load Models
		$this->loadModel('DmiDistricts');
		$this->loadModel('DmiFirms');
		$this->loadModel('DmiFinalSubmits');
		$this->loadModel('DmiCertificateTypes');
		$this->loadModel('DmiStates');
		$this->loadModel('MCommodityCategory');
		$this->loadModel('DmiDistricts');

		if ($this->getRequest()->getSession()->read('username') == null) {

			$this->customAlertPage("Sorry You are not authorized to view this page..");
			exit;

		} else {//this else portion added on 10-07-2017 by Amol to allow only logged in Applicant

			//checking applicant id pattern ex.102/1/PUN/006
			if (preg_match("/^[0-9]+\/[0-9]+$/", $this->getRequest()->getSession()->read('username'), $matches) == 1) {
				//Give Permission
			} else {

				$this->customAlertPage("Sorry You are not authorized to view this page..");
				exit;
			}
		}

		if (null !== ($this->request->getData('search_firms'))) {

			$firm_name = strtolower($this->request->getData('firm_name'));
			$firm_district = strtolower($this->request->getData('firm_district'));

			$id_not_empty = array();
			$name_not_empty = array();
			$district_not_empty = array();

			if ($this->request->getData('firm_id') != null) {

				$id_not_empty = array('customer_primary_id' => $this->getRequest()->getSession()->read('username'), 'customer_id' => $this->request->getData('firm_id'));
			}

			if ($this->request->getData('firm_name') != null) {

				$name_not_empty = array('customer_primary_id' => $this->getRequest()->getSession()->read('username'), 'LOWER(firm_name) LIKE' => '%' . $firm_name . '%');

			}


			if ($this->request->getData('firm_district') != null) {

				$find_district_id = $this->DmiDistricts->find('all', array('fields' => 'id', 'conditions' => array('LOWER(district_name) Like' => '%' . $firm_district . '%')))->first();

				if (!empty($find_district_id)) {

					$district_id = $find_district_id['id'];

				} else {

					$district_id = null;
				}

				$district_not_empty = array('customer_primary_id' => $this->getRequest()->getSession()->read('username'), 'district IS' => $district_id);

			}

			$this->paginate = array('conditions' => Hash::merge($id_not_empty, $name_not_empty, $district_not_empty, array('delete_status' => null)), 'limit' => 6, 'order' => array('id' => 'desc'));

			//this condition added on 05-05-2017 by Amol(to solve search in pagination error)
			if ($this->request->getData('firm_id') == '' && $this->request->getData('firm_name') == '' && $this->request->getData('firm_district') == '') {
				//for pagination
				$firms_details = $this->paginate('Dmi_firm');
			} else {
				// For normal search, show result without pagination
				$firms_details = $this->DmiFirms->find('all', array('order' => array('id' => 'asc'), 'conditions' => Hash::merge($id_not_empty, $name_not_empty, $district_not_empty, array('delete_status IS NULL'))))->toArray();
			}

		} else {

			$this->paginate = array('conditions' => array('customer_primary_id IS' => $this->getRequest()->getSession()->read('username'), array('delete_status IS NULL')),'order' => array('id' => 'desc'));
			//for pagination
			$firms_details = $this->paginate('DmiFirms');
			$firms_details = $firms_details->toArray();

		}

		//displaced from above loop to outside and make it single for both scenario.//on 05/5/2017 by Amol
		if (!empty($firms_details)) {

			$i = 0;
			$application_status = array();//added on 11-10-2017 by Amol

			foreach ($firms_details as $firms_detail) {

				$customer_id = $firms_detail['customer_id'];
				$split_customer_id = explode('/',(string) $customer_id); #For Deprecations

				//added on 05/05/2017 by Amol(to show delete firm button) //check this applicant id is final submitted or not to show dlete firm button
				$final_submit_done[$i] = $this->DmiFinalSubmits->find('all', array('conditions' => array('customer_id IS' => $customer_id)))->first();

				//fetch id from array and display values from table in list
				$certification_type = $this->DmiCertificateTypes->find('all', array('fields' => 'certificate_type', 'conditions' => array('id IS' => $firms_detail['certification_type'])))->first();
				$firms_details[$i]['certification_type'] = $certification_type['certificate_type'];

				$commodity = $this->MCommodityCategory->find('all', array('fields' => 'category_name', 'conditions' => array('category_code IS' => $firms_detail['commodity'])))->first();
				$firms_details[$i]['commodity'] = $commodity['category_name'];

				$state = $this->DmiStates->find('all', array('fields' => 'state_name', 'conditions' => array('id IS' => $firms_detail['state'])))->first();
				$firms_details[$i]['state'] = $state['state_name'];

				$district = $this->DmiDistricts->find('all', array('fields' => 'district_name', 'conditions' => array('id IS' => $firms_detail['district'])))->first();
				$firms_details[$i]['district'] = $district['district_name'];

				//added on 11-10-2017 by Amol
				$application_status[$i] = $this->Customfunctions->getApplicationCurrentStatus($customer_id);

				$i = $i + 1;
			}

			$this->set('firms_details', $firms_details);
			$this->set('final_submit_done', $final_submit_done);
			$this->set('application_status', $application_status);//added on 11-10-2017 by Amol

		} else {

			$this->set('firms_details', $firms_details = array());
		}

	}


	//Reset Password function Starts - Updated with new changes on 28-04-2021 By Akash.
	public function resetPassword() {

		// set variables to show popup messages from view file
		$message = '';
		$message_theme = '';
		$redirect_to = '';
		//Set the layout
		$this->viewBuilder()->setLayout('form_layout');

		if (empty($_GET['$key']) || empty($_GET['$id'])) {

			$this->customAlertPage("Sorry You are not authorized to view this page..");
			exit;

		} else {

			$key_id = $_GET['$key'];
			// Added the urldecode funtion to fix the issue of +,<,# etc issue in gettin through get parameter // added on 26/11/2018
			$user_id = $this->Authentication->decrypt($_GET['$id']);
			$this->set('user_id', $user_id);
			//check Applicant type (primary/secondary)
			$countspecialchar = substr_count($user_id, "/");

			if ($countspecialchar == 1) {

				$table = 'DmiCustomers';

			} elseif ($countspecialchar == 3) {

				$table = 'DmiFirms';

			} else {

				$this->customAlertPage("Sorry You are not authorized to view this page..");
				exit;
			}

			$this->loadModel($table);
			$this->loadModel('DmiApplicantsResetpassKeys');

			//fetch applicant details
			$get_record_details = $this->$table->find('all', array('conditions' => array('customer_id IS' => $user_id)))->first();
			$record_id = $get_record_details['id'];

			//call function to check valid key
			$valid_key_result = $this->DmiApplicantsResetpassKeys->checkValidKey($user_id, $key_id);

			if ($valid_key_result == 1) {

				if ($this->request->is('post')) {

					$randsalt = $this->Session->read('randSalt');
					$username = $this->request->getData('customer_id');
					$countspecialchar = substr_count($username, "/");
					$postdata = $this->request->getData();

					if ($countspecialchar == 1) {
						$table = 'DmiCustomers';
					} elseif ($countspecialchar == 3) {
						$table = 'DmiFirms';
					} else {
						$user_id_not_valid_msg = 'This User Id is not valid';
						$this->set('user_id_not_valid_msg', $user_id_not_valid_msg);
						return null;
						exit;
					}

					$newpassdata = $this->request->getData('new_password');

					// calling reset password library function
					$reset_pass_result = $this->Authentication->resetPasswordLib($table, $username, $newpassdata, $randsalt,$postdata);

					if ($reset_pass_result == 1) {

						$this->Customfunctions->saveActionPoint('Reset Password (Email Not Matched)','Failed',$user_id); #Action
						$email_id_not_matched_msg = 'Email id & User Id not Matched.';
						$this->set('email_id_not_matched_msg', $email_id_not_matched_msg);
						return null;
						exit;

					} elseif ($reset_pass_result == 2) {

						$this->Customfunctions->saveActionPoint('Reset Password (Incorrect Captcha)','Failed',$user_id); #Action
						$incorrect_captcha_msg = 'Incorrect Captcha code entered.';
						$this->set('incorrect_captcha_msg', $incorrect_captcha_msg);
						return null;
						exit;

					} elseif ($reset_pass_result == 3) {

						$this->Customfunctions->saveActionPoint('Reset Password (Password Not Macthed)','Failed',$user_id); #Action
						$comfirm_pass_msg = 'Confirm password not matched';
						$this->set('comfirm_pass_msg', $comfirm_pass_msg);
						return null;
						exit;

					} elseif ($reset_pass_result == 4) {

						$this->Customfunctions->saveActionPoint('Reset Password (Password is Same as Last)','Failed',$user_id); #Action
						// SHOW ERROR MESSAGE IF NEW PASSWORD FOUND UNDER LAST THREE PASSWORDS OF USER // By Aniket Ganvir dated 16th NOV 2020
						$comfirm_pass_msg = 'This password matched with your last three passwords, Please enter different password';
						$this->set('comfirm_pass_msg', $comfirm_pass_msg);
						return null;
						exit;

					} else {

						$this->Customfunctions->saveActionPoint('Reset Password','Success',$user_id); #Action
						//update link key table status to 1 for successfully
						$this->DmiApplicantsResetpassKeys->updateKeySuccess($user_id, $key_id);
						$message = 'Password Changed Successfully';
						$message_theme = 'success';
						$redirect_to = '../../customers/login_customer';

					}


				}

			} elseif ($valid_key_result == 2) {

				$message = 'Sorry.. This link to reset password is already used or expired. Please proceed through "Forgot Password" again.';
				$message_theme = 'failed';
				$redirect_to = '../forgot_password';

			}

		}


		// set variables to show popup messages from view file
		$this->set('message', $message);
		$this->set('message_theme',$message_theme);
		$this->set('redirect_to', $redirect_to);

	}


	//Forgot Password method starts
	public function forgotPassword() {

		// set variables to show popup messages from view file
		$message = '';
		$message_theme = '';
		$redirect_to = '';
		//set the Layout
		$this->viewBuilder()->setLayout('form_layout');

		if ($this->request->is('post')) {

			if ($this->request->getData('captcha') != "" && $this->Session->read('code') == $this->request->getData('captcha')) {

				$username = $this->request->getData('customer_id');

				$countspecialchar = substr_count($username, "/");

				if ($countspecialchar == 1) {
					$table = 'DmiCustomers';
				} elseif ($countspecialchar == 3) {
					$table = 'DmiFirms';
				} elseif ($countspecialchar == 2) {
					$table = 'DmiChemistRegistrations';
				} else {
					$message = 'Sorry...User Id entered is not valid';
					$message_theme = 'failed';
					$redirect_to = 'forgot_password';
				}

				$this->loadModel($table);

				$emailforrecovery = $this->request->getData('email');

				// For chemist module, Done by Pravin Bhakare 4/08/2021
				//check if Customer ID & Email Match in record.
				if($countspecialchar == 2){

					$check_valid_record = $this->$table->find('all', array('conditions' => array('email IS' => base64_encode($emailforrecovery), 'chemist_id IS' => $username)))->first();

				} else {

					$check_valid_record = $this->$table->find('all', array('conditions' => array('email IS' => base64_encode($emailforrecovery), 'customer_id IS' => $username)))->first();
				}

				if (empty($check_valid_record)) {

					$message = 'Sorry... Provided Applicant ID & Email Id does not Matched.';
					$message_theme = 'failed';
					$redirect_to = 'forgot_password';

				} else {

					$forgot_password_result = $this->Authentication->forgotPasswordLib($table, $emailforrecovery, $username);//added new parameter username on 25-10-2018

					// show forgot password failed messgae (by pravin 27/05/2017)
					if ($forgot_password_result == 1) {

						$message = 'Sorry Restricted... This email is not authorized';
						$message_theme = 'failed';
						$redirect_to = 'forgot_password';

					} elseif ($forgot_password_result == 2) {

						//get applicant email id and apply masking before showing in message by Amol on 25-02-2021
						$emailforrecovery = $this->Customfunctions->getMaskedValue($emailforrecovery, 'email');
						$message = 'Change password link sent on ' . $emailforrecovery;
						$message_theme = 'success';
						$redirect_to = 'forgot_password';

					}

				}

			} else {

				$message = 'Sorry...Wrong Captcha Code Entered';
				$message_theme = 'failed';
				$redirect_to = 'forgot_password';
			}
		}
		// set variables to show popup messages from view file
		$this->set('message', $message);
		$this->set('message_theme', $message_theme);
		$this->set('redirect_to', $redirect_to);

	}
	//Forgot Password method ends



	// Method to view and update primary customer profile
	public function customerProfile() {

		// error_reporting('0'); // needtoremove
		// SET MENU NAME FOR CURRENT ACTIVE MENU IN SIDEBAR
		$this->set('current_menu', 'menu_profile');

		$this->loadModel('DmiPermittedOnceUpdations');
		$this->loadModel('DmiCustomers');
		$this->loadModel('DmiStates');
		$this->loadModel('DmiDistricts');
		$this->loadModel('DmiDocumentLists');
		$this->loadModel('DmiCustomersHistoryLogs');

		if ($this->getRequest()->getSession()->read('username') == null) {

			$this->customAlertPage("Sorry You are not authorized to view this page..");
			exit;

		} else {//this else portion added on 10-07-2017 by Amol to allow only logged in Applicant

			//checking applicant id pattern ex.102/2016
			if (preg_match("/^[0-9]+\/[0-9]+$/", $this->getRequest()->getSession()->read('username'), $matches) == 1) {
				//Give Permission
			} else {

				$this->customAlertPage("Sorry You are not authorized to view this page..");
				exit;
			}

		}


		// set variables to show popup messages from view file
		$message = '';
		$message_theme = '';
		$redirect_to = '';

		$this->viewBuilder()->setLayout('corporate_customer');
		$customer_id = $this->getRequest()->getSession()->read('username');

		//get details from aadhar updation permission table to make aadhar & mobile field available to change //applied on 03-02-2018 by Amol
		$once_update_permission = $this->DmiPermittedOnceUpdations->find('list', array('valueField' => 'id', 'conditions' => array('primary_applicant_id IS' => $this->getRequest()->getSession()->read('username'))))->toArray();
		$aadhar_change_status = null;

		if (!empty($once_update_permission)) {

			$get_last_change_status = $this->DmiPermittedOnceUpdations->find('all', array('conditions' => array('id' => max($once_update_permission))))->first();
			$aadhar_change_status = $get_last_change_status['change_status'];
		}

		$this->set('aadhar_change_status', $aadhar_change_status);


		$customer_data = $this->DmiCustomers->find('all', array('conditions' => array('customer_id IS' => $this->getRequest()->getSession()->read('username'))))->first();

		//get personal details masked by custom function to show in secure mode //applied on 12-10-2017 by Amol
		$customer_data['mobile'] = $this->Customfunctions->getMaskedValue(base64_decode($customer_data['mobile']), 'mobile'); //This is addded on 27-04-2021 for base64decoding by AKASH
		$customer_data['email'] = $this->Customfunctions->getMaskedValue(base64_decode($customer_data['email']), 'email');



		$this->set('customer_data', $customer_data);

		// Apply "Order by" clause to get state list by order wise (Done By Pravin 10-01-2018)
		$states = $this->DmiStates->find('list', array('valueField' => 'state_name', 'conditions' => array('OR' => array('delete_status IS NULL', 'delete_status =' => 'no')), 'order' => array('state_name')))->toArray();
		$this->set('states', $states);

		/*error solve on district value by pravin 15/4/2017*/
		$districts = $this->DmiDistricts->find('list', array('valueField' => 'district_name', 'conditions' => array('state_id IS' => $customer_data['state'])))->toArray();
		$this->set('districts', $districts);

		$document_lists = $this->DmiDocumentLists->find('list', array('valueField' => 'document_name', 'conditions' => array('delete_status IS NULL')))->toArray(); // Changed the query on the 09-08-2022 by Akash
		$this->set('document_lists', $document_lists);


		$selected_states_query = $this->DmiStates->find('all', array('fields' => 'id', 'conditions' => array('id IS' => $customer_data['state'])))->first();
		$selected_states_value = $selected_states_query['id'];
		$this->set('selected_states_value', $selected_states_value);


		$selected_districts_query = $this->DmiDistricts->find('all', array('fields' => 'id', 'conditions' => array('id IS' => $customer_data['district'])))->first();
		$selected_districts_value = $selected_districts_query['id'];
		$this->set('selected_districts_value', $selected_districts_value);


		$selected_document_lists_query = $this->DmiDocumentLists->find('all', array('fields' => 'id', 'conditions' => array('id IS' => $customer_data['document'])))->first();
		$selected_document_lists_value = $selected_document_lists_query['id'];
		$this->set('selected_document_lists_value', $selected_document_lists_value);


		if (null !== $this->request->getData('back')) {

			$this->redirect(array('controller' => 'customers', 'action' => 'primary_home'));

		} elseif (null !== $this->request->getData('update')) {

			//check email already exist to avoid duplicates //applied on 19-06-2018
			$Checkemailexist = $this->DmiCustomers->find('all', array('fields' => 'email', 'conditions' => array('email IS' => $this->request->getData('email'), 'customer_id !=' => $customer_id)))->first();

			if ($Checkemailexist == null) {

				//applied condition to check all post data for !empty validation on server side
				//on 21/10/2017 by Amol
				if (!empty($this->request->getData('f_name')) && !empty($this->request->getData('l_name')) && !empty($this->request->getData('street_address')) &&
					!empty($this->request->getData('state')) && !empty($this->request->getData('district')) && !empty($this->request->getData('postal_code')) &&
					!empty($this->request->getData('email')) && !empty($this->request->getData('mobile')) &&
					!empty($this->request->getData('document')) && !empty($this->request->getData('photo_id_no'))) {

					if (!$this->Customfunctions->validateUniquePostData($this->request->getData('mobile'), 'mobile') == 1) {

						$this->set('return_error_msg','Please enter proper Mobile no.');
						return false;
						exit;
					}
					if (!$this->Customfunctions->validateUniquePostData($this->request->getData('email'), 'email') == 1) {

						$this->set('return_error_msg','Please enter proper Email id');
						return false;
						exit;
					}

					if ($this->request->getData('file')->getClientFilename() != null) {

						$attachment = $this->request->getData('file');
						$file_name = $attachment->getClientFilename();
						$file_size = $attachment->getSize();
						$file_type = $attachment->getClientMediaType();
						$file_local_path = $attachment->getStream()->getMetadata('uri');
						// calling file uploading function
						$uploadedfile = $this->Customfunctions->fileUploadLib($file_name, $file_size, $file_type, $file_local_path);

					} else {

						$uploadedfile = $customer_data['file'];
					}

					//html encoding start
					$htmlencodedfname = htmlentities($this->request->getData('f_name'), ENT_QUOTES);
					$htmlencodedmname = htmlentities($this->request->getData('m_name'), ENT_QUOTES);
					$htmlencodedlname = htmlentities($this->request->getData('l_name'), ENT_QUOTES);
					$htmlencodedstreetaddress = htmlentities($this->request->getData('street_address'), ENT_QUOTES);
					$htmlencodedpostalcode = htmlentities($this->request->getData('postal_code'), ENT_QUOTES);
					$htmlencodedemail = base64_encode(htmlentities($this->request->getData('email'), ENT_QUOTES));//for email encoding
					$htmlencodedmobile = htmlentities(base64_encode($this->request->getData('mobile')), ENT_QUOTES); //This is addded on 27-04-2021 for base64encoding by AKASH
					$htmlencodedlandline = htmlentities(base64_encode($this->request->getData('landline')), ENT_QUOTES);//This is addded on 27-04-2021 for base64encoding by AKASH
					$htmlencodedphoto_id_no = htmlentities($this->request->getData('photo_id_no'), ENT_QUOTES);

					//for business_type
					$table = 'DmiDocumentLists';
					$post_input_request = $this->request->getData('document');
					//calling library function
					$document = $this->Customfunctions->dropdownSelectInputCheck($table, $post_input_request);


					//for state
					$table = 'DmiStates';
					$post_input_request = $this->request->getData('state');
					//calling library function
					$state = $this->Customfunctions->dropdownSelectInputCheck($table, $post_input_request);


					//for district
					$table = 'DmiDistricts';
					$post_input_request = $this->request->getData('district');
					//calling library function
					$district = $this->Customfunctions->dropdownSelectInputCheck($table, $post_input_request);

					$fetch_id_query = $this->DmiCustomers->find('all', array('conditions' => array('customer_id IS' => $this->getRequest()->getSession()->read('username'))))->first();
					$fetch_id = $fetch_id_query['id'];

					if (preg_match("/^[X-X]{6}[0-9]{4}$/i", $this->request->getData('mobile'), $matches) == 1) {
						$htmlencodedmobile = $fetch_id_query['mobile'];
					}

					//called custom function
					$email_masked_value = $this->Customfunctions->getEmailMasked(base64_decode($fetch_id_query['email']));//for email encoding
					if ($email_masked_value == $this->request->getData('email')) {

						$htmlencodedemail = $fetch_id_query['email'];
					}

					//added on 06-05-2021 for profile pic
					if ($this->request->getData('profile_pic')->getClientFilename() != null) {

						$attachment = $this->request->getData('profile_pic');
						$file_name = $attachment->getClientFilename();
						$file_size = $attachment->getSize();
						$file_type = $attachment->getClientMediaType();
						$file_local_path = $attachment->getStream()->getMetadata('uri');
						// calling file uploading function
						$profile_pic = $this->Customfunctions->fileUploadLib($file_name, $file_size, $file_type, $file_local_path);

					} else {
						$profile_pic = $fetch_id_query['profile_pic'];
					}

					$DmiCustomersEntity = $this->DmiCustomers->newEntity(array(
						'id' => $fetch_id,
						'f_name' => $htmlencodedfname,
						'm_name' => $htmlencodedmname,
						'l_name' => $htmlencodedlname,
						'street_address' => $htmlencodedstreetaddress,
						'state' => $state,
						'district' => $district,
						'postal_code' => $htmlencodedpostalcode,
						'email' => $htmlencodedemail,
						'mobile' => $htmlencodedmobile,
						'landline' => $htmlencodedlandline,
						//'once_card_no'=>$encrypted_aadhar, //commented on 23-03-2018 to avoid mandatory for aadhar
						'document' => $document,
						'photo_id_no' => $htmlencodedphoto_id_no,
						'file' => $uploadedfile,
						'modified' => date('Y-m-d H:i:s'),
						'profile_pic' => $profile_pic
					));

					if ($this->DmiCustomers->save($DmiCustomersEntity)) {

						$DmiCustomersHistoryLogsEntity = $this->DmiCustomersHistoryLogs->newEntity(array(
							'customer_id' => $customer_id,
							'f_name' => $htmlencodedfname,
							'm_name' => $htmlencodedmname,
							'l_name' => $htmlencodedlname,
							'street_address' => $htmlencodedstreetaddress,
							'state' => $state,
							'district' => $district,
							'postal_code' => $htmlencodedpostalcode,
							'email' => $htmlencodedemail,
							'password' => $customer_data['password'],
							'mobile' => $htmlencodedmobile,
							'landline' => $htmlencodedlandline,
							//'once_card_no'=>$encrypted_aadhar, //commented on 23-03-2018 to avoid mandatory for aadhar
							'document' => $document,
							'photo_id_no' => $htmlencodedphoto_id_no,
							'file' => $uploadedfile,
							'created' => date('Y-m-d H:i:s'),
							'modified' => date('Y-m-d H:i:s'),
							'profile_pic' => $profile_pic,
							'done_by'=>$this->Session->read('username') // as per change req. added by shankhpal shende on 12/01/2023

						));

						$this->DmiCustomersHistoryLogs->save($DmiCustomersHistoryLogsEntity);

						//update aadhar permission table to done from 'in_progree' in entry exist
						if (!empty($once_update_permission)) {

							if ($aadhar_change_status == 'in_progress') {

								$DmiPermittedOnceUpdationsEntity = $this->DmiPermittedOnceUpdations->newEntity(array(
									'id' => $get_last_change_status['id'],
									'change_status' => 'done',
									'modified' => date('Y-m-d H:i:s')
								));

								$this->DmiPermittedOnceUpdations->save($DmiPermittedOnceUpdationsEntity);
							}
						}


						#SMS: Customer Profile Update
						$this->DmiSmsEmailTemplates->sendMessage(2,$customer_id);

						//Added this call to save the user action log on 01-03-2022
						$this->Customfunctions->saveActionPoint('Profile Update','Success');
						$message = 'Your details are updated successfully';
						$message_theme = 'success';
						$redirect_to = 'customer_profile';

					} else {

						//Added this call to save the user action log on 01-03-2022
						$this->Customfunctions->saveActionPoint('Profile Update','Failed');
						$message = 'Your details are not saved please check again';
						$message_theme = 'failed';
						$redirect_to = 'customer_profile';
					}

				} else {

					//Added this call to save the user action log on 01-03-2022
					$this->Customfunctions->saveActionPoint('Profile Update','Failed');
					$this->set('return_error_msg','Please check some fields are not entered');
					return null;
					exit;
				}

			} else {

				//Added this call to save the user action log on 01-03-2022
				$this->Customfunctions->saveActionPoint('Profile Update','Failed');
				$message = 'This email id is already exist. Please provide another email id to update. Thankyou.';
				$message_theme = 'failed';
				$redirect_to = 'customer_profile';
			}
		}

		// set variables to show popup messages from view file
		$this->set('message', $message);
		$this->set('message_theme', $message_theme);
		$this->set('return_error_msg',null);
		$this->set('redirect_to', $redirect_to);

		// PRIOR TO THE CAKEPHP 4, "$this->view" IS DEPRECATED,
		// SO ADDED "render" PROPERTY TO POP UP FORM RELATED MESSAGES
		// by Aniket Ganvir dated 29th JAN 2021
		if ($message != null) {
			$this->render('/element/message_boxes');
		}

	}



	//Secondary Home Method
	public function secondaryHome() {

		$customer_id = $this->Session->read('username');

		if ($customer_id == null) {
			$this->customAlertPage("Sorry You are not authorized to view this page..");
		} else {//this else portion added on 10-07-2017 by Amol to allow only logged in Applicant
			//checking applicant id pattern ex.102/1/PUN/006
			if (preg_match("/^[0-9]+\/[0-9]+\/[A-Z]+\/[0-9]+$/", $customer_id, $matches) == 1) {
				//Give Permission
			} else {
				$this->customAlertPage("Sorry You are not authorized to view this page..");
			}
		}

		//Set the Layout
		$this->viewBuilder()->setLayout('secondary_customer');
		//Load Models
		$this->loadModel('DmiApplicationPdfRecords');
		$this->loadModel('DmiRenewalApplicationPdfRecords');
		$this->loadModel('DmiFinalSubmits');
		$this->loadModel('DmiRenewalFinalSubmits');
		$this->loadModel('DmiGrantCertificatesPdfs');
		$this->loadModel('DmiFirms');
		$this->loadModel('DmiDistricts');
		$this->loadModel('DmiECodeFinalSubmits');
		$this->loadModel('Dmi15DigitFinalSubmits');
		$this->loadModel('DmiAdpFinalSubmits');
		$this->loadModel('DmiSurrenderFinalSubmits');
		$this->loadModel('DmiAdvPaymentDetails');


		$commoditiesDetails = $this->Customfunctions->commodityNames($customer_id);
		$this->set('commoditiesDetails',$commoditiesDetails);

		// to get export unit added by shankhpal shende on 08/11/2022
		$export_unit_status = $this->Customfunctions->checkApplicantExportUnit($customer_id);
		$this->set('export_unit_status',$export_unit_status);

		//Set Granr Date Condition
		$grantDateCondition = $this->Customfunctions->returnGrantDateCondition($customer_id);

		//Get commodities is used by current login applicant - For Surrender (SOC) - Akash [11-05-2023]
		$firmDetails = $this->DmiFirms->firmDetails($customer_id);


		//// For Applications PDFs on Dashboard /////

			#New/Old Application (1) /Application PDF/
			$this->loadModel('DmiApplicationPdfRecords');
			$application_pdfs = $this->DmiApplicationPdfRecords->find('all', array('conditions' => array('customer_id IS' => $customer_id)))->toArray();
			$this->set('application_pdfs', $application_pdfs);

			#Renewal Application (2) /Application PDF/
			$this->loadModel('DmiRenewalApplicationPdfRecords');
			$renewal_application_pdfs = $this->DmiRenewalApplicationPdfRecords->find('all', array('conditions' => array('customer_id IS' => $customer_id, $grantDateCondition)))->toArray();
			$this->set('renewal_application_pdfs', $renewal_application_pdfs);

			#Change/Modification (3) /Application PDF/ - Amol [13-04-2023]
			$this->loadModel('DmiChangePdfRecords');
			$appl_change_records = $this->DmiChangePdfRecords->find('all', array('conditions' => array('customer_id IS' => $customer_id)))->toArray();
			$this->set('appl_change_records', $appl_change_records);

			#15 Digit Code Approval @ FDC (5) /Application PDF/ - Amol [11-11-2021]
			$this->loadModel('Dmi15DigitPdfRecords');
			$appl_15_digit_pdfs = $this->Dmi15DigitPdfRecords->find('all', array('conditions' => array('customer_id IS' => $customer_id)))->toArray();
			$this->set('appl_15_digit_pdfs', $appl_15_digit_pdfs);

			#E-Code Application @ EC (6) /Application PDF/ -  Amol [18-11-2021]
			$this->loadModel('DmiECodePdfRecords');
			$appl_e_code_pdfs = $this->DmiECodePdfRecords->find('all', array('conditions' => array('customer_id IS' => $customer_id)))->toArray();
			$this->set('appl_e_code_pdfs', $appl_e_code_pdfs);

			#Approval of Designated Persons @ ADP (8) /Application PDF/ - Shankhpal Shende [18-11-2022]
			$this->loadModel('DmiAdpPdfRecords');
			$appl_adp_pdfs_records = $this->DmiAdpPdfRecords->find('all', array('conditions' => array('customer_id IS' => $customer_id)))->toArray();
			$this->set('appl_adp_pdfs_records', $appl_adp_pdfs_records);

			#Surrender Application @ SOC (9) /Application PDF/ -  Akash [14-04-2023]
			$this->loadModel('DmiSurrenderPdfRecords');
			$soc_pdfs_records = $this->DmiSurrenderPdfRecords->find('all', array('conditions' => array('customer_id IS' => $customer_id)))->toArray();
			$this->set('soc_pdfs_records', $soc_pdfs_records);

			#Suspension of Certificate @  SPN - PDF - Akash [02-06-2023]
			$this->loadModel('DmiMmrSuspensions');
			$currentDate = date('Y-m-d H:i:s'); // Get the current date and time
			$suspension_record = $this->DmiMmrSuspensions->find('all')->where(['customer_id IS' => $customer_id,'to_date >=' => $currentDate])->order('id DESC')->first();
			$this->set('suspension_record', $suspension_record);

			#Cancellation of Certificate @  CAN - PDF - Akash [02-06-2023]
			$this->loadModel('DmiMmrCancelledFirms');
			$cancelled_record = $this->DmiMmrCancelledFirms->find('all')->where(['customer_id IS' => $customer_id])->order('id DESC')->first();
			$this->set('cancelled_record', $cancelled_record);

			if(!empty($suspension_record) || !empty($cancelled_record)){
				#Misgrading Details
				$this->loadModel('DmiMmrActionFinalSubmits');
				$misgrading_details = $this->DmiMmrActionFinalSubmits->detailsForPdf($customer_id);
				$this->set('misgrading_details', $misgrading_details);
			}

		//================================================================//

		//// Grant PDFs for Applications ////


			#Change/Modification (3) /GRANT PDF/ - Amol [13-04-2023]
			$this->loadModel('DmiChangeGrantCertificatesPdfs');
			$appl_change_grant_pdfs = $this->DmiChangeGrantCertificatesPdfs->find('all', array('conditions' => array('customer_id IS' => $customer_id)))->toArray();
			$this->set('appl_change_grant_pdfs', $appl_change_grant_pdfs);

			#15 Digit Code Approval @ FDC (5) /GRANT PDF/ - Amol [17-11-2021]
			$this->loadModel('Dmi15DigitGrantCertificatePdfs');
			$cert_15_digit_pdfs = $this->Dmi15DigitGrantCertificatePdfs->find('all', array('conditions' => array('customer_id IS' => $customer_id), 'order' => array('id desc')))->toArray();
			$this->set('cert_15_digit_pdfs', $cert_15_digit_pdfs);

			#E-Code Application (6) /GRANT PDF/ - Amol [18-11-2021]
			$this->loadModel('DmiECodeGrantCertificatePdfs');
			$cert_e_code_pdfs = $this->DmiECodeGrantCertificatePdfs->find('all', array('conditions' => array('customer_id IS' => $customer_id), 'order' => array('id desc')))->toArray();
			$this->set('cert_e_code_pdfs', $cert_e_code_pdfs);

			#Approval of Designated Persons (8) /GRANT PDF/ - Shankhpal Shende [18-11-2022]
			$this->loadModel('DmiAdpGrantCertificatePdfs');
			$appl_adp_grant_pdfs = $this->DmiAdpGrantCertificatePdfs->find('all', array('conditions' => array('customer_id IS' => $customer_id)))->toArray();
			$this->set('appl_adp_grant_pdfs', $appl_adp_grant_pdfs);

			#Surrender Application @ SOC (9) /GRANT PDF/ - Akash [14-04-2023]
			$this->loadModel('DmiSurrenderGrantCertificatePdfs');
			$surrender_grant_certificate = $this->DmiSurrenderGrantCertificatePdfs->find('all')->where(['customer_id IS' => $customer_id])->order('id desc')->first();
			$this->set('surrender_grant_certificate', $surrender_grant_certificate);

			#Suspension Application @ SPN  /GRANT PDF/ - Akash [14-04-2023]
			$this->loadModel('DmiMmrSuspendedFirmsLogs');
			$suspension_grant_certificate = $this->DmiMmrSuspendedFirmsLogs->find('all')->where(['customer_id IS' => $customer_id])->order('id desc')->first();
			$this->set('suspension_grant_certificate', $suspension_grant_certificate);

			#Cancellation Application @ CAN  /GRANT PDF/ - Akash [14-04-2023]
			$this->loadModel('DmiMmrCancelledFirms');
			$cancelletion_grant_certificate = $this->DmiMmrCancelledFirms->find('all')->where(['customer_id IS' => $customer_id])->order('id desc')->first();
			$this->set('cancelletion_grant_certificate', $cancelletion_grant_certificate);



		//================================================================//

		//to check if the user has applied for Advance Payment - Akash [14-04-2023]
		$checkIfAdvancePayment = $this->DmiAdvPaymentDetails->find()->where(['customer_id IS' => $customer_id])->order(['id' => 'DESC'])->first();
		if (empty($checkIfAdvancePayment)) {
			$this->set('advance_payment_status',null);
		} else {
			$this->set('advance_payment_status',$checkIfAdvancePayment['payment_confirmation']);
		}


		//// Final Sumbit Statuses Entries for All Applications ////

			//New/Old Application : check final submit status to show final grant certificate on home
			$list_final_submit_id = $this->DmiFinalSubmits->find('list', array('valueField' => 'id', 'conditions' => array('customer_id IS' => $customer_id)))->toArray();

			if (!empty($list_final_submit_id)) {

				$final_submit_query = $this->DmiFinalSubmits->find('all', array('fields' => array('status', 'current_level'), 'conditions' => array('id' => max($list_final_submit_id))))->first();
				$final_submit_status = $final_submit_query['status'];
				$final_submit_level = $final_submit_query['current_level'];
				$this->set('final_submit_status', $final_submit_status);
				$this->set('final_submit_level', $final_submit_level);

			} else {

				$final_submit_status = 'no_final_submit';
				$this->set('final_submit_status', $final_submit_status);
			}


			//Renewal Application : applied query to get renewal final submit details //on 29-09-2017 by Amol
			$renewal_final_submit_details = $this->DmiRenewalFinalSubmits->find('list', array('valueField' => 'id', 'conditions' => array('customer_id IS' => $customer_id)))->toArray();
			$this->set('renewal_final_submit_details', $renewal_final_submit_details);

			if (!empty($renewal_final_submit_details)) {

				$renewal_final_submit_query = $this->DmiRenewalFinalSubmits->find('all', array('fields' => array('status', 'current_level'), 'conditions' => array('id' => max($renewal_final_submit_details))))->first();
				$renewal_final_submit_status = $renewal_final_submit_query['status'];
				$renewal_final_submit_level = $renewal_final_submit_query['current_level'];
				$this->set('renewal_final_submit_status', $renewal_final_submit_status);
				$this->set('renewal_final_submit_level', $renewal_final_submit_level);

			} else {

				$renewal_final_submit_status = 'no_final_submit';
				$this->set('renewal_final_submit_status', $renewal_final_submit_status);
			}


			$grant_certificate_pdf = null;
			$valid_upto_date = null;
			$fetch_max_pdf_id = $this->DmiGrantCertificatesPdfs->find('all', array('fields' => 'id', 'conditions' => array('customer_id IS' => $customer_id), 'order' => array('id desc')))->first();

			if (empty($fetch_max_pdf_id)) {
				$fetch_max_pdf_id = '';
			} else {
				$fetch_max_pdf_id = $fetch_max_pdf_id['id'];
			}

			$grant_certificate_pdf = $this->DmiGrantCertificatesPdfs->find('all', array('conditions' => array('id IS' => $fetch_max_pdf_id)))->toArray();

			if (!empty($grant_certificate_pdf)) {
				//below logic added on 04-06-2019 by Amol, to show valid upto date to applicant
				$grant_date = chop($grant_certificate_pdf[0]['date'], "00:00:00");
				$valid_upto_date = str_replace('-', '/', $this->Customfunctions->getCertificateValidUptoDate($customer_id, $grant_date));
			}

			$this->set('valid_upto_date', $valid_upto_date);
			$this->set('grant_certificate_pdf', $grant_certificate_pdf);


			//ECODE Application :applied query to get ecode final submit details //on 13-03-2023 by Akash
			$ecode_final_submit_details = $this->DmiECodeFinalSubmits->find('list', array('valueField' => 'id', 'conditions' => array('customer_id IS' => $customer_id)))->toArray();
			$this->set('renewal_final_submit_details', $ecode_final_submit_details);

			if (!empty($ecode_final_submit_details)) {
				$ecode_final_submit_query = $this->DmiECodeFinalSubmits->find('all', array('fields' => array('status', 'current_level'), 'conditions' => array('id' => max($ecode_final_submit_details))))->first();
				$ecode_final_submit_status = $ecode_final_submit_query['status'];
				$ecode_final_submit_level = $ecode_final_submit_query['current_level'];
				$this->set('ecode_final_submit_status', $ecode_final_submit_status);
				$this->set('ecode_final_submit_level', $ecode_final_submit_level);
			} else {
				$ecode_final_submit_status = 'no_final_submit';
				$this->set('ecode_final_submit_status', $ecode_final_submit_status);
			}


			//FDC Application : applied query to get  final submit details //on 13-03-2023 by Akash
			$fdc_final_submit_details = $this->Dmi15DigitFinalSubmits->find('list', array('valueField' => 'id', 'conditions' => array('customer_id IS' => $customer_id)))->toArray();
			$this->set('fdc_final_submit_details', $fdc_final_submit_details);

			if (!empty($fdc_final_submit_details)) {
				$fdc_final_submit_query = $this->Dmi15DigitFinalSubmits->find('all', array('fields' => array('status', 'current_level'), 'conditions' => array('id' => max($fdc_final_submit_details))))->first();
				$fdc_final_submit_status = $fdc_final_submit_query['status'];
				$fdc_final_submit_level = $fdc_final_submit_query['current_level'];
				$this->set('fdc_final_submit_status', $fdc_final_submit_status);
				$this->set('fdc_final_submit_level', $fdc_final_submit_level);
			} else {
				$fdc_final_submit_status = 'no_final_submit';
				$this->set('fdc_final_submit_status', $fdc_final_submit_status);
			}


			//ADP Application : applied query to get  final submit details //on 13-03-2023 by Akash
			$adp_final_submit_details = $this->DmiAdpFinalSubmits->find('list', array('valueField' => 'id', 'conditions' => array('customer_id IS' => $customer_id)))->toArray();
			$this->set('adp_final_submit_details', $adp_final_submit_details);

			if (!empty($adp_final_submit_details)) {
				$adp_final_submit_query = $this->DmiAdpFinalSubmits->find('all', array('fields' => array('status', 'current_level'), 'conditions' => array('id' => max($adp_final_submit_details))))->first();
				$adp_final_submit_status = $adp_final_submit_query['status'];
				$adp_final_submit_level = $adp_final_submit_query['current_level'];
				$this->set('adp_final_submit_status', $adp_final_submit_status);
				$this->set('adp_final_submit_level', $adp_final_submit_level);
			} else {
				$adp_final_submit_status = 'no_final_submit';
				$this->set('adp_final_submit_status', $adp_final_submit_status);
			}

			//SURRENDER Application : applied query to get  final submit details //on 13-03-2023 by Akash
			$soc_final_submit_details = $this->DmiSurrenderFinalSubmits->find('list', array('valueField' => 'id', 'conditions' => array('customer_id IS' => $customer_id)))->toArray();
			$this->set('soc_final_submit_details', $soc_final_submit_details);

			if (!empty($soc_final_submit_details)) {
				$soc_final_submit_query = $this->DmiSurrenderFinalSubmits->find('all', array('fields' => array('status', 'current_level'), 'conditions' => array('id' => max($soc_final_submit_details))))->first();
				$soc_final_submit_status = $soc_final_submit_query['status'];
				$soc_final_submit_level = $soc_final_submit_query['current_level'];
				$this->set('soc_final_submit_status', $soc_final_submit_status);
				$this->set('soc_final_submit_level', $soc_final_submit_level);
			} else {
				$soc_final_submit_status = 'no_final_submit';
				$this->set('soc_final_submit_status', $soc_final_submit_status);
			}

			//check condition to show "Applied to" popup on home page // on 17-08-2018
			$get_dist_id = $this->DmiFirms->find('all', array('conditions' => array('customer_id IS' => $customer_id)))->first();
			$dist_id = $get_dist_id['district'];

			$district_details = $this->DmiDistricts->find('all', array('conditions' => array('id IS' => $dist_id)))->first();
			$smd_id = $district_details['smd_id'];

			$show_applied_to_popup = null;

			if (!empty($smd_id) && empty($final_submit_status)) {

				$show_applied_to_popup = 'yes';
			}

			$this->set('show_applied_to_popup', $show_applied_to_popup);

		//added on 17-08-2018 to update dmi_firm table with login id record, set applied_to field
		if (null !== $this->request->getData('applying_to')) {
			$applied_to = $this->request->getData('applied_to');
			$this->DmiFirms->updateAll(array('applied_to' => "$applied_to"), array('customer_id' => $customer_id));
		}



		//// For Rejected / Suspension / Surrender / Showcause Notices  by Akash////

			//To check if the application is rejected or junked and set the custom message - Akash[14-11-2022]
			$is_appl_rejected =  $this->Customfunctions->isApplicationRejected($customer_id);
			$this->set('is_appl_rejected',$is_appl_rejected);

			//To check if the application is surrendered  and set the custom message - Akash[06-12-2022]
			$isSurrender =  $this->Customfunctions->isApplicationSurrendered($customer_id);
			$this->set('isSurrender',$isSurrender);

			//For Suspension
			$this->loadModel('DmiMmrActionFinalSubmits');
			$actionSubmitted = $this->DmiMmrActionFinalSubmits->find('all')->where(['customer_id IS' => $customer_id])->order('id desc')->first();
			$this->set('actionSubmitted', $actionSubmitted);


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

			if ($lastRecord !== null) {
				$this->set('showCauseNotice',$lastRecord);
			}else{
				$this->set('showCauseNotice',null);
			}



		//check if the applicant is commented on the showcause notice.
		$this->loadModel('DmiMmrShowcauseLogs');
		$showCauseComment = $this->DmiMmrShowcauseLogs->find()->select(['sample_code'])->where(['customer_id' => $customer_id])->order('id DESC')->first();


		if(!empty($showCauseComment)){

			// fetch comments history
			$this->loadModel('DmiMmrShowcauseComments');
			$showcause_comments = $this->DmiMmrShowcauseComments->find('all',array('conditions'=>array('sample_code IS'=>$showCauseComment['sample_code'],'OR'=>array('comment_by IS'=>$customer_id,'comment_to IS'=>$showCauseComment['by_user'])),'order'=>'id'))->toArray();
			$comments_result = array_merge($showcause_comments);
			$comments_result = Hash::sort($comments_result, '{n}.created', 'desc');
			$this->set('showcause_comments',$comments_result);

		}else{
			$this->set('showcause_comments',null);
		}


		// Comment: Added as per suggestion:
		// Suggestion: One Copy of inspection report needs to be sent to
		// packer for information and compliance of shortcomings after submission by inspection Officer.
		// Author: Shankhpal Shende
		// Date:17/05/2023
		$this->loadModel('DmiRtiReportPdfRecords');

		$fetch_max_pdf_id = $this->DmiRtiReportPdfRecords->find('list', array('valueField' => 'id', 'conditions' => array('customer_id IS' => $customer_id)))->toArray();

		$approved_routine_inspection_pdf = [];
		if(!empty($fetch_max_pdf_id)){  //added !empty condition on 19/05/2023 by shankhpal

			$approved_routine_inspection_pdf = $this->DmiRtiReportPdfRecords->find('all', array('conditions' => array('id IS' => max($fetch_max_pdf_id))))->toArray();
		}


		$this->set('approved_routine_inspection_pdf', $approved_routine_inspection_pdf);

		//check if certificate is generated or not, if old appl data is verified
		//added on 21-06-2023 by Amol
		$this->loadModel('DmiOldApplEsignCertLogs');
		$checkOldCertEsigned = $this->DmiOldApplEsignCertLogs->find('all',array('conditions'=>array('customer_id IS'=>$customer_id)))->first();
		$this->set('checkOldCertEsigned', $checkOldCertEsigned);

	}


	//To Commonly Show Replica Status
	public function replicaAllotedList() {

		//Set the Layout
		$this->viewBuilder()->setLayout('secondary_customer');
		//Calling the replica alloted list
		$this->Customfunctions->replicaAllotedListCall('replica');

	}

	//to show alloted 15 digit codes list, on 26-11-2021
	public function alloted15DigitList() {

		$this->viewBuilder()->setLayout('secondary_customer');
		$this->Customfunctions->replicaAllotedListCall('15Digit');

	}

	//to show alloted E-Code codes list, on 26-11-2021
	public function allotedECodeList() {

		$this->viewBuilder()->setLayout('secondary_customer');
		$this->Customfunctions->replicaAllotedListCall('ECode');

	}




	//To Show the Registered Chemist List
	public function getAllChemistList() {

		//Set the Layout
		$this->viewBuilder()->setLayout('secondary_customer');
		$this->Session->delete('unset_incharge_id');
		$this->Session->delete('set_incharge_id');
		$this->Customfunctions->chemistList();
	}


	//To Fetch the Chemist ID for editing purpose
	public function fetchChemistId($chemist_id) {

		$this->Session->write('alloc_chemist_id',$chemist_id);
		$this->redirect(array('controller'=>'customers','action'=>'allotChemist'));

	}



	//Set Chemist Incharge Method
	public function setChemistIncharge($chemist_id) {

		$this->Session->write('set_incharge_id',$chemist_id);
		$this->redirect(array('controller'=>'customers','action'=>'setIncharge'));

	}


	//Unset the Incharge Method
	public function unsetChemistIncharge($chemist_id) {

		$this->Session->write('unset_incharge_id',$chemist_id);
		$this->redirect(array('controller'=>'customers','action'=>'unsetIncharge'));

	}


	//Set Inchagre Method
	public function setIncharge() {

	$this->viewBuilder()->setLayout('secondary_customer');
		//Set the blank variables for the Displaying messages
		$message = '';
		$message_theme = '';
		$redirect_to = '';

		$set_incharge = $this->Session->read('set_incharge_id');

		//Create the data entity for saving in the 'DmiChemistAllotments'
		$this->loadModel('DmiChemistAllotments');
		$DmiChemistAllotmentsEntity = $this->DmiChemistAllotments->newEntity(array(

			'id'=>$set_incharge,
			'incharge'=>'yes',
			'modified'=>date('Y-m-d H:i:s')

		));

		//Save the data entity
		if ($this->DmiChemistAllotments->save($DmiChemistAllotmentsEntity)) {

			$message = 'You have successfully set the chemist incharge';
			$message_theme = 'success';
			$redirect_to = 'get_all_chemist_list';

		}

		//Set the variables for the mesasges
		$this->set('message',$message);
		$this->set('message_theme',$message_theme);
		$this->set('redirect_to',$redirect_to);

		if ($message != null) {
			$this->render('/element/message_boxes');
		}

	}



	//Unset Incharge Method
	public function unsetIncharge() {

		$this->viewBuilder()->setLayout('secondary_customer');
		$unset_incharge = $this->Session->read('unset_incharge_id');

		//Set the blank variables for the Displaying messages
		$message = '';
		$message_theme = '';
		$redirect_to = '';

		//Create the data entity for saving in the 'DmiChemistAllotments'
		$this->loadModel('DmiChemistAllotments');
		$DmiChemistAllotmentsEntity = $this->DmiChemistAllotments->newEntity(array(

			'id'=>$unset_incharge,
			'incharge'=>'no',
			'modified'=>date('Y-m-d H:i:s')

		));

		//Save the data entity
		if ($this->DmiChemistAllotments->save($DmiChemistAllotmentsEntity)) {

			$message = 'You have successfully unset the chemist incharge';
			$message_theme = 'success';
			$redirect_to = 'get_all_chemist_list';
		}


		//Set the variables for the mesasges
		$this->set('message',$message);
		$this->set('message_theme',$message_theme);
		$this->set('redirect_to',$redirect_to);

		if ($message != null) {
			$this->render('/element/message_boxes');
		}

	}


	//Allot Chemist Method
	public function allotChemist() {

		$this->viewBuilder()->setLayout('secondary_customer');

		$chemist_id = $this->Session->read('alloc_chemist_id');
		$customer_id = $this->Session->read('username');

		//Set the blank variables for the Displaying messages
		$message = '';
		$message_theme = '';
		$redirect_to = '';

		//check if this table id is having entry or not
		$this->loadModel('DmiChemistRegistrations');
		$check_id = $this->DmiChemistRegistrations->find('all',array('conditions'=>array('id IS'=>$chemist_id)))->first();


		$this->loadModel('DmiChemistAllotments');
		//Create the data entity for saving in the 'DmiChemistAllotments'
		$DmiChemistAllotmentsEntity = $this->DmiChemistAllotments->newEntity(array(

			'chemist_id'=>$check_id['chemist_id'],
			'customer_id'=>$customer_id,
			'status'=>'1',
			'incharge'=>'no',
			'created'=>$check_id['created'],
			'modified'=>date('Y-m-d H:i:s'),
			'created_by'=>$check_id['created_by'],
			'usertype'=>$check_id['usertype']

		));

		//Save the data entity
		if ($this->DmiChemistAllotments->save($DmiChemistAllotmentsEntity)) {

			$message = 'You have alloted the laboratory chemist yourself successfully';
			$message_theme = 'success';
			$redirect_to = 'get_all_chemist_list';
		}

		//Set the variables for the mesasges
		$this->set('message',$message);
		$this->set('message_theme',$message_theme);
		$this->set('redirect_to',$redirect_to);

		if ($message != null) {
			$this->render('/element/message_boxes');
		}

	}


	//below functions are for testing purpose for email encoding process

	//function to check the string is already base64 encoded
	public function is_base64_encoded($data){
		if (preg_match('%^[a-zA-Z0-9/+]*={0,2}$%', $data)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	//function to check is the string is proper email id
	public function is_email_valid($email_id){
		if (filter_var($email_id, FILTER_VALIDATE_EMAIL)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}



	//>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	// created by shankhpal shende for list of documents list on 09/08/2022
	public function documentCheckList()
	{
		$this->viewBuilder()->setLayout('document_check_list_layout');
		$this->loadModel('DmiDocCheckLists');

		$form_type = ['CA Non Bevo','CA Bevo','Printing Press','Laboratory','15 Digit Code','E code'];
		$this->set('form_type', $form_type);
		$comm_array = [];
		$i = 0;
		foreach($form_type as $eachformtype){
			$get_doc =  $this->DmiDocCheckLists->find('all')->select(['releted_document'])->where(['form_type'=>$eachformtype])->order('releted_document ASC')->toArray();
			$doc_array[$i] = $get_doc;
			$i++;
		}
		$this->set('doc_array', $doc_array);

	}

	public function showCommodities()
	{
		$this->viewBuilder()->setLayout('document_check_list_layout');
		$this->loadModel('MCommodityCategory');
		$this->loadModel('MCommodity');
		$conn = ConnectionManager::get('default');



		$commodity_cat =  $this->MCommodityCategory->find('all')->select(['category_code','category_name'])->where(['display' => 'Y'])->order('category_name ASC')->toArray();
		$this->set('commodity_cat', $commodity_cat);

		$comm_array = [];
		$i = 0;
		foreach($commodity_cat as $eachcat){
				$get_comm =  $this->MCommodity->find('all')->select(['commodity_name'])->where(['category_code'=>$eachcat['category_code'],'display' => 'Y'])->order('commodity_name ASC')->toArray();
				$comm_array[$i] = $get_comm;
				$i++;
		}

		$this->set('comm_array', $comm_array);

	}

	// customer_information function added by Laxmi Bhadade on 18-11-22
	public function customerInformation(){

		$result = '';
		$commodity = '';

		$this->viewBuilder()->setLayout('document_check_list_layout');

		$this->loadModel('DmiFirms');
		$this->loadModel('DmiCustomers');
		$this->loadModel('DmiStates');
		$this->loadModel('DmiDistricts');
		$this->loadModel('MCommodityCategory');
		$this->loadModel('DmiGrantCertificatesPdfs');
		$this->loadModel('DmiPackingTypes');


		$conn = ConnectionManager::get('default');

		if( $this->request->is('post') ) {

			if($this->request->getdata('name') == 'primary'){

				$customer_id = $this->request->getdata('id');
				$primary_data = $this->DmiCustomers->find('all')->where(array('customer_id IS' => $customer_id))->first();

				if (!empty($primary_data->district )) {
					$dist = $this->DmiDistricts->find('all',array('fields'=>array('district_name','state_id')))->where(array('id IS'=>$primary_data->district , 'state_id IS'=> $primary_data->state))->first();
					$state = $this->DmiStates->find('all',array('fields'=>array('state_name')))->where(array('id IS'=> $primary_data->state , 'id IS'=> $dist->state_id))->first();
				}

				if ($primary_data  !=null) {

					$result= "<table class= table table-bordered>
							<thead>
								<tr>
								<th>ID</th>
								<th>Name</th>
								<th>District</th>
								<th>State</th>
								</tr>
							</thead>
							<tbody>
								<tr id= custmer_data >
									<td id= id>".$customer_id."</td>
									<td id= name>".$primary_data->f_name." &nbsp; ".$primary_data->l_name."</td>
									<td id= district>".$dist->district_name."</td>
									<td id= state>".$state->state_name."</td>
								</tr>
							</tbody>
						</table>";

					echo $result;

				}else{
					echo $result = "<p class = 'text-danger' id = 'danger-id'>This Customer Id is not valid</p>";
				}

				exit; // This is added Intensionally

			}elseif($this->request->getdata('name') == 'firm'){

				$customer_id = $this->request->getdata('id');

				$this->loadModel('DmiMmrCancelledFirms');
				$this->loadModel('DmiMmrSuspensions');

				//Check if the firm is cancelled ot not first. - Akash[04-06-2023]
				$cancellation_record = $this->DmiMmrCancelledFirms->find('all')->where(['customer_id IS' => $customer_id])->order('id DESC')->first();
				if (empty($cancellation_record)) {

					#Suspension of Certificate @  SPN - PDF - Akash [02-06-2023]
					$currentDate = date('Y-m-d H:i:s'); // Get the current date and time
					$suspension_record = $this->DmiMmrSuspensions->find('all')->where(['customer_id IS' => $customer_id,'to_date >=' => $currentDate])->order('id DESC')->first();
					if (empty($suspension_record)) {

						//This Code is added to this function to avoid the customer ids if the firm is surrendered For SOC - Akash [12-05-2023]
						$isSurrender = $this->Customfunctions->isApplicationSurrendered($customer_id);
						if (empty($isSurrender)) {

							$firm_data = $this->DmiFirms->find('all')->where(array('customer_id IS' => $customer_id))->first();

							//if commodity is one then select packaging type
							if(!empty($firm_data->commodity) && $firm_data->commodity > 1) {

								$commodity = $this->MCommodityCategory->find('all',array('fields'=>array('category_name')))->where(array('category_code IS'=> $firm_data->commodity))->first();
								if(!empty ($commodity->category_name)) {
									$commodity = $commodity->category_name;
								} else {
									$commodity = "Not found ";
								}

							}else{

								if(!empty($firm_data->packaging_materials) && $firm_data->commodity == 1){

									$packaging =  explode(',', $firm_data->packaging_materials);
									foreach($packaging as $packtype){
										$pacging_type = $this->DmiPackingTypes->find('all',array('fields'=>array('packing_type')))->where(array('id IS'=> $packtype))->first();
										if(!empty($pacging_type->packing_type)){
											$commodity .= $pacging_type->packing_type. ",";
										}else{
											$commodity = "Not found ";
										}
									}
								}
							}

							$grant_date = $this->DmiGrantCertificatesPdfs->find('all',array('fields'=>array('date')))->where(array('customer_id IS'=> $customer_id))->last();
							if(!empty($grant_date->date)){
								$grant_date = $grant_date->date;
								$uptoDate = $this->Customfunctions->getCertificateValidUptoDate($customer_id, $grant_date);
								$date =  date('d-m-Y', strtotime("$uptoDate +1 Months"));
							}

							//added by laxmi on 27-12-2022
							$status = "";
							if(!empty($date)){

								$current_date = date("Y-m-d");
								$current_date = date("Y-m-d", strtotime($current_date));
								$date = date("Y-m-d", strtotime($date));

								if($date > $current_date ){
									$status = "Valid";
								}else{
									$status = "Invalid";
								}


								if ($firm_data  !=null) {

									$result .= "<tr><td><b>Name:</b></td><td>".$firm_data->firm_name."</td></tr>";
									$result .= "<tr><td><b>Commodity:</b></td><td>".$commodity."</td></tr>";

									//added by laxmi on 27-12-2022
									if(!empty($status && !empty($uptoDate))){
										$result .= "<tr><td><b>Status:</b></td><td>".$status."</td></tr>";
										$result .= "<tr><td><b>Valid Upto:</b></td><td>".$uptoDate."</td></tr>";
									}

									echo $result;

								}else{
									echo $result = "<tr><td></td><td>Sorry, This Customer Id you have searched is not valid</td></tr>";
								}
									//else part added by laxmi on 06-02-2023
							}else{
								echo $result = "<tr><td></td><td>Sorry, This Customer Id you have searched is not valid</td></tr>";
							}

						} else {
							echo "<b>This Application is surrendered on ".$isSurrender." and no longer available.</b>";
						}

					} else {
						$date = new \DateTime($suspension_record['to_date']);
						echo "<b>This Application is Suspended till Date: ".$date->format('d/m/Y')." and no longer available.</b>";
					}

				} else {
					$date = new \DateTime($cancellation_record['to_date']);
					echo "<b>This Application is cancelled on Date: ".$date->format('d/m/Y')." and no longer available.</b>";
				}

				exit; // This is added Intensionally

			}else{

				$result = "<tr><td></td><td>This Customer Id you have searched is not valid</td></tr>";
				echo $result;
				exit; // This is added Intensionally

			}
		}


	}

	//To fetch certified firm list added by laxmi B. dated on 06-02-23
	public function certifiedFirmList(){

		$this->viewBuilder()->setLayout('customer_information_layout');
		$this->loadModel('DmiFirms');
		$this->loadModel('DmiCustomers');
		$this->loadModel('DmiStates');
		$this->loadModel('DmiDistricts');
		$this->loadModel('MCommodityCategory');
		$this->loadModel('DmiGrantCertificatesPdfs');
		$this->loadModel('DmiPackingTypes');
		$this->loadModel('DmiMmrCancelledFirms');
		$this->loadModel('DmiMmrSuspensions');


		if(null!== ($this->request->getData('save'))){

			$data = $this->request->getData();
			$type = $data['type'];
			$firmname = array();
			$customer_id = array();
			$commodity = array();
			$date = array();
			$firmData = array();
			$i=0;
			$s=1;
			$current_date = date('d-m-Y');
			$grantData = $this->DmiGrantCertificatesPdfs->find('all')->where(array('customer_id LIKE'=>'%/'.$type.'/%',))->order(array('id'=>'desc'))->toArray();

			if(!empty($grantData)){

				foreach ($grantData as $key => $grantdata) {

					//Check if the firm is cancelled ot not first. - Akash[04-06-2023]
					$cancellation_record = $this->Customfunctions->isApplicationCancelled($grantdata['customer_id']);
					if (empty($cancellation_record)) {

						#Suspension of Certificate @  SPN - PDF - Akash [02-06-2023]
						$suspension_record = $this->Customfunctions->isApplicationSuspended($grantdata['customer_id']);
						if (empty($suspension_record)) {

							//This Code is added to this function to avoid the customer ids if the firm is surrendered For SOC - Akash [12-05-2023]
							$isSurrender = $this->Customfunctions->isApplicationSurrendered($grantdata['customer_id']);
							if (empty($isSurrender)) {

								$uptoDate = $this->Customfunctions->getCertificateValidUptoDate($grantdata['customer_id'], $grantdata['date']);
								$date =  date('d-m-Y', strtotime("$uptoDate +1 Months"));
								$date =date('Y-m-d', strtotime($date));
								$current_date = date('Y-m-d', strtotime($current_date));

								if($date > $current_date){

									$customerId[$i] = $grantdata['customer_id'];
									$firmData = $this->DmiFirms->find('all')->where(array('delete_status IS'=> NULL, 'customer_id'=>$customerId[$i]))->order(array('firm_name'=> 'asc'))->first();
									if(!empty($firmData)){
										$commodityData = $this->MCommodityCategory->find('all')->where(array('category_code IS'=>$firmData['commodity']))->first();

										$result= "<tr><td>".$i+$s."</td>";
										$result.= "<td>".$customer_id[$i] = $firmData['customer_id']."</td>";
										$result.= "<td>".$firmname[$i] = $firmData['firm_name']."</td>";
										$result.= "<td>".$commodity[$i] = $commodityData['category_name']."</td>";
										$result.= "<td>".$date = $date."</td></tr>";
										echo $result;
									}

									$i=$i+1;
								}else{
									$result= "<p>Records not found..</p>";
									echo $result;
								}
							}
						}
					}
				}

				exit; // This is intensionally added.
			}
		}
	}

	// This function added by Shankhpal shende
	// on date 24/08/2022
	// for Attach PP/LAB

	// for own laboratory update
	// Name of person: Shankhpal Shende [Date:02/05/2023]
	// Description : Applying Own Lab Logic in existing code
	// Author : Shankhpal Shende
	// Date : 02/05/2023
	// For Module : attached PP/Lab

	public function attachePpLab() {

		//load modals
		$this->loadModel('DmiFirms');
		$this->loadModel('DmiReplicaAllotmentDetails');
		$this->loadModel('DmiCaPpLabMapings');
		$this->loadModel('DmiGrantCertificatesPdfs');
		$this->loadModel('DmiCustomerLaboratoryDetails');
		$this->loadModel('DmiLaboratoryTypes');
		$this->loadModel('DmiCaMappingOwnLabDetails');
		$this->loadModel('DmiCaPpLabActionLogs');
		$this->loadModel('DmiApplWithRoMappings');
		$this->loadModel('DmiReplicaAllotmentDetails');
		$this->loadModel('DmiCaMappingOwnLabDetails');
		//Set the blank variables for the Displaying messages
		$message = '';
		$message_theme = '';
		$redirect_to = '';

		$this->viewBuilder()->setLayout('secondary_customer');
		$customer_id = $this->Session->read('username');

		//to get list of authorized laboratory
		$lab_list = $this->DmiFirms->find('all',array('keyField'=>'id','valueField'=>'firm_name','joins'=>array(array('table' => 'dmi_grant_certificates_pdfs','alias' => 'dmigcp','type' => 'INNER','conditions' => array('dmigcp.customer_id = DmiFirms.customer_id'))),
		'conditions'=>array('Dmifirms.customer_id like'=>'%'.'/3/'.'%','delete_status IS NULL'),'order'=>array('Dmifirms.firm_name asc')))->toArray();

		// added for
		// When attaching the printing press and laboratory, they must display some identifying information such as address and ID. added by shankhpal on 22/02/2023
		$i=0;
		foreach ($lab_list as $lab_list_value) {

			$app_id = $lab_list_value['customer_id'];
			$get_office_record = $this->DmiApplWithRoMappings->getOfficeDetails($app_id);

			// Check if $get_office_record is not null before accessing its values
			if ($get_office_record !== null) {
				$office_type = $get_office_record['office_type'];
				$ro_office = $get_office_record['ro_office'];
				$id = $lab_list_value['id'];
				$lab_data[$id] = $lab_list_value['firm_name'].", #"."Address: ".$lab_list_value['street_address'].", #"."Applicant ID: ".$lab_list_value['customer_id'].", #"."Office: ".$ro_office.", #"."Office Type: ".$office_type;
					// Rest of the code
			}
			$i++;
		}

		$printers_list = $this->DmiFirms->find('all',array('keyField'=>'id','valueField'=>'firm_name','joins'=>array(array('table' => 'dmi_grant_certificates_pdfs','alias' => 'dmigcp','type' => 'INNER','conditions' => array('dmigcp.customer_id = DmiFirms.customer_id'))),
		'conditions'=>array('Dmifirms.customer_id like'=>'%'.'/2/'.'%','delete_status IS NULL'),'order'=>array('Dmifirms.firm_name asc')))->toArray();

		// added for
		// When attaching the printing press and laboratory, they must display some identifying information such as address and ID. added by shankhpal on 22/02/2023

		$i=0;
		foreach ($printers_list as $printers_list) {
			// concate firm name street address and customer id
			$app_id = $printers_list['customer_id'];

			$get_office_record = $this->DmiApplWithRoMappings->getOfficeDetails($app_id);

			$office_type = $get_office_record['office_type'];
			$ro_office = $get_office_record['ro_office'];

			$id = $printers_list['id'];
			$printing_data[$id] = $printers_list['firm_name'].", #"."Address: ".$printers_list['street_address'].", #"."Applicant ID: ".$printers_list['customer_id'].", #"."Office: ".$ro_office.", #"."Office Type: ".$office_type;
			$i++;
		}

		$attached_list =  $this->DmiCaPpLabMapings->find('all')->select(['id','customer_id','pp_id','lab_id','map_type'])->where(array('customer_id IS' => $customer_id,'delete_status IS' => null,'is_own_lab IS'=>null))->toArray();

		// own lab start
		// data from laboratory profile form
		$fetch_laboratory_last_id = $this->DmiCustomerLaboratoryDetails->find('list',array('valueField'=>'id','conditions'=>array('customer_id IS'=>$customer_id)))->toArray();

		// to fetch own laboratory details name
		$fetch_laboratory_detail_data = $this->DmiCustomerLaboratoryDetails->find('all',array('conditions'=>array('id'=>max($fetch_laboratory_last_id))))->toArray();

		foreach($fetch_laboratory_detail_data as $own_lab_value) {
				$id = $own_lab_value['id'].'/'.'Own';
				$own_lab_data[$id] = $own_lab_value['laboratory_name'];
				$laboratory_type = $own_lab_value['laboratory_type'];
		}

		//to fetch laboratory type name
		$fetch_laboratory_type = $this->DmiLaboratoryTypes->find('all',array('fields'=>'laboratory_type','conditions'=>array('id IS'=>$laboratory_type, 'OR'=>array('delete_status IS NULL','delete_status ='=>'no'))))->first();

		$laboratory_type_name = '';
		if(!empty($fetch_laboratory_type)){
			$laboratory_type_name = $fetch_laboratory_type['laboratory_type'];
		}


		$this->set('laboratory_type_name',$laboratory_type_name);

		$attached_own_lab =  $this->DmiCaMappingOwnLabDetails->find('all')->select(['id','own_lab_id','map_type','lab_name'])->where(array('ca_id IS' => $customer_id,'delete_status IS' => null))->toArray();

		$ownlabresultArray = [];
		$i=0;
		foreach($attached_own_lab as $eachlab){
			$ownlabresultArray[$i]['id'] = $eachlab['id'];
			$ownlabresultArray[$i]['own_lab_id'] = $eachlab['own_lab_id'];
			$ownlabresultArray[$i]['map_type'] = $eachlab['map_type'];
			$ownlabresultArray[$i]['own_lab_name'] = $eachlab['own_lab_name'];
			$i++;
		}

		$is_own_lab_attached =  $this->DmiCaPpLabMapings->find('all')->where(array('customer_id IS' => $customer_id,'delete_status IS' => null,'is_own_lab IS'=>'yes'))->toArray();

		$is_own_lab = '';
		if(!empty($is_own_lab_attached)){
			foreach ($is_own_lab_attached as $each_value) {
			$is_own_lab = $each_value['is_own_lab'];
			}
		}

		$list_record_with_delete_null_own_lab = $this->DmiCaMappingOwnLabDetails->find('list', array('conditions'=>array('ca_id IS'=>$customer_id,'delete_status IS NULL')))->toArray();

		$list_record_with_delete_null = $this->DmiCaPpLabMapings->find('list', array('conditions'=>array('customer_id IS'=>$customer_id,'delete_status IS NULL')))->toArray();

		$last_record_with_delete_null = [];
		if(!empty($list_record_with_delete_null)){

			$last_record_with_delete_null= $this->DmiCaPpLabMapings->find('all', array('conditions'=>array('id'=>max($list_record_with_delete_null))))->toArray();
		}

		$resultArray_ca_pp = [];
		$j=0;
		foreach ($last_record_with_delete_null as $each_value) {

			$resultArray_ca_pp['id'] = $each_value['id'];
			$resultArray_ca_pp['pp_id'] = $each_value['pp_id'];
			$resultArray_ca_pp['lab_id'] = $each_value['lab_id'];
			$resultArray_ca_pp['map_type'] = $each_value['map_type'];
			$j++;
		}

		$this->set('resultArray_ca_pp',$resultArray_ca_pp);
		$last_record_with_delete_null_own = [];

		if(!empty($list_record_with_delete_null_own_lab)){

			$last_record_with_delete_null_own = $this->DmiCaMappingOwnLabDetails->find('all', array('conditions'=>array('id'=>max($list_record_with_delete_null_own_lab))))->toArray();
		}

		$resultArray_own_lab = [];
		$i=0;
		foreach ($last_record_with_delete_null_own as $each_value) {

			$resultArray_own_lab[$i]['id'] = $each_value['id'];
			$resultArray_own_lab[$i]['lab_name'] = $each_value['lab_name'];
			$i++;
		}

		$resultArr =  $this->DmiCaPpLabMapings->find('list')->where(array('customer_id IS' => $customer_id,'delete_status IS' => null))->toList();

		//this array is used for display printing press and laboratory on view
		$result = [];
		$i = 0;

		foreach($attached_list as $eachlist){

			$result[$i]['id'] = $eachlist['id'];
			$result[$i]['type'] = $eachlist['map_type'];
			$result[$i]['pp_id'] = $eachlist['pp_id'];
			$result[$i]['lab_id'] = $eachlist['lab_id'];

			if(!empty($eachlist['pp_id'])){
				$result[$i]['p_name'] = $printing_data[$eachlist['pp_id']];
			}

			if(!empty($eachlist['lab_id'])){
				if (strpos($eachlist['lab_id'], "/Own") !== false) {

					$own_lab_id = $eachlist['lab_id'];
					$lab_data = $this->DmiCaMappingOwnLabDetails->find()->where(['own_lab_id' => $own_lab_id])->order(['id' => 'DESC'])->toArray();

					$result[$i]['lab_name'] = $own_lab_data[$eachlist['lab_id']];
				}else{
					$result[$i]['l_name'] = $lab_data[$eachlist['lab_id']];
				}


			}
			$i++;
		}

		//fetch last reocrds from table, if empty set default value
		$dataArray = $this->DmiReplicaAllotmentDetails->getSectionData($customer_id);

		//to show selected lab in list
		if (!empty($dataArray)) {
			$selected_lab = $dataArray[0]['grading_lab'];
			$selected_PP = $dataArray[0]['authorized_printer'];
		} else {
			$selected_lab = '';
		}

		//to save post data
		if(null != $this->request->getData('save')) {

			$postData = $this->request->getData();
			// pr($postData);die;
			$maptype = $postData['maptype'];

			$customer_id = $this->Session->read('username');
			$get_data_pp_id = $this->request->getData('pp_id');
			$get_data_lab_id = $this->request->getData('lab_id');
			$get_data_maptype = $this->request->getData('maptype');

			$current_ip = $this->getRequest()->clientIp();
			if ($current_ip == '::1') {
				$current_ip = '127.0.0.1';
			}

			if($maptype == "lab"){

				$lab_id = $postData['lab_id'];
				// // to check if customer are already exist but is lab is deleted.
				$check_customer_record_is_exist = $this->DmiCaPpLabMapings->find('all')->where(array('customer_id IS' => $customer_id,'map_type IN' => $maptype,'delete_status IS NULL'))->first();

				if(!empty($check_customer_record_is_exist)){
					//	if lab is already exists then this condition stop adding new lab
					$message = 'Packer can attach only one laboratory.';
					$message_theme = 'failed';
					$redirect_to = 'attache_pp_lab';
				}else{

					$DmiCaPpLabMapings = $this->DmiCaPpLabMapings->newEntity(array(

						'customer_id'=>$customer_id,
						'lab_id'=>$lab_id,
						'map_type'=> $maptype,
						'created'=>date('Y-m-d H:i:s'),
						'modified'=>date('Y-m-d H:i:s'),
					));

					//Save laboratory Logs Status
					$DmiCaPpLabActionLogsEntity = $this->DmiCaPpLabActionLogs->newEntity(
						['customer_id'=>$customer_id,
						'ipaddress'=>$current_ip,
						'action_perform'=>'Laboratory (Attached)',
						'created'=>date('Y-m-d H:i:s'),
						'status'=>'Success']
					);

					$this->DmiCaPpLabActionLogs->save($DmiCaPpLabActionLogsEntity);

					if ($this->DmiCaPpLabMapings->save($DmiCaPpLabMapings) && $maptype == 'lab' ) {

						$message = 'Laboratory Attached successfully';
						$message_theme = 'success';
						$redirect_to = 'attache_pp_lab';
					}
				}
			}elseif($maptype == "pp"){

				$pp_id = $postData['pp_id'];
				// to check if respective printing press already attach or not
				$get_record_pp =  $this->DmiCaPpLabMapings->find('all')->where(array('customer_id IS' => $customer_id,'pp_id IS' => $pp_id,'delete_status IS'=>null))->first();

				if(!empty($get_record_pp)){
					$message = 'Printing Press alredy Attached with you';
					$message_theme = 'failed';
					$redirect_to = 'attache_pp_lab';
				}else{

					$DmiCaPpLabMapings = $this->DmiCaPpLabMapings->newEntity(array(

						'customer_id'=>$customer_id,
						'pp_id'=>$pp_id,
						'map_type'=> $maptype,
						'created'=>date('Y-m-d H:i:s'),
						'modified'=>date('Y-m-d H:i:s'),
					));

					//Save pp Logs Status
					$DmiCaPpLabActionLogsEntity = $this->DmiCaPpLabActionLogs->newEntity(
						['customer_id'=>$customer_id,
						'ipaddress'=>$current_ip,
						'action_perform'=>'Priinting Press (Attached)',
						'created'=>date('Y-m-d H:i:s'),
						'status'=>'Success']
					);

					$this->DmiCaPpLabActionLogs->save($DmiCaPpLabActionLogsEntity);
					if ($this->DmiCaPpLabMapings->save($DmiCaPpLabMapings) && $maptype == 'pp' ) {
						$message = 'Printing Press Attached successfully';
						$message_theme = 'success';
						$redirect_to = 'attache_pp_lab';
					}
				}
			}elseif($maptype == "wonlab"){

				$won_id = $postData['won_id']; // for own laboratory
				$won_lab_name = $postData['won_lab_name']; // for own laboratory name

				if($maptype == 'wonlab'){
						$lab_id = $postData['won_id'];
						$maptype = 'lab';
				}

				// // to check if customer are already exist but is lab is deleted.
				$check_customer_record_is_exist = $this->DmiCaPpLabMapings->find('all')->where(array('customer_id IS' => $customer_id,'map_type IN' => $maptype,'delete_status IS NULL'))->first();

				if(!empty($check_customer_record_is_exist)){
					//	if lab is already exists then this condition stop adding new lab
					$message = 'Packer can attach only one laboratory.';
					$message_theme = 'failed';
					$redirect_to = 'attache_pp_lab';
				}else{

					$DmiCaMappingOwnLabDetails = $this->DmiCaMappingOwnLabDetails->newEntity(array(

						'own_lab_id'=>$lab_id,
						'ca_id'=>$customer_id,
						'lab_name'=>$won_lab_name,
						'map_type'=> $maptype,
						'created'=>date('Y-m-d H:i:s'),
						'modified'=>date('Y-m-d H:i:s'),
					));

					$this->DmiCaMappingOwnLabDetails->save($DmiCaMappingOwnLabDetails);

					$DmiCaPpLabMapings = $this->DmiCaPpLabMapings->newEntity(array(
						'customer_id'=>$customer_id,
						'lab_id'=>$lab_id,
						'map_type'=> $maptype,
						'is_own_lab'=>'yes',
						'created'=>date('Y-m-d H:i:s'),
						'modified'=>date('Y-m-d H:i:s'),
					));
					$this->DmiCaPpLabMapings->save($DmiCaPpLabMapings);
					//Save laboratory Logs Status
					$DmiCaPpLabActionLogsEntity = $this->DmiCaPpLabActionLogs->newEntity([
						'customer_id'=>$customer_id,
						'ipaddress'=>$current_ip,
						'action_perform'=>'Laboratory (Attached)',
						'created'=>date('Y-m-d H:i:s'),
						'status'=>'Success'
					]);

					$this->DmiCaPpLabActionLogs->save($DmiCaPpLabActionLogsEntity);
					if ($this->DmiCaPpLabMapings->save($DmiCaPpLabMapings) && $maptype == 'lab' ) {
						$message = 'Laboratory Attached successfully';
						$message_theme = 'success';
						$redirect_to = 'attache_pp_lab';
					}
				}
			}
		}

		// set variables to show popup messages from view file
		$this->set('message',$message);
		$this->set('message_theme',$message_theme);
		$this->set('redirect_to',$redirect_to);

		if ($message != null) {
			$this->render('/element/message_boxes');
		}

		$this->set('result',$result);
		$this->set('resultArr',$resultArr);
		$this->set('resultArray_own_lab',$resultArray_own_lab);
		$this->set('is_own_lab',$is_own_lab);
		$this->set('attached_list',$attached_list);
		$this->set('printing_data',$printing_data);
		$this->set('lab_data',$lab_data);
		$this->set('ownlabresultArray',$ownlabresultArray);
		$this->set('own_lab_data',$own_lab_data);
		$this->set('selected_lab',$selected_lab);
		$this->set('selected_PP',$selected_PP);
		$this->set('dataArray',$dataArray);
	}
}

?>
