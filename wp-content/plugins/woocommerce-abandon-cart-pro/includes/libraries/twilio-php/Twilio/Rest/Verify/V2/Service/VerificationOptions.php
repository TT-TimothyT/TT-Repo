<?php
/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Verify
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace Twilio\Rest\Verify\V2\Service;

use Twilio\Options;
use Twilio\Values;

abstract class VerificationOptions
{
    /**
     * @param string $customFriendlyName A custom user defined friendly name that overwrites the existing one in the verification message
     * @param string $customMessage The text of a custom message to use for the verification.
     * @param string $sendDigits The digits to send after a phone call is answered, for example, to dial an extension. For more information, see the Programmable Voice documentation of [sendDigits](https://www.twilio.com/docs/voice/twiml/number#attributes-sendDigits).
     * @param string $locale Locale will automatically resolve based on phone number country code for SMS, WhatsApp, and call channel verifications. It will fallback to English or the template’s default translation if the selected translation is not available. This parameter will override the automatic locale resolution. [See supported languages and more information here](https://www.twilio.com/docs/verify/supported-languages).
     * @param string $customCode A pre-generated code to use for verification. The code can be between 4 and 10 characters, inclusive.
     * @param string $amount The amount of the associated PSD2 compliant transaction. Requires the PSD2 Service flag enabled.
     * @param string $payee The payee of the associated PSD2 compliant transaction. Requires the PSD2 Service flag enabled.
     * @param array $rateLimits The custom key-value pairs of Programmable Rate Limits. Keys correspond to `unique_name` fields defined when [creating your Rate Limit](https://www.twilio.com/docs/verify/api/service-rate-limits). Associated value pairs represent values in the request that you are rate limiting on. You may include multiple Rate Limit values in each request.
     * @param array $channelConfiguration [`email`](https://www.twilio.com/docs/verify/email) channel configuration in json format. The fields 'from' and 'from_name' are optional but if included the 'from' field must have a valid email address.
     * @param string $appHash Your [App Hash](https://developers.google.com/identity/sms-retriever/verify#computing_your_apps_hash_string) to be appended at the end of your verification SMS body. Applies only to SMS. Example SMS body: `<#> Your AppName verification code is: 1234 He42w354ol9`.
     * @param string $templateSid The message [template](https://www.twilio.com/docs/verify/api/templates). If provided, will override the default template for the Service. SMS and Voice channels only.
     * @param string $templateCustomSubstitutions A stringified JSON object in which the keys are the template's special variables and the values are the variables substitutions.
     * @param string $deviceIp Strongly encouraged if using the auto channel. The IP address of the client's device. If provided, it has to be a valid IPv4 or IPv6 address.
     * @param string $riskCheck
     * @return CreateVerificationOptions Options builder
     */
    public static function create(
        
        string $customFriendlyName = Values::NONE,
        string $customMessage = Values::NONE,
        string $sendDigits = Values::NONE,
        string $locale = Values::NONE,
        string $customCode = Values::NONE,
        string $amount = Values::NONE,
        string $payee = Values::NONE,
        array $rateLimits = Values::ARRAY_NONE,
        array $channelConfiguration = Values::ARRAY_NONE,
        string $appHash = Values::NONE,
        string $templateSid = Values::NONE,
        string $templateCustomSubstitutions = Values::NONE,
        string $deviceIp = Values::NONE,
        string $riskCheck = Values::NONE

    ): CreateVerificationOptions
    {
        return new CreateVerificationOptions(
            $customFriendlyName,
            $customMessage,
            $sendDigits,
            $locale,
            $customCode,
            $amount,
            $payee,
            $rateLimits,
            $channelConfiguration,
            $appHash,
            $templateSid,
            $templateCustomSubstitutions,
            $deviceIp,
            $riskCheck
        );
    }



}

