<?php


require_once dirname(__FILE__) . '/../StreamsHttpPOST.php';

/**
 * Test class for FormData.
 * Generated by PHPUnit on 2011-08-07 at 06:49:17.
 */
class FormDataTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var FormData
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		$this->object = new FormData();
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown() {

	}


	/**
	 * @todo Implement testAddData().
	 */
	public function testAddData() {
		$this->object->addData('foo', 'bar');
		$data = $this->object->getData();
		$expected = array('foo' => 'bar');
		$this->assertSame($data, $expected);
	}

	public function testGetData() {
		$data = array('beans' => 'toast');
		$this->object = new FormData($data);
		$this->assertSame($data, $this->object->getData());
	}

	/**
	 * @todo Implement testCreateStreamContext().
	 */
	public function testCreateStreamContext() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

		/**
	 * @todo Implement testGetContentString().
	 */
	public function testGetContentString() {
		$this->object->addData('foo', 'bar');
		$data = $this->object->getData();
		$contentString  = $this->object->getContentString();
		$expected = 'foo=bar';
		$this->assertSame($contentString, $expected);
	}

	/**
	 * @todo Implement testGetHeader().
	 */
	public function testGetHeader() {
		$expected = 'Content-type: application/x-www-form-urlencoded';
		$this->assertSame($expected, $this->object->getHeader());
	}


}

?>
