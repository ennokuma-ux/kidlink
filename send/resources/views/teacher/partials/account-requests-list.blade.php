{{-- The teacher's own account requests and where each one stands --}}
<div id="requestsView" class="hidden">
    <div class="flex flex-wrap items-center gap-2 mb-4">
        <span class="px-3 py-1 rounded-full bg-[#fdf3d7] text-[#8a6d10] text-sm font-semibold">{{ __('teacher.request_count_pending', ['count' => $requestCounts['pending']]) }}</span>
        <span class="px-3 py-1 rounded-full bg-[#e2f4e8] text-[#2f7a1a] text-sm font-semibold">{{ __('teacher.request_count_approved', ['count' => $requestCounts['approved']]) }}</span>
        <span class="px-3 py-1 rounded-full bg-[#fdecea] text-[#c0392b] text-sm font-semibold">{{ __('teacher.request_count_rejected', ['count' => $requestCounts['rejected']]) }}</span>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        @if($accountRequests->isEmpty())
            <div class="p-10 text-center">
                <p class="text-[#182438] font-semibold">{{ __('teacher.request_none') }}</p>
                <p class="text-sm text-[#8b949d] mt-1">{{ __('teacher.request_none_hint') }}</p>
            </div>
        @else
            <div class="overflow-auto max-h-[32rem]">
                <table class="w-full">
                    <thead class="bg-[#faf8f3] sticky top-0 z-10 shadow-[0_1px_0_#ebe8e1]">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#8b949d]">{{ __('teacher.request_account_col') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#8b949d]">{{ __('teacher.request_submitted') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#8b949d]">{{ __('teacher.request_status') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#8b949d]">{{ __('teacher.request_review_col') }}</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#ebe8e1]">
                        @foreach($accountRequests as $accountRequest)
                            @php
                                $statusClass = match ($accountRequest->status) {
                                    'approved' => 'bg-[#e2f4e8] text-[#2f7a1a]',
                                    'rejected' => 'bg-[#fdecea] text-[#c0392b]',
                                    default => 'bg-[#fdf3d7] text-[#8a6d10]',
                                };
                                $resubmit = [
                                    'role' => $accountRequest->role,
                                    'first_name' => $accountRequest->first_name,
                                    'last_name' => $accountRequest->last_name,
                                    'email' => $accountRequest->email,
                                    'contact' => $accountRequest->contact,
                                    'sex' => $accountRequest->sex,
                                    'date_of_birth' => $accountRequest->date_of_birth ? \Illuminate\Support\Carbon::parse($accountRequest->date_of_birth)->toDateString() : null,
                                    'address' => $accountRequest->address,
                                    'class_name' => $accountRequest->class_name,
                                    'dominant_hand' => $accountRequest->dominant_hand,
                                    'father_name' => $accountRequest->father_name,
                                    'father_contact' => $accountRequest->father_contact,
                                    'mother_name' => $accountRequest->mother_name,
                                    'mother_contact' => $accountRequest->mother_contact,
                                    'siblings_count' => $accountRequest->siblings_count,
                                    'parent_ids' => $accountRequest->parent_ids ?? [],
                                ];
                            @endphp
                            <tr class="hover:bg-[#faf8f3] transition">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-[#182438]">{{ $accountRequest->first_name }} {{ $accountRequest->last_name }}</span>
                                        <span class="px-2 py-0.5 rounded text-[11px] font-semibold {{ $accountRequest->role === 'parent' ? 'bg-[#e1f0f8] text-[#1e73a8]' : 'bg-[#fff0e6] text-[#c5582a]' }}">
                                            {{ $accountRequest->role === 'parent' ? __('teacher.request_parent') : __('teacher.request_student') }}
                                        </span>
                                    </div>
                                    @if($accountRequest->email)
                                        <p class="text-xs text-[#8b949d]">{{ $accountRequest->email }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-[#5f6873] whitespace-nowrap" title="{{ $accountRequest->created_at?->format('M j, Y g:i A') }}">
                                    {{ $accountRequest->created_at?->diffForHumans() }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
                                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                        {{ __('teacher.request_'.$accountRequest->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-[#5f6873] min-w-[14rem]">
                                    @if($accountRequest->status === 'pending')
                                        <span class="text-[#8b949d]">{{ __('teacher.request_awaiting') }}</span>
                                    @else
                                        <p>
                                            {{ __('teacher.request_reviewed_on', ['when' => $accountRequest->reviewed_at?->diffForHumans()]) }}
                                            @if($accountRequest->reviewer)
                                                {{ __('teacher.request_reviewed_by', ['name' => $accountRequest->reviewer->full_name]) }}
                                            @endif
                                        </p>
                                        @if($accountRequest->status === 'rejected' && $accountRequest->review_notes)
                                            <p class="mt-1 text-[#c0392b]"><span class="font-semibold">{{ __('teacher.request_reason') }}:</span> {{ $accountRequest->review_notes }}</p>
                                        @elseif($accountRequest->status === 'approved')
                                            <p class="mt-1 text-xs text-[#8b949d]">{{ __('teacher.request_approved_hint') }}</p>
                                        @endif
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    @if($accountRequest->status === 'pending')
                                        <form method="POST" action="{{ route('teacher.account-requests.cancel', $accountRequest) }}" class="inline"
                                              data-confirm="{{ __('teacher.request_withdraw_confirm') }}" data-confirm-title="{{ __('teacher.request_withdraw_title') }}"
                                              data-confirm-yes="{{ __('teacher.request_withdraw') }}" data-confirm-no="{{ __('teacher.cancel') }}">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="px-3 py-1.5 rounded-lg text-sm font-semibold text-[#f05d56] hover:bg-[#fdecea] transition">{{ __('teacher.request_withdraw') }}</button>
                                        </form>
                                    @elseif($accountRequest->status === 'rejected')
                                        <button type="button" data-resubmit='@json($resubmit)'
                                                onclick="window.dispatchEvent(new CustomEvent('open-account-request', { detail: JSON.parse(this.dataset.resubmit) }))"
                                                class="px-3 py-1.5 rounded-lg text-sm font-semibold text-[#258cc5] hover:bg-[#e1f0f8] transition">{{ __('teacher.request_resubmit') }}</button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
