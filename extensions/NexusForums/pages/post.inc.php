<?php
function userBlockFor($user) {
	$user = User::fetch($user, User::FETCH_ANY_USER);
?><div class="user <?php echo StringFormat::idForDisplay($user->getLevelString()); ?>">
	<h2><?php echo $user->getDisplayName(); ?></h2>
	<h3><?php echo $user->getTitle(); ?></h3>
	<img src="<?php echo $user->getAvatar(84); ?>" />
	<dl>
		<dt>Website</dt> <dd>http://www.domain.com/</dd>
		<dt>Popularity</dt> <dd><?php echo $user->getLevel(); ?>pts</dd>
	</dl></div><?php
}
?><widget class="forum head">
<h1>Example Forum Thread</h1>
<p>Somewhat long, maximum of 500 characters, description of this thread.<br />
Along with information about the poll below.</p>
<hr />
<table class="poll"><tr><th>Green</th><td><span>12%</span><bar style="width: 12%"></bar></td></tr>
<tr><th>Red</th><td><span>42%</span><bar style="width: 42%"></bar></td></tr>
<tr><th>Blue</th><td><span>6%</span><bar style="width: 6%"></bar></td></tr>
<tr><th>Orange</th><td><span>24%</span><bar style="width: 24%"></bar></td></tr>
</table></widget>

