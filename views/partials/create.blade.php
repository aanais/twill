@formField('input', [
    'name' => $titleFormKey ?? 'title',
    'label' => $titleFormKey === 'title' ? twillTrans('twill::lang.modal.title-field') : ucfirst($titleFormKey),
    'translated' => $translateTitle ?? false,
    'required' => true,
    'onChange' => 'formatPermalink',
    'ref' => 'permalinkField',
])

@include('twill::partials.create_permalink')
