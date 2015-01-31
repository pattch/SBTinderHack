<?php
	//$auth = 'Sniff out your auth token from the headers';
	$auth = "e705e8ea-9d8c-4f11-b9ff-fcfb4d26d6d8";
	//$auth = "AA";
	main($auth);
	
	
	// Get a new batch of users to choose from
	function getUsers($auth) {
		$usersResponse = SendRequest('user/recs', $auth, false, false);
		$array = json_decode($usersResponse, true);
		$users = $array['results'];
		return $users;
	}

	/*
	This will return an array with 11 elements in it.
	In each element, will be array keys with info about the user.
	Description, pics, first and last name etc...
	Get the user ID for each. It should be $users[$i]['_id'];
	*/

	// Like a user
	function likeUser($auth, $user_id) {
		$like = SendRequest('like/'.$user_id, $auth, false, false);
		return $like;
	}
	
	// Pass on a user
	function passUser($auth, $user_id) {
		$pass = SendRequest('pass/'.$user_id, $auth, false, false);
		return $pass;
	}

	/*
	If you like a user that has already liked you, there becomes a mutual liking
	Therefore, the array returned should contain a key called 'match_id'
	Otherwise, it should just return a boolean
	*/
	
	// Look up a user's info
	function getInfo($auth, $user_id) {
		$info = SendRequest('like/'.$user_id, $auth, false, false);
		$real_info = $info['results'];
		return $real_info;
	}

	// Get updated info. This includes new messages and matches
	function getUpdatedInfo($auth) {
		//$update_time = "2014-".date('m')."-".date('d')."T".$real.":".date('s').".906Z";
		$update = SendRequest("updates", $auth, true, false);
		return $update;
	}

	// Send a message. Must be the user ID of the user you want to receive the message/
	function sendMessage($auth, $user_id, $message) {
		$msg = SendRequest('user/matches/'.$user_id, $auth, true, array('message' => $message));
	}

	/*
	Will return containing a boolean if sent or not. 
	Will also contain a timestamp of the time the message was sent
	*/

	
	function SendRequest($url, $auth, $post, $payload) { 
		try {
			$headers = array('Authentication: Token token="'.$auth.'"',
							'X-Auth-Token: '.$auth);
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'https://api.gotinder.com/'.$url);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Tinder/4.0.3 (iPhone; iOS 7.1.1; Scale/2.00)');
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($ch, CURLOPT_CAINFO, "C:\xampp\php\cacert.pem");
			
			if($post) {
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
			}
			$content = curl_exec($ch);
			if(false == $content)
				throw new Exception(curl_error($ch),curl_errno($ch));
			else
				return $content;
		} catch(Exception $e) {
			trigger_error(sprintf(
			'Curl failed with error #%d: %s',
			$e->getCode(), $e->getMessage()),
			E_USER_ERROR);
		}
	}
	
	function main($auth) {
		$updateResult = getUpdatedInfo($auth);
		parseUpdateResult($updateResult);
	}
	
	function parseUpdateResult($result) {
		$nice_Result = print_r($result, true);
		
		$xml = new SimpleXMLElement('<xml />');
		$arrayResult = json_decode($result, true);
		$matches = $arrayResult['matches'];
		foreach($matches as $match) {
			$messages = $match['messages'];
			if(count($messages) > 0) {
				$conv = $xml->addChild("Conversation" );
				$conv->addAttribute('id',$match['_id']);
				foreach($messages as $message) {
					$conv->addChild("content", $message['message']);
					print_r($message['message']);
					echo '<br />';
				}
			}
		}
		
		//print_r($arrayResult['matches']);
		//print_r($arrayResult);
		
		file_put_contents("data.txt", $xml->asXML(), FILE_APPEND);
		return $nice_Result;
	}
?>
