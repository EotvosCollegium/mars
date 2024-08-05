<?php

namespace App\View\Components\Input;

use App\View\Components\Input;

class File extends Input
{
    public $helper;

    public function __construct($id, $text = null, $s = 12, $m = null, $l = null, $xl = null, $onlyInput = false, $helper = null)
    {
        parent::__construct($id, $text, $s, $m, $l, $xl, $onlyInput);
        $this->helper = $helper;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view('components.input.file');
    }
}
