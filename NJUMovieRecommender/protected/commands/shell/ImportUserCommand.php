<?php
	/* This shell is used to import user to the database */
	class ImportUserCommand extends CConsoleCommand
	{
		private $_age = array('children' => 1, 'teenagers' => 2, 'adults'=> 3, 'seniors'=> 4);
		private $_location = array(  // map zip code range to the state
			'Alaska' => array(99501, 99950),
			'Alabama'=> array(35004, 36925),
			'Arizona' => array(85001, 86556), 
			'Arkansas' => array(71601, 72959),
			'California' => array(90001, 96162),
			'Colorado' => array(80001, 81658),
			'Connecticut' => array(6001, 6928),
			'Delaware' => array(19701, 19980),
			'District of Columbia' => array(20001, 20599),
			'Florida' => array(32004, 34997),
			'Georgia' => array(30002, 31999),
			'Hawaii' => array(96701, 96898),
			'Idaho' => array(83201, 83888),
			'Illinois' => array(60001, 62999),
			'Indiana' => array(46001, 47997),
			'Iowa' => array(50001, 52809),
			'Kansas' => array(66002, 67954),
			'Kentucky' => array(40003, 42788),
			'Louisiana' => array(70001, 71497),
			'Maine' => array(3901, 4069),
			'Maryland' => array(20601, 21930),
			'Massachusetts' => array(1001, 2791),
			'Michigan' => array(48001, 49971),
			'Minnesota' => array(55001, 56763),
			'Mississippi' => array(38601, 39776),
			'Missouri' => array(63001, 65899),
			'Montana' => array(59001, 59937),
			'Nebraska' => array(68001, 69367),
			'Nevada' => array(88901, 89883),
			'New Hampshire' => array(3031, 3897),
			'New Jersey' => array(7001, 8989),
			'New Mexico' => array(87001, 88441),
			'New York' => array(10001, 14925),
			'North Carolina' => array(27001, 28909),
			'North Dakota' => array(58001, 58856),
			'Ohio' => array(43001, 45999),
			'Oklahoma' => array(73001, 74966),
			'Oregon' => array(97001, 97920),
			'Pennsylvania' => array(15001, 19640),
			'Rhode Island' => array(2801, 2940),
			'South Carolina' => array(29001, 29945),
			'South Dakota' => array(57001, 57799),
			'Tennessee' => array(37010, 38589),
			'Texas' => array(73301, 79999),
			'Texas' => array(88510, 88595),
			'Utah' => array(84001, 84791),
			'Vermont' => array(5001, 5907),
			'Virginia' => array(20101, 24658),
			'Washington' => array(98001, 99403),
			'West Virginia' => array(24701, 26886),
			'Wisconsin' => array(53001, 54990),
			'Wyoming' => array(82001, 83128),
		);
		
		public function getHelp()
		{
			return <<< EOD
USAGE
	importuser
DESCRIPTION
	This command imports user in the file to the database.
EOD;
		}
		
		/**
		 * Execute the action
		 * @param array command line parameters specific for this command
		 * args[0] is the file name that contains the user
		 */
		public function run($args)
		{
			echo "Running...\n";
			$file = fopen($args[0], "r");
			if($file == 0) exit("Unable to open file!");
			while(!feof($file))
			{
				$line = fgets($file);
				$line = str_replace("\n", "", $line);
				$line = str_replace("\r", "", $line);
				$info = explode("|", $line);
				$user = new User();
				//remove the dirty data(zip code is not numeric)
				if(is_numeric($info[4]))
				{
					$user->id = (int)$info[0];
					$user->age = (int)$info[1];
					$user->gender = $info[2];
					$user->occupation = $info[3];
					$user->zipCode = $info[4];
					$user->email = "example@example.com";
					$user->password = MD5("recsys-nju");
					$user->nickname = "NJUer";
					$user->ageCommunityId = $this->clusterAge($user->age);
					$user->locationCommunityId = $this->clusterLocation($user->zipCode);
					$user->occupationCommunityId = $this->clusterOccupation($user->occupation);
					if(!$user->save())
					{
						echo "user". $user->id. " fails to save\n";
					}
					else
					{
						echo "user". $user->id. " saved\n";
					}
				}
			}
			echo "Complete\n";
		}
			
		//return the ageCommnuityId
		private function clusterAge($age)
		{
			if($age < 12)
				return $this->_age['children'];
			else if($age >= 12 && age < 18)
				return $this->_age['teenagers'];
			else if($age >= 18 && age < 40)
				return $this->_age['adults'];
			else
				return $this->_age['seniors'];
		}
			
		//return the locationCommunityId
		private function clusterLocation($zipCode)
		{
			$zipCode = (int)$zipCode;
			foreach($this->_location as $state => $range)
			{
				if($zipCode >= $range[0] && $zipCode <= $range[1])
				{	
					$community = LocationCommunity::model()->findByAttributes(array('name' => $state));
					Assert('$community != null');
					return $community->id;
				}
			}
			echo "zipCode: ". $zipCode . " doesn't belong to a state";
			return 1;
		}
			
		//return the occupationCommunityId
		private function clusterOccupation($occupation)
		{
			$community =  OccupationCommunity::model()->findByAttributes(array('name' => $occupation));
			Assert('$community != null');
			return $community->id;
		}
	}
?>