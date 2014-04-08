<?php namespace Codenest\Ahem;

class Factory {
    
    /**
     * All of the custom notification extensions.
     *
     * @var array
     */
    protected $extensions = array();
    
    /**
     * Instance of \Codenest\Ahem\Container which holds and created notifications.
     * 
     * @var \Codenest\Ahem\Container
     */
    protected $container;
    
    /**
     * Instance of \Codenest\Ahem\Config used to access configurations.
     * 
     * @var \Codenest\Ahem\Config
     */
    protected $config;
    
    /**
     * Create new instance.
     *
     * @param \Codenest\Ahem\Container $container
     * @param \Codenest\Ahem\Config $config
     *
     * @return void
     */
    public function __construct(Container $container, Config $config )
    {
        $this->container = $container;
        $this->config = $config;
                
        $this->boot();
    }
    
    /**
     * Boot the factory.
     *
     * @return void
     */
    protected function boot()
    {
       $this->container->addTypes($this->allowedTypes());
       $this->container->boot();
    }
    
    /**
     * Make a new notification and set its messages.
     *
     * @param string $type
     * @param string|int $id
     * @param mixed $messages array, string, \Codenest\Ahem\MessageBag or \Illuminate\Support\MessageBag Instances.
     * @param bool $flashable
     *
     * @return \Codenest\Ahem\Notification
     */
    public function make($type, $id = null, $messages = array(), $flashable = true)
    {
        if(!$this->isAllowed($type, false))
            return $this->invalidTypeException($type, 'make'); 
             
        
        $id = $this->container->makeNewId($type, $id);
        $notification = new Notification($type, $id, $flashable);
        $notification = $this->extended($type) 
                            ? $this->configureExtended($notification) 
                            : $this->configureDefault($notification);
     
        $notification->addMessages($messages);
        $this->container->save($notification);
        
        return $notification;
    }
    
     /**
     * Sets the congifuration settings of the given extended notification.
     *
     * @param \Codenest\Ahem\Notification $notification
     *
     * @return \Codenest\Ahem\Notification
     */
    protected function configureExtended(Notification $notification)
    {
        if(!$this->extended($notification->getType()))
            return $this->invalidTypeException($notification->getType(), 'config Extended'); 
        
        $settings = $this->extensions[$notification->getType()]->getSettings();
        $headingKey = $this->extensions[$notification->getType()]->getHeadingKey();
        
        return  $notification->configure($settings, $headingKey);
    }
    
    /**
     * Sets the congifuration settings of the given default notification.
     *
     * @param \Codenest\Ahem\Notification $notification
     *
     * @return \Codenest\Ahem\Notification
     */
    protected function configureDefault(Notification $notification)
    {
        $settings = $this->config->getSettings($notification->getType());
        $headingKey = $this->config->getHeadingKey($notification->getType());
        
        return  $notification->configure($settings, $headingKey);
    }
    
    
    /**
     * Gets the current container instance.
     *
     * @return \Codenest\Ahem\Container
     */
    public function getContainer()
    {
        return $this->container;
    }
    
     /**
     * Gets all notifications of the given types (if provided) or all notifications in the container.
     *
     * @param string|array|null $types String for one type, Array for a number of types and null for all types.
     * @param bool $collapse Whether to collapse found notifications into a single array.
     * @param bool $idKeysOnly When collapsing, set true to use the notification ids as keys. false to use "type.id" as the keys.
     *
     * @return array An array of the found notification.
     */
    public function all($types = null, $collapse = false, $idKeysOnly = false)
    {
        return $this->container->all($types, $collapse);
    }
    
     /**
     * Gets all notifications (As an array) of the given types (if provided) or all notifications in the container.
     *
     * @param string|array|null $types String for one type, Array for a number of types and null for all types.
     * @param bool $collapse Whether to collapse found notifications into a single array.
     * @param bool $idKeysOnly When collapsing, set true to use the notification ids as keys. false to use "type.id" as the keys.
     *
     * @return array
     */
    public function allToArray($types = null, $collapse = false, $idKeysOnly = false)
    {
        $notifications = $this->all($types = null, $collapse = false, $idKeysOnly = false);
        return $this->toArray($notifications);
    }
    
