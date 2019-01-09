<?php

namespace Mindgrub\MTIP;

use \Sunra\PhpSimple\HtmlDomParser;
use \GuzzleHttp\Client;

define('MTIP_BASE_URI', 'https://careers2.umbc.edu/employers/');
define('MTIP_APPLICATION_FORM', 'mtip-emp-app.php');
define('MTIP_APPLICATION_RESPONSE', 'mtip_app_response.php');
define('MTIP_APPLICATION_SUCCESS_CHECK', 'Thank you for applying for MTIP funding.');

/**
 *
 *  An MTIP application.
 *
 *  Class used to retrieve fields for and submit an MTIP application.
 *
 */
class MTIPApplication {

  private $client;
  private $base_uri;
  private $form_uri;
  private $response_uri;
  private $fieldData = [];
  private $fieldsToSubmit = [];

  public function __construct($base = MTIP_BASE_URI, $form = MTIP_APPLICATION_FORM, $response = MTIP_APPLICATION_RESPONSE) {
    $this->base_uri = $base;
    $this->form_uri = $form;
    $this->response_uri = $response;

    $this->client = new Client([
      'base_uri' => $base,
    ]);

    $this->populateFieldData();
  }

  /**
   * Retrieve field data from the form endpoint.
   */
  public function populateFieldData() {
    $html = HtmlDomParser::file_get_html($this->base_uri . $this->form_uri);
    foreach ($html->find('form') as $form) {
      if ($form->action == $this->response_uri) {
        foreach ($form->find('input') as $input) {
          switch ($input->type) {
            case 'hidden':
              $this->fieldData[$input->name] = [
                'type' => $input->type,
                'value' => $input->value,
              ];

              // Make sure hidden fields are submitted.
              $this->fieldsToSubmit[$input->name] = $input->value;

              break;
            case 'text':
              $this->fieldData[$input->name] = [
                'type' => $input->type,
              ];
              break;
            case 'radio':
            case 'checkbox':
              if (!isset($this->fieldData[$input->name])) {
                $this->fieldData[$input->name] = [
                  'type' => $input->type,
                  'options' => [$input->value],
                ];
              }
              else {
                $this->fieldData[$input->name]['options'][] = $input->value;
              }
              break;
            default:
              break;
          }
        }
        foreach ($form->find('select') as $select) {
          $this->fieldData[$select->name] = [
            'type' => 'select',
            'options' => [],
          ];
          foreach ($select->find('option') as $option) {
            $this->fieldData[$select->name]['options'][] = $option->value ? $option->value : $option->text();
          }
        }
      }
    }
  }

  public function getFieldData() {
    return $this->fieldData;
  }

  /**
   * Set a value on the application.
   */
  public function setField($key, $value) {
    $this->fieldsToSubmit[$key] = $value;
  }

  /**
   * Submit the application.
   */
  public function submit() {
    // Check for required fields.
    $missing_fields = [];
    foreach (explode(', ', $this->fieldData['required']['value']) as $field_name) {
      if (!isset($this->fieldsToSubmit[$field_name]) || empty($this->fieldsToSubmit[$field_name])) {
        $missing_fields[] = $field_name;
      }
    }

    if (!empty($missing_fields)) {
      return [
        'status' => '0',
        'message' => 'The application was not submitted, the following fields are required: ' . implode(', ', $missing_fields),
        'response' => null,
      ];
    }

    $response = $this->client->request('POST', $this->response_uri, [
      'form_params' => $this->fieldsToSubmit,
    ]);

    if ($response->getStatusCode() != '200') {
      return [
        'status' => '0',
        'message' => 'The application was unable to be submitted.  Examine the response for more information.',
        'response' => $response,
      ];
    }

    if (strpos(MTIP_APPLICATION_SUCCESS_CHECK, $response->getBody() == false)) {
      return [
        'status' => '0',
        'message' => 'The application was submitted, but the MTIP response did not return a success.  Examine the response for more information.',
        'response' => $response,
      ];
    }

    return [
      'status' => '1',
      'message' => 'The application was submitted successfully and the MTIP endpoint returned a successful response.',
      'response' => $response,
    ];
  }
}
