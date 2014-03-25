<?php
/**
 * Created by PhpStorm.
 * User: Hadi Susanto
 * Date: 12/5/13, 2:44 PM
 */

class Sample {
	public $Name;

	public function exchangeArray($data) {
		$this->Name = $data["e_name"];
	}
}

// End of File: Sample.php 