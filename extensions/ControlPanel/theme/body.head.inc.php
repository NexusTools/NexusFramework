<framework:widgets>
		<whiteout style="display: none;"><spinner></spinner><popup></popup></whiteout>
		<toolbar style="position: relative; z-index: 100" align="left">
			<widget><? echo BASE_URL; ?></widget>
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
?>
		<uploads></uploads></navbar>
