<?php
require_once (APPPATH."libraries/php-barcode-generator/src/BarcodeGenerator.php");
require_once (APPPATH."libraries/php-barcode-generator/src/BarcodeGeneratorSVG.php");
require_once (APPPATH."libraries/php-barcode-generator/src/BarcodeGeneratorPNG.php");

class Barcode extends MY_Controller 
{
	function __construct()
	{
		parent::__construct();	
	}
	
	function index($type='png')
	{
		$text = rawurldecode($this->input->get('text'));
		$barcode = rawurldecode($this->input->get('barcode'));
		$scale = $this->input->get('scale') ? $this->input->get('scale') : 1;
		$thickness = $this->input->get('thickness') ? $this->input->get('thickness') : 30;
		if ($type == 'png')
		{
			$font_size = $this->input->get('font_size') ? $this->input->get('font_size') : 9;
			$generator = new Picqer\Barcode\BarcodeGeneratorPNG();
			header('Content-Type: image/png');
			echo $generator->getBarcode($barcode, $text,$generator::TYPE_CODE_128,$scale,$thickness,$font_size);
		}
		elseif($type=='svg')
		{
			$font_size = $this->input->get('font_size') ? $this->input->get('font_size') : 13;
			$generator = new Picqer\Barcode\BarcodeGeneratorSVG();
			header('Content-type: image/svg+xml');
			echo $generator->getBarcode($barcode, $text,$generator::TYPE_CODE_128,$scale,$thickness,$font_size);
			
		}
	}

}
?>