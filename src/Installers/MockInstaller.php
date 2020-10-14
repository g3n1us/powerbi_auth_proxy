<?php

namespace BlueRaster\PowerBIAuthProxy\Installers;

class MockInstaller extends Installer{

	protected function isInstalled() : bool {
		return true;
	}

	protected function getSteps() : array{
		return ['testing'];
	}


	public function testing(){
		echo 'testing';
	}


}
