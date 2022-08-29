@extends('layouts.app')
@section('title')
<i class="material-icons left">bed</i>@lang('rooms.rooms')
@endsection

<style>

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
  border: 2px solid grey;
  border-radius: 5px;
  padding: 5px;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
}

</style>

<script type="text/javascript" src="{{ mix('js/jquery.min.js') }}"></script>
<script>
    $( document ).ready(function() {
        var lastFillColor = "#FFFFFF"
        $("path, polygon, rect").hover(function(e) {
            if(this.id.indexOf('room')>-1){
                lastFillColor=$(this).css('fill');
                $(this).css({'filter': 'grayscale(50%)'});
                $('#info-box').css('display','block');
                people=$(this).data('info').split(';');
                htmlString='';
                
                people.forEach(element => {
                    if(element!=''){
                        htmlString+='<li>'+element+'</li>';
                    }
                });
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
                $(this).css('fill', lastFillColor);
                $(this).css('filter', '');
                $('#info-box').css('display','none');
                $('#info-box').html('');
            }
        });
        $(document).mousemove(function(e) {
            $('#info-box').css('top',e.pageY-$(document).scrollTop()+10);
            $('#info-box').css('left',e.pageX+10);
        }).mouseover();
        $("#name").submit(function(e){
            query=$('#nameInput').val();
            query2=query;
            if(query.trim().length>0 && !isNaN(query)){
                query2='room'+query;
            }
            console.log(query);
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
                @php
                    $colorArray=array();
                    foreach ($rooms as $room) {
                        $color=$room->color();
                        array_push($colorArray, [ "id" => $room->name, "color" => $color]);
                    }
                    $roomColors=collect($colorArray);
                @endphp
                <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                        viewBox="0 0 1047.8 670" style="enable-background:new 0 0 1047.8 750;" xml:space="preserve">
                    <style type="text/css">
                        .st0{fill:none;}
                        .st1{fill:#FFFFFF;stroke:#000000;stroke-width:0.25;}
                        .st2{fill:#808080;stroke:#000000;stroke-width:0.25;}
                        .st4{font-family:'RobotoSlab-Regular'; font-size:16px; fill:#000000;stroke:#FFFFFF;stroke-width:0.0; }
                        .st8{fill:#FFFFFF;font-size:14.1184px;font-family:'RobotoSlab-Regular';}
                        .st9{fill:#C6C6C6;}
                        .st10{font-family:'MyriadPro-Regular';}
                        .st11{font-size:36px;}
                    </style>
                    <g id="em2" xmlns:inkscape="http://www.inkscape.org/namespaces/inkscape" inkscape:version="1.1.2 (b8e25be833, 2022-02-05)" xmlns:svg="http://www.w3.org/2000/svg" xmlns:sodipodi="http://sodipodi.sourceforge.net/DTD/sodipodi-0.dtd" sodipodi:docname="drawing.svg">
                        <path id="room231" style="fill: {{$roomColors->where('id',231)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 231)->pluck('name')->join(';') }}" class="st1" d="M29.6,229.7l0.7,50.1H119v-50.1H29.6z"/>
                        <path id="room232" style="fill: {{$roomColors->where('id',232)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 232)->pluck('name')->join(';') }}" class="st1" d="M28.6,173.8l0.7,53.3l89.6-0.6l-0.1-53.1L28.6,173.8z"/>
                        <path id="room233" style="fill: {{$roomColors->where('id',233)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 233)->pluck('name')->join(';') }}" class="st1" d="M28.8,107.4l89.6-0.2l0.7,63.6L29,171L28.8,107.4z"/>
                        <path id="room234" style="fill: {{$roomColors->where('id',234)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 234)->pluck('name')->join(';') }}" class="st1" d="M172.3,107l0.9,63.4h90.4l-0.6-62.9L172.3,107z"/>
                        <path id="room235" style="fill: {{$roomColors->where('id',235)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 235)->pluck('name')->join(';') }}" class="st1" d="M173.1,173.1l0.9,52.8l89.8-0.2l-0.4-52.9L173.1,173.1z"/>
                        <path id="fiukonyha" class="st2" d="M173.6,228.6l0.4,50.3l89.8,0.2l-0.2-50.5L173.6,228.6z"/>
                        <path id="room230" style="fill: {{$roomColors->where('id',230)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 230)->pluck('name')->join(';') }}" class="st1" d="M29.9,282.9l0.4,62.7l90-0.2l-1.1-63.2L29.9,282.9z"/>
                        <path id="room229" style="fill: {{$roomColors->where('id',229)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 229)->pluck('name')->join(';') }}" class="st1" d="M30.1,348.2l0.7,52.6l90-0.4l-0.6-52.2L30.1,348.2z"/>
                        <path id="room228" style="fill: {{$roomColors->where('id',228)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 228)->pluck('name')->join(';') }}" class="st1" d="M30.7,403.6v61.2l90.7-0.2l-0.6-60.8L30.7,403.6z"/>
                        <path id="room227" style="fill: {{$roomColors->where('id',227)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 227)->pluck('name')->join(';') }}" class="st1" d="M29.6,529.3l26.8-0.2l-0.4,54.4l-26.4,0.4L29.6,529.3z"/>
                        <path id="room226" style="fill: {{$roomColors->where('id',226)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 226)->pluck('name')->join(';') }}" class="st1" d="M29.9,586.3l-1.1,55.9l80.6-0.7l0.6-55.2H29.9z"/>
                        <path id="room225" style="fill: {{$roomColors->where('id',225)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 225)->pluck('name')->join(';') }}" class="st1" d="M113.2,586.1l-0.6,55.8l54.4-0.2l0.4-69.6H145V586L113.2,586.1z"/>
                        <path id="em2fiufurdo" class="st2" d="M173.8,282.3l0.6,63.4l14.4-0.4l-0.4-15.5l3.6-0.2v16.1h72.2l-0.2-63.6h-72.6l0.6,34.8
                            l-3.2-0.6l-0.2-34.6L173.8,282.3z"/>
                        <path id="em2fiuwc" class="st2" d="M174.2,350.2l0.6,46.4h30.7v-6h3.6l-0.2,6l55.6-0.2l0.2-46.2l-56.3,0.2l0.4,28.3l-3.2-0.6
                            l-0.4-27.7L174.2,350.2z"/>
                        <path id="room224" style="fill: {{$roomColors->where('id',224)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 224)->pluck('name')->join(';') }}" class="st1" d="M174.9,525.5h51.4l-0.2,92l-51.4,0.4L174.9,525.5z"/>
                        <path id="room223" style="fill: {{$roomColors->where('id',223)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 223)->pluck('name')->join(';') }}" class="st1" d="M228.6,525.5l43.4,0.4l0.2,91.9h-44.2v-92.2H228.6z"/>
                        <path id="room222" style="fill: {{$roomColors->where('id',222)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 222)->pluck('name')->join(';') }}" class="st1" d="M274.5,525.5l47.9,0.2l0.6,91.9l-48.5,0.2L274.5,525.5z"/>
                        <path id="room221" style="fill: {{$roomColors->where('id',221)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 221)->pluck('name')->join(';') }}" class="st1" d="M324.4,525.5h46.4v92l-46.4,0.2V525.5z"/>
                        <path id="room220" style="fill: {{$roomColors->where('id',220)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 220)->pluck('name')->join(';') }}" class="st1" d="M372.5,525.5l51.1,0.2l-0.4,92h-50.5L372.5,525.5z"/>
                        <path id="em2fiulepcso" class="st2" d="M30.1,471.7l0.4,46.4l90.7-0.4l-0.4-46L30.1,471.7z"/>
                        <path id="tarsalgo" class="st2" d="M430.5,525.5v100.8h189l-0.2-100.5L430.5,525.5z"/>
                        <path id="room218" style="fill: {{$roomColors->where('id',218)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 218)->pluck('name')->join(';') }}" class="st1" d="M626,525.7l0.4,92.4l55.8,0.2l-0.2-92.8L626,525.7z"/>
                        <path id="room217" style="fill: {{$roomColors->where('id',217)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 217)->pluck('name')->join(';') }}" class="st1" d="M685.3,525.7l40,0.2l0.2,92.6l-40.2-0.4V525.7z"/>
                        <path id="room216" style="fill: {{$roomColors->where('id',216)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 216)->pluck('name')->join(';') }}" class="st1" d="M728.3,525.9l38.4,0.2l0.4,92l-38.5,0.2L728.3,525.9z"/>
                        <path id="room215" style="fill: {{$roomColors->where('id',215)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 215)->pluck('name')->join(';') }}" class="st1" d="M770.1,525.9l45.8,0.4l0.7,92.2l-46-0.2l-0.4-91.5L770.1,525.9z"/>
                        <path id="room214" style="fill: {{$roomColors->where('id',214)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 214)->pluck('name')->join(';') }}" class="st1" d="M820.2,526.3l55.6,0.4l0.7,92.4l-56.5-0.6l-0.4-92.2H820.2z"/>
                        <path id="room213" style="fill: {{$roomColors->where('id',213)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 213)->pluck('name')->join(';') }}" class="st1" d="M884.6,576.8l21.1,0.4l-0.2,10.7l23.2,0.9l-0.2,54.6l-44.3-0.4l-0.6-66.2H884.6z"/>
                        <path id="room212" style="fill: {{$roomColors->where('id',212)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 212)->pluck('name')->join(';') }}" class="st1" d="M931.7,588.4l90.4,0.2l-0.9,54.8h-89.4V588.4z"/>
                        <path id="room211" style="fill: {{$roomColors->where('id',211)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 211)->pluck('name')->join(';') }}" class="st1" d="M991,529.3l0.7,56.3l30.1-0.6l0.2-55.2L991,529.3z"/>
                        <path id="em2lanylepcso" class="st2" d="M933.2,476.1l0.4,41.7l88.5,0.6v-41.9L933.2,476.1z"/>
                        <path id="room210" style="fill: {{$roomColors->where('id',210)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 210)->pluck('name')->join(';') }}" class="st1" d="M930.9,468.6l90.7,0.4l0.7-53.9l-91.7-0.7L930.9,468.6z"/>
                        <path id="room209" style="fill: {{$roomColors->where('id',209)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 209)->pluck('name')->join(';') }}" class="st1" d="M930.7,349.8l-0.2,62l91.5-0.2v-61.9L930.7,349.8z"/>
                        <path id="room208" style="fill: {{$roomColors->where('id',208)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 208)->pluck('name')->join(';') }}" class="st1" d="M931,293.7l0.2,53.3l90.8-0.7l0.7-52.2L931,293.7z"/>
                        <path id="room207" style="fill: {{$roomColors->where('id',207)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 207)->pluck('name')->join(';') }}" class="st1" d="M931,238.9l0.2,51.8l91.7-0.4l-0.2-52l-91.5-0.4L931,238.9z"/>
                        <path id="room206" style="fill: {{$roomColors->where('id',206)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 206)->pluck('name')->join(';') }}" class="st1" d="M931.1,172.5l0.4,62.7h90.9v-62.5L931.1,172.5z"/>
                        <path id="room205B" style="fill: {{$roomColors->where('id','205B')->pluck('color')->join(',')}}" data-info="{{ $users->where('room', '205B')->pluck('name')->join(';') }}" class="st1" d="M930.8,106.5l0.6,63.2l90.7-0.4v-62.1L930.8,106.5z"/>
                        <path id="room205A" style="fill: {{$roomColors->where('id','205A')->pluck('color')->join(',')}}" data-info="{{ $users->where('room', '205A')->pluck('name')->join(';') }}" class="st1" d="M784.6,105.7l91.1,0.4l-0.2,63.4l-90.7,0.2L784.6,105.7z"/>
                        <path id="room204" style="fill: {{$roomColors->where('id',204)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 204)->pluck('name')->join(';') }}" class="st1" d="M784.8,172.7l-0.7,62.9h91.7v-63.2L784.8,172.7z"/>
                        <path id="em2lanykonyha" class="st2" d="M784.3,238.7l-0.2,51.8l91.3,0.2l-0.4-52L784.3,238.7z"/>
                        <path id="em2lanyfurdo" class="st2" d="M784.3,294.1l-0.6,52.9l73.7-0.4l0.4-25.3l3.4,0.2v25.3l13.8-0.2l0.4-52.8h-14.6l0.4,14h-3
                            l-0.4-13.8L784.3,294.1z"/>
                        <path id="em2lanywc" class="st2" d="M783.7,349.9v47.5h58.7l-0.2-7.3h2.8l0.2,7.5l29.6-0.7l-0.2-47h-29.4l-0.2,28.8l-3.2-0.4
                            l0.2-28.4L783.7,349.9z"/>
                        <text transform="matrix(1 0 0 1 814.5881 210.9719)" class="st4">204</text>
                        <text transform="matrix(1 0 0 1 810.4586 144.5031)" class="st4">205A</text>
                        <text transform="matrix(1 0 0 1 959.3518 144.3186)" class="st4">205B</text>
                        <text transform="matrix(1 0 0 1 962.5569 209.5066)" class="st4">206</text>
                        <text transform="matrix(1 0 0 1 963.2227 271.8166)" class="st4">207</text>
                        <text transform="matrix(1 0 0 1 962.5564 329.4761)" class="st4">208</text>
                        <text transform="matrix(1 0 0 1 962.5563 388.0116)" class="st4">209</text>
                        <text transform="matrix(1 0 0 1 963.2225 448.2936)" class="st4">210</text>
                        <text transform="matrix(1 0 0 1 643.1637 571.5956)" class="st4">218</text>
                        <text transform="matrix(1 0 0 1 383.7706 576.1368)" class="st4">220</text>
                        <text transform="matrix(1 0 0 1 335.6474 575.7629)" class="st4">221</text>
                        <text transform="matrix(1 0 0 1 285.2772 575.9521)" class="st4">222</text>
                        <text transform="matrix(1 0 0 1 236.5207 575.9521)" class="st4">223</text>
                        <text transform="matrix(1 0 0 1 498.2905 578.1061)" class=" st8">Társalgó</text>
                        <text transform="matrix(1 0 0 1 804.2112 326.2228)" class=" st8">Fürdő</text>
                        <text transform="matrix(1 0 0 1 209.2284 320.0796)" class=" st8">Fürdő</text>
                        <text transform="matrix(1 0 0 1 693.5294 571.5957)" class="st4">217</text>
                        <text transform="matrix(1 0 0 1 735.4492 572.2106)" class="st4">216</text>
                        <text transform="matrix(1 0 0 1 735.4492 572.2106)" class="st4">216</text>
                        <text transform="matrix(1 0 0 1 780.1288 571.9301)" class="st4">215</text>
                        <text transform="matrix(1 0 0 1 834.3699 571.5957)" class="st4">214</text>
                        <text transform="matrix(1 0 0 1 803.2761 380.1979)" class=" st8">WC</text>
                        <text transform="matrix(1 0 0 1 226.1865 378.8683)" class=" st8">WC</text>
                        <text transform="matrix(1 0 0 1 951.8069 502.4353)" class=" st8">Lépcsők</text>
                        <text transform="matrix(1 0 0 1 49.1869 499.1605)" class=" st8">Lépcsők</text>
                        <text transform="matrix(1 0 0 1 805.6963 268.3699)" class=" st8">Konyha</text>
                        <text transform="matrix(1 0 0 1 195.0278 258.3173)" class=" st8">Konyha</text>
                        <text transform="matrix(1 0 0 1 189.379 575.9521)" class="st4">224</text>
                        <text transform="matrix(1 0 0 1 59.761 439.8028)" class="st4">228</text>
                        <text transform="matrix(1 0 0 1 59.7609 379.6693)" class="st4">229</text>
                        <text transform="matrix(1 0 0 1 59.761 320.0795)" class="st4">230</text>
                        <text transform="matrix(1 0 0 1 60.4275 261.3302)" class="st4">231</text>
                        <text transform="matrix(1 0 0 1 60.4275 207.7073)" class="st4">232</text>
                        <text transform="matrix(1 0 0 1 60.4273 144.3188)" class="st4">233</text>
                        <text transform="matrix(1 0 0 1 203.1653 144.5032)" class="st4">234</text>
                        <text transform="matrix(1 0 0 1 203.8315 207.1782)" class="st4">235</text>
                        <text transform="matrix(1 0 0 1 450.7761 670)" class="st10 st11">2. emelet</text>
                    </g>
                    <g id="em3">
                        <rect x="272.2" y="15.4" class="st9" width="503" height="503"/>
                        <polygon id="room324" style="fill: {{$roomColors->where('id',324)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 324)->pluck('name')->join(';') }}" class="st1" points="282.8,24.5 359.4,24.5 359.4,81.1 281.7,81.1 281.7,24.5 	"/>
                        <polygon id="room323" style="fill: {{$roomColors->where('id',323)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 323)->pluck('name')->join(';') }}" class="st1" points="282.2,83.9 359.7,83.9 359.7,131.7 281.7,131.7 	"/>
                        <polygon id="room322" style="fill: {{$roomColors->where('id',322)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 322)->pluck('name')->join(';') }}" class="st1" points="282.1,134.5 359.3,134.5 359.3,181.3 281.7,181.3 	"/>
                        <polygon id="room321" style="fill: {{$roomColors->where('id',321)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 321)->pluck('name')->join(';') }}" class="st1" points="281.7,183.9 359.3,183.9 359.7,239.1 280.9,239.1 	"/>
                        <polygon id="room320" style="fill: {{$roomColors->where('id',320)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 320)->pluck('name')->join(';') }}" class="st1" points="280.9,241.7 358.9,241.7 359.3,289.5 280.9,289.5 	"/>
                        <polygon id="room319" style="fill: {{$roomColors->where('id',319)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 319)->pluck('name')->join(';') }}" class="st1" points="281.3,292.1 359.3,291.6 359.3,347.2 281.3,347.5 	"/>
                        <polygon id="em3fiulepcso" class="st2" points="281.3,354.1 359.3,354.1 359.3,395.3 281.3,395.8 281.3,354.6 	"/>
                        <polygon id="room318" style="fill: {{$roomColors->where('id',318)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 318)->pluck('name')->join(';') }}" class="st1" points="281.3,406 341,406 341,448.4 281.7,448.4 	"/>
                        <polygon id="room317" style="fill: {{$roomColors->where('id',317)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 317)->pluck('name')->join(';') }}" class="st1" points="281.8,451.1 359.1,451.1 359.1,507.3 281.8,507.7 	"/>
                        <rect id="room316" style="fill: {{$roomColors->where('id',316)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 316)->pluck('name')->join(';') }}" x="361.7" y="436.1" class="st1" width="37.7" height="70.9"/>
                        <polygon id="em3fiuwc" class="st2" points="406.1,243.6 406.1,285 483.1,285 482.9,243.6 	"/>
                        <polygon id="em3fiufurdo" class="st2" points="406.1,183.9 406.1,239.1 483.1,239.4 483.1,183.9 	"/>
                        <rect id="room327" style="fill: {{$roomColors->where('id',327)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 327)->pluck('name')->join(';') }}" x="406.1" y="133.6" class="st1" width="77.1" height="47.3"/>
                        <polygon id="room326" style="fill: {{$roomColors->where('id',326)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 326)->pluck('name')->join(';') }}" class="st1" points="406.4,83.9 406.1,130.9 483.1,130.9 483.1,83.9 	"/>
                        <polygon id="room325" style="fill: {{$roomColors->where('id',325)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 325)->pluck('name')->join(';') }}" class="st1" points="406.5,24.9 406.1,80.6 483.1,81.1 483.1,24.5 	"/>
                        <polygon id="room307" style="fill: {{$roomColors->where('id',307)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 307)->pluck('name')->join(';') }}" class="st1" points="768.8,22.8 692.3,22.8 692.3,79.3 770,79.3 770,22.8 	"/>
                        <polygon id="room308" style="fill: {{$roomColors->where('id',308)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 308)->pluck('name')->join(';') }}" class="st1" points="769.5,82.1 692,82.1 692,129.9 770,129.9 	"/>
                        <polygon id="room309" style="fill: {{$roomColors->where('id',309)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 309)->pluck('name')->join(';') }}" class="st1" points="769.6,132.7 692.3,132.7 692.3,179.5 770,179.5 	"/>
                        <polygon id="room310" style="fill: {{$roomColors->where('id',310)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 310)->pluck('name')->join(';') }}" class="st1" points="770,182.1 692.3,182.1 692,237.3 770.8,237.3 	"/>
                        <polygon id="room311" style="fill: {{$roomColors->where('id',311)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 311)->pluck('name')->join(';') }}" class="st1" points="770.8,240 692.8,240 692.3,287.7 770.8,287.7 	"/>
                        <polygon id="room312" style="fill: {{$roomColors->where('id',312)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 312)->pluck('name')->join(';') }}" class="st1" points="770.3,290.3 692.3,289.9 692.3,345.4 770.3,345.7 	"/>
                        <polygon id="em3lanylepcso" class="st2" points="770.3,352.4 692.3,352.4 692.3,393.6 770.3,394 770.3,352.8 	"/>
                        <polygon id="room313" style="fill: {{$roomColors->where('id',313)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 313)->pluck('name')->join(';') }}" class="st1" points="770.3,404.3 710.7,404.3 710.7,446.6 770,446.6 	"/>
                        <polygon id="room314" style="fill: {{$roomColors->where('id',314)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 314)->pluck('name')->join(';') }}" class="st1" points="769.9,449.4 692.6,449.4 692.6,505.6 769.9,506 	"/>
                        <rect id="room315" style="fill: {{$roomColors->where('id',315)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 315)->pluck('name')->join(';') }}" x="652.2" y="434.4" class="st1" width="37.7" height="70.9"/>
                        <polygon id="em3lanywc" class="st2" points="645.6,241.9 645.6,283.2 568.5,283.2 568.8,241.9 	"/>
                        <polygon id="em3lanyfurdo" class="st2" points="645.6,182.1 645.6,237.3 568.5,237.6 568.5,182.1 	"/>
                        <rect id="room304" style="fill: {{$roomColors->where('id',304)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 304)->pluck('name')->join(';') }}" x="568.5" y="131.9" class="st1" width="77.1" height="47.3"/>
                        <polygon id="room305" style="fill: {{$roomColors->where('id',305)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 305)->pluck('name')->join(';') }}" class="st1" points="645.3,82.1 645.6,129.1 568.5,129.1 568.5,82.1 	"/>
                        <polygon id="room306" style="fill: {{$roomColors->where('id',306)->pluck('color')->join(',')}}" data-info="{{ $users->where('room', 306)->pluck('name')->join(';') }}" class="st1" points="645.2,23.1 645.6,78.9 568.5,79.3 568.5,22.8 	"/>
                        <text transform="matrix(1 0 0 1 306.5262 59.5236)" class="st4">324</text>
                        <text transform="matrix(1 0 0 1 430.0117 59.7083)" class="st4">325</text>
                        <text transform="matrix(1 0 0 1 593.5908 57.5637)" class="st4">306</text>
                        <text transform="matrix(1 0 0 1 718.7113 59.5236)" class="st4">307</text>
                        <text transform="matrix(1 0 0 1 430.6694 112.09)" class="st4">326</text>
                        <text transform="matrix(1 0 0 1 594.8462 112.2747)" class="st4">305</text>
                        <text transform="matrix(1 0 0 1 718.0535 112.4592)" class="st4">308</text>
                        <text transform="matrix(1 0 0 1 430.6694 163.2767)" class="st4">327</text>
                        <text transform="matrix(1 0 0 1 594.2482 161.8294)" class="st4">304</text>
                        <text transform="matrix(1 0 0 1 718.7296 324.8566)" class="st4">312</text>
                        <text transform="matrix(1 0 0 1 728.9939 431.9666)" class="st4">313</text>
                        <text transform="matrix(1 0 0 1 718.7109 484.3134)" class="st4">314</text>
                        <text transform="matrix(1 0 0 1 658.494 477.6721)" class="st4">315</text>
                        <text transform="matrix(1 0 0 1 721.4578 271.8165)" class="st4">311</text>
                        <text transform="matrix(1 0 0 1 719.2666 217.3047)" class="st4">310</text>
                        <text transform="matrix(1 0 0 1 718.0537 163.6458)" class="st4">309</text>
                        <text transform="matrix(1 0 0 1 305.989 113.6833)" class="st4">323</text>
                        <text transform="matrix(1 0 0 1 307.1841 165.7198)" class="st4">322</text>
                        <text transform="matrix(1 0 0 1 304.9073 217.3047)" class="st4">321</text>
                        <text transform="matrix(1 0 0 1 304.7187 271.8163)" class="st4">320</text>
                        <text transform="matrix(1 0 0 1 305.5652 326.223)" class="st4">319</text>
                        <text transform="matrix(1 0 0 1 366.7706 478.2176)" class="st4">316</text>
                        <text transform="matrix(1 0 0 1 306.5264 486.1055)" class="st4">317</text>
                        <text transform="matrix(1 0 0 1 297.4482 435.1146)" class="st4">318</text>
                        <text transform="matrix(1 0 0 1 450.7761 425.4346)" class="st10 st11">3. emelet</text>
                        <text transform="matrix(1 0 0 1 294.3202 380.984)" class="st8">Lépcsők</text>
                        <text transform="matrix(1 0 0 1 705.4177 379.0125)" class="st8">Lépcsők</text>
                        <text transform="matrix(1 0 0 1 590.45 215.7253)" class="st8">Fürdő</text>
                        <text transform="matrix(1 0 0 1 425.6315 216.0271)" class="st8">Fürdő</text>
                        <text transform="matrix(1 0 0 1 596.1133 268.6588)" class="st8">WC</text>
                        <text transform="matrix(1 0 0 1 431.327 268.8032)" class="st8">WC</text>
                    </g>
                </svg>


                <div class="row">
                    <form id="name", onsubmit="return false;" style="vertical-align: top">
                        <x-input.text s=9  id="nameInput" text="rooms.search_name" type="text" />
                        <x-input.button s=3 class="right coli blue" text="rooms.search" />
                    </form>
                </div>
                
                @can('updateAny', \App\Models\Room::class) 
                    <form method="post" id="update-all" action="{{route('rooms.update')}}">
                        @csrf
                        @method('put')
                        <div class="fixed-action-btn">
                            <button type="submit" class="btn-floating btn-large" value="update-all">
                                <i id="save_btn" class="large material-icons" value="update-all">save</i>
                            </button>
                        </div>
                    </form>
                    @foreach ($rooms as $room)
                        <div class="row">
                            @if ($room->capacity>1)
                                <form method="post" id="update-remove" action="{{route('rooms.update-capacity', $room->name)}}">
                                    @csrf
                                    @method('put')
                                    <input type="hidden" name="type" value="remove"/>
                                    <x-input.button s=3 class="right coli blue" icon="person_remove" value="update-remove"/>
                                </form>
                            @else
                                <h5 class="col s3 center"></h5>
                            @endif
                            <h5 class="col s6 center">{{$room->name}}</h5>
                            @if ($room->capacity<4)
                                <form method="post" id="update-add" action="{{route('rooms.update-capacity', $room->name)}}">
                                    @csrf
                                    @method('put')
                                    <input type="hidden" name="type" value="add"/>
                                    <x-input.button s=3 class="left coli blue" icon="person_add" value="update-add"/>
                                </form>
                            @endif
                        </div>
                        <div class="row">
                            @php
                                $width=floor(12/$room->capacity);
                                $users_in_room=$users->where('room', $room->name)->pluck('id');
                            @endphp
                            {{-- {{$users}} --}}
                            @for ($i = 1; $i <= $room->capacity; $i++)
                                @if ($users_in_room->count()>=$i)
                                    <x-input.select form="update-all" :s="$width" allowEmpty="true" name="rooms[{{$room->name}}][]" id="{{$room->name}}_person_{{$i}}" :elements="$users" :default="$users_in_room[$i-1]" text="rooms.resident{{$i}}"/>
                                @else
                                    <x-input.select form="update-all" :s="$width" allowEmpty="true" name="rooms[{{$room->name}}][]" id="{{$room->name}}_person_{{$i}}" :elements="$users" text="rooms.resident{{$i}}"/>
                                @endif
                            @endfor
                        </div>
                    @endforeach
                @endcan
            </div>
        </div>
    </div>
</div>
@endsection
