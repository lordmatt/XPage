<?php

/**
 * A Delegation extension of SimpleXMLElement
 * 
 * Designed to act like SimpleXML but builds a back traceable tree that expects
 * XPage (or some other parent factory) to catch references and debug messages.
 * 
 * @author Lord Matt 
 * 
 * @license
 *  Copyright (C) 2015 Matthew Brown
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *  GNU General Public License for more details.
 *  You should have received a copy of the GNU General Public License along
 *  with this program; if not, write to the Free Software Foundation, Inc.,
 *  51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

class AEWrapper {
    
    protected $mother = null;
    protected $MyAE = null;
    
    
    public function __construct($MyAE,$mother) {
        $this->MyAE = $MyAE;
        $this->mother = $mother;
        return $this;
    }
    
    public function __call($name, $arguments) {
        try {
            $result = call_user_method_array($name, $this->MyAE, $arguments);
        } catch (Exception $e) {
            $this->log_error('AEWrapper caught exception: ',  $e->getMessage());
            return FALSE;
        }
        if(is_object($result)){
            return new AEWrapper($result,$this);
        }
        return $result;
    }
    
    public function __get($name) {
        try {
            $result = $this->MyAE->{$name};
        } catch (Exception $e) {
            $this->log_error('AEWrapper caught exception: ',  $e->getMessage());
            return FALSE;
        }
        if(is_object($result)){
            return new AEWrapper($result,$this);
        }
        return $result;
    }
    
    public function &actual_simpleXML_obj_please(){
        return $this->MyAE;
    }
    
    // methods exists in XPage
    public function register_as($name,$what=null){
        if($what===null){
            $what = $this;
        }
        $this->mother->register_as($name,$what);
    }
    // methods exists in XPage    
    public function &go_back_up_by_one(){
        return $this->mother();
    }
    // methods exists in XPage    
    public function this_is_XML(){
        return TRUE;
    }
    // methods exists in XPage    
    protected function log_error($error){
        $this->mother->log_error('/'.$error);
    }
}

