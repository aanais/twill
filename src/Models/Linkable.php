<?php

namespace A17\Twill\Models;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use ImageService;

class Linkable extends Model
{
    public function getTable()
    {
        return config('twill.linkables_table', 'twill_linkables');
    }

    public function linked()
    {
        
    }
}
