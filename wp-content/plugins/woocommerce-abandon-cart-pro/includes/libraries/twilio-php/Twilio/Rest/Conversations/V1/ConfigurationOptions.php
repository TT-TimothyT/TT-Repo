<?php
/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Conversations
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace Twilio\Rest\Conversations\V1;

use Twilio\Options;
use Twilio\Values;

abstract class ConfigurationOptions
{

    /**
     * @param string $defaultChatServiceSid The SID of the default [Conversation Service](https://www.twilio.com/docs/conversations/api/service-resource) to use when creating a conversation.
     * @param string $defaultMessagingServiceSid The SID of the default [Messaging Service](https://www.twilio.com/docs/messaging/services/api) to use when creating a conversation.
     * @param string $defaultInactiveTimer Default ISO8601 duration when conversation will be switched to `inactive` state. Minimum value for this timer is 1 minute.
     * @param string $defaultClosedTimer Default ISO8601 duration when conversation will be switched to `closed` state. Minimum value for this timer is 10 minutes.
     * @return UpdateConfigurationOptions Options builder
     */
    public static function update(
        
        string $defaultChatServiceSid = Values::NONE,
        string $defaultMessagingServiceSid = Values::NONE,
        string $defaultInactiveTimer = Values::NONE,
        string $defaultClosedTimer = Values::NONE

    ): UpdateConfigurationOptions
    {
        return new UpdateConfigurationOptions(
            $defaultChatServiceSid,
            $defaultMessagingServiceSid,
            $defaultInactiveTimer,
            $defaultClosedTimer
        );
    }

}


class UpdateConfigurationOptions extends Options
    {
    /**
     * @param string $defaultChatServiceSid The SID of the default [Conversation Service](https://www.twilio.com/docs/conversations/api/service-resource) to use when creating a conversation.
     * @param string $defaultMessagingServiceSid The SID of the default [Messaging Service](https://www.twilio.com/docs/messaging/services/api) to use when creating a conversation.
     * @param string $defaultInactiveTimer Default ISO8601 duration when conversation will be switched to `inactive` state. Minimum value for this timer is 1 minute.
     * @param string $defaultClosedTimer Default ISO8601 duration when conversation will be switched to `closed` state. Minimum value for this timer is 10 minutes.
     */
    public function __construct(
        
        string $defaultChatServiceSid = Values::NONE,
        string $defaultMessagingServiceSid = Values::NONE,
        string $defaultInactiveTimer = Values::NONE,
        string $defaultClosedTimer = Values::NONE

    ) {
        $this->options['defaultChatServiceSid'] = $defaultChatServiceSid;
        $this->options['defaultMessagingServiceSid'] = $defaultMessagingServiceSid;
        $this->options['defaultInactiveTimer'] = $defaultInactiveTimer;
        $this->options['defaultClosedTimer'] = $defaultClosedTimer;
    }

    /**
     * The SID of the default [Conversation Service](https://www.twilio.com/docs/conversations/api/service-resource) to use when creating a conversation.
     *
     * @param string $defaultChatServiceSid The SID of the default [Conversation Service](https://www.twilio.com/docs/conversations/api/service-resource) to use when creating a conversation.
     * @return $this Fluent Builder
     */
    public function setDefaultChatServiceSid(string $defaultChatServiceSid): self
    {
        $this->options['defaultChatServiceSid'] = $defaultChatServiceSid;
        return $this;
    }

    /**
     * The SID of the default [Messaging Service](https://www.twilio.com/docs/messaging/services/api) to use when creating a conversation.
     *
     * @param string $defaultMessagingServiceSid The SID of the default [Messaging Service](https://www.twilio.com/docs/messaging/services/api) to use when creating a conversation.
     * @return $this Fluent Builder
     */
    public function setDefaultMessagingServiceSid(string $defaultMessagingServiceSid): self
    {
        $this->options['defaultMessagingServiceSid'] = $defaultMessagingServiceSid;
        return $this;
    }

    /**
     * Default ISO8601 duration when conversation will be switched to `inactive` state. Minimum value for this timer is 1 minute.
     *
     * @param string $defaultInactiveTimer Default ISO8601 duration when conversation will be switched to `inactive` state. Minimum value for this timer is 1 minute.
     * @return $this Fluent Builder
     */
    public function setDefaultInactiveTimer(string $defaultInactiveTimer): self
    {
        $this->options['defaultInactiveTimer'] = $defaultInactiveTimer;
        return $this;
    }

    /**
     * Default ISO8601 duration when conversation will be switched to `closed` state. Minimum value for this timer is 10 minutes.
     *
     * @param string $defaultClosedTimer Default ISO8601 duration when conversation will be switched to `closed` state. Minimum value for this timer is 10 minutes.
     * @return $this Fluent Builder
     */
    public function setDefaultClosedTimer(string $defaultClosedTimer): self
    {
        $this->options['defaultClosedTimer'] = $defaultClosedTimer;
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
        return '[Twilio.Conversations.V1.UpdateConfigurationOptions ' . $options . ']';
    }
}

