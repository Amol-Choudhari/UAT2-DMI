<?php

namespace App\Controller;

use Cake\Event\Event;
use Cake\Network\Session\DatabaseSession;
use App\Network\Email\Email;
use App\Network\Request\Request;
use App\Network\Response\Response;
use Cake\Datasource\ConnectionManager;
use Cake\Http\Session;
use Cake\Utility\Hash;
use Controller\Applicationformspdfs;


class OthermodulesController extends AppController{

	var $name = 'Othermodules';

	public function initialize(): void {

		parent::initialize();
		$this->loadComponent('Createcaptcha');
		$this->loadComponent('Customfunctions');
		$this->loadComponent('Authentication');
		$this->loadComponent('Communication');
		$this->viewBuilder()->setHelpers(['Form','Html','Time']);
		$this->viewBuilder()->setLayout('admin_dashboard');
		$this->Session = $this->getRequest()->getSession();

		
		$this->loadModel('DmiMmrFinalSubmits');
		$this->loadModel('SampleInward');
		$this->loadModel('MSampleType');
		$this->loadModel('MGradeDesc');
		$this->loadModel('SampleInwardDetails');
		$this->loadModel('DmiMmrShowcauseComments');
		$this->loadModel('DmiMmrCancelledFirms');
		$this->loadModel('DmiMmrSuspensions');
		$this->loadModel('DmiMmrActionHomeLogs');
		$this->loadModel('DmiMmrShowcauseNoticePdfs');
		$this->loadModel('DmiMmrShowcauseLogs');
		$this->loadModel('DmiMmrCategories');
		$this->loadModel('DmiMmrLevels');
		$this->loadModel('DmiMmrActions');
		$this->loadModel('DmiMmrTimePeriod');
		$this->loadModel('DmiFirms');
		$this->loadModel('MCommodityCategory');
		$this->loadModel('MCommodity');
		$this->loadModel('DmiMmrActionFinalSubmits');


	}

	//Before Filter
	public function beforeFilter($event) {

		parent::beforeFilter($event);


		$username = $this->getRequest()->getSession()->read('username');
		if ($username == null){
			
			$this->customAlertPage("Sorry You are not authorized to view this page..");
			
		} elseif (preg_match("/^[0-9]+\/[0-9]+\/[A-Z]+\/[0-9]+$/", $username,$matches)==1) {
			//this added intensionally to avoid the login when applicant is logged in - Akash [06-06-2023]
		} else {

			$this->loadModel('DmiUsers');
			//Check User
			$check_user = $this->DmiUsers->find('all',array('conditions'=>array('email IS'=>$this->Session->read('username'))))->first();

			if (empty($check_user)) {
				$this->customAlertPage("Sorry You are not authorized to view this page..");
			}
		}
	}

/*>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>*/

																/***###| RE-ESIGN MODULE|###***/

	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////|[ RE-ESIGN ]|
	//To RE-ESIGN the Renewal Granted PDFs
	public function reEsignModule(){

		//default dummy id in session to prevent customer id session warings.
		if ($this->Session->read('customer_id')==null) {

			$this->Session->write('customer_id','000/0/000/000');
		}

		$this->Session->write('current_level','level_3');
		//$this->Session->delete('re_esigning');
		//$this->Session->delete('reason_to_re_esign');

		//Check Application Already Re-Esigned
		$this->loadModel('DmiReEsignGrantLogs');
		$appl_re_esigned = $this->DmiReEsignGrantLogs->find('list',array('valueField'=>'customer_id','conditions'=>array('re_esigned_by IS'=>$this->Session->read('username'))))->toArray();

		//commented logic to view application only to whome, who granted last
		//on 07-04-2021 by Amol, suggested by Tarun Sir
		/*
		//Get Application Added by Admin From Master
		$this->LoadModel('DmiApplAddedForReEsigns');
		$added_appl = $this->DmiApplAddedForReEsigns->find('list',array('keyField'=>'id','valueField'=>'customer_id','conditions'=>array('action_status'=>'active')))->toArray();

		//Get Renewal Granted Applcation Conditionlly
		$this->loadModel('DmiGrantCertificatesPdfs');
		$appl_list = $this->DmiGrantCertificatesPdfs->find('list',array('keyField'=>'customer_id','valueField'=>'customer_id','conditions'=>array('user_email_id IS'=>$this->Session->read('username'),'customer_id IN'=>$added_appl)))->toArray();
		*/
		
		//As per new logic now appl will available to current RO/SO In-charge of that office
		//on 07-04-2021 by Amol, suggested by Tarun Sir
		//get office Short code for login user as Incharge
		$this->LoadModel('DmiRoOffices');
		$getOffCode = $this->DmiRoOffices->find('list',array('valueField'=>'short_code','conditions'=>array('ro_email_id IS'=>$this->Session->read('username'))))->toList();
		
		//get application added by Admin from master
		$this->LoadModel('DmiApplAddedForReEsigns');
		$appl_list = array();
		foreach($getOffCode as $scode){
			$get_appl = $this->DmiApplAddedForReEsigns->find('list',array('keyField'=>'customer_id','valueField'=>'customer_id','conditions'=>array('customer_id LIKE'=>'%'.$scode.'%','action_status'=>'active','re_esign_status'=>'Pending')))->toArray();
			$appl_list = array_merge($appl_list,$get_appl);
		}

		$this->set('appl_list',$appl_list);
		$this->set('appl_re_esigned',$appl_re_esigned);


		if(null !== ($this->request->getData('cancel'))) {

			//deleteing all created sessions
			$this->Session->delete('customer_id');
			$this->Session->delete('current_level');
			$this->Session->delete('re_esigning');
			$this->Session->delete('reason_to_re_esign');
			$this->Session->delete('re_esign_grant_date');
			$this->Session->delete('pdf_file_name');

			$this->redirect(array('controller'=>'dashboard','action'=>'home'));
		}

	}

	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////|[ RE-ESIGN ]|
	//AJAX Function For Selecting Session IDs//
	Public function onSelectSetCustomerIdSession(){

		$customer_id = $_POST['appl_id'];
		$this->Session->write('customer_id',$customer_id);

		//to get file path
		$this->loadModel('DmiGrantCertificatesPdfs');

		//updated logic as per new order on 01-04-2021, 5 years validity for PP and Laboratory
		//as the module is to reesign renewal certificate only, So now need to re-esign the first grant also, if granted with 2 years of validity
		//but not the old first grant record
		//on 15-09-2021 by Amol

		//$get_file_path = $this->DmiGrantCertificatesPdfs->find('all',array('fields'=>array('pdf_file','date'),'conditions'=>array('pdf_version'=>'2','customer_id'=>$customer_id)))->first();

		$get_file_path = $this->DmiGrantCertificatesPdfs->find('all',array('fields'=>array('pdf_file','date'),'conditions'=>array('customer_id IS'=>$customer_id,'user_email_id IS NOT'=>'old_application'),'order'=>'id desc'))->first();

		$file_path = $get_file_path['pdf_file'];

		$grant_date = chop($get_file_path['date'],'00:00:00');
		$valid_upto = $this->Customfunctions->getCertificateValidUptoDate($customer_id,$grant_date);

		//returning file path and validity date both, to be used in view file
		echo $file_path.'@'.$valid_upto;

		exit;
	}


	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////|[ RE-ESIGN ]|
	//To Create re-esign Session when concent is checked
	Public function createReEsignSession(){

		$this->Session->write('re_esigning','yes');
		$this->Session->write('reason_to_re_esign',$_POST['reason_to_re_esign']);


		//get renewal grant date from DB to maintain on new certificate pdf
		//store in session and use while creating certificate pdf
		$this->loadModel('DmiGrantCertificatesPdfs');

		//updated logic as per new order on 01-04-2021, 5 years validity for PP and Laboratory
		//as the module is to reesign renewal certificate only, So now need to re-esign the first grant also, if granted with 2 years of validity
		//but not the old first grant record
		//on 15-09-2021 by Amol

		//$grant_details = $this->DmiGrantCertificatesPdfs->find('all',array('conditions'=>array('customer_id'=>$this->Session->read('customer_id'),'pdf_version'=>'2')))->first();

		$grant_details = $this->DmiGrantCertificatesPdfs->find('all',array('conditions'=>array('customer_id IS'=>$this->Session->read('customer_id'),'user_email_id IS NOT'=>'old_application'),'order'=>'id desc'))->first();
		//$grant_date = chop($grant_details['date'],'00:00:00');
		$grant_date = explode(' ',$grant_details['date']);
		$grant_date = $grant_date[0];	   
		$this->Session->write('re_esign_grant_date',$grant_date);

		//creating application type session
		$applicationType = 1;
		if ($grant_details['pdf_version'] > 1) {

			$applicationType = 2;
		}

		$this->Session->write('application_type',$applicationType);

		exit;
	}


	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////|[ RE-ESIGN ]|
	//To Create Grant Certificate PDFs to ReESign
	/*	public function createGrantCertificatePdfToReEsign() {

		$customer_id = $this->Session->read('customer_id');
		$split_customer_id = explode('/',$customer_id);

		//$view = new View($this, false);
		//$view->layout = null;
		$all_data_pdf = $this->render($pdf_view_path);

		if ($split_customer_id[1] == 1) {

			$all_data_pdf = $view->render('/Applicationformspdfs/grant_ca_certificate_pdf');

		} elseif ($split_customer_id[1] == 2) {

			$all_data_pdf = $view->render('/Applicationformspdfs/grant_printing_certificate_pdf');

		} elseif ($split_customer_id[1] == 3) {

			$all_data_pdf = $view->render('/Applicationformspdfs/grant_laboratory_certificate_pdf');
		}

		//commented all mpdf code and used tcpdf functionality on 27-01-2020
		/*	$this->Mpdf->init();
			$stylesheet = file_get_contents('css/forms-style.css');
			//$this->Mpdf->WriteHTML($stylesheet,1);

			$this->Mpdf->ob_clean();
			$this->Mpdf->SetDisplayMode('fullpage');
			$this->Mpdf->WriteHTML($all_data_pdf);
		*/

		/*		$rearranged_id = 'G-'.$split_customer_id[0].'-'.$split_customer_id[1].'-'.$split_customer_id[2].'-'.$split_customer_id[3];

		//Check Applicant Last Record Version to Increment
		$this->loadModel('DmiGrantCertificatesPdfs');

		//updated logic as per new order on 01-04-2021, 5 years validity for PP and Laboratory
		//as the module is to reesign renewal certificate only, So now need to re-esign the first grant also, if granted with 2 years of validity
		//but not the old first grant record
		//on 15-09-2021 by Amol

		//$list_id = $this->DmiGrantCertificatesPdfs->find('list', array('fields'=>'id', 'conditions'=>array('customer_id'=>$customer_id)))->toArray();
		//For Version 2 Only
		//$current_pdf_version = 2;

		$grant_details = $this->DmiGrantCertificatesPdfs->find('all',array('conditions'=>array('customer_id IS'=>$customer_id,'user_email_id !='=>'old_application'),'order'=>'id desc'))->first();
		$current_pdf_version = $grant_details['pdf_version'];

		//taking complete file name in session, which will be use in esign controller to esign the file.
		$this->Session->write('pdf_file_name',$rearranged_id.'('.$current_pdf_version.')'.'.pdf');

		$applicationPdf = new ApplicationformspdfsController();
		$applicationPdf->callTcpdf($all_data_pdf,'I',$customer_id,'re_esign');
		$applicationPdf->callTcpdf($all_data_pdf,'F',$customer_id,'re_esign');//on 27-01-2020 with save mode


	}*/

/*>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>*/


																/***###| WORK TRANSFER MODULE|###***/

