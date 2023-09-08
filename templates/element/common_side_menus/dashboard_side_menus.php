<?php ?>

<?php
	// SET ACTIVE MENU (HIGHLIGHT CURRENTLY SELECTED MENU) IN LEFT SIDEBAR
	// By Aniket Ganvir dated 8th DEC 2020

	if (!isset($current_menu) || $current_menu=='') {
		$current_menu = 'dashboard';
	}

	$menu_dashboard       	    = '';
	$menu_users           	    = '';
	$menu_users_open      	    = '';
	$menu_all_users       	    = '';
	$menu_add_user        	    = '';
	$menu_user_role       	    = '';
	$menu_user_role_open  	    = '';
	$menu_set_roles             = '';
	$menu_edit_roles            = '';
	$menu_file_uploads          = '';
	$menu_site_pages            = '';
	$menu_site_pages_open       = '';
	$menu_all_pages             = '';
	$menu_add_page              = '';
	$menu_site_menu             = '';
	$menu_site_menu_open        = '';
	$menu_all_menus             = '';
	$menu_add_menu              = '';
	$menu_masters               = '';
	$menu_reports               = '';
	$menu_granted_application   = '';
	$menu_feedbacks             = '';
	$menu_transfer_application  = '';
	$menu_unlock_accounts       = '';
	$menu_unlock_accounts_open  = '';
	$menu_dmi_users             = '';
	$menu_primary_application   = '';
	$menu_secondary_application = '';
	$menu_go_to_lims            = '';
	//for chemist training module added new menu as empty here by laxmi B on 28-12-2022
	$menu_chemist_training      = '';							  

	if ($current_menu == 'menu_profile') {
		$menu_profile = 'active';
	} elseif ($current_menu == 'menu_firm') {
		$menu_firm = 'active';
	} elseif ($current_menu == 'menu_password') {
		$menu_password = 'active';
	} elseif ($current_menu == 'menu_log') {
		$menu_log = 'active';
	} elseif ($current_menu == 'menu_action_log') {
		$menu_action_log = 'active';
	} else {
		$menu_dashboard = 'active';
	}

?>

