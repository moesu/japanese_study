<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['device', 'word', 'reading'])]
class LearnedKotoba extends Model
{
}
