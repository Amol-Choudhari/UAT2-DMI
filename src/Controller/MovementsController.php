<?php 
//added new file by Laxmi Bhadade for movement of application on 20-07-2023
namespace App\Controller;

use Cake\Event\Event;
use Cake\Network\Session\DatabaseSession;
use App\Network\Email\Email;
use App\Network\Request\Request;
use App\Network\Response\Response;
use Cake\Datasource\ConnectionManager;
use phpDocumentor\Reflection\Types\This;
use Cake\Http\Response\withHeader;

class MovementsController extends AppController {
    var $name = 'Movements';
    public function initialize(): void
		{
			parent::initialize();
            $this->viewBuilder()->setHelpers(['Form','Html','Time']);
			$this->Session = $this->getRequest()->getSession();
		}
        public function beforeFilter($event) {
            parent::beforeFilter($event);
            $username = $this->getRequest()->getSession()->read('username');

			if($username == null){
				$this->customAlertPage("Sorry You are not authorized to view this page..");
				exit();
			}else{
				$this->loadModel('DmiUsers');
				//check if user entry in Dmi_users table for valid user
				$check_user = $this->DmiUsers->find('all',array('conditions'=>array('email'=>$this->Session->read('username'))))->first();

				if(empty($check_user)){
					$this->customAlertPage("Sorry You are not authorized to view this page..");
					exit();
			    }
            }
        }
        
