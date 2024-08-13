@extends('layouts.app')
@section('title')
<i class="material-icons left">bed</i>Szobabeosztás
@endsection

<style>

/* Blinking animation for finding users */
.fix-stroke {
   paint-order: stroke fill;
}

.blink {
    -webkit-animation: blink 2s infinite both;
            animation: blink 2s infinite both;
}

@-webkit-keyframes blink {
  0%,
  50%,
  100% {
    fill-opacity: 1;
  }
  25%,
  75% {
    fill-opacity: 0;
  }
}
@keyframes blink {
  0%,
  50%,
  100% {
    fill-opacity: 1;
  }
  25%,
  75% {
    fill-opacity: 0;
  }
}

#info-box {
  display: none;
  position: fixed;
  top: 0px;
  left: 0px;
  z-index: 1;
  background-color: #ffffff;
  color: #000000;
  border: 2px solid grey;
  border-radius: 2px;
  padding: 5px;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
}

</style>

<script type="text/javascript" src="{{ mix('js/jquery.min.js') }}"></script>
<script>
    $( document ).ready(function() {
        // The rooms can be paths polygons and rects
        $("path, polygon, rect").hover(function(e) {
            if(this.id.indexOf('room')>-1){
                $(this).css({'filter': 'grayscale(50%)'});
                $('#info-box').css('display','block');
                people=$(this).data('info').split(';');
                htmlString='';

                people.forEach(element => {
                    if(element!=''){
                        htmlString+='<li>'+element+'</li>';
                    }
                });
                // The info-box is the box appearing when hovering over a room. It gets its infos from the data-info property.
                $('#info-box').html(
                    '<p>Szobaszám: '+this.id.replace('room','')+'</p>'+
                    '<p>Lakók:</p>'+
                    '<ol>'+
                    htmlString
                    +'</ol>'
                );
            }
        }, function(e){
            if(this.id.indexOf('room')>-1){
                $(this).css('filter', '');
                $('#info-box').css('display','none');
                $('#info-box').html('');
            }
        });
        $(document).mousemove(function(e) {
            $('#info-box').css('top',e.pageY-$(document).scrollTop()+10);
            $('#info-box').css('left',e.pageX+10);
        }).mouseover();
        // If the search button is hit, search for the name or the room number and add blink property to the matching room
        $("#nameSubmit").click(function(e){
            query=$('#nameInput').val();
            query2=query;
            if(query.trim().length>0 && !isNaN(query)){
                query2='room'+query;
            }
            $('#room'+query).addClass('blink');
            $('[data-info*='+String(query2)+' i]').addClass('blink');
            setTimeout(function(){
                $('#room'+query).removeClass('blink');
                $('[data-info*='+String(query2)+' i]').removeClass('blink');
            }, 5000);

        });
    });