	// created new function to list of applications fro specific user which restrict admin to deactivate the user.
	// This list will be available to RO/SO dashboard with option to get permission from HO to reallocate the work of any user under his office.
	// Created on 23-06-2021 by Amol
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////|[ WORK-TRANSFER ]|
	public function userWorkTransfer(){

		//Load Models
		$this->loadModel('DmiWorkTransferHoPermissions');
		$this->loadModel('DmiRoOffices');
		$this->loadModel('DmiUsers');
		$this->loadModel('DmiUserRoles');
		
		$message='';
		$redirect_to='';
		$workNotPendingMsg = '';

		//get RO/SO office id of current logged in RO/SO
		$ro_email_id = $this->Session->read('username');
		$get_office_id = $this->DmiRoOffices->find('list',array('keyField'=>'id','conditions'=>array('ro_email_id IS'=>$ro_email_id,'OR'=>array('delete_status IS NULL','delete_status'=>'no'))))->toArray();

		//get all users under the current logged in RO/SO
		$users_list = $this->DmiUsers->find('list',array('keyField'=>'email','valueField'=>'email','conditions'=>array('posted_ro_office IN'=>$get_office_id,'status'=>'active')))->toArray();
		
		//added below loop //for email encoding
			$newArray = array();
			foreach($users_list as $key => $emailId) {

				$newArray[$key] = base64_decode($emailId);
			}
			$users_list = $newArray;
		//till here

		$this->set('users_list',$users_list);

		//if RO/SO officer handles more than one office
		$req_by_office = implode(',',$get_office_id);

		if (null !== ($this->request->getData('get_details'))) {

			$postData = $this->request->getData();
			$user_email_id = $postData['users_list'];
			$inProgressWork = array();

			//get ho permission status
			$get_ho_perm_status = $this->DmiWorkTransferHoPermissions->find('all',array('fields'=>'status','conditions'=>array('req_by_office IN'=>$req_by_office,'req_by_user IS'=>$ro_email_id,'req_for_user IS'=>$user_email_id),'order'=>'id desc'))->first();

			if (!empty($get_ho_perm_status)) {

				$ho_perm_status = $get_ho_perm_status['status'];

			} else {

				$ho_perm_status = '';
			}


			//get scrutiny officers list
			$scrutiny_officers = $this->DmiUserRoles->find('list',array('keyField'=>'user_email_id','valueField'=>'user_email_id','conditions'=>array('OR'=>array('mo_smo_inspection'=>'yes','ho_mo_smo'=>'yes'))))->toArray();
			//added below loop //for email encoding
				$newArray = array();
				foreach($scrutiny_officers as $key => $emailId) {
					$newArray[$key] = base64_decode($emailId);
				}
				$scrutiny_officers = $newArray;
			//till here

			//added below loop //for email encoding
				$new_users_list = array();
				foreach($users_list as $key => $emailId) {
					$new_users_list[$key] = base64_encode($emailId);
				}
			//till here
			
			//get Inspection officers list
			$inspection_officers = $this->DmiUserRoles->find('list',array('keyField'=>'user_email_id','valueField'=>'user_email_id','conditions'=>array('user_email_id IN'=>$new_users_list,'io_inspection'=>'yes')))->toArray();
			//added below loop //for email encoding
			$newArray = array();
				foreach($inspection_officers as $key => $emailId) {
					$newArray[$key] = base64_decode($emailId);
				}
				$inspection_officers = $newArray;
			//till here
			
			$applTypeArray = $this->Session->read('applTypeArray');
			//Index 1, Now Renewal application will not list except DDO dashboard, any where in list. on 20-10-2022
			unset($applTypeArray['1']);
			
			//get flow wise tables
			$this->loadModel('DmiFlowWiseTablesLists');
			$flow_wise_tables = $this->DmiFlowWiseTablesLists->find('all',array('conditions'=>array('application_type IN'=>$applTypeArray),'order'=>'id ASC'))->toArray();
			
			$i=0;
			foreach($flow_wise_tables as $each_flow){
				
				$appl_type_id = $each_flow['application_type'];
				$DmiAllocations = $each_flow['allocation'];
				$DmiHoLevelAllocations = $each_flow['ho_level_allocation'];
				$DmiFinalSubmits = $each_flow['application_form'];
				
				$this->loadModel($DmiAllocations);
				$this->loadModel($DmiHoLevelAllocations);
				$this->loadModel($DmiFinalSubmits);
				
				//get application type name
				$this->loadModel('DmiApplicationTypes');
				$get_appl_type = $this->DmiApplicationTypes->find('all',array('fields'=>'application_type','conditions'=>array('id'=>$appl_type_id)))->first();
				$appl_type = $get_appl_type['application_type'];
				
				//check new application allocation
				$find_first_allocation = $this->$DmiAllocations->find('all',array('conditions'=>array('OR'=>array('level_1'=>$user_email_id, 'level_2'=>$user_email_id, 'level_3'=>$user_email_id))))->toArray();

				if (!empty($find_first_allocation)) {

					foreach ($find_first_allocation as $each_allocation) {

						$customer_id = $each_allocation['customer_id'];

						//updated on 23-06-2021, check for scrutiny with approved level 1
						$check_scrutiny_status = $this->$DmiFinalSubmits->find('all',array('conditions'=>array('customer_id IS'=>$customer_id, 'status'=>'approved','current_level'=>'level_1'),'order'=>'id desc'))->first();

						if (empty($check_scrutiny_status)) {
							//check from which the appl to be released
							//for scrutiny
							if($each_allocation['level_1'] == $user_email_id){

								$inProgressWork[$i]['rels_from'] = 'Scrutiny Allocation';
								$inProgressWork[$i]['appl_type'] = $appl_type;
								$inProgressWork[$i]['appl_id'] = $customer_id;

								$i=$i+1;
							}
						}
						

						//updated on 23-06-2021, check for inspection with approved level 3
						$check_inspection_status = $this->$DmiFinalSubmits->find('all',array('conditions'=>array('customer_id IS'=>$customer_id, 'status'=>'approved','current_level'=>'level_3'),'order'=>'id desc'))->first();

						if(empty($check_inspection_status)) {
							//for Inspection
							if ($each_allocation['level_2'] == $user_email_id) {

								$inProgressWork[$i]['rels_from'] = 'Inspection Allocation';
								$inProgressWork[$i]['appl_type'] = $appl_type;
								$inProgressWork[$i]['appl_id'] = $customer_id;

								$i=$i+1;
							}
						}
					}
				}
				
				//Check HO level application allocation
				$find_ho_allocation = $this->$DmiHoLevelAllocations->find('all',array('conditions'=>array('ho_mo_smo'=>$user_email_id)))->toArray();

				if (!empty($find_ho_allocation)) {
					
					foreach ($find_ho_allocation as $each_allocation) {

						$customer_id = $each_allocation['customer_id'];

						//check in new flow
						$check_new_status = $this->$DmiFinalSubmits->find('all',array('conditions'=>array('customer_id IS'=>$customer_id, 'status'=>'approved','current_level'=>'level_3'),'order'=>'id desc'))->first();

						//if pending in any flow
						if (empty($check_new_status)) {
							
							$inProgressWork[$i]['rels_from'] = 'Scrutiny Allocation(HO)';
							$inProgressWork[$i]['appl_type'] = $appl_type;
							$inProgressWork[$i]['appl_id'] = $customer_id;

							$i=$i+1;
						}
					}
				}
			}

			if (empty($inProgressWork)) {

				//get user table id
				$get_id = $this->DmiUsers->find('all',array('fields'=>'id','conditions'=>array('email IS'=>$user_email_id)))->first();

				$limsWork = $this->getLimsUserWiseSamplesInProgress($get_id['id']);

				if ($limsWork == false) { //for email encoding
					$workNotPendingMsg = '<p class="alert alert-primary middle">No work is pending with <span class="badge badge-info">'.base64_decode($user_email_id).'</span> in DMI module as <b>Scrutiny/Inspection Officer</b>, You can proceed to deactivate this user.</p>';
				}else{
					$workNotPendingMsg = '<p class="alert alert-primary middle">No work is pending with <span class="badge badge-info">'.base64_decode($user_email_id).'</span> in DMI module as <b>Scrutiny/Inspection Officer</b>,<br>But some work pending in <b>LIMS</b> module. Please contact Admin to Transfer LIMS work from <b>User Work Transfer</b> option in LIMS.</p>';
				}
			}

			$this->set(compact('users_list','inProgressWork','scrutiny_officers','inspection_officers','get_ho_perm_status','ho_perm_status'));
		}

		$this->set('workNotPendingMsg',$workNotPendingMsg);

		if (null !== ($this->request->getData('get_ho_permission'))){

			$postData = $this->request->getData();
			$user_email_id = $postData['users_list'];


			$DmiWorkTranferEntity = $this->DmiWorkTransferHoPermissions->newEntity(array(

				'req_by_office'=>$req_by_office,
				'req_by_user'=>$ro_email_id,
				'req_for_user'=>$user_email_id,
				'status'=>'Requested',
				'created'=>date('Y-m-d H:i:s'),
				'modified'=>date('Y-m-d H:i:s'),
			));

			//save request record in ho permission table
			if ($this->DmiWorkTransferHoPermissions->save($DmiWorkTranferEntity)) { //for email encoding

				$message = 'The request for HO(QC) permisssion to transfer work of user id "'.base64_decode($user_email_id).'" has been made successfully. <br><br> Once HO(QC) permits the request, you will be able to transfer the work to another users. Thank you';
				$redirect_to = 'user_work_transfer';
			}
		}

		$this->set('message',$message);
		$this->set('redirect_to',$redirect_to);

	}


	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////|[ WORK-TRANSFER ]|
	//to reallocate /transfer the selected application when action button hits by RO/SO user
	//on 23-06-2021 by Amol
	public function transferWork(){

		$this->autoRender = false;
		//get ajax post data
		$for_user_id = $_POST['for_user_id'];
		$appl_type = $_POST['appl_type'];
		$appl_id = $_POST['appl_id'];
		$rels_from = $_POST['rels_from'];
		$allocate_to = $_POST['allocate_to'];

		$ro_email_id = $this->Session->read('username');
		$this->loadModel('DmiRoOffices');
		$get_office_id = $this->DmiRoOffices->find('list',array('keyField'=>'id','conditions'=>array('ro_email_id IS'=>$ro_email_id,'delete_status is NULL')))->toArray();

		//if RO/SO officer handles more than one office
		$by_office = implode(',',$get_office_id);

		//to replace the allocation conditionally
		
		//get application type from name
		$this->loadModel('DmiApplicationTypes');
		$get_appl_type = $this->DmiApplicationTypes->find('all',array('fields'=>'id','conditions'=>array('application_type IS'=>$appl_type)))->first();
		$appl_type_id = $get_appl_type['id'];
		
		//get flow wise tables
		$this->loadModel('DmiFlowWiseTablesLists');
		$flow_wise_tables = $this->DmiFlowWiseTablesLists->find('all',array('conditions'=>array('application_type IS'=>$appl_type_id),'order'=>'id ASC'))->first();
		
		$DmiAllocations = $flow_wise_tables['allocation'];
		$DmiHoLevelAllocations = $flow_wise_tables['ho_level_allocation'];
		
		$this->loadModel($DmiAllocations);
		$this->loadModel($DmiHoLevelAllocations);

		//if HO allocation need to change
		if ($rels_from == 'Scrutiny Allocation(HO)') {
			
			$allocation_table = $DmiHoLevelAllocations;
			$level_to_update = 'ho_mo_smo';

		} else {// else other allocation tables

			$allocation_table = $DmiAllocations;
			
			if ($rels_from == 'Scrutiny Allocation') {

				$level_to_update = 'level_1';

			} elseif ($rels_from == 'Inspection Allocation') {

				$level_to_update = 'level_2';
			}
		}

		//get latest record id of allocation to update
		$this->loadModel($allocation_table);
		$get_last_id = $this->$allocation_table->find('all',array('conditions'=>array('customer_id IS'=>$appl_id,$level_to_update=>$for_user_id),'order'=>'id desc'))->first();
		//update the allocation record
		$mod_date = date('Y-m-d H:i:s');

		//if current level also contain same user id
		if($get_last_id['current_level'] == $for_user_id){
			$this->$allocation_table->updateAll(array($level_to_update=>"$allocate_to",'modified'=>"$mod_date",'current_level IS'=>"$allocate_to"),array('id'=>$get_last_id['id']));

		}else{
			$this->$allocation_table->updateAll(array($level_to_update=>"$allocate_to",'modified'=>"$mod_date"),array('id IS'=>$get_last_id['id']));

		}

		//to save application transfer logs
		$this->loadModel('DmiWorkTransferLogs');
		$DmiWorkTranferLogEntity = $this->DmiWorkTransferLogs->newEntity(array(

			'customer_id'=>$appl_id,
			'by_office'=>$by_office,
			'by_user'=>$ro_email_id,
			'from_stage'=>$rels_from,
			'from_user'=>$for_user_id,
			'to_user'=>$allocate_to,
			'appl_type'=>$appl_type,
			'created'=>date('Y-m-d H:i:s'),
			'modified'=>date('Y-m-d H:i:s'),
		));


		if ($this->DmiWorkTransferLogs->save($DmiWorkTranferLogEntity)) {

			echo '~done~';
		}else{
			echo '~error~';
		}
		exit;
	}


	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////|[ WORK-TRANSFER ]|
	//to display work transfer request on HO dashboard in work transfer request window
	//added menu on left side for this, //on 23-06-2021 by Amol
	public function workTransferRequests(){

		$this->viewBuilder()->setLayout('admin_dashboard');
		$this->loadModel('DmiWorkTransferHoPermissions');
		$this->loadModel('DmiRoOffices');
		$allRequests = $this->DmiWorkTransferHoPermissions->find('all',array('order'=>'id desc'))->toArray();

		//get office name from office id
		$i=1;
		$office_name = array();

		foreach ($allRequests as $each) {

			$office_id = explode(',',(string) $each['req_by_office']); #For Deprecations
			$get_office_name = '';

			foreach ($office_id as $each_office) {

				$office_details = $this->DmiRoOffices->find('all',array('fields'=>'ro_office','conditions'=>array('id'=>$each_office)))->first();
				$get_office_name .= $office_details['ro_office'].', ';
			}

			$office_name[$i] = $get_office_name;
			$i=$i+1;
		}

		$this->set(compact('allRequests','office_name'));

	}


	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////|[ WORK-TRANSFER ]|
	//to update the status as Permitted when HO click for permission
	//on 23-06-2021 by Amol
	public function hoPermittedForTransfer(){

		$this->autoRender = false;
		//get ajax post data
		$req_by_user = base64_encode($_POST['req_by_user']); //for email encoding
		$req_for_user = base64_encode($_POST['req_for_user']); //for email encoding
		$mod_date = date('Y-m-d H:i:s');

		$this->loadModel('DmiWorkTransferHoPermissions');
		$this->DmiWorkTransferHoPermissions->updateAll(array('status'=>'Permitted','modified'=>"$mod_date"),array('req_by_user'=>$req_by_user,'req_for_user'=>$req_for_user));

		echo '~done~';

	}


	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////|[ WORK-TRANSFER ]|
	//to update the status as rejected when HO click for rejection
	//on 23-06-2021 by Amol
	public function hoRejectedForTransfer(){

		$this->autoRender = false;
		//get ajax post data
		$req_by_user = base64_encode($_POST['req_by_user']); //for email encoding
		$req_for_user = base64_encode($_POST['req_for_user']); //for email encoding
		$mod_date = date('Y-m-d H:i:s');

		$this->loadModel('DmiWorkTransferHoPermissions');
		$this->DmiWorkTransferHoPermissions->updateAll(array('status'=>'Rejected','modified'=>"$mod_date"),array('req_by_user'=>$req_by_user,'req_for_user'=>$req_for_user));

		echo '~done~';

	}


	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////|[ WORK-TRANSFER ]|
	//to show application basic details to RO/SO user in popup
	//on 23-06-2021 by Amol
	public function showApplStatusPopup(){

		$this->autoRender = false;
		//get ajax post data
		$appl_id = $_POST['appl_id'];
		$appl_type = trim($_POST['appl_type']);

		//get firm details
		
		$this->loadModel('DmiAllApplicationsCurrentPositions');
		$this->loadModel('DmiFinalSubmits');
		$this->loadModel('DmiRenewalAllCurrentPositions');
		$this->loadModel('DmiRenewalFinalSubmits');
		$this->loadModel('DmiApplicationTypes');
		$this->loadModel('DmiFlowWiseTablesLists');
		
		$firm_details = $this->DmiFirms->find('all',array('fields'=>array('firm_name','created'),'conditions'=>array('customer_id'=>$appl_id)))->first();
		$firm_name = $firm_details['firm_name'];

		//get application type from name
		$get_appl_type = $this->DmiApplicationTypes->find('all',array('fields'=>'id','conditions'=>array('application_type'=>$appl_type)))->first();
		$appl_type_id = $get_appl_type['id'];
		
		//get flow wise tables
		$flow_wise_tables = $this->DmiFlowWiseTablesLists->find('all',array('conditions'=>array('application_type'=>$appl_type_id),'order'=>'id ASC'))->first();
		$current_position_table = $flow_wise_tables['appl_current_pos'];
		$final_submit_table = $flow_wise_tables['application_form'];
		
		//get application applied on
		$this->loadModel($final_submit_table);
		$applied_on_details = $this->$final_submit_table->find('all',array('fields'=>'created','conditions'=>array('customer_id'=>$appl_id,'status'=>'pending'),'order'=>'id desc'))->first();
		$applied_on = $applied_on_details['created'];

		//get application last status
		$applied_on_details = $this->$final_submit_table->find('all',array('fields'=>array('status','created','current_level'),'conditions'=>array('customer_id IS'=>$appl_id),'order'=>'id desc'))->first();
		$last_status = $applied_on_details['status'];
		$last_status_date = $applied_on_details['created'];

		if ($last_status=='approved' && $applied_on_details['current_level']=='level_1') {

			$last_status = 'Scrutinized';

		} elseif ($last_status=='approved' && $applied_on_details['current_level']=='level_2') {

			$last_status = 'Report Filed';
		}

		//get current position details
		$this->loadModel($current_position_table);
		$get_pos_details = $this->$current_position_table->find('all',array('fields'=>array('current_level'),'conditions'=>array('customer_id'=>$appl_id),'order'=>'id desc'))->first();
		$current_level = $get_pos_details['current_level'];

		if ($current_level == 'applicant') {
			$currently_with = 'Applicant';
		} elseif ($current_level == 'level_1') {
			$currently_with = 'Scrutiny Officer';
		} elseif ($current_level == 'level_2') {
			$currently_with = 'Inspection Officer';
		} elseif ($current_level == 'level_3') {
				$currently_with = 'RO/SO In-charge';
		} elseif ($current_level == 'level_4') {
				$currently_with = 'HO(QC)';
		}

		//create a array to return result
		$result = array(
			'appl_id'=>$appl_id,
			'firm_name'=>$firm_name,
			'applied_on'=>$applied_on,
			'last_status'=>$last_status,
			'currently_with'=>$currently_with,
			'last_status_date'=>$last_status_date
		);

		echo '~'.json_encode($result).'~';
		exit;

	}


	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////|[ WORK-TRANSFER ]|
	//For LIMS user deactivation check on 13-08-2019
	//this function is used to check the LIMS sample in prgress with user, which is selected to deactivate
	public function getLimsUserWiseSamplesInProgress($user_table_id) {

		$from_user = $user_table_id;
		//print_r($from_user); exit;

		//check status in worflow table regarding this user id
		$this->loadModel('Workflow');

		//Check from user id in distination user code colume in workflow table and get list of original sample code.
		$checkUserDestinationCode = $this->Workflow->find('list',array('keyField'=>'id','valueField'=>'org_sample_code','conditions'=>array('dst_usr_cd IS'=>$from_user),'order'=>'id desc'))->toArray();

		$asDestinationUserSample = array_unique($checkUserDestinationCode);

		//Check all get original sample code is final graded or not and get final graded sample list.
		$getOriginalSampleCode = $this->Workflow->find('list',array('keyField'=>'id','valueField'=>'org_sample_code','conditions'=>array('org_sample_code IN'=>$asDestinationUserSample,'stage_smpl_flag'=>'FG')))->toArray();
		$finalGradingCompletedSample = array_unique($getOriginalSampleCode);


		//Getting the sample list that have not final graded yet.
		$PendingFinalGradingSample = array_diff($asDestinationUserSample,$finalGradingCompletedSample);

		//new conditions added on 23-06-2021 by Amol
		//if sample forwarded by the any DMI officer and final grading not done yet,
		//then do not release the officer from LIMS pending work
		$this->loadModel('DmiUsers');
		$get_user_lims_role = $this->DmiUsers->find('all',array('fields'=>'role','conditions'=>array('id'=>$user_table_id)))->first();
		$lims_role = $get_user_lims_role['role'];

		if ($lims_role == 'RO/SO OIC' || $lims_role == 'RO Officer' || $lims_role == 'SO Officer' || $lims_role == 'Ro_assistant') {

			foreach ($PendingFinalGradingSample as $eachkey => $eachValue ) {

				//check if the sample is forwarded by the user
				$forwarded_by_user = 'no';
				$forwardStatus = $this->Workflow->find('all',array('fields'=>array('org_sample_code'),'conditions'=>array('src_usr_cd'=>$from_user,'org_sample_code'=>$eachValue,'stage_smpl_flag'=>'OF')))->first();

				if (!empty($forwardStatus)) {

					$forwarded_by_user = 'yes';
					break;//if found a single asample forwarded by the user and not final graded
				}
			}

		} else {

			$forwarded_by_user = 'yes'; //to proceed for further logic for LIMS users, default set to 'yes'
		}

		//added this condition on 23-06-2021 by Amol
		if ($forwarded_by_user == 'yes') {

			$teststatus =array();  $maxresultID = array(); $pendingtest = array();
			$tabc = array(); $chemistcode = array(); $finalresult =array(); $in_src_usr_cd_pr = array();$update_src_code_id=array();

			foreach ($PendingFinalGradingSample as $eachkey => $eachValue ) {

				//Getting list of stage sample status flag for particular original sample code for from user id
				$result = $this->Workflow->find('list',array('keyField'=>'id','valueField'=>'stage_smpl_flag','conditions'=>array('org_sample_code'=>$eachValue,'dst_usr_cd'=>$from_user),'order'=>'id'))->toArray();

				//Getting current stage sample status flag for particular original sample code for from user id
				$current_sample_status = $this->Workflow->find('all',array('fields'=>array('id','stage_smpl_flag'),'conditions'=>array('org_sample_code'=>$eachValue),'order'=>'id desc'))->first();

				//Checked and make list of TA and TABC stage sample status flag
				foreach($result as $eachkey1 => $eachValue1){

					if(trim($eachValue1) == 'TA'){

						$teststatus[] = $eachkey1;
					}
					if(trim($eachValue1) =='TABC'){

						$tabc[] = $eachkey1;
					}
				}

				if (in_array(trim($current_sample_status['stage_smpl_flag']),array('SD','TA','TABC'))) {

					$currentsamplestatus[] = array($current_sample_status['id'],$eachkey1);

				}else{

					$currentsamplestatus[] = array($current_sample_status['id']);
				}


				//store max id of particular original sample code
				$maxresultID[] = $eachkey1;


				$in_src_usr_cd_pr_list = $this->Workflow->find('list',array('keyField'=>'id','valueField'=>'stage_smpl_cd','conditions'=>array('org_sample_code'=>$eachValue,'src_usr_cd'=>$from_user,'stage_smpl_flag'=>'TA'),'order'=>'id'))->toArray();

				if (!empty($in_src_usr_cd_pr_list)) {

					$in_src_usr_cd_pr[] = $in_src_usr_cd_pr_list;
				}
			}



			foreach ($in_src_usr_cd_pr as $eachloop) {

				foreach ($eachloop as $eachloopkey =>$eachloopvalue ) {

					$loopvaluewithFT = $this->Workflow->find('all',array('fields'=>array('id'),'conditions'=>array('stage_smpl_cd'=>$eachloopvalue,'stage_smpl_flag'=>'FT')))->first();

					if (empty($loopvaluewithFT)) {

						$update_src_code_id[] = trim($eachloopkey);
					}
				}
			}

			//Getting list of actual pending samples on from user side.
			foreach ($maxresultID as $resultKey =>$resultValue) {

				if (in_array($resultValue,$currentsamplestatus[$resultKey])) {

					$finalresult[] = $resultValue;
				}
			}

			//Getting list of actual pending allocated test sample code on from user side.
			if(!empty($teststatus)){

				foreach($teststatus as $eachtest){

					$teststagecd = $this->Workflow->find('all',array('fields'=>array('id','stage_smpl_cd'),'conditions'=>array('id'=>$eachtest)))->first();
					$testsamplestage = $this->Workflow->find('all',array('fields'=>array('id','stage_smpl_cd'),'conditions'=>array('stage_smpl_cd'=>$teststagecd['stage_smpl_cd'],'stage_smpl_flag'=>'FT')))->first();

					if (empty($testsamplestage)) {

						$pendingtest[] = $eachtest;
						$chemistcode[] = trim($teststagecd['stage_smpl_cd']);
					}
				}
			}

			$this->loadModel('MSampleAllocate');

			//$chemist_allocated = $this->MSampleAllocate->find('list',array('fields'=>array('chemist_code','sr_no'),'conditions'=>array('chemist_code IN'=>$chemistcode)))->toArray();

			$finalPendingList = array_unique(array_merge(array_diff($finalresult,$tabc),$pendingtest));

			if (!empty($finalPendingList) || !empty($chemistcode) || !empty($update_src_code_id)) {

				$inprogress_status = 'yes';

			} else {

				$inprogress_status = null;
			}

		} else {

			//added on 23-06-2021 by Amol
			$inprogress_status = null;
		}

		if ($inprogress_status == 'yes') {

			return true;
		} else {
			return false;
		}

	}


/*>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>*/

