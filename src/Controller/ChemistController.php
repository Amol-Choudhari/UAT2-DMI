<?php
namespace App\Controller;

use Cake\Event\Event;
use Cake\Network\Session\DatabaseSession;
use App\Network\Email\Email;
use App\Network\Request\Request;
use App\Network\Response\Response;
use Cake\Datasource\ConnectionManager;
use phpDocumentor\Reflection\Types\This;
use Cake\Http\Response\withHeader;

class ChemistController extends AppController {

	var $name = 'Chemist';

	//to initialize our custom requirements
	public function initialize(): void {

		parent::initialize();

		$this->loadComponent('RequestHandler');
		$this->loadComponent('Createcaptcha');
		$this->loadComponent('Authentication');
		$this->loadComponent('Customfunctions');

		$this->viewBuilder()->setHelpers(['Form','Html','Time']);

		//$this->viewBuilder()->setLayout('admin_dashboard');
		$this->Session = $this->getRequest()->getSession();

		//Load Models
		$this->loadModel('DmiChemistRegistrations');
		$this->loadModel('DmiFirms');
		$this->loadModel('DmiChemistsResetpassKeys');
		$this->loadModel('DmiChemistLogs');
		$this->loadModel('DmiChemistProfileDetails');
		$this->loadModel('DmiChemistExperienceDetails');
		$this->loadModel('DmiChemistEducationDetails');
		$this->loadModel('DmiChemistTrainingDetails');
		$this->loadModel('DmiChemistFinalSubmits');
		$this->loadModel('DmiStates');
		$this->loadModel('DmiDistricts');
		$this->loadModel('DmiEducationTypes');
		$this->loadModel('DmiDivisionGrades');
		$this->loadModel('DmiChemistAllocations');
		$this->loadModel('DmiRoOffices');
		$this->loadModel('DmiChemistComments');
		$this->loadModel('DmiChemistAllotments');
		$this->loadModel('DmiChemistOtherDetails');
		$this->loadModel('DmiSmsEmailTemplates');


	}


