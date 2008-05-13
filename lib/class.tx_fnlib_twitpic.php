<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Frank Nägler <typo3@naegler.net>
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
 * Class 'tx_fnlib_twitpic' for the 'fn_lib' extension.
 *
 * @author	Frank Nägler <typo3@naegler.net>
 * @package	TYPO3
 * @subpackage	fn_lib
 */
class tx_fnlib_twitpic {
	var $host = 'http://twitpic.com';
	var $actions = array(
		'uploadAndPost' => '/api/uploadAndPost',
		'upload'		=> '/api/upload',
	);

	var $status = array(
		'1001' => 'Invalid twitter username or password',
		'1002' => 'Image not found',
		'1003' => 'Invalid image type',
		'1004' => 'Image larger than 4MB ',
	);

	function tx_fnlib_twitpic($username, $password) {
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
		return ($status) ? ($status) : ($this->checkFailure());
	}

	function checkFailure() {
		print_r($this->headers);
	}

	function uploadAndPost($media, $msg = '') {
		curl_setopt($this->transporter, CURLOPT_URL, $this->host . $this->actions['update']);
		$msg = str_replace('  ', ' ', $msg);
		$params = array(
			'media' => $media
		);
		curl_setopt($this->transporter, CURLOPT_POSTFIELDS, $params);
		return $this->makeRequest();
	}

	function upload($media) {
		return $this->uploadAndPost($media);
	}
}

?>
