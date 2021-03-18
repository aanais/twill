<?php

namespace A17\Twill\Models\Behaviors;

use A17\Twill\Models\Media;
use Illuminate\Support\Arr;

trait HasLink
{
    protected $cropParamsKeys = [
        'crop_x',
        'crop_y',
        'crop_w',
        'crop_h',
    ];

    public function linked()
    {
        return $this->morphedByMany(
            Media::class,
            'linkable',
            config('twill.mediables_table', 'twill_mediables')
        )->withPivot(array_merge([
            'crop',
            'role',
            'crop_w',
            'crop_h',
            'crop_x',
            'crop_y',
            'lqip_data',
            'ratio',
            'metadatas',
        ], config('twill.media_library.translated_form_fields', false) ? ['locale'] : []))
            ->withTimestamps()->orderBy(config('twill.mediables_table', 'twill_mediables') . '.id', 'asc');
    }


}
