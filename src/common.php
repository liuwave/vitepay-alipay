<?php


namespace vitepay\alipay {
    
    
    function convert_key($key, $type)
    {
        $type = strtoupper($type);
        
        return "-----BEGIN {$type}-----\n" .
          wordwrap($key, 64, "\n", true) .
          "\n-----END {$type}-----";
    }
    
  
}
