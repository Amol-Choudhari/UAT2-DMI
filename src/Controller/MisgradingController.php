<?php // Controller is created for the Management of misgrading report MMR - Akash [03-05-2023]
namespace App\Controller;
use Cake\Network\Session\DatabaseSession;
use App\Network\Request\Request;
use App\Network\Response\Response;
use Cake\Datasource\ConnectionManager;
use Cake\Event\Event;
use TCPDF;
use Controller\Dashboard;
use Cake\Utility\Hash;


class MisgradingController extends AppController{

	var $name = 'Misgrading';

	public function initialize(): void {

		parent::initialize();
		$this->viewBuilder()->setLayout('admin_dashboard');
		$this->viewBuilder()->setHelpers(['Form','Html']);
		$this->loadComponent('Customfunctions');
		$this->loadComponent('Authentication');
		
		//Load Model
		$this->loadModel('SampleInward');
		$this->loadModel('MCommodityCategory');
		$this->loadModel('MCommodity');
		$this->loadModel('MSampleType');
		$this->loadModel('DmiUsers');
		$this->loadModel('DmiDistricts');
		$this->loadModel('DmiFirms');
		$this->loadModel('DmiRoOffices');
		$this->loadModel('DmiUserRoles');
		$this->loadModel('Workflow');
		$this->loadModel('MSampleAllocate');
		$this->loadModel('DmiStates');
		$this->loadModel('DmiMmrSamplePackerLogs');
		$this->loadModel('DmiMmrFinalSubmits');
		$this->loadModel('DmiMmrAllocations');
		$this->loadModel('DmiMmrRoMoComments');
		$this->loadModel('DmiMmrSmsTemplates');


	}


	// Description : To list all the final graded reports from which RO/SO OIC will allocate the report for examination to Scrutinizer.
	// Author : Akash Thakre
	// Date : 28-04-2023
	//For Management of Misgrading (MMR)

	public function reportListingForAllocation(){


		//Below all the Session Delete setfor the MMR Application - Akash [06-06-2023]
		$this->Session->Delete('alloc_user_by');
		$this->Session->Delete('allocation_to');
		$this->Session->Delete('sample_code');
		$this->Session->Delete('pdf_file_name');
		$this->Session->Delete('application_mode');
		$this->Session->Delete('current_level');

		// Get the default database connection
		$con = ConnectionManager::get('default');

		// Get the posted office ID for the current user
		$loc_id = $this->DmiUsers->getPostedOffId($_SESSION['username']);

		// Fetch the final grading reports
		$query = "SELECT w.org_sample_code,	w.tran_date, mcc.category_name, 
						mc.commodity_name, mst.sample_type_desc, 
						mc.commodity_code, si.report_pdf, 
						si.report_status,si.packer_id
				FROM workflow w
				INNER JOIN sample_inward si ON si.org_sample_code = w.org_sample_code
				INNER JOIN m_commodity_category mcc ON mcc.category_code = si.category_code
				INNER JOIN m_commodity mc ON mc.commodity_code = si.commodity_code
				INNER JOIN m_sample_type mst ON mst.sample_type_code = si.sample_type_code
				WHERE si.status_flag != 'junked' 
					AND w.stage_smpl_flag = 'FG' 
					AND w.org_sample_code IN (
						SELECT org_sample_code 
						FROM workflow 
						WHERE src_loc_id = :loc_id 
						GROUP BY org_sample_code
					)
				ORDER BY w.tran_date DESC";

		$finalGrading = $con->execute($query, ['loc_id' => $loc_id])->fetchAll('assoc');
		
	
		$finalReports = [];
		foreach ($finalGrading as $row) {

			// Check if either 'scrutiny_status' or 'action_final_submit' contains 'Yes'
			if ($row['report_status'] === 'Scrutinized' || $row['report_status'] === 'Action Taken') {
				continue; // Skip the iteration
			}

			$finalReports[] = $row;
		}

		//pr($finalReports); exit;


		$scrutinizedReports = $this->DmiMmrFinalSubmits->find('all')->where(['scrutiny' => 'done'])->order('id DESC')->toArray();
	
		$scrutinyDone = [];

		// Check if the stage_smpl_cd is present in scrutinizedReports and scrutiny is done
		foreach ($scrutinizedReports as $report) {
			
			if ($report->scrutiny === 'done') {
				$scrutinyDone[] = [
					'customer_id' => $report->customer_id,
					'sample_code' => $report->sample_code,
					'date' => $report->modified
					// Add other details from another table here
				];
				break; // Break the loop if the match is found
			}
		}
	
	
	

		$this->set('scrutinyDone',$scrutinyDone);
		$this->set('final_reports', $finalReports);

		
		
	}


	// Description : To redirect the clicked sample code to the method allocateReport().
	// Author : Akash Thakre
	// Date : 28-04-2023
	//For Management of Misgrading (MMR)

	public function redirectToAllocate($sampleCode,$current_level,$mode){
		
		$this->Session->write('sample_code',$sampleCode);
		$this->Session->write('current_level',$current_level);
		$this->Session->write('application_mode',$mode);
		$this->redirect(array('controller'=>'misgrading','action'=>'allocate_report'));
	}



