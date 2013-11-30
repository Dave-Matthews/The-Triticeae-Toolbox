<?php
require 'config.php';
require $config['root_dir'].'includes/bootstrap.inc';
connect();
require $config['root_dir'].'theme/normal_header.php';
?>

<div id="primaryContentContainer">
	<div id="primaryContent">
  		<div class="box">

			<h2>Access Denied</h2>

			<div class="boxContent">
	
			   <p>Sorry, but you must be <a href="login.php">logged in</a> to view that page</p>

			</div>
		</div>
	</div>
</div>
</div>

<?php
require $config['root_dir'].'theme/footer.php'; ?>
