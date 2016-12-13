<?php
/**
 * @link      https://github.com/index0h/yii2-log
 * @copyright Copyright (c) 2014 Roman Levishchenko <index.0h@gmail.com>
 * @license   https://raw.github.com/index0h/yii2-log/master/LICENSE
 */

namespace index0h\log;

use index0h\log\base\EmergencyTrait;
use index0h\log\base\TargetTrait;
use yii\log\Target;
use yii\helpers\VarDumper;
use yii\helpers\Json;

/**
 * @author Roman Levishchenko <index.0h@gmail.com>
 */
class ElasticsearchTarget extends Target
{
	use TargetTrait;
	use EmergencyTrait;

	/** @var string Elasticsearch index name. */
	public $index = 'yii';

	/** @var string Elasticsearch type name. */
	public $type = 'log';

	/** @var string Yii Elasticsearch component name. */
	public $componentName = 'elasticsearch';

	/**
	 * @inheritdoc
	 */
	public function export()
	{
		try {
			$messages = array_map([$this, 'formatMessage'], $this->messages);
			foreach ($messages as &$message) {
				$result = \Yii::$app->{$this->componentName}->post([$this->index, $this->type], [], $message);
				if(!isset($result["created"]) || !$result["created"]){
					$this->emergencyExport([
						'index' => $this->index,
						'type' => $this->type,
						'message' => $message,
						'elasticResult' => $result,
					], false);
				}
			}
		} catch (\Exception $error) {
			$this->emergencyExport([
					'elasticExportError' => [
					'index' => $this->index,
					'type' => $this->type,
					'error' => $error->getMessage(),
					'errorNumber' => $error->getCode(),
					'trace' => $error->getTrace()
				]
			]);
		}
	}
}