     /**
     * Gets all notifications (in their JSON representation) of the given types (if provided) or all notifications in the container.
     *
     * @param string|array|null $types String for one type, Array for a number of types and null for all types.
     * @param bool $collapse Whether to collapse found notifications into a single array.
     * @param bool $idKeysOnly When collapsing, set true to use the notification ids as keys. false to use "type.id" as the keys.
     * @param  int  $options Json options
     * 
     * @return array An array of the found notification.
     */
    public function allToJson($types = null, $collapse = false, $idKeysOnly = false, $options = 0)
    {
        $notifications = $this->all($types = null, $collapse = false, $idKeysOnly = false);
        return $this->toJson($notifications, $options);
    }
    
   
    /**
     * Gets notifications of the given type and id(s) (if provided) or all notifications (of the given type) from the container.
     *
     * @param string $type
     * @param string|int|array|null $ids String or Integer for a specific notification, Array for a number of notifications and null for all notifications.
     *
     * @return \Codenest\Ahem\Notification|array
     */
    public function get($type, $ids = null)
    {
        return $this->container->get($type, $ids);
    }
    
     /**
     * Gets notifications (As an array) of the given type and id(s) (if provided) or all notifications (of the given type) from the container.
     *
     * @param string $type
     * @param string|int|array|null $ids String or Integer for a specific notification, Array for a number of notifications and null for all notifications.
     *
     * @return array
     */
    public function getArray($type, $ids = null)
    {
        $notifications = $this->get($type,$ids);
        if(is_object($notifications))
        {
            return $notifications->toArray();
        }
        elseif(is_array($notifications))
        {
            return $this->toArray($notifications);
        }
        
        return array();
        
    }
    
    /**
     * Gets notifications (in their JSON representation) of the given type and id(s) (if provided) or all notifications (of the given type) from the container.
     *
     * @param string $type
     * @param string|int|array|null $ids String or Integer for a specific notification, Array for a number of notifications and null for all notifications.
     * @param  int  $options Json options
     * 
     * @return string
     */
    public function getJson($type, $ids = null, $options = 0)
    {
        $notifications = $this->get($type,$ids);
        if(is_object($notifications))
        {
            return $notifications->toJson($options);
        }
        elseif(is_array($notifications))
        {
            return $this->toJson($notifications, $options);
        }
        else
        {
            return $this->toJson(array(), $options);
        }
    }
    
    /**
     * Determine if the container has notifications of the given type and id.
     *
     * @param string $type
     * @param string|integer|null $id String or Integer for a specific notification or null for any notification of the given $type.
     *
     * @return bool
     */    
    public function has($type, $id = null, $messageKey = null) 
    {
        return $this->container->has($type, $id, $messageKey);
    }
    
    /**
     * Get the number of notifications of the given type(s) if provided or all notifications in the container.
     *
     * @param string|array|null $types
     * 
     * @return int
     */ 
    public function count($types = null)
    {
        return $this->container->count($types);
    }
    
     /**
     * Add a new message into an existing notification.
     *
     * @param  string $type
     * @param  string|int $id
     * @param  string $message
     * @param  string|null $key
     *
     * @return void
     */
    public function addMessage($type, $id, $message, $key = null)
    {
        if(( $notification = $this->get($type, $id)))
        {
            $notification->addMessage($message, $key);
            $this->container->store($notification);
        }   
        
    }
    
    /**
     * Add new messages into an existing notification.
     *
     * @param  string $type
     * @param  string|int $id
     * @param  mixed $messages array, string, \Codenest\Ahem\MessageBag or \Illuminate\Support\MessageBag Instances.
     *
     * @return void
     */
    public function addMessages($type, $id, $messages)
    {
        if(( $notification = $this->get($type, $id) ))
        {
            $notification->addMessages($messages);
            $this->container->store($notification);
        }
    }
    
    /**
     * Remove all notifications of the given type(s) (if provided) from the container.
     *
     * @param  string|array|null $types
     * @param  bool $clearStore true to also remove them from the session store.
     *
     * @return void
     */
    public function clearAll($types = null, $clearStore = true)
    {
        return $this->container->clearAll($types, $clearStore);
    }
    
