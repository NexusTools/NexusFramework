<widget><h1>Nexus PHP Framework</h1>
<a href="<?php echo BASE_URI; ?>about:framework">About</a><br />
<a href="<?php echo BASE_URI; ?>about:license">Licensing</a><br />
<a href="<?php echo BASE_URI; ?>about:sitemap">Sitemap</a>
<hr />
<a target="_blank" href="https://github.com/NexusTools/NexusFramework">Source</a></widget>
<?php
if (User::isStaff()) {
?><widget><h1>Developer References</h1>
<a target="_blank" href="<?php echo BASE_URI; ?>?api&format=help">API</a><br />
<a href="<?php echo BASE_URI; ?>about:interpolation">Interpolation</a></widget><?php } ?>
<widget><h1>Links</h1>
<a target="_blank" href="http://prototypejs.org/">PrototypeJS</a><br />
<a target="_blank" href="http://www.nexustools.net/">NexusTools</a></widget>