	// Description : To allocate the sample to the SMO / MO in the DMI
	// Author : Akash Thakre
	// Date : 28-04-2023
	// For Management of Misgrading (MMR)
	public function allocateReport() {
		
		// Set Variables to Show Pop-up Messages From View File
		$message = '';
		$message_theme = '';
		$redirect_to = '';
		
		$sample_code = $this->Session->read('sample_code');
		

		//Check if the sample code is allocated
		$allocation = $this->DmiMmrAllocations->find()->where(['sample_code' => $sample_code])->order('id DESC')->first();
		if(!empty($allocation)){

			$this->Session->write('alloc_user_by',$allocation['level_3']);
			$this->Session->write('allocation_to',$allocation['level_1']);
		}

		
		$this->loadModel('DmiUserRoles'); 
		
	
		if(isset($_SESSION['current_level'])){ 
			if ($_SESSION['current_level'] == 'level_1') {
				$commentWindow = 'mo';
			}else {
				$commentWindow = 'ro';
			}	
		}
		
		$this->set('commentWindow',$commentWindow);

		$from_user = $commentWindow;
		$current_level = $_SESSION['current_level'];
		$username = $this->Session->read('username');
		
		
		
		// fetch comments history
		$ro_so_mo_comments = $this->DmiMmrRoMoComments->find('all',array('conditions'=>array('sample_code IS'=>$sample_code,'OR'=>array('comment_by IS'=>$username,'comment_to IS'=>$username)),'order'=>'id'))->toArray();	
		$comments_result = array_merge($ro_so_mo_comments);			
		$comments_result = Hash::sort($comments_result, '{n}.created', 'desc');	
		$this->set('ro_so_mo_comments',$comments_result);
		
		// fetch all allocation details
		$allocation_deatils = $this->DmiMmrAllocations->find('all',array('conditions'=>array('sample_code IS'=>$sample_code)))->first();
		if (!empty($allocation_deatils)) {
			$isAllocatd = 'yes';
		} else {
			$isAllocatd = 'no';
		}
		
		$this->set('allocation_deatils',$allocation_deatils);
		$this->set('isAllocatd',$isAllocatd);
		
	
		// Check current user roles
		$check_user_role = $this->DmiUserRoles->find('all',array('conditions'=>array('user_email_id IS'=>$username)))->first();
		$this->set('check_user_role',$check_user_role);					
			


		if(!empty($sample_code)){
			
			$sampleInfo = $this->SampleInward->sampleInformation($sample_code);
			$username = $this->Session->read('username');
			
			//Get Commodity Name
			$category_name = $this->MCommodityCategory->getCategory($sampleInfo['category_code']);
			//Get Category Name
			$commodity_name = $this->MCommodity->getCommodity($sampleInfo['commodity_code']);
			//Get Sample Type
			$sample_type = $this->MSampleType->getSampleType($sampleInfo['sample_type_code']);
		
			///Custmer IDs
			$customer_list = [];

			$this->loadModel('DmiMmrActionFinalSubmits');

			foreach ($this->getCaDetails() as $subarray) {
				foreach ($subarray as $customer) {
					$action_taken = $this->DmiMmrActionFinalSubmits->find()
						->where(['customer_id' => $customer['customer_id']])
						->orderDesc('id')
						->first();
					
					if (empty($action_taken)) {
						$customer_list[$customer['customer_id']] = $customer['customer_id'] . ' - ' . $customer['firm_name'];
					}
				}
			}

			//Check if the selected sample code is already exist in the packer-sample code log table 
			$isAlreadyExist = $this->DmiMmrSamplePackerLogs->detailsOfSample($sample_code);
			if (empty($isAlreadyExist)) {
				$isAlreadyExist = array();
			}
			
			//Check if the selected sample code is already exist in the final submit table
			$isSampleSaved = $this->DmiMmrFinalSubmits->find()->where(['sample_code IS' => $sample_code])->order('id DESC')->first();
			if (empty($isSampleSaved)) {
				$isSampleSaved = array();
			}
			
			//check if the sample report is allocated to scrutinizer
			$isSampleAllocated = $this->DmiMmrAllocations->find()->where(['sample_code IS' => $sample_code])->order('id DESC')->first();
			if (empty($isSampleAllocated)) {
				$isSampleAllocated = array();
			}

			$this->set('isSampleAllocated',$isSampleAllocated);
			$this->set('isAlreadyExist',$isAlreadyExist);
			$this->set('isSampleSaved',$isSampleSaved);
			$this->set('sample_code',$sampleInfo['org_sample_code']);
			$this->set('category_name',$category_name);
			$this->set('commodity_name',$commodity_name);
			$this->set('sample_type',$sample_type);
			$this->set('customer_list',$customer_list);
			$this->set('commodity_code',$sampleInfo['commodity_code']);
			
		}

		//To Save The Data
		if (null!==($this->request->getData('save_details'))) {

			//Save in the Database
			if($this->DmiMmrFinalSubmits->saveData($this->request->getData())){

				//SMS: Sample Attached
				$this->DmiMmrSmsTemplates->sendMessage(5,$this->request->getData('packers_id'),$sample_code);

				$message_theme = 'success';
				$message = 
				'	
					The Details for the Sample Code and the Packer are Saved Successfully.
					Now, You can choose any of these options based on the requirements and circumstances surrounding the report.: <br>
					1.Allocate the report: Assign the report to a scrutinizer who will review and analyze it further.<br>
					2.Scrutinize the report: Personally review and examine the report for any discrepancies or issues.<br>
					3.Take action against the packer: If necessary, initiate appropriate actions or follow-up steps in response to the report findings.
					
				';
				$redirect_to = 'allocate_report';
			}

		} elseif (null!==($this->request->getData('send_comment'))){
			
			//html encoding post data before saving
			$htmlencoded_comment = htmlentities($this->request->getData('comment'), ENT_QUOTES);
			$comment_to = $this->request->getData('comment_to');
			$customer_id = $isSampleAllocated['customer_id'];
			$comment_by = $this->Session->read('username');

			if(!empty($comment_to) && !empty($htmlencoded_comment)){	
				
				if($comment_to == 'ro'){

					$comment_to_email_id = $allocation_deatils['level_3'];
					$available_to = 'ro';
					$sms_id = 3;
					$redirect_to_path = '../misgrading/allocated_reports_for_mo';

				}elseif($comment_to == 'mo'){

					$comment_to_email_id = $allocation_deatils['level_1'];
					$available_to = 'mo';
					$sms_id = 4;
					$redirect_to_path = '../misgrading/report_listing_for_allocation';
					
				}

				if(!empty($comment_to_email_id)){
					
					$saveComments = $this->DmiMmrRoMoComments->saveCommentDetails($customer_id,$sample_code,$comment_by,$comment_to_email_id,$htmlencoded_comment,$available_to);
					
					if($saveComments==true){
						
						//update allocation current level
						$this->DmiMmrAllocations->updateAll(array('current_level' => "$comment_to_email_id",'available_to' => "$available_to"),array('sample_code IS' => $sample_code));
						
						$last_ent =$this->DmiMmrAllocations->find()->select(['available_to'])->where(['sample_code IS' => $sample_code])->order(['id'=>'DESC'])->first();
					
						if ($last_ent['available_to'] == 'mo') {
							//Update the sample inward table report status 
							$this->SampleInward->updateAll(
								['report_status' => 'RO Replied', 'packer_id' => $customer_id],
								['org_sample_code' => $sample_code]
							);

						}elseif ($last_ent['available_to'] == 'ro'){
							//Update the sample inward table report status 
							$this->SampleInward->updateAll(
								['report_status' => 'MO Replied', 'packer_id' => $customer_id],
								['org_sample_code' => $sample_code]
							);
						}
						

						//SMS: Communication
						$this->DmiMmrSmsTemplates->sendMessage($sms_id,$customer_id,$sample_code);
						


						$message = 'Your Comment is successfully sent';
						$message_theme = 'success';
						$redirect_to = $redirect_to_path;
					}
				}

			} else{
				$message = 'Sorry.. User not selected or Comment box is blank';
				$message_theme = 'failed';
				$redirect_to = '../dashboard/home';
			}

		} elseif (null!==($this->request->getData('scrutiny'))){

			$current_level = 'level_3'; //forced to save the entry as from level_1, but done by RO

	
			$list_id = $this->DmiMmrFinalSubmits->find('list', array('valueField'=>'id', 'conditions'=>array('sample_code IS'=>$sample_code)))->toArray();
			$max_id = $this->DmiMmrFinalSubmits->find('all',array('fields'=>array('id','status'), 'conditions'=>array('id' => MAX($list_id))))->first();
	
			$fetch_id = $max_id['id'];
			$last_user_email_id = $_SESSION['username'];
			$last_record_id = $fetch_id;

			$getCustomerId = $this->DmiMmrSamplePackerLogs->find()->select(['customer_id'])->where(['sample_code IS' => $sample_code])->order('id DESC')->first();
			$customer_id = $getCustomerId['customer_id'];
			
			// calling custome method from model for accepted
			$form_accepted_result = $this->DmiMmrFinalSubmits->reportScrutinized($customer_id,$sample_code,$last_user_email_id,$current_level);
	
			if($form_accepted_result == 1){

				//This below action call is added t save the action log for the user by AKASH on 19-08-2022
				$this->Customfunctions->saveActionPoint('Report Scrutinized', 'Success');

				//SMS: Communication
				$this->DmiMmrSmsTemplates->sendMessage(6,$customer_id,$sample_code);

				$message = "Report is scrutinized and verified successfully";
				$message_theme = "success";
				$redirect_to =  '../misgrading/report_listing_for_allocation';

			}
		}

		// set variables to show popup messages from view file
		$this->set('message_theme', $message_theme);
		$this->set('message', $message);
		$this->set('redirect_to', $redirect_to);

		if ($message != null) {
			$this->render('/element/message_boxes');
		}
	
	}



	// DESCRIPTION : Show applicant email details for new audit changes
	// @AUTHOR : PRAVIN BHAKARE
	// @CONTRIBUTER : AKASH THAKRE (migration)
	// DATE : 25-02-2021
		
