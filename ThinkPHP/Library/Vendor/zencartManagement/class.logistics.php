<?php

class logistics{
    private $_moduel;
    
    public function logistics($module) {
        $this->_moduel = new $module;
    }
    
    public function query($no){
        $r = $this->_moduel->arrayQuery($no);
        
        if($r!=false){
            
        }
        
        return $r;
    }
}
