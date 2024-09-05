<!DOCTYPE html>
<html>
    <head>
        <title>{{$item->name}}</title>
        <style>
/* This hides the margins and the headers/footers added by the browser. */
@page {
    size: auto;
    margin: 0;
}

html, body {
    /* This makes the gray background color appear in print, too. */
    print-color-adjust: exact;
    padding: 3px 10px;
}

h1 {
    font-size: 14pt;
    text-align: center;
}

div.navbuttons {
    margin: 10px 5px;
}
/* hiding the navigation buttons when we really come to printing */
@media print {
    div.navbuttons {
        display: none;
    }
}

table {
    border: solid 2px black;
    width: 100%;
}

.timetable-block {
    box-sizing: border-box;
    border-top: solid 1px black;
    border-left: solid 1px black;
    font-weight: bold;
    padding: 3px;
    font-size: 10pt;
}

/* we won't have to deal with unverified reservations
   as they are to be hidden */

/* We are going to color the rectangles here,
   as the styles do not get imported by default.
   But we will use gray,
   for the sake of black-and-white printers. */
.red, .orange {
    background-color: #bbbbbb;
    color: black;
}
        </style>
    </head>
    <body>
        <h1>{{$item->name}}</h1>
        @livewire('timetable', [
            'items' => [$item],
            'days' => 5,
            'firstHour' => 6,
            'lastHour' => 23,
            'isPrintVersion' => true
        ])
    </body>
</html>
