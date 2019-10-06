<?php

class Mailgun extends Plugin
{
    public function init($host)
    {
        $host->add_hook($host::HOOK_SEND_MAIL, $this);
    }

    public function hook_send_mail($mailer, $params)
    {
        if (!defined("MAILGUN_API_BASE_URL")) {
            $mailer->set_error("Email could not be sent. Please specify your API " .
                "base URL (MAILGUN_API_BASE_URL) in your config.php file.");
            return 0;
        }

        if (!defined("MAILGUN_API_KEY")) {
            $mailer->set_error("Email could not be sent. Please specify your API " .
                "key (MAILGUN_API_KEY) in your config.php file.");
            return 0;
        }

        if (filter_var(SMTP_FROM_ADDRESS, FILTER_VALIDATE_EMAIL) === false ||
            strpos(MAILGUN_API_BASE_URL, substr(SMTP_FROM_ADDRESS, strrpos(SMTP_FROM_ADDRESS, "@") + 1)) === false
        ) {
            $mailer->set_error("Email could not be sent. Please specify a valid " .
                "from address (SMTP_FROM_ADDRESS) in your config.php file.");
            return 0;
        }

        if (filter_var($params["to_address"], FILTER_VALIDATE_EMAIL) === false) {
            $mailer->set_error("Email could not be sent. The destination address is invalid.");
            return 0;
        }

        $post = array(
            "from" => SMTP_FROM_NAME . " <" . SMTP_FROM_ADDRESS . ">",
            "to" => $params["to_name"] . " <" . $params["to_address"] . ">",
            "subject" => $params["subject"],
            "text" => $params["message"]
        );

        if (array_key_exists("message_html", $params)) {
            $post["html"] = $params["message_html"];
        }

        foreach ($params["headers"] as $key => $header) {
            if (strpos(strtolower($header), "reply-to") !== false) {
                $replyTo = str_ireplace("reply-to: ", "", $header);
                $post["h:Reply-To"] = $replyTo;
            }
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, MAILGUN_API_BASE_URL);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, MAILGUN_API_KEY);
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
            "https://github.com/klempin"
        );
    }

    public function api_version()
    {
        return 2;
    }
}