<aside class="main-sidebar sidebar-dark-primary elevation-4">
	<?php echo $this->element('common_side_menus/common_top_left_logo'); ?>
	<div class="sidebar">
		<?php echo $this->element('common_side_menus/common_top_left_profile'); ?>
		<nav class="mt-2">
			<ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
				<li class="nav-item">
					<?php echo $this->Html->link('<i class="nav-icon fas fa-tachometer-alt"></i><p class="nav-icon-p">Dashboard</p>', array('controller'=>'dashboard', 'action'=>'home'), array('escape'=>false, 'class'=>'nav-link '.$menu_dashboard)); ?>
				</li>

				<?php if (!empty($current_user_roles)) { ?>
					

					<!-- show applicant email details for new audit changes, 25-02-2021, Pravin Bhakare -->
					<!-- Added on 30-05-2022 For the My Team Function by Akash-->
					<?php if($current_user_roles['super_admin'] != 'yes') { ?>

						<li class="nav-item">
							<a href="<?php echo $this->request->getAttribute('webroot');?>othermodules/my_team" class="nav-link">
							<i class="fas fa-users nav-icon"></i>
								<p class="nav-icon-p">My Team</p>
							</a>
						</li>
						
					<?php } ?>

					<?php if ($current_user_roles['dy_ama'] == 'yes') { ?>

						<!--for granted pdf list -->
						<!--commented below li on 09-10-2017 by Amol temp. to hide lab export
							<li class="dropdown">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown">Granted Lab Export</a>
									<ul class="submenu nav menu">
										<li><a href="<?php //echo $this->request->getAttribute('webroot');?>hoinspections/grant_certificates_list">All Applications</a></li>
									</ul>
							</li>
						-->
					<?php } if ($current_user_roles['add_user'] == 'yes') { ?>

						<li class="nav-item has-treeview">
							<a href="#" class="nav-link ">
								<i class="nav-icon far fa-user"></i>
								<p>Users<i class="fas fa-long-arrow-alt-right right"></i></p>
							</a>
							<ul class="nav nav-treeview ">
								<li class="nav-item has-treeview">
									<li class="nav-item">
										<a href="<?php echo $this->request->getAttribute("webroot");?>users/all_users" class="bg-cyan nav-link <?php echo $menu_all_users; ?>">
											<i class="far fas fa-users"></i>
											<p class="nav-icon-p">All Users</p>
										</a>
									</li>
									<li class="nav-item">
										<a href="<?php echo $this->request->getAttribute("webroot");?>users/add_user" class="bg-cyan nav-link <?php echo $menu_add_user; ?>">
											<i class="far fas fa-user-plus"></i>
											<p class="nav-icon-p">Add User</p>
										</a>
									</li>
								</li>
							</ul>
						</li>

					<?php } if ($current_user_roles['set_roles'] == 'yes') { ?>

						<li class="nav-item">
							<li class="nav-item has-treeview">
								<a href="#" class="nav-link">
									<i class="nav-icon fas fa-user-tag"></i>
									<p>Users Roles<i class="fas fa-long-arrow-alt-right right"></i></p>
								</a>
								<ul class="nav nav-treeview ">
									<li class="nav-item has-treeview">
										<li class="nav-item">
											<a href="<?php echo $this->request->getAttribute("webroot");?>roles/set_roles" class="bg-cyan nav-link <?php echo $menu_set_roles; ?>">
												<i class="far fas fa-user-cog nav-icon"></i>
												<p class="nav-icon-p">Set Roles</p>
											</a>
										</li>
										<li class="nav-item">
											<a href="<?php echo $this->request->getAttribute("webroot");?>roles/edit_roles" class="bg-cyan nav-link <?php echo $menu_edit_roles; ?>">
												<i class="far fas fa-user-edit nav-icon"></i>
												<p class="nav-icon-p">Edit Roles</p>
											</a>
										</li>
									</li>
								</ul>
							</li>
						</li>

					<?php } if ($current_user_roles['file_upload'] == 'yes') { ?>

						<li class="nav-item">
							<a href="<?php echo $this->request->getAttribute('webroot');?>cms/file_uploads" class="nav-link <?php echo $menu_file_uploads; ?>">
								<i class="far fas fa-file-upload nav-icon"></i>
								<p class="nav-icon-p">File Uploads</p>
							</a>
						</li>

					<?php } if ($current_user_roles['page_draft'] == 'yes' || $current_user_roles['page_publish'] == 'yes') { ?>

						<li class="nav-item">
							<li class="nav-item has-treeview">
								<a href="#" class="nav-link">
									<i class="nav-icon fas fa-copy"></i>
									<p>Site Pages<i class="fas fa-long-arrow-alt-right right"></i></p>
								</a>
								<ul class="nav nav-treeview ">
									<li class="nav-item has-treeview">
										<li class="nav-item">
											<a href="<?php echo $this->request->getAttribute("webroot");?>cms/all_pages" class="bg-cyan nav-link <?php echo $menu_all_pages; ?>">
												<i class="far fa-file nav-icon"></i>
												<p class="nav-icon-p">All Pages</p>
											</a>
										</li>
										<li class="nav-item">
											<a href="<?php echo $this->request->getAttribute("webroot");?>cms/add_page" class="bg-cyan nav-link <?php echo $menu_add_page; ?>">
												<i class="far fas fa-plus nav-icon"></i>
												<p class="nav-icon-p">Add Page</p>
											</a>
										</li>
									</li>
								</ul>
							</li>
						</li>

					<?php } if ($current_user_roles['menus'] == 'yes') { ?>

						<li class="nav-item">
							<li class="nav-item has-treeview">
								<a href="#" class="nav-link">
									<i class="nav-icon fas fa-sitemap"></i>
									<p>Site Menus<i class="fas fa-long-arrow-alt-right right"></i></p>
								</a>
								<ul class="nav nav-treeview ">
									<li class="nav-item has-treeview">
										<li class="nav-item">
											<a href="<?php echo $this->request->getAttribute("webroot");?>cms/all_menus" class="bg-cyan nav-link <?php echo $menu_all_menus; ?>">
												<i class="far fas fa-sitemap nav-icon"></i>

												<p class="nav-icon-p">All Menus</p>
											</a>
										</li>

										<li class="nav-item">
											<a href="<?php echo $this->request->getAttribute("webroot");?>cms/add_menu" class="bg-cyan nav-link <?php echo $menu_add_menu; ?>">
												<i class="far fas fa-plus nav-icon"></i>
												<p class="nav-icon-p">Add Menu</p>
											</a>
										</li>
									</li>
								</ul>
							</li>
						</li>

					<?php } if ($current_user_roles['masters'] == 'yes') { ?>

						<li class="nav-item">
							<a href="<?php echo $this->request->getAttribute('webroot');?>masters/masters_home" class="nav-link <?php echo $menu_masters; ?>">
								<i class="far fas fa-asterisk nav-icon"></i>
								<p>Masters</p>
							</a>
						</li>
						<li class="nav-item">
							<a href="<?php echo $this->request->getAttribute('webroot');?>Othermodules/work_transfer_requests" class="nav-link">
								<i class="fas fa-random nav-icon"></i>
								<p>Work Transfer Requests</p>
							</a>
						</li>

					<?php } if ($current_user_roles['view_reports'] == 'yes') { ?>

						<li class="nav-item">
							<a href="<?php echo $this->request->getAttribute('webroot');?>reports/report_types" class="nav-link <?php echo $menu_reports; ?>">
								<i class="far fas fa-poll nav-icon"></i>
								<p>Reports</p>
							</a>
						</li>

					<?php } if ($current_user_roles['ro_inspection'] == 'yes' || $current_user_roles['so_inspection']=='yes' ||

						//added new conditions on 05-05-2021 by Amol, to show all granted applications to HO users
						$current_user_roles['dy_ama'] == 'yes' || $current_user_roles['jt_ama'] == 'yes' ||
						$current_user_roles['ama'] == 'yes' || $current_user_roles['super_admin'] == 'yes') { ?>

						<!-- for granted pdf list -->
						<li class="nav-item">
							<li class="nav-item has-treeview">
								<a href="#" class="nav-link" class="nav-link <?php echo $menu_granted_application; ?>" >
									<i class="nav-icon fas far fa-check"></i>
									<p>Granted Applications<i class="fas fa-long-arrow-alt-right right"></i></p>
								</a>

								<ul class="nav nav-treeview">
									<li class="nav-item has-treeview">
										<!-- added new Li to show menu for Old applications, with two parameters, on 29-05-2023 by Amol -->
										<li class="nav-item">
											<a href="<?php echo $this->request->getAttribute("webroot");?>hoinspections/redirectGrantedApplications/1/old" class="bg-cyan nav-link">
												<i class="far fa-dot-circle nav-icon"></i>
												<p class="nav-icon-p"><b>Old Application</b></p>
											</a>
										</li>
										<li class="nav-item">
											<a href="<?php echo $this->request->getAttribute("webroot");?>hoinspections/redirectGrantedApplications/1" class="bg-cyan nav-link">
												<i class="far fa-dot-circle nav-icon"></i>
												<p class="nav-icon-p"><b>New Application</b></p>
											</a>
										</li>

										<li class="nav-item">
											<a href="<?php echo $this->request->getAttribute("webroot");?>hoinspections/redirectGrantedApplications/2" class="bg-cyan nav-link">
												<i class="far fa-dot-circle nav-icon"></i>
												<p class="nav-icon-p"><b>Renewal Application</b></p>
											</a>
										</li>
										<!-- Added option for Change grant list on 01-05-2023 -->
										<li class="nav-item">
											<a href="<?php echo $this->request->getAttribute("webroot");?>hoinspections/redirectGrantedApplications/3" class="bg-cyan nav-link">
												<i class="far fa-dot-circle nav-icon"></i>
												<p class="nav-icon-p"><b>Change Application</b></p>
											</a>
										</li>
										<li class="nav-item">
											<a href="<?php echo $this->request->getAttribute("webroot");?>hoinspections/redirectGrantedApplications/5" class="bg-cyan nav-link">
												<i class="far fa-dot-circle nav-icon"></i>
												<p class="nav-icon-p"><b>Approval for 15 Digit Code</b></p>
											</a>
										</li>
										<li class="nav-item">
											<a href="<?php echo $this->request->getAttribute("webroot");?>hoinspections/redirectGrantedApplications/6" class="bg-cyan nav-link">
												<i class="far fa-dot-circle nav-icon"></i>
												<p class="nav-icon-p"><b>Approval for E-Code</b></p>
											</a>
										</li>
										<li class="nav-item">
											<a href="<?php echo $this->request->getAttribute('webroot');?>hoinspections/redirectGrantedApplications/9" class="bg-cyan nav-link">
												<i class="far fa-dot-circle nav-icon"></i>
												<p class="nav-icon-p"><b>Surrender Applications</b></p>
											</a>
										</li>
										
										
										 <!-- added new submenu chemist approval in granted application menu by Laxmi On 29-05-2023 -->
										<li class="nav-item">
											<a href="<?php echo $this->request->getAttribute("webroot");?>hoinspections/redirectGrantedApplications/4" class="bg-cyan nav-link ">
												<i class="far fa-dot-circle nav-icon"></i>
												<p class="nav-icon-p"><b>Approval for Chemist</b></p>
											</a>
										</li>

										<li class="nav-item">
											<a href="<?php echo $this->request->getAttribute('webroot');?>othermodules/list_of_suspended_firms/" class="bg-cyan nav-link">
												<i class="far fa-dot-circle nav-icon"></i>
												<p class="nav-icon-p"><b>Suspended Applications</b></p>
											</a>
										</li>
										<li class="nav-item">
											<a href="<?php echo $this->request->getAttribute('webroot');?>othermodules/list_of_cancelled_firms/" class="bg-cyan nav-link">
												<i class="far fa-dot-circle nav-icon"></i>
												<p class="nav-icon-p"><b>Cancelled Applications</b></p>
											</a>
										</li>
										<!-- For Biannually Grading Report added by shankhpal shende on 07/09/2023 -->
										<li class="nav-item"> 
											<a href="<?php echo $this->request->getAttribute('webroot');?>othermodules/list_of_bgr_report" class="bg-cyan nav-link">
												<i class="far fa-dot-circle nav-icon"></i>
												<p class="nav-icon-p"><b>Biannually Grading Report</b></p>
											</a>
										</li>
									</li>
								</ul>
							</li>
						</li>
						
					<?php } if ($current_user_roles['ro_inspection'] == 'yes' || $current_user_roles['so_inspection']=='yes' || 
								$current_user_roles['dy_ama'] == 'yes' || $current_user_roles['jt_ama'] == 'yes' ||
								$current_user_roles['ama'] == 'yes' || $current_user_roles['super_admin'] == 'yes' || $current_user_roles['pao'] == 'yes') { ?>

								<!-- below li added on 07-09-2022  -->
								<li class="nav-item">
									<a href="<?php echo $this->request->getAttribute('webroot');?>hoinspections/rejectedApplList" class="nav-link <?php echo $menu_add_menu; ?>">
										<i class="far fas fa-close nav-icon"></i>
										<p>Rejected/Junked Appl.</p>
									</a>
								</li>

					<?php } if ($current_user_roles['ro_inspection'] == 'yes' || $current_user_roles['so_inspection'] == 'yes' && $current_user_roles['super_admin'] != 'yes') { ?>
						<li class="nav-item">
							<li class="nav-item has-treeview">
								<a href="#" class="nav-link">
									<i class="nav-icon fas fa-sitemap"></i>
									<p>Other Modules <i class="fas fa-long-arrow-alt-right right"></i></p>
								</a>
								<ul class="nav nav-treeview ">
									<li class="nav-item has-treeview">
										<!-- Added for update firm details option menu for secondary firms that is for RO / SO user on 29-12-2021 by AKASH -->
										<li class="nav-item">
											<a href="<?php echo $this->request->getAttribute('webroot'); ?>othermodules/firms_list_to_update" class="bg-success nav-link">
												<i class="fas fa-user-edit nav-icon"></i>
												<p class="nav-icon-p"><b>Update Firm Details</b></p>
											</a>
										</li>
										<li class="nav-item">
											<a href="<?php echo $this->request->getAttribute('webroot');?>othermodules/re_esign_module" class="bg-success nav-link">
												<i class="fas fa-redo-alt nav-icon"></i>
												<p class="nav-icon-p"><b>Re-esign</b></p>
											</a>
										</li>
										<li class="nav-item">
											<a href="<?php echo $this->request->getAttribute('webroot');?>othermodules/user_work_transfer" class="bg-success nav-link">
												<i class="fas fa-retweet nav-icon"></i>
												<p class="nav-icon-p"><b>Work Transfer</b></p>
											</a>
										</li>
										<!-- new menu added on 22-06-2023 by Amol for RO Incharge -->
										<?php if ($current_user_roles['ro_inspection'] == 'yes' && $current_user_roles['super_admin'] != 'yes'){ ?>
											<li class="nav-item">
												<a href="<?php echo $this->request->getAttribute('webroot');?>othermodules/get_officer_wise_pending_appl" class="bg-success nav-link">
													<i class="fas fa-retweet nav-icon"></i>
													<p class="nav-icon-p"><b>Office wise Pending</b></p>
												</a>
											</li>
										<?php } ?>
										
									</li>
									<li class="nav-item">
										<a href="<?php echo $this->request->getAttribute('webroot');?>othermodules/routine_inspection_list" class="bg-success nav-link">
											<i class="fas fa-retweet nav-icon"></i>
											<p class="nav-icon-p"><b>Routine Inspection</b></p>
										</a>
									</li>
								</ul>
							</li>
						</li>

						<li class="nav-item">
							<li class="nav-item has-treeview">
								<a href="#" class="nav-link">
									<i class="nav-icon fas fa-sitemap"></i>
									<p class="badge">Replica/15 Digit/E-Code</p><i class="fas fa-long-arrow-alt-right right"></i>
								</a>
								<ul class="nav nav-treeview ">
									<li class="nav-item has-treeview">
										<li class="nav-item">
											<a href="<?php echo $this->request->getAttribute('webroot');?>users/replica_alloted_list" class="bg-Gray Dark nav-link">
												<i class="fas fa-info-circle nav-icon"></i>
												<p class="nav-icon-p">Replica Alloted List</p>
											</a>
										</li>
										<li class="nav-item">
											<a href="<?php echo $this->request->getAttribute('webroot');?>users/alloted15_digit_list" class="bg-Gray Dark nav-link">
												<i class="fas fa-info-circle nav-icon"></i>
												<p class="nav-icon-p">Alloted 15 Digit Code</p>
											</a>
										</li>
										<li class="nav-item">
											<a href="<?php echo $this->request->getAttribute('webroot');?>users/alloted_e_code_list" class="bg-Gray Dark nav-link">
												<i class="fas fa-info-circle nav-icon"></i>
												<p class="nav-icon-p">Alloted E-Code</p>
											</a>
										</li>
									</li>
								</ul>
							</li>
						</li>
						
						<!-- This Whole block is For the Management of Misgrading Module (MMR) / Actions / Suspensions / Cancellations - Akash [12-06-2023]-->
						<li class="nav-item">
							<li class="nav-item has-treeview">
								<a href="#" class="nav-link">
									<i class="nav-icon fas fa-sitemap"></i>
									<p class="badge">Management of Misgrading</p><i class="fas fa-long-arrow-alt-right right"></i>
								</a>
								<ul class="nav nav-treeview ">
									<li class="nav-item has-treeview">
									
										<li class="nav-item">
											<a href="<?php echo $this->request->getAttribute('webroot');?>misgrading/report_listing_for_allocation" class="bg-success nav-link">
												<i class="fas fa-arrow-alt-circle-right nav-icon"></i>
												<p class="nav-icon-p">LIMS Reports</p>
											</a>
										</li>
										<li class="nav-item">
											<a href="<?php echo $this->request->getAttribute('webroot');?>othermodules/misgrading_home" class="bg-success nav-link">
												<i class="fas fa-arrow-alt-circle-right nav-icon"></i>
												<p class="nav-icon-p">Actions On Misgrade</p>
											</a>
										</li>
										<li class="nav-item">
											<a href="<?php echo $this->request->getAttribute('webroot');?>othermodules/list_of_packer_action_taken" class="bg-success nav-link">
												<i class="fas fa-arrow-alt-circle-right nav-icon"></i>
												<p class="nav-icon-p">Suspension/Cancellation</p>
											</a>
										</li>
									</li>
								</ul>
							</li>
						</li>
						<!-- End of Block -->
					
					<!--The Below Block is For module Management of Misgrading (MMR) - Allocation of Report to Scruinizer Part Akash [12-06-2023]-->
					<?php } if ($current_user_roles['allocate_lims_report'] == 'yes') { ?>

						<li class="nav-item">
							<a href="<?php echo $this->request->getAttribute('webroot');?>misgrading/allocated_reports_for_mo/" class="nav-link">
								<i class="fas fa-arrow-alt-circle-right nav-icon"></i>
								<p class="nav-icon-p">Allocated Reports</p>
							</a>
						</li>
					
					<!--The Below Block is For module Management of Misgrading (MMR) - Refer to the Head Office Part Akash [12-06-2023]-->
					<?php } if ($_SESSION['role'] == 'Head Office') { ?>

						<li class="nav-item">
							<a href="<?php echo $this->request->getAttribute('webroot');?>othermodules/referred_to_head_office/" class="nav-link">
								<i class="fas fa-arrow-alt-circle-right nav-icon"></i>
								<p class="nav-icon-p">Misgrade Refer to HO</p>
							</a>
						</li>

					<?php } if ($current_user_roles['old_appln_data_entry'] == 'yes') { ?>

						<li class="nav-item">
							<a href="<?php echo $this->request->getAttribute('webroot');?>authprocessedoldapp/home" class="nav-link <?php echo $menu_granted_application; ?>">
							<i class="fas fa-file-invoice nav-icon"></i>
							<p class="badge">Authorized Old Applications</p>
							</a>
						</li>

					<?php } if ($current_user_roles['feedbacks'] == 'yes') { ?>

						<li class="nav-item">
							<a href="<?php echo $this->request->getAttribute('webroot');?>feedbacks/all_feedback" class="nav-link <?php echo $menu_feedbacks; ?>">
								<i class="far fas fa-comment-dots nav-icon"></i>
								<p>Feedbacks</p>
							</a>
						</li>

					<?php } if ($current_user_roles['transfer_appl'] == 'yes' && $current_user_roles['super_admin'] != 'yes') { ?>

						<li class="nav-item">
							<a href="<?php echo $this->request->getAttribute('webroot');?>dashboard/transfer_appl" class="nav-link <?php echo $menu_transfer_application; ?>">
								<i class="far fas fa-exchange-alt nav-icon"></i>
								<p>Transfer Application</p>
							</a>
						</li>

					<?php } if ($current_user_roles['unlock_user'] == 'yes') { ?>

						<li class="nav-item">
							<li class="nav-item has-treeview">
								<a href="#" class="nav-link">
									<i class="nav-icon fas fa-unlock-alt"></i>
									<p>Unlock Accounts<i class="fas fa-long-arrow-alt-right right"></i></p>
								</a>
								<ul class="nav nav-treeview ">
									<li class="nav-item has-treeview">
										<li class="nav-item">
											<a href="<?php echo $this->request->getAttribute("webroot");?>users/lock-user-redirect/dmiUsers" class="bg-cyan nav-link <?php echo $menu_all_menus; ?>">
												<i class="fas fa-user-lock nav-icon"></i>
												<p class="nav-icon-p">DMI Users</p>
											</a>
										</li>

										<li class="nav-item">
											<a href="<?php echo $this->request->getAttribute("webroot");?>users/lock-user-redirect/primary" class="bg-cyan nav-link <?php echo $menu_add_menu; ?>">
												<i class="fas fa-key nav-icon"></i>
												<p class="nav-icon-p">Primary Application</p>
											</a>
										</li>

										<li class="nav-item">
											<a href="<?php echo $this->request->getAttribute("webroot");?>users/lock-user-redirect/secondary" class="bg-cyan nav-link <?php echo $menu_add_menu; ?>">
												<i class="fas fa-unlock nav-icon"></i>
												<p class="nav-icon-p">Secondary Application</p>
											</a>
										</li>
									</li>
								</ul>
							</li>
						</li>

					<?php } ?>

					<!-- show applicant email details for new audit changes, 25-02-2021, Pravin Bhakare -->
					<li class="nav-item">
						<a href="<?php echo $this->request->getAttribute('webroot');?>othermodules/applicant_details" class="nav-link">
							<i class="fas fa-info-circle nav-icon"></i>
							<p class="nav-icon-p">Applicant Details</p>
						</a>
					</li>
				
					<?php if ($current_user_roles['pao'] == 'yes' && $current_user_roles['super_admin'] != 'yes') { ?>

						<li class="nav-item">
							<a href="<?php echo $this->request->getAttribute('webroot');?>advancepayment/adv_payment_verification" class="nav-link">
								<i class="fas fa-money-check-alt nav-icon"></i>
								<p class="nav-icon-p badge">Advance Payment Verification</p>
							</a>
						</li>
						
						<li class="nav-item">
							<a href="<?php echo $this->request->getAttribute('webroot');?>commercial/commercial_verfication" class="nav-link">
								<i class="fas fa-money-check-alt nav-icon"></i>
								<p class="nav-icon-p badge">Sample Payment Verification</p>
							</a>
						</li>

					<?php }?>
				
					<?php if (($current_user_roles['ro_inspection'] == 'yes' || $current_user_roles['so_inspection'] == 'yes' || $current_user_roles['pao'] == 'yes') && $current_user_roles['super_admin'] != 'yes') { ?>

						<li class="nav-item">
							<a href="<?php echo $this->request->getAttribute('webroot');?>users/replica_transaction" class="nav-link">
								<i class="fas fa-info-circle nav-icon"></i>
								<p class="nav-icon-p badge">Packer Transaction Statements</p>
							</a>
						</li>

					<?php }?>

					<!-- Added for update firm details option menu for primay firms that is for admin on 29-12-2021 by AKASH -->
					<?php if ($current_user_roles['super_admin'] == 'yes') { ?>

						<li class="nav-item">
							<a href="<?php echo $this->request->getAttribute('webroot'); ?>othermodules/firms_list_to_update" class="nav-link">
								<i class="fas fa-user-edit nav-icon"></i>
								<p class="nav-icon-p">Update Firm Details</p>
							</a>
						</li>

						<!-- new menu added on 27-06-2023 by Amol for HO Admin -->
						<li class="nav-item">
							<a href="<?php echo $this->request->getAttribute('webroot');?>othermodules/get_ro_wise_pending_appl" class="bg-success nav-link">
								<i class="fas fa-retweet nav-icon"></i>
								<p class="nav-icon-p"><b>RO Wise Pending</b></p>
							</a>
						</li>

					<?php } ?>
			<!-- added chemist training menu  by laxmi B. on 28-12-2022 -->
					<?php if($current_user_roles['ro'] == 'yes' || $current_user_roles['so'] == 'yes'){ ?>
				            <li class="nav-item">
							<li class="nav-item has-treeview">
								<a href="#" class="nav-link">
									<i class="nav-icon fas fa-flask"></i>
									<p>Chemist Training<i class="fas fa-long-arrow-alt-right right"></i></p>
								</a>
								<ul class="nav nav-treeview ">
									<li class="nav-item has-treeview">
										<li class="nav-item">
											<a href="<?php echo $this->request->getAttribute("webroot");?>chemist/listOfChemistApplRoToRal" class="bg-cyan nav-link">
												<i class="fas fa-list nav-icon"></i>

												<p class="nav-icon-p"><?php if(!empty($current_user_roles['user_flag'])){ echo $current_user_roles['user_flag']; }?> to RAL List</p>
											</a>
										</li>

										<li class="nav-item">
											<a href="<?php echo $this->request->getAttribute("webroot");?>chemist/listOfChemistApplRalToRo" class="bg-cyan nav-link">
												<i class="fas fa-list nav-icon"></i>
												<p class="nav-icon-p">RAL to <?php if(!empty($current_user_roles['user_flag'])){ echo $current_user_roles['user_flag']; }?> List</p>
											</a>
										</li>
									</li>
								</ul>
							</li>
						</li>
			   <?php }?>

			   <!-- added movement menu by laxmi on 09/08/2023 -->
			   <!-- below code comment by laxmi due to only one user to show movement suggest by tarun sir on 30-08-2023 -->
			  <?php /*if ($current_user_roles['ro_inspection'] == 'yes' || $current_user_roles['so_inspection']=='yes' ||
						$current_user_roles['dy_ama'] == 'yes' || $current_user_roles['jt_ama'] == 'yes' ||
						$current_user_roles['ama'] == 'yes' || $current_user_roles['super_admin'] == 'yes') { */
						if(!empty($_SESSION['username']) && $_SESSION['username'] == 'YWdtYXJrb25saW5lLmRtaUBnbWFpbC5jb20='){ ?>
                     <li class="nav-item">
								<a href="<?php echo $this->request->getAttribute('webroot');?>movements/movement_history" class="nav-link">
									<i class="far fas fa-server nav-icon"></i>
									<p class="nav-icon-p">Application Movement	</p>
								</a>
							</li>
					<?php } ?>
					<!-- added for testing user redirect for DMI and LMIS -->
					<?php if ($current_user_division['division'] == 'BOTH') { ?>

						<?php if ($current_user_division['f_name'] == 'LMIS') {?>

							<li class="nav-item">
								<a href="<?php echo $this->request->getAttribute('webroot');?>users/common_user_redirect_login/<?php echo $current_user_division['id']; ?>" class="nav-link">
									<i class="far fas fa-arrow-circle-right nav-icon"></i>
									<p class="nav-icon-p">Go To DMI</p>
								</a>
							</li>

						<?php }else{ ?>

							<li class="nav-item">
								<a href="../../UAT-LIMS/users/common_user_redirect_login/<?php echo $current_user_division['id']; ?>" class="nav-link">
								<i class="far fas fa-arrow-circle-right nav-icon"></i>
									<p class="nav-icon-p">Go To LIMS</p>
								</a>
							</li>

						<?php } ?>

					<?php } ?>

				<?php } ?>

				<li class="nav-item">
					<a href="<?php echo $this->request->getAttribute('webroot');?>common/logout" class="nav-link">
						<i class="fas fa-power-off nav-icon"></i>
						<p class="nav-icon-p">Logout</p>
					</a>
				</li>
			</ul>
		</nav>
	</div>
</aside>

<?php echo $this->Html->script('element/common_side_menus/dashboard_side_menus'); ?>
