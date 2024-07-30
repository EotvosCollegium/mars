<?php

namespace App\View\Components\Input;

use App\View\Components\Input;

class Textarea extends Input
{
    public $helper;

    /**
     * Create a new textarea input instance.
     *
     * @param  string  $helper  helper message
     * @param  ?string $value   pre-filled contents
     * @return void
     */
    public function __construct($id, $helper = null, $text = null, $s = 12, $m = null, $l = null, $xl = null, $onlyInput = false, ?string $value = '')
    {
        parent::__construct($id, $text, $s, $m, $l, $xl, $onlyInput);
        $this->helper = $helper;
        $this->value = $value;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view('components.input.textarea');
    }
}
