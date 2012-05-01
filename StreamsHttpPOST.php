<?php

/**
 *  Class to represent an HTTP POST request without the need for the cURL
 * nat@fishtrap.co.uk
 */
class StreamsHttpPOST {

	private $_url = "";

	private $_form;

	public function __construct($url, $formData = array()) {
		$this->_url = $url;
		if ($formData instanceof FormData) {
			$this->_form = $formData;
		} else {
			$this->_form = new FormData($formData);
		}
	}

	public function addFile($filename, $file) {
		if (!($this->_form instanceof  MultipartFormData)) {
		 $this->_form = new MultipartFormData($this->_form->getData());
		}
		$this->_form->addFile($filename, $file);
	}

	public function getResponseCode() {
		foreach ($this->_responseHeaders as $header) {
			if (preg_match('/HTTP\/(\d\.\d) ([\d]+) ([\w]+)/', $header, $matches)) {
				return (int) $matches[2];
			}
		}
	}

	public function addData(Array $data) {
			$this->_form->mergeData($data);
	}


	public function post($data = array()) {
		if (!empty($data)) {
			$this->addData($data);
		}
		$content = file_get_contents($this->_url, false, $this->_form->createStreamContext());
		$this->_responseHeaders = $http_response_header;
		return $content;
	}
}

class FormData extends ArrayObject
{

	public function addData($name, $value) {
		$this[$name] = $value;
	}

	public function mergeData($newData) {
		$oldData = $this->getData();
		$data = array_merge($oldData, $newData);
		$this->exchangeArray($data);
	}

	public function getData() {
		return $this->getArrayCopy();
	}

	public function createStreamContext() {
		$opts = array('http' =>
			array(
				'method' => 'POST',
				'header' => $this->getHeader(),
				'timeout' => 30, // response timeout
				'content' => $this->getContentString(),
			)
		);

		return stream_context_create($opts);
	}

	public  function getHeader() {
			return 'Content-type: application/x-www-form-urlencoded';
	}

	public function getContentString() {
		return http_build_query($this);
	}
}


class MultipartFormData extends FormData
{

	private $_files;
	private $_multipartBoundary = "";

	public function  __construct($data = array()) {
		parent::__construct($data);
		$this->_multipartBoundary = md5(microtime(true));
		$this->_files = new ArrayObject();
	}

	public function addFile($fieldName, $file){
		$this->_files[$fieldName] = new SplFileInfo($file);
	}

	public  function getHeader() {
		return "Content-Type: multipart/form-data; boundary={$this->_multipartBoundary}";
	}

	public function  getContentString() {
		$content = "";
		foreach ($this->_files as $fieldName => $file) {

			$content .= "--{$this->_multipartBoundary}".PHP_EOL.
			'Content-Disposition: form-data; name="'.$fieldName.'"; filename="'.basename($file->getBasename()).'"'.PHP_EOL.
			"Content-Type: ".mime_content_type((string) $file).PHP_EOL.PHP_EOL.
			file_get_contents((string) $file).PHP_EOL;

		}

		$content .= "--{$this->_multipartBoundary}".PHP_EOL.
		'Content-Disposition: form-data; ';
	    foreach ($this as $name => $value) {
			$content .= 'name="'.$name.'"'.PHP_EOL.PHP_EOL.$value;
		}
		// signal end of request (note the trailing "--")
		$content .= PHP_EOL."--{$this->_multipartBoundary}--".PHP_EOL;
		return $content;
	}
}