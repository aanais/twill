@php
$fieldName = Str::slug($label);
$sortable = !empty($sortable);
@endphp
 <div class="inserter"
    id="inserter{{$fieldName}}"
    data-insert-url="{{ route($module.'.partial.insert') }}"
    data-sort-url="{{ route($module.'.partial.sort') }}"
    data-container="container{{ $fieldName }}"
    data-csrf="{{ csrf_token() }}"
    >
    <input type="hidden" name="{{ $foreignKey }}" value="{{ $item->id }}">
    <input type="text" name="{{ $titleColumn ?? 'title' }}" placeholder="{{ $titlePlaceholder ?? 'Title' }}">
    <button type="button">Add a new {{ $label }}</button>
</div>

<div class="has-many-container" id="container{{ $fieldName }}">
    <div class="fieldset-many @if ($sortable) sortable @endif" style="width: 100%">
        @foreach ($item->$relation()->orderBy($order ?? ($sortable ? 'position' : 'begin_at'))->get() as $related)
            <div class="item" data-id="{{ $related->id }}"
                data-delete-url="{{ route($module.'.partial.delete', ['id' => $related->id]) }}"
            >
                <div class="delete" onclick="deleteItem{{ $fieldName }}({{ $related->id }})"></div>
                <iframe style="width: 100%; height: 100px"
                    onload="updateIframeHeight{{ $fieldName }}()"
                    src="{{ route($module.'.partial', ['id' => $related->id]) }}"></iframe>
            </div>
        @endforeach

    </div>
</div>



<script>
    document.addEventListener('DOMContentLoaded', () => {



        document.querySelectorAll('#inserter{{$fieldName}}').forEach(function (inserter) {
            inserter.querySelector('button').addEventListener('click', function() {
                var url = inserter.dataset.insertUrl;
                var data = new FormData();
                inserter.querySelectorAll('input').forEach(function (input) {
                    data.append(input.name, input.value);
                    if (input.type != 'hidden') {
                        input.value = '';
                    }
                });

                var xhr = new XMLHttpRequest();
                xhr.open("POST", url, true);
                xhr.setRequestHeader('X-CSRF-TOKEN', inserter.dataset.csrf);
                xhr.onreadystatechange = function() { // Call a function when the state changes.
                    if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
                        var json = JSON.parse(this.responseText);

                        var item = document.createElement('div');
                        item.classList.add('item');
                        item.dataset.id = json.id;
                        item.dataset.deleteUrl = json.delete;
                        var deleteDiv = document.createElement('div');
                        deleteDiv.classList.add('delete');
                        deleteDiv.addEventListener('click', function() { deleteItem{{ $fieldName }}(json.id) });

                        var iframe = document.createElement('iframe');
                        iframe.setAttribute('src', json.item);
                        iframe.style.width = '100%';
                        iframe.style.height = "100px";
                        iframe.onload = function() {
                            updateIframeHeight{{ $fieldName }}();
                            window.scrollTo({left: 0, top: iframe.getBoundingClientRect().top + window.scrollY, behavior: 'smooth'})
                        };
                        item.appendChild(deleteDiv);
                        item.appendChild(iframe);
                        document.getElementById('container{{ $fieldName }}').querySelector('.fieldset-many').appendChild(item);
                    }
                }
                xhr.send(data);
            });
        });

        document.querySelector('.publisher__wrapper').addEventListener('mouseenter', function() {
            document.getElementById('container{{ $fieldName }}').querySelectorAll('iframe').forEach(function (iframe) {
                var btn = iframe.contentDocument.querySelector('.publisher .button.button--validate');
                btn.click();
            });
        });

        tick{{ $fieldName }}();

        @if ($sortable)
            const form = document.querySelector('#container{{ $fieldName }} .fieldset-many');
            form.classList.add('bite');
            new Sortable(form, {
                animation: 150,
                onEnd: function() {
                    sort{{ $fieldName }}();
                }
            });
        @endif
    });

    function sort{{ $fieldName }}() {
        const inserter = document.querySelector('#inserter{{$fieldName}}');
        const container = document.querySelector('#container{{$fieldName}}');
        const url = inserter.dataset.sortUrl;
        let data = {};
        let count = 0;
        container.querySelectorAll('.item').forEach((item) => {
            data[item.dataset.id] = count++;
        });

        var xhr = new XMLHttpRequest();

        fetch(url, {
            method : "POST",
            headers: {
                'X-CSRF-TOKEN': inserter.dataset.csrf,
                'Content-Type': 'application/json'
            },
            body : JSON.stringify({positions: data})
        });
    }

    function deleteItem{{ $fieldName }}(id) {
        if (window.confirm("Are you sure you want to delete this item ?")) {
            var item = document.getElementById('container{{ $fieldName }}').querySelector('[data-id="' + id+ '"]');
            var deleteUrl = item.dataset.deleteUrl;
            var xhttp = new XMLHttpRequest();
            xhttp.open("GET", deleteUrl, true);
            xhttp.send();
            item.remove();
        }
    }

    function tick{{ $fieldName }}() {
        setTimeout(tick{{ $fieldName }}, 100);
        updateIframeHeight{{ $fieldName }}();
    }

    function updateIframeHeight{{ $fieldName }}() {
        document.getElementById('container{{ $fieldName }}').querySelectorAll('iframe').forEach(function (iframe) {
            if (!iframe.contentWindow.document || !iframe.contentWindow.document.body) {
                return;
            }
            iframe.style.height = iframe.contentWindow.document.body.scrollHeight + "px"
        });
    }
</script>

@if ($sortable)
    @prepend('extra_js')
        <script src="{{ asset('js/Sortable.min.js') }}"></script>
    @endprepend
