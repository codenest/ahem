<?php

    return array (
                
                //The session key to store flashed notifications
                'session_key' => 'ahem_notifications',
                
                /*
                * Settings for all default. notifications. 
                * Feel free to add, edit or delete notifications to suite your needs.

                **************************** NOTE **********************************
                ********************************************************************
                 The notification names defined here are the ones you will use in 
                 your app. So incase you change any, make sure you use the new name.
                ********************************************************************
                *
                */
                'settings'  => array (
                
                            /*
                            * Default settings. 
                            * If you don't define any of these variables in the specific notifications settings,
                            * these defaults will be used. 
                            */
                            'default_settings'  => array (

                                    // The HTML element that wraps a notification.
                                   'wrapper'                    => 'div',
                                   
                                   // The CSS class value of the wrapper.
                                   'wrapper_class'              => 'alert-box',
                                   
                                   // Any HTML that need to be appended to the notification after the wrapper element is opened but before the messages are rendered.
                                   'before_message'             => '<a href="#" class="close">&times;</a>',
                                   
                                   // The format in which to display single messages eg. <p> :message </p>.
                                   'single_message'             => ':message',

                                   // The format in which to display the notification's heading.
                                   'heading'                    => '<strong> :heading </strong>',

                                  // The HTML element that wraps a message list if a notification contains multiple messages
                                   'message_list_wrapper'       => 'ul',

                                   // The CSS class value of the message list wrapper.
                                   'message_list_wrapper_class' => '',

                                   // The format in which to display single message list item.
                                   'message_list'               => '<li> :message </li>',

                                    // Any HTML that need to be appended to the notification after the messages are rendered but before the wrapper is closed.
                                   'after_message'              => ''
                            ),     
                            
                            // Success notification settings.
                            
                            /*************************** NOTE **********************************
                            * As you can see, only the 'wrapper_class' is set since all other 
                            * settings are the same as the default_settings. Only provide the settings 
                            * you need over ridden from the default_settings.
                            */
                            'success'  => array (
                                    
                                    'wrapper_class'     => 'alert-box success',
                                                    
                            ),

                             // Info notification settings.
                            'info'  => array (
                                    
                                    'wrapper_class'     => 'alert-box info',
                                                    
                            ),

                             // Warning notification settings.
                            'warning'  => array (
                                    
                                    'wrapper_class'     => 'alert-box warning',
                                                    
                            ),

                             // Error notification settings.
                            'error'  => array (
                                    
                                    'wrapper_class'     => 'alert-box error',
                                                    
                            ),    
                             
                
                ),
       
    );
