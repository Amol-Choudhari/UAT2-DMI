<?php
	namespace app\Model\Table;
	use Cake\ORM\Table;
	use App\Model\Model;
	use Cake\ORM\TableRegistry;
	use App\Controller\CustomersController;

class DmiChemistEsignedStatusesTable extends Table{

	var $name = "DmiChemistEsignedStatuses";
	var $useTable = 'dmi_chemist_esigned_statuses';



		//added new method by laxmi B. on 04-01-2022
		public function getEsignedStatus($customer_id,$current_level){
		// set packer id as customer id for firmdata details & for grantDate check added by laxmi B.	
		
		$CustomersController = new CustomersController;

		$grantDateCondition = $CustomersController->Customfunctions->returnGrantDateCondition($customer_id,4); //added appl type 4 in parameter on 26-05-2023 by Amol

		//commented this line, not required packer id, chemist id is sufficient, on 26-05-2023 by Amol
		//$customer_id = $_SESSION['packer_id'];
		//create other model objects
		$Dmi_firm = TableRegistry::getTableLocator()->get('DmiFirms');
		$Dmi_flow_wise_tables_list = TableRegistry::getTableLocator()->get('DmiFlowWiseTablesLists');	

		$application_final_submit_table = $Dmi_flow_wise_tables_list->getFlowWiseTableDetails($_SESSION['application_type'],'application_form');
		$Dmi_final_submit = TableRegistry::getTableLocator()->get($application_final_submit_table);

		$inspection_report_table = $Dmi_flow_wise_tables_list->getFlowWiseTableDetails($_SESSION['application_type'],'inspection_report');
		$Dmi_siteinspection_final_report = TableRegistry::getTableLocator()->get($inspection_report_table);

		//check application type new/old 
		$get_type = $Dmi_firm->find('all',array('conditions'=>array('customer_id IS'=>$customer_id)))->first();

		//for chemist flow, let it throw 'new' always, no issue, i.e customer id not change with packer id above
		//on 26-05-2023 by Amol
		if(!empty($get_type) && $get_type['is_already_granted']=='yes'){			
		$type = 'old';
		}else{
		$type = 'new';
		}
		//Revert back customer id to chemist id
		//$customer_id = $_SESSION['customer_id'];//commented this line as not required, on 26-05-2023 by Amol
		$status = null;
		if($current_level == 'applicant'){
			//get application status from final submit table
			$get_ids = $Dmi_final_submit->find('list',array('conditions'=>array('customer_id IS'=>$customer_id,$grantDateCondition)))->toArray();
			if(!empty($get_ids)){
			$get_status = $Dmi_final_submit->find('all',array('conditions'=>array('id'=>max($get_ids))))->first();
				$status = $get_status['status'];
			}else{
				$status = 'pending';
			}

		}elseif($current_level == 'level_3'){
			//get application status from final submit table
			$get_ids = $Dmi_siteinspection_final_report->find('list',array('valueField'=>'id','conditions'=>array('customer_id IS'=>$customer_id,$grantDateCondition)))->toArray();

			if(!empty($get_ids)){
				$get_status = $Dmi_siteinspection_final_report->find('all',array('conditions'=>array('id'=>max($get_ids))))->first();
				$status = $get_status['status'];
				
				//added condition to set status as 'Granted', as current level is 3, on 26-05-2023 by Amol, for level 2 it is 'approved'
				if($status=='approved'){
					$status='Granted';
				}
			}else{
				$status = 'pending';
			}

		}

		//taking condition for query conditionally
		if($current_level == 'applicant' || $current_level == 'level_3'){
			$query_conditions = array('customer_id'=>$customer_id, 'application_type'=>$type, 'application_status'=>$status ,$grantDateCondition);
		}else{
			$query_conditions = array('customer_id'=>$customer_id, 'application_type'=>$type, $grantDateCondition);
		}

		$get_esign_details = $this->find('all',array('conditions'=>$query_conditions))->first();		
		$esign_status = 'no';

		if(!empty($get_esign_details)){

		if($current_level == 'applicant'){

		if($get_esign_details['application_esigned']=='yes'){

			$esign_status = 'yes';
		}
		}
		elseif($current_level == 'level_2'){

			if($get_esign_details['report_esigned']=='yes'){

				$esign_status = 'yes';
			}
		}
		elseif($current_level == 'level_3' || $current_level == 'level_4'){

			if($get_esign_details['certificate_esigned']=='yes'){

				$esign_status = 'yes';
			}
		}

		}

		return $esign_status;
		}

