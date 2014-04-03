<?php namespace Codenest\Ahem;

use Illuminate\Session\Store;

class Factory {
    
    /**
     * All of the custom notification extensions.
     *
     * @var array
     */
    protected $extensions = array();
    
    /**
     * 
     * 
     */
    protected $container;
    
    protected $config;
    
    public function __construct(Container $container, Config $config )
    {
        $this->container = $container;
        $this->config = $config;
                
        $this->boot();
    }
    
    protected function boot()
    {
       $this->container->addTypes($this->allowedTypes());
       $this->container->boot();
    }
    
    public function make($type, $id = null, $messages = array(), $flashable = true)
    {
        if(!$this->isAllowed($type, false))
             return $this->invalidTypeException($type); 
        
        $notification = new Notification($type, $id, $messages, $flashable);
        $notification = $this->extended($type) 
                            ? $this->configureExtended($notification) 
                            : $this->configureDefault($notification);
     
        $this->container->add($notification);
        
        return $notification;
    }
    
    public function configureExtended(Notification $notification)
    {
        $settings = $this->extensions[$notification->getType()]->getSettings();
        $headingKey = $this->extensions[$notification->getType()]->getHeadingKey();
        
        return  $notification->configure($settings, $headingKey);
    }
    
    public function configureDefault(Notification $notification)
    {
        $settings = $this->config->getSettings($notification->getType());
        $headingKey = $this->config->getHeadingKey($notification->getType());
        
        return  $notification->configure($settings, $headingKey);
    }
    
    public function addMessage($type, $id, $message, $key = null)
    {
        if(( $notification = $this->get($type, $id)))
        {
            $notification->addMessage($message, $key);
            $this->container->store($notification);
        }   
        
    }
    
    public function mergeMessages($type, $id, array $messages)
    {
        if(( $notification = $this->get($type, $id)))
        {
            $notification->addMessages($message);
             $this->container->store($notification);
        }   
    }
    
    public function all($types = null, $collapse = false)
    {
        return $this->container->all($types, $collapse);
    }
    
    public function get($type, $id = null)
    {
        return $this->container->get($type, $id);
    }
    
    public function has($type, $id)
    {
        return $this->container->has($type, $id);
    }
    
    public function count($type = null, $id = null)
    {
        return $this->container->count($type, $id);
    }
       
    public function renderAll($types = null, $options = array(), $clear = true, $close = true)
    {
        $notifications = $this->all($types, true);
        $html = '';
        
        foreach ($notifications as $notification) 
        {
            $option =  is_string($types) 
                        ? $this->getHtmlOptions( $notification->getId(), $options )
                        : $this->getHtmlOptions( $notification->getType(), $options );          
            $html .= $notification->render($option, $close);
        }
        
        if($clear)
            $this->clearStore($types);
        
        
        return $html;
    }
    
    public function renderAllAndKeep($types = null, $options = array(), $close = true)
    {
        return $this->renderAll($types,$options, false, $close);
    }
    
    public function render($type, $ids = null, $options = array(), $clear = true, $close = true)
    {
        $html = '';
        if(is_null($ids))
        {
            $html .=  $this->renderAll($type, $options, $close, $clear);
            return $html; 
        }
        
        if(is_array($ids))
        {
            $notifications = $this->get($type, $ids);
            foreach($notifications as $notification)
            {
                 $option = $this->getHtmlOptions( $notification->getId(), $options );          
                 $html .= $notification->render($option, $close);
            }
            
        }
        else
        {
            $notification = $this->get($type, $ids);
            $html .= $notification->render($options, $close);
        }
        
        if($clear)
                $this->container->clearFromStore($type, $ids);
        
        return $html;
    }
    
    public function renderAndKeep($type, $ids = null, $options = array(), $close = true)
    {
        return $this->render($type, $ids, $options, false, $close);
    }
    
    public function clearAll($types = null, $clearStore = true)
    {
        return $this->container->clearAll($types, $clearStore);
    }
    
    public function clear($type, $ids = null, $clearStore = true)
    {
       return $this->container->clear($type, $ids, $clearStore);
    }
    
    public function clearStore($types = null)
    {
        return $this->container->clearStore($types);
    }
    
    public function clearFromStore($type, $ids = null)
    {
        return $this->container->clearFromStore($type, $ids);
    }
    
    protected function getHtmlOptions($item, $options)
    {
        $itemOptions = isset($options[$item]) ? $options[$item] : array();
        if(!empty($itemOptions))
            return $itemOptions;
        
        foreach($options as $key => $option)
        {
            if(is_array($option))
                continue;
            $itemOptions[$key] = $option;
        } 
        
        return $itemOptions;
    }
    
