<?php
/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Chat
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace Twilio\Rest\Chat\V3;

use Twilio\Options;
use Twilio\Values;

abstract class ChannelOptions
{
    /**
     * @param string $type
     * @param string $messagingServiceSid The unique ID of the [Messaging Service](https://www.twilio.com/docs/messaging/services/api) this channel belongs to.
     * @param string $xTwilioWebhookEnabled The X-Twilio-Webhook-Enabled HTTP request header
     * @return UpdateChannelOptions Options builder
     */
    public static function update(
        
        string $type = Values::NONE,
        string $messagingServiceSid = Values::NONE,
        string $xTwilioWebhookEnabled = Values::NONE

    ): UpdateChannelOptions
    {
        return new UpdateChannelOptions(
            $type,
            $messagingServiceSid,
            $xTwilioWebhookEnabled
        );
    }

}

class UpdateChannelOptions extends Options
    {
    /**
     * @param string $type
     * @param string $messagingServiceSid The unique ID of the [Messaging Service](https://www.twilio.com/docs/messaging/services/api) this channel belongs to.
     * @param string $xTwilioWebhookEnabled The X-Twilio-Webhook-Enabled HTTP request header
     */
    public function __construct(
        
        string $type = Values::NONE,
        string $messagingServiceSid = Values::NONE,
        string $xTwilioWebhookEnabled = Values::NONE

    ) {
        $this->options['type'] = $type;
        $this->options['messagingServiceSid'] = $messagingServiceSid;
        $this->options['xTwilioWebhookEnabled'] = $xTwilioWebhookEnabled;
    }

    /**
     * @param string $type
     * @return $this Fluent Builder
     */
    public function setType(string $type): self
    {
        $this->options['type'] = $type;
        return $this;
    }

    /**
     * The unique ID of the [Messaging Service](https://www.twilio.com/docs/messaging/services/api) this channel belongs to.
     *
     * @param string $messagingServiceSid The unique ID of the [Messaging Service](https://www.twilio.com/docs/messaging/services/api) this channel belongs to.
     * @return $this Fluent Builder
     */
    public function setMessagingServiceSid(string $messagingServiceSid): self
    {
        $this->options['messagingServiceSid'] = $messagingServiceSid;
        return $this;
    }

    /**
     * The X-Twilio-Webhook-Enabled HTTP request header
     *
     * @param string $xTwilioWebhookEnabled The X-Twilio-Webhook-Enabled HTTP request header
     * @return $this Fluent Builder
     */
    public function setXTwilioWebhookEnabled(string $xTwilioWebhookEnabled): self
    {
        $this->options['xTwilioWebhookEnabled'] = $xTwilioWebhookEnabled;
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
        return '[Twilio.Chat.V3.UpdateChannelOptions ' . $options . ']';
    }
}

