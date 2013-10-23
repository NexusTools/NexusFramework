<?php
class BBCYoutube {

	/*
		
	 @type video
	 @dialog youtubePicker.class.php
		
	 */
	public static function youtube($rawFiller) {
		if (preg_match('%(?:youtube\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $rawFiller, $match))
			$rawFiller = $match[1];

		if (preg_match("/^[\w\-]{11}$/", $rawFiller))
			return "<iframe width=\"768\" height=\"432\" src=\"http://www.youtube.com/embed/$rawFiller\" frameborder=\"0\" allowfullscreen></iframe>";
		else
			throw new Exception("Invalid Youtube Video URL `$rawFiller`");
	}

}
?>
