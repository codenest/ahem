<?php namespace Codenest\Ahem;

use Illuminate\Session\Store;
use Illuminate\Support\Contracts\JsonableInterface;
use Illuminate\Support\Contracts\ArrayableInterface;

class Container implements ArrayableInterface, JsonableInterface
{
   /**
     * Instance of \Illuminate\Session\Store used to store and access notifications in the session.
     *
     * @var \Illuminate\Session\Store
     */
    protected $session;
    
    /**
     * Instance of \Codenest\Ahem\Config used to access configurations.
     *
     * @var \Codenest\Ahem\Config
     */
    protected $config;
    
    /**
     * All of the created notifications.
     *
     * @var array
     */
    protected $notifications = array();
    
    /**
     * All the available notification types.
     *
     * @var array
     */
    protected $types = array();
    
    /**
     * Create new instance.
     *
     * @param \Illuminate\Session\Store $session
     * @param \Codenest\Ahem\Config $config
     * @param array $types
     *
     * @return void
     */
    public function __construct( Store $session, Config $config, array $types = array() )
    {
        $this->session = $session;
        $this->config = $config;
        $this->addTypes($types);
        
    }
    
    /**
     * Boot the container.
     *
     * @return void
     */
    public function boot()
    {
        $this->syncStored();
    }
    
    /**
     * Get all stored notifications and sync them with the current ones.
     *
     * @return void
     */
    protected function syncStored()
    {
        $stored = $this->getAllStored();
       
        foreach($stored as $type => $notifications)
        {
            if(!isset($this->notifications[$type]))
            {
                $this->notifications[$type] = $notifications;
                
            }
            else
            {
                foreach ($notifications as $id => $notification) 
                {
                    $this->notifications[$type][$id] = $notification;
                }    
            }
            
        }
    }
    
    /**
     * Get all stored notifications of the given type(s) or all notifications in the session if type is not provided.
     *
     * @param array\string\null $types
     * @return array
     */
    protected function getAllStored( $types = null )
    {
        $all = $this->session->get($this->config->storeKey(), array());
        
        if(is_null($types))
           return $all;
        
        $types = $this->getTypes($types);
        $stored = array();
        foreach($types as $type)
        {
             if(isset($all[$type]))
                $stored[$type] = $all[$type];
        }
        
        return $stored;
        
    }
    
    /**
     * Get all stored notifications of the given type and id(s) or all notifications in the given type if $ids is null.
     *
     * @param string $type
     * @param array\string\null $ids
     * @return array
     */
    protected function getStored($type, $ids = null)
    {
        $all = array_pop($this->getAllStored($type));
        $stored = array();
        if(is_null($ids))
        {
            $stored = $all;
        }
        elseif( is_array($ids) ) 
        {
            foreach($ids as $id)
            {
                if(isset($all[$id]))
                    $stored[$id] = $all[$id];
            }
        }
        elseif(isset($all[$ids]))
        {
            $stored = $all[$ids];
        }
                
        
        return $stored; 
    }
    
    /**
     * Store the passed notification in the session.
     *
     * @param \Codenest\Ahem\Notification $notification
     * @return void
     */
    public function store(Notification $notification)
    {
        if($notification ->isFlashable())
        {
            $stored = $this->getAllStored();
            $stored[$notification->getType()][$notification->getId()] = $notification;
            $this->flash($stored);
        }
    }
    
    /**
     * Store all the current notifications in the session.
     *
     * @return void
     */
    public function StoreAll()
    {
        foreach($this->notifications as $type => $notifications)
        {
            foreach($notifications as $notification)
            {
                $this->store($notification);
            }
        }
    }
    
    /**
     * Store the passed $data in the session.
     *
     * @param array $data
     * @return void
     */
    protected function flash(Array $data = array())
    {
        $this->session->flash($this->config->storeKey(), $data);
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
        if(is_null($types))
        {
            $this->flash();
        }
        else
        {
            $types = $this->getTypes($types);
            foreach($types as $type)
            {
                $this->clearFromStore($type);
            }
        }
        
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
        $stored = $this->getAllStored();
        if(is_null($ids) && isset($stored[$type]))
        {
           unset($stored[$type]);
        }
        elseif(is_array($ids))
        {
            foreach($ids as $id)
            {
                if(isset($stored[$type][$id]))
                    unset($stored[$type][$id]);
            }
        }
        elseif(isset($stored[$type][$ids]))
        {
            unset($stored[$type][$ids]);
        }
        
        $this->flash($stored);
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
        if($clearStore)
                $this->clearStore($types);
        
         if(is_null($types))
         {
            $this->notifications = array();
            $this->addTypes($this->types);
            return; 
         }
         
        $types = $this->getTypes($types);
        foreach($types as $type)
        {
            if(isset($this->notifications[$type]))
                 $this->notifications[$type] = array();
        }
        
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
       if(is_null($ids))
            return $this->clearAll($type, $clearStore);
        
        if($clearStore)
            $this->clearFromStore($type, $ids);
        
        if(is_array($ids))
        {
            foreach($ids as $id)
            {
                if(isset($this->notifications[$type][$id]))
                    unset($this->notifications[$type][$id]);
                
            }
        }
        elseif(isset($this->notifications[$type][$ids])) 
        {
           unset($this->notifications[$type][$ids]);
        }
        
    }
    
