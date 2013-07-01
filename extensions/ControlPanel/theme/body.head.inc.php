<framework:widgets>
		<whiteout style="display: none;"><spinner></spinner><popup></popup></whiteout>
		<toolbar style="position: relative; z-index: 300" align="left">
			<?
			$banner = fullpath("cp-banner.png");
			if(file_exists($banner)) {
				echo "<a title='Return to Website' href='/'><img style='float: left; display: inline-block; margin: 0px 2px; padding: 0px;' src='";
				echo Framework::getReferenceURI($banner);
				echo "' alt='";
				echo $domain;
				echo "' height='32px' /></a>";
				echo '<widget onclick="location.href=\'/control\'">Control Panel</widget>';
			} else {
				$logo = fullpath("logo.png");
				if(!file_exists($logo))
					$logo = fullpath("favicon.png");
				$domain = StringFormat::displayForID(DOMAIN_SL);
				if(file_exists($logo)) {
					echo "<a title='Return to Website' href='/'><img style='float: left; display: inline-block; margin: 1px 5px; padding: 0px;' src='";
					echo Framework::getReferenceURI($logo);
					echo "' alt='";
					echo $domain;
					echo "' height='30px' /></a>";
				}
			?><widget onclick="location.href='/control'"><? echo $domain; ?> Control Panel</widget><? } ?>
			<?
			foreach(ControlPanel::getToolbarWidgets() as $widget) {
				echo "<widget";
				if($widget[2])
					echo " href='control://$widget[2]'";
				echo ">";
				if(is_callable($widget[1]))
					$widget[1] = call_user_func($widget[1]);
				echo interpolate($widget[0]);
				if(is_array($widget[1])) {
					echo "<menu>";
					foreach($widget[1] as $text => $url) {
						if($url == "----")
							echo "<hr />";
						else if(is_numeric($text)) {
							echo "<a>";
							echo interpolate($url);
							echo "</a>";
						} else {
							echo "<a href=\"$url\">";
							echo interpolate($text);
							echo "</a>";
						}
					}
					echo "</menu>";
				}
				echo "</widget>";
			}
			?>
			<widget class="last">Logged in as <? echo User::getFullName(); ?>
				<menu>
					<a href="control://Users/Profile">Edit My Profile</a>
					<a href="control://Users/Logout">Log Out</a>
				</menu>
			</widget>
		</toolbar><navbar style="position: relative;">
<?php
if(PageModule::countArguments() === 3)
    ControlPanel::dumpNavBar(PageModule::getArgument(1), PageModule::getArgument(2));
else
    ControlPanel::dumpNavBar();
?><uploads></uploads></navbar>
