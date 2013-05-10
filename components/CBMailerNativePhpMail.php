<?php
/**
 * Concrete mailer using native PHP mail() function.
 */
class CBMailerNativePhpMail extends CBMailer
{
	/**
	 * Create a named address string from a named address assoc.
	 *
	 * @param string[] $namedAddressAssoc
	 * @return string
	 */
	private function makeNamedAddressString($namedAddressAssoc)
	{
		list($address, $name) = each($namedAddressAssoc);

		return $name ? $name.' <'.$address.'>' : $address;
	}

	/**
	 * Create a csv of name addresses.
	 *
	 * @param type $rawAddresses
	 * @return string
	 */
	private function makeNamedAddressesString($rawAddresses)
	{
		$str = '';
		foreach ($rawAddresses as $rawAddress) {
			$str .= ($str ? ', ' : '').$this->makeNamedAddressString(CBMailer::makeNamedAddressAssoc($rawAddress));
		}
		return $str;
	}

	/**
	 * Send email message.
	 *
	 * @param CBMailEnvelope|array $envelope
	 * @param CBMailMessage|array $message
	 */
	public function send($envelope, $message)
	{
		if (is_array($message)) {
			$message = new CBMailMessage($message);
		}

		if (is_array($envelope)) {
			$envelope = new CBMailEnvelope($envelope);
		}

		//
		// Start with transport headers
		//
		$finalHeaders = $this->transportHeaders;

		//
		// Copy envelope info
		//
		if ($envelope->cc) {
			$finalHeaders['Cc'] = $this->makeNamedAddressesString($envelope->cc);
		}
		if ($envelope->bcc) {
			$finalHeaders['Bcc'] = $this->makeNamedAddressesString($envelope->bcc);
		}

		//
		// Merge message headers
		//
		$finalHeaders['Content-type'] = $message->contentType.'; '.$message->charset;

		//
		// Customly managed headers
		//
		$finalHeaders['From'] = $this->makeNamedAddressString(CBMailer::makeNamedAddressAssoc($this->getProperFrom($envelope)));
		$finalHeaders['Reply-to'] = $this->makeNamedAddressString(CBMailer::makeNamedAddressAssoc($this->getProperReplyTo($envelope)));
		$finalHeaders['MIME-Version'] = '1.0';

		//
		// Convert headers into a string
		//
		$finalHeadersString = '';
		foreach ($finalHeaders as $headerName=>$headerValue) {
			if (!empty($headerValue)) {
				$finalHeadersString .= ($finalHeadersString ? "\r\n" : '').$headerName.': '.$headerValue;
			}
		}

		//
		// Create recipients string
		//
		$toAddressesString = $this->makeNamedAddressesString($envelope->to);

		// Send email
		if ($this->inDebugMode) {
			Yii::trace(print_r([
				'to' => $toAddressesString,
				'subject' => $message->subject,
				'body' => $message->body,
				'headers' => $finalHeadersString
			], true), 'bogo-yii-mailer.CBMailerPhpMail');
		} else {
			mail($toAddressesString, $message->subject, $message->body, $finalHeadersString);
		}
	}
}