<?php

function __pageSearch_Callback($matches, $filters) {
	OutputFilter::startRawOutput();
	die;
}

SearchCore::registerSection("Pages", "{{name}}
{{small}}{{url}}{{endsmall}}");
SearchCore::registerHandler(".+", "__pageSearch_Callback", "Miscellaneous", "Pages");

?>
