[![Build Status](https://travis-ci.com/DiscipleTools/disciple-tools-channels-twilio.svg?branch=master)](https://travis-ci.com/DiscipleTools/disciple-tools-channels-twilio)

# Disciple Tools - Channels - Twilio

Disciple Tools - Channels - Twilio plugin; captures settings enabling communication with target Twilio platform.

## Usage

#### Will Do

- Enable/Disable plugin execution.
- Specify target Twilio SID and Access Tokens.
- Switch between SMS and WhatsApp services.
- Select pre-configured Twilio Messaging Services.
- Select D.T. contacts field to source recipient phone numbers.

#### Will Not Do

- Your weekly shopping...! ;)

#### Direct Usage

The Disciple Tools - Channels - Twilio plugin can be decoupled, so as to work independently of the Disciple Tools - Bulk Magic link Sender plugin; in order to send messages directly.

In order to make use of this functionality; simply call the following action:

```php
do_action('dt_twilio_send', 1, 'wp_user', $msg );
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

## Requirements

- Disciple Tools Theme installed on a Wordpress Server

## Installing

- Install as a standard Disciple.Tools/Wordpress plugin in the system Admin/Plugins area.
- Requires the user role of Administrator.

## Contribution

Contributions welcome. You can report issues and bugs in the
[Issues](https://github.com/DiscipleTools/disciple-tools-channels-twilio/issues) section of the repo. You can present
ideas in the [Discussions](https://github.com/DiscipleTools/disciple-tools-channels-twilio/discussions) section of the
repo. And code contributions are welcome using
the [Pull Request](https://github.com/DiscipleTools/disciple-tools-channels-twilio/pulls)
system for git. For a more details on contribution see the
[contribution guidelines](https://github.com/DiscipleTools/disciple-tools-channels-twilio/blob/master/CONTRIBUTING.md).
