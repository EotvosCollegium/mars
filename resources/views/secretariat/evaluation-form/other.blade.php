<form method="POST" action="">
    @csrf
    <div class="row">
        <div class="col s12">
            @livewire('parent-child-form', [
            'title' => "Szakmai eredmények",
            'name' => 'semester_average',
            'items' => null])
        </div>
        <div class="col s12">
            @livewire('parent-child-form', [
            'title' => "Kutatómunka",
            'name' => 'competition',
            'helper' => 'Rövid leírás, témavezető/kutatócsoport',
            'items' => null])
        </div>
        <div class="col s12">
            @livewire('parent-child-form', [
            'title' => "Publikációk",
            'name' => 'publication',
            'helper' => 'Név, kiadó, társszerző (ha van)',
            'items' => null])
        </div>
        <div class="col s12">
            @livewire('parent-child-form', [
            'title' => "Konferenciarészvétel",
            'name' => 'foreign_studies',
            'helper' => 'TDK/OTDK, nemzetközi/hazai konferencián előadás/poszter',
            'items' => null])
        </div>
        <div class="col s12">
            @livewire('parent-child-form', [
            'title' => "Ösztöndíjak, elismerések",
            'name' => 'foreign_studies',
            'items' => null])
        </div>
        <div class="col s12">
            @livewire('parent-child-form', [
            'title' => "Oktatási tevékenység",
            'helper' => 'A Collegiumban vagy egyetemen',
            'name' => 'foreign_studies',
            'items' => null])
        </div>
        <div class="col s12">
            @livewire('parent-child-form', [
            'title' => "Közéleti tevékenységek",
            'name' => 'foreign_studies',
            'items' => null])
        </div>
        <x-input.checkbox id="accommodation"
                            text="Hozzájárulok, hogy eredményeim megjelenjenek a Collegium honlapján és közösségi oldalain."
                            :checked="false"/>
        <x-input.button  class="right" text="general.save"/>
    </div>
</form>
