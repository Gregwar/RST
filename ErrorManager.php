<?php

namespace Gregwar\RST;

class ErrorManager
{
    protected $abort = true;

    public function abortOnError($abort)
    {
        $this->abort = $abort;
    }

    public function error($message)
    {
        if ($abort) {
            throw new \Exception($message);
        } else {
            echo '/!\\ '.$message."\n";
        }
    }
}
