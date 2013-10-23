<h2>Definition for `<?php echo $_GET['db']; ?>`</h2><div style="width: 600px; height: 400px; overflow: scroll"><pre><?php
if (defined("JSON_PRETTY_PRINT"))
	echo json_encode(Database::getInstance($_GET['db'])->getDefinition(), JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);
else
	print_r(Database::getInstance($_GET['db'])->getDefinition());
?></pre></div>
