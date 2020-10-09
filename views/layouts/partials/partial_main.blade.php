<!DOCTYPE html>
<html dir="ltr" lang="{{ config('twill.locale', 'en') }}">
    <head>
        @include('twill::partials.head')
    </head>
    <body class="env env--{{ app()->environment() }} @yield('appTypeClass')">
        <div class="svg-sprite">
            @if(config('twill.dev_mode', false))
                {!! file_get_contents(twillAsset('icons.svg')) !!}
                {!! file_get_contents(twillAsset('icons-files.svg')) !!}
                {!! file_get_contents(twillAsset('icons-wysiwyg.svg')) !!}
            @else
                {!! File::exists(public_path(twillAsset('icons.svg'))) ? File::get(public_path(twillAsset('icons.svg'))) : '' !!}
                {!! File::exists(public_path(twillAsset('icons-files.svg'))) ? File::get(public_path(twillAsset('icons-files.svg'))) : '' !!}
                {!! File::exists(public_path(twillAsset('icons-wysiwyg.svg'))) ? File::get(public_path(twillAsset('icons-wysiwyg.svg'))) : '' !!}
            @endif
        </div>
        <div >
            <section class="main">
                <div id="app" v-cloak>
                    @yield('content')
                    @if (config('twill.enabled.media-library') || config('twill.enabled.file-library'))
                        <a17-medialibrary ref="mediaLibrary"
                                          :authorized="{{ json_encode(auth('twill_users')->user()->can('upload')) }}" :extra-metadatas="{{ json_encode(array_values(config('twill.media_library.extra_metadatas_fields', []))) }}"
                                          :translatable-metadatas="{{ json_encode(array_values(config('twill.media_library.translatable_metadatas_fields', []))) }}"
                        ></a17-medialibrary>
                        <a17-dialog ref="warningMediaLibrary" modal-title="Delete media" confirm-label="Delete">
                            <p class="modal--tiny-title"><strong>Delete media</strong></p>
                            <p>Are you sure ?<br />This change can't be undone.</p>
                        </a17-dialog>
                    @endif
                    <a17-notif variant="success"></a17-notif>
                    <a17-notif variant="error"></a17-notif>
                    <a17-notif variant="info" :auto-hide="false" :important="false"></a17-notif>
                    <a17-notif variant="warning" :auto-hide="false" :important="false"></a17-notif>
                </div>
                <div class="appLoader">
                    <span>
                        <span class="loader"><span></span></span>
                    </span>
                </div>
            </section>
        </div>
        <script>
            window['{{ config('twill.js_namespace') }}'] = {};
            window['{{ config('twill.js_namespace') }}'].version = '{{ config('twill.version') }}';
            window['{{ config('twill.js_namespace') }}'].twillLocalization = {!! json_encode($twillLocalization) !!};
            window['{{ config('twill.js_namespace') }}'].STORE = {};
            window['{{ config('twill.js_namespace') }}'].STORE.form = {};
            window['{{ config('twill.js_namespace') }}'].STORE.medias = {};
            window['{{ config('twill.js_namespace') }}'].STORE.medias.types = [];
            window['{{ config('twill.js_namespace') }}'].STORE.languages = {!! json_encode(getLanguagesForVueStore($form_fields ?? [], $translate ?? false)) !!};

            @if (config('twill.enabled.media-library'))
                window['{{ config('twill.js_namespace') }}'].STORE.medias.types.push({
                    value: 'image',
                    text: '{{ twillTrans("twill::lang.media-library.images") }}',
                    total: {{ \A17\Twill\Models\Media::count() }},
                    endpoint: '{{ route('admin.media-library.medias.index') }}',
                    tagsEndpoint: '{{ route('admin.media-library.medias.tags') }}',
                    uploaderConfig: {!! json_encode($mediasUploaderConfig) !!}
                });
            @endif

            @if (config('twill.enabled.file-library'))
                window['{{ config('twill.js_namespace') }}'].STORE.medias.types.push({
                    value: 'file',
                    text: '{{ twillTrans("twill::lang.media-library.files") }}',
                    total: {{ \A17\Twill\Models\File::count() }},
                    endpoint: '{{ route('admin.file-library.files.index') }}',
                    tagsEndpoint: '{{ route('admin.file-library.files.tags') }}',
                    uploaderConfig: {!! json_encode($filesUploaderConfig) !!}
                });
            @endif


            @yield('initialStore')

            window.STORE = {}
            window.STORE.form = {}
            window.STORE.publication = {}
            window.STORE.medias = {}
            window.STORE.medias.types = []
            window.STORE.medias.selected = {}
            window.STORE.browsers = {}
            window.STORE.browsers.selected = {}

            @stack('vuexStore')
        </script>
        <script src="{{ twillAsset('chunk-vendors.js') }}"></script>
        <script src="{{ twillAsset('chunk-common.js') }}"></script>
        @stack('extra_js')
    </body>
</html>
