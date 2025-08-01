<?php

/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Intelligence
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */


namespace Twilio\Rest\Intelligence\V2;

use Twilio\Exceptions\TwilioException;
use Twilio\ListResource;
use Twilio\Options;
use Twilio\Values;
use Twilio\Version;
use Twilio\InstanceContext;
use Twilio\Serialize;
use Twilio\Rest\Intelligence\V2\Transcript\OperatorResultList;
use Twilio\Rest\Intelligence\V2\Transcript\SentenceList;
use Twilio\Rest\Intelligence\V2\Transcript\MediaList;


/**
 * @property OperatorResultList $operatorResults
 * @property SentenceList $sentences
 * @property MediaList $media
 * @method \Twilio\Rest\Intelligence\V2\Transcript\OperatorResultContext operatorResults(string $operatorSid)
 * @method \Twilio\Rest\Intelligence\V2\Transcript\MediaContext media()
 */
class TranscriptContext extends InstanceContext
    {
    protected $_operatorResults;
    protected $_sentences;
    protected $_media;

    /**
     * Initialize the TranscriptContext
     *
     * @param Version $version Version that contains the resource
     * @param string $sid A 34 character string that uniquely identifies this Transcript.
     */
    public function __construct(
        Version $version,
        $sid
    ) {
        parent::__construct($version);

        // Path Solution
        $this->solution = [
        'sid' =>
            $sid,
        ];

        $this->uri = '/Transcripts/' . \rawurlencode($sid)
        .'';
    }

    /**
     * Delete the TranscriptInstance
     *
     * @return bool True if delete succeeds, false otherwise
     * @throws TwilioException When an HTTP error occurs.
     */
    public function delete(): bool
    {

        return $this->version->delete('DELETE', $this->uri);
    }


    /**
     * Fetch the TranscriptInstance
     *
     * @param array|Options $options Optional Arguments
     * @return TranscriptInstance Fetched TranscriptInstance
     * @throws TwilioException When an HTTP error occurs.
     */
    public function fetch(array $options = []): TranscriptInstance
    {

        $options = new Values($options);

        $params = Values::of([
            'Redacted' =>
                Serialize::booleanToString($options['redacted']),
        ]);

        $payload = $this->version->fetch('GET', $this->uri, $params);

        return new TranscriptInstance(
            $this->version,
            $payload,
            $this->solution['sid']
        );
    }


    /**
     * Access the operatorResults
     */
    protected function getOperatorResults(): OperatorResultList
    {
        if (!$this->_operatorResults) {
            $this->_operatorResults = new OperatorResultList(
                $this->version,
                $this->solution['sid']
            );
        }

        return $this->_operatorResults;
    }

    /**
     * Access the sentences
     */
    protected function getSentences(): SentenceList
    {
        if (!$this->_sentences) {
            $this->_sentences = new SentenceList(
                $this->version,
                $this->solution['sid']
            );
        }

        return $this->_sentences;
    }

    /**
     * Access the media
     */
    protected function getMedia(): MediaList
    {
        if (!$this->_media) {
            $this->_media = new MediaList(
                $this->version,
                $this->solution['sid']
            );
        }

        return $this->_media;
    }

    /**
     * Magic getter to lazy load subresources
     *
     * @param string $name Subresource to return
     * @return ListResource The requested subresource
     * @throws TwilioException For unknown subresources
     */
    public function __get(string $name): ListResource
    {
        if (\property_exists($this, '_' . $name)) {
            $method = 'get' . \ucfirst($name);
            return $this->$method();
        }

        throw new TwilioException('Unknown subresource ' . $name);
    }

    /**
     * Magic caller to get resource contexts
     *
     * @param string $name Resource to return
     * @param array $arguments Context parameters
     * @return InstanceContext The requested resource context
     * @throws TwilioException For unknown resource
     */
    public function __call(string $name, array $arguments): InstanceContext
    {
        $property = $this->$name;
        if (\method_exists($property, 'getContext')) {
            return \call_user_func_array(array($property, 'getContext'), $arguments);
        }

        throw new TwilioException('Resource does not have a context');
    }

    /**
     * Provide a friendly representation
     *
     * @return string Machine friendly representation
     */
    public function __toString(): string
    {
        $context = [];
        foreach ($this->solution as $key => $value) {
            $context[] = "$key=$value";
        }
        return '[Twilio.Intelligence.V2.TranscriptContext ' . \implode(' ', $context) . ']';
    }
}
