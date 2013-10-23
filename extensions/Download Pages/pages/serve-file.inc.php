<widget class="file download">
<h1>Download File</h1><?php
if (array_key_exists("key", $_GET)) {
?><div class="error">That download session expired, you need to click download again.</div><?php
}
?><h2><?php echo basename(REQUEST_URI); ?></h2>
<p><a href="<?php echo REQUEST_URI; ?>?key=<?php echo urlencode($_SESSION['file-serve-key']); ?>" class="button">Download File</a></p>
</widget>
