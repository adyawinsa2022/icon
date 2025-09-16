<x-layout>
    <div class="container pt-3 flex-grow-1 d-flex flex-column">
        <div class="card mb-3 shadow-sm">
            <div class="card-body">
                <h2>
                    {{ $article['name'] }}
                </h2>
                <br>
                {!! html_entity_decode($article['answer']) !!}
            </div>
        </div>
    </div>
</x-layout>
