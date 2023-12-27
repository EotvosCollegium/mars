@php
    $value = Cache::remember($model::cacheKey(), $model::$cacheSeconds, function () use ($model) { return $model::notificationCount(); });
@endphp
@if ($value != 0)
    <span class="new badge" data-badge-caption="">{{ $value }}</span>
@endif
