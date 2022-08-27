<table>
    <tbody>
        @if(!$semesters->contains(\App\Models\Semester::current()))
            <tr>
                <td>
                    <b>{{ \App\Models\Semester::current()->tag }} (jelenlegi)</b>
                </td>
                <td class="right">
                    @livewire('edit-status', ['user' => $user, 'semester' => \App\Models\Semester::current()])
                </td>
            </tr>
        @endif
        @foreach ($semesters as $semester)
            <tr>
                <td>
                    <b>{{ $semester->tag }}</b>
                </td>
                <td class="right">
                    @livewire('edit-status', ['user' => $user, 'semester' => $semester])
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
@once
    @push('scripts')
        <script>
            $(document).ready(function(){
                $('.tooltipped').tooltip();
            });
        </script>
    @endpush
@endonce