    /**
     * Gets all notifications types allowed or all types in the given $types parameter.
     *
     * @param string|array|null $types
     *
     * @return array
     */
    public function getTypes($types = null)
    {
        if(is_null($types))
            return $this->types;
        
        return is_array($types) ? $types : func_get_args();        
    }
    
    /**
     * Adds notifications type(s).
     *
     * @param string|array $types
     *
     * @return void
     */
    public function addTypes($types)
    {
        $types = is_array($types) ? $types : func_get_args();
        $diff = array_diff($types, $this->types);
        $this->types = array_merge($this->types, $diff);
        foreach($types as $type)
        {
            if(!isset($this->notifications[$type]))
                $this->notifications[$type] = array();
        }   
    }
    
    /**
     * Adds the given notification to the notifications array.
     *
     * @param \Codenest\Ahem\Notification $notification
     *
     * @return \Codenest\Ahem\Notification The saved notification
     */
    public function save(Notification $notification)
    {
        if($notification->getId() == null)
            $notification->setId($this->makeNewId($notification->getType(), $notification->getId()));        
        
        $this->notifications[$notification->getType()][$notification->getId()] = $notification;   
        
        $this->store($notification);
        
        return $notification; 
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
        if(is_null($types))
            return $collapse ?  $this->collapse($this->notifications, $idKeysOnly) : $this->notifications ;
        
        $types = $this->getTypes($types);
        $notifications = array();
        foreach ($types as $type) 
        {
            $notifications[$type] = $this->notifications[$type];    
        }
        
        return $collapse ?  $this->collapse($notifications, $idKeysOnly) : $notifications ;
    }
    
    /**
     * Collapse the given notifications into a single array.
     *
     * @param array $notifications
     * @param bool $idKeysOnly true to use the notification ids as keys. false to use "type.id" as the keys.
     *
     * @return array
     */
    public function collapse(Array $notifications, $idKeysOnly = false)
    {
        $collapsed = array();
        foreach($notifications as $type => $typeNotifications)
        {
            if($idKeysOnly)
            {
                $collapsed = array_merge($collapsed, $typeNotifications);
            }
            else
            {
               foreach ($typeNotifications as $id => $notification) 
               {
                    $collapsed[$type.'.'.$id] = $notification;
               } 
            }
            
            
        }
        return $collapsed;
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
        $all = $this->all($type);
        $all = array_pop($all);
        $notifications = array();
        if(is_null($ids))
        {
            $notifications = $all;
        }    
        elseif(is_array($ids))
        {
            foreach($ids as $id)
            {
                if(isset($all[$id]))
                    $notifications[$id] = $all[$id];
            }
        }
        else
        {
            $notifications = isset($all[$ids]) ? $all[$ids] : null;
        }
        
        return $notifications;
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
        if(is_null($id))
            return $this->countType($type) > 0 ? true  : false;
        
        
        $notificationExists = array_key_exists($id, $this->allIds($type));
        if(is_null($messageKey))
           return  $notificationExists;
        
        
        return isset($this->notifications[$type][$id]) ? $this->notifications[$type][$id]->has($messageKey) : false;
        
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
        $types = $this->getTypes($types);
        
        $count = 0;
        foreach($types as $type)
        {
            $count += $this->countType($type);
        }
        
        return $count;
    }
    
    /**
     * Get the number of notifications of the given type.
     *
     * @param string $type
     * 
     * @return int
     */ 
    public function countType($type)
    {
        return count($this->notifications[$type]);
    }
    
    /**
     * Get the id of a new notification.
     *
     * @param string $type
     * @param string|int|null $id If string or int, $id is returned irregardless of whether it's unique or not. If null a new unique interger id is generated
     * 
     * @return int|string
     */ 
    public function makeNewId($type, $id = null)
    {
        if(!is_null($id))
            return $id;
        
        $allIds = $this->allIds($type);
        $maxId = !empty($allIds) ? max($allIds) : null;
        $newId = is_numeric($maxId) ? $maxId + 1: 0;
        
        while($this->has($type, $newId))
        {
            $newId++;
        }
        return $newId;
        
    }
    
    /**
     * Get all the ids of available notifications in the given type.
     *
     * @param string $type
     *
     * @return array
     */ 
    protected function allIds($type)
    {
        return array_keys($this->notifications[$type]);
    }
    
    /**
     * Get notifications as an array.
     *
     * @param  array|null  $notifications Pass the notification array to convert or null to convert all notifications in the container.
     * @return array
     */
    public function toArray($notifications = null)
    {
        $notifications = $notifications ?: $this->notifications;
        $array = array();
        if(!is_array($notifications))
            return $array;
        
        foreach($notifications as $key => $values)
        {
            if(is_array($values))
            {
                $array[$key] = $this->toArray($values);
            }
            elseif(is_object($values))
            {
                $array[$key] = $values->toArray();
            }
            
        }
        return $array;
        
    }

    /**
     * Converts notifications to their JSON representation.
     *
     * @param  array|null  $notifications Pass the notification array to convert or null to convert all notifications in the container.
     * @param  int  $options
     * 
     * @return string
     */
    public function toJson($notifications = null, $options = 0)
    {
        return json_encode($this->toArray($notifications), $options);
    }

    /**
     * Convert the notifications to its string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }
    
}