        public function movementHistory(){
            $this->viewBuilder()->setLayout('admin_dashboard');
            $this->loadModel('DmiApplicationTypes');
            $all_appl_type = $this->DmiApplicationTypes->find('all')->select(['id', 'application_type'])->where(['delete_status IS'=>NULL])->order(['id'=>'ASC'])->combine('id','application_type')->toArray();
            $this->set('applTypesList', $all_appl_type);

            if(NULL != $this->request->getData()){
                $reqdata = $this->request->getData();
                $appli_type = $reqdata['appl_type'];
                $appli_id = $reqdata['appl_id'];
                $this->loadModel('DmiFlowWiseTablesLists');
                $flowwiseTable = $this->DmiFlowWiseTablesLists->find('all',['fields'=>['application_form','inspection_report','ho_level_allocation','ama_approved_application','ho_comment_reply','allocation', 'commenting_with_mo','esign_status','payment','appl_current_pos','ro_so_comments','grant_pdf','level_4_ro_approved']])
                ->where(['application_type IS'=>$appli_type])->first();
               
                   $applicant = $this->loadModel($flowwiseTable['application_form']);
                   $payment = $this->loadModel($flowwiseTable['payment']);
                   $current = $this->loadModel($flowwiseTable['appl_current_pos']);
                   $allocation = $this->loadModel($flowwiseTable['allocation']);
                   $inspection = $this->loadModel($flowwiseTable['inspection_report']);
                   $ho_lev = $this->loadModel($flowwiseTable['ho_level_allocation']);
                   $ama = $this->loadModel($flowwiseTable['ama_approved_application']);
                   $ho_comment = $this->loadModel($flowwiseTable['ho_comment_reply']);
                   $mo_comment = $this->loadModel($flowwiseTable['commenting_with_mo']);
                   $esign = $this->loadModel($flowwiseTable['esign_status']);
                   $ro_So = $this->loadModel($flowwiseTable['ro_so_comments']);
                   $grant = $this->loadModel($flowwiseTable['grant_pdf']);
                   $leve4App= $this->loadModel($flowwiseTable['level_4_ro_approved']);
                   $this->loadModel('DmiFirms');
                   $this->loadModel('DmiRoOffices');
                   $this->loadModel('DmiUsers');
                   $this->loadModel('DmiPaoDetails');
                       
                   $to = array();
                   $from =array();
                   $sentdate = array();
                   $action = array();
                   $i=0;
                   //fetch using core join
                   $conn = ConnectionManager::get('default');

                   $isPaymentDone = $payment->find('all')->where(['customer_id IS'=>$appli_id])->order('modified DESC')->toArray();
                   $applicant_final= $applicant->find('all')->where(['customer_id IS'=>$appli_id])->first();
                   $current_pos = $current->find('all', ['conditions'=>['customer_id IS'=>$appli_id]] )->first();
                   $firm_details = $this->DmiFirms->find('all', ['fields'=>['firm_name','email'], 'conditions'=>['customer_id IS'=>$appli_id]])->first(); 

                   if(!empty($isPaymentDone) && !empty($applicant_final['status']) && !empty($current_pos)){
                     //DDO
                     
                     foreach($isPaymentDone as $isPayment){
                        if($isPayment['payment_confirmation'] == 'pending'){
                           //applicant to ddo
                            if(!empty($applicant_final)){
                                $pao_details = $this->DmiPaoDetails->find('all',['fields'=>['pao_user_id'], 'conditions'=>['id IS'=>$isPayment['pao_id']]])->first();
                                if(!empty($pao_details)){
                                $officer_details = $this->DmiUsers->find('all', ['fields'=>['f_name','l_name','role'], 'conditions'=>['id IS'=>$pao_details['pao_user_id']]])->first();
                                }
                                $to[] = $officer_details['f_name'].' '.$officer_details['l_name'].' '.$officer_details['role'];
                                $from[] = $firm_details['firm_name'];
                                $sentdate[] = $isPayment['modified'];
                                $action[] = 'payment is ' . $isPayment['payment_confirmation'];
                            }
                           

                        } elseif($isPayment['payment_confirmation'] == 'not_confirmed'){
                            //ddo reffered back 
                            if(!empty($applicant_final)){
                                $pao_details = $this->DmiPaoDetails->find('all',['fields'=>['pao_user_id'], 'conditions'=>['id IS'=>$isPayment['pao_id']]])->first();
                                if(!empty($pao_details)){
                                $officer_details = $this->DmiUsers->find('all', ['fields'=>['f_name','l_name','role'], 'conditions'=>['id IS'=>$pao_details['pao_user_id']]])->first();
                                }
                                $firm_details = $this->DmiFirms->find('all', ['fields'=>['firm_name','email'], 'conditions'=>['customer_id IS'=>$appli_id]])->first(); 
                                $from []= $officer_details['f_name'].' '.$officer_details['l_name'].' '.$officer_details['role'];
                                $sentdate[] = $isPayment['modified'];
                                $to[] = $firm_details['firm_name'];
                                $action[] = 'payment is ' . $isPayment['payment_confirmation'];
                                
                            }
                            
                        }elseif( $isPayment['payment_confirmation'] == 'replied' ){
                            if(!empty($applicant_final)){
                                $pao_details = $this->DmiPaoDetails->find('all',['fields'=>['pao_user_id'], 'conditions'=>['id IS'=>$isPayment['pao_id']]])->first();
                                if(!empty($pao_details)){
                                $officer_details = $this->DmiUsers->find('all', ['fields'=>['f_name','l_name','role'], 'conditions'=>['id IS'=>$pao_details['pao_user_id']]])->first();
                                }
                                $firm_details = $this->DmiFirms->find('all', ['fields'=>['firm_name','email'], 'conditions'=>['customer_id IS'=>$appli_id]])->first(); 
                                $to []= $officer_details['f_name'].' '.$officer_details['l_name'].' '.$officer_details['role'];
                                $sentdate[] = $isPayment['modified'];
                                $from[] = $firm_details['firm_name'];
                                $action[] = 'payment is ' . $isPayment['payment_confirmation'];
                                
                            }
                        }else{
                           //ddo approved application is in RO/SO side
                          if($isPayment['payment_confirmation'] == 'confirmed'){
                                    if($current_pos['current_level'] == 'level_3'){
                                        $officer_details =  $this->DmiUsers->find('all', ['fields'=>['f_name','l_name','role'], 'conditions'=>['email IS'=>$current_pos['current_user_email_id']]])->first();
                                    }elseif($current_pos['current_level'] == 'level_1' || $current_pos['current_level'] == 'applicant' || $current_pos['current_level'] == 'level_2'){
                                        // allocation table
                                         $roofficer_details = $allocation->find('all', ['fields'=>['level_3'], 'conditions'=>['customer_id IS'=>$appli_id]])->first();
                                       if(!empty($roofficer_details)){
                                          $officer_details   = $this->DmiUsers->find('all', ['fields'=>['f_name','l_name','role'], 'conditions'=>['email IS'=>$roofficer_details['level_3']]])->first();
                                    
                                        }
                                    }
                                   
                                        $pao_details = $this->DmiPaoDetails->find('all',['fields'=>['pao_user_id'], 'conditions'=>['id IS'=>$isPayment['pao_id']]])->first();
                                        if(!empty($pao_details)){
                                        $ddo = $this->DmiUsers->find('all', ['fields'=>['f_name','l_name','role'], 'conditions'=>['id IS'=>$pao_details['pao_user_id']]])->first();
                                        }
                           
                                        $to[] = $officer_details['f_name'].' '.$officer_details['l_name'].' '.$officer_details['role'];
                                        $from[] = $ddo['f_name'].' '.$ddo['l_name'].' '.$ddo['role'];
                                        $sentdate[] = $isPayment['modified'];
                                        $action[] = 'payment is ' . $isPayment['payment_confirmation'];
                                
                           }


                        }
                     }//foreach close
                     
                    
                    }elseif(!empty($current_pos) && !empty($applicant_final['status']) && empty($isPayment['payment_confirmation']) ){
                        //Ro/So
                       
                        if(($current_pos['current_level'] == 'level_3')  && ($applicant_final['status'] == 'pending' || $applicant_final['status'] == 'approved' ) ){
                            if(!empty($applicant_final)){
                                $ro_details = $this->DmiUsers->find('all', ['fields'=>['f_name','l_name','role'], 'conditions'=>['email IS'=>$current_pos['current_user_email_id']]])->first();
                                
                               
                                $firm_details = $this->DmiFirms->find('all', ['fields'=>['firm_name','email'], 'conditions'=>['customer_id IS'=>$appli_id]])->first(); 
                                $to[] = $ro_details['f_name'].' '.$ro_details['l_name'].' '.$ro_details['role'];
                                $from[] = $firm_details['firm_name'];
                                $sentdate[] = $current_pos['created'];
                                $action[] = 'Applicant forwarded to RO/SO';
                            }
                        }
                    }else{
                        //applicant side
                        if(!empty($applicant_final)){
                            $firm_details = $this->DmiFirms->find('all', ['fields'=>['firm_name','email'], 'conditions'=>['customer_id IS'=>$appli_id]])->first(); 
                            
                            $to[] = $firm_details['firm_name'];
                            $from[] ='Appliacant side';
                            $sentdate[] =  $applicant_final['created']; 
                            $action[] = 'Not forwarded yet';
                         }
                    }

                          $allcation_table = $allocation->find('all', ['conditions'=>['customer_id IS'=>$appli_id]])->first();#pr($allcation_table);exit;
                          $comment_by_mo   = $mo_comment->find('all', ['conditions'=>['customer_id IS'=>$appli_id]])->order(['modified'=>'desc'])->toArray();
                          $applicant_level3 = $applicant->find('all', ['conditions'=>['customer_id IS'=>$appli_id]])->order(['modified'=>'desc'])->toArray();
                        
                          if(!empty($allcation_table)){
                                if(!empty($allcation_table['level_3'])){           
                                 $officer_details   = $this->DmiUsers->find('all', ['fields'=>['f_name','l_name','role'], 'conditions'=>['email IS'=>$allcation_table['level_3']]])->first();
                                }
                               
                                 if(!empty($officer_details)){
                                  $from[] = $officer_details['f_name'].' '.$officer_details['l_name'].' '.$officer_details['role'];
                                 }
                                if(!empty($allcation_table['level_1'])){
                                  $officer_detailsCurrent   = $this->DmiUsers->find('all', ['fields'=>['f_name','l_name','role'], 'conditions'=>['email IS'=>$allcation_table['level_1']]])->first();
                                    if(!empty($officer_detailsCurrent)){
                                        $to[] = $officer_detailsCurrent['f_name'].' '.$officer_detailsCurrent['l_name'].' '.$officer_detailsCurrent['role'];
                                        $sentdate[] = $allcation_table['created'];
                                        $action[] = 'Allocated to Scrutinized';
                                    }
                                }
                                if(!empty($allcation_table['level_2']) && !empty($allcation_table['level_3'])){
                                    $officer_details   = $this->DmiUsers->find('all', ['fields'=>['f_name','l_name','role'], 'conditions'=>['email IS'=>$allcation_table['level_3']]])->first();
                                    if(!empty($officer_details)){
                                        $from[] = $officer_details['f_name'].' '.$officer_details['l_name'].' '.$officer_details['role'];
                                    }
                                    $officer_detailsCurrent   = $this->DmiUsers->find('all', ['fields'=>['f_name','l_name','role'], 'conditions'=>['email IS'=>$allcation_table['level_2']]])->first();
                                    if(!empty($officer_detailsCurrent)){
                                        $to[] = $officer_detailsCurrent['f_name'].' '.$officer_detailsCurrent['l_name'].' '.$officer_detailsCurrent['role'];
                                       
                                    }
                                    $sentdate[] = $allcation_table['modified'];
                                    $action[] = 'Allocated to IO';
                                }

                              
                              
                            }
                            
                            if(!empty($comment_by_mo)){
                                  
                              foreach ($comment_by_mo as $key => $crm) {
                               
                                    if($crm['available_to'] == 'ro'){
                                        $officer_details   = $this->DmiUsers->find('all', ['fields'=>['f_name','l_name','role'], 'conditions'=>['email IS'=>$crm['comment_by']]])->first();  
                                        if(!empty($officer_details)){
                                            $from[] = $officer_details['f_name'].' '.$officer_details['l_name'].' '.$officer_details['role'];
                                        }
                                        $officer_detailsCurrent   = $this->DmiUsers->find('all', ['fields'=>['f_name','l_name','role'], 'conditions'=>['email IS'=>$crm['comment_to']]])->first();
                                        if(!empty($officer_detailsCurrent)){
                                            $to[] = $officer_detailsCurrent['f_name'].' '.$officer_detailsCurrent['l_name'].' '.$officer_detailsCurrent['role'];
                                        }
                                        $sentdate[] = $crm['comment_date'];
                                        $action[] = 'Forwarded Back to RO/SO ';
                                    }elseif($crm['available_to'] == 'mo'){
                                        $officer_details   = $this->DmiUsers->find('all', ['fields'=>['f_name','l_name','role'], 'conditions'=>['email IS'=>$crm['comment_by']]])->first();  
                                        if(!empty($officer_details)){
                                            $from[] = $officer_details['f_name'].' '.$officer_details['l_name'].' '.$officer_details['role'];
                                        }
                                        $officer_detailsCurrent   = $this->DmiUsers->find('all', ['fields'=>['f_name','l_name','role'], 'conditions'=>['email IS'=>$crm['comment_to']]])->first();
                                        if(!empty($officer_detailsCurrent)){
                                            $to[] = $officer_detailsCurrent['f_name'].' '.$officer_detailsCurrent['l_name'].' '.$officer_detailsCurrent['role'];
                                        }
                                        $sentdate[] = $crm['comment_date'];
                                        $action[] = 'Forwarded Back to MO/SMO';
                                    }
                                }
                            }

                            if(!empty($applicant_level3)){
                                if($current_pos['current_level'] == 'level_3'){
                                    $officer_details =  $this->DmiUsers->find('all', ['fields'=>['f_name','l_name','role'], 'conditions'=>['email IS'=>$current_pos['current_user_email_id']]])->first();
                                }elseif($current_pos['current_level'] == 'level_1' || $current_pos['current_level'] == 'applicant' || $current_pos['current_level'] == 'level_2'){
                                    // allocation table
                                     $roofficer_details = $allocation->find('all', ['fields'=>['level_3'], 'conditions'=>['customer_id IS'=>$appli_id]])->first();
                                   if(!empty($roofficer_details)){
                                      $officer_details   = $this->DmiUsers->find('all', ['fields'=>['f_name','l_name','role'], 'conditions'=>['email IS'=>$roofficer_details['level_3']]])->first();
                                
                                    }
                                }
                                
                                
                                foreach ($applicant_level3 as $key => $l3) {   
                                    if($l3['status'] == 'referred_back'){
                                        if(!empty($officer_details)){
                                            $from[] = $officer_details['f_name'].' '.$officer_details['l_name'].' '.$officer_details['role'];
                                        }
                                    
                                        $to[] = $firm_details['firm_name'];
                                    
                                        $sentdate[] = $l3['modified'];
                                        $action[] = 'Forwarded Back to Applicant ';
                                   }elseif($l3['status'] == 'replied'){
                                        if(!empty($officer_details)){
                                            $to[] = $officer_details['f_name'].' '.$officer_details['l_name'].' '.$officer_details['role'];
                                        }
                                    
                                            $from[] = $firm_details['firm_name'];
                                        
                                            $sentdate[] = $l3['modified'];
                                            $action[] = 'Forwarded to RO/SO ';
                                    }elseif($l3['status'] == 'approved' && $l3['current_level'] == 'level_1'){
                                        if(!empty($officer_details)){
                                            $to[] = $officer_details['f_name'].' '.$officer_details['l_name'].' '.$officer_details['role'];
                                            $from[] = $officer_details['f_name'].' '.$officer_details['l_name'].' '.$officer_details['role'];
                                        }
                                    
                                            
                                        
                                            $sentdate[] = $l3['modified'];
                                            $action[] = 'Application Scrutinized';

                                    } 
           
                                   
                                }
                                   
                            }
                        
                         
                            $ispectionReportData = $inspection->find('all', ['conditions'=>['customer_id IS'=>$appli_id]])->order('created','ASC')->toArray();
                            $status= array();
                            $i=0;
                            if(!empty($ispectionReportData)){
                                    foreach ($ispectionReportData as $key => $inspect) {
                                        if($inspect['current_level'] == 'level_2'  && $inspect['status'] == 'pending'){
                                            if(!empty($officer_details)){
                                                
                                                $to[] = $officer_details['f_name'].' '.$officer_details['l_name'].' '.$officer_details['role'];
                                            }
                                            if(!empty($allcation_table['level_2'])){
                                                $iOofficer_details   = $this->DmiUsers->find('all', ['fields'=>['f_name','l_name','role'], 'conditions'=>['email IS'=>$allcation_table['level_2']]])->first();
                                                if(!empty($iOofficer_details)){
                                                    $from[] = $iOofficer_details['f_name'].' '.$iOofficer_details['l_name'].' '.$iOofficer_details['role'];
                                                    $sentdate[] = $inspect['modified'];
                                                    $action[] = 'Application forwarded IO to RO/SO';
                                                }
                                            } 
                                        
                                        }elseif($inspect['current_level'] == 'level_3'  && $inspect['status'] == 'referred_back'){
                                            
                                             if(!empty($officer_details)){
                                                
                                                $from[] = $officer_details['f_name'].' '.$officer_details['l_name'].' '.$officer_details['role'];
                                            }
                                            if(!empty($allcation_table['level_2'])){
                                                $iOofficer_details   = $this->DmiUsers->find('all', ['fields'=>['f_name','l_name','role'], 'conditions'=>['email IS'=>$allcation_table['level_2']]])->first();
                                                if(!empty($iOofficer_details)){
                                                    $to[] = $iOofficer_details['f_name'].' '.$iOofficer_details['l_name'].' '.$iOofficer_details['role'];
                                                    $sentdate[] = $inspect['modified'];
                                                    $action[] = 'Application referred back RO/SO to IO';
                                                }
                                            }
                                        }elseif($inspect['current_level'] == 'level_3'  && $inspect['status'] == 'replied'){
                                            if(!empty($officer_details)){
                                        
                                                $to[] = $officer_details['f_name'].' '.$officer_details['l_name'].' '.$officer_details['role'];
                                            }
                                            if(!empty($allcation_table['level_2'])){
                                                $iOofficer_details   = $this->DmiUsers->find('all', ['fields'=>['f_name','l_name','role'], 'conditions'=>['email IS'=>$allcation_table['level_2']]])->first();
                                                if(!empty($iOofficer_details)){
                                                    $from[] = $iOofficer_details['f_name'].' '.$iOofficer_details['l_name'].' '.$iOofficer_details['role'];
                                                    $sentdate[] = $inspect['modified'];
                                                    $action[] = 'Application again forwarded IO to RO/SO';
                                                }
                                            }
                                        }
                                        $i++;
                                    }
                                
                                }
                    $esignedRecord = $esign->find('all', ['conditions'=>['customer_id IS'=>$appli_id]])->first();
                    $grantedRecord = $grant->find('all', ['conditions'=>['customer_id IS'=>$appli_id]])->first();
                    if(!empty($esignedRecord) && $esignedRecord['certificate_esigned'] == 'yes'){
                        $to[] = $officer_details['f_name'].' '.$officer_details['l_name'].' '.$officer_details['role'];
                        $from[] = $officer_details['f_name'].' '.$officer_details['l_name'].' '.$officer_details['role'];
                        $sentdate[]= $esignedRecord['modified'];
                        $action[]= "Esigned by RO/SO";
                    }
                    if(!empty($grantedRecord)){
                        $to[] = $officer_details['f_name'].' '.$officer_details['l_name'].' '.$officer_details['role'];
                        $from[] = $officer_details['f_name'].' '.$officer_details['l_name'].' '.$officer_details['role'];
                        $sentdate[]= $grantedRecord['modified'];
                        $action[]= " Granted";
                    }
                 
              
                             
                          
                            $this->set('to',$to);
                            $this->set('from',$from);
                            $this->set('sentdate',$sentdate);
                            $this->set('action',$action);
                } 
            }
           
            


