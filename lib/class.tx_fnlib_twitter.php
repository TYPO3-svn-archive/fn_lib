<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Frank Nägler <typo3@naegler.net>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Class 'tx_fnlib_twitter' for the 'fn_lib' extension.
 *
 * @author	Frank Nägler <typo3@naegler.net>
 * @package	TYPO3
 * @subpackage	fn_lib
 */
class tx_fnlib_twitter {
	var $host = 'http://twitter.com';
	var $actions = array(
		'update' => '/statuses/update',
		'timeline' => array(
			'public' => '/statuses/public_timeline',
			'friends' => '/statuses/friends_timeline',
			'user' => '/statuses/user_timeline'
		),
		'getStatus' => '/statuses/show/id',
		'replies' => '/statuses/replies',
		'destroy' => '/statuses/destroy',
		'friends' => '/statuses/friends',
		'followers' => '/statuses/followers',
		'featured' => '/statuses/featured',
		'userinfo' => '/users/show',
		'direct_messages' => '/direct_messages',
		'direct_messages_sent' => '/direct_messages/sent',
		'sent_direct_message' => '/direct_messages/new',
		'destroy_direct_message' => '/direct_messages/destroy',
		'create_friend' => '/friendships/create',
		'destroy_friend' => '/friendships/destroy',
		'verify_credentials' => '/account/verify_credentials',
		'end_session' => '/account/end_session',
		'archive' => '/account/archive',
		'favorites' => '/favourings',
		'create_favorites' => '/favourings/create',
		'destroy_favorites' => '/favourings/destroy',
		'notifications' => array(
			'follow' => '/notifications/follow',
			'leave' => '/notifications/leave',
		),
	);

	var $status = array(
		'200' => 'OK: everything went awesome.',
		'302' => 'Not Modified: there was no new data to return.',
		'400' => 'Bad Request:',
		'401' => 'Not Authorized:',
		'403' => 'Forbidden:',
		'404' => 'Not Found: ',
		'500' => 'Internal Server Error:',
		'502' => 'Bad Gateway: returned if Twitter is down or being upgraded.',
		'503' => 'Service Unavailable: the Twitter servers are up, but are overloaded with requests.  Try again later.'
	);

	function tx_fnlib_twitter($username, $password) {
		$this->username = $username;
		$this->password = $password;
		$this->init();
	}

