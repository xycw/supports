<?php
class credit_pay{
	public $cost = 0;//总手续费
	public $desc = '单笔手续费:4%，拒付费：25USD';//手续费说明
	
	function __construct(){
	}
	
	/*
	 * 支付方式手续费
	 */
	function cost($money, $jufu=false){
		$this->cost = $money*0.04;
		if ($jufu==true) {
			$this->cost += 25*6.2;
		}
		
		return round($this->cost, 2);
	}
}