																/***###| UPDATE FIRM DETAILS MODULE|###***/

	//Update Details Function For Update the Firm Details For Primary and Secondary Firm on 29-12-2021 By Akash
	//for request to change email id and mobile no. of firms and primary applicants

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////|[ UPDATE FIRM DETAILS ]|
	public function firmsListToUpdate(){

		$this->viewBuilder()->setLayout('admin_dashboard');

		//deleteing all created sessions
		$this->Session->delete('customer_id');
		$this->Session->delete('current_level');
		$this->Session->delete('re_esigning');
		$this->Session->delete('reason_to_re_esign');
		$this->Session->delete('re_esign_grant_date');
		$this->Session->delete('pdf_file_name');

		$userName = $this->Session->read('username');
		$this->loadModel('DmiUsers');
		$this->loadModel('DmiDistricts');
		
		$this->loadModel('DmiRoOffices');
		$this->loadModel('DmiUserRoles');
		$this->loadModel('DmiCustomers');
		$conn = ConnectionManager::get('default');

		$user_role = $this->DmiUserRoles->find('all',array('fields'=>'super_admin','conditions'=>array('user_email_id IS'=>$this->Session->read('username'))))->first();

		$list = array();

		if ($user_role['super_admin'] == 'yes') {

			$for_firm = 'primary';
			$primary_id = $this->DmiCustomers->find()->select(['id','modified','f_name','l_name','customer_id'])->toArray();
			$this->set('primary_id',$primary_id);

		} else {

			$for_firm = 'secondary';
			$userOffice = $this->DmiUsers->find('all',array('fields'=>array('posted_ro_office'),'conditions'=>array('email IS'=>$userName)))->first();
			$userPostedOffice = $userOffice['posted_ro_office'];
			
			$office_type = $this->DmiRoOffices->getOfficeDetails($userName);
			$roDistricts = $this->DmiRoOffices->find('list',array('valueField'=>array('id'),'conditions'=>array('ro_email_id IS'=>$userName)))->toArray();

			if ($office_type[1] == 'SO') {
				$conditionA = array('so_id IN'=>$roDistricts);
				$conditionB = array('so_id IN'=>$userPostedOffice); 
			}else{
				$conditionA = array('ro_id IN'=>$roDistricts);
				$conditionB = array('ro_id IN'=>$userPostedOffice); 
			}

			if (!empty($roDistricts)) {
				$districtlis = $this->DmiDistricts->find('list',array('valueField'=>array('id'),'conditions'=>$conditionA))->toArray();
			} else {
				$districtlis = $this->DmiDistricts->find('list',array('valueField'=>array('id'),'conditions'=>$conditionB))->toArray();
			}

			
			foreach($districtlis as $each){

				$firmDetails = $conn->execute("SELECT df.id,df.modified,df.customer_id, df.firm_name, df.email, dd.district_name, df.customer_primary_id, dc.email 
												FROM dmi_firms AS df 
												INNER JOIN dmi_districts AS dd ON dd.id = df.district::integer
												INNER JOIN dmi_customers AS dc ON dc.customer_id = df.customer_primary_id 
												WHERE df.district='$each' OR df.delete_status = 'null' OR df.delete_status = 'no'")->fetchAll('assoc');

				if(!empty($firmDetails)){ $list[] = $firmDetails; }
			}

			$this->set('datalist',$list);
		}

		$this->set('for_firm',$for_firm);

	}



	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////|[ UPDATE FIRM DETAILS ]|
	//for fetching the id to edit
	public function fetchFirmId($id) {

		$this->Session->write('firm_id',$id);
		$this->redirect(array('controller'=>'othermodules','action'=>'update_firm_details'));
	}



	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////|[ UPDATE FIRM DETAILS ]|
	//for fetching the id to edit
	public function fetchPrimaryFirmId($id) {

		$this->Session->write('primary_firm_id',$id);
		$this->redirect(array('controller'=>'othermodules','action'=>'update_firm_details'));
	}



	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////|[ UPDATE FIRM DETAILS ]|
	//Main Function to edit the firm details
	public function updateFirmDetails() {

		$this->viewBuilder()->setLayout('admin_dashboard');
		$firm_id = $this->Session->read('firm_id');
		$this->Session->write('current_level','level_3');

		//Load Models
		$this->loadModel('DmiUserRoles');
		$this->loadModel('DmiGrantCertificatesPdfs');
		$this->loadModel('DmiUpdateFirmDetails');

		//Set the variables for dislaying messsages
		$message = '';
		$message_theme = '';
		$redirect_to = '';

		//check valid user
		$user_access = $this->DmiUserRoles->find('all',array('conditions'=>array('user_email_id IS'=>$this->Session->read('username'))))->first();

		if (!empty($user_access)) {

			if ($user_access['super_admin'] == 'yes') {

				$type='primary';
				$model = 'DmiCustomers';
				$log_model = 'DmiCustomersHistoryLogs';

			} elseif ($user_access['ro_inspection'] == 'yes' || $user_access['so_inspection'] == 'yes') {

				$type='firm';
				$model = 'DmiFirms';
				$log_model = 'DmiFirmHistoryLogs';

			}

			$this->set('type',$type);

			$this->loadModel($model);
			$firm_details = $this->$model->find('all',array('conditions'=>array('id'=>$firm_id)))->first();
			$customer_id = $firm_details['customer_id'];

			$this->Session->write('customer_id',$customer_id);//for further use
			$this->set('firm_details',$firm_details);
			$this->set('model',$model);


			//check if the firm is granted or not
			$is_firm_granted = $this->DmiGrantCertificatesPdfs->find('all',array('conditions'=>array('customer_id IS'=>$customer_id),'order'=>'id desc'))->first();
			$this->set('is_firm_granted',$is_firm_granted);

			$button = 'Udpate';
			if (!empty($is_firm_granted)) {
				$appl_cur_status = 'granted';
			} else {
				$appl_cur_status = 'in_progress';
			}

			//get update log to map the updated field last time, in different color
			$email_updated='';
			$mob_updated='';

			$get_update_log = $this->DmiUpdateFirmDetails->find('all',array('conditions'=>array('customer_id IS'=>$customer_id),'order'=>'id desc'))->first();

			if (!empty($get_update_log)) {

				if ($get_update_log['prev_email']!= $get_update_log['cur_email']) {

					$email_updated='yes';
				}

				if ($get_update_log['prev_mob']!= $get_update_log['cur_mob']) {

					$mob_updated='yes';
				}
			}

			$this->set('email_updated',$email_updated);
			$this->set('mob_updated',$mob_updated);


			//post values
			if ($this->request->is('post')) {

				$postData = $this->request->getData();
				//without esign
				if ($postData['update_details'] == 'Update') {

					$htmlEncodedUpdatedMobile = base64_encode(htmlentities($postData['mobile_no'], ENT_QUOTES));
					$htmlEncodedUpdatedEmail  = base64_encode(htmlentities($postData['email'], ENT_QUOTES));//for email encoding

					if (!empty($firm_id)) {

						//For Primary Firm
						if ($user_access['super_admin'] == 'yes') {

							$previous_email = $firm_details['email'];
							$previous_mobile = $firm_details['mobile'];
							$customer_id = $firm_details['customer_id'];

							//for main table
							$result =array('id'=>$firm_id,
											'mobile'=>$htmlEncodedUpdatedMobile,
											'email'=>$htmlEncodedUpdatedEmail,
											'modified'=>date('Y-m-d H:i:s'));


							//for log table
							$log_result = array(

								'f_name'=>$firm_details['f_name'],
								'm_name'=>$firm_details['m_name'],
								'l_name'=>$firm_details['l_name'],
								'street_address'=>$firm_details['street_address'],
								'district'=>$firm_details['district'],
								'state'=>$firm_details['state'],
								'postal_code'=>$firm_details['postal_code'],
								'mobile'=>$htmlEncodedUpdatedMobile,
								'email'=>$htmlEncodedUpdatedEmail,
								'landline'=>$firm_details['landline'],
								'file'=>$firm_details['file'],
								'modified'=>$firm_details['modified'],
								'document'=>$firm_details['document'],
								'password'=>$firm_details['password'],
								'customer_id'=>$firm_details['customer_id'],
								'photo_id_no'=>$firm_details['photo_id_no']

							);

						} else {

							$previous_email = $firm_details['email'];
							$previous_mobile = $firm_details['mobile_no'];
							$customer_id = $firm_details['customer_id'];

							//for main table
							$result = array('id'=>$firm_id,
											'mobile_no'=>$htmlEncodedUpdatedMobile,
											'email'=>$htmlEncodedUpdatedEmail,
											'modified'=>date('Y-m-d H:i:s'));

							//for log table
							$log_result = array(

								'customer_primary_id'=>$firm_details['customer_primary_id'],
								'firm_name'=>$firm_details['firm_name'],
								'certification_type'=>$firm_details['certification_type'],
								'commodity'=>$firm_details['commodity'],
								'sub_commodity'=>$firm_details['sub_commodity'],
								'street_address'=>$firm_details['street_address'],
								'state'=>$firm_details['state'],
								'mobile_no'=>$htmlEncodedUpdatedMobile,
								'email'=>$htmlEncodedUpdatedEmail,
								'district'=>$firm_details['district'],
								'postal_code'=>$firm_details['postal_code'],
								'modified'=>$firm_details['modified'],
								'customer_id'=>$firm_details['customer_id'],
								'password'=>$firm_details['password'],
								'total_charges'=>$firm_details['total_charges'],
								'export_unit'=>$firm_details['export_unit'],
								'fax_no'=>$firm_details['fax_no'],
								'packaging_materials'=>$firm_details['packaging_materials'],
								'other_packaging_details'=>$firm_details['other_packaging_details'],
								'is_already_granted'=>$firm_details['is_already_granted']

							);

						}

						$ModelEntity = $this->$model->newEntity($result);

						if($this->$model->save($ModelEntity)){

							//this log table is clone of main table
							$this->loadModel($log_model);

							$LogModelEntity = $this->$log_model->newEntity($log_result);

							$this->$log_model->save($LogModelEntity);

							//another log table by user side to maintain the change
							$DmiUpdateFirmDetailsEntity = $this->DmiUpdateFirmDetails->newEntity(array(

								'customer_id'=>$customer_id,
								'update_by'=>$this->Session->read('username'),
								'prev_email'=>$previous_email,
								'prev_mob'=>$previous_mobile,
								'cur_email'=>$htmlEncodedUpdatedEmail,
								'cur_mob'=>$htmlEncodedUpdatedMobile,
								'appl_cur_status'=>$appl_cur_status,
								'created'=>date('Y-m-d H:i:s'),
								'modified'=>date('Y-m-d H:i:s'),
								'reason'=>htmlentities($postData['reason'], ENT_QUOTES)
							));

						//	print_r($DmiUpdateFirmDetailsEntity); exit;
							$this->DmiUpdateFirmDetails->save($DmiUpdateFirmDetailsEntity);

							$this->Session->write('reason_to_re_esign',htmlentities($postData['reason'], ENT_QUOTES));//to update the re-esign grant log table

							if(!empty($is_firm_granted) && $is_firm_granted['user_email_id'] != 'old_application'){
								//set button for re-esign
								$this->set('btn_to_re_esign','yes');
							}

							//again to get updated firm details on screen when retrun false, hence set below
							$firm_details = $this->$model->find('all',array('conditions'=>array('id IS'=>$firm_id)))->first();
							$this->set('firm_details',$firm_details);

							if($type=='primary'){
								$this->set('return_message','The details of primary applicant has been updated');
								return null;
								exit;

							}else{

								if(!empty($is_firm_granted) && $is_firm_granted['user_email_id'] != 'old_application'){
									$this->set('return_message','Firm details are updated, kindly proceed to re-esign the certificate');
									return null;
									exit;

								}else{

									$this->set('return_message','Firm details are updated Successfully');
									return null;
									exit;
								}
							}

							$this->set('return_message',null);
							//return null;

						}
					}

				} else {
						//reesign
				}
			}

			$this->set('button',$button);
		}
	}


/*>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>*/

																/***###| APPLICANT DETAILS MODULE|###***/

	// APPLICANT DETAILS
	// DESCRIPTION : Show applicant email details for new audit changes
	// @AUTHOR : PRAVIN BHAKARE
	// @CONTRIBUTER : AKASH THAKRE (migration)
	// DATE : 25-02-2021
		
	public function applicantDetails() {

		$this->viewBuilder()->setLayout('admin_dashboard');
		$userName = $this->Session->read('username');
		$this->loadModel('DmiUsers');
		$this->loadModel('DmiDistricts');
		
		$this->loadModel('DmiRoOffices');
		$this->loadModel('DmiUserRoles');
		$conn = ConnectionManager::get('default');
		$user_role = $this->DmiUserRoles->find('all', array('fields' => 'super_admin', 'conditions' => array('user_email_id IS' => $this->Session->read('username'))))->first();

		if ($user_role['super_admin'] == 'yes') {

			$districtlist = $this->DmiDistricts->find('list', array('fields' => array('id'), 'order' => array('district_name')))->toArray();

		} else {

			$userOffice = $this->DmiUsers->find('all', array('fields' => array('posted_ro_office'), 'conditions' => array('email IS' => $userName)))->first();
			$userPostedOffice = $userOffice['posted_ro_office'];

			$roDistricts = $this->DmiRoOffices->find('list', array('fields' => array('id'), 'conditions' => array('ro_email_id' => $userName)))->toArray();

			if (!empty($roDistricts)) {
				$districtlist = $this->DmiDistricts->find('list', array('fields' => array('id'), 'conditions' => array('ro_id IN' => $roDistricts)))->toArray();
			} else {
				$districtlist = $this->DmiDistricts->find('list', array('fields' => array('id'), 'conditions' => array('ro_id IS' => $userPostedOffice)))->toArray();
			}

		}

		$list = array();
		foreach ($districtlist as $each) {

			$firmDetails = $conn->execute("SELECT df.customer_id, df.firm_name, df.email, dd.district_name, df.customer_primary_id, dc.email
											FROM dmi_firms AS df INNER JOIN dmi_districts AS dd ON dd.id = df.district::INTEGER
											INNER JOIN dmi_customers AS dc ON dc.customer_id = df.customer_primary_id WHERE df.district='$each'")->fetchAll('assoc');
			if (!empty($firmDetails)) {
				$list[] = $firmDetails;
			}
		}
		
		$this->set('datalist', $list);

	}

/*>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>*/

																/***###| MY TEAM MODULE|###***/

	// myTeam
	// Author : Akash Thakre
	// Description : This function is created to show the list of office users
	// Date : 30-05-2022

	public function myTeam(){

		$message = '';
		$message_theme = '';
		$redirect_to = '';

		$username = $this->Session->read('username');

		$this->loadModel('DmiRoOffices');
		$this->loadModel('DmiApplWithRoMappings');
		$this->loadModel('DmiUsers');
		$this->loadModel('DmiUserRoles');
		$this->loadModel('DmiPaoDetails');
		
		$this->loadModel('DmiDistricts');

		//get details
		$officeDetails = $this->DmiRoOffices->getOfficeDetails($username);

		$office_name = $officeDetails[0];
		$office_type = $officeDetails[1];
		
		//set pao
		$getPao = $this->DmiPaoDetails->getPaoDetails($username,null);
		
		//Set HO Usersd
		$getHoUsers = $this->DmiUserRoles->getHORoles();

		$dy_ama = $getHoUsers['dy_ama'];
		$jt_ama = $getHoUsers['jt_ama'];
		$ama = $getHoUsers['ama'];
		
		//Full Name for Head Office Users
		$dy_ama_name = $this->DmiUsers->getFullName($dy_ama);
		$jt_ama_name = $this->DmiUsers->getFullName($jt_ama);
		$ama_name = $this->DmiUsers->getFullName($ama);
		
		//Get Scrutiny Officers
		$get_scrutinizers_list = $this->DmiRoOffices->getScrutinizerForCurrentOffice();
		$this->set('get_scrutinizers_list',$get_scrutinizers_list);

		// Get Inspection Officers
		$get_io_list = $this->DmiRoOffices->getIoForCurrentOffice();
		$this->set('get_io_list',$get_io_list);

		//Set HO MO SMO
		$ho_scrutinizers_list = $this->DmiUserRoles->getHoScrutinizerForCurrentOffice();
		$this->set('ho_scrutinizers_list',$ho_scrutinizers_list);

		if ($officeDetails[1] == 'SO') {
			
			$soInchargeEmail = $officeDetails[2];
			$soInchargeName = $this->DmiUsers->getFullName($soInchargeEmail);
			
			$roInchargeEmail = $this->DmiRoOffices->getRoOfficeEmail($officeDetails[3]);
			$roInchargeName = $this->DmiUsers->getFullName($roInchargeEmail);
			
		} else {

			$soInchargeEmail = '';
			$soInchargeName = '';
			$roInchargeEmail = $officeDetails[2];
			$roInchargeName = $this->DmiUsers->getFullName($roInchargeEmail);

		}


		//post values
		if ($this->request->is('post')) {

			//get the post value
			$postData = $this->request->getData();
			$customer_id = trim($postData['search_applicant_id']);
			
			$firm_details = $this->DmiFirms->firmDetails($customer_id);
		
			if (!empty($firm_details)) {

				
				$officeDetails = $this->DmiApplWithRoMappings->getOfficeDetails($customer_id);
				$ro_id_from_district = $this->DmiDistricts->getRoIdFromDistrictId($firm_details['district']);
				$ro_id_from_session = $this->DmiUsers->getPostedOffId($_SESSION['username']);

				if (in_array($ro_id_from_session,$ro_id_from_district)) {
					//DDO Details
					$getPao = $this->DmiPaoDetails->getPaoDetails(null,$customer_id);
				
					//Get Scrutiny Officers
					$get_scrutinizers_list = $this->DmiRoOffices->getScrutinizerForCurrentOffice();
					// Get Inspection Officers
					$get_io_list = $this->DmiRoOffices->getIoForCurrentOffice();

					
					$office_name = $officeDetails['ro_office'];
					$office_type = $officeDetails['office_type'];

					//check the Office type
					if ($officeDetails['office_type'] == 'SO') {

						$soInchargeEmail = $officeDetails['ro_email_id'];
						$soInchargeName = $this->DmiUsers->getFullName($soInchargeEmail);

						$roInchargeEmail = $this->DmiRoOffices->getRoOfficeEmail($officeDetails['ro_id_for_so']);
						$roInchargeName = $this->DmiUsers->getFullName($roInchargeEmail);

					} elseif ($officeDetails['office_type'] == 'RO') {

						$roInchargeEmail = $officeDetails['ro_email_id'];
						$roInchargeName = $this->DmiUsers->getFullName($roInchargeEmail);
					}

				} else {
				
					$message = 'Sorry, The entered Applicant Id is not belongs to this office';
					$message_theme = 'failed';
					$redirect_to = '../othermodules/my_team';
				}

			} else {

				$message = 'Sorry, The entered Applicant Id is not Valid';
				$message_theme = 'failed';
				$redirect_to = '../othermodules/my_team';
			}
		}

		//Set DDO
		$this->set('getPao',$getPao);

		//Set Office
		$this->set('office_name',$office_name);
		$this->set('office_type',$office_type);

		//Set RO Information
		$this->set('roInchargeEmail',$roInchargeEmail);
		$this->set('roInchargeName',$roInchargeName);

		//set SO Information
		$this->set('soInchargeEmail',$soInchargeEmail);
		$this->set('soInchargeName',$soInchargeName);

		$this->set(compact('dy_ama','jt_ama','ama'));
		$this->set(compact('dy_ama_name','jt_ama_name','ama_name'));

		$this->set('message', $message);
		$this->set('message_theme', $message_theme);
		$this->set('redirect_to', $redirect_to);

	}




	// getScenarios
	// Author : Shankhpal Shende
	// Description : This function is created to show scenarios view
	// Date : 08-02-2023
	public function getScenarios(){

		$this->loadModel('DmiRoOffices');
		$username = $this->getRequest()->getSession()->read('username');
		$this->set('username',$username);
		// Find out the officer present in RO/SO office, 
		$officerPresentInOff = $this->Customfunctions->findOfficerCountInoffice($username);
		$this->set('officerPresentInOff',$officerPresentInOff);

		$office = $this->DmiRoOffices->getOfficeDetails($username);
		$office_type = $office[1];	

		$this->set('office_type',$office_type);

		// SO  office where SO In-charge has power(role) to grant (more than one officer posted)
		$so_power_to_grant_appl = $this->soAuthorisedToGrantApp($username);

		$this->set('so_power_to_grant_appl',$so_power_to_grant_appl);
	}
		


	// checked if SO have power to grant the CA Non Bevo or printing application
	// added by shankhpal shende on 02/02/2023
	public function soAuthorisedToGrantApp($username){

		$this->loadModel("DmiUserRoles");
		$this->loadModel("DmiApplWithRoMappings");
		$nodalOfficerId = $username;
		$soPowerToGrantApp = 'no';
		
		$soGrantPP = $this->DmiUserRoles->find('all',array('conditions'=>array('so_grant_pp'=>'yes','user_email_id IS'=>$nodalOfficerId)))->first();
	
		if(!empty($soGrantPP))
		{
			$soPowerToGrantApp = 'yes';
		}
		
		return $soPowerToGrantApp;
	}		



	//added method to check if the lab application is NABL accreditated
	//on 03-02-2023 by Shankhpal Shende
	public function checkIfLabNablAccreditated($username){
	
		//check if the applicant for laboratory selected the NABL accreditation
		$this->loadModel("DmiLaboratoryOtherDetails");
		
		$checkNabl = $this->DmiLaboratoryOtherDetails->find('all',['conditions'=>['user_email_id'=>$username],'order'=>'id desc'])->first();

		if(!empty($checkNabl) && $checkNabl['is_accreditated']=='yes'){

			return true;
		}else{
			return null;
		}
		
	}



	// Author : Shankhpal Shende
	// Description : This function is created for display list of records whose status approved and matched
	// Date : 08-02-2023
	// Note : For Routine Inspection (RTI)

	public function routineInspectionList(){

		$this->viewBuilder()->setLayout('admin_dashboard');
		$userName = $this->Session->read('username');
		$this->loadModel('DmiRtiAllocations');
		$this->loadModel('DmiRtiFinalReports');
		$this->loadModel('DmiRoutineInspectionPeriod');
		$this->loadModel('DmiFirms');
		$this->loadModel('DmiFlowWiseTablesLists');
		
		

		$conn = ConnectionManager::get('default');

		// $users = "SELECT
		// tbl.customer_id, dff.firm_name,dff.sub_commodity,tbl.tbl_name
		// FROM dmi_firms AS df
		// INNER JOIN dmi_ca_pp_lab_mapings AS map ON map.pp_id=df.id::varchar
		// INNER JOIN dmi_firms AS dff ON dff.customer_id = map.customer_id
		// INNER JOIN dmi_all_tbls_details AS tbl ON tbl.customer_id = map.customer_id
		// WHERE df.customer_id = '$customer_id'";

		$this->Session->write('application_type',10);
		
		$application_type = $this->Session->read('application_type');

		$customer_id = $this->Session->read('customer_id');
		
		$get_period = $conn->execute("SELECT pp.* FROM dmi_routine_inspection_period AS pp")->fetchAll('assoc');

		$period_ca = $get_period[0]['period'];
			
		$period_lab = $get_period[1]['period'];
			
		$period_pp = $get_period[2]['period'];

		//dates between to fetch records
		$from_date_ca = date("Y-m-d H:i:s",strtotime("-$period_ca month"));

		$from_date_pp = date("Y-m-d H:i:s",strtotime("-$period_pp month"));
			
		$from_date_lab = date("Y-m-d H:i:s",strtotime("-$period_lab month"));

		$to_date = date('Y-m-d H:i:s');

		// to get array list for allocated ca application 
		$list_array_ca = $this->DmiRtiAllocations->find('list',array('keyField'=>'id','valueField'=>'customer_id','conditions'=>array('customer_id like'=>'%'.'/1/'.'%', array('date(created) >=' => $from_date_ca, 'date(created) <=' =>$to_date)),'order'=>'id desc'))->toArray();
		
		// to get array list for allocated pp application 
		$list_array_pp = $this->DmiRtiAllocations->find('list',array('keyField'=>'id','valueField'=>'customer_id','conditions'=>array('customer_id like'=>'%'.'/2/'.'%',array('date(created) >=' => $from_date_pp, 'date(created) <=' =>$to_date)),'order'=>'id desc'))->toArray();
	
		// to get array list for allocated lab application 
		$list_array_lab = $this->DmiRtiAllocations->find('list',array('keyField'=>'id','valueField'=>'customer_id','conditions'=>array('customer_id like'=>'%'.'/3/'.'%',array('date(created) >=' => $from_date_lab, 'date(created) <=' =>$to_date)),'order'=>'id desc'))->toArray();
		//added by shankhpal for approved list of ca 16/05/2023
		$get_rti_approved_list_for_ca = [];
		if(!empty($list_array_ca)){
					$get_rti_approved_list_for_ca = $this->DmiRtiFinalReports->find('all',array('conditions'=>array('customer_id IN'=>$list_array_ca,'status IN'=>'approved','current_level'=>'level_3'),'order'=>'id desc'))->toArray();
		}
		//added by shankhpal for approved list of pp 16/05/2023
		$get_rti_approved_list_for_pp = [];
		if(!empty($list_array_pp)){
			$get_rti_approved_list_for_pp = $this->DmiRtiFinalReports->find('all',array('conditions'=>array('customer_id IN'=>$list_array_pp,'status IN'=>'approved','current_level'=>'level_3'),'order'=>'id desc'))->toArray();
		}
		//added by shankhpal for approved list of lab 16/05/2023
		$get_rti_approved_list_for_lab = [];
		if(!empty($list_array_lab)){
			$get_rti_approved_list_for_lab = $this->DmiRtiFinalReports->find('all',array('conditions'=>array('customer_id IN'=>$list_array_lab,'status IN'=>'approved','current_level'=>'level_3'),'order'=>'id desc'))->toArray();
		}

			$flow_wise_table = $this->DmiFlowWiseTablesLists->find('all',array('conditions'=>array('application_type IS'=>$application_type)))->first();

		$report_pdf_table = $flow_wise_table['DmiRtiReportPdfRecords'];
		$this->loadModel('DmiRtiReportPdfRecords');

	
		$appl_array_ca = array();
		$i=0;
		foreach($get_rti_approved_list_for_ca as $each){	
			
			$customer_id = $each['customer_id'];
		
				$form_type = $this->Customfunctions->checkApplicantFormType($customer_id);
			
				$report_pdf_field = 'pdf_file';
				$get_report_pdf = $this->DmiRtiReportPdfRecords->find('all',array('conditions'=>array('customer_id'=>$customer_id),'order'=>'id desc'))->first();
				
					
					$report_pdf = '';
					$pdf_version_ca = '';
				if(!empty($get_report_pdf)){
					$report_pdf = $get_report_pdf[$report_pdf_field];
					$pdf_version_ca = $get_report_pdf['pdf_version'];
				}

				

				//get firm details
				$firm_details = $this->DmiFirms->firmDetails($customer_id);
				$firm_name = $firm_details['firm_name'];					
				$firm_table_id = $firm_details['id'];

				$report_link = '../inspections/routine_inspection_report_fetch_id/'.$firm_details['id'].'/view/'.$application_type.'/yes';
			
				$appl_array_ca[$i]['customer_id'] = $customer_id.'-'.$form_type;
				$appl_array_ca[$i]['firm_name'] = $firm_name;
				$appl_array_ca[$i]['on_date'] = $each['created'];
				$appl_array_ca[$i]['report_pdf'] = $report_pdf;
				$appl_array_ca[$i]['report_link'] = $report_link;
				$appl_array_ca[$i]['pdf_version'] = $pdf_version_ca;
				
				$i=$i+1;
			//}
		}

		$appl_array_pp = array();
		$i=0;
			
		foreach($get_rti_approved_list_for_pp as $each){	
			
			$customer_id = $each['customer_id'];
		
				$form_type = $this->Customfunctions->checkApplicantFormType($customer_id);
			
				$report_pdf_field = 'pdf_file';
				$get_report_pdf = $this->DmiRtiReportPdfRecords->find('all',array('conditions'=>array('customer_id'=>$customer_id),'order'=>'id desc'))->first();
				
				$pdf_version_pp = '';
				$report_pdf = '';
				if(!empty($get_report_pdf)){
					$report_pdf = $get_report_pdf[$report_pdf_field];
						$pdf_version_pp = $get_report_pdf['pdf_version'];
						
				}

				//get firm details
				$firm_details = $this->DmiFirms->firmDetails($customer_id);
				$firm_name = $firm_details['firm_name'];					
				$firm_table_id = $firm_details['id'];

				$report_link = '../inspections/routine_inspection_report_fetch_id/'.$firm_details['id'].'/view/'.$application_type.'/yes';

				$appl_array_pp[$i]['customer_id'] = $customer_id.'-'.$form_type;
				$appl_array_pp[$i]['firm_name'] = $firm_name;
				$appl_array_pp[$i]['on_date'] = $each['created'];
				$appl_array_pp[$i]['report_pdf'] = $report_pdf;
				$appl_array_pp[$i]['report_link'] = $report_link;
				$appl_array_pp[$i]['pdf_version'] = $pdf_version_pp;
				
				$i=$i+1;
			//}
		}

		$appl_array_lab = array();
		$i=0;
		foreach($get_rti_approved_list_for_lab as $each){	
			
			$customer_id = $each['customer_id'];
		
				$form_type = $this->Customfunctions->checkApplicantFormType($customer_id);

				$report_pdf_field = 'pdf_file';
				$get_report_pdf = $this->DmiRtiReportPdfRecords->find('all',array('conditions'=>array('customer_id'=>$customer_id),'order'=>'id desc'))->first();
				
				$report_pdf = '';
				$pdf_version_lab = '';
				if(!empty($get_report_pdf)){
					$report_pdf = $get_report_pdf[$report_pdf_field];
					$pdf_version_lab= $get_report_pdf['pdf_version'];
				}
			
				//get firm details
				$firm_details = $this->DmiFirms->firmDetails($customer_id);
				$firm_name = $firm_details['firm_name'];					
				$firm_table_id = $firm_details['id'];

				$report_link = '../inspections/routine_inspection_report_fetch_id/'.$firm_details['id'].'/view/'.$application_type.'/yes';


				$appl_array_lab[$i]['customer_id'] = $customer_id.'-'.$form_type;
				$appl_array_lab[$i]['firm_name'] = $firm_name;
				$appl_array_lab[$i]['on_date'] = $each['created'];
				$appl_array_lab[$i]['report_pdf'] = $report_pdf;
				$appl_array_lab[$i]['report_link'] = $report_link;
				$appl_array_lab[$i]['pdf_version'] = $pdf_version_lab;
				
				$i=$i+1;
			//}
		}


	

		// $report_pdf_path = $this->DmiRtiReportPdfRecords->find('all',array('conditions'=>array('customer_id IS'=>$customer_id),'order'=>'id DESC'))->first();
		// $report_link = '/inspections/inspection_report_fetch_id/'.$firm_details['id'].'/view/'.$application_type.'/yes';
		// $this->redirect($report_link);

		$this->set('appl_array_ca',$appl_array_ca);
		$this->set('appl_array_pp',$appl_array_pp);
		$this->set('appl_array_lab',$appl_array_lab);
	
	}






	
	/*>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>*/

											/***###|Action on Misgrading / Suspension / Cancellation / Management of Misgrading Reports|###***/

	// Misgrading Home
	// DESCRIPTION : 
	// A/C  : AKASH THAKRE
	// DATE : 09-12-2022
	// For : Action on Misgrading / Suspension / Cancellation / Management of Misgrading Reports

	public function misgradingHome(){

		$conn = ConnectionManager::get('default');

		$username = $this->Session->read('username');

		$countForScn = '';
		$currentDate = date('Y-m-d H:i:s'); 
		
		//get posted office id
		$postedOffice = $this->DmiUsers->getPostedOffId($username);

		//::: For the Firm who have attached the Sample code and customer_id to take action
		$underThisOffice = $conn->execute("SELECT DISTINCT dg.id,dg.customer_id,dg.sample_code,df.firm_name, df.email,df.mobile_no,mc.commodity_name
											FROM dmi_mmr_final_submits AS dg 
											INNER JOIN dmi_appl_with_ro_mappings AS dd ON dd.customer_id = dg.customer_id
											INNER JOIN dmi_firms AS df ON df.customer_id = dg.customer_id
											INNER JOIN sample_inward AS si ON si.org_sample_code = dg.sample_code
											INNER JOIN m_commodity AS mc ON mc.commodity_code = si.commodity_code
											WHERE dd.office_id='$postedOffice' AND dg.is_attached_packer_sample = 'Y'")->fetchAll('assoc');
		$filteredRecords = [];

		foreach ($underThisOffice as $each) {
			$customer_id = $each['customer_id'];

			// Check if customer_id is present in $is_cancelled
			$is_cancelled = $this->DmiMmrCancelledFirms
				->find()
				->select('customer_id')
				->where(['customer_id' => $customer_id])
				->order(['id' => 'DESC'])
				->first();
			
			// Check if customer_id is present in $suspension_record
			$suspension_record = $this->DmiMmrSuspensions
				->find('all')
				->where([
					'customer_id' => $customer_id,
					'to_date >=' => $currentDate
				])
				->order(['id' => 'DESC'])
				->first();
			
			// Exclude the record if customer_id is present in either $is_cancelled or $suspension_record
			if ($is_cancelled || $suspension_record) {
				continue; // Skip to the next iteration of the loop
			}

			// Rest of your code logic here

			$showcause_status = $this->DmiMmrShowcauseLogs
				->find()
				->select(['status'])
				->where(['sample_code IS' => $each['sample_code']])
				->order(['id' => 'DESC'])
				->first();

			$each['showcause_status'] = $showcause_status ? $showcause_status->status : null;

			// Add the record to the filtered array
			$filteredRecords[] = $each;
		}

		$this->set('underThisOffice', $filteredRecords);




		//::: For the Action Taken Listing
		$actionTaken = $conn->execute("SELECT DISTINCT on (dmafs.customer_id) dmafs.id,dmafs.customer_id,
														df.firm_name, df.email,df.mobile_no,dmafs.status,
														dmafs.is_suspended,dmafs.is_cancelled,dmafs.refer_to_ho
										FROM dmi_mmr_action_final_submits AS dmafs 
										INNER JOIN dmi_appl_with_ro_mappings AS dd ON dd.customer_id = dmafs.customer_id
										INNER JOIN dmi_firms AS df ON df.customer_id = dmafs.customer_id
										INNER JOIN m_commodity_category AS mc ON mc.category_code = df.commodity::INTEGER
										INNER JOIN dmi_certificate_types AS dc ON dc.id = df.certification_type::INTEGER
										WHERE dd.office_id='$postedOffice' AND dmafs.status='final_submit'")->fetchAll('assoc');

		$this->set('actionTaken',$actionTaken);

	}

	// Suspension Home
	// DESCRIPTION : 
	// AUTHOR : AKASH THAKRE
	// DATE : 09-12-2022
	// For : Action on Misgrading / Suspension / Cancellation / Management of Misgrading Reports

	public function fetchIdForAction() {

		$this->Session->write('table_id', $this->request->getQuery('id'));
		$this->Session->write('firm_id', $this->request->getQuery('customer_id'));
		$this->Session->write('sample_code', $this->request->getQuery('sample_code'));
		$this->redirect(array('controller'=>'othermodules','action'=>'misgrading_actions_home'));
	}



	// Suspension Home
	// DESCRIPTION : 
	// AUTHOR : AKASH THAKRE
	// DATE : 09-12-2022
	// For : Action on Misgrading / Suspension / Cancellation / Management of Misgrading Reports

	public function fetchIdForShowcause() {
		
		$this->Session->write('table_id', $this->request->getQuery('id'));
		$this->Session->write('firm_id', $this->request->getQuery('customer_id'));
		$this->Session->write('sample_code', $this->request->getQuery('sample_code'));
		$this->Session->write('whichUser','dmiuser');
		$this->Session->write('scn_mode',$this->request->getQuery('scn_mode'));
		$this->redirect(array('controller'=>'othermodules','action'=>'showcause_home'));
	}

	public function fetchIdFromScnAppl() {

	
		$this->Session->write('table_id', $this->request->getQuery('id'));
		$this->Session->write('firm_id', $this->request->getQuery('customer_id'));
		$this->Session->write('sample_code', $this->request->getQuery('sample_code'));
		$this->Session->write('whichUser','applicant');
		$this->Session->write('scn_mode',$this->request->getQuery('scn_mode'));
		$this->redirect(array('controller'=>'othermodules','action'=>'showcause_home'));
	}



	// DESCRIPTION : To show and redirect on the Misgrading Actions Home.
	// AUTHOR : AKASH THAKRE
	// DATE : 09-12-2022
	// For : Action on Misgrading / Suspension / Cancellation / Management of Misgrading Reports

	public function misgradingActionsHome(){

		$message = '';
		$message_theme = '';
		$redirect_to = '';

		$customer_id = $this->Session->read('firm_id');
		$this->set('customer_id',$customer_id);

		$sample_code = $this->Session->read('sample_code');
		$this->set('sample_code',$sample_code);

		$conn = ConnectionManager::get('default');
		//Load Models
		

		//Firm Details
		$firmDetails = $this->DmiFirms->firmDetails($customer_id); 
		$category = $this->MCommodityCategory->getCategory($firmDetails['commodity']); 

		$sub_comm_id = explode(',',(string) $firmDetails['sub_commodity']); #For Deprecations
		$sub_commodity_value = $this->MCommodity->find('list',array('valueField'=>'commodity_name', 'conditions'=>array('commodity_code IN'=>$sub_comm_id)))->toList();
		
		//Misgrading Category
		$misgradingCategories = $this->DmiMmrCategories->getMisgradingCategoriesList();

		//Misgrading Levels 
		$misgradingLevels = $this->DmiMmrLevels->getMisgradingLevelsList();

		//Misgrading Actions
		$misgradingActions = $this->DmiMmrActions->getMisgradingActionList();

		//Time Period
		$timePeriod = $this->DmiMmrTimePeriod->getTimePeriodList();

		//Status
		$misgradeStatus = $this->DmiMmrActionHomeLogs->getInformation($customer_id,$sample_code);

		//Check if the firm have the commodity of ghee
		$ifGheeComm = $conn->execute("SELECT *
										FROM dmi_firms
										WHERE customer_id = '$customer_id' 
											AND ((sub_commodity LIKE '13' 
											OR sub_commodity LIKE '13,%' 
											OR sub_commodity LIKE '%,13,%' 
											OR sub_commodity LIKE '%,13')) 
											AND sub_commodity NOT LIKE '%13[0-9]%' 
										AND delete_status IS NULL AND certification_type = '1'")->fetchAll('assoc');

		if (!empty($ifGheeComm)) {
			$isCommodityGhee = 'yes';
		}else{
			$isCommodityGhee = 'no';
		}

	
		if (!empty($misgradeStatus)) {

			//Misgrade Category Info
			$misgrade_category = $this->DmiMmrCategories->getMisgradingCategory($misgradeStatus['misgrade_category']);
			$misCatId   = $misgrade_category['id'];
			$misCatName = $misgrade_category['misgrade_category_name'];
			$misCatDscp = $misgrade_category['misgrade_category_dscp'];

			//Misgrade Category Info
			$misgrade_level = $this->DmiMmrLevels->getMisgradingLevel($misgradeStatus['misgrade_level']);
			$misLvlId = $misgrade_level['id'];
			$misLvlName = $misgrade_level['misgrade_level_name'];

			//Misgrade Category Info
			$misgrade_action = $this->DmiMmrActions->getMisgradingAction($misgradeStatus['misgrade_action']);
			$misActId = $misgrade_action['id'];
			$misActName = $misgrade_action['misgrade_action_name'];

			//Misgrade Category Info
			$time_period = $this->DmiMmrTimePeriod->getTimePeriod($misgradeStatus['time_period']);
			
			$periodId = $time_period['time_period'];
			$periodMonth = $time_period['month'];

			$reason = $misgradeStatus['reason'];
			$status = $misgradeStatus['status'];

			
		} else {
			$misCatId = ''; $misCatName = ''; $misCatDscp = '';
			$misLvlId = ''; $misActId = ''; $periodMonth = '';
			$reason = '';   $status = ''; $misLvlName = '';
			$misActName = ''; $periodId = '';
		}
			
		

		//get the post value
		$postData = $this->request->getData();
		
		if (null !== $this->request->getData('save_action')) {
			
			if($this->DmiMmrActionHomeLogs->saveMisgradeAction($postData) == 1){

				$message = 'The Actions to be taken for the current firm is saved Successfully.';
				$message_theme = 'success';
				$redirect_to = '../othermodules/misgradingActionsHome';
			}else{
				$message = 'Sorry, The entered Applicant Id is not Valid';
				$message_theme = 'failed';
				$redirect_to = '../othermodules/misgradingActionsHome'; 
			}
			
		} 
		
		
		$this->set('isCommodityGhee',$isCommodityGhee);
		$this->set(compact('firmDetails','category','sub_commodity_value'));                             #Set the Firm Details
		$this->set(compact('misgradingActions','misgradingLevels','misgradingCategories','timePeriod')); #Set the Dropdowns
		$this->set(compact('misCatId','misCatName','misCatDscp','misLvlName','misActName'));             #Set the Saved Misgrade Category Values
		$this->set(compact('misLvlId','misActId','periodMonth','reason','status','periodId'));           #Set the Saved Values
		$this->set(compact('message','message_theme','redirect_to'));                                    #Set the Message Variables

	}


	// Final Submit Actions
	// DESCRIPTION : 
	// AUTHOR : AKASH THAKRE
	// DATE : 09-12-2022
	// For : Action on Misgrading / Suspension / Cancellation / Management of Misgrading Reports

	public function finalSubmitActions(){

		$this->autoRender = false;
		//get ajax post data
		$customer_id = trim($_POST['customer_id']);
		$sample_code = trim($_POST['sample_code']);
		
		//Get the saved details
		$savedDetails = $this->DmiMmrActionHomeLogs->getInformation($customer_id,$sample_code);
		
		//Showcause Details
		$scnDetails = $this->DmiMmrShowcauseNoticePdfs->find()->where(['customer_id IS'=>$customer_id])->order('id DESC')->first();
		if (!empty($scnDetails)) {
			$showcause = 'Yes';
		}else{
			$showcause = 'No';
		}

		//For Suspension
		if (!empty($savedDetails['misgrade_action'])) {
			if ($savedDetails['misgrade_action'] == 1 || $savedDetails['misgrade_action'] == 5 || $savedDetails['misgrade_action'] == 7) {
				$for_suspension = 'Yes';
			} else {
				$for_suspension = 'No';
			}
		}

		//For Cancellation
		if (!empty($savedDetails['misgrade_action'])) {
			if ($savedDetails['misgrade_action'] == 2 || $savedDetails['misgrade_action'] == 4) {
				$for_cancel = 'Yes';
			} else {
				$for_cancel = 'No';
			}
		}

		//For Head Office
		if (!empty($savedDetails['misgrade_action'])) {
			if ($savedDetails['misgrade_action'] == 3) {
				$refer_to_ho = 'Yes';
			} else {
				$refer_to_ho = 'No';
			}
		}

		

		$arrayFinal = [

			'customer_id' => $customer_id,
			'sample_code' => $sample_code,
			'misgrade_category' => $savedDetails['misgrade_category'],
			'misgrade_level' => $savedDetails['misgrade_level'],
			'misgrade_action' => $savedDetails['misgrade_action'],
			'time_period'=>$savedDetails['time_period'],
			'showcause'=>$showcause,
			'by_user' => $_SESSION['username'],
			'for_suspension' => $for_suspension,
			'for_cancel' => $for_cancel,
			'refer_to_ho' => $refer_to_ho
		];

		$misgradeStatus = $this->DmiMmrActionFinalSubmits->saveActionFinalData($arrayFinal);
		
		echo '~'. $misgradeStatus. '~';
		
		exit;
	}


	// DESCRIPTION : To show the suspension / cancellation home
	// AUTHOR : AKASH THAKRE
	// DATE : 09-12-2022
	// For : Action on Misgrading / Suspension / Cancellation / Management of Misgrading Reports

	public function showcauseHome(){

		$message = '';
		$message_theme = '';
		$redirect_to = '';

		$reason  = '';
		$status = '';

		$whichUser = $this->Session->read('whichUser');

		$customer_id = $this->Session->read('firm_id');
		$this->set('customer_id',$customer_id);

		$sample_code = $this->Session->read('sample_code');
		$this->set('sample_code',$customer_id);

		

		if ($whichUser == 'applicant') {

			$this->viewBuilder()->setLayout('secondary_customer');
			$this->loadComponent('Beforepageload');
			$this->Beforepageload->showButtonOnSecondaryHome();
			$customer_last_login = $this->Customfunctions->customerLastLogin();
			$this->set('customer_last_login', $customer_last_login);
		}

	

		//Firm Details
		$firmDetails = $this->DmiFirms->firmDetails($customer_id); 
		$username = $this->Session->read('username');

		$category = $this->MCommodityCategory->getCategory($firmDetails['commodity']); 

		$sub_comm_id = explode(',',(string) $firmDetails['sub_commodity']); #For Deprecations
		$sub_commodity_value = $this->MCommodity->find('list',array('valueField'=>'commodity_name', 'conditions'=>array('commodity_code IN'=>$sub_comm_id)))->toList();
	
		$statusofscn = $this->DmiMmrShowcauseLogs->getInformation($customer_id);
		if (!empty($statusofscn)) {
			$reason = $statusofscn['reason'];
			$status = $statusofscn['status'];
			$statusofscn = $statusofscn;
		} else {
			$statusofscn=null;
		}

		$this->set('statusofscn',$statusofscn);
		

		$mmrlogs = $this->DmiMmrFinalSubmits->find()->where(['sample_code IS' => $sample_code])->order('id DESC')->first();
		$this->set('mmrlogs',$mmrlogs);

		$sampleDetails = $this->SampleInward->find()->where(['org_sample_code' => $sample_code])->first();
		$this->set('sampleDetails',$sampleDetails);

		$commodity_name = $this->MCommodity->getCommodity($sampleDetails['commodity_code']);

		$sample_type_code = $this->MSampleType->find()->where(['sample_type_code' => $sampleDetails['sample_type_code'],'display' => 'Y'])->first();

		$grade_descrition = $this->MGradeDesc->find()->select(['grade_desc'])->where(['grade_code' => $sampleDetails['grade'],'display' => 'Y'])->first();
		
		$sample_inward_details = $this->SampleInwardDetails->find()->where(['org_sample_code' => $_SESSION['sample_code']])->order('id DESC')->first();


		$sampleArray = [
			'sample_code' => $sampleDetails['org_sample_code'],
			'sample_type' => $sample_type_code['sample_type_desc'],
			'commodity' => $commodity_name,
			'grade_desc' => $grade_descrition['grade_desc'],
			'smpl_drwl_dt' => $sample_inward_details['sample_inward_details'],
			'replica_serial_no' => $sample_inward_details['replica_serial_no'],
			'tbl' => $sample_inward_details['tbl'],
			'pack_size' => $sample_inward_details['pack_size']
		];
		
		$this->set('sampleArray',$sampleArray);

		$scn_pdf = $this->DmiMmrShowcauseNoticePdfs->find()->select(['pdf_file'])->where(['customer_id' => $customer_id])->order('id DESC')->first();
		if (!empty($scn_pdf)) {
			$scn_pdf_path = $scn_pdf['pdf_file'];
		} else {
			$scn_pdf_path=null;
		}

		$this->set('scn_pdf_path',$scn_pdf_path);

		// fetch comments history
		$showcause_comments = $this->DmiMmrShowcauseComments->find('all',array('conditions'=>array('sample_code IS'=>$sample_code,'OR'=>array('comment_by IS'=>$username,'comment_to IS'=>$username)),'order'=>'id'))->toArray();
		$comments_result = array_merge($showcause_comments);
		$comments_result = Hash::sort($comments_result, '{n}.created', 'desc');	
		$this->set('showcause_comments',$comments_result);
		
		//post values
		if ($this->request->is('post')) {

			//get the post value
			$postData = $this->request->getData();
			
			if (null !== $this->request->getData('save_action')) {

				if($this->DmiMmrShowcauseLogs->saveLog($postData) == 1){

					//Create the PDF for the showcause notice
					$Applicationformspdfs = new ApplicationformspdfsController();
					$pdf_details = $Applicationformspdfs->showcauseApplPdf();

					$message = 'Saved the details for Show Cause Notice Succesfully.';
					$message_theme = 'success';
					$redirect_to = '../othermodules/showcauseHome';

				}else{

					$message = 'Sorry, The details could not be saved. Try Again';
					$message_theme = 'failed';
					$redirect_to = '../othermodules/showcauseHome'; 
				}

			} elseif (null !== $this->request->getData('update_action')) {

				if($this->DmiMmrShowcauseLogs->updateLog($postData) == 1){
					$message = 'Updated the details for Show Cause Notice Succesfully.';
					$message_theme = 'success';
					$redirect_to = '../othermodules/showcauseHome';
				}else{
					$message = 'Sorry, The details could not be saved. Try Again';
					$message_theme = 'failed';
					$redirect_to = '../othermodules/showcauseHome'; 
				}
				
			} elseif(null!==($this->request->getData('save_applicant_comment'))){
				
			
				$comment_by = $this->Session->read('username');
				$comment_to = 'bWVsdmlucm95LnBAZ292Lmlu';
				$comment = htmlentities($this->request->getData('reffered_back_comment'), ENT_QUOTES);
				$from_user = 'applicant';
				$to_user = 'ro';

				$result = $this->DmiMmrShowcauseComments->replyFromApplicant($customer_id,$sample_code,$comment_by,$comment_to,$comment,$from_user,$to_user);
				
				if($result == 1){

					$this->Customfunctions->saveActionPoint('Reffered Back Comment Saved', 'Success'); #Action
					$message = "Comment on Showcause notice is sent tio the agmark successfully.";
					$message_theme = "success";
					$redirect_to = '../customers/secondary-home';

				}elseif($result == 2){

					$this->Customfunctions->saveActionPoint('Reffered Back Comment Saved', 'Failed'); #Action
					$message = " Section, Sorry you can not save blank Reffered back";
					$message_theme = "failed";
					$redirect_to = '../customers/secondary-home';
				}
			}
		}

		$this->set(compact('firmDetails','category','sub_commodity_value'));
		$this->set(compact('reason','status'));
		$this->set(compact('message','message_theme','redirect_to'));

		if ($message != null){
			$this->render('/element/message_boxes');
		}

	}



	// Description : To sent the final showcause notice to applicant.
	// AUTHOR : Akash Thakre
	// DATE : 09-12-2022
	// For : Action on Misgrading / Suspension / Cancellation / Management of Misgrading Reports

	public function finalSendNotice(){

		$this->autoRender = false;
		//get ajax post data
		$customer_id = $_POST['customer_id'];
		$showCause = $this->DmiMmrShowcauseLogs->getInformation($customer_id);
		$result =  $this->DmiMmrShowcauseLogs->sendFinalNotice($showCause); 
		
		if ($result == true) {
			echo '~done~';
		}
		exit;
	}



	//Description : To give the user an list of CAs to select for suspension of cancellation 
	//Author : Akash Thakre
	//Date : 24-04-2023
	//For : Action on Misgrading / Suspension / Cancellation / Management of Misgrading Reports

	public function suspensionHome(){

		$customer_id = $this->getRequest()->getQuery('customer_id');
    	$sample_code = $this->getRequest()->getQuery('sample_code');
    	$for_module = $this->getRequest()->getQuery('for_module');

		$this->Session->write('customer_id',$customer_id);
		$this->Session->write('sample_code',$sample_code);
		$this->Session->write('for_module',$for_module);
		$this->Session->write('current_level','level_3');
		$this->set('btn_to_re_esign','yes');

		//get the view for diffrent module
		if ($for_module == 'Suspension') {

			$dashMessage = "Note: This module is to:<br>
			1. To process the Suspension of the Packer through AQCMS system online with option to select time period for suspension.<br>
			2. To lock registered Packer account on for time period of suspension.<br>
			3. Click on Proceed to Esign button. After esign the suspension  will be completed and packer will receive the suspension notice on dashboard.";
	
		} elseif ($for_module == 'Cancellation') {

			$dashMessage = "Note: This module is to:<br>
			1. To process the Cancellation of the Packer through AQCMS system online.<br>
			2. To cancel registered Packer account permantly and cancel the packer's certificates.";
			
		} elseif ($for_module == 'Refer') {
			
		}

		//Get the details
		$actionDetails = $this->DmiMmrActionFinalSubmits->find()->where(['customer_id' => $customer_id, 'sample_code' => $sample_code])->order('id DESC')->first();
		$is_showcause = $actionDetails['showcause'];

		//Misgrade Category Info
		$misgrade_category = $this->DmiMmrCategories->getMisgradingCategory($actionDetails['misgrade_category']);
		$misgradeCategory  = $misgrade_category['misgrade_category_name']. " : " .$misgrade_category['misgrade_category_dscp'];

		//Misgrade Category Info
		$misgrade_level = $this->DmiMmrLevels->getMisgradingLevel($actionDetails['misgrade_level']);
		$levelName = $misgrade_level['misgrade_level_name'];

		//Misgrade Category Info
		$misgrade_action = $this->DmiMmrActions->getMisgradingAction($actionDetails['misgrade_action']);
		$actionName = $misgrade_action['misgrade_action_name'];

		//Misgrade Category Info
		$time_period = $this->DmiMmrTimePeriod->getTimePeriod($actionDetails['time_period']);
		$periodMonth = $time_period['month'];

		//Packer Details
		$firmDetails = $this->DmiFirms->firmDetails($customer_id); 
		$category = $this->MCommodityCategory->getCategory($firmDetails['commodity']); 
		$sub_comm_id = explode(',',(string) $firmDetails['sub_commodity']); #For Deprecations
		$sub_commodity_value = $this->MCommodity->find('list',array('valueField'=>'commodity_name', 'conditions'=>array('commodity_code IN'=>$sub_comm_id)))->toList();


		$this->set(compact('firmDetails','category','sub_commodity_value'));
		$this->set(compact('dashMessage','misgradeCategory','levelName','actionName','periodMonth','is_showcause'));
		$this->set(compact('customer_id','sample_code','for_module'));
	}



	public function listOfPackerActionTaken()
	{
		$conn = ConnectionManager::get('default');

		$firmDetails = $conn->execute("
			SELECT dmafs.customer_id, df.firm_name, dmafs.sample_code
			FROM dmi_mmr_action_final_submits AS dmafs 
			INNER JOIN dmi_firms AS df ON df.customer_id = dmafs.customer_id 
			WHERE dmafs.status != 'final_submit'
			GROUP BY dmafs.customer_id, df.firm_name, dmafs.sample_code
			ORDER BY dmafs.customer_id DESC
			LIMIT 1
		")->fetchAll('assoc');

		// Customer IDs
		$customer_list = [];

		foreach ($firmDetails as $customer) {
			$customer_list[$customer['customer_id']] = $customer['customer_id'] . ' - ' . $customer['firm_name'] . ' (' . $customer['sample_code'] . ')';
		}

		$this->set('customer_list', $customer_list);

		if ($this->request->is('post')) {

			$customer_id = $this->request->getData('customer_id');
			$sample_code = substr($customer_list[$this->request->getData('customer_id')], strrpos($customer_list[$this->request->getData('customer_id')], '(') + 1, -1);
			$actionDetails = $this->DmiMmrActionFinalSubmits->find()->where(['customer_id' => $customer_id, 'sample_code' => $sample_code])->order('id DESC')->first();

			if (!empty($actionDetails)) {

				if ($actionDetails['for_suspension'] == 'Yes') {
					$for_module = 'Suspension';
				} elseif ($actionDetails['for_cancel'] == 'Yes') {
					$for_module = 'Cancellation';
				} elseif ($actionDetails['refer_to_ho'] == 'Yes') {
					$for_module = 'Refer';
				}
			
				// Redirect to suspensionHome() with parameters
				return $this->redirect([
					'controller' => 'othermodules',
					'action' => 'suspensionHome',
					'?' => [
						'customer_id' => $customer_id,
						'sample_code' => $sample_code,
						'for_module' => $for_module
					]
				]);
			} else {
				// Handle the else case if needed
			}
		}
	
	}

	

}

?>