<widget style="padding: 0px" class="forum thread"><style>
widget.forum {
	line-height: normal;
}
widget.forum table {
	width: 100%;
}
widget.forum.head table.poll {
	white-space: nowrap;
}
widget.forum.head table.poll th {
	padding-right: 12px;
}
widget.forum.head table.poll td {
	width: 100%;
	text-align: center;
	position: relative;
	line-height: 25px;
}
widget.forum.head table.poll td bar {
	width: 0%;
	margin: 0px;
	height: 25px;
	margin-top: -25px;
	background-color: #cdcdcd;
	display: block;
}
widget.forum hr {
	height: 1px;
	border: none;
	background-color: #ababab;
}
widget.forum h2 {
	font-size: 120%;
}
widget.forum h3 {
	font-size: 110%;
	font-weight: normal;
	font-style: italic;
}
widget.forum.thread {
	display: table;
}
widget.forum.thread post {
	display: block;
	overflow: hidden;
	position: relative;
	text-align: left;
}
widget.forum.thread post timestamp {
	display: inline-block;
	border-bottom-left-radius: 4px;
	-moz-border-radius-bottomleft: 4px;
	-webkit-border-bottom-left-radius: 4px;
    transition: all 200ms;
    -moz-transition: all 200ms; /* Firefox 4 */
    -webkit-transition: all 200ms; /* Safari and Chrome */
    -o-transition: all 200ms; /* Opera */
	border: solid 1pt #d7d7d7;
	border-right: none;
	border-top: none;
	padding: 4px;
	margin-top: -6px;
	margin-right: -6px;
	margin-bottom: 6px;
	margin-left: 6px;
	float: right;
}
widget.forum.thread post:hover timestamp {
	-webkit-box-shadow:  -1px 1px 2px 0px rgba(0, 0, 0, 0.3);
    box-shadow:  -1px 1px 2px 0px rgba(0, 0, 0, 0.3);
}
widget.forum.thread post div.user {
	display: table-cell;
	border-right: solid 1px #aaa;
	border-bottom: solid 1px #aaa;
	background: #d7d7d7;
	position: relative;
	font-size: 75%;
	padding: 6px;
	height: 100%;
	width: 160px;
}
widget.forum.thread post div.user.staff h2 {
	color: #d8d808;
}
widget.forum.thread post div.user.admin h2 {
	color: #0505c6;
}
widget.forum.thread post div.user.super-admin h2,
widget.forum.thread post div.user.system h2 {
	color: #f29900;
}
widget.forum.thread post div.user.owner h2,
widget.forum.thread post div.user.root h2 {
	color: purple;
}
widget.forum.thread post div.user.banned h2,
widget.forum.thread post div.user.disabled h2 {
	color: red;
}
widget.forum.thread post div.user dl {
	margin: 0px;
}
widget.forum.thread post div.user dl dt {
	float: left;
	padding: 2px;
	text-align: left;
	color: #777777;
	margin-right: 10px;
	min-width: 60px;
	margin-left: 0;
}
widget.forum.thread post div.user dl dd {
	-webkit-margin-start: 0px;
	word-wrap: break-word;
	max-width: 140px;
	display: block;
	padding: 2px;
}
widget.forum.thread post div.user img {
	margin: 2px 0px;
	height: 84px;
	width: 84px;
}
widget.forum.thread post div.user hr {
	border: none;
	background-color: #989898;
	margin: 3px 0px;
	height: 1px;
}
widget.forum.thread post p {
	display: table-cell;
	border-bottom: solid 1px #aaa;
    transition: all 200ms;
    -moz-transition: all 200ms; /* Firefox 4 */
    -webkit-transition: all 200ms; /* Safari and Chrome */
    -o-transition: all 200ms; /* Opera */
	position: relative;
	padding: 6px;
	margin: 0px;
}
widget.forum.thread post buttons {
	padding: 4px;
	display: block;
	position: absolute;
    transition: all 400ms;
    -moz-transition: all 400ms; /* Firefox 4 */
    -webkit-transition: all 400ms; /* Safari and Chrome */
    -o-transition: all 400ms; /* Opera */
	border-top-left-radius: 4px;
	border-left: solid 1px #aaa;
	border-top: solid 1px #aaa;
    line-height: 18px;
    font-size: 90%;
	bottom: -38px;
	opacity: 0;
	right: 0px;
}
widget.forum.thread post:hover buttons {
	-webkit-box-shadow:  -1px -1px 4px 0px rgba(0, 0, 0, 0.2);
    box-shadow:  -1px -1px 4px 0px rgba(0, 0, 0, 0.2);
	bottom: 0px;
	opacity: 1;
}
widget.forum.thread post p:hover {
	-webkit-box-shadow: inset 0px 0px 4px 0px rgba(0, 0, 0, 0.3);
    box-shadow: inset 0px 0px 4px 0px rgba(0, 0, 0, 0.3);
}
widget.forum.thread post.bottom div.user,
widget.forum.thread post.bottom p {
	border-bottom: none;
}
widget.forum.thread post.bottom div.user {
	-webkit-border-bottom-left-radius: 8px;
	-moz-border-radius-bottomleft: 8px;
	border-bottom-left-radius: 8px;
}
widget.forum.thread post.top div.user {
	-webkit-border-top-left-radius: 8px;
	-moz-border-radius-topleft: 8px;
	border-top-left-radius: 8px;
}
widget.forum.footer {
	font-size: 85%;
}
widget.forum.footer color {
	display: inline-block;
	vertical-align: middle;
	margin: 4px;
	height: 8px;
	width: 8px;
}
widget.forum.footer h3 {
	font-size: 120%;
	margin-bottom: 6px;
}
</style><post class="top"><?php
echo userBlockFor("ktaeyln");
?>
<p><timestamp>Posted on <?php echo StringFormat::formatDate(time()); ?></timestamp>
Lorem ipsum dolor sit amet, eu quam ipsum ultricies ac, sed vestibulum ante, tincidunt laoreet sed sollicitudin ipsum, porttitor lectus libero quam convallis integer, venenatis tortor. Quis laoreet nec potenti id, vitae hendrerit morbi tortor integer. Vel mauris hac vitae velit morbi, urna laoreet nascetur suspendisse, tincidunt cubilia ante curabitur. Orci phasellus, ut rhoncus aenean suscipit porttitor eget, duis mattis sit in feugiat vel.</p>
<buttons><a href="" class="button">Like/Share</a> <a href="" class="button">Quote</a> <a popup href="<?php echo REQUEST_URI; ?>/reply" class="button">Reply</a></buttons>
</post><post><?php
userBlockFor("root");
?>
<p><timestamp>Posted on <?php echo StringFormat::formatDate(time()); ?></timestamp>
Lorem ipsum dolor sit amet, eu quam ipsum ultricies ac, sed vestibulum ante, tincidunt laoreet sed sollicitudin ipsum, porttitor lectus libero quam convallis integer, venenatis tortor. Quis laoreet nec potenti id, vitae hendrerit morbi tortor integer. Vel mauris hac vitae velit morbi, urna laoreet nascetur suspendisse, tincidunt cubilia ante curabitur. Orci phasellus, ut rhoncus aenean suscipit porttitor eget, duis mattis sit in feugiat vel.</p>
<buttons><a href="" class="button">Like/Share</a> <a href="" class="button">Quote</a> <a popup href="<?php echo REQUEST_URI; ?>/reply" class="button">Reply</a></buttons>
</post><post><?php
userBlockFor("system");
?>
<p><timestamp>Posted on <?php echo StringFormat::formatDate(time()); ?></timestamp>
Lorem ipsum dolor sit amet, eu quam ipsum ultricies ac, sed vestibulum ante, tincidunt laoreet sed sollicitudin ipsum, porttitor lectus libero quam convallis integer, venenatis tortor. Quis laoreet nec potenti id, vitae hendrerit morbi tortor integer. Vel mauris hac vitae velit morbi, urna laoreet nascetur suspendisse, tincidunt cubilia ante curabitur. Orci phasellus, ut rhoncus aenean suscipit porttitor eget, duis mattis sit in feugiat vel.</p>
<buttons><a href="" class="button">Like/Share</a> <a href="" class="button">Quote</a> <a popup href="<?php echo REQUEST_URI; ?>/reply" class="button">Reply</a></buttons>
</post><post><?php
userBlockFor("guest");
?>
<p><timestamp>Posted on <?php echo StringFormat::formatDate(time()); ?></timestamp>
Lorem ipsum dolor sit amet, eu quam ipsum ultricies ac, sed vestibulum ante, tincidunt laoreet sed sollicitudin ipsum, porttitor lectus libero quam convallis integer, venenatis tortor. Quis laoreet nec potenti id, vitae hendrerit morbi tortor integer. Vel mauris hac vitae velit morbi, urna laoreet nascetur suspendisse, tincidunt cubilia ante curabitur. Orci phasellus, ut rhoncus aenean suscipit porttitor eget, duis mattis sit in feugiat vel.</p>
<buttons><a href="" class="button">Like/Share</a> <a href="" class="button">Quote</a> <a popup href="<?php echo REQUEST_URI; ?>/reply" class="button">Reply</a></buttons>
</post><post class="bottom"><?php
userBlockFor("root");
?>
<p><timestamp>Posted on <?php echo StringFormat::formatDate(time()); ?></timestamp>
Lorem ipsum dolor sit amet, eu quam ipsum ultricies ac, sed vestibulum ante, tincidunt laoreet sed sollicitudin ipsum, porttitor lectus libero quam convallis integer, venenatis tortor. Quis laoreet nec potenti id, vitae hendrerit morbi tortor integer. Vel mauris hac vitae velit morbi, urna laoreet nascetur suspendisse, tincidunt cubilia ante curabitur. Orci phasellus, ut rhoncus aenean suscipit porttitor eget, duis mattis sit in feugiat vel.</p>
<buttons><a href="" class="button">Like/Share</a> <a href="" class="button">Quote</a> <a popup href="<?php echo REQUEST_URI; ?>/reply" class="button">Reply</a></buttons>
</post></widget>