    public function extend($type, \Closure $closure)
    {
        $this->extensions[$type] = $this->configureDefault(new Notification($type));
        
        $this->container->addTypes($type);
        call_user_func($closure,  $this->extensions[$type]);
        
        return  $this->extensions[$type];
        
    }
    
    protected function defaultTypes($type = null)
    {
        return is_null($type) ? $this->config->defaultTypes() : in_array($type, $this->config->defaultTypes());        
    }
    
    protected function extended($type = null)
    {
        return is_null($type) ? array_keys($this->extensions) : in_array($type, array_keys($this->extensions));
    }
    
    protected function allowedTypes()
    {
        return array_unique(array_merge($this->extended(), $this->defaultTypes()));
    }
    
    protected function isAllowed($type, $throwException = true)
    {
        $allowed = in_array($type, $this->allowedTypes());
        if(!$allowed && $throwException)
        {
            $this->invalidTypeException($type);
        }
        return $allowed;
    }
    
    protected function invalidTypeException($type = null)
    {
        $message = $type == null ? 'Invalid notification type.' : ucfirst($type). ' is an invalid notification type.';
        throw new InvalidNotificationException($message);
    }
    
    
    protected function typeFromMethod($method, $search = '', $postition = 0, $replace = '', $lcFirst = true)
    {
        if(empty($search))
            return $lcFirst ? lcfirst($method) : $method;
        
        $method = strpos($method, $search) === $postition ? str_replace($search, $replace, $method) : false;
        return is_string($method) && $lcFirst ? lcfirst($method) : $method;
        
    }
   
    
    public function __call($method, $parameters)
    {
        if(in_array($method, $this->allowedTypes()))
        {
            $messages = isset($parameters[0]) ? $parameters[0] : array();
            $id = isset($parameters[1]) ? $parameters[1] : null;
            $flashable = isset($parameters[2]) ? $parameters[2] : true;
            return $this->make($this->typeFromMethod($method), $id, $messages, $flashable);
        }
        elseif( ( $type = $this->typeFromMethod($method, 'get') ) )
        {
            $id = isset($parameters[0]) ? $parameters[0] : null;
            return $this->get($type, $id);
        }
        elseif( ( $type = $this->typeFromMethod($method, 'count') ) && !$this->typeFromMethod($method, 'count') )
        {
             $id = isset($parameters[0]) ? $parameters[0] : null;
             return $this->count($type, $id);
        }
        elseif(( $type = $this->typeFromMethod($method, 'has') ))
        {
             $id = isset($parameters[0]) ? $parameters[0] : null;
             return $this->has($type, $id);
        }
        elseif( ( $type = $this->typeFromMethod($method, 'addTo') ) )
        {
             $id = isset($parameters[0]) ? $parameters[0] : null;
             $message = isset($parameters[1]) ? $parameters[1] : null;
             $key = isset($parameters[2]) ? $parameters[2] : null;
             return $this->addMessage($type, $id, $message, $key);
        }
        elseif( ( $type = $this->typeFromMethod($method, 'merge') ) )
        {
             $id = isset($parameters[0]) ? $parameters[0] : null;
             $messages = isset($parameters[1]) ? $parameters[1] : array();
             return $this->mergeMessages($type, $id, $messages);
        }
        elseif( ( $method = $this->typeFromMethod($method, 'render' ) ) )
        {
             $keepType = $this->typeFromMethod($method, 'andKeep');
             $clear = $keepType ? false : true;
             $type = $keepType  ?: $method;
             
             $id = isset($parameters[0]) && !is_array($parameters[0]) ? $parameters[0] : null;
             $options = array();
             $close = true;
             if(isset($parameters[0]) && is_array($parameters[0]))
             {
                 $options = $parameters[0];
                 $close = isset($parameters[1]) ? $parameters[1] : $close;
             }
             elseif(isset($parameters[0]) && !is_array($parameters[0]))
             {
                 $options = isset($parameters[1]) ? $parameters[1] : $options;
                 $close = isset($parameters[2]) ? $parameters[2] : $close;
             }
             
             return $this->render($type, $id, $options, $clear, $close);
        }
        elseif( ( $type = $this->typeFromMethod($method, 'clearStored') ) )
        {
             $id = isset($parameters[0]) ? $parameters[0] : null;
             return $this->clearFromStore($type, $id);
        }
        elseif( ( $type = $this->typeFromMethod($method, 'clear') ) )
        {
             $id = isset($parameters[0]) ? $parameters[0] : null;
             $clearStore = isset($parameters[1]) ? $parameters[1] : true;
             return $this->clear($type, $id, $clearStore);
        }
    }
}
