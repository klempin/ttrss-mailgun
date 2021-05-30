# TT-RSS Mailgun Plugin

Setup [Tiny Tiny RSS](https://tt-rss.org/) to send emails using the [Mailgun](https://www.mailgun.com/) API.

## Installation

Clone this project or download a copy of it into a folder called `mailgun` and place it into the `plugins.local` folder of your Tiny Tiny RSS instance. Enable the plugin globally using the system configuration.


```
TTRSS_PLUGINS=auth_internal, mailgun, nginx_xaccel
```

Add the following settings to your environment variables. Fill in your API base URL and API key. The `TTRSS_MAILGUN_FROM_NAME` will be the name that shows up in the recipients email client. The `TTRSS_MAILGUN_FROM_ADDRESS` will be the email address the emails originate from and *the domain part must match your domain in the API base URL*.

```
TTRSS_MAILGUN_API_BASE_URL=YOUR_API_BASE_URL
TTRSS_MAILGUN_API_KEY=YOUR_API_KEY
TTRSS_MAILGUN_FROM_NAME=Tiny Tiny RSS
TTRSS_MAILGUN_FROM_ADDRESS=noreply@example.com
```

The configuration should look similar to this.

```
TTRSS_MAILGUN_API_BASE_URL=https://api.mailgun.net/v3/mg.example.com
TTRSS_MAILGUN_API_KEY=1xjyzy7vodi26aox4zmq7tec8ni40vvv-8wb82zzk-5ds03p3r
TTRSS_MAILGUN_FROM_NAME=Example - Tiny Tiny RSS
TTRSS_MAILGUN_FROM_ADDRESS=noreply@mg.example.com
```


Mailgun integration is now set up and Tiny Tiny RSS will send emails using the Mailgun API. You can check your configuration in the preferences tab. *Note: This tab is only available to users in the admin group.*

## License

This project is licensed under the GNU General Public License v3.0 - see the [LICENSE](LICENSE) file for details.
