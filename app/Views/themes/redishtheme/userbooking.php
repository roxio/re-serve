<?php $theme_view('includes/head'); ?>
<?php $theme_view('includes/headEnd'); ?>
<?php $theme_view('includes/header'); ?>
	
	<div class="mainSection endUser">
		<div class="container">
            <div class="profileSetting selectionBoxMain clearfix">
                <h1 class="profileTitle"><?php echo legacy_esc($title, true); ?></h1>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Service</th>
                                <th scope="col">Agent Name</th>
                                <th scope="col">Date</th>
                                <th scope="col">Time</th>
                                <th scope="col">Adults</th>
                                <th scope="col">Childrens</th>
                                <th scope="col">Total Bill</th>
                                <th scope="col">Service Status</th>
                                <th scope="col">Payment Status</th>
                                <?php if($stripe['status'] == 1){ ?><th scope="col">Action</th><?php } ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!$bookings){?>
                                <tr>
                                    <td colspan="<?php echo $stripe['status'] == 1 ? 11 : 10; ?>" class="text-center"><h4 class="text-muted">No Booking Found</h4></td>
                                </tr>
                            <?php } else{?>
                            <?php foreach ($bookings as $booking ){ ?>
                                <?php
                                    $isPaid = (string) ($booking['paymentStatus'] ?? '') === '1' || ! empty($booking['orderId']);
                                    $hasOnlineReceipt = ! empty($booking['orderId']);
                                ?>
                                <tr>
                                    <th scope="row"><?php echo legacy_esc($booking['id'], true); ?></th>
                                    <td><?php echo legacy_esc($booking['title'], true); ?></td>
                                    <td><?php if($booking['agentId'] == 0){ echo 'Any Agent'; }else{ echo legacy_esc($booking['agentName'], true); } ?></td>
                                    <td><?php echo legacy_esc($booking['date'], true); ?></td>
                                    <td><?php echo legacy_esc($booking['timing'], true) ?></td>
                                    <td><?php echo legacy_esc($booking['adults'], true) ?></td>
                                    <td><?php echo legacy_esc($booking['childrens'], true) ?></td>
                                    <td><?php echo '$'.(legacy_esc($booking['adults'], true) + legacy_esc($booking['childrens'], true))*legacy_esc($booking['price'], true) ?></td>
                                    <td><?php if(legacy_esc($booking['serviceStatus'], true) == '' || legacy_esc($booking['serviceStatus'], true) == '0'){ echo '<span class="badge badge-warning">Pending</span>'; } else if(legacy_esc($booking['serviceStatus'], true) == '1') { echo '<span class="badge badge-success">Confirmed</span>'; } else if(legacy_esc($booking['serviceStatus'], true) == '2') { echo '<span class="badge badge-secondary">Cancelled</span>'; } ?></td>
                                    <td><?php if(!$isPaid){ echo '<span class="badge badge-danger">Due</span>'; } else { echo '<span class="badge badge-success">Paid</span>'; } ?></td>
                                    <?php if($stripe['status'] == 1){ ?>
                                        <td>
                                            <?php
                                                if(!$isPaid){
                                                    echo '<a href="'.base_url('userbooking/paynow/'.legacy_esc($booking['id'], true)).'" class="btn btn-outline-primary btn-sm">Pay Now</a>';
                                                } elseif($hasOnlineReceipt) {
                                                    echo '<a target="_blank" href="'.legacy_esc($booking['receipt_url'], true).'" class="btn btn-primary btn-sm">Invoice</a>';
                                                } else {
                                                    echo '<span class="badge badge-success">Paid</span>';
                                                }
                                            ?>
                                        </td>
                                    <?php } ?>

                                </tr>
                            <?php }} ?>
                        </tbody>
                    </table>
                </div>
            </div>



		</div>
	</div>
	<!-- /mainSection -->
	
<?php $theme_view('includes/foot'); ?>

<?php $theme_view('includes/footEnd'); ?>
