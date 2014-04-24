<?php namespace Codenest\Ahem;

use Illuminate\Support\MessageBag;
use Illuminate\Support\Contracts\JsonableInterface;
use Illuminate\Support\Contracts\ArrayableInterface;

class Notification implements ArrayableInterface, JsonableInterface {
    
    /** 
     * Notification's unique id.
     * 
     * @var string|integer
     */
    protected $id = null;
    
    /** 
     * The type of the notification.
     * 
     * @var string
     */
    protected $type = null;
    
    /** 
     * Notification's settings.
     * 
     * @var array
     */
    protected $settings = array();
    
    /** 
     * Indicates if the notification should be flashed to the session.
     * 
     * @var bool
     */
    protected $flashable = true;
    
    /** 
     * The notification heading.
     * 
     * @var string
     */
    protected $heading = '';
    
    /** 
     * MessageBag Instance.
     * 
     * @var Illuminate\Support\MessageBag
     */
    protected $messages;
   
   /**
     * Create new instance.
     *
     * @param string $type
     * @param array $id
     *
     * @return void
     */
    public function __construct( $type, $id = null )
    {
        $this->type = $type;
        $this->id = $id;
        $this->messages = new MessageBag();               
    }
    
    /**
     * Set the flashable status.
     *
     * @param bool $flashable
     * @return \Codenest\Ahem\Notification
     */
    public function flashable($flashable)
    {
        $this->flashable = $flashable;
        return $this;
    }

    /**
     * Determine if this notification is flashable.
     *
     * @return bool
     */
    public function isFlashable()
    {
        return $this->flashable;
    }
    
    /**
     * Set the Id.
     *
     * @param string|integer $id
     * @return \Codenest\Ahem\Notification
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
    
    /**
     * Get the id.
     *
     * @return string|integer
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Set the notification type.
     *
     * @param string $type
     * @return \Codenest\Ahem\Notification
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }
    
    /**
     * Get the notification type.
     *
     * @return string type
     */
    public function getType()
    {
        return $this->type;
    }
    
    /**
     * Set the notification heading.
     *
     * @param string $heading
     * @return \Codenest\Ahem\Notification
     */
    public function heading($heading)
    {
        $this->heading = $heading;
        return $this;
    }
    
    /**
     * Get the notification heading.
     *
     * @return string
     */
    public function getHeading()
    {
        $this->heading;
    }
    
    /**
     * Get the notification settings.
     *
     * @return array
     */    
    public function getSettings()
    {
        return $this->settings;
    }
    
    /**
     * Get the raw messages in the message bag.
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->messages->getMessages();
    }

    /**
     * Get the MessageBag instance.
     *
     * @return \Illuminate\Support\MessageBag
     */
    public function getMessageBag()
    {
        return $this->messages;
    }
        
    /**
     * Add new messages into the bag.
     *
     * @param  mixed $messages array, string, \Codenest\Ahem\MessageBag or \Illuminate\Support\MessageBag Instances.
     * @return \Codenest\Ahem\Notification
     */
    public function messages($messages = array())
    {
        if(is_object($messages))
        {
             $this->messages->merge($messages->getMessages());
        }
        elseif(is_array($messages))
        {
            $this->messages->merge($messages);
        }
        else
        {
            $this->messages->merge(array($messages));
        }

        return $this;
                
    }
    

    /**
     * Add new a message into the bag.
     *
     * @param  mixed $messages array, string, \Codenest\Ahem\MessageBag or \Illuminate\Support\MessageBag Instances.
     * @return \Codenest\Ahem\Notification
     */
    public function message($message)
    {
        return $this->messages($message);
    }
    /**
     * Add a new message into the bag.
     *
     * @param  string $message
     * @param  string $key
     * @return \Codenest\Ahem\Notification
     */
    public function addMessage($message, $key = null)
    {
        if($key == null)
           return  $this->addMessages(array($message));   
        
        $this->messages->add($key, $message);
        return $this;
    }
    
    /**
     * Get the number of messages in the bag.
     *
     * @return int
     */
    public function count()
    {
        return $this->messages->count();
    }
    
    /**
     * Set the notification settings.
     *
     * @param  array $settings
     *
     * @return \Codenest\Ahem\Notification
     */
    public function configure(array $settings = array())
    {
        foreach ($settings as $key => $value) 
        {
            $this->settings[$key] = $value;
        }

        return $this;
    }
   
    
    /**
     * Set the notification's HTML wrapper element and its css class value.
     *
     * @param  string $wrapper
     * @param  string $class
     * @return \Codenest\Ahem\Notification
     */
    public function wrapper($wrapper, $class = null)
    {
        $this->settings['wrapper'] = $wrapper;
        if($class != null)
            $this->wrapperClass($class);
        
        return $this;
    }
    
    /**
     * Set the notification's HTML wrapper css class value.
     *
     * @param  string $class
     * @return \Codenest\Ahem\Notification
     */
    public function wrapperClass($class)
    {
        $this->settings['wrapper_class'] = $class;
        return $this;
    }
    
    /**
     * Set the HTML to be rendered before the message(s).
     *
     * @param  string $html
     * @return \Codenest\Ahem\Notification
     */
    public function beforeMessage($html)
    {
        $this->settings['before_message'] = $html;
        return $this;
    }
    
    /**
     * Set the single messages display format.
     *
     * @param  string $format
     * @return \Codenest\Ahem\Notification
     */
    public function singleMessage($format)
    {
        $this->settings['single_message'] = $format;
        return $this;
    }
    
    /**
     * Set the messages heading display format.
     *
     * @param  string $format
     * @return \Codenest\Ahem\Notification
     */
    public function headingFormat($format)
    {
        $this->settings['heading'] = $format;
        return $this;
    }
    
