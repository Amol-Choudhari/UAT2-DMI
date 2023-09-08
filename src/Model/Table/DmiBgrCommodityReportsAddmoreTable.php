<?php 
	namespace app\Model\Table;
	use Cake\ORM\Table;
	use App\Model\Model;
	use Cake\ORM\TableRegistry;
	use App\Controller\AppController;
	use App\Controller\CustomersController;
	
	class DmiBgrCommodityReportsAddmoreTable extends Table{
	
	var $name = "DmiBgrCommodityReportsAddmore";
	
	public function saveCommodityWiseReport($forms_data){
		
		// pr($forms_data);die;
		
		$customer_id = $_SESSION['packer_id'];
		$commodity = $forms_data['commodity'];
    $lotNo = $forms_data['lotno'];
    $dateSampling = $forms_data['datesampling'];
    $dateofPacking = $forms_data['dateofpacking'];
    $gradeAsign = $forms_data['grade'];
    $packetSize = $forms_data['packetsize'];
		$packetsizeunit = $forms_data['packetsizeunit'];
    $totalNoOfPackets = $forms_data['totalnoofpackets'];
    $totalQtyQuintal = $forms_data['totalqtyquintal'];
    $estimatedValue = $forms_data['estimatedvalue'];
    $agmarkReplicaFrom = $forms_data['agmarkreplicafrom'];
    $agmarkReplicaTo = $forms_data['agmarkreplicato'];
    $agmarkReplicaTotal = $forms_data['agmarkreplicatotal'];
    $replicaCharges = $forms_data['replicacharges'];
    $laboratoryName = isset($forms_data['laboratoryname'])?$forms_data['laboratoryname']:null;
    $reportNo = isset($forms_data['reportno'])?$forms_data['reportno']:null;
    $reportDate = isset($forms_data['reportdate'])?$forms_data['reportdate']:null;
    $remarks = isset($forms_data['remarks'])?$forms_data['remarks']:null;
		$period_from = $forms_data['period_from'];
		$period_to =  $forms_data['period_to'];

		if(!empty($forms_data['record_id'])){

			$newEntity = $this->newEntity(array(
				'id'=>$forms_data['record_id'],
				'customer_id'=>$customer_id,
				'commodity' =>$commodity,
				'lotno' => $lotNo,
				'datesampling' => $dateSampling,
				'dateofpacking' => $dateofPacking,
				'gradeasign' => $gradeAsign,
				'packetsize' => $packetSize,
				'packetsizeunit'=>$packetsizeunit,
				'totalnoofpackets' => $totalNoOfPackets,
				'totalqtyquintal' => $totalQtyQuintal,
				'estimatedvalue' => $estimatedValue,
				'agmarkreplicafrom' => $agmarkReplicaFrom,
				'agmarkreplicato' => $agmarkReplicaTo,
				'agmarkreplicatotal' => $agmarkReplicaTotal,
				'replicacharges' => $replicaCharges,
				'laboratoryname' => $laboratoryName,
				'reportno' => $reportNo,
				'reportdate' => $reportDate,
				'remarks' => $remarks,
				'created'=>date('Y-m-d H:i:s'),
				'modified'=>date('Y-m-d H:i:s')
			));

			if($this->save($newEntity)){
				echo "updated";
			}

		}else{
		$newEntity = $this->newEntity(array(
								
				'customer_id'=>$customer_id,
				'commodity' =>$commodity,
				'lotno' => $lotNo,
				'datesampling' => $dateSampling,
				'dateofpacking' => $dateofPacking,
				'gradeasign' => $gradeAsign,
				'packetsize' => $packetSize,
				'packetsizeunit'=>$packetsizeunit,
				'totalnoofpackets' => $totalNoOfPackets,
				'totalqtyquintal' => $totalQtyQuintal,
				'estimatedvalue' => $estimatedValue,
				'agmarkreplicafrom' => $agmarkReplicaFrom,
				'agmarkreplicato' => $agmarkReplicaTo,
				'agmarkreplicatotal' => $agmarkReplicaTotal,
				'replicacharges' => $replicaCharges,
				'laboratoryname' => $laboratoryName,
				'reportno' => $reportNo,
				'reportdate' => $reportDate,
				'remarks' => $remarks,
				'period_from' => $period_from,
				'period_to' => $period_to,
				'created'=>date('Y-m-d H:i:s'),
				'modified'=>date('Y-m-d H:i:s')
			));

			if($this->save($newEntity)){
				echo "added";
			}
	}
}

	public function getBgrData($id){
		
		$query = $this->find()
    ->where(['id' => $id, 'delete_status IS NULL']);
		return $editData = $query->first();
		

	}

	public function deleteBgrData($id){
		
		$newEntity = $this->newEntity(array(
				'id'=>$id,
				'delete_status'=>'yes',
				'modified'=>date('Y-m-d H:i:s')
			));

			if($this->save($newEntity)){
				return true;
			}
	}

	public function saveReplicaAllotmentData($forms_data){
			
			$customer_id = $_SESSION['packer_id'];

			$newEntity = $this->newEntity(array(

				
				'customer_id'=>$customer_id,
				'commodity' =>$forms_data['rpl_commodity'],
				'lotno' => $forms_data['rpl_lotno'],
				'datesampling' => $forms_data['rpl_datesampling'],
				'dateofpacking' => $forms_data['rpl_dateofpacking'],
				'gradeasign' => $forms_data['rpl_grade'],
				'packetsize' => $forms_data['rpl_packet_size'],
				'packetsizeunit'=>$forms_data['rpl_packet_size_unit'],
				'totalnoofpackets' => $forms_data['rpl_no_of_packets'],
				'totalqtyquintal' => $forms_data['rpl_qty_quantal'],
				'estimatedvalue' => $forms_data['rpl_estimatedvalue'],
				'agmarkreplicafrom' => $forms_data['rpl_alloted_rep_from'],
				'agmarkreplicato' => $forms_data['rpl_alloted_rep_to'],
				'agmarkreplicatotal' => $forms_data['rpl_total_quantity'],
				'replicacharges' => $forms_data['rpl_replicacharges'],
				'laboratoryname' => $forms_data['rpl_grading_lab'],
				'reportno' => $forms_data['rpl_reportno'],
				'reportdate' => $forms_data['rpl_reportdate'],
				'remarks' => $forms_data['rpl_remarks'],
				'period_from'=> $forms_data['period_from'],
				'period_to'=> $forms_data['period_to'],
				'replica_alloted_record'=>'yes',
				'created'=>date('Y-m-d H:i:s'),
				'modified'=>date('Y-m-d H:i:s')
			));

			if($this->save($newEntity)){
				echo "added";
			}

	}
		
}
