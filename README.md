PHP MTIP Application
=========================

This library allows field data and submission of the MTIP application, found originally at https://careers2.umbc.edu/employers/mtip_app_response.php

Usage
--------
```php
// Create a new application.
$application = \Mindgrub\MTIP\MTIPApplication();

// Examine fields on the application.
$available_fields = $application->getFieldData();

// Set fields on the application.
$application->setField('first_name', $my_first_name);

// Submit the application.
$application->submit();
```

Submission Response
--------
`$application->submit()` returns an array with 3 keys:
* `status`, either `0` or `1` depending on success.
* `message`, describing the result of the submission.
* `response`, a Guzzle Response Object received after making the submission, where applicable.