<?php

namespace BlueRaster\PowerBIAuthProxy\Guards;

class MockGuard {

	protected $exception = "Mock is the best!!";

	public static function challenge() : MockGuard{
		return new MockGuard;
		return php_sapi_name() === 'cli-server';
	}

}
