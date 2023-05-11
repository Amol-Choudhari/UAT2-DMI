<?php
	namespace app\Controller\Component;
	use Cake\Controller\Controller;
	use Cake\Controller\Component;
	use Cake\Controller\ComponentRegistry;
	use Cake\ORM\Table;
	use Cake\ORM\TableRegistry;
	use Cake\Datasource\EntityInterface;
	use Cake\Datasource\ConnectionManager;
	use Cake\Database\Expression\QueryExpression;									  

class ReportstatisticsComponent extends Component {
	
	public $components= array('Session');
	public $controller = null;
	public $session = null;

	public function initialize(array $config): void{
		parent::initialize($config);
		$this->Controller = $this->_registry->getController();
		$this->Session = $this->getController()->getRequest()->getSession();
	}

	public function backlog_app_processed($searchConditions, $from_date, $to_date){//from_date to_date added by laxmi on 13-20-2023

		
		$DmiFirms = TableRegistry::getTableLocator()->get('DmiFirms');//initialize model in component
		$final_submitT	= 'DmiFinalSubmits';//change table name by laxmi on 14-12-2023 
		
		$conditions = array();
		$date_conditions = array();
		
		if(array_key_exists("OR",$searchConditions)){
			$conditions = array('OR'=>$searchConditions['OR']);
		}
		if(array_key_exists("AND",$searchConditions)){
			$date_conditions = array('AND'=>$searchConditions['AND']);
		}
		if(!empty($searchConditions[0]) && $searchConditions[0]=='date(modified) BETWEEN :start AND :end'){//change condition by laxmi on 13-02-2023
			$date_conditions = $searchConditions[0];
		}		
		
		// $firm_result = $DmiFirms->find('list',array('fields'=>'customer_id','conditions'=>am($conditions,array('is_already_granted'=>'yes'))));
		// below query updated by Ankur Jangid as per cakePHP 4.2
		$firm_result = $DmiFirms->find('all')->where($conditions)->where(['is_already_granted IS' => 'yes'])->combine('id', 'customer_id')->toArray();
		
		$final_result = $this->inprocess_applications($firm_result,$final_submitT,$date_conditions,$from_date, $to_date);//added from_date and to_date by laxmi on 14-02-23
		return $final_result;
	}
	
	public function new_app_processed($searchConditions,$from_date, $to_date){//added from_date and to_date by laxmi on 14-02-23
		
		$DmiFirms = TableRegistry::getTableLocator()->get('DmiFirms');//initialize model in component
		$final_submitT	= 'DmiFinalSubmits';//change table name by laxmi on 14-12-2023 	
		
		$conditions = array();
		$date_conditions = array();

		if(array_key_exists("OR",$searchConditions)){
			// $conditions = array('OR'=>$searchConditions['OR']); 
			$conditions = ['OR'=>$searchConditions['OR']]; 
		}
		if(array_key_exists("AND",$searchConditions)){
			$date_conditions = array('AND'=>$searchConditions['AND']);
		}
		if(!empty($searchConditions[0]) && $searchConditions[0]=='date(modified) BETWEEN :start AND :end'){//change condition by laxmi on 13-02-2023
			$date_conditions = $searchConditions[0]; //print_r('2 - ReportstatisticsComponent'); exit;
		}
		
		// $firm_result = $DmiFirms->find('list',array('fields'=>'customer_id','conditions'=>am($conditions,array('is_already_granted !='=>'yes'))));
		// below query updated by Ankur Jangid as per cakePHP 4.2
		$firm_result = $DmiFirms->find('all')->where($conditions)->where(['is_already_granted IS NOT' => 'yes'])->combine('id', 'customer_id')->toArray();
	
		$final_result = $this->inprocess_applications($firm_result,$final_submitT,$date_conditions,$from_date, $to_date);//added from_date and to_date by laxmi on 14-02-23
		
		return $final_result;
	}
	
	public function renewal_app_processed($searchConditions,$from_date, $to_date){//added from_date and to_date by laxmi on 14-02-23
		
		$DmiFirms = TableRegistry::getTableLocator()->get('DmiFirms');//initialize model in component
		$final_submitT	= 'DmiRenewalFinalSubmits';	//change table name by laxmi on 14-12-2023 
		$conditions = array();
		$date_conditions = array();
		
		if(array_key_exists("OR",$searchConditions)){
			$conditions = array('OR'=>$searchConditions['OR']);
		}
		if(array_key_exists("AND",$searchConditions)){
			$date_conditions = array('AND'=>$searchConditions['AND']);
		}
		if(!empty($searchConditions[0]) && $searchConditions[0]=='date(modified) BETWEEN :start AND :end'){//change condition by laxmi on 13-02-2023
			$date_conditions = $searchConditions[0];
			
		}
		
		$firm_result = $DmiFirms->find('list',array('fields'=>'customer_id','conditions'=>$conditions));
		$final_result = $this->inprocess_applications($firm_result,$final_submitT,$date_conditions,$from_date, $to_date);//added from_date and to_date by laxmi on 14-02-23
		return $final_result;
	}
	