    /**
     * Remove all notifications of the given type and id(s) (if provided) from the container.
     *
     * @param  string $type
     * @param  string|array|null $ids
     * @param  bool $clearStore true to also remove them from the session store.
     *
     * @return void
     */
    public function clear($type, $ids = null, $clearStore = true)
    {
       return $this->container->clear($type, $ids, $clearStore);
    }
    
    /**
     * Remove all notifications of the given type(s) if provided or all stored notifications from the session store.
     *
     * @param  string|array|null $type
     *
     * @return void
     */
    public function clearStore($types = null)
    {
        return $this->container->clearStore($types);
    }
    
    /**
     * Remove all notifications of the given type and id(s) (if provided) from the session store.
     *
     * @param  string $type
     * @param  string|array|null $ids
     *
     * @return void
     */
    public function clearFromStore($type, $ids = null)
    {
        return $this->container->clearFromStore($type, $ids);
    }
    
    /**
     * Render all notifications of the given type(s) if provided or all notifications from the container.
     *
     * @param  string|array|null $types
     * @param  array $options html attributes. single array if options apply to all types or multidimensional fpr each type as key.
     * @param  bool $clear whether to clear the notifications from the session after rendering.
     * 
     * @return string
     */
    public function renderAll($types = null, $options = array(), $clear = true)
    {
        $notifications = $this->all($types, true);
        $html = '';
        
        foreach ($notifications as $notification) 
        {
            $option =  is_string($types) 
                        ? $this->getHtmlOptions( $notification->getId(), $options )
                        : $this->getHtmlOptions( $notification->getType(), $options );          
            $html .= $notification->render($option);
        }
        
        if($clear)
            $this->clearStore($types);
        
        
        return $html;
    }
    
    /**
     * Render all notifications of the given type(s) if provided or all notifications from the container but retain them in the seesion store.
     *
     * @param  string|array|null $types
     * @param  array $options html attributes. single array if options apply to all types or multidimensional fpr each type as key.
     *
     * @return string
     */
    public function renderAllButKeep($types = null, $options = array())
    {
        return $this->renderAll($types,$options, false);
    }
    
    /**
     * Render all notifications of the given type and id(s) if provided or all notifications of the type from the container.
     *
     * @param  string $type
     * @param  string|array|null $ids 
     * @param  array $options html attributes. single array if options apply to all types or multidimensional fpr each type as key.
     * @param  bool $clear whether to clear the notifications from the session after rendering.
     * 
     * @return string
     */
    public function render($type, $ids = null, $options = array(), $clear = true)
    {
        $html = '';
        if(is_null($ids))
        {
            $html .=  $this->renderAll($type, $options, $clear);
            return $html; 
        }
        
        if(is_array($ids))
        {
            $notifications = $this->get($type, $ids);
            foreach($notifications as $notification)
            {
                 $option = $this->getHtmlOptions( $notification->getId(), $options );          
                 $html .= $notification->render($option);
            }
            
        }
        else
        {
            $notification = $this->get($type, $ids);
            $html .= $notification->render($options);
        }
        
        if($clear)
                $this->container->clearFromStore($type, $ids);
        
        return $html;
    }
    
    
    /**
     * Render all notifications of the given type and id(s) if provided or all notifications of the type from the container but retain them in the seesion store.
     *
     * @param  string $type
     * @param  string|array|null $ids 
     * @param  array $options html attributes. single array if options apply to all types or multidimensional fpr each type as key.
     * @param  bool $clear whether to clear the notifications from the session after rendering.
     * 
     * @return string
     */
    public function renderButKeep($type, $ids = null, $options = array())
    {
        return $this->render($type, $ids, $options, false);
    }
    
    /**
     * Get the html options of a given item from the provided render $options array
     *
     * @param  string $item
     * @param  array $options
     *
     * @return array
     */
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
    
   
    /**
     * Determine if a type is defined in the cofig or Get all the defined types in the cofig.
     *
     * @param  string|null $type leave blank to get all types or give type to check if it exists; 
     *
     * @return array|bool
     */
    protected function defaultTypes($type = null)
    {
        return is_null($type) ? $this->config->defaultTypes() : in_array($type, $this->config->defaultTypes());        
    }
    
