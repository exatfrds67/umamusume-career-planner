<?php

namespace App\Enums;

enum RivalRaceOutcome: string
{
    case Won = 'won';
    case Lost = 'lost';
    case DidNotRace = 'did_not_race';
}
