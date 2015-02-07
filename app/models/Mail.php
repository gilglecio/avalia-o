<?php

class Mail
{
	public $input;

	const SEPARATOR_LIST = ',';
	const SEPARATOR_FIND = ':';

	public function __construct(array $input)
	{
		$this->setInput($input);
	}

	public function setInput($input)
	{
		$this->input = $input;
	}

	public function replacements($replacements, $message)
	{

		if ($message == '') {
			$message = "#avaliacao\n";
			$message .= "Url do Questionario: #questionario.\n";
			$message .= "Data de Inicio: #inicio.\n";
			$message .= "Data de Finalizacao: #termino.\n";
		}

		if (empty($replacements)) return $message;
		
		foreach ($replacements as $tag => $value) {
			$message = str_ireplace($tag, $value, $message);
		}

		return $message;
	}

	public function getFrom()
	{
		$from = array(
			'name' => config('mail.name'),
			'email' => config('mail.email')
		);

		$setting = Setting::find('last');

		if ($setting) {
			$from['name'] = $setting->getName();
			$from['email'] = $setting->getEmail();
		}

		return $from;
	}

	public function send()
	{
		if (ENV_DEFAULT == 'dev')
			return false;

		try {
			$from = $this->getFrom();

			$headers = "MIME-Version: 1.1\r\n";
			$headers .= "Date: " . date('r') . "\r\n";
			$headers .= "Message-ID: <" . $_SERVER['REQUEST_TIME'] . md5($_SERVER['REQUEST_TIME']) . '@' . $_SERVER['SERVER_NAME'] . ">\r\n";
			$headers .= "Reply-To: ".config('mail.name')."<".config('mail.email').">\r\n";
			$headers .= "X-Priority: 3\r\n";
			$headers .= 'X-Originating-IP: ' . $_SERVER['REMOTE_ADDR'];
			$headers .= 'X-Mailer: PHP v' . phpversion();
			$headers .= "Content-type: text/html; charset=UTF-8\r\n";
			$headers .= "From: ".utf8_decode($from['name'])."<".$from['email'].">\r\n";
			$headers .= "Return-Path: ".config('mail.name')."<".config('mail.email').">\r\n";

			if (empty($this->input))
				die('Input Empty.');

			$message = isset($this->input['replacements']) ? $this->replacements($this->input['replacements'], $this->input['message']) : $this->input['message'];
			
			$envio = mail($this->input['to']['email'], utf8_decode($this->input['subject']), $message, $headers);
			
			return $envio;

		} catch (Exception $e) {
			return array('error' => $e->getMessage());
		}		
	}

	public function send_old()
	{
		if (ENV_DEFAULT == 'dev')
			return false;

		try {
			$transport = Swift_SmtpTransport::newInstance(config('mail.smtp'), config('mail.port'), 'ssl');
			$transport->setUsername(config('mail.email'));
			$transport->setPassword(config('mail.pass'));

			$mailer = Swift_Mailer::newInstance($transport);

			if (isset($this->input['replacements'])) {
				$plugin = new Swift_Plugins_DecoratorPlugin($this->input['replacements']);
				$mailer->registerPlugin($plugin);
			}

			$from = $this->getFrom();

			$message = Swift_Message::newInstance('Mail')
				->setSubject($this->input['subject'])
				->setBody($this->input['message'])
				->setFrom($from['name'], $from['email'])
				->setTo($this->input['to']['email'], $this->input['to']['name'])
				;

			$mailer->send($message, $failedRecipients);

			return $failedRecipients;

		} catch (Exception $e) {
			return array('error' => $e->getMessage());
		}		
	}

	public static function mailIsValid($email)
 	{
 		$pattern = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';
		return preg_match($pattern, $email);
 	}

	public static function parseBcc($bcc, $to_json = false)
	{
		$bcc = strip_tags($bcc);
		$list = array_filter(explode(self::SEPARATOR_LIST, $bcc));

		$mails = array();

		foreach ($list as $find) {
			
			$split_find = array_filter(explode(self::SEPARATOR_FIND, $find));

			if (count($split_find) != 2) 
				continue;

			$email = trim($split_find[0]);
			$name = trim($split_find[1]);

			if ( ! self::mailIsValid($email)) 
				continue;

			$mails[$email] = $name;
		}

		if ($to_json)
			return json_encode($mails);

		return $mails;
	}
}