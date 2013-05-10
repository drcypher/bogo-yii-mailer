<?php
/**
 * Abstract mailer application component.
 *
 * Defines a standard interface for concrete mailers.
 */
abstract class CBMailer extends CApplicationComponent
{
	/**
	 * Optional transport headers.
	 * @var string[]
	 */
	public $transportHeaders = array();

	/**
	 * From address (and name).
	 * @var string|string[]
	 */
	public $from;

	/**
	 * Reply-to address (and name).
	 * @var string|string[]
	 */
	public $replyTo;

	/**
	 * Enforces set fromAddress, otherwise it's used as fallback.
	 *
	 * @var boolean
	 */
	public $doEnforceFromAddress = false;

	/**
	 * Enforces set replyToAddress, otherwise it's used as fallback.
	 *
	 * @var boolean
	 */
	public $doEnforceReplyToAddress = false;

	/**
	 * Allows to turn actual dispatch off.
	 *
	 * Useful for debugging purposes.
	 *
	 * @var boolean
	 */
	public $isActive = true;

	/**
	 * Initialize from application-wide configuration.
	 */
	public function init()
	{
		parent::init();
	}

	/**
	 * Create a named address assoc.
	 *
	 * Recognized formats:
	 * <ul>
	 * <li>(Array)array('address'=>$address, 'name'=>$name)</li>
	 * <li>(Array)array('address'=>$address)</li>
	 * <li>(String)$address</li>
	 * </ul>
	 *
	 * @param string|string[] $address
	 * @return array
	 */
	static public function makeNamedAddressAssoc($address)
	{
		if (is_array($address)) {
			if (isset($address['address'])) {
				// array('address'=>$address, 'name'=>$name) or array('address'=>$address) format
				return array('address'=>$address['address'],'name'=>empty($address['name']) ? null : $address['name']);
			} else {
				throw new CException('Unrecognized address format: '.var_export($address, true));
			}
		} else {
			// $address format
			return array('address'=>$address,'name'=>null);
		}
	}

	/**
	 * Pick 'from' address either from envelope or mailer.
	 *
	 * @param CBMailEnvelope $envelope
	 * @return string
	 * @throws CException
	 */
	public function getProperFrom($envelope)
	{
		if ($this->doEnforceFromAddress) {
			// Enforce
			return $this->from;
		} else {
			// Fallback
			return $envelope->from ?: $this->from;
		}
	}

	/**
	 * Pick 'from' address either from envelope or mailer.
	 *
	 * @param CBMailEnvelope $envelope
	 * @return string
	 * @throws CException
	 */
	public function getProperReplyTo($envelope)
	{
		if ($this->doEnforceReplyToAddress) {
			// Enforce
			return $this->replyTo;
		} else {
			// Fallback
			return $envelope->replyTo ?: $this->replyTo;
		}
	}

	/**
	 * Send email message.
	 *
	 * @param CBMailEnvelope|array $envelope
	 * @param CBMailMessage|array $message
	 */
	abstract public function send($envelope, $message);
}