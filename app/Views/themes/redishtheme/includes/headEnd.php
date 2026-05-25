
</head>
<?php $uri = strtolower(service('uri')->getSegment(1) ?? ''); ?>
<body class="<?php echo_if($uri == 'enduser' || $uri == 'userbooking', 'endUserBody')?>">
