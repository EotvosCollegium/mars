<?php

namespace App\View\Components\Input;

use App\View\Components\Input;
use Illuminate\View\Component;

class Checkbox extends Input
{
    public bool $checked;

    /**
     * Create a new button instance. The button will be a link if the href attribute is provided.
     *
     * @param $floating if provided, the button will be a floating action button. The icon attribute is recommended.
     * @param $icon if provided, an icon will be placed in the button instead of a text
     */
    public function __construct($checked = false, $id = null, $text = null, $s = 12, $m = null, $l = null, $xl = null, $onlyInput = false)
    {
        parent::__construct($id, $text, $s, $m, $l, $xl, $onlyInput);
        $this->checked = $checked;
    }
    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view('components.input.checkbox');
    }
}
