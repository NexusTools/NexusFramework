<widget class="file download">
<h1>Download File</h1><?
if(array_key_exists("key", $_GET)) {
	?><div class="error">That download session expired, you need to click download again.</div><?
}
?><h2><? echo basename(REQUEST_URI); ?></h2>
<p><a href="<? echo REQUEST_URI; ?>?key=<? echo urlencode($_SESSION['file-serve-key']); ?>" class="button">Download File</a></p>
</widget>
