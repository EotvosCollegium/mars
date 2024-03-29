<?php

namespace App\View\Components\Input;

use App\View\Components\Input;
use Illuminate\Support\Collection;

class Select extends Input
{
    public $elements;
    public $placeholder;
    public $withoutPlaceholder;
    public $withoutLabel;
    public $default;
    public $allowEmpty;
    public $formatter;
    public $helper;

    /**
     * Create a new select component instance with a search field.
     *
     * @param $elements elements that can be selected. Id and name tags will be used if exists, otherwise the value itself.
     * @param $formatter function(element): string that will be used to format the elements.
     * @param $placeholder (the default placeholder is general.choose, that can be overwritten with a placeholder attribute)
     * @param $withoutPlaceholder
     * @param $withoutLabel
     * @param $default the default value (the id will be matched)
     * @param $helper helper message
     * @return void
     */
    public function __construct($id, Collection|array $elements, $formatter = null, $placeholder = null, $withoutPlaceholder = false, $withoutLabel = false, $default = null, $text = null, $s = 12, $m = null, $l = null, $xl = null, $onlyInput = false, $allowEmpty = false, $helper = null)
    {
        parent::__construct($id, $text, $s, $m, $l, $xl, $onlyInput);
        $this->elements = (isset($elements[0]->name) ? $elements->sortBy('name') : $elements);
        $this->placeholder = $placeholder;
        $this->withoutPlaceholder = $withoutPlaceholder;
        $this->withoutLabel = $withoutLabel;
        $this->default = $default;
        $this->allowEmpty = $allowEmpty;
        $this->formatter = isset($formatter) ? $formatter : function ($i) {
            return isset($i->name) ? $i->name : $i;
        };
        $this->helper = $helper;
    }

    /**
     * Convert an array with keys to a collection of objects with id and name.
     *
     * @param $array
     * @return \Illuminate\Support\Collection
     */
    public static function convertArray($array)
    {
        $objects = [];
        foreach ($array as $key => $value) {
            $objects[] = (object)["id" => $key, "name" => __($value)];
        }
        return collect($objects);
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view('components.input.select');
    }
}