@endif

@push('extra_css')

<style>
    .fieldset-many .item {
        position: relative;
        width: 100%;
    }


    .fieldset-many.sortable .item {
        padding-left: 20px;
    }

    .fieldset-many.sortable .item:before{
        content: "";
        position: absolute;
        left: -20px;
        top: 50%;
        background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAMgAAADICAYAAACtWK6eAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAALEwAACxMBAJqcGAAAA51JREFUeJzt3bFqHFcUBuA/suXeYWXkwtIDOEYpTFo160J+MgccHIR7oUeKIa4lV5JlHKeTkJBSXBYHeUGzq+HeYef74JR7+Vk4c2fuznISAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAKA3P7UOQJJkPclOkq0kN0mOk3xIctUyFLS2mWQ/ybeUxvh/fU3yLslGs3TQ0F5KE9xujNt1lmTaKCM0sZfkMnc3x6wuokkYic102znm7SSTBnmhqv0s3hyzetsgL1SznvkP5IvsImvVU0MlL7N8c8zqRfXUI+ZqVNdWD2ts97AGHWmQum5aB2AxGqSu4x7WOOphDRikh1nuiHdWp3FRq8qXXddVksN7fP4gyXVPWWCQNlKOaxfdPU6SPG6QF6qbprw+0rU5zpPsNkkKjUzTbSc5ieZgpCYpr4/Ma5TTJG/itqopf5gahrUkz/P9R8CjJB/jgRwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABYYQboDMN6kp0kWynTpY6TfEiZigujtZlkP8m3/DiC7WuSdylTcWF09lKa4K4hnmcpAz9hNPaSXKb7GOiLaBJGYjPddo55O8mkQV6oaj+LN8es3jbIC9WsZ/4D+SK7yFr11FDJyyzfHLN6UT31iLka1bXVwxrbPaxBRxqkrpvWAViMBqnruIc1jnpYAwbpYZY74p3VaVzUqvJl13WV5PAenz9Ict1TFhikjZTj2kV3j5MkjxvkheqmKa+PdG2O8yS7TZJCI9N020lOojkYqUnK6yPzGuU0yZu4rWrKH6aGYS3J83z/EfAoycd4IAcAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABWmAE6w/Aoya9JnqVMl/qU5K8kly1DQWtPk7xP8m9+HMH2T5I/kzxplg4aep3SBHcN8fyS5FWjjNDE65Tbp65joC9SpuLCynuabjvH7TpLmYoLK+19Fm+OWf3RIC9U8yjzH8i71pckD6qnhkp+y/LNMaud6qlHbK11gJF51sMa2z2sQUcapK6bgaxBRxqkrk89rHHUwxowSOtZ7oh3Vp/jolaVL7uuyySH9/j8QZLrnrLAID1JOa5ddPc4SfJzg7xQ3auU10e6Nsd5kt0mSaGRacrrI112Ds3BKE1SXh+Zd8v1OcnvcVvVlD9MDcODJL+k/Ah4k3KU+3c8kAMAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAq+Q/vrbt7vvWVnEAAAAASUVORK5CYII=);
        opacity: 1;
        width: 40px;
        height: 43px;
        background-size: contain;
        background-repeat: no-repeat;
        transform: translateY(-50%);
        cursor: move;
    }

    .fieldset-many .delete {
        content: url('data:image/svg+xml; utf8, <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 491.111 491.111" style="enable-background:new 0 0 491.111 491.111;" xml:space="preserve"><g id="XMLID_117_"><path id="XMLID_121_" d="M310.082,245.553l51.883-51.877c3.686-3.678,5.755-8.669,5.755-13.885c0-5.208-2.069-10.206-5.755-13.885l-36.769-36.759c-7.662-7.664-20.105-7.664-27.768,0l-51.87,51.876l-51.885-51.876c-7.662-7.664-20.089-7.664-27.768,0l-36.753,36.759c-3.688,3.68-5.756,8.678-5.756,13.885c0,5.216,2.068,10.207,5.756,13.885l51.876,51.877l-51.884,51.875c-7.672,7.681-7.672,20.108,0,27.779l36.753,36.761c7.679,7.662,20.106,7.662,27.769,0l51.893-51.885l51.878,51.885c7.662,7.662,20.106,7.662,27.768,0l36.769-36.761c7.655-7.671,7.655-20.098,0-27.779L310.082,245.553z"/><path id="XMLID_118_" d="M245.556,0C110.16,0,0,110.162,0,245.564c0,135.387,110.16,245.547,245.556,245.547c135.394,0,245.555-110.16,245.555-245.547C491.111,110.162,380.95,0,245.556,0z M245.556,438.198c-106.218,0-192.641-86.424-192.641-192.633c0-106.233,86.423-192.648,192.641-192.648c106.217,0,192.639,86.416,192.639,192.648C438.195,351.773,351.773,438.198,245.556,438.198z"/></g></svg>');
        position: absolute;
        width: 20px;
        height: 20px;
        cursor:pointer;
        right: 10px;
        top: 10px;
    }

    .inserter {
        margin: 20px 0;
        font-size: 15px;
        display: flex;
        justify-content: space-between;
    }

    .inserter * {
        padding: 5px;
        font-size: 15px;
    }

    .inserter input {
        width: 70%;
        border: 1px solid #d9d9d9;
    }

    .inserter button {
        width: 28%;
        background: #1d9f3c;
        color: #fff;
        border: 0;
        border: 1px solid #1d9f3c;
        cursor: pointer;
    }

    .has-many-container {
        margin-top: 20px;
    }
</style>
@endpush
