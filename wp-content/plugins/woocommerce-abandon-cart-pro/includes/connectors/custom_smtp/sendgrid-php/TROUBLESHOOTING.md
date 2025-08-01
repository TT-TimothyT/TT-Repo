If you have an issue logging into your Twilio SendGrid account, please read this [document](https://sendgrid.com/docs/ui/account-and-settings/troubleshooting-login/). For any questions regarding login issues, please contact our [support team](https://support.sendgrid.com).

If you have a non-library Twilio SendGrid issue, please contact our [support team](https://support.sendgrid.com).

If you can't find a solution below, please open an [issue](https://github.com/sendgrid/sendgrid-php/issues).

## Table of Contents

- [Table of Contents](#table-of-contents)
- [Migrating from v2 to v3](#migrating-from-v2-to-v3)
- [Continue Using v2](#continue-using-v2)
- [Testing v3 /mail/send Calls Directly](#testing-v3-mailsend-calls-directly)
- [Error Messages](#error-messages)
- [Versions](#versions)
- [Environment Variables and Your Twilio SendGrid API Key](#environment-variables-and-your-twilio-sendgrid-api-key)
- [Using the Package Manager](#using-the-package-manager)
- [Fixing Error 415](#fixing-error-415)
- [Viewing the Request Body](#viewing-the-request-body)
- [Google App Engine installation](#google-app-engine-installation)
- [Verifying Event Webhooks](#signed-webhooks)

<a name="migrating"></a>
## Migrating from v2 to v3

In this context, we are referring to the version of the Twilio SendGrid API.

Please review [our guide](https://sendgrid.com/docs/Classroom/Send/v3_Mail_Send/how_to_migrate_from_v2_to_v3_mail_send.html) on how to migrate from v2 to v3.

<a name="v2"></a>
## Continue Using v2

In this context, we are referring to the version of the Twilio SendGrid API.

[Here](https://github.com/sendgrid/sendgrid-php/releases/tag/v4.0.4) is the last working version with v2 support.

Using composer:

```json
{
  "require": {
    "sendgrid/sendgrid": "~4.0.4"
  }
}
```

Download packaged zip [here](https://sendgrid-open-source.s3.amazonaws.com/sendgrid-php/versions/sendgrid-php-75970eb.zip).

<a name="testing"></a>
## Testing v3 /mail/send Calls Directly

[Here](https://sendgrid.com/docs/for-developers/sending-email/curl-examples/) are some cURL examples for common use cases.

<a name="error"></a>
## Error Messages

Failed requests will always return an error response, including a response code, a message explaining the reason for the error, and a link to any relevant documentation that may help you troubleshoot the problem.

To read the error message returned by Twilio SendGrid's API:

```php
try {
    $response = $sendgrid->send($email);
    print $response->statusCode() . "\n";
    print_r($response->headers());
    print $response->body() . "\n"; // Twilio SendGrid specific errors are found here
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}
```

You may find complete documentation [here](https://sendgrid.com/docs/API_Reference/Web_API_v3/Mail/errors.html).

<a name="versions"></a>
## Versions

We follow the MAJOR.MINOR.PATCH versioning scheme as described by [SemVer.org](http://semver.org). Therefore, we recommend that you always pin (or vendor) the particular version you are working with to your code and never auto-update to the latest version. Especially when there is a MAJOR point release, since that is guaranteed to be a breaking change. Changes are documented in the [CHANGELOG](CHANGELOG.md) and [releases](https://github.com/sendgrid/sendgrid-php/releases) section.

<a name="environment"></a>
## Environment Variables and Your Twilio SendGrid API Key

All of our examples assume you are using [environment variables](https://github.com/sendgrid/sendgrid-php#setup-environment-variables) to hold your Twilio SendGrid API key.

If you choose to add your Twilio SendGrid API key directly (not recommended):

`$apiKey = getenv('SENDGRID_API_KEY');`

becomes

`$apiKey = 'SENDGRID_API_KEY';`

In the first case SENDGRID_API_KEY is in reference to the name of the environment variable, while the second case references the actual Twilio SendGrid API Key.

<a name="package-manager"></a>
## Using the Package Manager

We upload this library to [Packagist](https://packagist.org/packages/sendgrid/sendgrid) whenever we make a release. This allows you to use [composer](https://getcomposer.org) for easy installation.

In most cases we recommend you download the latest version of the library, but if you need a different version, please use:

```json
{
  "require": {
    "sendgrid/sendgrid": "~X.X.X"
  }
}
```

<a name="error-415"></a>
## Fixing Error 415

If you're getting the following error while using this library:

`Content-Type should be application/json.`

It is most likely due to a linebreak in your API key. Passing your key through `trim` should fix this:

`$apiKey = trim($apiKey)`

<a name="request-body"></a>
## Viewing the Request Body

When debugging or testing, it may be useful to examine the raw request body to compare against the [documented format](https://sendgrid.com/docs/API_Reference/api_v3.html).

You can do this right before you call `$response = $sg->send($email);` like so:

```php
echo json_encode($email, JSON_PRETTY_PRINT);
```

<a name="GAE-instructions"></a>
## Google App Engine installation

Please refer to [`USE_CASES.md`](USE_CASES.md#GAE-instructions) for additional instructions.

<a name="signed-webhooks"></a>
## Signed Webhook Verification

Twilio SendGrid's Event Webhook will notify a URL via HTTP POST with information about events that occur as your mail is processed. [This](https://docs.sendgrid.com/for-developers/tracking-events/getting-started-event-webhook-security-features) article covers all you need to know to secure the Event Webhook, allowing you to verify that incoming requests originate from Twilio SendGrid. The sendgrid-php library can help you verify these Signed Event Webhooks.

You can find the usage example [here](examples/helpers/eventwebhook/example.php) and the tests [here](test/unit/EventWebhookTest.php). 
If you are still having trouble getting the validation to work, follow the following instructions:
- Be sure to use the *raw* payload for validation
- Be sure to include a trailing carriage return and newline in your payload
- In case of multi-event webhooks, make sure you include the trailing newline and carriage return after *each* event
