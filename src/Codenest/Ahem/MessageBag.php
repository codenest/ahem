<?php namespace Codenest\Ahem;

use Illuminate\Support\MessageBag as BaseMessageBag;


class MessageBag extends BaseMessageBag {
    
    
    protected $heading = '';
    
    protected $headingKey;
    
    protected $headingFormat = ':heading';
    
    public function __construct( $headingKey = 'heading', array $messages = array())
    {
        $this->headingKey = $headingKey;
        $messages = $this->extractHeading($messages);
        foreach ($messages as $key => $value)
        {
            $this->messages[$key] = (array) $value;
        }
    }

    /**
     * Add a message to the bag.
     *
     * @param  string  $key
     * @param  string  $message
     * @return \Illuminate\Support\MessageBag
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
     * @return \Illuminate\Support\MessageBag
     */
    public function merge(array $messages)
    {
        $messages = $this->extractHeading($messages);
        
        $this->messages = array_merge_recursive($this->messages, $messages);

        return $this;
    }
    
    public function extractHeading(array $messages)
    {
       if(isset($messages[$this->headingKey]))
       {
           $this->setHeading($messages[$this->headingKey]);
           unset($messages[$this->headingKey]);
       }
       return $messages;
    }
    
    public function getHeadingKey()
    {
        return $this->headingKey;
    }
    
    public function setHeadingKey($key)
    {
        $this->headingKey = $key;
        $this->messages = $this->extractHeading($this->messages);
    }
    
    public function setHeading($heading)
    {
        $this->heading = $heading;
        return $this;
    }
    
    public function getHeading($formart = null)
    {
        $formart = is_null($formart) ? $this->headingFormat : $formart;     
        return $this->hasHeading() ? $this->transformHeading($this->heading, $formart) : '';
    }
    
    public function transformHeading($string, $format)
    {
       return str_replace(':heading', $string, $format);
    }
    
    public function hasHeading()
    {
        return empty($this->heading) ? false : true;
    }
    
    public function destroy($key)
    {
        if($this->has($key))
            unset($this->messages[$key]);
        
        return $this;
    }
}
