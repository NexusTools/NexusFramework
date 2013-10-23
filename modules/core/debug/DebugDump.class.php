<?php
class DebugDump {

	const MAX_DEPTH = 200;
	private static $dumpedScript = false;

	private static function dump1($nam, $val, $depth, $parent) {
		$is_class = is_object($val);
		if (!$is_class && is_string($val) && startsWith($val, "-")) {
			$cname = substr($val, 1);
			if (class_exists($cname)) {
				$is_class = true;
				$val = $cname;
			}
		}

		if ($is_class) {
			$clas = new ReflectionClass($val);

			if ($depth > self::MAX_DEPTH) {
				echo "$nam [<font color=\"#990000\">".$clas->getName()."</font>:Recursion Limit]";
				return;
			}

			$static = is_string($val);
			$vars = Array();

			if ($clas->isSubclassOf("Exception") || $clas->getName() == "Exception") {
				if (!$static)
					$vars['StackTrace'] = $val->getTrace();
			} else
				if (!$clas->isUserDefined()) {
					self::dump0("$nam [<font color=\"#440000\">".$clas->getName()."</font>]", "<a target=\"_blank\" href=\"http://php.net/manual/en/class.".strtolower($clas->getName()).".php\">Class Documentation</a>", 0, $parent);
					return;
				}

			if ($static) {
				$vars['Extends'] = $clas->getParentClass();
				if ($vars['Extends'])
					$vars['Extends'] = $vars['Extends']->getName();
				else
					unset($vars['Extends']);
			}

			$vars['Methods'] = self::getClassMethods($clas, $static);
			if (!count($vars['Methods']))
				unset($vars['Methods']);

			if ($static)
				foreach ($clas->getConstants() as $key => $val)
					$vars["const $key"] = $val;

			foreach (self::getClassProperties($clas, $static, $val) as $key => $val)
				$vars[$key] = $val;

			if (count($vars))
				self::dump0("$nam [<font color=\"#990000\">".$clas->getName()."</font>]", $vars, 0, $parent);
			else
				echo "<div style=\"padding-left: 13px\">$nam [<font color=\"#990000\">".$clas->getName()."</font>]</div>";
			return;
		}

		if (is_array($val)) {
			$tot = count($val);
			if ($tot) {
				if ($depth > self::MAX_DEPTH)
					echo "<div style=\"padding-left: 13px\">$nam [Array:Recursion Limit]</div>";
				else {
					if (is_assoc($val))
						self::dump0("$nam [AssocArray:".$tot."]", $val, $depth, $parent);
					else
						self::dump0("$nam [Array:".$tot."]", $val, $depth, $parent);
				}
			} else
				echo "<div style=\"padding-left: 13px\">$nam [EmptyArray]</div>";
		} else
			if (is_numeric($val))
				echo "<div style=\"padding-left: 13px\">$nam [Numeric:<b><font color=\"#00a5a0\">$val</font></b>]</div>";
			else
				if (is_bool($val))
					echo "<div style=\"padding-left: 13px\">$nam [Boolean:<b><font color=\"#00a5a0\">".($val ? "True" : "False")."</font></b>]</div>";
				else
					if (is_string($val)) {
						if (startsWith($val, "<"))
							echo "<div style=\"padding-left: 13px\">$val</div>";
						else {
							if (strlen($val) < 200 && strpos($val, "\n") === false)
								echo "<div style=\"padding-left: 13px\">$nam [String:<b><font color=\"#00a5a0\">\"".htmlentities($val)."\"</font></b>]</div>";
							else
								self::dump0("$nam [String]", $val, $depth, $parent);
						}
					} else
						echo "<div style=\"padding-left: 13px\">$nam [Null]</div>";
	}

	private static function dump0($nam, $content, $depth, $parent) {
		$chid = base64_encode(md5($parent.$nam, true));
		echo "<div><a id=\"".$chid."_o\" style=\"font-family:monospace\" href=\"";
		echo REQUEST_URL;
		if (strlen($_SERVER['QUERY_STRING']))
			echo "?".$_SERVER['QUERY_STRING'];
		echo "#$chid\" onclick=\"toggle(this,'$chid');\">+</a> $nam</div>";

		echo "<div style=\"display: none; padding-left: 15px\" id=\"$chid\">";
		if (is_array($content)) {
			$depth++;
			foreach ($content as $name => $value)
				self::dump1($name, $value, $depth, $chid);
		} else
			if (is_int($content)) {
				echo "<input type=\"text\" value=\"$content\" readonly />";
			} else
				if (is_string($content)) {
					if (startsWith($content, "<"))
						echo $content;
					else
						echo "<textarea wrap=\"off\" style=\"width: 500px; height: 100px;\" type=\"text\" readonly>".htmlentities($content)."</textarea>";
				}

		echo "</div>";
	}

	public static function dump($value, $name = "") {
		if (!self::$dumpedScript) {
?><script>
function toggle(cl,id){
	var obj=document.getElementById(id);
	if(obj.style.display=="none"){
		obj.style.display="";
		cl.innerHTML="-";
	}else{
		obj.style.display="none";
		cl.innerHTML="+"
	}
}
window.onload = function(){
	if(window.location.hash){
		var hash = window.location.hash.toString();
		if(hash.indexOf("#") === 0)
			hash = hash.substring(1);
		
		var obj = document.getElementById(hash);
		while(obj.style.display == "none") {
			var link = obj.previousSibling;
			while(link && link.tagName != "DIV")
				link = link.previousSibling;
			if(link)
				link = link.firstChild;
			while(link && link.tagName != "A")
				link = link.previousSibling;
			if(link)
				link.innerHTML = "-";
			
			obj.style.display = "";
			obj = obj.parentNode;
		}
		
	}
}
</script><?php
			self::$dumpedScript = true;
		}

		self::dump1($name, $value, 0, is_object($value) ? get_class($value) : "");
	}

	private static function formatClassElementName($element, $prop) {
		$name = "";
		if (!$prop && $element->isFinal())
			$name .= "final ";

		if ($element->isPublic())
			$name .= "public";
		else
			if ($element->isProtected())
				$name .= "protected";
			else
				$name .= "private";

		if ($element->isStatic())
			$name .= " static";

		if ($prop)
			$name .= " <font color=\"#006600\">$";
		else
			$name .= " ";
		$name .= $element->getName();
		if ($prop)
			$name .= "</font>";
		return $name;
	}

	private static function getClassMethods($clas, $static) {
		$methods = Array();
		foreach ($clas->getMethods() as $method) {
			$smethod = $method->isStatic() || $method->getName() == "__construct";

			if ($smethod != $static)
				continue;

			$argList = null;
			$optionals = 0;
			foreach ($method->getParameters() as $param) {
				if (!$argList) {
					if ($param->isOptional()) {
						$argList = "[";
						$optionals++;
					} else
						$argList = "";
				} else {
					if ($param->isOptional()) {
						$argList .= '[, ';
						$optionals++;
					} else
						$argList .= ', ';
				}

				if ($param->isPassedByReference())
					$argList .= "&";
				$argList .= '$';
				$argList .= $param->getName();
			}
			$argList .= str_repeat("]", $optionals);
			$methods[$method->getName()] = "<span>".self::formatClassElementName($method, false)."($argList) [Callable]</span>";
		}

		return $methods;
	}

	private static function getClassProperties($clas, $static, $instance) {
		$properties = Array();
		foreach ($clas->getProperties() as $property) {
			if ($property->isStatic() != $static)
				continue;

			$property->setAccessible(true);
			$properties[self::formatClassElementName($property, true)] = $property->getValue($instance);
		}

		return $properties;
	}

}
?>
