@props(['dot' => false])
@php
    $unseenAnnouncements = \App\Models\Announcement::unseenCountFor(auth()->user());
@endphp
@if($unseenAnnouncements > 0)
    @if($dot)
        <span class="absolute -top-1 -right-1 w-3.5 h-3.5 rounded-full bg-[#ff595e] ring-2 ring-white" role="status" aria-label="New announcements"></span>
    @else
        <span class="min-w-[1.5rem] text-center px-1.5 py-0.5 rounded-full bg-[#ff595e] text-white text-xs font-bold" role="status"
              title="New announcements" aria-label="{{ $unseenAnnouncements }} new announcements">{{ $unseenAnnouncements > 99 ? '99+' : $unseenAnnouncements }}</span>
    @endif
@endif
