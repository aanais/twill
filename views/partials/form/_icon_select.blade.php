@php
    $options = $options;

    $placeholder = $placeholder ?? false;
    $required = $required ?? false;
    $searchable = $searchable ?? true;

    // do not use for now, but this will allow you to create a new option directly from the form
    $addNew = $addNew ?? false;
    $moduleName = $moduleName ?? null;
    $storeUrl = $storeUrl ?? '';
    $inModal = $fieldsInModal ?? false;
    $optgroup = $optgroup ?? null;
@endphp

<a17-slimselect
    label="{{ $label }}"
    @include('twill::partials.form.utils._field_name')
    :options='{{ json_encode($options) }}'
    @if ($emptyText ?? false) empty-text="{{ $emptyText }}" @endif
    @if ($placeholder) placeholder="{{ $placeholder }}" @endif
    @if (isset($default)) :selected="{{ json_encode(collect($options)->first(function ($option) use ($default) {
        return $option['value'] === $default;
    })) }}" @endif
    @if ($required) :required="true" @endif
    @if ($inModal) :in-modal="true" @endif
    :is-html="true"
    optgroup="namespace"
    :show-icons="true"
    :has-default-store="true"
    @if ($searchable) :searchable="true" @endif
    size="large"
    in-store="inputValue"
>
</a17-slimselect>


@unless($renderForBlocks || $renderForModal || (!isset($item->$name) && null == $formFieldsValue = getFormFieldsValue($form_fields, $name)))
@push('vuexStore')
    @include('twill::partials.form.utils._selector_input_store')
@endpush
@endunless
