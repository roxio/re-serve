<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

A PHP Error was encountered

Severity:    <?php echo html_escape($severity, true), "\n"; ?>
Message:     <?php echo html_escape($message, true), "\n"; ?>
Filename:    <?php echo html_escape($filepath, true), "\n"; ?>
Line Number: <?php echo html_escape($line, true); ?>

<?php if (defined('SHOW_DEBUG_BACKTRACE') && SHOW_DEBUG_BACKTRACE === TRUE): ?>

Backtrace:
<?php	foreach (debug_backtrace() as $error): ?>
<?php		if (isset($error['file']) && strpos($error['file'], realpath(BASEPATH)) !== 0): ?>
	File: <?php echo html_escape($error['file'], true), "\n"; ?>
	Line: <?php echo html_escape($error['line'], true), "\n"; ?>
	Function: <?php echo html_escape($error['function'], true), "\n\n"; ?>
<?php		endif ?>
<?php	endforeach ?>

<?php endif ?>
