<?php
$active = $active ?? $application->needsFile($type);
$optional = $optional ?? false;
$files = $application->filesOfType($type)->get();
$extensions = $extensions ?? '.pdf,.jpg,.png,.jpeg';
?>
<ul class="collapsible">
    <li @class(['active' => $active, 'form-disabled' => !$active])>
        <div class="collapsible-header">
            @if (!$active)
                <i class="material-icons">help_outline</i>
            @elseif (count($files) > 0)
                <i class="material-icons" style="color:green">check_circle</i>
            @elseif (!$optional)
                <i class="material-icons" style="color:red">cancel</i>
            @else
                <i class="material-icons" style="color:green">help_outline</i>
            @endif
            {{ __('document.file_types.' . $type->value, [], 'hu') }}
        </div>
        <div class="collapsible-body">
            <div class="markdown_with_red">
                @markdown(\App\Models\ConfigurableValue::getText("APPLICATION_FILE_" . strtoupper($type->value)))
            </div>


            @if (!$active)
            <hr />
            <p style="font-style:italic;font-size:0.9em;color:black">A megadott tanulmányi adatok alapján ilyen típusú dokumentumot nem szükséges feltöltenie.</p>
            @endif

            @if ($active || count($files) > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Fájl megnevezése</th>
                            <th>Letöltés</th>
                            <th>Törlés</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($files as $file)
                            <tr>
                                <td>
                                    <a href="{{ url($file->path) }}" target="_blank" style="text-decoration:underline">{{ $file->description }}</a>
                                </td>
                                <td>
                                    <a href="{{ url($file->path) }}" class="btn-floating btn-small waves-effect waves-light coli" download>
                                        <i class="material-icons">file_download</i>
                                    </a>
                                </td>
                                <td>
                                    <form method="POST"
                                          action="{{ route('application.store', ['page' => 'files.delete', 'id' => $file->id]) }}"
                                          enctype='multipart/form-data'>
                                        @csrf
                                        <x-input.button floating class="right btn-small red" icon="delete"/>
                                    </form>
                            </td>
                            </tr>
                        @endforeach
                </table>
            @endif

            @if($active)
                <form method="POST" action="{{ route('application.store', ['page' => 'files', 'type' => $type->value]) }}"
                    enctype='multipart/form-data'>
                    @csrf
                    <div class="row" style="margin-top: 20px;">
                        <x-input.file s=12 m=6 id="{{ 'file' . $type->value}}" name="file" accept="{{  $extensions }}" text="Fájl kiválasztása"
                            helper="{{  $extensions }} fájlok tölthetőek fel, maximum {{ config('custom.general_file_size_limit') / 1000 }} MB-os méretig."
                            required asterisk />
                        <x-input.text s=12 m=4 id="{{ 'name' . $type->value }}" name="name" text="Fájl megnevezése" maxlength="250" required asterisk :value="$default_name ?? ''" />
                        <x-input.button id="{{ 'submit' . $type->value }}" class="s12 m2" only_input class="right" text="general.upload" />
                    </div>
                </form>
            @endif

        </div>
    </li>
</ul>
