<?php namespace Codenest\Ahem;

use Illuminate\Config\Repository;

class Config 
{
    /**
     * An instance of \Illuminate\Config\Repository.
     *
     * @var Illuminate\Config\Repository
     */
    protected $repository;
    
    /**
     * Config key for the session key value used to store flashed notifications.
     *
     * @var string
     */
    protected $sessionKey = 'session_key';
        
    
    /**
     * Config key for the notification settings.
     *
     * @var string
     */
    protected $settingsKey = 'settings';
    
    /**
     * Config key for the default notification settings.
     *
     * @var string
     */
    protected $defaultSettingsKey = 'default_settings';
    
    /**
     * Create new instance.
     *
     * @param Illuminate\Config\Repository $repository
     * $return void
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }
    
    /**
     * Dynamically retrieve config key values.
     *
     * @param  string  $property
     * @return string
     */
    public function __get($property)
    {
        $protected = array( 'repository' );
        if(!in_array($property, $protected))
            return $this->$property;
    }
    
    /**
     * Dynamically set config key values.
     *
     * @param  string  $property
     * @param  string  $value
     * @return void
     */ 
    public function __set($property, $value)
    {
        $protected = array( 'repository' );
        if(!in_array($property, $protected))
            $this->$property = $value;
    }
    
    /**
     * Appends options to 'ahem::' with a dot(.) notation to create a complete config 'query' string.
     * 
     * @param  string  $options 
     * @return string
     */ 
    protected function repoOptionString()
    {
        $str = 'ahem::';
        $args = func_get_args();
        foreach ($args as $arg) 
        {
            $str .= !empty($arg) ? $arg.'.' : '';
        }
        $str = rtrim($str,'.');
        return $str;
        
    }
    
    /**
     * Retrieves the session key value from config.
     *
     * @return string
     */
    public function storeKey()
    {
        return $this->repository->get($this->repoOptionString($this->sessionKey));
    }
    
    /**
     * Retrieves the default notification types.
     *
     * @return array
     */
    public function defaultTypes()
    {
        $types = array_keys($this->repository->get($this->repoOptionString($this->settingsKey), array()));
        return array_diff($types, array($this->defaultSettingsKey));
    }
    
    /**
     * Retrieves the default notification settings.
     *
     * @return array
     */
    public function defaultSettings()
    {
        return $this->repository->get( $this->repoOptionString($this->settingsKey, $this->defaultSettingsKey), array());
    }
    
    /**
     * Retrieves settings for a notification and syncs them with the default settings.
     *
     * @param  string $type The type name or null to get all settings
     * @return array
     */
    public function getSettings($type = null)
    {
        if(is_null($type))
            return $this->repository->get( $this->repoOptionString($this->settingsKey), array() );
        
        $typeSettings = $this->repository->get( $this->repoOptionString($this->settingsKey, $type) , array());
        $default = $this->defaultSettings();
        $settings = array();
        foreach ($default as $key => $value) 
        {
            $settings[$key] = isset($typeSettings[$key]) ? $typeSettings[$key] : $value;    
        }
        return $settings;
    }
            
}
