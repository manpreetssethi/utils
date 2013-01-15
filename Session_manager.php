<?php

/*

An implementation mimicking the global SESSION variable using memcache
The primary purpose of writing this module was to eliminate dependency
of the SESSION variable on the browsers' P3P implementation when working
with cross domain SESSIONS

Issue in detail: http://stackoverflow.com/questions/10105168/facebook-iframe-canvas-app-php-sessions-issue

  //----------------------//
 // Memcache Settings    //
//----------------------//
$_app_settings = array();

$_app_settings['fb']['app_id'] = 1234567890;

$_app_settings['memcache'] = array();
$_app_settings['memcache']['use']   = true;
$_app_settings['memcache']['host']  = '127.0.0.1';
$_app_settings['memcache']['port']  = 11211;

//The memache object
$memcache = new Memcache;
$memcache->connect( $_app_settings['memcache']['host'], $_app_settings['memcache']['port'] );

*/

class Session_manager {

    private $session_data = array();
    private $session_id = '';

    function __construct() {
        global $memcache, $_app_settings;

        $this->session_id = session_id();

        $this->session_data = $_app_settings['memcache']['use'] ? $memcache->get( $_app_settings['fb']['app_id'].'_'.$this->session_id.'_session_data' ) : $_SESSION;

        if( empty( $this->session_data ) && $_app_settings['memcache']['use'] ) {
            $memcache->set(  $_app_settings['fb']['app_id'].'_'.$this->session_id.'_session_data', array() );
        }
    }

    //Getter
    public function __get( $key ) {
        if ( array_key_exists( $key, $this->session_data ) ) {
            return $this->session_data[$key];
        }

        return null;
    }

    //Setter
    public function __set( $key, $value ) {
        global $memcache, $_app_settings;

        $this->session_data[$key] = $value;

        if( $_app_settings['memcache']['use'] )
            $memcache->set( $_app_settings['fb']['app_id'].'_'.$this->session_id.'_session_data', $this->session_data );
        else
            $_SESSION = $this->session_data;

        return true;
    }


    public function __destruct() {
    }
}

?>
