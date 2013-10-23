<?php
function getallheaders() {
	foreach ($_SERVER as $h => $v)
		if (ereg('HTTP_(.+)', $h, $hp)) {
			$key = "";
			$upper = true;
			foreach (str_split($hp[1]) as $char) {
				if ($char == "_") {
					$key .= '-';
					$upper = true;
					continue;
				}

				if ($upper) {
					$key .= $char;
					$upper = false;
				} else
					$key .= strtolower($char);
			}
			$headers[$key] = $v;
		}

	return $headers;
}
?>
