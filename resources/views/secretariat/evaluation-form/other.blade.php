<form method="POST" action="">
    @csrf
    <input type="hidden" name="section" value="other"/>
    <div class="row">
        <div class="col s12">
            @livewire('parent-child-form', [
            'title' => "Szakmai eredmények",
            'name' => 'professional_results',
            'items' => $evaluation?->professional_results])
        </div>

        <div class="col s12">
            @livewire('parent-child-form', [
            'title' => "Kutatómunka",
            'name' => 'research',
            'helper' => 'Rövid leírás, témavezető/kutatócsoport',
            'items' => $evaluation?->research])
        </div>
        <div class="col s12">
            @livewire('parent-child-form', [
            'title' => "Publikációk",
            'name' => 'publications',
            'helper' => 'Név, kiadó, társszerző (ha van)',
            'items' => $evaluation?->publications])
        </div>
        <div class="col s12">
            @livewire('parent-child-form', [
            'title' => "Konferenciarészvétel",
            'name' => 'conferences',
            'helper' => 'TDK/OTDK, nemzetközi/hazai konferencián előadás/poszter',
            'items' => $evaluation?->conferences])
        </div>
        <div class="col s12">
            @livewire('parent-child-form', [
            'title' => "Ösztöndíjak, elismerések",
            'name' => 'scholarships',
            'items' => $evaluation?->scholarships])
        </div>
        <div class="col s12">
            @livewire('parent-child-form', [
            'title' => "Oktatási tevékenység",
            'helper' => 'A Collegiumban vagy egyetemen',
            'name' => 'educational_activity',
            'items' => $evaluation?->educational_activity])
        </div>
        <div class="col s12">
            @livewire('parent-child-form', [
            'title' => "Közéleti tevékenységek",
            'name' => 'public_life_activities',
            'items' => $evaluation?->public_life_activities])
        </div>
        <x-input.checkbox id="can_be_shared"
                            text="Hozzájárulok, hogy eredményeim megjelenjenek a Collegium honlapján és közösségi oldalain."
                            :checked="$evaluation?->can_be_shared ?? false"/>
        <x-input.button  class="right" text="general.save"/>

    </div>
</form>
