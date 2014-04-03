<?php namespace Codenest\Ahem;

class Notification {
    
    protected $id = null;
    
    protected $type = null;
    
    protected $settings = array();
    
    protected $flashable = true;
    
    /* MessageBag Instance.
     * 
     * @var \Codenest\Ahem\MessageBag
     */
    protected $messages;
   
    public function __construct( $type, $id = null, $messages = array(), $flashable = true )
    {
        $this->type = $type;
        $this->id = $id;
        $this->flashable = $flashable;
        $this->messages = new MessageBag();
        $this->addMessages($messages);
               
    }
    
    public function isFlashable($flashable = null)
    {
        $this->flashable = is_null($flashable) ? $this->flashable : $flashable;
        return $this->flashable;
    }
    
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
    
    public function setType($type)
    {
        $this->type = $type;
    }
    
    public function getType()
    {
        return $this->type;
    }
    
    public function setHeading($heading)
    {
        $this->messages->setHeading($heading);
        return $this;
    }
    
    public function getHeading()
    {
        $this->messages->getHeading();
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function getSettings()
    {
        return $this->settings;
    }
    
    /**
     * Get the raw messages in the container.
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->messages->getMessages();
    }

    /**
     * Get the messages for the instance.
     *
     * @return \Codenest\Ahem\MessageBag
     */
    public function getMessageBag()
    {
        return $this->messages;
    }
    
    public function getHeadingKey()
    {
        return $this->messages->getHeadingKey();
    }
    
    
    public function addMessages($messages = array())
    {
        if($messages instanceof MessageBag)
        {
            
            $messagesArray = $messages->getMessages();
            $this->messages->merge($messagesArray);
            
            if($messages->hasHeading())
                $this->setHeading($messages->getHeading());
            
        }
        elseif($messages instanceof Illuminate\Support\MessageBag)
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
                
    }
    
    public function addMessage($message, $key = null)
    {
        if($key == null)
           return  $this->addMessages(array($message));   
        
        $this->messages->add($key, $message);
             
    }
    
    public function count()
    {
        return $this->messages->count();
    }
    
    public function configure(array $settings = array(), $headingKey = null)
    {
        foreach ($settings as $key => $value) 
        {
            $this->settings[$key] = $value;
        }
        
        if(!is_null($headingKey))
            return $this->headingKey($headingKey);
        
        return $this;
    }
   
    public function headingKey($key)
    {
        $this->messages->setHeadingKey($key);
        return $this;
    }
    public function wrapper($wrap, $class = null)
    {
        $this->settings['wrapper'] = $wrap;
        if($class != null)
            $this->wrapperClass($class);
        
        return $this;
    }
    
    public function wrapperClass($class)
    {
        $this->settings['wrapper_class'] = $class;
        return $this;
    }
    
    public function closeButton($html)
    {
        $this->settings['close_button'] = $html;
        return $this;
    }
    
    public function singleMessage($format)
    {
        $this->settings['single_message'] = $format;
        return $this;
    }
    
    public function heading($format)
    {
        $this->settings['heading'] = $format;
        return $this;
    }
    
    public function messageListWrapper($wrap, $class = null)
    {
        $this->settings['message_list_wrapper'] = $wrap;
        if($class !== null)
           return $this->messageListWrapperClass($class);
        
        return $this;
        
    }
    
    public function messageListWrapperClass($class)
    {
        $this->settings['message_list_wrapper_class'] = $class;
        return $this;
    }
    
    public function messageList($format)
    {
        $this->settings['message_list'] = $format;
        return $this;
    }
    
    public function render($options = array(), $close = true)
    {
        $html  = $this->openHtml($options);
        $html .= $close ? $this->closeButtonHtml() : '';
        $html .= $this->messagesHtml();
        $html .= $this->closeHtml();
        return $html;
    }
    
    public function htmlAttributes($options)
    {
        $html = ' ';
        foreach ($options as $key => $value) 
        {
            $html .= $key . '="' . $value . '" ';
        }
        return $html;
    }
    
    public function openHtml($options = array())
    {
        $options['class'] = isset($options['class']) ? $options['class'] : $this->settings['wrapper_class'];
        return '<' . $this->settings['wrapper'] . ' '. $this->htmlAttributes($options) .'>';
    }
    
    public function closeHtml()
    {
        return '</' . $this->settings['wrapper'] . '>';
    }
    
    public function closeButtonHtml()
    {
        return $this->settings['close_button'];
    }
    
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
            $html .= $this->headingHtml($messages);
            $html .= $this->messageListHtml($messages);
        }
        return $html;
    }
    
    public function singleMessageHtml($messages = null)
    {
        $messages = $messages ?: $this->messages;
        return $messages->first(null, $this->settings['single_message'] );
    }
    
    public function headingHtml($messages = null)
    {
        $messages = $messages ?: $this->messages;
        return $this->messages->getHeading($this->settings['heading']);
    }
    
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
    
    public function openMessageListHtml()
    {
        return  !empty($this->settings['message_list_wrapper']) 
                    ? '<' . $this->settings['message_list_wrapper'] . ' class="'. $this->settings['message_list_wrapper_class'] .'">'
                    : '';
    }
    
    public function closeMessageListHtml()
    {
        return  !empty($this->settings['message_list_wrapper']) 
                    ? '</' . $this->settings['message_list_wrapper'] . '>'
                    : '';
    }
}