     /**
     * Determine if a type is dynamically extended or Get all the dynamically extended types.
     *
     * @param  string|null $type leave blank to get all types or give type to check if it exists; 
     *
     * @return array|bool
     */
    protected function extended($type = null)
    {
        return is_null($type) ? array_keys($this->extensions) : in_array($type, array_keys($this->extensions));
    }
    
     /**
     * Get all the types extended + those defined in the cofig.
     *
     * @param  string|null $type leave blank to get all types or give type to check if it exists 
     *
     * @return array|bool
     */
    protected function allowedTypes()
    {
        return array_unique(array_merge($this->extended(), $this->defaultTypes()));
    }
    
    /**
     * Determine if a type is extended or those defined in the cofig.
     *
     * @param  string $type
     * @param  bool $throwException Throw exception if type is not found. 
     *
     * @return bool
     */
    protected function isAllowed($type, $throwException = true)
    {
        $allowed = in_array($type, $this->allowedTypes());
        if(!$allowed && $throwException)
        {
            $this->invalidTypeException($type, 'isAllowed');
        }
        return $allowed;
    }
    
    /**
     * Throw a \Codenest\Ahem\InvalidNotificationException exception. 
     *
     * @param  string $type
     *
     * @return void
     */
    protected function invalidTypeException($type = null, $custom = '')
    {
        $message = $type == null ? 'Invalid notification type. ['. $custom .']' : ucfirst($type). ' is an invalid notification type. ['. $custom .']';
        throw new InvalidNotificationException($message);
    }

    
    /**
     * Extend an existing or define a new custom notification. 
     *
     * @param  string $type
     * @param  \Closure $closure
     * 
     * @return \Codenest\Ahem\Notification
     */
    public function extend($type, \Closure $closure)
    {
        $this->extensions[$type] = $this->configureDefault(new Notification($type));
        
        $this->container->addTypes($type);
        call_user_func($closure,  $this->extensions[$type]);
        
        return  $this->extensions[$type];
        
    }
    
    
    /**
     * Get notifications as an array.
     *
     * @param  array  $notifications
     * 
     * @return array
     */   
    public function toArray(Array $notifications)
    {
        return $this->container->toArray($notifications);
    }
    
    /**
     * Converts notifications to their JSON representation.
     *
     * @param  array  $notifications
     * @param  int  $options
     * 
     * @return string
     */
    public function toJson(Array $notifications, $options = 0)
    {
        return $this->container->toJson($notifications, $options);
    }  
    
    /**
     * Convert the notifications to its string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->container->__toString();
    }
     
    /**
     * Get the type name from a dynamic method call.
     *
     * @param  string  $method The dynamic method called.
     * @param  string  $search The string to search and replace. default = ''.
     * @param  int  $postition The expected $search string posistion. default = 0.
     * @param  string  $replace The string to replace the $search string if found. default = ''. 
     * 
     * @return string|bool $type The type name if found or false if not found.
     */  
    protected function typeFromMethod($method, $search = '', $postition = 0, $replace = '')
    {
        if(empty($search))
            return lcfirst($method);
        
        return strpos($method, $search) === $postition ? lcfirst(str_replace($search, $replace, $method)) : false;
       
    }
    
     /**
     * Get the type name if a method is dynamically callable.
     *
     * @param  string  $method
     * @param  array   $dynamicMethodVars
     *
     * @return string|bool $type The type name if found or false if not found.
     */
    protected function callableMethodType($method, Array $dynamicMethodVars)
    {
        return $this->typeFromMethod(
                                $method,
                                isset($dynamicMethodVars['search']) ? $dynamicMethodVars['search'] : '',
                                isset($dynamicMethodVars['postition']) ? $dynamicMethodVars['postition'] : 0,
                                isset($dynamicMethodVars['replace']) ? $dynamicMethodVars['replace'] : ''
                                );
    }
    
    
    /**
     * Get all the dynamically callable type methods.
     *
     * @return array
     */
    protected function dynamicallyCallable()
    {
        return array (
                    array( 'search' => 'get', 'method' => 'dynamicGet'),
                    array( 'search' => 'count', 'method' => 'dynamicCount'),
                    array( 'search' => 'has', 'method' => 'dynamicHas'),
                    array( 'search' => 'addTo', 'method' => 'dynamicAddTo'),
                    array( 'search' => 'render', 'method' => 'dynamicRender'),
                    array( 'search' => 'clearStored', 'method' => 'dynamicClearStored'),
                    array( 'search' => 'clear', 'method' => 'dynamicClear'),
                    
            );
    } 
    
    
     /**
     * Dynamically calls the 'make' method.
     *
     * @param  string  $type
     * @param  array   $parameters
     * 
     * @return mixed
     */
    protected function dynamicMake($type, $parameters)
    {
        $messages = isset($parameters[0]) ? $parameters[0] : array();
        $id = isset($parameters[1]) ? $parameters[1] : null;
        $flashable = isset($parameters[2]) ? $parameters[2] : true;
        return $this->make( $type, $id, $messages, $flashable);
    }
    