        //get application id and name of array in ajax success and add in dropdown by laxmi on 20-7-23
        public function getApplId(){
            $this->autoRender = false;
            $this->loadModel('DmiApplWithRoMappings');
            $this->loadModel('DmiFlowWiseTablesLists'); 
            $this->loadModel('DmiUserRoles');
            $this->loadModel('DmiRoOffices');
            $this->loadModel('DmiFirms');
            $this->loadModel('DmiChemistRegistrations');

            $appl_type = $this->request->getData('appl_type');
            $username  = $this->Session->read('username'); 
            $findRole  = $this->DmiUserRoles->find('all')->where(['user_email_id IS'=> $username])->first();
            if($findRole['dy_ama'] == 'yes' || $findRole['jt_ama'] == 'yes' || $findRole['ama'] == 'yes' || $findRole['super_admin'] == 'yes'){
                $findShortCode = $this->DmiRoOffices->find('all', array('fields'=>['short_code'], 'conditions'=>['delete_status IS'=>NULL]))->toArray();
                $condition = '';
                $chemistcondition = '';
                $n = 1;
                foreach($findShortCode as $key => $value){
                    
                 
                        $seprator = ($n!=1)?' OR ':'';
                        $shrtcode = $value['short_code'];
                        $condition .= $seprator."customer_id like '%/$shrtcode/%'";  // dynamic condition to get short code of login users
                        if($appl_type == 4){
                            $chemistcondition .= $seprator."created_by like '%/$shrtcode/%'"; 
                        }
                        $n++;	
                    

                }

                $firmsDetails =  $this->DmiFirms->find('all',['fields'=>['customer_id','firm_name']])->where(array($condition))->where(['delete_status IS'=>null])->order(['firm_name'=>'ASC'])->toArray(); 
                if($appl_type == 4){

                    $firmsDetails =  $this->DmiChemistRegistrations->find('all',['fields'=>['chemist_id','chemist_fname','chemist_lname']])->where(array($chemistcondition))->where(['delete_status IS'=>null])->order(['chemist_fname'=>'ASC'])->toArray();
                }
                
                echo json_encode($firmsDetails);
                exit;
                   


            }elseif($findRole['ro_inspection'] == 'yes'){
                $findShortCode = $this->DmiRoOffices->find('all', array('fields'=>['short_code'], 'conditions'=>['ro_email_id IS'=>$username], 'delete_status IS'=>NULL))->toArray();
                $condition = '';
                $chemistcondition = '';
                $n = 1;
                foreach($findShortCode as $key => $value){
                    
                    
                        $seprator = ($n!=1)?' OR ':'';
                        $shrtcode = $value['short_code'];
                        $condition .= $seprator."customer_id like '%/$shrtcode/%'";  // dynamic condition to get short code of login users
                        if($appl_type == 4){
                            $chemistcondition .= $seprator."created_by like '%/$shrtcode/%'"; 
                        }
                        $n++;	 
                    

                } 
                
                $firmsDetails =  $this->DmiFirms->find('all',['fields'=>['customer_id','firm_name']])->where(array($condition))->where(['delete_status IS'=>null])->order(['firm_name'=>'ASC'])->toArray(); 
                if($appl_type == 4){
                    
                    $firmsDetails =  $this->DmiChemistRegistrations->find('all',['fields'=>['chemist_id','chemist_fname','chemist_lname']])->where(array($chemistcondition))->where(['delete_status IS'=>null])->order(['chemist_fname'=>'ASC'])->toArray();
                
                }
                echo json_encode($firmsDetails);
                exit;
            
            }elseif($findRole['so_inspection'] == 'yes'){

                $findShortCode = $this->DmiRoOffices->find('all', array('fields'=>['short_code'], 'conditions'=>['ro_email_id IS'=>$username], 'delete_status IS'=>NULL))->first();
                $condition = '';
                $chemistcondition = '';
                $n = 1;
                foreach($findShortCode as $key => $value){
                    
                    
                        $seprator = ($n!=1)?' OR ':'';
                        $shrtcode = $value['short_code'];
                        $condition .= $seprator."customer_id like '%/$shrtcode/%'";  // dynamic condition to get short code of login users
                        if($appl_type == 4){
                            $chemistcondition .= $seprator."created_by like '%/$shrtcode/%'"; 
                        }
                        $n++;	 
                    

                } 
               
                $firmsDetails =  $this->DmiFirms->find('all',['fields'=>['customer_id','firm_name']])->where(array($condition))->where(['delete_status IS'=>null])->order(['firm_name'=>'ASC'])->toArray(); 
                if($appl_type == 4){

                    $firmsDetails =  $this->DmiChemistRegistrations->find('all',['fields'=>['chemist_id','chemist_fname','chemist_lname']])->where(array($chemistcondition))->where(['delete_status IS'=>null])->order(['chemist_fname'=>'ASC'])->toArray();
                }
                echo json_encode($firmsDetails);
                exit;
            }
           
        }
}
?>