<widget><h1>Nexus PHP Framework</h1>
<a href="<? echo BASE_URI; ?>about:framework">About</a><br />
<a href="<? echo BASE_URI; ?>about:license">Licensing</a><br />
<a href="<? echo BASE_URI; ?>about:sitemap">Sitemap</a>
<hr />
<a target="_blank" href="http://svn.nexustools.net/nexusframework/trunk">SVN Trunk</a></widget>
<?
if(User::isStaff()) {
?><widget><h1>Developer References</h1>
<a target="_blank" href="<? echo BASE_URI; ?>?api&format=help">API</a><br />
<a href="<? echo BASE_URI; ?>about:interpolation">Interpolation</a></widget><? } ?>
<widget><h1>Links</h1>
<a target="_blank" href="http://prototypejs.org/">PrototypeJS</a><br />
<a target="_blank" href="http://www.nexustools.net/">NexusTools</a></widget>
