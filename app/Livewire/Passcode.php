<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\GeneralAssemblies\GeneralAssembly;

class Passcode extends Component
{
    /**
     * Whether the font size should be large
     * (so that it's seen full-screen).
     */
    public bool $isFullscreen;

    /**
     * Returns the passcode.
     */
    public function getPasscodeProperty()
    {
        return app(GeneralAssembly::class)->getTemporaryPasscode();
    }

    /**
     * View to render the passcode.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view(
            'student-council.general-assemblies.passcode',
            ['isFullscreen' => $this->isFullscreen]
        );
    }
}
