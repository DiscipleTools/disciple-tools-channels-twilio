[![Build Status](https://travis-ci.com/DiscipleTools/disciple-tools-channels-twilio.svg?branch=master)](https://travis-ci.com/DiscipleTools/disciple-tools-channels-twilio)

# Disciple Tools - Channels - Twilio

Send SMS and WhatsApp messages or Disciple.Tools notifications using Twilio.

## Pre-requisites
A [Twilio Account](https://www.twilio.com/) setup with a phone number and [messaging service](https://github.com/DiscipleTools/disciple-tools-channels-twilio/wiki/Getting-Started-With-Twilio-Messaging-Services) set up.
To use WhatsApp you'll need a [Whatsapp Sender](https://www.twilio.com/docs/whatsapp/self-sign-up) linked to one of your twilio phone numbers.

See setup instructions in the [wiki](https://github.com/DiscipleTools/disciple-tools-channels-twilio/wiki).

#### Will Do

- Let other plugins (link magic link scheduler) send messages using Twilio.
- Optionally: Setup D.T notifications to be sent over SMS or WhatsApp.
- Provides an API to send messages directly.


#### API Usage

The Disciple Tools - Channels - Twilio plugin can be decoupled, so as to work independently of the Disciple Tools - Magic Links plugin; in order to send messages directly.

Send as sms to a number.
Returns a boolean value indicating if the message was sent successfully.
```php
Disciple_Tools_Twilio_API::send_sms( $phone_number, $message );
```

Send as WhatsApp message to a number.
Note: This will only work if the contact has WhatsApp messaged you in the last 24 hours.
Returns a boolean value indicating if the message was sent successfully.
```php
Disciple_Tools_Twilio_API::send_whatsapp( $phone_number, $message );
```

Send a message to a D.T User
```php
if ( dt_twilio_configured() ) {
    $bool_result = dt_twilio_direct_send( 12, 'wp_user', $msg, [ 'service' => 'sms' ] );
}
```

Send a message to a D.T Contact
```php
if ( dt_twilio_configured() ) {
    $bool_result = dt_twilio_direct_send( 343, 'post', $msg, [ 'service' => 'sms' ] );
}
```

- __id:__ Assigned WP user id or post id, depending on type value.
- __type:__ System type; which must be one of the following:
  - wp_user
  - post
- __msg:__ Actual message to be sent; which must adhere to the pre-defined Twilio message template shape. For example:
  ```text
  Hi, Please update records -> {{link}} -> Link will expire on {{time}}
  ```
  - {{...}} placeholders to be substituted with actual values.
- __args:__ Ability to specify option overrides during sending. Currently, the following overrides are supported:
  - _service:_ Specify which of the following twilio service types are to be adopted:
    - sms
    - whatsapp



## Contribution

Contributions welcome. You can report issues and bugs in the
[Issues](https://github.com/DiscipleTools/disciple-tools-channels-twilio/issues) section of the repo. You can present
ideas in the [Discussions](https://github.com/DiscipleTools/disciple-tools-channels-twilio/discussions) section of the
repo. And code contributions are welcome using
the [Pull Request](https://github.com/DiscipleTools/disciple-tools-channels-twilio/pulls)
system for git. For a more details on contribution see the
[contribution guidelines](https://github.com/DiscipleTools/disciple-tools-channels-twilio/blob/master/CONTRIBUTING.md).
