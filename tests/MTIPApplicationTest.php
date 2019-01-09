<?php

/**
 *  Unit tests for MTIPApplication.
 */
class MTIPApplicationTest extends PHPUnit_Framework_TestCase {

  /**
   * Test retrieval of fields from the MTIP application form.
   */
  public function testFields() {
    $application = new \Mindgrub\MTIP\MTIPApplication();
    $this->assertNotEmpty($application->getFieldData());
  }

  /**
   * Test an empty submission.
   */
  public function testEmptySubmission() {
    $application = new \Mindgrub\MTIP\MTIPApplication();
    $result = $application->submit();

    $this->assertEquals(0, $result['status']);
  }

  /**
   * Test valid submission.
   */
  public function testValidSubmission() {
    $application = new \Mindgrub\MTIP\MTIPApplication();
    $required_fields = explode(', ', $application->getFieldData()['required']['value']);

    foreach($required_fields as $field_name) {
      $application->setField($field_name, 'test');
    }

    $result = $application->submit();

    $this->assertEquals(1, $result['status']);
  }
}
