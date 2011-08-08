<?php

/**
 *  Class to represent an HTTP POST request without the need for the cURL
 * nat@fishtrap.co.uk
 */
class StreamsHttpPOST {

	private $_url = "";

	private $_form;

	public function __construct($url, $formDataObject = null,  $dataArray = array()) {
		$this->_url = $url;
		if ($formDataObject instanceof FormData) {
			$this->_form = $formDataObject;
		} else {
			$this->_form = new FormData($dataArray);
		}
	}

	public function getResponseCode() {
		foreach ($this->_responseHeaders as $header) {
			if (preg_match('/HTTP\/(\d\.\d) ([\d]+) ([\w]+)/', $header, $matches)) {
				return (int) $matches[2];
			}
		}
	}

	public function addData(Array $data) {
		foreach ($data as $name => $value) {
			$this->_form->addData($name, $value);
		}
	}


	public function post() {
		$content = file_get_contents($this->_url, false, $this->_form->createStreamContext());
		$this->_responseHeaders = $http_response_header;
		return $content;
	}
}

class FormData
{
	private $_data = array();

	public function  __construct($data = array()) {
		foreach ($data as $name => $value) {
			$this->addData($name, $value);
		}
	}

	public function addData($name, $value) {
		$this->_data[$name] = $value;
	}

	public function getData() {
		return $this->_data;
	}

	public function createStreamContext() {
		$opts = array('http' =>
			array(
				'method' => 'POST',
				'header' => $this->getHeader(),
				'timeout' => 30, # response timeout
				'content' => $this->getContentString(),
			)
		);

		return stream_context_create($opts);
	}

	public  function getHeader() {
			return 'Content-type: application/x-www-form-urlencoded';
	}

	public function getContentString() {
		return http_build_query($this->getData());
	}
}


class MultipartFormData extends FormData
{

	private $_files = array();
	private $_multipartBoundary = "";

	public function  __construct($data = array()) {
		define('CRLF', PHP_EOL);
		parent::__construct($data);
		$this->_multipartBoundary = md5(microtime(true));
	}

	public function addFile($fieldName, $file){
		$this->_files[$fieldName] = $file;
	}

	public  function getHeader() {
		return "Content-Type: multipart/form-data; boundary={$this->_multipartBoundary}";
	}

	public function  getContentString() {
		$content = "";
		foreach ($this->_files as $fieldName => $filename) {
			$filename = realpath($filename);
			$file_contents = file_get_contents($filename);

			$content .= "--{$this->_multipartBoundary}".CRLF.
			'Content-Disposition: form-data; name="'.$fieldName.'"; filename="'.basename($filename).'"'.CRLF.
			"Content-Type: application/zip".CRLF.
			$file_contents.CRLF;

		}

		$content .= "--{$this->_multipartBoundary}".CRLF.
		'Content-Disposition: form-data; ';
	    foreach ($this->getData() as $name => $value) {
			$content .= 'name="'.$name.'"'.CRLF.
//			"Content-type: text/plain;charset=windows-1250".CRLF.
			$value.CRLF;
		}

		// signal end of request (note the trailing "--")
		$content .= "--{$this->_multipartBoundary}--".CRLF;
		return $content;
	}
}