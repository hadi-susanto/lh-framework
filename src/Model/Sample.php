<?php
/**
 * Created by PhpStorm.
 * User: Hadi Susanto
 * Date: 12/5/13, 2:44 PM
 */

class SampleTest {
	private $name;

	/**
	 * @return mixed
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param mixed $name
	 */
	public function setName($name) {
		$this->name = $name;
	}



	public function exchangeArray($data) {
		$this->name = $data["e_name"];
	}
}

// End of File: Sample.php 