class CreateVerificationOptions extends Options
    {
    /**
     * @param string $customFriendlyName A custom user defined friendly name that overwrites the existing one in the verification message
     * @param string $customMessage The text of a custom message to use for the verification.
     * @param string $sendDigits The digits to send after a phone call is answered, for example, to dial an extension. For more information, see the Programmable Voice documentation of [sendDigits](https://www.twilio.com/docs/voice/twiml/number#attributes-sendDigits).
     * @param string $locale Locale will automatically resolve based on phone number country code for SMS, WhatsApp, and call channel verifications. It will fallback to English or the template’s default translation if the selected translation is not available. This parameter will override the automatic locale resolution. [See supported languages and more information here](https://www.twilio.com/docs/verify/supported-languages).
     * @param string $customCode A pre-generated code to use for verification. The code can be between 4 and 10 characters, inclusive.
     * @param string $amount The amount of the associated PSD2 compliant transaction. Requires the PSD2 Service flag enabled.
     * @param string $payee The payee of the associated PSD2 compliant transaction. Requires the PSD2 Service flag enabled.
     * @param array $rateLimits The custom key-value pairs of Programmable Rate Limits. Keys correspond to `unique_name` fields defined when [creating your Rate Limit](https://www.twilio.com/docs/verify/api/service-rate-limits). Associated value pairs represent values in the request that you are rate limiting on. You may include multiple Rate Limit values in each request.
     * @param array $channelConfiguration [`email`](https://www.twilio.com/docs/verify/email) channel configuration in json format. The fields 'from' and 'from_name' are optional but if included the 'from' field must have a valid email address.
     * @param string $appHash Your [App Hash](https://developers.google.com/identity/sms-retriever/verify#computing_your_apps_hash_string) to be appended at the end of your verification SMS body. Applies only to SMS. Example SMS body: `<#> Your AppName verification code is: 1234 He42w354ol9`.
     * @param string $templateSid The message [template](https://www.twilio.com/docs/verify/api/templates). If provided, will override the default template for the Service. SMS and Voice channels only.
     * @param string $templateCustomSubstitutions A stringified JSON object in which the keys are the template's special variables and the values are the variables substitutions.
     * @param string $deviceIp Strongly encouraged if using the auto channel. The IP address of the client's device. If provided, it has to be a valid IPv4 or IPv6 address.
     * @param string $riskCheck
     */
    public function __construct(
        
        string $customFriendlyName = Values::NONE,
        string $customMessage = Values::NONE,
        string $sendDigits = Values::NONE,
        string $locale = Values::NONE,
        string $customCode = Values::NONE,
        string $amount = Values::NONE,
        string $payee = Values::NONE,
        array $rateLimits = Values::ARRAY_NONE,
        array $channelConfiguration = Values::ARRAY_NONE,
        string $appHash = Values::NONE,
        string $templateSid = Values::NONE,
        string $templateCustomSubstitutions = Values::NONE,
        string $deviceIp = Values::NONE,
        string $riskCheck = Values::NONE

    ) {
        $this->options['customFriendlyName'] = $customFriendlyName;
        $this->options['customMessage'] = $customMessage;
        $this->options['sendDigits'] = $sendDigits;
        $this->options['locale'] = $locale;
        $this->options['customCode'] = $customCode;
        $this->options['amount'] = $amount;
        $this->options['payee'] = $payee;
        $this->options['rateLimits'] = $rateLimits;
        $this->options['channelConfiguration'] = $channelConfiguration;
        $this->options['appHash'] = $appHash;
        $this->options['templateSid'] = $templateSid;
        $this->options['templateCustomSubstitutions'] = $templateCustomSubstitutions;
        $this->options['deviceIp'] = $deviceIp;
        $this->options['riskCheck'] = $riskCheck;
    }

    /**
     * A custom user defined friendly name that overwrites the existing one in the verification message
     *
     * @param string $customFriendlyName A custom user defined friendly name that overwrites the existing one in the verification message
     * @return $this Fluent Builder
     */
    public function setCustomFriendlyName(string $customFriendlyName): self
    {
        $this->options['customFriendlyName'] = $customFriendlyName;
        return $this;
    }

    /**
     * The text of a custom message to use for the verification.
     *
     * @param string $customMessage The text of a custom message to use for the verification.
     * @return $this Fluent Builder
     */
    public function setCustomMessage(string $customMessage): self
    {
        $this->options['customMessage'] = $customMessage;
        return $this;
    }

    /**
     * The digits to send after a phone call is answered, for example, to dial an extension. For more information, see the Programmable Voice documentation of [sendDigits](https://www.twilio.com/docs/voice/twiml/number#attributes-sendDigits).
     *
     * @param string $sendDigits The digits to send after a phone call is answered, for example, to dial an extension. For more information, see the Programmable Voice documentation of [sendDigits](https://www.twilio.com/docs/voice/twiml/number#attributes-sendDigits).
     * @return $this Fluent Builder
     */
    public function setSendDigits(string $sendDigits): self
    {
        $this->options['sendDigits'] = $sendDigits;
        return $this;
    }

    /**
     * Locale will automatically resolve based on phone number country code for SMS, WhatsApp, and call channel verifications. It will fallback to English or the template’s default translation if the selected translation is not available. This parameter will override the automatic locale resolution. [See supported languages and more information here](https://www.twilio.com/docs/verify/supported-languages).
     *
     * @param string $locale Locale will automatically resolve based on phone number country code for SMS, WhatsApp, and call channel verifications. It will fallback to English or the template’s default translation if the selected translation is not available. This parameter will override the automatic locale resolution. [See supported languages and more information here](https://www.twilio.com/docs/verify/supported-languages).
     * @return $this Fluent Builder
     */
    public function setLocale(string $locale): self
    {
        $this->options['locale'] = $locale;
        return $this;
    }

    /**
     * A pre-generated code to use for verification. The code can be between 4 and 10 characters, inclusive.
     *
     * @param string $customCode A pre-generated code to use for verification. The code can be between 4 and 10 characters, inclusive.
     * @return $this Fluent Builder
     */
    public function setCustomCode(string $customCode): self
    {
        $this->options['customCode'] = $customCode;
        return $this;
    }

    /**
     * The amount of the associated PSD2 compliant transaction. Requires the PSD2 Service flag enabled.
     *
     * @param string $amount The amount of the associated PSD2 compliant transaction. Requires the PSD2 Service flag enabled.
     * @return $this Fluent Builder
     */
    public function setAmount(string $amount): self
    {
        $this->options['amount'] = $amount;
        return $this;
    }

    /**
     * The payee of the associated PSD2 compliant transaction. Requires the PSD2 Service flag enabled.
     *
     * @param string $payee The payee of the associated PSD2 compliant transaction. Requires the PSD2 Service flag enabled.
     * @return $this Fluent Builder
     */
    public function setPayee(string $payee): self
    {
        $this->options['payee'] = $payee;
        return $this;
    }

    /**
     * The custom key-value pairs of Programmable Rate Limits. Keys correspond to `unique_name` fields defined when [creating your Rate Limit](https://www.twilio.com/docs/verify/api/service-rate-limits). Associated value pairs represent values in the request that you are rate limiting on. You may include multiple Rate Limit values in each request.
     *
     * @param array $rateLimits The custom key-value pairs of Programmable Rate Limits. Keys correspond to `unique_name` fields defined when [creating your Rate Limit](https://www.twilio.com/docs/verify/api/service-rate-limits). Associated value pairs represent values in the request that you are rate limiting on. You may include multiple Rate Limit values in each request.
     * @return $this Fluent Builder
     */
    public function setRateLimits(array $rateLimits): self
    {
        $this->options['rateLimits'] = $rateLimits;
        return $this;
    }

    /**
     * [`email`](https://www.twilio.com/docs/verify/email) channel configuration in json format. The fields 'from' and 'from_name' are optional but if included the 'from' field must have a valid email address.
     *
     * @param array $channelConfiguration [`email`](https://www.twilio.com/docs/verify/email) channel configuration in json format. The fields 'from' and 'from_name' are optional but if included the 'from' field must have a valid email address.
     * @return $this Fluent Builder
     */
    public function setChannelConfiguration(array $channelConfiguration): self
    {
        $this->options['channelConfiguration'] = $channelConfiguration;
        return $this;
    }

    /**
     * Your [App Hash](https://developers.google.com/identity/sms-retriever/verify#computing_your_apps_hash_string) to be appended at the end of your verification SMS body. Applies only to SMS. Example SMS body: `<#> Your AppName verification code is: 1234 He42w354ol9`.
     *
     * @param string $appHash Your [App Hash](https://developers.google.com/identity/sms-retriever/verify#computing_your_apps_hash_string) to be appended at the end of your verification SMS body. Applies only to SMS. Example SMS body: `<#> Your AppName verification code is: 1234 He42w354ol9`.
     * @return $this Fluent Builder
     */
    public function setAppHash(string $appHash): self
    {
        $this->options['appHash'] = $appHash;
        return $this;
    }

    /**
     * The message [template](https://www.twilio.com/docs/verify/api/templates). If provided, will override the default template for the Service. SMS and Voice channels only.
     *
     * @param string $templateSid The message [template](https://www.twilio.com/docs/verify/api/templates). If provided, will override the default template for the Service. SMS and Voice channels only.
     * @return $this Fluent Builder
     */
    public function setTemplateSid(string $templateSid): self
    {
        $this->options['templateSid'] = $templateSid;
        return $this;
    }

    /**
     * A stringified JSON object in which the keys are the template's special variables and the values are the variables substitutions.
     *
     * @param string $templateCustomSubstitutions A stringified JSON object in which the keys are the template's special variables and the values are the variables substitutions.
     * @return $this Fluent Builder
     */
    public function setTemplateCustomSubstitutions(string $templateCustomSubstitutions): self
    {
        $this->options['templateCustomSubstitutions'] = $templateCustomSubstitutions;
        return $this;
    }

    /**
     * Strongly encouraged if using the auto channel. The IP address of the client's device. If provided, it has to be a valid IPv4 or IPv6 address.
     *
     * @param string $deviceIp Strongly encouraged if using the auto channel. The IP address of the client's device. If provided, it has to be a valid IPv4 or IPv6 address.
     * @return $this Fluent Builder
     */
    public function setDeviceIp(string $deviceIp): self
    {
        $this->options['deviceIp'] = $deviceIp;
        return $this;
    }

    /**
     * @param string $riskCheck
     * @return $this Fluent Builder
     */
    public function setRiskCheck(string $riskCheck): self
    {
        $this->options['riskCheck'] = $riskCheck;
        return $this;
    }

    /**
     * Provide a friendly representation
     *
     * @return string Machine friendly representation
     */
    public function __toString(): string
    {
        $options = \http_build_query(Values::of($this->options), '', ' ');
        return '[Twilio.Verify.V2.CreateVerificationOptions ' . $options . ']';
    }
}



