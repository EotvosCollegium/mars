<?php

namespace App\Livewire;

use Livewire\Component;

/**
 * General purpose form component to add multiple values to a field.
 */
class ParentChildForm extends Component
{
    public $items; // array of result items the user given
    public $name; // name of the field
    public $title; // title of the field
    public $helper; // helper text for the field
    public $hidden; // hidden state
    public $optional; // if true, the form is hidden by default (meaning no data is given), and a checkbox is added whether the user wants to add data or not

    /**
     * Mount the component.
     *
     * @param array $items
     * @param bool $optional
     */
    public function mount(?array $items, bool $optional = false): void
    {
        if (is_null($items) || count($items) == 0) {
            $items = [''];
        }
        $this->items = $items;
        $this->hidden = $optional ? count($items) == 1 && $items[0] == '' : false;
        $this->optional = $optional;
    }

    /**
     * Switch the hidden state.
     */
    public function show()
    {
        $this->hidden = !$this->hidden;
    }

    /**
     * Add an empty item to the list.
     */
    public function addItem()
    {
        $this->items[] = '';
    }

    /**
     * Remove an item from the list.
     *
     * @param int $index
     */
    public function removeItem($index)
    {
        unset($this->items[$index]);
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.parent-child-form');
    }
}