<widget class="forum footer">
<table><tr><td valign="top">
<div style="color: #767676; border: solid 1pt #ccc; padding: 3px; font-size: 90%; width: 140px; float: right; padding-bottom: 8px">Do not trust the text on the page, always check the colour of someone's name before assuming they're staff.<br /><br />
<a style="text-align: center;" href="/forum/general/rules" class="button">Forum Rules</a>
</div>
<h3>Name Colour Legend</h3>
<color style="background-color: red"></color>Banned<br />
<color style="background-color: #2a2a2a"></color>Member<br />
<color style="background-color: #d8d808"></color>Moderator<br />
<color style="background-color: #0505c6"></color>Administrator<br />
<color style="background-color: #f29900"></color>Super Admin<br />
<color style="background-color: purple"></color>Owner
</td><td style="padding-left: 8px;" valign="top">
<div style="float: right; width: 140px; padding-left: 6px">
<h3>Available Features</h3>
<h4>In this thread</h4>
BB code is On<br />
Markdown code is On<br />
HTML code is Off<br /><br />

<a style="text-align: center;" href="/forum/help/forum-code" class="button">Post Help</a></div>
<h3>Permissions</h3><h4>In this thread</h4>
You may not post replies<br />
You may not post attachments<br />
You may not edit your posts
</td></tr></table>
<hr />
0 other users are viewing this post.
</widget>
