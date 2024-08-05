<?php

namespace App\View\Components\Input;

use App\View\Components\Input;

class Text extends Input
{
    public $helper;
    public $withoutLabel;

    /**
     * Create a new text input instance.
     *
     * @param  string  $helper  helper message
     * @param  ?string $value   pre-filled contents
     * @return void
     */
    public function __construct($id, $withoutLabel = false, $text = null, $s = 12, $m = null, $l = null, $xl = null, $onlyInput = false, $helper = null, ?string $value = '')
    {
        parent::__construct($id, $text, $s, $m, $l, $xl, $onlyInput);
        $this->helper = $helper;
        $this->withoutLabel = $withoutLabel;
        $this->value = $value;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view('components.input.text');
    }
}
