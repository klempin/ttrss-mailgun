<?php

class Mailgun extends Plugin
{
    public function init($host)
    {
        $host->add_hook($host::HOOK_PREFS_TAB, $this);
        $host->add_hook($host::HOOK_SEND_MAIL, $this);

        foreach ([
            ['MAILGUN_API_BASE_URL', 'MAILGUN_API_BASE_URL'],
            ['MAILGUN_API_KEY', 'MAILGUN_API_KEY'],
            ['MAILGUN_FROM_NAME', 'SMTP_FROM_NAME'],
            ['MAILGUN_FROM_ADDRESS', 'SMTP_FROM_ADDRESS']
        ] as $config) {
            if (defined($config[1])) {
                Config::add($config[0], constant($config[1]));
            } else {
                Config::add($config[0], '');
            }
        }
    }

    public function hook_prefs_tab($args)
    {
        if ($args !== "prefFeeds" || $_SESSION["access_level"] < 10) {
            return;
        }

        $configCorrect = true;

        if ($this->apiBaseUrlValid()) {
            $baseUrl = Config::get('MAILGUN_API_BASE_URL');
        } else {
            $configCorrect = false;
            $baseUrl = "<span class=\"error\">undefined</span>";
        }

        if ($this->apiKeyValid()) {
            $apiKey = Config::get('MAILGUN_API_KEY');
            $apiKey = substr($apiKey, 0, 6) . str_repeat('*', strlen($apiKey) - 6);
        } else {
            $configCorrect = false;
            $apiKey = "<span class=\"error\">invalid</span>";
        }

        if (strlen(Config::get('MAILGUN_FROM_NAME')) !== 0) {
            $fromName = Config::get('MAILGUN_FROM_NAME');
        } else {
            $configCorrect = false;
            $fromName = '<span class="error">undefined</span>';
        }

        if ($this->fromEmailValid()) {
            $fromEmail = Config::get('MAILGUN_FROM_ADDRESS');
        } else {
            $configCorrect = false;
            $fromEmail = '<span class="error">invalid: ' . Config::get('MAILGUN_FROM_ADDRESS') . '</span>';
        }

        $msg = '';
        if (!$configCorrect) {
            $msg = <<< EOT
<div class="alert alert-danger">
    There are errors in you TTRSS-Mailgun configuration. Please check the
    <a href="https://github.com/klempin/ttrss-mailgun#installation">setup guide</a>.
</div>
EOT;
        }

        echo <<<EOT
<div id="prefs-mailgun" data-dojo-type="dijit/layout/ContentPane" title="<i class='material-icons'>email</i> Mailgun">
    <h3>Current Mailgun settings</h3>
    {$msg}
    <table>
        <tr><td>API base URL</td><td>{$baseUrl}</td></tr>
        <tr><td>API key</td><td>{$apiKey}</td></tr>
        <tr><td>Sender name</td><td>{$fromName}</td></tr>
        <tr><td>Sender email address</td><td>{$fromEmail}</td></tr>
    </table>
    <style>
        #prefs-mailgun {
            display: flex;
            flex-direction: column;
        }

        #prefs-mailgun span.error {
            color: red;
        }
    </style>
</div>
EOT;
    }

    public function hook_send_mail($mailer, $params)
    {
        if (!$this->apiBaseUrlValid()) {
            $mailer->set_error("Email could not be sent. Please specify your API " .
                "base URL (MAILGUN_API_BASE_URL) in your config.php file.");
            return 0;
        }

        if (!$this->apiKeyValid()) {
            $mailer->set_error("Email could not be sent. Please specify your API " .
                "key (MAILGUN_API_KEY) in your config.php file.");
            return 0;
        }

        if (!$this->fromEmailValid()) {
            $mailer->set_error("Email could not be sent. Please specify a valid " .
                "from address (SMTP_FROM_ADDRESS) in your config.php file.");
            return 0;
        }

        if (filter_var($params["to_address"], FILTER_VALIDATE_EMAIL) === false) {
            $mailer->set_error("Email could not be sent. The destination address is invalid.");
            return 0;
        }

        $post = array(
            "from" => Config::get('MAILGUN_FROM_NAME') . " <" . Config::get('MAILGUN_FROM_ADDRESS') . ">",
            "to" => $params["to_name"] . " <" . $params["to_address"] . ">",
            "subject" => $params["subject"],
            "text" => $params["message"]
        );

        if (array_key_exists("message_html", $params)) {
            $post["html"] = $params["message_html"];
        }

        if (array_key_exists("headers", $params)) {
            foreach ($params["headers"] as $key => $header) {
                if (strpos(strtolower($header), "reply-to") !== false) {
                    $replyTo = str_ireplace("reply-to: ", "", $header);
                    $post["h:Reply-To"] = $replyTo;
                }
            }
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, Config::get('MAILGUN_API_BASE_URL') . "/messages");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "api:" . Config::get('MAILGUN_API_KEY'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        $response = curl_exec($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        if ($responseCode === 200) {
            return 1;
        } else {
            return 0;
        }
    }

    public function about()
    {
        return array(
            1.0,
            "Send emails using the Mailgun API",
            "Philip Klempin",
            true,
            "https://github.com/klempin/ttrss-mailgun"
        );
    }

    public function api_version()
    {
        return 2;
    }

    private function apiBaseUrlValid()
    {
        $url = Config::get('MAILGUN_API_BASE_URL');

        if (strlen($url) < 26) {
            return false;
        }

        if (strpos($url, 'https://api.mailgun.net/v3') !== 0 && strpos($url, 'https://api.eu.mailgun.net/v3') !== 0) {
            return false;
        }

        return true;
    }

    private function apiKeyValid()
    {
        return strlen(Config::get('MAILGUN_API_KEY')) > 0;
    }

    private function fromEmailValid()
    {
        $from = Config::get('MAILGUN_FROM_ADDRESS');

        if (filter_var($from, FILTER_VALIDATE_EMAIL) === false) {
            return false;
        }

        if (!$this->apiBaseUrlValid()) {
            return false;
        }

        if (strpos($from, substr($from, strrpos($from, "@") + 1)) === false) {
            return false;
        }

        return true;
    }
}