		//added new method by laxmi B. on 04-01-2022
		public function saveEsignStatus(){

		if(strpos(base64_decode($_SESSION['username']), '@') !== false){//for email encoding
		$customer_id = $_SESSION['customer_id'];

		}else{
		$customer_id = $_SESSION['username'];
		}

		$CustomersController = new CustomersController;
		$grantDateCondition = $CustomersController->Customfunctions->returnGrantDateCondition($customer_id,4);//added appl type 4 in parameter on 26-05-2023 by Amol

		$current_level = $_SESSION['current_level'];

		$Dmi_firm = TableRegistry::getTableLocator()->get('DmiFirms');

		$Dmi_flow_wise_tables_list = TableRegistry::getTableLocator()->get('DmiFlowWiseTablesLists');	

		$application_final_submit_table = $Dmi_flow_wise_tables_list->getFlowWiseTableDetails($_SESSION['application_type'],'application_form');
		$Dmi_final_submit = TableRegistry::getTableLocator()->get($application_final_submit_table);

		$inspection_report_table = $Dmi_flow_wise_tables_list->getFlowWiseTableDetails($_SESSION['application_type'],'inspection_report');
		$Dmi_siteinspection_final_report = TableRegistry::getTableLocator()->get($inspection_report_table);
		$application_status = 'pending';


		//check application type old/new
		$get_type = $Dmi_firm->find('all',array('conditions'=>array('customer_id IS'=>$customer_id)))->first();
		if(!empty($get_type) && $get_type['is_already_granted']=='yes'){			
		$application_type = 'old';
		}else{
		$application_type = 'new';
		}

		//check record in esign status table
		$check_esigned_record = $this->find('all',array('conditions'=>array('customer_id IS'=>$customer_id,$grantDateCondition)))->first();

		//if record exist then update that record
		if(!empty($check_esigned_record)){

		$application_status = null;
		if($current_level == 'applicant'){
		//get application status from final submit table
		$get_ids = $Dmi_final_submit->find('list',array('conditions'=>array('customer_id IS'=>$customer_id,$grantDateCondition)))->toArray();
		if(!empty($get_ids)){
		$get_status = $Dmi_final_submit->find('all',array('conditions'=>array('id'=>max($get_ids))))->first();
		$application_status = $get_status['status'];
		}else{
		$application_status = 'pending';
		}

		$esiging_level = 'application_esigned';

		}elseif($current_level == 'level_2'){
		//get application status from final submit table
		$get_ids = $Dmi_siteinspection_final_report->find('list',array('conditions'=>array('customer_id IS'=>$customer_id,$grantDateCondition)))->toArray();
		if(!empty($get_ids)){
		$get_status = $Dmi_siteinspection_final_report->find('all',array('conditions'=>array('id'=>max($get_ids))))->first();
		$application_status = $get_status['status'];
		}else{
		$application_status = 'pending';
		}

		$esiging_level = 'report_esigned';
		}elseif($current_level == 'level_3' || $current_level == 'level_4'){

		$esiging_level = 'certificate_esigned';
		$application_status = 'Granted';
		}

		$newEntity = $this->newEntity(array(		
		'id'=>$check_esigned_record['id'],
		$esiging_level=>'yes',
		'application_status'=>$application_status,
		'modified'=>date('Y-m-d H:i:s'),			
		));

		if($this->save($newEntity)){
		return true;
		}
		}
		else{//else create new entry in table


		//start - below logic added on 21-04-2018 by Amol, after applied optional esign for applicant
		if($current_level == 'applicant'){

		$esiging_level = 'application_esigned';
		$application_status = 'pending';

		}elseif($current_level == 'level_2'){

		//get application status from final submit table
		$get_ids = $Dmi_siteinspection_final_report->find('list',array('conditions'=>array('customer_id IS'=>$customer_id,$grantDateCondition)))->toArray();
		if(!empty($get_ids)){
		$get_status = $Dmi_siteinspection_final_report->find('all',array('conditions'=>array('id'=>max($get_ids))))->first();
		$application_status = $get_status['status'];
		}else{
		$application_status = 'pending';
		}

		$esiging_level = 'report_esigned';

		}elseif($current_level == 'level_3' || $current_level == 'level_4'){

		$esiging_level = 'certificate_esigned';
		$application_status = 'Granted';
		}


		$newEntity = $this->newEntity(array(		
		'customer_id'=>$customer_id,
		$esiging_level=>'yes',
		'application_type'=>$application_type,
		'application_status'=>$application_status,
		'created'=>date('Y-m-d H:i:s'),
		'modified'=>date('Y-m-d H:i:s'),
		));

		if($this->save($newEntity)){
		return true;
		}

		} 

		}






		}
?>
