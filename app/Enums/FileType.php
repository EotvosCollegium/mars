<?php

namespace App\Enums;

enum FileType: string
{
    case PROFILE_PICTURE = 'profile_picture';
    case RECEIPT = 'receipt';
    case RESUME = 'resume';
    case BESOROLASI_HATAROZAT = 'besorolasi_hatarozat';
    case ERETTSEGI = 'erettsegi';
    case ELVEGZETT_FELEV = 'elvegzett_felev';
    case DIPLOMA = 'diploma';
    case APPLICATION_CUSTOM = 'application_custom';
}
