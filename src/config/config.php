<?php

    return array (
                
                'session_key' => 'ahem_notifications',
                
                'headings'  => array (
                
                            'default_key'   => 'notification_heading',
                
                ),
                
                
                'settings'  => array (
                
                            'default_settings'  => array (
                                   'wrapper'                    => 'div',
                                   'wrapper_class'              => 'alert-box',
                                   'before_message'             => '<a href="#" class="close">&times;</a>',
                                   'single_message'             => ':message',
                                   'heading'                    => '<strong> :heading </strong>',
                                   'message_list_wrapper'       => 'ul',
                                   'message_list_wrapper_class' => '',
                                   'message_list'               => '<li> :message </li>',
                                   'after_message'              => ''
                            ),     
                            
                            'success'  => array (
                                    
                                    'wrapper_class'     => 'alert-box success',
                                                    
                            ),
                            'info'  => array (
                                    
                                    'wrapper_class'     => 'alert-box info',
                                                    
                            ),
                            'warning'  => array (
                                    
                                    'wrapper_class'     => 'alert-box warning',
                                                    
                            ),
                            'error'  => array (
                                    
                                    'wrapper_class'     => 'alert-box error',
                                                    
                            ),    
                             
                
                ),
       
    );