	// BEFORE FILTER
	public function beforeFilter($event) {

		parent::beforeFilter($event);
		//To Find Customer Last Login
		$customer_last_login = $this->Customfunctions->customerLastLogin();
		$this->set('customer_last_login', $customer_last_login);
		// Change layout for Ajax requests
		if ($this->request->is('ajax')) {
			$this->layout = 'ajax';
		}

		// Checked final submit status, moved here on 10-08-2021 by Amol, for common use
		$chemist_id = $this->Session->read('username');
		$final_submit_record = $this->DmiChemistFinalSubmits->find('all',array('conditions'=>array('customer_id IS'=>$chemist_id),'order'=>'id desc'))->first();
		$final_status = '';

		if (!empty($final_submit_record)) {
			$final_status = $final_submit_record['status'];
		}

		$this->set('final_submit_status',$final_status);
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




	// VALID USER
	// @AUTHOR : PRAVIN BHAKARE
	// #CONTRIBUTER : AKASH THAKRE (Migration)
	
	public function validUser() {

		$application_dashboard = $this->Session->read('application_dashboard');

		if ($this->Session->read('username') == null) {
			
			$this->customAlertPage("Sorry You are not authorized to view this page..");
			exit;
		} else {

			if ($application_dashboard == 'packer') {
				//checking applicant id pattern ex.102/1/PUN/006
				if (preg_match("/^[0-9]+\/[0-9]+\/[A-Z]+\/[0-9]+$/", $this->Session->read('username'),$matches) !=1) {

					$this->customAlertPage("Sorry You are not authorized to view this page..");
					exit;
				}
			}
		}


		if ($application_dashboard == 'packer') {

			$show_renewal_btn = $this->Customfunctions->checkApplicantValidForRenewal($this->Session->read('username'));
			$this->set('show_renewal_btn',$show_renewal_btn);

			//Find the value of "is_already_granted" flag status to redirect the application on appropriate new application or old application controller
			//Done by pravin 27-09-2017
			$is_already_granted = null;
			$get_is_already_granted = $this->DmiFirms->find('all', array('fields'=>'is_already_granted','conditions'=>array('customer_id IS'=>$this->Session->read('username'))))->first();

			if (!empty($get_is_already_granted)) {

				$is_already_granted = $get_is_already_granted['is_already_granted'];
			}

			$this->set('is_already_granted',$is_already_granted);
		}

	}



	// CHEMIST LOGIN
	// @AUTHOR : PRAVIN BHAKARE
	// #CONTRIBUTER : AKASH THAKRE (Migration)
	// DATE : 25-06-2021 
	
	public function chemistLogin() {

		// set variables to show popup messages from view file
		$message = '';
		$message_theme = '';
		$redirect_to = '';
		$login_result = '';
		$already_loggedin_msg = 'no';

		//Set Layout
		$this->viewBuilder()->setLayout('form_layout');

		if ($this->request->is('post')) {

			//check login lockout status, applied on 24-04-2018 by Amol
			$lockout_status = $this->Customfunctions->checkLoginLockout('DmiChemistLogs',$this->request->getData('chemist_id'));
			
			if ($lockout_status == 'yes') {

				$message = 'Sorry... Your account is disabled for today, on account of 3 login failure.';
				$message_theme = 'failed';
				$redirect_to = $this->getRequest()->getAttribute('webroot');

			} else {


				$countspecialchar = substr_count($this->request->getData('chemist_id') ,"/");

				if ($countspecialchar == 2) {

					if (substr_count($this->request->getData('chemist_id') ,"/")!=0) {

						$split_customer_id = explode('/',(string) $this->request->getData('chemist_id')); #For Deprecations

						$randsalt = $this->Session->read('randSalt');
						$captchacode1 = $this->Session->read('code');
						$logindata = $this->request->getData();
						$username = $this->request->getData('chemist_id');
						$password = $this->request->getData('password');
						$captcharequest = $this->request->getData('captcha');
						$current_ip = $this->getRequest()->clientIp();

						if ($current_ip == '::1') {

							$current_ip = '127.0.0.1';
						}

						if ($this->request->getData('captcha') !="" && $this->Session->read('code') == $this->request->getData('captcha')) {

							$PassFromdb = $this->DmiChemistRegistrations->find('all', array('conditions'=> array('chemist_id IS' => $username,'delete_status IS NULL')))->first();

							$userType = 'chem';

							if ($PassFromdb != null && $PassFromdb != '') {

								$passarray1 = $PassFromdb;
								$passarray2 = $passarray1['password'];
								$emailforrecovery = $passarray1['email'];
								$PassFromdbsalted = $randsalt . $passarray2; //adding random salt
								$DbpasssaltedSHA512 = hash('sha512',$PassFromdbsalted); // Encripting

								// check password to db password
								if ($password == $DbpasssaltedSHA512 ) {
									
									$checkLog = $this->Authentication->alreadyLoggedInCheck($username);
									if ($checkLog == 'norecord') {
											
										//the logic from here is transffered to the function and called here
										//on 25-06-2021 by Amol
										$this->chemistLoginProced($username);

									} else {
										
										$_SESSION['username'] = $username;
										$_SESSION['userloggedin'] = 'no';
										$already_loggedin_msg = 'yes';
									}

								} else {

									//Save Chemist Logs Failed Status
									$DmiChemistLogsEntity = $this->DmiChemistLogs->newEntity(
										['customer_id'=>$username,
										'ip_address'=>$current_ip,
										'date'=>date('Y-m-d'),
										'time_in'=>date('H:i:s'),
										'remark'=>'Failed']
									);

									$this->DmiChemistLogs->save($DmiChemistLogsEntity);

									$login_result = 1;
								}

							} else {

								$login_result = 2;
							}

						} else {

							$login_result = 3;
						}

						// show user login failed messgae (by pravin 27/05/2017)
						if ($login_result == 1) {

							//this custom functionn is called on 08-04-2021, to show remaining login attempts
							$remng_attempts_msg = $this->showRemainingLoginAttempts('DmiChemistLogs',$this->request->getData('chemist_id'));
							$message = 'Username or password do not match. <br>'.$remng_attempts_msg;
							$message_theme = 'failed';
							$redirect_to = 'chemist_login';

						} elseif ($login_result == 2) {

							//this custom functionn is called on 08-04-2021, to show remaining login attempts
							$remng_attempts_msg = $this->showRemainingLoginAttempts('DmiChemistLogs',$this->request->getData('chemist_id'));
							$message = 'Username or password do not match. <br>'.$remng_attempts_msg;
							$message_theme = 'info';
							$redirect_to = 'chemist_login';

						} elseif ($login_result == 3) {

							$message = 'Sorry...Wrong Captcha Code Entered';
							$message_theme = 'failed';
							$redirect_to = 'chemist_login';
						}

					} else {

						$message = 'Username or password do not match or your account is freezed';
						$message_theme = 'failed';
						$redirect_to = 'chemist_login';
					}

				} else {

					$message = 'Username or password do not match or your account is freezed';
					$message_theme = 'failed';
					$redirect_to = 'chemist_login';
				}
			}
		}

		// set variables to show popup messages from view file
		$this->set('already_loggedin_msg',$already_loggedin_msg);
		$this->set('message',$message);
		$this->set('message_theme',$message_theme);
		$this->set('redirect_to',$redirect_to);

		if ($message != null) {
			$this->render('/element/message_boxes');
		}

	}



	// CHEMIST REGISTRATION
	// @AUTHOR : PRAVIN BHAKARE
	// #CONTRIBUTER : AKASH THAKRE (Migration)
	// DATE : 25-06-2021 
	
	public function chemistRegistration() {

		$this->Session->write('application_dashboard','packer');
		$this->validUser();
		//Set Varibles For Display
		$message = '';
		$message_theme = '';
		$redirect_to = '';
		//Show button on Side menu
		$this->Beforepageload->showButtonOnSecondaryHome();

		//$url = $this->request->getParam('here');
		//$new = explode('/',$url);
		//$new_method = $new[3];
		//Set the Layout
		$this->viewBuilder()->setLayout('secondary_customer');

		$already_present = 'no';
		$present_email  = '';
		$present_mobile = '';

		$username = $this->Session->read('username');

		if ($this->request->is('post')) {

			//applied condition to check all post data for !empty validation on server side //on 21/10/2017 by Amol
			if (!empty($this->request->getData('email')) && !empty($this->request->getData('mobile')) && !empty($this->request->getData('dob'))) {

				$usersData = $this->request->getData();

				$checkEmailExist =  $this->DmiChemistRegistrations->find('all', array('fields' => 'email', 'conditions' => array('email IS' => base64_encode($usersData['email']))))->first();
				$checkMobileExist =  $this->DmiChemistRegistrations->find('all', array('fields' => 'mobile', 'conditions' => array('mobile IS' => $usersData['mobile'])))->first();

				if ($this->request->getData('chemist_fname') !="" && $this->request->getData('chemist_lname') !="" && $this->request->getData('email') !="" && $this->request->getData('mobile') !="" && $this->request->getData('dob') !="") {

					if ($checkEmailExist == null) {

						if ($checkMobileExist == null) {

							$last_registered_record	= $this->DmiChemistRegistrations->find('all', array('fields'=>'chemist_id','order'=>'id desc'))->first();

							if ($last_registered_record != null) {

								$last_registered_id = substr($last_registered_record['chemist_id'],-4) + 1;
								$chemist_id = 'CHM/'.date('y').'/'.$last_registered_id;

							} else {

								$chemist_id = 'CHM/'.date('y').'/1001';
							}


							$htmlEncoded_dob= htmlentities($this->Customfunctions->changeDateFormat($this->request->getData('dob')), ENT_QUOTES);
							$htmlEncoded_email = htmlentities(base64_encode($this->request->getData('email')), ENT_QUOTES); //for email encoding
							$htmlEncoded_mobile = htmlentities($this->request->getData('mobile'), ENT_QUOTES);
							$htmlEncoded_chemistFirstname = htmlentities($this->request->getData('chemist_fname'), ENT_QUOTES);
							$htmlEncoded_chemistLastname = htmlentities($this->request->getData('chemist_lname'), ENT_QUOTES);

							$certificationType = explode('/',(string) $username); #For Deprecations
                              
							$DmiChemistRegistrationsEntity = $this->DmiChemistRegistrations->newEntity(array(

								'chemist_fname'=>$htmlEncoded_chemistFirstname,
								'chemist_lname'=>$htmlEncoded_chemistLastname,
								'chemist_id'=>$chemist_id,
								'email'=>$htmlEncoded_email,
								'password'=>'91c8559eb34ab5e1ab86f9e80d9753c59b7da0d0e025ec8e7785f19e7852ca428587cdb4f02b5c67d1220ca5bb440b5592cd76b1c13878d7f10a1e568014f4dc', //Agmark123@
							//	'password'=>'3c9909afec25354d551dae21590bb26e38d53f2173b8d3dc3eee4c047e7ab1c1eb8b85103e3be7ba613b31bb5c9c36214dc9f14a42fd7a2fdb84856bca5c44c2', // 123
								//chemist training alredy completed or not status added in db by laxmi bhadade on 12-12-22
								'is_training_completed'=>$this->request->getData('is_training_completed'),
								'mobile'=>base64_encode($htmlEncoded_mobile),
								'dob'=>$htmlEncoded_dob,
								'created_by'=>$username,
								'usertype'=>$certificationType[1],
								'created'=>date('Y-m-d H:i:s'),
								'modified'=>date('Y-m-d H:i:s'),
								
							));
                              
                           
							if ($this->DmiChemistRegistrations->save($DmiChemistRegistrationsEntity)) {

								//Save Chemist Logs
								$DmiChemistLogsEntity = $this->DmiChemistLogs->newEntity(array(

									'chemist_fname'=>$htmlEncoded_chemistFirstname,
									'chemist_lname'=>$htmlEncoded_chemistLastname,
									'chemist_id'=>$chemist_id,
									'email'=>$htmlEncoded_email,
									'password'=>'91c8559eb34ab5e1ab86f9e80d9753c59b7da0d0e025ec8e7785f19e7852ca428587cdb4f02b5c67d1220ca5bb440b5592cd76b1c13878d7f10a1e568014f4dc', //Agmark123@
								//	'password'=>'3c9909afec25354d551dae21590bb26e38d53f2173b8d3dc3eee4c047e7ab1c1eb8b85103e3be7ba613b31bb5c9c36214dc9f14a42fd7a2fdb84856bca5c44c2', // 123
									'mobile'=>base64_encode($htmlEncoded_mobile),
									'dob'=>$htmlEncoded_dob,
									'created_by'=>$username,
									'usertype'=>$certificationType[1],
									//chemist training alredy completed or not status added in db by laxmi bhadade on 12-12-22
									'is_training_completed'=>$this->request->getData('is_training_completed'),											   
									'created'=>date('Y-m-d H:i:s'),
									'modified'=>date('Y-m-d H:i:s')
								));

								$this->DmiChemistLogs->save($DmiChemistLogsEntity);

								$this->set('new_customer_id',$chemist_id);
								$this->set('htmlencodedemail',base64_decode($htmlEncoded_email)); //for email encoding
								$this->set('htmlencodedchemist_fname',$htmlEncoded_chemistFirstname);
								$this->set('htmlencodedchemist_lname',$htmlEncoded_chemistLastname);

								//called function to send link for reset password on registered email//on 13-02-2018 by Amol
								//In below condition the #Customer ID is passed to fetch the newly created Customer ID on Forgot Password - Akash[20-03-2023]
								$this->Authentication->forgotPasswordLib('DmiChemistRegistrations', $htmlEncoded_email,$chemist_id);

								//Save Chemist Allotment Entry
								$DmiChemistAllotmentsEntity = $this->DmiChemistAllotments->newEntity(array(

									'chemist_id'=>$chemist_id,
									'customer_id'=>$username,
									'created_by'=>$username,
									'usertype'=>$certificationType[1],
									'status'=>1,
									'incharge'=>'no',
									'created'=> date('Y-m-d H:i:s'),
									'modified'=>date('Y-m-d H:i:s')
								));

								$this->DmiChemistAllotments->save($DmiChemistAllotmentsEntity);

								#SMS: Chemist Registration
								$this->DmiSmsEmailTemplates->sendMessage(66,$chemist_id); #Packer
								$this->DmiSmsEmailTemplates->sendMessage(67,$chemist_id); #Chemist

								$message = 'You have registered Chemist <strong>"'.$htmlEncoded_chemistFirstname.' '.$htmlEncoded_chemistLastname.'"</strong> with chemist ID is <strong>"'.$chemist_id.'"</strong> .<br>An email has been sent to you and your chemist to set your login password. <br> <strong>Now chemist need to login and complete profile verification.';
								$message_theme = 'success';
								$redirect_to = '../customers/secondary_home';

							} else {

								$message = 'Your chemist details are not saved please check again';
								$message_theme = 'warning';
								$redirect_to = 'chemist_registration';
							}

						} else {

							$present_mobile = 'mobile number';
							$already_present = 'yes';
						}

					} else {

						$present_email = 'email id';
						$already_present = 'yes';
					}

					if ($already_present == 'yes') {

						$message = 'This '.$present_email.' '.$present_mobile.' is already registered with us.';
						$message_theme = 'failed';
						$redirect_to = '../customers/secondary_home';
					}

				} else {

					$message = 'Please enter all details. Do not leave any field empty!!';
					$message_theme = 'warning';
					$redirect_to = 'chemist_registration';
				}

			} else {

				$message = 'Please enter all details. Do not leave any field empty!!';
				$message_theme = 'warning';
				$redirect_to = 'chemist_registration';
			}
		}

		// set variables to show popup messages from view file
		$this->set('message',$message);
		$this->set('message_theme',$message_theme);
		$this->set('redirect_to',$redirect_to);

		if ($message != null) {
			$this->render('/element/message_boxes');

		}
	
	}



	// RESET PASSWORD
	// @AUTHOR : PRAVIN BHAKARE
	// #CONTRIBUTER : AKASH THAKRE (Migration)
	// DATE : 25-06-2021 
	
	public function resetPassword() {

		// set variables to show popup messages from view file
		$message = '';
		$message_theme = '';
		$redirect_to = '';

		$this->viewBuilder()->setLayout('form_layout');

		if (empty($_GET['$key']) || empty($_GET['$id'])) {

			$this->customAlertPage("Sorry You are not authorized to view this page..");
			exit;

		} else {

			$key_id = $_GET['$key'];
			// Added the urldecode funtion to fix the issue of +,<,# etc issue in gettin through get parameter
			// added on 26/11/2018

			$user_id = $this->Authentication->decrypt($_GET['$id']);
			$this->set('user_id',$user_id);

			$countspecialchar = substr_count($user_id ,"/");

			if ($countspecialchar != 2) {

				$this->customAlertPage("Sorry You are not authorized to view this page..");
				exit;
			}

			//fetch applicant details
			$get_record_details = $this->DmiChemistRegistrations->find('all',array('conditions'=>array('chemist_id'=>$user_id)))->first();
			$record_id = $get_record_details['id'];

			//call function to check valid key
			$valid_key_result = $this->DmiChemistsResetpassKeys->checkValidKey($user_id,$key_id);

			if ($valid_key_result == 1) {

				if ($this->request->is('post')) {

					$randsalt = $this->Session->read('randSalt');
					$captchacode1 = $this->Session->read('code');
					$postData = $this->request->getData();
					$username = $this->request->getData('chemist_id');
					$countspecialchar = substr_count($username ,"/");

					if ($countspecialchar != 2) {

						$user_id_not_valid_msg = 'This User Id is not valid';
						$this->set('user_id_not_valid_msg',$user_id_not_valid_msg);
						return null;
						exit;
					}

					$newpassdata = $this->request->getData('new_password');
					$confpassdata = $this->request->getData('confirm_password');

					$reset_pass_result = $this->Authentication->resetPasswordLib('DmiChemistRegistrations',$username,$newpassdata,$randsalt,$postData);

					if ($reset_pass_result == 1) {

						$this->Customfunctions->saveActionPoint('Reset Password (Email Not Matched)','Failed',$user_id); #Action
						$email_id_not_matched_msg = 'Email id & User Id not Matched.';
						$this->set('email_id_not_matched_msg',$email_id_not_matched_msg);
						return null;
						exit;

					} elseif ($reset_pass_result == 2) {

						$this->Customfunctions->saveActionPoint('Reset Password (Incorrect Captcha)','Failed',$user_id); #Action
						$incorrect_captcha_msg = 'Incorrect Captcha code entered.';
						$this->set('incorrect_captcha_msg',$incorrect_captcha_msg);
						return null;
						exit;

					} elseif ($reset_pass_result == 3) {

						$this->Customfunctions->saveActionPoint('Reset Password (Password Not Macthed)','Failed',$user_id); #Action
						$comfirm_pass_msg = 'Confirm password not matched';
						$this->set('comfirm_pass_msg',$comfirm_pass_msg);
						return null;
						exit;

					} elseif ($reset_pass_result == 4) {

						$this->Customfunctions->saveActionPoint('Reset Password (Password is Same as Last)','Failed',$user_id); #Action
						$comfirm_pass_msg = 'This password matched with your last three passwords, Please enter different password';
						$this->set('comfirm_pass_msg',$comfirm_pass_msg);
						return null;
						exit;

					} else {

						$this->Customfunctions->saveActionPoint('Reset Password','Success',$user_id); #Action
						//update link key table status to 1 for successfully
						$this->DmiChemistsResetpassKeys->updateKeySuccess($user_id,$key_id);
						$message = 'Password Changed Successfully';
						$message_theme = 'success';
						$redirect_to = '../../chemist/chemist_login';
					}
				}
			
			} elseif ($valid_key_result == 2) {

				$message = 'Sorry.. This link to reset password is already used or expired. Please proceed through "Forgot Password" again.';
				$message_theme = 'failed';
				$redirect_to = '../../customers/forgot_password';
			}
		}

		// set variables to show popup messages from view file
		$this->set('message',$message);
		$this->set('message_theme',$message_theme);
		$this->set('redirect_to',$redirect_to);
	}



	// HOME
	// @AUTHOR : PRAVIN BHAKARE
	// DATE : 25-06-2021 
			
	public function home() {

		$this->viewBuilder()->setLayout('chemist_home_layout');
		$this->Session->write('application_dashboard','chemist');
	   //chemist application shedule letter display in dashboard added by laxmi b.on 30-12-22
		
		$this->loadModel('DmiChemistRalToRoLogs');
        $scheduleLetter = $this->DmiChemistRalToRoLogs->find('list',array('valueField'=>'pdf_file', 'conditions'=>array('chemist_id'=>$_SESSION['username'], 'training_completed IS'=>NULL, 'reshedule_status IS'=>'confirm')))->first();  
		if(!empty($scheduleLetter)){
			$this->set('pdf_file', $scheduleLetter);
		}
		
		 //chemist reliving  letter from RO display in dashboard added by laxmi b.on 03-01-2023
		$this->loadModel('DmiChemistTrainingAtRo');  
		$relivingLetter = $this->DmiChemistTrainingAtRo->find('list',array('valueField'=>'pdf_file', 'conditions'=>array('chemist_id'=>$_SESSION['username'])))->first();
		if(!empty($relivingLetter)){
		$this->set('relivingLetter', $relivingLetter);
		 }

		  //grant certificate PDF display in dashboard added by laxmi b.on 05-01-2023
		$this->loadModel('DmiChemistGrantCertificatePdfs');  
		$certificates = $this->DmiChemistGrantCertificatePdfs->find('all',array('fields'=>'pdf_file', 'conditions'=>array('customer_id'=>$_SESSION['username'])))->first();
		if(!empty($certificates)){
		$this->set('certificate', $certificates['pdf_file']);
		 }

		 //Ro side Training schedule letter added by laxmi on 6-01-2023
		 $this->loadModel('DmiChemistRoToRalLogs');  
		$ro_side_schedule_letter = $this->DmiChemistRoToRalLogs->find('all',array('fields'=>'ro_schedule_letter', 'conditions'=>array('chemist_id'=>$_SESSION['username'])))->first(); 
		if(!empty($ro_side_schedule_letter)){
		$this->set('ro_side_schedule_letter', $ro_side_schedule_letter['ro_schedule_letter']);
		 }
		 //Ral side Training letter added by laxmi on 6-01-2023
		 $this->loadModel('DmiChemistRalToRoLogs');  
		$ral_letter = $this->DmiChemistRalToRoLogs->find('all',array('fields'=>'pdf_file', 'conditions'=>array('chemist_id'=>$_SESSION['username'])))->first(); 
		if(!empty($ral_letter)){
		$this->set('ral_letter', $ral_letter['pdf_file']);
		 }										 
	}





	// REPLICA ALLOTED LIST
	// @AUTHOR : AKASH THAKRE
	// DATE : 25-06-2021 
	
	public function replicaAllotedList() {

		$this->viewBuilder()->setLayout('chemist_home_layout');
		$this->Customfunctions->replicaAllotedListCall('replica');
	}


	// ALLOTED 15 DIGIT LIST
	// @AUTHOR : AKASH THAKRE
	// DATE : 25-06-2021 
	
	public function alloted15DigitList() {

		$this->viewBuilder()->setLayout('chemist_home_layout');
		$this->Customfunctions->replicaAllotedListCall('15Digit');

	}


	// ALLOTED E CODE LIST
	// @AUTHOR : AKASH THAKRE
	// DATE : 25-06-2021 
	
	
	public function allotedECodeList() {

		$this->viewBuilder()->setLayout('chemist_home_layout');
		$this->Customfunctions->replicaAllotedListCall('ECode');

	}



	// LOGOUT
	// @AUTHOR : AKASH THAKRE
	// DATE : 28-04-2021
	
	public function logout() {
		//LOAD MODEL
		$this->loadModel('DmiChemistLogs');
		$list_id = $this->DmiChemistLogs->find('list', array('valueField' => 'id', 'conditions' => array('customer_id IS' => $this->Session->read('username'))))->toArray();

		if (!empty($list_id)) {

			$fetch_last_id_query = $this->DmiChemistLogs->find('all', array('fields' => 'id', 'conditions' => array('id' => max($list_id), 'remark' => 'Success')))->first();
			$fetch_last_id = $fetch_last_id_query['id'];
			$DmiChemistLogsEntity = $this->DmiChemistLogs->newEntity(array('id' => $fetch_last_id,'time_out' => date('H:i:s')));
			$this->DmiChemistLogs->save($DmiChemistLogsEntity);

			// Update status of browser login history, Done By Pravin Bhakare 12-11-2020 & Added on 28-04-2021 by Akash
			$this->Authentication->browserLoginStatus($this->Session->read('username'),null);
			$this->Session->destroy();
			$this->redirect('/');

		} else {

			$this->customAlertPage("Sorry You are not authorized to view this page..");
			exit;
		}

	}


	
	
	// CHEMIST LOGIN PROCED
	// @AUTHOR : PRAVIN BHAKARE
	// DATE : 28-04-2021
	
	public function chemistLoginProced($username) {

		

		$this->Session->destroy();// destroy old session data
		session_start();

		$current_ip = $this->getRequest()->clientIp();
		if ($current_ip == '::1')
		{
			$current_ip = '127.0.0.1';
		}
										
		$this->Authentication->browserLoginStatus($username,'yes');

		//updating customer successful logs
		$DmiChemistLogsEntity = $this->DmiChemistLogs->newEntity(
			['customer_id'=>$username,
			'ip_address'=>$current_ip,
			'date'=>date('Y-m-d'),
			'time_in'=>date('H:i:s'),
			'remark'=>'Success']
		);

		$this->DmiChemistLogs->save($DmiChemistLogsEntity);

		$customer_data_query = $this->DmiChemistRegistrations->find('all', array('conditions'=> array('chemist_id IS' => $username)))->first();

		$this->Session->write('username',$username);
		$this->Session->write('last_login_time_value',time()); // Store the "login time" into session for checking user activity time (Done by pravin 24/4/2018)
		$this->Session->write('ip_address',$this->getRequest()->clientIp());


		$customer_f_name = $customer_data_query['chemist_fname'];
		$this->Session->write('f_name',$customer_f_name);

		$customer_l_name = $customer_data_query['chemist_lname'];
		$this->Session->write('l_name',$customer_l_name);

		$this->redirect(array('controller'=>'chemist', 'action'=>'home'));


	}
	
		// forward application from Ro to RAL added by laxmi B. on 21-12-2022
		public function forwardApplicationtoRal(){
		$message = "";
		$message_theme = "";
		$redirect_to = "";
		$this->viewBuilder()->setLayout('admin_dashboard');
		$ro_email = $this->Session->read('username');  
		$ro_fname = $this->Session->read('f_name');
		$ro_lname = $this->Session->read('l_name');
		$ro_role  = $this->Session->read('role');
		$chemist_id = $this->Session->read('customer_id');
		//for information
		$this->set('ro_fname', $ro_fname);
		$this->set('ro_lname', $ro_lname);
		$this->set('ro_role', $ro_role);
		//for chemist information
		$this->loadModel('DmiChemistRegistrations');
		$this->loadComponent('Customfunctions');

		$chemist_details = $this->DmiChemistRegistrations->find('all')->where(array('chemist_id'=>$chemist_id))->first();
		$this->set('chemist_id', $chemist_id);

		if(!empty($chemist_details['chemist_fname'] && !empty($chemist_details['chemist_lname']))){
		$this->set('chemist_fname', $chemist_details['chemist_fname']);
		$this->set('chemist_lname', $chemist_details['chemist_lname']);
		}

		// for Ral Office and RAL information
		$this->loadModel('DmiRoOffices');

		$ral_details =$this->DmiRoOffices->find('all')->select(['id','ro_office'])->where(['office_type IS'=>'RAL', 'delete_status IS '=>NULL])->toArray();

		if(!empty($ral_details)){
		$this->set('ral_details',$ral_details);
		}

		// for export unit  condition added by laxmi on 9-1-23
		if(!empty($_SESSION['export_unit']) && $_SESSION['export_unit'] == 'yes'){
		$ral_details = $this->DmiRoOffices->find('all')->select(['id','ro_office'])->where(array('office_type IS'=>'RAL', 'ro_office IS'=> 'Mumbai'))->toArray();
		if(!empty($ral_details)){
		$this->set('ral_details',$ral_details);
		}
		}

		$ro_office_id= $this->DmiRoOffices->find('all')->where(array('ro_email_id'=>$ro_email, 'or'=>array(['office_type IS'=>'RO'],['office_type IS'=>'SO'])))->first();
         
		if($this->request->is('post') != '' ){
		$document= $this->request->getData('document');
		$shedule_from = $this->request->getData('shedule_from');
		$from_date = date('Y-m-d H:i:s', strtotime($shedule_from));
		$shedule_to = $this->request->getData('shedule_to');
		$to_date = date('Y-m-d H:i:s', strtotime($shedule_to));
 
		if (!empty($this->request->getData('document')->getClientFilename())) {

		$attchment = $document; 
		$file_name = $attchment->getClientFilename();
		$file_size = $attchment->getSize();
		$file_type = $attchment->getClientMediaType();
		$file_local_path = $attchment->getStream()->getMetadata('uri');
		// calling file uploading function

		$document = $this->Customfunctions->fileUploadLib($file_name,$file_size,$file_type,$file_local_path);

		}else{
		$document = "";
		}

		$postdata = $this->request->getData();

		if(!empty($postdata['ro_office']) && !empty($postdata['shedule_from'])  && !empty($postdata['shedule_to'])){


		$this->loadModel('DmiChemistRoToRalLogs');
		$chemistId = $postdata['chemist_id'];

		$data = $this->DmiChemistRoToRalLogs->newEntity(array(
		'chemist_id' =>$postdata['chemist_id'],
		'ro_first_name' =>$postdata['ro_first_name'],
		'ro_last_name' => $postdata['ro_last_name'],
		'chemist_first_name' => $postdata['chemist_first_name'],
		'chemist_last_name' => $postdata['chemist_last_name'],
		'ral_office_id' => $postdata['ro_office'], 
		'remark' => $postdata['remark'], 
		'document' => $document,
		'is_forwordedtoral' => 'yes',
		'created' => date('Y-m-d H:i:s'),
		'appliaction_type'=> 4,
		'shedule_from' => $from_date,
		'shedule_to' => $to_date,
		'ro_office_id' =>$ro_office_id['id'],
		));
          
		if($this->DmiChemistRoToRalLogs->save($data)){

		//to enter RAL Email id in allocation and current position table added by laxmi on 10-01-2023
		$this->loadModel('DmiRoOffices');
		$find_office_email_id = $this->DmiRoOffices->find('all',array('fields'=>'ro_email_id', 'conditions'=>array('id'=>$data['ral_office_id'])))->first();  
		$office_incharge_id = $find_office_email_id['ro_email_id'];

		//Entry in allocation table for level_3 Ro
		$this->loadModel('DmiChemistAllocations');
		$allocationEntity = $this->DmiChemistAllocations->newEntity(array(
		'customer_id'=>$chemist_id,
		'level_3'=>$office_incharge_id,
		'current_level'=>$office_incharge_id,
		'created'=>date('Y-m-d H:i:s'),
		'modified'=>date('Y-m-d H:i:s')
		));

		if($this->DmiChemistAllocations->save($allocationEntity)){

		$this->loadModel('DmiChemistAllCurrentPositions');
		//Entry in all applications current position table
		$customer_id =  $chemist_id;
		$user_email_id = $office_incharge_id;
		$current_level = 'level_3';
		$this->DmiChemistAllCurrentPositions->currentUserUpdate($customer_id,$user_email_id,$current_level);//call to custom function from model
		}

		$message ="Chemist Application Forwarded to RAL";
		$message_theme = "success";

		// for rescheduling chemist training at RAL and generate letter pdf so comment this redirect url and redirect on  chemist module  by laxmi B. on 10-05-2023  for chemist modeule
		//$redirect_to = '../applicationformspdfs/chemistAppPdfRoToRal/';

		$redirect_to = '../chemist/listOfChemistApplRoToRal/';

		}else{

		$message ="Something went wrong, Please Try Again!";
		$message_theme = "warning";
		$redirect_to = '../scrutiny/form-scrutiny';
		}

		}else{
		$message ="Please Enter all Field data";
		$message_theme = "warning";
		$redirect_to = '';
		}  
		}
		// set variables to show popup messages from view file
		$this->set('message',$message);
		$this->set('message_theme',$message_theme);
		$this->set('redirect_to',$redirect_to);

		}

			// List of chemist application  forwarded by ro to RAL added by laxmi on 26-12-2022
			public function listOfChemistApplRoToRal(){
			$this->loadModel('DmiChemistRoToRalLogs');
			$this->loadModel('DmiRoOffices');
			$this->loadModel('DmiChemistRegistrations');
			$this->viewBuilder()->setLayout('admin_dashboard');

			$ro_email = $_SESSION['username'];	
			$ro_office_id = $this->DmiRoOffices->find('list',array('valueField'=>'id', 'conditions'=>array('ro_email_id IS'=>$ro_email)))->first(); 
			$listofApp = $this->DmiChemistRoToRalLogs->find('all')->where(array('is_forwordedtoral IS NOT '=>NULL, 'ro_office_id IS'=>$ro_office_id))->order('created desc')->toArray();
               
			$i=0;
			$ral_offices= array();
			$chemistId= array(); 
			$ro_offices = array();

			if(!empty($listofApp)){
			$this->set('listOfChemistApp',$listofApp);
			foreach($listofApp as $list){
			$ral_result = $this->DmiRoOffices->find('all',array('fields'=>'ro_office', 'conditions'=>array('id IS'=>$list['ral_office_id'])))->first();
			$ral_offices[$i] = $ral_result['ro_office'];
			$chemistId[$i] = $this->DmiChemistRegistrations->find('all',array('fields'=>'id', 'conditions'=>array('chemist_id'=>$list['chemist_id'])))->first();


			$ro_officesId = $this->DmiRoOffices->find('all',array('fields'=>'ro_office', 'conditions'=>array('id IS'=>$list['ro_office_id'])))->first();
			if(!empty($ro_officesId)){
			$ro_offices[$i]= $ro_officesId['ro_office'];
			}


			$i= $i+1;
			}
			$this->set('ro_office', $ro_offices);
			$this->set('ral_offices',$ral_offices);
			$this->set('chemisttblId',$chemistId);

			}

			}
			
			//list of application forwarded back Ral to RO added by laxmi on 29/12/2022
			public function  listOfChemistApplRalToRo(){  
			$this->viewBuilder()->setLayout('admin_dashboard');
            
			$this->loadModel('DmiChemistRalToRoLogs');
			$this->loadModel('DmiRoOffices');
			$this->loadModel('DmiChemistTrainingAtRo');
			$this->loadModel('DmiChemistRegistrations');
			$this->loadModel('DmiChemistAllCurrentPositions');
			$this->loadModel('DmiChemistRoToRalLogs');
			
			$ro_email = $this->Session->read('username');
			$chemist_allocation  = $this->DmiChemistAllCurrentPositions->find('all',array('fields'=>array('current_level', 'current_user_email_id')))->where(array('current_user_email_id IS'=>$ro_email))->first();
			$this->set('current_level', $chemist_allocation['current_level']);
			$this->Session->write('current_level', $chemist_allocation['current_level']);	
			
			$ro_office_data = $this->DmiRoOffices->find('all',array('fields'=>array('id', 'office_type','ro_office'), 'conditions'=>array('ro_email_id IS'=>$ro_email, 'ro_email_id'=>$chemist_allocation['current_user_email_id'])))->first(); 
			
			$this->set('level_3_for', $ro_office_data['office_type']);
			$this->Session->write('level_3_for', $ro_office_data['office_type']);
			$listofApp = $this->DmiChemistRalToRoLogs->find('all')->where(array('training_completed IS  '=>1, 'ro_office_id IS'=>$ro_office_data['id']))->order('created desc')->toArray();
			$i=0;
			$ral_offices = array();
			$isTainingCompleted = array();
			$pdf_file = array();
			$chemistTblid = array();
			$is_trainingScheduleRO = array();
			$ro_schedule_letter = array();
			$reschedule_status =  array();
			    
			if(!empty($listofApp)){
			foreach($listofApp as $list){
			$ral_offices[$i] = $this->DmiRoOffices->find('list',array('valueField'=>'ro_office', 'conditions'=>array('id IS'=>$list['ral_office_id'])))->first();

			//training complete or not
			$trainingComplete = $this->DmiChemistTrainingAtRo->find('all', array('fields'=>array('training_completed', 'pdf_file')))->where(array('chemist_id IS'=>$list['chemist_id']))->first();

			if(!empty($trainingComplete)){
			$isTainingCompleted[$i] = $trainingComplete['training_completed'];
			$pdf_file[$i]  = $trainingComplete['pdf_file'];

			}

			$chemistTableid = $this->DmiChemistRegistrations->find('all',array('fields'=>['id', 'chemist_id'], 'conditions'=>array('chemist_id'=>$list['chemist_id'])))->first();
			if(!empty($chemistTableid)){
			$chemistTblid[$i] = $chemistTableid['id'];
			}

			//training schedule at ro side training
			$ro_schedule_training = $this->DmiChemistRoToRalLogs->find('all', array('conditions'=>array('chemist_id'=>$list['chemist_id'])))->last();
           
			if(!empty($ro_schedule_training)){
			$is_trainingScheduleRO[$i] = $ro_schedule_training['is_training_scheduled_ro'];
			$ro_schedule_letter[$i] = $ro_schedule_training['ro_schedule_letter'];
            $reschedule_status[$i] = $ro_schedule_training['reshedule_status'];
			}
               
			$i= $i+1;	
			}

             //check application is final granted
			$this->loadModel('DmiChemistFinalReports');
			$status = $this->DmiChemistFinalReports->find('all',array('fields'=>array('status'), 'conditions'=>['customer_id IS'=>$chemistTableid['chemist_id']]))->last();
            if(!empty($reschedule_status)){
             $this->set('grant_approval_status',$status['status']);
            }
			$this->set('ro_schedule_letter',$ro_schedule_letter);
			$this->set('is_trainingScheduleRO',$is_trainingScheduleRO);
			$this->set('chemistTblid',$chemistTblid);
			$this->set('listOfChemistApp',$listofApp);
			$this->set('ral_office', $ral_offices);
			$this->set('isTrainingComplete',$isTainingCompleted);   
			$this->set('pdf_file',$pdf_file);
			$this->set('ro_offices',$ro_office_data['ro_office']);
			$this->set('reschedule_status',$reschedule_status);
			
 
			}

			}

               //List of chemist training application where training completed at ro office added by laxmi on 3/1/2023
				public function chemistTrainingCompleteAtRo ($id){
				$this->viewBuilder()->setLayout('admin_dashboard');
				$message="";
				$message_theme ="";
				$redirect_to   ="";


				$ro_fname = $this->Session->read('f_name'); 
				$ro_lname = $this->Session->read('l_name');
				$this->set('ro_fname',$ro_fname);
				$this->set('ro_last_name',$ro_lname);


				$this->loadModel('DmiChemistRalToRoLogs');
				$roToRalData = $this->DmiChemistRalToRoLogs->find('all')->where(array('id'=>$id))->first(); 
				if(!empty($roToRalData)){
				$this->set('chemist_fname',$roToRalData['chemist_first_name']);
				$this->set('chemist_lname',$roToRalData['chemist_last_name']);
				$this->set('chemist_id',$roToRalData['chemist_id']);
				$ro_id = $roToRalData['ro_office_id'];
				$customer_id =$roToRalData['chemist_id'];
				}

				if($this->request->is('post') != ''){
				$postdata = $this->request->getData();
                 
				if (!empty($postdata['document']->getClientFilename()) && !empty($postdata['document'])) {
                   
				$attchment = $postdata['document']; 
				$file_name = $attchment->getClientFilename();
				$file_size = $attchment->getSize();
				$file_type = $attchment->getClientMediaType();
				$file_local_path = $attchment->getStream()->getMetadata('uri');
				// calling file uploading function

				$document = $this->Customfunctions->fileUploadLib($file_name,$file_size,$file_type,$file_local_path);
				}else{
				$document = NULL;
				}
              

				if(!empty($postdata['training_completed'] && !empty($postdata['chemist_id']))){
                     
				$this->loadModel('DmiChemistTrainingAtRo');

                $chemist_id = htmlentities($postdata['chemist_id'], ENT_QUOTES);
                $chemist_first_name = htmlentities($postdata['chemist_first_name'], ENT_QUOTES);
                $chemist_last_name = htmlentities($postdata['chemist_last_name'], ENT_QUOTES);
                $remark = htmlentities($postdata['remark'], ENT_QUOTES);
                $training_completed = htmlentities($postdata['training_completed'], ENT_QUOTES);
                $appl_type = 4;

				$data = $this->DmiChemistTrainingAtRo->newEntity(array(
				'chemist_id' =>$chemist_id,
				'chemist_fname' => $chemist_first_name,
				'chemist_lname' => $chemist_last_name,
				'remark' => $remark, 
				'document' => $document,
				'training_completed' =>$training_completed,
				'ro_office_id' =>$ro_id,
				'appliaction_type'=> $appl_type,
				'created' => date('Y-m-d H:i:s'),
				));
                 
				$result = $this->DmiChemistTrainingAtRo->save($data);
				if($result){
				$lastInsertedId = $result['id'];	
				$message ="Chemist Application Training done at Ro";
				$message_theme = "success";
				$redirect_to = '../../Applicationformspdfs/chemistTrainingCompPdfRo/'.$lastInsertedId;
				}else{

				$message ="Something went wrong, Please Try Again!";
				$message_theme = "warning";
				$redirect_to = '../scrutiny/form-scrutiny';
				}

				}else{
				$message ="Please Enter all Field data";
				$message_theme = "warning";
				$redirect_to = '';
				}  
				}
				// set variables to show popup messages from view file
				$this->set('message',$message);
				$this->set('message_theme',$message_theme);
				$this->set('redirect_to',$redirect_to);
				}
				
				
				// Training scheduled at Ro added by laxmi on 27-01-2023
				public function trainingScheduleAtRo($id=null){
				$message = "";
				$message_theme = "";
				$redirect_to = "";
				$this->viewBuilder()->setLayout('admin_dashboard');
				$ro_email = $this->Session->read('username');  
				$ro_fname = $this->Session->read('f_name');
				$ro_lname = $this->Session->read('l_name');
				$ro_role  = $this->Session->read('role');
				//for information
				$this->set('ro_fname', $ro_fname);
				$this->set('ro_lname', $ro_lname);
				$this->set('ro_role', $ro_role);
				//for chemist information
				$this->loadModel('DmiChemistRegistrations');
				$this->loadModel('DmiChemistRalToRoLogs');

				$ralToRoDatas = $this->DmiChemistRalToRoLogs->find('all')->where(['id'=>$id])->last();
				$chemist_id =  $ralToRoDatas['chemist_id'];
				
				$this->Session->write('customer_id',$ralToRoDatas['chemist_id']);

				$chemist_details = $this->DmiChemistRegistrations->find('all')->where(array('chemist_id'=>$chemist_id))->first();
				$this->set('chemist_id', $chemist_id);
                $this->set('ral_reschedule_status', $ralToRoDatas['reshedule_status']);
				if(!empty($chemist_details['chemist_fname'] && !empty($chemist_details['chemist_lname']))){
				$this->set('chemist_fname', $chemist_details['chemist_fname']);
				$this->set('chemist_lname', $chemist_details['chemist_lname']);
				
				}

				$this->loadModel('DmiChemistRoToRalLogs');
                $check_reschedule = $this->DmiChemistRoToRalLogs->find('all', ['conditions'=>['chemist_id IS'=> $chemist_id]])->last();
                 
                $this->set('ro_schedule_from', date('d/m/Y',strtotime($check_reschedule['ro_schedule_from'])));
                $this->set('ro_schedule_to', date('d/m/Y',strtotime($check_reschedule['ro_schedule_to'])));
                $this->set('reschedule_status', $check_reschedule['ro_reschedule_status']);

                $this->set('is_training_scheduled_ro', $check_reschedule['is_training_scheduled_ro']);

				if($this->request->is('post') != '' ){
                 
				$shedule_from = $this->request->getData('shedule_from');
				$from_date = date('Y-m-d H:i:s', strtotime(str_replace('/','-',$shedule_from)));
				$shedule_to = $this->request->getData('shedule_to');
				$to_date = date('Y-m-d H:i:s', strtotime(str_replace('/','-',$shedule_to)));

				$postdata = $this->request->getData();

				if(!empty($postdata['shedule_from'])  && !empty($postdata['shedule_to'])){


				$this->loadModel('DmiChemistRoToRalLogs');
				$chemistId = $postdata['chemist_id'];
				if(empty($check_reschedule['ro_reschedule_status']) && empty($check_reschedule['is_training_scheduled_ro'])) {

				$data = array(
				'ro_schedule_from' => $from_date,
				'ro_schedule_to'=>$to_date,
				'is_training_scheduled_ro' => 1,
				'modified' => date('Y-m-d'),
				);

				$result = $this->DmiChemistRoToRalLogs->updateAll($data,array('chemist_id'=>$chemistId));

				if($result){
				$message ="Chemist Training Schedule at Ro";
				$message_theme = "success";

				//for reschedule dates at ro side comment this pdf genration redirection  and redirect on list by laxmi B. on  10-05-2023 for chemist training module 
				//$redirect_to = '../../applicationformspdfs/trainingScheduleLetterFromRo/';

				$redirect_to = '../../chemist/listOfChemistApplRalToRo/';
				}else{

				$message ="Something went wrong, Please Try Again!";
				$message_theme = "warning";
				$redirect_to = '../../chemist/listOfChemistApplRalToRo/';
				} 
                }else{
                	
                	$reqData = $this->request->getData();
                	$ro_office_id = $this->DmiRoOffices->find('all', ['conditions'=>['ro_email_id IS'=> $_SESSION['username'],'OR'=>array(['office_type IS'=>'RO'], ['office_type IS'=>'SO']), 'delete_status IS'=> NULL ]])->first();
                	
					$this->loadModel('DmiChemistRoToRalLogs');
					$rescheduleDateData = $this->DmiChemistRoToRalLogs->newEntity( array('chemist_id' => $reqData['chemist_id'],
					'chemist_first_name' => $reqData['chemist_first_name'],
					'chemist_last_name' => $reqData['chemist_last_name'],
					'ro_first_name' => $reqData['ro_first_name'],
					'ro_last_name' => $reqData['ro_last_name'],
					'ro_schedule_from'=> date('Y-m-d H:i:s',strtotime(str_replace('/','-',$reqData['shedule_from']))),
					'ro_schedule_to'=> date('Y-m-d H:i:s',strtotime(str_replace('/','-',$reqData['shedule_to']))),
					'is_training_scheduled_ro'=> 1,
					'created' => date('Y-m-d H:i:s'),
					'reshedule_remark' =>$reqData['reshedule_remark'],
					'modified' => date('Y-m-d H:i:s'),
					'appliaction_type' =>4,
					'reshedule_status' =>'confirm',
					'ro_office_id' =>$ro_office_id['id'],
					));
               
                 $result = $this->DmiChemistRoToRalLogs->save($rescheduleDateData);
                 if($result){
				$message ="Chemist Training Schedule at Ro";
				$message_theme = "success";
				$redirect_to = '../../applicationformspdfs/trainingScheduleLetterFromRo/';
                }else{

				$message ="Something went wrong, Please Try Again!";
				$message_theme = "warning";
				$redirect_to = '../../chemist/listOfChemistApplRalToRo/';
				} 
                }

				}else{
				$message ="Please Enter all Field data";
				$message_theme = "warning";
				$redirect_to = '';
				}
				}  
				
			    
				// set variables to show popup messages from view file
				$this->set('message',$message);
				$this->set('message_theme',$message_theme);
				$this->set('redirect_to',$redirect_to);

				}  


}

?>
