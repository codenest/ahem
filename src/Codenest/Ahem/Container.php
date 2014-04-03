<?php namespace Codenest\Ahem;

use Illuminate\Session\Store;

class Container 
{
   
    protected $session;
    
    protected $config;
    
    protected $notifications = array();
    
    protected $types = array();
    
    public function __construct( Store $session, Config $config, array $types = array() )
    {
        $this->session = $session;
        $this->config = $config;
        $this->addTypes($types);
        
    }
    
    public function boot()
    {
        $this->syncStored();
    }
    
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
    
    public function store(Notification $notification)
    {
        if($notification ->isFlashable())
        {
            $stored = $this->getAllStored();
            $stored[$notification->getType()][$notification->getId()] = $notification;
            $this->flash($stored);
        }
    }
    
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
    
    protected function flash(Array $data = array())
    {
        $this->session->flash($this->config->storeKey(), $data);
    }
    
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
    
    
    public function getTypes($types = null)
    {
        if(is_null($types))
            return $this->types;
        
        return is_array($types) ? $types : func_get_args();        
    }
    
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
    
    public function add(Notification $notification)
    {
        $notification->setId($this->makeNewId($notification->getType(), $notification->getId()));        
        $this->notifications[$notification->getType()][$notification->getId()] = $notification;   
        
        $this->store($notification);
        
        return $notification; 
    }
    
    public function all($types = null, $collapse = false)
    {
        if(is_null($types))
            return $collapse ?  $this->collapse($this->notifications) : $this->notifications ;
        
        $types = $this->getTypes($types);
        $notifications = array();
        foreach ($types as $type) 
        {
            $notifications[$type] = $this->notifications[$type];    
        }
        
        return $collapse ?  $this->collapse($notifications) : $notifications ;
    }
    
    public function collapse(Array $notifications)
    {
        $collapsed = array();
        foreach($notifications as $type => $typeNotifications)
        {
            foreach ($typeNotifications as $id => $notification) 
            {
                $collapsed[$type.'.'.$id] = $notification;
            }
        }
        return $collapsed;
    }
    
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
    
     
    public function has($type, $id)
    {
        return  array_key_exists($id, $this->allIds($type));
    }
    
    public function count($type = null, $id = null)
    {
        if(!is_null($id) && !is_null($type) && !is_array($type))
            return $this->get($type, $id)->count();    
        
        $types = $this->getTypes($type);
        
        $count = 0;
        foreach($types as $type)
        {
            $count += $this->countType($type);
        }
        
        return $count;
    }
    
    public function countType($type)
    {
        return count($this->notifications[$type]);
    }
    
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
    
    public function allIds($type)
    {
        return array_keys($this->notifications[$type]);
    }
}