	function init() {
		$this->transporter = curl_init();
		curl_setopt($this->transporter, CURLOPT_HEADER, true);
		curl_setopt($this->transporter, CURLOPT_USERPWD, $this->username . ":" . $this->password);
		curl_setopt($this->transporter, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($this->transporter, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->transporter, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($this->transporter, CURLOPT_HEADERFUNCTION, array($this, '_setHeader'));
	}

	function _setHeader($ch, $header) {
		$this->headers[] = $header;
        return strlen($header);
	}

	function makeRequest($format) {
		$this->headers = array();
		$status = curl_exec($this->transporter);
		//debug($status);
		return ($status) ? ($status) : ($this->checkFailure($format));
	}

	function checkFailure($format) {
		print_r($this->headers);
	}

	/**
	 * get tiny url from tinyurl.com API
	 *
	 * @param string $url
	 * @return string
	 */
	function getTinyUrl($url) {
		$url = 'http://tinyurl.com/api-create.php?url=' . $url;
		$cu = curl_init();
		curl_setopt($cu, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($cu, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($cu, CURLOPT_URL, $url);
		return curl_exec($cu);
	}
	
	// Status Methods
	function update($msg, $format = 'xml') {
		curl_setopt($this->transporter, CURLOPT_URL, $this->host . $this->actions['update'] . ".$format");
		$msg = str_replace('  ', ' ', $msg);
		$params = array(
			'status' => $msg
		);
		curl_setopt($this->transporter, CURLOPT_POSTFIELDS, $params);
		return $this->makeRequest($format);
	}

	function destroy($id, $format = 'xml') {
		$action = $this->host . $this->actions['destroy'] . '/' . intval($id) . ".$format";
		curl_setopt($this->transporter, CURLOPT_URL, $action);
		return $this->makeRequest($format);
	}

	function getTimeline($which = 'public', $format = 'xml', $params = null) {
		$action = $this->host . $this->actions['timeline'][$which] . ".$format";
		curl_setopt($this->transporter, CURLOPT_URL, $action);
		if ($params != null)
			curl_setopt($this->transporter, CURLOPT_POSTFIELDS, $params);
		return $this->makeRequest($format);
	}

	function getStatus($id, $format = 'xml') {
		$action = $this->host . $this->actions['getStatus'] . '/' . intval($id) . ".$format";
		curl_setopt($this->transporter, CURLOPT_URL, $action);
		return $this->makeRequest($format);
	}

	function getReplies($format = 'xml', $params = null) {
		$action = $this->host . $this->actions['replies'] . ".$format";
		curl_setopt($this->transporter, CURLOPT_URL, $action);
		if ($params != null)
			curl_setopt($this->transporter, CURLOPT_POSTFIELDS, $params);
		return $this->makeRequest($format);
	}

	// User Methods
	function getFriends($format = 'xml', $params = null) {
		$action = $this->host . $this->actions['friends'] . ".$format";
		curl_setopt($this->transporter, CURLOPT_URL, $action);
		if ($params != null)
			curl_setopt($this->transporter, CURLOPT_POSTFIELDS, $params);
		return $this->makeRequest($format);
	}

	function getFollowers($format = 'xml', $lite = false) {
		$action = $this->host . $this->actions['followers'] . ".$format";
		curl_setopt($this->transporter, CURLOPT_URL, $action);
		if ($lite)
			curl_setopt($this->transporter, CURLOPT_POSTFIELDS, array('lite' => 'true'));
		return $this->makeRequest($format);
	}

	function getFeatured($format = 'xml') {
		$action = $this->host . $this->actions['featured'] . ".$format";
		curl_setopt($this->transporter, CURLOPT_URL, $action);
		return $this->makeRequest($format);
	}

	function getUserinfoById($id, $format = 'xml') {
		$action = $this->host . $this->actions['userinfo'] . '/' . $id . ".$format";
		curl_setopt($this->transporter, CURLOPT_URL, $action);
		return $this->makeRequest($format);
	}

	function getUserinfoByEmail($email, $format = 'xml') {
		$action = $this->host . $this->actions['userinfo'] . ".$format";
		curl_setopt($this->transporter, CURLOPT_URL, $action);
		curl_setopt($this->transporter, CURLOPT_POSTFIELDS, array('email' => $email));
		return $this->makeRequest($format);
	}

	// Direct Message Methods
	function getDirectMessages($format = 'xml', $params = null) {
		$action = $this->host . $this->actions['direct_messages'] . ".$format";
		curl_setopt($this->transporter, CURLOPT_URL, $action);
		if ($params != null)
			curl_setopt($this->transporter, CURLOPT_POSTFIELDS, $params);
		return $this->makeRequest($format);
	}

	function getSentDirectMessages($format = 'xml', $params = null) {
		$action = $this->host . $this->actions['direct_messages_sent'] . ".$format";
		curl_setopt($this->transporter, CURLOPT_URL, $action);
		if ($params != null)
			curl_setopt($this->transporter, CURLOPT_POSTFIELDS, $params);
		return $this->makeRequest($format);
	}

	function sentDirectMessage($user, $msg, $format = 'xml') {
		$action = $this->host . $this->actions['sent_direct_message'] . ".$format";
		curl_setopt($this->transporter, CURLOPT_URL, $action);
		$params = array(
			'user' => $user,
			'text' => $msg
		);
		curl_setopt($this->transporter, CURLOPT_POSTFIELDS, $params);
		return $this->makeRequest($format);
	}

	function destroyDirectMessage($id, $format = 'xml') {
		$action = $this->host . $this->actions['destroy_direct_message'] . '/' . $id . ".$format";
		curl_setopt($this->transporter, CURLOPT_URL, $action);
		return $this->makeRequest($format);
	}
	
	// Friendship Methods
	function createFriend($id, $format = 'xml') {
		$action = $this->host . $this->actions['create_friend'] . '/' . $id . ".$format";
		curl_setopt($this->transporter, CURLOPT_URL, $action);
		return $this->makeRequest($format);
	}

	function destroyFriend($id, $format = 'xml') {
		$action = $this->host . $this->actions['destroy_friend'] . '/' . $id . ".$format";
		curl_setopt($this->transporter, CURLOPT_URL, $action);
		return $this->makeRequest($format);
	}
	
	// Account Methods
	function verify_credentials($format = null) {
		$action = $this->host . $this->actions['verify_credentials'];
		$action .= ($format != null) ? (".$format") : ('');
		curl_setopt($this->transporter, CURLOPT_URL, $action);
		return $this->makeRequest($format);
	}

	function end_session() {
		$action = $this->host . $this->actions['end_session'];
		curl_setopt($this->transporter, CURLOPT_URL, $action);
		return $this->makeRequest(null);
	}

	function getArchive($format = 'xml', $page = null) {
		$action = $this->host . $this->actions['archive'] . ".$format";
		curl_setopt($this->transporter, CURLOPT_URL, $action);
		if ($page != null) {
			$params = array(
				'page' => intval($page)
			);
			curl_setopt($this->transporter, CURLOPT_POSTFIELDS, $params);
		}		
		return $this->makeRequest($format);
	}
	
	// Favorite Methods
	function getFavorites($format = 'xml', $params = null) {
		$action = $this->host . $this->actions['favorites'] . ".$format";
		curl_setopt($this->transporter, CURLOPT_URL, $action);
		if ($params != null)
			curl_setopt($this->transporter, CURLOPT_POSTFIELDS, $params);
		return $this->makeRequest($format);
	}

	function createFavorite($id, $format = 'xml') {
		$action = $this->host . $this->actions['create_favorites'] . '/' . intval($id). ".$format";
		curl_setopt($this->transporter, CURLOPT_URL, $action);
		return $this->makeRequest($format);
	}

	function destroyFavorite($id, $format = 'xml') {
		$action = $this->host . $this->actions['destroy_favorites'] . '/' . intval($id). ".$format";
		curl_setopt($this->transporter, CURLOPT_URL, $action);
		return $this->makeRequest($format);
	}

	// Notification Methods
	function enableNotification($id, $format = 'xml') {
		$action = $this->host . $this->actions['notications']['follow'] . '/' . $id. ".$format";
		curl_setopt($this->transporter, CURLOPT_URL, $action);
		return $this->makeRequest($format);
	}

	function disableNotification($id, $format = 'xml') {
		$action = $this->host . $this->actions['notications']['leave'] . '/' . $id. ".$format";
		curl_setopt($this->transporter, CURLOPT_URL, $action);
		return $this->makeRequest($format);
	}

}

?>