	public function getCaDetails() {

		$userName = $this->Session->read('username');
		$conn = ConnectionManager::get('default');
				
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
			$districtlist = $this->DmiDistricts->find('list',array('valueField'=>array('id'),'conditions'=>$conditionA))->toArray();
		} else {
			$districtlist = $this->DmiDistricts->find('list',array('valueField'=>array('id'),'conditions'=>$conditionB))->toArray();
		}


		$list = array();
		foreach ($districtlist as $each) {

			$firmDetails = $conn->execute("SELECT dgcp.customer_id, df.firm_name
											FROM dmi_firms AS df 
											INNER JOIN dmi_districts AS dd ON dd.id = df.district::INTEGER
											INNER JOIN dmi_certificate_types AS dct ON dct.id = df.certification_type::INTEGER
											INNER JOIN dmi_grant_certificates_pdfs AS dgcp ON df.customer_id = dgcp.customer_id 
											WHERE df.district='$each' AND df.certification_type='1'")->fetchAll('assoc');
			
			if (!empty($firmDetails)) {
				$list[] = $firmDetails;
			}
		}
		
		return $list;
	}


	//to generate report pdf for preview and store on server
	public function sampleTestReportCode($sample_code,$sample_test_mc){

		$this->Session->write('sample_test_code',$sample_code);
		$this->Session->write('sample_test_mc',$sample_test_mc);
		$this->redirect(array('controller'=>'misgrading','action'=>'sample_test_report'));
	}


