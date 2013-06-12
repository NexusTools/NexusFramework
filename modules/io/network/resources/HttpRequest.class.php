<?php
class HttpRequest extends AbstractResourceRequest {
=
	public abstract function open($path);

	public abstract function headers();

	public abstract function write($data);
	public abstract function read();

}
?>