</script>
@section('content')
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <div id="info-box"></div>
                {{-- Coloring legend --}}
                @can('updateAny', \App\Models\Room::class)
                <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                    viewBox="0 0 300 15" style="enable-background:new 0 0 1047.8 750;" xml:space="preserve">
                    <text x="0" y="12" class="on-background" style="font-size: 12px;">Szabad helyek száma:</text>
                    @php
                        $padding=120;
                    @endphp
                    @for ($i = 0; $i < count(\App\Models\Room::$roomColors); $i++)
                    <polygon points="{{$padding+$i*25}},0 {{$padding+$i*25}},15, {{$padding+$i*25+20}},15 {{$padding+$i*25+20}},0" style="fill: {{\App\Models\Room::$roomColors[$i]}}"/>
                    <text x="{{$padding+$i*25+7}}" y="12" style="font-size: 12px; fill: #000000;">{{$i}}</text>
                    @endfor
                </svg>
                @endcan
                {{-- The goal was to make an interactive map of the Collegium and show the room occupation on it.
                    This can be achieved by using an svg (vector graphic image) and modifying its style properties
                    using javascript. The problem with this is that the info shown by the javascript all has to be put inside
                    of the HTML when creating the file as javascript cannot access the database. This is the reason the data-info
                    tag is used. The dynamic styling is accomplished with JQuery.
                    The svg file was created with Adobe Illustrator, drawing over a plan of the building. First I used
                    gimp, but using Illustrator resulted in a much cleaner code (it created style properties for example). --}}
                <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                        viewBox="0 0 1047.8 670" style="enable-background:new 0 0 1047.8 750;" xml:space="preserve">
                    <style type="text/css">
                        .st1{fill:#c6f0ff;stroke:#000000;stroke-width:0.25;}
                        .st2{fill:#808080;stroke:#000000;stroke-width:0.25;}
                        .st4{font-size:18px; fill:#000000; pointer-events: none}
                        .st8{fill:#FFFFFF;font-size:16px;pointer-events: none}
                        .st9{fill:#C6C6C6;}
                        .st11{font-size:36px;}
                    </style>
                    <g id="em2" xmlns:inkscape="http://www.inkscape.org/namespaces/inkscape" inkscape:version="1.1.2 (b8e25be833, 2022-02-05)" xmlns:svg="http://www.w3.org/2000/svg" xmlns:sodipodi="http://sodipodi.sourceforge.net/DTD/sodipodi-0.dtd" sodipodi:docname="drawing.svg">
                        @foreach ($roomNumbersSecondFloor as $roomNumber)
                        <path id="{{'room'.$roomNumber}}"
                            class="st1"
                            data-info="{{ $rooms->firstWhere('name', $roomNumber)->users->pluck('name')->join(';') }}"
                            @can('updateAny', \App\Models\Room::class) style="fill: {{ $rooms->firstWhere('name', $roomNumber)->color() }}" @endcan
                            d="{{$roomCoords['room'.$roomNumber]}}"/>
                        <text transform="{{$roomCoords['text'.$roomNumber]}}" class="st4">{{$roomNumber}}</text>
                        @endforeach
                        @foreach ($specialRoomsSecondFloor as $roomNumber)
                        <path id="{{$roomNumber}}" class="st2" d="{{$roomCoords[$roomNumber]}}"/>
                        @endforeach
                        <text transform="matrix(1 0 0 1 450.7761 670)" class="st11 on-background">2. emelet</text>
                        <text transform="matrix(1 0 0 1 498.2905 578.1061)" class="st8">Társalgó</text>
                        <text transform="matrix(1 0 0 1 804.2112 326.2228)" class="st8">Fürdő</text>
                        <text transform="matrix(1 0 0 1 209.2284 320.0796)" class="st8">Fürdő</text>
                        <text transform="matrix(1 0 0 1 951.8069 502.4353)" class="st8">Lépcsők</text>
                        <text transform="matrix(1 0 0 1 49.1869 499.1605)" class="st8">Lépcsők</text>
                        <text transform="matrix(1 0 0 1 805.6963 268.3699)" class="st8">Konyha</text>
                        <text transform="matrix(1 0 0 1 195.0278 258.3173)" class="st8">Konyha</text>
                        <text transform="matrix(1 0 0 1 803.2761 380.1979)" class="st8">WC</text>
                        <text transform="matrix(1 0 0 1 226.1865 378.8683)" class="st8">WC</text>
                    </g>
                    <g id="em3">
                        <rect x="272.2" y="15.4" class="st9" width="503" height="503"/>
                        @foreach ($roomNumbersThirdFloor as $roomNumber)
                        <polygon id="{{'room'.$roomNumber}}"
                            @can('updateAny', \App\Models\Room::class) style="fill: {{ $rooms->firstWhere('name', $roomNumber)->color() }}" @endcan
                            data-info="{{ $rooms->firstWhere('name', $roomNumber)->users->pluck('name')->join(';') }}"
                            class="st1"
                            points="{{$roomCoords['room'.$roomNumber]}}"/>
                        <text transform="{{$roomCoords['text'.$roomNumber]}}" class="st4">{{$roomNumber}}</text>
                        @endforeach
                        @foreach ($specialRoomsThirdFloor as $roomNumber)
                        <polygon id="{{$roomNumber}}" class="st2" points="{{$roomCoords[$roomNumber]}}"/>
                        @endforeach
                        <text transform="matrix(1 0 0 1 450.7761 425.4346)" class="st10 st11">3. emelet</text>
                        <text transform="matrix(1 0 0 1 294.3202 380.984)" class="st8">Lépcsők</text>
                        <text transform="matrix(1 0 0 1 705.4177 379.0125)" class="st8">Lépcsők</text>
                        <text transform="matrix(1 0 0 1 590.45 215.7253)" class="st8">Fürdő</text>
                        <text transform="matrix(1 0 0 1 425.6315 216.0271)" class="st8">Fürdő</text>
                        <text transform="matrix(1 0 0 1 596.1133 268.6588)" class="st8">WC</text>
                        <text transform="matrix(1 0 0 1 431.327 268.8032)" class="st8">WC</text>
                    </g>
                </svg>
                <div class="row" style="align-items: center">
                    <x-input.text s=9  id="nameInput" placeholder=" " text="Keress névre vagy szobaszámra" type="text" />
                    <x-input.button s=3 id="nameSubmit" class="right primary" text="Keresés" />
                    @can('updateAny', \App\Models\Room::class)
                        <div class="col center s12">
                            <x-input.button class="primary" text="Szobabeosztás szerkesztése" :href="route('rooms.modify')"/>
                        </div>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