	public function sampleTestReport(){

		$this->viewBuilder()->setLayout('pdf_layout');

		$this->loadModel('FinalTestResult');
		$this->loadModel('ActualTestData');
		$this->loadModel('CommGrade');
		$this->loadModel('CommGrade');
		
		
		$posted_ro_office = $this->DmiUsers->getPostedOffId($_SESSION['username']);
		$ro_office_name = $this->DmiRoOffices->getOfficeDetails($_SESSION['username']);
		$this->set('ro_office',$ro_office_name[0]);
		

		
		$conn = ConnectionManager::get('default');

		$commodity_code=$this->Session->read('sample_test_mc');
		$sample_code1=$this->Session->read('sample_test_code');

		

		$str1="SELECT org_sample_code FROM workflow WHERE display='Y' ";

		if ($sample_code1!='') {

			$str1.=" AND stage_smpl_cd='$sample_code1' GROUP BY org_sample_code"; /* remove trim fun on 01/05/2022 */
		}

		$sample_code2 = $conn->execute($str1);
		$sample_code2 = $sample_code2->fetchAll('assoc');

		$Sample_code = $sample_code2[0]['org_sample_code'];
		
		$getSampleType = $this->SampleInward->find('all',array('fields'=>'sample_type_code','conditions'=>array('org_sample_code IS' => $Sample_code)))->first();
		$sampleTypeCode = $getSampleType['sample_type_code'];
		$this->set('sampleTypeCode',$sampleTypeCode);
		
		$str2="SELECT stage_smpl_cd FROM workflow WHERE display='Y' ";

		if ($sample_code1!='') {
			$str2.=" AND org_sample_code='$Sample_code' AND stage_smpl_flag='AS' GROUP BY stage_smpl_cd";
		}

		$sample_code3 = $conn->execute($str2);
		$sample_code3 = $sample_code3->fetchAll('assoc');

		$Sample_code_as=trim($sample_code3[0]['stage_smpl_cd']);
		$this->set('Sample_code_as',$Sample_code_as);

		$this->loadModel('MSampleRegObs');

		$query2 = "SELECT msr.m_sample_reg_obs_code, mso.m_sample_obs_code, mso.m_sample_obs_desc, mst.m_sample_obs_type_code,mst.m_sample_obs_type_value
					FROM m_sample_reg_obs AS msr
					INNER JOIN m_sample_obs_type AS mst ON mst.m_sample_obs_type_code=msr.m_sample_obs_type_code
					INNER JOIN m_sample_obs AS mso ON mso.m_sample_obs_code=mst.m_sample_obs_code AND stage_sample_code='$Sample_code_as'
					GROUP BY msr.m_sample_reg_obs_code,mso.m_sample_obs_code,mso.m_sample_obs_desc,mst.m_sample_obs_type_code,mst.m_sample_obs_type_value";

		$method_homo = $conn->execute($query2);
		$method_homo = $method_homo->fetchAll('assoc');

		$this->set('method_homo',$method_homo);

		if (null!==($this->request->getData('ral_lab'))) {

			$data=$this->request->getData('ral_lab');

			$data1=explode("~",$data);

			if ($data1[0]!='all') {

				$ral_lab=$data1[0];
				$ral_lab_name=$data1[1];
				$this->set('ral_lab_name',$ral_lab_name);

			} else {

				$ral_lab=$data1[0];
				$ral_lab_name='all';
			}

		} else {

			$ral_lab='';
			$ral_lab_name='all';
		}




		$test = $this->ActualTestData->find('all', array('fields' => array('test_code'=>'distinct(test_code)'),'conditions' =>array('org_sample_code IS' => $Sample_code, 'display' => 'Y')))->toArray();

		$test_string=array();
		$test_string_ext=array();

		$i=0;

		foreach ($test as $each) {

			$test_string[$i]=$each['test_code'];
			$i++;
		}

		//new queries and conditions added on 03-02-2022 by Amol
		//to print NABL logo and ULR no. on final test report

		$showNablLogo = ''; $urlNo=''; $certNo='';
		//get NABL commosity and test details if exist
		$this->loadModel('LimsLabNablCommTestDetails');
		$NablTests = $this->LimsLabNablCommTestDetails->find('all',array('fields'=>'tests','conditions'=>array('lab_id IS'=>$posted_ro_office,'commodity IS'=>$commodity_code),'order'=>'id desc'))->first();

		if(!empty($NablTests)){
			//get NABL certifcate details
			$this->loadModel('LimsLabNablDetails');
			$NablDetails = $this->LimsLabNablDetails->find('all',array('fields'=>array('accreditation_cert_no','valid_upto_date'), 'conditions'=>array('lab_id IS'=>$posted_ro_office),'order'=>'id desc'))->first();
			//check validity //added str_replace on 14-09-2022 by Amol
			$validUpto = strtotime(str_replace('/','-',$NablDetails['valid_upto_date']));
			$curDate = strtotime(date('d-m-Y'));

			if($validUpto > $curDate){

				$showNablLogo = 'yes';
				$certNo = $NablDetails['accreditation_cert_no'];
				$curYear = date('y');
				//Custom array for Lab no.
				$labNoArr = array('55'=>'0','56'=>'1','45'=>'2','46'=>'3','47'=>'4','48'=>'5','49'=>'6','50'=>'7','51'=>'8','52'=>'9','53'=>'10','54'=>'11');
				$labNo = $labNoArr[$posted_ro_office];

				//get total report for respective lab for current year
				$newDate = '01-01-'.date('Y');
				$getReportsCounts = $this->Workflow->find('all',array('fields'=>'id','conditions'=>array('src_loc_id'=>$posted_ro_office,'stage_smpl_flag'=>'FG','date(tran_date) >=' =>$newDate,)))->toArray();
				$NoOfReport = '';
				for($i=0;$i<(8-(strlen(count($getReportsCounts))));$i++){
					$NoOfReport .= '0';
				}
				if(count($getReportsCounts)==0){
					$NoOfReport .= '1';
				}else{
					$NoOfReport .= count($getReportsCounts)+1;
				}


				$NablTests = explode(',',$NablTests['tests']);
				//compare tests arrays
				$result=array_diff($test_string,$NablTests);
				if(!empty($result)){$F_or_P = 'P';}else{$F_or_P = 'F';}

				$urlNo = 'ULR-'.$certNo.$curYear.$labNo.$NoOfReport.$F_or_P;

				//to get tests with accreditation
				$accreditatedtest = $this->ActualTestData->find('all', array('fields' => array('test_code'=>'distinct(test_code)'),'conditions' =>array('org_sample_code IS' => $Sample_code, 'test_code IN'=>$NablTests, 'display' => 'Y')))->toArray();
				$test_string=array();
				$i=0;
				foreach ($accreditatedtest as $each) {

					$test_string[$i]=$each['test_code'];
					$i++;
				}

				//to get tests without accreditation
				$nonAccreditatedtest = $this->ActualTestData->find('all', array('fields' => array('test_code'=>'distinct(test_code)'),'conditions' =>array('org_sample_code IS' => $Sample_code, 'test_code NOT IN'=>$NablTests, 'display' => 'Y')))->toArray();
				$i=0;
				foreach ($nonAccreditatedtest as $each) {

					$test_string_ext[$i]=$each['test_code'];
					$i++;
				}
			}
		}

		$this->set(compact('showNablLogo','urlNo','certNo'));

		foreach($test_string as $row1) {

			$query = $conn->execute("SELECT DISTINCT(grade.grade_desc),grade.grade_code,test_code
										FROM comm_grade AS cg
										INNER JOIN m_grade_desc AS grade ON grade.grade_code = cg.grade_code
										WHERE cg.commodity_code = '$commodity_code' AND cg.test_code = '$row1' AND cg.display = 'Y'");

			$commo_grade = $query->fetchAll('assoc');
			$str="";

			$this->set('commo_grade',$commo_grade );
		}

		$j=1;

		foreach ($test_string as $row) {

			$query = $conn->execute("SELECT cg.grade_code,cg.grade_value,cg.max_grade_value,cg.min_max
										FROM comm_grade AS cg
										INNER JOIN m_test_method AS tm ON tm.method_code = cg.method_code
										INNER JOIN m_test AS t ON t.test_code = cg.test_code
										WHERE cg.commodity_code = '$commodity_code' AND cg.test_code = '$row' AND cg.display = 'Y'
										ORDER BY cg.grade_code ASC");


			$data = $query->fetchAll('assoc');


			$query = $conn->execute("SELECT t.test_name,tm.method_name
										FROM comm_grade AS cg
										INNER JOIN m_test_method AS tm ON tm.method_code = cg.method_code
										INNER JOIN m_test AS t ON t.test_code = cg.test_code
										INNER JOIN test_formula AS tf ON tf.test_code = cg.test_code AND tm.method_code = cg.method_code
										WHERE cg.commodity_code = '$commodity_code' AND cg.test_code = '$row' AND cg.display = 'Y'
										ORDER BY t.test_name ASC");

			$data1 = $query->fetchAll('assoc');

			if (!empty($data1)) {

				$data_method_name = $data1[0]['method_name'];
				$data_test_name = $data1[0]['test_name'];

			} else {

				$data_method_name = '';
				$data_test_name = '';
			}


			$qry1 = "SELECT count(chemist_code)
						FROM final_test_result AS ftr
						INNER JOIN sample_inward AS si ON si.org_sample_code=ftr.org_sample_code AND si.result_dupl_flag='D' AND ftr.sample_code='$sample_code1'
						GROUP BY chemist_code ";

			$res2	= $conn->execute($qry1);
			$res2 = $res2->fetchAll('assoc');

			//get sample type code from sample sample inward table, to check if sample type is "Challenged"
			//if sample type is "challenged" then get report for selected final values only, no matter if single/duplicate analysis
			//applied on 27-10-2011 by Amol
			

			if($sampleTypeCode==4){
				$res2=array();//this will create report for selected final results, if this res set to blank
			}

			$count_chemist = '';
			$all_chemist_code = array();


			//get al  allocated chemist if sample is for duplicate analysis
			if (isset($res2[0]['count'])>0) {

					$all_chemist_code = $conn->execute("SELECT ftr.chemist_code
														FROM m_sample_allocate AS ftr
														INNER JOIN sample_inward AS si ON si.org_sample_code=ftr.org_sample_code AND si.result_dupl_flag='D' AND ftr.sample_code='$sample_code1' ");

				$all_chemist_code= $all_chemist_code->fetchAll('assoc');

				$count_chemist = count($all_chemist_code);

			}

			//to get approved final result by Inward officer test wise
			$test_result= $this->FinalTestResult->find('list',array('valueField' => 'final_result','conditions' =>array('org_sample_code IS' => $Sample_code,'test_code' => $row,'display'=>'Y')))->toArray();

			//if sample is for duplicate analysis
			//so get result chmeist wise
			$result_D = '';
			$result = array();

			if (isset($res2[0]['count'])>0) {

				$i=0;

				foreach ($all_chemist_code as $each) {

					$chemist_code = $each['chemist_code'];

					//get result for each chemist_code
					$get_results = $this->ActualTestData->find('all',array('fields'=>array('result'),'conditions'=>array('org_sample_code IS' => $Sample_code,'chemist_code IS'=>$chemist_code,'test_code IS'=>$row,'display'=>'Y')))->first();

					$result[$i] = $get_results['result'];

					$i=$i+1;

				}


				//else get result from final test rsult
				//for single anaylsis this is fianl approved result array
			} else {

				if (count($test_result)>0) {

					foreach ($test_result as $key=>$val) {

						$result = $val;
					}
				} else {

					$result="";
				}
			}


			//for duplicate anaylsis this is final approved result array
			if (count($test_result)>0) {

				foreach ($test_result as $key=>$val) {
					$result_D= $val;
				}

			} else {
				$result_D="";
			}

			$commencement_date= $this->MSampleAllocate->find('all',array('order' => array('commencement_date' => 'asc'),'fields' => array('commencement_date'),'conditions' =>array('org_sample_code IS' => $Sample_code, 'display' => 'Y')))->toArray();
			$this->set('comm_date',$commencement_date[0]['commencement_date']);

			if (!empty($count_chemist)) {

				$count_chemist1 =  $count_chemist;
			} else {
				$count_chemist1 = '';
			}

			$this->set('count_test_result',$count_chemist1);


			$minMaxValue = '';

			foreach ($commo_grade as $key=>$val) {

				$key = $val['grade_code'];

				foreach ($data as $data4) {

					$data_grade_code = $data4['grade_code'];

					if ($data_grade_code == $key) {

						$grade_code_match = 'yes';

						if (trim($data4['min_max'])=='Min') {
							$minMaxValue = "<br>(".$data4['min_max'].")";
						}
						elseif (trim($data4['min_max'])=='Max') {
							$minMaxValue = "<br>(".$data4['min_max'].")";
						}
					}
				}

			}

			$str.="<tr><td>".$j."</td><td>".$data_test_name.$minMaxValue."</td>";
			//$sampleTypeCode = $getSampleType['sample_type_code'];/*  check the count of max value added on 01/06/2022 */

			if($sampleTypeCode!=8 && $sampleTypeCode!=9){/* if sample type food safety parameter & ILC could not show grade added on 01/06/2022  by shreeya */

				// Draw tested test reading values,
				foreach ($commo_grade as $key=>$val) {

					$key = $val['grade_code'];

					$grade_code_match = 'no';

					foreach ($data as $data4) {

						$data_grade_code = $data4['grade_code'];

						if ($data_grade_code == $key) {

							$grade_code_match = 'yes';

							if (trim($data4['min_max'])=='Range') {

								$str.="<td>".$data4['grade_value']."-".$data4['max_grade_value']."</td>";

							} elseif (trim($data4['min_max'])=='Min') {

								$str.="<td>".$data4['grade_value']."</td>";

							} elseif (trim($data4['min_max'])=='Max') {

								$str.="<td>".$data4['max_grade_value']."</td>";

							} elseif (trim($data4['min_max'])=='-1') {

								$str.="<td>".$data4['grade_value']."</td>";

							}
						}
					}

					if ($grade_code_match == 'no') {
						$str.="<td>---</td>";
					}

				}

			}
			//for duplicate analysis chemist wise results
			if ($count_chemist1>0) {

				for ($g=0;$g<$count_chemist;$g++) {
					$str.="<td align='center'>".$result[$g]."</td>";
				}

				//for final result column
				$str.="<td align='center'>".$result_D."</td>";

			//for single analysis final results
			} else {
				// start for max val according to food sefety parameter added on 01/06/2022 by shreeya
				$str.="<td>".$result."</td>";
				if($sampleTypeCode==8){
					$max_val = $data[0]['max_grade_value'];
					$str.="<td>".$max_val."</td>";
				}
				// end 01/06/2022
			}

			//$this->set('getSampleType',$getSampleType );

			$str.="<td>".$data_method_name."</td></tr>";
			$j++;
		}

		$this->set('table_str',$str);


		/*
		Starts here
		to bifurcate accredited and non accredited test parameters on report
		The conditional non accredited tests logic starts here for NABL non accredited test results.
		The code is repitition of the logic from above code.
		on 09-08-2022 by Amol
		*/
		foreach($test_string_ext as $row1) {

			$query = $conn->execute("SELECT DISTINCT(grade.grade_desc),grade.grade_code,test_code
										FROM comm_grade AS cg
										INNER JOIN m_grade_desc AS grade ON grade.grade_code = cg.grade_code
										WHERE cg.commodity_code = '$commodity_code' AND cg.test_code = '$row1' AND cg.display = 'Y'");

			$commo_grade = $query->fetchAll('assoc');
			$str2="";

			$this->set('commo_grade',$commo_grade );
		}

		$j=1;

		foreach ($test_string_ext as $row) {

			$query = $conn->execute("SELECT cg.grade_code,cg.grade_value,cg.max_grade_value,cg.min_max
										FROM comm_grade AS cg
										INNER JOIN m_test_method AS tm ON tm.method_code = cg.method_code
										INNER JOIN m_test AS t ON t.test_code = cg.test_code
										WHERE cg.commodity_code = '$commodity_code' AND cg.test_code = '$row' AND cg.display = 'Y'
										ORDER BY cg.grade_code ASC");

			$data = $query->fetchAll('assoc');


			$query = $conn->execute("SELECT t.test_name,tm.method_name
										FROM comm_grade AS cg
										INNER JOIN m_test_method AS tm ON tm.method_code = cg.method_code
										INNER JOIN m_test AS t ON t.test_code = cg.test_code
										INNER JOIN test_formula AS tf ON tf.test_code = cg.test_code AND tm.method_code = cg.method_code
										WHERE cg.commodity_code = '$commodity_code' AND cg.test_code = '$row' AND cg.display = 'Y'
										ORDER BY t.test_name ASC");

			$data1 = $query->fetchAll('assoc');

			if (!empty($data1)) {

				$data_method_name = $data1[0]['method_name'];
				$data_test_name = $data1[0]['test_name'];

			} else {

				$data_method_name = '';
				$data_test_name = '';
			}


			$qry1 = "SELECT count(chemist_code)
						FROM final_test_result AS ftr
						INNER JOIN sample_inward AS si ON si.org_sample_code=ftr.org_sample_code AND si.result_dupl_flag='D' AND ftr.sample_code='$sample_code1'
						GROUP BY chemist_code ";

			$res2	= $conn->execute($qry1);
			$res2 = $res2->fetchAll('assoc');

			//get sample type code from sample sample inward table, to check if sample type is "Challenged"
			//if sample type is "challenged" then get report for selected final values only, no matter if single/duplicate analysis
			//applied on 27-10-2011 by Amol
			//$getSampleType = $this->SampleInward->find('all',array('fields'=>'sample_type_code','conditions'=>array('org_sample_code IS' => $Sample_code)))->first();
			//$sampleTypeCode = $getSampleType['sample_type_code'];

			if($sampleTypeCode==4){
				$res2=array();//this will create report for selected final results, if this res set to blank
			}

			$count_chemist = '';
			$all_chemist_code = array();


			//get al  allocated chemist if sample is for duplicate analysis
			if (isset($res2[0]['count'])>0) {

					$all_chemist_code = $conn->execute("SELECT ftr.chemist_code
														FROM m_sample_allocate AS ftr
														INNER JOIN sample_inward AS si ON si.org_sample_code=ftr.org_sample_code AND si.result_dupl_flag='D' AND ftr.sample_code='$sample_code1' ");

				$all_chemist_code= $all_chemist_code->fetchAll('assoc');

				$count_chemist = count($all_chemist_code);

			}

			//to get approved final result by Inward officer test wise
			$test_result= $this->FinalTestResult->find('list',array('valueField' => 'final_result','conditions' =>array('org_sample_code IS' => $Sample_code,'test_code' => $row,'display'=>'Y')))->toArray();

			//if sample is for duplicate analysis
			//so get result chmeist wise
			$result_D = '';
			$result = array();

			if (isset($res2[0]['count'])>0) {

				$i=0;

				foreach ($all_chemist_code as $each) {

					$chemist_code = $each['chemist_code'];

					//get result for each chemist_code
					$get_results = $this->ActualTestData->find('all',array('fields'=>array('result'),'conditions'=>array('org_sample_code IS' => $Sample_code,'chemist_code IS'=>$chemist_code,'test_code IS'=>$row,'display'=>'Y')))->first();

					$result[$i] = $get_results['result'];

					$i=$i+1;

				}


				//else get result from final test rsult
				//for single anaylsis this is fianl approved result array
			} else {

				if (count($test_result)>0) {

					foreach ($test_result as $key=>$val) {

						$result = $val;
					}
				} else {

					$result="";
				}
			}


			//for duplicate anaylsis this is final approved result array
			if (count($test_result)>0) {

				foreach ($test_result as $key=>$val) {
					$result_D= $val;
				}
			} else {
				$result_D="";
			}

			$commencement_date= $this->MSampleAllocate->find('all',array('order' => array('commencement_date' => 'asc'),'fields' => array('commencement_date'),'conditions' =>array('org_sample_code IS' => $Sample_code, 'display' => 'Y')))->toArray();
			$this->set('comm_date',$commencement_date[0]['commencement_date']);

			if (!empty($count_chemist)) {

				$count_chemist1 =  $count_chemist;
			} else {
				$count_chemist1 = '';
			}

			$this->set('count_test_result',$count_chemist1);


			$minMaxValue = '';

			foreach ($commo_grade as $key=>$val) {

				$key = $val['grade_code'];

				foreach ($data as $data4) {

					$data_grade_code = $data4['grade_code'];

					if ($data_grade_code == $key) {

						$grade_code_match = 'yes';

						if (trim($data4['min_max'])=='Min') {
							$minMaxValue = "<br>(".$data4['min_max'].")";
						}
						elseif (trim($data4['min_max'])=='Max') {
							$minMaxValue = "<br>(".$data4['min_max'].")";
						}
					}
				}

			}

			$str2.="<tr><td>".$j."</td><td>".$data_test_name.$minMaxValue."</td>";
			//$sampleTypeCode = $getSampleType['sample_type_code'];/*  check the count of max value added on 01/06/2022 */

			if($sampleTypeCode!=8){/* if sample type food safety parameter added on 01/06/2022  by shreeya */

				// Draw tested test reading values,
				foreach ($commo_grade as $key=>$val) {

					$key = $val['grade_code'];

					$grade_code_match = 'no';

					foreach ($data as $data4) {

						$data_grade_code = $data4['grade_code'];

						if ($data_grade_code == $key) {

							$grade_code_match = 'yes';

							if (trim($data4['min_max'])=='Range') {

								$str2.="<td>".$data4['grade_value']."-".$data4['max_grade_value']."</td>";

							} elseif (trim($data4['min_max'])=='Min') {

								$str2.="<td>".$data4['grade_value']."</td>";

							} elseif (trim($data4['min_max'])=='Max') {

								$str2.="<td>".$data4['max_grade_value']."</td>";

							} elseif (trim($data4['min_max'])=='-1') {

								$str2.="<td>".$data4['grade_value']."</td>";

							}
						}
					}

					if ($grade_code_match == 'no') {
						$str2.="<td>---</td>";
					}

				}

			}
			//for duplicate analysis chemist wise results
			if ($count_chemist1>0) {

				for ($g=0;$g<$count_chemist;$g++) {
					$str2.="<td align='center'>".$result[$g]."</td>";
				}

				//for final result column
				$str2.="<td align='center'>".$result_D."</td>";

			//for single analysis final results
			} else {
				// start for max val according to food sefety parameter added on 01/06/2022 by shreeya
				$str2.="<td>".$result."</td>";
				if($sampleTypeCode==8){
					$max_val = $data[0]['max_grade_value'];
					$str2.="<td>".$max_val."</td>";
				}
				// end 01/06/2022
			}
			//$this->set('getSampleType',$getSampleType );

			$str2.="<td>".$data_method_name."</td></tr>";
			$j++;
		}

		$this->set('table_str2',$str2 );
		/*
		Ends here
		The conditional non accredited tests logic ends here for NABL non accredited test results.
		The code is repitition of the logic from above code.
		on 09-08-2022 by Amol
		*/

		//added to by pass ilc report without grading
		//as in ilc there is no grading at all
		//on 11-08-2022 by shreeya
		if($sampleTypeCode == 9){

			$checktestallocation = "";
			$allocatefield = "";
			if(empty($checkifmainilc)){
				$allocatefield = "sa.sample_code,";
				$checktestallocation = "INNER JOIN m_sample_allocate AS sa ON sa.org_sample_code = si.org_sample_code";
			}

			$query = $conn->execute("SELECT si.*,mc.commodity_name, mcc.category_name, st.sample_type_desc, ct.container_desc, pc.par_condition_desc, uw.unit_weight, rf.ro_office,".$allocatefield." ur.user_flag, u1.f_name, u1.l_name, rf2.ro_office
								FROM sample_inward AS si
								INNER JOIN m_commodity AS mc ON mc.commodity_code = si.commodity_code
								INNER JOIN m_commodity_category AS mcc ON mcc.category_code = si.category_code
								INNER JOIN m_sample_type AS st ON st.sample_type_code = si.sample_type_code
								INNER JOIN m_container_type AS ct ON ct.container_code = si.container_code
								INNER JOIN m_par_condition AS pc ON pc.par_condition_code = si.par_condition_code
								INNER JOIN dmi_ro_offices AS rf ON rf.id = si.loc_id
								INNER JOIN dmi_ro_offices AS rf2 ON rf2.id = si.grade_user_loc_id
								INNER JOIN m_unit_weight AS uw ON uw.unit_id = si.parcel_size
								".$checktestallocation."
								INNER JOIN dmi_users AS u ON u.id = si.user_code
								INNER JOIN dmi_users AS u1 ON u1.id = si.grade_user_cd
								INNER JOIN dmi_user_roles AS ur ON u.email = ur.user_email_id
								WHERE si.org_sample_code = '$Sample_code' ");

			$test_report = $query->fetchAll('assoc');

			/* else forother sample types reports*/
		} else {

			$query = $conn->execute("SELECT si.*,mc.commodity_name, mcc.category_name, st.sample_type_desc, ct.container_desc, pc.par_condition_desc, uw.unit_weight, rf.ro_office, sa.sample_code, ur.user_flag, gd.grade_desc, u1.f_name, u1.l_name, rf2.ro_office
								FROM sample_inward AS si
								INNER JOIN m_commodity AS mc ON mc.commodity_code = si.commodity_code
								INNER JOIN m_commodity_category AS mcc ON mcc.category_code = si.category_code
								INNER JOIN m_sample_type AS st ON st.sample_type_code = si.sample_type_code
								INNER JOIN m_container_type AS ct ON ct.container_code = si.container_code
								INNER JOIN m_par_condition AS pc ON pc.par_condition_code = si.par_condition_code
								INNER JOIN dmi_ro_offices AS rf ON rf.id = si.loc_id
								INNER JOIN dmi_ro_offices AS rf2 ON rf2.id = si.grade_user_loc_id
								INNER JOIN m_unit_weight AS uw ON uw.unit_id = si.parcel_size
								INNER JOIN m_sample_allocate AS sa ON sa.org_sample_code = si.org_sample_code
								INNER JOIN dmi_users AS u ON u.id = si.user_code
								INNER JOIN dmi_users AS u1 ON u1.id = si.grade_user_cd
								INNER JOIN dmi_user_roles AS ur ON u.email = ur.user_email_id
								INNER JOIN m_grade_desc AS gd ON gd.grade_code = si.grade
								WHERE si.org_sample_code = '$Sample_code'");

			$test_report = $query->fetchAll('assoc');
		}

		if($test_report){

			$query = $conn->execute("SELECT ur.user_flag,office.ro_office,usr.email
										FROM workflow AS w
										INNER JOIN dmi_ro_offices AS office ON office.id = w.src_loc_id
										INNER JOIN dmi_users AS usr ON usr.id=w.src_usr_cd
										INNER JOIN dmi_user_roles AS ur ON usr.email= ur.user_email_id
										WHERE w.org_sample_code='$Sample_code'
										AND stage_smpl_flag IN('OF','HF')");

			$sample_forwarded_office = $query->fetchAll('assoc');

			$sample_final_date = $this->Workflow->find('all',array('fields'=>'tran_date','conditions'=>array('stage_smpl_flag'=>'FG','org_sample_code IS'=>$Sample_code)))->first();
			$sample_final_date['tran_date'] = date('d/m/Y');//taking current date bcoz creating pdf before grading for preview.

			//Customer Details on 05-08-2022 by akash
			$this->loadModel('LimsCustomerDetails');
			$customerDetails = $this->LimsCustomerDetails->find('all')->where(['org_sample_code IS' => $Sample_code])->first();
			if (!empty($customerDetails)) {
				$customer_details = $customerDetails;

				$stateAndDistrict = $conn->execute("SELECT ds.state_name,dd.district_name
													FROM lims_customer_details AS lcd
													INNER JOIN dmi_states AS ds ON ds.id = lcd.state
													INNER JOIN dmi_districts AS dd ON dd.id = lcd.district
													WHERE lcd.org_sample_code = '$Sample_code'")->fetch('assoc');
				if (!empty($stateAndDistrict)) {
					$this->set('stateAndDistrict',$stateAndDistrict);
				} else {
					$stateAndDistrict = null;
				}

			} else {
				$customer_details = null;
			}

			$this->set('sample_final_date',$sample_final_date['tran_date']);
			$this->set('sample_forwarded_office',$sample_forwarded_office);
			$this->set('test_report',$test_report);
			$this->set('customer_details',$customer_details);

			// Call to function for generate pdf file,
			// change generate pdf file name,
			$current_date = date('d-m-Y');
			$test_report_name = 'grade_report_'.trim($sample_code1).'.pdf';

			//store pdf path to sample inward table to preview further
			//store link only after esign done.
			//ajax condition added on 23-01-2023  By Shreya 
			if($this->request->is('ajax')){
				$pdf_path = '/testdocs/LIMS/reports/'.$test_report_name;
				$this->SampleInward->updateAll(array('report_pdf'=>"$pdf_path"),array('org_sample_code'=>$Sample_code));
			}

			$this->Session->write('pdf_file_name',$test_report_name);

			//Send parameter for Sample Test Report to getQrCodeSampleTestReport function
			// Author : Shankhpal Shende
			// Description : This will send parameter for QR code for Sample Test Report
			// Date : 01/09/2022
			$result_for_qr = $this->Customfunctions->getQrCodeSampleTestReport($Sample_code_as,$sample_forwarded_office,$test_report);
			$this->set('result_for_qr',$result_for_qr);

			//call to the pdf creation common method
			if($this->request->is('ajax')){//on consent check box click
				$this->EsigncallTcpdf($this->render(),'F',$test_report_name);//to save and store

			}else{//on preview link click
				$this->EsigncallTcpdf($this->render(),'I',$test_report_name);//to preview
			}


		}

	}

	//this function is created to generate pdf with empty signature content space. for esign
	public function EsigncallTcpdf($html,$mode,$file_name=null){

		//generatin pdf starts here
		//create new pdf using tcpdf
		require_once(ROOT . DS .'vendor' . DS . 'tcpdf' . DS . 'tcpdf.php');
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

			$pdf->SetFooterMargin(5);

			//to set signature content block in pdf
			$info = array();
			$pdf->my_set_sign('', '', '', '', 2, $info);

			$pdf->AddPage();

			$pdf->writeHTML($html, true, false, true, false, '');

			//start to add bg image for the 'esigned by' cell on document
			// get the current page break margin
			$bMargin = $pdf->getBreakMargin();
			// get current auto-page-break mode
			$auto_page_break = $pdf->getAutoPageBreak();
			// restore auto-page-break status
			$pdf->SetAutoPageBreak($auto_page_break, $bMargin);
			// set the starting point for the page content
			$pdf->setPageMark();
			//end to add bg image on cell

			//sig appearence will only for F mode when save and store file
			if($mode=='F'){
				$esigner = $this->Session->read('f_name').' '.$this->Session->read('l_name');

				// set bacground image on cell
				//to show esigned by block on pdf
				$img_file = 'img/checked.png';
				$pdf->Image($img_file, 165, 266, 8, 8, '', '', '', false, 300, '', false, false, 0);

				$pdf->SetFont('times', '', 8);
				$pdf->setCellPaddings(1, 2, 1, 1);
				$pdf->MultiCell(40, 10, 'Esigned by: '.$esigner."\n".'Date: '.$_SESSION['sign_timestamp'], 1, '', 0, 1, 150, 265, true);

				// define active area for signature appearance
				$pdf->setSignatureAppearance(150, 265, 40, 10);

				// reset pointer to the last page
				$pdf->lastPage();
			}

			// Clean any content of the output buffer
			if(ob_get_length() > 0) {
				ob_end_clean();
			}

			if($file_name == null){
				$file_name = '';
			}
			$file_path = $_SERVER["DOCUMENT_ROOT"].'testdocs/LIMS/reports/'.$file_name;

			//Close and output PDF document
			$pdf->my_output($file_path, $mode);
			//generatin pdf ends here
	}



	//Description : This function is added to get the details of the firm and append to left of the form
	//Author : Akash Thakre
	//Date : 22-05-2023
	//For : MMR 
		
	public function getFirmDetails(){
		
		$this->autoRender = false;

		//PostData
		$customer_id = $this->request->getData('customer_id');
		$sample_code = $this->request->getData('sample_code');
		
		//if the customer id is blank 
		if (empty($customer_id)) {
			$customer_id_view = $this->DmiMmrSamplePackerLogs->find()->select(['customer_id'])->where(['sample_code' => $sample_code])->order('id DESC')->first();
			$customer_id = $customer_id_view['customer_id'];
		}

		//Get the firm details
		$firm_details = $this->DmiFirms->firmDetails(trim($customer_id));
		//Get the State name
		$state_name = $this->DmiStates->getStateNameById(trim($firm_details['state']));
		//Get the District name
		$district_name = $this->DmiDistricts->getDistrictNameById(trim($firm_details['district']));
		//Get if the sample code is already in the table DmiMmrSamplePackerLogs
		$status = $this->DmiMmrSamplePackerLogs->detailsOfSample(trim($sample_code));
		
		//check if the sample report is allocated to scrutinizer
		$isSampleAllocated = $this->DmiMmrAllocations->find()->where(['sample_code IS' => $sample_code])->order('id DESC')->first();
		if (empty($isSampleAllocated)) {
			$isSampleAllocated = 'not_allocated';
		} else {
			$isSampleAllocated = 'allocated';
		}

		$response = [
			'firm_name' => $firm_details->firm_name,
			'street_address' => $firm_details->street_address,
			'district_name' => $district_name,
			'state_name'=> $state_name,
			'postal_code'=> $firm_details->postal_code,
			'status' => $status,
			'isSampleAllocated' => $isSampleAllocated
		];
		
		echo '~' . json_encode($response) . '~';
		exit;
		
	}


	//Description : This function is added to get the details of sample
	//Author : Akash Thakre
	//Date : 22-05-2023
	//For : MMR 

	public function detailsOfSample(){

		$this->autoRender = false;
		$sample_code = $this->request->getData('sample_code');

		$status = $this->DmiMmrSamplePackerLogs->detailsOfSample(trim($sample_code));
		echo '~' . json_encode($status) . '~';
		exit;
	}


	//Description : This function is added to get the details of sample
	//Author : Akash Thakre
	//Date : 22-05-2023
	//For : MMR 

	public function statusForScrutinizer(){

		$this->autoRender = false;
		$sample_code = $this->request->getData('sample_code');
		$status = $this->DmiMmrAllocations->find()->where(['sample_code' => trim($sample_code)])->order('id DESC')->first();
		$firmDetails = $this->DmiFirms->firmDetails($status['customer_id']);
		$userDetails = $this->DmiUsers->getDetailsByEmail($status['level_3']);
	
		$resultArray = [
			'customer_id' => $status['customer_id'],
			'firm_name' => $firmDetails['firm_name'],
			'allocated_by' => $userDetails['f_name']." ".$userDetails['l_name'],
			'date' => $status['created']
		];
	
		echo '~' . json_encode($resultArray) . '~';
		exit;
	}


	// Description : To attach the customer id to the sample code 
	// Author : Akash Thakre
	// Date : 23-05-2023
	// For : MMR

	public function attachSamplePacker(){

		$this->autoRender = false;

		//Load Models

		//get the values from post
		$customer_id = $this->request->getData('customer_id');
		$sample_code = $this->request->getData('sample_code');
		$username = $this->Session->read('username');
		$office = $this->DmiUsers->getPostedOffId($username);


		$saveArray = [

			'customer_id' => trim($customer_id),
			'sample_code' => trim($sample_code),
			'attached_by' => trim($username),
			'office' => $office,
		];

		//Save the Entity 
		$result = $this->DmiMmrSamplePackerLogs->attachSampleWithPacker($saveArray);
		
		//Add the entry of the sample attched in the sample inward table
		if ($result) {
			
			$this->SampleInward->updateAll(
				['report_status' => 'Packer Attached', 'packer_id' => $customer_id],
				['org_sample_code' => $sample_code]
			);
		}

		echo '~' . $result . '~';
		exit;

	}


	// Description : To Remove the customer id to the sample code 
	// Author : Akash Thakre
	// Date : 23-05-2023
	// For : MMR
	// Follow Up Private Method : updateFinalSubmitDetails()

	public function removeSamplePacker()
	{
		$this->autoRender = false;

		// Get the values from post
		$customer_id = $this->request->getData('customer_id');
		$sample_code = $this->request->getData('sample_code');
		$username = $this->Session->read('username');
		$office = $this->DmiUsers->getPostedOffId($username);

		// Save the entity
		$samplePackerData = [
			'customer_id' => trim($customer_id),
			'sample_code' => trim($sample_code),
			'attached_by' => trim($username),
			'office' => $office,
		];
		$result = $this->DmiMmrSamplePackerLogs->removeSampleWithPacker($samplePackerData);

		if ($result) {

			$this->updateFinalSubmitDetails($customer_id, $sample_code);

			//Remove the entry of the sample attched in the sample inward table
			$this->SampleInward->updateAll(
				['report_status' => null, 'packer_id' => null],
				['org_sample_code' => $sample_code]
			);
		}

		echo '~' . $result . '~';
		exit;
	}

	// this is the private funtion for the Update the entries if sample is removed
	private function updateFinalSubmitDetails($customer_id, $sample_code)
	{
		$finalSubmitTable = $this->loadModel('DmiMmrFinalSubmits');
		$query = $finalSubmitTable->query();
		$subquery = $finalSubmitTable->find();
		$subquery
			->select(['id'])
			->where(['customer_id' => $customer_id, 'sample_code' => $sample_code])
			->order(['id' => 'DESC'])
			->limit(1);
		
		$result = $subquery->first(); // Retrieve the first result from the subquery
		
		if ($result) {
			$query
				->update()
				->set(['is_attached_packer_sample' => 'N'])
				->where(['id' => $result->id])
				->execute();
		}
	}





	// Description : Allocate the scrutiny Tab 
	// Author : Akash Thakre
	// Date : 25-05-2023
	// For : MMR

	public function popupForScrutiny(){

		$this->viewBuilder()->enableAutoLayout(false);//to stop showing blank page for ajax call

		$sample_code = trim($_POST['sample_code']);

		//Get the Sample Details from the Smaple code 
		$getAllocationDetails = $this->DmiMmrFinalSubmits->find()->where(['sample_code IS' => $sample_code])->order('id DESC')->first();
	
		$customer_id = $getAllocationDetails['customer_id'];
		$status = $getAllocationDetails['status'];
		$allocted_status = $getAllocationDetails['allocted'];
		if ($allocted_status == null) {
			$allocted = 'not_allocated';
		}else{
			$allocted = 'allocated';
		}

		$current_level = $this->Session->read('current_level');
		

		$this->loadModel('DmiUserRoles');
		$mo_users_list = $this->DmiUserRoles->find('list',array('keyField'=>'user_email_id','valueField'=>'user_email_id','conditions'=>array('allocate_lims_report'=>'yes')))->toArray();

		//function to get first & last name wise list
		$DashboardController = new DashboardController();
		$mo_users_list = $DashboardController->userNameList($mo_users_list);

		$this->set(compact('customer_id','sample_code','mo_users_list','status','allocted'));
		$this->render('popupForScrutiny');
	
	
	}


	// Description : Allocate the scrutiny Tab 
	// Author : Akash Thakre
	// Date : 25-05-2023
	// For : MMR

	public function allocateReportForScrutiny(){

		$this->autoRender= false;

		$customer_id = trim($_POST['customer_id']);
		$sample_code = trim($_POST['sample_code']);
		$mo_user_id = htmlentities($_POST['mo_user_id'], ENT_QUOTES);
		$current_date = date('d-m-Y H:i:s');

		//get allocation table name from flow wise tables
		$current_level = $this->Session->read('current_level');
		$allocation_by = $this->Session->read('allocation_by');
		$username = $this->Session->read('username');

		//get allocating officer user details
		$get_user_id = $this->DmiUsers->find('all',array('fields'=>'id','conditions'=>array('email IS'=>$username)))->first();
		$user_id = $get_user_id['id'];
		
		//get MO/SMO user details
		$user_details = $this->DmiUsers->find('all',array('conditions'=>array('email IS'=>$mo_user_id)))->first();
		$mo_posted_id = $user_details['posted_ro_office'];
		
		//get MO/SMO posted office
		$mo_office = $this->DmiRoOffices->find('all',array('conditions'=>array('id IS'=>$mo_posted_id)))->first();
		$mo_office = $mo_office['ro_office'];

	
		//Check the current level
		if ($this->Session->read('current_level') == 'level_3') {
			$comment_by = $this->Session->read('alloc_user_by');
			$comment_to = $this->Session->read('allocation_to');
			$comment = null;
			$available_to = 'mo';
		} else {
			
		}
		
		//Save Array of the allocation table
		$saveAllocationDetails = $this->DmiMmrAllocations->saveAllocationDetails($customer_id,$sample_code,$mo_user_id,$available_to);
			
		if($saveAllocationDetails == true){

			//Save the entry in the comments table
			//$this->DmiMmrRoMoComments->saveCommentDetails($customer_id,$sample_code,$comment_by,$comment_to,null,$available_to);

			//Update the entry in the sample inward table for the sample that it is allocated
			$this->SampleInward->updateAll(['report_status' => 'Allocated'],['org_sample_code' => $sample_code]);

		}
			
		#SMS: MMR Report Allocation
		$this->DmiMmrSmsTemplates->sendMessage(1,$customer_id,$sample_code);
		$this->DmiMmrSmsTemplates->sendMessage(2,$customer_id,$sample_code);

		$mo_array = [
			'mo_name' => $user_details['f_name']." ".$user_details['l_name'],
			'mo_email' => base64_decode($user_details['email'])
		];
	
		echo '~' . json_encode($mo_array) . '~';
		
		exit;

	}

	//Description : This function is added to get the details of sample
	//Author : Akash Thakre
	//Date : 22-05-2023
	//For : MMR 

	public function statusOfReportAlloactions(){

		$this->autoRender = false;
		$sample_code = $this->request->getData('sample_code');

		$status = $this->DmiMmrAllocations->detailsOfAllocations(trim($sample_code));
		echo '~' . json_encode($status) . '~';
		exit;
	}



	//Description : This function is added to get the details of sample
	//Author : Akash Thakre
	//Date : 22-05-2023
	//For : MMR 

	public function allocatedReportsForMo(){


		//Below all the Session Delete setfor the MMR Application - Akash [06-06-2023]
		$this->Session->Delete('alloc_user_by');
		$this->Session->Delete('allocation_to');
		$this->Session->Delete('sample_code');
		$this->Session->Delete('application_mode');

		$this->loadModel('DmiMmrAllocations');
		$this->loadModel('DmiFirms');

	
		$allocationDetails = $this->DmiMmrAllocations
		->find()
		->select([
			'customer_id', 'DmiMmrAllocations.id', 'sample_code', 'current_level', 'created', 'modified',
			'level_1', 'level_3', 'available_to', 'DmiUsers.f_name', 'DmiUsers.l_name','DmiUsers.email', 'SampleInward.report_status',
			'MCommodity.commodity_name', 'DmiFirms.firm_name','DmiFirms.email','DmiRoOffices.ro_office','DmiRoOffices.office_type'
		])
		->distinct(['DmiMmrAllocations.customer_id'])
		->order(['DmiMmrAllocations.customer_id', 'DmiMmrAllocations.id DESC'])
		->leftJoin(['DmiUsers' => 'dmi_users'], ['DmiUsers.email = DmiMmrAllocations.level_3'])
		->leftJoin(['SampleInward' => 'sample_inward'], ['SampleInward.org_sample_code = DmiMmrAllocations.sample_code'])
		->leftJoin(['MCommodity' => 'm_commodity'], ['MCommodity.commodity_code = SampleInward.commodity_code'])
		->leftJoin(['DmiFirms' => 'dmi_firms'], ['DmiFirms.customer_id = DmiMmrAllocations.customer_id'])
		->leftJoin(['DmiRoOffices' => 'dmi_ro_offices'], ['DmiRoOffices.id = DmiUsers.posted_ro_office'])
		->where(['level_1' => $this->Session->read('username')])
		->toArray();
			//pr($allocationDetails); exit;
		$this->set('allocationDetails',$allocationDetails);
		
	}





}
?>