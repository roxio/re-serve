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
                        <a href="<?php anchor_to(GALLERY_CONTROLLER . '/categories') ?>">
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
                            <div class="card-title float-left">Gallery</div>
                            <a href="<?php anchor_to(GALLERY_CONTROLLER . '/catAdd') ?>" class="btn btn-primary float-right"><i class="fas fa-plus mr-2"></i> Add Category</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped mt-3">
                                    <thead>
                                        <tr>
                                            <th scope="col">Category Name</th>
                                            <th class="text-right">Attached Images</th>
                                            <th class="text-right" scope="col">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(!empty($categories)){?>
                                        <?php foreach ($categories as $gCat ){ ?>
                                        <tr>
                                            <td><?php echo legacy_esc($gCat['cName'], true) ?></td>
                                            <th class="text-right"><?php echo legacy_esc($gCat['count'], true) ?></th>
                                            <td class="text-right">
                                                <a href="<?php anchor_to(GALLERY_CONTROLLER . '/catEdit/' . $gCat['id']) ?>" data-toggle="tooltip" data-placement="top" title="Edit" class="btn btn-link btn-primary btn-lg">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-link btn-danger catDelete" data-toggle="tooltip" data-placement="top" title="Delete" value="<?php echo legacy_esc($gCat['id'], true) ?>"><i class="fa fa-times"></i></button>
                                            </td>
                                        </tr>
                                        <?php }
                                        } else{
                                        ?>
                                        <tr>
                                            <td class="text-center" colspan="3"><h4 class="text-muted">No Category Found</h4></td>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
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
<script type="text/javascript" src="<?php admin_assets('js/plugin/sweetalert/sweetalert.min.js') ?>"></script>
<script type="text/javascript" src="<?php admin_assets('js/includes/alerts.js') ?>"></script>
<?php echo view('admin/includes/footEnd', get_defined_vars()); ?>