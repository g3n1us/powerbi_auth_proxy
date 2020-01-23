<?php
	
namespace BlueRaster\PowerBIAuthProxy\Exceptions;

class IllegalClassDirectoryListException extends \Exception{
	protected $message = "Only specific directories can auto-list their available classes.";
	
}
