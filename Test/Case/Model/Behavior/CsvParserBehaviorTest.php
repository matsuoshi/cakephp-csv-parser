<?php
App::import('Behavior', 'CsvParser.CsvParser');

define('_TEST_CSV_DIR', dirname(dirname(dirname(dirname(__FILE__)))) . DS . 'Csv' . DS);


class CsvParserModel extends CakeTestModel
{
	public $useTable = false;
    public $actsAs = array('CsvParser.CsvParser');
}


class CsvParserTest extends CakeTestCase
{
	public $fixtures = array();

	public function setUp()
	{
		$this->CsvParserModel = new CsvParserModel();
	}
	
	public function tearDown()
	{
		unset($this->CsvParserModel);
		unset($this->Behavior);
		ClassRegistry::flush();
	}
	
	
	/*
	 * デフォルト
	 */
	public function testImportCsv001()
	{
		$assert = array(
			2 => array(
				'id' => '1',
				'title' => 'タイトルです',
				'date' => '2013/01/15',
			),
			4 => array(
				'id' => '2',
				'title' => 'trimサンブル',
				'date' => '2013/02/01',
			),
			5 => array(
				'id' => '3',
				'title' => "改行を\n含む\nデータ",
				'date' => '2013/03/10',
			),
		);
		
		$result = $this->CsvParserModel->readCsv(_TEST_CSV_DIR . 'csv001.csv');
		
		$this->assertEquals($assert, $result);
	}
	
	
	/**
	 * utf-8, fields指定あり
	 */
	public function testImportCsv002()
	{
		$assert = array(
			2 => array(
				'f1' => '1',
				'f2' => 'タイトルです',
				'f3' => '2013/01/15',
			),
			4 => array(
				'f1' => '2',
				'f2' => 'trimサンブル',
				'f3' => null,
			),
			5 => array(
				'f1' => '3',
				'f2' => "改行を\n含む\nデータ",
				'f3' => '2013/03/10',
			),
		);
		
		$result = $this->CsvParserModel->readCsv(_TEST_CSV_DIR . 'csv002.csv', array(
			'input_charset' => 'utf-8',
			'fields' => array(
				'f1', 'f2', 'f3',
			)
		));
		
		$this->assertEquals($assert, $result);
	}
	
	
	/**
	 * header と fields が false
	 */
	public function testImportCsv003()
	{
		$assert = array(
			1 => array(
				0 => 'text1',
				1 => 'text2',
			),
			2 => array(
				0 => 'aaa',
				1 => 'bbbccc',
			),
		);
		
		$result = $this->CsvParserModel->readCsv(_TEST_CSV_DIR . 'csv003.csv', array('header' => false));
		
		$this->assertEquals($assert, $result);
	}
}
