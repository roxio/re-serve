<?php echo view('admin/includes/head', get_defined_vars()); ?>
<div class="wrapper fullheight-side">
<?php echo view('admin/includes/header', get_defined_vars());
echo view('admin/includes/sidebar', get_defined_vars()); 
echo view('admin/includes/navbar', get_defined_vars()); ?>

<div class="main-panel">
   <div class="container">
      <div class="page-inner">
         <div class="page-header">
            <h4 class="page-title"><?php echo esc($page_title) ?></h4>
            <ul class="breadcrumbs">
               <li class="nav-home">
                  <a href="<?php anchor_to(GENERAL_CONTROLLER . '/dashboard') ?>">
                  <i class="flaticon-home"></i>
                  </a>
               </li>
               <li class="separator">
                  <i class="flaticon-right-arrow"></i>
               </li>
               <li class="nav-home">
                  <a href="<?php anchor_to(LAYOUT_CONTROLLER . '/analytics') ?>">
                  <?php echo esc($page_title) ?>
                  </a>
               </li>
            </ul>
         </div>
         <?php echo view('admin/includes/alert', get_defined_vars()); ?>
         <div class="row">
            <div class="col-md-12">
               <div class="card">
                  <div class="card-header">
                     <div class="card-title">Add your Analytics Code</div>
                  </div>
				  <form action="<?php anchor_to(LAYOUT_CONTROLLER . '/analytics') ?>" method="POST">
					<div class="card-body">
						<div class="row">
							<div class="col-12">
								<div class="form-group">
									<label for="analytics">Analytics UA Code</label>
									<input name="site-analytics" class="form-control form-control-lg resize-none" id="analytics" value="<?php echo legacy_esc($page_data['analytics'], true) ?>" placeholder="Ex: UA-123456789-0">
									<small>Paste here google analytics UA code. Ex. <code>UA-123456789-0</code>.</small>
								</div>
							</div>
						</div>
					</div>
					<div class="card-action">
						<input type="hidden" name="submit" value="Submit">
						<button type="submit" class="btn btn-success"><i class="fas fa-check mr-1"></i> Update Analytics</button>
				  </form>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<!-- End Page Content -->
</div>
<?php echo view('admin/includes/foot', get_defined_vars()); ?>