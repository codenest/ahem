<?php namespace Codenest\Ahem;

use Illuminate\Support\MessageBag as BaseMessageBag;


class MessageBag extends BaseMessageBag {
    
    /**
     * Heading of the current bag.
     *
     * @var string
     */
    protected $heading = '';
    
    /**
     * Heading array key.
     *
     * @var string
     */
    protected $headingKey;
    
    /**
     * Heading format.
     *
     * @var string
     */
    protected $headingFormat = ':heading';
    
    /**
     * Create new instance.
     *
     * @param string $headingKey
     * @param array $messages
     * @return void
     */
    public function __construct( $headingKey = 'heading', array $messages = array())
    {
        $this->headingKey = $headingKey;
        $messages = $this->extractHeading($messages);
        
        parent::__construct($messages);
    }

    /**
     * Add a message to the bag.
     *
     * @param  string  $key
     * @param  string  $message
     * @return \Codenest\Ahem\MessageBag
     */
    public function add($key, $message)
    {
        if($key == $this->headingKey)
            return $this->setHeading($message);
        
        if ($this->isUnique($key, $message))
        {
            $this->messages[$key][] = $message;
        }

        return $this;
    }

    /**
     * Merge a new array of messages into the bag.
     *
     * @param  array  $messages
     * @return \Codenest\Ahem\MessageBag
     */
    public function merge(array $messages)
    {
        $messages = $this->extractHeading($messages);
        
        $this->messages = array_merge_recursive($this->messages, $messages);

        return $this;
    }
    
    /**
     * Extact and set the bag's heading from an array of messages.
     *
     * @param  array  $messages
     * @return array
     */
    public function extractHeading(array $messages)
    {
       if(isset($messages[$this->headingKey]))
       {
           $this->setHeading($messages[$this->headingKey]);
           unset($messages[$this->headingKey]);
       }
       return $messages;
    }
    
    /**
     * Get the heading array key.
     *
     * @return string
     */
    public function getHeadingKey()
    {
        return $this->headingKey;
    }
    
    /**
     * Set the heading array key.
     *
     * @param  string  $key
     * @return void
     */
    public function setHeadingKey($key)
    {
        $this->headingKey = $key;
        $this->messages = $this->extractHeading($this->messages);
    }
    
    /**
     * Set the heading.
     *
     * @param  string  $heading
     * @return \Codenest\Ahem\MessageBag
     */
    public function setHeading($heading)
    {
        $this->heading = $heading;
        return $this;
    }
    
    /**
     * Get the heading array key.
     *
     * @param string $format custom format.
     * @return string
     */
    public function getHeading($formart = null)
    {
        $formart = is_null($formart) ? $this->headingFormat : $formart;     
        return $this->hasHeading() ? $this->transformHeading($this->heading, $formart) : '';
    }
    
    /**
     * Format the heading.
     *
     * @param string $heading
     * @param string $format
     * @return string
     */
    protected function transformHeading($heading, $format)
    {
       return str_replace(':heading', $heading, $format);
    }
    
     /**
     * Determine if the message bag has an heading.
     *
     * @return bool
     */
    public function hasHeading()
    {
        return empty($this->heading) ? false : true;
    }
    
     /**
     * Remove a message from the bag.
     *
     * @param string $key
     * @return \Codenest\Ahem\MessageBag
     */
    public function destroy($key)
    {
        if($this->has($key))
            unset($this->messages[$key]);
        
        return $this;
    }
}
