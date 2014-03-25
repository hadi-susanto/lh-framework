<?php
/**
 * LH Framework
 *
 * @author Hadi Susanto (lh_framework@yahoo.com)
 * @copyright 2014
 */

namespace Lh\Web\Http;

/**
 * Class FileUpload
 *
 * Encapsulated object from $_FILES for easy file upload manipulation
 */
class FileUpload {
	/** @var string Uploaded filename */
	protected $name;
	/** @var string Mime type of uploaded file */
	protected $type;
	/** @var double Filesize of uploaded file */
	protected $size;
	/** @var string Temporary filename in server storage */
	protected $tempName;
	/** @var int Error code while uploading. Refer to UPLOAD_ERR_* constants */
	protected $error;

	/**
	 * Create new FileUpload object based on $_FILES array value
	 *
	 * This method should be called by framework only. User code don't need call this method
	 * The given array should contain keys:
	 *  - name
	 *  - type
	 *  - size
	 *  - tmp_name
	 *  - error
	 *
	 * @param $array
	 *
	 * @return FileUpload
	 */
	public static function fromArray($array) {
		$file = new FileUpload();
		$file->setName(isset($array["name"]) ? $array["name"] : null);
		$file->setType(isset($array["type"]) ? $array["type"] : null);
		$file->setSize(isset($array["size"]) ? $array["size"] : -1);
		$file->setTempName(isset($array["tmp_name"]) ? $array["tmp_name"] : null);
		$file->setError(isset($array["error"]) ? $array["error"] : UPLOAD_ERR_NO_FILE);

		return $file;
	}

	/**
	 * Set error code
	 *
	 * @param int $error
	 */
	protected function setError($error) {
		$this->error = $error;
	}

	/**
	 * Get error code
	 *
	 * @return int
	 */
	public function getError() {
		return $this->error;
	}

	/**
	 * Set filename
	 *
	 * @param string $name
	 */
	protected function setName($name) {
		$this->name = $name;
	}

	/**
	 * Get filename
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Set file size
	 *
	 * @param float $size
	 */
	protected function setSize($size) {
		$this->size = $size;
	}

	/**
	 * Get file size
	 *
	 * @return float
	 */
	public function getSize() {
		return $this->size;
	}

	/**
	 * Set temporary filename
	 *
	 * @param string $tempName
	 */
	protected function setTempName($tempName) {
		$this->tempName = $tempName;
	}

	/**
	 * Get temporary filename
	 *
	 * @return string
	 */
	public function getTempName() {
		return $this->tempName;
	}

	/**
	 * Set mime type of uploaded file
	 *
	 * @param string $type
	 */
	protected function setType($type) {
		$this->type = $type;
	}

	/**
	 * Get mime type of uploaded file
	 *
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Tells whether the file was uploaded via HTTP POST. Internal function is using is_uploaded_file()
	 *
	 * @see is_upload_file()
	 *
	 * @return bool
	 */
	public function isUploadedFile() {
		return is_uploaded_file($this->tempName);
	}

	/**
	 * Moves an uploaded file to a new location. Internal function is using move_uploaded_file()
	 *
	 * @param string $destination
	 *
	 * @see move_uploaded_file()
	 *
	 * @return bool
	 */
	public function moveUploadedFile($destination) {
		return move_uploaded_file($this->tempName, $destination);
	}
}

// End of File: FileUpload.php 