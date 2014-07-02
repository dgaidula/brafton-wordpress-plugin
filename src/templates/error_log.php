<?php
    // Initialize Settings
    require_once( sprintf(realpath(dirname(__FILE__) . '/..') .'/brafton_options.php'));
    $brafton_options = Brafton_options::get_instance(); 

    $log = get_option( 'brafton_error_log' );
    $log_entries = $log['entries'];
 ?>
<div class="wrap">
	<div class="brafton-error-page">
		<h1>Brafton Error Log</h1>
		<ul class="brafton-errors">

		<?php $count = count( $log_entries ); foreach( $log_entries as $entry ) : $count--; ?>
		 		<li ="error-<?php echo $count ?>">
		 			<?php echo $count . ". " .  $entry['message']; ?>
		 		</li>
		<?php endforeach; ?>
		</ul>
	</div>
</div><!-- end wrap -->
