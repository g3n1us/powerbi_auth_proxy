<?php

namespace BlueRaster\PowerBIAuthProxy;

use Composer\Factory as ComposerFactory;

class SafetyNet{

	private $document_root;

	public function __construct(){

		$this->document_root = $_SERVER['DOCUMENT_ROOT'];

		if(file_exists($this->document_root . '/auth_proxy_installer.php')){
			die("The installer file has not been deleted.");
		}

	}
}
