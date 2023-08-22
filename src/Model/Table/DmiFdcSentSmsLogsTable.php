<?php
namespace app\Model\Table;
use Cake\ORM\Table;
use App\Model\Model;
use App\Controller\AppController;
use App\Controller\CustomersController;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

class DmiFdcSentSmsLogsTable extends Table{

	public function saveLog($message_id,$mobile_no,$sms_text,$template_id){

		$entity = $this->newEntity(array(

			'message_id'=>$message_id,
			'destination'=>$mobile_no,
			'mid'=>null,
			'sent_date'=>date('Y-m-d H:i:s'),
			'message'=>$sms_text,
			'created'=>date('Y-m-d H:i:s'),
			'template_id'=>$template_id
		));

		$this->save($entity);
	}
}
?>