    /**
     * Dynamically calls the 'get', 'getArray' and 'getJson' methods.
     *
     * @param  string  $type
     * @param  array   $parameters
     * 
     * @return mixed
     */
    protected function dynamicGet($type, $parameters)
    {
        $id = isset($parameters[0]) ? $parameters[0] : null;
        return $this->get($type, $id);
    }
    
    /**
     * Dynamically calls the 'count' method.
     *
     * @param  string  $type
     * @param  array   $parameters
     * 
     * @return mixed
     */
    protected function dynamicCount($type, $parameters)
    {
         $id = isset($parameters[0]) ? $parameters[0] : null;
         return $this->count($type, $id);
    }
    
    /**
     * Dynamically calls the 'has' method.
     *
     * @param  string  $type
     * @param  array   $parameters
     * 
     * @return mixed
     */
    protected function dynamicHas($type, $parameters)
    {
        $id = isset($parameters[0]) ? $parameters[0] : null;
        $messageKey = isset($parameters[1]) ? $parameters[1] : null;
        return $this->has($type, $id, $messageKey);
    }
    
    /**
     * Dynamically calls the 'addMessages' method.
     *
     * @param  string  $type
     * @param  array   $parameters
     * 
     * @return mixed
     */
    protected function dynamicAddTo($type, $parameters)
    {
         $id = isset($parameters[0]) ? $parameters[0] : null;
         $messages = isset($parameters[1]) ? $parameters[1] : array();
         $key = isset($parameters[2]) ? $parameters[2] : null;
         $messages = !is_null($key) ? array($key => $messages) : $messages;
         
         return $this->addMessages($type, $id, $messages);
    }
    
    /**
     * Dynamically calls the 'render' method.
     *
     * @param  string  $type
     * @param  array   $parameters
     * 
     * @return mixed
     */
    protected function dynamicRender($type, $parameters)
    {
         $keepType = $this->typeFromMethod($type, 'butKeep');
         $clear = $keepType ? false : true;
         $type = $keepType  ?: $type;
         
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
    
    /**
     * Dynamically calls the 'clearFromStore' method.
     *
     * @param  string  $type
     * @param  array   $parameters
     * 
     * @return mixed
     */
    protected function dynamicClearStored($type, $parameters)
    {
        $id = isset($parameters[0]) ? $parameters[0] : null;
        return $this->clearFromStore($type, $id);
    }
    
    /**
     * Dynamically calls the 'clear' method.
     *
     * @param  string  $type
     * @param  array   $parameters
     * 
     * @return mixed
     */
    protected function dynamicClear($type, $parameters)
    {
         $id = isset($parameters[0]) ? $parameters[0] : null;
         $clearStore = isset($parameters[1]) ? $parameters[1] : true;
         return $this->clear($type, $id, $clearStore);
    }
    
     
     /**
     * Dynamically handle calls to this class.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        if(in_array($method, $this->allowedTypes()))
        {
           return $this->dynamicMake($this->typeFromMethod($method), $parameters);
        }
        
        foreach ($this->dynamicallyCallable() as $key => $dynamicMethodVars) 
        {
            $dynamicMethod = $dynamicMethodVars['method'];
            if( ( $type = $this->callableMethodType( $method, $dynamicMethodVars ) ) )
                 return $this->$dynamicMethod($type, $parameters);
            
        }
       
       throw new \BadMethodCallException("Method {$method} does not exist.");
        
    }
}
