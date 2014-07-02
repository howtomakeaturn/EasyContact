<?php
/*
 * An abstract class to get contact from services that supports
 * OAuth2.0
 */
abstract class AbstractContact{
    
    // verification code to ask for request token
    protected $code;
    // url to the auth page
    protected $auth_url;    

    protected $access_token;
    // the params to send to the auth page
    protected $auth_params;
    // the params to send to ask for the token
    protected $token_params;
    // the final contacts to return 
    protected $contacts;    
    
    // fetch config from json file
    function fetchConfig($path){
        $string = file_get_contents($path);
        $config = json_decode($string,true);
        $this->setClientId($config['client_id']);
        $this->setRedirectUri($config['redirect_uri']);
        $this->setClientSecret($config['client_secret']);      
    }

    // set client id in both params
    function setClientId($client_id){
        $this->auth_params['client_id'] = $client_id;
        $this->token_params['client_id'] = $client_id;
    }

    // set redirect uri in both params
    function setRedirectUri($redirect_uri){
        $this->auth_params['redirect_uri'] = $redirect_uri;      
        $this->token_params['redirect_uri'] = $redirect_uri;      
    }

    // set client secret in token params
    function setClientSecret($client_secret){
        $this->token_params['client_secret'] = $client_secret;            
    }
    
    // build the auth page uri for uses
    function getAuthUrl(){
        return $this->auth_url . 
            http_build_query($this->auth_params);
    }
    
    // handle the process to get access token
    abstract protected function proceedForToken();

    // fetch code return by the identity provider
    private function fetchCode(){
        $this->code = $_REQUEST['code'];
        $this->token_params['code'] = $_REQUEST['code'];
    }
    
    function getAccessToken(){
        return $this->access_token;
    }

    // handle the process to get contacts we need!    
    abstract protected function proceedForContacts();

    /*
     * The process to get contact:
     * 1. using code and other params to get access token
     * 2. using access token and other params to get api response
     */    
    function getContacts(){
        $this->fetchCode();
        $this->proceedForToken();
        $this->proceedForContacts();      
        return $this->contacts;
    }
  
}