    /**
     * Set the message list wrapper and its css class value.
     *
     * @param  string $wrap
     * @param  string|null $class
     * @return \Codenest\Ahem\Notification
     */
    public function messageListWrapper($wrap, $class = null)
    {
        $this->settings['message_list_wrapper'] = $wrap;
        if($class !== null)
           return $this->messageListWrapperClass($class);
        
        return $this;
        
    }
    
     /**
     * Set the message list wrapper css class value.
     *
     * @param  string $class
     * @return \Codenest\Ahem\Notification
     */
    public function messageListWrapperClass($class)
    {
        $this->settings['message_list_wrapper_class'] = $class;
        return $this;
    }
    
    /**
     * Set the display format of message list item.
     *
     * @param  string $class
     * @return \Codenest\Ahem\Notification
     */
    public function messageList($format)
    {
        $this->settings['message_list'] = $format;
        return $this;
    }
    
    /**
     * Set the HTML to be rendered after the message(s).
     *
     * @param  string $html
     * @return \Codenest\Ahem\Notification
     */
    public function afterMessage($html)
    {
        $this->settings['after_message'] = $html;
        return $this;
    }
    
    /**
     * Render the notification html with all messages.
     *
     * @param  array $options
     * @return string
     */
    public function render($options = array())
    {
        $html  = $this->openHtml($options);
        $html .= $this->beforeMessageHtml();
        $html .= $this->messagesHtml();
        $html .= $this->afterMessageHtml();
        $html .= $this->closeHtml();
        return $html;
    }
    

    /**
     * Render the notification's heading.
     *
     * @param  array $options
     * @return string
     */
    public function renderHeading($options = array())
    {
        $html  = $this->openHtml($options);
        $html .= $this->beforeMessageHtml();
        $html .= $this->headingHtml();
        $html .= $this->afterMessageHtml();
        $html .= $this->closeHtml();
        return $html;
    }

    /**
     * Open a new html notification.
     *
     * @param  array $options
     * @return string
     */
    public function openHtml($options = array())
    {
        $options['class'] = isset($options['class']) ? $options['class'] : $this->settings['wrapper_class'];
        return '<' . $this->settings['wrapper'] . ' '. $this->htmlAttributes($options) .'>';
    }
    
     /**
     * Close the current notification.
     *
     * @return string
     */
    public function closeHtml()
    {
        return '</' . $this->settings['wrapper'] . '>';
    }
    
    /**
     * Get the html to be rendered before the messages.
     *
     * @return string
     */
    public function beforeMessageHtml()
    {
        return $this->settings['before_message'];
    }
    
    /**
     * Get the html to be rendered after the messages.
     *
     * @return string
     */
    public function afterMessageHtml()
    {
        return $this->settings['after_message'];
    }
    
    
    /**
     * Get all the messages html.
     *
     * @param Codenest\Ahem\MessageBag|null $messages
     * @return string
     */
    public function messagesHtml($messages = null)
    {
        $messages = is_null($messages) ? $this->messages : $messages;
        $html = '';
        if($messages->count() < 2 && $messages->any() && !$messages->hasHeading())
        {
            $html .= $this->singleMessageHtml($messages);
        }
        else
        {
            $html .= $this->headingHtml();
            $html .= $this->messageListHtml($messages);
        }
        return $html;
    }
    
    /**
     * Get a single message's html.
     *
     * @param Codenest\Ahem\MessageBag|null $messages
     * @return string
     */
    public function singleMessageHtml($messages = null)
    {
        $messages = $messages ?: $this->messages;
        return $messages->first(null, $this->settings['single_message'] );
    }
    
    /**
     * Get the messages' heading html.
     *
     * @param Codenest\Ahem\MessageBag|null $messages
     * @return string
     */
    public function headingHtml()
    {
        return !empty($this->heading) ? str_replace(':heading', $this->heading, $this->settings['heading']) : '';
    }
    
    /**
     * Get the entire message list html.
     *
     * @param Codenest\Ahem\MessageBag|null $messages
     * @return string
     */
    public function messageListHtml($messages = null)
    {
        $messages = $messages ?: $this->messages;
        if($messages->isEmpty())
            return '';
        
        $html = $this->openMessageListHtml();
        foreach($messages->all($this->settings['message_list']) as $message)
        {
            $html .= $message;
        }
        $html .= $this->closeMessageListHtml();
        return $html;
    }
    
    /**
     * Open a new message list.
     *
     * @return string
     */
    public function openMessageListHtml()
    {
        return  !empty($this->settings['message_list_wrapper']) 
                    ? '<' . $this->settings['message_list_wrapper'] . ' class="'. $this->settings['message_list_wrapper_class'] .'">'
                    : '';
    }
    
    /**
     * Close the current message list.
     *
     * @return string
     */
    public function closeMessageListHtml()
    {
        return  !empty($this->settings['message_list_wrapper']) 
                    ? '</' . $this->settings['message_list_wrapper'] . '>'
                    : '';
    }
    
    /**
     * Get html attributes string from array.
     *
     * @param  array $options
     * @return string
     */
    protected function htmlAttributes(array $options)
    {
        $html = ' ';
        foreach ($options as $key => $value) 
        {
            $html .= $key . '="' . $value . '" ';
        }
        return $html;
    }
    
    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return array( 
                'id'        => $this->id,
                'type'      => $this->type,
                'settings'  => $this->settings,
                'heading'   => $this->heading,
                'messages'  => $this->messages->toArray() 
                );
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Convert the message bag to its string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }
    
    /**
     * Dynamically calls the current MessageBag's class methods.
     *
     * @param  string  $method
     * @param  array   $parameters
     * 
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array(array($this->messages, $method), $parameters);
    }
}
