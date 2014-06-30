<?php
/*
 * EasyContact helps you get users' email contact.
 * Integrated with APIs from Yahoo, Google, and Microsoft.
 * (Yahoo Contacts API, Google Contacts API version 3.0, and Office 365 API)
 * 
 * It helps you develope application that needs users' email contact,
 * for example, inviting friends, advertising friends, and etc.
 * 
 * @author Chuan-Hao, You (howtomakeaturn)
 * @blog http://blog.turn.tw/
 * @github 
 */
 
require_once 'EasyContact/Exceptions.php';
require_once 'EasyContact/GoogleContact.php';
require_once 'EasyContact/MicrosoftContact.php';
 
class EasyContact{
  
    public $google;

    function __construct(){
        $this->google = new GoogleContact();
        $this->microsoft = new MicrosoftContact();
    }
        
}
