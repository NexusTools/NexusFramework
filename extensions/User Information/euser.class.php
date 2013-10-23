<?php
class ExtendedUser {

	private static $database;
	private $username;
	private $rowid;
	private $salu;
	private $first;
	private $last;
	private $address;
	private $address2;
	private $country;
	private $city;
	private $province;
	private $postal;
	private $phone1;
	private $phone2;
	private $phone3;
	private $phone4;
	private $phone5;
	private $fax;
	private $email1;
	private $email2;
	private $email3;
	private $website;
	private $occupation;
	private $birth;
	private $live;
	private $skype;
	private $gtalk;
	private $aim;

	public function getFullName($salu = false) {
		if ($salu && $this->salu)
			return $this->salu.". ".$this->getFullName(false);
		else
			if ($this->first && $this->last)
				return ucfirst($this->first)." ".ucfirst($this->last);
			else
				return ucfirst($this->username);
	}

	public function getSalutation() {
	}

	public function getFirstName() {
		return $this->first;
	}

	public function getLastName() {
		return $this->last;
	}

	public function getAddress1() {
		return $this->address;
	}

	public function getAddress2() {
		return $this->address2;
	}

	public function getCity() {
		return $this->city;
	}

	public function getCountry() {
		return $this->country;
	}

	public function getProvince() {
		return $this->province;
	}

	public function getState() {
		return $this->province;
	}

	public function getPostalCode() {
		return $this->postal;
	}

	public function getZipCode() {
		return $this->postal;
	}

	public function getPhoneNumber() {
		return $this->phone1;
	}

	public function getPhoneNumber2() {
		return $this->phone2;
	}

	public function getPhoneNumber3() {
		return $this->phone3;
	}

	public function getPhoneNumber4() {
		return $this->phone4;
	}

	public function getPhoneNumber5() {
		return $this->phone5;
	}

	public function getFax() {
		return $this->fax;
	}

	public function getEmail1() {
		return $this->email1;
	}

	public function getEmail2() {
		return $this->email2;
	}

	public function getEmail3() {
		return $this->email3;
	}

	public function getWebsite() {
		return $this->website;
	}

	public function getOccupation() {
		return $this->occupation;
	}

	public function hasWebsite() {
		return strlen($this->website);
	}

	public function getBirthDate() {
		return $this->birth;
	}

	public function getWindowsLiveID() {
		return $this->live;
	}

	public function getSkypeID() {
		return $this->skype;
	}

	public function getGTalkID() {
		return $this->gtalk;
	}

	public function getAimID() {
		return $this->aim;
	}

	public static function init() {
		self::$database = Database::getInstance();
	}

	public static function getDatabase() {
		return self::$database;
	}

	public function updateExtendedInfo($data) {
		return self::$database->update("account", $data, Array("rowid" => $this->rowid));
	}

	public function __construct($user) {
		$data = self::$database->selectRow("account", Array("rowid" => $user->getID()));
		$this->rowid = $user->getID();
		$this->username = $user->getUsername();

		if (!$data)
			self::$database->insert("account", Array("rowid" => $user->getID(), "first" => $user->getUsername()));
		else {
			$this->salu = ucfirst($data['salu']);
			$this->first = ucfirst($data['first']);
			$this->last = ucfirst($data['last']);
			$this->address = StringFormat::properCase($data['address']);
			$this->address2 = StringFormat::properCase($data['address2']);
			$this->city = StringFormat::properCase($data['city']);
			$this->country = strlen($data['country']) > 3 ? StringFormat::properCase($data['country']) : strtoupper($data['country']);
			$this->province = strlen($data['province']) > 3 ? StringFormat::properCase($data['province']) : strtoupper($data['province']);
			$this->postal = strtoupper($data['postal']);
			/*$this->phone1 = StringFormat::expandPhoneNumber($data['phone1']);
			 $this->phone2 = StringFormat::expandPhoneNumber($data['phone2']);
			 $this->phone3 = StringFormat::expandPhoneNumber($data['phone3']);
			 $this->phone4 = StringFormat::expandPhoneNumber($data['phone4']);
			 $this->phone5 = StringFormat::expandPhoneNumber($data['phone5']);
			 $this->fax = StringFormat::expandPhoneNumber($data['fax']);*/
			$this->phone1 = $data['phone1'];
			$this->phone2 = $data['phone2'];
			$this->phone3 = $data['phone3'];
			$this->phone4 = $data['phone4'];
			$this->phone5 = $data['phone5'];
			$this->fax = $data['fax'];
			$this->email1 = $data['email1'];
			$this->email2 = $data['email2'];
			$this->email3 = $data['email3'];
			$this->website = $data['website'];
			$this->occupation = $data['occupation'];
			$this->birth = $data['birth'];
			$this->live = $data['live'];
			$this->skype = $data['skype'];
			$this->gtalk = $data['gtalk'];
			$this->aim = $data['aim'];
		}
	}

	/*public static function suggestions($query){
	 $results = User::_suggestions($query);
	 $more = 10 - $results['total'];
	 $results = $results['results'];
			
	 $found = Array();
	 foreach($results as &$result){
	 $user = User::fetch($result['rowid']);
	 $result['display'] = $user->getDisplayName(true);
	 $result['country'] = $user->getCountry();
	 $result['city'] = $user->getCity();
	 $result['province'] = $user->getProvince();
	 }
			
	 if($more) {
	 $moreresults = self::$database->queryRows("account", Array("LIKE first" => "%$query%", "OR", "LIKE first" => "%$query%"), 0, 10, false, Array("first", "last"));
	 foreach($results as &$result){
				
	 }
	 }
			
	 return Array("total" => count($results), "results" => $results);
	 }*/

}
ExtendedUser::init();
?>
