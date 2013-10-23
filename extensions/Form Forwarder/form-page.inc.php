<?php
switch (VirtualPages::getMode()) {
case VirtualPages::RENDER_CREATOR:
?>Show in Website Navigation<br /><input type="radio" value="1" name="inputFormPage-navbar" id="__cp__inputFormPage_inNavbarYes" /><label for="__cp__inputFormPage_inNavbarYes">Yes</label> 
<input type="radio" value="0" name="inputFormPage-navbar" id="__cp__inputFormPage_inNavbarNo" checked /><label for="__cp__inputFormPage_inNavbarNo">No</label><br />Show in Website Footer<br /><input type="radio" value="1" name="inputFormPage-footer" id="__cp__inputFormPage_inFooterYes" /><label for="__cp__inputFormPage_inFooterYes">Yes</label> 
<input type="radio" value="0" name="inputFormPage-footer" id="__cp__inputFormPage_inFooterNo" checked /><label for="__cp__inputFormPage_inFooterNo">No</label><br />
Inherit Header Widgets<br /><input type="radio" value="1" name="inputFormPage-inherit-headers" id="__cp__inputFormPage_inheritHeadersYes" checked /><label for="__cp__inputFormPage_inheritHeadersYes">Yes</label> 
<input type="radio" value="0" name="inputFormPage-inherit-headers" id="__cp__inputFormPage_inheritHeadersNo" /><label for="__cp__inputFormPage_inheritHeadersNo">No</label><br />
Inherit Footer Widgets<br /><input type="radio" value="1" name="inputFormPage-inherit-footers" id="__cp__inputFormPage_inheritFootersYes" checked /><label for="__cp__inputFormPage_inheritFootersYes">Yes</label> 
<input type="radio" value="0" name="inputFormPage-inherit-footers" id="__cp__inputFormPage_inheritFootersNo" /><label for="__cp__inputFormPage_inheritFootersNo">No</label>
<div>Require Recaptcha<br /><input onchange="$('__cp_inputFormPage_recat').style.display=(this.checked ? '' : 'none')" type="radio" checked value="1" name="inputFormPage-require-captcha" id="__cp__inputFormPage_requireRecaptchaYes" /><label for="__cp__inputFormPage_requireRecaptchaYes">Yes</label> 
<input onchange="$('__cp_inputFormPage_recat').style.display=(this.checked ? 'none' : '')" type="radio" value="0" name="inputFormPage-require-captcha" id="__cp__inputFormPage_requireRecaptchaNo" /><label for="__cp__inputFormPage_requireRecaptchaNo">No</label></div>
<div id="__cp_inputFormPage_recat">Recatcha Public Key<br /><input style="width: 350px" name="inputFormPage-captcha-public-key" class="text" type="text" />
<br />Recatcha Private Key<br /><input style="width: 350px" class="text" name="inputFormPage-captcha-private-key" type="text" /></div>
Destination <help title="The form's destination url.">?</help><br />
<input value="" style="width: 350px" class="text" name="inputFormPage-destination" type="text" /><br />Referrer <help title="The url to send from.">?</help><br />
<input value="" style="width: 350px" class="text" name="inputFormPage-referrer" type="text" /><br />Category<br /><?php
	$category = isset($_POST['category']) ? $_POST['category'] : 0;
	echo "<select style=\"width: 350px; font-family: monospace, courier new\" name=\"category\" value=\"$category\">";
	ControlPanel::renderRecursiveSelectOptions(PageCategories::getCategories(), $category);
	echo "</select>";
?><br />
Tags<br />
<textarea name="tags" title="Comma Separated Tags" style="width: 350px; height: 50px;" resize="no"></textarea><br />
Snippet <help title="a.k.a. Description, used in Article Links.">?</help><br />
<textarea name="description" code="html" style="width: 350px; height: 200px; " resize="no"></textarea><br />
Content <help title="use {{FORM_URL}} to get this form's url, and RECAPTCHA to get the recaptcha block.">?</help><br />
<textarea name="content" code="html" style="width: 350px; height: 200px; " resize="no"><form method="POST" action="{{FORM_URL}}">
{{RECAPTCHA}}
<input type="submit" value="Send"></form></textarea><?php
	break;

case VirtualPages::CREATE:
	$pageid = VirtualPages::getArguments();
	$db = Articles::getDatabase();
	$db->insert("instances", Array("page" => $pageid,
		"category" => $_POST['category'],
		"content" => $_POST['content'],
		"navbar" => $_POST['inputFormPage-navbar'],
		"description" => $_POST['description'],
		"infooter" => $_POST['inputFormPage-footer'],
		"inherit-headers" => $_POST['inputFormPage-inherit-headers'],
		"inherit-footers" => $_POST['inputFormPage-inherit-footers']));
	$formDB = Database::getInstance("Form Forwarder");
	$formDB->insert("instances", Array("page" => $pageid,
		"destination" => trim($_POST['inputFormPage-destination']),
		"use-recaptcha" => $_POST['inputFormPage-require-captcha'],
		"recaptcha-public-key" => trim($_POST['inputFormPage-captcha-public-key']),
		"recaptcha-private-key" => trim($_POST['inputFormPage-captcha-private-key']),
		"referrer" => trim($_POST['inputFormPage-referrer'])));

	foreach (preg_split('/\s*,\s*/', $_POST['tags'], 0, PREG_SPLIT_NO_EMPTY) as $tag) {
		$db->insert("page-tags",
			Array("page" => $pageid,
			"tag" => $tag));
	}

	$path = "";
	$category = $_POST['category'];

	Articles::updateNavigation();
	return PageCategories::getPathForCategory($category)."/".StringFormat::idForDisplay($_POST['title']);

case VirtualPages::UPDATE_CONFIG:
	$pageid = VirtualPages::getArguments();
	$db = Articles::getDatabase();
	$db->update("instances", Array("category" => $_POST['category'],
		"content" => $_POST['content'],
		"navbar" => $_POST['inputFormPage-navbar'],
		"description" => $_POST['description'],
		"infooter" => $_POST['inputFormPage-footer'],
		"inherit-headers" => $_POST['inputFormPage-inherit-headers'],
		"inherit-footers" => $_POST['inputFormPage-inherit-footers']), Array("page" => $pageid));
	$formDB = Database::getInstance("Form Forwarder");
	$formDB->update("instances", Array("destination" => trim($_POST['inputFormPage-destination']),
		"use-recaptcha" => $_POST['inputFormPage-require-captcha'],
		"recaptcha-public-key" => trim($_POST['inputFormPage-captcha-public-key']),
		"recaptcha-private-key" => trim($_POST['inputFormPage-captcha-private-key']),
		"referrer" => trim($_POST['inputFormPage-referrer'])), Array("page" => $pageid));

	$db->delete("page-tags", Array("page" => $pageid));
	foreach (preg_split('/\s*,\s*/', $_POST['tags'], 0, PREG_SPLIT_NO_EMPTY) as $tag) {
		$db->insert("page-tags",
			Array("page" => $pageid,
			"tag" => $tag));
	}

	$path = "";
	$category = $_POST['category'];

	VirtualPages::updatePath($pageid, PageCategories::getPathForCategory($category)."/".StringFormat::idForDisplay($_POST['title']));
	Articles::updateNavigation();
	break;

case VirtualPages::DELETE:
	return Articles::getDatabase()->delete("instances", Array("page" => VirtualPages::getArguments()));
	Articles::updateNavigation();
	break;

case VirtualPages::RENDER_EDITOR:
	$page = VirtualPages::getArguments();
	$data = Articles::getDatabase()->selectRow("instances", Array("page" => $page['rowid']));
	$formData = Database::getInstance("Form Forwarder")->selectRow("instances", Array("page" => $page['rowid']));
	if (!$formData) {
		echo "<banner class=\"error\">Missing Form Data Instance</banner>";
		break;
	}

	if (!$data) {
		echo "<banner class=\"error\">Missing Article Instance</banner>";
		break;
	}

	$layout = PageCategories::getLayoutForCategory($data['category']);
?>Widgets<br /><?php
	if ($layout >= 2) {
		echo "<button onclick=\"";
		echo "ControlPanel.loadPage('Pages', 'Edit Widgets', {location: $page[rowid], title: ";
		echo htmlspecialchars("\"`$page[title]` [Left Column]\"");
		echo ", subtitle: ";
		echo htmlspecialchars("\"$page[path]\"");
		echo ", slot: ".VirtualPages::LEFTCOLUMN;
		echo "});return false;\"><center>Left Column<br />";
		echo VirtualPages::countWidgets($page['rowid'], VirtualPages::LEFTCOLUMN);
		echo " Widgets</center></button>";
	}
	echo "<button onclick=\"";
	echo "ControlPanel.loadPage('Pages', 'Edit Widgets', {location: $page[rowid], title: ";
	echo htmlspecialchars("\"`$page[title]` [Page Area]\"");
	echo ", subtitle: ";
	echo htmlspecialchars("\"$page[path]\"");
	echo "});return false;\"><center>Page Area<br />";
	echo VirtualPages::countWidgets($page['rowid']);
	echo " Widgets</center></button>";
	if ($layout == 1 || $layout == 3) {
		echo "<button onclick=\"";
		echo "ControlPanel.loadPage('Pages', 'Edit Widgets', {location: $page[rowid], title: ";
		echo htmlspecialchars("\"`$page[title]` [Right Column]\"");
		echo ", subtitle: ";
		echo htmlspecialchars("\"$page[path]\"");
		echo ", slot: ".VirtualPages::RIGHTCOLUMN;
		echo "});return false;\"><center>Right Column<br />";
		echo VirtualPages::countWidgets($page['rowid'], VirtualPages::RIGHTCOLUMN);
		echo " Widgets</center></button>";
	}
?><br />Show in Website Navigation<br /><input type="radio" value="1" name="inputFormPage-navbar" id="__cp__inputFormPage_inNavbarYes"<?php if($data['navbar']) echo " checked"; ?> /><label for="__cp__inputFormPage_inNavbarYes">Yes</label> 
<input type="radio" value="0" name="inputFormPage-navbar" id="__cp__inputFormPage_inNavbarNo"<?php if(!$data['navbar']) echo " checked"; ?> /><label for="__cp__inputFormPage_inNavbarNo">No</label><br />Show in Website Footer<br /><input type="radio" value="1" name="inputFormPage-footer" id="__cp__inputFormPage_inFooterYes"<?php if($data['infooter']) echo " checked"; ?> /><label for="__cp__inputFormPage_inFooterYes">Yes</label> 
<input type="radio" value="0" name="inputFormPage-footer" id="__cp__inputFormPage_inFooterNo"<?php if(!$data['infooter']) echo " checked"; ?> /><label for="__cp__inputFormPage_inFooterNo">No</label><br />Inherit Header Widgets<br />
<input type="radio" value="1" name="inputFormPage-inherit-headers" id="__cp__inputFormPage_inheritHeaderYes"<?php if($data['inherit-headers']) echo " checked"; ?> /><label for="__cp__inputFormPage_inheritHeaderYes">Yes</label> 
<input type="radio" value="0" name="inputFormPage-inherit-headers" id="__cp__inputFormPage_inheritHeaderNo"<?php if(!$data['inherit-headers']) echo " checked"; ?> /><label for="__cp__inputFormPage_inheritHeaderNo">No</label>
<br />Inherit Footer Widgets<br /><input type="radio" value="1" name="inputFormPage-inherit-footers" id="__cp__inputFormPage_inheritFooterYes"<?php if($data['inherit-footers']) echo " checked"; ?> /><label for="__cp__inputFormPage_inheritFooterYes">Yes</label> 
<input type="radio" value="0" name="inputFormPage-inherit-footers" id="__cp__inputFormPage_inheritFooterNo"<?php if(!$data['inherit-footers']) echo " checked"; ?> /><label for="__cp__inputFormPage_inheritFooterNo">No</label>
<div>Require Recaptcha<br /><input onchange="$('__cp_inputFormPage_recat').style.display=(this.checked ? '' : 'none')" type="radio" value="1" name="inputFormPage-require-captcha"<?php if($formData['use-recaptcha']){echo " checked";} ?> id="__cp__inputFormPage_requireRecaptchaYes" /><label for="__cp__inputFormPage_requireRecaptchaYes">Yes</label> 
<input onchange="$('__cp_inputFormPage_recat').style.display=(this.checked ? 'none' : '')"<?php if(!$formData['use-recaptcha']){echo " checked";} ?> type="radio" value="0" name="inputFormPage-require-captcha" id="__cp__inputFormPage_requireRecaptchaNo" /><label for="__cp__inputFormPage_requireRecaptchaNo">No</label></div>
<div id="__cp_inputFormPage_recat">Recatcha Public Key<br /><input value="<?php echo htmlspecialchars($formData['recaptcha-public-key']); ?>" style="width: 350px" name="inputFormPage-captcha-public-key" class="text" type="text" />
<br />Recatcha Private Key<br /><input value="<?php echo htmlspecialchars($formData['recaptcha-private-key']); ?>" style="width: 350px" class="text" name="inputFormPage-captcha-private-key" type="text" /></div>
Destination <help title="The form's destination url.">?</help><br />
<input value="<?php echo htmlspecialchars($formData['destination']); ?>" style="width: 350px" class="text" name="inputFormPage-destination" type="text" /><br />Referrer <help title="The url to send from.">?</help><br />
<input value="<?php echo htmlspecialchars($formData['referrer']); ?>" style="width: 350px" class="text" name="inputFormPage-referrer" type="text" /><br />Category<br />
<?php
	echo "<select style=\"width: 350px; font-family: monospace, courier new\" name=\"category\" value=\"$data[category]\">";
	ControlPanel::renderRecursiveSelectOptions(PageCategories::getCategories(), $data['category']);
	echo "</select>";
?><br />
Tags<br />
<textarea name="tags" title="Comma Separated Tags" style="width: 350px; height: 50px;" resize="no"><?php
	foreach (Articles::getDatabase()->selectArray("page-tags", Array("page" => $page['rowid']), "tag") as $tag)
		echo htmlspecialchars($tag).", ";
?></textarea><br />
Snippet <help title="a.k.a. Description, used in Article Links.">?</help><br />
<textarea name="description" code="html" style="width: 350px; height: 200px; " resize="no"><?php
	echo htmlspecialchars($data['description']);
?></textarea><br />Content <help title="use {{FORM_URL}} to get this form's url, and RECAPTCHA to get the recaptcha block.">?</help><br />
<textarea name="content" code="html" style="width: 100%; height: 400px; " resize="no"><?php
	echo htmlspecialchars($data['content']);
?></textarea><?php
	break;

case VirtualPages::RENDER:
	$page = VirtualPages::getArguments();
	$article = Articles::getDatabase()->selectRow("instances", Array("page" => $page['rowid']));
	$layout = Articles::getLayoutForArticle($page['rowid']);
	$formData = Database::getInstance("Form Forwarder")->selectRow("instances", Array("page" => $page['rowid']));

	if ($formData['use-recaptcha']) {

		require_once(dirname(__FILE__).DIRSEP."recaptchalib.php");
		define("RECAPTCHA", (RECAPTCHA_WRONG ? "<font color=\"red\">Captcha Wrong</font><br />" : "").recaptcha_get_html($formData['recaptcha-public-key']));
		if (RECAPTCHA_WRONG)
			define("FORM_ERROR_HTML", "<div style=\"border: solid 1pt black; background-color: red; color: white; padding: 5px\">Captcha Entered Incorrectly</div>");
	}
	if (!defined("FORM_ERROR_HTML"))
		define("FORM_ERROR_HTML", "");

	switch ($layout) {
	case 0: // Single Column
		echo "<Column class=\"pagearea large\">";
		try {
			require_chdir("pagearea.inc.php", Theme::getPath());
		} catch (Exception $e) {
		}
		if ($article['inherit-headers'])
			Articles::runInheritedWidgets($page['rowid'], 0);
		VirtualPages::runWidgets($page['rowid']);
		echo interpolate($article['content'], true, Triggers::broadcast("template", "page-data"));
		if ($article['inherit-footers'])
			Articles::runInheritedWidgets($page['rowid'], 1);
		echo "</Column>";
		break;

	case 1: // Right Sidebar
		echo "<Column class=\"pagearea medium\">";
		try {
			require_chdir("pagearea.inc.php", Theme::getPath());
		} catch (Exception $e) {
		}
		if ($article['inherit-headers'])
			Articles::runInheritedWidgets($page['rowid'], 0);
		VirtualPages::runWidgets($page['rowid']);
		echo interpolate($article['content'], true, Triggers::broadcast("template", "page-data"));
		if ($article['inherit-footers'])
			Articles::runInheritedWidgets($page['rowid'], 1);
		echo "</Column><Column class=\"sidebar right\">";
		try {
			require_chdir("right.inc.php", Theme::getPath());
		} catch (Exception $e) {
		}
		if ($article['inherit-headers'])
			Articles::runInheritedWidgets($page['rowid'], 0, VirtualPages::RIGHTCOLUMN);
		VirtualPages::runWidgets($page['rowid'], VirtualPages::RIGHTCOLUMN);
		if ($article['inherit-footers'])
			Articles::runInheritedWidgets($page['rowid'], 1, VirtualPages::RIGHTCOLUMN);
		echo "</Column>";
		break;

	case 2: // Left Sidebar
		echo "<Column class=\"sidebar left\">";
		echo "<pre>";
		print_r(Triggers::broadcast("template", "page-data"));
		echo "</pre>";

		try {
			require_chdir("left.inc.php", Theme::getPath());
		} catch (Exception $e) {
		}
		if ($article['inherit-headers'])
			Articles::runInheritedWidgets($page['rowid'], 0, VirtualPages::LEFTCOLUMN);
		VirtualPages::runWidgets($page['rowid'], VirtualPages::LEFTCOLUMN);
		if ($article['inherit-footers'])
			Articles::runInheritedWidgets($page['rowid'], 1, VirtualPages::LEFTCOLUMN);
		echo "</Column><Column class=\"pagearea medium\">";
		try {
			require_chdir("pagearea.inc.php", Theme::getPath());
		} catch (Exception $e) {
		}
		if ($article['inherit-headers'])
			Articles::runInheritedWidgets($page['rowid'], 0); // Header Widgets
		VirtualPages::runWidgets($page['rowid']);
		echo interpolate($article['content'], true, Triggers::broadcast("template", "page-data"));
		if ($article['inherit-footers'])
			Articles::runInheritedWidgets($page['rowid'], 1); // Footer Widgets
		echo "</Column>";
		break;

	case 3: // Dual Sidebars
		echo "<Column class=\"sidebar left\">";
		try {
			require_chdir("left.inc.php", Theme::getPath());
		} catch (Exception $e) {
		}
		if ($article['inherit-headers'])
			Articles::runInheritedWidgets($page['rowid'], 0, VirtualPages::LEFTCOLUMN);
		VirtualPages::runWidgets($page['rowid'], VirtualPages::LEFTCOLUMN);
		if ($article['inherit-footers'])
			Articles::runInheritedWidgets($page['rowid'], 1, VirtualPages::LEFTCOLUMN);
		echo "</Column><Column class=\"pagearea small\">";
		try {
			require_chdir("pagearea.inc.php", Theme::getPath());
		} catch (Exception $e) {
		}
		if ($article['inherit-headers'])
			Articles::runInheritedWidgets($page['rowid'], 0); // Header Widgets
		VirtualPages::runWidgets($page['rowid']);
		echo interpolate($article['content'], true, Triggers::broadcast("template", "page-data"));
		if ($article['inherit-footers'])
			Articles::runInheritedWidgets($page['rowid'], 1); // Footer Widgets
		echo "</Column><Column class=\"sidebar right\">";
		try {
			require_chdir("right.inc.php", Theme::getPath());
		} catch (Exception $e) {
		}
		if ($article['inherit-headers'])
			Articles::runInheritedWidgets($page['rowid'], 0, VirtualPages::RIGHTCOLUMN);
		VirtualPages::runWidgets($page['rowid'], VirtualPages::RIGHTCOLUMN);
		if ($article['inherit-footers'])
			Articles::runInheritedWidgets($page['rowid'], 1, VirtualPages::RIGHTCOLUMN);
		echo "</Column>";
		break;
	}

	break;

case VirtualPages::HEADER:
	$page = VirtualPages::getArguments();
	if ($_SERVER['REQUEST_METHOD'] == "POST") {

		OutputHandlerStack::setBufferEnabled(false);
		$privateKey = Database::getInstance("Form Forwarder")->selectField("instances", Array("page" => $page['rowid']), "recaptcha-private-key");
		require_once(dirname(__FILE__).DIRSEP."recaptchalib.php");
		$resp = recaptcha_check_answer($privateKey,
			ClientInfo::getRemoteAddress(),
			$_POST["recaptcha_challenge_field"],
			$_POST["recaptcha_response_field"]);
		if ($resp->is_valid) {
			$formData = Database::getInstance("Form Forwarder")->selectRow("instances", Array("page" => $page['rowid']));
			$ch = curl_init();

			$data = Array();
			foreach ($_POST as $key => $val)
				if (!startsWith($key, "recaptcha_"))
					$data[$key] = $val;

			//set the url, number of POST vars, POST data
			curl_setopt($ch, CURLOPT_URL, $formData['destination']);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('HTTP_FORWARDED_FOR: '.ClientInfo::getRemoteAddress()));
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_MUTE, true);
			curl_setopt($ch, CURLOPT_REFERER, $formData['referrer']);
			curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSLVERSION, 3);

			//execute post
			$result = curl_exec($ch);
			$last_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

			//close connection
			curl_close($ch);

			if ($result)
				Framework::redirect($last_url, false, true);
			else
				echo("<h2>We were unable to submit your information to the database, please try again later.</h2>");

			die();
		} else
			define("RECAPTCHA_WRONG", true);
		OutputHandlerStack::setBufferEnabled(true);
	} else
		define("RECAPTCHA_WRONG", false);

	$layout = Articles::getLayoutForArticle($page['rowid']);
	switch ($layout) {
	case 0: // Single Column
		Articles::initializeInheritedWidgets($page['rowid'], 0);
		Articles::initializeInheritedWidgets($page['rowid'], 1);
		break;

	case 1: // Right Sidebar
		Articles::initializeInheritedWidgets($page['rowid'], 0);
		Articles::initializeInheritedWidgets($page['rowid'], 1);
		Articles::initializeInheritedWidgets($page['rowid'], 0, VirtualPages::RIGHTCOLUMN);
		Articles::initializeInheritedWidgets($page['rowid'], 1, VirtualPages::RIGHTCOLUMN);
		break;

	case 2: // Left Sidebar
		Articles::initializeInheritedWidgets($page['rowid'], 0);
		Articles::initializeInheritedWidgets($page['rowid'], 1);
		Articles::initializeInheritedWidgets($page['rowid'], 0, VirtualPages::LEFTCOLUMN);
		Articles::initializeInheritedWidgets($page['rowid'], 1, VirtualPages::LEFTCOLUMN);
		break;

	case 3: // Dual Sidebars
		Articles::initializeInheritedWidgets($page['rowid'], 0);
		Articles::initializeInheritedWidgets($page['rowid'], 1);
		Articles::initializeInheritedWidgets($page['rowid'], 0, VirtualPages::LEFTCOLUMN);
		Articles::initializeInheritedWidgets($page['rowid'], 1, VirtualPages::LEFTCOLUMN);
		Articles::initializeInheritedWidgets($page['rowid'], 0, VirtualPages::RIGHTCOLUMN);
		Articles::initializeInheritedWidgets($page['rowid'], 1, VirtualPages::RIGHTCOLUMN);
		break;
	}
	break;
}
?>
