<?php
OutputFilter::resetToNative(false);
MailCenter::trackEmailView(PageModule::getValue("email-id"));
Framework::serveFile(dirname(dirname(__FILE__)).DIRSEP."pixel.gif");
?>
