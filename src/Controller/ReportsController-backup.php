<?php

namespace App\Controller;

use Cake\Event\Event;
use Cake\Network\Session\DatabaseSession;
use App\Network\Email\Email;
use App\Network\Request\Request;
use App\Network\Response\Response;
use Cake\Datasource\ConnectionManager;
use phpDocumentor\Reflection\Types\This;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\ORM\TableRegistry;
use Cake\Collection\Collection;
use Cake\ORM\Query;
use Cake\Database\Expression\QueryExpression;
use App\Controller\ApplicationformspdfsController;
use Cake\Core\Configure;



class ReportsController extends AppController {

	var $name = 'Reports';

	public function initialize(): void {
		// ini_set('memory_limit', '1024M');
		ini_set('memory_limit', '2G');



		parent::initialize();
		$this->loadComponent('Customfunctions');
		$this->loadComponent('Mastertablecontent');
		$this->loadComponent('Progressbar');
		$this->loadComponent('Createcaptcha');
		$this->loadComponent('Reportstatistics');
		$this->loadComponent('Reportsfunctions');
		$this->viewBuilder()->setLayout('admin_dashboard');
		$this->viewBuilder()->setHelpers(['Form','Html','Csv']);

		//LOAD ALL MODELS
		$this->loadModel('DmiUsers');
		$this->loadModel('UserRole');
		$this->loadModel('DmiUserRolesManagmentLogs');
		$this->loadModel('DmiUserRoles');
		$this->loadModel('DmiMoAllocationLogs');
		$this->loadModel('DmiGrantCertificatesPdfs');
		$this->loadModel('DmiIoAllocationLogs');
		$this->loadModel('DmiRoAllocationLogs');
		$this->loadModel('DmiStates');
		$this->loadModel('DmiDistricts');
		$this->loadModel('DmiFirms');
		$this->loadModel('DmiRoOffices');
		$this->loadModel('DmiCustomers');
		$this->loadModel('DmiCertificateTypes');
		$this->loadModel('DmiApplicantPaymentDetails');
		$this->loadModel('DmiRenewalApplicantPaymentDetails');
		$this->loadModel('DmiSentEmailLogs');
		$this->loadModel('DmiApplicationEsignedStatuses');
		$this->loadModel('DmiVisitorCounts');
		$this->loadModel('DmiRenewalEsignedStatuses');
		$this->loadModel('DmiFrontStatistics');
		$this->loadModel('DmiAllTblsDetails');
		$this->loadModel('DmiCustomerLaboratoryDetails');
		// Added by Ankur Jangid
		$this->loadModel('DmiAllApplicationsCurrentPositions');
		$this->loadModel('DmiRenewalAllCurrentPositions');

		$this->Session = $this->getRequest()->getSession();
	}



	//Before Filter
	public function beforeFilter($event) {

		parent::beforeFilter($event);

		if ($this->Session->read('username') == null) {

			$this->customAlertPage("Sorry You are not authorized to view this page..");
		}

		$user_role = $this->DmiUserRoles->find('all')->select(['view_reports'])->where(['user_email_id IS' => $this->Session->read('username')])->first();

		if ($this->Session->read('username') == null && $user_role['view_reports'] == 'yes') {

			$this->customAlertPage("Sorry You are not authorized to view this page..");
		}

		/* set last action url for back button on reports, Done By Pravin Bhakare 06-03-2018  */
		$currentAction = $this->request->getParam('action');

		if ($this->Session->read('backAction') == null && $currentAction == "reportTypes") {

			$this->Session->write('backAction',$currentAction);
			$this->Session->write('back',$currentAction );//added by laxmi for back button on 9-03-23

		} elseif ($this->Session->read('backAction') != null && $currentAction == "aqcmsStatistics") {

			$this->Session->write('backAction',$currentAction);

		} elseif ($this->Session->read('backAction') != null && $currentAction == "reportTypes") {

			$this->Session->write('backAction',$currentAction);
		//added elseif by laxmi on 09-03-2023
		}elseif($this->Session->read('backAction') == 'aqcmsStatistics' && $currentAction != 'aqcmsStatistics'){

			$this->Session->write('backAction',$this->Session->read('backAction'));
		}

		//added if else by laxmi for back button on 9-03-23
		if($currentAction == 'aqcmsStatistics'){
			$this->set('backAction',$this->Session->read('back'));
		}else{
			$this->set('backAction',$this->Session->read('backAction'));
		}
	}




	// Report Types
	// Description : Manin Window of Reports Where Shows Reports Types Table
	// @Author : Pravin Bhakare
	// #Contributer : Ankur (M)
	// Date : ------

	public function reportTypes() {

		$this->Session->delete('search_office');
		$this->Session->delete('search_user_role');
		$this->Session->delete('search_user_id');
		$this->Session->delete('search_from_date');
		$this->Session->delete('search_to_date');
		$this->Session->delete('search_application_type_id');
		$this->Session->delete('application_approved_office');
		$this->Session->delete('ro_office_id');
		$this->Session->delete('mo_office_id');
		$this->Session->delete('io_office_id');
		$this->Session->delete('search_user_email_id');
		$this->Session->delete('pending_days');
		$this->Session->delete('state');
		$this->Session->delete('district');
		$this->Session->delete('application_type');
		$this->Session->delete('renewal_year');
		$this->Session->delete('application_id');
		$this->Session->delete('company_id');
		$this->Session->delete('ro_id');
	}





	// AQCMS Statistics
	// Description : Manin Window of Reports Where Shows Reports Types Table
	// @Author : Amol Choudhari / Pravin Bhakare
	// #Contributer : Ankur (M)
	// Date : -----

	public function aqcmsStatistics() {


		$report_name = 'AQCMS Statistics';
		$this->set('report_name',$report_name);

		$user_role = $this->DmiUserRoles->find('all')->where(['user_email_id IS' => $this->Session->read('username'), 'ro_inspection'=>'yes', 'view_reports IS NOT'=>'yes'])->first();

		$ro_office_list = $this->DmiRoOffices->find('all')->select(['id', 'ro_office'])->where(['delete_status IS NULL'])->combine('id', 'ro_office')->toArray();

		$statistics_counts = $this->DmiFrontStatistics->find('all')->where(['id IS' => 1])->toArray();

		$this->set('statistics_counts',$statistics_counts);

		$username = $this->Session->read('username');

		if (!empty($user_role)) {


			$rolist = $this->DmiUsers->find('all')->select(['id', 'f_name', 'l_name', 'email', 'created', 'role', 'posted_ro_office', 'status'])
													->join(['DmiUserRoles' => ['table' => 'dmi_user_roles', 'type' => 'INNER',
															'conditions' => ['DmiUserRoles.user_email_id = DmiUsers.email',
															'DmiUserRoles.ro_inspection' => 'yes', 'DmiUserRoles.user_email_id' => $username]],
															'DmiRoOffices' => ['table' => 'dmi_ro_offices', 'type' => 'INNER',
															'conditions' => ['DmiRoOffices.ro_email_id = DmiUsers.email', 'DmiUsers.status' => 'active']]])->toArray();
		} else {


			$rolist = $this->DmiUsers->find('all')->select(['id', 'f_name', 'l_name', 'email', 'created', 'role', 'posted_ro_office', 'status'])
													->join(['DmiUserRoles' => ['table' => 'dmi_user_roles', 'type' => 'INNER',
															'conditions' => ['DmiUserRoles.user_email_id = DmiUsers.email', 'DmiUserRoles.ro_inspection' => 'yes']],
															'DmiRoOffices' => ['table' => 'dmi_ro_offices', 'type' => 'INNER',
															'conditions' => ['DmiRoOffices.ro_email_id = DmiUsers.email', 'DmiUsers.status' => 'active']]])->toArray();
		}

		$current_ro_id = '';

		foreach ($rolist as $each_user) {

			if (!empty($user_role)) {

				$current_ro_id = $each_user['id'];
			}

			$ro_name_list[$each_user['id']] = $each_user['f_name']." ".$each_user['l_name']."  (".base64_decode($each_user['email']).") - (".$ro_office_list[$each_user['posted_ro_office']].")"; //for email encoding
		}

		asort($ro_name_list);
		$this->set('ro_name_list',$ro_name_list);


		$aqcms_statistics_report = null;

		 //delete previus session by laxmi b on 21-02-2023
		if(!empty($this->Session->read('search_from_date')) && !empty($this->Session->read('search_to_date')) || !empty($this->Session->read('search_user_role')) || !empty($this->Session->read('application_approved_office'))) {
			$this->Session->delete('search_from_date');
			$this->Session->delete('search_to_date');
			$this->Session->delete('search_user_role');
			$this->Session->delete('application_approved_office');
		}

		if (null !== ($this->request->getData('search'))) {

			$aqcms_statistics_report = 1;

			$this->Session->delete('ro_id');
			$ro_id = $this->request->getData('ro_id');
			$from_date = $this->request->getData('from_date');
			$to_date = $this->request->getData('to_date');
			$this->Session->write('ro_id',$ro_id);
			//delete session variable by laxmi on 15-02-2023
			$this->Session->delete('roOfficeShortCode');
			$this->Session->delete('ro_office_id');
			$this->Session->delete('from_date');
			$this->Session->delete('to_date');
			$this->Session->delete('roId');

			if (!empty($ro_id)) {


				$rolist = $this->DmiRoOffices->find('all')->select(['id'])->join(['table' => 'dmi_users', 'alias' => 'users', 'type' => 'INNER','conditions' => ['users.email = dmirooffices.ro_email_id', 'users.id' => $ro_id]])->combine('id', 'id')->toArray();

				$district_list = $this->DmiDistricts->find('all')->select(['id'])->where(['ro_id IN' => $rolist])->combine('id', 'id')->toArray();

				$roOfficeShortCode = $this->DmiRoOffices->find('all')->select(['id', 'short_code'])->where(['id IN' => $rolist])->combine('id', 'short_code')->toArray();
				//set ro_office short code ro_office id from_date and to_date in session by laxmi B. on 15-02-2023

				$this->Session->write('roOfficeShortCode',$roOfficeShortCode);
				$this->Session->write('ro_office_id',$rolist);

				$this->Session->write('roId', $ro_id);

				foreach ($roOfficeShortCode as $eachCode) {

					$OfficeShortCode[] = ['customer_id LIKE' => '%'.$eachCode.'%'];
				}

				//code added for disrtict problem for brijesh tiwari added by laxmi B on 17-02-2023
				if(empty($district_list)){
					$from_date= '';
					$to_date = '';
					$ro_id = '';
				}
			}
			//set ro_office short code ro_office id from_date and to_date in session by laxmi B. on 15-02-2023
			$this->Session->write('from_date',$from_date);
			$this->Session->write('to_date',$to_date);


			//Check Between Dates
			if (!empty($from_date) && !empty($to_date) && !empty($ro_id)) {

				$searchConditions = ['district IN' => $district_list, 'date(created) BETWEEN :start AND :end'];

			} elseif (empty($from_date) && empty($to_date) && !empty($ro_id)) {

				$searchConditions = ['district IN' => $district_list];

			} elseif (!empty($from_date) && !empty($to_date) && empty($ro_id)) {

				$searchConditions = ['date(created) BETWEEN :start AND :end'];

			} elseif (empty($from_date) && empty($to_date) && empty($ro_id)) {

				$searchConditions = array();
			}


			//Check between dates
			if (!empty($from_date) && !empty($to_date)) {

				$total_primary_user = $this->DmiCustomers->find('list')->where($searchConditions)->bind(':start', $from_date, 'date')->bind(':end', $to_date, 'date')->toArray();
				$query_tfr = $this->DmiFirms->find('all');

				$total_firm_register = $query_tfr->select(['certification_type', 'count' => $query_tfr->func()->count('certification_type')])
												->where($searchConditions)
												->bind(':start', $from_date, 'date')->bind(':end', $to_date, 'date')
												->group(['certification_type'])
												->order(['certification_type' => 'ASC'])
												->toArray();

				$total_delete_firms = $this->DmiFirms->find('all')->where($searchConditions)->where(['delete_status IS NOT' => NULL])
																	->bind(':start', $from_date, 'date')->bind(':end', $to_date, 'date')
																	->toArray();

				$list4RenewalDueCheck = $this->DmiFirms->find('all')->where($searchConditions)
														->bind(':start', $from_date, 'date')->bind(':end', $to_date, 'date')
														->combine('id', 'customer_id')
														->toArray();
			} else {

				$total_primary_user = $this->DmiCustomers->find('list')->where($searchConditions)->toArray();
				$query_tfr = $this->DmiFirms->find('all');

				$total_firm_register = $query_tfr->select(['certification_type', 'count' => $query_tfr->func()->count('certification_type')])
													->where($searchConditions)
													->group(['certification_type'])
													->order(['certification_type' => 'ASC'])
													->toArray();

				$total_delete_firms = $this->DmiFirms->find('all')->where($searchConditions)->where(['delete_status IS NOT' => NULL])->toArray();

				$list4RenewalDueCheck = $this->DmiFirms->find('all')->where($searchConditions)->combine('id', 'customer_id')->toArray();
			}

			$caRenewalDue = 0; 	$printingRenewalDue = 0; $labRenewalDue = 0;

			foreach ($list4RenewalDueCheck as $each_application) {

				$renewalDue = $this->Customfunctions->checkApplicantValidForRenewal($each_application);

				if ($renewalDue == 'yes') {
					$split_customer_id = explode('/',$each_application);

					if ($split_customer_id[1] == 1) {

						$caRenewalDue = $caRenewalDue+1;

					} elseif ($split_customer_id[1] == 2) {

						$printingRenewalDue = $printingRenewalDue+1;

					} elseif ($split_customer_id[1] == 3) {

						$labRenewalDue = $labRenewalDue+1;
					}
				}
			}


			//$applications_current_positions_tables =  ['DmiFinalSubmits' => 'DmiAllApplicationsCurrentPositions', 'DmiRenewalFinalSubmits' => 'DmiRenewalAllCurrentPositions'];
			//comment above & fetch all array of table from flowise table added by laxmi B. on 09-02-2023
			$applTypeArray = $this->Session->read('applTypeArray');
			$this->loadModel('DmiFlowWiseTablesLists');
			$applications_current_positions_tables = $this->DmiFlowWiseTablesLists->find('all')->select(['application_form','appl_current_pos'])->where(array('application_type IN'=>$applTypeArray))->order(['id'])->combine('application_form','appl_current_pos')->toArray();


			$pendingCountForMo = 0;
			$pendingCountForIo = 0;
			$pendingCountForHo = 0;
			$inprogress_app_with_ro = array();
			//this condtion inside changed by laxmi on 14-02-2023
			if (!empty($from_date) && !empty($to_date) && !empty($ro_id)) {

				$searchPendingConditions = ['AND' => [$OfficeShortCode, 'date(modified) BETWEEN :start AND :end']];// added array for Key AND by laxmi on 14-02-2023
				$from_date =$from_date;
				$to_date = $to_date;
			} elseif (empty($from_date) && empty($to_date) && !empty($ro_id)) {

				$searchPendingConditions = ['OR'=>$OfficeShortCode];
				$from_date ='';
				$to_date = '';
			} elseif (!empty($from_date) && !empty($to_date) && empty($ro_id)) {

				$searchPendingConditions = ['date(modified) BETWEEN :start AND :end'];
				 // added   from_date and to_date by laxmi on 14-02-2023
				$from_date =$from_date;
				$to_date = $to_date;

			} elseif (empty($from_date) && empty($to_date) && empty($ro_id)) {

				$searchPendingConditions = array();
				$from_date ='';// added   from_date and to_date by laxmi on 14-02-2023
				$to_date = '';
			}


			$application_processed_type = ['new_app_processed','renewal_app_processed','backlog_app_processed'];


			foreach ($application_processed_type as $each) {

				$application_processed[] = $this->Reportstatistics->$each($searchPendingConditions, $from_date, $to_date);//from_date to_date added by laxmi on 13-20-2023

			}


			if (!empty($from_date) && !empty($to_date)) {

				foreach ($applications_current_positions_tables as $each_table) {

					$key = array_search ($each_table, $applications_current_positions_tables);
					//load list of table added by laxmi on 09-02-2023
					$this->loadModel($each_table);
					$this->loadModel($key);

					//below query commented by shreeya bcoz of added new query Date [ 01-06-23]
					//For Progress with MO
					// $inprogress_with_mo = $this->$each_table->find('all')->select(['id', 'customer_id'])
					// 										->where($searchPendingConditions)->where(['current_level' => 'level_1'])
					// 										->bind(':start', $from_date, 'date')->bind(':end', $to_date, 'date')
					// 										->combine('id', 'customer_id')->toArray();
					// $pendingCountForMo = $pendingCountForMo + count($inprogress_with_mo);

					//added new query if customer_is is null could not show null entry in cout
					//by shreeya on date [ 01-06-2023]
					//For Progress with MO
					$inprogress_with_mo = $this->$each_table->find('all')->select(['id', 'customer_id'])
												->where($searchPendingConditions)->where(['current_level' => 'level_1'])->
												bind(':start', $from_date, 'date')->bind(':end', $to_date, 'date')
												->where(function ($exp, $q) {return $exp->notEq('customer_id', '');
												})->combine('id', 'customer_id')->toArray();
					$pendingCountForMo = $pendingCountForMo + count($inprogress_with_mo);

					//For Progress with IO
					$inprogress_with_io = $this->$each_table->find('all')->select(['id', 'customer_id'])
															->where($searchPendingConditions)->where(['current_level' => 'level_2'])
															->bind(':start', $from_date, 'date')->bind(':end', $to_date, 'date')
															->combine('id', 'customer_id')->toArray();
					$pendingCountForIo = $pendingCountForIo + count($inprogress_with_io);


					//For Progress with HO
					$inprogress_with_ho = $this->$each_table->find('all')->select(['id', 'customer_id'])
															->where($searchPendingConditions)->where(['current_level' => 'level_4'])
															->bind(':start', $from_date, 'date')->bind(':end', $to_date, 'date')
															->combine('id', 'customer_id')->toArray();
					$pendingCountForHo = $pendingCountForHo + count($inprogress_with_ho);


					//For Progress with RO
					$inprogress_with_ro = $this->$each_table->find('all')->select(['id', 'customer_id'])
															->where($searchPendingConditions)->where(['current_level' => 'level_3'])
															->bind(':start', $from_date, 'date')->bind(':end', $to_date, 'date')
															->combine('id', 'customer_id')->toArray();

															foreach($inprogress_with_ro as $each_record ){

																$result_status = $this->$key->find('all')->where(['customer_id' => $each_record, 'status' => 'approved', 'current_level' => 'level_3'])->toArray();

																//below condition commented by Shreeya
																/*if(empty($result_status)){
																	$inprogress_app_with_ro[] = $each_record;
																}*/

																if(empty($result_status)){
																	//$each_record is not already in the array, it will be added to the $inprogress_app_with_ro array using the [] notation.By Shreeya on Date [02-06-2023]
																	if(!in_array($each_record,$inprogress_app_with_ro)){
																		$inprogress_app_with_ro[] = $each_record;
																	}
																}
															}

															$inprogress_app_with_ro = array_unique($inprogress_app_with_ro);
				}


				$applicationEsigned = $this->DmiApplicationEsignedStatuses->find('all')->where($searchPendingConditions)->where(['application_esigned' => 'yes'])
					->bind(':start', $from_date, 'date')->bind(':end', $to_date, 'date')->toArray();

				$inspectionReportEsigned = $this->DmiApplicationEsignedStatuses->find('all')->where($searchPendingConditions)->where(['report_esigned' => 'yes'])
					->bind(':start', $from_date, 'date')->bind(':end', $to_date, 'date')->toArray();

				$certificateEsigned = $this->DmiApplicationEsignedStatuses->find('all')->where($searchPendingConditions)->where(['certificate_esigned' => 'yes'])
					->bind(':start', $from_date, 'date')->bind(':end', $to_date, 'date')->toArray();

				$renewalApplicationEsigned = $this->DmiRenewalEsignedStatuses->find('all')->where($searchPendingConditions)->where(['application_esigned' => 'yes'])
					->bind(':start', $from_date, 'date')->bind(':end', $to_date, 'date')->toArray();

				$renewalInspectionReportEsigned = $this->DmiRenewalEsignedStatuses->find('all')->where($searchPendingConditions)->where(['report_esigned' => 'yes'])
					->bind(':start', $from_date, 'date')->bind(':end', $to_date, 'date')->toArray();

				$renewalCertificateEsigned = $this->DmiRenewalEsignedStatuses->find('all')->where($searchPendingConditions)->where(['certificate_esigned' => 'yes'])
					->bind(':start', $from_date, 'date')->bind(':end', $to_date, 'date')->toArray();

				$newApplicationrevenue_Query = $this->DmiApplicantPaymentDetails->find('all')->where($searchPendingConditions)
					->where(['payment_confirmation' => 'confirmed'])->bind(':start', $from_date, 'date')->bind(':end', $to_date, 'date')->sumOf('amount_paid');
				$newApplicationrevenue = ['sum' => $newApplicationrevenue_Query];

				$renewalApplicationrevenue_Query =$this->DmiRenewalApplicantPaymentDetails->find('all')->where($searchPendingConditions)
					->where(['payment_confirmation' => 'confirmed'])->bind(':start', $from_date, 'date')->bind(':end', $to_date, 'date')->sumOf('amount_paid');
					$renewalApplicationrevenue = ['sum' => $renewalApplicationrevenue_Query];

			} else {

				foreach ($applications_current_positions_tables as $each_table) {

					$key = array_search ($each_table, $applications_current_positions_tables);

					//load all flowlist table by laxmi on 10-09-2023
					$this->loadModel($each_table);
					$this->loadModel($key);

					$inprogress_with_mo = $this->$each_table->find('all')->select(['id', 'customer_id'])->where($searchPendingConditions)->where(['current_level' => 'level_1'])
						->combine('id', 'customer_id')->toArray();
					$pendingCountForMo = $pendingCountForMo + count($inprogress_with_mo);

					$inprogress_with_io = $this->$each_table->find('all')->select(['id', 'customer_id'])->where($searchPendingConditions)->where(['current_level' => 'level_2'])
						->combine('id', 'customer_id')->toArray();
					$pendingCountForIo = $pendingCountForIo + count($inprogress_with_io);

					$inprogress_with_ho = $this->$each_table->find('all')->select(['id', 'customer_id'])->where($searchPendingConditions)->where(['current_level' => 'level_4'])
						->combine('id', 'customer_id')->toArray();
					$pendingCountForHo = $pendingCountForHo + count($inprogress_with_ho);

					$inprogress_with_ro = $this->$each_table->find('all')->select(['id', 'customer_id'])->where($searchPendingConditions)->where(['current_level' => 'level_3'])
						->combine('id', 'customer_id')->toArray();

					foreach ($inprogress_with_ro as $each_record ) {
						$result_status = $this->$key->find('all')->where(['customer_id' => $each_record, 'status' => 'approved', 'current_level' => 'level_3'])->toArray();

						if (empty($result_status)) {
							$inprogress_app_with_ro[] = $each_record;
						}
					}
				}

				$applicationEsigned = $this->DmiApplicationEsignedStatuses->find('all')->where($searchPendingConditions)->where(['application_esigned' => 'yes'])->toArray();

				$inspectionReportEsigned = $this->DmiApplicationEsignedStatuses->find('all')->where($searchPendingConditions)->where(['report_esigned' => 'yes'])->toArray();

				$certificateEsigned = $this->DmiApplicationEsignedStatuses->find('all')->where($searchPendingConditions)->where(['certificate_esigned' => 'yes'])->toArray();

				$renewalApplicationEsigned = $this->DmiRenewalEsignedStatuses->find('all')->where($searchPendingConditions)->where(['application_esigned' => 'yes'])->toArray();

				$renewalInspectionReportEsigned = $this->DmiRenewalEsignedStatuses->find('all')->where($searchPendingConditions)->where(['report_esigned' => 'yes'])->toArray();

				// below query is commented by shreeya adde new query on date [05-06-2023]
				//$renewalCertificateEsigned = $this->DmiRenewalEsignedStatuses->find('all')->where($searchPendingConditions)->where(['certificate_esigned' => 'yes'])->toArray();
				// adde for if customer id is null could not show null records count
				// added by shreeya on date [05-06-2023]
				$renewalCertificateEsigned = $this->DmiRenewalEsignedStatuses->find('all')->where($searchPendingConditions)->where(['certificate_esigned' => 'yes'])
				->where(function ($exp, $q) {return $exp->notEq('customer_id', '');})->toArray();

				$newApplicationrevenue_Query = $this->DmiApplicantPaymentDetails->find('all')->where($searchPendingConditions)
					->where(['payment_confirmation' => 'confirmed'])->sumOf('amount_paid');
				$newApplicationrevenue = ['sum' => $newApplicationrevenue_Query];

				$renewalApplicationrevenue_Query =$this->DmiRenewalApplicantPaymentDetails->find('all')->where($searchPendingConditions)
					->where(['payment_confirmation' => 'confirmed'])->sumOf('amount_paid');
				$renewalApplicationrevenue = ['sum' => $renewalApplicationrevenue_Query];

				//sum of revenue change application by shreeya on date [22-06-2023]
				$changeApplicationrevenue_Query =$this->DmiChangePaymentDetails->find('all')->where($searchPendingConditions)
						->where(['payment_confirmation' => 'confirmed'])->sumOf('amount_paid');
				$changeApplicationrevenue = ['sum' => $changeApplicationrevenue_Query];

				//sum of chemist application by shreeya on date [22-06-2023]
				$chemistApplicationrevenue_Query =$this->DmiChemistPaymentDetails->find('all')->where($searchPendingConditions)
						->where(['payment_confirmation' => 'confirmed'])->sumOf('amount_paid');
				$chemistApplicationrevenue = ['sum' => $chemistApplicationrevenue_Query];

				//sum for adv renewal application by shreeya on date [22-06-2023]
				$advApplicationrevenue_Query =$this->DmiAdvPaymentDetails->find('all')->where($searchPendingConditions)
						->where(['payment_confirmation' => 'confirmed'])->sumOf('amount_paid');
				$advApplicationrevenue = ['sum' => $advApplicationrevenue_Query];


			}



			$totalVisitor =$this->DmiVisitorCounts->find('all')->select(['visitor'])->order(['id' => 'DESC'])->first();

			$FrontStatisticsController = new FrontstatisticsController();
			$newrevenue = $FrontStatisticsController->thousandsCurrencyFormat($newApplicationrevenue['sum']);
			$renewalrevenue = $FrontStatisticsController->thousandsCurrencyFormat($renewalApplicationrevenue['sum']);
			//added by shreeya for chemist,change And adv applitionson date [22-06-2023]
			$changerevenue = $FrontStatisticsController->thousandsCurrencyFormat($changeApplicationrevenue['sum']);
			$chemistrevenue = $FrontStatisticsController->thousandsCurrencyFormat($chemistApplicationrevenue['sum']);
			$advrevenue = $FrontStatisticsController->thousandsCurrencyFormat($advApplicationrevenue['sum']);

			//added sum of chemist, change and adv by shreeya on date [22-06-2023]
			$totalrevenue = $FrontStatisticsController->thousandsCurrencyFormat($newApplicationrevenue['sum']+$renewalApplicationrevenue['sum']+
							$changeApplicationrevenue['sum']+$chemistApplicationrevenue['sum']+$advApplicationrevenue['sum']);



			$this->Session->write('total_primary_user',$total_primary_user);
			$this->Session->write('total_firm_register',$total_firm_register);
			$this->Session->write('total_delete_firms',$total_delete_firms);
			$this->Session->write('application_processed',$application_processed);
			$this->Session->write('pendingCountForMo',$pendingCountForMo);
			$this->Session->write('pendingCountForIo',$pendingCountForIo);
			$this->Session->write('pendingCountForHo',$pendingCountForHo);
			$this->Session->write('pendingCountForRo',count($inprogress_app_with_ro));
			$this->Session->write('applicationEsigned',count($applicationEsigned));
			$this->Session->write('inspectionReportEsigned',count($inspectionReportEsigned));
			$this->Session->write('certificateEsigned',count($certificateEsigned));
			$this->Session->write('renewalApplicationEsigned',count($renewalApplicationEsigned));
			$this->Session->write('renewalInspectionReportEsigned',count($renewalInspectionReportEsigned));
			$this->Session->write('renewalCertificateEsigned',count($renewalCertificateEsigned));
			$this->Session->write('newApplicationrevenue',$newrevenue);
			$this->Session->write('renewalApplicationrevenue',$renewalrevenue);
			//added by shreeya on date [22-06-2023]
			$this->Session->write('changeApplicationrevenue',$changerevenue);
			$this->Session->write('chemistApplicationrevenue',$chemistrevenue);
			$this->Session->write('advApplicationrevenue',$advrevenue);

			$this->Session->write('totalrevenue',$totalrevenue);
			$this->Session->write('totalVisitor',$totalVisitor['visitor']);
			$this->Session->write('caRenewalDue',$caRenewalDue);
			$this->Session->write('printingRenewalDue',$printingRenewalDue);
			$this->Session->write('labRenewalDue',$labRenewalDue);

			$this->set('total_primary_user',$total_primary_user);
			$this->set('total_firm_register',$total_firm_register);
			$this->set('total_delete_firms',$total_delete_firms);
			$this->set('application_processed',$application_processed);
			$this->set('pendingCountForMo',$pendingCountForMo);
			$this->set('pendingCountForIo',$pendingCountForIo);
			$this->set('pendingCountForHo',$pendingCountForHo);
			$this->set('pendingCountForRo',count($inprogress_app_with_ro));
			$this->set('applicationEsigned',count($applicationEsigned));
			$this->set('inspectionReportEsigned',count($inspectionReportEsigned));
			$this->set('certificateEsigned',count($certificateEsigned));
			$this->set('renewalApplicationEsigned',count($renewalApplicationEsigned));
			$this->set('renewalInspectionReportEsigned',count($renewalInspectionReportEsigned));
			$this->set('renewalCertificateEsigned',count($renewalCertificateEsigned));
			$this->set('newApplicationrevenue',$newrevenue);
			$this->set('renewalApplicationrevenue',$renewalrevenue);
			//added by shreeya on date [22-06-2023]
			$this->set('changeApplicationrevenue',$changerevenue);
			$this->set('chemistApplicationrevenue',$chemistrevenue);
			$this->set('advApplicationrevenue',$advrevenue);

			$this->set('totalrevenue',$totalrevenue);
			$this->set('totalVisitor',$totalVisitor['visitor']);
			$this->set('caRenewalDue',$caRenewalDue);
			$this->set('printingRenewalDue',$printingRenewalDue);
			$this->set('labRenewalDue',$labRenewalDue);

		} else {


			if (!empty($user_role)) {
				$ro_id = $current_ro_id;
				$this->Session->write('ro_id', $ro_id);
			} else {
				$ro_id = null;
			}

			$from_date = null;
			$to_date = null;
		}

		$this->set('ro_id',$ro_id);
		$this->set('aqcms_statistics_report',$aqcms_statistics_report);
		$this->set('from_date',$from_date);
		$this->set('to_date',$to_date);

		if (null !== $this->request->getData('download_report')) {

			$this->viewBuilder()->setLayout('pdf_layout');

			$this->set('total_primary_user',$this->Session->read('total_primary_user'));
			$this->set('total_firm_register',$this->Session->read('total_firm_register'));
			$this->set('total_delete_firms',$this->Session->read('total_delete_firms'));
			$this->set('application_processed',$this->Session->read('application_processed'));
			$this->set('pendingCountForMo',$this->Session->read('pendingCountForMo'));
			$this->set('pendingCountForIo',$this->Session->read('pendingCountForIo'));
			$this->set('pendingCountForHo',$this->Session->read('pendingCountForHo'));
			$this->set('pendingCountForRo',$this->Session->read('pendingCountForRo'));
			$this->set('applicationEsigned',$this->Session->read('applicationEsigned'));
			$this->set('inspectionReportEsigned',$this->Session->read('inspectionReportEsigned'));
			$this->set('certificateEsigned',$this->Session->read('certificateEsigned'));
			$this->set('renewalApplicationEsigned',$this->Session->read('renewalApplicationEsigned'));
			$this->set('renewalInspectionReportEsigned',$this->Session->read('renewalInspectionReportEsigned'));
			$this->set('renewalCertificateEsigned',$this->Session->read('renewalCertificateEsigned'));
			$this->set('newApplicationrevenue',$this->Session->read('newApplicationrevenue'));
			$this->set('renewalApplicationrevenue',$this->Session->read('renewalApplicationrevenue'));

			//added by shreeya on date [22-06-2023]
			$this->set('changeApplicationrevenue',$this->Session->read('changeApplicationrevenue'));
			$this->set('chemistApplicationrevenue',$this->Session->read('chemistApplicationrevenue'));
			$this->set('advApplicationrevenue',$this->Session->read('advApplicationrevenue'));

			$this->set('totalrevenue',$this->Session->read('totalrevenue'));
			$this->set('totalVisitor',$this->Session->read('totalVisitor'));
			$this->set('caRenewalDue',$this->Session->read('caRenewalDue'));
			$this->set('printingRenewalDue',$this->Session->read('printingRenewalDue'));
			$this->set('labRenewalDue',$this->Session->read('labRenewalDue'));


			$this->autoRender = false;

			if (empty($aqcms_statistics_report)) {
				$pdfHtml = $this->render('/element/download_report_excel_format/download_report_aqcms_statistics_without_search');
			} else {
				$pdfHtml = $this->render('/element/download_report_excel_format/download_report_aqcms_statistics_with_search');
			}

			$pdf = new ApplicationformspdfsController();
			$pdf->callTcpdf($pdfHtml, 'D', '', '');
		}

		if (!empty($user_role)) {
			$this->view = 'ro_aqcms_statistics';
		} else {
			$this->view = 'aqcms_statistics';
		}

	}




	// User Roles Logs Report
	// Description : Start User Roles Logs History Reports Section
	// @Author : Pravin Bhakare
	// #Contributer : Ankur (M)
	// Date : ------

	public function userRolesLogsReport() {

		$report_name = 'User Roles Logs History Report';
		$this->set('report_name',$report_name);

		$user_name_details = $this->userNameList();
		$this->set('user_name_details',$user_name_details);

		// Change on 1/11/2018 : Add new user roles such as MO/SMO(SO), Inspection Officer(SO), MO/SMO(SMD), Inspection Officer(SMD) in user_roles array - By Pravin Bhakare
		$user_roles = ['add_user'=>'Add User','page_draft'=>'Page (Draft only)','page_publish'=>'Page Publish','menus'=>'Menus','file_upload'=>'Upload Files',
								'mo_smo_inspection'=>'MO/SMO','io_inspection'=>'Inspection Officer','allocation_mo_smo'=>'Allocate to MO/SMO',
								'allocation_io'=>'Allocate to IO','reallocation'=>'Re-Allocate','form_verification_home'=>'Form Scrutiny Home',
								'allocation_home'=>'Allocation Home','ro_inspection'=>'RO/SO In-Charge','set_roles'=>'Set User Roles','allocation_dy_ama'=>'Forward to Dy. AMA',
								'allocation_ho_mo_smo'=>'Allocate to HO MO/SMO','allocation_jt_ama'=>'Forward to Jt. AMA','allocation_ama'=>'Forward to AMA',
								'dy_ama'=>'Dy. AMA','ho_mo_smo'=>'HO MO/SMO','jt_ama'=>'Jt. AMA','ama'=>'AMA','masters'=>'Masters','super_admin'=>'Super Admin',
								'renewal_verification'=>'Renewal Scrutiny','renewal_allocation'=>'Renewal Allocation','view_reports'=>'View Reports','pao'=>'PAO/DDO',
								'once_update_permission'=>'Aadhar Update Permission','old_appln_data_entry'=>'Old Applications Data Entry','so_inspection'=>'SO In-Charge',
								'smd_inspection'=>'SMD In-Charge','feedbacks'=>'Feedbacks','unlock_user'=>'Unlock User'];

		asort($user_roles); // Change on 9/11/2018, Sorting array by ascending order - By Pravin Bhakare
		$this->set('user_roles',$user_roles);

		//added 'office_type'=>'RO' condition on 27-07-2018
		$ro_office = $this->DmiRoOffices->find('all')->select(['id', 'ro_office'])->where(['office_type' => 'RO','delete_status IS NULL'])->order(['ro_office ASC'])->combine('id', 'ro_office')->toArray();
		$this->set('ro_office',$ro_office);

		$search_office = $this->Session->read('search_office');
		$search_user_role = $this->Session->read('search_user_role');
		$search_user_id = $this->Session->read('search_user_id');
		$search_from_date = $this->Session->read('search_from_date');
		$search_to_date = $this->Session->read('search_to_date');
		$this->set('search_office',$search_office);
		$this->set('search_user_role',$search_user_role);
		$this->set('search_user_id',$search_user_id);
		$this->set('search_from_date',$search_from_date);
		$this->set('search_to_date',$search_to_date);

		$download_report = 'no'; // Set default value for "Download Report as Excel" click event (Done by Pravin 13/3/2018)

		//Pass the entry for "Search" or "Download Report as Excel" button click event (Done by Pravin 13/3/2018)
		if (null !== ($this->request->getData('search_logs')) || null !== ($this->request->getData('download_report'))) {

			$table = 'DmiRooffices';
			$post_input_request = $this->request->getData('office');

			if (!empty($post_input_request)) {
				$search_office = $this->Customfunctions->dropdownSelectInputCheck($table,$post_input_request); //calling library function
			} else {
				$search_office = '';
			}

			$search_user_role = $this->request->getData('user_roles');
			$search_user_id = $this->request->getData('user_id');
			$search_from_date = $this->request->getData('from_date');
			$search_from_date = $this->Customfunctions->dateFormatCheck($search_from_date);
			$search_to_date = $this->request->getData('to_date');
			$search_to_date = $this->Customfunctions->dateFormatCheck($search_to_date);
			$this->date_comparison($search_from_date,$search_to_date);

			// Change on 1/11/2018 : For download excel report, Take search filter field value from session variables instend of POST variable - By Pravin Bhakare
			if (!empty($this->request->getData('download_report'))) {

				$table = 'DmiRoOffices';
				$post_input_request = $this->Session->read('search_office');
				if (!empty($post_input_request)) {
					$search_office = $this->Customfunctions->dropdownSelectInputCheck($table,$post_input_request); // calling library function
				} else {
					$search_office = '';
				}

				$search_user_role = $this->Session->read('search_user_role');
				$search_user_id = $this->Session->read('search_user_id');
				$search_from_date = $this->Session->read('search_from_date');
				$search_from_date = $this->Customfunctions->dateFormatCheck($search_from_date);
				$search_to_date = $this->Session->read('search_to_date');
				$search_to_date = $this->Customfunctions->dateFormatCheck($search_to_date);
				$this->date_comparison($search_from_date,$search_to_date);
			}

			$this->Session->delete('search_office');
			$this->Session->delete('search_user_role');
			$this->Session->delete('search_user_id');
			$this->Session->delete('search_from_date');
			$this->Session->delete('search_to_date');
			$this->Session->delete('user_roles_email');

			//store the value of fields in session variable and used this value in else condition of search_logs button
			$this->Session->write('search_office',$search_office);
			$this->Session->write('search_user_role',$search_user_role);
			$this->Session->write('search_user_id',$search_user_id);
			$this->Session->write('search_from_date',$search_from_date);
			$this->Session->write('search_to_date',$search_to_date);

			$this->set('search_office',$search_office);
			$this->set('search_user_role',$search_user_role);
			$this->set('search_user_id',$search_user_id);
			$this->set('search_from_date',$search_from_date);
			$this->set('search_to_date',$search_to_date);

			if ($search_office == '' || $search_user_role =='' || $search_user_id =='' || $search_from_date =='' || $search_to_date =='') {

				$user_role_logs_details = null;
				if (!empty($search_user_id)) {
					$search_user_id = $this->search_user_id($user_name_details, $search_user_id);
				}

				if (!empty($search_office)) {

					$user_role_details = $this->DmiUsers->find('all')->where(['posted_ro_office' => $search_office])->toArray();
					$user_roles_email = $this->DmiUsers->find('all')->select(['email'])->where(['posted_ro_office' => $search_office])->extract('email')->toArray();
					// $i=0;
					// foreach ($user_role_details as $user_user) {
					// 	$user_roles_email[$i] = $user_user['email'];
					// 	$i = $i+1;
					// }

					if (!empty($user_roles_email)) {
						$this->Session->write('user_roles_email',$user_roles_email);
						$user_role_logs_history_details = $this->DmiUserRolesManagmentLogs->find('all')->where(['to_user IN' => $user_roles_email])->order(['id' => 'DESC'])->toArray();
					} else {
						$user_role_logs_history_details = null;
					}

				} elseif ($search_office == null && $search_user_role == null && $search_user_id != null && $search_from_date == null && $search_to_date == null) {

					$user_role_logs_history_details = $this->DmiUserRolesManagmentLogs->find('all')->where(['to_user' => $search_user_id])->order(['id' => 'DESC'])->toArray();

				} elseif ($search_office ==null && $search_user_role==null && $search_user_id==null && $search_from_date!=null && $search_to_date!=null) {

					$user_role_logs_history_details = $this->DmiUserRolesManagmentLogs->find('all')->where(['date(created) BETWEEN :start AND :end'])
						->bind(':start', $search_from_date, 'date')->bind(':end', $search_to_date, 'date')->order(['id' => 'DESC'])->toArray();
				} elseif ($search_office ==null && $search_user_role==null && $search_user_id!=null && $search_from_date!=null && $search_to_date!=null) {

					$user_role_logs_history_details = $this->DmiUserRolesManagmentLogs->find('all')->where(['to_user' => $search_user_id,'date(created) BETWEEN :start AND :end'])
						->bind(':start', $search_from_date, 'date')->bind(':end', $search_to_date, 'date')->order(['id' => 'DESC'])->toArray();
				} else {

					$user_role_logs_history_details = $this->DmiUserRolesManagmentLogs->find('all')->order(['id' => 'DESC'])->toArray();
				}

				$user_role_logs_details = $this->userLogsSearchCondition($user_role_logs_history_details,$search_office,$search_user_role,$search_user_id,$search_from_date,$search_to_date);
				$this->userLogsSearchResult($user_role_logs_details,$download_report);

			} else {

				$user_role_logs_details = null;

				if (!empty($search_user_id)) {
					$search_user_id = $this->search_user_id($user_name_details,$search_user_id);
				}

				$user_role_details = $this->DmiUsers->find('all')->where(['posted_ro_office'=>$search_office])->toArray();
				$user_roles_email = $this->DmiUsers->find('all')->select(['email'])->where(['posted_ro_office'=>$search_office])->extract('email')->toArray();
				// $i=0;
				// foreach ($user_role_details as $user_user) {
				// 	$user_roles_email[$i] = $user_user['email'];
				// 	$i = $i+1;
				// }

				$this->Session->write('user_roles_email',$user_roles_email);

				$user_role_logs_history_details = $this->DmiUserRolesManagmentLogs->find('all')->where(['to_user IN'=>$user_roles_email])->order(['id' => 'DESC'])->toArray();
				$user_role_logs_details = $this->userLogsSearchCondition($user_role_logs_history_details,$search_office,$search_user_role,$search_user_id,$search_from_date,$search_to_date);
				$this->userLogsSearchResult($user_role_logs_details,$download_report);
			}

			// Check not empty "Download Report as Excel" button Request, if condition TRUE then set value "yes" for "Download Report as Excel" click event (Done By pravin 14/3/2018)
			// and pass this value to "user_logs_search_result" function
			if (!empty($this->request->getData('download_report'))) {
				$download_report = 'yes';
				$this->userLogsSearchResult($user_role_logs_details,$download_report);
			}

		} else {

			$user_roles_email = $this->Session->read('user_roles_email');
			$user_role_logs_details = null;

			if (!empty($search_user_id)) {
				$search_user_id = $this->search_user_id($user_name_details,$search_user_id);
			}

			if (!empty($search_office)) {

				$user_role_logs_history_details = $this->DmiUserRolesManagmentLogs->find('all')->where(['to_user IN' => $user_roles_email])->order(['id' => 'DESC'])->toArray();

			} elseif ($search_office ==null && $search_user_role==null && $search_user_id!=null && $search_from_date==null && $search_to_date==null) {

				$user_role_logs_history_details = $this->DmiUserRolesManagmentLogs->find('all')->where(['to_user' => $search_user_id])->order(['id' => 'DESC'])->toArray();

			} elseif ($search_office ==null && $search_user_role==null && $search_user_id==null && $search_from_date!=null && $search_to_date!=null) {

				$user_role_logs_history_details = $this->DmiUserRolesManagmentLogs->find('all')->where(['date(created) BETWEEN :start AND :end'])
					->bind(':start', $search_from_date, 'date')->bind(':end', $search_to_date, 'date')->order(['id' => 'DESC'])->toArray();

			} elseif ($search_office ==null && $search_user_role==null && $search_user_id!=null && $search_from_date!=null && $search_to_date!=null) {

				$user_role_logs_history_details = $this->DmiUserRolesManagmentLogs->find('all')->where(['to_user' => $search_user_id])
					->where(['date(created) BETWEEN :start AND :end'])->bind(':start', $search_from_date, 'date')->bind(':end', $search_to_date, 'date')
					->order(['id' => 'DESC'])->toArray();

			} else {
				// limit set to 100 will show only latest 100 results
				//$user_role_logs_history_details = $this->DmiUserRolesManagmentLogs->find('all')->order(['id' => 'DESC'])->limit(['100'])->toArray();
				$user_role_logs_history_details = $this->DmiUserRolesManagmentLogs->find('all')->order(['id' => 'DESC'])->toArray();
			}

			$user_role_logs_details = $this->userLogsSearchCondition($user_role_logs_history_details,$search_office,$search_user_role,$search_user_id,$search_from_date,$search_to_date);
			$this->userLogsSearchResult($user_role_logs_details,$download_report);
		}

	}



	// User Logs Search Result
	// Description : Function For user roles logs history reports result
	// @Author : Pravin Bhakare
	// #Contributer : Ankur (M)
	// Date : ------

	public function userLogsSearchResult($user_role_logs_details,$download_report) {

		if (!empty($user_role_logs_details)) {

			$i=0;

			foreach ($user_role_logs_details as $user_name) {
				$user_role_logs_ids[$i] = $user_name['id'];
				$i = $i+1;
			}

			if (!empty($user_role_logs_ids)) {
				$download_condition = ['id IN' => $user_role_logs_ids];
			} else {
				$download_condition = ['id IS' => ''];
			}

			// Fetch the all data that required for creating the downloading report as execel (Done By pravin 13/3/2018)
			if ($download_report == 'yes') {
				$user_role_logs_history_details = $this->DmiUserRolesManagmentLogs->find('all')->where($download_condition)->order(['id' => 'DESC'])->toArray();
				$this->downloadReportUserRolesLogsHistory($user_role_logs_history_details);
				// $this->csv($user_role_logs_history_details); // excel added by ankur
			}

			$user_role_logs_history_details = $this->DmiUserRolesManagmentLogs->find('all')->where(['id IN' => $user_role_logs_ids])->order(['id' => 'DESC'])->toArray();

			$i=0;
			foreach ($user_role_logs_history_details as $user_name) {

				$user_email_id = $user_name['to_user'];
				$user_details = $this->DmiUsers->find('all')->select(['f_name', 'l_name', 'email', 'posted_ro_office'])->where(['email' => $user_email_id])->first();

				if (!empty($user_details)) {
					$user_office_details = $this->DmiRoOffices->find('all')->select(['ro_office'])->where(['id IS' => $user_details['posted_ro_office']])->first();
				}

				if (!empty($user_office_details)) {
					$user_office[$i] = $user_office_details['ro_office'];
				} else {
					$user_office[$i] = '---';
				}

				if (!empty($user_details)) {
					$user_full_name = $user_details['f_name'].' '.$user_details['l_name'];
					$user_name_detail[$i] = $user_full_name.' ('.base64_decode($user_details['email']).')';//for email encoding
				} else {
					$user_name_detail[$i] = '---';
				}

				$i = $i+1;
			}

			$user_roles_history_data = $this->DmiUserRolesManagmentLogs->showUserRolesHistory($user_role_logs_history_details);
			$user_roles_name_list = $user_roles_history_data[0][0];
			$user_roles_name_view_list = $user_roles_history_data[0][1]; // added by Ankur
			$remove_user_roles_name_list = $user_roles_history_data[1][0];
			$remove_user_roles_name_view_list = $user_roles_history_data[1][1]; // added by Ankur
			$add_user_roles_name_list = $user_roles_history_data[2][0];
			$add_user_roles_name_view_list = $user_roles_history_data[2][1]; // added by Ankur

			$this->set('user_roles_name_list',$user_roles_name_list);
			$this->set('user_roles_name_view_list',$user_roles_name_view_list); // added by Ankur
			$this->set('remove_user_roles_name_list',$remove_user_roles_name_list);
			$this->set('remove_user_roles_name_view_list',$remove_user_roles_name_view_list); // added by Ankur
			$this->set('add_user_roles_name_list',$add_user_roles_name_list);
			$this->set('add_user_roles_name_view_list',$add_user_roles_name_view_list); // added by Ankur
			$this->set('user_office',$user_office);
			$this->set('user_name_detail',$user_name_detail);

		} else {
			$user_role_logs_history_details = array();
		}

		$this->set('user_role_logs_history_details',$user_role_logs_history_details);
	}




	// Download Report User Roles Logs History
	// Description : This function create excel format file for downloading the report for user roles logs history
	// @Author : Pravin Bhakare
	// #Contributer : Ankur Jangid (Migration)
	// Date : 13/3/2018

	public function downloadReportUserRolesLogsHistory($user_role_logs_history_details) {

		$this->viewBuilder()->setLayout('downloadpdf');

		$i=0;
		foreach ($user_role_logs_history_details as $user_name) {

			$user_email_id = $user_name['to_user'];
			$user_details = $this->DmiUsers->find('all')->where(['email'=>$user_email_id])->first();

			if (!empty($user_details)) {
				$user_office_details = $this->DmiRoOffices->find('all')->select(['ro_office'])->where(['id IS'=>$user_details['posted_ro_office']])->first();
			}

			if (!empty($user_office_details)) {
				$user_office[$i] = $user_office_details['ro_office'];
			} else {
				$user_office[$i] = '---';
			}

			if (!empty($user_details)) {
				$user_full_name = $user_details['f_name'].' '.$user_details['l_name'];
				$user_name_detail[$i] = $user_full_name.' ('.base64_decode($user_details['email']).')';//for email encoding
			} else {
				$user_name_detail[$i] = '---';
			}

			$i = $i+1;
		}

		$user_roles_history_data = $this->DmiUserRolesManagmentLogs->showUserRolesHistory($user_role_logs_history_details);
		$user_roles_name_list = $user_roles_history_data[0][0];
		$remove_user_roles_name_list = $user_roles_history_data[1][0];
		$add_user_roles_name_list = $user_roles_history_data[2][0];

		$this->set('orders',$user_role_logs_history_details);
		$this->set('user_roles_name_list',$user_roles_name_list);
		$this->set('remove_user_roles_name_list',$remove_user_roles_name_list);
		$this->set('add_user_roles_name_list',$add_user_roles_name_list);
		$this->set('user_office',$user_office);
		$this->set('user_name_detail',$user_name_detail);

		$this->layout = null;
		$this->autoLayout = false;
		Configure::write('debug', '0');
		$this -> render('/element/download_report_excel_format/download_report_user_roles_logs_history');
	}




	// User Logs Search Condition
	// Description : Function For user roles logs history reports search conditions
	// @Author : Pravin Bhakare
	// #Contributer : Ankur Jangid (Migration)
	// Date : 11-09-2017

	public function userLogsSearchCondition($user_role_logs_history_details,$search_office,$search_user_role,$search_user_id,$search_from_date,$search_to_date) {

		if (!empty($user_role_logs_history_details)) {

			if ($search_user_role != null && $search_user_id != null && $search_from_date != null && $search_to_date != null) {
				$user_role_logs_details = null;

				$j=0;
				foreach ($user_role_logs_history_details as $user_assign_roles) {

					$user_role_assign_details = $user_assign_roles['user_roles'];
					$assign_user_roles = explode(',',$user_role_assign_details);

					if (in_array($search_user_role,$assign_user_roles, TRUE)) {

						if ($user_assign_roles['to_user']==$search_user_id) {

							$date = explode(' ',$user_assign_roles['created']);
							$created_value_xy = strtotime(str_replace('/','-',$date[0]));
							$search_from_date_xy = strtotime(str_replace('/','-',$search_from_date));
							$search_to_date_xy  = strtotime(str_replace('/','-',$search_to_date));

							if (($created_value_xy > $search_from_date_xy) && ($created_value_xy < $search_to_date_xy)) {
								$user_role_logs_details[$j] = $user_assign_roles;
								$j = $j+1;
							}
						}
					}
				}

				return $user_role_logs_details;

			} elseif ($search_user_role != null && $search_user_id != null) {

				$user_role_logs_details = null;
				$j=0;

				foreach ($user_role_logs_history_details as $user_assign_roles) {

					$user_role_assign_details = $user_assign_roles['user_roles'];
					$assign_user_roles = explode(',',$user_role_assign_details);

					if (in_array($search_user_role,$assign_user_roles, TRUE)) {

						if ($user_assign_roles['to_user']==$search_user_id) {
							$user_role_logs_details[$j] = $user_assign_roles;
							$j = $j+1;
						}
					}
				}

				return $user_role_logs_details;

			} elseif ($search_user_role != null && $search_from_date != null && $search_to_date != null) {

				$user_role_logs_details = null;

				$j=0;
				foreach ($user_role_logs_history_details as $user_user) {

					$user_role_assign_details = $user_user['user_roles'];
					$assign_user_roles = explode(',',$user_role_assign_details);

					if (in_array($search_user_role,$assign_user_roles, TRUE)) {

						$date = explode(' ',$user_user['created']);
						$created_value_xy = strtotime(str_replace('/','-',$date[0]));
						$search_from_date_xy = strtotime(str_replace('/','-',$search_from_date));
						$search_to_date_xy  = strtotime(str_replace('/','-',$search_to_date));
						if (($created_value_xy > $search_from_date_xy) && ($created_value_xy < $search_to_date_xy)) {
							$user_role_logs_details[$j] = $user_user; //change on 08-04-2022
							$j = $j+1;
						}
					}
				}

				return $user_role_logs_details;

			} elseif ($search_from_date != null && $search_to_date != null) {

				$user_role_logs_details = null;

				$j=0;
				foreach ($user_role_logs_history_details as $user_assign_roles) {

					$date = explode(' ',$user_assign_roles['created']);
					$created_value_xy = strtotime(str_replace('/','-',$date[0]));
					$search_from_date_xy = strtotime(str_replace('/','-',$search_from_date));
					$search_to_date_xy  = strtotime(str_replace('/','-',$search_to_date));

					if (($created_value_xy >= $search_from_date_xy) && ($created_value_xy <= $search_to_date_xy)) {
						$user_role_logs_details[$j] = $user_assign_roles;
						$j = $j+1;
					}
				}

				return $user_role_logs_details;

			} elseif ($search_user_id != null) {

				$user_role_logs_details = null;

				$j=0;
				foreach ($user_role_logs_history_details as $user_assign_roles) {

					if ($user_assign_roles['to_user']==$search_user_id) {
						$user_role_logs_details[$j] = $user_assign_roles;
						$j = $j+1;
					}
				}

				return $user_role_logs_details;

			} elseif ($search_user_role != null) {

				$user_role_logs_details = null;

				$j=0;
				foreach ($user_role_logs_history_details as $user_assign_roles) {

					$user_role_assign_details = $user_assign_roles['user_roles'];
					$assign_user_roles = explode(',',$user_role_assign_details);

					if (in_array($search_user_role,$assign_user_roles, TRUE)) {
						$user_role_logs_details[$j] = $user_assign_roles;
						$j = $j+1;
					}
				}

				return $user_role_logs_details;

			} else {
				$user_role_logs_details = $user_role_logs_history_details;
				return $user_role_logs_details;
			}

		} else {
			$user_role_logs_details = $user_role_logs_history_details;
			return $user_role_logs_details;
		}

	}




	// Mo Allocation Logs Report / Io Allocation Logs Report / Ro Allocation Logs Report
	// Description :  Start Mo , IO, RO allocation Logs History Report Section
	// @Author : Pravin Bhakare
	// #Contributer : Ankur Jangid (Migration)
	// Date : 11-09-2017

	public function moAllocationLogsReport()
	{
		$this->Session->write('allocation_logs_user_type','MO');
		$this->redirect('/reports/allocation_logs_report');
	}

	public function ioAllocationLogsReport()
	{
		$this->Session->write('allocation_logs_user_type','IO');
		$this->redirect('/reports/allocation_logs_report');
	}

	public function roAllocationLogsReport()
	{
		$this->Session->write('allocation_logs_user_type','RO');
		$this->redirect('/reports/allocation_logs_report');
	}





	// Allocation Logs Report
	// Description :  ----
	// @Author : Pravin Bhakare
	// #Contributer : Ankur Jangid (Migration)
	// Date : ----

	public function allocationLogsReport() {

		$user_type = $this->Session->read('allocation_logs_user_type');

		if (!empty($user_type)) {
			$user_type = $user_type;
		} else {
			$user_type ='MO';
		}

		$this->set('user_type',$user_type);

		if ($user_type == 'MO') {
			$report_heading = 'MO/SMO Allocation Logs History Report';
		} elseif ($user_type == 'IO') {
			$report_heading = 'IO Allocation Logs History Report';
		} elseif ($user_type == 'RO') {
			$report_heading = 'RO Incharge Allocation Logs History Report';
		}

		$this->set('report_heading',$report_heading);

		$user_name_list = $this->userNameList();
		$this->set('user_name_list',$user_name_list);

		//added 'office_type'=>'RO' condition on 27-07-2018
		$ro_office = $this->DmiRoOffices->find('all')->where(['office_type' => 'RO','delete_status IS NULL'])->order(['ro_office' => 'ASC'])->combine('id', 'ro_office')->toArray();
		$this->set('ro_office',$ro_office);

		$search_office = $this->Session->read('search_office');
		$application_id = $this->Session->read('application_id');
		$search_user_id = $this->Session->read('search_user_id');
		$search_from_date = $this->Session->read('search_from_date');
		$search_to_date = $this->Session->read('search_to_date');
		$this->set('search_office',$search_office);
		$this->set('application_id',$application_id);
		$this->set('search_user_id',$search_user_id);
		$this->set('search_from_date',$search_from_date);
		$this->set('search_to_date',$search_to_date);

		// Set default value for download report click event (Done by pravin 13-03-2018)
		$download_report = 'no';

		//Check and Pass the entry for "Search" or "Download Report as Excel" button click event (Done by pravin 13-03-2018)
		if (null != ($this->request->getData('search_logs')) || null != ($this->request->getData('download_report'))) {

			//Check not empty "Download Report as Excel" button Request, if condition TRUE then set value "yes" for "Download Report as Excel" click event
			//and pass this value to "mo_io_ro_allocation_serach_conditions" function (Done by pravin 13-03-2018)
			if (!empty($this->request->getData('download_report'))) {
				$download_report = 'yes';
			}

			//checking dropdown input
			$table = 'DmiRoOffices';
			$post_input_request = $this->request->getData('office');
			if (!empty($post_input_request)) {
				$search_office = $this->Customfunctions->dropdownSelectInputCheck($table,$post_input_request); // calling library function
			} else {
				$search_office = '';
			}

			if ($user_type == 'RO') {
				$application_id = null;
			} else {
				$application_id =  htmlentities($this->request->getData('application_id'), ENT_QUOTES);
			}

			$search_user_id = $this->request->getData('user_id');
			$search_from_date = $this->request->getData('from_date');
			$search_from_date = $this->Customfunctions->dateFormatCheck($search_from_date);
			$search_to_date = $this->request->getData('to_date');
			$search_to_date = $this->Customfunctions->dateFormatCheck($search_to_date);
			$this->date_comparison($search_from_date,$search_to_date);

			// Change on 2/11/2018 : For download excel report, Take search filter field value from session variables instend of POST variable - By Pravin Bhakare
			if ($download_report == 'yes') {

				$search_office = $this->Session->read('search_office');
				$application_id = $this->Session->read('application_id');
				$search_user_id = $this->Session->read('search_user_id');
				$search_from_date = $this->Session->read('search_from_date');
				$search_to_date = $this->Session->read('search_to_date');
			}

			$this->Session->delete('search_office');
			$this->Session->delete('application_id');
			$this->Session->delete('search_user_id');
			$this->Session->delete('search_from_date');
			$this->Session->delete('search_to_date');

			$this->Session->write('search_office',$search_office);
			$this->Session->write('application_id',$application_id);
			$this->Session->write('search_user_id',$search_user_id);
			$this->Session->write('search_from_date',$search_from_date);
			$this->Session->write('search_to_date',$search_to_date);


			$this->set('search_office',$search_office);
			$this->set('application_id',$application_id);
			$this->set('search_user_id',$search_user_id);
			$this->set('search_from_date',$search_from_date);
			$this->set('search_to_date',$search_to_date);

			$this->moIoRoAllocationSerachConditions($user_type, $search_office, $application_id, $search_user_id, $search_from_date, $user_name_list, $search_to_date, $ro_office, $download_report);
		} else {
			$this->moIoRoAllocationSerachConditions($user_type, $search_office, $application_id, $search_user_id, $search_from_date, $user_name_list, $search_to_date, $ro_office, $download_report);
		}

	}



	// Mo Io Ro Allocation Serach Conditions
	// Description : This function is set of search conditions that are used for to create MO, IO, RO allocation logs report_types
	// @Author : Pravin Bhakare
	// #Contributer : Ankur Jangid (Migration)
	// Date : ----

	public function moIoRoAllocationSerachConditions($user_type, $search_office, $application_id, $search_user_id, $search_from_date, $user_name_list, $search_to_date, $ro_office, $download_report) {

		if ($user_type == 'IO') {
			$table= 'DmiIoAllocationLogs';
			$office_field = 'io_office';
			$user_id_field = 'io_email_id';
		} elseif ($user_type == 'MO') {
			$table= 'DmiMoAllocationLogs';
			$office_field = 'mo_office';
			$user_id_field = 'mo_email_id';
		} elseif ($user_type == 'RO') {
			$table= 'DmiRoAllocationLogs';
			$office_field = 'ro_office';
			$user_id_field = 'ro_incharge_id';
		}

		$office_not_empty = null;
		$application_id_not_empty = null;
		$user_id_not_empty = null;
		$date_not_empty = null;

		if ($search_office != null) {
			$search_office = $ro_office[$search_office];
			$office_not_empty = array($office_field => $search_office);
		}

		if ($application_id != null) {
			$update_application_id = $application_id; // updated strtolower($application_id) to $application_id by Ankur
			$application_id_not_empty = ['customer_id LIKE' => '%'.$update_application_id.'%']; // updated ['LOWER(customer_id) LIKE' => to ['customer_id LIKE' => by Ankur
		}

		if ($search_user_id != null) {
			$search_user_id = $this->search_user_id($user_name_list, $search_user_id);
			$user_id_not_empty = [$user_id_field => $search_user_id];
		}

		if ($search_from_date != null && $search_to_date != null) {
			$date_not_empty = ['date(created) BETWEEN :start AND :end'];
		}

		if ($search_office == '' && $application_id == '' && $search_user_id == '' && $search_from_date == '' && $search_to_date == '') {
			$this->set('search_result_for', 'all');

			if (null != ($this->request->getData('search_logs')) || null != ($this->request->getData('download_report'))) {
				$allocation_logs_details = $this->$table->find('all')->order(['id' => 'DESC'])->toArray();
			} else { // default query without search with limit top 100
				//$allocation_logs_details = $this->$table->find('all')->order(['id' => 'DESC'])->limit(['100'])->toArray();
				$allocation_logs_details = $this->$table->find('all')->order(['id' => 'DESC'])->toArray();
			}
			//Fetch the all data that required for creating the downloading report as execel (Done by pravin 13-03-2018)
			if ($download_report == 'yes') {
				$download_allocation_logs_details = $this->$table->find('all')->order(['id' => 'DESC'])->toArray();
				$this->downloadAllocationLogsDetailsReport($download_allocation_logs_details, $table, $office_field, $user_id_field);
			}

		} else {

			if ($search_from_date != null && $search_to_date != null) {
				$allocation_logs_details = $this->$table->find('all')->where($office_not_empty)->where($application_id_not_empty)->where($user_id_not_empty)
					->where($date_not_empty)->bind(':start', $search_from_date, 'date')->bind(':end', $search_to_date, 'date')->order(['id' => 'desc'])->toArray();
			} else {
				$allocation_logs_details = $this->$table->find('all')->where($office_not_empty)->where($application_id_not_empty)->where($user_id_not_empty)
					->order(['id' => 'desc'])->toArray();
			}

			//Fetch the all data that required for creating the downloading report as execel (Done by pravin 13-03-2018)
			if ($download_report == 'yes') {

				if ($search_from_date != null && $search_to_date != null) {
					$download_allocation_logs_details = $this->$table->find('all')->where($office_not_empty)->where($application_id_not_empty)->where($user_id_not_empty)
						->where($date_not_empty)->bind(':start', $search_from_date, 'date')->bind(':end', $search_to_date, 'date')->order(['id' => 'DESC'])->toArray();
				} else {
					$download_allocation_logs_details = $this->$table->find('all')->where($office_not_empty)->where($application_id_not_empty)->where($user_id_not_empty)
						->order(['id' => 'DESC'])->toArray();
				}
				$this->downloadAllocationLogsDetailsReport($download_allocation_logs_details, $table, $office_field, $user_id_field);
			}
		}

		if (!empty($allocation_logs_details)) {

			$i=0;
			foreach ($allocation_logs_details as $user_name) {
				$user_id = $user_name['user_id'];
				$user_details = $this->DmiUsers->find('all')->where(['id IS' => intval($user_id)])->first();

				if (!empty($user_details)) {
					$user_full_name = $user_details['f_name'].' '.$user_details['l_name'];
					$user_name_detail[$i] = $user_full_name.' ('.base64_decode($user_details['email']).')';//for email encoding
				} else {
					$user_name_detail[$i] = '---';
				}

				$i = $i+1;
			}

			$this->set('user_name_detail', $user_name_detail);

		} else {
			$allocation_logs_details = array();
		}

		$this->set('allocation_logs_details', $allocation_logs_details);
		$this->set('table', $table);
		$this->set('office_field', $office_field);
		$this->set('user_id_field', $user_id_field);
	}





	// Download Allocation Logs Details Report
	// Description : This function create excel format file for downloading the report for "allocation logs details report"
	// @Author : Pravin Bhakare
	// #Contributer : Ankur Jangid (Migration)
	// Date : 13-03-2018

	public function downloadAllocationLogsDetailsReport($download_allocation_logs_details, $table, $office_field, $user_id_field) {

		$this->viewBuilder()->setLayout('downloadpdf');

		if (!empty($download_allocation_logs_details)) {
			$i=0;
			foreach ($download_allocation_logs_details as $user_name) {
				$user_id = $user_name['user_id'];
				$user_details = $this->DmiUsers->find('all')->where(['id IS' => intval($user_id)])->first();

				if (!empty($user_details)) {
					$user_full_name = $user_details['f_name'].' '.$user_details['l_name'];
					$user_name_detail[$i] = $user_full_name.' ('.base64_decode($user_details['email']).')';//for email encoding
				}
				else {
					$user_name_detail[$i] = '---';
				}
				$i = $i+1;
			}
			$this->set('user_name_detail', $user_name_detail);
			$this->set('orders', $download_allocation_logs_details);
			$this->set('table', $table);
			$this->set('office_field', $office_field);
			$this->set('user_id_field', $user_id_field);

			$this->layout = null;
			$this->autoLayout = false;
			Configure::write('debug', '0');
			$this -> render('/element/download_report_excel_format/download_allocation_logs_details_report');
		}
	}





	// Fifteen Day Pending New Application / Fifteen Day Pending Renewal Application
	// Description : Start to create Pending new application report
	// @Author : Pravin Bhakare
	// #Contributer : Ankur Jangid (Migration)
	// Date : 16-09-2017

	public function fifteenDayPendingNewApplication()
	{
		$this->Session->write('pending_days','more_than_fifteen');
		$this->redirect('/reports/pending_new_applications_report');
	}

	public function fifteenDayPendingRenewalApplication()
	{
		$this->Session->write('pending_days','more_than_fifteen');
		$this->redirect('/reports/pending_renewal_applications_report');
	}



		// Pending New Applications Report
	// Description : ----
	// @Author : Pravin Bhakare
	// #Contributer : Ankur Jangid (Migration)
	// Date : ----

	public function pendingNewApplicationsReport() {

		
		$application_pending_days = $this->Session->read('pending_days');

		if (!empty($application_pending_days)) {
			$report_name = 'Pending New Applications Report ( More than 15 Days)';
		} else {
			$report_name ='Pending New Applications Report';
		}

		$this->set('report_name',$report_name);

		$table = 'DmiAllApplicationsCurrentPositions';
		$pending_application_type = 'new';

		$application_type_xy = array('A'=>'CA (Form-A)', 'C'=>'Laboratory (Form-C)', 'E'=>'CA (Form-E)', 'B'=>'Printing Press (Form-B)', 'D'=>'Laboratory (Form-D)', 'F'=>'CA (Form-F)');

		//Change on 9/11/2018, Sorting array by ascending order - By Pravin Bhakare
		asort($application_type_xy);
		$this->set('application_type_xy',$application_type_xy);

		$user_roles_xy = array('RO/SO'=>'RO/SO','MO/SMO'=>'MO/SMO','IO'=>'IO','HO MO/SMO'=>'HO MO/SMO','DY.AMA'=>'DY.AMA','JT.AMA'=>'JT.AMA','AMA'=>'AMA');

		//Change on 9/11/2018, Sorting array by ascending order - By Pravin Bhakare
		asort($user_roles_xy);
		$this->set('user_roles_xy',$user_roles_xy);

		$ro_office = $this->DmiRoOffices->find('all')->where(['office_type' => 'RO','delete_status IS NULL'])->order(['ro_office' => 'ASC'])->combine('id', 'ro_office')->toArray(); 
		$this->set('ro_office',$ro_office);

		$search_application_type_id = $this->Session->read('search_application_type_id');
		$search_user_role = $this->Session->read('search_user_role');
		$ro_office_id = $this->Session->read('ro_office_id');
		$mo_office_id = $this->Session->read('mo_office_id');
		$io_office_id = $this->Session->read('io_office_id');
		$search_from_date = $this->Session->read('search_from_date');
		$search_to_date = $this->Session->read('search_to_date');
		$search_user_email_id = $this->Session->read('search_user_email_id');

		$download_search_application_type_id = $this->Session->read('search_application_type_id');
		$download_search_user_role = $this->Session->read('search_user_role');
		$download_ro_office_id = $this->Session->read('ro_office_id');
		$download_mo_office_id = $this->Session->read('mo_office_id');
		$download_io_office_id = $this->Session->read('io_office_id');
		$download_search_from_date = $this->Session->read('search_from_date');
		$download_search_to_date = $this->Session->read('search_to_date');
		$download_search_user_email_id = $this->Session->read('search_user_email_id');

		$this->set('search_application_type_id',$search_application_type_id);
		$this->set('search_user_role',$search_user_role);
		$this->set('ro_office_id',$ro_office_id);
		$this->set('mo_office_id',$mo_office_id);
		$this->set('io_office_id',$io_office_id);
		$this->set('search_from_date',$search_from_date);
		$this->set('search_to_date',$search_to_date);
		$this->set('search_user_email_id',$search_user_email_id);

		// Set default value for download report click event (Done by pravin 14-03-2018)
		$download_report = 'no';

		//Check and Pass the entry for "Search" or "Download Report as Excel" button click event (Done by pravin 14-03-2018)
		if (null != ($this->request->getData('search_logs')) || null != ($this->request->getData('download_report'))) {
			//Check not empty "Download Report as Excel" button Request, if condition TRUE then set value "yes" for "Download Report as Excel" click event
			//and pass this value to "mo_io_ro_allocation_serach_conditions" function (Done by pravin 14-03-2018)
			if (!empty($this->request->getData('download_report'))) {
				$download_report = 'yes';
			}
			
			$search_application_type_id = $this->request->getData('application_type');
			$search_user_role =  $this->request->getData('user_role');
			$ro_office_id =  $this->request->getData('ro_office');
			$mo_office_id =  $this->request->getData('mo_office');
			$io_office_id =  $this->request->getData('io_office');

			$search_user_email_id =  $this->request->getData('user_id');
			$search_from_date =  $this->request->getData('from_date');
			$search_from_date = $this->Customfunctions->dateFormatCheck($search_from_date);
			$search_to_date =  $this->request->getData('to_date');
			$search_to_date = $this->Customfunctions->dateFormatCheck($search_to_date);
			$this->date_comparison($search_from_date,$search_to_date);

			// Change on 2/11/2018 - For download excel report, Take search filter field value from session variables instend of POST variable - By Pravin
			if ($download_report == 'yes') {

				$search_application_type_id = $this->Session->read('search_application_type_id');
				$search_user_role = $this->Session->read('search_user_role');
				$ro_office_id = $this->Session->read('ro_office_id');
				$mo_office_id = $this->Session->read('mo_office_id');
				$io_office_id = $this->Session->read('io_office_id');
				$search_from_date = $this->Session->read('search_from_date');
				$search_to_date = $this->Session->read('search_to_date');
				$search_user_email_id = $this->Session->read('search_user_email_id');
			}

			$download_application_customer_id_list = $this->pendingApplicationSearchConditions($download_search_application_type_id,$download_search_user_role,$download_ro_office_id,$download_mo_office_id,$download_io_office_id,$download_search_from_date,$download_search_to_date,$download_search_user_email_id,$table,$pending_application_type,$application_pending_days);

			$this->Session->delete('search_application_type_id');
			$this->Session->delete('search_user_role');
			$this->Session->delete('ro_office_id');
			$this->Session->delete('mo_office_id');
			$this->Session->delete('io_office_id');
			$this->Session->delete('search_from_date');
			$this->Session->delete('search_to_date');
			$this->Session->delete('search_user_email_id');

			$this->Session->write('search_application_type_id',$search_application_type_id);
			$this->Session->write('search_user_role',$search_user_role);
			$this->Session->write('ro_office_id',$ro_office_id);
			$this->Session->write('mo_office_id',$mo_office_id);
			$this->Session->write('io_office_id',$io_office_id);
			$this->Session->write('search_from_date',$search_from_date);
			$this->Session->write('search_to_date',$search_to_date);
			$this->Session->write('search_user_email_id',$search_user_email_id);

			$this->set('search_application_type_id',$search_application_type_id);
			$this->set('search_user_role',$search_user_role);
			$this->set('ro_office_id',$ro_office_id);
			$this->set('mo_office_id',$mo_office_id);
			$this->set('io_office_id',$io_office_id);
			$this->set('search_from_date',$search_from_date);
			$this->set('search_to_date',$search_to_date);
			$this->set('search_user_email_id',$search_user_email_id);


			$application_customer_id_list = $this->pendingApplicationSearchConditions($search_application_type_id,$search_user_role,$ro_office_id,$mo_office_id,$io_office_id,$search_from_date,$search_to_date,$search_user_email_id,$table,$pending_application_type,$application_pending_days);

			if (!empty($application_customer_id_list)) {

				$current_users_details = $this->$table->find('all')->where(['customer_id IN'=>$application_customer_id_list])->order(['id' => 'DESC'])->toArray(); 

				//Fetch the all data that required for creating the downloading report as execel (Done by pravin 14-03-2018)
				if ($download_report == 'yes') {

					if (!empty($download_application_customer_id_list)) {
						$download_condition = ['customer_id IN' => $download_application_customer_id_list];
					} else {
						$download_condition = ['customer_id IS' => ''];
					}

					$download_pending_application = $this->$table->find('all')->where($download_condition)->order(['id' => 'DESC'])->toArray(); 
					$this->downloadPendingApplicationReport($download_pending_application,$pending_application_type,$table);
				}
				
			} else {
				$current_users_details = null;
			}

			$this->pendingApplicationReportResults($current_users_details,$pending_application_type,$table);

		} else {
			
		$application_customer_id_list = $this->pendingApplicationSearchConditions($search_application_type_id,$search_user_role,$ro_office_id,$mo_office_id,$io_office_id,$search_from_date,$search_to_date,$search_user_email_id,$table,$pending_application_type,$application_pending_days,$selected_month=Null);/*add new parameter by shreeya*/

			if (!empty($application_customer_id_list)) {

				$current_users_details = $this->$table->find('all')->where(['customer_id IN' => $application_customer_id_list])->order(['id' => 'DESC'])->limit(['100'])->toArray(); 
				$this->set('current_users_details',$current_users_details);

				//Fetch the all data that required for creating the downloading report as execel (Done by pravin 14-03-2018)
				if ($download_report == 'yes') {
					$download_pending_application = $this->$table->find('all')->where(['customer_id' => $application_customer_id_list])->order(['id' => 'DESC'])->toArray(); 
					$this->downloadPendingApplicationReport($download_pending_application,$pending_application_type,$table);
				}

			} else {
				$current_users_details = null;
			}

			$this->pendingApplicationReportResults($current_users_details,$pending_application_type,$table);
		}
	
	}


	// Pending New Applications Report For Stats
	// Description : ----
	// @Author : Pravin Bhakare
	// #Contributer : Yash & Shreeya
	// Date : 24-05-2023

	/*=================== This Function Used for In_process New Application KPI's===========*/
	public function pendingNewApplicationsReportForStats($cert_type,$appl_type) {	//newapp_id replace ->cert_type By Shreeya

		/*===================Added New Code  for show the list of count (Start) Date [24-05-2023 By Shreeya] ===========*/
		//pass the parameter of cert_type,appl_type
		$appl_type=base64_decode($appl_type);
		$cert_type=base64_decode($cert_type);
		$data_id =array($cert_type);


		if ($cert_type== 'CA')
		{
			$cert_type = 1;
		}
		elseif ($cert_type== 'PP')
		{
			$cert_type = 2;
		}
		elseif ($cert_type== 'LAB')
		{
			$cert_type = 3;
		}



		//check the which application type is present
		if($appl_type=='new'){
			$processFunction = 'new_app_processed';
		}elseif($appl_type=='renewal'){
			$processFunction = 'renewal_app_processed';
		}elseif($appl_type=='backlog'){
			$processFunction = 'backlog_app_processed';
		}


		//show the count according to application type
		$searchConditions = array();
		$application_processed[] = $this->Reportstatistics->$processFunction($searchConditions,null,null,$cert_type,$appl_type);
		$applListToShow = $application_processed[0][2];


		$application_id = null;
		$application_type = null;
		$user_roles = null;
		$user_office = null;
		$user_email_id =null;
		$date = []; // Rename the variable to avoid overwriting the previous $date variable

		$i = 0;
		foreach ($applListToShow as $each_customer_id) {

			$application_id[$i] = $each_customer_id;


			$table = 'DmiAllApplicationsCurrentPositions';
			$current_users_details = $this->$table->find('all')->where(['customer_id IN' => $each_customer_id])->order(['id' => 'DESC'])->first();


			$application_form_type = $this->Customfunctions->checkApplicantFormType($each_customer_id);

				if ($application_form_type == 'A') {
					$application_type[$i]='CA (Form-A)';
				} elseif ($application_form_type == 'B') {
					$application_type[$i]='Printing Press (Form-B)';
				} elseif ($application_form_type == 'C') {
					$application_type[$i]='Laboratory (Form-C)';
				} elseif ($application_form_type == 'D') {
					$application_type[$i]='Laboratory (Form-D)';
				} elseif ($application_form_type == 'E') {
					$application_type[$i]='CA (Form-E)';
				} elseif ($application_form_type == 'F') {
					$application_type[$i]='CA (Form-F)';
				}

			$date[$i] = $current_users_details['modified']; // Store the value in a new array
			$user_email_id[$i] = $current_users_details['current_user_email_id'];
			$current_level[$i] = $current_users_details['current_level'];


				$user_posted_office_id=array();
				if (!empty($user_email_id[$i])) {
					$user_posted_office_id = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email IN' => $user_email_id[$i]])->first();

				}
				if (!empty($user_posted_office_id)) {
					$user_office[$i] = $this->DmiRoOffices->find('all')->select(['ro_office'])->where(['id' => $user_posted_office_id['posted_ro_office']])->first();

				}

				if (!empty($user_office[$i])) {
					$user_office[$i] = $user_office[$i]['ro_office'];
				} else {
					$user_office[$i] = '--';
				}

				$check_roles=array();
				if (!empty($user_email_id[$i])) {
					$check_roles = $this->DmiUserRoles->find('all')->where(['user_email_id IN' => $user_email_id[$i]])->first();
				}

				if (!empty($check_roles)) {
					$user_list[$i] = $check_roles;
				} else {
					$user_list[$i] = '---';
				}

				$user_roles[$i] = $this->checkUserRoleFromCurrentLevel($current_users_details['current_level'],$current_users_details['current_user_email_id']);



			$i = $i + 1;

			$this->set('date',$date);
			$this->set('user_list',$user_list);
			$this->set('application_type',$application_type);
			$this->set('user_roles',$user_roles);
			$this->set('user_office',$user_office);
			$this->set('user_email_id',$user_email_id);
			$this->set('application_id',$application_id);
		}

		### -End- ###

		//variable set for session from in process  laxmi b on 16-02-2023
		$static_pending_from_date = '';
		$static_pending_to_date = '';
		$static_pending_roOfficeShortCode ='';
		$static_pending_ro_office_id ='';

		$application_pending_days = $this->Session->read('pending_days');

		if (!empty($application_pending_days)) {
			$report_name = 'Pending New Applications Report ( More than 15 Days)';
		} else {
			$report_name ='Pending New Applications Report';
		}

		$this->set('report_name',$report_name);

		//$table = 'DmiAllApplicationsCurrentPositions';

		$pending_application_type = 'new';

		$application_type_xy = array('A'=>'CA (Form-A)', 'C'=>'Laboratory (Form-C)', 'E'=>'CA (Form-E)', 'B'=>'Printing Press (Form-B)', 'D'=>'Laboratory (Form-D)', 'F'=>'CA (Form-F)');
		//newapp_id replace ->cert_type By Shreeya
		if($cert_type=='CA')
		{
			$application_type_xy = array('A'=>'CA (Form-A)','E'=>'CA (Form-E)','F'=>'CA (Form-F)');
		}
		elseif($cert_type=='PP')
		{
			$application_type_xy = array('B'=>'Printing Press (Form-B)');
		}
		elseif ($cert_type=='LAB') {
			$application_type_xy = array('C'=>'Laboratory (Form-C)','D'=>'Laboratory (Form-D)');
		}


		//Change on 9/11/2018, Sorting array by ascending order - By Pravin Bhakare
		asort($application_type_xy);
		$this->set('application_type_xy',$application_type_xy);
		//newapp_id replace ->cert_type By Shreeya
		$this->set('cert_type',$cert_type);

		$user_roles_xy = array('RO/SO'=>'RO/SO','MO/SMO'=>'MO/SMO','IO'=>'IO','HO MO/SMO'=>'HO MO/SMO','DY.AMA'=>'DY.AMA','JT.AMA'=>'JT.AMA','AMA'=>'AMA');

		//Change on 9/11/2018, Sorting array by ascending order - By Pravin Bhakare
		asort($user_roles_xy);
		$this->set('user_roles_xy',$user_roles_xy);

		$ro_office = $this->DmiRoOffices->find('all')->where(['office_type' => 'RO','delete_status IS NULL'])->order(['ro_office' => 'ASC'])->combine('id', 'ro_office')->toArray();
		$this->set('ro_office',$ro_office);



		$search_application_type_id = $this->Session->read('search_application_type_id');
		$search_user_role = $this->Session->read('search_user_role');
		$ro_office_id = $this->Session->read('ro_office_id');
		$mo_office_id = $this->Session->read('mo_office_id');
		$io_office_id = $this->Session->read('io_office_id');
		$search_from_date = $this->Session->read('search_from_date');
		$search_to_date = $this->Session->read('search_to_date');
		$search_user_email_id = $this->Session->read('search_user_email_id');

		$download_search_application_type_id = $this->Session->read('search_application_type_id');
		$download_search_user_role = $this->Session->read('search_user_role');
		$download_ro_office_id = $this->Session->read('ro_office_id');
		$download_mo_office_id = $this->Session->read('mo_office_id');
		$download_io_office_id = $this->Session->read('io_office_id');
		$download_search_from_date = $this->Session->read('search_from_date');
		$download_search_to_date = $this->Session->read('search_to_date');
		$download_search_user_email_id = $this->Session->read('search_user_email_id');

		$this->set('search_application_type_id',$search_application_type_id);
		$this->set('search_user_role',$search_user_role);
		$this->set('ro_office_id',$ro_office_id);
		$this->set('mo_office_id',$mo_office_id);
		$this->set('io_office_id',$io_office_id);
		$this->set('search_from_date',$search_from_date);
		$this->set('search_to_date',$search_to_date);
		$this->set('search_user_email_id',$search_user_email_id);

		// Set default value for download report click event (Done by pravin 14-03-2018)
		$download_report = 'no';
		 //set session variable and delete it by laxmi Bhadade on 16-02-2023
		$static_pending_from_date = $this->Session->read('from_date');
		$static_pending_to_date = $this->Session->read('to_date');
		$static_pending_roOfficeShortCode = $this->Session->read('roOfficeShortCode');
		$static_pending_ro_office_id = $this->Session->read('ro_office_id');

		//delete session
		$this->Session->delete('ro_office_id');
		$this->Session->delete('roOfficeShortCode');
		$this->Session->delete('to_date');
		$this->Session->delete('from_date');

		//Check and Pass the entry for "Search" or "Download Report as Excel" button click event (Done by pravin 14-03-2018)
		if (((!empty($static_pending_from_date) && !empty($static_pending_to_date)) || !empty($static_pending_roOfficeShortCode)) || (null != ($this->request->getData('search_logs')) || null != ($this->request->getData('download_report')))) {
			//Check not empty "Download Report as Excel" button Request, if condition TRUE then set value "yes" for "Download Report as Excel" click event
			//and pass this value to "mo_io_ro_allocation_serach_conditions" function (Done by pravin 14-03-2018)
			if (!empty($this->request->getData('download_report'))) {
				$download_report = 'yes';
			}

			$search_application_type_id = $this->request->getData('application_type');
			$search_user_role =  $this->request->getData('user_role');
			$ro_office_id =  $this->request->getData('ro_office');
			$mo_office_id =  $this->request->getData('mo_office');
			$io_office_id =  $this->request->getData('io_office');

			$search_user_email_id =  $this->request->getData('user_id');
			$search_from_date =  $this->request->getData('from_date');
			$search_from_date = $this->Customfunctions->dateFormatCheck($search_from_date);
			$search_to_date =  $this->request->getData('to_date');
			$search_to_date = $this->Customfunctions->dateFormatCheck($search_to_date);
			$this->date_comparison($search_from_date,$search_to_date);

			// Change on 2/11/2018 - For download excel report, Take search filter field value from session variables instend of POST variable - By Pravin
			if ($download_report == 'yes') {

				$search_application_type_id = $this->Session->read('search_application_type_id');
				$search_user_role = $this->Session->read('search_user_role');
				$ro_office_id = $this->Session->read('ro_office_id');
				$mo_office_id = $this->Session->read('mo_office_id');
				$io_office_id = $this->Session->read('io_office_id');
				$search_from_date = $this->Session->read('search_from_date');
				$search_to_date = $this->Session->read('search_to_date');
				$search_user_email_id = $this->Session->read('search_user_email_id');
			}

			$download_application_customer_id_list = $this->pendingApplicationSearchConditions($download_search_application_type_id,$download_search_user_role,$download_ro_office_id,$download_mo_office_id,$download_io_office_id,$download_search_from_date,$download_search_to_date,$download_search_user_email_id,$table,$pending_application_type,$application_pending_days,$data_id,$selected_month=Null);/*add new parameter by shreeya*/ 

			$this->Session->delete('search_application_type_id');
			$this->Session->delete('search_user_role');
			$this->Session->delete('ro_office_id');
			$this->Session->delete('mo_office_id');
			$this->Session->delete('io_office_id');
			$this->Session->delete('search_from_date');
			$this->Session->delete('search_to_date');
			$this->Session->delete('search_user_email_id');

			//set from_date and to_date and office name from session of statstics report added by laxmi B. on 15-02-2023
			if(!empty($static_pending_from_date) && !empty($static_pending_to_date && empty($static_pending_roOfficeShortCode) )){
				$search_from_date = $static_pending_from_date;
				$search_to_date = $static_pending_to_date;
				$search_from_date = $this->Customfunctions->dateFormatCheck($search_from_date);
				$search_to_date = $this->Customfunctions->dateFormatCheck($search_to_date);
				$this->date_comparison($search_from_date, $search_to_date);

			} elseif(!empty($static_pending_from_date) && !empty($static_pending_to_date && !empty($static_pending_roOfficeShortCode))) {
				$search_from_date = $static_pending_from_date;
				$search_to_date = $static_pending_to_date;
				$search_from_date = $this->Customfunctions->dateFormatCheck($search_from_date);
				$search_to_date = $this->Customfunctions->dateFormatCheck($search_to_date);
				$this->date_comparison($search_from_date, $search_to_date);

				$ro_office = $this->DmiRoOffices->find('all')->select(['id'])->where(['id IN' => $static_pending_ro_office_id])->where(['short_code IN'=>$static_pending_roOfficeShortCode])->first();
				$ro_office_id = [$ro_office['id']];
				$search_user_role = 'RO/SO';
			} elseif(!empty($static_pending_roOfficeShortCode)) {
				$ro_office = $this->DmiRoOffices->find('all')->select(['id'])->where(['id IN' => $static_pending_ro_office_id])->where(['short_code IN'=>$static_pending_roOfficeShortCode])->first();
				$ro_office_id = [$ro_office['id']];
				$search_user_role = 'RO/SO';
			}
			//end laxmi

			$this->Session->write('search_application_type_id',$search_application_type_id);
			$this->Session->write('search_user_role',$search_user_role);
			$this->Session->write('ro_office_id',$ro_office_id);
			$this->Session->write('mo_office_id',$mo_office_id);
			$this->Session->write('io_office_id',$io_office_id);
			$this->Session->write('search_from_date',$search_from_date);
			$this->Session->write('search_to_date',$search_to_date);
			$this->Session->write('search_user_email_id',$search_user_email_id);

			$this->set('search_application_type_id',$search_application_type_id);
			$this->set('search_user_role',$search_user_role);
			$this->set('ro_office_id',$ro_office_id);
			$this->set('mo_office_id',$mo_office_id);
			$this->set('io_office_id',$io_office_id);
			$this->set('search_from_date',$search_from_date);
			$this->set('search_to_date',$search_to_date);
			$this->set('search_user_email_id',$search_user_email_id);


			$application_customer_id_list = $this->pendingApplicationSearchConditions($search_application_type_id,$search_user_role,$ro_office_id,$mo_office_id,$io_office_id,$search_from_date,$search_to_date,$search_user_email_id,$table,$pending_application_type,$application_pending_days,$data_id,$selected_month=Null);/*add new parameter by shreeya*/

			if (!empty($application_customer_id_list)) {

				$current_users_details = $this->$table->find('all')->where(['customer_id IN'=>$application_customer_id_list])->order(['id' => 'DESC'])->toArray();

				//Fetch the all data that required for creating the downloading report as execel (Done by pravin 14-03-2018)
				if ($download_report == 'yes') {

					if (!empty($download_application_customer_id_list)) {
						$download_condition = ['customer_id IN' => $download_application_customer_id_list];
					} else {
						$download_condition = ['customer_id IS' => ''];
					}

					$download_pending_application = $this->$table->find('all')->where($download_condition)->order(['id' => 'DESC'])->toArray();
					$this->downloadPendingApplicationReport($download_pending_application,$pending_application_type,$table);
				}

			} else {
				$current_users_details = null;
			}

			//below query commented by shreeya for display list of new application
			//$this->pendingApplicationReportResults($current_users_details,$pending_application_type,$table,$data_id);

		} else {


			$application_customer_id_list = $this->pendingApplicationSearchConditions($search_application_type_id,$search_user_role,$ro_office_id,$mo_office_id,$io_office_id,$search_from_date,$search_to_date,$search_user_email_id,$table,$pending_application_type,$application_pending_days,$data_id,$selected_month=Null);/*add new parameter by shreeya*/

			if (!empty($application_customer_id_list)) {

				$current_users_details = $this->$table->find('all')->where(['customer_id IN' => $application_customer_id_list])->order(['id' => 'DESC'])->toArray(); /*->limit(['100'])*/

				$this->set('current_users_details',$current_users_details);

				//Fetch the all data that required for creating the downloading report as execel (Done by pravin 14-03-2018)
				if ($download_report == 'yes') {
					$download_pending_application = $this->$table->find('all')->where(['customer_id' => $application_customer_id_list])->order(['id' => 'DESC'])->toArray();
					$this->downloadPendingApplicationReport($download_pending_application,$pending_application_type,$table);
				}

			} else {
				$current_users_details = null;
			}
			//Below query commented by shreya for display list of new application
			//$this->pendingApplicationReportResults($current_users_details,$pending_application_type,$table,$data_id);
		}

	}




	// Pending Application Search Conditions
	// Description : function used to find search conditions for pending new and renewal application report
	// @Author : Pravin Bhakare
	// #Contributer : Ankur Jangid (Migration)
	// Date : 18-09-2017

	public function pendingApplicationSearchConditions($search_application_type_id,$search_user_role,$ro_office_id,$mo_office_id,$io_office_id,$search_from_date,$search_to_date,$search_user_email_id,$table,$application_array,$pending_application_type,$application_pending_days,$data_id=null,$selected_month=null)/*add new parameter by shreeya*/
	{

		
		$current_date = new \DateTime(date("d-m-Y")); // Ankur updated new DateTime to new \DateTime as Class "App\Controller\DateTime" not found
		$modify_date_obj = $current_date->modify('-15 day');
		$modify_date = $modify_date_obj->format('d-m-Y H:i:s');


		if (!empty($application_pending_days)) {

			$conditions = ['DATE(modified) <' => $modify_date];

			$date_conditions = ['date(modified) BETWEEN :start AND :end', 'DATE(created) <' => $modify_date];

			$date_conditions_1 = ['date(modified) BETWEEN :start AND :end', 'DATE(created) <' => $modify_date];

		} else {

			$conditions = [];

			$date_conditions = ['date(modified) BETWEEN :start AND :end'];

			$date_conditions_1 = ['date(modified) BETWEEN :start AND :end', 'DATE(created) <' => $modify_date];
		}

		$final_customer_id_list = null;

		if ($ro_office_id != '' && $search_user_role == 'RO/SO') {
			$level_1_2_3_office = $ro_office_id;
		} elseif ($mo_office_id != '' && $search_user_role == 'MO/SMO') {
			$level_1_2_3_office = $mo_office_id;
		} elseif ($io_office_id != '' && $search_user_role == 'IO') {
			$level_1_2_3_office = $io_office_id;
		} else {
			$level_1_2_3_office = '';
		}


		if ($search_application_type_id != '' && $search_user_role == '' && $search_from_date == '' && $search_to_date == '') {

			$application_customer_id = $this->$table->find('all')->where($conditions)->toArray();

			$i=0;
			foreach ($application_customer_id as $each_customer_id) {

				if (!empty($each_customer_id['customer_id'])) {
					$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

					if (in_array($application_customer_type, $search_application_type_id, TRUE)) {
						$application_customer_id_list[$i] = $each_customer_id['customer_id'];
						$i=$i+1;
					}
				}
			}

		//Start Yashwant 29/mar/2023 This function used for IN PRocess-posted Filter
		} elseif ($search_application_type_id == '' && $search_from_date == '' && $search_to_date == '' && $search_user_role != '' && $level_1_2_3_office == '')
		{
			if($search_application_type_id != '')
			{

				$application_customer_id = $this->$table->find('all')->where($conditions)->toArray();

				$i=0;
				foreach ($application_customer_id as $each_customer_id) {

					if (!empty($each_customer_id['customer_id'])) {
						$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

						if (in_array($application_customer_type, $search_application_type_id, TRUE)) {
							$application_customer_id_list[$i] = $each_customer_id['customer_id'];
							$i=$i+1;
						}
					}
				}

				$application_current_levels = $this->$table->find('all')->where(['customer_id IN' => $seach_application_customer_id_list])->toArray();

			}else{
				$application_current_levels = $this->$table->find('all')->where($conditions)->toArray();
			}

			$i=0;
			foreach ($application_current_levels as $each_current_levels) {

				$application_current_level_user_role = $this->checkUserRoleFromCurrentLevel($each_current_levels['current_level'], $each_current_levels['current_user_email_id']);

				if ($application_current_level_user_role == $search_user_role) {
					$application_customer_id_list[$i] = $each_current_levels['customer_id'];
					$i=$i+1;
				}
			}

		/*Start Yashwant 30/mar/2023 This function used for IN PRocess-Office-Filter  MULTI-SELECT*/
		} elseif ($search_application_type_id == ''  && $search_user_role != '' && $level_1_2_3_office != '' && $search_from_date == '' && $search_to_date == '') /*$search_user_email_id ==''*/
		{

			if ($search_application_type_id != '') {

				$application_customer_id = $this->$table->find('all')->where($conditions)->toArray();

				$i=0;
				foreach ($application_customer_id as $each_customer_id) {
					if (!empty($each_customer_id['customer_id'])) {
						$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

						if (in_array($application_customer_type,$search_application_type_id)) {
							$seach_application_customer_id_list[$i] = $each_customer_id['customer_id'];
							$i=$i+1;
						}
					}
				}
				$application_current_levels = $this->$table->find('all')->where(['customer_id IN' => $seach_application_customer_id_list])->toArray();

			} else {

				$application_current_levels = $this->$table->find('all')->where($conditions);
			}

			$i=0;
			foreach ($application_current_levels as $each_current_levels) {

				$application_current_level_user_role = $this->checkUserRoleFromCurrentLevel($each_current_levels['current_level'], $each_current_levels['current_user_email_id']);

				if ($application_current_level_user_role == $search_user_role) {

					$user_posted_office_id_xy = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email IS' => $each_current_levels['current_user_email_id']])->first();

					if (!empty($user_posted_office_id_xy)) {
						$user_posted_office_id_xyx = $user_posted_office_id_xy['posted_ro_office'];
					} else {
						$user_posted_office_id_xyx = '';
					}

					if (in_array($user_posted_office_id_xyx,$level_1_2_3_office)) {
						$application_customer_id_list[$i] = $each_current_levels['customer_id'];
						$i=$i+1;
					}
				}
			}

		/*=====Start Yashwant 31/mar/2023 This function used for IN PRocess-Search-from_date & Search_t-Date-Filter ======*/
		} elseif ($search_application_type_id == '' && $search_user_role == '' && $level_1_2_3_office == '' && $search_from_date != '' && $search_to_date != '') {

			if ($search_application_type_id != '') {

				$application_customer_id = $this->$table->find('all')->where($date_conditions)->bind(':start', $search_from_date, 'date')->bind(':end', $search_to_date, 'date')->toArray();

				$i=0;
				foreach ($application_customer_id as $each_customer_id) {
					if (!empty($each_customer_id['customer_id'])) {
						$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

						if (in_array($application_customer_type,$search_application_type_id)) {
							$application_customer_id_list[$i] = $each_customer_id['customer_id'];
							$i=$i+1;
						}
					}
				}

			} else {

				$application_customer_id_list = $this->$table->find('all')->select(['customer_id'])->where($date_conditions)->bind(':start', $search_from_date, 'date')->bind(':end', $search_to_date, 'date')->combine('id', 'customer_id')->toArray();
			}

		/*=====Start Yashwant 02/mar/2023 This function used for IN PRocess Report Count ======*/
		 } elseif(!empty($data_id)) {


			$application_customer_id = $this->$table->find('all')->where($conditions)->toArray();

			$i=0;
			foreach ($application_customer_id as $each_customer_id)
			{

				if (!empty($each_customer_id['customer_id']))
				{
					$application_customer_type = $this->Reportsfunctions->newApplicantType($each_customer_id['customer_id']);

					if (in_array($application_customer_type, $data_id, TRUE))

					{
						$application_customer_id_list[$i] = $each_customer_id['customer_id'];

						$i=$i+1;
					}

				}
			}

		} elseif (($search_application_type_id == '' || $search_application_type_id != '') && $search_user_role != '' && $level_1_2_3_office == '' && $search_from_date == '' && $search_to_date == '')
		{

			if ($search_application_type_id != '') {

				$application_customer_id = $this->$table->find('all')->where($conditions)->toArray();

				$i=0;
				foreach ($application_customer_id as $each_customer_id) {
					if (!empty($each_customer_id['customer_id'])) {
						$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

						if (in_array($application_customer_type,$search_application_type_id)) {
							$seach_application_customer_id_list[$i] = $each_customer_id['customer_id'];
							$i=$i+1;
						}
					}
				}

				$application_current_levels = $this->$table->find('all')->where(['customer_id IN' => $seach_application_customer_id_list])->toArray();

			} else {

				$application_current_levels = $this->$table->find('all')->where($conditions)->toArray();
			}

			$i=0;
			foreach ($application_current_levels as $each_current_levels) {

				$application_current_level_user_role = $this->checkUserRoleFromCurrentLevel($each_current_levels['current_level'], $each_current_levels['current_user_email_id']);

				if ($application_current_level_user_role == $search_user_role) {
					$application_customer_id_list[$i] = $each_current_levels['customer_id'];
					$i=$i+1;
				}
			}

		} elseif (($search_application_type_id == '' || $search_application_type_id != '') && $search_user_role != '' && $level_1_2_3_office != '' && $search_from_date == '' && $search_to_date == '' && $search_user_email_id =='')
		{

			if ($search_application_type_id != '') {

				$application_customer_id = $this->$table->find('all')->where($conditions)->toArray();

				$i=0;
				foreach ($application_customer_id as $each_customer_id) {
					if (!empty($each_customer_id['customer_id'])) {
						$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

						if (in_array($application_customer_type,$search_application_type_id)) {
							$seach_application_customer_id_list[$i] = $each_customer_id['customer_id'];
							$i=$i+1;
						}
					}
				}

				$application_current_levels = $this->$table->find('all')->where(['customer_id IN' => $seach_application_customer_id_list])->toArray();

			} else {

				$application_current_levels = $this->$table->find('all')->where($conditions);

			}

			$i=0;
			foreach ($application_current_levels as $each_current_levels) {

				$application_current_level_user_role = $this->checkUserRoleFromCurrentLevel($each_current_levels['current_level'], $each_current_levels['current_user_email_id']);

				if ($application_current_level_user_role == $search_user_role) {

					$user_posted_office_id_xy = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email IS' => $each_current_levels['current_user_email_id']])->first();

					if (!empty($user_posted_office_id_xy)) {
						$user_posted_office_id_xyx = $user_posted_office_id_xy['posted_ro_office'];
					} else {
						$user_posted_office_id_xyx = '';
					}

					if (in_array($user_posted_office_id_xyx,$level_1_2_3_office)) {
						$application_customer_id_list[$i] = $each_current_levels['customer_id'];
						$i=$i+1;
					}
				}
			}

		} elseif (($search_application_type_id == '' || $search_application_type_id != '') && $search_user_role != '' && $level_1_2_3_office != '' && $search_from_date == '' && $search_to_date == '' && $search_user_email_id !='')
		{

			if ($search_application_type_id != '') {

				$application_customer_id = $this->$table->find('all')->where($conditions)->toArray();

				$i=0;
				foreach ($application_customer_id as $each_customer_id) {

					if (!empty($each_customer_id['customer_id'])) {

						$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

						if (in_array($application_customer_type, $search_application_type_id)) {
							$seach_application_customer_id_list[$i] = $each_customer_id['customer_id'];
							$i=$i+1;
						}
					}
				}

				$application_current_levels = $this->$table->find('all')->where(['customer_id IN' => $seach_application_customer_id_list])->toArray();

			} else {

				$application_current_levels = $this->$table->find('all')->where($conditions)->toArray();
			}

			$i=0;
			foreach ($application_current_levels as $each_current_levels) {

				$application_current_level_user_role = $this->checkUserRoleFromCurrentLevel($each_current_levels['current_level'], $each_current_levels['current_user_email_id']);

				if ($application_current_level_user_role == $search_user_role) {

					$user_posted_office_id_xy = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email IS' => $each_current_levels['current_user_email_id']])->first();

					if (!empty($user_posted_office_id_xy)) {
						$user_posted_office_id_xyx = $user_posted_office_id_xy['posted_ro_office'];
					} else {
						$user_posted_office_id_xyx = '';
					}

					if (in_array($user_posted_office_id_xyx,$level_1_2_3_office)) {

						$search_user_email = $this->DmiUserRoles->find('all')->select(['user_email_id'])->where(['id IS' => $search_user_email_id])->first();

						if ($each_current_levels['current_user_email_id'] == $search_user_email['user_email_id']) {
							$application_customer_id_list[$i] = $each_current_levels['customer_id'];
							$i=$i+1;
						}
					}
				}
			}

		} elseif (($search_application_type_id == '' || $search_application_type_id != '') && $search_user_role != '' && $search_from_date != '' && $search_to_date != '' && $level_1_2_3_office !='' && ($search_user_email_id != '' || $search_user_email_id == ''))
		{

			if ($search_application_type_id != '') {

				$application_customer_id = $this->$table->find('all')->where($conditions)->toArray();

				$i=0;
				foreach ($application_customer_id as $each_customer_id) {

					if (!empty($each_customer_id['customer_id'])) {
						$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

						if (in_array($application_customer_type,$search_application_type_id)) {
							$seach_application_customer_id_list[$i] = $each_customer_id['customer_id'];
							$i=$i+1;
						}
					}
				}

				$application_current_levels = $this->$table->find('all')->where(['customer_id IN' => $seach_application_customer_id_list])
					->where(['date(modified) BETWEEN :start AND :end'])->bind(':start', $search_from_date, 'date')->bind(':end', $search_to_date, 'date')->toArray();

				} else {

				$application_current_levels = $this->$table->find('all')->where($date_conditions)->bind(':start', $search_from_date, 'date')
				->bind(':end', $search_to_date, 'date')->toArray();

			}

			$i=0;
			foreach ($application_current_levels as $each_current_levels) {

				$application_current_level_user_role = $this->checkUserRoleFromCurrentLevel($each_current_levels['current_level'],$each_current_levels['current_user_email_id']);

				if ($application_current_level_user_role == $search_user_role) {

					if ($level_1_2_3_office == '') {
						$level_1_2_3_office = [];
					}

					$user_posted_office_id_xy = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email IS' => $each_current_levels['current_user_email_id']])->first();

					if (!empty($user_posted_office_id_xy)) {
						$user_posted_office_id_xyx = $user_posted_office_id_xy['posted_ro_office'];
					} else {
						$user_posted_office_id_xyx = '';
					}

					if (in_array($user_posted_office_id_xyx,$level_1_2_3_office)) {

						$search_user_email = $this->DmiUserRoles->find('all')->select(['user_email_id'])->where(['id IS' => $search_user_email_id])->first();

						//to remove error empty condition added by laxmi B on 16-02-2023
						if ((!empty($each_current_levels['current_user_email_id']) && !empty($search_user_email['user_email_id']) )  && $each_current_levels['current_user_email_id'] == $search_user_email['user_email_id']) {
							$application_customer_id_list[$i] = $each_current_levels['customer_id'];
							$i=$i+1;
						}

					} else {
						$application_customer_id_list[$i] = $each_current_levels['customer_id'];
						$i=$i+1;
					}
				}
			}

		} else {
			
			$application_customer_id = $this->$table->find('all')->select(['customer_id'])->where($conditions)->extract('customer_id')->toArray();
			

			// $i=0;
			// foreach ($application_customer_id as $each_customer_id)
			// {
			// 		$application_customer_id_list[$i] = $each_customer_id['customer_id'];
			// 		$i=$i+1;
			// }
			// replaced foreach with query by Ankur
			$application_customer_id_list = $application_customer_id;
			
		}

		if (!empty($application_customer_id_list)) {
		
			$i=0;
			if ($pending_application_type = 'new') {
				
				foreach ($application_customer_id_list as $customer_id) {
					
					$customer_id_list = $this->DmiGrantCertificatesPdfs->find('all')->where(['customer_id IS' => $customer_id])->first();

					if (empty($customer_id_list)) {
						$final_customer_id_list[$i] = $customer_id;
						$i=$i+1;
					}
				}
				
				
			} 
			elseif ($pending_application_type = 'renewal') {

				foreach ($application_customer_id_list as $customer_id ) {
					
					$customer_id_list = $this->DmiRenewalFinalSubmits->find('all')->where(['customer_id IS' => $customer_id, 'status' => 'approved'])->first();

					if (empty($customer_id_list)) {
						$final_customer_id_list[$i] = $customer_id;
						$i=$i+1;
					}
				}

				$final_table = 'DmiRenewalFinalSubmits';
			}


			//if data same data id exist in rejcted table it is not apper in report added by laxmi B. on 20-01-2023
			$this->loadModel('DmiRejectedApplLogs');
			$rejectedList = $this->DmiRejectedApplLogs->find('all', array('fields'=>array('customer_id')))->order(['id' => 'DESC'])->toArray();//

			$reject_id = array();
			$i=0;
			if(!empty($rejectedList)){
				foreach($rejectedList as $reject){
					$reject_id[$i] = $reject['customer_id'];
					$i++;
				}

				if(!empty($final_customer_id_list)){
					$final_customer_id_list = array_diff($final_customer_id_list, $reject_id);

				}
			}//end by laxmi b.
		}


		return $final_customer_id_list;

	}



	// Pending Application Report Results
	// Description : function to used to create out result of pending new and renewal application report
	// @Author : Pravin Bhakare
	// #Contributer : Ankur Jangid (Migration)
	// Date : 18-09-2017

	public function pendingApplicationReportResults($current_users_details,$pending_application_type,$table) {

		$date = [];
		$user_list = null;
		$application_type = null;
		$user_roles = null;
		$user_office = null;
		$user_email_id =null;
		$application_id = null;

		if (!empty($current_users_details)) {

			$i=0;
			foreach ($current_users_details as $each_user) {

				$customer_id = $each_user['customer_id'];
				$each_user_detail = $each_user;
				$current_level = $each_user_detail['current_level'];
				$application_form_type = $this->Customfunctions->checkApplicantFormType($each_user_detail['customer_id']);

				if ($application_form_type == 'A') {
					$application_type[$i]='CA (Form-A)';
				} elseif ($application_form_type == 'B') {
					$application_type[$i]='Printing Press (Form-B)';
				} elseif ($application_form_type == 'C') {
					$application_type[$i]='Laboratory (Form-C)';
				} elseif ($application_form_type == 'D') {
					$application_type[$i]='Laboratory (Form-D)';
				} elseif ($application_form_type == 'E') {
					$application_type[$i]='CA (Form-E)';
				} elseif ($application_form_type == 'F') {
					$application_type[$i]='CA (Form-F)';
				}

				$date[$i] = $each_user_detail['modified'];
				$user_email_id[$i] = $each_user_detail['current_user_email_id'];
				$application_id[$i] = $each_user_detail['customer_id'];

				$user_posted_office_id=array();
				if (!empty($user_email_id[$i])) {
					$user_posted_office_id = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email IN' => $user_email_id[$i]])->first();

				}
				if (!empty($user_posted_office_id)) {
					$user_office[$i] = $this->DmiRoOffices->find('all')->select(['ro_office'])->where(['id' => $user_posted_office_id['posted_ro_office']])->first();

				}

				if (!empty($user_office[$i])) {
					$user_office[$i] = $user_office[$i]['ro_office'];
				} else {
					$user_office[$i] = '--';
				}

				$check_roles=array();
				if (!empty($user_email_id[$i])) {
					$check_roles = $this->DmiUserRoles->find('all')->where(['user_email_id IN' => $user_email_id[$i]])->first();
				}

				if (!empty($check_roles)) {
					$user_list[$i] = $check_roles;
				} else {
					$user_list[$i] = '---';
				}

				$user_roles[$i] = $this->checkUserRoleFromCurrentLevel($each_user_detail['current_level'],$each_user_detail['current_user_email_id']);
				$i=$i+1;

			}

		}

		$this->set('date',$date);
		$this->set('user_list',$user_list);
		$this->set('application_type',$application_type);
		$this->set('user_roles',$user_roles);
		$this->set('user_office',$user_office);
		$this->set('user_email_id',$user_email_id);
		$this->set('application_id',$application_id);

	}


	// Pending Back Application Report Results
	// Description :
	// @Author :
	// #Contributer : Ankur Jangid (Migration)
	// Date : 18-09-2017

	public function pendingBackApplicationReportResults($current_users_details,$table) {

		$date = [];
		$user_list = null;
		$application_type = null;
		$user_roles = null;
		$user_office = null;
		$user_email_id =null;
		$application_id = null;

		if (!empty($current_users_details)) {

			$i=0;
			foreach ($current_users_details as $each_user) {

				$customer_id = $each_user['customer_id'];
				$each_user_detail = $each_user;
				$current_level = $each_user_detail['current_level'];
				$application_form_type = $this->Customfunctions->checkApplicantFormType($each_user_detail['customer_id']);

				if ($application_form_type == 'A') {
					$application_type[$i]='CA (Form-A)';
				} elseif ($application_form_type == 'B') {
					$application_type[$i]='Printing Press (Form-B)';
				} elseif ($application_form_type == 'C') {
					$application_type[$i]='Laboratory (Form-C)';
				} elseif ($application_form_type == 'D') {
					$application_type[$i]='Laboratory (Form-D)';
				} elseif ($application_form_type == 'E') {
					$application_type[$i]='CA (Form-E)';
				} elseif ($application_form_type == 'F') {
					$application_type[$i]='CA (Form-F)';
				}

				$date[$i] = $each_user_detail['modified'];
				$user_email_id[$i] = $each_user_detail['current_user_email_id'];
				$application_id[$i] = $each_user_detail['customer_id'];

				$user_posted_office_id=array();

				if (!empty($user_email_id[$i])) {
					$user_posted_office_id = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email IN' => $user_email_id[$i]])->first();
				}

				if (!empty($user_posted_office_id)) {
					$user_office[$i] = $this->DmiRoOffices->find('all')->select(['ro_office'])->where(['id' => $user_posted_office_id['posted_ro_office']])->first();
				}

				if (!empty($user_office[$i])) {
					$user_office[$i] = $user_office[$i]['ro_office'];
				} else {
					$user_office[$i] = '--';
				}

				$check_roles=array();
				if (!empty($user_email_id[$i])) {
					$check_roles = $this->DmiUserRoles->find('all')->where(['user_email_id IN' => $user_email_id[$i]])->first();
				}

				if (!empty($check_roles)) {
					$user_list[$i] = $check_roles;
				} else {
					$user_list[$i] = '---';
				}

				$user_roles[$i] = $this->checkUserRoleFromCurrentLevel($each_user_detail['current_level'],$each_user_detail['current_user_email_id']);
				$i=$i+1;
			}

		}

		$this->set('date',$date);
		$this->set('user_list',$user_list);
		$this->set('application_type',$application_type);
		$this->set('user_roles',$user_roles);
		$this->set('user_office',$user_office);
		$this->set('user_email_id',$user_email_id);
		$this->set('application_id',$application_id);

	}





	// Download Pending Application Report
	// Description : ----
	// @Author : Pravin Bhakare
	// #Contributer : Ankur Jangid (Migration)
	// Date : ----

	public function downloadPendingApplicationReport($download_pending_application,$pending_application_type,$table) {

		$this->viewBuilder()->setLayout('downloadpdf');

		$date = [];
		$user_list = null;
		$application_type =null;
		$user_roles = null;
		$user_office = null;
		$user_email_id =null;
		$application_id = null;

		if (!empty($download_pending_application)) {

			$i=0;
			foreach ($download_pending_application as $each_user) {

				$customer_id = $each_user['customer_id'];
				$each_user_detail = $each_user;
				$current_level = $each_user_detail['current_level'];
				$application_form_type = $this->Customfunctions->checkApplicantFormType($each_user_detail['customer_id']);

				if ($application_form_type == 'A') {
					$application_type[$i]='CA (Form-A)';
				} elseif ($application_form_type == 'B') {
					$application_type[$i]='Printing Press (Form-B)';
				} elseif ($application_form_type == 'C') {
					$application_type[$i]='Laboratory (Form-C)';
				} elseif ($application_form_type == 'D') {
					$application_type[$i]='Laboratory (Form-D)';
				} elseif ($application_form_type == 'E') {
					$application_type[$i]='CA (Form-E)';
				} elseif ($application_form_type == 'F') {
					$application_type[$i]='CA (Form-F)';
				}

				$date[$i] = $each_user_detail['modified'];
				$user_email_id[$i] = $each_user_detail['current_user_email_id'];
				$application_id[$i] = $each_user_detail['customer_id'];

				$user_posted_office_id = array();
				if(!empty($user_email_id[$i])){
					$user_posted_office_id = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email IN' => $user_email_id[$i]])->first();
				}

				if (!empty($user_posted_office_id)) {
					$user_office[$i] = $this->DmiRoOffices->find('all')->select(['ro_office'])->where(['id' => $user_posted_office_id['posted_ro_office']])->first();
				}

				if (!empty($user_office[$i])) {
					$user_office[$i] = $user_office[$i]['ro_office'];
				} else {
					$user_office[$i] = '--';
				}

				if(!empty($user_email_id[$i])){
					$check_roles = $this->DmiUserRoles->find('all')->where(['user_email_id IS' => $user_email_id[$i]])->first();
				}

				$user_email_id[$i] = base64_decode($user_email_id[$i]);//for email encoding

				if (!empty($check_roles)) {
					$user_list[$i] = $check_roles;
				} else {
					$user_list[$i] = '---';
				}

				$user_roles[$i] = $this->checkUserRoleFromCurrentLevel($each_user_detail['current_level'],$each_user_detail['current_user_email_id']);
				$i=$i+1;
			}
		}

		$this->set('orders',$date);
		$this->set('user_list',$user_list);
		$this->set('application_type',$application_type);
		$this->set('user_roles',$user_roles);
		$this->set('user_office',$user_office);
		$this->set('user_email_id',$user_email_id);
		$this->set('application_id',$application_id);

		$this->layout = null;
		$this->autoLayout = false;
		Configure::write('debug', '0');
		$this->render('/element/download_report_excel_format/download_pending_application_report');


	}





	// Approved New Application Type / Approved Renewal Application Type
	// Description : For AQCMS Stats
	// @Author : Pravin Bhakare
	// #Contributer : Ankur Jangid (Migration)
	// Date : ----

	//newapp_id replace ->cert_type By Shreeya
	public function approvedNewApplicationTypeForStats($cert_type)
	{
		$this->Session->write('approved_application_type','new');
		$this->redirect('/reports/approved_applications_report_for_stats/'.$cert_type);
	}

	public function approvedRenewalApplicationTypeForStats()
	{
		$this->Session->write('approved_application_type','renewal');
		$this->redirect('/reports/approved_applications_report_for_stats');
	}

	// This function is added to show all the approved application on 16-06-2022
	public function approvedAllApplicationTypeForStats()
	{
		$this->Session->write('approved_application_type','all_reports');
		$this->redirect('/reports/approved_applications_report_for_stats');
	}


	// Approved New Application Type / Approved Renewal Application Type
	// Description : For Normal Listing
	// @Author : Pravin Bhakare
	// #Contributer : Ankur Jangid (Migration)
	// Date : 28-04-2023

	public function approvedNewApplicationType()
	{
		$this->Session->write('approved_application_type','new');
		$this->redirect('/reports/approved_applications_report');
	}

	public function approvedRenewalApplicationType()
	{
		$this->Session->write('approved_application_type','renewal');
		$this->redirect('/reports/approved_applications_report');
	}

	// This function is added to show all the approved application on 16-06-2022
	public function approvedAllApplicationType()
	{
		$this->Session->write('approved_application_type','all_reports');
		$this->redirect('/reports/approved_applications_report');
	}



	// Approved Applications Report
	// Description : ----
	// @Author : Pravin Bhakare
	// #Contributer : Yashwant & Shreeya
	// Date : 25-05-2023

	public function approvedApplicationsReportForStats($cert_type) // $newappId replace $cert_type by Shreeya
	{
		
		// $newappId replace $cert_type by Shreeya
		$cert_type=base64_decode($cert_type);
		$data_id =array($cert_type);

		//added by laxmi on 15-02-2023
		$aqcms_from_date ='' ;
		$aqcms_to_date = '';
		$aqcms_ro_office_short_code = '';
		$aqcms_ro_office_id = '';

		$approved_application_type = $this->Session->read('approved_application_type');

		if ($approved_application_type == 'new' || $approved_application_type =='') {

			$table = 'DmiFinalSubmits';
			$report_heading = 'Approved New Applications Report';

		}
		// elseif ($approved_application_type == 'renewal') {

		// 	$table = 'DmiRenewalFinalSubmits';
		// 	$report_heading = 'Approved Renewal Applications Report';

		// }
		elseif ($approved_application_type == 'all_reports') {

			$table = 'DmiGrantCertificatesPdfs';
			$report_heading = 'All Approved Report';

			// this below code is added to show the deafult office by Akash on 16-06-2022
			$posted_ro_office = $this->DmiUsers->find('all',array('fields'=>'posted_ro_office', 'conditions'=>array('email IS'=>$_SESSION['username'])))->first();
			$default_ro_office = $this->DmiRoOffices->find('all',array('conditions'=>array('id IS'=>$posted_ro_office['posted_ro_office'])))->first();
			$this->set('default_ro_office',$default_ro_office['ro_office']);
		}

		//************************New Code Added for show count list by Shreeya Date [25-05-2023]**************************************************************************

		//check the which certificate type is present
		if ($cert_type== 'CA')
		{
			$cert_type = 1;
		}
		elseif ($cert_type== 'PP')
		{
			$cert_type = 2;
		}
		elseif ($cert_type== 'LAB')
		{
			$cert_type = 3;
		}


		//check the which application type is present
		if($approved_application_type=='new'){
			$processFunction = 'new_app_processed';
		}elseif($approved_application_type=='renewal'){
			$processFunction = 'renewal_app_processed';
		}elseif($approved_application_type=='backlog'){
			$processFunction = 'backlog_app_processed';
		}


		//show the count according to application type  and cutomer_id
		$searchConditions = array();
		$application_processed[] = $this->Reportstatistics->$processFunction($searchConditions,null,null,$cert_type,$approved_application_type);
		$applListToShow = $application_processed[0][3];


		$date=array();
		$application_type=array();
		$application_user_email_id=array();
		$user_office=array();
		$application_customer_id=array();
		$name_of_the_firm=array();
		$address_of_the_firm=array();
		$contact_details_of_the_firm=array();
		$approved_TBL_details_tbl_name=array();
		$approved_TBL_details_tbl_registered_no=array();
		$laboratory_details_name=array();
		$laboratory_details_address=array();


		$i=0;
			//applied array_unique function on 18-07-2019
			foreach (array_unique($applListToShow) as $approved_application) {

				$approved_application_details = array(); //this line added on 18-07-2019

				// THIS BELOW CONDITION IS ADDED FOR THE ALL REPORTS BY AKASH ON 16-06-2022
				if ($approved_application_type == 'all_reports') {
					$approved_application_details = $this->DmiGrantCertificatesPdfs->find('all',array('conditions'=>array('customer_id IS'=>$approved_application),'order' => array('id' => 'desc')))->first();
				} elseif ($approved_application_type == 'new' || $approved_application_type =='') {
					$approved_application_details = $this->DmiGrantCertificatesPdfs->find('all')->where(['customer_id' => $approved_application])->first();
				} elseif ($approved_application_type == 'backlog') {
					$approved_application_details = $this->DmiFinalSubmits->find('all')->where(['customer_id' => $approved_application,'status'=>'approved','current_level'=>'level_3'])->first();
				} elseif ($approved_application_type == 'renewal') {
					$approved_application_detail = $this->DmiGrantCertificatesPdfs->find('all')->select(['id'])->where(['customer_id IS'=>$approved_application])->combine('id','id')->toArray();
					//applied this condition on 27-04-2019
					if (!empty($approved_application_detail)) {
						$approved_application_details = $this->DmiGrantCertificatesPdfs->find('all')->where(['id IN'=> $approved_application_detail])->order(['id' => 'DESC'])->first();
					}
				}

				//this condition added on 18-07-2019
				if (!empty($approved_application_details)) {

					$approved_application_result = $approved_application_details;

					//to check if the application is old or not to print on the excel and for viewing part dont by Akash 07-04-2022

					// THIS BELOW CONDITION IS ADDED FOR THE ALL REPORTS BY AKASH ON 16-06-2022
					if ($approved_application_type == 'all_reports') {

						if ($approved_application_result['pdf_version'] > '1') {
							$approved_application_type_text[$i] = "<b>RENEWAL</b>";
						} elseif ($approved_application_result['user_email_id'] == 'old_application') {
							$approved_application_type_text[$i] = "<i>OLD</i>";
						} else {
							$approved_application_type_text[$i] = "NEW";
						}

					} else {

						if ($approved_application_type == 'renewal') {
							$approved_application_type_text[$i] = "<b>RENEWAL</b>";
						} elseif ($approved_application_result['user_email_id'] == 'old_application') {
							$approved_application_type_text[$i] = "<i>OLD</i>";
						} else {
							$approved_application_type_text[$i] = "NEW";
						}

					}


					if ($approved_application_result['user_email_id'] == 'old_application') {
						$old_app_approved_by = $this->Customfunctions->old_app_approved_by($approved_application_result['customer_id']);
						$approved_application_result['user_email_id'] = $old_app_approved_by;
					}

					$explode = explode("/",$approved_application_result['customer_id']);

					$approved_office_id = $this->DmiRoOffices->find('all')->select(['ro_email_id'])->where(['short_code' => $explode[2]])->first();

					$user_posted_office_id = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email'=>$approved_office_id['ro_email_id']])->first();

					if (!empty($user_posted_office_id)) {

						$user_office_details = $this->DmiRoOffices->find('all')->select(['ro_office'])->where(['id'=>$user_posted_office_id['posted_ro_office']])->first();

						if (!empty($user_office_details)) {
							$user_office[$i] = $user_office_details['ro_office'];
						} else {
							$user_office[$i] = 'N/A';
						}
					} else {
						$user_office[$i] = 'N/A';
					}

					$application_form_type = $this->Customfunctions->checkApplicantFormType($approved_application_result['customer_id']);

					if ($application_form_type == 'A') {
						$application_type[$i]='CA (Form-A)';
					} elseif ($application_form_type == 'B') {
						$application_type[$i]='Printing Press (Form-B)';
					} elseif ($application_form_type == 'C') {
						$application_type[$i]='Laboratory (Form-C)';
					} elseif ($application_form_type == 'D') {
						$application_type[$i]='Laboratory (Form-D)';
					} elseif ($application_form_type == 'E') {
						$application_type[$i]='CA (Form-E)';
					} elseif ($application_form_type == 'F') {
						$application_type[$i]='CA (Form-F)';
					}

					$date[$i] = $approved_application_result['created'];
					$application_customer_id[$i] = $approved_application_result['customer_id'];

					//added by the akash on 13-11-2021
					$firmDetails = $this->DmiFirms->find('all',array('conditions'=>array('customer_id IS'=>$approved_application_result['customer_id'])))->first();

					$name_of_the_firm[$i] = $firmDetails['firm_name'];
					$address_of_the_firm[$i] = $firmDetails['street_address'];
					$contact_details_of_the_firm[$i] = base64_decode($firmDetails['email']);
					$phoneno[$i] = $firmDetails['mobile_no'];

					//tbl details
					$tbl_details = $this->DmiAllTblsDetails->find('all',array('conditions'=>array('customer_id IS'=>$approved_application_result['customer_id'],'OR' => array('delete_status IS NULL', 'delete_status' => 'no'))))->toArray();

					if (!empty($tbl_details)) {
						$j=0;
						foreach ($tbl_details as $each) {

							$approved_TBL_details_tbl_name[$i][$j] = $each['tbl_name'];
							$approved_TBL_details_tbl_registered_no[$i][$j] = $each['tbl_registered_no'];
							$j++;
						}

					} else {
						$approved_TBL_details_tbl_name[$i][0] = 'N/A';
						$approved_TBL_details_tbl_registered_no[$i][0] = 'N/A';
					}



					//lab details
					$lab_details = $this->DmiCustomerLaboratoryDetails->find('all',array('conditions'=>array('customer_id IS'=>$approved_application_result['customer_id'])))->toArray();
					if (!empty($lab_details)) {
						$laboratory_details_name[$i] = $lab_details[0]['laboratory_name'];
						$laboratory_details_address[$i] = $lab_details[0]['street_address'];
					} else {
						$laboratory_details_name[$i] = 'N/A';
						$laboratory_details_address[$i] = 'N/A';
					}

					$commodity_value = $this->DmiFirms->find('all')->select(['sub_commodity'])->where(['customer_id'=>$approved_application_result['customer_id']])->first();

					$commodity_list[$i] = $this->Customfunctions->showCommdityInApplList($commodity_value['sub_commodity']);


					//Added this Code on the 08-04-2022 as the base encoding was not on some emails so to dispaly the email , checking if the encoding is needed or not.
					$checkEmailForEncoding[$i] = $approved_application_result['user_email_id'];

					if($this->isBase64Encoded($checkEmailForEncoding[$i]) == true){
						$application_user_email_id[$i] = base64_decode($approved_application_result['user_email_id']);
					} else {
						$application_user_email_id[$i] = $approved_application_result['user_email_id'];
					}

					//check the expiry dateand print to the reports  added by Akash on 24-05-2022
					$grant_date = chop($approved_application_details['date'],"00:00:00");
					$valid_upto[$i] = $this->Customfunctions->getCertificateValidUptoDate($approved_application_result['customer_id'],$grant_date);

					//check the state name added by akash on 14-06-2022
					$state_name[$i] = $this->getStateName($approved_application_result['customer_id']);

					//Certificate Issued on
					$issued_on[$i] = chop($approved_application_result['date'],"00:00:00");

					$i=$i+1;
				}
			}



		$this->set('date',$date);
		$this->set('application_customer_id',$application_customer_id);
		$this->set('application_user_email_id',$application_user_email_id);
		$this->set('application_type',$application_type);
		$this->set('user_office',$user_office);
		//$this->set('approved_application_list',$approved_application_list);
		$this->set('commodity_list',$commodity_list);
		$this->set('approved_application_type',$approved_application_type_text);
		$this->set('name_of_the_firm',$name_of_the_firm);
		$this->set('address_of_the_firm',$address_of_the_firm);
		$this->set('contact_details_of_the_firm',$contact_details_of_the_firm);
		$this->set('approved_TBL_details_tbl_name',$approved_TBL_details_tbl_name);
		$this->set('approved_TBL_details_tbl_registered_no',$approved_TBL_details_tbl_registered_no);
		$this->set('laboratory_details_name',$laboratory_details_name);
		$this->set('laboratory_details_address',$laboratory_details_address);
		$this->set('valid_upto',$valid_upto);
		$this->set('state_name',$state_name);
		$this->set('phoneno',$phoneno);
		$this->set('issued_on',$issued_on);


		//*****************End************************************

		$this->set('table', $table);	// set table value ( Done by pravin 16-07-2018)
		$this->set('report_heading', $report_heading);

		$application_type_xy = array('A'=>'CA (Form-A)', 'C'=>'Laboratory (Form-C)', 'E'=>'CA (Form-E)', 'B'=>'Printing Press (Form-B)', 'D'=>'Laboratory (Form-D)', 'F'=>'CA (Form-F)');
		// $newappId replace $cert_type by Shreeya date [25-05-2023]
		if($cert_type=='CA')
		{
			$application_type_xy = array('A'=>'CA (Form-A)','E'=>'CA (Form-E)','F'=>'CA (Form-F)');
		}
		elseif($cert_type=='PP')
		{
			$application_type_xy = array('B'=>'Printing Press (Form-B)');
		}
		elseif ($cert_type=='LAB') {
			$application_type_xy = array('C'=>'Laboratory (Form-C)','D'=>'Laboratory (Form-D)');
		}


		//Change on 9/11/2018, Sorting array by ascending order - By Pravin Bhakare
		if(!empty($application_type_xy)){
			asort($application_type_xy);
		}

		$this->set('application_type_xy',$application_type_xy);
		// $newappId replace $cert_type by Shreeya date [25-05-2023]
		$this->set('cert_type',$cert_type);

		//added 'office_type'=>'RO' condition on 27-07-2018   // Change on 3/11/2018 -  add order by condition - by Pravin Bhakare
		$ro_office = $this->DmiRoOffices->find('all')->select(['id', 'ro_office'])->where(['office_type' => 'RO','delete_status IS NULL'])->order(['ro_office' => 'ASC'])->combine('id', 'ro_office')->toArray();
		$this->set('ro_office',$ro_office);

		$search_application_type_id = $this->Session->read('search_application_type_id');
		$application_approved_office = $this->Session->read('application_approved_office');
		$search_from_date = $this->Session->read('search_from_date');
		$search_to_date = $this->Session->read('search_to_date');

		$this->set('search_application_type_id',$search_application_type_id);
		$this->set('application_approved_office',$application_approved_office);
		$this->set('search_from_date',$search_from_date);
		$this->set('search_to_date',$search_to_date);

		$search_flag = 'off'; // added by Ankur
		// Set default value for download report click event (Done by pravin 13-03-2018)
		$download_report = 'no';

		//if from to date and office id in  session condtion added by laxmi B on 15-02-2023

		$aqcms_from_date = $this->Session->read('from_date') ;
		$aqcms_to_date = $this->Session->read('to_date');
		$aqcms_ro_office_short_code = $this->Session->read('roOfficeShortCode');
		$aqcms_ro_office_id = $this->Session->read('ro_office_id');
		$this->Session->delete('ro_office_id');
		$this->Session->delete('roOfficeShortCode');
		$this->Session->delete('from_date');
		$this->Session->delete('to_date');



		if ((((!empty($aqcms_from_date && !empty($aqcms_to_date))) || !empty($aqcms_ro_office_short_code))) || null != ($this->request->getData('search_logs')) || null != ($this->request->getData('download_report')))
		{

			$search_flag = 'on'; // added by Ankur
			//Check not empty "Download Report as Excel" button Request, if condition TRUE then set value "yes" for "Download Report as Excel" click event
			//and pass this value to "approved_application_search_conditions" function (Done by pravin 13-03-2018)
			if (!empty($this->request->getData('download_report'))) {
				$download_report = 'yes';
			}

			$search_application_type_id = $this->request->getData('application_type');
			$application_approved_office = $this->request->getData('office');
			$search_from_date =  $this->request->getData('from_date');
			$search_from_date = $this->Customfunctions->dateFormatCheck($search_from_date);
			$search_to_date =  $this->request->getData('to_date');
			$search_to_date = $this->Customfunctions->dateFormatCheck($search_to_date);
			$this->date_comparison($search_from_date, $search_to_date);

			// Change on 3/11/2018 - For download excel report, Take search filter field value from session variables instend of POST variable - By Pravin
			if ($download_report == 'yes') {

				$search_application_type_id = $this->Session->read('search_application_type_id');
				$application_approved_office = $this->Session->read('application_approved_office');
				$search_from_date = $this->Session->read('search_from_date');
				$search_to_date = $this->Session->read('search_to_date');
			}

			$this->Session->delete('search_application_type_id');
			$this->Session->delete('application_approved_office');
			$this->Session->delete('search_from_date');
			$this->Session->delete('search_to_date');



			//set from_date and to_date and office name from session of statstics report added by laxmi B. on 15-02-2023
			if(!empty($aqcms_from_date) && !empty($aqcms_to_date && empty($aqcms_ro_office_short_code) )){
				$search_from_date = $aqcms_from_date;
				$search_to_date = $aqcms_to_date;
				$search_from_date = $this->Customfunctions->dateFormatCheck($search_from_date);
				$search_to_date = $this->Customfunctions->dateFormatCheck($search_to_date);
				$this->date_comparison($search_from_date, $search_to_date);

			}elseif(!empty($aqcms_from_date) && !empty($aqcms_to_date && !empty($aqcms_ro_office_short_code))){


				$search_from_date = $aqcms_from_date;
				$search_to_date = $aqcms_to_date;
				$search_from_date = $this->Customfunctions->dateFormatCheck($search_from_date);
				$search_to_date = $this->Customfunctions->dateFormatCheck($search_to_date);
				$this->date_comparison($search_from_date, $search_to_date);

				$ro_office = $this->DmiRoOffices->find('all')->select(['id'])->where(['id IN' => $aqcms_ro_office_id])->where(['short_code IN'=>$aqcms_ro_office_short_code])->first();
				$application_approved_office = [$ro_office['id']];


			} elseif(!empty($aqcms_ro_office_short_code)){
				$ro_office = $this->DmiRoOffices->find('all')->select(['id'])->where(['id IN' => $aqcms_ro_office_id])->where(['short_code IN'=>$aqcms_ro_office_short_code])->first();
				$application_approved_office = [$ro_office['id']];
			}//end


			$this->Session->write('search_application_type_id', $search_application_type_id);
			$this->Session->write('application_approved_office', $application_approved_office);
			$this->Session->write('search_from_date', $search_from_date);
			$this->Session->write('search_to_date', $search_to_date);

			$this->set('search_application_type_id', $search_application_type_id);
			$this->set('application_approved_office', $application_approved_office);
			$this->set('search_from_date', $search_from_date);
			$this->set('search_to_date', $search_to_date);

			$approved_application_lists = $this->approvedApplicationSearchConditions($search_application_type_id, $application_approved_office, $search_from_date, $search_to_date, $table, $search_flag,$data_id,$approved_application_type,$selected_month=Null);/*add new parameter by shreeya*/
			$approved_application_list = $approved_application_lists[0];
			$download_approved_application_list = $approved_application_lists[1];

			$i=0;
			foreach ($approved_application_list as $each) {

				$approved_application_list[$i] = $each['customer_id'];
				$i=$i+1;
			}

			$j=0;
			foreach ($download_approved_application_list as $each) {

				$download_approved_application_list[$j] = $each['customer_id'];
				$j=$j+1;
			}
			//if data same data id exist in rejcted table it is not apper in report added by laxmi B. on 20-01-2023

			$this->loadModel('DmiRejectedApplLogs');
			$rejectedList = $this->DmiRejectedApplLogs->find('all')->select(['id','customer_id'])->order(['id','customer_id'])->combine('id','customer_id')->toArray();

			if(!empty($rejectedList)){
				if(!empty($approved_application_list)){
					$approved_application_list = array_diff($approved_application_list, $rejectedList);
				}
			}//end laxmi B.


			//Fetch the all data that required for creating the downloading report as execel (Done by pravin 14-03-2018)
			if ($download_report == 'yes') {

				$this->downloadApprovedApplicationReportResults($download_approved_application_list, $approved_application_type);
			}

			$this->approvedApplicationReportResults($approved_application_list, $approved_application_type);

		} else {


			$approved_application_lists = $this->approvedApplicationSearchConditions($search_application_type_id, $application_approved_office, $search_from_date, $search_to_date, $table, $search_flag,$data_id,$approved_application_type,$selected_month=Null);/*add new parameter by shreeya*/

			$approved_application_list = $approved_application_lists[0];

			$i=0;
			foreach ($approved_application_list as $each) {
				$approved_application_list[$i] = $each['customer_id'];
				$i=$i+1;
			}
			 //if data same data id exist in rejcted table it is not apper in report added by laxmi B. on 20-01-2023
			$this->loadModel('DmiRejectedApplLogs');
			$rejectedList = $this->DmiRejectedApplLogs->find('all')->select(['id','customer_id'])->order(['id','customer_id'])->combine('id','customer_id')->toArray();

			if(!empty($rejectedList)){

				if(!empty($approved_application_list)){
					$approved_application_list = array_diff($approved_application_list, $rejectedList);
				}
			}//end laxmi B.

			//below query commented by shreeya for display list of granted new application already added this code not use this function
			//$this->approvedApplicationReportResults($approved_application_list,$approved_application_type);

		}

	}


	// Approved Applications Report
	// Description : ----
	// @Author : Pravin Bhakare
	// #Contributer : Ankur Jangid (Migration)
	// Date : ----

	public function approvedApplicationsReport() {

	
		$approved_application_type = $this->Session->read('approved_application_type');

		if ($approved_application_type == 'new' || $approved_application_type =='') {

			$table = 'DmiFinalSubmits';
			$report_heading = 'Approved New Applications Report';

		} elseif ($approved_application_type == 'renewal') {

			$table = 'DmiRenewalFinalSubmits';
			$report_heading = 'Approved Renewal Applications Report';

		}elseif ($approved_application_type == 'all_reports') {

			$table = 'DmiGrantCertificatesPdfs';
			$report_heading = 'All Approved Report';

			// this below code is added to show the deafult office by Akash on 16-06-2022
			$posted_ro_office = $this->DmiUsers->find('all',array('fields'=>'posted_ro_office', 'conditions'=>array('email IS'=>$_SESSION['username'])))->first();
			$default_ro_office = $this->DmiRoOffices->find('all',array('conditions'=>array('id IS'=>$posted_ro_office['posted_ro_office'])))->first();
			$this->set('default_ro_office',$default_ro_office['ro_office']);
		}

		$this->set('table', $table);	// set table value ( Done by pravin 16-07-2018)
		$this->set('report_heading', $report_heading);

		$application_type_xy = array('A'=>'CA (Form-A)','C'=>'Laboratory (Form-C)','E'=>'CA (Form-E)','B'=>'Printing Press (Form-B)','D'=>'Laboratory (Form-D)','F'=>'CA (Form-F)');

		//Change on 9/11/2018, Sorting array by ascending order - By Pravin Bhakare
		asort($application_type_xy);
		$this->set('application_type_xy', $application_type_xy);

		//added 'office_type'=>'RO' condition on 27-07-2018   // Change on 3/11/2018 -  add order by condition - by Pravin Bhakare
		$ro_office = $this->DmiRoOffices->find('all')->select(['id', 'ro_office'])->where(['office_type' => 'RO','delete_status IS NULL'])->order(['ro_office' => 'ASC'])->combine('id', 'ro_office')->toArray();
		$this->set('ro_office',$ro_office);

		$search_application_type_id = $this->Session->read('search_application_type_id');
		$application_approved_office = $this->Session->read('application_approved_office');
		$search_from_date = $this->Session->read('search_from_date');
		$search_to_date = $this->Session->read('search_to_date');

		$this->set('search_application_type_id',$search_application_type_id);
		$this->set('application_approved_office',$application_approved_office);
		$this->set('search_from_date',$search_from_date);
		$this->set('search_to_date',$search_to_date);

		$search_flag = 'off'; // added by Ankur
		// Set default value for download report click event (Done by pravin 13-03-2018)
		$download_report = 'no';

		if (null != ($this->request->getData('search_logs')) || null != ($this->request->getData('download_report'))) {

			$search_flag = 'on'; // added by Ankur
			//Check not empty "Download Report as Excel" button Request, if condition TRUE then set value "yes" for "Download Report as Excel" click event
			//and pass this value to "approved_application_search_conditions" function (Done by pravin 13-03-2018)
			if (!empty($this->request->getData('download_report'))) {
				$download_report = 'yes';
			}

			$search_application_type_id = $this->request->getData('application_type');
			$application_approved_office = $this->request->getData('office');
			$search_from_date =  $this->request->getData('from_date');
			$search_from_date = $this->Customfunctions->dateFormatCheck($search_from_date);
			$search_to_date =  $this->request->getData('to_date');
			$search_to_date = $this->Customfunctions->dateFormatCheck($search_to_date);
			$this->date_comparison($search_from_date, $search_to_date);

			// Change on 3/11/2018 - For download excel report, Take search filter field value from session variables instend of POST variable - By Pravin
			if ($download_report == 'yes') {

				$search_application_type_id = $this->Session->read('search_application_type_id');
				$application_approved_office = $this->Session->read('application_approved_office');
				$search_from_date = $this->Session->read('search_from_date');
				$search_to_date = $this->Session->read('search_to_date');
			}

			$this->Session->delete('search_application_type_id');
			$this->Session->delete('application_approved_office');
			$this->Session->delete('search_from_date');
			$this->Session->delete('search_to_date');

			$this->Session->write('search_application_type_id', $search_application_type_id);
			$this->Session->write('application_approved_office', $application_approved_office);
			$this->Session->write('search_from_date', $search_from_date);
			$this->Session->write('search_to_date', $search_to_date);

			$this->set('search_application_type_id', $search_application_type_id);
			$this->set('application_approved_office', $application_approved_office);
			$this->set('search_from_date', $search_from_date);
			$this->set('search_to_date', $search_to_date);

			$approved_application_lists = $this->approvedApplicationSearchConditions($search_application_type_id, $application_approved_office, $search_from_date, $search_to_date, $table, $search_flag,$selected_month=Null);/*add new parameter by shreeya*/
			$approved_application_list = $approved_application_lists[0];
			$download_approved_application_list = $approved_application_lists[1];

			$i=0;
			foreach ($approved_application_list as $each) {

				$approved_application_list[$i] = $each['customer_id'];
				$i=$i+1;
			}

			$j=0;
			foreach ($download_approved_application_list as $each) {

				$download_approved_application_list[$j] = $each['customer_id'];
				$j=$j+1;
			}

			//Fetch the all data that required for creating the downloading report as execel (Done by pravin 14-03-2018)
			if ($download_report == 'yes') {

				$this->downloadApprovedApplicationReportResults($download_approved_application_list, $approved_application_type);
			}

			$this->approvedApplicationReportResults($approved_application_list, $approved_application_type);

		} else {

			$approved_application_lists = $this->approvedApplicationSearchConditions($search_application_type_id, $application_approved_office, $search_from_date, $search_to_date, $table, $search_flag,$selected_month=Null);/*add new parameter by shreeya*/

			$approved_application_list = $approved_application_lists[0];

			$i=0;
			foreach ($approved_application_list as $each) {
				$approved_application_list[$i] = $each['customer_id'];
				$i=$i+1;
			}

			$this->approvedApplicationReportResults($approved_application_list,$approved_application_type);
		}

	}


	// Approved Application Search Conditions
	// Description : ----
	// @Author : Pravin Bhakare
	// #Contributer : Ankur Jangid (Migration)
	// Date : ----

	public function approvedApplicationSearchConditions ($search_application_type_id, $application_approved_office, $search_from_date, $search_to_date, $table, $search_flag,$data_id=null,$approved_application_type=null,$selected_month=Null)/*add new parameter by shreeya*/
	{

		$approved_application_list = [];

		if ($search_application_type_id != '' && $application_approved_office == '' && $search_from_date =='' && $search_to_date == '') {

			if ($table == 'DmiFinalSubmits') {
				$approved_application_customer_id = $this->DmiGrantCertificatesPdfs->find('all')->select(['customer_id'])->group(['customer_id HAVING COUNT(customer_id) < 2'])->toArray();
			} elseif ($table == 'DmiGrantCertificatesPdfs') {
				$approved_application_customer_id = $this->DmiGrantCertificatesPdfs->find('all')->select(['customer_id'])->group(['customer_id HAVING COUNT(customer_id) >= 1'])->toArray();
			} else {
				$approved_application_customer_id = $this->DmiGrantCertificatesPdfs->find('all')->select(['customer_id'])->group(['customer_id HAVING COUNT(customer_id) > 1'])->toArray();
			}

			$i=0;
			foreach ($approved_application_customer_id as $each_customer_id)	{
				$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

				if (in_array($application_customer_type, $search_application_type_id)) {
					$approved_application_list[$i] = $each_customer_id['customer_id'];
					$i=$i+1;
				}
			}

			if (!empty($approved_application_list)) {
				$conditions = ['customer_id IN' => $approved_application_list, 'status' => 'approved', 'current_level' => 'level_3'];
			} else {
				$conditions = ['customer_id IS' => '', 'status' => 'approved', 'current_level' => 'level_3'];
			}

			$approved_application_list = $this->$table->find('all')->where($conditions)->order(['id' => 'DESC'])->toArray();

			$download_approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();
		}
		///===========================YAshwant 05-Apr-2023====================================
		elseif ($search_application_type_id == '' && $application_approved_office == '' && $search_from_date !='' && $search_to_date != '')
		{

			$approved_application_customer_id = $this->DmiApplicationEsignedStatuses->find('all')->select(['customer_id'])->where(['application_status' =>"Granted"])->where(['date(modified) BETWEEN :start AND :end'])
			->bind(':start', $search_from_date, 'date')->bind(':end', $search_to_date, 'date')->order(['created'=>'DESC'])->toArray();

			//echo"<pre>";print_r($approved_application_customer_id);exit;


			$i=0;
			foreach ($approved_application_customer_id as $each_customer_id)
			{

				if (!empty($each_customer_id['customer_id']))
				{
					$application_customer_type = $this->Reportsfunctions->newApplicantType($each_customer_id['customer_id']);

					if(in_array($application_customer_type, $data_id, TRUE))
					{
						$approved_application_list[$i] = $each_customer_id['customer_id'];
						$i=$i+1;
					}
				}
			}

			if (!empty($approved_application_list))
			{
				$conditions = ['customer_id IN' => $approved_application_list];
			} else {
				$conditions = ['customer_id IS' => '', 'status' => 'approved', 'current_level' => 'level_3'];
			}

			$approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

			$download_approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();


		}
		///==========YAshwant 05-Apr-2023 END Search FRom-To filter ====================================

		///==========YAshwant 05-Apr-2023 START Search Select Office Filter filter ==================

		elseif ($search_application_type_id == '' && $application_approved_office != '' && $search_from_date =='' && $search_to_date == '' )
		{

			//$approved_application_customer_id = $this->$table->find('all')->select(['customer_id'])->where(['status' => 'approved', 'current_level' => 'level_3'])->toArray();

			$approved_application_customer_id = $this->DmiApplicationEsignedStatuses->find('all')->select(['customer_id'])->where(['application_status' =>"Granted"])->toArray();

			$i=0;
			foreach ($approved_application_customer_id as $each_customer_id)
			{

				if (!empty($each_customer_id['customer_id']))
				{
					$application_customer_type = $this->Reportsfunctions->newApplicantType($each_customer_id['customer_id']);

					if(in_array($application_customer_type, $data_id, TRUE))
					{
						/*$approved_application_list[$i] = $each_customer_id['customer_id'];
						$i=$i+1;*/


					$approved_application_details_list = $this->DmiGrantCertificatesPdfs->find('all')->select(['id', 'id'])->where(['customer_id' => $each_customer_id['customer_id']])->combine('id', 'id')->toArray();

						if (!empty($approved_application_details_list))
						{

							$approved_application_details = $this->DmiGrantCertificatesPdfs->find('all')->where(['id' => max($approved_application_details_list)])->first();

							$user_posted_office_id = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email' => $approved_application_details['user_email_id']])->first();

							if (!empty($user_posted_office_id)) {

								if (in_array($user_posted_office_id['posted_ro_office'],$application_approved_office)) {

									$approved_application_list[$i] = $each_customer_id['customer_id'];
									$i=$i+1;
								}
							}
						}
					}

				}
			}

			if (!empty($approved_application_list))
			{
				$conditions = ['customer_id IN' => $approved_application_list];
			} else {
				$conditions = ['customer_id IS' => '', 'status' => 'approved', 'current_level' => 'level_3'];
			}

			$approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

			$download_approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

		}
		///==========YAshwant 05-Apr-2023 END Search Select Office Filter filter ==================
		/*Yashwant====10/Mar/2023 This below Condition Used For NEW Appln(E-signed) count for GRanted=============*/

		elseif (!empty($data_id))
		{

			$approved_application_customer_id = $this->DmiApplicationEsignedStatuses->find('all')->select(['customer_id'])->where(['application_status' =>"Granted"])->toArray();

			//$approved_application_customer_id = $this->DmiRenewalEsignedStatuses->find('all')->select(['customer_id'])->where(['application_status' =>"Granted"])->toArray();

			$i=0;
			foreach ($approved_application_customer_id as $each_customer_id)
			{

				if (!empty($each_customer_id['customer_id']))
				{
					$application_customer_type = $this->Reportsfunctions->newApplicantType($each_customer_id['customer_id']);

					if(in_array($application_customer_type, $data_id, TRUE))
					{
						$approved_application_list[$i] = $each_customer_id['customer_id'];
						$i=$i+1;
					}
				}
			}

			if (!empty($approved_application_list))
			{
				$conditions = ['customer_id IN' => $approved_application_list];
			} else {
				$conditions = ['customer_id IS' => '', 'status' => 'approved', 'current_level' => 'level_3'];
			}

			$approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

			$download_approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

		} elseif ($search_application_type_id == '' && $application_approved_office == '' && $search_from_date !='' && $search_to_date != '')
		{

			$approved_application_customer_id = $this->$table->find('all')->select(['customer_id'])->where(['date(modified) BETWEEN :start AND :end'])
			->where(['status' => 'approved', 'current_level' => 'level_3'])->bind(':start', $search_from_date, 'date')->bind(':end', $search_to_date, 'date')->toArray();

			$i=0;
			foreach ($approved_application_customer_id as $each_customer_id) {
				$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

				if (in_array($application_customer_type, $search_application_type_id)) {
					$approved_application_list[$i] = $each_customer_id['customer_id'];
					$i=$i+1;
				}
			}

			// below if-else code added by Ankur Jangid
			if (!empty($approved_application_list)) {
				$conditions = ['customer_id IN' => $approved_application_list, 'status' => 'approved', 'current_level' => 'level_3'];
			} else {
				$conditions = ['customer_id IS' => '', 'status' => 'approved', 'current_level' => 'level_3'];
			}

			$approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

			$download_approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

		} elseif ($search_application_type_id != '' && $application_approved_office != '' && $search_from_date =='' && $search_to_date == '' )
		{

			$approved_application_customer_id = $this->$table->find('all')->select(['customer_id'])->where(['status' => 'approved', 'current_level' => 'level_3'])->toArray();

			$i=0;
			foreach ($approved_application_customer_id as $each_customer_id) {

				$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

				if (in_array($application_customer_type,$search_application_type_id)) {

					$approved_application_details_list = $this->DmiGrantCertificatesPdfs->find('all')->select(['id', 'id'])->where(['customer_id' => $each_customer_id['customer_id']])->combine('id', 'id')->toArray();

					if (!empty($approved_application_details_list)) {

						$approved_application_details = $this->DmiGrantCertificatesPdfs->find('all')->where(['id' => max($approved_application_details_list)])->first();

						$user_posted_office_id = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email' => $approved_application_details['user_email_id']])->first();

						if (!empty($user_posted_office_id)) {

							if (in_array($user_posted_office_id['posted_ro_office'],$application_approved_office)) {

								$approved_application_list[$i] = $each_customer_id['customer_id'];
								$i=$i+1;
							}
						}
					}
				}
			}

			// below if-else code added by Ankur Jangid
			if (!empty($approved_application_list)) {
				$conditions = ['customer_id IN' => $approved_application_list, 'status' => 'approved', 'current_level' => 'level_3'];
			} else {
				$conditions = ['customer_id IS' => '', 'status' => 'approved', 'current_level' => 'level_3'];
			}

			$approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

			$download_approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();


		} elseif ($search_application_type_id != '' && $application_approved_office == '' && $search_from_date !='' && $search_to_date != '') {

			$approved_application_customer_id = $this->$table->find('all')->select(['customer_id'])->where(['date(modified) BETWEEN :start AND :end'])
			->where(['status' => 'approved', 'current_level' => 'level_3'])->bind(':start', $search_from_date, 'date')->bind(':end', $search_to_date, 'date')->toArray();

			$i=0;
			foreach ($approved_application_customer_id as $each_customer_id) {
				$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

				if (in_array($application_customer_type, $search_application_type_id)) {
					$approved_application_list[$i] = $each_customer_id['customer_id'];
					$i=$i+1;
				}
			}

			// below if-else code added by Ankur Jangid
			if (!empty($approved_application_list)) {
				$conditions = ['customer_id IN' => $approved_application_list, 'status' => 'approved', 'current_level' => 'level_3'];
			} else {
				$conditions = ['customer_id IS' => '', 'status' => 'approved', 'current_level' => 'level_3'];
			}

			$approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

			$download_approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

		} elseif ($search_application_type_id != '' && $application_approved_office != '' && $search_from_date !='' && $search_to_date != '') {

			$approved_application_customer_id = $this->$table->find('all')->select(['customer_id'])->where(['date(modified) BETWEEN :start AND :end'])
				->where(['status' => 'approved', 'current_level' => 'level_3'])->bind(':start', $search_from_date, 'date')->bind(':end', $search_to_date, 'date')->toArray();

			$i=0;
			foreach ($approved_application_customer_id as $each_customer_id) {

				$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

				if (in_array($application_customer_type,$search_application_type_id)) {

					$approved_application_details = $this->DmiGrantCertificatesPdfs->find('all')->where(['customer_id' => $each_customer_id['customer_id']])->first();

					$user_posted_office_id = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email IS' => $approved_application_details['user_email_id']])->first();

					if (!empty($user_posted_office_id)) {

						if (in_array($user_posted_office_id['posted_ro_office'], $application_approved_office)) {
							$approved_application_list[$i] = $each_customer_id['customer_id'];
							$i=$i+1;
						}
					}
				}
			}

			// below if-else code added by Ankur Jangid
			if (!empty($approved_application_list)) {
				$conditions = ['customer_id IN' => $approved_application_list, 'status' => 'approved', 'current_level' => 'level_3'];
			} else {
				$conditions = ['customer_id IS' => '', 'status' => 'approved', 'current_level' => 'level_3'];
			}

			$approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

			$download_approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

		} elseif ($search_application_type_id == '' && $application_approved_office != '' && $search_from_date =='' && $search_to_date == '') {

			$approved_application_customer_id = $this->$table->find('all')->select(['customer_id'])->where(['status' => 'approved', 'current_level' => 'level_3'])->toArray();

			$i=0;
			foreach ($approved_application_customer_id as $each_customer_id) {

				$approved_application_details_list = $this->DmiGrantCertificatesPdfs->find('all')->select(['id', 'id'])->where(['customer_id IS' => $each_customer_id['customer_id']])->combine('id', 'id')->toArray();

				if (!empty($approved_application_details_list)) {

					$approved_application_details = $this->DmiGrantCertificatesPdfs->find('all')->where(['id' => max($approved_application_details_list)])->first();

					$user_posted_office_id = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email' => $approved_application_details['user_email_id']])->first();

					if (!empty($user_posted_office_id)) {

						if (in_array($user_posted_office_id['posted_ro_office'],$application_approved_office)) {
							$approved_application_list[$i] = $each_customer_id['customer_id'];
							$i=$i+1;
						}
					}
				}
			}

			// below if-else code added by Ankur Jangid
			if (!empty($approved_application_list)) {
				$conditions = ['customer_id IN' => $approved_application_list, 'status' => 'approved', 'current_level' => 'level_3'];
			} else {
				$conditions = ['customer_id IS' => '', 'status' => 'approved', 'current_level' => 'level_3'];
			}

			$approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

			$download_approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

		} elseif ($search_application_type_id == '' && $application_approved_office != '' && $search_from_date !='' && $search_to_date != '') {

			$approved_application_customer_id = $this->$table->find('all')->select(['customer_id'])->where(['date(modified) BETWEEN :start AND :end', 'status' => 'approved',
				'current_level' => 'level_3'])->bind(':start', $search_from_date, 'date')->bind(':end', $search_to_date, 'date')->toArray();

			$i=0;
			foreach ($approved_application_customer_id as $each_customer_id) {

				$approved_application_details_list = $this->DmiGrantCertificatesPdfs->find('all')->select(['id', 'id'])->where(['customer_id' => $each_customer_id['customer_id']])->combine('id', 'id')->toArray();

				if (!empty($approved_application_details_list)) {

					$approved_application_details = $this->DmiGrantCertificatesPdfs->find('all')->where(['id' => max($approved_application_details_list)])->first();

					$user_posted_office_id = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email' => $approved_application_details['user_email_id']])->first();

					if (!empty($user_posted_office_id)) {

						if (in_array($user_posted_office_id['posted_ro_office'], $application_approved_office)) {
							$approved_application_list[$i] = $each_customer_id['customer_id'];
							$i=$i+1;
						}
					}
				}
			}

			// below if-else code added by Ankur Jangid
			if (!empty($approved_application_list)) {
				$conditions = ['customer_id IN' => $approved_application_list, 'status' => 'approved', 'current_level' => 'level_3'];
			} else {
				$conditions = ['customer_id IS' => '', 'status' => 'approved', 'current_level' => 'level_3'];
			}

			$approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

			$download_approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

		} elseif ($search_application_type_id == '' && $application_approved_office == '' && $search_from_date !='' && $search_to_date != '') {

			$approved_application_customer_id = $this->$table->find('all')->select(['customer_id'])->where(['date(modified) BETWEEN :start AND :end', 'status' => 'approved',
				'current_level' => 'level_3'])->bind(':start', $search_from_date, 'date')->bind(':end', $search_to_date, 'date')->toArray();

			$i=0;
			foreach ($approved_application_customer_id as $each_customer_id) {
				$approved_application_list[$i] = $each_customer_id['customer_id'];
				$i=$i+1;
			}

			// below if-else code added by Ankur Jangid
			if (!empty($approved_application_list)) {
				$conditions = ['customer_id IN' => $approved_application_list, 'status' => 'approved', 'current_level' => 'level_3'];
			} else {
				$conditions = ['customer_id IS' => '', 'status' => 'approved', 'current_level' => 'level_3'];
			}

			$approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

			$download_approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

		} else {

			if ($search_flag == 'on') {
				$approved_application_customer_id = $this->$table->find('all')->select(['customer_id'])->where(['status' => 'approved', 'current_level' => 'level_3'])
				->order(['created'=>'DESC'])->extract('customer_id')->toArray(0);
			} else {

				// THIS BELOW CONDITION IS ADDED FOR THE ALL REPORTS BY AKASH ON 16-06-2022
				if ($table == 'DmiGrantCertificatesPdfs') {

					$posted_ro_office = $this->DmiUsers->find('all',array('fields'=>'posted_ro_office', 'conditions'=>array('email IS'=>$_SESSION['username'])))->first();
					$get_short_code = $this->DmiRoOffices->find('all',array('fields'=>'short_code', 'conditions'=>array('id IS'=>$posted_ro_office['posted_ro_office'])))->first();
					$short_code = $get_short_code['short_code'];

					if ($_SESSION['role'] == 'Head Office') {
						//$approved_application_customer_id = $this->DmiGrantCertificatesPdfs->find('all',array('fields'=>array('customer_id'),'group'=>array('customer_id having count(customer_id) >= 1'),'having'=>array('count(customer_id) >= 1')))->toArray();
						$approved_application_customer_id = $this->DmiGrantCertificatesPdfs->find('all')->select(['customer_id'])->group(['customer_id HAVING COUNT(customer_id) >= 1'])->limit(['2'])->extract('customer_id')->toArray(0);
					} else {
						$approved_application_customer_id = $this->DmiGrantCertificatesPdfs->find('all',array('fields'=>'customer_id','conditions'=>array('customer_id like'=>'%/'.$short_code.'/%'),'group'=>'customer_id having count(customer_id) >= 1','having'=>array('count(customer_id) >= 1')))->toArray();
					}

				} else {
					$approved_application_customer_id = $this->$table->find('all')->select(['customer_id'])->where(['status' => 'approved', 'current_level' => 'level_3'])
					->order(['created'=>'DESC'])->limit(['100'])->extract('customer_id')->toArray(0);
				}

			}

			$approved_application_list = $approved_application_customer_id;

			// below if-else code added by Ankur Jangid
			if (!empty($approved_application_list)) {

				// THIS BELOW CONDITION IS ADDED FOR THE ALL REPORTS BY AKASH ON 16-06-2022
				if ($table == 'DmiGrantCertificatesPdfs') {
					$conditions = array('customer_id IN'=>$approved_application_list);
				} else {
					$conditions = ['customer_id IN' => $approved_application_list, 'status' => 'approved', 'current_level' => 'level_3'];
				}

			} else {
				$conditions = ['customer_id IS' => '', 'status' => 'approved', 'current_level' => 'level_3'];
			}


			$approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

			$download_approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();
		}

		return array($approved_application_list,$download_approved_application_list);

	}





	// Approved Application Report Results
	// Description : ----
	// @Author : Pravin Bhakare
	// #Contributer : Ankur Jangid (Migration)
	// Date : ----

	public function approvedApplicationReportResults($approved_application_list,$approved_application_type) {


		$date=array();
		$application_type=array();
		$application_user_email_id=array();
		$user_office=array();
		$application_customer_id=array();
		$name_of_the_firm=array();
		$address_of_the_firm=array();
		$contact_details_of_the_firm=array();
		$approved_TBL_details_tbl_name=array();
		$approved_TBL_details_tbl_registered_no=array();
		$laboratory_details_name=array();
		$laboratory_details_address=array();
		// set values null added laxmi on 16-02-2023
		$approved_application_type_text = array();
		$valid_upto= array();
		$state_name = array();
		$phoneno = array();
		$issued_on = array();


		if (!empty($approved_application_list)) {

			$i=0;
			//applied array_unique function on 18-07-2019
			foreach (array_unique($approved_application_list) as $approved_application) {

				$approved_application_details = array(); //this line added on 18-07-2019

				// THIS BELOW CONDITION IS ADDED FOR THE ALL REPORTS BY AKASH ON 16-06-2022
				if ($approved_application_type == 'all_reports') {
					$approved_application_details = $this->DmiGrantCertificatesPdfs->find('all',array('conditions'=>array('customer_id IS'=>$approved_application),'order' => array('id' => 'desc')))->first();
				} elseif ($approved_application_type == 'new' || $approved_application_type =='') {
					$approved_application_details = $this->DmiGrantCertificatesPdfs->find('all')->where(['customer_id' => $approved_application])->first();
				} elseif ($approved_application_type == 'backlog') {
					$approved_application_details = $this->DmiFinalSubmits->find('all')->where(['customer_id' => $approved_application,'status'=>'approved','current_level'=>'level_3'])->first();
				} elseif ($approved_application_type == 'renewal') {
					$approved_application_detail = $this->DmiGrantCertificatesPdfs->find('all')->select(['id'])->where(['customer_id IS'=>$approved_application])->combine('id','id')->toArray();
					//applied this condition on 27-04-2019
					if (!empty($approved_application_detail)) {
						$approved_application_details = $this->DmiGrantCertificatesPdfs->find('all')->where(['id IN'=> $approved_application_detail])->order(['id' => 'DESC'])->first();
					}
				}

				//this condition added on 18-07-2019
				if (!empty($approved_application_details)) {

					$approved_application_result = $approved_application_details;

					//to check if the application is old or not to print on the excel and for viewing part dont by Akash 07-04-2022

					// THIS BELOW CONDITION IS ADDED FOR THE ALL REPORTS BY AKASH ON 16-06-2022
					if ($approved_application_type == 'all_reports') {

						if ($approved_application_result['pdf_version'] > '1') {
							$approved_application_type_text[$i] = "<b>RENEWAL</b>";
						} elseif ($approved_application_result['user_email_id'] == 'old_application') {
							$approved_application_type_text[$i] = "<i>OLD</i>";
						} else {
							$approved_application_type_text[$i] = "NEW";
						}

					} else {

						if ($approved_application_type == 'renewal') {
							$approved_application_type_text[$i] = "<b>RENEWAL</b>";
						} elseif ($approved_application_result['user_email_id'] == 'old_application') {
							$approved_application_type_text[$i] = "<i>OLD</i>";
						} else {
							$approved_application_type_text[$i] = "NEW";
						}

					}


					if ($approved_application_result['user_email_id'] == 'old_application') {
						$old_app_approved_by = $this->Customfunctions->old_app_approved_by($approved_application_result['customer_id']);
						$approved_application_result['user_email_id'] = $old_app_approved_by;
					}

					$explode = explode("/",$approved_application_result['customer_id']);

					$approved_office_id = $this->DmiRoOffices->find('all')->select(['ro_email_id'])->where(['short_code' => $explode[2]])->first();

					$user_posted_office_id = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email'=>$approved_office_id['ro_email_id']])->first();

					if (!empty($user_posted_office_id)) {

						$user_office_details = $this->DmiRoOffices->find('all')->select(['ro_office'])->where(['id'=>$user_posted_office_id['posted_ro_office']])->first();

						if (!empty($user_office_details)) {
							$user_office[$i] = $user_office_details['ro_office'];
						} else {
							$user_office[$i] = 'N/A';
						}
					} else {
						$user_office[$i] = 'N/A';
					}

					$application_form_type = $this->Customfunctions->checkApplicantFormType($approved_application_result['customer_id']);

					if ($application_form_type == 'A') {
						$application_type[$i]='CA (Form-A)';
					} elseif ($application_form_type == 'B') {
						$application_type[$i]='Printing Press (Form-B)';
					} elseif ($application_form_type == 'C') {
						$application_type[$i]='Laboratory (Form-C)';
					} elseif ($application_form_type == 'D') {
						$application_type[$i]='Laboratory (Form-D)';
					} elseif ($application_form_type == 'E') {
						$application_type[$i]='CA (Form-E)';
					} elseif ($application_form_type == 'F') {
						$application_type[$i]='CA (Form-F)';
					}

					$date[$i] = $approved_application_result['created'];
					$application_customer_id[$i] = $approved_application_result['customer_id'];

					//added by the akash on 13-11-2021
					$firmDetails = $this->DmiFirms->find('all',array('conditions'=>array('customer_id IS'=>$approved_application_result['customer_id'])))->first();

					$name_of_the_firm[$i] = $firmDetails['firm_name'];
					$address_of_the_firm[$i] = $firmDetails['street_address'];
					$contact_details_of_the_firm[$i] = base64_decode($firmDetails['email']);
					$phoneno[$i] = $firmDetails['mobile_no'];

					//tbl details
					$tbl_details = $this->DmiAllTblsDetails->find('all',array('conditions'=>array('customer_id IS'=>$approved_application_result['customer_id'],'OR' => array('delete_status IS NULL', 'delete_status' => 'no'))))->toArray();

					if (!empty($tbl_details)) {
						$j=0;
						foreach ($tbl_details as $each) {

							$approved_TBL_details_tbl_name[$i][$j] = $each['tbl_name'];
							$approved_TBL_details_tbl_registered_no[$i][$j] = $each['tbl_registered_no'];
							$j++;
						}

					} else {
						$approved_TBL_details_tbl_name[$i][0] = 'N/A';
						$approved_TBL_details_tbl_registered_no[$i][0] = 'N/A';
					}



					//lab details
					$lab_details = $this->DmiCustomerLaboratoryDetails->find('all',array('conditions'=>array('customer_id IS'=>$approved_application_result['customer_id'])))->toArray();
					if (!empty($lab_details)) {
						$laboratory_details_name[$i] = $lab_details[0]['laboratory_name'];
						$laboratory_details_address[$i] = $lab_details[0]['street_address'];
					} else {
						$laboratory_details_name[$i] = 'N/A';
						$laboratory_details_address[$i] = 'N/A';
					}

					$commodity_value = $this->DmiFirms->find('all')->select(['sub_commodity'])->where(['customer_id'=>$approved_application_result['customer_id']])->first();

					$commodity_list[$i] = $this->Customfunctions->showCommdityInApplList($commodity_value['sub_commodity']);


					//Added this Code on the 08-04-2022 as the base encoding was not on some emails so to dispaly the email , checking if the encoding is needed or not.
					$checkEmailForEncoding[$i] = $approved_application_result['user_email_id'];

					if($this->isBase64Encoded($checkEmailForEncoding[$i]) == true){
						$application_user_email_id[$i] = base64_decode($approved_application_result['user_email_id']);
					} else {
						$application_user_email_id[$i] = $approved_application_result['user_email_id'];
					}

					//check the expiry dateand print to the reports  added by Akash on 24-05-2022
					$grant_date = chop($approved_application_details['date'],"00:00:00");
					$valid_upto[$i] = $this->Customfunctions->getCertificateValidUptoDate($approved_application_result['customer_id'],$grant_date);

					//check the state name added by akash on 14-06-2022
					$state_name[$i] = $this->getStateName($approved_application_result['customer_id']);

					//Certificate Issued on
					$issued_on[$i] = chop($approved_application_result['date'],"00:00:00");

					$i=$i+1;
				}
			}

		} else {
			$commodity_list = '';
		}

		$this->set('date',$date);
		$this->set('application_customer_id',$application_customer_id);
		$this->set('application_user_email_id',$application_user_email_id);
		$this->set('application_type',$application_type);
		$this->set('user_office',$user_office);
		$this->set('approved_application_list',$approved_application_list);
		$this->set('commodity_list',$commodity_list);
		$this->set('approved_application_type',$approved_application_type_text);
		$this->set('name_of_the_firm',$name_of_the_firm);
		$this->set('address_of_the_firm',$address_of_the_firm);
		$this->set('contact_details_of_the_firm',$contact_details_of_the_firm);
		$this->set('approved_TBL_details_tbl_name',$approved_TBL_details_tbl_name);
		$this->set('approved_TBL_details_tbl_registered_no',$approved_TBL_details_tbl_registered_no);
		$this->set('laboratory_details_name',$laboratory_details_name);
		$this->set('laboratory_details_address',$laboratory_details_address);
		$this->set('valid_upto',$valid_upto);
		$this->set('state_name',$state_name);
		$this->set('phoneno',$phoneno);
		$this->set('issued_on',$issued_on);


	}





	// Download Approved Application Report Results
	// Description : ----
	// @Author : Pravin Bhakare
	// #Contributer : Ankur Jangid (Migration)
	// Date : ----

	public function downloadApprovedApplicationReportResults($approved_application_list,$approved_application_type) {

		$this->viewBuilder()->setLayout('downloadpdf');

		$date=array();
		$application_type=array();
		$application_user_email_id=array();
		$user_office=array();
		$application_customer_id=array();

		if (!empty($approved_application_list)) {

			$i=0;
			foreach (array_unique($approved_application_list) as $approved_application)//applied array_unique function on 18-07-2019
			{
				$approved_application_details = array(); //this line added on 18-07-2019

				if ($approved_application_type == 'new' || $approved_application_type =='') {
					$approved_application_details = $this->DmiGrantCertificatesPdfs->find('all')->where(['customer_id' => $approved_application])->first();
				} elseif ($approved_application_type == 'renewal') {

					$approved_application_detail = $this->DmiGrantCertificatesPdfs->find('all')->select(['id'])->where(['customer_id' => $approved_application])->combine('id', 'id')->toArray();

					if (!empty($approved_application_detail)) {//applied this condition on 27-04-2019
						$approved_application_details = $this->DmiGrantCertificatesPdfs->find('all')->where(['id' => max($approved_application_detail)])->first();
					}
				}

				if (!empty($approved_application_details)) { //this condition added on 18-07-2019

					$approved_application_result = $approved_application_details;

					//to check if the application is old or not to print on the excel and for viewing part dont by Akash 07-04-2022
					if ($approved_application_type == 'renewal') {

						$approved_application_type_text[$i] = "Renewal";

					} elseif ($approved_application_result['user_email_id'] == 'old_application') {
						$approved_application_type_text[$i] = "Old";
					} else {
						$approved_application_type_text[$i] = "New";
					}


					if ($approved_application_result['user_email_id'] == 'old_application') {
						$old_app_approved_by = $this->Customfunctions->old_app_approved_by($approved_application_result['customer_id']);
						$approved_application_result['user_email_id'] = $old_app_approved_by;
					}

					$explode = explode("/",$approved_application_result['customer_id']);

					$approved_office_id = $this->DmiRoOffices->find('all')->select(['ro_email_id'])->where(['short_code' => $explode[2]])->first();

					$user_posted_office_id = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email' => $approved_office_id['ro_email_id']])->first();

					if (!empty($user_posted_office_id)) {
						$user_office_details = $this->DmiRoOffices->find('all')->select(['ro_office'])->where(['id' => $user_posted_office_id['posted_ro_office']])->first();

						if (!empty($user_office_details)) {
							$user_office[$i] = $user_office_details['ro_office'];
						} else {
							$user_office[$i] = '--';
						}
					} else {
						$user_office[$i] = '--';
					}

					$application_form_type = $this->Customfunctions->checkApplicantFormType($approved_application_result['customer_id']);

					if ($application_form_type == 'A') {$application_type[$i]='CA (Form-A)';}
					elseif ($application_form_type == 'B') {$application_type[$i]='Printing Press (Form-B)';}
					elseif ($application_form_type == 'C') {$application_type[$i]='Laboratory (Form-C)';}
					elseif ($application_form_type == 'D') {$application_type[$i]='Laboratory (Form-D)';}
					elseif ($application_form_type == 'E') {$application_type[$i]='CA (Form-E)';}
					elseif ($application_form_type == 'F') {$application_type[$i]='CA (Form-F)';}

					$date[$i] = $approved_application_result['created'];
					$application_customer_id[$i] = $approved_application_result['customer_id'];

					//To get the Firm details to show in the excel this queries are added By Akash Thakre on 08-04-2022
					$firmDetails = $this->DmiFirms->find('all',array('conditions'=>array('customer_id IS'=>$approved_application_result['customer_id'])))->first();

					$name_of_the_firm[$i] = $firmDetails['firm_name'];
					$address_of_the_firm[$i] = $firmDetails['street_address'];
					$contact_details_of_the_firm[$i] = base64_decode($firmDetails['email']);

					//To get the TBL details like approved tbl name and registred no to print the Excel file added this below code by Akash Thakre on 08-04-2022
					$tbl_details = $this->DmiAllTblsDetails->find('all',array('conditions'=>array('customer_id IS'=>$approved_application_result['customer_id'],'delete_status IS NULL')))->toArray();

					if (!empty($tbl_details)) {
						$j=0;
						foreach ($tbl_details as $each) {

							$approved_TBL_details_tbl_name[$i][$j] = $each['tbl_name'];
							$approved_TBL_details_tbl_registered_no[$i][$j] = $each['tbl_registered_no'];
							$j++;
						}

					} else {
						$approved_TBL_details_tbl_name[$i][0] = '--';
						$approved_TBL_details_tbl_registered_no[$i][0] = '--';
					}


					//lab details
					$lab_details = $this->DmiCustomerLaboratoryDetails->find('all',array('conditions'=>array('customer_id IS'=>$approved_application_result['customer_id'])))->toArray();
					if (!empty($lab_details)) {
						$laboratory_details_name[$i] = $lab_details[0]['laboratory_name'];
						$laboratory_details_address[$i] = $lab_details[0]['street_address'];
					} else {
						$laboratory_details_name[$i] = '--';
						$laboratory_details_address[$i] = '--';
					}


					$commodity_value = $this->DmiFirms->find('all')->select(['sub_commodity'])->where(['customer_id' => $approved_application_result['customer_id']])->first();

					$commodity_list[$i] = $this->Customfunctions->showCommdityInApplList($commodity_value['sub_commodity']);


					//Added this Code on the 08-04-2022 as the base encoding was not on some emails so to dispaly the email , checking if the encoding is needed or not.
					$checkEmailForEncoding[$i] = $approved_application_result['user_email_id'];

					if($this->isBase64Encoded($checkEmailForEncoding[$i]) == true){
						$application_user_email_id[$i] = base64_decode($approved_application_result['user_email_id']);
					} else {
						$application_user_email_id[$i] = $approved_application_result['user_email_id'];
					}

					$i=$i+1;
				}
			}
		}

		$this->set('orders',$date);
		$this->set('application_id',$application_customer_id);
		$this->set('application_user_email_id',$application_user_email_id);
		$this->set('application_type',$application_type);
		$this->set('user_office',$user_office);
		$this->set('approved_application_list',$approved_application_list);
		$this->set('commodity_list',$commodity_list);
		$this->set('approved_application_type',$approved_application_type_text);
		$this->set('name_of_the_firm',$name_of_the_firm);
		$this->set('address_of_the_firm',$address_of_the_firm);
		$this->set('contact_details_of_the_firm',$contact_details_of_the_firm);
		$this->set('approved_TBL_details_tbl_name',$approved_TBL_details_tbl_name);
		$this->set('approved_TBL_details_tbl_registered_no',$approved_TBL_details_tbl_registered_no);
		$this->set('laboratory_details_name',$laboratory_details_name);
		$this->set('laboratory_details_address',$laboratory_details_address);

		$this->layout = null;
		$this->autoLayout = false;
		Configure::write('debug', '0');
		$this -> render('/element/download_report_excel_format/download_approved_application_report_results');


	}



	// Download Grant Backlog Application Report Results
	// Description : function is uesd for download granted backlog applications report
	// #Author : Yashwant
	// Date : 06-APR-2023

	public function downloadGrantBacklogApplicationReportResults($approved_application_list,$approved_application_type)
	{
		$this->viewBuilder()->setLayout('downloadpdf');

		$date=array();
		$application_type=array();
		$application_user_email_id=array();
		$user_office=array();
		$application_customer_id=array();

		if (!empty($approved_application_list)) {

			$i=0;
			foreach (array_unique($approved_application_list) as $approved_application)//applied array_unique function on 18-07-2019
			{
				$approved_application_details = array(); //this line added on 18-07-2019

				if ($approved_application_type == 'new' || $approved_application_type =='') {
					$approved_application_details = $this->DmiGrantCertificatesPdfs->find('all')->where(['customer_id' => $approved_application])->first();
				}

				if ($approved_application_type == 'backlog' || $approved_application_type =='') {
					$approved_application_details = $this->DmiGrantCertificatesPdfs->find('all')->where(['customer_id' => $approved_application])->first();
				} elseif ($approved_application_type == 'renewal') {
					$approved_application_detail = $this->DmiGrantCertificatesPdfs->find('all')->select(['id'])->where(['customer_id' => $approved_application])->combine('id', 'id')->toArray();

					if (!empty($approved_application_detail)) {//applied this condition on 27-04-2019
						$approved_application_details = $this->DmiGrantCertificatesPdfs->find('all')->where(['id' => max($approved_application_detail)])->first();
					}
				}

				if (!empty($approved_application_details)) { //this condition added on 18-07-2019

					$approved_application_result = $approved_application_details;

					//to check if the application is old or not to print on the excel and for viewing part dont by Akash 07-04-2022
					if ($approved_application_type == 'renewal') {
						$approved_application_type_text[$i] = "Renewal";
					} elseif ($approved_application_result['user_email_id'] == 'old_application') {
						$approved_application_type_text[$i] = "Old";
					} else {
						$approved_application_type_text[$i] = "New";
					}


					if ($approved_application_result['user_email_id'] == 'old_application') {
						$old_app_approved_by = $this->Customfunctions->old_app_approved_by($approved_application_result['customer_id']);
						$approved_application_result['user_email_id'] = $old_app_approved_by;
					}

					$explode = explode("/",$approved_application_result['customer_id']);

					$approved_office_id = $this->DmiRoOffices->find('all')->select(['ro_email_id'])->where(['short_code' => $explode[2]])->first();

					$user_posted_office_id = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email' => $approved_office_id['ro_email_id']])->first();

					if (!empty($user_posted_office_id)) {
						$user_office_details = $this->DmiRoOffices->find('all')->select(['ro_office'])->where(['id' => $user_posted_office_id['posted_ro_office']])->first();

						if (!empty($user_office_details)) {
							$user_office[$i] = $user_office_details['ro_office'];
						} else {
							$user_office[$i] = '--';
						}
					} else {
						$user_office[$i] = '--';
					}

					$application_form_type = $this->Customfunctions->checkApplicantFormType($approved_application_result['customer_id']);

					if ($application_form_type == 'A') {$application_type[$i]='CA (Form-A)';}
					elseif ($application_form_type == 'B') {$application_type[$i]='Printing Press (Form-B)';}
					elseif ($application_form_type == 'C') {$application_type[$i]='Laboratory (Form-C)';}
					elseif ($application_form_type == 'D') {$application_type[$i]='Laboratory (Form-D)';}
					elseif ($application_form_type == 'E') {$application_type[$i]='CA (Form-E)';}
					elseif ($application_form_type == 'F') {$application_type[$i]='CA (Form-F)';}

					$date[$i] = $approved_application_result['created'];
					$application_customer_id[$i] = $approved_application_result['customer_id'];

					//To get the Firm details to show in the excel this queries are added By Akash Thakre on 08-04-2022
					$firmDetails = $this->DmiFirms->find('all',array('conditions'=>array('customer_id IS'=>$approved_application_result['customer_id'])))->first();

					$name_of_the_firm[$i] = $firmDetails['firm_name'];
					$address_of_the_firm[$i] = $firmDetails['street_address'];
					$contact_details_of_the_firm[$i] = base64_decode($firmDetails['email']);

					//To get the TBL details like approved tbl name and registred no to print the Excel file added this below code by Akash Thakre on 08-04-2022
					$tbl_details = $this->DmiAllTblsDetails->find('all',array('conditions'=>array('customer_id IS'=>$approved_application_result['customer_id'],'delete_status IS NULL')))->toArray();

					if (!empty($tbl_details)) {
						$j=0;
						foreach ($tbl_details as $each) {

							$approved_TBL_details_tbl_name[$i][$j] = $each['tbl_name'];
							$approved_TBL_details_tbl_registered_no[$i][$j] = $each['tbl_registered_no'];
							$j++;
						}

					} else {
						$approved_TBL_details_tbl_name[$i][0] = '--';
						$approved_TBL_details_tbl_registered_no[$i][0] = '--';
					}


					//lab details
					$lab_details = $this->DmiCustomerLaboratoryDetails->find('all',array('conditions'=>array('customer_id IS'=>$approved_application_result['customer_id'])))->toArray();
					if (!empty($lab_details)) {
						$laboratory_details_name[$i] = $lab_details[0]['laboratory_name'];
						$laboratory_details_address[$i] = $lab_details[0]['street_address'];
					} else {
						$laboratory_details_name[$i] = '--';
						$laboratory_details_address[$i] = '--';
					}


					$commodity_value = $this->DmiFirms->find('all')->select(['sub_commodity'])->where(['customer_id' => $approved_application_result['customer_id']])->first();

					$commodity_list[$i] = $this->Customfunctions->showCommdityInApplList($commodity_value['sub_commodity']);

					//Added this Code on the 08-04-2022 as the base encoding was not on some emails so to dispaly the email , checking if the encoding is needed or not.
					$checkEmailForEncoding[$i] = $approved_application_result['user_email_id'];

					if($this->isBase64Encoded($checkEmailForEncoding[$i]) == true){
						$application_user_email_id[$i] = base64_decode($approved_application_result['user_email_id']);
					} else {
						$application_user_email_id[$i] = $approved_application_result['user_email_id'];
					}

					$i=$i+1;
				}
			}
		}

		$this->set('orders',$date);
		$this->set('application_id',$application_customer_id);
		$this->set('application_user_email_id',$application_user_email_id);
		$this->set('application_type',$application_type);
		$this->set('user_office',$user_office);
		$this->set('approved_application_list',$approved_application_list);
		$this->set('commodity_list',$commodity_list);
		$this->set('approved_application_type',$approved_application_type_text);
		$this->set('name_of_the_firm',$name_of_the_firm);
		$this->set('address_of_the_firm',$address_of_the_firm);
		$this->set('contact_details_of_the_firm',$contact_details_of_the_firm);
		$this->set('approved_TBL_details_tbl_name',$approved_TBL_details_tbl_name);
		$this->set('approved_TBL_details_tbl_registered_no',$approved_TBL_details_tbl_registered_no);
		$this->set('laboratory_details_name',$laboratory_details_name);
		$this->set('laboratory_details_address',$laboratory_details_address);

		$this->layout = null;
		$this->autoLayout = false;
		Configure::write('debug', '0');
		$this -> render('/element/download_report_excel_format/download_approved_application_report_results');
	}




	// Search User Id
	// Description : Function to find user email id from user name(ID)
	// @Author : Pravin Bhakare
	// #Contributer : Ankur Jangid (Migration)
	// Date : 12-09-2017



	public function search_user_id($user_name_details,$search_user_id) {

		if (!empty($user_name_details[$search_user_id])) {
			$search_user_id = $user_name_details[$search_user_id];
			$search_user_id = explode('(',$search_user_id);
			$search_user_id = rtrim($search_user_id[1],')');
			return $search_user_id;
		} else {
			$this->redirect('/');
			$this->Session->destroy();
		}
	}





	// User Name List
	// Description : Function to combine user name and user email id and create user name(ID) value
	// @Author : Pravin Bhakare
	// #Contributer : Ankur Jangid (Migration) / Amol Choudhari (Modification)
	// Date : 12-09-2017 (C) | 06-01-2022 (M)

	public function userNameList() {

		//updated query on 06-01-2022 by Amol
		$users_name_list = $this->DmiUsers->find('all',array('fields'=>array('id','f_name', 'l_name', 'email'),'conditions'=>array('status'=>'active','OR'=>array('division IN'=>array('BOTH','DMI'))),'order'=>'f_name ASC'))->toArray();
		$user_name_details = array();
		foreach ($users_name_list as $value) {

			$user_name_details[$value['id']] = $value['f_name'].' '.$value['l_name'].' ('.base64_decode($value['email']) . ')';
		}

		return $user_name_details;
	}




	// Date Comparison
	// Description : Function to check comparison between from date and To date
	// @Author : Pravin Bhakare
	// #Contributer : Ankur Jangid (Migration) / Amol Choudhari (Modification)
	// Date : 13-09-2017

	public function date_comparison($from_date,$to_date) {

		$from_date = date('d/m/Y H:i:s', strtotime(str_replace('/','-',$from_date)));
		$to_date  = strtotime(str_replace('/','-',$to_date));

		// set variables to show popup messages from view file
		$message = '';
		$redirect_to = '';

		if ($from_date <= $to_date) {
			return true;
		}elseif($from_date){
			return true;
		}
		else {
			$message = 'Invalid Date Range Selection';
			$redirect_to = 'report_types';
			$this->view = '/element/message_boxes';
		}




		// set variables to show popup messages from view file
		$this->set('message',$message);
		$this->set('redirect_to',$redirect_to);
	}





	// Show District Dropdown
	// Description : ----
	// @Author : Pravin Bhakare
	// #Contributer : ----
	// Date : ----

	public function showDistrictDropdown() {
		$this->autoRender = false;
		$state_id = $this->request->getData('state');

		// Change on 3/11/2018 -  add order by condition - by Pravin Bhakare
		$districts = $this->DmiDistricts->find('all')->select(['id','district_name'])->where(['state_id' => $state_id])->order(['district_name' => 'ASC'])->toArray();

		?><option value="">All</option><?php
		foreach ($districts as $district) { ?>
			<option value="<?php echo $district['id']?>"><?php echo $district['district_name']?></option>
		<?php	}
	}





	// Pending Application Report User Id
	// Description : ----
	// @Author : Pravin Bhakare
	// #Contributer : Ankur Jangid (Migration)
	// Date : ----

	public function pendingApplicationReportUserId() {

		$this->autoRender = false;
		$table = 'DmiRoOffices';

		$user_role = $_POST['user_role'];
		$ro_offices = $_POST['ro_offices'];
		$mo_offices = $_POST['mo_offices'];
		$io_offices = $_POST['io_offices'];

		
		if ($ro_offices != null) {
			$ro_offices = explode(',', $ro_offices);
			?><option value="<?php echo ''; ?>"><?php echo 'All'; ?></option><?php

			foreach ($ro_offices as $office_id) {

				$user_email_details = $this->DmiUsers->find('all')->select(['email'])->where(['posted_ro_office' => $office_id])->order(['email' => 'ASC'])
					->extract('email')->toList();

				$i=0;
				foreach ($user_email_details as $user_email_id) {
					$user_email_details = $this->DmiUserRoles->find('all')->select(['id', 'user_email_id'])->where(['user_email_id' => $user_email_id, 'ro_inspection' => 'yes'])->first();
					if (!empty($user_email_details)) {
						$user_email_details_xy[$i] = $user_email_details;
						?><option value="<?php echo $user_email_details_xy[$i]['id']; ?>"><?php echo base64_decode($user_email_details_xy[$i]['user_email_id']); ?></option><?php
						$i=$i+1;
					}
				}
			}

		} elseif ($mo_offices != null) {

			$mo_offices = explode(',', $mo_offices);
			?><option value="<?php echo ''; ?>"><?php echo 'All'; ?></option><?php

			foreach ($mo_offices as $office_id) {

				$user_email_details = $this->DmiUsers->find('all')->select(['email'])->where(['posted_ro_office' => $office_id])->order(['email' => 'ASC'])
					->extract('email')->toList();

				$i=0;
				foreach ($user_email_details as $user_email_id) {

					$user_email_details = $this->DmiUserRoles->find('all')->select(['id', 'user_email_id'])->where(['user_email_id' => $user_email_id, 'mo_smo_inspection' => 'yes'])->first();
					if (!empty($user_email_details)) {
						$user_email_details_xy[$i] = $user_email_details;
						?><option value="<?php echo $user_email_details_xy[$i]['id']; ?>"><?php echo base64_decode($user_email_details_xy[$i]['user_email_id']); ?></option><?php
						$i=$i+1;
					}
				}
			}

		} elseif ( $io_offices != null) {

			$io_offices = explode(',',$io_offices);
			?><option value="<?php echo ''; ?>"><?php echo 'All'; ?></option><?php

			foreach ($io_offices as $office_id) {

				$user_email_details = $this->DmiUsers->find('all')->select(['email'])->where(['posted_ro_office' => $office_id])->order(['email' => 'ASC'])
					->extract('email')->toList();

				$i=0;
				foreach ($user_email_details as $user_email_id) {

					$user_email_details = $this->DmiUserRoles->find('all')->select(['id', 'user_email_id'])->where(['user_email_id' => $user_email_id, 'io_inspection' => 'yes'])->first();

					if (!empty($user_email_details)) {
						$user_email_details_xy[$i] = $user_email_details;
						?><option value="<?php echo $user_email_details_xy[$i]['id']; ?>"><?php echo base64_decode($user_email_details_xy[$i]['user_email_id']); ?></option><?php
						$i=$i+1;
					}
				}
			}

		} elseif ($user_role == 'HO MO/SMO' || $user_role == 'DY.AMA' || $user_role == 'JT.AMA' || $user_role == 'AMA') {

			if ($user_role == 'HO MO/SMO') {
				$office_id='ho_mo_smo';
			} elseif ($user_role == 'DY.AMA') {
				$office_id='dy_ama';
			} elseif ($user_role == 'JT.AMA') {
				$office_id='jt_ama';
			} else {
				$office_id='ama';
			}

			$user_email_id = $this->DmiUserRoles->find('all')->select(['id', 'user_email_id'])->where([$office_id => 'yes'])->toArray();
			//added this for loop to show list of users on 05-07-2019 by Amol
			foreach ($user_email_id as $each) {
				?><option value="<?php echo $each['id']; ?>"><?php echo base64_decode($each['user_email_id']); ?></option><?php
			}
		} elseif ($user_role == 'RO/SO' || $user_role == 'MO/SMO' || $user_role == 'IO' || $user_role == 'null_value') {
			?><option value="<?php echo ''; ?>"><?php echo 'All'; ?></option><?php
		} else{
			?><option value="<?php echo ''; ?>"><?php echo 'All'; ?></option><?php
		}
		exit;

	}





	// Check User Role From Current Level
	// Description : ----
	// @Author : Pravin Bhakare
	// #Contributer : Ankur Jangid (Migration)
	// Date : ----

	public function checkUserRoleFromCurrentLevel($current_level,$user_email_id) {

		$user_roles = '--';
		if ($current_level == 'level_3') {
			$user_roles = 'RO/SO';
		} elseif ($current_level == 'level_1') {
			$user_roles ='MO/SMO';
		} elseif ($current_level == 'level_2') {
			$user_roles ='IO';
		} elseif ($current_level == 'applicant') {
			$user_roles ='Applicant';
		} elseif ($current_level == 'pao') {
			$user_roles ='PAO/DDO';
		} elseif ($current_level == 'level_4') {

			$check_roles = $this->DmiUserRoles->find('all')->where(['user_email_id IS' => $user_email_id])->first();

			if (!empty($check_roles)) {
				$user_list = $check_roles;

				if ($user_list['dy_ama'] == 'yes') {
					$user_roles ='DY.AMA';
				} elseif ($user_list['jt_ama'] == 'yes') {
					$user_roles ='JT.AMA';
				} elseif ($user_list['ama'] == 'yes') {
					$user_roles ='AMA';
				} elseif ($user_list['ho_mo_smo'] == 'yes') {
					$user_roles='HO MO/SMO';
				} else {
					$user_roles='--';
				}
			}
		}

		return $user_roles;
	}





	// Newly Added Firm List Report
	// Description : For AQCMS Stats
	// @Author : Pravin Bhakare
	// #Contributer : Yeshwant
	// Date :

	public function newlyAddedFirmListReportForStats($cert_type)
	{

		$application_type_array = array('A'=>'CA (Form-A)','C'=>'Laboratory (Form-C)','E'=>'CA (Form-E)','B'=>'Printing Press (Form-B)','D'=>'Laboratory (Form-D)','F'=>'CA (Form-F)');

		$cert_type_decode= base64_decode($cert_type);
		/*if($cert_type_decode=='1')
		{
			$application_type_array = array('A'=>'CA (Form-A)','E'=>'CA (Form-E)','F'=>'CA (Form-F)');
		}
		elseif ($cert_type_decode=='2')
		{
			$application_type_array = array('B'=>'Printing Press (Form-B)');
		}
		elseif ($cert_type_decode=='3') {
			$application_type_array = array('C'=>'Laboratory (Form-C)','D'=>'Laboratory (Form-D)');
		}*/



		//Change on 9/11/2018, Sorting array by ascending order - By Pravin Bhakare
		asort($application_type_array);


		$this->set('cert_type_decode',$cert_type_decode);
		$this->set('application_type_array',$application_type_array);

		// Apply "Order by" clause to get state list by order wise (Done By Pravin 10-01-2018)
		$states = $this->DmiStates->find('all')->select(['id', 'state_name'])->where(['OR' => [['delete_status IS' => null], ['delete_status IS' => 'no']]])
			->order(['state_name' => 'ASC'])->combine('id', 'state_name')->toArray();
		$this->set('states',$states);

		// Change on 05/11/2018, Get list of all district - By Pravin Bhakare
		$all_district_name = $this->DmiDistricts->find('all')->select(['id', 'district_name'])->where(['OR' => [['delete_status IS' => null], ['delete_status ='=>'no']]])
			->order(['district_name' => 'ASC'])->combine('id', 'district_name')->toArray();
		$this->set('all_district_name', $all_district_name);


		
		$company_id = null;
		$application_type = null;
		$state = null;
		$district = null;
		$search_from_date = null;
		$search_to_date = null;

		$application_type = $this->Session->read('application_type');
		$company_id = $this->Session->read('company_id');
		$state = $this->Session->read('state');
		$district = $this->Session->read('district');
		$search_from_date = $this->Session->read('search_from_date');
		$search_to_date = $this->Session->read('search_to_date');

		$download_application_type = $this->Session->read('application_type');
		$download_company_id = $this->Session->read('company_id');
		$download_state = $this->Session->read('state');
		$download_district = $this->Session->read('district');
		$download_search_from_date = $this->Session->read('search_from_date');
		$download_search_to_date = $this->Session->read('search_to_date');

		$search_flag = 'off'; // added by Ankur
		$download_report = 'no';

		if (null != ($this->request->getData('search_logs')) || null != ($this->request->getData('download_report'))) {
			$search_flag = 'on'; // added by Ankur

			//Check not empty "Download Report as Excel" button Request, if condition TRUE then set value "yes" for "Download Report as Excel" click event
			//(Done by pravin 14-03-2018)
			if (!empty($this->request->getData('download_report'))) {
				$download_report = 'yes';
			}

			$company_id = htmlentities($this->request->getData('company_id'), ENT_QUOTES);
			$application_type = $this->request->getData('application_type');


			$table = 'DmiStates';
			$post_input_request = $this->request->getData('state');
			if (!empty($post_input_request)) {
				$state = $this->Customfunctions->dropdownSelectInputCheck($table,$post_input_request); // calling library function
			} else {
				$state = '';
			}

			$table = 'DmiDistricts';
			$post_input_request = $this->request->getData('district');
			if (!empty($post_input_request)) {
				$district = $this->Customfunctions->dropdownSelectInputCheck($table,$post_input_request); // calling library function
			} else {
				$district = '';
			}

			$download_firms_data_details_result = $this->DmiFirms->newlyAddedFirmListReportConditionsForStats($download_application_type,$download_company_id,$download_state,$download_district,$download_search_from_date,$download_search_to_date,$search_flag,$cert_type);

			$search_from_date =  $this->request->getData('from_date');
			$search_from_date = $this->Customfunctions->dateFormatCheck($search_from_date);
			$search_to_date =  $this->request->getData('to_date');
			$search_to_date = $this->Customfunctions->dateFormatCheck($search_to_date);
			$this->date_comparison($search_from_date,$search_to_date);

			$this->Session->delete('application_type');
			$this->Session->delete('company_id');
			$this->Session->delete('state'); $this->Session->delete('district');
			$this->Session->delete('search_from_date'); $this->Session->delete('search_to_date');

			$this->Session->write('application_type',$application_type);
			$this->Session->write('company_id',$company_id);
			$this->Session->write('search_from_date',$search_from_date); $this->Session->write('search_to_date',$search_to_date);
			$this->Session->write('state',$state); $this->Session->write('district',$district);

			$firms_data_details_result = $this->DmiFirms->newlyAddedFirmListReportConditionsForStats($application_type,$company_id,$state,$district,$search_from_date,$search_to_date,$search_flag,$cert_type);
			$this->newlyAddedFirmReportResult($firms_data_details_result,$application_type_array);

			if ($download_report == 'yes') {
				$this->downloadNewlyAddedFirmReportResult($download_firms_data_details_result,$application_type_array);
			}

		} else {

			if ($this->request->getData('download_report') =='') {
				$download_report = 'no';
			}
			
			$firms_data_details_result = $this->DmiFirms->newlyAddedFirmListReportConditionsForStats($application_type,$company_id,$state,$district,$search_from_date,$search_to_date,$search_flag,$cert_type);

			$this->newlyAddedFirmReportResult($firms_data_details_result,$application_type_array);

			if(!empty($firms_data_details_result))
			{
				if($download_report=='yes')
				{
					$this->downloadNewlyAddedFirmReportResult($firms_data_details_result,$application_type_array);
				}
			}
		}


		$this->set('application_type',$application_type);
		$this->set('state',$state);
		$this->set('company_id',$company_id);
		$this->set('district',$district); $this->set('search_from_date',$search_from_date);	 $this->set('search_to_date',$search_to_date);
	}




	// Newly Added Firm List Report
	// Description : For Front Listing
	// @Author : Pravin Bhakare
	// #Contributer : Ankur Jangid (Migration)
	// Date : 28-04-2023

	public function newlyAddedFirmListReport() {

		$application_type_array = array('A'=>'CA (Form-A)','C'=>'Laboratory (Form-C)','E'=>'CA (Form-E)','B'=>'Printing Press (Form-B)','D'=>'Laboratory (Form-D)','F'=>'CA (Form-F)');

		//Change on 9/11/2018, Sorting array by ascending order - By Pravin Bhakare
		asort($application_type_array);
		$this->set('application_type_array',$application_type_array);

		// Apply "Order by" clause to get state list by order wise (Done By Pravin 10-01-2018)
		$states = $this->DmiStates->find('all')->select(['id', 'state_name'])->where(['OR' => [['delete_status IS' => null], ['delete_status IS' => 'no']]])
			->order(['state_name' => 'ASC'])->combine('id', 'state_name')->toArray();
		$this->set('states',$states);

		// Change on 05/11/2018, Get list of all district - By Pravin Bhakare
		$all_district_name = $this->DmiDistricts->find('all')->select(['id', 'district_name'])->where(['OR' => [['delete_status IS' => null], ['delete_status ='=>'no']]])
			->order(['district_name' => 'ASC'])->combine('id', 'district_name')->toArray();
		$this->set('all_district_name', $all_district_name);

		$company_id = null;
		$application_type = null;
		$state = null;
		$district = null;
		$search_from_date = null;
		$search_to_date = null;

		$application_type = $this->Session->read('application_type');
		$company_id = $this->Session->read('company_id');
		$state = $this->Session->read('state');
		$district = $this->Session->read('district');
		$search_from_date = $this->Session->read('search_from_date');
		$search_to_date = $this->Session->read('search_to_date');

		$download_application_type = $this->Session->read('application_type');
		$download_company_id = $this->Session->read('company_id');
		$download_state = $this->Session->read('state');
		$download_district = $this->Session->read('district');
		$download_search_from_date = $this->Session->read('search_from_date');
		$download_search_to_date = $this->Session->read('search_to_date');

		$search_flag = 'off'; // added by Ankur
		$download_report = 'no';

		if (null != ($this->request->getData('search_logs')) || null != ($this->request->getData('download_report'))) {
			$search_flag = 'on'; // added by Ankur

			//Check not empty "Download Report as Excel" button Request, if condition TRUE then set value "yes" for "Download Report as Excel" click event
			//(Done by pravin 14-03-2018)
			if (!empty($this->request->getData('download_report'))) {
				$download_report = 'yes';
			}

			$company_id = htmlentities($this->request->getData('company_id'), ENT_QUOTES);
			$application_type = $this->request->getData('application_type');

			$table = 'DmiStates';
			$post_input_request = $this->request->getData('state');
			if (!empty($post_input_request)) {
				$state = $this->Customfunctions->dropdownSelectInputCheck($table,$post_input_request); // calling library function
			} else {
				$state = '';
			}

			$table = 'DmiDistricts';
			$post_input_request = $this->request->getData('district');
			if (!empty($post_input_request)) {
				$district = $this->Customfunctions->dropdownSelectInputCheck($table,$post_input_request); // calling library function
			} else {
				$district = '';
			}

			$download_firms_data_details_result = $this->DmiFirms->newlyAddedFirmListReportConditions($download_application_type,$download_company_id,$download_state,$download_district,$download_search_from_date,$download_search_to_date,$search_flag);

			$search_from_date =  $this->request->getData('from_date');
			$search_from_date = $this->Customfunctions->dateFormatCheck($search_from_date);
			$search_to_date =  $this->request->getData('to_date');
			$search_to_date = $this->Customfunctions->dateFormatCheck($search_to_date);
			$this->date_comparison($search_from_date,$search_to_date);

			$this->Session->delete('application_type');	$this->Session->delete('company_id');
			$this->Session->delete('state'); $this->Session->delete('district');
			$this->Session->delete('search_from_date'); $this->Session->delete('search_to_date');

			$this->Session->write('application_type',$application_type); $this->Session->write('company_id',$company_id);
			$this->Session->write('search_from_date',$search_from_date); $this->Session->write('search_to_date',$search_to_date);
			$this->Session->write('state',$state); $this->Session->write('district',$district);

			$firms_data_details_result = $this->DmiFirms->newlyAddedFirmListReportConditions($application_type,$company_id,$state,$district,$search_from_date,$search_to_date,$search_flag);
			$this->newlyAddedFirmReportResult($firms_data_details_result,$application_type_array);

			if ($download_report == 'yes') {
				$this->downloadNewlyAddedFirmReportResult($download_firms_data_details_result,$application_type_array);
			}

		} else {

			$firms_data_details_result = $this->DmiFirms->newlyAddedFirmListReportConditions($application_type,$company_id,$state,$district,$search_from_date,$search_to_date,$search_flag);
			$this->newlyAddedFirmReportResult($firms_data_details_result,$application_type_array);
		}

		$this->set('application_type',$application_type);
		$this->set('state',$state); $this->set('company_id',$company_id);
		$this->set('district',$district); $this->set('search_from_date',$search_from_date);	 $this->set('search_to_date',$search_to_date);
	}


	// Newly Added Firm Report Result
	// Description : ----
	// @Author : Pravin Bhakare
	// #Contributer : Ankur Jangid (Migration)
	// Date : ----
	public function newlyAddedFirmReportResult($final_customer_id_list,$application_type_array) {

		$firms_data_details = array();
		$application_type_name = array();
		$districts = array();
		$states = array();

		if (!empty($final_customer_id_list)) {

			$final_result_firm_data = $this->DmiFirms->find('all')->select(['id', 'customer_primary_id', 'firm_name', 'certification_type', 'state', 'district',
				'created', 'customer_id'])->where(['customer_id IN'=>$final_customer_id_list])->order(['created' => 'DESC'])->group(['id', 'customer_primary_id'])
				->toArray();

			$i = 0;

			foreach ($final_result_firm_data as $firms_data) {

				$firms_data_details[$i] = $firms_data;
				$application_form_type = $this->Customfunctions->checkApplicantFormType($firms_data['customer_id']);
				$application_type_name[$i] = $application_type_array[$application_form_type];
				$districts[$i] = $this->DmiDistricts->find('all')->select(['district_name'])->where(['id' => $firms_data['district']])->extract('district_name')->first();
				$states[$i] = $this->DmiStates->find('all')->select(['state_name'])->where(['id' => $firms_data['state']])->extract('state_name')->first();
				$i=$i+1;
			}
		}

		$this->set('firms_data_details',$firms_data_details);
		$this->set('application_type_name',$application_type_name);
		$this->set('firms_districts',$districts);
		$this->set('firms_states',$states);

	}




	// Download Newly Added Firm Report Result
	// Description : ----
	// @Author : Pravin Bhakare
	// #Contributer : Ankur Jangid (Migration)
	// Date : ----

	public function downloadNewlyAddedFirmReportResult($final_customer_id_list,$application_type_array) {

		$this->viewBuilder()->setLayout('downloadpdf');

		$firms_data_details = array();
		$application_type_name = array();
		$districts = array();
		$states = array();

		if (!empty($final_customer_id_list)) {
			$conditions = ['customer_id IN' => $final_customer_id_list];
		} else {
			$conditions = ['customer_id IS' => ''];
		}

		if (!empty($final_customer_id_list)) {

			$final_result_firm_data = $this->DmiFirms->find('all')->select(['id', 'customer_primary_id', 'firm_name', 'certification_type', 'state', 'district',
			'created', 'customer_id'])->where($conditions)->group(['id', 'customer_primary_id'])->order(['created' => 'DESC'])->toArray();

			$i = 0;
			foreach ($final_result_firm_data as $firms_data) {

				$firms_data_details[$i] = $firms_data;
				$application_form_type = $this->Customfunctions->checkApplicantFormType($firms_data['customer_id']);
				$application_type_name[$i] = $application_type_array[$application_form_type];
				$districts[$i] = $this->DmiDistricts->find('all')->select(['district_name'])->where(['id' => $firms_data['district']])->extract('district_name')->first();
				$states[$i] = $this->DmiStates->find('all')->select(['state_name'])->where(['id' => $firms_data['state']])->extract('state_name')->first();
				$i=$i+1;
			}
		}

		$this->set('orders',$firms_data_details);
		$this->set('application_type_name',$application_type_name);
		$this->set('firms_districts',$districts);
		$this->set('firms_states',$states);

		$this->layout = null;
		$this->autoLayout = false;
		Configure::write('debug', '0');
		$this -> render('/element/download_report_excel_format/download_newly_added_firm_report_result');
	}




	// Primary User Details Report
	// Description : Find primary user details with his added firms details
	// @Author : Pravin Bhakare
	// #Contributer : Ankur Jangid (Migration)
	// Date : ----

	public function primaryUserDetailsReport() {

		$application_type_array = array('A'=>'CA (Form-A)','C'=>'Laboratory (Form-C)','E'=>'CA (Form-E)','B'=>'Printing Press (Form-B)','D'=>'Laboratory (Form-D)','F'=>'CA (Form-F)');

		// Change on 9/11/2018, Add order by conditions , By Pravin Bhakare
		$all_states = $this->DmiStates->find('all')->select(['id', 'state_name'])->where(['OR'=> [['delete_status IS' => null], ['delete_status ='=>'no']]])
			->order(['state_name' => 'ASC'])->combine('id', 'state_name')->toArray();

		$all_district = $this->DmiDistricts->find('all')->select(['id', 'district_name'])->where(['OR'=> [['delete_status IS' => null], ['delete_status ='=>'no']]])
			->combine('id', 'district_name')->toArray();

		$this->set('all_states', $all_states);
		$this->set('all_district', $all_district);

		$state = null;
		$district = null;
		$search_from_date = null;
		$search_to_date = null;
		$primary_firms_details = array();
		$certification_type = array();
		$primary_user_details = array();

		$state = $this->Session->read('state');
		$district = $this->Session->read('district');
		$search_from_date = $this->Session->read('search_from_date');
		$search_to_date = $this->Session->read('search_to_date');

		$download_state = $this->Session->read('state');
		$download_district = $this->Session->read('district');
		$download_search_from_date = $this->Session->read('search_from_date');
		$download_search_to_date = $this->Session->read('search_to_date');

		$search_flag = 'off'; // added by Ankur
		$download_report = 'no';

		if (null != ($this->request->getData('search_logs')) || null != ($this->request->getData('download_report'))) {

			$search_flag = 'on'; // added by Ankur
			//Check not empty "Download Report as Excel" button Request, if condition TRUE then set value "yes" for "Download Report as Excel" click event
			//(Done by pravin 14-03-2018)

			if (!empty($this->request->getData('download_report'))) {
				$download_report = 'yes';
			}

			$table = 'DmiStates';
			$post_input_request = $this->request->getData('state');

			if (!empty($post_input_request)) {
				$state = $this->Customfunctions->dropdownSelectInputCheck($table, $post_input_request); // calling library function
			} else {
				$state = '';
			}

			$table = 'DmiDistricts';
			$post_input_request = $this->request->getData('district');
			if (!empty($post_input_request)) {
				$district = $this->Customfunctions->dropdownSelectInputCheck($table, $post_input_request);//calling library function
			} else {
				$district = '';
			}

			$search_from_date =  $this->request->getData('from_date');
			$search_from_date = $this->Customfunctions->dateFormatCheck($search_from_date);
			$search_to_date =  $this->request->getData('to_date');
			$search_to_date = $this->Customfunctions->dateFormatCheck($search_to_date);
			$this->date_comparison($search_from_date, $search_to_date);

			$download_primary_user_ids = $this->DmiCustomers->primaryUserDetailsReportConditions($download_state,$download_district,$download_search_from_date,$download_search_to_date,$search_flag);

			if ($download_report == 'yes') {
				$this->downloadPrimaryUserDetailsReport($download_primary_user_ids, $all_states, $all_district, $application_type_array);
			}

			$this->Session->delete('state');
			$this->Session->delete('district');
			$this->Session->delete('search_from_date');
			$this->Session->delete('search_to_date');

			$this->Session->write('search_from_date', $search_from_date);
			$this->Session->write('search_to_date',$search_to_date);
			$this->Session->write('state',$state);
			$this->Session->write('district',$district);

			$primary_user_ids = $this->DmiCustomers->primaryUserDetailsReportConditions($state,$district,$search_from_date,$search_to_date,$search_flag);
		} else {
			$primary_user_ids = $this->DmiCustomers->primaryUserDetailsReportConditions($state,$district,$search_from_date,$search_to_date,$search_flag);
		}

		if (!empty($primary_user_ids)) {

			$user_id_list = $primary_user_ids;

			if (!empty($user_id_list)) {
				$primary_user_details = $this->DmiCustomers->find('all')->select(['id', 'district', 'state', 'created', 'customer_id'])
					->where(['customer_id IN' => $user_id_list])->order(['id' => 'DESC'])->toArray();
			} else {
				$primary_user_details = $this->DmiCustomers->find('all')->select(['id', 'district', 'state', 'created', 'customer_id'])
					->where(['customer_id IS' => ''])->order(['id' => 'DESC'])->toArray();
			}

		}


		$this->set('primary_user_details', $primary_user_details);
		$this->set('state', $state);
		$this->set('search_from_date', $search_from_date);
		$this->set('search_to_date', $search_to_date);	  // Change on 5/11/2018 , Set search_to_date value , By Pravin Bhakare
		$this->set('district', $district);

	}




	// Primary Firm Details Report
	// Description :  to fetch firm details by ajax call
	// @Author : Ankur Jangid
	// #Contributer :  ----
	// Date : ----

	public function primaryFirmDetailsReport() {

		$this->autoRender = false;

		$application_type_array = array('A'=>'CA (Form-A)','C'=>'Laboratory (Form-C)','E'=>'CA (Form-E)','B'=>'Printing Press (Form-B)','D'=>'Laboratory (Form-D)','F'=>'CA (Form-F)');
		//Change on 9/11/2018, Sorting array by ascending order - By Pravin Bhakare
		asort($application_type_array);

		$all_states = $this->DmiStates->find('all')->select(['id', 'state_name'])->where(['OR'=> [['delete_status IS' => null], ['delete_status ='=>'no']]])
			->order(['state_name' => 'ASC'])->combine('id', 'state_name')->toArray();

		$all_district = $this->DmiDistricts->find('all')->select(['id', 'district_name'])->where(['OR'=> [['delete_status IS' => null], ['delete_status ='=>'no']]])
			->combine('id', 'district_name')->toArray();

		$customer_id = $this->request->getData('data');

		$primary_firm_details = $this->DmiFirms->find('all')->select(['id', 'customer_primary_id', 'firm_name', 'certification_type', 'state', 'district',
				'created', 'customer_id'])->where(['customer_primary_id IS' => $customer_id])->order(['created'=>'DESC'])->toArray();

		if (!empty($primary_firm_details)) {
			$primary_firms_details = $primary_firm_details;

			$j = 0;
			foreach ($primary_firm_details as $certification_id) {
				$certification_type[$j] = $this->Customfunctions->checkApplicantFormType($certification_id['customer_id']);
				$j = $j+1;
			}

			for($i = 0; $i<sizeof($primary_firm_details); $i++) {

				$firm_id[$i] = $primary_firm_details[$i]['customer_id'];
				$firm_name[$i] = $primary_firm_details[$i]['firm_name'];
				$application_type[$i] = $application_type_array[$certification_type[$i]];
				$firm_state[$i] = $all_states[$primary_firm_details[$i]['state']];
				$firm_district[$i] = $all_district[$primary_firm_details[$i]['district']];
				$firm_time[$i] = $primary_firm_details[$i]['created'];
				$firms_details[$i] = ['customer_id'=>$firm_id[$i] ,'firm_name'=>$firm_name[$i] ,'certification_type'=>$application_type[$i] ,
										'state'=>$firm_state[$i] ,'district'=>$firm_district[$i] ,'created'=>$firm_time[$i]];
			}

			$response_data = [$firms_details];
		} else {
			$primary_firms_details = [];
			$response_data = $primary_firms_details;
		}

		echo json_encode($response_data);
		exit;
	}




	// Download Primary User Details Report
	// Description :  ----
	// @Author : Pravin Bhakare
	// #Contributer : Ankur Jangid (Migration)
	// Date : ----

	public function downloadPrimaryUserDetailsReport($primary_user_ids, $all_states, $all_district, $application_type_array) {

		$this->viewBuilder()->setLayout('downloadpdf');

		$primary_firms_details = array();
		$certification_type = array();
		$primary_user_details = array();

		if (!empty($primary_user_ids)) {

			$i = 0;
			foreach ( $primary_user_ids as $user_id ) {
				$user_id_list[$i] = $user_id;
				$i=$i+1;
			}

			if(!empty($user_id_list)){
				$primary_user_details = $this->DmiCustomers->find('all')->where(['customer_id IN' => $user_id_list])->order(['id' => 'DESC'])->toArray();
			}

			$i = 0;

			foreach ($primary_user_details as $user_details) {

				$primary_firm_details = $this->DmiFirms->find('all')->where(['customer_primary_id' => $user_details['customer_id']])->toArray();

				if (!empty($primary_firm_details)) {

					$primary_firms_details[$i] = $primary_firm_details;
					$j = 0;

					foreach ($primary_firm_details as $certification_id) {

						$certification_type[$i][$j] = $this->Customfunctions->checkApplicantFormType($certification_id['customer_id']);
						$j = $j+1;
					}
				} else {
					$primary_firms_details[$i] = array();
				}

				$i=$i+1;
			}
		}

		$this->set('certification_type', $certification_type);
		$this->set('primary_firms_details', $primary_firms_details);
		$this->set('primary_user_details', $primary_user_details);
		$this->set('all_states', $all_states);
		$this->set('all_district', $all_district);
		$this->set('application_type_array', $application_type_array);

		$this->layout = null;
		$this->autoLayout = false;
		Configure::write('debug', '0');
		$this -> render('/element/download_report_excel_format/download_primary_user_details_report');

	}




	// Renewal Due Application Report
	// Description :  Find renewal due applications
	// @Author : Pravin Bhakare
	// #Contributer : Ankur Jangid (Migration)
	// Date : ----

	public function renewalDueApplicationReport() {

		// Change on 9/11/2018, Add order by conditions , By Pravin Bhakare
		$all_states = $this->DmiStates->find('all')->select(['id', 'state_name'])->where(['OR' => [['delete_status IS' => null], ['delete_status ='=>'no']]])
			->order(['state_name' => 'ASC'])->combine('id', 'state_name')->toArray();

		$all_district = $this->DmiDistricts->find('all')->select(['id', 'district_name'])->where(['OR' => [['delete_status IS' => null], ['delete_status ='=>'no']]])
			->combine('id', 'district_name')->toArray();

		$all_application_type = $this->DmiCertificateTypes->find('all')->select(['id', 'certificate_type'])->combine('id', 'certificate_type')->toArray();

		$this->set('all_states',$all_states);
		$this->set('all_district',$all_district);
		$this->set('all_application_type',$all_application_type);

		$renewal_due_applications_id = array();
		$application_expiry_date = array();
		$state = '';
		$district = '';
		$renewal_user_details = array();
		$application_type = '';

		$dropdown_year_list = date('Y');

		for($i=0; $i<=6; $i++) {
			$dropdown_years[$i] = $dropdown_year_list;
			$dropdown_year_list = $dropdown_year_list+1;
		}

		$this->set('dropdown_years',$dropdown_years);

		$renewal_year = $this->Session->read('renewal_year');

		if (empty($renewal_year)) {
			$renewal_year = date('Y');
		}

		$state = $this->Session->read('state');
		$district = $this->Session->read('district');
		$application_type = $this->Session->read('application_type');

		$download_renewal_year = $renewal_year;
		$download_state = $this->Session->read('state');
		$download_district = $this->Session->read('district');
		$download_application_type = $this->Session->read('application_type');

		$search_flag = 'off'; // added by Ankur
		$download_report = 'no';

		if (null != ($this->request->getData('search_logs')) || null != ($this->request->getData('download_report'))) {

			$search_flag = 'on';
			//Check not empty "Download Report as Excel" button Request, if condition TRUE then set value "yes" for "Download Report as Excel" click event
			//(Done by pravin 14-03-2018)
			if (!empty($this->request->getData('download_report'))) {
				$download_report = 'yes';
			}

			$renewal_year = $dropdown_years[$this->request->getData('year')];
			$table = 'DmiStates';
			$post_input_request = $this->request->getData('state');

			if (!empty($post_input_request)) {
				$state = $this->Customfunctions->dropdownSelectInputCheck($table,$post_input_request); // calling library function
			} else {
				$state = '';
			}

			$table = 'DmiDistricts';
			$post_input_request = $this->request->getData('district');

			if (!empty($post_input_request)) {
				$district = $this->Customfunctions->dropdownSelectInputCheck($table,$post_input_request);//calling library function
			} else {
				$district = '';
			}

			$table = 'DmiCertificateTypes';
			$post_input_request = $this->request->getData('application_type');

			if (!empty($post_input_request)) {
				$application_type = $this->Customfunctions->dropdownSelectInputCheck($table,$post_input_request);//calling library function
			} else {
				$application_type = '';
			}

			$download_final_customer_id = $this->DmiGrantCertificatesPdfs->renewalDueReportConditions($download_renewal_year,$download_state,$download_district,$download_application_type);

			if ($download_report == 'yes') {
				$this->downloadRenewalDueApplicationReport($download_final_customer_id,$all_states,$all_district,$all_application_type);
			}

			$this->Session->delete('state');
			$this->Session->delete('district');
			$this->Session->delete('renewal_year');
			$this->Session->delete('application_type');

			$this->Session->write('renewal_year',$renewal_year);
			$this->Session->write('application_type',$application_type);
			$this->Session->write('state',$state);
			$this->Session->write('district',$district);

			$final_customer_id = $this->DmiGrantCertificatesPdfs->renewalDueReportConditions($renewal_year,$state,$district,$application_type);

		} else {
			$final_customer_id = $this->DmiGrantCertificatesPdfs->renewalDueReportConditions($renewal_year,$state,$district,$application_type);
		}

		//to fetch id data which entry is not in rejected table added by laxmi B. on 23-01-23
		$this->loadModel('DmiRejectedApplLogs');
		$rejectedList = $this->DmiRejectedApplLogs->find('all')->select(['id','customer_id'])->order(['id','customer_id'])->combine('id','customer_id')->toArray();
		$final_customer_id = array_diff($final_customer_id, $rejectedList);

		if (!empty($final_customer_id)) {

			if (null != ($this->request->getData('search_logs')) || null != ($this->request->getData('download_report'))) {
				$renewal_user_details = $this->DmiFirms->find('all')->where(['customer_id IN' => $final_customer_id])->order(['id', 'certification_type'])->toArray();
			} else {
				$renewal_user_details = $this->DmiFirms->find('all')->where(['customer_id IN' => $final_customer_id])->order(['id', 'certification_type'])->limit(['100'])->toArray();
			}


			$i=0;
			foreach ($renewal_user_details as $application_id) {

				$granted_application_id = $this->DmiGrantCertificatesPdfs->find('all')->select(['id'])->where(['customer_id' => $application_id['customer_id']])->combine('id', 'id')->toArray();

				$granted_application_details = $this->DmiGrantCertificatesPdfs->find('all')->select(['customer_id', 'date'])->where(['id' => max($granted_application_id)])->first();

				$application_renewal_date = $this->Customfunctions->getCertificateValidUptoDate($granted_application_details['customer_id'],$granted_application_details['date']);

				$application_expiry_date[$i] = $application_renewal_date;
				$renewal_due_applications_id[$i] = $granted_application_details;
				$i=$i+1;
			}
		}

		$this->set('renewal_user_details',$renewal_user_details);
		$this->set('renewal_due_applications_id',$renewal_due_applications_id);
		$this->set('application_expiry_date',$application_expiry_date);

		$this->set('renewal_year',$renewal_year);
		$this->set('state',$state);
		$this->set('district',$district);
		$this->set('application_type',$application_type);

	}



	// Download Renewal Due Application Report
	// Description : ----
	// @Author : Pravin Bhakare
	// #Contributer : Ankur Jangid (Migration)
	// Date : 5/11/2018

	public function downloadRenewalDueApplicationReport($download_final_customer_id,$all_states,$all_district,$all_application_type) {

		$this->viewBuilder()->setLayout('downloadpdf');

		// Change on 5/11/2018, Set values for download report , By Pravin Bhakare 5/11/2018
		$renewal_user_details = '';
		$renewal_due_applications_id = [];
		$application_expiry_date = [];

		if (!empty($download_final_customer_id)) {

			if (!empty($download_final_customer_id)) {
				$condition = ['customer_id IN'=>$download_final_customer_id];
			} else {
				$condition = ['customer_id IS'=>''];
			}

			$renewal_user_details = $this->DmiFirms->find('all')->select(['id','customer_id', 'certification_type', 'state', 'district'])->where($condition)->order(['id', 'certification_type'])->toArray();

			$i=0;
			foreach ($renewal_user_details as $application_id) {

				$granted_application_id = $this->DmiGrantCertificatesPdfs->find('all')->select(['id'])->where(['customer_id'=>$application_id['customer_id']])->combine('id', 'id')->toArray();

				// $granted_application_details = $this->Dmi_grant_certificates_pdf->find('first',array('conditions'=>array('id'=>max($granted_application_id))));
				$granted_application_details = $this->DmiGrantCertificatesPdfs->find('all')->select(['customer_id', 'date'])->where(['id'=>max($granted_application_id)])->first();

				$application_renewal_date = $this->Customfunctions->getCertificateValidUptoDate($granted_application_details['customer_id'],$granted_application_details['date']);

				$application_expiry_date[$i] = $application_renewal_date;
				$renewal_due_applications_id[$i] = $granted_application_details;
				$i=$i+1;
			}
		}

		$this->set('renewal_user_details',$renewal_user_details);
		$this->set('renewal_due_applications_id',$renewal_due_applications_id);
		$this->set('application_expiry_date',$application_expiry_date);
		$this->set('all_states',$all_states);
		$this->set('all_district',$all_district);
		$this->set('all_application_type',$all_application_type);

		$this->layout = null;
		$this->autoLayout = false;
		Configure::write('debug', '0');
		$this -> render('/element/download_report_excel_format/download_renewal_due_application_report');

	}





	// Payment Details Report
	// Description : ----
	// @Author : Pravin Bhakare
	// #Contributer : Ankur Jangid (Migration)
	// Date : 27-07-2018

	// added new parameter for show application type by shreeya on date [07-06-2023]
	public function paymentDetailsReport($applicn_type) {

		//added for show the application_type by shreeya on date [07-06-2023]
		$applicn_type=base64_decode($applicn_type);

		$this->loadModel('DmiApplicationTypes');
		//show the all application type added by shreeya on date 13-06-2023
		$application_type_data = $this->DmiApplicationTypes->find('all')->select(['id', 'application_type'])->where(['delete_status IS NULL','application_type'=>$applicn_type])->toArray();
		$report_for = ''; // Initialize $report_for with a default value
		if(!empty($application_type_data)){
			$report_for = $application_type_data[0]['id'];
			
		}

		$this->set('report_for',$report_for);

		
		//load models
		$this->loadModel('DmiApplicationTypes');
		$this->loadModel('DmiFlowWiseTablesLists');
		//new model according to application type by shreeya on date [22-06-2023]
		$this->loadModel('DmiChangePaymentDetails');
		$this->loadModel('DmiRenewalApplicantPaymentDetails');
		$this->loadModel('DmiChangePaymentDetails');
		$this->loadModel('DmiChemistPaymentDetails');
		$this->loadModel('DmiAdvPaymentDetails');

		$all_states = $this->DmiStates->find('all')->select(['id', 'state_name'])->where(['OR' => [['delete_status IS NULL'] ,['delete_status ='=>'no']]])
			->order(['state_name'])->combine('id', 'state_name')->toArray();

		$all_district = $this->DmiDistricts->find('all')->select(['id', 'district_name'])->where(['OR' => [['delete_status IS NULL'], ['delete_status ='=>'no']]])
			->combine('id', 'district_name')->toArray();

		$all_certificate_type = $this->DmiCertificateTypes->find('all')->select(['id','certificate_type'])->combine('id', 'certificate_type')->toArray();
		
		//added 'office_type'=>'RO' condition on 27-07-2018
		// Change on 5/11/2018, Add order by conditions , By Pravin Bhakare

		$all_ro_office = $this->DmiRoOffices->find('all')->select(['id', 'ro_office'])->where([['delete_status IS NULL'],'OR' => [['office_type' => 'RO'], ['office_type' => 'SO']]])
			->order(['ro_office'])->combine('id', 'ro_office')->toArray();


		//show the all application type added by shreeya on date 13-06-2023
		//$application_array = $this->DmiApplicationTypes->find('all')->select(['id', 'application_type'])->where(['delete_status IS NULL'])->order(['id'])->combine('id', 'application_type')->toArray();
		
		$application_array = $this->DmiApplicationTypes->find('all')
						->select(['id', 'application_type'])
						->where(['delete_status IS NULL', 'id IN' => $this->Session->read('applTypeArray')])
						->order(['id'])
						->combine('id', 'application_type')
						->toArray();

		
		// Change on 5/11/2018, set search_to_date value, By Pravin 5/11/2018
		$certificate_type = '';
		$ro_office='';
		$state='';
		$district='';
		$search_from_date='';
		$search_to_date='';

		$firm_customer_id_list ='';
		$grant_total =array();
		$total_payment_details =array();
		$ro_id =array();
		$firms_details =array();
		$customer_payment_details =array();
		$payment_max_id =array();
		$ca_application_payment_total =array();
		$chemist_application_payment_total = array(); //new added
		$printing_application_payment_total =array();
		$laboratory_application_payment_total =array();
		$ca_payment = null; // $ca_payment = ''; commented by Ankur
		$printing_payment = null; // $printing_payment = '';
		$lab_payment = null; // $lab_payment = '';

		$renewal_total_payment_details =array();
		$renewal_ro_id =array();
		$renewal_firms_details =array();
		$renewal_customer_payment_details =array();
		$renewal_payment_max_id =array();
		$renewal_ca_application_payment_total =array();
		$renewal_printing_application_payment_total =array();
		$renewal_laboratory_application_payment_total =array();
		$renewal_ca_payment = null; // $renewal_ca_payment = '';
		$renewal_printing_payment = null; // $renewal_printing_payment = '';
		$renewal_lab_payment = null; // $renewal_lab_payment = '';


		$i=1;
		$new_ca_total = 0;  //default 0
		$new_pp_total = 0;  //default 0
		$new_lab_total = 0; //default 0

		$renewal_ca_total = 0; //default 0
		$renewal_pp_total = 0; //default 0
		$renewal_lab_total = 0; //default 0

		$change_ca_total = 0; //default 0
		$change_pp_total = 0; //default 0
		$change_lAB_total = 0; //default 0

		$chemist_total = 0; //default 0
		$fiftin_digit_total = 0;
		$ecode_total = 0;
		$adp_total = 0;
		$adv_total = 0;
		$rti_total = 0;
		$bgr_total = 0;

		$total_new_ca_pp_lab = 0;
		$total_renewal_ca_pp_lab = 0;
		$total_change_ca_pp_lab = 0;


		$sum_of_all_total_status = false; // added by shreeya [21/06/2023]


		//sum of revenue new application by shreeya on date [20-06-2023]
		$newApplicationrevenue_Query = $this->DmiApplicantPaymentDetails->find('all')
						->where(['payment_confirmation' => 'confirmed'])->sumOf('amount_paid'); // updated by Ankur
		$newApplicationrevenue = ['sum' => $newApplicationrevenue_Query];

		//sum of revenue renewal application by shreeya on date [20-06-2023]
		$renewalApplicationrevenue_Query =$this->DmiRenewalApplicantPaymentDetails->find('all')
						->where(['payment_confirmation' => 'confirmed'])->sumOf('amount_paid');
		$renewalApplicationrevenue = ['sum' => $renewalApplicationrevenue_Query];

		//sum of revenue change application by shreeya on date [20-06-2023]
		$changeApplicationrevenue_Query =$this->DmiChangePaymentDetails->find('all')
						->where(['payment_confirmation' => 'confirmed'])->sumOf('amount_paid');
		$changeApplicationrevenue = ['sum' => $changeApplicationrevenue_Query];

		//sum of chemist application by shreeya on date [20-06-2023]
		$chemistApplicationrevenue_Query =$this->DmiChemistPaymentDetails->find('all')
						->where(['payment_confirmation' => 'confirmed'])->sumOf('amount_paid');
		$chemistApplicationrevenue = ['sum' => $chemistApplicationrevenue_Query];

		//sum for adv renewal application by shreeya on date [20-06-2023]
		$advApplicationrevenue_Query =$this->DmiAdvPaymentDetails->find('all')
						->where(['payment_confirmation' => 'confirmed'])->sumOf('amount_paid');
		$advApplicationrevenue = ['sum' => $advApplicationrevenue_Query];

		$newrevenue = $this->thousandsCurrencyFormat($newApplicationrevenue['sum']);
		$renewalrevenue = $this->thousandsCurrencyFormat($renewalApplicationrevenue['sum']);
		$changerevenue =$this->thousandsCurrencyFormat($changeApplicationrevenue['sum']);
		$chemistrevenue = $this->thousandsCurrencyFormat($chemistApplicationrevenue['sum']);
		$advrevenue = $this->thousandsCurrencyFormat($advApplicationrevenue['sum']);

		$sum_of_all_total = $this->thousandsCurrencyFormat($newApplicationrevenue['sum']+$renewalApplicationrevenue['sum']+$changeApplicationrevenue['sum']+
		$chemistApplicationrevenue['sum']+$advApplicationrevenue['sum']);


		//first show  all  type of application by shreeya on date [17-06-2023]
		if (null!==($this->request->getData('search_logs')))
		{

			//sum of revenue new application by shreeya on date [20-06-2023]
			$newApplicationrevenue_Query = $this->DmiApplicantPaymentDetails->find('all')
			->where(['payment_confirmation' => 'confirmed'])->sumOf('amount_paid'); // updated by Ankur
			$newApplicationrevenue = ['sum' => $newApplicationrevenue_Query];

			//sum of revenue renewal application by shreeya on date [20-06-2023]
			$renewalApplicationrevenue_Query =$this->DmiRenewalApplicantPaymentDetails->find('all')
					->where(['payment_confirmation' => 'confirmed'])->sumOf('amount_paid');
			$renewalApplicationrevenue = ['sum' => $renewalApplicationrevenue_Query];

			//sum of revenue change application by shreeya on date [20-06-2023]
			$changeApplicationrevenue_Query =$this->DmiChangePaymentDetails->find('all')
					->where(['payment_confirmation' => 'confirmed'])->sumOf('amount_paid');
			$changeApplicationrevenue = ['sum' => $changeApplicationrevenue_Query];

			//sum of chemist application by shreeya on date [20-06-2023]
			$chemistApplicationrevenue_Query =$this->DmiChemistPaymentDetails->find('all')
					->where(['payment_confirmation' => 'confirmed'])->sumOf('amount_paid');
			$chemistApplicationrevenue = ['sum' => $chemistApplicationrevenue_Query];

			//sum for adv renewal application by shreeya on date [20-06-2023]
			$advApplicationrevenue_Query =$this->DmiAdvPaymentDetails->find('all')
					->where(['payment_confirmation' => 'confirmed'])->sumOf('amount_paid');
			$advApplicationrevenue = ['sum' => $advApplicationrevenue_Query];

			$newrevenue = $this->thousandsCurrencyFormat($newApplicationrevenue['sum']);
			$renewalrevenue = $this->thousandsCurrencyFormat($renewalApplicationrevenue['sum']);
			$changerevenue =$this->thousandsCurrencyFormat($changeApplicationrevenue['sum']);
			$chemistrevenue = $this->thousandsCurrencyFormat($chemistApplicationrevenue['sum']);
			$advrevenue = $this->thousandsCurrencyFormat($advApplicationrevenue['sum']);

			$sum_of_all_total = $this->thousandsCurrencyFormat($newApplicationrevenue['sum']+$renewalApplicationrevenue['sum']+$changeApplicationrevenue['sum']+
			$chemistApplicationrevenue['sum']+$advApplicationrevenue['sum']);
			$all_certificate_type = $this->DmiCertificateTypes->find('all')->select(['id','certificate_type'])->combine('id', 'certificate_type')->toArray();

			$this->loadModel('DmiApplicationTypes');
			//show the all application type added by shreeya on date 13-06-2023
			$application_type_data = $this->DmiApplicationTypes->find('all')->select(['id', 'application_type'])->where(['delete_status IS NULL','application_type'=>$applicn_type])->toArray();
		
			$report_for = ''; // Initialize $report_for with a default value
			if(!empty($application_type_data)){
				
				$report_for = $application_type_data[0]['application_type'];
				
			}

			$this->set('report_for',$report_for);

			

			$report_for_array_select = $this->request->getData('report_for');
			$report_for = $report_for_array_select;
			

			//when search application show payment count according to application type
			//by shreeya on date [23-2023]
			if($report_for == 1){

				//sum of revenue new application by shreeya on date [23-06-2023]
				$newApplicationrevenue_Query = $this->DmiApplicantPaymentDetails->find('all')
					->where(['payment_confirmation' => 'confirmed'])->sumOf('amount_paid'); // updated by Ankur
				$newApplicationrevenue = ['sum' => $newApplicationrevenue_Query];
				$newrevenue = $this->thousandsCurrencyFormat($newApplicationrevenue['sum']);
				$sum_of_all_total = $newrevenue;
			}
			elseif($report_for == 2){
				//sum of revenue renewal application by shreeya on date [23-06-2023]
				$renewalApplicationrevenue_Query =$this->DmiRenewalApplicantPaymentDetails->find('all')
					->where(['payment_confirmation' => 'confirmed'])->sumOf('amount_paid'); 
				$renewalApplicationrevenue = ['sum' => $renewalApplicationrevenue_Query];
				$renewalrevenue = $this->thousandsCurrencyFormat($renewalApplicationrevenue['sum']);
				$sum_of_all_total = $renewalrevenue;
			}
			elseif($report_for == 3){
				//sum of revenue change application by shreeya on date [23-06-2023]
				$changeApplicationrevenue_Query =$this->DmiChangePaymentDetails->find('all')
					->where(['payment_confirmation' => 'confirmed'])->sumOf('amount_paid'); 
				$changeApplicationrevenue = ['sum' => $changeApplicationrevenue_Query];	
				$changerevenue =$this->thousandsCurrencyFormat($changeApplicationrevenue['sum']);
				$sum_of_all_total = $changerevenue;

			}elseif($report_for == 4 ){
				//sum of chemist application by shreeya on date [23-06-2023]
				$chemistApplicationrevenue_Query =$this->DmiChemistPaymentDetails->find('all')
						->where(['payment_confirmation' => 'confirmed'])->sumOf('amount_paid'); 
				$chemistApplicationrevenue = ['sum' => $chemistApplicationrevenue_Query];
				$chemistrevenue = $this->thousandsCurrencyFormat($chemistApplicationrevenue['sum']);
				$sum_of_all_total = $chemistrevenue;
			}
			elseif($report_for == 7){
				//sum for adv renewal application by shreeya on date [23-06-2023]
				$advApplicationrevenue_Query =$this->DmiAdvPaymentDetails->find('all')
					->where(['payment_confirmation' => 'confirmed'])->sumOf('amount_paid'); 
				$advApplicationrevenue = ['sum' => $advApplicationrevenue_Query];
				$advrevenue = $this->thousandsCurrencyFormat($advApplicationrevenue['sum']);
				$sum_of_all_total = $advrevenue;

			}

			$table = 'DmiStates';


			$post_input_request = $this->request->getData('state');

			if (!empty($post_input_request)) {
				$state = $this->Customfunctions->dropdownSelectInputCheck($table, $post_input_request); //calling library function
			} else {
				$state = '';
			}

			$table = 'DmiDistricts';
			$post_input_request = $this->request->getData('district');

			if (!empty($post_input_request)) {
				$district = $this->Customfunctions->dropdownSelectInputCheck($table,$post_input_request); //calling library function
			} else {
				$district = '';
			}

			$table = 'DmiCertificateTypes';
			$post_input_request = $this->request->getData('application_type');

			if (!empty($post_input_request)) {
				$certificate_type = $this->Customfunctions->dropdownSelectInputCheck($table,$post_input_request); //calling library function
			} else {
				$certificate_type = '';
			}
		
			
			$table = 'DmiRoOffices';
			$post_input_request = $this->request->getData('office');

			if (!empty($post_input_request)) {
				$ro_office = $this->Customfunctions->dropdownSelectInputCheck($table,$post_input_request); //calling library function
			} else {
				$ro_office = '';
			}

			$search_from_date =  $this->request->getData('from_date');
			$search_from_date = $this->Customfunctions->dateFormatCheck($search_from_date);
			$search_to_date =  $this->request->getData('to_date');
			$search_to_date = $this->Customfunctions->dateFormatCheck($search_to_date);
			$this->date_comparison($search_from_date,$search_to_date);


			$certificate_type_not_empty = array();
			$ro_office_not_empty = array();
			$state_not_empty = array();
			$district_not_empty = array();


			if ($certificate_type != '') {
				$certificate_type_not_empty = ['certification_type IS' => $certificate_type];
				
			}

			if ($ro_office != '') {
				$ro_office_not_empty = ['DmiDistricts.ro_id IS' => $ro_office];
			}

			if ($state != '') {
				$state_not_empty = ['state IS' => $state];
			}

			if ($district != '') {
				$district_not_empty = ['district IS' => $district];
			}



			if ($certificate_type != '' || $ro_office != '' || $state != '' || $district != '' || $search_from_date != '' || $search_to_date != '')
			{
					// added by shreeya on date [29-08-2023]
					if($certificate_type != '' && $report_for != '' && $search_from_date != '' && $search_to_date != '') {
					
						$application_array = $this->DmiApplicationTypes->find('all')
						->select(['id', 'application_type'])
						->where(['delete_status IS NULL', 'id IN' => $this->Session->read('applTypeArray')])
						->order(['id'])
						->combine('id', 'application_type')
						->toArray();

						$application_key = array_keys($application_array);
						

						if(empty($report_for) || $report_for == ''){

							$flowwise_table_data = $this->DmiFlowWiseTablesLists->find('all')->select(['id','payment','application_type'])->where(['application_type IN' =>$application_key])->toArray();

						}else{
							$flowwise_table_data = $this->DmiFlowWiseTablesLists->find('all')->select(['id','payment','application_type'])->where(['payment IS NOT NULL','application_type IN'=>$report_for])->toArray();
							//'application_type IN'=>array('3','4','7') 'application_type IN'=>$report_for

						}

						
						$i=0;
						$total_payment_details = [];
						$ca_payment = [];
						$printing_payment = [];
						$lab_payment = [];
						$apl_type_res = [];
						$application_type_id = '';

						foreach ($flowwise_table_data as $FlowWise_Tables){

							$apl_type = $FlowWise_Tables['application_type'];
							$payment_table = $FlowWise_Tables['payment'];
							$this->loadModel($payment_table);
							$query_cil = $this->$payment_table->find('all');

							//by default show all application recordes ..
							//By Shreeya on Date [12-06-2023]

							if($report_for == 2){

								//change the query remove group for renewal application  by shreeya on date 13-06-2023
								//$customer_id_list = $query_cil->select('customer_id')->where(['payment_confirmation' => 'confirmed'])->toArray();
								$customer_id_list = $query_cil
									->where([
										'payment_confirmation' => 'confirmed',
										'certificate_type' => $certificate_type
										])
									->toArray();	
				
							}
							elseif($report_for != '' || $report_for == 0 ){

								// $customer_id_list = $query_cil->select(['customer_id','certificate_type'])
								// ->where(['certificate_type'=> $certificate_type, 'payment_confirmation' => 'confirmed'])
								// ->toArray();
						
								$customer_id_list = $query_cil->find('all')
									->where([
										'certificate_type' => $certificate_type,
										'payment_confirmation' => 'confirmed',
										'created >=' => $search_from_date,
										'created <=' => $search_to_date
									])
									->toArray();

							}
							
							
						
							$k=0;
							foreach ($customer_id_list as $customer_id) {

								$customer_payment_id_list = $this->$payment_table->find('all')->select(['id','certificate_type','customer_id'])->where(['customer_id' => $customer_id['customer_id'], 'payment_confirmation' => 'confirmed'])->toArray();
								
								if (!empty($customer_payment_id_list)) {

									$split_customer_id = explode('/',$customer_id['customer_id']);

									if ($split_customer_id[1] == 1){ 
										$ca_application_payment_total[$i] = $i;
									}elseif ($split_customer_id[1] == 2) {
										 $printing_application_payment_total[$i] = $i;
									}elseif ($split_customer_id[1] == 3) {
										 $laboratory_application_payment_total[$i] = $i;
									}else{
										$payment_max_id[$i][$k] = $customer_payment_id_list[0]['id'];
									}

									$payment_max_id[$i][$k] = $customer_payment_id_list[0]['id'];

									$customer_payment_details[$i][$k] =  $this->$payment_table
										->find('all')
										->where(['id IN' => $customer_payment_id_list[0]['id']])
										->first();
									
									//added by application type Chemist Approval by shreeya on date on 15-06-2023
									if (isset($application_array[4])) {
										$this->loadModel('DmiChemistRegistrations');
										$chemist_details= $this->DmiChemistRegistrations->find('all')->where(['chemist_id' => $customer_id['customer_id'],['delete_status IS NULL']])->first();

										if(!empty($chemist_details)){
											$customer_id['customer_id'] = $chemist_details['created_by'];

										}

									}

									$firms_details[$i][$k] = $this->DmiFirms->find('all')->where(['customer_id IS' => $customer_id['customer_id'],['delete_status IS NULL']])->order(['customer_id' => 'ASC'])->first();
									
									$this->loadModel('DmiApplicationTypes');
									$apl_type_res1 =  $this->DmiApplicationTypes->find('all')->select(['id','application_type'])->where(['id' => $apl_type])->first();
									$apl_type_res[$i][$k] = $apl_type_res1['application_type'];
								
									if($firms_details[$i][$k] != NULL){
										$ro_id[$i][$k] = $this->DmiDistricts->find('all')->select(['ro_id'])->where(['id IS' => $firms_details[$i][$k]['district']])->first();
										//$i=$i+1;

									}
									$k++;
								}
							}
							$i++;
						}


						$payment_data = $this->DmiFlowWiseTablesLists->find('all')
						->select(['id', 'payment', 'application_type'])
						->where(['payment IS NOT NULL', 'application_type IN' => $this->Session->read('applTypeArray')])
						->order(['id'])
						->toArray();

						
						
						$j=1;
						$application_list_data = [];
						foreach ($payment_data as $payment_value) {

							$tbl_data = $payment_value['payment'];
							$this->loadModel($tbl_data);

							$application_list_data[$j] = $this->$tbl_data->find('all')
								->select(['id', 'customer_id', 'certificate_type', 'amount_paid', 'payment_confirmation', 'transaction_date'])
								->where([
									'payment_confirmation' => 'confirmed',
									'certificate_type' => $certificate_type,      
									'created >=' => $search_from_date,
									'created <=' => $search_to_date
									
								])
								->toArray();

							$j++;
						}

						
						
						//for new
						foreach ($application_list_data[1] as $resultArr) {

							$certiifctaetype = $resultArr['certificate_type'];
							
							
							if($certiifctaetype == 1){
								$new_ca_total = $new_ca_total + $resultArr['amount_paid'];  // store total amt of newca
							}
							if($certiifctaetype == 2){
								$new_pp_total = $new_pp_total + $resultArr['amount_paid'];  // store total amt of newpp
							}
							if($certiifctaetype == 3){
								$new_lab_total = $new_lab_total + $resultArr['amount_paid'];  // store total amt of newlab
							}
							
						}
						//for renewal
						foreach ($application_list_data[2] as $resultArr) {
							
							$certiifctaetype = $resultArr['certificate_type'];
							
							if($certiifctaetype == 1){
								$renewal_ca_total = $renewal_ca_total + $resultArr['amount_paid'];  // store total amt of renewalCA
							}
							if($certiifctaetype == 2){
								$renewal_pp_total = $renewal_pp_total + $resultArr['amount_paid'];  // store total amt of renewalPP
							}
							if($certiifctaetype == 3){
								$renewal_lab_total = $renewal_lab_total + $resultArr['amount_paid'];  // store total amt of renewalLAB
							}
							
						}
							
						// for change
						foreach ($application_list_data[3] as $resultArr) {
						
							$certiifctaetype = $resultArr['certificate_type'];
							
							
							if($certiifctaetype == 1){
								$change_ca_total = $change_ca_total + $resultArr['amount_paid'];  // store total amt of ChangeCA
							}
							if($certiifctaetype == 2){
								$change_pp_total = $change_pp_total + $resultArr['amount_paid'];  // store total amt of ChangePP
							}
							if($certiifctaetype == 3){
								$change_lAB_total = $change_lAB_total + $resultArr['amount_paid'];  // store total amt of ChangeLAB
							}
						
						}
						
						// for chemist
						foreach ($application_list_data[4] as $resultArr) {
						
							$chemist_total = $chemist_total + $resultArr['amount_paid'];
						}
						
						// for adv
						foreach ($application_list_data[7] as $resultArr) {
							$adv_total = $adv_total + $resultArr['amount_paid'];
						}

							
					}
					// (not empty certificate type & application type )added by shreeya on date [29-08-2023]
					elseif($certificate_type != '' && $report_for != ''){
						$application_array = $this->DmiApplicationTypes->find('all')
						->select(['id', 'application_type'])
						->where(['delete_status IS NULL', 'id IN' => $this->Session->read('applTypeArray')])
						->order(['id'])
						->combine('id', 'application_type')
						->toArray();

						$application_key = array_keys($application_array);
						

						if(empty($report_for) || $report_for == ''){

							$flowwise_table_data = $this->DmiFlowWiseTablesLists->find('all')->select(['id','payment','application_type'])->where(['application_type IN' =>$application_key])->toArray();

						}else{
							$flowwise_table_data = $this->DmiFlowWiseTablesLists->find('all')->select(['id','payment','application_type'])->where(['payment IS NOT NULL','application_type IN'=>$report_for])->toArray();
							//'application_type IN'=>array('3','4','7') 'application_type IN'=>$report_for

						}

						
						$i=0;
						$total_payment_details = [];
						$ca_payment = [];
						$printing_payment = [];
						$lab_payment = [];
						$apl_type_res = [];
						$application_type_id = '';

						foreach ($flowwise_table_data as $FlowWise_Tables){

							$apl_type = $FlowWise_Tables['application_type'];
							$payment_table = $FlowWise_Tables['payment'];
							$this->loadModel($payment_table);
							$query_cil = $this->$payment_table->find('all');

							
							if($report_for != '' || $report_for == 0 ){

								$customer_id_list = $query_cil->select(['customer_id','certificate_type'])
								->where(['certificate_type'=> $certificate_type, 'payment_confirmation' => 'confirmed'])
								->toArray();
						
							}
							
							$k=0;
							foreach ($customer_id_list as $customer_id) {

								$customer_payment_id_list = $this->$payment_table->find('all')->where(['customer_id' => $customer_id['customer_id'], 'payment_confirmation' => 'confirmed'])->toArray();
								
								if (!empty($customer_payment_id_list)) {

									$split_customer_id = explode('/',$customer_id['customer_id']);

									if ($split_customer_id[1] == 1){ 
										$ca_application_payment_total[$i] = $i;
									}elseif ($split_customer_id[1] == 2) {
										 $printing_application_payment_total[$i] = $i;
									}elseif ($split_customer_id[1] == 3) {
										 $laboratory_application_payment_total[$i] = $i;
									}else{
										$payment_max_id[$i][$k] = $customer_payment_id_list[0]['id'];
									}

									$payment_max_id[$i][$k] = $customer_payment_id_list[0]['id'];

									$customer_payment_details[$i][$k] =  $this->$payment_table
										->find('all')
										->where(['id IN' => $customer_payment_id_list[0]['id']])
										->first();
									
									//added by application type Chemist Approval by shreeya on date on 15-06-2023
									if (isset($application_array[4])) {
										$this->loadModel('DmiChemistRegistrations');
										$chemist_details= $this->DmiChemistRegistrations->find('all')->where(['chemist_id' => $customer_id['customer_id'],['delete_status IS NULL']])->first();

										if(!empty($chemist_details)){
											$customer_id['customer_id'] = $chemist_details['created_by'];

										}

									}

									$firms_details[$i][$k] = $this->DmiFirms->find('all')->where(['customer_id IS' => $customer_id['customer_id'],['delete_status IS NULL']])->order(['customer_id' => 'ASC'])->first();
									
									$this->loadModel('DmiApplicationTypes');
									$apl_type_res1 =  $this->DmiApplicationTypes->find('all')->select(['id','application_type'])->where(['id' => $apl_type])->first();
									$apl_type_res[$i][$k] = $apl_type_res1['application_type'];
								
									if($firms_details[$i][$k] != NULL){
										$ro_id[$i][$k] = $this->DmiDistricts->find('all')->select(['ro_id'])->where(['id IS' => $firms_details[$i][$k]['district']])->first();
										//$i=$i+1;

									}
									$k++;
								}

								
							}
							$i++;
						}
						
						
						$payment_data = $this->DmiFlowWiseTablesLists->find('all')
						->select(['id', 'payment', 'application_type'])
						->where(['payment IS NOT NULL', 'application_type IN' => $this->Session->read('applTypeArray')])
						->order(['id'])
						->toArray();

						
						
						$j=1;
						$application_list_data = [];
						foreach ($payment_data as $payment_value) {

							$tbl_data = $payment_value['payment'];
							$this->loadModel($tbl_data);

							$application_list_data[$j] = $this->$tbl_data->find('all')
								->select(['id', 'customer_id', 'certificate_type', 'amount_paid', 'payment_confirmation', 'transaction_date'])
								->where([
									'payment_confirmation' => 'confirmed',
									'certificate_type' => $certificate_type	
								])
								->toArray();

								$j++;		
						}
						/*********************************************************************************************/
						// Initialize an array to store the total amounts for each certificate type
						$totalAmounts = [];

						// Loop through the application list data and calculate the total amount for each certificate type
						foreach ($application_list_data as $type => $applicationList) {
							
							$totalAmounts[$type] = 0; // Initialize total amount for this certificate type

							foreach ($applicationList as $resultArr) {
								$totalAmounts[$type] += $resultArr['amount_paid'];
							}
						}
						
						foreach ($totalAmounts as $type => $totalAmount) {
							echo "Total amount for Certificate Type $type: " . $this->thousandsCurrencyFormat($totalAmount) . "<br>";
						}

						
						/*********************************************************************************************/

						//for new
						foreach ($application_list_data[1] as $resultArr) {
						
							$certiifctaetype = $resultArr['certificate_type'];
							
							
							if($certiifctaetype == 1){
								$new_ca_total = $new_ca_total + $resultArr['amount_paid'];  // store total amt of newca
							}
							if($certiifctaetype == 2){
								$new_pp_total = $new_pp_total + $resultArr['amount_paid'];  // store total amt of newpp
							}
							if($certiifctaetype == 3){
								$new_lab_total = $new_lab_total + $resultArr['amount_paid'];  // store total amt of newlab
							}
							
						}
					
						//for renewal
						foreach ($application_list_data[2] as $resultArr) {
							
							$certiifctaetype = $resultArr['certificate_type'];

						

							if($certiifctaetype == 1){
								$renewal_ca_total = $renewal_ca_total + $resultArr['amount_paid'];  // store total amt of renewalCA
							}
							if($certiifctaetype == 2){
								$renewal_pp_total = $renewal_pp_total + $resultArr['amount_paid'];  // store total amt of renewalPP
							}
							if($certiifctaetype == 3){
								$renewal_lab_total = $renewal_lab_total + $resultArr['amount_paid'];  // store total amt of renewalLAB
							
							}
							
						}
					
							
						// for change
						foreach ($application_list_data[3] as $resultArr) {
						
							$certiifctaetype = $resultArr['certificate_type'];
							
							
							if($certiifctaetype == 1){
								$change_ca_total = $change_ca_total + $resultArr['amount_paid'];  // store total amt of ChangeCA
							}
							if($certiifctaetype == 2){
								$change_pp_total = $change_pp_total + $resultArr['amount_paid'];  // store total amt of ChangePP
							}
							if($certiifctaetype == 3){
								$change_lAB_total = $change_lAB_total + $resultArr['amount_paid'];  // store total amt of ChangeLAB
							}
						
						}
						
						// for chemist
						foreach ($application_list_data[4] as $resultArr) {
						
							$chemist_total = $chemist_total + $resultArr['amount_paid'];
						}
						
						// for adv
						foreach ($application_list_data[7] as $resultArr) {
							$adv_total = $adv_total + $resultArr['amount_paid'];
						}

					}
					else{

						$firm_customer_id_list = $this->DmiFirms->find('all')
									->select(['id', 'customer_id'])
									->join(['DmiDistricts' => ['table' => 'dmi_districts', 'type' => 'INNER',
											'conditions' => ['DmiDistricts.id = DmiFirms.district::integer', $ro_office_not_empty]],
											'DmiApplicantPaymentDetails' => ['table' => 'dmi_applicant_payment_details', 'type' => 'INNER',
											'conditions' => ['DmiApplicantPaymentDetails.customer_id = DmiFirms.customer_id', $date_not_empty,'payment_confirmation' => 'confirmed']]])
									->select(['id','customer_id'])
									->where(array_merge($application_type_not_empty, $state_not_empty, $district_not_empty))
									->bind(':start', $search_from_date, 'date')->bind(':end', $search_to_date, 'date')
									->combine('id', 'customer_id')
									->toArray();

						// $renewal_firm_customer_id_list = $this->DmiFirms->find('all')
						// 					->select(['id', 'customer_id'])
						// 					->join(['DmiDistricts' => ['table' => 'dmi_districts', 'type' => 'INNER',
						// 							'conditions' => ['DmiDistricts.id = DmiFirms.district::integer', $ro_office_not_empty]],
						// 							'DmiRenewalApplicantPaymentDetails' => ['table' => 'dmi_renewal_applicant_payment_details', 'type' => 'INNER',
						// 							'conditions' => ['DmiRenewalApplicantPaymentDetails.customer_id = DmiFirms.customer_id',$renewal_date_not_empty, 'payment_confirmation' => 'confirmed']]])
						// 					->select(['id','customer_id'])
						// 					->where(array_merge($application_type_not_empty, $state_not_empty, $district_not_empty))
						// 					->bind(':start', $search_from_date, 'date')->bind(':end', $search_to_date, 'date')
						// 					->combine('id', 'customer_id')
						// 					->toArray();

					}

					if ($firm_customer_id_list != null) {
						$firm_customer_id_condition = ['customer_id IN' => $firm_customer_id_list];
					} else {
						$firm_customer_id_condition = ['customer_id IS' => ''];
					}

					
			} else {


				$firm_customer_id_condition = array();
				$renewal_firm_customer_id_list = array();
			}

			
			$total_new_ca_pp_lab =  $new_ca_total + $new_pp_total + $new_lab_total;   // for total newca payment
			$total_renewal_ca_pp_lab =  $renewal_ca_total + $renewal_pp_total + $renewal_lab_total;   // for total_renewal_ca_pp_lab
			$total_change_ca_pp_lab =  $change_ca_total + $change_pp_total + $change_lAB_total;   // for total_change_ca_pp_lab

		

		} else {

			$firm_customer_id_condition = array();
			$renewal_firm_customer_id_list = array();


			if($report_for == 1){

				//sum of revenue new application by shreeya on date [23-06-2023]
				$newApplicationrevenue_Query = $this->DmiApplicantPaymentDetails->find('all')
					->where(['payment_confirmation' => 'confirmed'])->sumOf('amount_paid'); // updated by Ankur
				$newApplicationrevenue = ['sum' => $newApplicationrevenue_Query];
				$newrevenue = $this->thousandsCurrencyFormat($newApplicationrevenue['sum']);
				$sum_of_all_total = $newrevenue;
			}
			elseif($report_for == 2){

				//sum of revenue renewal application by shreeya on date [23-06-2023]
				$renewalApplicationrevenue_Query =$this->DmiRenewalApplicantPaymentDetails->find('all')
					->where(['payment_confirmation' => 'confirmed'])->sumOf('amount_paid');
				$renewalApplicationrevenue = ['sum' => $renewalApplicationrevenue_Query];
				$renewalrevenue = $this->thousandsCurrencyFormat($renewalApplicationrevenue['sum']);
				$sum_of_all_total = $renewalrevenue;
			}
			elseif($report_for == 3){

				//sum of revenue change application by shreeya on date [23-06-2023]
				$changeApplicationrevenue_Query =$this->DmiChangePaymentDetails->find('all')
					->where(['payment_confirmation' => 'confirmed'])->sumOf('amount_paid');
				$changeApplicationrevenue = ['sum' => $changeApplicationrevenue_Query];
				$changerevenue =$this->thousandsCurrencyFormat($changeApplicationrevenue['sum']);
				$sum_of_all_total = $changerevenue;

			}elseif($report_for == 4){

				//sum of chemist application by shreeya on date [23-06-2023]
				$chemistApplicationrevenue_Query =$this->DmiChemistPaymentDetails->find('all')
						->where(['payment_confirmation' => 'confirmed'])->sumOf('amount_paid');
				$chemistApplicationrevenue = ['sum' => $chemistApplicationrevenue_Query];
				$chemistrevenue = $this->thousandsCurrencyFormat($chemistApplicationrevenue['sum']);
				$sum_of_all_total = $chemistrevenue;
			}
			elseif($report_for == 7){
				//sum for adv renewal application by shreeya on date [23-06-2023]
				$advApplicationrevenue_Query =$this->DmiAdvPaymentDetails->find('all')
					->where(['payment_confirmation' => 'confirmed'])->sumOf('amount_paid');
				$advApplicationrevenue = ['sum' => $advApplicationrevenue_Query];
				$advrevenue = $this->thousandsCurrencyFormat($advApplicationrevenue['sum']);
				$sum_of_all_total = $advrevenue;

			}

		}



		$application_array = $this->DmiApplicationTypes->find('all')
		->select(['id', 'application_type'])
		->where(['delete_status IS NULL', 'id IN' => $this->Session->read('applTypeArray')])
		->order(['id'])
		->combine('id', 'application_type')
		->toArray();

		$application_key = array_keys($application_array);


		if(empty($report_for) || $report_for == ''){

			$flowwise_table_data = $this->DmiFlowWiseTablesLists->find('all')->select(['id','payment','application_type'])->where(['application_type IN' =>$application_key])->toArray();

		}else{
			$flowwise_table_data = $this->DmiFlowWiseTablesLists->find('all')->select(['id','payment','application_type'])->where(['payment IS NOT NULL','application_type IN'=>$report_for])->toArray();
			//'application_type IN'=>array('3','4','7') 'application_type IN'=>$report_for

		}

		$i=0;
		$total_payment_details = [];
		$ca_payment = [];
		$printing_payment = [];
		$lab_payment = [];
		$apl_type_res = [];
		$application_type_id = '';

		foreach ($flowwise_table_data as $FlowWise_Tables){


			$apl_type = $FlowWise_Tables['application_type'];
			$payment_table = $FlowWise_Tables['payment'];

			$this->loadModel($payment_table);
			$query_cil = $this->$payment_table->find('all');


			//by default show all application recordes ..
			//By Shreeya on Date [12-06-2023]

			if($report_for == 2 || $report_for == 0){

				//change the query remove group for renewal application  by shreeya on date 13-06-2023
				$customer_id_list = $query_cil->select('customer_id')->where(['payment_confirmation' => 'confirmed'])->toArray();


			}else{

				$customer_id_list = $query_cil->select(['customer_id', 'max' => $query_cil->func()->max('certificate_type')])
								->distinct()->where($firm_customer_id_condition)
								->group(['customer_id'])->order(['MAX(certificate_type)'])->toArray();

			}

			//added for application type Chemist Approval by shreeya on date on 15-06-2023
			if (isset($application_array[4])) {
				$customer_id_list = $query_cil->select('customer_id')->where(['payment_confirmation' => 'confirmed'])->toArray();
			}

			

			$k=0;
			foreach ($customer_id_list as $customer_id) {

				$customer_payment_id_list = $this->$payment_table->find('all')->select(['id'])->where(['customer_id' => $customer_id['customer_id'], 'payment_confirmation' => 'confirmed'])->toArray();
				

				if (!empty($customer_payment_id_list)) {

					$split_customer_id = explode('/',$customer_id['customer_id']);

					if ($split_customer_id[1] == 1) { $ca_application_payment_total[$i] = $i;}
					elseif ($split_customer_id[1] == 2) { $printing_application_payment_total[$i] = $i;}
					elseif ($split_customer_id[1] == 3) { $laboratory_application_payment_total[$i] = $i;}else{
						$payment_max_id[$i][$k] = $customer_payment_id_list[0]['id'];
					}

					$payment_max_id[$i][$k] = $customer_payment_id_list[0]['id'];

					$customer_payment_details[$i][$k] =  $this->$payment_table->find('all')->where(['id IN' => $customer_payment_id_list[0]['id']])->first();

					//added by application type Chemist Approval by shreeya on date on 15-06-2023
					if (isset($application_array[4])) {
						$this->loadModel('DmiChemistRegistrations');
						$chemist_details= $this->DmiChemistRegistrations->find('all')->where(['chemist_id' => $customer_id['customer_id'],['delete_status IS NULL']])->first();

						if(!empty($chemist_details)){
							$customer_id['customer_id'] = $chemist_details['created_by'];

						}

					}

					$firms_details[$i][$k] = $this->DmiFirms->find('all')->where(['customer_id IS' => $customer_id['customer_id'],['delete_status IS NULL']])->order(['customer_id' => 'ASC'])->first();

					$this->loadModel('DmiApplicationTypes');
					// $apl_type_res[$i][$k] =  $this->DmiApplicationTypes->find('all')->select(['application_type'])->where(['id IS' => $apl_type])->first();


					$apl_type_res1 =  $this->DmiApplicationTypes->find('all')->select(['id','application_type'])->where(['id' => $apl_type])->first();
						$apl_type_res[$i][$k] = $apl_type_res1['application_type'];

					if($firms_details[$i][$k] != NULL){
						$ro_id[$i][$k] = $this->DmiDistricts->find('all')->select(['ro_id'])->where(['id IS' => $firms_details[$i][$k]['district']])->first();
						//$i=$i+1;

					}
					$k++;
				}



			}


			// below if-else check added by Ankur Jangid for empty IN query error check
			if (!empty($customer_id_list)) {
				$payment_max_id_condition = ['id IN' => $payment_max_id];
			} else {
				$payment_max_id_condition = ['id IS' => ''];
			}


			$i++;
		}
			//$payment_data = $this->DmiFlowWiseTablesLists->find('all')->select(['id','payment','application_type'])->where(['payment IS NOT NULL'])->order(['id'])->toArray();

			$payment_data = $this->DmiFlowWiseTablesLists->find('all')
			->select(['id', 'payment', 'application_type'])
			->where(['payment IS NOT NULL', 'application_type IN' => $this->Session->read('applTypeArray')])
			->order(['id'])
			->toArray();
		


			//dates between to fetch records
			$from_date = date("Y-m-d H:i:s",strtotime("-12 month"));

			$to_date = date('Y-m-d H:i:s');//str_replace('/','-',$to_date);

			$j=1;
			$application_list_data = [];
			foreach ($payment_data as $payment_value) {


				$tbl_data = $payment_value['payment'];
				$this->loadModel($tbl_data);

				//$application_list_data[$j] = $this->$tbl_data->find('all',array('conditions'=>array('payment_confirmation'=>'confirmed','and'=>array('date(created) >=' => $from_date, 'date(created) <=' =>$to_date)),'order'=>'id desc'))->toArray();
				//added for show payment count of ca,pp,lab by shreeya on date [21-06-2023]
				$application_list_data[$j] = $this->$tbl_data->find('all')->select(['id','customer_id','certificate_type','amount_paid','payment_confirmation'])->where(['payment_confirmation' =>'confirmed'])->toArray();
				
				$j++;
			}
			

			foreach ($application_list_data[1] as $resultArr) {

					$certiifctaetype = $resultArr['certificate_type'];


					if($certiifctaetype == 1){

						$new_ca_total = $new_ca_total + $resultArr['amount_paid'];  // store total amt of newca

					}
					if($certiifctaetype == 2){

						$new_pp_total = $new_pp_total + $resultArr['amount_paid'];  // store total amt of newpp

					}
					if($certiifctaetype == 3){
						$new_lab_total = $new_lab_total + $resultArr['amount_paid'];  // store total amt of newlab

					}
					//$i++;
			}

			//for renewal
			foreach ($application_list_data[2] as $resultArr) {

					$certiifctaetype = $resultArr['certificate_type'];


					if($certiifctaetype == 1){
						$renewal_ca_total = $renewal_ca_total + $resultArr['amount_paid'];  // store total amt of renewalCA
					}
					if($certiifctaetype == 2){
						$renewal_pp_total = $renewal_pp_total + $resultArr['amount_paid'];  // store total amt of renewalPP
					}
					if($certiifctaetype == 3){
						$renewal_lab_total = $renewal_lab_total + $resultArr['amount_paid'];  // store total amt of renewalLAB
					}
					//$i++;
			}

			// for change
			foreach ($application_list_data[3] as $resultArr) {

					$certiifctaetype = $resultArr['certificate_type'];


					if($certiifctaetype == 1){
						$change_ca_total = $change_ca_total + $resultArr['amount_paid'];  // store total amt of ChangeCA
					}
					if($certiifctaetype == 2){
						$change_pp_total = $change_pp_total + $resultArr['amount_paid'];  // store total amt of ChangePP
					}
					if($certiifctaetype == 3){
						$change_lAB_total = $change_lAB_total + $resultArr['amount_paid'];  // store total amt of ChangeLAB
					}
					//$i++;
			}

			// for chemist
			foreach ($application_list_data[4] as $resultArr) {
				
					$chemist_total = $chemist_total + $resultArr['amount_paid'];
					//$i++;
			}
			
			// // for 15digit
			// foreach ($application_list_data[5] as $resultArr) {

			// 		$fiftin_digit_total = $fiftin_digit_total + $resultArr['amount_paid'];
			// 		//$i++;
			// }

			// // for Ecode
			// foreach ($application_list_data[6] as $resultArr) {

			// 		$ecode_total = $ecode_total + $resultArr['amount_paid'];
			// 		//$i++;
			// }

			// for adv
			foreach ($application_list_data[7] as $resultArr) {

					$adv_total = $adv_total + $resultArr['amount_paid'];
					//$i++;
			}
			// // for adp
			// foreach ($application_list_data[8] as $resultArr) {

			// 		$adp_total = $adp_total + $resultArr['amount_paid'];
			// 		//$i++;
			// }
			// // for RTI
			// foreach ($application_list_data[9] as $resultArr) {

			// 		$rti_total = $rti_total + $resultArr['amount_paid'];
			// 		//$i++;
			// }
			// // for bgr
			// foreach ($application_list_data[10] as $resultArr) {

			// 		$bgr_total = $bgr_total + $resultArr['amount_paid'];
			// 		//$i++;
			// }



		$total_new_ca_pp_lab =  $new_ca_total + $new_pp_total + $new_lab_total;   // for total newca payment
		$total_renewal_ca_pp_lab =  $renewal_ca_total + $renewal_pp_total + $renewal_lab_total;   // for total_renewal_ca_pp_lab
		$total_change_ca_pp_lab =  $change_ca_total + $change_pp_total + $change_lAB_total;   // for total_change_ca_pp_lab

		

		//change payment count format by shreeya on date [23-06-2023]
		// $new_ca = $this->thousandsCurrencyFormat($new_ca_total);
		// $new_pp = $this->thousandsCurrencyFormat($new_pp_total);
		// $new_lab = $this->thousandsCurrencyFormat($new_lab_total);

		// $renewal_ca = $this->thousandsCurrencyFormat($renewal_ca_total);
		// $renewal_pp = $this->thousandsCurrencyFormat($renewal_pp_total);
		// $renewal_lab = $this->thousandsCurrencyFormat($renewal_lab_total);

		// $change_ca = $this->thousandsCurrencyFormat($change_ca_total);
		// $change_pp = $this->thousandsCurrencyFormat($change_pp_total);
		// $change_lAB = $this->thousandsCurrencyFormat($change_lAB_total);

		// $chemist_total = $this->thousandsCurrencyFormat($chemist_total);
		// $adv_total = $this->thousandsCurrencyFormat($adv_total);


		// $total_new_ca_pp_lab = $this->thousandsCurrencyFormat($new_ca_total+$new_pp_total+$new_lab_total);
		// $total_renewal_ca_pp_lab = $this->thousandsCurrencyFormat($new_ca_total+$new_pp_total+$new_lab_total);
		// $total_change_ca_pp_lab = $this->thousandsCurrencyFormat($new_ca_total+$new_pp_total+$new_lab_total);



		//sum  for 15digit renewal application by shreeya on date [20-06-2023]
		// $digitApplicationrevenue_Query =$this->Dmi15DigitPaymentDetails->find('all')
		// 				->where(['payment_confirmation' => 'confirmed'])->sumOf('amount_paid');
		// $digitApplicationrevenue = ['sum' => $digitApplicationrevenue_Query];

		//sum  for ecode application by shreeya on date [20-06-2023]
		// $ecodeApplicationrevenue_Query =$this->DmiECodePaymentDetails->find('all')
		// 				->where(['payment_confirmation' => 'confirmed'])->sumOf('amount_paid');
		// $ecodeApplicationrevenue = ['sum' => $ecodeApplicationrevenue_Query];



		//sum for adp renewal application by shreeya on date [20-06-2023]
		// $adpApplicationrevenue_Query =$this->DmiAdpPaymentDetails->find('all')
		// 				->where(['payment_confirmation' => 'confirmed'])->sumOf('amount_paid');
		// $adpApplicationrevenue = ['sum' => $adpApplicationrevenue_Query];

		//sum for RTI renewal application by shreeya on date [20-06-2023]
		// $rtiApplicationrevenue_Query =$this->DmiRtiPaymentDetails->find('all')
		// 				->where(['payment_confirmation' => 'confirmed'])->sumOf('amount_paid');
		// $rtiApplicationrevenue = ['sum' => $rtiApplicationrevenue_Query];

		//sum for surrender  renewal application by shreeya on date [20-06-2023]
		// $surrenderApplicationrevenue_Query =$this->DmiSurrenderPaymentDetails->find('all')
		// 				->where(['payment_confirmation' => 'confirmed'])->sumOf('amount_paid');
		// $surrenderApplicationrevenue = ['sum' => $surrenderApplicationrevenue_Query];



		// $digitrevenue = $this->thousandsCurrencyFormat($digitApplicationrevenue['sum']);
		// $ecoderevenue = $this->thousandsCurrencyFormat($ecodeApplicationrevenue['sum']);
		// $adprevenue = $this->thousandsCurrencyFormat($adpApplicationrevenue['sum']);
		// $rtirevenue = $this->thousandsCurrencyFormat($rtiApplicationrevenue['sum']);
		// $surrenderrevenue = $this->thousandsCurrencyFormat($surrenderApplicationrevenue['sum']);



		//added the condition to show grant total by shreeya on date[21-06-2023]
		if($sum_of_all_total_status == false){
			$sum_of_all = 'Grant Total:'.$sum_of_all_total;
		}

		$this->set('sum_of_all',$sum_of_all);


		$this->set('application_array',$application_array);
		$this->set('all_ro_office',$all_ro_office);
		$this->set('all_states',$all_states);
		$this->set('all_district',$all_district);
		$this->set('all_certificate_type',$all_certificate_type);

		//$this->set('report_for_array',$report_for_array); // variable set for Application type
		$this->set('total_new_ca_pp_lab',$total_new_ca_pp_lab); // variable set for total_new_ca_pp_lab
		$this->set('total_renewal_ca_pp_lab',$total_renewal_ca_pp_lab); // variable set for total_renewal_ca_pp_lab
		$this->set('total_change_ca_pp_lab',$total_change_ca_pp_lab); // variable set for total_change_ca_pp_lab

		$this->set('new_ca_total',$new_ca_total); // variable set for new_ca_total
		$this->set('new_pp_total',$new_pp_total); // variable set for new_pp_total
		$this->set('new_lab_total',$new_lab_total); // variable set for new_lab_total



		$this->set('renewal_ca_total',$renewal_ca_total); // variable set for renewal_ca_total
		$this->set('renewal_pp_total',$renewal_pp_total); // variable set for renewal_pp_total
		$this->set('renewal_lab_total',$renewal_lab_total); // variable set for renewalLAB

		$this->set('change_ca_total',$change_ca_total); // variable set for ChangeCA
		$this->set('change_pp_total',$change_pp_total); // variable set for ChangePP
		$this->set('change_lAB_total',$change_lAB_total); // variable set for ChangeLAB
		$this->set('fiftin_digit_total',$fiftin_digit_total); // variable set for fiftin_digit_total
		$this->set('ecode_total',$ecode_total); // variable set for ecode_total
		$this->set('adv_total',$adv_total); // variable set for adp_total
		$this->set('adp_total',$adp_total);
		$this->set('rti_total',$rti_total);
		$this->set('bgr_total',$bgr_total);

		$this->set('ro_id',$ro_id);
		$this->set('firms_details',$firms_details);
		$this->set('apl_type_res',$apl_type_res);
		$this->set('customer_payment_details',$customer_payment_details);


		$this->set('certificate_type',$certificate_type);
		$this->set('ro_office',$ro_office); $this->set('state',$state);
		$this->set('district',$district); $this->set('search_from_date',$search_from_date);
		$this->set('search_to_date',$search_to_date);   // Change on 5/11/2018, set search_to_date value, By Pravin 5/11/2018

		$this->set('flowwise_table_data',$flowwise_table_data);

		//aaded by shreeya on date [20-06-2023]
		$this->set('chemist_total',$chemist_total);




	}

	//added by shreeya for rupees format on date [20-06-2023]
	public function thousandsCurrencyFormat($number) {

		if ($number >= 1000) {

			$x_array = explode('.',number_format(($number / 1000), 1));

			if($x_array[1] == 0 ){
				$number_value = $x_array[0]. 'K';
			}else{
				$number_value = number_format(($number / 1000), 1) . 'k';
			}
			return $number_value;

		} else {
			return $number;
		}

	}


	// Sent Email Report
	// Description : ----
	// @Author : Pravin Bhakare
	// #Contributer : Ankur Jangid (Migration)
	// Date : 27-07-2018

	public function sentEmailReport() {

		// $all_application_type = $this->DmiCertificateTypes->find('list', array('fields'=>'certificate_type'));
		$all_application_type = $this->DmiCertificateTypes->find('all')->select(['id', 'certificate_type'])->combine('id', 'certificate_type')->toArray();

		//added 'office_type'=>'RO' condition on 27-07-2018
		// $all_ro_office = $this->DmiRoOffices->find('list',array('fields'=>'ro_office','conditions'=>array('office_type'=>'RO')));
		$all_ro_office = $this->DmiRoOffices->find('all')->select(['id', 'ro_office'])->where(['office_type'=>'RO','delete_status IS NULL'])->combine('id', 'ro_office')->toArray();

		$this->set('all_application_type',$all_application_type);
		$this->set('all_ro_office',$all_ro_office);

		if (null != ($this->request->getData('search_logs'))) {

			$table = 'DmiCertificateTypes';
			$post_input_request = $this->request->getData('application_type');
			if (!empty($post_input_request)) {
				$application_type = $this->Customfunctions->dropdownSelectInputCheck($table,$post_input_request);//calling library function
			}
			else {
				$application_type = '';
			}

			$table = 'DmiRoOffices';
			$post_input_request = $this->request->getData('office');
			if (!empty($post_input_request)) {
				$ro_office = $this->Customfunctions->dropdownSelectInputCheck($table,$post_input_request);//calling library function
			}
			else {
				$ro_office = '';
			}

			$search_from_date =  $this->request->getData('from_date');
			$search_from_date = $this->Customfunctions->dateFormatCheck($search_from_date);
			$search_to_date =  $this->request->getData('to_date');
			$search_to_date = $this->Customfunctions->dateFormatCheck($search_to_date);
			$this->date_comparison($search_from_date,$search_to_date);

			// $this->paginate = array('conditions'=>array('date(sent_date) BETWEEN ? AND ?' => array($search_from_date,$search_to_date)),'limit' => 5,'order'=>'id desc');
			$condition = ['date(sent_date) BETWEEN :start AND :end'];
			$sent_email_details = $this->DmiSentEmailLogs->find('all')->where($condition)->bind(':start', $search_from_date, 'date')->bind(':end', $search_to_date, 'date')
				->order(['id'=>'DESC'])->toArray();
		}
		else {
			// $this->paginate = array('limit' => 5,'order'=>'id desc');
			$sent_email_details = $this->DmiSentEmailLogs->find('all')->order(['id'=>'DESC'])->toArray();
		}

		if (!empty($sent_email_details)) {
			$i=0;
			foreach ($sent_email_details as $destination_list) {
				$split_value = explode(',',$destination_list['destination_list']);
				$email_destination_list[$i] = implode(', ',$split_value);
				$i=$i+1;
			}
		}

		$this->set('sent_email_details',$sent_email_details);
		$this->set('email_destination_list',$email_destination_list);
	}



	// for checking the encoded or not
	public function isBase64Encoded($data){
		if (preg_match('%^[a-zA-Z0-9/+]*={0,2}$%', $data)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}



	public function getStateName($customer_id) {

		$state_id = $this->DmiFirms->find('all',array('fields'=>'state','conditions'=>array('customer_id IS'=>$customer_id)))->first();
		$state_name = $this->DmiStates->find('all',array('fields'=>'state_name','conditions'=>array('id IS'=>$state_id['state'])))->first();
		return $state_name['state_name'];
	}




	// Sent Email Report
	// Description : This Function Developed For In-Process Report for Renewal Appln Showing Count & list
	// @Author : Yashwant
	// #Contributer : Shreeya
	// Date : 03/Mar/2023

	public function inprocessRenwalApplicationsReport($cert_type,$appl_type) //renewal_id replace ->cert_type By Shreeya
	{

		/*===================Added New Code show list of count (Start) Date [25-05-2023 By Shreeya] ===========*/
		//pass the parameter of cert_type,appl_type
		$appl_type=base64_decode($appl_type);
		$cert_type=base64_decode($cert_type);
		$data_id =array($cert_type);


		if ($cert_type== 'CA')
		{
			$cert_type = 1;
		}
		elseif ($cert_type== 'PP')
		{
			$cert_type = 2;
		}
		elseif ($cert_type== 'LAB')
		{
			$cert_type = 3;
		}



		//check the which application type is present
		if($appl_type=='new'){
			$processFunction = 'new_app_processed';
		}elseif($appl_type=='renewal'){
			$processFunction = 'renewal_app_processed';
		}elseif($appl_type=='backlog'){
			$processFunction = 'backlog_app_processed';
		}


		//show the count according to application type
		$searchConditions = array();
		$application_processed[] = $this->Reportstatistics->$processFunction($searchConditions,null,null,$cert_type,$appl_type);
		$applListToShow = $application_processed[0][2];


		$application_id = null;
		$application_type = null;
		$user_roles = null;
		$user_office = null;
		$user_email_id =null;
		$date = []; // Rename the variable to avoid overwriting the previous $date variable

		$i = 0;
		foreach ($applListToShow as $each_customer_id) {


			$application_id[$i] = $each_customer_id;


			$table = 'DmiAllApplicationsCurrentPositions';
			$current_users_details = $this->$table->find('all')->where(['customer_id IN' => $each_customer_id])->order(['id' => 'DESC'])->first();


			$application_form_type = $this->Customfunctions->checkApplicantFormType($each_customer_id);

				if ($application_form_type == 'A') {
					$application_type[$i]='CA (Form-A)';
				} elseif ($application_form_type == 'B') {
					$application_type[$i]='Printing Press (Form-B)';
				} elseif ($application_form_type == 'C') {
					$application_type[$i]='Laboratory (Form-C)';
				} elseif ($application_form_type == 'D') {
					$application_type[$i]='Laboratory (Form-D)';
				} elseif ($application_form_type == 'E') {
					$application_type[$i]='CA (Form-E)';
				} elseif ($application_form_type == 'F') {
					$application_type[$i]='CA (Form-F)';
				}

			$date[$i] = $current_users_details['modified']; // Store the value in a new array
			$user_email_id[$i] = $current_users_details['current_user_email_id'];
			$current_level[$i] = $current_users_details['current_level'];


				$user_posted_office_id=array();
				if (!empty($user_email_id[$i])) {
					$user_posted_office_id = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email IN' => $user_email_id[$i]])->first();

				}
				if (!empty($user_posted_office_id)) {
					$user_office[$i] = $this->DmiRoOffices->find('all')->select(['ro_office'])->where(['id' => $user_posted_office_id['posted_ro_office']])->first();

				}

				if (!empty($user_office[$i])) {
					$user_office[$i] = $user_office[$i]['ro_office'];
				} else {
					$user_office[$i] = '--';
				}

				$check_roles=array();
				if (!empty($user_email_id[$i])) {
					$check_roles = $this->DmiUserRoles->find('all')->where(['user_email_id IN' => $user_email_id[$i]])->first();
				}

				if (!empty($check_roles)) {
					$user_list[$i] = $check_roles;
				} else {
					$user_list[$i] = '---';
				}

				$user_roles[$i] = $this->checkUserRoleFromCurrentLevel($current_users_details['current_level'],$current_users_details['current_user_email_id']);



			$i = $i + 1;

			$this->set('date',$date);
			$this->set('user_list',$user_list);
			$this->set('application_type',$application_type);
			$this->set('user_roles',$user_roles);
			$this->set('user_office',$user_office);
			$this->set('user_email_id',$user_email_id);
			$this->set('application_id',$application_id);
		}

		/*====(End)===*/



		$application_pending_days = $this->Session->read('pending_days');

		if (!empty($application_pending_days)) {
			$report_name = 'Pending Renewal Applications Report (More than 15 Days)';
		} else {
			$report_name ='Pending Renewal Applications Report';
		}

		$this->set('report_name',$report_name);
		//$table = 'DmiRenewalFinalSubmits';
		$table = 'DmiRenewalAllCurrentPositions';
		//$table = 'DmiFirms';
		$pending_application_type = 'renewal';

		$application_type_xy = array('A'=>'CA (Form-A)','C'=>'Laboratory (Form-C)','E'=>'CA (Form-E)','B'=>'Printing Press (Form-B)','D'=>'Laboratory (Form-D)','F'=>'CA (Form-F)');
		//RenewalId replace ->cert_type By Shreeya on Date [25-05-2023]
		if($cert_type=='CA')
		{
			$application_type_xy = array('A'=>'CA (Form-A)','E'=>'CA (Form-E)','F'=>'CA (Form-F)');
		}
		elseif($cert_type=='PP')
		{
			$application_type_xy = array('B'=>'Printing Press (Form-B)');
		}
		elseif ($cert_type=='LAB') {
			$application_type_xy = array('C'=>'Laboratory (Form-C)','D'=>'Laboratory (Form-D)');
		}




		//Change on 9/11/2018, Sorting array by ascending order - By Pravin Bhakare
		asort($application_type_xy);
		$this->set('application_type_xy',$application_type_xy);
		//RenewalId replace ->cert_type By Shreeya on Date [25-05-2023]
		$this->set('cert_type',$cert_type);

		$user_roles_xy = array('RO/SO'=>'RO/SO','MO/SMO'=>'MO/SMO','IO'=>'IO');
		//Change on 9/11/2018, Sorting array by ascending order - By Pravin Bhakare
		asort($user_roles_xy);
		$this->set('user_roles_xy',$user_roles_xy);

		$ro_office = $this->DmiRoOffices->find('all')->select(['id', 'ro_office'])->where(['office_type' => 'RO','delete_status IS NULL'])->order(['ro_office' => 'ASC'])
			->combine('id', 'ro_office')->toArray();
		$this->set('ro_office',$ro_office);

		$search_application_type_id = $this->Session->read('search_application_type_id');

		$search_user_role = $this->Session->read('search_user_role');
		$ro_office_id = $this->Session->read('ro_office_id');
		$mo_office_id = $this->Session->read('mo_office_id');
		$io_office_id = $this->Session->read('io_office_id');
		$search_from_date = $this->Session->read('search_from_date');
		$search_to_date = $this->Session->read('search_to_date');
		$search_user_email_id = $this->Session->read('search_user_email_id');

		$download_search_application_type_id = $this->Session->read('search_application_type_id');
		$download_search_user_role = $this->Session->read('search_user_role');
		$download_ro_office_id = $this->Session->read('ro_office_id');
		$download_mo_office_id = $this->Session->read('mo_office_id');
		$download_io_office_id = $this->Session->read('io_office_id');
		$download_search_from_date = $this->Session->read('search_from_date');
		$download_search_to_date = $this->Session->read('search_to_date');
		$download_search_user_email_id = $this->Session->read('search_user_email_id');

		$this->set('search_application_type_id',$search_application_type_id);
		$this->set('search_user_role',$search_user_role);
		$this->set('ro_office_id',$ro_office_id);
		$this->set('mo_office_id',$mo_office_id);
		$this->set('io_office_id',$io_office_id);
		$this->set('search_from_date',$search_from_date);
		$this->set('search_to_date',$search_to_date);
		$this->set('search_user_email_id',$search_user_email_id);
		$download_report = 'no';


		if (null != ($this->request->getData('search_logs')) || null != ($this->request->getData('download_report'))) {

			if (!empty($this->request->getData('download_report'))) {
				$download_report = 'yes';
			}

			$search_application_type_id = $this->request->getData('application_type');
			$search_user_role =  $this->request->getData('user_role');
			$ro_office_id =  $this->request->getData('ro_office');
			$mo_office_id =  $this->request->getData('mo_office');
			$io_office_id =  $this->request->getData('io_office');

			$search_user_email_id =  $this->request->getData('user_id');
			$search_from_date =  $this->request->getData('from_date');
			$search_from_date = $this->Customfunctions->dateFormatCheck($search_from_date);
			$search_to_date =  $this->request->getData('to_date');
			$search_to_date = $this->Customfunctions->dateFormatCheck($search_to_date);
			$this->date_comparison($search_from_date,$search_to_date);

			// Change on 2/11/2018 - For download excel report, Take search filter field value from session variables instend of POST variable - By Pravin
			if ($download_report == 'yes') {

				$search_user_role = $this->Session->read('search_user_role');
				$ro_office_id = $this->Session->read('ro_office_id');
				$mo_office_id = $this->Session->read('mo_office_id');
				$io_office_id = $this->Session->read('io_office_id');
				$search_from_date = $this->Session->read('search_from_date');
				$search_to_date = $this->Session->read('search_to_date');
				$search_user_email_id = $this->Session->read('search_user_email_id');
			}

			$download_application_customer_id_list = $this->pendingRenewalAppln($download_search_application_type_id,$download_search_user_role,$download_ro_office_id,$download_mo_office_id,$download_io_office_id,$download_search_from_date,$download_search_to_date,$download_search_user_email_id,$table,$pending_application_type,$application_pending_days,$data_id);

			$this->Session->delete('search_application_type_id');
			$this->Session->delete('search_user_role');
			$this->Session->delete('ro_office_id');
			$this->Session->delete('mo_office_id');
			$this->Session->delete('io_office_id');
			$this->Session->delete('search_from_date');
			$this->Session->delete('search_to_date');
			$this->Session->delete('search_user_email_id');

			$this->Session->write('search_application_type_id',$search_application_type_id);
			$this->Session->write('search_user_role',$search_user_role);
			$this->Session->write('ro_office_id',$ro_office_id);
			$this->Session->write('mo_office_id',$mo_office_id);
			$this->Session->write('io_office_id',$io_office_id);
			$this->Session->write('search_from_date',$search_from_date);
			$this->Session->write('search_to_date',$search_to_date);
			$this->Session->write('search_user_email_id',$search_user_email_id);

			$this->set('search_application_type_id',$search_application_type_id);
			$this->set('search_user_role',$search_user_role);
			$this->set('ro_office_id',$ro_office_id);
			$this->set('mo_office_id',$mo_office_id);
			$this->set('io_office_id',$io_office_id);
			$this->set('search_from_date',$search_from_date);
			$this->set('search_to_date',$search_to_date);
			$this->set('search_user_email_id',$search_user_email_id);

			$application_customer_id_list = $this->pendingRenewalAppln($search_application_type_id,$search_user_role,$ro_office_id,$mo_office_id,$io_office_id,$search_from_date,$search_to_date,$search_user_email_id,$table,$pending_application_type,$application_pending_days,$data_id);

			if (!empty($application_customer_id_list)) {

				$current_users_details = $this->$table->find('all')->where(['customer_id IN'=>$application_customer_id_list])->order(['id' => 'DESC'])->toArray();

				//Fetch the all data that required for creating the downloading report as execel (Done by pravin 14-03-2018)
				if ($download_report == 'yes')
				{
					$download_pending_application = $this->$table->find('all')->where(['customer_id IN' => $download_application_customer_id_list])->order(['id' => 'DESC'])->toArray();
					$this->downloadPendingApplicationReport($download_pending_application,$pending_application_type,$table);
				}

			} else {
				$current_users_details = null;
			}
			//Below query commented by shreya for display list of renewal application
			//$this->pendingApplicationReportResults($current_users_details,$pending_application_type,$table);

		} else {

			$application_customer_id_list = $this->pendingRenewalAppln($search_application_type_id,$search_user_role,$ro_office_id,$mo_office_id,$io_office_id,$search_from_date,$search_to_date,$search_user_email_id,$table,$pending_application_type,$application_pending_days,$data_id);

			if (!empty($application_customer_id_list))
			{

				$current_users_details = $this->$table->find('all')->where(['customer_id IN' => $application_customer_id_list])->order(['id' => 'DESC'])->toArray();

				//Fetch the all data that required for creating the downloading report as execel (Done by pravin 14-03-2018)
				if ($download_report == 'yes')
				 {
					$download_pending_application = $this->$table->find('all')->where(['customer_id IN' => $application_customer_id_list])
						->order(['id' => 'DESC'])->toArray();
					$this->downloadPendingApplicationReport($download_pending_application,$pending_application_type,$table);
				}

			} else {
				$current_users_details = null;
			}
			//Below query commented by shreya for display list of renewal application already use this code
			//$this->pendingApplicationReportResults($current_users_details,$pending_application_type,$table);

		}
	}





	// Pending Renewal Applications
	// Description :
	// @Author : Yashwant
	// Date : 03/Mar/2023

	public function pendingRenewalAppln($search_application_type_id,$search_user_role,$ro_office_id,$mo_office_id,$io_office_id,$search_from_date,$search_to_date,$search_user_email_id,$table,$pending_application_type,$application_pending_days,$data_id)
	{

		$current_date = new \DateTime(date("d-m-Y")); // Ankur updated new DateTime to new \DateTime as Class "App\Controller\DateTime" not found
		$modify_date_obj = $current_date->modify('-15 day');
		$modify_date = $modify_date_obj->format('d-m-Y H:i:s');


		if (!empty($application_pending_days))
		{

			$conditions = ['DATE(modified) <' => $modify_date];

			$date_conditions = ['date(modified) BETWEEN :start AND :end', 'DATE(created) <' => $modify_date];

			$date_conditions_1 = ['date(modified) BETWEEN :start AND :end', 'DATE(created) <' => $modify_date];
		} else {

			$conditions = [];

			$date_conditions = ['date(modified) BETWEEN :start AND :end'];

			$date_conditions_1 = ['date(modified) BETWEEN :start AND :end', 'DATE(created) <' => $modify_date];
		}

		$final_customer_id_list = null;

		if ($ro_office_id != '' && $search_user_role == 'RO/SO') {
			$level_1_2_3_office = $ro_office_id;
		} elseif ($mo_office_id != '' && $search_user_role == 'MO/SMO') {
			$level_1_2_3_office = $mo_office_id;
		} elseif ($io_office_id != '' && $search_user_role == 'IO') {
			$level_1_2_3_office = $io_office_id;
		} else {
			$level_1_2_3_office = '';
		}


		if ($search_application_type_id != '' && $search_user_role == '' && $search_from_date == '' && $search_to_date == '')
		{

			$application_customer_id = $this->$table->find('all')->where($conditions)->toArray();

			$i=0;
			foreach ($application_customer_id as $each_customer_id)
			{

				if (!empty($each_customer_id['customer_id']))
				{
					$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

					if (in_array($application_customer_type, $search_application_type_id, TRUE)) {
						$application_customer_id_list[$i] = $each_customer_id['customer_id'];
						$i=$i+1;
					}
				}
			}

		/*Start- Yashwant 29-MArch-2023 This function used for Inprocess- Renewal POsted Filter*/
		} elseif ($search_application_type_id == '' && $search_user_role != '' && $search_from_date == '' && $search_to_date == '' && $level_1_2_3_office == '')
		{
			if ($search_application_type_id != '')
			{
				$application_customer_id = $this->$table->find('all')->where($conditions)->toArray();
				$i=0;
				foreach ($application_customer_id as $each_customer_id)
				{
					if (!empty($each_customer_id['customer_id']))
					{
						$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

						if (in_array($application_customer_type, $search_application_type_id, TRUE)) {
							$application_customer_id_list[$i] = $each_customer_id['customer_id'];
							$i=$i+1;
						}
					}
				}
				$application_current_levels = $this->$table->find('all')->where(['customer_id IN' => $seach_application_customer_id_list])->toArray();

			} else {
				$application_current_levels = $this->$table->find('all')->where($conditions)->toArray();
			}

			$i=0;
			foreach ($application_current_levels as $each_current_levels) {

				$application_current_level_user_role = $this->checkUserRoleFromCurrentLevel($each_current_levels['current_level'], $each_current_levels['current_user_email_id']);

				if ($application_current_level_user_role == $search_user_role) {
					$application_customer_id_list[$i] = $each_current_levels['customer_id'];
					$i=$i+1;
				}
			}

		/*====Start- Yashwant 30-MArch-2023 This function used for Inprocess- Renewal Offices  Multi-Select Filter====*/
		} elseif ($search_application_type_id == '' && $search_user_role != '' && $level_1_2_3_office != '' && $search_from_date == '' && $search_to_date == '')
		{
			if ($search_application_type_id != '')
			{
				$application_customer_id = $this->$table->find('all')->where($conditions)->toArray();

				$i=0;
				foreach ($application_customer_id as $each_customer_id) {
					if (!empty($each_customer_id['customer_id'])) {
						$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

						if (in_array($application_customer_type,$search_application_type_id)) {
							$seach_application_customer_id_list[$i] = $each_customer_id['customer_id'];
							$i=$i+1;
						}
					}
				}
				$application_current_levels = $this->$table->find('all')->where(['customer_id IN' => $seach_application_customer_id_list])->toArray();

			} else {

				$application_current_levels = $this->$table->find('all')->where($conditions);
			}

			$i=0;
			foreach ($application_current_levels as $each_current_levels)
			{

				$application_current_level_user_role = $this->checkUserRoleFromCurrentLevel($each_current_levels['current_level'], $each_current_levels['current_user_email_id']);

				if ($application_current_level_user_role == $search_user_role) {

					$user_posted_office_id_xy = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email IS' => $each_current_levels['current_user_email_id']])->first();


					if (!empty($user_posted_office_id_xy)) {
						$user_posted_office_id_xyx = $user_posted_office_id_xy['posted_ro_office'];
					} else {
						$user_posted_office_id_xyx = '';
					}

					if (in_array($user_posted_office_id_xyx,$level_1_2_3_office)) {
						$application_customer_id_list[$i] = $each_current_levels['customer_id'];
						$i=$i+1;
					}
				}
			}

		/*====Start- Yashwant 31-MArch-2023 This function used for Inprocess- Renewal Search FROM-TO Date Filter====*/
		} elseif ($search_application_type_id == ''  && $search_user_role == '' && $level_1_2_3_office == '' && $search_from_date != '' && $search_to_date != '')
		{

			if ($search_application_type_id != '') {

				$application_customer_id = $this->$table->find('all')->where($date_conditions)->bind(':start', $search_from_date, 'date')->bind(':end', $search_to_date, 'date')->toArray();

				$i=0;
				foreach ($application_customer_id as $each_customer_id) {
					if (!empty($each_customer_id['customer_id'])) {
						$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

						if (in_array($application_customer_type,$search_application_type_id)) {
							$application_customer_id_list[$i] = $each_customer_id['customer_id'];
							$i=$i+1;
						}
					}
				}

			} else {
				$application_customer_id_list = $this->$table->find('all')->select(['customer_id'])->where($date_conditions)->bind(':start', $search_from_date, 'date')->bind(':end', $search_to_date, 'date')->combine('id', 'customer_id')->toArray();
			}

		/*=============== Yashwant 06 Mar 2023 Start ================*/
		}elseif(!empty($data_id)){

			$application_customer_id = $this->$table->find('all')->where($conditions)->toArray();

			$i=0;
			foreach ($application_customer_id as $each_customer_id)
			{

				if (!empty($each_customer_id['customer_id']))
				{
					$application_customer_type = $this->Reportsfunctions->newApplicantType($each_customer_id['customer_id']);

					if (in_array($application_customer_type, $data_id, TRUE))
						{
							$application_customer_id_list[$i] = $each_customer_id['customer_id'];
							$i=$i+1;

						}
				}
			}

		} elseif (($search_application_type_id == '' || $search_application_type_id != '') && $search_user_role != '' && $level_1_2_3_office == '' && $search_from_date == '' && $search_to_date == '')
		{

			if ($search_application_type_id != '')
			{

				$application_customer_id = $this->$table->find('all')->where($conditions)->toArray();

				$i=0;
				foreach ($application_customer_id as $each_customer_id) {
					if (!empty($each_customer_id['customer_id'])) {
						$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

						if (in_array($application_customer_type,$search_application_type_id)) {
							$seach_application_customer_id_list[$i] = $each_customer_id['customer_id'];
							$i=$i+1;
						}
					}
				}

				$application_current_levels = $this->$table->find('all')->where(['customer_id IN' => $seach_application_customer_id_list])->toArray();

			} else {

				$application_current_levels = $this->$table->find('all')->where($conditions)->toArray();
			}

			$i=0;
			foreach ($application_current_levels as $each_current_levels) {

				$application_current_level_user_role = $this->checkUserRoleFromCurrentLevel($each_current_levels['current_level'], $each_current_levels['current_user_email_id']);

				if ($application_current_level_user_role == $search_user_role) {
					$application_customer_id_list[$i] = $each_current_levels['customer_id'];
					$i=$i+1;
				}
			}

		} elseif (($search_application_type_id == '' || $search_application_type_id != '') && $search_user_role != '' && $level_1_2_3_office != '' && $search_from_date == '' && $search_to_date == '' && $search_user_email_id =='')
		{

			if ($search_application_type_id != '')
			{

				$application_customer_id = $this->$table->find('all')->where($conditions)->toArray();

				$i=0;
				foreach ($application_customer_id as $each_customer_id) {
					if (!empty($each_customer_id['customer_id'])) {
						$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

						if (in_array($application_customer_type,$search_application_type_id)) {
							$seach_application_customer_id_list[$i] = $each_customer_id['customer_id'];
							$i=$i+1;
						}
					}
				}
				$application_current_levels = $this->$table->find('all')->where(['customer_id IN' => $seach_application_customer_id_list])->toArray();

			} else {

				$application_current_levels = $this->$table->find('all')->where($conditions);
			}

			$i=0;
			foreach ($application_current_levels as $each_current_levels)
			{

				$application_current_level_user_role = $this->checkUserRoleFromCurrentLevel($each_current_levels['current_level'], $each_current_levels['current_user_email_id']);

				if ($application_current_level_user_role == $search_user_role) {

					$user_posted_office_id_xy = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email IS' => $each_current_levels['current_user_email_id']])->first();


					if (!empty($user_posted_office_id_xy)) {
						$user_posted_office_id_xyx = $user_posted_office_id_xy['posted_ro_office'];
					} else {
						$user_posted_office_id_xyx = '';
					}

					if (in_array($user_posted_office_id_xyx,$level_1_2_3_office)) {
						$application_customer_id_list[$i] = $each_current_levels['customer_id'];
						$i=$i+1;
					}
				}
			}

		} elseif (($search_application_type_id == '' || $search_application_type_id != '') && $search_user_role != '' && $level_1_2_3_office != '' && $search_from_date == '' && $search_to_date == '' && $search_user_email_id !='')
		{

			if ($search_application_type_id != '') {

				$application_customer_id = $this->$table->find('all')->where($conditions)->toArray();

				$i=0;
				foreach ($application_customer_id as $each_customer_id) {

					if (!empty($each_customer_id['customer_id'])) {

						$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

						if (in_array($application_customer_type, $search_application_type_id)) {
							$seach_application_customer_id_list[$i] = $each_customer_id['customer_id'];
							$i=$i+1;
						}
					}
				}

				$application_current_levels = $this->$table->find('all')->where(['customer_id IN' => $seach_application_customer_id_list])->toArray();

			} else {

				$application_current_levels = $this->$table->find('all')->where($conditions)->toArray();
			}

			$i=0;
			foreach ($application_current_levels as $each_current_levels) {

				$application_current_level_user_role = $this->checkUserRoleFromCurrentLevel($each_current_levels['current_level'], $each_current_levels['current_user_email_id']);

				if ($application_current_level_user_role == $search_user_role) {

					$user_posted_office_id_xy = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email IS' => $each_current_levels['current_user_email_id']])->first();

					if (!empty($user_posted_office_id_xy)) {
						$user_posted_office_id_xyx = $user_posted_office_id_xy['posted_ro_office'];
					} else {
						$user_posted_office_id_xyx = '';
					}

					if (in_array($user_posted_office_id_xyx,$level_1_2_3_office)) {

						$search_user_email = $this->DmiUserRoles->find('all')->select(['user_email_id'])->where(['id IS' => $search_user_email_id])->first();

						if ($each_current_levels['current_user_email_id'] == $search_user_email['user_email_id']) {
							$application_customer_id_list[$i] = $each_current_levels['customer_id'];
							$i=$i+1;
						}
					}
				}
			}

		} elseif (($search_application_type_id == '' || $search_application_type_id != '') && $search_user_role != '' && $search_from_date != '' && $search_to_date != '' && $level_1_2_3_office !='' && ($search_user_email_id != '' || $search_user_email_id == ''))
		{

			if ($search_application_type_id != '') {

				$application_customer_id = $this->$table->find('all')->where($conditions)->toArray();

				$i=0;
				foreach ($application_customer_id as $each_customer_id) {

					if (!empty($each_customer_id['customer_id'])) {
						$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

						if (in_array($application_customer_type,$search_application_type_id)) {
							$seach_application_customer_id_list[$i] = $each_customer_id['customer_id'];
							$i=$i+1;
						}
					}
				}

				$application_current_levels = $this->$table->find('all')->where(['customer_id IN' => $seach_application_customer_id_list])
					->where(['date(modified) BETWEEN :start AND :end'])->bind(':start', $search_from_date, 'date')->bind(':end', $search_to_date, 'date')->toArray();
			} else {

				$application_current_levels = $this->$table->find('all')->where($date_conditions)->bind(':start', $search_from_date, 'date')
				->bind(':end', $search_to_date, 'date')->toArray();
			}

			$i=0;
			foreach ($application_current_levels as $each_current_levels)
			{

				$application_current_level_user_role = $this->checkUserRoleFromCurrentLevel($each_current_levels['current_level'],$each_current_levels['current_user_email_id']);

				if ($application_current_level_user_role == $search_user_role) {

					if ($level_1_2_3_office == '') {
						$level_1_2_3_office = [];
					}

					$user_posted_office_id_xy = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email IS' => $each_current_levels['current_user_email_id']])->first();

					if (!empty($user_posted_office_id_xy)) {
						$user_posted_office_id_xyx = $user_posted_office_id_xy['posted_ro_office'];
					} else {
						$user_posted_office_id_xyx = '';
					}

					if (in_array($user_posted_office_id_xyx,$level_1_2_3_office))
					{

						$search_user_email = $this->DmiUserRoles->find('all')->select(['user_email_id'])->where(['id IS' => $search_user_email_id])->first();
						//to remove error empty condition added by laxmi B on 16-02-2023
						if ((!empty($each_current_levels['current_user_email_id']) && !empty($search_user_email['user_email_id']) )  && $each_current_levels['current_user_email_id'] == $search_user_email['user_email_id'])
						{
							$application_customer_id_list[$i] = $each_current_levels['customer_id'];
							$i=$i+1;
						}

					}
					else
					{
						$application_customer_id_list[$i] = $each_current_levels['customer_id'];
						$i=$i+1;
					}
				}
			}

		} else {
			$application_customer_id = $this->$table->find('all')->select(['customer_id'])->where($conditions)->extract('customer_id')->toArray();
			// replaced foreach with query by Ankur
			$application_customer_id_list = $application_customer_id;
		}

		if (!empty($application_customer_id_list))
		{

			$i=0;

			if ($pending_application_type == 'new')
			{
				foreach ($application_customer_id_list as $customer_id)
				{
					$customer_id_list = $this->DmiGrantCertificatesPdfs->find('all')->where(['customer_id IS' => $customer_id])->first();

					if (empty($customer_id_list))
					{
						$final_customer_id_list[$i] = $customer_id;
						$i=$i+1;
					}
				}
			}

			elseif ($pending_application_type == 'renewal')
			{
				foreach ($application_customer_id_list as $customer_id )
				{

					$customer_id_list = $this->DmiRenewalFinalSubmits->find('all')->where(['customer_id IS' => $customer_id, 'status' => 'approved'])->first();


					if (empty($customer_id_list))
					{
						$final_customer_id_list[$i] = $customer_id;
						$i=$i+1;
					}
				}

				$final_table = 'DmiRenewalFinalSubmits';
			}

		}
		return $final_customer_id_list;
	}



	// Inprocess Backlog Applications Report
	// Description : This Function Is Used for In-Process Backlog Aplln.Report Count & list
	// @Author : Yashwant
	// Date : 08-Mar-2023

	public function inprocessBacklogApplicationsReport($cert_type,$appl_type)//$backlog_id replace ->cert_type by shreeya
	{

		/*===================Added New Code show the list of count (Start) Date [25-05-2023 By Shreeya] ===========*/
		//pass the parameter of cert_type,appl_type
		$appl_type=base64_decode($appl_type);
		$cert_type=base64_decode($cert_type);
		$data_id =array($cert_type);


		if ($cert_type== 'CA')
		{
			$cert_type = 1;
		}
		elseif ($cert_type== 'PP')
		{
			$cert_type = 2;
		}
		elseif ($cert_type== 'LAB')
		{
			$cert_type = 3;
		}



		//check the which application type is present
		if($appl_type=='new'){
			$processFunction = 'new_app_processed';
		}elseif($appl_type=='renewal'){
			$processFunction = 'renewal_app_processed';
		}elseif($appl_type=='backlog'){
			$processFunction = 'backlog_app_processed';
		}


		//show the count according to application type
		$searchConditions = array();
		$application_processed[] = $this->Reportstatistics->$processFunction($searchConditions,null,null,$cert_type,$appl_type);
		$applListToShow = $application_processed[0][2];


		$application_id = null;
		$application_type = null;
		$user_roles = null;
		$user_office = null;
		$user_email_id =null;
		$date = []; // Rename the variable to avoid overwriting the previous $date variable

		$i = 0;
		foreach ($applListToShow as $each_customer_id) {

			$application_id[$i] = $each_customer_id;


			$table = 'DmiAllApplicationsCurrentPositions';
			$current_users_details = $this->$table->find('all')->where(['customer_id IN' => $each_customer_id])->order(['id' => 'DESC'])->first();


			$application_form_type = $this->Customfunctions->checkApplicantFormType($each_customer_id);

				if ($application_form_type == 'A') {
					$application_type[$i]='CA (Form-A)';
				} elseif ($application_form_type == 'B') {
					$application_type[$i]='Printing Press (Form-B)';
				} elseif ($application_form_type == 'C') {
					$application_type[$i]='Laboratory (Form-C)';
				} elseif ($application_form_type == 'D') {
					$application_type[$i]='Laboratory (Form-D)';
				} elseif ($application_form_type == 'E') {
					$application_type[$i]='CA (Form-E)';
				} elseif ($application_form_type == 'F') {
					$application_type[$i]='CA (Form-F)';
				}

			$date[$i] = $current_users_details['modified']; // Store the value in a new array
			$user_email_id[$i] = $current_users_details['current_user_email_id'];
			$current_level[$i] = $current_users_details['current_level'];


				$user_posted_office_id=array();
				if (!empty($user_email_id[$i])) {
					$user_posted_office_id = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email IN' => $user_email_id[$i]])->first();

				}
				if (!empty($user_posted_office_id)) {
					$user_office[$i] = $this->DmiRoOffices->find('all')->select(['ro_office'])->where(['id' => $user_posted_office_id['posted_ro_office']])->first();

				}

				if (!empty($user_office[$i])) {
					$user_office[$i] = $user_office[$i]['ro_office'];
				} else {
					$user_office[$i] = '--';
				}

				$check_roles=array();
				if (!empty($user_email_id[$i])) {
					$check_roles = $this->DmiUserRoles->find('all')->where(['user_email_id IN' => $user_email_id[$i]])->first();
				}

				if (!empty($check_roles)) {
					$user_list[$i] = $check_roles;
				} else {
					$user_list[$i] = '---';
				}

				$user_roles[$i] = $this->checkUserRoleFromCurrentLevel($current_users_details['current_level'],$current_users_details['current_user_email_id']);



			$i = $i + 1;

			$this->set('date',$date);
			$this->set('user_list',$user_list);
			$this->set('application_type',$application_type);
			$this->set('user_roles',$user_roles);
			$this->set('user_office',$user_office);
			$this->set('user_email_id',$user_email_id);
			$this->set('application_id',$application_id);
		}

		/*====(End)===*/

		// $BacklogId=base64_decode($backlog_id);
		// $data_id =array($BacklogId);

		$application_pending_days = $this->Session->read('pending_days');

		if (!empty($application_pending_days)) {
			$report_name = 'Pending Backlog Applications Report (More than 15 Days)';
		} else {
			$report_name ='Pending Backlog Applications Report';
		}

		$this->set('report_name',$report_name);
		/*$DmiFirms = TableRegistry::getTableLocator()->get('DmiFirms');//initialize model in component
		$final_submitT	= 'DmiFinalSubmits';//change table name by laxmi on 14-12-2023
		$conditions = array();
		$date_conditions = array();

		$firm_result = $DmiFirms->find('all')->where(['is_already_granted IS' => 'yes'])->combine('id', 'customer_id')->toArray();

		$DmiFinalSubmits = TableRegistry::getTableLocator()->get($final_submitT);
		$final_submit_result = $DmiFinalSubmits->find('all')->select(['customer_id'])->distinct(['customer_id'])->where(['customer_id IN' => $firm_result])->toArray();*/

		//echo"<pre>";print_r($final_submit_result);exit;


		//$table = 'DmiRenewalAllCurrentPositions';
		//$table = 'DmiFirms';

		$application_type_xy = array('A'=>'CA (Form-A)','C'=>'Laboratory (Form-C)','E'=>'CA (Form-E)','B'=>'Printing Press (Form-B)','D'=>'Laboratory (Form-D)','F'=>'CA (Form-F)');

		//Change on 9/11/2018, Sorting array by ascending order - By Pravin Bhakare

		//$BacklogId replace ->cert_type By Shreeya on Date [25-05-2023]
		if($cert_type=='CA')
		{
			$application_type_xy = array('A'=>'CA (Form-A)','E'=>'CA (Form-E)','F'=>'CA (Form-F)');
		}
		elseif($cert_type=='PP')
		{
			$application_type_xy = array('B'=>'Printing Press (Form-B)');
		}
		elseif ($cert_type=='LAB') {
			$application_type_xy = array('C'=>'Laboratory (Form-C)','D'=>'Laboratory (Form-D)');
		}

		//Change on 9/11/2018, Sorting array by ascending order - By Pravin Bhakare
		asort($application_type_xy);
		$this->set('application_type_xy',$application_type_xy);
		//$BacklogId replace ->cert_type By Shreeya on Date [25-05-2023]
		$this->set('cert_type',$cert_type);

		$user_roles_xy = array('RO/SO'=>'RO/SO','MO/SMO'=>'MO/SMO','IO'=>'IO');
		//Change on 9/11/2018, Sorting array by ascending order - By Pravin Bhakare
		asort($user_roles_xy);
		$this->set('user_roles_xy',$user_roles_xy);

		$ro_office = $this->DmiRoOffices->find('all')->select(['id', 'ro_office'])->where(['office_type' => 'RO','delete_status IS NULL'])->order(['ro_office' => 'ASC'])
			->combine('id', 'ro_office')->toArray();
		$this->set('ro_office',$ro_office);

		$search_application_type_id = $this->Session->read('search_application_type_id');

		$search_user_role = $this->Session->read('search_user_role');
		$ro_office_id = $this->Session->read('ro_office_id');
		$mo_office_id = $this->Session->read('mo_office_id');
		$io_office_id = $this->Session->read('io_office_id');
		$search_from_date = $this->Session->read('search_from_date');
		$search_to_date = $this->Session->read('search_to_date');
		$search_user_email_id = $this->Session->read('search_user_email_id');

		$download_search_application_type_id = $this->Session->read('search_application_type_id');
		$download_search_user_role = $this->Session->read('search_user_role');
		$download_ro_office_id = $this->Session->read('ro_office_id');
		$download_mo_office_id = $this->Session->read('mo_office_id');
		$download_io_office_id = $this->Session->read('io_office_id');
		$download_search_from_date = $this->Session->read('search_from_date');
		$download_search_to_date = $this->Session->read('search_to_date');
		$download_search_user_email_id = $this->Session->read('search_user_email_id');

		$this->set('search_application_type_id',$search_application_type_id);
		$this->set('search_user_role',$search_user_role);
		$this->set('ro_office_id',$ro_office_id);
		$this->set('mo_office_id',$mo_office_id);
		$this->set('io_office_id',$io_office_id);
		$this->set('search_from_date',$search_from_date);
		$this->set('search_to_date',$search_to_date);
		$this->set('search_user_email_id',$search_user_email_id);
		$download_report = 'no';


		if (null != ($this->request->getData('search_logs')) || null != ($this->request->getData('download_report'))) {

			if (!empty($this->request->getData('download_report'))) {
				$download_report = 'yes';
			}

			$search_application_type_id = $this->request->getData('application_type');
			$search_user_role =  $this->request->getData('user_role');
			$ro_office_id =  $this->request->getData('ro_office');
			$mo_office_id =  $this->request->getData('mo_office');
			$io_office_id =  $this->request->getData('io_office');

			$search_user_email_id =  $this->request->getData('user_id');
			$search_from_date =  $this->request->getData('from_date');
			$search_from_date = $this->Customfunctions->dateFormatCheck($search_from_date);
			$search_to_date =  $this->request->getData('to_date');
			$search_to_date = $this->Customfunctions->dateFormatCheck($search_to_date);
			$this->date_comparison($search_from_date,$search_to_date);

			// Change on 2/11/2018 - For download excel report, Take search filter field value from session variables instend of POST variable - By Pravin
			if ($download_report == 'yes') {

				$search_user_role = $this->Session->read('search_user_role');
				$ro_office_id = $this->Session->read('ro_office_id');
				$mo_office_id = $this->Session->read('mo_office_id');
				$io_office_id = $this->Session->read('io_office_id');
				$search_from_date = $this->Session->read('search_from_date');
				$search_to_date = $this->Session->read('search_to_date');
				$search_user_email_id = $this->Session->read('search_user_email_id');
			}

			$download_application_customer_id_list = $this->pendingBacklogAppln($download_search_application_type_id,$download_search_user_role,$download_ro_office_id,$download_mo_office_id,$download_io_office_id,$download_search_from_date,$download_search_to_date,$download_search_user_email_id,$table,$application_pending_days,$data_id);

			$this->Session->delete('search_application_type_id');
			$this->Session->delete('search_user_role');
			$this->Session->delete('ro_office_id');
			$this->Session->delete('mo_office_id');
			$this->Session->delete('io_office_id');
			$this->Session->delete('search_from_date');
			$this->Session->delete('search_to_date');
			$this->Session->delete('search_user_email_id');

			$this->Session->write('search_application_type_id',$search_application_type_id);
			$this->Session->write('search_user_role',$search_user_role);
			$this->Session->write('ro_office_id',$ro_office_id);
			$this->Session->write('mo_office_id',$mo_office_id);
			$this->Session->write('io_office_id',$io_office_id);
			$this->Session->write('search_from_date',$search_from_date);
			$this->Session->write('search_to_date',$search_to_date);
			$this->Session->write('search_user_email_id',$search_user_email_id);

			$this->set('search_application_type_id',$search_application_type_id);
			$this->set('search_user_role',$search_user_role);
			$this->set('ro_office_id',$ro_office_id);
			$this->set('mo_office_id',$mo_office_id);
			$this->set('io_office_id',$io_office_id);
			$this->set('search_from_date',$search_from_date);
			$this->set('search_to_date',$search_to_date);
			$this->set('search_user_email_id',$search_user_email_id);

			$application_customer_id_list = $this->pendingBacklogAppln($search_application_type_id,$search_user_role,$ro_office_id,$mo_office_id,$io_office_id,$search_from_date,$search_to_date,$search_user_email_id,$table,$application_pending_days,$data_id);

			if (!empty($application_customer_id_list)) {

				$current_users_details = $this->$table->find('all')->where(['customer_id IN'=>$application_customer_id_list])->order(['id' => 'DESC'])->toArray();

				//Fetch the all data that required for creating the downloading report as execel (Done by pravin 14-03-2018)
				if ($download_report == 'yes') {
					$download_pending_application = $this->$table->find('all')->where(['customer_id IN' => $download_application_customer_id_list])->order(['id' => 'DESC'])->toArray();
					$this->downloadPendingApplicationReport($download_pending_application,$pending_application_type,$table);
				}

			} else {
				$current_users_details = null;
			}
			//Below query commented by shreya for display list of backlog application
			//$this->pendingBackApplicationReportResults($current_users_details,$table);

		} else {

			$application_customer_id_list = $this->pendingBacklogAppln($search_application_type_id,$search_user_role,$ro_office_id,$mo_office_id,$io_office_id,$search_from_date,$search_to_date,$search_user_email_id,$table,$application_pending_days,$data_id);

			if (!empty($application_customer_id_list)) {

				$current_users_details = $this->$table->find('all')->where(['customer_id IN' => $application_customer_id_list])->order(['id' => 'DESC'])->toArray();

				//Fetch the all data that required for creating the downloading report as execel (Done by pravin 14-03-2018)
				if ($download_report == 'yes') {
					$download_pending_application = $this->$table->find('all')->where(['customer_id IN' => $application_customer_id_list])
						->order(['id' => 'DESC'])->toArray();
					$this->downloadPendingApplicationReport($download_pending_application,$pending_application_type,$table);
				}

			} else
			{
				$current_users_details = null;
			}
			//Below query commented by shreya for display list of backlog application
			//$this->pendingBackApplicationReportResults($current_users_details,$table);

		}
	}





	// Pending Backlog Application
	// Description : This Function Is Used for In-Process Backlog Aplln.Report Count & list
	// @Author : Yashwant
	// Date : 08-Mar-2023

	public function pendingBacklogAppln($search_application_type_id,$search_user_role,$ro_office_id,$mo_office_id,$io_office_id,$search_from_date,$search_to_date,$search_user_email_id,$table,$application_pending_days,$data_id)
	{
		$current_date = new \DateTime(date("d-m-Y")); // Ankur updated new DateTime to new \DateTime as Class "App\Controller\DateTime" not found
		$modify_date_obj = $current_date->modify('-15 day');
		$modify_date = $modify_date_obj->format('d-m-Y H:i:s');


		if (!empty($application_pending_days))
		{

			$conditions = ['DATE(modified) <' => $modify_date];

			$date_conditions = ['date(modified) BETWEEN :start AND :end', 'DATE(created) <' => $modify_date];

			$date_conditions_1 = ['date(modified) BETWEEN :start AND :end', 'DATE(created) <' => $modify_date];

		} else {

			$conditions = [];

			$date_conditions = ['date(modified) BETWEEN :start AND :end'];

			$date_conditions_1 = ['date(modified) BETWEEN :start AND :end', 'DATE(created) <' => $modify_date];
		}

		$final_customer_id_list = null;

		if ($ro_office_id != '' && $search_user_role == 'RO/SO') {
			$level_1_2_3_office = $ro_office_id;
		} elseif ($mo_office_id != '' && $search_user_role == 'MO/SMO') {
			$level_1_2_3_office = $mo_office_id;
		} elseif ($io_office_id != '' && $search_user_role == 'IO') {
			$level_1_2_3_office = $io_office_id;
		} else {
			$level_1_2_3_office = '';
		}

		if ($search_application_type_id != '' && $search_user_role == '' && $search_from_date == '' && $search_to_date == '')
		{
			$application_customer_id = $this->$table->find('all')->where($conditions)->where(['is_already_granted IS' => 'yes'])->toArray();
			//$application_customer_id = $DmiFirms->find('all')->where($conditions)->where(['is_already_granted IS' => 'yes'])->combine('id', 'customer_id')->toArray();

			$i=0;
			foreach ($application_customer_id as $each_customer_id)
			{
				if (!empty($each_customer_id['customer_id']))
				{
					$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

					if (in_array($application_customer_type, $search_application_type_id, TRUE))
					{
						$application_customer_id_list[$i] = $each_customer_id['customer_id'];
						$i=$i+1;
					}
				}
			}


		/*===Start YAshwant 31-MAR-2023 BAcklog Search FROM-TO Date Filter=======*/
		} elseif ($search_application_type_id == '' && $search_user_role == '' && $level_1_2_3_office == '' && $search_from_date != '' && $search_to_date != '')
		{
			if ($search_application_type_id != '')
			{

				$application_customer_id = $this->$table->find('all')->where($date_conditions)->where(['is_already_granted IS' => 'yes'])->bind(':start', $search_from_date, 'date')->bind(':end', $search_to_date, 'date')->toArray();

				/*$DmiFirms = TableRegistry::getTableLocator()->get('DmiFirms');
				$firm_result = $DmiFirms->find('all')->where($conditions)->where(['is_already_granted IS' => 'yes'])->combine('id', 'customer_id')->toArray();
				$DmiFinalSubmits = TableRegistry::getTableLocator()->get('DmiFinalSubmits');
				$application_customer_id = $DmiFinalSubmits->find('all')->select(['customer_id'])->distinct(['customer_id'])->where(['customer_id IN' => $firm_result])->bind(':start', $search_from_date, 'date')->bind(':end', $search_to_date, 'date')->toArray();*/

				$i=0;
				foreach ($application_customer_id as $each_customer_id)
				{
					if (!empty($each_customer_id['customer_id'])) {
						$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

						if (in_array($application_customer_type,$search_application_type_id)) {
							$application_customer_id_list[$i] = $each_customer_id['customer_id'];
							$i=$i+1;
						}
					}
				}

			} else {
				$application_customer_id_list = $this->$table->find('all')->select(['customer_id'])->where($date_conditions)->bind(':start', $search_from_date, 'date')->bind(':end', $search_to_date, 'date')->combine('id', 'customer_id')->toArray();
			}


		/*=====Start Yashwant 08/mar/2023 This function used for IN PRocess Report Count ======*/
		} elseif(!empty($data_id))
		{
			//$application_customer_id = $this->$table->find('all')->where($conditions)->toArray();
			$DmiFirms = TableRegistry::getTableLocator()->get('DmiFirms');
			$firm_result = $DmiFirms->find('all')->where($conditions)->where(['is_already_granted IS' => 'yes'])->combine('id', 'customer_id')->toArray();
			//$firm_result = $this->$table->find('all')->where($conditions)->where(['is_already_granted IS' => 'yes'])->combine('id','customer_id')->toArray();
			//$application_customer_id = $this->$table->find('all')->where($conditions)->where(['is_already_granted IS' => 'yes'])->toArray();

			$DmiFinalSubmits = TableRegistry::getTableLocator()->get('DmiFinalSubmits');
			$application_customer_id = $DmiFinalSubmits->find('all')->select(['customer_id'])->distinct(['customer_id'])->where(['customer_id IN' => $firm_result])->toArray();
			//yashwant-08/Mar/2023 == Grant-Backlog Appln-Query
			//$application_customer_id = $DmiFinalSubmits->find('all')->select(['customer_id'])->distinct(['customer_id'])->where(['customer_id IN' => $firm_result,'current_level' => 'level_3', 'status' => 'approved'])->toArray();


			$i=0;
			foreach ($application_customer_id as $each_customer_id)
			{
				if (!empty($each_customer_id['customer_id']))
				{
					$application_customer_type = $this->Reportsfunctions->newApplicantType($each_customer_id['customer_id']);
					if (in_array($application_customer_type, $data_id, TRUE))
					{
						$application_customer_id_list[$i] = $each_customer_id['customer_id'];
						$i=$i+1;
					}
				}
			}

		} elseif (($search_application_type_id == '' || $search_application_type_id != '') && $search_user_role != '' && $level_1_2_3_office == '' && $search_from_date == '' && $search_to_date == '')
		{

			if ($search_application_type_id != '') {

				$application_customer_id = $this->$table->find('all')->where($conditions)->where(['is_already_granted IS' => 'yes'])->toArray();
				/*$DmiFirms = TableRegistry::getTableLocator()->get('DmiFirms');
				$firm_result = $DmiFirms->find('all')->where($conditions)->where(['is_already_granted IS' => 'yes'])->combine('id', 'customer_id')->toArray();
				$DmiFinalSubmits = TableRegistry::getTableLocator()->get('DmiFinalSubmits');
				$application_customer_id = $DmiFinalSubmits->find('all')->select(['customer_id'])->distinct(['customer_id'])->where(['customer_id IN' => $firm_result])->toArray();*/

				$i=0;
				foreach ($application_customer_id as $each_customer_id) {
					if (!empty($each_customer_id['customer_id'])) {
						$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

						if (in_array($application_customer_type,$search_application_type_id)) {
							$seach_application_customer_id_list[$i] = $each_customer_id['customer_id'];
							$i=$i+1;
						}
					}
				}

				$application_current_levels = $this->$table->find('all')->where(['customer_id IN' => $seach_application_customer_id_list])->toArray();
			} else {

				$application_current_levels = $this->$table->find('all')->where($conditions)->toArray();
			}

			$i=0;
			foreach ($application_current_levels as $each_current_levels) {

				$application_current_level_user_role = $this->checkUserRoleFromCurrentLevel($each_current_levels['current_level'], $each_current_levels['current_user_email_id']);

				if ($application_current_level_user_role == $search_user_role) {
					$application_customer_id_list[$i] = $each_current_levels['customer_id'];
					$i=$i+1;
				}
			}

		} elseif (($search_application_type_id == '' || $search_application_type_id != '') && $search_user_role != '' && $level_1_2_3_office != '' && $search_from_date == '' && $search_to_date == '' && $search_user_email_id =='')
		{

			if ($search_application_type_id != '')
			{

				$application_customer_id = $this->$table->find('all')->where($conditions)->where(['is_already_granted IS' => 'yes'])->toArray();

				/*$DmiFirms = TableRegistry::getTableLocator()->get('DmiFirms');
				$firm_result = $DmiFirms->find('all')->where($conditions)->where(['is_already_granted IS' => 'yes'])->combine('id', 'customer_id')->toArray();
				$DmiFinalSubmits = TableRegistry::getTableLocator()->get('DmiFinalSubmits');
				$application_customer_id = $DmiFinalSubmits->find('all')->select(['customer_id'])->distinct(['customer_id'])->where(['customer_id IN' => $firm_result])->toArray();*/

				$i=0;
				foreach ($application_customer_id as $each_customer_id) {
					if (!empty($each_customer_id['customer_id'])) {
						$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

						if (in_array($application_customer_type,$search_application_type_id)) {
							$seach_application_customer_id_list[$i] = $each_customer_id['customer_id'];
							$i=$i+1;
						}
					}
				}
				$application_current_levels = $this->$table->find('all')->where(['customer_id IN' => $seach_application_customer_id_list])->toArray();

			} else {

				$application_current_levels = $this->$table->find('all')->where($conditions);
			}

			$i=0;
			foreach ($application_current_levels as $each_current_levels)
			{

				$application_current_level_user_role = $this->checkUserRoleFromCurrentLevel($each_current_levels['current_level'], $each_current_levels['current_user_email_id']);

				if ($application_current_level_user_role == $search_user_role) {

					$user_posted_office_id_xy = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email IS' => $each_current_levels['current_user_email_id']])->first();


					if (!empty($user_posted_office_id_xy)) {
						$user_posted_office_id_xyx = $user_posted_office_id_xy['posted_ro_office'];
					} else {
						$user_posted_office_id_xyx = '';
					}

					if (in_array($user_posted_office_id_xyx,$level_1_2_3_office)) {
						$application_customer_id_list[$i] = $each_current_levels['customer_id'];
						$i=$i+1;
					}
				}
			}

		} elseif (($search_application_type_id == '' || $search_application_type_id != '') && $search_user_role == '' && $level_1_2_3_office == '' && $search_from_date != '' && $search_to_date != '')
		{

			if ($search_application_type_id != '')
			{
				$application_customer_id = $this->$table->find('all')->where($date_conditions)->where(['is_already_granted IS' => 'yes'])->bind(':start', $search_from_date, 'date')->bind(':end', $search_to_date, 'date')->toArray();
				/*$DmiFirms = TableRegistry::getTableLocator()->get('DmiFirms');
				$firm_result = $DmiFirms->find('all')->where($conditions)->where(['is_already_granted IS' => 'yes'])->combine('id', 'customer_id')->toArray();
				$DmiFinalSubmits = TableRegistry::getTableLocator()->get('DmiFinalSubmits');
				$application_customer_id = $DmiFinalSubmits->find('all')->select(['customer_id'])->distinct(['customer_id'])->where(['customer_id IN' => $firm_result])->bind(':start', $search_from_date, 'date')->bind(':end', $search_to_date, 'date')->toArray();*/

				$i=0;
				foreach ($application_customer_id as $each_customer_id)
				{
					if (!empty($each_customer_id['customer_id'])) {
						$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

						if (in_array($application_customer_type,$search_application_type_id)) {
							$application_customer_id_list[$i] = $each_customer_id['customer_id'];
							$i=$i+1;
						}
					}
				}

			} else{

				$application_customer_id_list = $this->$table->find('all')->select(['customer_id'])->where($date_conditions_1)->bind(':start', $search_from_date, 'date')->bind(':end', $search_to_date, 'date')->combine('id', 'customer_id')->toArray();
			}

		} elseif (($search_application_type_id == '' || $search_application_type_id != '') && $search_user_role != '' && $level_1_2_3_office != '' && $search_from_date == '' && $search_to_date == '' && $search_user_email_id !='')
		{
			if ($search_application_type_id != '') {

				$application_customer_id = $this->$table->find('all')->where($conditions)->where(['is_already_granted IS' => 'yes'])->toArray();

				/*$DmiFirms = TableRegistry::getTableLocator()->get('DmiFirms');
				$firm_result = $DmiFirms->find('all')->where($conditions)->where(['is_already_granted IS' => 'yes'])->combine('id', 'customer_id')->toArray();
				$DmiFinalSubmits = TableRegistry::getTableLocator()->get('DmiFinalSubmits');
				$application_customer_id = $DmiFinalSubmits->find('all')->select(['customer_id'])->distinct(['customer_id'])->where(['customer_id IN' => $firm_result])->toArray();*/

				$i=0;
				foreach ($application_customer_id as $each_customer_id) {

					if (!empty($each_customer_id['customer_id'])) {

						$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

						if (in_array($application_customer_type, $search_application_type_id)) {
							$seach_application_customer_id_list[$i] = $each_customer_id['customer_id'];
							$i=$i+1;
						}
					}
				}

				$application_current_levels = $this->$table->find('all')->where(['customer_id IN' => $seach_application_customer_id_list])->toArray();

			} else {

				$application_current_levels = $this->$table->find('all')->where($conditions)->toArray();
			}

			$i=0;
			foreach ($application_current_levels as $each_current_levels) {

				$application_current_level_user_role = $this->checkUserRoleFromCurrentLevel($each_current_levels['current_level'], $each_current_levels['current_user_email_id']);

				if ($application_current_level_user_role == $search_user_role) {

					$user_posted_office_id_xy = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email IS' => $each_current_levels['current_user_email_id']])->first();

					if (!empty($user_posted_office_id_xy)) {
						$user_posted_office_id_xyx = $user_posted_office_id_xy['posted_ro_office'];
					} else {
						$user_posted_office_id_xyx = '';
					}

					if (in_array($user_posted_office_id_xyx,$level_1_2_3_office)) {

						$search_user_email = $this->DmiUserRoles->find('all')->select(['user_email_id'])->where(['id IS' => $search_user_email_id])->first();

						if ($each_current_levels['current_user_email_id'] == $search_user_email['user_email_id']) {
							$application_customer_id_list[$i] = $each_current_levels['customer_id'];
							$i=$i+1;
						}
					}
				}
			}

		} elseif (($search_application_type_id == '' || $search_application_type_id != '') && $search_user_role != '' && $search_from_date != '' && $search_to_date != '' && $level_1_2_3_office !='' && ($search_user_email_id != '' || $search_user_email_id == ''))
		{

			if ($search_application_type_id != '')
			{

				$application_customer_id = $this->$table->find('all')->where($conditions)->where(['is_already_granted IS' => 'yes'])->toArray();

				/*$DmiFirms = TableRegistry::getTableLocator()->get('DmiFirms');
				$firm_result = $DmiFirms->find('all')->where($conditions)->where(['is_already_granted IS' => 'yes'])->combine('id', 'customer_id')->toArray();
				$DmiFinalSubmits = TableRegistry::getTableLocator()->get('DmiFinalSubmits');
				$application_customer_id = $DmiFinalSubmits->find('all')->select(['customer_id'])->distinct(['customer_id'])->where(['customer_id IN' => $firm_result])->toArray();*/

				$i=0;
				foreach ($application_customer_id as $each_customer_id) {

					if (!empty($each_customer_id['customer_id'])) {
						$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

						if (in_array($application_customer_type,$search_application_type_id)) {
							$seach_application_customer_id_list[$i] = $each_customer_id['customer_id'];
							$i=$i+1;
						}
					}
				}

				$application_current_levels = $this->$table->find('all')->where(['customer_id IN' => $seach_application_customer_id_list])
					->where(['date(modified) BETWEEN :start AND :end'])->bind(':start', $search_from_date, 'date')->bind(':end', $search_to_date, 'date')->toArray();
			} else {

				$application_current_levels = $this->$table->find('all')->where($date_conditions)->bind(':start', $search_from_date, 'date')
				->bind(':end', $search_to_date, 'date')->toArray();
			}

			$i=0;
			foreach ($application_current_levels as $each_current_levels) {

				$application_current_level_user_role = $this->checkUserRoleFromCurrentLevel($each_current_levels['current_level'],$each_current_levels['current_user_email_id']);

				if ($application_current_level_user_role == $search_user_role) {

					if ($level_1_2_3_office == '') {
						$level_1_2_3_office = [];
					}

					$user_posted_office_id_xy = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email IS' => $each_current_levels['current_user_email_id']])->first();

					if (!empty($user_posted_office_id_xy)) {
						$user_posted_office_id_xyx = $user_posted_office_id_xy['posted_ro_office'];
					} else {
						$user_posted_office_id_xyx = '';
					}

					if (in_array($user_posted_office_id_xyx,$level_1_2_3_office)) {

						$search_user_email = $this->DmiUserRoles->find('all')->select(['user_email_id'])->where(['id IS' => $search_user_email_id])->first();
						//to remove error empty condition added by laxmi B on 16-02-2023
						if ((!empty($each_current_levels['current_user_email_id']) && !empty($search_user_email['user_email_id']) )  && $each_current_levels['current_user_email_id'] == $search_user_email['user_email_id']) {
							$application_customer_id_list[$i] = $each_current_levels['customer_id'];
							$i=$i+1;
						}

					} else {
						$application_customer_id_list[$i] = $each_current_levels['customer_id'];
						$i=$i+1;
					}
				}
			}

		/*== Below Condition Check In-process backlog Record in Two-Tables finalSubmit & CertificationPdf Table yashwant 08/mar/2023 ===*/
		}

		if (!empty($application_customer_id_list))
		{
			$i=0;

			foreach ($application_customer_id_list as $customer_id)
			{
				$customer_id_list = $this->DmiGrantCertificatesPdfs->find('all')->where(['customer_id IS' => $customer_id])->first();
				if (empty($customer_id_list))
				{
					$final_customer_id_list[$i] = $customer_id;
					$i=$i+1;
				}
			}
		}

		return $final_customer_id_list;


	}




	// approved Renewal Grant Report
	// Description : Function derives for Ganted Renewal Application(Esigned)
	// @Author : Yashwant & shreeya
	// Date : 10-Mar-2023

	public function approvedRenewalGrantReport($cert_type) //$newapp_id ->replace by cert_type by shreeya
	{
		// $newappId replace $cert_type by Shreeya date [26-05-2023]
		$cert_type=base64_decode($cert_type);
		$data_id =array($cert_type);

		$aqcms_from_date ='' ;
		$aqcms_to_date = '';
		$aqcms_ro_office_short_code = '';
		$aqcms_ro_office_id = '';

		$approved_application_type = $this->Session->read('approved_application_type');
		$approved_application_type = 'renewal';

		/*if ($approved_application_type == 'new' || $approved_application_type =='')
		{
			$table = 'DmiFinalSubmits';
			$report_heading = 'Approved New Applications Report';
		}*/
		if ($approved_application_type == 'renewal') {

			$table = 'DmiRenewalFinalSubmits';
			$report_heading = 'Approved Renewal Applications Report';

		}elseif ($approved_application_type == 'all_reports') {

			$table = 'DmiGrantCertificatesPdfs';
			$report_heading = 'All Approved Report';


			// this below code is added to show the deafult office by Akash on 16-06-2022
			$posted_ro_office = $this->DmiUsers->find('all',array('fields'=>'posted_ro_office', 'conditions'=>array('email IS'=>$_SESSION['username'])))->first();
			$default_ro_office = $this->DmiRoOffices->find('all',array('conditions'=>array('id IS'=>$posted_ro_office['posted_ro_office'])))->first();
			$this->set('default_ro_office',$default_ro_office['ro_office']);
		}

		//************************New Code Added for show count list by Shreeya Date [25-05-2023]**************************************************************************

		//check the which certificate type is present by shreeya on date [26-05-2023]
		if ($cert_type== 'CA')
		{
			$cert_type = 1;
		}
		elseif ($cert_type== 'PP')
		{
			$cert_type = 2;
		}
		elseif ($cert_type== 'LAB')
		{
			$cert_type = 3;
		}


		//check the which application type is present by shreeya on date [26-05-2023]
		if($approved_application_type=='new'){
			$processFunction = 'new_app_processed';
		}elseif($approved_application_type=='renewal'){
			$processFunction = 'renewal_app_processed';
		}elseif($approved_application_type=='backlog'){
			$processFunction = 'backlog_app_processed';
		}


		//show the count according to application type  and cutomer_id by shreeya on date [26-05-2023]
		$searchConditions = array();
		$application_processed[] = $this->Reportstatistics->$processFunction($searchConditions,null,null,$cert_type,$approved_application_type);
		$applListToShow = $application_processed[0][3];


		$date=array();
		$application_type=array();
		$application_user_email_id=array();
		$user_office=array();
		$application_customer_id=array();
		$name_of_the_firm=array();
		$address_of_the_firm=array();
		$contact_details_of_the_firm=array();
		$approved_TBL_details_tbl_name=array();
		$approved_TBL_details_tbl_registered_no=array();
		$laboratory_details_name=array();
		$laboratory_details_address=array();


		$i=0;
			//applied array_unique function on 18-07-2019
			foreach (array_unique($applListToShow) as $approved_application) {

				$approved_application_details = array(); //this line added on 18-07-2019

				// THIS BELOW CONDITION IS ADDED FOR THE ALL REPORTS BY AKASH ON 16-06-2022
				if ($approved_application_type == 'all_reports') {
					$approved_application_details = $this->DmiGrantCertificatesPdfs->find('all',array('conditions'=>array('customer_id IS'=>$approved_application),'order' => array('id' => 'desc')))->first();
				} elseif ($approved_application_type == 'new' || $approved_application_type =='') {
					$approved_application_details = $this->DmiGrantCertificatesPdfs->find('all')->where(['customer_id' => $approved_application])->first();
				} elseif ($approved_application_type == 'backlog') {
					$approved_application_details = $this->DmiFinalSubmits->find('all')->where(['customer_id' => $approved_application,'status'=>'approved','current_level'=>'level_3'])->first();
				} elseif ($approved_application_type == 'renewal') {

					$approved_application_detail = $this->DmiGrantCertificatesPdfs->find('all')->select(['id'])->where(['customer_id IS'=>$approved_application])->combine('id','id')->toArray();
					//print_r($approved_application.'@'.$i.'@@');
					//applied this condition on 27-04-2019
					if (!empty($approved_application_detail)) {
						$approved_application_details = $this->DmiGrantCertificatesPdfs->find('all')->where(['id IN'=> $approved_application_detail])->order(['id' => 'DESC'])->first();

					}
				}

				//this condition added on 18-07-2019
				if (!empty($approved_application_details)) {

					$approved_application_result = $approved_application_details;

					//to check if the application is old or not to print on the excel and for viewing part dont by Akash 07-04-2022

					// THIS BELOW CONDITION IS ADDED FOR THE ALL REPORTS BY AKASH ON 16-06-2022
					if ($approved_application_type == 'all_reports') {

						if ($approved_application_result['pdf_version'] > '1') {
							$approved_application_type_text[$i] = "<b>RENEWAL</b>";
						} elseif ($approved_application_result['user_email_id'] == 'old_application') {
							$approved_application_type_text[$i] = "<i>OLD</i>";
						} else {
							$approved_application_type_text[$i] = "NEW";
						}

					} else {

						if ($approved_application_type == 'renewal') {
							$approved_application_type_text[$i] = "<b>RENEWAL</b>";
						} elseif ($approved_application_result['user_email_id'] == 'old_application') {
							$approved_application_type_text[$i] = "<i>OLD</i>";
						} else {
							$approved_application_type_text[$i] = "NEW";
						}

					}


					if ($approved_application_result['user_email_id'] == 'old_application') {
						$old_app_approved_by = $this->Customfunctions->old_app_approved_by($approved_application_result['customer_id']);
						$approved_application_result['user_email_id'] = $old_app_approved_by;
					}

					$explode = explode("/",$approved_application_result['customer_id']);

					$approved_office_id = $this->DmiRoOffices->find('all')->select(['ro_email_id'])->where(['short_code' => $explode[2]])->first();

					$user_posted_office_id = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email'=>$approved_office_id['ro_email_id']])->first();

					if (!empty($user_posted_office_id)) {

						$user_office_details = $this->DmiRoOffices->find('all')->select(['ro_office'])->where(['id'=>$user_posted_office_id['posted_ro_office']])->first();

						if (!empty($user_office_details)) {
							$user_office[$i] = $user_office_details['ro_office'];
						} else {
							$user_office[$i] = 'N/A';
						}
					} else {
						$user_office[$i] = 'N/A';
					}

					$application_form_type = $this->Customfunctions->checkApplicantFormType($approved_application_result['customer_id']);

					if ($application_form_type == 'A') {
						$application_type[$i]='CA (Form-A)';
					} elseif ($application_form_type == 'B') {
						$application_type[$i]='Printing Press (Form-B)';
					} elseif ($application_form_type == 'C') {
						$application_type[$i]='Laboratory (Form-C)';
					} elseif ($application_form_type == 'D') {
						$application_type[$i]='Laboratory (Form-D)';
					} elseif ($application_form_type == 'E') {
						$application_type[$i]='CA (Form-E)';
					} elseif ($application_form_type == 'F') {
						$application_type[$i]='CA (Form-F)';
					}

					$date[$i] = $approved_application_result['created'];
					$application_customer_id[$i] = $approved_application_result['customer_id'];

					//added by the akash on 13-11-2021
					$firmDetails = $this->DmiFirms->find('all',array('conditions'=>array('customer_id IS'=>$approved_application_result['customer_id'])))->first();

					$name_of_the_firm[$i] = $firmDetails['firm_name'];
					$address_of_the_firm[$i] = $firmDetails['street_address'];
					$contact_details_of_the_firm[$i] = base64_decode($firmDetails['email']);
					$phoneno[$i] = $firmDetails['mobile_no'];

					//tbl details
					$tbl_details = $this->DmiAllTblsDetails->find('all',array('conditions'=>array('customer_id IS'=>$approved_application_result['customer_id'],'OR' => array('delete_status IS NULL', 'delete_status' => 'no'))))->toArray();

					if (!empty($tbl_details)) {
						$j=0;
						foreach ($tbl_details as $each) {

							$approved_TBL_details_tbl_name[$i][$j] = $each['tbl_name'];
							$approved_TBL_details_tbl_registered_no[$i][$j] = $each['tbl_registered_no'];
							$j++;
						}

					} else {
						$approved_TBL_details_tbl_name[$i][0] = 'N/A';
						$approved_TBL_details_tbl_registered_no[$i][0] = 'N/A';
					}



					//lab details
					$lab_details = $this->DmiCustomerLaboratoryDetails->find('all',array('conditions'=>array('customer_id IS'=>$approved_application_result['customer_id'])))->toArray();
					if (!empty($lab_details)) {
						$laboratory_details_name[$i] = $lab_details[0]['laboratory_name'];
						$laboratory_details_address[$i] = $lab_details[0]['street_address'];
					} else {
						$laboratory_details_name[$i] = 'N/A';
						$laboratory_details_address[$i] = 'N/A';
					}

					$commodity_value = $this->DmiFirms->find('all')->select(['sub_commodity'])->where(['customer_id'=>$approved_application_result['customer_id']])->first();

					$commodity_list[$i] = $this->Customfunctions->showCommdityInApplList($commodity_value['sub_commodity']);


					//Added this Code on the 08-04-2022 as the base encoding was not on some emails so to dispaly the email , checking if the encoding is needed or not.
					$checkEmailForEncoding[$i] = $approved_application_result['user_email_id'];

					if($this->isBase64Encoded($checkEmailForEncoding[$i]) == true){
						$application_user_email_id[$i] = base64_decode($approved_application_result['user_email_id']);
					} else {
						$application_user_email_id[$i] = $approved_application_result['user_email_id'];
					}

					//check the expiry dateand print to the reports  added by Akash on 24-05-2022
					$grant_date = chop($approved_application_details['date'],"00:00:00");
					$valid_upto[$i] = $this->Customfunctions->getCertificateValidUptoDate($approved_application_result['customer_id'],$grant_date);

					//check the state name added by akash on 14-06-2022
					$state_name[$i] = $this->getStateName($approved_application_result['customer_id']);

					//Certificate Issued on
					$issued_on[$i] = chop($approved_application_result['date'],"00:00:00");

					$i=$i+1;
				}
			}

			//exit;

		$this->set('date',$date);
		$this->set('application_customer_id',$application_customer_id);
		$this->set('application_user_email_id',$application_user_email_id);
		$this->set('application_type',$application_type);
		$this->set('user_office',$user_office);
		//$this->set('approved_application_list',$approved_application_list);
		$this->set('commodity_list',$commodity_list);
		$this->set('approved_application_type',$approved_application_type_text);
		$this->set('name_of_the_firm',$name_of_the_firm);
		$this->set('address_of_the_firm',$address_of_the_firm);
		$this->set('contact_details_of_the_firm',$contact_details_of_the_firm);
		$this->set('approved_TBL_details_tbl_name',$approved_TBL_details_tbl_name);
		$this->set('approved_TBL_details_tbl_registered_no',$approved_TBL_details_tbl_registered_no);
		$this->set('laboratory_details_name',$laboratory_details_name);
		$this->set('laboratory_details_address',$laboratory_details_address);
		$this->set('valid_upto',$valid_upto);
		$this->set('state_name',$state_name);
		$this->set('phoneno',$phoneno);
		$this->set('issued_on',$issued_on);


		//*****************End************************************


		$this->set('table', $table);	// set table value ( Done by pravin 16-07-2018)
		$this->set('report_heading', $report_heading);

		$application_type_xy = array('A'=>'CA (Form-A)','C'=>'Laboratory (Form-C)','E'=>'CA (Form-E)','B'=>'Printing Press (Form-B)','D'=>'Laboratory (Form-D)','F'=>'CA (Form-F)');
		//Change on 9/11/2018, Sorting array by ascending order - By Pravin Bhakare
		asort($application_type_xy);
		$this->set('application_type_xy', $application_type_xy);
		// $newappId replace $cert_type by Shreeya date [26-05-2023]
		$this->set('cert_type',$cert_type);

		// $newappId replace $cert_type by Shreeya date [26-05-2023]
		if($cert_type=='CA'){
			$application_type_xy = array('A'=>'CA (Form-A)','E'=>'CA (Form-E)','F'=>'CA (Form-F)');
		}elseif($cert_type=='PP') {
			$application_type_xy = array('B'=>'Printing Press (Form-B)');
		}elseif ($cert_type=='LAB') {
			$application_type_xy = array('C'=>'Laboratory (Form-C)','D'=>'Laboratory (Form-D)');
		}


		//added 'office_type'=>'RO' condition on 27-07-2018   // Change on 3/11/2018 -  add order by condition - by Pravin Bhakare
		$ro_office = $this->DmiRoOffices->find('all')->select(['id', 'ro_office'])->where(['office_type' => 'RO','delete_status IS NULL'])->order(['ro_office' => 'ASC'])->combine('id', 'ro_office')->toArray();
		$this->set('ro_office',$ro_office);

		$search_application_type_id = $this->Session->read('search_application_type_id');
		$application_approved_office = $this->Session->read('application_approved_office');
		$search_from_date = $this->Session->read('search_from_date');
		$search_to_date = $this->Session->read('search_to_date');

		$this->set('search_application_type_id',$search_application_type_id);
		$this->set('application_approved_office',$application_approved_office);
		$this->set('search_from_date',$search_from_date);
		$this->set('search_to_date',$search_to_date);

		$search_flag = 'off'; // added by Ankur
		// Set default value for download report click event (Done by pravin 13-03-2018)
		$download_report = 'no';

		//if from to date and office id in  session condtion added by laxmi B on 15-02-2023

		$aqcms_from_date = $this->Session->read('from_date') ;
		$aqcms_to_date = $this->Session->read('to_date');
		$aqcms_ro_office_short_code = $this->Session->read('roOfficeShortCode');
		$aqcms_ro_office_id = $this->Session->read('ro_office_id');
		$this->Session->delete('ro_office_id');
		$this->Session->delete('roOfficeShortCode');
		$this->Session->delete('from_date');
		$this->Session->delete('to_date');



		if ((((!empty($aqcms_from_date && !empty($aqcms_to_date))) || !empty($aqcms_ro_office_short_code))) || null != ($this->request->getData('search_logs')) || null != ($this->request->getData('download_report')))
		{
			$search_flag = 'on'; // added by Ankur
			//Check not empty "Download Report as Excel" button Request, if condition TRUE then set value "yes" for "Download Report as Excel" click event
			//and pass this value to "approved_application_search_conditions" function (Done by pravin 13-03-2018)
			if (!empty($this->request->getData('download_report'))) {
				$download_report = 'yes';
			}

			$search_application_type_id = $this->request->getData('application_type');
			$application_approved_office = $this->request->getData('office');
			$search_from_date =  $this->request->getData('from_date');
			$search_from_date = $this->Customfunctions->dateFormatCheck($search_from_date);
			$search_to_date =  $this->request->getData('to_date');
			$search_to_date = $this->Customfunctions->dateFormatCheck($search_to_date);
			$this->date_comparison($search_from_date, $search_to_date);

			// Change on 3/11/2018 - For download excel report, Take search filter field value from session variables instend of POST variable - By Pravin
			if ($download_report == 'yes') {

				$search_application_type_id = $this->Session->read('search_application_type_id');
				$application_approved_office = $this->Session->read('application_approved_office');
				$search_from_date = $this->Session->read('search_from_date');
				$search_to_date = $this->Session->read('search_to_date');
			}

			$this->Session->delete('search_application_type_id');
			$this->Session->delete('application_approved_office');
			$this->Session->delete('search_from_date');
			$this->Session->delete('search_to_date');



			//set from_date and to_date and office name from session of statstics report added by laxmi B. on 15-02-2023
			if(!empty($aqcms_from_date) && !empty($aqcms_to_date && empty($aqcms_ro_office_short_code) )){
				$search_from_date = $aqcms_from_date;
				$search_to_date = $aqcms_to_date;
				$search_from_date = $this->Customfunctions->dateFormatCheck($search_from_date);
				$search_to_date = $this->Customfunctions->dateFormatCheck($search_to_date);
				$this->date_comparison($search_from_date, $search_to_date);

			} elseif(!empty($aqcms_from_date) && !empty($aqcms_to_date && !empty($aqcms_ro_office_short_code))) {

				$search_from_date = $aqcms_from_date;
				$search_to_date = $aqcms_to_date;
				$search_from_date = $this->Customfunctions->dateFormatCheck($search_from_date);
				$search_to_date = $this->Customfunctions->dateFormatCheck($search_to_date);
				$this->date_comparison($search_from_date, $search_to_date);

				$ro_office = $this->DmiRoOffices->find('all')->select(['id'])->where(['id IN' => $aqcms_ro_office_id])->where(['short_code IN'=>$aqcms_ro_office_short_code])->first();
				$application_approved_office = [$ro_office['id']];

			} elseif(!empty($aqcms_ro_office_short_code)) {

				$ro_office = $this->DmiRoOffices->find('all')->select(['id'])->where(['id IN' => $aqcms_ro_office_id])->where(['short_code IN'=>$aqcms_ro_office_short_code])->first();
				$application_approved_office = [$ro_office['id']];
			}//end


			$this->Session->write('search_application_type_id', $search_application_type_id);
			$this->Session->write('application_approved_office', $application_approved_office);
			$this->Session->write('search_from_date', $search_from_date);
			$this->Session->write('search_to_date', $search_to_date);

			$this->set('search_application_type_id', $search_application_type_id);
			$this->set('application_approved_office', $application_approved_office);
			$this->set('search_from_date', $search_from_date);
			$this->set('search_to_date', $search_to_date);

			$approved_application_lists = $this->approvedRenewalAppliSearchConditions($search_application_type_id, $application_approved_office, $search_from_date, $search_to_date, $table, $search_flag,$data_id,$approved_application_type);



			$approved_application_list = $approved_application_lists[0];

			$download_approved_application_list = $approved_application_lists[1];

			$i=0;
			foreach ($approved_application_list as $each) {

				$approved_application_list[$i] = $each['customer_id'];
				$i=$i+1;
			}

			$j=0;
			foreach ($download_approved_application_list as $each) {

				$download_approved_application_list[$j] = $each['customer_id'];
				$j=$j+1;
			}



			$this->loadModel('DmiRejectedApplLogs');
			$rejectedList = $this->DmiRejectedApplLogs->find('all')->select(['id','customer_id'])->order(['id','customer_id'])->combine('id','customer_id')->toArray();

			if(!empty($rejectedList)){

				if(!empty($approved_application_list)){
					$approved_application_list = array_diff($approved_application_list, $rejectedList);
				}
			}


			//Fetch the all data that required for creating the downloading report as execel (Done by pravin 14-03-2018)
			if ($download_report == 'yes') {

				$this->downloadApprovedApplicationReportResults($download_approved_application_list, $approved_application_type);
			}

			$this->approvedApplicationReportResults($approved_application_list, $approved_application_type);

		} else {

			$approved_application_lists = $this->approvedRenewalAppliSearchConditions($search_application_type_id, $application_approved_office, $search_from_date, $search_to_date, $table, $search_flag,$data_id,$approved_application_type);

			$approved_application_list = $approved_application_lists[0];

			$i=0;
			foreach ($approved_application_list as $each)
			{
				$approved_application_list[$i] = $each['customer_id'];
				$i=$i+1;
			}

			//if data same data id exist in rejcted table it is not apper in report added
			$this->loadModel('DmiRejectedApplLogs');
			$rejectedList = $this->DmiRejectedApplLogs->find('all')->select(['id','customer_id'])->order(['id','customer_id'])->combine('id','customer_id')->toArray();

			if(!empty($rejectedList))
			{
				if(!empty($approved_application_list)){
					$approved_application_list = array_diff($approved_application_list, $rejectedList);
				}
			}//end laxmi B.

			//bellow function is commented by shreeya already use this code [ Date 26-05-2023]
			//$this->approvedApplicationReportResults($approved_application_list,$approved_application_type);
		}

	}


	// approved Renewal Application Search Conditions
	// Description :
	// @Author : Yashwant
	// Date : 10-Mar-2023

	public function approvedRenewalAppliSearchConditions ($search_application_type_id, $application_approved_office, $search_from_date, $search_to_date, $table, $search_flag,$data_id,$approved_application_type)
	{

		$approved_application_list = [];

		if ($search_application_type_id != '' && $application_approved_office == '' && $search_from_date =='' && $search_to_date == '') {

			if ($table == 'DmiFinalSubmits') {
				$approved_application_customer_id = $this->DmiGrantCertificatesPdfs->find('all')->select(['customer_id'])->group(['customer_id HAVING COUNT(customer_id) < 2'])->toArray();
			} elseif ($table == 'DmiGrantCertificatesPdfs') {
				$approved_application_customer_id = $this->DmiGrantCertificatesPdfs->find('all')->select(['customer_id'])->group(['customer_id HAVING COUNT(customer_id) >= 1'])->toArray();
			} else {
				$approved_application_customer_id = $this->DmiGrantCertificatesPdfs->find('all')->select(['customer_id'])->group(['customer_id HAVING COUNT(customer_id) > 1'])->toArray();
			}

			$i=0;
			foreach ($approved_application_customer_id as $each_customer_id)
			{
				$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

				if (in_array($application_customer_type, $search_application_type_id))
				{
					$approved_application_list[$i] = $each_customer_id['customer_id'];
					$i=$i+1;
				}
			}

			if (!empty($approved_application_list)) {
				$conditions = ['customer_id IN' => $approved_application_list, 'status' => 'approved', 'current_level' => 'level_3'];
			} else {
				$conditions = ['customer_id IS' => '', 'status' => 'approved', 'current_level' => 'level_3'];
			}

			$approved_application_list = $this->$table->find('all')->where($conditions)->order(['id' => 'DESC'])->toArray();

			$download_approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();


		///===========================YAshwant 06-Apr-2023====================================
		} elseif ($search_application_type_id == '' && $application_approved_office == '' && $search_from_date !='' && $search_to_date != '')
		{

			$approved_application_customer_id = $this->DmiRenewalEsignedStatuses->find('all')->select(['customer_id'])->where(['application_status' =>"Granted"])->where(['date(modified) BETWEEN :start AND :end'])
			->bind(':start', $search_from_date, 'date')->bind(':end', $search_to_date, 'date')->order(['created'=>'DESC'])->toArray();

			$i=0;
			foreach ($approved_application_customer_id as $each_customer_id)
			{
				if (!empty($each_customer_id['customer_id']))
				{
					$application_customer_type = $this->Reportsfunctions->newApplicantType($each_customer_id['customer_id']);

					if(in_array($application_customer_type, $data_id, TRUE))
					{
						$approved_application_list[$i] = $each_customer_id['customer_id'];
						$i=$i+1;
					}
				}
			}

			if (!empty($approved_application_list)) {
				$conditions = ['customer_id IN' => $approved_application_list];
			} else {
				$conditions = ['customer_id IS' => '', 'status' => 'approved', 'current_level' => 'level_3'];
			}

			$approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();
			$download_approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();


		///==========YAshwant 06-Apr-2023 START Search Select Office Filter filter ==================
		} elseif ($search_application_type_id == '' && $application_approved_office != '' && $search_from_date =='' && $search_to_date == '' )
		{

			//$approved_application_customer_id = $this->$table->find('all')->select(['customer_id'])->where(['status' => 'approved', 'current_level' => 'level_3'])->toArray();

			$approved_application_customer_id = $this->DmiRenewalEsignedStatuses->find('all')->select(['customer_id'])->where(['application_status' =>"Granted"])->toArray();

			$i=0;
			foreach ($approved_application_customer_id as $each_customer_id)
			{
				if (!empty($each_customer_id['customer_id']))
				{
					$application_customer_type = $this->Reportsfunctions->newApplicantType($each_customer_id['customer_id']);

					if (in_array($application_customer_type,$data_id)) {

						$approved_application_details_list = $this->DmiGrantCertificatesPdfs->find('all')->select(['id', 'sid'])->where(['customer_id' => $each_customer_id['customer_id']])->combine('id', 'id')->toArray();

						if (!empty($approved_application_details_list)) {

							$approved_application_details = $this->DmiGrantCertificatesPdfs->find('all')->where(['id' => max($approved_application_details_list)])->first();
							$user_posted_office_id = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email' => $approved_application_details['user_email_id']])->first();

							if (!empty($user_posted_office_id)) {

								if (in_array($user_posted_office_id['posted_ro_office'],$application_approved_office)) {

									$approved_application_list[$i] = $each_customer_id['customer_id'];
									$i=$i+1;
								}
							}
						}
					}
				}
			}

			if (!empty($approved_application_list)) {
				$conditions = ['customer_id IN' => $approved_application_list];
			} else {
				$conditions = ['customer_id IS' => '', 'status' => 'approved', 'current_level' => 'level_3'];
			}

			$approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();
			$download_approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();


		/*Yashwant====10/Mar/2023 This below Condition Used For Renewal Appln(E-signed) count for GRanted=============*/
		} elseif (!empty($data_id))
		{
			//$approved_application_customer_id = $this->DmiApplicationEsignedStatuses->find('all')->select(['customer_id'])->where(['application_status' =>"Granted"])->toArray();
			$approved_application_customer_id = $this->DmiRenewalEsignedStatuses->find('all')->select(['customer_id'])->where(['application_status' =>"Granted"])->toArray();

			$i=0;
			foreach ($approved_application_customer_id as $each_customer_id)
			{
				if (!empty($each_customer_id['customer_id']))
				{
					$application_customer_type = $this->Reportsfunctions->newApplicantType($each_customer_id['customer_id']);
					if(in_array($application_customer_type, $data_id, TRUE))
					{
						$approved_application_list[$i] = $each_customer_id['customer_id'];
						$i=$i+1;
					}
				}
			}

			if (!empty($approved_application_list)) {
				$conditions = ['customer_id IN' => $approved_application_list];
			} else {
				$conditions = ['customer_id IS' => '', 'status' => 'approved', 'current_level' => 'level_3'];
			}

			$approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();
			$download_approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

		}elseif ($search_application_type_id != '' && $application_approved_office != '' && $search_from_date =='' && $search_to_date == '' )
		{
			$approved_application_customer_id = $this->$table->find('all')->select(['customer_id'])->where(['status' => 'approved', 'current_level' => 'level_3'])->toArray();

			$i=0;
			foreach ($approved_application_customer_id as $each_customer_id) {

				$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

				if (in_array($application_customer_type,$search_application_type_id))
				{
					$approved_application_details_list = $this->DmiGrantCertificatesPdfs->find('all')->select(['id', 'id'])->where(['customer_id' => $each_customer_id['customer_id']])->combine('id', 'id')->toArray();
					if (!empty($approved_application_details_list))
					{
						$approved_application_details = $this->DmiGrantCertificatesPdfs->find('all')->where(['id' => max($approved_application_details_list)])->first();
						$user_posted_office_id = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email' => $approved_application_details['user_email_id']])->first();
						if (!empty($user_posted_office_id))
						{
							if (in_array($user_posted_office_id['posted_ro_office'],$application_approved_office)) {
								$approved_application_list[$i] = $each_customer_id['customer_id'];
								$i=$i+1;
							}
						}
					}
				}
			}



			// below if-else code added by Ankur Jangid
			if (!empty($approved_application_list)) {
				$conditions = ['customer_id IN' => $approved_application_list, 'status' => 'approved', 'current_level' => 'level_3'];
			} else {
				$conditions = ['customer_id IS' => '', 'status' => 'approved', 'current_level' => 'level_3'];
			}

			$approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

			$download_approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();


		}
		elseif ($search_application_type_id != '' && $application_approved_office == '' && $search_from_date !='' && $search_to_date != '')
		{

			$approved_application_customer_id = $this->$table->find('all')->select(['customer_id'])->where(['date(modified) BETWEEN :start AND :end'])
			->where(['status' => 'approved', 'current_level' => 'level_3'])->bind(':start', $search_from_date, 'date')->bind(':end', $search_to_date, 'date')->toArray();

			$i=0;
			foreach ($approved_application_customer_id as $each_customer_id) {
				$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

				if (in_array($application_customer_type, $search_application_type_id)) {
					$approved_application_list[$i] = $each_customer_id['customer_id'];
					$i=$i+1;
				}
			}

			// below if-else code added by Ankur Jangid
			if (!empty($approved_application_list)) {
				$conditions = ['customer_id IN' => $approved_application_list, 'status' => 'approved', 'current_level' => 'level_3'];
			} else {
				$conditions = ['customer_id IS' => '', 'status' => 'approved', 'current_level' => 'level_3'];
			}

			$approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

			$download_approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

		} elseif ($search_application_type_id != '' && $application_approved_office != '' && $search_from_date !='' && $search_to_date != '')
		{

			$approved_application_customer_id = $this->$table->find('all')->select(['customer_id'])->where(['date(modified) BETWEEN :start AND :end'])
			->where(['status' => 'approved', 'current_level' => 'level_3'])->bind(':start', $search_from_date, 'date')->bind(':end', $search_to_date, 'date')->toArray();

			$i=0;
			foreach ($approved_application_customer_id as $each_customer_id) {

				$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

				if (in_array($application_customer_type,$search_application_type_id)) {

					$approved_application_details = $this->DmiGrantCertificatesPdfs->find('all')->where(['customer_id' => $each_customer_id['customer_id']])->first();

					$user_posted_office_id = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email IS' => $approved_application_details['user_email_id']])->first();

					if (!empty($user_posted_office_id)) {

						if (in_array($user_posted_office_id['posted_ro_office'], $application_approved_office)) {
							$approved_application_list[$i] = $each_customer_id['customer_id'];
							$i=$i+1;
						}
					}
				}
			}

			// below if-else code added by Ankur Jangid
			if (!empty($approved_application_list)) {
				$conditions = ['customer_id IN' => $approved_application_list, 'status' => 'approved', 'current_level' => 'level_3'];
			} else {
				$conditions = ['customer_id IS' => '', 'status' => 'approved', 'current_level' => 'level_3'];
			}

			$approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

			$download_approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

		} elseif ($search_application_type_id == '' && $application_approved_office != '' && $search_from_date =='' && $search_to_date == '')
		{

			$approved_application_customer_id = $this->$table->find('all')->select(['customer_id'])->where(['status' => 'approved', 'current_level' => 'level_3'])->toArray();

			$i=0;
			foreach ($approved_application_customer_id as $each_customer_id) {

				$approved_application_details_list = $this->DmiGrantCertificatesPdfs->find('all')->select(['id', 'id'])->where(['customer_id IS' => $each_customer_id['customer_id']])->combine('id', 'id')->toArray();

				if (!empty($approved_application_details_list)) {

					$approved_application_details = $this->DmiGrantCertificatesPdfs->find('all')->where(['id' => max($approved_application_details_list)])->first();

					$user_posted_office_id = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email' => $approved_application_details['user_email_id']])->first();

					if (!empty($user_posted_office_id)) {

						if (in_array($user_posted_office_id['posted_ro_office'],$application_approved_office)) {
							$approved_application_list[$i] = $each_customer_id['customer_id'];
							$i=$i+1;
						}
					}
				}
			}

			// below if-else code added by Ankur Jangid
			if (!empty($approved_application_list)) {
				$conditions = ['customer_id IN' => $approved_application_list, 'status' => 'approved', 'current_level' => 'level_3'];
			} else {
				$conditions = ['customer_id IS' => '', 'status' => 'approved', 'current_level' => 'level_3'];
			}

			$approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

			$download_approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

		} elseif ($search_application_type_id == '' && $application_approved_office != '' && $search_from_date !='' && $search_to_date != '')
		{

			$approved_application_customer_id = $this->$table->find('all')->select(['customer_id'])->where(['date(modified) BETWEEN :start AND :end', 'status' => 'approved',
				'current_level' => 'level_3'])->bind(':start', $search_from_date, 'date')->bind(':end', $search_to_date, 'date')->toArray();

			$i=0;
			foreach ($approved_application_customer_id as $each_customer_id) {

				$approved_application_details_list = $this->DmiGrantCertificatesPdfs->find('all')->select(['id', 'id'])->where(['customer_id' => $each_customer_id['customer_id']])->combine('id', 'id')->toArray();

				if (!empty($approved_application_details_list)) {

					$approved_application_details = $this->DmiGrantCertificatesPdfs->find('all')->where(['id' => max($approved_application_details_list)])->first();

					$user_posted_office_id = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email' => $approved_application_details['user_email_id']])->first();

					if (!empty($user_posted_office_id)) {

						if (in_array($user_posted_office_id['posted_ro_office'], $application_approved_office)) {
							$approved_application_list[$i] = $each_customer_id['customer_id'];
							$i=$i+1;
						}
					}
				}
			}

			// below if-else code added by Ankur Jangid
			if (!empty($approved_application_list)) {
				$conditions = ['customer_id IN' => $approved_application_list, 'status' => 'approved', 'current_level' => 'level_3'];
			} else {
				$conditions = ['customer_id IS' => '', 'status' => 'approved', 'current_level' => 'level_3'];
			}

			$approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

			$download_approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

		} elseif ($search_application_type_id == '' && $application_approved_office == '' && $search_from_date !='' && $search_to_date != '')
		{

			$approved_application_customer_id = $this->$table->find('all')->select(['customer_id'])->where(['date(modified) BETWEEN : startAND :end', 'status' => 'approved',
				'current_level' => 'level_3'])->bind(':start', $search_from_date, 'date')->bind(':end', $search_to_date, 'date')->toArray();

			$i=0;
			foreach ($approved_application_customer_id as $each_customer_id) {
				$approved_application_list[$i] = $each_customer_id['customer_id'];
				$i=$i+1;
			}

			// below if-else code added by Ankur Jangid
			if (!empty($approved_application_list))
			{
				$conditions = ['customer_id IN' => $approved_application_list, 'status' => 'approved', 'current_level' => 'level_3'];
			} else {
				$conditions = ['customer_id IS' => '', 'status' => 'approved', 'current_level' => 'level_3'];
			}

			$approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

			$download_approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

		} else {

			if ($search_flag == 'on') {
				$approved_application_customer_id = $this->$table->find('all')->select(['customer_id'])->where(['status' => 'approved', 'current_level' => 'level_3'])
				->order(['created'=>'DESC'])->extract('customer_id')->toArray(0);
			} else {

				// THIS BELOW CONDITION IS ADDED FOR THE ALL REPORTS BY AKASH ON 16-06-2022
				if ($table == 'DmiGrantCertificatesPdfs') {

					$posted_ro_office = $this->DmiUsers->find('all',array('fields'=>'posted_ro_office', 'conditions'=>array('email IS'=>$_SESSION['username'])))->first();
					$get_short_code = $this->DmiRoOffices->find('all',array('fields'=>'short_code', 'conditions'=>array('id IS'=>$posted_ro_office['posted_ro_office'])))->first();
					$short_code = $get_short_code['short_code'];

					if ($_SESSION['role'] == 'Head Office') {
						$approved_application_customer_id = $this->DmiGrantCertificatesPdfs->find('all')->select(['customer_id'])->group(['customer_id HAVING COUNT(customer_id) >= 1'])->limit(['2'])->extract('customer_id')->toArray(0);
					} else {
						$approved_application_customer_id = $this->DmiGrantCertificatesPdfs->find('all',array('fields'=>'customer_id','conditions'=>array('customer_id like'=>'%/'.$short_code.'/%'),'group'=>'customer_id having count(customer_id) >= 1','having'=>array('count(customer_id) >= 1')))->toArray();
					}

				} else {
					$approved_application_customer_id = $this->$table->find('all')->select(['customer_id'])->where(['status' => 'approved', 'current_level' => 'level_3'])
					->order(['created'=>'DESC'])->limit(['100'])->extract('customer_id')->toArray(0);
				}
			}

			$approved_application_list = $approved_application_customer_id;

			// below if-else code added by Ankur Jangid
			if (!empty($approved_application_list)) {

				// THIS BELOW CONDITION IS ADDED FOR THE ALL REPORTS BY AKASH ON 16-06-2022
				if ($table == 'DmiGrantCertificatesPdfs') {
					$conditions = array('customer_id IN'=>$approved_application_list);
				} else {
					$conditions = ['customer_id IN' => $approved_application_list, 'status' => 'approved', 'current_level' => 'level_3'];
				}

			} else {
				$conditions = ['customer_id IS' => '', 'status' => 'approved', 'current_level' => 'level_3'];
			}

			$approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();
			$download_approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();
		}


		return array($approved_application_list,$download_approved_application_list);

	}




	// Granted Backlog Applications Report
	// Description :  Function Used For Backlog Appln(E-signed) count for GRanted
	// @Author : Yashwant & shreeya
	// Date : 10-Mar-2023

	public function grantedBacklogApplicationsReport($cert_type) //$backlog_id ->replace by cert_type by shreeya
	{
		// $backlog_id replace $cert_type by Shreeya date [26-05-2023]
		$cert_type=base64_decode($cert_type);
		$data_id =array($cert_type);

		$aqcms_from_date ='' ;
		$aqcms_to_date = '';
		$aqcms_ro_office_short_code = '';
		$aqcms_ro_office_id = '';

		//$approved_application_type = $this->Session->read('approved_application_type');
		$table = 'DmiFinalSubmits';
		$approved_application_type = 'backlog';
		$report_heading = 'Approved Backlog Applications Report';


		if ($approved_application_type == 'backlog' || $approved_application_type =='') {
			$table = 'DmiFinalSubmits';
			$report_heading = 'Approved Backlog Applications Report';
		} elseif ($approved_application_type == 'all_reports') {

			$table = 'DmiGrantCertificatesPdfs';
			$report_heading = 'All Approved Report';

			// this below code is added to show the deafult office by Akash on 16-06-2022
			$posted_ro_office = $this->DmiUsers->find('all',array('fields'=>'posted_ro_office', 'conditions'=>array('email IS'=>$_SESSION['username'])))->first();
			$default_ro_office = $this->DmiRoOffices->find('all',array('conditions'=>array('id IS'=>$posted_ro_office['posted_ro_office'])))->first();
			$this->set('default_ro_office',$default_ro_office['ro_office']);
		}

			//************************New Code Added for show count list by Shreeya Date [25-05-2023]**************************************************************************

		//check the which certificate type is present by shreeya on date [26-05-2023]
		if ($cert_type== 'CA')
		{
			$cert_type = 1;
		}
		elseif ($cert_type== 'PP')
		{
			$cert_type = 2;
		}
		elseif ($cert_type== 'LAB')
		{
			$cert_type = 3;
		}


		//check the which application type is present by shreeya on date [26-05-2023]
		if($approved_application_type=='new'){
			$processFunction = 'new_app_processed';
		}elseif($approved_application_type=='renewal'){
			$processFunction = 'renewal_app_processed';
		}elseif($approved_application_type=='backlog'){
			$processFunction = 'backlog_app_processed';
		}


		//show the count according to application type  and cutomer_id by shreeya on date [26-05-2023]
		$searchConditions = array();
		$application_processed[] = $this->Reportstatistics->$processFunction($searchConditions,null,null,$cert_type,$approved_application_type);
		$applListToShow = $application_processed[0][3];


		$date=array();
		$application_type=array();
		$application_user_email_id=array();
		$user_office=array();
		$application_customer_id=array();
		$name_of_the_firm=array();
		$address_of_the_firm=array();
		$contact_details_of_the_firm=array();
		$approved_TBL_details_tbl_name=array();
		$approved_TBL_details_tbl_registered_no=array();
		$laboratory_details_name=array();
		$laboratory_details_address=array();


		$i=0;
			//applied array_unique function on 18-07-2019
			foreach (array_unique($applListToShow) as $approved_application) {

				$approved_application_details = array(); //this line added on 18-07-2019

				// THIS BELOW CONDITION IS ADDED FOR THE ALL REPORTS BY AKASH ON 16-06-2022
				if ($approved_application_type == 'all_reports') {
					$approved_application_details = $this->DmiGrantCertificatesPdfs->find('all',array('conditions'=>array('customer_id IS'=>$approved_application),'order' => array('id' => 'desc')))->first();
				} elseif ($approved_application_type == 'new' || $approved_application_type =='') {
					$approved_application_details = $this->DmiGrantCertificatesPdfs->find('all')->where(['customer_id' => $approved_application])->first();
				} elseif ($approved_application_type == 'backlog') {
					$approved_application_details = $this->DmiFinalSubmits->find('all')->where(['customer_id' => $approved_application,'status'=>'approved','current_level'=>'level_3'])->first();
				} elseif ($approved_application_type == 'renewal') {
					$approved_application_detail = $this->DmiGrantCertificatesPdfs->find('all')->select(['id'])->where(['customer_id IS'=>$approved_application])->combine('id','id')->toArray();
					//applied this condition on 27-04-2019
					if (!empty($approved_application_detail)) {
						$approved_application_details = $this->DmiGrantCertificatesPdfs->find('all')->where(['id IN'=> $approved_application_detail])->order(['id' => 'DESC'])->first();
					}
				}

				//this condition added on 18-07-2019
				if (!empty($approved_application_details)) {

					$approved_application_result = $approved_application_details;

					//to check if the application is old or not to print on the excel and for viewing part dont by Akash 07-04-2022

					// THIS BELOW CONDITION IS ADDED FOR THE ALL REPORTS BY AKASH ON 16-06-2022
					if ($approved_application_type == 'all_reports') {

						if ($approved_application_result['pdf_version'] > '1') {
							$approved_application_type_text[$i] = "<b>RENEWAL</b>";
						} elseif ($approved_application_result['user_email_id'] == 'old_application') {
							$approved_application_type_text[$i] = "<i>OLD</i>";
						} else {
							$approved_application_type_text[$i] = "NEW";
						}

					} else {

						if ($approved_application_type == 'renewal') {
							$approved_application_type_text[$i] = "<b>RENEWAL</b>";
						} elseif ($approved_application_result['user_email_id'] == 'old_application') {
							$approved_application_type_text[$i] = "<i>OLD</i>";
						} else {
							$approved_application_type_text[$i] = "NEW";
						}

					}


					if ($approved_application_result['user_email_id'] == 'old_application') {
						$old_app_approved_by = $this->Customfunctions->old_app_approved_by($approved_application_result['customer_id']);
						$approved_application_result['user_email_id'] = $old_app_approved_by;
					}

					$explode = explode("/",$approved_application_result['customer_id']);

					$approved_office_id = $this->DmiRoOffices->find('all')->select(['ro_email_id'])->where(['short_code' => $explode[2]])->first();

					$user_posted_office_id = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email'=>$approved_office_id['ro_email_id']])->first();

					if (!empty($user_posted_office_id)) {

						$user_office_details = $this->DmiRoOffices->find('all')->select(['ro_office'])->where(['id'=>$user_posted_office_id['posted_ro_office']])->first();

						if (!empty($user_office_details)) {
							$user_office[$i] = $user_office_details['ro_office'];
						} else {
							$user_office[$i] = 'N/A';
						}
					} else {
						$user_office[$i] = 'N/A';
					}

					$application_form_type = $this->Customfunctions->checkApplicantFormType($approved_application_result['customer_id']);

					if ($application_form_type == 'A') {
						$application_type[$i]='CA (Form-A)';
					} elseif ($application_form_type == 'B') {
						$application_type[$i]='Printing Press (Form-B)';
					} elseif ($application_form_type == 'C') {
						$application_type[$i]='Laboratory (Form-C)';
					} elseif ($application_form_type == 'D') {
						$application_type[$i]='Laboratory (Form-D)';
					} elseif ($application_form_type == 'E') {
						$application_type[$i]='CA (Form-E)';
					} elseif ($application_form_type == 'F') {
						$application_type[$i]='CA (Form-F)';
					}

					$date[$i] = $approved_application_result['created'];
					$application_customer_id[$i] = $approved_application_result['customer_id'];

					//added by the akash on 13-11-2021
					$firmDetails = $this->DmiFirms->find('all',array('conditions'=>array('customer_id IS'=>$approved_application_result['customer_id'])))->first();

					$name_of_the_firm[$i] = $firmDetails['firm_name'];
					$address_of_the_firm[$i] = $firmDetails['street_address'];
					$contact_details_of_the_firm[$i] = base64_decode($firmDetails['email']);
					$phoneno[$i] = $firmDetails['mobile_no'];

					//tbl details
					$tbl_details = $this->DmiAllTblsDetails->find('all',array('conditions'=>array('customer_id IS'=>$approved_application_result['customer_id'],'OR' => array('delete_status IS NULL', 'delete_status' => 'no'))))->toArray();

					if (!empty($tbl_details)) {
						$j=0;
						foreach ($tbl_details as $each) {

							$approved_TBL_details_tbl_name[$i][$j] = $each['tbl_name'];
							$approved_TBL_details_tbl_registered_no[$i][$j] = $each['tbl_registered_no'];
							$j++;
						}

					} else {
						$approved_TBL_details_tbl_name[$i][0] = 'N/A';
						$approved_TBL_details_tbl_registered_no[$i][0] = 'N/A';
					}



					//lab details
					$lab_details = $this->DmiCustomerLaboratoryDetails->find('all',array('conditions'=>array('customer_id IS'=>$approved_application_result['customer_id'])))->toArray();
					if (!empty($lab_details)) {
						$laboratory_details_name[$i] = $lab_details[0]['laboratory_name'];
						$laboratory_details_address[$i] = $lab_details[0]['street_address'];
					} else {
						$laboratory_details_name[$i] = 'N/A';
						$laboratory_details_address[$i] = 'N/A';
					}

					$commodity_value = $this->DmiFirms->find('all')->select(['sub_commodity'])->where(['customer_id'=>$approved_application_result['customer_id']])->first();

					$commodity_list[$i] = $this->Customfunctions->showCommdityInApplList($commodity_value['sub_commodity']);


					//Added this Code on the 08-04-2022 as the base encoding was not on some emails so to dispaly the email , checking if the encoding is needed or not.
					$checkEmailForEncoding[$i] = $approved_application_result['user_email_id'];

					if($this->isBase64Encoded($checkEmailForEncoding[$i]) == true){
						$application_user_email_id[$i] = base64_decode($approved_application_result['user_email_id']);
					} else {
						$application_user_email_id[$i] = $approved_application_result['user_email_id'];
					}

					//check the expiry dateand print to the reports  added by Akash on 24-05-2022
					$grant_date = chop($approved_application_details['date'],"00:00:00");
					$valid_upto[$i] = $this->Customfunctions->getCertificateValidUptoDate($approved_application_result['customer_id'],$grant_date);

					//check the state name added by akash on 14-06-2022
					$state_name[$i] = $this->getStateName($approved_application_result['customer_id']);

					//Certificate Issued on
					$issued_on[$i] = chop($approved_application_result['date'],"00:00:00");

					$i=$i+1;
				}
			}



		$this->set('date',$date);
		$this->set('application_customer_id',$application_customer_id);
		$this->set('application_user_email_id',$application_user_email_id);
		$this->set('application_type',$application_type);
		$this->set('user_office',$user_office);
		//$this->set('approved_application_list',$approved_application_list);
		$this->set('commodity_list',$commodity_list);
		$this->set('approved_application_type',$approved_application_type_text);
		$this->set('name_of_the_firm',$name_of_the_firm);
		$this->set('address_of_the_firm',$address_of_the_firm);
		$this->set('contact_details_of_the_firm',$contact_details_of_the_firm);
		$this->set('approved_TBL_details_tbl_name',$approved_TBL_details_tbl_name);
		$this->set('approved_TBL_details_tbl_registered_no',$approved_TBL_details_tbl_registered_no);
		$this->set('laboratory_details_name',$laboratory_details_name);
		$this->set('laboratory_details_address',$laboratory_details_address);
		$this->set('valid_upto',$valid_upto);
		$this->set('state_name',$state_name);
		$this->set('phoneno',$phoneno);
		$this->set('issued_on',$issued_on);


		//*****************End************************************

		$this->set('table', $table);	// set table value ( Done by pravin 16-07-2018)
		$this->set('report_heading', $report_heading);


		$application_type_xy = array('A'=>'CA (Form-A)','C'=>'Laboratory (Form-C)','E'=>'CA (Form-E)','B'=>'Printing Press (Form-B)','D'=>'Laboratory (Form-D)','F'=>'CA (Form-F)');
		//Change on 9/11/2018, Sorting array by ascending order - By Pravin Bhakare
		asort($application_type_xy);
		$this->set('application_type_xy',$application_type_xy);
		// $backlogId replace $cert_type by Shreeya date [26-05-2023]
		$this->set('cert_type',$cert_type);

		// $backlogId replace $cert_type by Shreeya date [26-05-2023]
		if($cert_type=='CA'){
			$application_type_xy = array('A'=>'CA (Form-A)','E'=>'CA (Form-E)','F'=>'CA (Form-F)');
		}elseif($cert_type=='PP') {
			$application_type_xy = array('B'=>'Printing Press (Form-B)');
		}elseif ($cert_type=='LAB') {
			$application_type_xy = array('C'=>'Laboratory (Form-C)','D'=>'Laboratory (Form-D)');
		}



		//added 'office_type'=>'RO' condition on 27-07-2018   // Change on 3/11/2018 -  add order by condition - by Pravin Bhakare
		$ro_office = $this->DmiRoOffices->find('all')->select(['id', 'ro_office'])->where(['office_type' => 'RO','delete_status IS NULL'])->order(['ro_office' => 'ASC'])->combine('id', 'ro_office')->toArray();
		$this->set('ro_office',$ro_office);

		$search_application_type_id = $this->Session->read('search_application_type_id');
		$application_approved_office = $this->Session->read('application_approved_office');
		$search_from_date = $this->Session->read('search_from_date');
		$search_to_date = $this->Session->read('search_to_date');

		$this->set('search_application_type_id',$search_application_type_id);
		$this->set('application_approved_office',$application_approved_office);
		$this->set('search_from_date',$search_from_date);
		$this->set('search_to_date',$search_to_date);

		$search_flag = 'off'; // added by Ankur
		// Set default value for download report click event (Done by pravin 13-03-2018)
		$download_report = 'no';

		//if from to date and office id in  session condtion added by laxmi B on 15-02-2023

		$aqcms_from_date = $this->Session->read('from_date') ;
		$aqcms_to_date = $this->Session->read('to_date');
		$aqcms_ro_office_short_code = $this->Session->read('roOfficeShortCode');
		$aqcms_ro_office_id = $this->Session->read('ro_office_id');
		$this->Session->delete('ro_office_id');
		$this->Session->delete('roOfficeShortCode');
		$this->Session->delete('from_date');
		$this->Session->delete('to_date');



		if ((((!empty($aqcms_from_date && !empty($aqcms_to_date))) || !empty($aqcms_ro_office_short_code))) || null != ($this->request->getData('search_logs')) || null != ($this->request->getData('download_report'))) {
			$search_flag = 'on'; // added by Ankur
			//Check not empty "Download Report as Excel" button Request, if condition TRUE then set value "yes" for "Download Report as Excel" click event
			//and pass this value to "approved_application_search_conditions" function (Done by pravin 13-03-2018)
			if (!empty($this->request->getData('download_report'))) {
				$download_report = 'yes';
			}

			$search_application_type_id = $this->request->getData('application_type');
			$application_approved_office = $this->request->getData('office');
			$search_from_date =  $this->request->getData('from_date');
			$search_from_date = $this->Customfunctions->dateFormatCheck($search_from_date);
			$search_to_date =  $this->request->getData('to_date');
			$search_to_date = $this->Customfunctions->dateFormatCheck($search_to_date);
			$this->date_comparison($search_from_date, $search_to_date);


			if ($download_report == 'yes') {

				$search_application_type_id = $this->Session->read('search_application_type_id');
				$application_approved_office = $this->Session->read('application_approved_office');
				$search_from_date = $this->Session->read('search_from_date');
				$search_to_date = $this->Session->read('search_to_date');
			}

			$this->Session->delete('search_application_type_id');
			$this->Session->delete('application_approved_office');
			$this->Session->delete('search_from_date');
			$this->Session->delete('search_to_date');



			//set from_date and to_date and office name from session of statstics report added by laxmi B. on 15-02-2023
			if(!empty($aqcms_from_date) && !empty($aqcms_to_date && empty($aqcms_ro_office_short_code) )){

				$search_from_date = $aqcms_from_date;
				$search_to_date = $aqcms_to_date;
				$search_from_date = $this->Customfunctions->dateFormatCheck($search_from_date);
				$search_to_date = $this->Customfunctions->dateFormatCheck($search_to_date);
				$this->date_comparison($search_from_date, $search_to_date);

			} elseif(!empty($aqcms_from_date) && !empty($aqcms_to_date && !empty($aqcms_ro_office_short_code))) {

				$search_from_date = $aqcms_from_date;
				$search_to_date = $aqcms_to_date;
				$search_from_date = $this->Customfunctions->dateFormatCheck($search_from_date);
				$search_to_date = $this->Customfunctions->dateFormatCheck($search_to_date);
				$this->date_comparison($search_from_date, $search_to_date);

				$ro_office = $this->DmiRoOffices->find('all')->select(['id'])->where(['id IN' => $aqcms_ro_office_id])->where(['short_code IN'=>$aqcms_ro_office_short_code])->first();
				$application_approved_office = [$ro_office['id']];


			} elseif(!empty($aqcms_ro_office_short_code)) {

				$ro_office = $this->DmiRoOffices->find('all')->select(['id'])->where(['id IN' => $aqcms_ro_office_id])->where(['short_code IN'=>$aqcms_ro_office_short_code])->first();
				$application_approved_office = [$ro_office['id']];
			}//end


			$this->Session->write('search_application_type_id', $search_application_type_id);
			$this->Session->write('application_approved_office', $application_approved_office);
			$this->Session->write('search_from_date', $search_from_date);
			$this->Session->write('search_to_date', $search_to_date);

			$this->set('search_application_type_id', $search_application_type_id);
			$this->set('application_approved_office', $application_approved_office);
			$this->set('search_from_date', $search_from_date);
			$this->set('search_to_date', $search_to_date);

			$approved_application_lists = $this->grantBcklogSearchConditions($search_application_type_id, $application_approved_office, $search_from_date, $search_to_date, $table, $search_flag,$data_id,$approved_application_type);
			$approved_application_list = $approved_application_lists[0];
			$download_approved_application_list = $approved_application_lists[1];

			$i=0;
			foreach ($approved_application_list as $each) {

				$approved_application_list[$i] = $each['customer_id'];
				$i=$i+1;
			}

			$j=0;
			foreach ($download_approved_application_list as $each) {

				$download_approved_application_list[$j] = $each['customer_id'];
				$j=$j+1;
			}



			$this->loadModel('DmiRejectedApplLogs');
			$rejectedList = $this->DmiRejectedApplLogs->find('all')->select(['id','customer_id'])->order(['id','customer_id'])->combine('id','customer_id')->toArray();

			if(!empty($rejectedList)){

				if(!empty($approved_application_list)){
					$approved_application_list = array_diff($approved_application_list, $rejectedList);
				}
			}


			//Fetch the all data that required for creating the downloading report as execel (Done by pravin 14-03-2018)
			if ($download_report == 'yes') {

				$this->downloadGrantBacklogApplicationReportResults($download_approved_application_list, $approved_application_type);
			}

			$this->approvedApplicationReportResults($approved_application_list, $approved_application_type);

		} else {

			$approved_application_lists = $this->grantBcklogSearchConditions($search_application_type_id, $application_approved_office, $search_from_date, $search_to_date, $table, $search_flag,$data_id,$approved_application_type);

			$approved_application_list = $approved_application_lists[0];

			$i=0;
			foreach ($approved_application_list as $each)
			{
				$approved_application_list[$i] = $each['customer_id'];
				$i=$i+1;
			}

			//if data same data id exist in rejcted table it is not apper in report added
			$this->loadModel('DmiRejectedApplLogs');
			$rejectedList = $this->DmiRejectedApplLogs->find('all')->select(['id','customer_id'])->order(['id','customer_id'])->combine('id','customer_id')->toArray();

			if(!empty($rejectedList)){

				if(!empty($approved_application_list)){
					$approved_application_list = array_diff($approved_application_list, $rejectedList);
				}
			}//end laxmi B.

			//below code is already used commented by shreeya [Date - 26-05-2023]
			//$this->approvedApplicationReportResults($approved_application_list,$approved_application_type);
		}

	}

	// Grant Bcklog Search Conditions
	// Description :  Function Used For Backlog Appln(E-signed) count for GRanted
	// @Author : Yashwant
	// Date : 10-Mar-2023


	public function grantBcklogSearchConditions ($search_application_type_id, $application_approved_office, $search_from_date, $search_to_date, $table, $search_flag,$data_id,$approved_application_type)
	{

		$approved_application_list = [];

		if ($search_application_type_id != '' && $application_approved_office == '' && $search_from_date =='' && $search_to_date == '') {

			if ($table == 'DmiFinalSubmits') {
				$approved_application_customer_id = $this->DmiGrantCertificatesPdfs->find('all')->select(['customer_id'])->group(['customer_id HAVING COUNT(customer_id) < 2'])->toArray();

			} elseif ($table == 'DmiGrantCertificatesPdfs') {
				$approved_application_customer_id = $this->DmiGrantCertificatesPdfs->find('all')->select(['customer_id'])->group(['customer_id HAVING COUNT(customer_id) >= 1'])->toArray();
			} else {
				$approved_application_customer_id = $this->DmiGrantCertificatesPdfs->find('all')->select(['customer_id'])->group(['customer_id HAVING COUNT(customer_id) > 1'])->toArray();
			}

			$i=0;
			foreach ($approved_application_customer_id as $each_customer_id)
			{
				$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

				if (in_array($application_customer_type, $search_application_type_id))
				{
					$approved_application_list[$i] = $each_customer_id['customer_id'];
					$i=$i+1;
				}
			}

			if (!empty($approved_application_list)) {
				$conditions = ['customer_id IN' => $approved_application_list, 'status' => 'approved', 'current_level' => 'level_3'];
			} else {
				$conditions = ['customer_id IS' => '', 'status' => 'approved', 'current_level' => 'level_3'];
			}

			$approved_application_list = $this->$table->find('all')->where($conditions)->order(['id' => 'DESC'])->toArray();

			$download_approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();


		//===========================YAshwant 06-Apr-2023====================================
		} elseif ($search_application_type_id == '' && $application_approved_office == '' && $search_from_date !='' && $search_to_date != '')
		{

			$DmiFirms = TableRegistry::getTableLocator()->get('DmiFirms');

			$firm_result = $DmiFirms->find('all')->where(['is_already_granted IS' => 'yes'])->combine('id', 'customer_id')->toArray();

			$DmiFinalSubmits = TableRegistry::getTableLocator()->get('DmiFinalSubmits');


			$approved_application_customer_id = $DmiFinalSubmits->find('all')->select(['customer_id'])->distinct(['customer_id'])->where(['customer_id IN' => $firm_result,'current_level' => 'level_3', 'status' => 'approved'])
			->where(['date(modified) BETWEEN :start AND :end'])
			->bind(':start', $search_from_date, 'date')->bind(':end', $search_to_date, 'date')->toArray();

			$i=0;
			foreach ($approved_application_customer_id as $each_customer_id)
			{
				if (!empty($each_customer_id['customer_id']))
				{
					$application_customer_type = $this->Reportsfunctions->newApplicantType($each_customer_id['customer_id']);
					if(in_array($application_customer_type, $data_id, TRUE))
					{
						$approved_application_list[$i] = $each_customer_id['customer_id'];
						$i=$i+1;
					}
				}
			}

			if (!empty($approved_application_list)) {
				$conditions = ['customer_id IN' => $approved_application_list];
			} else {
				$conditions = ['customer_id IS' => '', 'status' => 'approved', 'current_level' => 'level_3'];
			}

			$approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

			$download_approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();


		///==========YAshwant 06-Apr-2023 START Search Select Office Filter filter ==================
		} elseif ($search_application_type_id == '' && $application_approved_office != '' && $search_from_date =='' && $search_to_date == '' )
		{
			$DmiFirms = TableRegistry::getTableLocator()->get('DmiFirms');
			$firm_result = $DmiFirms->find('all')->where(['is_already_granted IS' => 'yes'])->combine('id', 'customer_id')->toArray();
			$DmiFinalSubmits = TableRegistry::getTableLocator()->get('DmiFinalSubmits');
			$approved_application_customer_id = $DmiFinalSubmits->find('all')->select(['customer_id'])->distinct(['customer_id'])->where(['customer_id IN' => $firm_result,'current_level' => 'level_3', 'status' => 'approved'])->toArray();

			$i=0;
			foreach ($approved_application_customer_id as $each_customer_id)
			{
				if (!empty($each_customer_id['customer_id']))
				{
					$application_customer_type = $this->Reportsfunctions->newApplicantType($each_customer_id['customer_id']);

					if(in_array($application_customer_type, $data_id, TRUE))
					{
						$approved_application_details_list = $this->DmiGrantCertificatesPdfs->find('all')->select(['id', 'id'])->where(['customer_id' => $each_customer_id['customer_id']])->combine('id', 'id')->toArray();

						if (!empty($approved_application_details_list))
						{
							$approved_application_details = $this->DmiGrantCertificatesPdfs->find('all')->where(['id' => max($approved_application_details_list)])->first();
							$user_posted_office_id = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email' => $approved_application_details['user_email_id']])->first();

							if (!empty($user_posted_office_id))
							{
								if (in_array($user_posted_office_id['posted_ro_office'],$application_approved_office)) {

									$approved_application_list[$i] = $each_customer_id['customer_id'];
									$i=$i+1;
								}
							}
						}
					}
				}
			}

			if (!empty($approved_application_list))
			{
				$conditions = ['customer_id IN' => $approved_application_list];
			} else {
				$conditions = ['customer_id IS' => '', 'status' => 'approved', 'current_level' => 'level_3'];
			}

			$approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

			$download_approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();


		/*Yashwant====10/Mar/2023 This below Condition Used For BAcklog Appln(E-signed) count for GRanted=============*/
		} elseif (!empty($data_id))
		{
			$DmiFirms = TableRegistry::getTableLocator()->get('DmiFirms');
			$firm_result = $DmiFirms->find('all')->where(['is_already_granted IS' => 'yes'])->combine('id', 'customer_id')->toArray();
			$DmiFinalSubmits = TableRegistry::getTableLocator()->get('DmiFinalSubmits');
			$approved_application_customer_id = $DmiFinalSubmits->find('all')->select(['customer_id'])->distinct(['customer_id'])->where(['customer_id IN' => $firm_result,'current_level' => 'level_3', 'status' => 'approved'])->toArray();

			$i=0;
			foreach ($approved_application_customer_id as $each_customer_id)
			{
				if (!empty($each_customer_id['customer_id']))
				{
					$application_customer_type = $this->Reportsfunctions->newApplicantType($each_customer_id['customer_id']);
					if(in_array($application_customer_type, $data_id, TRUE))
					{
						$approved_application_list[$i] = $each_customer_id['customer_id'];
						$i=$i+1;
					}
				}
			}

			if (!empty($approved_application_list)) {
				$conditions = ['customer_id IN' => $approved_application_list];
			} else {
				$conditions = ['customer_id IS' => '', 'status' => 'approved', 'current_level' => 'level_3'];
			}

			$approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

			$download_approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

		} elseif ($search_application_type_id != '' && $application_approved_office != '' && $search_from_date =='' && $search_to_date == '' )
		{

			$approved_application_customer_id = $this->$table->find('all')->select(['customer_id'])->where(['status' => 'approved', 'current_level' => 'level_3'])->toArray();

			$i=0;
			foreach ($approved_application_customer_id as $each_customer_id) {

				$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

				if (in_array($application_customer_type,$search_application_type_id))
				{

					$approved_application_details_list = $this->DmiGrantCertificatesPdfs->find('all')->select(['id', 'id'])->where(['customer_id' => $each_customer_id['customer_id']])->combine('id', 'id')->toArray();

					if (!empty($approved_application_details_list))
					{

						$approved_application_details = $this->DmiGrantCertificatesPdfs->find('all')->where(['id' => max($approved_application_details_list)])->first();

						$user_posted_office_id = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email' => $approved_application_details['user_email_id']])->first();

						if (!empty($user_posted_office_id))
						{

							if (in_array($user_posted_office_id['posted_ro_office'],$application_approved_office)) {

								$approved_application_list[$i] = $each_customer_id['customer_id'];
								$i=$i+1;
							}
						}
					}
				}
			}

			// below if-else code added by Ankur Jangid
			if (!empty($approved_application_list)) {
				$conditions = ['customer_id IN' => $approved_application_list, 'status' => 'approved', 'current_level' => 'level_3'];
			} else {
				$conditions = ['customer_id IS' => '', 'status' => 'approved', 'current_level' => 'level_3'];
			}

			$approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

			$download_approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

		} elseif ($search_application_type_id != '' && $application_approved_office == '' && $search_from_date !='' && $search_to_date != '')
		{
			$approved_application_customer_id = $this->$table->find('all')->select(['customer_id'])->where(['date(modified) BETWEEN :start AND :end'])
			->where(['status' => 'approved', 'current_level' => 'level_3'])->bind(':start', $search_from_date, 'date')->bind(':end', $search_to_date, 'date')->toArray();

			$i=0;
			foreach ($approved_application_customer_id as $each_customer_id) {
				$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

				if (in_array($application_customer_type, $search_application_type_id)) {
					$approved_application_list[$i] = $each_customer_id['customer_id'];
					$i=$i+1;
				}
			}

			// below if-else code added by Ankur Jangid
			if (!empty($approved_application_list)) {
				$conditions = ['customer_id IN' => $approved_application_list, 'status' => 'approved', 'current_level' => 'level_3'];
			} else {
				$conditions = ['customer_id IS' => '', 'status' => 'approved', 'current_level' => 'level_3'];
			}

			$approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

			$download_approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

		} elseif ($search_application_type_id != '' && $application_approved_office != '' && $search_from_date !='' && $search_to_date != '')
		{
			$approved_application_customer_id = $this->$table->find('all')->select(['customer_id'])->where(['date(modified) BETWEEN :start AND :end'])
			->where(['status' => 'approved', 'current_level' => 'level_3'])->bind(':start', $search_from_date, 'date')->bind(':end', $search_to_date, 'date')->toArray();

			$i=0;
			foreach ($approved_application_customer_id as $each_customer_id) {

				$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

				if (in_array($application_customer_type,$search_application_type_id)) {

					$approved_application_details = $this->DmiGrantCertificatesPdfs->find('all')->where(['customer_id' => $each_customer_id['customer_id']])->first();

					$user_posted_office_id = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email IS' => $approved_application_details['user_email_id']])->first();

					if (!empty($user_posted_office_id)) {

						if (in_array($user_posted_office_id['posted_ro_office'], $application_approved_office)) {
							$approved_application_list[$i] = $each_customer_id['customer_id'];
							$i=$i+1;
						}
					}
				}
			}

			// below if-else code added by Ankur Jangid
			if (!empty($approved_application_list)) {
				$conditions = ['customer_id IN' => $approved_application_list, 'status' => 'approved', 'current_level' => 'level_3'];
			} else {
				$conditions = ['customer_id IS' => '', 'status' => 'approved', 'current_level' => 'level_3'];
			}

			$approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

			$download_approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

		} elseif ($search_application_type_id == '' && $application_approved_office != '' && $search_from_date =='' && $search_to_date == '')
		{

			$approved_application_customer_id = $this->$table->find('all')->select(['customer_id'])->where(['status' => 'approved', 'current_level' => 'level_3'])->toArray();

			$i=0;
			foreach ($approved_application_customer_id as $each_customer_id) {

				$approved_application_details_list = $this->DmiGrantCertificatesPdfs->find('all')->select(['id', 'id'])->where(['customer_id IS' => $each_customer_id['customer_id']])->combine('id', 'id')->toArray();

				if (!empty($approved_application_details_list)) {

					$approved_application_details = $this->DmiGrantCertificatesPdfs->find('all')->where(['id' => max($approved_application_details_list)])->first();

					$user_posted_office_id = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email' => $approved_application_details['user_email_id']])->first();

					if (!empty($user_posted_office_id)) {

						if (in_array($user_posted_office_id['posted_ro_office'],$application_approved_office)) {
							$approved_application_list[$i] = $each_customer_id['customer_id'];
							$i=$i+1;
						}
					}
				}
			}

			// below if-else code added by Ankur Jangid
			if (!empty($approved_application_list))
			{
				$conditions = ['customer_id IN' => $approved_application_list, 'status' => 'approved', 'current_level' => 'level_3'];
			}
			else
			{
				$conditions = ['customer_id IS' => '', 'status' => 'approved', 'current_level' => 'level_3'];
			}

			$approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

			$download_approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

		} elseif ($search_application_type_id == '' && $application_approved_office != '' && $search_from_date !='' && $search_to_date != '')
		{

			$approved_application_customer_id = $this->$table->find('all')->select(['customer_id'])->where(['date(modified) BETWEEN :start AND :end', 'status' => 'approved',
				'current_level' => 'level_3'])->bind(':start', $search_from_date, 'date')->bind(':end', $search_to_date, 'date')->toArray();

			$i=0;
			foreach ($approved_application_customer_id as $each_customer_id) {

				$approved_application_details_list = $this->DmiGrantCertificatesPdfs->find('all')->select(['id', 'id'])->where(['customer_id' => $each_customer_id['customer_id']])->combine('id', 'id')->toArray();

				if (!empty($approved_application_details_list)) {

					$approved_application_details = $this->DmiGrantCertificatesPdfs->find('all')->where(['id' => max($approved_application_details_list)])->first();

					$user_posted_office_id = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email' => $approved_application_details['user_email_id']])->first();

					if (!empty($user_posted_office_id)) {

						if (in_array($user_posted_office_id['posted_ro_office'], $application_approved_office)) {
							$approved_application_list[$i] = $each_customer_id['customer_id'];
							$i=$i+1;
						}
					}
				}
			}

			// below if-else code added by Ankur Jangid
			if (!empty($approved_application_list)) {
				$conditions = ['customer_id IN' => $approved_application_list, 'status' => 'approved', 'current_level' => 'level_3'];
			} else {
				$conditions = ['customer_id IS' => '', 'status' => 'approved', 'current_level' => 'level_3'];
			}

			$approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

			$download_approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

		}
		elseif ($search_application_type_id == '' && $application_approved_office == '' && $search_from_date !='' && $search_to_date != '')
		{

			$approved_application_customer_id = $this->$table->find('all')->select(['customer_id'])->where(['date(modified) BETWEEN : startAND :end', 'status' => 'approved',
				'current_level' => 'level_3'])->bind(':start', $search_from_date, 'date')->bind(':end', $search_to_date, 'date')->toArray();

			$i=0;
			foreach ($approved_application_customer_id as $each_customer_id) {
				$approved_application_list[$i] = $each_customer_id['customer_id'];
				$i=$i+1;
			}

			// below if-else code added by Ankur Jangid
			if (!empty($approved_application_list))
			{
				$conditions = ['customer_id IN' => $approved_application_list, 'status' => 'approved', 'current_level' => 'level_3'];
			} else {
				$conditions = ['customer_id IS' => '', 'status' => 'approved', 'current_level' => 'level_3'];
			}

			$approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

			$download_approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

		} else {

			if ($search_flag == 'on')
			{
				$approved_application_customer_id = $this->$table->find('all')->select(['customer_id'])->where(['status' => 'approved', 'current_level' => 'level_3'])
				->order(['created'=>'DESC'])->extract('customer_id')->toArray(0);
			} else {

				// THIS BELOW CONDITION IS ADDED FOR THE ALL REPORTS BY AKASH ON 16-06-2022
				if ($table == 'DmiGrantCertificatesPdfs') {

					$posted_ro_office = $this->DmiUsers->find('all',array('fields'=>'posted_ro_office', 'conditions'=>array('email IS'=>$_SESSION['username'])))->first();

					$get_short_code = $this->DmiRoOffices->find('all',array('fields'=>'short_code', 'conditions'=>array('id IS'=>$posted_ro_office['posted_ro_office'])))->first();

					$short_code = $get_short_code['short_code'];

					if ($_SESSION['role'] == 'Head Office')
					{

						$approved_application_customer_id = $this->DmiGrantCertificatesPdfs->find('all')->select(['customer_id'])->group(['customer_id HAVING COUNT(customer_id) >= 1'])->limit(['2'])->extract('customer_id')->toArray(0);
					} else {
						$approved_application_customer_id = $this->DmiGrantCertificatesPdfs->find('all',array('fields'=>'customer_id','conditions'=>array('customer_id like'=>'%/'.$short_code.'/%'),'group'=>'customer_id having count(customer_id) >= 1','having'=>array('count(customer_id) >= 1')))->toArray();
					}

				} else {
					$approved_application_customer_id = $this->$table->find('all')->select(['customer_id'])->where(['status' => 'approved', 'current_level' => 'level_3'])
					->order(['created'=>'DESC'])->limit(['100'])->extract('customer_id')->toArray(0);
				}


			}

			$approved_application_list = $approved_application_customer_id;

			// below if-else code added by Ankur Jangid
			if (!empty($approved_application_list))
			{

				// THIS BELOW CONDITION IS ADDED FOR THE ALL REPORTS BY AKASH ON 16-06-2022
				if ($table == 'DmiGrantCertificatesPdfs')
				{
					$conditions = array('customer_id IN'=>$approved_application_list);
				} else
				{
					$conditions = ['customer_id IN' => $approved_application_list, 'status' => 'approved', 'current_level' => 'level_3'];
				}

			} else {
				$conditions = ['customer_id IS' => '', 'status' => 'approved', 'current_level' => 'level_3'];
			}


			$approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

			$download_approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();
		}


		return array($approved_application_list,$download_approved_application_list);

	}





	// renewal Due For Ca Pp Lab
	// Description :  Function used for Renewal Due Counts & Records List
	// @Author : Yashwant
	// Date : 16/Mar/2023

	public function renewalDueForCaPpLab($renw_ca_id)
	{
		$report_heading = 'Renewal Due Report';
		$this->set('report_heading',$report_heading);

		$reCaId=base64_decode($renw_ca_id);
		$data_id =array($reCaId);

		$searchConditions = array();
		$from_date = array();
		$to_date = array();

		$renewal_application_list=array();

		$all_states = $this->DmiStates->find('all')->select(['id', 'state_name'])->where(['OR'=> [['delete_status IS' => null], ['delete_status ='=>'no']]])
		->order(['state_name' => 'ASC'])->combine('id', 'state_name')->toArray();

		$all_district = $this->DmiDistricts->find('all')->select(['id', 'district_name'])->where(['OR'=> [['delete_status IS' => null], ['delete_status ='=>'no']]])
		->combine('id', 'district_name')->toArray();

		$caRenewalDue = 0; 	$printingRenewalDue = 0; $labRenewalDue = 0;

		if (!empty($data_id))
		{
			$list4RenewalDueCheck = $this->DmiFirms->find('all')->where($searchConditions)->toArray();
			$i=0;
			foreach($list4RenewalDueCheck as $each_application)
			{
				$renewalDue = $this->Customfunctions->checkApplicantValidForRenewal($each_application['customer_id']);
				if($renewalDue == 'yes'){

					$application_customer_type = $this->Reportsfunctions->newApplicantType($each_application['customer_id']);
					$application_form_type = $this->Customfunctions->checkApplicantFormType($each_application['customer_id']);

					if ($application_form_type == 'A') {
						$application_type[$i]='CA (Form-A)';
					} elseif ($application_form_type == 'B') {
						$application_type[$i]='Printing Press (Form-B)';
					} elseif ($application_form_type == 'C') {
						$application_type[$i]='Laboratory (Form-C)';
					} elseif ($application_form_type == 'D') {
						$application_type[$i]='Laboratory (Form-D)';
					} elseif ($application_form_type == 'E') {
						$application_type[$i]='CA (Form-E)';
					} elseif ($application_form_type == 'F') {
						$application_type[$i]='CA (Form-F)';
					}

					if(in_array($application_customer_type, $data_id, TRUE)) {
						$renewal_application_list[$i] =$each_application['customer_id'];
						$i=$i+1;
					}
				}
			}
		}

		if (!empty($renewal_application_list)) {
			$current_Renewaldue_details = $this->DmiFirms->find('all')->where(['customer_id IN'=>$renewal_application_list])->order(['id' => 'DESC'])->toArray();
		} else {
			$current_Renewaldue_details = null;
		}

		$this->set('renewal_application_list',$renewal_application_list);
		$this->set('current_Renewaldue_details',$current_Renewaldue_details);
		$this->set('all_states', $all_states);
		$this->set('all_district', $all_district);
		$this->set('application_form_type', $application_form_type);
		$this->set('application_type', $application_type);


	}




	// pendingScrunitizerApplicationsReport
	// Description : This Function used for pending Scrunitizer  Counts & Records List
	// @Author : Yashwant & shreeya
	// Date : 16/Mar/2023

	public function pendingScrunitizerApplicationsReport($pending_id)
	{
		$PenId=base64_decode($pending_id);
		$data_id =$PenId;


		$date = [];
		$user_list = null;
		$application_type = null;
		$user_roles = null;
		$user_office = array();
		$user_email_id =null;
		$application_id = null;



		$applTypeArray = $this->Session->read('applTypeArray');
		$this->loadModel('DmiFlowWiseTablesLists');
		$applications_current_positions_tables = $this->DmiFlowWiseTablesLists->find('all')->select(['application_form','appl_current_pos'])->where(array('application_type IN'=>$applTypeArray))->order(['id'])->combine('application_form','appl_current_pos')->toArray();

		$pendingCountForMo = 0; $pendingCountForIo = 0; $pendingCountForHo = 0;
		$inprogress_app_with_ro = array();
		$searchPendingConditions = array();

		// Initialize an variable for show the count of array
		//by shreeya [Date - 31-05-2023]
		$pendingcount;
		$appl_type = 1;//added on 02-06-2023 by Shreeya
		$i=0; //increment variable added bt shreeya [Date - 01-05-2023]
		foreach($applications_current_positions_tables as $each_table)
		{


			$key = array_search ($each_table, $applications_current_positions_tables);
			$this->loadModel($each_table);
			$this->loadModel($key);

			//For Progress with MO
			if ($data_id=='MO')
			{

				$report_heading = 'Pending Scrutinizer Applications Report';

				//below query commented by shreeya bcoz of added new query Date [ 01-06-23]

				// $inprogress_with_mo = $this->$each_table->find('all')->select(['id', 'customer_id'])
				// ->where($searchPendingConditions)->where(['current_level' => 'level_1'])
				// ->combine('id', 'customer_id')->toArray();

				//added new query if customer_is is null could not show null entry in cout
				//by shreeya on date [ 01-06-2023]
				$inprogress_with_mo = $this->$each_table->find('all')->select(['id', 'customer_id'])
				->where($searchPendingConditions)->where(['current_level' => 'level_1'])
				->where(function ($exp, $q) {return $exp->notEq('customer_id', '');})
				->combine('id', 'customer_id')->toArray();


				//this foreach loop added for show the existing result of total count
				//by shreeya on date [ 01-06-2023]
				foreach($inprogress_with_mo as $key=> $value){

					$pendingcount[$key] = $value ;

				}

				//fetch the customer_id according to $inprogress_with_mo
				if (!empty($inprogress_with_mo)) {

					$inprogress_mo_details = $this->DmiFirms->find('all')->where(['customer_id IN' => $inprogress_with_mo])->order(['id' => 'DESC'])->toArray();

				} else {
					$inprogress_mo_details = null;
				}


				if (!empty($inprogress_mo_details))
				{
					// $i=0; commented by shreeya on [Date 01-06-2023]this increment is already use in front
					foreach ($inprogress_mo_details as $each_user)
					{

							$customer_id = $each_user['customer_id'];
							$each_user_detail = $each_user;
							$current_level = $each_user_detail['current_level'];
							$application_form_type = $this->Customfunctions->checkApplicantFormType($each_user_detail['customer_id']);

							if ($application_form_type == 'A')
							{
								$application_type[$i]='CA (Form-A)';
							}
							elseif ($application_form_type == 'B')
							{
								$application_type[$i]='Printing Press (Form-B)';
							} elseif ($application_form_type == 'C')
							{
								$application_type[$i]='Laboratory (Form-C)';
							} elseif ($application_form_type == 'D')
							{
								$application_type[$i]='Laboratory (Form-D)';
							} elseif ($application_form_type == 'E')
							{
								$application_type[$i]='CA (Form-E)';
							} elseif ($application_form_type == 'F')
							{
								$application_type[$i]='CA (Form-F)';
							}

							$date[$i] = $each_user_detail['modified'];

							// Added for show the list of userid(current_user_email_id) & pending with(current_level)
							// By shreeya on Date : [16-05-2023]
							$mo_user  = $this->$each_table->find()->select(['current_user_email_id','current_level'])->where(['customer_id IS' => $customer_id])->order('id DESC')->first();

							if(!empty($mo_user)){
								$current_level[$i] = $mo_user['current_level'];
							}else{
								$current_level[$i] = '--';
							}


							if(!empty($mo_user)){
								$user_email_id[$i] = $mo_user['current_user_email_id'];
							}else{
								$user_email_id[$i] = '--';
							}


							//$user_email_id[$i] = $each_user_detail['current_user_email_id'];
							$application_id[$i] = $each_user_detail['customer_id'];

							$user_posted_office_id=array();
							if (!empty($user_email_id[$i]))
							{
								$user_posted_office_id = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email IN' => $user_email_id[$i]])->first();
							}
							if (!empty($user_posted_office_id))
							{
								$user_office_details[$i] = $this->DmiRoOffices->find('all')->select(['ro_office'])->where(['id' => $user_posted_office_id['posted_ro_office']])->first();
							}

							## uncommented this code for show the posted office [Date 16-05-2023 By Shreeya ]
							if (!empty($user_office_details[$i]))
							{
								$user_office[$i] = $user_office_details[$i]['ro_office'];
							} else
							{
								$user_office[$i] = '--';
							}

							$check_roles=array();

							if (!empty($user_email_id[$i]))
							{
								$check_roles = $this->DmiUserRoles->find('all')->where(['user_email_id IN' => $user_email_id[$i]])->first();
							}

							if (!empty($check_roles))
							{
								$user_list[$i] = $check_roles;
							}
							else {
								$user_list[$i] = '---';
							}

							//below condition change variable for show the users roles on listing  $each_user_detail -> $mo_user By Shreeya on Date [16-05-2023]

							if(!empty($mo_user)){
								$user_roles[$i] = $this->checkUserRoleFromCurrentLevel($mo_user['current_level'],$mo_user['current_user_email_id']);
							}else{
								$user_roles[$i] = '--';
							}

							$i=$i+1;

					}

				}

			} elseif($data_id=='IO') {


				$report_heading ='Pending IO Applications Report';
				//For Progress with IO
				$inprogress_with_io = $this->$each_table->find('all')->select(['id', 'customer_id'])
				->where($searchPendingConditions)->where(['current_level' => 'level_2'])
				->combine('id', 'customer_id')->toArray();

				//this foreach loop added for show the total count
				//by shreeya on date [ 01-06-2023]
				foreach($inprogress_with_io as $key=> $value){

					$pendingcount[$key] = $value ;

				}

				if (!empty($inprogress_with_io)) {
					$inprogress_io_details = $this->DmiFirms->find('all')->where(['customer_id IN'=>$inprogress_with_io])->order(['id' => 'DESC'])->toArray();
				} else {
					$inprogress_io_details = null;
				}

				if (!empty($inprogress_io_details))
				{
					// $i=0; commented by shreeya on [Date 01-06-2023]this increment is already use in front
					foreach ($inprogress_io_details as $each_user)
					{
						$customer_id = $each_user['customer_id'];
						$each_user_detail = $each_user;
						$current_level = $each_user_detail['current_level'];
						$application_form_type = $this->Customfunctions->checkApplicantFormType($each_user_detail['customer_id']);

						if ($application_form_type == 'A') {
							$application_type[$i]='CA (Form-A)';
						}elseif ($application_form_type == 'B') {
							$application_type[$i]='Printing Press (Form-B)';
						} elseif ($application_form_type == 'C') {
							$application_type[$i]='Laboratory (Form-C)';
						} elseif ($application_form_type == 'D') {
							$application_type[$i]='Laboratory (Form-D)';
						} elseif ($application_form_type == 'E') {
							$application_type[$i]='CA (Form-E)';
						} elseif ($application_form_type == 'F') {
							$application_type[$i]='CA (Form-F)';
						}


						$date[$i] = $each_user_detail['modified'];

						// Added for show the list of userid(current_user_email_id) & pending with(current_level)
						// By shreeya on Date : [16-05-2023]
						$mo_user  = $this->$each_table->find()->select(['current_user_email_id','current_level'])->where(['customer_id IS' => $customer_id])->order('id DESC')->first();

						if(!empty($mo_user)){
							$current_level[$i] = $mo_user['current_level'];
						}else{
							$current_level[$i] = '--';
						}

						if(!empty($mo_user)){
							$user_email_id[$i] = $mo_user['current_user_email_id'];
						}else{
							$user_email_id[$i] = '--';
						}


						$application_id[$i] = $each_user_detail['customer_id'];


						$user_posted_office_id=array();
						if (!empty($user_email_id[$i]))
						{
							$user_posted_office_id = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email IN' => $user_email_id[$i]])->first();
						}
						if (!empty($user_posted_office_id))
						{
							$user_office_details[$i] = $this->DmiRoOffices->find('all')->select(['ro_office'])->where(['id' => $user_posted_office_id['posted_ro_office']])->first();
						}
						## uncommented this code for show the posted office [Date 16-05-2023 By Shreeya ]
						if (!empty($user_office_details[$i]))
						{
							$user_office[$i] = $user_office_details[$i]['ro_office'];
						} else
						{
							$user_office[$i] = '--';
						}

						$check_roles=array();

						if (!empty($user_email_id[$i]))
						{
							$check_roles = $this->DmiUserRoles->find('all')->where(['user_email_id IN' => $user_email_id[$i]])->first();
						}

						if (!empty($check_roles))
						{
							$user_list[$i] = $check_roles;
						}
						else {
							$user_list[$i] = '---';
						}

						//below condition change variable for show user roles $each_user_detail -> $mo_user By Shreeya on Date [16-05-2023]
						if(!empty($mo_user)){
							$user_roles[$i] = $this->checkUserRoleFromCurrentLevel($mo_user['current_level'],$mo_user['current_user_email_id']);
						}else{
							$user_roles[$i] = '--';
						}
						$i=$i+1;
					}
				}

			//For Progress with HO
			} elseif($data_id=='HO') {

				$report_heading ='Pending HO Applications Report';
				$inprogress_with_ho = $this->$each_table->find('all')->select(['id', 'customer_id'])
				->where($searchPendingConditions)->where(['current_level' => 'level_4'])
				->combine('id', 'customer_id')->toArray(); // updated by Ankur

				//this foreach loop added for show the total count
				//by shreeya on date [ 01-06-2023]
				foreach($inprogress_with_ho as $key=> $value){

					$pendingcount[$key] = $value ;

				}

				if (!empty($inprogress_with_ho)) {
					$inprogress_ho_details = $this->DmiFirms->find('all')->where(['customer_id IN'=>$inprogress_with_ho])->order(['id' => 'DESC'])->toArray();
				} else {
					$inprogress_ho_details = null;
				}

				if (!empty($inprogress_ho_details))
				{

					// $i=0; commented by shreeya on [Date 01-06-2023]this increment is already use in front
					foreach ($inprogress_ho_details as $each_user)
					{

						$customer_id = $each_user['customer_id'];

						$each_user_detail = $each_user;

						$current_level = $each_user_detail['current_level'];
						$application_form_type = $this->Customfunctions->checkApplicantFormType($each_user_detail['customer_id']);

						if ($application_form_type == 'A')
						{
							$application_type[$i]='CA (Form-A)';
						}
						elseif ($application_form_type == 'B')
						{
							$application_type[$i]='Printing Press (Form-B)';
						} elseif ($application_form_type == 'C')
						{
							$application_type[$i]='Laboratory (Form-C)';
						} elseif ($application_form_type == 'D')
						{
							$application_type[$i]='Laboratory (Form-D)';
						} elseif ($application_form_type == 'E')
						{
							$application_type[$i]='CA (Form-E)';
						} elseif ($application_form_type == 'F')
						{
							$application_type[$i]='CA (Form-F)';
						}

						$date[$i] = $each_user_detail['modified'];

						// Added for show the list of userid(current_user_email_id) & pending with(current_level)
						// By shreeya on Date : [16-05-2023]
						$mo_user  = $this->$each_table->find()->select(['current_user_email_id','current_level'])->where(['customer_id IS' => $customer_id])->order('id DESC')->first();
						if(!empty($mo_user)){
							$current_level[$i] = $mo_user['current_level'];
						}else{
							$current_level[$i] = '--';
						}


						if(!empty($mo_user)){
							$user_email_id[$i] = $mo_user['current_user_email_id'];
						}else{
							$user_email_id[$i] = '--';
						}


						//$user_email_id[$i] = $each_user_detail['current_user_email_id'];
						$application_id[$i] = $each_user_detail['customer_id'];

						$user_posted_office_id=array();
						if (!empty($user_email_id[$i]))
						{
							$user_posted_office_id = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email IN' => $user_email_id[$i]])->first();
						}
						if (!empty($user_posted_office_id))
						{
							$user_office_details[$i] = $this->DmiRoOffices->find('all')->select(['ro_office'])->where(['id' => $user_posted_office_id['posted_ro_office']])->first();
						}

						## uncommented this code for show the posted office [Date 16-05-2023 By Shreeya ]
						if (!empty($user_office_details[$i]))
						{
							$user_office[$i] = $user_office_details[$i]['ro_office'];
						} else
						{
							$user_office[$i] = '--';
						}

						$check_roles=array();

						if (!empty($user_email_id[$i]))
						{
							$check_roles = $this->DmiUserRoles->find('all')->where(['user_email_id IN' => $user_email_id[$i]])->first();
						}

						if (!empty($check_roles))
						{
							$user_list[$i] = $check_roles;
						}
						else {
							$user_list[$i] = '---';
						}

						//below condition change variable for shoe user role $each_user_detail -> $mo_user By Shreeya on Date [16-05-2023]

						if(!empty($mo_user)){
							$user_roles[$i] = $this->checkUserRoleFromCurrentLevel($mo_user['current_level'],$mo_user['current_user_email_id']);
						}else{
							$user_roles[$i] = '--';
						}

						$i=$i+1;
					}
				}

			} elseif($data_id=='RO') {

				$report_heading ='Pending RO Applications Report';
				//For Progress with RO
				$inprogress_with_ro = $this->$each_table->find('all')->select(['id', 'customer_id'])
														->where($searchPendingConditions)->where(['current_level' => 'level_3'])
														->combine('id', 'customer_id')->toArray();



				$pending_with_ro_arr = array(); //Initialize an array by Shreeya on date [02-06-2023]
				foreach($inprogress_with_ro as $each_record ){
					//The query searches for records in the specified table where the customer_id is equal to the current record
					$result_status = $this->$key->find('all')->where(['customer_id' => $each_record, 'status' => 'approved', 'current_level' => 'level_3'])->toArray();
					//If the $result_status is empty the current record is added to the $pending_with_ro_arr array using the in_array() function to check if it is not already present.
					if(empty($result_status)){
						//$inprogress_app_with_ro[] = $each_record;

						if(!in_array($each_record,$pending_with_ro_arr)){
							$pending_with_ro_arr[]=$each_record;
						}

					}
				}
				//the $pending_with_ro_arr array is assigned to the $inprogress_app_with_ro array. This adds the array of pending records
				//By Shreeya on Date [02-06-2023]
				$inprogress_app_with_ro[] = $pending_with_ro_arr;


				//if the $pending_with_ro_arr is not empty. the pending records based on the $appl_type.
				//If $appl_type is not equal to 4, it queries the DmiFirms table using the customer_id from the $pending_with_ro_arr.
				//By Shreeya on Date [02-06-2023]
				if (!empty($pending_with_ro_arr)) {

					if($appl_type!=4){
						$inprogress_ro_details = $this->DmiFirms->find('all')->where(['customer_id IN'=>$pending_with_ro_arr])->order(['id' => 'DESC'])->toArray();
					}else{
						//specific for chemist application flow
						//it queries the DmiChemistRegistrations table using the chemist_id from the $pending_with_ro_arr. The resulting records are stored in the $inprogress_ro_details variable.
						//By Shreeya on Date [02-06-2023]
						$this->loadModel('DmiChemistRegistrations');
						$inprogress_ro_details = $this->DmiChemistRegistrations->find('all')->where(['chemist_id IN'=>$pending_with_ro_arr])->order(['id' => 'DESC'])->toArray();
					}


				} else {
					$inprogress_ro_details = null;
				}

				if (!empty($inprogress_ro_details))
				{

					// $i=0; commented by shreeya on [Date 01-06-2023]this increment is already use in front
					foreach ($inprogress_ro_details as $each_user) {
						//If $appl_type is not equal to 4, it assigns the value of $each_user['customer_id'] to
						// the $customer_id variable. Otherwise, it assigns the value of $each_user['chemist_id'] to the $customer_id variable.
						//By Shreeya on Date [02-06-2023]
						if($appl_type!=4){
							$customer_id = $each_user['customer_id'];
						}else{
							$customer_id = $each_user['chemist_id'];
						}
						//$each_user_detail = $each_user;
						//If $appl_type is not equal to 4, it assigns the value of $each_user['current_level'] to the $current_level variable.
						//By Shreeya on Date [02-06-2023]
						if($appl_type!=4){
							$current_level = $each_user['current_level'];
						}



						if($appl_type == 4){

							$application_form_type ='Chemist';

							if ($application_form_type == 'Chemist') {
								$application_type[$i] = 'CHM (Chemist)';
							}
						}

						$application_form_type = $this->Customfunctions->checkApplicantFormType($customer_id);

						if ($application_form_type == 'A') {
							$application_type[$i] = 'CA (Form-A)';
						} elseif ($application_form_type == 'B') {
							$application_type[$i] = 'Printing Press (Form-B)';
						} elseif ($application_form_type == 'C') {
							$application_type[$i] = 'Laboratory (Form-C)';
						} elseif ($application_form_type == 'D') {
							$application_type[$i] = 'Laboratory (Form-D)';
						} elseif ($application_form_type == 'E') {
							$application_type[$i] = 'CA (Form-E)';
						} elseif ($application_form_type == 'F') {
							$application_type[$i] = 'CA (Form-F)';
						}

						$date[$i] = $each_user['modified'];

						// Added for show the list of userid(current_user_email_id) & pending with(current_level)
						// By shreeya on Date : [16-05-2023]
						$mo_user = $this->$each_table->find()
							->select(['current_user_email_id', 'current_level'])
							->where(['customer_id IS' => $customer_id])
							->order('id DESC')
							->first();

						if (!empty($mo_user)) {
							$user_email_id[$i] = $mo_user['current_user_email_id'];
							//$user_email_id[$i]= ''. $each_user[$j] .'-'. $mo_user['current_user_email_id'] . '';
						} else {
							$user_email_id[$i] = '--';
						}

						if (!empty($mo_user)) {
							$current_level[$i] = $mo_user['current_level'];
						} else {
							$current_level[$i] = '--';
						}

						$application_id[$i] = $customer_id;

						$user_posted_office_id = array();
						if (!empty($user_email_id[$i])) {
							$user_posted_office_id = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email IN' => $user_email_id[$i]])->first();
						}

						if (!empty($user_posted_office_id)) {
							$user_office_details[$i] = $this->DmiRoOffices->find('all')->select(['ro_office'])->where(['id' => $user_posted_office_id['posted_ro_office']])->first();
						}

						## uncommented this code for show the posted office [Date 16-05-2023 By Shreeya ]
						if (!empty($user_office_details[$i])) {
							$user_office[$i] = $user_office_details[$i]['ro_office'];
						} else {
							$user_office[$i] = '--';
						}

						$check_roles = array();
						if (!empty($user_email_id[$i])) {
							$check_roles = $this->DmiUserRoles->find('all')->where(['user_email_id IN' => $user_email_id[$i]])->first();
						}

						if (!empty($check_roles)) {
							$user_list[$i] = $check_roles;
						} else {
							$user_list[$i] = '---';
						}

						//below condition change variable $each_user_detail -> $mo_user By Shreeya on Date [16-05-2023]
						if (!empty($mo_user)) {
							$user_roles[$i] = $this->checkUserRoleFromCurrentLevel($mo_user['current_level'], $mo_user['current_user_email_id']);
						}
						else{
						 	$user_roles[$i] = '3';
						 }
						 $i = $i + 1;
					}


				}

			}

			$appl_type++;
		}

		//exit;

		$total_pending = $pendingCountForMo + $pendingCountForIo + $pendingCountForHo + count($inprogress_app_with_ro);



		$this->set('report_heading',$report_heading);
		$this->set('data_id',$data_id);
		$this->set('date',$date);
		$this->set('user_list',$user_list);
		$this->set('application_type',$application_type);
		$this->set('user_roles',$user_roles);
		$this->set('user_office',$user_office);
		$this->set('user_email_id',$user_email_id);
		$this->set('application_id',$application_id);
		$this->set('total_pending',$total_pending);



		// $this->set('pendingCountForMo',$pendingCountForMo);
		// $this->set('pendingCountForIo',$pendingCountForIo);
		// $this->set('pendingCountForHo',$pendingCountForHo);

		//$this->set('inprogress_mo_details',$inprogress_mo_details);
		// $this->set('inprogress_io_details',$inprogress_io_details);
		// $this->set('inprogress_ho_details',$inprogress_ho_details);
		// $this->set('inprogress_ro_details',$inprogress_ro_details);




	}





	// esign New Applications Report
	// Description : function Used for ESIGNED NEW Application kpi's COUNT & RECORD
	// @Author : Yashwant
	// Date : 20-Mar-2023

	public function esignNewApplicationsReport($esigned_new_id)
	{
		$EsignNewId=base64_decode($esigned_new_id);
		$data_id =$EsignNewId;

		$searchPendingConditions =array();
		$this->loadModel('DmiApplicationEsignedStatuses');

		$applicationEsigned = $this->DmiApplicationEsignedStatuses->find('all')->where($searchPendingConditions)->where(['application_esigned' => 'yes'])->toArray();

		$inspectionReportEsigned = $this->DmiApplicationEsignedStatuses->find('all')->where($searchPendingConditions)->where(['report_esigned' => 'yes'])->toArray();

		$certificateEsigned = $this->DmiApplicationEsignedStatuses->find('all')->where($searchPendingConditions)->where(['certificate_esigned' => 'yes'])->toArray();

		$search_application_type_id='';

		if($data_id =="APP")
		{
			$i=0;
			foreach ($applicationEsigned as $each)
			{
				$approved_esign_list[$i] = $each['customer_id'];
				$i=$i+1;
			}
			$report_heading ="Esigned New Applications Reports";
			$this->set('report_heading', $report_heading);
			$this->esignNewApplnResults($approved_esign_list,$data_id);
		}

		if($data_id =="INSPECT")
		{
			$i=0;
			foreach ($inspectionReportEsigned as $each)
			{
				$approved_esign_list[$i] = $each['customer_id'];
				$i=$i+1;
			}
			$report_heading ="Esigned Inspection Application Reports";
			$this->set('report_heading', $report_heading);
			$this->esignNewApplnResults($approved_esign_list,$data_id);
		}

		if($data_id =="GRANT")
		{
			$i=0;
			foreach ($certificateEsigned as $each)
			{
				$approved_esign_list[$i] = $each['customer_id'];
				$i=$i+1;
			}
			$report_heading ="Esigned Grant Certificate Application Reports";
			$this->set('report_heading', $report_heading);
			$this->esignNewApplnResults($approved_esign_list,$data_id);
		}


	}



	// esignNewApplnResults
	// Description : function Used for ESIGNED NEW Application kpi's COUNT & RECORD
	// @Author : Yashwant
	// Date : 20-Mar-2023

	public function esignNewApplnResults($approved_esign_list,$data_id)
	{

		$date=array();
		$application_type=array();
		$application_user_email_id=array();
		$user_office=array();
		$application_customer_id=array();
		$name_of_the_firm=array();
		$address_of_the_firm=array();
		$contact_details_of_the_firm=array();
		$approved_TBL_details_tbl_name=array();
		$approved_TBL_details_tbl_registered_no=array();
		$laboratory_details_name=array();
		$laboratory_details_address=array();
		$approved_application_type_text = array();
		$valid_upto= array();
		$state_name = array();
		$phoneno = array();
		$issued_on = array();

		if (!empty($approved_esign_list))
		{
			$i=0;
			//applied array_unique function on 18-07-2019
			foreach (array_unique($approved_esign_list) as $esigned_application)
			{
				$esign_application_details = array();

				if ($data_id == 'APP')
				{
					$esign_application_details = $this->DmiApplicationEsignedStatuses->find('all')->where(['customer_id' => $esigned_application,'application_esigned' => 'yes'])->first();
				}
				elseif($data_id == 'INSPECT')
				{
					$esign_application_details = $this->DmiApplicationEsignedStatuses->find('all')->where(['customer_id' => $esigned_application,'report_esigned' => 'yes'])->first();
				}
				elseif($data_id == 'GRANT')
				{
					$esign_application_details = $this->DmiApplicationEsignedStatuses->find('all')->where(['customer_id' => $esigned_application,'certificate_esigned' => 'yes'])->first();
				}

				//this condition added on 18-07-2019
				if (!empty($esign_application_details))
				{
					$approved_application_result = $esign_application_details;
					if ($approved_application_result['user_email_id'] == 'old_application')
					{
						$old_app_approved_by = $this->Customfunctions->old_app_approved_by($approved_application_result['customer_id']);
						$approved_application_result['user_email_id'] = $old_app_approved_by;
					}

					$explode = explode("/",$approved_application_result['customer_id']);

					$approved_office_id = $this->DmiRoOffices->find('all')->select(['ro_email_id'])->where(['short_code' => $explode[2]])->first();

					$user_posted_office_id = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email'=>$approved_office_id['ro_email_id']])->first();

					if (!empty($user_posted_office_id))
					{
						$user_office_details = $this->DmiRoOffices->find('all')->select(['ro_office'])->where(['id'=>$user_posted_office_id['posted_ro_office']])->first();
						if (!empty($user_office_details)) {
							$user_office[$i] = $user_office_details['ro_office'];
						} else {
							$user_office[$i] = 'N/A';
						}

					} else {
						$user_office[$i] = 'N/A';
					}

					$application_form_type = $this->Reportsfunctions->checkApplicantFormTypeForReports($approved_application_result['customer_id']);

					if ($application_form_type == 'A')
					{
						$application_type[$i]='CA (Form-A)';

					} elseif ($application_form_type == 'B')
					{
						$application_type[$i]='Printing Press (Form-B)';
					} elseif ($application_form_type == 'C')
					{
						$application_type[$i]='Laboratory (Form-C)';
					} elseif ($application_form_type == 'D')
					{
						$application_type[$i]='Laboratory (Form-D)';
					} elseif ($application_form_type == 'E')
					{
						$application_type[$i]='CA (Form-E)';
					} elseif ($application_form_type == 'F')
					{
						$application_type[$i]='CA (Form-F)';
					}

					$date[$i] = $approved_application_result['created'];
					$application_customer_id[$i] = $approved_application_result['customer_id'];

					//added by the akash on 13-11-2021
					$firmDetails = $this->DmiFirms->find('all',array('conditions'=>array('customer_id IS'=>$approved_application_result['customer_id'])))->first();
					//added not empty contion & rmove isset by shreeya on date (06-07-2023)
					if(!empty($firmDetails)){
						$name_of_the_firm[$i] = $firmDetails['firm_name'];
						$address_of_the_firm[$i] = $firmDetails['street_address'];
						$contact_details_of_the_firm[$i] = base64_decode($firmDetails['email']);
						$phoneno[$i] = $firmDetails['mobile_no'];
					}




					//tbl details
					$tbl_details = $this->DmiAllTblsDetails->find('all',array('conditions'=>array('customer_id IS'=>$approved_application_result['customer_id'],'OR' => array('delete_status IS NULL', 'delete_status' => 'no'))))->toArray();

					if (!empty($tbl_details))
					{
						$j=0;
						foreach ($tbl_details as $each) {

							$approved_TBL_details_tbl_name[$i][$j] = $each['tbl_name'];
							$approved_TBL_details_tbl_registered_no[$i][$j] = $each['tbl_registered_no'];
							$j++;
						}
					}
					else
					{
						$approved_TBL_details_tbl_name[$i][0] = 'N/A';
						$approved_TBL_details_tbl_registered_no[$i][0] = 'N/A';
					}


					//lab details
					$lab_details = $this->DmiCustomerLaboratoryDetails->find('all',array('conditions'=>array('customer_id IS'=>$approved_application_result['customer_id'])))->toArray();
					if (!empty($lab_details))
					{
						$laboratory_details_name[$i] = $lab_details[0]['laboratory_name'];
						$laboratory_details_address[$i] = $lab_details[0]['street_address'];
					} else
					{
						$laboratory_details_name[$i] = 'N/A';
						$laboratory_details_address[$i] = 'N/A';
					}

					$commodity_value = $this->DmiFirms->find('all')->select(['sub_commodity'])->where(['customer_id'=>$approved_application_result['customer_id']])->first();
					//$commodity_list[$i] = $this->Customfunctions->showCommdityInApplList($commodity_value['sub_commodity']);
					//Added this Code on the 08-04-2022 as the base encoding was not on some emails so to dispaly the email , checking if the encoding is needed or not.
					$checkEmailForEncoding[$i] = $approved_application_result['user_email_id'];

					if($this->isBase64Encoded($checkEmailForEncoding[$i]) == true){
						$application_user_email_id[$i] = base64_decode($approved_application_result['user_email_id']);
					} else {
						$application_user_email_id[$i] = $approved_application_result['user_email_id'];
					}

					//check the expiry dateand print to the reports  added by Akash on 24-05-2022
					$grant_date = chop($esign_application_details['date'],"00:00:00");
					$valid_upto[$i] = $this->Customfunctions->getCertificateValidUptoDate($approved_application_result['customer_id'],$grant_date);

					//check the state name added by akash on 14-06-2022
					//$state_name[$i] = $this->getStateName($approved_application_result['customer_id']);

					//Certificate Issued on
					$issued_on[$i] = chop($approved_application_result['date'],"00:00:00");

					$i=$i+1;
				}
			}
		}


		$this->set('date',$date);
		$this->set('application_customer_id',$application_customer_id);
		$this->set('application_user_email_id',$application_user_email_id);
		$this->set('application_type',$application_type);
		$this->set('user_office',$user_office);
		$this->set('approved_esign_list',$approved_esign_list);

		$this->set('approved_application_type',$approved_application_type_text);
		$this->set('name_of_the_firm',$name_of_the_firm);
		$this->set('address_of_the_firm',$address_of_the_firm);
		$this->set('contact_details_of_the_firm',$contact_details_of_the_firm);
		$this->set('approved_TBL_details_tbl_name',$approved_TBL_details_tbl_name);
		$this->set('approved_TBL_details_tbl_registered_no',$approved_TBL_details_tbl_registered_no);
		$this->set('laboratory_details_name',$laboratory_details_name);
		$this->set('laboratory_details_address',$laboratory_details_address);
		$this->set('valid_upto',$valid_upto);
		$this->set('state_name',$state_name);
		$this->set('phoneno',$phoneno);
		$this->set('issued_on',$issued_on);


	}




	// esign Renewal Applications Report
	// Description : This function Used for ESIGNED RENEWAL Application kpi's COUNT & RECORD LIST
	// @Author : Yashwant
	// Date : 21-Mar-2023

	public function esignRenewalApplicationsReport($esigned_renewal_id)
	{

		$EsignRenewId=base64_decode($esigned_renewal_id);
		$data_id =$EsignRenewId;
		$searchPendingConditions =array();
		$renewal_esign_list=array();

		$this->loadModel('DmiRenewalEsignedStatuses');
		$renewalApplicationEsigned = $this->DmiRenewalEsignedStatuses->find('all')->where($searchPendingConditions)->where(['application_esigned' => 'yes'])->toArray();
		$renewalInspectionReportEsigned = $this->DmiRenewalEsignedStatuses->find('all')->where($searchPendingConditions)->where(['report_esigned' => 'yes'])->toArray();
		// below query is commented by shreeya adde new query on date [05-06-2023]
		//$renewalCertificateEsigned = $this->DmiRenewalEsignedStatuses->find('all')->where($searchPendingConditions)->where(['certificate_esigned' => 'yes'],['customer_id' => 'IS NULL'])->toArray();

		// adde for if customer id is null could not show null records count
		// added by shreeya on date [05-06-2023]
		$renewalCertificateEsigned = $this->DmiRenewalEsignedStatuses->find('all')->where($searchPendingConditions)->where(['certificate_esigned' => 'yes'])
		->where(function ($exp, $q) {return $exp->notEq('customer_id', '');})->toArray();



		if($data_id =="APP"){
			$i=0;
			foreach ($renewalApplicationEsigned as $each) {

				$renewal_esign_list[$i] = $each['customer_id'];
				$i=$i+1;
			}

			$report_heading ="Esigned New Applications Reports";
			$this->set('report_heading', $report_heading);
			$this->esignRenewalApplnResults($renewal_esign_list,$data_id);
		}

		if($data_id =="INSPECT"){

			$i=0;
			foreach ($renewalInspectionReportEsigned as $each) {
				$renewal_esign_list[$i] = $each['customer_id'];
				$i=$i+1;
			}

			$report_heading ="Esigned Inspection Application Reports";
			$this->set('report_heading', $report_heading);
			$this->esignRenewalApplnResults($renewal_esign_list,$data_id);
		}

		if($data_id =="GRANT"){
			$i=0;
			foreach ($renewalCertificateEsigned as $each) {
				$renewal_esign_list[$i] = $each['customer_id'];
				$i=$i+1;
			}

			$report_heading ="Esigned Grant Certificate Application Reports";
			$this->set('report_heading', $report_heading);
			$this->esignRenewalApplnResults($renewal_esign_list,$data_id);
		}

	}



	// esign Renewal Application Results
	// @Author : Yashwant
	//Contribution : Shreeya
	// Date : 21-Mar-2023

	public function esignRenewalApplnResults($renewal_esign_list,$data_id)
	{


		$date=array();
		$application_type=array();
		$application_user_email_id=array();
		$user_office=array();
		$application_customer_id=array();
		$name_of_the_firm=array();
		$address_of_the_firm=array();
		$contact_details_of_the_firm=array();
		$approved_TBL_details_tbl_name=array();
		$approved_TBL_details_tbl_registered_no=array();
		$laboratory_details_name=array();
		$laboratory_details_address=array();
		$approved_application_type_text = array();
		$valid_upto= array();
		$state_name = array();
		$phoneno = array();
		$issued_on = array();


		$this->loadModel('DmiRenewalEsignedStatuses');
		if (!empty($renewal_esign_list))
		{

			$i=0;
			//applied array_unique function on 18-07-2019
			//remove array_unique By shreeya on date [02-06-2023]
			foreach ($renewal_esign_list as $esigned_renew_application)
			{

				$esign_application_details = array();

				if ($data_id == 'APP') {
					$esign_application_details = $this->DmiRenewalEsignedStatuses->find('all')->where(['customer_id' => $esigned_renew_application,'application_esigned' => 'yes'])->first();

				}elseif($data_id == 'INSPECT') {
					$esign_application_details = $this->DmiRenewalEsignedStatuses->find('all')->where(['customer_id' => $esigned_renew_application,'report_esigned' => 'yes'])->first();
				}elseif($data_id == 'GRANT') {
					$esign_application_details = $this->DmiRenewalEsignedStatuses->find('all')->where(['customer_id IN' =>  $esigned_renew_application ?  $esigned_renew_application : [null],'certificate_esigned' => 'yes'])->first();

					/*['customer_id IN' =>
					count($esigned_renew_application) > 0 ?  $esigned_renew_application : [null])*/
					//echo"<pre>";print_r($esign_application_details);
				}

				//this condition added on 18-07-2019
				if (!empty($esign_application_details)) {

					$approved_application_result = $esign_application_details;

					if ($approved_application_result['user_email_id'] == 'old_application') {

						$old_app_approved_by = $this->Customfunctions->old_app_approved_by($approved_application_result['customer_id']);
						$approved_application_result['user_email_id'] = $old_app_approved_by;
					}

					$explode = explode("/",$approved_application_result['customer_id']);

					$approved_office_id = $this->DmiRoOffices->find('all')->select(['ro_email_id'])->where(['short_code' => $explode[2]])->first();

					$user_posted_office_id = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email'=>$approved_office_id['ro_email_id']])->first();

					if (!empty($user_posted_office_id)) {

						$user_office_details = $this->DmiRoOffices->find('all')->select(['ro_office'])->where(['id'=>$user_posted_office_id['posted_ro_office']])->first();
						if (!empty($user_office_details)) {
							$user_office[$i] = $user_office_details['ro_office'];
						} else {
							$user_office[$i] = 'N/A';
						}

					} else {
						$user_office[$i] = 'N/A';
					}

					$application_form_type = $this->Reportsfunctions->checkApplicantFormTypeForReports($approved_application_result['customer_id']);

					if ($application_form_type == 'A')
					{
						$application_type[$i]='CA (Form-A)';

					} elseif ($application_form_type == 'B')
					{
						$application_type[$i]='Printing Press (Form-B)';
					} elseif ($application_form_type == 'C')
					{
						$application_type[$i]='Laboratory (Form-C)';
					} elseif ($application_form_type == 'D')
					{
						$application_type[$i]='Laboratory (Form-D)';
					} elseif ($application_form_type == 'E')
					{
						$application_type[$i]='CA (Form-E)';
					} elseif ($application_form_type == 'F')
					{
						$application_type[$i]='CA (Form-F)';
					}

					$date[$i] = $approved_application_result['created'];
					$application_customer_id[$i] = $approved_application_result['customer_id'];

					//added by the akash on 13-11-2021
					$firmDetails = $this->DmiFirms->find('all',array('conditions'=>array('customer_id IS'=>$approved_application_result['customer_id'])))->first();
					//added !empty condition & remove isset() by shreeya on date [06-07-2023]
					if(!empty($firmDetails)){
						$name_of_the_firm[$i] = $firmDetails['firm_name'];
						$address_of_the_firm[$i] = $firmDetails['street_address'];
						$contact_details_of_the_firm[$i] = base64_decode($firmDetails['email']);
						$phoneno[$i] = $firmDetails['mobile_no'];
					}


					//tbl details
					$tbl_details = $this->DmiAllTblsDetails->find('all',array('conditions'=>array('customer_id IS'=>$approved_application_result['customer_id'],'OR' => array('delete_status IS NULL', 'delete_status' => 'no'))))->toArray();

					if (!empty($tbl_details))
					{
						$j=0;
						foreach ($tbl_details as $each) {

							$approved_TBL_details_tbl_name[$i][$j] = $each['tbl_name'];
							$approved_TBL_details_tbl_registered_no[$i][$j] = $each['tbl_registered_no'];
							$j++;
						}

					} else {
						$approved_TBL_details_tbl_name[$i][0] = 'N/A';
						$approved_TBL_details_tbl_registered_no[$i][0] = 'N/A';
					}



					//lab details
					$lab_details = $this->DmiCustomerLaboratoryDetails->find('all',array('conditions'=>array('customer_id IS'=>$approved_application_result['customer_id'])))->toArray();
					if (!empty($lab_details))
					{
						$laboratory_details_name[$i] = $lab_details[0]['laboratory_name'];
						$laboratory_details_address[$i] = $lab_details[0]['street_address'];
					} else
					{
						$laboratory_details_name[$i] = 'N/A';
						$laboratory_details_address[$i] = 'N/A';
					}

					$commodity_value = $this->DmiFirms->find('all')->select(['sub_commodity'])->where(['customer_id'=>$approved_application_result['customer_id']])->first();

					//$commodity_list[$i] = $this->Customfunctions->showCommdityInApplList($commodity_value['sub_commodity']);

					//Added this Code on the 08-04-2022 as the base encoding was not on some emails so to dispaly the email , checking if the encoding is needed or not.
					$checkEmailForEncoding[$i] = $approved_application_result['user_email_id'];

					if($this->isBase64Encoded($checkEmailForEncoding[$i]) == true){
						$application_user_email_id[$i] = base64_decode($approved_application_result['user_email_id']);
					} else {
						$application_user_email_id[$i] = $approved_application_result['user_email_id'];
					}

					//check the expiry dateand print to the reports  added by Akash on 24-05-2022
					$grant_date = chop($esign_application_details['date'],"00:00:00");
					$valid_upto[$i] = $this->Customfunctions->getCertificateValidUptoDate($approved_application_result['customer_id'],$grant_date);

					//check the state name added by akash on 14-06-2022
					//$state_name[$i] = $this->getStateName($approved_application_result['customer_id']);

					//Certificate Issued on
					$issued_on[$i] = chop($approved_application_result['date'],"00:00:00");

					$i=$i+1;
				}
			}


		}


		$this->set('date',$date);
		$this->set('application_customer_id',$application_customer_id);
		$this->set('application_user_email_id',$application_user_email_id);
		$this->set('application_type',$application_type);
		$this->set('user_office',$user_office);
		$this->set('renewal_esign_list',$renewal_esign_list);

		$this->set('approved_application_type',$approved_application_type_text);
		$this->set('name_of_the_firm',$name_of_the_firm);
		$this->set('address_of_the_firm',$address_of_the_firm);
		$this->set('contact_details_of_the_firm',$contact_details_of_the_firm);
		$this->set('approved_TBL_details_tbl_name',$approved_TBL_details_tbl_name);
		$this->set('approved_TBL_details_tbl_registered_no',$approved_TBL_details_tbl_registered_no);
		$this->set('laboratory_details_name',$laboratory_details_name);
		$this->set('laboratory_details_address',$laboratory_details_address);
		$this->set('valid_upto',$valid_upto);
		$this->set('state_name',$state_name);
		$this->set('phoneno',$phoneno);
		$this->set('issued_on',$issued_on);

	}


	/*27-mar-2023 Yashwant*/

	// Pending Renewal Applications Report
	// Description : Start to create Pending New Application main report
	// @Author : Pravin Bhakare
	// #Contributer : Yashwant Singade
	// Date : 27-03-2023

	public function pendingNewApplicationsMainReport()
	{
		 //variable set for session
		$static_pending_from_date = '';
		$static_pending_to_date = '';
		$static_pending_roOfficeShortCode ='';
		$static_pending_ro_office_id ='';

		$application_pending_days = $this->Session->read('pending_days');

		if (!empty($application_pending_days)) {
			$report_name = 'Pending New Applications Report ( More than 15 Days)';
		} else {
			$report_name ='Pending New Applications Report';
		}

		$this->set('report_name',$report_name);

		$table = 'DmiAllApplicationsCurrentPositions';
		$pending_application_type = 'new';

		$application_type_xy = array('A'=>'CA (Form-A)', 'C'=>'Laboratory (Form-C)', 'E'=>'CA (Form-E)', 'B'=>'Printing Press (Form-B)', 'D'=>'Laboratory (Form-D)', 'F'=>'CA (Form-F)');

		//Change on 9/11/2018, Sorting array by ascending order - By Pravin Bhakare
		asort($application_type_xy);
		$this->set('application_type_xy',$application_type_xy);

		$user_roles_xy = array('RO/SO'=>'RO/SO','MO/SMO'=>'MO/SMO','IO'=>'IO','HO MO/SMO'=>'HO MO/SMO','DY.AMA'=>'DY.AMA','JT.AMA'=>'JT.AMA','AMA'=>'AMA');

		//Change on 9/11/2018, Sorting array by ascending order - By Pravin Bhakare
		asort($user_roles_xy);
		$this->set('user_roles_xy',$user_roles_xy);

		$ro_office = $this->DmiRoOffices->find('all')->where(['office_type' => 'RO','delete_status IS NULL'])->order(['ro_office' => 'ASC'])->combine('id', 'ro_office')->toArray();
		$this->set('ro_office',$ro_office);

		$search_application_type_id = $this->Session->read('search_application_type_id');   //result
		$search_user_role = $this->Session->read('search_user_role');
		$ro_office_id = $this->Session->read('ro_office_id');
		$mo_office_id = $this->Session->read('mo_office_id');
		$io_office_id = $this->Session->read('io_office_id');
		$search_from_date = $this->Session->read('search_from_date');
		$search_to_date = $this->Session->read('search_to_date');
		$search_user_email_id = $this->Session->read('search_user_email_id');

		$download_search_application_type_id = $this->Session->read('search_application_type_id');
		$download_search_user_role = $this->Session->read('search_user_role');
		$download_ro_office_id = $this->Session->read('ro_office_id');
		$download_mo_office_id = $this->Session->read('mo_office_id');
		$download_io_office_id = $this->Session->read('io_office_id');
		$download_search_from_date = $this->Session->read('search_from_date');
		$download_search_to_date = $this->Session->read('search_to_date');
		$download_search_user_email_id = $this->Session->read('search_user_email_id');

		$this->set('search_application_type_id',$search_application_type_id);
		$this->set('search_user_role',$search_user_role);
		$this->set('ro_office_id',$ro_office_id);
		$this->set('mo_office_id',$mo_office_id);
		$this->set('io_office_id',$io_office_id);
		$this->set('search_from_date',$search_from_date);
		$this->set('search_to_date',$search_to_date);
		$this->set('search_user_email_id',$search_user_email_id);

		// Set default value for download report click event (Done by pravin 14-03-2018)
		$download_report = 'no';
		 //set session variable and delete it by laxmi Bhadade on 16-02-2023
		$static_pending_from_date = $this->Session->read('from_date');
		$static_pending_to_date = $this->Session->read('to_date');
		$static_pending_roOfficeShortCode = $this->Session->read('roOfficeShortCode');
		$static_pending_ro_office_id = $this->Session->read('ro_office_id');

		//delete session
		$this->Session->delete('ro_office_id');
		$this->Session->delete('roOfficeShortCode');
		$this->Session->delete('to_date');
		$this->Session->delete('from_date');

		//Check and Pass the entry for "Search" or "Download Report as Excel" button click event (Done by pravin 14-03-2018)
		if (((!empty($static_pending_from_date) && !empty($static_pending_to_date)) || !empty($static_pending_roOfficeShortCode)) || (null != ($this->request->getData('search_logs')) || null != ($this->request->getData('download_report')))) {
			//Check not empty "Download Report as Excel" button Request, if condition TRUE then set value "yes" for "Download Report as Excel" click event
			//and pass this value to "mo_io_ro_allocation_serach_conditions" function (Done by pravin 14-03-2018)
			if (!empty($this->request->getData('download_report'))) {
				$download_report = 'yes';
			}

			$search_application_type_id = $this->request->getData('application_type');
			$search_user_role =  $this->request->getData('user_role');
			$ro_office_id =  $this->request->getData('ro_office');
			$mo_office_id =  $this->request->getData('mo_office');
			$io_office_id =  $this->request->getData('io_office');

			$search_user_email_id =  $this->request->getData('user_id');
			$search_from_date =  $this->request->getData('from_date');
			$search_from_date = $this->Customfunctions->dateFormatCheck($search_from_date);
			$search_to_date =  $this->request->getData('to_date');
			$search_to_date = $this->Customfunctions->dateFormatCheck($search_to_date);
			$this->date_comparison($search_from_date,$search_to_date);

			// Change on 2/11/2018 - For download excel report, Take search filter field value from session variables instend of POST variable - By Pravin
			if ($download_report == 'yes') {

				$search_application_type_id = $this->Session->read('search_application_type_id');
				$search_user_role = $this->Session->read('search_user_role');
				$ro_office_id = $this->Session->read('ro_office_id');
				$mo_office_id = $this->Session->read('mo_office_id');
				$io_office_id = $this->Session->read('io_office_id');
				$search_from_date = $this->Session->read('search_from_date');
				$search_to_date = $this->Session->read('search_to_date');
				$search_user_email_id = $this->Session->read('search_user_email_id');
			}

			$download_application_customer_id_list = $this->pendingNewSearchConditions($download_search_application_type_id,$download_search_user_role,$download_ro_office_id,$download_mo_office_id,$download_io_office_id,$download_search_from_date,$download_search_to_date,$download_search_user_email_id,$table,$pending_application_type,$application_pending_days);

			$this->Session->delete('search_application_type_id');
			$this->Session->delete('search_user_role');
			$this->Session->delete('ro_office_id');
			$this->Session->delete('mo_office_id');
			$this->Session->delete('io_office_id');
			$this->Session->delete('search_from_date');
			$this->Session->delete('search_to_date');
			$this->Session->delete('search_user_email_id');

			//set from_date and to_date and office name from session of statstics report added by laxmi B. on 15-02-2023
			if(!empty($static_pending_from_date) && !empty($static_pending_to_date && empty($static_pending_roOfficeShortCode) )){
				$search_from_date = $static_pending_from_date;
				$search_to_date = $static_pending_to_date;
				$search_from_date = $this->Customfunctions->dateFormatCheck($search_from_date);
				$search_to_date = $this->Customfunctions->dateFormatCheck($search_to_date);
				$this->date_comparison($search_from_date, $search_to_date);

			} elseif(!empty($static_pending_from_date) && !empty($static_pending_to_date && !empty($static_pending_roOfficeShortCode))) {
				$search_from_date = $static_pending_from_date;
				$search_to_date = $static_pending_to_date;
				$search_from_date = $this->Customfunctions->dateFormatCheck($search_from_date);
				$search_to_date = $this->Customfunctions->dateFormatCheck($search_to_date);
				$this->date_comparison($search_from_date, $search_to_date);

				$ro_office = $this->DmiRoOffices->find('all')->select(['id'])->where(['id IN' => $static_pending_ro_office_id])->where(['short_code IN'=>$static_pending_roOfficeShortCode])->first();
				$ro_office_id = [$ro_office['id']];
				$search_user_role = 'RO/SO';
			} elseif(!empty($static_pending_roOfficeShortCode)) {
				$ro_office = $this->DmiRoOffices->find('all')->select(['id'])->where(['id IN' => $static_pending_ro_office_id])->where(['short_code IN'=>$static_pending_roOfficeShortCode])->first();
				$ro_office_id = [$ro_office['id']];
				$search_user_role = 'RO/SO';
			}
			//end laxmi

			$this->Session->write('search_application_type_id',$search_application_type_id);
			$this->Session->write('search_user_role',$search_user_role);
			$this->Session->write('ro_office_id',$ro_office_id);
			$this->Session->write('mo_office_id',$mo_office_id);
			$this->Session->write('io_office_id',$io_office_id);
			$this->Session->write('search_from_date',$search_from_date);
			$this->Session->write('search_to_date',$search_to_date);
			$this->Session->write('search_user_email_id',$search_user_email_id);

			$this->set('search_application_type_id',$search_application_type_id);
			$this->set('search_user_role',$search_user_role);
			$this->set('ro_office_id',$ro_office_id);
			$this->set('mo_office_id',$mo_office_id);
			$this->set('io_office_id',$io_office_id);
			$this->set('search_from_date',$search_from_date);
			$this->set('search_to_date',$search_to_date);
			$this->set('search_user_email_id',$search_user_email_id);


			$application_customer_id_list = $this->pendingNewSearchConditions($search_application_type_id,$search_user_role,$ro_office_id,$mo_office_id,$io_office_id,$search_from_date,$search_to_date,$search_user_email_id,$table,$pending_application_type,$application_pending_days);

			if (!empty($application_customer_id_list)) {

				$current_users_details = $this->$table->find('all')->where(['customer_id IN'=>$application_customer_id_list])->order(['id' => 'DESC'])->toArray();

				//Fetch the all data that required for creating the downloading report as execel (Done by pravin 14-03-2018)
				if ($download_report == 'yes') {

					if (!empty($download_application_customer_id_list)) {
						$download_condition = ['customer_id IN' => $download_application_customer_id_list];
					} else {
						$download_condition = ['customer_id IS' => ''];
					}

					$download_pending_application = $this->$table->find('all')->where($download_condition)->order(['id' => 'DESC'])->toArray();
					$this->downloadPendingApplicationReport($download_pending_application,$pending_application_type,$table);
				}

			} else {
				$current_users_details = null;
			}

			$this->pendingApplicationReportResults($current_users_details,$pending_application_type,$tables);

		} else {

			$application_customer_id_list = $this->pendingNewSearchConditions($search_application_type_id,$search_user_role,$ro_office_id,$mo_office_id,$io_office_id,$search_from_date,$search_to_date,$search_user_email_id,$table,$pending_application_type,$application_pending_days);

			if (!empty($application_customer_id_list)) {

				$current_users_details = $this->$table->find('all')->where(['customer_id IN' => $application_customer_id_list])->order(['id' => 'DESC'])->toArray(); /*->limit(['100'])*/
				$this->set('current_users_details',$current_users_details);

				//Fetch the all data that required for creating the downloading report as execel (Done by pravin 14-03-2018)
				if ($download_report == 'yes') {
					$download_pending_application = $this->$table->find('all')->where(['customer_id' => $application_customer_id_list])->order(['id' => 'DESC'])->toArray();
					$this->downloadPendingApplicationReport($download_pending_application,$pending_application_type,$table);
				}

			} else {
				$current_users_details = null;
			}

			$this->pendingApplicationReportResults($current_users_details,$pending_application_type,$table);
		}


	}


	// Pending New Search Conditions
	// #Contributer : Yashwant Singade
	// Date : 27-03-2023

	public function pendingNewSearchConditions($search_application_type_id,$search_user_role,$ro_office_id,$mo_office_id,$io_office_id,$search_from_date,$search_to_date,$search_user_email_id,$table,$pending_application_type,$application_pending_days)
	{
		$current_date = new \DateTime(date("d-m-Y")); // Ankur updated new DateTime to new \DateTime as Class "App\Controller\DateTime" not found
		$modify_date_obj = $current_date->modify('-15 day');
		$modify_date = $modify_date_obj->format('d-m-Y H:i:s');

		if (!empty($search_from_date)) {

			$conditions = ['DATE(modified) <' => $modify_date];

			$date_conditions = ['date(modified) BETWEEN :start AND :end', 'DATE(created) <' => $modify_date];

			$date_conditions_1 = ['date(modified) BETWEEN :start AND :end', 'DATE(created) <' => $modify_date];

		} else {

			$conditions = [];

			$date_conditions = ['date(modified) BETWEEN :start AND :end'];

			$date_conditions_1 = ['date(modified) BETWEEN :start AND :end', 'DATE(created) <' => $modify_date];
		}

		$final_customer_id_list = null;

		if ($ro_office_id != '' && $search_user_role == 'RO/SO') {
			$level_1_2_3_office = $ro_office_id;
		} elseif ($mo_office_id != '' && $search_user_role == 'MO/SMO') {
			$level_1_2_3_office = $mo_office_id;
		} elseif ($io_office_id != '' && $search_user_role == 'IO') {
			$level_1_2_3_office = $io_office_id;
		} else {
			$level_1_2_3_office = '';
		}


		if ($search_application_type_id != '' && $search_user_role == '' && $search_from_date == '' && $search_to_date == '') {

			$application_customer_id = $this->$table->find('all')->where($conditions)->toArray();

			$i=0;
			foreach ($application_customer_id as $each_customer_id) {

				if (!empty($each_customer_id['customer_id'])) {
					$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

					if (in_array($application_customer_type, $search_application_type_id, TRUE)) {
						$application_customer_id_list[$i] = $each_customer_id['customer_id'];
						$i=$i+1;
					}
				}
			}

		}


		elseif (($search_application_type_id == '' || $search_application_type_id != '') && $search_user_role != '' && $level_1_2_3_office == '' && $search_from_date == '' && $search_to_date == '')
		{

			if ($search_application_type_id != '') {

				$application_customer_id = $this->$table->find('all')->where($conditions)->toArray();

				$i=0;
				foreach ($application_customer_id as $each_customer_id) {
					if (!empty($each_customer_id['customer_id'])) {
						$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

						if (in_array($application_customer_type,$search_application_type_id)) {
							$seach_application_customer_id_list[$i] = $each_customer_id['customer_id'];
							$i=$i+1;
						}
					}
				}

				$application_current_levels = $this->$table->find('all')->where(['customer_id IN' => $seach_application_customer_id_list])->toArray();
			} else {

				$application_current_levels = $this->$table->find('all')->where($conditions)->toArray();
			}

			$i=0;
			foreach ($application_current_levels as $each_current_levels) {

				$application_current_level_user_role = $this->checkUserRoleFromCurrentLevel($each_current_levels['current_level'], $each_current_levels['current_user_email_id']);

				if ($application_current_level_user_role == $search_user_role) {
					$application_customer_id_list[$i] = $each_current_levels['customer_id'];
					$i=$i+1;
				}
			}

		} elseif (($search_application_type_id == '' || $search_application_type_id != '') && $search_user_role != '' && $level_1_2_3_office != '' && $search_from_date == '' && $search_to_date == '' && $search_user_email_id =='') {

			if ($search_application_type_id != '') {

				$application_customer_id = $this->$table->find('all')->where($conditions)->toArray();

				$i=0;
				foreach ($application_customer_id as $each_customer_id) {
					if (!empty($each_customer_id['customer_id'])) {
						$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

						if (in_array($application_customer_type,$search_application_type_id)) {
							$seach_application_customer_id_list[$i] = $each_customer_id['customer_id'];
							$i=$i+1;
						}
					}
				}
				$application_current_levels = $this->$table->find('all')->where(['customer_id IN' => $seach_application_customer_id_list])->toArray();

			} else {

				$application_current_levels = $this->$table->find('all')->where($conditions);
			}

			$i=0;
			foreach ($application_current_levels as $each_current_levels) {

				$application_current_level_user_role = $this->checkUserRoleFromCurrentLevel($each_current_levels['current_level'], $each_current_levels['current_user_email_id']);

				if ($application_current_level_user_role == $search_user_role) {

					$user_posted_office_id_xy = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email IS' => $each_current_levels['current_user_email_id']])->first();

					if (!empty($user_posted_office_id_xy)) {
						$user_posted_office_id_xyx = $user_posted_office_id_xy['posted_ro_office'];
					} else {
						$user_posted_office_id_xyx = '';
					}

					if (in_array($user_posted_office_id_xyx,$level_1_2_3_office)) {
						$application_customer_id_list[$i] = $each_current_levels['customer_id'];
						$i=$i+1;
					}
				}
			}

		} elseif (($search_application_type_id == '' || $search_application_type_id != '') && $search_user_role == '' && $level_1_2_3_office == '' && $search_from_date != '' && $search_to_date != '') {

			if ($search_application_type_id != '') {

				$application_customer_id = $this->$table->find('all')->where($date_conditions)->bind(':start', $search_from_date, 'date')->bind(':end', $search_to_date, 'date')->toArray();

				$i=0;
				foreach ($application_customer_id as $each_customer_id) {
					if (!empty($each_customer_id['customer_id'])) {
						$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

						if (in_array($application_customer_type,$search_application_type_id)) {
							$application_customer_id_list[$i] = $each_customer_id['customer_id'];
							$i=$i+1;
						}
					}
				}

			} else {

				$application_customer_id_list = $this->$table->find('all')->select(['customer_id'])->where($date_conditions_1)->bind(':start', $search_from_date, 'date')->bind(':end', $search_to_date, 'date')->combine('id', 'customer_id')->toArray();
			}

		} elseif (($search_application_type_id == '' || $search_application_type_id != '') && $search_user_role != '' && $level_1_2_3_office != '' && $search_from_date == '' && $search_to_date == '' && $search_user_email_id !='') {

			if ($search_application_type_id != '') {

				$application_customer_id = $this->$table->find('all')->where($conditions)->toArray();

				$i=0;
				foreach ($application_customer_id as $each_customer_id) {

					if (!empty($each_customer_id['customer_id'])) {

						$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

						if (in_array($application_customer_type, $search_application_type_id)) {
							$seach_application_customer_id_list[$i] = $each_customer_id['customer_id'];
							$i=$i+1;
						}
					}
				}

				$application_current_levels = $this->$table->find('all')->where(['customer_id IN' => $seach_application_customer_id_list])->toArray();

			} else {

				$application_current_levels = $this->$table->find('all')->where($conditions)->toArray();
			}

			$i=0;
			foreach ($application_current_levels as $each_current_levels) {

				$application_current_level_user_role = $this->checkUserRoleFromCurrentLevel($each_current_levels['current_level'], $each_current_levels['current_user_email_id']);

				if ($application_current_level_user_role == $search_user_role) {

					$user_posted_office_id_xy = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email IS' => $each_current_levels['current_user_email_id']])->first();

					if (!empty($user_posted_office_id_xy)) {
						$user_posted_office_id_xyx = $user_posted_office_id_xy['posted_ro_office'];
					} else {
						$user_posted_office_id_xyx = '';
					}

					if (in_array($user_posted_office_id_xyx,$level_1_2_3_office)) {

						$search_user_email = $this->DmiUserRoles->find('all')->select(['user_email_id'])->where(['id IS' => $search_user_email_id])->first();

						if ($each_current_levels['current_user_email_id'] == $search_user_email['user_email_id']) {
							$application_customer_id_list[$i] = $each_current_levels['customer_id'];
							$i=$i+1;
						}
					}
				}
			}

		} elseif (($search_application_type_id == '' || $search_application_type_id != '') && $search_user_role != '' && $search_from_date != '' && $search_to_date != '' && $level_1_2_3_office !='' && ($search_user_email_id != '' || $search_user_email_id == '')) {

			if ($search_application_type_id != '') {

				$application_customer_id = $this->$table->find('all')->where($conditions)->toArray();

				$i=0;
				foreach ($application_customer_id as $each_customer_id) {

					if (!empty($each_customer_id['customer_id'])) {
						$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

						if (in_array($application_customer_type,$search_application_type_id)) {
							$seach_application_customer_id_list[$i] = $each_customer_id['customer_id'];
							$i=$i+1;
						}
					}
				}

				$application_current_levels = $this->$table->find('all')->where(['customer_id IN' => $seach_application_customer_id_list])
					->where(['date(modified) BETWEEN :start AND :end'])->bind(':start', $search_from_date, 'date')->bind(':end', $search_to_date, 'date')->toArray();
			} else {

				$application_current_levels = $this->$table->find('all')->where($date_conditions)->bind(':start', $search_from_date, 'date')
				->bind(':end', $search_to_date, 'date')->toArray();
			}

			$i=0;
			foreach ($application_current_levels as $each_current_levels) {

				$application_current_level_user_role = $this->checkUserRoleFromCurrentLevel($each_current_levels['current_level'],$each_current_levels['current_user_email_id']);

				if ($application_current_level_user_role == $search_user_role) {

					if ($level_1_2_3_office == '') {
						$level_1_2_3_office = [];
					}

					$user_posted_office_id_xy = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email IS' => $each_current_levels['current_user_email_id']])->first();

					if (!empty($user_posted_office_id_xy)) {
						$user_posted_office_id_xyx = $user_posted_office_id_xy['posted_ro_office'];
					} else {
						$user_posted_office_id_xyx = '';
					}

					if (in_array($user_posted_office_id_xyx,$level_1_2_3_office)) {

						$search_user_email = $this->DmiUserRoles->find('all')->select(['user_email_id'])->where(['id IS' => $search_user_email_id])->first();
						//to remove error empty condition added by laxmi B on 16-02-2023
						if ((!empty($each_current_levels['current_user_email_id']) && !empty($search_user_email['user_email_id']) )  && $each_current_levels['current_user_email_id'] == $search_user_email['user_email_id']) {
							$application_customer_id_list[$i] = $each_current_levels['customer_id'];
							$i=$i+1;
						}

					} else {
						$application_customer_id_list[$i] = $each_current_levels['customer_id'];
						$i=$i+1;
					}
				}
			}

		} else {
			$application_customer_id = $this->$table->find('all')->select(['customer_id'])->where($conditions)->extract('customer_id')->toArray();

			// $i=0;
			// foreach ($application_customer_id as $each_customer_id)
			// {
			// 		$application_customer_id_list[$i] = $each_customer_id['customer_id'];
			// 		$i=$i+1;
			// }

			$application_customer_id_list = $application_customer_id;
		}

		if (!empty($application_customer_id_list)) {

			$i=0;
			if ($pending_application_type == 'new') {
				foreach ($application_customer_id_list as $customer_id) {
					$customer_id_list = $this->DmiGrantCertificatesPdfs->find('all')->where(['customer_id IS' => $customer_id])->first();

					if (empty($customer_id_list)) {
						$final_customer_id_list[$i] = $customer_id;
						$i=$i+1;
					}
				}

			} elseif ($pending_application_type == 'renewal') {

				foreach ($application_customer_id_list as $customer_id ) {

					$customer_id_list = $this->DmiRenewalFinalSubmits->find('all')->where(['customer_id IS' => $customer_id, 'status' => 'approved'])->first();

					if (empty($customer_id_list)) {
						$final_customer_id_list[$i] = $customer_id;
						$i=$i+1;
					}
				}

				$final_table = 'DmiRenewalFinalSubmits';
			}


			//if data same data id exist in rejcted table it is not apper in report added by laxmi B. on 20-01-2023
			$this->loadModel('DmiRejectedApplLogs');
			$rejectedList = $this->DmiRejectedApplLogs->find('all', array('fields'=>array('customer_id')))->order(['id' => 'DESC'])->toArray();//
			$reject_id = array();
			$i=0;
			if(!empty($rejectedList)){
				foreach($rejectedList as $reject){
					$reject_id[$i] = $reject['customer_id'];
					$i++;
				}

				if(!empty($final_customer_id_list)){
					$final_customer_id_list = array_diff($final_customer_id_list, $reject_id);
				}
			}//end by laxmi b.
		}


		return $final_customer_id_list;


	}



	// Pending Renewal Applications Report
	// Description : Start to create Pending renewal application main report
	// @Author : Pravin Bhakare
	// #Contributer : Yashwant Singade
	// Date : 27-03-2023

	public function pendingRenewalApplicationsReport(){

		$application_pending_days = $this->Session->read('pending_days');

		if (!empty($application_pending_days)) {
			$report_name = 'Pending Renewal Applications Report (More than 15 Days)';
		} else {
			$report_name ='Pending Renewal Applications Report';
		}

		$this->set('report_name',$report_name);

		$table = 'DmiRenewalAllCurrentPositions';

		$pending_application_type = 'renewal';

		$application_type_xy = array('A'=>'CA (Form-A)','C'=>'Laboratory (Form-C)','E'=>'CA (Form-E)','B'=>'Printing Press (Form-B)','D'=>'Laboratory (Form-D)','F'=>'CA (Form-F)');

		//Change on 9/11/2018, Sorting array by ascending order - By Pravin Bhakare
		asort($application_type_xy);
		$this->set('application_type_xy',$application_type_xy);

		$user_roles_xy = array('RO/SO'=>'RO/SO','MO/SMO'=>'MO/SMO','IO'=>'IO');
		//Change on 9/11/2018, Sorting array by ascending order - By Pravin Bhakare
		asort($user_roles_xy);
		$this->set('user_roles_xy',$user_roles_xy);

		$ro_office = $this->DmiRoOffices->find('all')->select(['id', 'ro_office'])->where(['office_type' => 'RO','delete_status IS NULL'])->order(['ro_office' => 'ASC'])
			->combine('id', 'ro_office')->toArray();
		$this->set('ro_office',$ro_office);

		$search_application_type_id = $this->Session->read('search_application_type_id');




		$search_user_role = $this->Session->read('search_user_role');
		$ro_office_id = $this->Session->read('ro_office_id');
		$mo_office_id = $this->Session->read('mo_office_id');
		$io_office_id = $this->Session->read('io_office_id');
		$search_from_date = $this->Session->read('search_from_date');
		$search_to_date = $this->Session->read('search_to_date');
		$search_user_email_id = $this->Session->read('search_user_email_id');

		$download_search_application_type_id = $this->Session->read('search_application_type_id');
		$download_search_user_role = $this->Session->read('search_user_role');
		$download_ro_office_id = $this->Session->read('ro_office_id');
		$download_mo_office_id = $this->Session->read('mo_office_id');
		$download_io_office_id = $this->Session->read('io_office_id');
		$download_search_from_date = $this->Session->read('search_from_date');
		$download_search_to_date = $this->Session->read('search_to_date');
		$download_search_user_email_id = $this->Session->read('search_user_email_id');

		$this->set('search_application_type_id',$search_application_type_id);
		$this->set('search_user_role',$search_user_role);
		$this->set('ro_office_id',$ro_office_id);
		$this->set('mo_office_id',$mo_office_id);
		$this->set('io_office_id',$io_office_id);
		$this->set('search_from_date',$search_from_date);
		$this->set('search_to_date',$search_to_date);
		$this->set('search_user_email_id',$search_user_email_id);

		// Set default value for download report click event (Done by pravin 14-03-2018)
		$download_report = 'no';

		//Check and Pass the entry for "Search" or "Download Report as Excel" button click event (Done by pravin 14-03-2018)
		if (null != ($this->request->getData('search_logs')) || null != ($this->request->getData('download_report'))) {

			//Check not empty "Download Report as Excel" button Request, if condition TRUE then set value "yes" for "Download Report as Excel" click event
			//and pass this value to "mo_io_ro_allocation_serach_conditions" function (Done by pravin 14-03-2018)
			if (!empty($this->request->getData('download_report'))) {
				$download_report = 'yes';
			}

			$search_application_type_id = $this->request->getData('application_type');
			$search_user_role =  $this->request->getData('user_role');
			$ro_office_id =  $this->request->getData('ro_office');
			$mo_office_id =  $this->request->getData('mo_office');
			$io_office_id =  $this->request->getData('io_office');

			$search_user_email_id =  $this->request->getData('user_id');
			$search_from_date =  $this->request->getData('from_date');
			$search_from_date = $this->Customfunctions->dateFormatCheck($search_from_date);
			$search_to_date =  $this->request->getData('to_date');
			$search_to_date = $this->Customfunctions->dateFormatCheck($search_to_date);
			$this->date_comparison($search_from_date,$search_to_date);

			// Change on 2/11/2018 - For download excel report, Take search filter field value from session variables instend of POST variable - By Pravin
			if ($download_report == 'yes') {

				$search_user_role = $this->Session->read('search_user_role');
				$ro_office_id = $this->Session->read('ro_office_id');
				$mo_office_id = $this->Session->read('mo_office_id');
				$io_office_id = $this->Session->read('io_office_id');
				$search_from_date = $this->Session->read('search_from_date');
				$search_to_date = $this->Session->read('search_to_date');
				$search_user_email_id = $this->Session->read('search_user_email_id');
			}

			$download_application_customer_id_list = $this->pendingRenewalMainApplnReport($download_search_application_type_id,$download_search_user_role,$download_ro_office_id,$download_mo_office_id,$download_io_office_id,$download_search_from_date,$download_search_to_date,$download_search_user_email_id,$table,$pending_application_type,$application_pending_days);

			$this->Session->delete('search_application_type_id');
			$this->Session->delete('search_user_role');
			$this->Session->delete('ro_office_id');
			$this->Session->delete('mo_office_id');
			$this->Session->delete('io_office_id');
			$this->Session->delete('search_from_date');
			$this->Session->delete('search_to_date');
			$this->Session->delete('search_user_email_id');

			$this->Session->write('search_application_type_id',$search_application_type_id);
			$this->Session->write('search_user_role',$search_user_role);
			$this->Session->write('ro_office_id',$ro_office_id);
			$this->Session->write('mo_office_id',$mo_office_id);
			$this->Session->write('io_office_id',$io_office_id);
			$this->Session->write('search_from_date',$search_from_date);
			$this->Session->write('search_to_date',$search_to_date);
			$this->Session->write('search_user_email_id',$search_user_email_id);

			$this->set('search_application_type_id',$search_application_type_id);
			$this->set('search_user_role',$search_user_role);
			$this->set('ro_office_id',$ro_office_id);
			$this->set('mo_office_id',$mo_office_id);
			$this->set('io_office_id',$io_office_id);
			$this->set('search_from_date',$search_from_date);
			$this->set('search_to_date',$search_to_date);
			$this->set('search_user_email_id',$search_user_email_id);




			$application_customer_id_list = $this->pendingRenewalMainApplnReport($search_application_type_id,$search_user_role,$ro_office_id,$mo_office_id,$io_office_id,$search_from_date,$search_to_date,$search_user_email_id,$table,$pending_application_type,$application_pending_days,$data_id);


			if (!empty($application_customer_id_list)) {

				$current_users_details = $this->$table->find('all')->where(['customer_id IN'=>$application_customer_id_list])->order(['id' => 'DESC'])->toArray();

				//Fetch the all data that required for creating the downloading report as execel (Done by pravin 14-03-2018)
				if ($download_report == 'yes') {
					$download_pending_application = $this->$table->find('all')->where(['customer_id IN' => $download_application_customer_id_list])->order(['id' => 'DESC'])->toArray();
					$this->downloadPendingApplicationReport($download_pending_application,$pending_application_type,$table);
				}

			} else {
				$current_users_details = null;
			}

			$this->pendingApplicationReportResults($current_users_details,$pending_application_type,$table);

		} else {



			$application_customer_id_list = $this->pendingRenewalMainApplnReport($search_application_type_id,$search_user_role,$ro_office_id,$mo_office_id,$io_office_id,$search_from_date,$search_to_date,$search_user_email_id,$table,$pending_application_type,$application_pending_days);

			if (!empty($application_customer_id_list)) {

				$current_users_details = $this->$table->find('all')->where(['customer_id IN' => $application_customer_id_list])->order(['id' => 'DESC'])->toArray();

				//Fetch the all data that required for creating the downloading report as execel (Done by pravin 14-03-2018)
				if ($download_report == 'yes') {
					$download_pending_application = $this->$table->find('all')->where(['customer_id IN' => $application_customer_id_list])
						->order(['id' => 'DESC'])->toArray();
					$this->downloadPendingApplicationReport($download_pending_application,$pending_application_type,$table);
				}

			} else {
				$current_users_details = null;
			}

			$this->pendingApplicationReportResults($current_users_details,$pending_application_type,$table);

		}
	}



	// pending Renewal MainApplication Report
	// @Author : Pravin Bhakare
	// #Contributer : Yashwant Singade
	// Date : 27-03-2023

	public function pendingRenewalMainApplnReport($search_application_type_id,$search_user_role,$ro_office_id,$mo_office_id,$io_office_id,$search_from_date,$search_to_date,$search_user_email_id,$table,$pending_application_type,$application_pending_days)
	{

		$current_date = new \DateTime(date("d-m-Y")); // Ankur updated new DateTime to new \DateTime as Class "App\Controller\DateTime" not found
		$modify_date_obj = $current_date->modify('-15 day');
		$modify_date = $modify_date_obj->format('d-m-Y H:i:s');


		if (!empty($application_pending_days))
		{
			$conditions = ['DATE(modified) <' => $modify_date];

			$date_conditions = ['date(modified) BETWEEN :start AND :end', 'DATE(created) <' => $modify_date];

			$date_conditions_1 = ['date(modified) BETWEEN :start AND :end', 'DATE(created) <' => $modify_date];

		} else {

			$conditions = [];

			$date_conditions = ['date(modified) BETWEEN :start AND :end'];

			$date_conditions_1 = ['date(modified) BETWEEN :start AND :end', 'DATE(created) <' => $modify_date];
		}

		$final_customer_id_list = null;

		if ($ro_office_id != '' && $search_user_role == 'RO/SO')
		{
			$level_1_2_3_office = $ro_office_id;

		} elseif ($mo_office_id != '' && $search_user_role == 'MO/SMO')
		{
			$level_1_2_3_office = $mo_office_id;
		}
		elseif ($io_office_id != '' && $search_user_role == 'IO')
		{
			$level_1_2_3_office = $io_office_id;
		} else
		{
			$level_1_2_3_office = '';
		}


		if ($search_application_type_id != '' && $search_user_role == '' && $search_from_date == '' && $search_to_date == '')
		{

			$application_customer_id = $this->$table->find('all')->where($conditions)->toArray();

			$i=0;
			foreach ($application_customer_id as $each_customer_id)
			{

				if (!empty($each_customer_id['customer_id']))
				{
					$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

					if (in_array($application_customer_type, $search_application_type_id, TRUE)) {
						$application_customer_id_list[$i] = $each_customer_id['customer_id'];
						$i=$i+1;
					}
				}
			}
		}

		elseif (($search_application_type_id == '' || $search_application_type_id != '') && $search_user_role != '' && $level_1_2_3_office == '' && $search_from_date == '' && $search_to_date == '')
		{

			if ($search_application_type_id != '')
			{

				$application_customer_id = $this->$table->find('all')->where($conditions)->toArray();

				$i=0;
				foreach ($application_customer_id as $each_customer_id) {
					if (!empty($each_customer_id['customer_id'])) {
						$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

						if (in_array($application_customer_type,$search_application_type_id)) {
							$seach_application_customer_id_list[$i] = $each_customer_id['customer_id'];
							$i=$i+1;
						}
					}
				}

				$application_current_levels = $this->$table->find('all')->where(['customer_id IN' => $seach_application_customer_id_list])->toArray();
			} else
			{

				$application_current_levels = $this->$table->find('all')->where($conditions)->toArray();
			}

			$i=0;
			foreach ($application_current_levels as $each_current_levels) {

				$application_current_level_user_role = $this->checkUserRoleFromCurrentLevel($each_current_levels['current_level'], $each_current_levels['current_user_email_id']);

				if ($application_current_level_user_role == $search_user_role) {
					$application_customer_id_list[$i] = $each_current_levels['customer_id'];
					$i=$i+1;
				}
			}

		} elseif (($search_application_type_id == '' || $search_application_type_id != '') && $search_user_role != '' && $level_1_2_3_office != '' && $search_from_date == '' && $search_to_date == '' && $search_user_email_id =='')
		{

			if ($search_application_type_id != '')
			{

				$application_customer_id = $this->$table->find('all')->where($conditions)->toArray();

				$i=0;
				foreach ($application_customer_id as $each_customer_id) {
					if (!empty($each_customer_id['customer_id'])) {
						$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

						if (in_array($application_customer_type,$search_application_type_id)) {
							$seach_application_customer_id_list[$i] = $each_customer_id['customer_id'];
							$i=$i+1;
						}
					}
				}
				$application_current_levels = $this->$table->find('all')->where(['customer_id IN' => $seach_application_customer_id_list])->toArray();

			} else {

				$application_current_levels = $this->$table->find('all')->where($conditions);
			}

			$i=0;
			foreach ($application_current_levels as $each_current_levels)
			{

				$application_current_level_user_role = $this->checkUserRoleFromCurrentLevel($each_current_levels['current_level'], $each_current_levels['current_user_email_id']);

				if ($application_current_level_user_role == $search_user_role) {

					$user_posted_office_id_xy = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email IS' => $each_current_levels['current_user_email_id']])->first();


					if (!empty($user_posted_office_id_xy)) {
						$user_posted_office_id_xyx = $user_posted_office_id_xy['posted_ro_office'];
					} else {
						$user_posted_office_id_xyx = '';
					}

					if (in_array($user_posted_office_id_xyx,$level_1_2_3_office)) {
						$application_customer_id_list[$i] = $each_current_levels['customer_id'];
						$i=$i+1;
					}
				}
			}

		} elseif (($search_application_type_id == '' || $search_application_type_id != '') && $search_user_role == '' && $level_1_2_3_office == '' && $search_from_date != '' && $search_to_date != '')
		{

			if ($search_application_type_id != '') {

				$application_customer_id = $this->$table->find('all')->where($date_conditions)->bind(':start', $search_from_date, 'date')->bind(':end', $search_to_date, 'date')->toArray();

				$i=0;
				foreach ($application_customer_id as $each_customer_id) {
					if (!empty($each_customer_id['customer_id'])) {
						$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

						if (in_array($application_customer_type,$search_application_type_id)) {
							$application_customer_id_list[$i] = $each_customer_id['customer_id'];
							$i=$i+1;
						}
					}
				}

			}
			else
			{

				$application_customer_id_list = $this->$table->find('all')->select(['customer_id'])->where($date_conditions_1)->bind(':start', $search_from_date, 'date')->bind(':end', $search_to_date, 'date')->combine('id', 'customer_id')->toArray();
			}

		} elseif (($search_application_type_id == '' || $search_application_type_id != '') && $search_user_role != '' && $level_1_2_3_office != '' && $search_from_date == '' && $search_to_date == '' && $search_user_email_id !='')
		{

			if ($search_application_type_id != '') {

				$application_customer_id = $this->$table->find('all')->where($conditions)->toArray();

				$i=0;
				foreach ($application_customer_id as $each_customer_id) {

					if (!empty($each_customer_id['customer_id'])) {

						$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

						if (in_array($application_customer_type, $search_application_type_id)) {
							$seach_application_customer_id_list[$i] = $each_customer_id['customer_id'];
							$i=$i+1;
						}
					}
				}

				$application_current_levels = $this->$table->find('all')->where(['customer_id IN' => $seach_application_customer_id_list])->toArray();

			} else {

				$application_current_levels = $this->$table->find('all')->where($conditions)->toArray();
			}

			$i=0;
			foreach ($application_current_levels as $each_current_levels) {

				$application_current_level_user_role = $this->checkUserRoleFromCurrentLevel($each_current_levels['current_level'], $each_current_levels['current_user_email_id']);

				if ($application_current_level_user_role == $search_user_role) {

					$user_posted_office_id_xy = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email IS' => $each_current_levels['current_user_email_id']])->first();

					if (!empty($user_posted_office_id_xy)) {
						$user_posted_office_id_xyx = $user_posted_office_id_xy['posted_ro_office'];
					} else {
						$user_posted_office_id_xyx = '';
					}

					if (in_array($user_posted_office_id_xyx,$level_1_2_3_office)) {

						$search_user_email = $this->DmiUserRoles->find('all')->select(['user_email_id'])->where(['id IS' => $search_user_email_id])->first();

						if ($each_current_levels['current_user_email_id'] == $search_user_email['user_email_id']) {
							$application_customer_id_list[$i] = $each_current_levels['customer_id'];
							$i=$i+1;
						}
					}
				}
			}

		} elseif (($search_application_type_id == '' || $search_application_type_id != '') && $search_user_role != '' && $search_from_date != '' && $search_to_date != '' && $level_1_2_3_office !='' && ($search_user_email_id != '' || $search_user_email_id == ''))
		{

			if ($search_application_type_id != '') {

				$application_customer_id = $this->$table->find('all')->where($conditions)->toArray();

				$i=0;
				foreach ($application_customer_id as $each_customer_id) {

					if (!empty($each_customer_id['customer_id'])) {
						$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

						if (in_array($application_customer_type,$search_application_type_id)) {
							$seach_application_customer_id_list[$i] = $each_customer_id['customer_id'];
							$i=$i+1;
						}
					}
				}

				$application_current_levels = $this->$table->find('all')->where(['customer_id IN' => $seach_application_customer_id_list])
					->where(['date(modified) BETWEEN :start AND :end'])->bind(':start', $search_from_date, 'date')->bind(':end', $search_to_date, 'date')->toArray();
			} else {

				$application_current_levels = $this->$table->find('all')->where($date_conditions)->bind(':start', $search_from_date, 'date')
				->bind(':end', $search_to_date, 'date')->toArray();
			}

			$i=0;
			foreach ($application_current_levels as $each_current_levels)
			{

				$application_current_level_user_role = $this->checkUserRoleFromCurrentLevel($each_current_levels['current_level'],$each_current_levels['current_user_email_id']);

				if ($application_current_level_user_role == $search_user_role) {

					if ($level_1_2_3_office == '') {
						$level_1_2_3_office = [];
					}

					$user_posted_office_id_xy = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email IS' => $each_current_levels['current_user_email_id']])->first();

					if (!empty($user_posted_office_id_xy)) {
						$user_posted_office_id_xyx = $user_posted_office_id_xy['posted_ro_office'];
					} else {
						$user_posted_office_id_xyx = '';
					}

					if (in_array($user_posted_office_id_xyx,$level_1_2_3_office))
					{

						$search_user_email = $this->DmiUserRoles->find('all')->select(['user_email_id'])->where(['id IS' => $search_user_email_id])->first();
						//to remove error empty condition added by laxmi B on 16-02-2023
						if ((!empty($each_current_levels['current_user_email_id']) && !empty($search_user_email['user_email_id']) )  && $each_current_levels['current_user_email_id'] == $search_user_email['user_email_id'])
						{
							$application_customer_id_list[$i] = $each_current_levels['customer_id'];
							$i=$i+1;
						}

					}
					else
					{
						$application_customer_id_list[$i] = $each_current_levels['customer_id'];
						$i=$i+1;
					}
				}
			}

		} else
		{
			$application_customer_id = $this->$table->find('all')->select(['customer_id'])->where($conditions)->extract('customer_id')->toArray();

			// replaced foreach with query by Ankur
			$application_customer_id_list = $application_customer_id;
		}

		if (!empty($application_customer_id_list))
		{

			$i=0;

			if ($pending_application_type == 'new')
			{
				foreach ($application_customer_id_list as $customer_id)
				{
					$customer_id_list = $this->DmiGrantCertificatesPdfs->find('all')->where(['customer_id IS' => $customer_id])->first();

					if (empty($customer_id_list))
					{
						$final_customer_id_list[$i] = $customer_id;
						$i=$i+1;
					}
				}
			}

			elseif ($pending_application_type == 'renewal')
			{
				foreach ($application_customer_id_list as $customer_id )
				{

					$customer_id_list = $this->DmiRenewalFinalSubmits->find('all')->where(['customer_id IS' => $customer_id, 'status' => 'approved'])->first();


					if (empty($customer_id_list))
					{
						$final_customer_id_list[$i] = $customer_id;
						$i=$i+1;
						//echo"<pre>";print_r($final_customer_id_list);
					}
				}

				$final_table = 'DmiRenewalFinalSubmits';
			}

		}
		return $final_customer_id_list;


	}




	/*This function is uesd for Approved New & OLd applications Main report Yashwant-27-03-2023*/
	// Approved Applications Report
	// Description : ----
	// @Author : Pravin Bhakare
	// #Contributer : Yashwant
	// Date : 27-03-2023

	public function approvedNewAndOldApplicationType()
	{

		$aqcms_from_date ='' ;
		$aqcms_to_date = '';
		$aqcms_ro_office_short_code = '';
		$aqcms_ro_office_id = '';
		$approved_application_type = $this->Session->read('approved_application_type');

		if ($approved_application_type == 'new' || $approved_application_type =='') {

			$table = 'DmiFinalSubmits';
			$report_heading = 'Approved New Applications Report';

		} elseif ($approved_application_type == 'renewal') {

			$table = 'DmiRenewalFinalSubmits';
			$report_heading = 'Approved Renewal Applications Report';

		}elseif ($approved_application_type == 'all_reports') {

			$table = 'DmiGrantCertificatesPdfs';
			$report_heading = 'All Approved Report';

			// this below code is added to show the deafult office by Akash on 16-06-2022
			$posted_ro_office = $this->DmiUsers->find('all',array('fields'=>'posted_ro_office', 'conditions'=>array('email IS'=>$_SESSION['username'])))->first();
			$default_ro_office = $this->DmiRoOffices->find('all',array('conditions'=>array('id IS'=>$posted_ro_office['posted_ro_office'])))->first();
			$this->set('default_ro_office',$default_ro_office['ro_office']);
		}

		$this->set('table', $table);	// set table value ( Done by pravin 16-07-2018)
		$this->set('report_heading', $report_heading);

		$application_type_xy = array('A'=>'CA (Form-A)','C'=>'Laboratory (Form-C)','E'=>'CA (Form-E)','B'=>'Printing Press (Form-B)','D'=>'Laboratory (Form-D)','F'=>'CA (Form-F)');

		//Change on 9/11/2018, Sorting array by ascending order - By Pravin Bhakare
		asort($application_type_xy);
		$this->set('application_type_xy', $application_type_xy);

		//added 'office_type'=>'RO' condition on 27-07-2018   // Change on 3/11/2018 -  add order by condition - by Pravin Bhakare
		$ro_office = $this->DmiRoOffices->find('all')->select(['id', 'ro_office'])->where(['office_type' => 'RO','delete_status IS NULL'])->order(['ro_office' => 'ASC'])->combine('id', 'ro_office')->toArray();
		$this->set('ro_office',$ro_office);

		$search_application_type_id = $this->Session->read('search_application_type_id');
		$application_approved_office = $this->Session->read('application_approved_office');
		$search_from_date = $this->Session->read('search_from_date');
		$search_to_date = $this->Session->read('search_to_date');

		$this->set('search_application_type_id',$search_application_type_id);
		$this->set('application_approved_office',$application_approved_office);
		$this->set('search_from_date',$search_from_date);
		$this->set('search_to_date',$search_to_date);

		$search_flag = 'off'; // added by Ankur
		// Set default value for download report click event (Done by pravin 13-03-2018)
		$download_report = 'no';

		//if from to date and office id in  session condtion added by laxmi B on 15-02-2023

			$aqcms_from_date = $this->Session->read('from_date') ;
			$aqcms_to_date = $this->Session->read('to_date');
			$aqcms_ro_office_short_code = $this->Session->read('roOfficeShortCode');
			$aqcms_ro_office_id = $this->Session->read('ro_office_id');
			$this->Session->delete('ro_office_id');
			$this->Session->delete('roOfficeShortCode');
			$this->Session->delete('from_date');
			$this->Session->delete('to_date');



		if ((((!empty($aqcms_from_date && !empty($aqcms_to_date))) || !empty($aqcms_ro_office_short_code))) || null != ($this->request->getData('search_logs')) || null != ($this->request->getData('download_report'))) {
			$search_flag = 'on'; // added by Ankur
			//Check not empty "Download Report as Excel" button Request, if condition TRUE then set value "yes" for "Download Report as Excel" click event
			//and pass this value to "approved_application_search_conditions" function (Done by pravin 13-03-2018)
			if (!empty($this->request->getData('download_report'))) {
				$download_report = 'yes';
			}

			$search_application_type_id = $this->request->getData('application_type');
			$application_approved_office = $this->request->getData('office');
			$search_from_date =  $this->request->getData('from_date');
			$search_from_date = $this->Customfunctions->dateFormatCheck($search_from_date);
			$search_to_date =  $this->request->getData('to_date');
			$search_to_date = $this->Customfunctions->dateFormatCheck($search_to_date);
			$this->date_comparison($search_from_date, $search_to_date);

			// Change on 3/11/2018 - For download excel report, Take search filter field value from session variables instend of POST variable - By Pravin
			if ($download_report == 'yes') {

				$search_application_type_id = $this->Session->read('search_application_type_id');
				$application_approved_office = $this->Session->read('application_approved_office');
				$search_from_date = $this->Session->read('search_from_date');
				$search_to_date = $this->Session->read('search_to_date');
			}

			$this->Session->delete('search_application_type_id');
			$this->Session->delete('application_approved_office');
			$this->Session->delete('search_from_date');
			$this->Session->delete('search_to_date');



			//set from_date and to_date and office name from session of statstics report added by laxmi B. on 15-02-2023
			if(!empty($aqcms_from_date) && !empty($aqcms_to_date && empty($aqcms_ro_office_short_code) )){
				$search_from_date = $aqcms_from_date;
				$search_to_date = $aqcms_to_date;
				$search_from_date = $this->Customfunctions->dateFormatCheck($search_from_date);
				$search_to_date = $this->Customfunctions->dateFormatCheck($search_to_date);
				$this->date_comparison($search_from_date, $search_to_date);

			}elseif(!empty($aqcms_from_date) && !empty($aqcms_to_date && !empty($aqcms_ro_office_short_code))){


				$search_from_date = $aqcms_from_date;
				$search_to_date = $aqcms_to_date;
				$search_from_date = $this->Customfunctions->dateFormatCheck($search_from_date);
				$search_to_date = $this->Customfunctions->dateFormatCheck($search_to_date);
				$this->date_comparison($search_from_date, $search_to_date);

				$ro_office = $this->DmiRoOffices->find('all')->select(['id'])->where(['id IN' => $aqcms_ro_office_id])->where(['short_code IN'=>$aqcms_ro_office_short_code])->first();
				$application_approved_office = [$ro_office['id']];


			} elseif(!empty($aqcms_ro_office_short_code)){
				$ro_office = $this->DmiRoOffices->find('all')->select(['id'])->where(['id IN' => $aqcms_ro_office_id])->where(['short_code IN'=>$aqcms_ro_office_short_code])->first();
				$application_approved_office = [$ro_office['id']];
			}//end


			$this->Session->write('search_application_type_id', $search_application_type_id);
			$this->Session->write('application_approved_office', $application_approved_office);
			$this->Session->write('search_from_date', $search_from_date);
			$this->Session->write('search_to_date', $search_to_date);

			$this->set('search_application_type_id', $search_application_type_id);
			$this->set('application_approved_office', $application_approved_office);
			$this->set('search_from_date', $search_from_date);
			$this->set('search_to_date', $search_to_date);

			$approved_application_lists = $this->approvedNewOLdAppliSearchCondin($search_application_type_id, $application_approved_office, $search_from_date, $search_to_date, $table, $search_flag,$data_id,$approved_application_type);
			$approved_application_list = $approved_application_lists[0];
			$download_approved_application_list = $approved_application_lists[1];

			$i=0;
			foreach ($approved_application_list as $each) {

				$approved_application_list[$i] = $each['customer_id'];
				$i=$i+1;
			}

			$j=0;
			foreach ($download_approved_application_list as $each) {

				$download_approved_application_list[$j] = $each['customer_id'];
				$j=$j+1;
			}
			//if data same data id exist in rejcted table it is not apper in report added by laxmi B. on 20-01-2023

			$this->loadModel('DmiRejectedApplLogs');
			$rejectedList = $this->DmiRejectedApplLogs->find('all')->select(['id','customer_id'])->order(['id','customer_id'])->combine('id','customer_id')->toArray();

			if(!empty($rejectedList)){
				if(!empty($approved_application_list)){
					$approved_application_list = array_diff($approved_application_list, $rejectedList);
				}
			}//end laxmi B.


			//Fetch the all data that required for creating the downloading report as execel (Done by pravin 14-03-2018)
			if ($download_report == 'yes') {

				$this->downloadApprovedApplicationReportResults($download_approved_application_list, $approved_application_type);
			}

			$this->approvedApplicationReportResults($approved_application_list, $approved_application_type);

		} else {

			$approved_application_lists = $this->approvedNewOLdAppliSearchCondin($search_application_type_id, $application_approved_office, $search_from_date, $search_to_date, $table, $search_flag,$approved_application_type);


			$approved_application_list = $approved_application_lists[0];

			$i=0;
			foreach ($approved_application_list as $each) {
				$approved_application_list[$i] = $each['customer_id'];
				$i=$i+1;
			}
			 //if data same data id exist in rejcted table it is not apper in report added by laxmi B. on 20-01-2023
			$this->loadModel('DmiRejectedApplLogs');
			$rejectedList = $this->DmiRejectedApplLogs->find('all')->select(['id','customer_id'])->order(['id','customer_id'])->combine('id','customer_id')->toArray();

			if(!empty($rejectedList)){

				if(!empty($approved_application_list)){
					$approved_application_list = array_diff($approved_application_list, $rejectedList);
				}
			}//end laxmi B.

			$this->approvedApplicationReportResults($approved_application_list,$approved_application_type);
		}

	}


	// Approved OLD / NEW Application Search Conditions
	// Description : ----
	// @Author : Pravin Bhakare
	// #Contributer : Ankur Jangid (Migration)
	// Date : ----

	public function approvedNewOLdAppliSearchCondin ($search_application_type_id, $application_approved_office, $search_from_date, $search_to_date, $table, $search_flag,$approved_application_type) {

		$approved_application_list = [];

		if ($search_application_type_id != '' && $application_approved_office == '' && $search_from_date =='' && $search_to_date == '') {

			if ($table == 'DmiFinalSubmits') {
				$approved_application_customer_id = $this->DmiGrantCertificatesPdfs->find('all')->select(['customer_id'])->group(['customer_id HAVING COUNT(customer_id) < 2'])->toArray();
			} elseif ($table == 'DmiGrantCertificatesPdfs') {
				$approved_application_customer_id = $this->DmiGrantCertificatesPdfs->find('all')->select(['customer_id'])->group(['customer_id HAVING COUNT(customer_id) >= 1'])->toArray();
			} else {
				$approved_application_customer_id = $this->DmiGrantCertificatesPdfs->find('all')->select(['customer_id'])->group(['customer_id HAVING COUNT(customer_id) > 1'])->toArray();
			}

			$i=0;
			foreach ($approved_application_customer_id as $each_customer_id)	{
				$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

				if (in_array($application_customer_type, $search_application_type_id)) {
					$approved_application_list[$i] = $each_customer_id['customer_id'];
					$i=$i+1;
				}
			}

			if (!empty($approved_application_list)) {
				$conditions = ['customer_id IN' => $approved_application_list, 'status' => 'approved', 'current_level' => 'level_3'];
			} else {
				$conditions = ['customer_id IS' => '', 'status' => 'approved', 'current_level' => 'level_3'];
			}

			$approved_application_list = $this->$table->find('all')->where($conditions)->order(['id' => 'DESC'])->toArray();

			$download_approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

		}



		elseif ($search_application_type_id != '' && $application_approved_office != '' && $search_from_date =='' && $search_to_date == '' )
		{

			$approved_application_customer_id = $this->$table->find('all')->select(['customer_id'])->where(['status' => 'approved', 'current_level' => 'level_3'])->toArray();

			$i=0;
			foreach ($approved_application_customer_id as $each_customer_id) {

				$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

				if (in_array($application_customer_type,$search_application_type_id)) {

					$approved_application_details_list = $this->DmiGrantCertificatesPdfs->find('all')->select(['id', 'id'])->where(['customer_id' => $each_customer_id['customer_id']])->combine('id', 'id')->toArray();

					if (!empty($approved_application_details_list)) {

						$approved_application_details = $this->DmiGrantCertificatesPdfs->find('all')->where(['id' => max($approved_application_details_list)])->first();

						$user_posted_office_id = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email' => $approved_application_details['user_email_id']])->first();

						if (!empty($user_posted_office_id)) {

							if (in_array($user_posted_office_id['posted_ro_office'],$application_approved_office)) {

								$approved_application_list[$i] = $each_customer_id['customer_id'];
								$i=$i+1;
							}
						}
					}
				}
			}

			// below if-else code added by Ankur Jangid
			if (!empty($approved_application_list)) {
				$conditions = ['customer_id IN' => $approved_application_list, 'status' => 'approved', 'current_level' => 'level_3'];
			} else {
				$conditions = ['customer_id IS' => '', 'status' => 'approved', 'current_level' => 'level_3'];
			}

			$approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

			$download_approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();


		} elseif ($search_application_type_id != '' && $application_approved_office == '' && $search_from_date !='' && $search_to_date != '') {

			$approved_application_customer_id = $this->$table->find('all')->select(['customer_id'])->where(['date(modified) BETWEEN :start AND :end'])
			->where(['status' => 'approved', 'current_level' => 'level_3'])->bind(':start', $search_from_date, 'date')->bind(':end', $search_to_date, 'date')->toArray();

			$i=0;
			foreach ($approved_application_customer_id as $each_customer_id) {
				$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

				if (in_array($application_customer_type, $search_application_type_id)) {
					$approved_application_list[$i] = $each_customer_id['customer_id'];
					$i=$i+1;
				}
			}

			// below if-else code added by Ankur Jangid
			if (!empty($approved_application_list)) {
				$conditions = ['customer_id IN' => $approved_application_list, 'status' => 'approved', 'current_level' => 'level_3'];
			} else {
				$conditions = ['customer_id IS' => '', 'status' => 'approved', 'current_level' => 'level_3'];
			}

			$approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

			$download_approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

		} elseif ($search_application_type_id != '' && $application_approved_office != '' && $search_from_date !='' && $search_to_date != '') {

			$approved_application_customer_id = $this->$table->find('all')->select(['customer_id'])->where(['date(modified) BETWEEN :start AND :end'])
				->where(['status' => 'approved', 'current_level' => 'level_3'])->bind(':start', $search_from_date, 'date')->bind(':end', $search_to_date, 'date')->toArray();

			$i=0;
			foreach ($approved_application_customer_id as $each_customer_id) {

				$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_customer_id['customer_id']);

				if (in_array($application_customer_type,$search_application_type_id)) {

					$approved_application_details = $this->DmiGrantCertificatesPdfs->find('all')->where(['customer_id' => $each_customer_id['customer_id']])->first();

					$user_posted_office_id = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email IS' => $approved_application_details['user_email_id']])->first();

					if (!empty($user_posted_office_id)) {

						if (in_array($user_posted_office_id['posted_ro_office'], $application_approved_office)) {
							$approved_application_list[$i] = $each_customer_id['customer_id'];
							$i=$i+1;
						}
					}
				}
			}

			// below if-else code added by Ankur Jangid
			if (!empty($approved_application_list)) {
				$conditions = ['customer_id IN' => $approved_application_list, 'status' => 'approved', 'current_level' => 'level_3'];
			} else {
				$conditions = ['customer_id IS' => '', 'status' => 'approved', 'current_level' => 'level_3'];
			}

			$approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

			$download_approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

		} elseif ($search_application_type_id == '' && $application_approved_office != '' && $search_from_date =='' && $search_to_date == '')
		{

			$approved_application_customer_id = $this->$table->find('all')->select(['customer_id'])->where(['status' => 'approved', 'current_level' => 'level_3'])->toArray();

			$i=0;
			foreach ($approved_application_customer_id as $each_customer_id) {

				$approved_application_details_list = $this->DmiGrantCertificatesPdfs->find('all')->select(['id', 'id'])->where(['customer_id IS' => $each_customer_id['customer_id']])->combine('id', 'id')->toArray();

				if (!empty($approved_application_details_list)) {

					$approved_application_details = $this->DmiGrantCertificatesPdfs->find('all')->where(['id' => max($approved_application_details_list)])->first();

					$user_posted_office_id = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email' => $approved_application_details['user_email_id']])->first();

					if (!empty($user_posted_office_id)) {

						if (in_array($user_posted_office_id['posted_ro_office'],$application_approved_office)) {
							$approved_application_list[$i] = $each_customer_id['customer_id'];
							$i=$i+1;
						}
					}
				}
			}

			// below if-else code added by Ankur Jangid
			if (!empty($approved_application_list)) {
				$conditions = ['customer_id IN' => $approved_application_list, 'status' => 'approved', 'current_level' => 'level_3'];
			} else {
				$conditions = ['customer_id IS' => '', 'status' => 'approved', 'current_level' => 'level_3'];
			}

			$approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

			$download_approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

		} elseif ($search_application_type_id == '' && $application_approved_office != '' && $search_from_date !='' && $search_to_date != '') {

			$approved_application_customer_id = $this->$table->find('all')->select(['customer_id'])->where(['date(modified) BETWEEN :start AND :end', 'status' => 'approved',
				'current_level' => 'level_3'])->bind(':start', $search_from_date, 'date')->bind(':end', $search_to_date, 'date')->toArray();

			$i=0;
			foreach ($approved_application_customer_id as $each_customer_id) {

				$approved_application_details_list = $this->DmiGrantCertificatesPdfs->find('all')->select(['id', 'id'])->where(['customer_id' => $each_customer_id['customer_id']])->combine('id', 'id')->toArray();

				if (!empty($approved_application_details_list)) {

					$approved_application_details = $this->DmiGrantCertificatesPdfs->find('all')->where(['id' => max($approved_application_details_list)])->first();

					$user_posted_office_id = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email' => $approved_application_details['user_email_id']])->first();

					if (!empty($user_posted_office_id)) {

						if (in_array($user_posted_office_id['posted_ro_office'], $application_approved_office)) {
							$approved_application_list[$i] = $each_customer_id['customer_id'];
							$i=$i+1;
						}
					}
				}
			}

			// below if-else code added by Ankur Jangid
			if (!empty($approved_application_list)) {
				$conditions = ['customer_id IN' => $approved_application_list, 'status' => 'approved', 'current_level' => 'level_3'];
			} else {
				$conditions = ['customer_id IS' => '', 'status' => 'approved', 'current_level' => 'level_3'];
			}

			$approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

			$download_approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

		} elseif ($search_application_type_id == '' && $application_approved_office == '' && $search_from_date !='' && $search_to_date != '') {

			$approved_application_customer_id = $this->$table->find('all')->select(['customer_id'])->where(['date(modified) BETWEEN :start AND :end', 'status' => 'approved',
				'current_level' => 'level_3'])->bind(':start', $search_from_date, 'date')->bind(':end', $search_to_date, 'date')->toArray();

			$i=0;
			foreach ($approved_application_customer_id as $each_customer_id) {
				$approved_application_list[$i] = $each_customer_id['customer_id'];
				$i=$i+1;
			}

			// below if-else code added by Ankur Jangid
			if (!empty($approved_application_list)) {
				$conditions = ['customer_id IN' => $approved_application_list, 'status' => 'approved', 'current_level' => 'level_3'];
			} else {
				$conditions = ['customer_id IS' => '', 'status' => 'approved', 'current_level' => 'level_3'];
			}

			$approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

			$download_approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

		} else {

			if ($search_flag == 'on') {
				$approved_application_customer_id = $this->$table->find('all')->select(['customer_id'])->where(['status' => 'approved', 'current_level' => 'level_3'])
				->order(['created'=>'DESC'])->extract('customer_id')->toArray(0);
			} else {

				// THIS BELOW CONDITION IS ADDED FOR THE ALL REPORTS BY AKASH ON 16-06-2022
				if ($table == 'DmiGrantCertificatesPdfs') {

					$posted_ro_office = $this->DmiUsers->find('all',array('fields'=>'posted_ro_office', 'conditions'=>array('email IS'=>$_SESSION['username'])))->first();
					$get_short_code = $this->DmiRoOffices->find('all',array('fields'=>'short_code', 'conditions'=>array('id IS'=>$posted_ro_office['posted_ro_office'])))->first();
					$short_code = $get_short_code['short_code'];

					if ($_SESSION['role'] == 'Head Office') {
						//$approved_application_customer_id = $this->DmiGrantCertificatesPdfs->find('all',array('fields'=>array('customer_id'),'group'=>array('customer_id having count(customer_id) >= 1'),'having'=>array('count(customer_id) >= 1')))->toArray();
						$approved_application_customer_id = $this->DmiGrantCertificatesPdfs->find('all')->select(['customer_id'])->group(['customer_id HAVING COUNT(customer_id) >= 1'])->limit(['2'])->extract('customer_id')->toArray(0);
					} else {
						$approved_application_customer_id = $this->DmiGrantCertificatesPdfs->find('all',array('fields'=>'customer_id','conditions'=>array('customer_id like'=>'%/'.$short_code.'/%'),'group'=>'customer_id having count(customer_id) >= 1','having'=>array('count(customer_id) >= 1')))->toArray();
					}

				} else {
					$approved_application_customer_id = $this->$table->find('all')->select(['customer_id'])->where(['status' => 'approved', 'current_level' => 'level_3'])
					->order(['created'=>'DESC'])->extract('customer_id')->toArray(0);
				}

			}

			$approved_application_list = $approved_application_customer_id;

			// below if-else code added by Ankur Jangid
			if (!empty($approved_application_list)) {

				// THIS BELOW CONDITION IS ADDED FOR THE ALL REPORTS BY AKASH ON 16-06-2022
				if ($table == 'DmiGrantCertificatesPdfs') {
					$conditions = array('customer_id IN'=>$approved_application_list);
				} else {
					$conditions = ['customer_id IN' => $approved_application_list, 'status' => 'approved', 'current_level' => 'level_3'];
				}

			} else {
				$conditions = ['customer_id IS' => '', 'status' => 'approved', 'current_level' => 'level_3'];
			}


			$approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();

			$download_approved_application_list = $this->$table->find('all')->select(['customer_id'])->where($conditions)->order(['id' => 'DESC'])->toArray();
		}

		return array($approved_application_list,$download_approved_application_list);

	}


	/*=================== This Function Used for AQCMS MPR Report Added By Shreeya [26-06-2023] ===========*/
	public function pendingMprReport($applicn_type) {


		$application_pending_days = $this->Session->read('pending_days');

		$applicn_type=base64_decode($applicn_type);

		$this->loadModel('DmiApplicationTypes');
		//show the all application type added by shreeya on date 13-06-2023
		$application_type_data = $this->DmiApplicationTypes->find('all')->select(['id', 'application_type'])->where(['delete_status IS NULL','application_type'=>$applicn_type])->toArray();
		$report_for = ''; // Initialize $report_for with a default value
		if(!empty($application_type_data)){
			$report_for = $application_type_data[0]['id'];
		}
		$this->set('report_for',$report_for);


		//get application id array
		//$application_array = $this->DmiApplicationTypes->find('all')->select(['id', 'application_type'])->where(['delete_status IS NULL'])->order(['id'])->combine('id', 'application_type')->toArray();
		//$application_key = array_keys($application_array);

		if (!empty($application_pending_days)) {
			$report_name = 'AQCMS Pendency MPR';
		} else {
			$report_name ='AQCMS Pendency MPR';
		}

		$this->set('report_name',$report_name);

		$table = 'DmiAllApplicationsCurrentPositions';
		$pending_application_type = null ;


		$application_type_xy = array('A'=>'CA (Form-A)', 'C'=>'Laboratory (Form-C)', 'E'=>'CA (Form-E)', 'B'=>'Printing Press (Form-B)', 'D'=>'Laboratory (Form-D)', 'F'=>'CA (Form-F)');

		//Change on 9/11/2018, Sorting array by ascending order - By Pravin Bhakare
		asort($application_type_xy);
		$this->set('application_type_xy',$application_type_xy);

		//commented role not using by shreeya on date [08-07-2023]
		$user_roles_xy = array('RO/SO'=>'RO/SO',/*'MO/SMO'=>'MO/SMO','IO'=>'IO','HO MO/SMO'=>'HO MO/SMO','DY.AMA'=>'DY.AMA','JT.AMA'=>'JT.AMA','AMA'=>'AMA'*/);

		//Change on 9/11/2018, Sorting array by ascending order - By Pravin Bhakare
		asort($user_roles_xy);
		$this->set('user_roles_xy',$user_roles_xy);

		$ro_office = $this->DmiRoOffices->find('all')->where(['office_type IN' => ['SO', 'RO'], 'delete_status IS NULL'])->order(['ro_office' => 'ASC'])->combine('id', 'ro_office')->toArray();
		$this->set('ro_office',$ro_office);

		$search_application_type_id = $this->Session->read('search_application_type_id');
		$search_user_role = $this->Session->read('search_user_role');
		$ro_office_id = $this->Session->read('ro_office_id');
		$mo_office_id = $this->Session->read('mo_office_id');
		$io_office_id = $this->Session->read('io_office_id');
		$selected_month = $this->Session->read('selected_month');
		$search_to_date = $this->Session->read('search_to_date');
		$search_user_email_id = $this->Session->read('search_user_email_id');
		$application_array = $this->Session->read('application_array');
		$report_for = $this->Session->read('report_for');




		$download_search_application_type_id = $this->Session->read('search_application_type_id');
		$download_search_user_role = $this->Session->read('search_user_role');
		$download_ro_office_id = $this->Session->read('ro_office_id');
		$download_mo_office_id = $this->Session->read('mo_office_id');
		$download_io_office_id = $this->Session->read('io_office_id');
		$download_selected_month = $this->Session->read('selected_month');
		$download_search_to_date = $this->Session->read('search_to_date');
		$download_search_user_email_id = $this->Session->read('search_user_email_id');
		$download_report_for = $this->Session->read('report_for');


		$this->set('search_application_type_id',$search_application_type_id);
		$this->set('search_user_role',$search_user_role);
		$this->set('ro_office_id',$ro_office_id);
		$this->set('mo_office_id',$mo_office_id);
		$this->set('io_office_id',$io_office_id);
		$this->set('selected_month',$selected_month);
		$this->set('search_to_date',$search_to_date);
		$this->set('search_user_email_id',$search_user_email_id);
		$this->set('report_for',$report_for);


		// Set default value for download report click event (Done by pravin 14-03-2018)
		$download_report = 'no';

		//Check and Pass the entry for "Search" or "Download Report as Excel" button click event (Done by pravin 14-03-2018)
		if (null != ($this->request->getData('search_logs')) || null != ($this->request->getData('download_report'))) {
			
			
			//Check not empty "Download Report as Excel" button Request, if condition TRUE then set value "yes" for "Download Report as Excel" click event
			//and pass this value to "mo_io_ro_allocation_serach_conditions" function (Done by pravin 14-03-2018)
			if (!empty($this->request->getData('download_report'))) {
				$download_report = 'yes';
				
			}
			
			$application_array = $this->request->getData('application_array');
			$search_application_type_id = $this->request->getData('application_type');
			$search_user_role =  $this->request->getData('user_role');
			$ro_office_id =  $this->request->getData('ro_office');
			$mo_office_id =  $this->request->getData('mo_office');
			$io_office_id =  $this->request->getData('io_office');
			$report_for =  $this->request->getData('report_for');

			
			
			$search_user_email_id =  $this->request->getData('user_id');
			$selected_month =  $this->request->getData('selected_month');
			
			$selected_month = $this->Customfunctions->dateFormatCheck($selected_month);
			$search_to_date =  $this->request->getData('to_date');
			//$search_to_date = $this->Customfunctions->dateFormatCheck($search_to_date);
			//$this->date_comparison($selected_month,$search_to_date);

			// Change on 2/11/2018 - For download excel report, Take search filter field value from session variables instend of POST variable - By Pravin
			if ($download_report == 'yes') {
			
				$search_application_type_id = $this->Session->read('search_application_type_id');
				$search_user_role = $this->Session->read('search_user_role');
				$ro_office_id = $this->Session->read('ro_office_id');
				$mo_office_id = $this->Session->read('mo_office_id');
				$io_office_id = $this->Session->read('io_office_id');
				$selected_month = $this->Session->read('selected_month');
				$search_to_date = $this->Session->read('search_to_date');
				$search_user_email_id = $this->Session->read('search_user_email_id');
				$report_for = $this->Session->read('report_for');
				
				
			}

			
			$this->Session->delete('search_application_type_id');
			$this->Session->delete('search_user_role');
			$this->Session->delete('ro_office_id');
			$this->Session->delete('mo_office_id');
			$this->Session->delete('io_office_id');
			$this->Session->delete('selected_month');
			$this->Session->delete('search_to_date');
			$this->Session->delete('search_user_email_id');
			$this->Session->delete('report_for');


			$this->Session->write('search_application_type_id',$search_application_type_id);
			$this->Session->write('search_user_role',$search_user_role);
			$this->Session->write('ro_office_id',$ro_office_id);
			$this->Session->write('mo_office_id',$mo_office_id);
			$this->Session->write('io_office_id',$io_office_id);
			$this->Session->write('selected_month',$selected_month);
			$this->Session->write('search_to_date',$search_to_date);
			$this->Session->write('search_user_email_id',$search_user_email_id);
			$this->Session->write('report_for',$report_for);
			

			$this->set('search_application_type_id',$search_application_type_id);
			$this->set('search_user_role',$search_user_role);
			$this->set('ro_office_id',$ro_office_id);
			$this->set('mo_office_id',$mo_office_id);
			$this->set('io_office_id',$io_office_id);
			$this->set('selected_month',$selected_month);
			$this->set('search_to_date',$search_to_date);
			$this->set('search_user_email_id',$search_user_email_id);
			$this->set('report_for',$report_for);



			$application_customer_id_list = $this->applicationSearchConditions($applicn_type,$report_for,$search_application_type_id,$search_user_role,$ro_office_id,$mo_office_id,$io_office_id,$selected_month,$search_to_date,$search_user_email_id,$table,$application_array,$pending_application_type,$application_pending_days);

			if (!empty($application_customer_id_list)) {

				$this->loadModel('DmiFlowWiseTablesLists');
				if(empty($report_for) || $report_for == ''){

					$applications_current_positions_tables = $this->DmiFlowWiseTablesLists->find('all')->select(['application_form','appl_current_pos'])->where(array('application_type IN'=>$this->Session->read('applTypeArray')))->order(['id'])->combine('application_form','appl_current_pos')->toArray();

				}

					if($search_user_role == "AMA"){
						$search_user_role ="level_4";
					}elseif($search_user_role == 'RO/SO'){
						$search_user_role ="level_3";
					}elseif($search_user_role == 'MO/SO'){
						$search_user_role ="level_1";
					}elseif($search_user_role == 'IO'){
						$search_user_role ="level_2";
					}	elseif($search_user_role == 'DY.AMA'){
							$search_user_role ="level_4";
					}elseif($search_user_role == 'JT.AMA'){
						$search_user_role ="level_4";
					}elseif($search_user_role == 'HO MO/SMO'){
						$search_user_role ="level_4";
					}



				
				$current_users_details = array();
				
				foreach($applications_current_positions_tables as $each_table)
				{
					$key = array_search ($each_table, $applications_current_positions_tables);
					$this->loadModel($each_table);
					$this->loadModel($key);
					
					// show the listion customer id and check status approve with livel by shreeya [08-07-2023]
					$checkLastStatus = $this->$key->find('all',array('conditions'=>array('customer_id IN'=>$application_customer_id_list),'order'=>'created'))->first();
					if(!empty($checkLastStatus) && (($checkLastStatus['status']=='approved' && ($checkLastStatus['current_level']=='level_3' || $checkLastStatus['current_level']=='level_4')) ||
					($checkLastStatus['status']=='approved' && ($checkLastStatus['current_level']=='level_3' || $checkLastStatus['current_level']=='level_1')))){
						$current_users_details = $this->$each_table->find('all')->where(['customer_id IN'=>$application_customer_id_list,'current_level IS'=>$search_user_role])->order(['created' => 'ASC'])->toArray();
					} else {//further condition
						$current_users_details = $this->$each_table->find('all')->where(['customer_id IN'=>$application_customer_id_list,'current_level IS'=>$search_user_role])->order(['created' => 'ASC'])->toArray();
						
					}
					
					
				}

				
				//Fetch the all data that required for creating the downloading report as execel (Done by pravin 14-03-2018)
				if ($download_report == 'yes') {
					
					if (!empty($application_customer_id_list)) {
						$download_condition = ['customer_id IN' => $application_customer_id_list];
					} else {
						$download_condition = ['customer_id IS' => ''];
					}

					$download_pending_application = array();

					foreach($applications_current_positions_tables as $each_table)
					{
						$key = array_search ($each_table, $applications_current_positions_tables);
						$this->loadModel($each_table);
						$this->loadModel($key);
						
						$customer_id_list = $this->$each_table->find('all')->select(['id', 'customer_id'])
						->combine('id', 'customer_id')->toArray();

						// Show customer id and approve -> level condition check by shreeya on date [08-07-2023]
						if (!empty($customer_id_list)) {
							$checkLastStatus = $this->$key->find('all', [
								'conditions' => ['customer_id IN' => $customer_id_list],
								'order' => 'created'
							])->first();
						
							if (!empty($checkLastStatus) && (
								($checkLastStatus['status'] == 'approved' && ($checkLastStatus['current_level'] == 'level_3' || $checkLastStatus['current_level'] == 'level_4')) ||
								($checkLastStatus['status'] == 'approved' && ($checkLastStatus['current_level'] == 'level_3' || $checkLastStatus['current_level'] == 'level_1'))
							)) {
								$download_pending_application = $this->$each_table->find('all')->where(['customer_id IN'=>$application_customer_id_list,'current_level IS'=>$search_user_role])->order(['created' => 'ASC'])->toArray();
							}else{
								$download_pending_application = $this->$each_table->find('all')->where(['customer_id IN'=>$application_customer_id_list,'current_level IS'=>$search_user_role])->order(['created' => 'ASC'])->toArray();
							}
						
						}

					}

			
					$this->downloadPendingMprReport($download_pending_application,$application_array,$pending_application_type,$table);
				}

			} else {
				$current_users_details = null;
			}

			$this->pendingMPRReportResults($applicn_type,$current_users_details,$application_array,$pending_application_type,$table);

		} else {
		
			$application_customer_id_list = $this->applicationSearchConditions($applicn_type,$report_for,$search_application_type_id,$search_user_role,$ro_office_id,$mo_office_id,$io_office_id,$selected_month,$search_to_date,$search_user_email_id,$table,$application_array,$pending_application_type,$application_pending_days);

			if (!empty($application_customer_id_list)) {	


				$this->loadModel('DmiFlowWiseTablesLists');
				if(empty($report_for) || $report_for == ''){

					$applications_current_positions_tables = $this->DmiFlowWiseTablesLists->find('all')->select(['application_form','appl_current_pos'])->where(array('application_type IN'=>$this->Session->read('applTypeArray')))->order(['id'])->combine('application_form','appl_current_pos')->toArray();
				}
				
				$current_users_details = array();
			
				foreach($applications_current_positions_tables as $each_table)
				{

					$key = array_search ($each_table, $applications_current_positions_tables);
					$this->loadModel($each_table);
					$this->loadModel($key);

					$customer_id_list = $this->$each_table->find('all')->select(['id', 'customer_id'])
					->combine('id', 'customer_id')->toArray();

					// Show customer id and approve -> level condition check by shreeya on date [08-07-2023]
					if (!empty($customer_id_list)) {
						$checkLastStatus = $this->$key->find('all', [
							'conditions' => ['customer_id IN' => $customer_id_list],
							'order' => 'created'
						])->first();
					
						if (!empty($checkLastStatus) && (
							($checkLastStatus['status'] == 'approved' && ($checkLastStatus['current_level'] == 'level_3' || $checkLastStatus['current_level'] == 'level_4')) ||
							($checkLastStatus['status'] == 'approved' && ($checkLastStatus['current_level'] == 'level_3' || $checkLastStatus['current_level'] == 'level_1'))
						)) {
							$current_users_details = $this->$each_table->find('all')->where(['customer_id IN'=>$application_customer_id_list])->order(['created' => 'ASC'])->toArray();
						}else{
							$current_users_details = $this->$each_table->find('all')->where(['customer_id IN'=>$application_customer_id_list])->order(['created' => 'ASC'])->toArray();
						}
					
					}
					


				
				}

				
				//$current_users_details = $this->$table->find('all')->where(['customer_id IN' => $application_customer_id_list])->order(['id' => 'DESC'])->toArray(); //->limit(['100'])
				$this->set('current_users_details',$current_users_details);

				//Fetch the all data that required for creating the downloading report as execel (Done by pravin 14-03-2018)
				if ($download_report == 'yes') {
					
				
					if (!empty($application_customer_id_list)) {
						$download_condition = ['customer_id IN' => $application_customer_id_list];
					} else {
						$download_condition = ['customer_id IS' => ''];
					}

					$download_pending_application = array();

					foreach($applications_current_positions_tables as $each_table)
					{
						$key = array_search ($each_table, $applications_current_positions_tables);
						$this->loadModel($each_table);
						$this->loadModel($key);
						
						$customer_id_list = $this->$each_table->find('all')->select(['id', 'customer_id'])
						->combine('id', 'customer_id')->toArray();

						// Show customer id and approve -> level condition check by shreeya on date [08-07-2023]
						if (!empty($customer_id_list)) {
							$checkLastStatus = $this->$key->find('all', [
								'conditions' => ['customer_id IN' => $customer_id_list],
								'order' => 'created'
							])->first();
						
							if (!empty($checkLastStatus) && (
								($checkLastStatus['status'] == 'approved' && ($checkLastStatus['current_level'] == 'level_3' || $checkLastStatus['current_level'] == 'level_4')) ||
								($checkLastStatus['status'] == 'approved' && ($checkLastStatus['current_level'] == 'level_3' || $checkLastStatus['current_level'] == 'level_1'))
							)) {
								$download_pending_application = $this->$each_table->find('all')->where(['customer_id IN'=>$application_customer_id_list])->order(['created' => 'ASC'])->toArray();
							}else{
								$download_pending_application = $this->$each_table->find('all')->where(['customer_id IN'=>$application_customer_id_list])->order(['created' => 'ASC'])->toArray();
							}
						
						}
						
					}

			
					$this->downloadPendingMprReport($download_pending_application,$application_array,$pending_application_type,$table);
				}

			} else {
				
				$current_users_details = null;
			}

			$this->pendingMPRReportResults($applicn_type,$current_users_details,$application_array,$pending_application_type,$table);
		}

			// $this->Session->delete('search_user_role');
			// $this->Session->delete('ro_office_id');
			// $this->Session->delete('selected_month');

	}

	//added by shreeya on date [27-06-202]
	public function pendingMPRReportResults($applicn_type,$current_users_details,$application_array,$pending_application_type,$table) {

		$applicn_type=base64_decode($applicn_type);

		$date = [];
		$user_list = null;
		$application_type = null;
		$user_roles = null;
		$user_office = null;
		$user_email_id =null;
		$application_id = null;



		if (!empty($current_users_details)) {


				$i=0;
				foreach ($current_users_details as $each_user) {


					//$customer_id = $each_user['customer_id'];
					// $each_user_detail = $each_user;
					//$current_level = $each_user['current_level'];
					$application_form_type = $this->Customfunctions->checkApplicantFormType($each_user['customer_id']);

					if ($application_form_type == 'A') {
						$application_type[$i]='CA (Form-A)';
					} elseif ($application_form_type == 'B') {
						$application_type[$i]='Printing Press (Form-B)';
					} elseif ($application_form_type == 'C') {
						$application_type[$i]='Laboratory (Form-C)';
					} elseif ($application_form_type == 'D') {
						$application_type[$i]='Laboratory (Form-D)';
					} elseif ($application_form_type == 'E') {
						$application_type[$i]='CA (Form-E)';
					} elseif ($application_form_type == 'F') {
						$application_type[$i]='CA (Form-F)';
					}


					$date[$i] = $each_user['created'];
					$user_email_id[$i] = $each_user['current_user_email_id'];
					$application_id[$i] = $each_user['customer_id'];


					$user_posted_office_id=array();
					if (!empty($user_email_id[$i])) {
						$user_posted_office_id = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email IN' => $user_email_id[$i]])->first();

					}
					if (!empty($user_posted_office_id)) {
						$user_office_details[$i] = $this->DmiRoOffices->find('all')->select(['ro_office'])->where(['id' => $user_posted_office_id['posted_ro_office']])->first();

					}

					if (!empty($user_office_details[$i])) {
						$user_office[$i] = $user_office_details[$i]['ro_office'];
					} else {
						$user_office[$i] = '--';
					}

					$check_roles=array();
					if (!empty($user_email_id[$i])) {
						$check_roles = $this->DmiUserRoles->find('all')->where(['user_email_id IN' => $user_email_id[$i]])->first();
					}

					if (!empty($check_roles)) {
						$user_list[$i] = $check_roles;
					} else {
						$user_list[$i] = '---';
					}


					// $apl_type =  $this->DmiApplicationTypes->find('all')->select(['id','application_type'])->where(['id' =>$application_array])->first();
					// $apl_type_res[$i] = $apl_type['application_type'];


					$user_roles[$i] = $this->checkUserRoleFromCurrentLevel($each_user['current_level'],$each_user['current_user_email_id']);


				$i=$i+1;

				}



		}

		$this->set('date',$date);
		$this->set('user_list',$user_list);
		$this->set('application_type',$application_type);
		// $this->set('apl_type_res',$apl_type_res);
		$this->set('user_roles',$user_roles);
		$this->set('user_office',$user_office);
		$this->set('user_email_id',$user_email_id);
		$this->set('application_id',$application_id);

	}

	//added by shreeya on date [27-06-202]
	public function applicationSearchConditions($applicn_type,$report_for,$search_application_type_id,$search_user_role,$ro_office_id,$mo_office_id,$io_office_id,$selected_month,$search_to_date,$search_user_email_id,$table,$application_array,$pending_application_type,$application_pending_days,$data_id=null,)
	{

		
			$applicn_type=base64_decode($applicn_type);

			$this->loadModel('DmiApplicationTypes');
			//show the all application type added by shreeya on date 17-06-2023
			$application_type_data = $this->DmiApplicationTypes->find('all')->select(['id', 'application_type'])->where(['delete_status IS NULL','application_type'=>$applicn_type])->toArray();
			$report_for = ''; // Initialize $report_for with a default value
			if(!empty($application_type_data)){
				$report_for = $application_type_data[0]['id'];
			}
			$this->set('report_for',$report_for);
		
			//get application id array
			//$application_array = $this->DmiApplicationTypes->find('all')->select(['id', 'application_type'])->where(['delete_status IS NULL'])->order(['id'])->combine('id', 'application_type')->toArray();
			//$application_key = array_keys($application_array);


			$final_customer_id_list = null;

			if ($ro_office_id != '' && $search_user_role == 'RO/SO') {
				$level_1_2_3_office = $ro_office_id;
			} elseif ($mo_office_id != '' && $search_user_role == 'MO/SMO') {
				$level_1_2_3_office = $mo_office_id;
			} elseif ($io_office_id != '' && $search_user_role == 'IO') {
				$level_1_2_3_office = $io_office_id;
			} else {
				$level_1_2_3_office = '';
			}

				//added for show data according to level ,offices & month by shreeya on date [04-07-2023]
			if ($search_user_role != '' && $level_1_2_3_office != '' && $selected_month != '')
			{
			
			
				if($search_user_role == "AMA"){
					$search_user_role ="level_4";
				}elseif($search_user_role == 'RO/SO'){
					$search_user_role ="level_3";
				}elseif($search_user_role == 'MO/SO'){
					$search_user_role ="level_1";
				}elseif($search_user_role == 'IO'){
					$search_user_role ="level_2";
				}	elseif($search_user_role == 'DY.AMA'){
						$search_user_role ="level_4";
				}elseif($search_user_role == 'JT.AMA'){
					$search_user_role ="level_4";
				}elseif($search_user_role == 'HO MO/SMO'){
					$search_user_role ="level_4";
				}



				//fetch the records of email id done by shreeya on date [30-06-2023]
				$this->loadModel('DmiRoOffices');
				// $posted_off = $this->DmiRoOffices->find('all')->where(['id IN'=>$level_1_2_3_office])->first();
				// $posted_offices = $posted_off['ro_email_id'];

				//Change condtion for show result in array format (ro-office) by shreeya on date [11-07-2023]
				$posted_off = $this->DmiRoOffices->find('all')->where(['id IN' => $level_1_2_3_office])->toArray();
				if (!empty($posted_off)) {
					$posted_offices = [];
					foreach ($posted_off as $off) {
						$posted_offices[] = $off['ro_email_id'];
					}
						
				} 
				
				
				$this->loadModel('DmiFlowWiseTablesLists');
				$applications_current_positions_tables = $this->DmiFlowWiseTablesLists->find('all')->select(['application_form','appl_current_pos'])->where(array('application_type IN'=>$this->Session->read('applTypeArray')))->order(['id'])->combine('application_form','appl_current_pos')->toArray();


				$i=0;

				$customer_id_details = array();
				$appl_type = array();

				foreach($applications_current_positions_tables as $each_table)
				{

					$key = array_search ($each_table, $applications_current_positions_tables);
					$this->loadModel($each_table);
					$this->loadModel($key);
					/******************************** Perticular Month Wise List Start************************************** */
					// Convert the selected month to the desired format
					// $selected_month = date('d-m-Y', strtotime($selected_month));
					// $split_selected_month = explode('-', $selected_month);

					// $day = $split_selected_month[0];
					// $month = $split_selected_month[1];
					// $year = $split_selected_month[2];

					// $selected_month = date('d-m-Y', strtotime($selected_month));

					// $customer_id_list = $this->$each_table->find('all')->select(['id', 'customer_id'])
					// 	->where([
					// 		'current_level' => $search_user_role,
					// 		'current_user_email_id' => $posted_office,
					// 		'created BETWEEN :start AND :end'
					// 	])
					// 	->bind(':start', '01'. '-'. $month .'-' . $year)
					// 	->bind(':end', '30'. '-'. $month . '-' . $year)
					// 	->combine('id', 'customer_id')
					// 	->toArray(); 
					/******************************** Perticular Month Wise List End************************************** */

					$selected_month = date('d-m-Y', strtotime($selected_month));
					$split_selected_month = explode('-', $selected_month);

					$day = $split_selected_month[0];
					$month = $split_selected_month[1];
					$year = $split_selected_month[2];

					// Calculate the last day of the selected month
					$last_day = date('t', strtotime($selected_month));

					$selected_month = date('d-m-Y', strtotime($selected_month));

					$customer_id_list = $this->$each_table->find('all')->select(['id', 'customer_id'])
						->where([
							'current_level IS' => $search_user_role,
							'current_user_email_id IN' => $posted_offices,
							'created BETWEEN :start AND :end'
						])
						->bind(':start', '01-04-2023')
						->bind(':end', $last_day . '-' . $month . '-' . $year)
						->combine('id', 'customer_id')
						->toArray();



					
					// Show customer id and approve -> level condition check by shreeya on date [08-07-2023]
					if (!empty($customer_id_list)) {
						$checkLastStatus = $this->$key->find('all', [
							'conditions' => ['customer_id IN' => $customer_id_list],
							'order' => 'created'
						])->first();
					
						if (!empty($checkLastStatus) && (
							($checkLastStatus['status'] == 'approved' && ($checkLastStatus['current_level'] == 'level_3' || $checkLastStatus['current_level'] == 'level_4')) ||
							($checkLastStatus['status'] == 'approved' && ($checkLastStatus['current_level'] == 'level_3' || $checkLastStatus['current_level'] == 'level_1'))
						)) {
							$customer_id_details[$i] = $customer_id_list;
						}else{
							$customer_id_details[$i] = null;
						}
					
					}

				
						
					//fetch the customer_id
					if (!empty($customer_id_list)) {

						$customer_id_details[$i] = $this->DmiFirms->find('all')->where(['customer_id IN' => $customer_id_list])->order(['created' => 'ASC'])->toArray();

					} else {
						$customer_id_details[$i] = array(); // Initialize as empty array if customer_id_list is empty
						// $customer_id_details = null;
					}

					

				}
			
				
				$final_customer_id_list = array();
				$j=0;
				foreach ($customer_id_details[$i] as $each_user) {

					if (!empty($each_user['customer_id'])) {
						$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_user['customer_id']);

						$final_customer_id_list[$j] = $each_user['customer_id'];

						$j++;

					}
				}

				$i++;


			}
				//added for show data according to level  & month by shreeya on date [10-07-2023]
			elseif($search_user_role != ''  || $selected_month != '')
			{

				
				if($search_user_role == "AMA"){
					$search_user_role ="level_4";
				}elseif($search_user_role == 'RO/SO'){
					$search_user_role ="level_3";
				}elseif($search_user_role == 'MO/SO'){
					$search_user_role ="level_1";
				}elseif($search_user_role == 'IO'){
					$search_user_role ="level_2";
				}	elseif($search_user_role == 'DY.AMA'){
						$search_user_role ="level_4";
				}elseif($search_user_role == 'JT.AMA'){
					$search_user_role ="level_4";
				}elseif($search_user_role == 'HO MO/SMO'){
					$search_user_role ="level_4";
				}

				//fetch the records of email id done by shreeya on date [30-06-2023]
				$this->loadModel('DmiRoOffices');
				// $posted_off = $this->DmiRoOffices->find('all')->where(['id IN'=>$level_1_2_3_office])->first();
				// $posted_office = $posted_off['ro_email_id'];
				//Change condtion for show result in array format (ro-office) by shreeya on date [11-07-2023]
				$posted_off = $this->DmiRoOffices->find('all')->where(['id IN' => $level_1_2_3_office])->toArray();
				if (!empty($posted_off)) {
					$posted_offices = [];
					foreach ($posted_off as $off) {
						$posted_offices[] = $off['ro_email_id'];
					}
						
				} 

				$this->loadModel('DmiFlowWiseTablesLists');
				$applications_current_positions_tables = $this->DmiFlowWiseTablesLists->find('all')->select(['application_form','appl_current_pos'])->where(array('application_type IN'=>$this->Session->read('applTypeArray')))->order(['id'])->combine('application_form','appl_current_pos')->toArray();


				$i=0;

				$customer_id_details = array();
				$appl_type = array();

				foreach($applications_current_positions_tables as $each_table)
				{

					$key = array_search ($each_table, $applications_current_positions_tables);
					$this->loadModel($each_table);
					$this->loadModel($key);

					//added for after search show record start month april to current month
					//by shreeya [10-07-2023]
					$selected_month = date('d-m-Y', strtotime($selected_month));
					$split_selected_month = explode('-', $selected_month);

					$day = $split_selected_month[0];
					$month = $split_selected_month[1];
					$year = $split_selected_month[2];

					// Calculate the last day of the selected month
					$last_day = date('t', strtotime($selected_month));

					$selected_month = date('d-m-Y', strtotime($selected_month));

					$customer_id_list = $this->$each_table->find('all')->select(['id', 'customer_id'])
						->where([
							'current_level IS' => $search_user_role,
							'created BETWEEN :start AND :end'
						])
						->bind(':start', '01-04-2023')
						->bind(':end', $last_day . '-' . $month . '-' . $year)
						->combine('id', 'customer_id')
						->toArray();
 


						


					// Show customer id and approve -> level condition check by shreeya on date [08-07-2023]
					if (!empty($customer_id_list)) {
						$checkLastStatus = $this->$key->find('all', [
							'conditions' => ['customer_id IN' => $customer_id_list],
							'order' => 'created'
						])->first();
					
						if (!empty($checkLastStatus) && (
							($checkLastStatus['status'] == 'approved' && ($checkLastStatus['current_level'] == 'level_3' || $checkLastStatus['current_level'] == 'level_4')) ||
							($checkLastStatus['status'] == 'approved' && ($checkLastStatus['current_level'] == 'level_3' || $checkLastStatus['current_level'] == 'level_1'))
						)) {
							$customer_id_details[$i] = $customer_id_list;
						}else{
							$customer_id_details[$i] = null;
						}
					
					}

				
						
					//fetch the customer_id
					if (!empty($customer_id_list)) {

						$customer_id_details[$i] = $this->DmiFirms->find('all')->where(['customer_id IN' => $customer_id_list])->order(['created' => 'ASC'])->toArray();

					} else {
						$customer_id_details[$i] = array(); // Initialize as empty array if customer_id_list is empty
						// $customer_id_details = null;
					}

					

				}
			
				
				$final_customer_id_list = array();
				$j=0;
				foreach ($customer_id_details[$i] as $each_user) {

					if (!empty($each_user['customer_id'])) {
						$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_user['customer_id']);

						$final_customer_id_list[$j] = $each_user['customer_id'];

						$j++;

					}
				}

				$i++;


			}
			//added for show data according to Role shreeya on date [10-07-2023]
			elseif($search_user_role != '')
			{
			
				
				if($search_user_role == "AMA"){
					$search_user_role ="level_4";
				}elseif($search_user_role == 'RO/SO'){
					$search_user_role ="level_3";
				}elseif($search_user_role == 'MO/SO'){
					$search_user_role ="level_1";
				}elseif($search_user_role == 'IO'){
					$search_user_role ="level_2";
				}	elseif($search_user_role == 'DY.AMA'){
						$search_user_role ="level_4";
				}elseif($search_user_role == 'JT.AMA'){
					$search_user_role ="level_4";
				}elseif($search_user_role == 'HO MO/SMO'){
					$search_user_role ="level_4";
				}

				//fetch the records of email id done by shreeya on date [30-06-2023]
				$this->loadModel('DmiRoOffices');
				// $posted_off = $this->DmiRoOffices->find('all')->where(['id IN'=>$level_1_2_3_office])->first();
				// $posted_office = $posted_off['ro_email_id'];
				//Change condtion for show result in array format (ro-office) by shreeya on date [11-07-2023]
				$posted_off = $this->DmiRoOffices->find('all')->where(['id IN' => $level_1_2_3_office])->toArray();
				if (!empty($posted_off)) {
					$posted_offices = [];
					foreach ($posted_off as $off) {
						$posted_offices[] = $off['ro_email_id'];
					}
						
				} 

				$this->loadModel('DmiFlowWiseTablesLists');
				$applications_current_positions_tables = $this->DmiFlowWiseTablesLists->find('all')->select(['application_form','appl_current_pos'])->where(array('application_type IN'=>$this->Session->read('applTypeArray')))->order(['id'])->combine('application_form','appl_current_pos')->toArray();


				$i=0;

				$customer_id_details = array();
				$appl_type = array();

				foreach($applications_current_positions_tables as $each_table)
				{

					$key = array_search ($each_table, $applications_current_positions_tables);
					$this->loadModel($each_table);
					$this->loadModel($key);

					//added for after search show record start month april to current month
					//by shreeya [10-07-2023]
					$firstDayOfMonth = date('01-m-Y');
					$currentDate = date('d-m-Y');

					$customer_id_list = $this->$each_table->find('all')->select(['id', 'customer_id'])
						->where([
							'current_level IS' => $search_user_role
						])
						->combine('id', 'customer_id')
						->toArray();



					// Show customer id and approve -> level condition check by shreeya on date [08-07-2023]
					if (!empty($customer_id_list)) {
						$checkLastStatus = $this->$key->find('all', [
							'conditions' => ['customer_id IN' => $customer_id_list],
							'order' => 'created'
						])->first();
					
						if (!empty($checkLastStatus) && (
							($checkLastStatus['status'] == 'approved' && ($checkLastStatus['current_level'] == 'level_3' || $checkLastStatus['current_level'] == 'level_4')) ||
							($checkLastStatus['status'] == 'approved' && ($checkLastStatus['current_level'] == 'level_3' || $checkLastStatus['current_level'] == 'level_1'))
						)) {
							$customer_id_details[$i] = $customer_id_list;
						}else{
							$customer_id_details[$i] = null;
						}
					
					}

				
						
					//fetch the customer_id
					if (!empty($customer_id_list)) {

						$customer_id_details[$i] = $this->DmiFirms->find('all')->where(['customer_id IN' => $customer_id_list])->order(['created' => 'ASC'])->toArray();

					} else {
						$customer_id_details[$i] = array(); // Initialize as empty array if customer_id_list is empty
						// $customer_id_details = null;
					}

					

				}
			
				
				$final_customer_id_list = array();
				$j=0;
				foreach ($customer_id_details[$i] as $each_user) {

					if (!empty($each_user['customer_id'])) {
						$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_user['customer_id']);

						$final_customer_id_list[$j] = $each_user['customer_id'];

						$j++;

					}
				}

				$i++;


			}
			//added for show data according to Month shreeya on date [10-07-2023]
			elseif($selected_month != '')
			{
				
				$this->loadModel('DmiFlowWiseTablesLists');
				$applications_current_positions_tables = $this->DmiFlowWiseTablesLists->find('all')->select(['application_form','appl_current_pos'])->where(array('application_type IN'=>$this->Session->read('applTypeArray')))->order(['id'])->combine('application_form','appl_current_pos')->toArray();


				$i=0;

				$customer_id_details = array();
				$appl_type = array();

				foreach($applications_current_positions_tables as $each_table)
				{

					$key = array_search ($each_table, $applications_current_positions_tables);
					$this->loadModel($each_table);
					$this->loadModel($key);

					//added for after search show record start month april to current month
					//by shreeya [10-07-2023]
					$selected_month = date('d-m-Y', strtotime($selected_month));
					$split_selected_month = explode('-', $selected_month);

					$day = $split_selected_month[0];
					$month = $split_selected_month[1];
					$year = $split_selected_month[2];

					// Calculate the last day of the selected month
					$last_day = date('t', strtotime($selected_month));

					$selected_month = date('d-m-Y', strtotime($selected_month));

					$customer_id_list = $this->$each_table->find('all')->select(['id', 'customer_id'])
						->where([
							'created BETWEEN :start AND :end'
						])
						->bind(':start', '01-04-2023')
						->bind(':end', $last_day . '-' . $month . '-' . $year)
						->combine('id', 'customer_id')
						->toArray();




					// Show customer id and approve -> level condition check by shreeya on date [08-07-2023]
					if (!empty($customer_id_list)) {
						$checkLastStatus = $this->$key->find('all', [
							'conditions' => ['customer_id IN' => $customer_id_list],
							'order' => 'created'
						])->first();
					
						if (!empty($checkLastStatus) && (
							($checkLastStatus['status'] == 'approved' && ($checkLastStatus['current_level'] == 'level_3' || $checkLastStatus['current_level'] == 'level_4')) ||
							($checkLastStatus['status'] == 'approved' && ($checkLastStatus['current_level'] == 'level_3' || $checkLastStatus['current_level'] == 'level_1'))
						)) {
							$customer_id_details[$i] = $customer_id_list;
						}else{
							$customer_id_details[$i] = null;
						}
					
					}

				
						
					//fetch the customer_id
					if (!empty($customer_id_list)) {

						$customer_id_details[$i] = $this->DmiFirms->find('all')->where(['customer_id IN' => $customer_id_list])->order(['created' => 'ASC'])->toArray();

					} else {
						$customer_id_details[$i] = array(); // Initialize as empty array if customer_id_list is empty
						// $customer_id_details = null;
					}

					

				}

				
				$final_customer_id_list = array();
				$j=0;
				foreach ($customer_id_details[$i] as $each_user) {

					if (!empty($each_user['customer_id'])) {
						$application_customer_type = $this->Customfunctions->checkApplicantFormType($each_user['customer_id']);

						$final_customer_id_list[$j] = $each_user['customer_id'];

						$j++;

					}
				}

				$i++;


			}
			//null all record and search 
			elseif($search_user_role == ''  || $selected_month == '' || $level_1_2_3_office == '')
			{
				
			
				$this->loadModel('DmiFlowWiseTablesLists');
				if(empty($report_for) || $report_for == ''){

					$applications_current_positions_tables = $this->DmiFlowWiseTablesLists->find('all')->select(['application_form','appl_current_pos'])->where(array('application_type IN'=>$this->Session->read('applTypeArray')))->order(['id'])->combine('application_form','appl_current_pos')->toArray();
				}

				$i=0;
				$customer_id_details = array();
				foreach($applications_current_positions_tables as $each_table)
				{

					$key = array_search ($each_table, $applications_current_positions_tables);
					$this->loadModel($each_table);
					$this->loadModel($key);


					$customer_id_list = $this->$each_table->find('all')->select(['id', 'customer_id'])
						->combine('id', 'customer_id')->toArray();

					
					// Show customer id and approve -> level condition check by shreeya on date [08-07-2023]
					if (!empty($customer_id_list)) {
						$checkLastStatus = $this->$key->find('all', [
							'conditions' => ['customer_id IN' => $customer_id_list],
							'order' => 'created'
						])->first();
					
						if (!empty($checkLastStatus) && (
							($checkLastStatus['status'] == 'approved' && ($checkLastStatus['current_level'] == 'level_3' || $checkLastStatus['current_level'] == 'level_4')) ||
							($checkLastStatus['status'] == 'approved' && ($checkLastStatus['current_level'] == 'level_3' || $checkLastStatus['current_level'] == 'level_1'))
						)) {
							$customer_id_details[$i] = $customer_id_list;
						}else{
							$customer_id_details[$i] = $customer_id_list;
						}
					
					}

					
					//fetch the customer_id
					if (!empty($customer_id_list)) {

						$customer_id_details[$i] = $this->DmiFirms->find('all')->where(['customer_id IN' => $customer_id_list])->order(['created' => 'ASC'])->toArray();
						
					} else {
						$customer_id_details[$i] = array(); // Initialize as empty array if customer_id_list is empty
						// $customer_id_details = null;
					}

						
					$final_customer_id_list = array();

					if (!empty($customer_id_details[$i])) {
						
						$j=0;
						foreach ($customer_id_details[$i] as $each_user){
							$final_customer_id_list[$j] = $each_user['customer_id'];

							$j++;
						}

					}
					$i++;

				}
				

			}
			// show the by defalut data without search by shreeya on date [29-06-2023]
			else
			{
				
				$this->loadModel('DmiFlowWiseTablesLists');
				if(empty($report_for) || $report_for == ''){

					$applications_current_positions_tables = $this->DmiFlowWiseTablesLists->find('all')->select(['application_form','appl_current_pos'])->where(array('application_type IN'=>$this->Session->read('applTypeArray')))->order(['id'])->combine('application_form','appl_current_pos')->toArray();
				}

				$i=0;
				$customer_id_details = array();
				foreach($applications_current_positions_tables as $each_table)
				{

					$key = array_search ($each_table, $applications_current_positions_tables);
					$this->loadModel($each_table);
					$this->loadModel($key);

					//show last month record
					//$lastMonth = date('Y-m-d', strtotime('-1month'));
					
					//$selected_month = ['modified >=' => date('Y-m-01', strtotime($lastMonth))];
					//$search_to_date = ['modified <=' => date('Y-m-t', strtotime($lastMonth))];

					$customer_id_list = $this->$each_table->find('all')->select(['id', 'customer_id'])
						->combine('id', 'customer_id')->toArray();

					

					// Show customer id and approve -> level condition check by shreeya on date [08-07-2023]
					if (!empty($customer_id_list)) {
						$checkLastStatus = $this->$key->find('all', [
							'conditions' => ['customer_id IN' => $customer_id_list],
							'order' => 'created'
						])->first();
					
						if (!empty($checkLastStatus) && (
							($checkLastStatus['status'] == 'approved' && ($checkLastStatus['current_level'] == 'level_3' || $checkLastStatus['current_level'] == 'level_4')) ||
							($checkLastStatus['status'] == 'approved' && ($checkLastStatus['current_level'] == 'level_3' || $checkLastStatus['current_level'] == 'level_1'))
						)) {
							$customer_id_details[$i] = $customer_id_list;
						}else{
							$customer_id_details[$i] = $customer_id_list;
						}
					
					}

					
					//fetch the customer_id
					if (!empty($customer_id_list)) {

						$customer_id_details[$i] = $this->DmiFirms->find('all')->where(['customer_id IN' => $customer_id_list])->order(['created' => 'ASC'])->toArray();

					} else {
						$customer_id_details[$i] = array(); // Initialize as empty array if customer_id_list is empty
						// $customer_id_details = null;
					}


					$final_customer_id_list = array();

					if (!empty($customer_id_details[$i])) {
						$j=0;
						foreach ($customer_id_details[$i] as $each_user){
							$final_customer_id_list[$j] = $each_user['customer_id'];

							$j++;
						}

					}
					$i++;

				}

				
			}
			
			//if data same data id exist in rejcted table it is not apper in report added by laxmi B. on 20-01-2023
			$this->loadModel('DmiRejectedApplLogs');
			$rejectedList = $this->DmiRejectedApplLogs->find('all', array('fields'=>array('customer_id')))->order(['id' => 'DESC'])->toArray();//

			$reject_id = array();
			$i=0;
			if(!empty($rejectedList)){
				foreach($rejectedList as $reject){
					$reject_id[$i] = $reject['customer_id'];
					$i++;
				}

				if(!empty($final_customer_id_list)){
					$final_customer_id_list = array_diff($final_customer_id_list, $reject_id);

				}

				// } //end by laxmi b.

				return $final_customer_id_list;



			}

	}

	//new function added by shreeya for download and generage exitseet mpr report [28-06-2023]
	public function downloadPendingMprReport($download_pending_application,$pending_application_type,$table) {

		$this->viewBuilder()->setLayout('downloadpdf');
		//print_r($download_pending_application); exit;
		$date = [];
		$user_list = null;
		$application_type =null;
		$user_roles = null;
		$user_office = null;
		$user_email_id =null;
		$application_id = null;

		if (!empty($download_pending_application)) {

			$i=0;
			foreach ($download_pending_application as $each_user) {

				//$customer_id = $each_user['customer_id'];
				// $each_user_detail = $each_user;
				//$current_level = $each_user['current_level'];
				$application_form_type = $this->Customfunctions->checkApplicantFormType($each_user['customer_id']);


				if ($application_form_type == 'A') {
					$application_type[$i]='CA (Form-A)';
				} elseif ($application_form_type == 'B') {
					$application_type[$i]='Printing Press (Form-B)';
				} elseif ($application_form_type == 'C') {
					$application_type[$i]='Laboratory (Form-C)';
				} elseif ($application_form_type == 'D') {
					$application_type[$i]='Laboratory (Form-D)';
				} elseif ($application_form_type == 'E') {
					$application_type[$i]='CA (Form-E)';
				} elseif ($application_form_type == 'F') {
					$application_type[$i]='CA (Form-F)';
				}

				$date[$i] = $each_user['created'];
				$user_email_id[$i] = $each_user['current_user_email_id'];
				$application_id[$i] = $each_user['customer_id'];

				$user_posted_office_id = array();
				if(!empty($user_email_id[$i])){
					$user_posted_office_id = $this->DmiUsers->find('all')->select(['posted_ro_office'])->where(['email IN' => $user_email_id[$i]])->first();
				}

				if (!empty($user_posted_office_id)) {
					$user_office[$i] = $this->DmiRoOffices->find('all')->select(['ro_office'])->where(['id' => $user_posted_office_id['posted_ro_office']])->first();
				}

				if (!empty($user_office[$i])) {
					$user_office[$i] = $user_office[$i]['ro_office'];
				} else {
					$user_office[$i] = '--';
				}

				if(!empty($user_email_id[$i])){
					$check_roles = $this->DmiUserRoles->find('all')->where(['user_email_id IS' => $user_email_id[$i]])->first();
				}

				$user_email_id[$i] = base64_decode($user_email_id[$i]);//for email encoding

				if (!empty($check_roles)) {
					$user_list[$i] = $check_roles;
				} else {
					$user_list[$i] = '---';
				}

				$user_roles[$i] = $this->checkUserRoleFromCurrentLevel($each_user['current_level'],$each_user['current_user_email_id']);
				$i=$i+1;
			}
		}

		$this->set('orders',$date);
		$this->set('user_list',$user_list);
		$this->set('application_type',$application_type);
		$this->set('user_roles',$user_roles);
		$this->set('user_office',$user_office);
		$this->set('user_email_id',$user_email_id);
		$this->set('application_id',$application_id);

		$this->layout = null;
		$this->autoLayout = false;
		Configure::write('debug', '0');
		$this->render('/element/download_report_excel_format/download_pending_mpr_report');


	}








}

?>
