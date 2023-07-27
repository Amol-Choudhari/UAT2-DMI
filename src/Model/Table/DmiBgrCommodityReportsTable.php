<?php 
	namespace app\Model\Table;
	use Cake\ORM\Table;
	use App\Model\Model;
	use Cake\ORM\TableRegistry;
	use App\Controller\AppController;
	use App\Controller\CustomersController;
	
	class DmiBgrCommodityReportsTable extends Table{
	
	var $name = "DmiBgrCommodityReports";
	
	
	public function sectionFormDetails($customer_id){

			$latest_id = $this->find('list', array('valueField'=>'id', 'conditions'=>array('customer_id IS'=>$customer_id)))->toArray();
				
			if($latest_id != null){
				$form_fields = $this->find('all', array('conditions'=>array('id'=>MAX($latest_id))))->first();		
				
				$form_fields_details = $form_fields;
				
			}else{
				
				$form_fields_details = Array ( 'id'=>"", 'customer_id' => "",
				'reffered_back_comment' => "",
				'reffered_back_date' => "", 'form_status' =>"", 'customer_reply' =>"", 'customer_reply_date' =>"", 'approved_date' => "",
				'current_level' => "",'mo_comment' =>"", 'mo_comment_date' => "", 'ro_reply_comment' =>"", 'ro_reply_comment_date' =>"", 'delete_mo_comment' =>"", 'delete_ro_reply' => "",'delete_ro_referred_back' => "", 'delete_customer_reply' => "", 'ro_current_comment_to' => "",
				'rb_comment_ul'=>"",'mo_comment_ul'=>"",'rr_comment_ul'=>"",'cr_comment_ul'=>""); 
				
			}

			// to fetch CA details: Name of Packer with address and e-mail id
			$customer_id = $_SESSION['packer_id'];

			$DmiFirms = TableRegistry::getTableLocator()->get('DmiFirms');
			$DmiStates = TableRegistry::getTableLocator()->get('DmiStates');
			$Dmi_ro_office = TableRegistry::getTableLocator()->get('DmiRoOffices');
			$DmiApplWithRoMappings = TableRegistry::getTableLocator()->get('DmiApplWithRoMappings');

			$firm_details = $DmiFirms->firmDetails($customer_id);
		
			$state_id = $firm_details['state'];

			$fetch_state_name = $DmiStates->find('all',array('fields'=>'state_name','conditions'=>array('id IS'=>$state_id, 'OR'=>array('delete_status IS NULL','delete_status ='=>'no'))))->first();
			$state_name = $fetch_state_name['state_name'];
			
			$firmname = $firm_details['firm_name'];
			$email = $firm_details['email'];
			$address = $firm_details['street_address'];

			// to get RO/SO office
			$get_office = $DmiApplWithRoMappings->getOfficeDetails($customer_id);
			$region = $get_office['ro_office'];
			
			$CustomersController = new CustomersController;
			$export_unit_status = $CustomersController->Customfunctions->checkApplicantExportUnit($customer_id);
			
			// Get the current month and year
			$current_month = date('m');
			$current_year = date('y');

			// Calculate the "from" and "to" dates based on the current month
			if ($current_month >= 4 && $current_month <= 9) {
					$from_date = '04/01/' . ($current_year - 1);
					$to_date = '09/30/' . $current_year;
			} else {
					$from_date = '10/01/' . ($current_year - 1);
					$to_date = '03/31/' . $current_year;
			}
			

			return array($form_fields_details,$firmname,$email,$address,$state_name,$region,$export_unit_status,$from_date,$to_date);

	}
	
		
}

?>