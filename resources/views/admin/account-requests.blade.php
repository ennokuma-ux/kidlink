@extends('layouts.admin')

@section('title', 'Account Requests')
@section('page-title', 'Account Requests')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <h2 class="text-xl font-bold text-[#182438]">Account Requests</h2>
            <p class="text-sm text-[#5f6873] mt-1">Review account requests submitted by teachers. Accounts are not created until you approve them.</p>
        </div>
        <div class="px-4 py-2 rounded-lg bg-[#fff9e6] text-[#6e5000] font-semibold text-sm">{{ $pendingCount }} pending approval{{ $pendingCount === 1 ? '' : 's' }}</div>
    </div>
</div>

@if(session('success'))<div class="mb-4 rounded-xl border border-[#d0eb9c] bg-[#f3fae4] px-4 py-3 text-sm text-[#3e610c]">{{ session('success') }}</div>@endif
@if(session('error'))<div class="mb-4 rounded-xl border border-[#ffc9cb] bg-[#fff1f1] px-4 py-3 text-sm text-[#a82a2f]">{{ session('error') }}</div>@endif

<div class="flex flex-wrap items-center gap-1 bg-white rounded-xl shadow-sm p-1.5 mb-4 w-fit max-w-full">
    @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'all' => 'All'] as $key => $label)
        <a href="{{ route('admin.account-requests.index', ['status' => $key]) }}"
           class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $filter === $key ? 'bg-[#ff8555] text-white' : 'text-[#5f6873] hover:bg-[#fdf3d6]' }}">
            {{ $label }} <span class="ml-1 text-xs {{ $filter === $key ? 'text-white/80' : 'text-[#6b7280]' }}">{{ $counts[$key] }}</span>
        </a>
    @endforeach
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-[#fffbf0]"><tr>
                <th class="px-5 py-4 text-left font-semibold text-[#182438]">Requested Account</th>
                <th class="px-5 py-4 text-left font-semibold text-[#182438]">Email / Contact</th>
                <th class="px-5 py-4 text-left font-semibold text-[#182438]">Account Type</th>
                <th class="px-5 py-4 text-left font-semibold text-[#182438]">Requested By</th>
                <th class="px-5 py-4 text-left font-semibold text-[#182438]">Submitted</th>
                <th class="px-5 py-4 text-center font-semibold text-[#182438]">Status</th>
                <th class="px-5 py-4 text-center font-semibold text-[#182438]">Action</th>
            </tr></thead>
            <tbody class="divide-y divide-[#f1e7cc]">
            @forelse($requests as $accountRequest)
                <tr class="hover:bg-[#fffbf0]">
                    <td class="px-5 py-4"><div class="font-semibold text-[#182438]">{{ $accountRequest->first_name }} {{ $accountRequest->last_name }}</div>@if($accountRequest->student)<div class="text-xs text-[#5f6873] mt-1">Student: {{ $accountRequest->student->name }}</div>@endif</td>
                    <td class="px-5 py-4"><div>{{ $accountRequest->email }}</div><div class="text-xs text-[#5f6873] mt-1">{{ $accountRequest->contact ?: 'No contact provided' }}</div></td>
                    <td class="px-5 py-4"><span class="px-3 py-1 rounded-full text-xs font-semibold {{ $accountRequest->role === 'parent' ? 'bg-[#e6f4c8] text-[#4f7a0f]' : 'bg-[#d3e8f6] text-[#0f5483]' }}">{{ $accountRequest->role === 'student' ? 'Child' : 'Parent' }}</span></td>
                    <td class="px-5 py-4">{{ $accountRequest->requester?->full_name ?? 'Deleted user' }}</td>
                    <td class="px-5 py-4 whitespace-nowrap">{{ $accountRequest->created_at?->format('M d, Y g:i A') }}</td>
                    <td class="px-5 py-4 text-center"><span class="px-3 py-1 rounded-full text-xs font-semibold {{ $accountRequest->status === 'approved' ? 'bg-[#e6f4c8] text-[#4f7a0f]' : ($accountRequest->status === 'rejected' ? 'bg-[#ffe2e3] text-[#c9343a]' : 'bg-[#fff0bf] text-[#8a6500]') }}">{{ ucfirst($accountRequest->status) }}</span>@if($accountRequest->review_notes)<p class="text-xs text-[#5f6873] mt-2 max-w-40">{{ $accountRequest->review_notes }}</p>@endif</td>
                    <td class="px-5 py-4">
                        @if($accountRequest->status === 'pending')
                        <div class="flex items-center justify-center gap-2">
                            <form method="POST" action="{{ route('admin.account-requests.approve', $accountRequest) }}" class="approve-request-form" data-name="{{ $accountRequest->first_name }} {{ $accountRequest->last_name }}">@csrf<button class="px-3 py-2 rounded-lg bg-[#6fa51c] hover:bg-[#4f7a0f] text-white font-medium" type="submit">Approve</button></form>
                            <form method="POST" action="{{ route('admin.account-requests.reject', ['accountRequest' => $accountRequest, 'status' => $filter]) }}" class="reject-request-form" data-name="{{ $accountRequest->first_name }} {{ $accountRequest->last_name }}">@csrf<input type="hidden" name="review_notes" value=""><button class="px-3 py-2 rounded-lg bg-[#ffe2e3] hover:bg-[#ffc9cb] text-[#c9343a] font-medium" type="submit">Reject</button></form>
                        </div>
                        @else
                        <div class="text-center text-xs text-[#5f6873]">{{ $accountRequest->reviewer?->full_name ?? 'Reviewed' }}<br>{{ $accountRequest->reviewed_at?->format('M d, Y') }}</div>
                        @endif
                    </td>
                </tr>
                @if($accountRequest->role === 'student')
                <tr class="bg-[#fcfbf8]">
                    <td colspan="7" class="px-5 py-4">
                        <details>
                            <summary class="cursor-pointer font-semibold text-[#1982c4]">View submitted child profile details</summary>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 mt-4 text-sm text-[#5f6873]">
                                <div><span class="font-semibold text-[#182438]">Sex:</span> {{ $accountRequest->sex ?: 'Not provided' }}</div>
                                <div><span class="font-semibold text-[#182438]">Date of birth:</span> {{ $accountRequest->date_of_birth ?: 'Not provided' }}</div>
                                <div><span class="font-semibold text-[#182438]">Class:</span> {{ $accountRequest->class_name ?: 'Not provided' }}</div>
                                <div><span class="font-semibold text-[#182438]">Academic year:</span> {{ $accountRequest->academic_year ?: \App\Models\Student::currentAcademicYear().' (default)' }}</div>
                                <div><span class="font-semibold text-[#182438]">Address:</span> {{ $accountRequest->address ?: 'Not provided' }}</div>
                                <div><span class="font-semibold text-[#182438]">Dominant hand:</span> {{ $accountRequest->dominant_hand ?: 'Not yet established' }}</div>
                                <div><span class="font-semibold text-[#182438]">Siblings:</span> {{ $accountRequest->siblings_count ?? 0 }}</div>
                                <div><span class="font-semibold text-[#182438]">Father:</span> {{ $accountRequest->father_name ?: 'Not provided' }}{{ $accountRequest->father_contact ? ' · '.$accountRequest->father_contact : '' }}</div>
                                <div><span class="font-semibold text-[#182438]">Mother:</span> {{ $accountRequest->mother_name ?: 'Not provided' }}{{ $accountRequest->mother_contact ? ' · '.$accountRequest->mother_contact : '' }}</div>
                            </div>
                        </details>
                    </td>
                </tr>
                @endif
            @empty
                <tr><td colspan="7" class="px-6 py-14 text-center text-[#6b7280]">{{ $filter === 'pending' ? 'Nothing is waiting for approval.' : 'No account requests here yet.' }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.approve-request-form').forEach(form => form.addEventListener('submit', event => {
    event.preventDefault();
    if (typeof Swal === 'undefined') {
        if (confirm('Approve this account request and create the account?')) form.submit();
        return;
    }
    Swal.fire({title:'Approve account request?',text:`Create an account for ${form.dataset.name}?`,icon:'question',showCancelButton:true,confirmButtonText:'Approve and create',cancelButtonText:'Cancel',confirmButtonColor:'#6fa51c',reverseButtons:true}).then(result => { if(result.isConfirmed) form.submit(); });
}));
document.querySelectorAll('.reject-request-form').forEach(form => form.addEventListener('submit', event => {
    event.preventDefault();
    const notes = form.querySelector('input[name="review_notes"]');
    if (typeof Swal === 'undefined') {
        if (confirm('Reject this account request?')) { notes.value = prompt('Reason (optional)') || ''; form.submit(); }
        return;
    }
    Swal.fire({title:'Reject account request?',text:`The account for ${form.dataset.name} will not be created.`,icon:'warning',input:'textarea',inputPlaceholder:'Reason for the teacher (optional)',inputAttributes:{maxlength:1000},showCancelButton:true,confirmButtonText:'Reject request',cancelButtonText:'Keep pending',confirmButtonColor:'#dc2626',reverseButtons:true}).then(result => { if(result.isConfirmed) { notes.value = result.value || ''; form.submit(); } });
}));
@if(session('generated_credentials'))
const credentials = @json(session('generated_credentials'));
Swal.fire({title:'Account created',icon:'success',html:'<div class="text-left"><p id="credentialName" class="mb-2 font-semibold"></p><p class="mb-2"><b>Email:</b> <span id="credentialEmail"></span></p><p><b>Temporary password:</b> <code id="credentialPassword"></code></p><p class="text-xs mt-3">Copy these credentials now and share them securely. This temporary password will not be shown again.</p></div>',didOpen:()=>{document.getElementById('credentialName').textContent=credentials.name;document.getElementById('credentialEmail').textContent=credentials.email;document.getElementById('credentialPassword').textContent=credentials.password;},confirmButtonText:'Done'});
@endif
</script>
@endpush
