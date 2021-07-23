# Why this fork

Twill maintainers don't have much time to improve or fix the bugs in the CMS or review pull requests and while Twill is a fantastic tool those bugs are really slowing down our development process so we had to fork it and maintain our own version of Twill.

Twill is also really hard (or even impossible) to extend without changing this package so this version is bound to be significantly different from the upstream.

# If you are a developer outside of SMITH and JOSETTE

Don't post issues or pull request on this repository they will be ignored. You can post them on : https://github.com/area17/twill

# Changes made by SMITH and JOSETTE

## CRUD

### Slug

* Add an checkbox to prevent slugs from updating when you change the title of an item

### Partials

Partials are a way to display a module inside an other module.

For example let's imagine a module Office which hasMany Employees linked to it.

Just start by creating two independant modules Office and Employee, the Employee module doesn't have to be in the navigation for this to work.


Add the relation ship in the Office Model:

```php
class Office extends Model
{
  public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
```

Change the Office form.blade.php to add the "partial"


```php
@include('twill::partials.form.partial', [
    'module' => 'admin.employees', // Put the complete module name
    'label' => 'Employee',
    'relation' => 'employees',
    'foreignKey' => 'office_id',
])
```

You can change the Employee form.blade.php

You can add in this file fields that will not be displayed in the Office form.

```php
@extends('twill::layouts.form')

@section('contentFields')

@include('twill-offices::admin.employees.form_content')

@stop

```

You need to create a partial view for your Employee : form_partial.blade.php

This is the form that will be displayed in the Office form (note that the layout is different)

```php
@extends('twill::layouts.partials.partial_form')

@section('contentFields')

@include('twill-offices::admin.employees.form_content')

@stop

```

Employee form_content.blade.php

The list of all the common fields for your Employee.

```php

@formField('input', [
    'name' => 'summary',
    'label' => 'Summary',
    'type' => 'textarea',
    'translated' => true,
    'rows' => 3
])

...

```

## CMS

### Modules

* Allow modules to be loaded from a composer package. To configure the module you can change these properties in the module controller.

```php
  protected $previewView = 'package-name::admin.preview';
  protected $namespace = 'Package\Namespace';

  protected function getViewPrefix()
  {
      return 'package-name::admin';
  }
```

### Media Library

* Allow the user to replace file uploads
* Display the file upload URL to the user

## Forms

### Select

* Added a slim select mode by default (https://slimselectjs.com/) with a search bar and an user friendly multiple mode. Is is used by default.

If you want to go back to the original vSelect, you can add `'vSelect' => true` when displaying a `@formField('select')` 

### Icon selector

* Added an field used to select an icon.

Usage :

```php
  @formField('icon_select', [
      'name' => $name,
      'label' => $label,
      'placeholder' => $placeholder,
      'options' => [
          ['namespace' => 'Namespace / Category', 'value' => 'ID', 'label' => 'TITLE', 'html' => "<div class='icon'>" .File::get(base_path($SVG_ICON_FILE)). "</div><div>$TITLE</div>"],...
      ];
      })->toArray(),
  ])
```

### Time picker

* Allow timepicker to be displayed in 24H format and allow customization of the timeformat

Example:

```php

@formField('date_picker', [
    'name' => 'last_contacted_at',
    'label' => 'Date of last contact',
    'withTime' => true,
    'time24Hr' => true,
    'altFormat' => 'd F Y H:i', // Format used by Flatpick
])

```

You can also change the default format in the publications fields by setting these values in twill.php

```php
return [
    'publish_date_format' => 'd F Y H:i', // Format used by Flatpick
    'publish_date_24h' => true, // Enable the 24h format
    'publish_date_format_fns' => 'DD MMMM YYYY HH:mm', // Format used by date-fns
];
```

## Blocks

### Publication date and locale

Add a new option on every block to set a publication date and select all locales where the block should be displayed.

### Bugfixes

* Fixe recursive blocks preview
* Fixed a lot of bugs in the settings, mostly in multiple locale settings or with images

## Quill

Fixed the quill configuration to avoid this bug : https://github.com/quilljs/parchment/issues/87

