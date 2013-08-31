<?php
interface Locker {

	public function lock($exclusive);
	public function tryLock($exclusive);
	public function relock($exclusive);
	public function isLocked();
	public function unlock();

}
?>
