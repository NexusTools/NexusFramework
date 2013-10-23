<?php
if (PageModule::countArguments() == 2 && ClassLoader::isHashRegistered(PageModule::getArgument(1))) {
	$classFile = ClassLoader::classFileForHash(PageModule::getArgument(1));
	$infoPath = substr($classFile, 0, strlen($classFile) - 4).".json";
	if (!is_file($infoPath))
		continue;

	$classInfo = json_decode(file_get_contents($infoPath), true);
	if (!$classInfo || !array_key_exists("name", $classInfo))
		return false;

	if (!array_key_exists("long-description", $classInfo)) {
		if (array_key_exists("description", $classInfo))
			$classInfo['long-description'] = $classInfo['description'];
		else
			$classInfo['long-description'] = "Description missing...";
	}

	PageModule::setValue("ClassInfo", $classInfo);
	return true;
}
return false;
?>
