<?php

namespace App\Domains\Shared\Utils;

class CreateStubs
{
    public function __construct()
    {

    }

    /**
     * Get the stub path and the stub variables
     *
     * @return bool|mixed|string
     *
     */
    public function getSourceFile($stubPath, $stubVariables)
    {
        return $this->getStubContents($stubPath, $stubVariables);
    }

    /**
     * Replace the stub variables(key) with the desire value
     *
     * @param $stub
     * @param array $stubVariables
     * @return bool|mixed|string
     */
    private function getStubContents($stub , $stubVariables = [])
    {
        $contents = file_get_contents($stub);

        foreach ($stubVariables as $search => $replace)
        {
            $contents = str_replace('{{'.$search.'}}' , $replace, $contents);
        }
        return $contents;
    }
}
