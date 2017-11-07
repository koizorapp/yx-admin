<?php
/**
 * Created by PhpStorm.
 * User: koizora
 * Date: 2017/11/7
 * Time: 10:27
 */

namespace App\Exceptions;


class InvalidRequestException extends \Exception
{
    public $data;
    public function __construct($data){
        $this->data = $data;
    }
}