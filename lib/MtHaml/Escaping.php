<?php

namespace MtHaml;

class Escaping
{
    protected $enabled;
    protected $once;

    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function isEnabled()
    {
        return $this->enabled;
    }

    public function setOnce($once)
    {
        $this->once = $once;

        return $this;
    }

    public function isOnce()
    {
        return $this->once;
    }
}
