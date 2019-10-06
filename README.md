# TT-RSS Mailgun

Setup [Tiny Tiny RSS](https://tt-rss.org/) to send emails using the [Mailgun](https://www.mailgun.com/) API.

## Installation

Clone this project or download a copy of it into a folder called `mailgun` and place it into the `plugins.local` folder of your Tiny Tiny RSS instance. The plugin should now show up in Tiny Tiny RSS in the list of system plugins.

Add the following settings to the end of your `config.php` file and fill in your API base URL and API key.

```php
define('MAILGUN_API_BASE_URL', 'YOUR_API_BASE_URL');
define('MAILGUN_API_KEY', 'YOUR_API_KEY');
```

Adjust the following settings that should already be in your `config.php` file or add them if necessary. The `SMTP_FROM_NAME` will be the name that shows up in the recipients email client. The `SMTP_FROM_ADDRESS` will be the email address the emails originate from and *the domain part must match your domain in the API base URL*.

```php
define('SMTP_FROM_NAME', 'Tiny Tiny RSS');
define('SMTP_FROM_ADDRESS', 'noreply@your.domain.dom');
```

The configuration should look similar to this.

```php
define('SMTP_FROM_NAME', 'Example - Tiny Tiny RSS');
define('SMTP_FROM_ADDRESS', 'noreply@mg.example.com');
define('MAILGUN_API_BASE_URL', 'https://api.mailgun.net/v3/mg.example.com');
define('MAILGUN_API_KEY', '1xjyzy7vodi26aox4zmq7tec8ni40vvv-8wb82zzk-5ds03p3r');
```

Lastly, add `mailgun` to the comma-separated list found in the `config.php` setting `PLUGINS`.

```php
define('PLUGINS', 'auth_internal, note, mailgun');
```

Mailgun integration is now set up and Tiny Tiny RSS will send emails using the Mailgun API. You can check your configuration in the preferences tab. *Note: This tab is only available to users in the admin group.*

## License

This project is licensed under the GNU General Public License v3.0 - see the [LICENSE](LICENSE) file for details.
