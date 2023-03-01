<?php
class payment{
	private $_payment_module = array();
	function cost($payment_module,$money, $jufu=false){
		if(isset($this->_payment_module[$payment_module])){
			$module = $this->_payment_module[$payment_module];
		}else{
		    if(class_exists ($payment_module))
			   $module = new $payment_module;
		    else
		      $module = new default_pay;
			$this->_payment_module[$payment_module] = $module;
		}
	
		return $module->cost($money, $jufu);
	}
	
	
}
