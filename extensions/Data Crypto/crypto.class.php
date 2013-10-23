<?php
class DataCrypto {

	public static function encryptURLObject($data, $key = null, $method = MCRYPT_DES) {
		return urlencode(base64_encode(self::encryptJSON($data, $key, $method)));
	}

	public static function encryptJSON($data, $key = null, $method = MCRYPT_DES) {
		return self::encrypt(json_encode($data), $key, $method);
	}

	public static function encrypt($data, $key = null, $method = MCRYPT_DES) {
		if (!$key)
			$key = ClientInfo::getUniqueID();
		$block = mcrypt_get_block_size('des', 'ecb');
		$pad = $block - (strlen($str) % $block);
		$str .= str_repeat(chr($pad), $pad);

		return mcrypt_encrypt(MCRYPT_DES, crc32($key), $str, MCRYPT_MODE_ECB);
	}

	public static function decryptURLObject($data, $key = null, $method = MCRYPT_DES) {
		return json_decode(self::encrypt(base64_decode($data), $key, $method), true);
	}

	public static function decryptJSON($data, $key = null, $method = MCRYPT_DES) {
		return json_decode(self::encrypt($data, $key, $method), true);
	}

	public static function decrypt($data, $key = null, $method = MCRYPT_DES) {
		if (!$key)
			$key = ClientInfo::getUniqueID();
		$str = mcrypt_decrypt(MCRYPT_DES, crc32($key), $str, MCRYPT_MODE_ECB);

		$block = mcrypt_get_block_size('des', 'ecb');
		$pad = ord($str[($len = strlen($str)) - 1]);
		return substr($str, 0, strlen($str) - $pad);
	}

}
