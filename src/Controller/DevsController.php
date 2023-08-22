<?php

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\Network\Session\DatabaseSession;
use App\Network\Email\Email;
use App\Network\Request\Request;
use App\Network\Response\Response;
use Cake\Utility\Hash;
use Cake\Datasource\ConnectionManager;

class DevsController extends AppController {

	var $name = 'Devs';

	public function initialize(): void {

		parent::initialize();
		//Load Components
		$this->loadComponent('Customfunctions');
		$this->loadComponent('Authentication');
		//Set Helpers
		$this->viewBuilder()->setHelpers(['Form', 'Html', 'Time']);
		$this->viewBuilder()->setLayout('devs_layout');

	}

	
	public function authenticateUser($username){

		if ($username == null) {
			return 0;
		} else {
			
			//checking primary applicant id pattern ex.102/2016
			if (preg_match("/^[0-9]+\/[0-9]+$/", $username, $matches) == 1) { 
				return 'DmiCustomers';
			//checking secondary applicant id pattern ex.102/1/PUN/006
			} elseif (preg_match("/^[0-9]+\/[0-9]+\/[A-Z]+\/[0-9]+$/", $username, $matches) == 1) { 
				return 'DmiFirms';
			//checking chemist user id pattern ex. CHM/21/1003    
			} elseif (preg_match("/^[CHM]+\/[0-9]+\/[0-9]+$/", $username, $matches) == 1) { 
				return 'DmiChemistRegistrations';
			// checking the if Email User 
			} elseif (preg_match("/^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/", $username,$matches)==1) { 
				return 'DmiUsers';
			} else {
				return 1;
			}
		}
	}
	
	//Login Customer function start
    public function login() {
		
        // set variables to show popup messages from view file
        $message = '';
        $message_theme = '';
        $redirect_to = '';


		if ($this->request->is('post')) {

			$postData = $this->request->getData();
			$passcode = $postData['passcode'];
		
			$validUser = $this->authenticateUser($postData['username']);
			
			if($validUser == 0){
				$message = 'Enter the Customer ID / User Email or Chemist ID".';
				$message_theme = 'failed';
				$redirect_to = 'login';
	
			} elseif ($validUser == 1) {
	
				$message = 'Customer ID / User Email or Chemist ID is not valid';
				$message_theme = 'failed';
				$redirect_to = 'login';
	
			} else {
				
				$loginpro = $this->proceedLogin($postData['username'],$validUser,$passcode);

				if($loginpro==0){

					$message = 'Sorry... It seems you are LMIS module user. Please use "LMIS Login".';
					$message_theme = 'failed';
					$redirect_to = 'login';

				} elseif ($loginpro == 2){

					$message = 'Wrong Passcode Entered.';
					$message_theme = 'failed';
					$redirect_to = 'login';

				}
			}
		}

        // set variables to show popup messages from view file
        $this->set('message', $message);
        $this->set('message_theme', $message_theme);
        $this->set('redirect_to', $redirect_to);
    }



	// Customer Proceed Login
	// Description : this function contains the login logic for Authorized  user & on for multiple logged in check security updates for customers
	// @AUTHOR : Amol Chaudhari (c)
	// #CONTRIBUTER : Akash Thakre (u) (m)
	// DATE : 25-06-2021

	public function proceedLogin($username,$table,$passcode) {

		$this->Session->destroy();
		Session_start();
		$defPasscode = '123';
		if ($passcode == $defPasscode) {

			$this->Session->write('username',$username);
			$this->Session->write('last_login_time_value',time()); 
			$this->Session->write('ip_address',$this->request->clientIp());

			$this->loadModel($table);

			if ($table == 'DmiCustomers') {

				$customer_data_query = $this->$table->find('all', array('conditions'=> array('customer_id IS' => $username)))->first();
				$customer_f_name = $customer_data_query['f_name'];
				$this->Session->write('f_name',$customer_f_name);
				$customer_l_name = $customer_data_query['l_name'];
				$this->Session->write('l_name',$customer_l_name);
				$this->redirect(array('controller'=>'customers', 'action'=>'primary_home'));

			} elseif ($table == 'DmiFirms') {

				$customer_data_query = $this->$table->find('all', array('conditions'=> array('customer_id IS' => $username)))->first();
				$firm_name = $customer_data_query['firm_name'];
				$this->Session->write('firm_name',$firm_name);
				$this->redirect(array('controller'=>'customers', 'action'=>'secondary_home'));
			
			} elseif ($table == 'DmiChemistRegistrations') {

				$customer_data_query = $this->$table->find('all', array('conditions'=> array('chemist_id IS' => $username)))->first();
				$customer_f_name = $customer_data_query['chemist_fname'];
				$this->Session->write('f_name',$customer_f_name);
				$customer_l_name = $customer_data_query['chemist_lname'];
				$this->Session->write('l_name',$customer_l_name);
				$this->redirect(array('controller'=>'chemist', 'action'=>'home'));
				
			} elseif ($table == 'DmiUsers') {

				$user_data_query = $this->$table->find('all', array('conditions'=> array('email IS' => base64_encode($username))))->first();

				$this->loadModel('DmiUserRoles');
				$user_roles = $this->DmiUserRoles->find('all',array('conditions'=>array('user_email_id IS'=>base64_encode($username))))->first();


				if ($user_data_query['division'] == 'DMI' || $user_data_query['division'] == 'BOTH') {

					$userProceedLogin = 'yes';

				} elseif ($user_data_query['division'] == 'LMIS' && !empty($user_roles)) {

					if ($user_roles['set_roles']=='yes') {

						$userProceedLogin = 'yes';
					} 
				}
			
				if ($userProceedLogin == 'yes') {
							
					$this->loadModel('DmiUserRoles');
					$this->loadModel('DmiRoOffices');

					$customer_data_query = $this->$table->find('all', array('conditions'=> array('email IS' => base64_encode($username))))->first();
					$f_name = $customer_data_query['f_name'];
					$l_name = $customer_data_query['l_name'];
					$once_card_no = '000000000000';
					$division = $user_data_query['division'];
					$role = $user_data_query['role'];

					$this->Session->write('username',base64_encode($username));
					$this->Session->write('division',$division);
					$this->Session->write('f_name',$f_name);
					$this->Session->write('l_name',$l_name);
					$this->Session->write('role',$role);
					$this->redirect('/dashboard/home');
				} else {
					return 0;
				}
			
			}

			//$_SESSION['browser_session_d'] = 123;
			$this->Session->write('profile_pic',$customer_data_query['profile_pic']);
			$once_card_no = null; 
			$this->Session->write('once_card_no',$once_card_no);
			$this->Session->write('userloggedin','yes');

		}else{
			return 2;
		}
	}



	// SMS Test 
	// Description : This common logout function is created for the user,chemist and customer customer
	// @Author : Amol Choudhari
	// #Contributer : Akash Thakre
	// Date : 19-04-2022

	public function smsTest() {
	
		
	}


	

}
?>
