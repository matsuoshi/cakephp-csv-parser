<?php
/**
 * Group test - Csv Parser
 */
class AllCsvParserPluginTest extends PHPUnit_Framework_TestSuite
{
	public static function suite()
	{
		$Suite = new CakeTestSuite('All Plugin tests');
		$path = dirname(__FILE__);
		$Suite->addTestDirectory($path . DS . 'Model' . DS . 'Behavior');
		return $Suite;
	}
}
