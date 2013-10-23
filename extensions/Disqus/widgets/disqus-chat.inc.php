<?php
switch (VirtualPages::getMode()) {

case VirtualPages::CREATE:
	return Array("shortname" => "");

case VirtualPages::RENDER_EDITOR:
	$config = VirtualPages::getArguments();
	echo "Shortname<br /><input style=\"width: 350px;\" name=\"shortname\" class=\"text\" value=\"";
	echo htmlspecialchars($config['shortname']);
	echo "\" />";
	break;

case VirtualPages::UPDATE_CONFIG:
	return Array("shortname" => $_POST['shortname']);

case VirtualPages::RENDER:
	$config = VirtualPages::getArguments();
	if (!strlen($config['shortname']))
		return;
?><div id="disqus_thread"></div>
<noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript>
<a href="http://disqus.com" class="dsq-brlink">comments powered by <span class="logo-disqus">Disqus</span></a><?php
	define("DISQUS_CHAT_SHORTNAME", $config['shortname']);
	Template::addScript(dirname(__FILE__).DIRSEP."disqus-chat.js");
	break;

}
?>
