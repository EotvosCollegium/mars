<?php

namespace App\View\Components\Input;

use App\View\Components\Input;

class Datepicker extends Input
{
    public $format;
    public $yearRange;
    public $helper;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($id, $format = null, $yearRange = null, $text = null, $s = 12, $m = null, $l = null, $xl = null, $onlyInput = false, $helper = null)
    {
        parent::__construct($id, $text, $s, $m, $l, $xl, $onlyInput);
        $this->format = $format ?? 'yyyy-mm-dd';
        $this->yearRange = $yearRange ?? 100;
        $this->helper = $helper;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view('components.input.datepicker');
    }
}
