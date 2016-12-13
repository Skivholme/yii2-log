<?php
/**
 * @link      https://github.com/index0h/yii2-log
 * @copyright Copyright (c) 2014 Roman Levishchenko <index.0h@gmail.com>
 * @license   https://raw.github.com/index0h/yii2-log/master/LICENSE
 */

namespace index0h\log\base;

use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\VarDumper;

/**
 * Current class needs to write logs on external service exception.
 *
 * @property array messages The messages that are retrieved from the logger so far by this log target.
 *
 * @author Roman Levishchenko <index.0h@gmail.com>
 */
trait EmergencyTrait
{
    /** @var string Alias of log file. */
    public $emergencyLogFile = '@runtime/logs/logService.log';

    /**
     * @param array $data Additional information to log messages from target.
     */
    public function emergencyExport($data, $em = true)
    {
        //$this->emergencyPrepareMessages($data);
        $text = "";
        if($em){
            //$this->emergencyPrepareMessages($data);
            $data['messageQueue'] = $this->messages;
            $text .= Json::encode($data);
        }else{
            $text = sprintf("\n#########################################\n%s\n#########################################\n", Json::encode($data));
        }

        file_put_contents(\Yii::getAlias($this->emergencyLogFile), $text, FILE_APPEND);
    }

    /**
     * @param array $data Additional information to log messages from target.
     */
    protected function emergencyPrepareMessages($data)
    {
        foreach ($this->messages as &$message) {
            $message[0] = ArrayHelper::merge($message[0], $data);
            //$message[0] .= Json::encode($data);
        }

    }
}