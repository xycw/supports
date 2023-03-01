<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function word_break($string, $count){
    if(mb_strlen($string, "UTF-8")<=$count)
        return $string;
    return mb_substr($string, 0, $count, "UTF-8").'...';
}