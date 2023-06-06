<aside class="main-sidebar sidebar-dark-primary elevation-4">
	<?php echo $this->element('common_side_menus/common_top_left_logo'); ?>
	<div class="sidebar">
		<?php echo $this->element('common_side_menus/common_top_left_profile_firm'); ?>
		<?php $split_user_name = explode('/',$this->getRequest()->getSession()->read('username')); ?>
		<nav class="mt-2">
			<ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

				<li class="nav-item">
					<a href="<?php echo $this->request->getAttribute("webroot");?>customers/secondary-home" class="nav-link active">
						<i class="nav-icon fas fa-tachometer-alt"></i>
						<p class="nav-icon-p">Dashboard</p>
					</a>
				</li>

				<?php if ($isAppSurrender == 'no') { ?>


						<li class="nav-item has-treeview">

							<a href="#" class="nav-link">
								<i class="nav-icon fas fa-edit"></i>
								<p>Apply For<i class="fas fa-angle-left right"></i></p>
							</a>

							<ul class="nav nav-treeview dnone">
								<!-- Added new conditions to check inprocess appl, to hide/show specific menus only on 28-04-2023 by Amol -->
								<?php if (($show_button == 'New Certification' || $show_button == 'Application Status') && ((!empty($InprocessMsg) && $InprocessApplId==1) || empty($InprocessMsg))) { ?>
								
									<li class="nav-item">
										<a href="<?php echo $this->request->getAttribute("webroot");?>application/application-type/1" class="nav-link">
											<i class="far fa-plus-square nav-icon"></i>
											<p><?php echo $show_button; ?></p>
										</a>
									</li>

								<?php } ?>

								<!-- Added new conditions to check inprocess appl, to hide/show specific menus only on 28-04-2023 by Amol -->
								<?php if (($show_renewal_button == 'Renewal' || $show_renewal_button == 'Renewal Status') && ((!empty($InprocessMsg) && $InprocessApplId==2) || empty($InprocessMsg))) { ?>

									<li class="nav-item">
										<a href="<?php echo $this->request->getAttribute("webroot");?>application/application-type/2" class="nav-link">
											<i class="far fa-calendar-alt nav-icon"></i>
											<p><?php echo $show_renewal_button; ?></p>
										</a>
									</li>

								<?php } ?>
							
								<?php if ($IsApproved == 'yes') { ?>
								
									<!--# This menu is added for the Surrender Flow - Akash[22-11-2022] #-->
									<!-- Added new conditions to check inprocess appl, to hide/show specific menus only on 28-04-2023 by Amol -->
									<?php if((!empty($InprocessMsg) && $InprocessApplId==9) || empty($InprocessMsg)){ ?>
										<li class="nav-item">
											<a href="<?php echo $this->request->getAttribute("webroot");?>application/application-type/9" class="nav-link">
												<i class="nav-icon fas fa-share"></i>
												<p class="nav-icon-p">Surrender</p>
											</a>
										</li>
									<?php } ?>
									
									<!-- Added new conditions to check inprocess appl, to hide/show specific menus only on 28-04-2023 by Amol -->
									<?php if((!empty($InprocessMsg) && $InprocessApplId==3) || empty($InprocessMsg)){ ?>
										<li class="nav-item">
											<a href="<?php echo $this->request->getAttribute("webroot");?>application/application-type/3" class="nav-link <?php //echo $menu_mod; ?>">
												<i class="fa fa-wrench nav-icon"></i>
												<p>Modification</p>
											</a>
										</li>
									<?php } ?>
								
								<?php } ?>	

								<!-- <li class="nav-item">
								<a href="#" class="nav-link <?php //echo $menu_susp; ?>">
									<i class="far fa-clock nav-icon"></i>
									<p>Suspension</p>
								</a>
								</li> -->
								


								<!-- condition added by Shankhpal Shende on 08/11/2022 When user login with export lab, then it show on left menu. -->
								<!-- Added new conditions to check inprocess appl, to hide/show specific menus only on 28-04-2023 by Amol -->

							    <!-- to remove error in another menus to fetch & set export_status from session [laxmi-06-06-23] -->
								<?php if(($split_user_name[1] == 3 && $_SESSION['export_unit_status'] == "yes" && $IsApproved=='yes') && ((!empty($InprocessMsg) && $InprocessApplId==8) || empty($InprocessMsg))) { ?> 

									<li class="nav-item">
										<a href="<?php echo $this->request->getAttribute("webroot");?>application/application-type/8" class="nav-link">
											<i class="nav-icon fas fa-user-check"></i>
											<p class="nav-icon-p">Approval of Designated Person</p>
										</a>
									</li>

								<?php } ?>

								 <!-- added or condtion to show chemist registration of on export type  by laxmi on 06-01-2023 -->
						   <?php if(($split_user_name[1] == 1 && $IsApproved=='yes') || ($split_user_name[1] == 3 && $IsApproved=='yes')) { ?>

									<li class="nav-item">
										<a href="<?php echo $this->request->getAttribute("webroot");?>replica/replica_application" class="nav-link">
										<i class="nav-icon fas fa-award"></i>
										<p class="nav-icon-p">Apply for Replica</p>
										</a>
									</li>

									<!-- Added by shankhpal shende on 24/08/2022-->
									<li class="nav-item">
										<a href="<?php echo $this->request->getAttribute("webroot");?>customers/attache_pp_lab" class="nav-link">
										<i class="nav-icon fas fa-award"></i>
										<p class="nav-icon-p badge">Attach Printing Press/LAB</p>
										</a>
									</li>	
									
									<li class="nav-item">
										<a href="<?php echo $this->request->getAttribute("webroot");?>advancepayment/transactions" class="nav-link">
										<i class="nav-icon fas fa-rupee-sign"></i>
										<p class="nav-icon-p">Advance Payment</p>
										</a>
									</li>
										
									<!-- Added new conditions to check inprocess appl, to hide/show specific menus only on 28-04-2023 by Amol -->
									<?php if((!empty($InprocessMsg) && $InprocessApplId==4) || empty($InprocessMsg)){ ?>
										<li class="nav-item">
											<a href="<?php echo $this->request->getAttribute("webroot");?>chemist/chemist_registration" class="nav-link">
											<i class="nav-icon fas fa-plus-circle"></i>
											<p class="nav-icon-p">Chemist Registration</p>
											</a>
										</li>
									<?php } ?>

									<!-- below code is added on 14-10-2022 by Amol, to hide options if 15 digit and Ecode certificate is approved once.
									no renewal so only can apply once till grant. -->
									<!-- Added new conditions to check inprocess appl, to hide/show specific menus only on 28-04-2023 by Amol -->
									<?php if (($Is15DigitApproved!='yes') && ((!empty($InprocessMsg) && $InprocessApplId==5) || empty($InprocessMsg))) { ?>

										<li class="nav-item">
											<a href="<?php echo $this->request->getAttribute("webroot");?>application/application-type/5" class="nav-link">
												<i class="nav-icon fas fa-share"></i>
												<p class="nav-icon-p">15 Digit Code Approval</p>
											</a>
										</li>

									<!-- Added new conditions to check inprocess appl, to hide/show specific menus only on 28-04-2023 by Amol -->
									<?php } if(($IsECodeApproved!='yes') && ((!empty($InprocessMsg) && $InprocessApplId==6) || empty($InprocessMsg))) {  ?>

										<li class="nav-item">
											<a href="<?php echo $this->request->getAttribute("webroot");?>application/application-type/6" class="nav-link">
												<i class="nav-icon fas fa-share"></i>
												<p class="nav-icon-p">E-Code Approval</p>
											</a>
										</li>

									<?php } ?>
								<?php } ?>
							</ul>
						</li>

						<?php if ($IsApproved=='yes') { ?>

							<?php if($split_user_name[1] == 1 || $split_user_name[1] == 3) { ?>

								<li class="nav-item">
									<a href="<?php echo $this->request->getAttribute("webroot");?>customers/get_all_chemist_list" class="nav-link">
										<i class="nav-icon fas fa-user-check"></i>
										<p class="nav-icon-p">Registered Chemist</p>
									</a>
								</li>

							<?php } ?>

							<li class="nav-item">
								<a href="<?php echo $this->request->getAttribute("webroot");?>customers/replica_alloted_list" class="nav-link">
									<i class="nav-icon far fa-check-circle"></i>
									<p class="nav-icon-p">Replica Alloted List</p>
								</a>
							</li>
								
							<li class="nav-item">
								<a href="<?php echo $this->request->getAttribute('webroot');?>customers/alloted15_digit_list" class="nav-link">
									<i class="nav-icon far fa-check-circle"></i>
									<p class="nav-icon-p">Alloted 15 Digit Code</p>
								</a>
							</li>
								
							<li class="nav-item">
								<a href="<?php echo $this->request->getAttribute('webroot');?>customers/alloted_e_code_list" class="nav-link">
									<i class="nav-icon far fa-check-circle"></i>
									<p class="nav-icon-p">Alloted E-Code</p>
								</a>
							</li>
						
						<?php } ?>
			
				<?php } ?>

	

				<li class="nav-item">
					<a href="<?php echo $this->request->getAttribute("webroot");?>common/change_password" class="nav-link">
						<i class="nav-icon fas fa-lock"></i>
						<p class="nav-icon-p">Change Password</p>
					</a>
				</li>

				<li class="nav-item">
					<a href="<?php echo $this->request->getAttribute("webroot");?>common/current_user_logs" class="nav-link">
						<i class="nav-icon fas fa-book"></i>
						<p class="nav-icon-p">Log History</p>
					</a>
				</li>

				<li class="nav-item">
					<a href="<?php echo $this->request->getAttribute("webroot");?>common/user_action_history" class="nav-link">
						<i class="nav-icon fas fa-book"></i>
						<p class="nav-icon-p">Action History</p>
					</a>
				</li>

				<li class="nav-item">
					<a href="<?php echo $this->request->getAttribute("webroot");?>common/all_manuals" class="nav-link">
						<i class="nav-icon fas fa-address-book"></i>
						<p class="nav-icon-p">User Manuals</p>
					</a>
				</li>

				<li class="nav-item">
					<a href="<?php echo $this->request->getAttribute("webroot");?>common/logout" class="nav-link">
						<i class="nav-icon fas fa-power-off"></i>
						<p class="nav-icon-p">Logout</p>
					</a>
				</li>
			</ul>
		</nav>
	</div>
</aside>
