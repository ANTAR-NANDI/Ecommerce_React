<div class="row row-cols-2 row-cols-md-3 g-3">
    @forelse($media as $item)
        <div class="col"><button type="button" class="media-choice" data-select-media="{{ $item->id }}" data-media-url="{{ $item->url }}" data-media-name="{{ e($item->original_name) }}"><img src="{{ $item->url }}" alt="{{ $item->original_name }}"><div class="p-2 media-file-name text-truncate">{{ $item->original_name }}</div></button></div>
    @empty
        <div class="col-12 text-center py-5" style="color:var(--muted)"><i class="bi bi-images fs-2 d-block mb-2"></i>No images found. Upload one in Media Library first.</div>
    @endforelse
</div>
@if($media->hasPages())
    <nav class="mt-4 d-flex justify-content-center" aria-label="Media pages"><ul class="pagination pagination-sm mb-0">
        @foreach($media->linkCollection() as $link)
            <li class="page-item {{ $link['active'] ? 'active' : '' }} {{ $link['url'] ? '' : 'disabled' }}"><a class="page-link" data-media-page href="{{ $link['url'] ?? '#' }}">{!! $link['label'] !!}</a></li>
        @endforeach
    </ul></nav>
@endif
