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
			<widget>1 User(s) Online</widget>
			<widget>0 Notifications</widget>
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
