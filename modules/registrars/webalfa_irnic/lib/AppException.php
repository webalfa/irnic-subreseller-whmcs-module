<?php

namespace WHMCS\Module\Registrar\WebalfaIrnic;


class AppException extends \Exception
{
    protected $extra;

    public function __construct($message, $code = 0, $extra = null, IRNICException $previous = null)
    {
        $this->extra = $extra;

        parent::__construct($message, $code, $previous);
    }

    public function getExtra()
    {
        return $this->extra;
    }

    public static function parseException($exception)
    {
        $extra = $exception->getExtra();
        if (!empty($extra))
            return $exception->getMessage() . '<br>' . json_encode($extra);
        else
            return $exception->getMessage();
    }


}