	public function inprocess_applications($firm_result,$final_submitT,$date_conditions,$from_date, $to_date){//added from_date and to_date by laxmi on 14-02-23
    	
		if(!empty($date_conditions)){
			$date = $date_conditions;

		}else{
			$date = array();
		}
		
		// $Dmi_final_submit = ClassRegistry::init($final_submitT);
		$DmiFinalSubmits = TableRegistry::getTableLocator()->get($final_submitT); // initialize model in component

		// $final_submit_result = $Dmi_final_submit->find('all',array('fields'=>array('DISTINCT customer_id'),'conditions'=>array('customer_id'=>$firm_result)));
		// below query updated by Ankur Jangid as per cakePHP 4.2

        // if condition added by laxmi with office else date on 14-02-2023 
		if(!empty($date)){	
		$final_submit_result = $DmiFinalSubmits->find('all')->select(['customer_id'])->distinct(['customer_id'])->where(['customer_id IN' => $firm_result])
		  ->where($date)
		  ->bind(':start', $from_date, 'date')->bind(':end', $to_date, 'date')
		  ->toArray();
		}else{
        $final_submit_result = $DmiFinalSubmits->find('all')->select(['customer_id'])->distinct(['customer_id'])->where(['customer_id IN' => $firm_result])->toArray();
		 }
		
		
		$tot = 0; $ca = 0; $pp = 0; $la = 0;
		$grant_tot = 0; $grant_ca = 0; $grant_pp = 0; $grant_la = 0; 	
		$application_inprocess_status = array();
		foreach($final_submit_result as $each_result){ 
			// $customer_id = $each_result[$final_submitT]['customer_id'];
			$customer_id = $each_result['customer_id']; 
			$explode = explode('/',$customer_id); 
			$application_type = $explode[1]; 
			
			// $grant_result = $Dmi_final_submit->find('first',array('fields'=>array('customer_id'),'conditions'=>am($date,array('customer_id'=>$customer_id,'current_level'=>'level_3','status'=>'approved'))));
			// below query updated by Ankur Jangid as per cakePHP 4.2

			//updated query by laxmi B with condtion for applied filter in date else not on 13-02-2023
			if(!empty($date)){
			$grant_result = $DmiFinalSubmits->find('all')->select(['customer_id'])
										->where($date)
										->where(['customer_id IS' => $customer_id, 'current_level' => 'level_3', 'status' => 'approved'])
										->bind(':start', $from_date, 'date')->bind(':end', $to_date, 'date')
										->first();
		 }else{
            $grant_result = $DmiFinalSubmits->find('all')->select(['customer_id'])->where($date)->where(['customer_id IS' => $customer_id, 'current_level' => 'level_3', 'status' => 'approved'])->first();
		 }
			if(empty($grant_result)){
				
				if($application_type == 1){
					$ca = $ca +1;
				}elseif($application_type == 2){
					$pp = $pp +1;
				}elseif($application_type == 3){
					$la = $la +1;
				}
				$tot = $tot + 1;
			}else{
				
				if($application_type == 1){
					$grant_ca = $grant_ca +1;
				}elseif($application_type == 2){
					$grant_pp = $grant_pp +1;
				}elseif($application_type == 3){
					$grant_la = $grant_la +1;
				}
				$grant_tot = $grant_tot + 1;
			}
		}
		//exit;
		$inprogress_application = array($ca,$pp,$la,$tot);
		  //added below in session by laxmi B.on 15-03-2023
		if(!empty($inprogress_application)){
			  $this->Session->write('inprogress_application', $inprogress_application);
			  $this->Session->write('in_ca', $ca);
			  $this->Session->write('in_pp', $pp);
			  $this->Session->write('in_la', $la);
		}
	
		$grant_application = array($grant_ca,$grant_pp,$grant_la,$grant_tot);
		//added below in session by laxmi B.on 15-03-2023
		if(!empty($grant_application)){
			  $this->Session->write('grant_application', $grant_application);
			  $this->Session->write('g_ca', $grant_ca);
			  $this->Session->write('g_pp', $grant_pp);
			  $this->Session->write('g_la', $grant_la);
		}
		return array($inprogress_application,$grant_application);		
	}
	
}
?>