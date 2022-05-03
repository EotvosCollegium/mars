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
        $("path").hover(function(e) {
            if(this.id.indexOf('room')>-1){
                $(this).css({'fill': '#002868', 'cursor' : 'pointer'});
                $('#info-box').css('display','block');
                $('#info-box').html(
                    '<p>Lakók:</p>'+
                    '<ol>'+
                    //  {{}}   
                    '</ol>'
                );
            }
        }, function(e){
            if(this.id.indexOf('room')>-1){
                $(this).css('fill', 'white');
                $('#info-box').css('display','none');
            }
        });
        $(document).mousemove(function(e) {
            $('#info-box').css('top',e.pageY-$(document).scrollTop()+10);
            $('#info-box').css('left',e.pageX+10);
        }).mouseover();
        $("#name").submit(function(e){
            num=$('#nameInput').val();
            $('#room'+num).addClass('blink');
            $('#room'+num).css('fill', 'red');
            setTimeout(function(){
                $('#room'+num).removeClass('blink');
                $('#room'+num).css('fill', 'white');
            }, 5000)

        });
    });
    
</script>
@section('content')
<div class="row">
    <div class="col s12">
        <div class="card">
            <div class="card-content">
                <div id="info-box"></div>
                <svg
                    id="em2"
                    preserveAspectRatio="xMinYMin meet"
                    viewBox="0 0 998.75 543.75002"
                    version="1.1"
                    sodipodi:docname="2em.svg"
                    inkscape:version="1.1.2 (b8e25be833, 2022-02-05)"
                    xmlns:inkscape="http://www.inkscape.org/namespaces/inkscape"
                    xmlns:sodipodi="http://sodipodi.sourceforge.net/DTD/sodipodi-0.dtd"
                    xmlns="http://www.w3.org/2000/svg"
                    xmlns:svg="http://www.w3.org/2000/svg">
                    
                    <defs
                    id="defs2">
                    <pattern
                    id="EMFhbasepattern"
                    patternUnits="userSpaceOnUse"
                    width="6"
                    height="6"
                    x="0"
                    y="0" />
                    </defs>
                    <g
                        inkscape:groupmode="layer"
                        inkscape:label="Layer 2"
                        style="display:inline"
                        id="g117">
                        <path
                        style="fill:#ffffff;stroke:#000000;stroke-width:0.264583px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
                        d="M 2.811655,3.3889 92.42713,3.20181 93.17548,66.81196 2.998744,66.99905 Z"
                        id="room233"
                        data-info="" />
                        <path
                        style="fill:#ffffff;stroke:#000000;stroke-width:0.264583px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
                        d="M 2.624568,69.80537 3.372923,123.12565 92.9884,122.56439 92.8949,69.4312 Z"
                        id="room232"
                        sodipodi:nodetypes="ccccc" />
                        <path
                        style="fill:#ffffff;stroke:#000000;stroke-width:0.264583px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
                        d="m 3.560011,125.74489 0.748354,50.13976 88.680035,-1e-5 v -50.13975 z"
                        id="room231"
                        sodipodi:nodetypes="ccccc" />
                        <path
                        style="fill:#ffffff;stroke:#000000;stroke-width:0.264583px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
                        d="m 146.30867,3.01473 0.93545,63.42305 h 90.36382 L 237.04667,3.57599 Z"
                        id="room234"
                        sodipodi:nodetypes="ccccc" />
                        <path
                        style="fill:#ffffff;stroke:#000000;stroke-width:0.264583px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
                        d="m 147.05702,69.05702 0.93545,52.75901 89.80256,-0.18709 -0.37418,-52.94609 z"
                        id="room235" />
                        <path
                        style="fill:#808080;stroke:#000000;stroke-width:0.264583px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
                        d="m 147.61829,124.62236 0.37418,50.32685 89.80256,0.18709 -0.18709,-50.51394 z"
                        id="fiukonyha" />
                        <path
                        style="fill:#ffffff;stroke:#000000;stroke-width:0.264583px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
                        d="m 3.934189,178.87809 0.374177,62.67468 89.989654,-0.18708 -1.12253,-63.23598 z"
                        id="room230" />
                        <path
                        style="fill:#ffffff;stroke:#000000;stroke-width:0.264583px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
                        d="m 4.121276,244.17202 0.748355,52.57191 89.989649,-0.37417 -0.56126,-52.19774 z"
                        id="room229" />
                        <path
                        style="fill:#ffffff;stroke:#000000;stroke-width:0.264583px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
                        d="m 4.682542,299.55026 v 61.17799 l 90.738008,-0.18708 -0.56126,-60.80382 z"
                        id="room228"
                        sodipodi:nodetypes="ccccc" />
                        <path
                        style="fill:#ffffff;stroke:#000000;stroke-width:0.264583px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
                        d="m 3.560012,425.27387 26.753679,-0.18709 -0.374177,54.44281 -26.379504,0.37417 z"
                        id="room227"
                        sodipodi:nodetypes="ccccc" />
                        <path
                        style="fill:#ffffff;stroke:#000000;stroke-width:0.264583px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
                        d="m 3.934189,482.33592 -1.122534,55.9395 80.635225,-0.74834 0.56126,-55.19116 z"
                        id="room226" />
                        <path
                        style="fill:#ffffff;stroke:#000000;stroke-width:0.264583px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
                        d="m 87.18865,482.1488 -0.56127,55.75245 54.44281,-0.18709 0.37417,-69.597 h -22.45064 v 13.84456 z"
                        id="room225" />
                        <path
                        style="fill:#808080;stroke:#000000;stroke-width:0.264583px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
                        d="m 147.80538,178.3168 0.56126,63.42306 14.40583,-0.37417 -0.37418,-15.52835 3.55469,-0.18711 v 16.08963 h 72.21623 l -0.18709,-63.61015 h -72.59041 l 0.56127,34.79851 -3.18051,-0.56126 -0.18709,-34.61142 z"
                        id="em2fiufurdo" />
                        <path
                        style="fill:#808080;stroke:#000000;stroke-width:0.264583px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
                        d="m 148.17955,246.23 0.56127,46.39799 h 30.68255 v -5.98683 h 3.55468 l -0.18709,5.98683 55.56534,-0.18708 0.18709,-46.21091 -56.3137,0.18709 0.37418,28.25038 -3.1805,-0.56126 -0.37418,-27.68912 z"
                        id="em2fiuwc" />
                        <path
                        style="fill:#ffffff;stroke:#000000;stroke-width:0.264583px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
                        d="m 148.92791,421.53207 h 51.44939 l -0.18709,92.04765 -51.44939,0.37417 z"
                        id="room224" />
                        <path
                        style="fill:#ffffff;stroke:#000000;stroke-width:0.264583px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
                        d="m 202.62236,421.53207 43.40458,0.3742 0.18709,91.86053 h -44.15294 v -92.23473 z"
                        id="room223" />
                        <path
                        style="fill:#ffffff;stroke:#000000;stroke-width:0.264583px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
                        d="m 248.45907,421.53207 47.89472,0.18712 0.56126,91.86053 -48.45598,0.18708 z"
                        id="room222" />
                        <path
                        style="fill:#ffffff;stroke:#000000;stroke-width:0.264583px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
                        d="m 298.41177,421.53207 h 46.39797 v 92.04765 l -46.39797,0.18708 z"
                        id="room221" />
                        <path
                        style="fill:#ffffff;stroke:#000000;stroke-width:0.264583px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
                        d="m 346.49355,421.53207 51.07522,0.18712 -0.3742,92.04761 h -50.51393 z"
                        id="room220" />
                        <path
                        style="fill:#808080;stroke:#000000;stroke-width:0.264583px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
                        d="m 4.121276,367.65055 0.374179,46.39799 90.738005,-0.37417 -0.37418,-46.02382 z"
                        id="em2fiulepcso" />
                        <path
                        style="fill:#808080;stroke:#000000;stroke-width:0.264583px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
                        d="M 404.49104,421.53207 V 522.37288 H 593.45061 L 593.2635,421.90627 Z"
                        id="tarsalgo" />
                        <path
                        style="fill:#ffffff;stroke:#000000;stroke-width:0.264583px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
                        d="m 599.99871,421.71919 0.37417,92.42179 55.75242,0.18708 -0.18709,-92.79599 z"
                        id="room218" />
                        <path
                        style="fill:#ffffff;stroke:#000000;stroke-width:0.264583px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
                        d="m 659.3058,421.71919 40.03699,0.18708 0.18709,92.60888 -40.22408,-0.37417 z"
                        id="room217" />
                        <path
                        style="fill:#ffffff;stroke:#000000;stroke-width:0.264583px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
                        d="m 702.33621,421.90627 38.35318,0.18709 0.37417,92.04762 -38.54027,0.18708 z"
                        id="room216" />
                        <path
                        style="fill:#ffffff;stroke:#000000;stroke-width:0.264583px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
                        d="m 744.05698,421.90627 45.83673,0.37418 0.74835,92.2347 -46.02382,-0.18709 -0.37417,-91.48635 z"
                        id="room215" />
                        <path
                        style="fill:#ffffff;stroke:#000000;stroke-width:0.264583px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
                        d="m 794.19674,422.28045 55.56533,0.37417 0.74838,92.42179 -56.5008,-0.56126 -0.37417,-92.2347 z"
                        id="room214" />
                        <path
                        style="fill:#ffffff;stroke:#000000;stroke-width:0.264583px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
                        d="m 858.55526,472.79438 21.141,0.37417 -0.18708,10.66406 23.19901,0.93544 -0.18709,54.62989 -44.34004,-0.37417 -0.56126,-66.22939 z"
                        id="room213" />
                        <path
                        style="fill:#ffffff;stroke:#000000;stroke-width:0.264583px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
                        d="m 905.7016,484.39387 90.3638,0.18709 -0.9354,54.81698 h -89.4284 z"
                        id="room212" />
                        <path
                        style="fill:#ffffff;stroke:#000000;stroke-width:0.264583px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
                        d="m 965.0087,425.27386 0.74835,56.31368 30.12125,-0.56126 0.1871,-55.19116 z"
                        id="room211" />
                        <path
                        style="fill:#808080;stroke:#000000;stroke-width:0.264583px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
                        d="m 907.1983,372.14069 0.37417,41.72077 88.49293,0.56126 v -41.90786 z"
                        id="em2lanylepcso" />
                        <path
                        style="fill:#ffffff;stroke:#000000;stroke-width:0.264583px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
                        d="m 904.94934,364.60233 90.74186,0.42898 0.7484,-53.88156 -91.67735,-0.71625 z"
                        id="room210"
                        sodipodi:nodetypes="ccccc" />
                        <path
                        style="fill:#ffffff;stroke:#000000;stroke-width:0.264583px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
                        d="m 904.70746,245.80103 -0.1777,62.00384 91.53564,-0.20979 v -61.92634 z"
                        id="room209"
                        sodipodi:nodetypes="ccccc" />
                        <path
                        style="fill:#ffffff;stroke:#000000;stroke-width:0.264583px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
                        d="m 904.95323,189.72921 0.155,53.32029 90.77007,-0.74835 0.7484,-52.19774 z"
                        id="room208"
                        sodipodi:nodetypes="ccccc" />
                        <path
                        style="fill:#ffffff;stroke:#000000;stroke-width:0.264583px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
                        d="m 904.95323,134.91224 0.18711,51.82355 91.67346,-0.37417 -0.1871,-52.01065 -91.48636,-0.37418 z"
                        id="room207" />
                        <path
                        style="fill:#ffffff;stroke:#000000;stroke-width:0.264583px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
                        d="m 905.14034,68.49576 0.37418,62.6747 H 996.4396 V 68.68285 Z"
                        id="room206" />
                        <path
                        style="fill:#ffffff;stroke:#000000;stroke-width:0.264583px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
                        d="m 904.76614,2.45346 0.56129,63.23597 90.73797,-0.37418 V 3.20181 Z"
                        id="room205B" />
                        <path
                        style="fill:#ffffff;stroke:#000000;stroke-width:0.264583px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
                        d="m 758.64989,1.7051 91.11218,0.37418 -0.18709,63.42306 -90.73801,0.18709 z"
                        id="room205A" />
                        <path
                        style="fill:#ffffff;stroke:#000000;stroke-width:0.264583px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
                        d="m 758.83697,68.68285 -0.74834,62.86179 h 91.67344 V 68.30867 Z"
                        id="room204" />
                        <path
                        style="fill:#808080;stroke:#000000;stroke-width:0.264583px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
                        d="m 758.27571,134.72515 -0.18708,51.82355 91.29927,0.18709 -0.37418,-52.01064 z"
                        id="em2lanykonyha" />
                        <path
                        style="fill:#808080;stroke:#000000;stroke-width:0.264583px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
                        d="m 758.27571,190.10341 -0.56126,52.94609 73.71295,-0.37418 0.37417,-25.25697 3.36759,0.18709 v 25.25697 l 13.84456,-0.18709 0.37418,-52.759 h -14.59291 l 0.37417,14.03165 h -2.99341 l -0.37418,-13.84456 z"
                        id="em2lanyfurdo" />
                        <path
                        style="fill:#808080;stroke:#000000;stroke-width:0.264583px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
                        d="m 757.71445,245.85583 v 47.52051 h 58.74584 l -0.18709,-7.29647 h 2.80633 l 0.18709,7.48356 29.56002,-0.74835 -0.18709,-46.95925 h -29.37293 l -0.18709,28.81164 -3.1805,-0.37417 0.18709,-28.43747 z"
                        id="em2lanywc" />
                    <text
                    xml:space="preserve"
                    style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:14.1184px;line-height:1.25;font-family:Roboto;-inkscape-font-specification:Roboto;stroke-width:0.882398"
                    x="761.58813"
                    y="82.218987"
                    id="text11608"><tspan
                        class="fix-stroke"
                        sodipodi:role="line"
                        id="tspan11606"
                        style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-family:Roboto;-inkscape-font-specification:Roboto;stroke:#ffffff;stroke-width:0.882398"
                        x="761.58813"
                        y="82.218987">204</tspan></text>
                    <text
                    xml:space="preserve"
                    style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:14.1184px;line-height:1.25;font-family:Roboto;-inkscape-font-specification:Roboto;display:inline;stroke-width:0.882398"
                    x="760.98792"
                    y="14.78949"
                    id="text11608-2"><tspan
                        class="fix-stroke"
                        sodipodi:role="line"
                        id="tspan11606-1"
                        style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-family:Roboto;-inkscape-font-specification:Roboto;stroke:#ffffff;stroke-width:0.882398"
                        x="760.98792"
                        y="14.78949">205A</tspan></text>
                    <text
                    xml:space="preserve"
                    style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:14.1184px;line-height:1.25;font-family:Roboto;-inkscape-font-specification:Roboto;display:inline;stroke-width:0.882398"
                    x="908.09625"
                    y="15.318649"
                    id="text11608-25"><tspan
                        class="fix-stroke"
                        sodipodi:role="line"
                        id="tspan11606-9"
                        style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-family:Roboto;-inkscape-font-specification:Roboto;stroke:#ffffff;stroke-width:0.882398"
                        x="908.09625"
                        y="15.318649">205B</tspan></text>
                    <text
                    xml:space="preserve"
                    style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:14.1184px;line-height:1.25;font-family:Roboto;-inkscape-font-specification:Roboto;display:inline;stroke-width:0.882398"
                    x="908.89001"
                    y="81.464493"
                    id="text11608-6"><tspan
                        class="fix-stroke"
                        sodipodi:role="line"
                        style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-family:Roboto;-inkscape-font-specification:Roboto;stroke:#ffffff;stroke-width:0.882398"
                        x="908.89001"
                        y="81.464493"
                        id="tspan35983">206</tspan></text>
                    <text
                    xml:space="preserve"
                    style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:14.1184px;line-height:1.25;font-family:Roboto;-inkscape-font-specification:Roboto;display:inline;stroke-width:0.882398"
                    x="907.5672"
                    y="146.81657"
                    id="text11608-23"><tspan
                        class="fix-stroke"
                        sodipodi:role="line"
                        id="tspan11606-3"
                        style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-family:Roboto;-inkscape-font-specification:Roboto;stroke:#ffffff;stroke-width:0.882398"
                        x="907.5672"
                        y="146.81657">207</tspan></text>
                    <text
                    xml:space="preserve"
                    style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:14.1184px;line-height:1.25;font-family:Roboto;-inkscape-font-specification:Roboto;display:inline;stroke-width:0.882398"
                    x="907.03796"
                    y="202.90823"
                    id="text11608-5"><tspan
                        class="fix-stroke"
                        sodipodi:role="line"
                        id="tspan11606-8"
                        style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-family:Roboto;-inkscape-font-specification:Roboto;stroke:#ffffff;stroke-width:0.882398"
                        x="907.03796"
                        y="202.90823">208</tspan></text>
                    <text
                    xml:space="preserve"
                    style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:14.1184px;line-height:1.25;font-family:Roboto;-inkscape-font-specification:Roboto;display:inline;stroke-width:0.882398"
                    x="907.26544"
                    y="260.32281"
                    id="text11608-5-9"><tspan
                        class="fix-stroke"
                        sodipodi:role="line"
                        id="tspan11606-8-2"
                        style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-family:Roboto;-inkscape-font-specification:Roboto;stroke:#ffffff;stroke-width:0.882398"
                        x="907.26544"
                        y="260.32281">209</tspan></text>
                    <g
                    aria-label="209"
                    id="text11608-1"
                    style="font-size:14.1184px;line-height:1.25;font-family:Roboto;-inkscape-font-specification:Roboto;display:inline;fill:#999999;stroke-width:0.882398" />
                    <text
                    xml:space="preserve"
                    style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:14.1184px;line-height:1.25;font-family:Roboto;-inkscape-font-specification:Roboto;display:inline;stroke-width:0.882398"
                    x="907.56708"
                    y="323.29364"
                    id="text11608-8"><tspan
                        class="fix-stroke"
                        sodipodi:role="line"
                        id="tspan11606-36"
                        style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-family:Roboto;-inkscape-font-specification:Roboto;stroke:#ffffff;stroke-width:0.882398"
                        x="907.56708"
                        y="323.29364">210</tspan></text>
                    <text
                    xml:space="preserve"
                    style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:14.1184px;line-height:1.25;font-family:Roboto;-inkscape-font-specification:Roboto;display:inline;stroke-width:0.882398"
                    x="602.1637"
                    y="434.59558"
                    id="text11608-27"><tspan
                        class="fix-stroke"
                        sodipodi:role="line"
                        id="tspan11606-5"
                        style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-family:Roboto;-inkscape-font-specification:Roboto;stroke:#ffffff;stroke-width:0.882398"
                        x="602.1637"
                        y="434.59558">218</tspan></text>
                    <text
                    xml:space="preserve"
                    style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:14.1184px;line-height:1.25;font-family:Roboto;-inkscape-font-specification:Roboto;display:inline;stroke-width:0.882398"
                    x="348.9946"
                    y="434.4187"
                    id="text11608-27-50"><tspan
                        class="fix-stroke"
                        sodipodi:role="line"
                        id="tspan11606-5-64"
                        style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-family:Roboto;-inkscape-font-specification:Roboto;stroke:#ffffff;stroke-width:0.882398"
                        x="348.9946"
                        y="434.4187">220</tspan></text>
                    <text
                    xml:space="preserve"
                    style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:14.1184px;line-height:1.25;font-family:Roboto;-inkscape-font-specification:Roboto;display:inline;stroke-width:0.882398"
                    x="301.89877"
                    y="434.41864"
                    id="text11608-27-18"><tspan
                        class="fix-stroke"
                        sodipodi:role="line"
                        id="tspan11606-5-9"
                        style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-family:Roboto;-inkscape-font-specification:Roboto;stroke:#ffffff;stroke-width:0.882398"
                        x="301.89877"
                        y="434.41864">221</tspan></text>
                    <text
                    xml:space="preserve"
                    style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:14.1184px;line-height:1.25;font-family:Roboto;-inkscape-font-specification:Roboto;display:inline;stroke-width:0.882398"
                    x="251.09879"
                    y="434.41864"
                    id="text11608-27-8"><tspan
                        class="fix-stroke"
                        sodipodi:role="line"
                        id="tspan11606-5-97"
                        style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-family:Roboto;-inkscape-font-specification:Roboto;stroke:#ffffff;stroke-width:0.882398"
                        x="251.09879"
                        y="434.41864">222</tspan></text>
                    <text
                    xml:space="preserve"
                    style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:14.1184px;line-height:1.25;font-family:Roboto;-inkscape-font-specification:Roboto;display:inline;stroke-width:0.882398"
                    x="204.00294"
                    y="434.41864"
                    id="text11608-27-9"><tspan
                        class="fix-stroke"
                        sodipodi:role="line"
                        id="tspan11606-5-1"
                        style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-family:Roboto;-inkscape-font-specification:Roboto;stroke:#ffffff;stroke-width:0.882398"
                        x="204.00294"
                        y="434.41864">223</tspan></text>
                    <text
                    xml:space="preserve"
                    style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:14.1184px;line-height:1.25;font-family:Roboto;-inkscape-font-specification:Roboto;display:inline;stroke-width:0.882398"
                    x="472.29047"
                    y="474.10614"
                    id="text11608-27-11"><tspan
                        class="fix-stroke"
                        sodipodi:role="line"
                        id="tspan11606-5-7"
                        style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-family:Roboto;-inkscape-font-specification:Roboto;stroke:#ffffff;stroke-width:0.882398"
                        x="472.29047"
                        y="474.10614">Társalgó</tspan></text>
                    <text
                    xml:space="preserve"
                    style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:14.1184px;line-height:1.25;font-family:Roboto;-inkscape-font-specification:Roboto;display:inline;stroke-width:0.882398"
                    x="779.20709"
                    y="222.22284"
                    id="text11608-27-7"><tspan
                        class="fix-stroke"
                        sodipodi:role="line"
                        id="tspan11606-5-8"
                        style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-family:Roboto;-inkscape-font-specification:Roboto;stroke:#ffffff;stroke-width:0.882398"
                        x="779.20709"
                        y="222.22284">Fürdő</tspan></text>
                    <text
                    xml:space="preserve"
                    style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:14.1184px;line-height:1.25;font-family:Roboto;-inkscape-font-specification:Roboto;display:inline;stroke-width:0.882398"
                    x="183.22842"
                    y="216.07962"
                    id="text11608-27-7-1"><tspan
                        class="fix-stroke"
                        sodipodi:role="line"
                        id="tspan11606-5-8-5"
                        style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-family:Roboto;-inkscape-font-specification:Roboto;stroke:#ffffff;stroke-width:0.882398"
                        x="183.22842"
                        y="216.07962">Fürdő</tspan></text>
                    <text
                    xml:space="preserve"
                    style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:14.1184px;line-height:1.25;font-family:Roboto;-inkscape-font-specification:Roboto;display:inline;stroke-width:0.882398"
                    x="661.51129"
                    y="434.96973"
                    id="text11608-27-1"><tspan
                        class="fix-stroke"
                        sodipodi:role="line"
                        id="tspan11606-5-0"
                        style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-family:Roboto;-inkscape-font-specification:Roboto;stroke:#ffffff;stroke-width:0.882398"
                        x="661.51129"
                        y="434.96973">217</tspan></text>
                    <text
                    xml:space="preserve"
                    style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:14.1184px;line-height:1.25;font-family:Roboto;-inkscape-font-specification:Roboto;display:inline;stroke-width:0.882398"
                    x="705.66418"
                    y="435.34387"
                    id="text11608-27-4"><tspan
                        class="fix-stroke"
                        sodipodi:role="line"
                        id="tspan11606-5-4"
                        style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-family:Roboto;-inkscape-font-specification:Roboto;stroke:#ffffff;stroke-width:0.882398"
                        x="705.66418"
                        y="435.34387">216</tspan></text>
                    <text
                    xml:space="preserve"
                    style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:14.1184px;line-height:1.25;font-family:Roboto;-inkscape-font-specification:Roboto;display:inline;stroke-width:0.882398"
                    x="746.82367"
                    y="434.96973"
                    id="text11608-27-10"><tspan
                        class="fix-stroke"
                        sodipodi:role="line"
                        id="tspan11606-5-6"
                        style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-family:Roboto;-inkscape-font-specification:Roboto;stroke:#ffffff;stroke-width:0.882398"
                        x="746.82367"
                        y="434.96973">215</tspan></text>
                    <text
                    xml:space="preserve"
                    style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:14.1184px;line-height:1.25;font-family:Roboto;-inkscape-font-specification:Roboto;display:inline;stroke-width:0.882398"
                    x="795.31171"
                    y="434.87891"
                    id="text11608-27-6"><tspan
                        class="fix-stroke"
                        sodipodi:role="line"
                        id="tspan11606-5-2"
                        style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-family:Roboto;-inkscape-font-specification:Roboto;stroke:#ffffff;stroke-width:0.882398"
                        x="795.31171"
                        y="434.87891">214</tspan></text>
                    <text
                    xml:space="preserve"
                    style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:14.1184px;line-height:1.25;font-family:Roboto;-inkscape-font-specification:Roboto;display:inline;stroke-width:0.882398"
                    x="777.09045"
                    y="276.19785"
                    id="text11608-27-19"><tspan
                        class="fix-stroke"
                        sodipodi:role="line"
                        id="tspan11606-5-70"
                        style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-family:Roboto;-inkscape-font-specification:Roboto;stroke:#ffffff;stroke-width:0.882398"
                        x="777.09045"
                        y="276.19785">WC</tspan></text>
                    <text
                    xml:space="preserve"
                    style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:14.1184px;line-height:1.25;font-family:Roboto;-inkscape-font-specification:Roboto;display:inline;stroke-width:0.882398"
                    x="925.80682"
                    y="398.43533"
                    id="text11608-27-19-6"><tspan
                        class="fix-stroke"
                        sodipodi:role="line"
                        id="tspan11606-5-70-5"
                        style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-family:Roboto;-inkscape-font-specification:Roboto;stroke:#ffffff;stroke-width:0.882398"
                        x="925.80682"
                        y="398.43533">Lépcsők</tspan></text>
                    <text
                    xml:space="preserve"
                    style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:14.1184px;line-height:1.25;font-family:Roboto;-inkscape-font-specification:Roboto;display:inline;stroke-width:0.882398"
                    x="23.186848"
                    y="395.16049"
                    id="text11608-27-19-6-7"><tspan
                        class="fix-stroke"
                        sodipodi:role="line"
                        id="tspan11606-5-70-5-0"
                        style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-family:Roboto;-inkscape-font-specification:Roboto;stroke:#ffffff;stroke-width:0.882398"
                        x="23.186848"
                        y="395.16049">Lépcsők</tspan></text>
                    <text
                    xml:space="preserve"
                    style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:14.1184px;line-height:1.25;font-family:Roboto;-inkscape-font-specification:Roboto;display:inline;stroke-width:0.882398"
                    x="199.79013"
                    y="275.66864"
                    id="text11608-27-19-9"><tspan
                        class="fix-stroke"
                        sodipodi:role="line"
                        id="tspan11606-5-70-7"
                        style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-family:Roboto;-inkscape-font-specification:Roboto;stroke:#ffffff;stroke-width:0.882398"
                        x="199.79013"
                        y="275.66864">WC</tspan></text>
                    <text
                    xml:space="preserve"
                    style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:14.1184px;line-height:1.25;font-family:Roboto;-inkscape-font-specification:Roboto;display:inline;stroke-width:0.882398"
                    x="783.96967"
                    y="166.66034"
                    id="text11608-27-5"><tspan
                        class="fix-stroke"
                        sodipodi:role="line"
                        id="tspan11606-5-3"
                        style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-family:Roboto;-inkscape-font-specification:Roboto;stroke:#ffffff;stroke-width:0.882398"
                        x="783.96967"
                        y="166.66034">Konyha</tspan></text>
                    <text
                    xml:space="preserve"
                    style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:14.1184px;line-height:1.25;font-family:Roboto;-inkscape-font-specification:Roboto;display:inline;stroke-width:0.882398"
                    x="169.0278"
                    y="154.31729"
                    id="text11608-27-5-8"><tspan
                        class="fix-stroke"
                        sodipodi:role="line"
                        id="tspan11606-5-3-7"
                        style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-family:Roboto;-inkscape-font-specification:Roboto;stroke:#ffffff;stroke-width:0.882398"
                        x="169.0278"
                        y="154.31729">Konyha</tspan></text>
                    <text
                    xml:space="preserve"
                    style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:14.1184px;line-height:1.25;font-family:Roboto;-inkscape-font-specification:Roboto;display:inline;stroke-width:0.882398"
                    x="151.61545"
                    y="434.41864"
                    id="text11608-27-46"><tspan
                        class="fix-stroke"
                        sodipodi:role="line"
                        id="tspan11606-5-89"
                        style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-family:Roboto;-inkscape-font-specification:Roboto;stroke:#ffffff;stroke-width:0.882398"
                        x="151.61545"
                        y="434.41864">224</tspan></text>
                    <text
                    xml:space="preserve"
                    style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:14.1184px;line-height:1.25;font-family:Roboto;-inkscape-font-specification:Roboto;display:inline;stroke-width:0.882398"
                    x="6.0946035"
                    y="311.65201"
                    id="text11608-27-61"><tspan
                        class="fix-stroke"
                        sodipodi:role="line"
                        id="tspan11606-5-99"
                        style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-family:Roboto;-inkscape-font-specification:Roboto;stroke:#ffffff;stroke-width:0.882398"
                        x="6.0946035"
                        y="311.65201">228</tspan></text>
                    <text
                    xml:space="preserve"
                    style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:14.1184px;line-height:1.25;font-family:Roboto;-inkscape-font-specification:Roboto;display:inline;stroke-width:0.882398"
                    x="6.6237717"
                    y="257.14783"
                    id="text11608-27-2"><tspan
                        class="fix-stroke"
                        sodipodi:role="line"
                        id="tspan11606-5-31"
                        style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-family:Roboto;-inkscape-font-specification:Roboto;stroke:#ffffff;stroke-width:0.882398"
                        x="6.6237717"
                        y="257.14783">229</tspan></text>
                    <text
                    xml:space="preserve"
                    style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:14.1184px;line-height:1.25;font-family:Roboto;-inkscape-font-specification:Roboto;display:inline;stroke-width:0.882398"
                    x="6.0946083"
                    y="191.53116"
                    id="text11608-27-0"><tspan
                        class="fix-stroke"
                        sodipodi:role="line"
                        id="tspan11606-5-25"
                        style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-family:Roboto;-inkscape-font-specification:Roboto;stroke:#ffffff;stroke-width:0.882398"
                        x="6.0946083"
                        y="191.53116">230</tspan></text>
                    <text
                    xml:space="preserve"
                    style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:14.1184px;line-height:1.25;font-family:Roboto;-inkscape-font-specification:Roboto;display:inline;stroke-width:0.882398"
                    x="5.0362778"
                    y="138.6145"
                    id="text11608-27-02"><tspan
                        class="fix-stroke"
                        sodipodi:role="line"
                        id="tspan11606-5-68"
                        style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-family:Roboto;-inkscape-font-specification:Roboto;stroke:#ffffff;stroke-width:0.882398"
                        x="5.0362778"
                        y="138.6145">231</tspan></text>
                    <text
                    xml:space="preserve"
                    style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:14.1184px;line-height:1.25;font-family:Roboto;-inkscape-font-specification:Roboto;display:inline;stroke-width:0.882398"
                    x="5.036272"
                    y="82.522827"
                    id="text11608-27-3"><tspan
                        class="fix-stroke"
                        sodipodi:role="line"
                        id="tspan11606-5-38"
                        style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-family:Roboto;-inkscape-font-specification:Roboto;stroke:#ffffff;stroke-width:0.882398"
                        x="5.036272"
                        y="82.522827">232</tspan></text>
                    <text
                    xml:space="preserve"
                    style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:14.1184px;line-height:1.25;font-family:Roboto;-inkscape-font-specification:Roboto;display:inline;stroke-width:0.882398"
                    x="4.5071049"
                    y="15.847809"
                    id="text11608-27-86"><tspan
                        class="fix-stroke"
                        sodipodi:role="line"
                        id="tspan11606-5-96"
                        style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-family:Roboto;-inkscape-font-specification:Roboto;stroke:#ffffff;stroke-width:0.882398"
                        x="4.5071049"
                        y="15.847809">233</tspan></text>
                    <text
                    xml:space="preserve"
                    style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:14.1184px;line-height:1.25;font-family:Roboto;-inkscape-font-specification:Roboto;display:inline;stroke-width:0.882398"
                    x="148.44044"
                    y="15.318642"
                    id="text11608-27-52"><tspan
                        class="fix-stroke"
                        sodipodi:role="line"
                        id="tspan11606-5-76"
                        style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-family:Roboto;-inkscape-font-specification:Roboto;stroke:#ffffff;stroke-width:0.882398"
                        x="148.44044"
                        y="15.318642">234</tspan></text>
                    <text
                    xml:space="preserve"
                    style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:14.1184px;line-height:1.25;font-family:Roboto;-inkscape-font-specification:Roboto;display:inline;stroke-width:0.882398"
                    x="149.49878"
                    y="81.993652"
                    id="text11608-27-69"><tspan
                        class="fix-stroke"
                        sodipodi:role="line"
                        id="tspan11606-5-85"
                        style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-family:Roboto;-inkscape-font-specification:Roboto;stroke:#ffffff;stroke-width:0.882398"
                        x="149.49878"
                        y="81.993652">235</tspan></text>
                    </g>
                    </svg>

                <div class="row">
                    <form id="name", onsubmit="return false;" style="vertical-align: top">
                        <x-input.text l=9  id="nameInput" text="rooms.name" type="text" />
                        <x-input.button l=3 class="right coli blue" text="rooms.search" />
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
