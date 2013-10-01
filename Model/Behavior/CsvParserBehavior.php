<?php
/**
 * Csv Parser Plugin
 */
App::uses('ModelBehavior', 'Model');

/**
 * Csv Parser behavior
 *
 * @package		plugins.csvparser
 * @subpackage	plugins.csvparser.models.behaviors
 */
class CsvParserBehavior extends ModelBehavior
{
	public $settings = array();
	
	protected $_default = array(
		'input_charset' => 'sjis-win',
		'output_charset' => 'utf-8',
		'header' => true,
		'fields' => true,
		'delimiter' => ',',
		'enclosure' => '"',
	);


	public function setup(Model $Model, $options = array())
	{
		$this->settings[$Model->alias] = Hash::merge($this->_default, $options);
	}

	
	/**
	 * read CSV file
	 * 
	 * @param Model $model
	 * @param string $filename CSV File name
	 * @param array $options
	 */
	public function readCsv(Model $Model, $filename, $options = array())
	{
		// オプション設定
		$options = Hash::merge($this->settings[$Model->alias], $options);
		
		// 文字コード設定
		setlocale(LC_ALL, "ja_JP.{$options['output_charset']}");
		
		// ファイル読み込み
		if ($options['input_charset'] == $options['output_charset']) {
			$fp = fopen($filename, 'r');
		}
		else {
			// ファイルの文字コード変換
			$tmp = file_get_contents($filename);
			$tmp = mb_convert_encoding($tmp, $options['output_charset'], $options['input_charset']);

			$fp = tmpfile();
			fwrite($fp, $tmp);
			rewind($fp);
		}

		
		/*
		 * CSV 解析開始
		 */
		$csvdata = array();
		$line = 0;		// CSVの何行目か?
		$header = null;
		$fields = false;
		$field_count = null;
		
		// 1行目の扱い
		if ($options['header']) {
			// 1行目をヘッダとして扱う
			$header = fgetcsv($fp, 99999, $options['delimiter'], $options['enclosure']);
			$line++;
		}
		
		// データを連想配列で返すかどうか
		if (! empty($options['fields'])) {
			if (is_array($options['fields'])) {
				$fields = $options['fields'];
			}
			else if (is_array($header)) {
				$fields = $header;
			}
			$field_count = count($fields);
		}
		
		
		while (($data = fgetcsv($fp, 99999, $options['delimiter'], $options['enclosure'])) !== FALSE) {
			$line++;
			
			// 空行なら無視
			if (trim(implode('', $data)) == '') {
				continue;
			}
			
			if ($fields) {
				$data_count = count($data);
				if ($field_count == $data_count) {
					$csvdata[$line] = array_combine($fields, $data);
				}
				elseif ($field_count > $data_count) {
					$data = array_merge($data, array_fill($data_count, $field_count - $data_count, NULL));
					$csvdata[$line] = array_combine($fields, $data);
				}
				else {
					$data = array_slice($data, 0, $field_count);
					$csvdata[$line] = array_combine($fields, $data);
				}
			}
			else {
				$csvdata[$line] = $data;
			}
		}

		// trim
		array_walk_recursive($csvdata, create_function('&$val, $key', '$val = trim($val);'));
		
		return $csvdata;
	}
}
