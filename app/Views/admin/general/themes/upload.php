<?php echo view('admin/includes/head', get_defined_vars()); ?>
<div class="wrapper fullheight-side">
<?php echo view('admin/includes/header', get_defined_vars());
echo view('admin/includes/sidebar', get_defined_vars()); 
echo view('admin/includes/navbar', get_defined_vars()); ?>

<!-- Page Content -->

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
                        <a href="<?php anchor_to(GENERAL_CONTROLLER . '/themes') ?>">
                        Theme Settings
                        </a>
                    </li>
                    <li class="separator">
                        <i class="flaticon-right-arrow"></i>
                    </li>
                    <li class="nav-home">
                        <a href="<?php anchor_to(GENERAL_CONTROLLER . '/upload_theme') ?>">
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
                            <div class="card-title">Upload a New Theme</div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <form enctype="multipart/form-data" method="POST" accept="application/zip" action="<?php anchor_to(GENERAL_CONTROLLER . '/upload_theme') ?>">
                                        <div class="form-group">
                                            <input type="file" allowed="zip" name="theme" class="alert w-100">
                                        </div>
                                        <div class="form-group">
                                            <input class="btn btn-success" type="submit" name="submit" value="Submit">
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- End Page Content -->

</div>
<?php echo view('admin/includes/foot', get_defined_vars()); ?>
<?php echo view('admin/includes/footEnd', get_defined_vars()); ?>