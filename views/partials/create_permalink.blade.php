
@if ($permalink ?? true)

    @formField('input', [
        'name' => 'slug',
        'label' => twillTrans('twill::lang.modal.permalink-field'),
        'translated' => true,
        'ref' => 'permalink',
        'prefix' => $permalinkPrefix ?? '',
    ])

    @formField('checkbox', [
        'name' => 'update_permalink',
        'label' => 'Update permalink automatically',
        'ref' => 'updatePermalink',
        'default' => empty($item)
    ])
@endif
