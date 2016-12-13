<?php
/**
 * @link      https://github.com/index0h/yii2-log
 * @copyright Copyright (c) 2014 Roman Levishchenko <index.0h@gmail.com>
 * @license   https://raw.github.com/index0h/yii2-log/master/LICENSE
 */

namespace index0h\log\base;

use yii\helpers\ArrayHelper;
use yii\log\Logger;
use yii\helpers\VarDumper;
use yii\helpers\Json;

trait TargetTrait
{
	/** @var bool Whether to log a message containing the current user name and ID. */
	public $logUser = false;

	/**
     * Returns the text display of the specified level.
     * @param integer $level the message level, e.g. [[LEVEL_ERROR]], [[LEVEL_WARNING]].
     * @return string the text display of the level
     */
    public static function getLevelName($level)
    {
        static $levels = [
            Logger::LEVEL_ERROR => 'error',
            Logger::LEVEL_WARNING => 'warning',
            Logger::LEVEL_INFO => 'info',
            Logger::LEVEL_TRACE => 'trace',
            Logger::LEVEL_PROFILE => 'profile',
            Logger::LEVEL_PROFILE_BEGIN => 'profile begin',
            Logger::LEVEL_PROFILE_END => 'profile end',
        ];
        return isset($levels[$level]) ? $levels[$level] : 'unknown';
    }

	/**
	 * Formats a log message.
	 *
	 * @param array $message The log message to be formatted.
	 *
	 * @return string
	 */
	public function formatMessage($message)
	{
		$result = [];
		list($text, $level, $category, $timestamp) = $message;
		$level = static::getLevelName($level);
		$timestamp = date('c', $timestamp);

		$prefix = $this->getMessagePrefix($message);

		$result = ArrayHelper::merge(
			$this->parseText($text),
			['level' => $level, 'category' => $category, '@timestamp' => $timestamp]
		);

		if (isset($message[4]) === true && !empty($message[4])) {
			$result['trace'] = $message[4];
		}

		if (isset($message[5]) === true) {
			$result['duration'] = $message[5];
		}

		$result['info'] = $prefix;

		//return Json::encode($result);
		return Json::encode($result);
	}

	/**
	 * Convert's any type of log message to array.
	 *
	 * @param mixed $text Input log message.
	 *
	 * @return array
	 */
	protected function parseText($text)
	{
		$type = gettype($text);
		switch ($type) {
			case 'array':
				if(!isset($text['@message']))
					$text['@message'] = (string) implode(";", $text);
				return $text;
			case 'string':
				return ['@message' => (string) $text];
			case 'object':
				if(is_a($text, "yii\base\ErrorException"))
					return [
						"@message" => $text->getMessage(),
						"file" => $text->getFile(),
						"line" => $text->getLine(),
						"trace" => $text->getTrace(),
						"code" => $text->getCode(),
						"Exception" => (string) $text,
					];
				else
					return [
						'@message' => get_class($text),
						"Object" => get_object_vars($text),
						"__toString" => method_exists($text, "__toString") ? (string) $text : "",
					];
			default:
				return ['@message' => "Warning, wrong log message type '{$type}'."];
		}
	}
}
