<?php $__env->startSection('title', __('teacher.eccd_title')); ?>
<?php $__env->startSection('page-title', __('teacher.eccd_title')); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-6 bg-white rounded-xl shadow-sm p-3">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h3 class="text-lg font-semibold text-[#182438]"><?php echo e(__('teacher.eccd_heading')); ?></h3>
            <p class="text-sm text-[#5f6873] mt-1"><?php echo e(__('teacher.eccd_administer_note')); ?> <span class="font-medium text-[#ff8555]"><?php echo e(__('teacher.evaluation_number')); ?><?php echo e($evaluationNumber); ?></span></p>
        </div>
        <?php if (isset($component)) { $__componentOriginal8224c6c2d1ef7589c452d8e68a79975d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8224c6c2d1ef7589c452d8e68a79975d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.student-chooser','data' => ['students' => $students,'selected' => $selectedStudent,'action' => route('teacher.eccd.index'),'param' => 'student_id','extra' => ['evaluation' => $evaluationNumber]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('student-chooser'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['students' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($students),'selected' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($selectedStudent),'action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('teacher.eccd.index')),'param' => 'student_id','extra' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['evaluation' => $evaluationNumber])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8224c6c2d1ef7589c452d8e68a79975d)): ?>
<?php $attributes = $__attributesOriginal8224c6c2d1ef7589c452d8e68a79975d; ?>
<?php unset($__attributesOriginal8224c6c2d1ef7589c452d8e68a79975d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8224c6c2d1ef7589c452d8e68a79975d)): ?>
<?php $component = $__componentOriginal8224c6c2d1ef7589c452d8e68a79975d; ?>
<?php unset($__componentOriginal8224c6c2d1ef7589c452d8e68a79975d); ?>
<?php endif; ?>
    </div>
</div>

<?php if($selectedStudent): ?>
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
    <div class="bg-white rounded-xl shadow-sm p-3 border-l-4 border-[#ff8555]">
        <p class="text-[#5f6873] text-sm"><?php echo e(__('teacher.child_name')); ?></p>
        <p class="text-base font-bold text-[#182438]"><?php echo e($selectedStudent->name); ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-3 border-l-4 border-[#258cc5]">
        <p class="text-[#5f6873] text-sm"><?php echo e(__('teacher.age')); ?></p>
        <p class="text-base font-bold text-[#182438]"><?php echo e($selectedStudent->age); ?> <?php echo e(__('teacher.yrs')); ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-3 border-l-4 border-[#68b82f]">
        <p class="text-[#5f6873] text-sm"><?php echo e(__('teacher.parent')); ?></p>
        <p class="text-base font-bold text-[#182438]"><?php echo e($selectedStudent->parent->full_name ?? '—'); ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-3 border-l-4 border-[#d9b348]">
        <p class="text-[#5f6873] text-sm"><?php echo e(__('teacher.overall_progress')); ?></p>
        <p class="text-base font-bold text-[#182438]" data-overall-pct>
            <?php echo e($records->flatten()->count() > 0 ? round($records->flatten()->where('status','Achieved')->count() / max($records->flatten()->count(),1) * 100) : 0); ?>%
        </p>
    </div>
</div>

<div class="flex flex-wrap gap-2 mb-4" aria-label="Filter ECCD evaluation domains">
    <button type="button" data-eccd-filter="all" aria-pressed="true" class="eccd-filter px-3 py-1.5 rounded-full border border-[#ff8555] bg-[#ff8555] text-white text-sm font-medium">All domains</button>
    <?php $__currentLoopData = $domains; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $domain): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <button type="button" data-eccd-filter="<?php echo e(\Illuminate\Support\Str::slug($domain)); ?>" aria-pressed="false"
       class="eccd-filter px-3 py-1.5 rounded-full border border-[#ebe8e1] bg-white text-sm font-medium text-[#182438] hover:border-[#ff8555] hover:text-[#f47548]">
        <?php echo e($labels['domains'][$domain] ?? $domain); ?>

    </button>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3 mb-4">
    <?php $__currentLoopData = $domains; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $domain): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="bg-white rounded-xl shadow-sm p-3">
        <div class="flex items-center justify-between mb-3">
            <h4 class="font-semibold text-[#182438]"><?php echo e($labels['domains'][$domain] ?? $domain); ?></h4>
            <span class="text-sm font-bold text-[#ff8555]" data-score-pct="<?php echo e(\Illuminate\Support\Str::slug($domain)); ?>"><?php echo e($scores[$domain] ?? 0); ?>%</span>
        </div>
        <div class="w-full bg-[#ebe8e1] rounded-full h-1.5 mb-2">
            <div class="bg-gradient-to-r from-[#ff8555] to-[#f47548] h-1.5 rounded-full transition-all" data-score-bar="<?php echo e(\Illuminate\Support\Str::slug($domain)); ?>" style="width: <?php echo e(min($scores[$domain] ?? 0, 100)); ?>%"></div>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<?php if(count($recommendations) > 0): ?>
<div class="bg-white rounded-xl shadow-sm p-3">
    <h3 class="text-lg font-semibold text-[#182438] mb-4"><?php echo e(__('teacher.expert_recommendations')); ?></h3>
    <div class="space-y-3">
        <?php $__currentLoopData = $recommendations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="p-4 rounded-lg border
            <?php echo e($rec['severity'] === 'success' ? 'border-[#68b82f] bg-green-50' : ($rec['severity'] === 'warning' ? 'border-[#d9b348] bg-yellow-50' : 'border-[#f05d56] bg-red-50')); ?>">
            <p class="font-semibold text-[#182438] text-sm"><?php echo e($rec['title']); ?></p>
            <p class="text-sm text-[#5f6873] mt-1"><?php echo e($rec['message']); ?></p>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>
<?php endif; ?>

<form id="eccdForm" method="POST" action="<?php echo e(route('teacher.eccd.save')); ?>" novalidate>
    <?php echo csrf_field(); ?>
    <input type="hidden" name="student_id" value="<?php echo e($selectedStudent->id); ?>">
    <input type="hidden" name="evaluation" value="<?php echo e($evaluationNumber); ?>">
    <input type="hidden" name="next" value="" id="eccdNext">

<?php $__currentLoopData = $domains; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $domain): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php $domainRecords = $records->get($domain, collect()); ?>
<div data-eccd-domain="<?php echo e(\Illuminate\Support\Str::slug($domain)); ?>" class="bg-white rounded-xl shadow-sm overflow-hidden mb-4 scroll-mt-24">
    <div class="px-4 py-2 bg-[#182438] text-white flex items-center justify-between">
        <div>
            <h3 class="font-semibold"><?php echo e($labels['domains'][$domain] ?? $domain); ?></h3>
            <p class="text-xs text-white/60"><?php echo e(__('teacher.evaluation_number')); ?><?php echo e($evaluationNumber); ?></p>
        </div>
        <div class="text-right">
            <p class="text-xs text-white/60"><?php echo e(__('teacher.score')); ?></p>
            <p class="text-base font-bold" data-score-pct="<?php echo e(\Illuminate\Support\Str::slug($domain)); ?>"><?php echo e($scores[$domain] ?? 0); ?>%</p>
        </div>
    </div>
    <table class="w-full">
        <thead class="bg-[#faf8f3]">
            <tr>
                <th class="px-4 py-2 text-left text-xs font-semibold text-[#5f6873] w-10">#</th>
                <th class="px-4 py-2 text-left text-xs font-semibold text-[#5f6873]"><?php echo e(__('teacher.indicator')); ?></th>
                <th class="px-4 py-2 text-center text-xs font-semibold text-[#5f6873] w-32"><?php echo e(__('teacher.status')); ?></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-[#ebe8e1]">
            <?php $__empty_1 = true; $__currentLoopData = $domainRecords; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr class="hover:bg-[#faf8f3] transition" data-eccd-row>
                <td class="px-4 py-2 text-[#5f6873]"><?php echo e($i + 1); ?></td>
                <td class="px-4 py-2 text-sm font-medium text-[#182438]"><?php echo e($labels['indicators'][$record->indicator] ?? $record->indicator); ?></td>
                <td class="px-4 py-2">
                    <div class="flex items-center justify-center gap-2" data-eccd-group data-record="<?php echo e($record->id); ?>" data-original="<?php echo e($record->status); ?>" data-current="<?php echo e($record->status); ?>" role="group" aria-label="<?php echo e($labels['indicators'][$record->indicator] ?? $record->indicator); ?>">
                        <input type="hidden" name="statuses[<?php echo e($record->id); ?>]" value="<?php echo e($record->status); ?>" disabled data-eccd-input>
                        <?php $__currentLoopData = ['Not Yet','In Progress','Achieved']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $icon = ['Not Yet' => 'close-circle.svg', 'In Progress' => 'remove-circle.svg', 'Achieved' => 'checkmark-circle.svg'][$status];
                            $color = ['Not Yet' => '#f05d56', 'In Progress' => '#d9b348', 'Achieved' => '#68b82f'][$status];
                            $label = ['Not Yet' => __('teacher.status_not_yet'), 'In Progress' => __('teacher.status_in_progress'), 'Achieved' => __('teacher.status_achieved')][$status];
                        ?>
                        <button type="button" data-status="<?php echo e($status); ?>" data-color="<?php echo e($color); ?>" aria-label="<?php echo e($label); ?>" title="<?php echo e($label); ?>"
                            aria-pressed="<?php echo e($record->status === $status ? 'true' : 'false'); ?>"
                            class="eccd-status-button rounded-full p-1 transition <?php echo e($record->status === $status ? 'ring-2 ring-offset-2' : 'opacity-45 hover:opacity-80'); ?>"
                            style="--tw-ring-color: <?php echo e($color); ?>">
                            <img src="<?php echo e(asset('assets/icons/'.$icon)); ?>" alt="" class="h-6 w-6" style="filter: <?php echo e($status === 'Not Yet' ? 'invert(48%) sepia(76%) saturate(1280%) hue-rotate(322deg) brightness(98%) contrast(92%)' : ($status === 'In Progress' ? 'invert(77%) sepia(49%) saturate(637%) hue-rotate(4deg) brightness(92%) contrast(88%)' : 'invert(61%) sepia(65%) saturate(519%) hue-rotate(47deg) brightness(94%) contrast(87%)')); ?>">
                        </button>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="3" class="px-6 py-8 text-center text-[#5f6873]"><?php echo e(__('teacher.no_records_domain')); ?></td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    
    <div id="eccdSaveBar" class="sticky bottom-0 z-20 -mx-4 md:-mx-6 px-4 md:px-6 py-3 bg-white/95 backdrop-blur border-t border-[#ebe8e1] shadow-[0_-4px_12px_rgba(24,36,56,0.08)]">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <p id="eccdSaveStatus" class="text-sm text-[#5f6873]" role="status" aria-live="polite"><?php echo e(__('All changes saved.')); ?></p>
            <button type="submit" id="eccdSaveButton" disabled
                class="inline-flex items-center justify-center gap-2 bg-[#ff8555] hover:bg-[#f47548] disabled:bg-[#ebe8e1] disabled:text-[#8b949d] disabled:cursor-not-allowed text-white px-8 py-2.5 rounded-lg transition font-semibold">
                <?php echo e(__('Save changes')); ?>

                <span id="eccdSaveCount" class="hidden rounded-full bg-white/25 px-2 py-0.5 text-xs font-bold"></span>
            </button>
        </div>
    </div>
</form>

<?php elseif($students->isNotEmpty()): ?>
<?php if (isset($component)) { $__componentOriginal2f2279ac2ac44e3f8764428a51763a86 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2f2279ac2ac44e3f8764428a51763a86 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.student-chooser-prompt','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('student-chooser-prompt'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2f2279ac2ac44e3f8764428a51763a86)): ?>
<?php $attributes = $__attributesOriginal2f2279ac2ac44e3f8764428a51763a86; ?>
<?php unset($__attributesOriginal2f2279ac2ac44e3f8764428a51763a86); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2f2279ac2ac44e3f8764428a51763a86)): ?>
<?php $component = $__componentOriginal2f2279ac2ac44e3f8764428a51763a86; ?>
<?php unset($__componentOriginal2f2279ac2ac44e3f8764428a51763a86); ?>
<?php endif; ?>
<?php else: ?>
<div class="bg-white rounded-xl shadow-sm p-10 text-center">
    <p class="text-[#5f6873]"><?php echo e(__('teacher.no_students_assigned')); ?></p>
    <a href="<?php echo e(route('teacher.accounts.index')); ?>" class="inline-block mt-4 bg-[#ff8555] hover:bg-[#f47548] text-white px-6 py-2.5 rounded-lg transition font-medium"><?php echo e(__('teacher.go_to_accounts')); ?></a>
</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('scripts'); ?>
<script>
(function () {
    const form = document.getElementById('eccdForm');
    const filterButtons = document.querySelectorAll('[data-eccd-filter]');

    // ---- domain filter pills (display only, nothing to save) ----------------
    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            const selected = button.dataset.eccdFilter;
            document.querySelectorAll('[data-eccd-domain]').forEach(section => {
                section.hidden = selected !== 'all' && section.dataset.eccdDomain !== selected;
            });
            filterButtons.forEach(item => {
                const active = item === button;
                item.setAttribute('aria-pressed', active ? 'true' : 'false');
                item.classList.toggle('bg-[#ff8555]', active);
                item.classList.toggle('text-white', active);
                item.classList.toggle('border-[#ff8555]', active);
                item.classList.toggle('bg-white', !active);
            });
        });
    });

    if (!form) return;

    // Js::from rather than the json directive: the directive splits its argument on
    // every comma, so an array this long is cut off and PHP fails with
    // "Unclosed '[' ... does not match ')'".
    const T = <?php echo e(\Illuminate\Support\Js::from([
        'saveTitle' => __('Save changes?'),
        'saveConfirm' => __('Save changes'),
        'cancel' => __('Cancel'),
        'leaveTitle' => __('You have unsaved changes'),
        'saveContinue' => __('Save & continue'),
        'saveOnly' => __('Save changes'),
        'discard' => __('Leave without saving'),
        'keep' => __('Keep editing'),
        'nothing' => __('There are no changes to save.'),
        'allSaved' => __('All changes saved.'),
        'student' => $selectedStudent?->name,
    ])); ?>;
    const plural = (n, one, many) => n === 1 ? one : many.replace(':count', n);
    const pendingText = n => plural(n, <?php echo e(\Illuminate\Support\Js::from(__('1 unsaved change'))); ?>, <?php echo e(\Illuminate\Support\Js::from(__(':count unsaved changes'))); ?>);

    const pendingSaveText = n => (n === 1
        ? <?php echo e(\Illuminate\Support\Js::from(__('You are about to save 1 change to :name checklist.', ['name' => $selectedStudent?->name.'’s']))); ?>

        : <?php echo e(\Illuminate\Support\Js::from(__('You are about to save :count changes to :name checklist.', ['name' => $selectedStudent?->name.'’s']))); ?>

    ).replace(':count', n);

    const groups = Array.from(form.querySelectorAll('[data-eccd-group]'));
    const saveButton = document.getElementById('eccdSaveButton');
    const saveStatus = document.getElementById('eccdSaveStatus');
    const saveCount = document.getElementById('eccdSaveCount');
    const nextInput = document.getElementById('eccdNext');

    let confirmedSave = false;   // the save popup was accepted, let the submit through
    let allowLeave = false;      // the teacher chose to discard, stop guarding

    const changedGroups = () => groups.filter(g => g.dataset.current !== g.dataset.original);
    const changedCount = () => changedGroups().length;
    const isDirty = () => !allowLeave && !confirmedSave && changedCount() > 0;

    // ---- staging a status change ------------------------------------------
    function setStatus(group, status) {
        group.dataset.current = status;
        const input = group.querySelector('[data-eccd-input]');
        input.value = status;
        // Only changed rows are sent; untouched rows stay out of the request.
        input.disabled = status === group.dataset.original;

        group.querySelectorAll('.eccd-status-button').forEach(item => {
            const active = item.dataset.status === status;
            item.setAttribute('aria-pressed', active ? 'true' : 'false');
            item.classList.toggle('opacity-45', !active);
            item.classList.toggle('hover:opacity-80', !active);
            item.classList.toggle('ring-2', active);
            item.classList.toggle('ring-offset-2', active);
        });

        const row = group.closest('[data-eccd-row]');
        if (row) row.classList.toggle('eccd-row-changed', status !== group.dataset.original);

        refresh();
    }

    form.addEventListener('click', event => {
        const button = event.target.closest('.eccd-status-button');
        if (!button) return;
        const group = button.closest('[data-eccd-group]');
        if (group) setStatus(group, button.dataset.status);
    });

    // ---- live scores and the save bar -----------------------------------------
    function refresh() {
        const perDomain = {};
        let achievedAll = 0;
        groups.forEach(group => {
            const section = group.closest('[data-eccd-domain]');
            if (!section) return;
            const key = section.dataset.eccdDomain;
            perDomain[key] ??= { achieved: 0, total: 0 };
            perDomain[key].total++;
            if (group.dataset.current === 'Achieved') { perDomain[key].achieved++; achievedAll++; }
        });

        Object.entries(perDomain).forEach(([key, v]) => {
            const pct = Math.round((v.achieved / Math.max(v.total, 1)) * 100);
            document.querySelectorAll('[data-score-pct="' + key + '"]').forEach(el => el.textContent = pct + '%');
            document.querySelectorAll('[data-score-bar="' + key + '"]').forEach(el => el.style.width = Math.min(pct, 100) + '%');
        });
        const overall = document.querySelector('[data-overall-pct]');
        if (overall) overall.textContent = (groups.length ? Math.round(achievedAll / groups.length * 100) : 0) + '%';

        const n = changedCount();
        saveButton.disabled = n === 0;
        saveCount.textContent = n;
        saveCount.classList.toggle('hidden', n === 0);
        saveStatus.textContent = n === 0 ? T.allSaved : pendingText(n);
        saveStatus.classList.toggle('text-[#c0392b]', n > 0);
        saveStatus.classList.toggle('font-semibold', n > 0);
        saveStatus.classList.toggle('text-[#5f6873]', n === 0);
    }

    // ---- Save button: ask first ----------------------------------------------
    function submitNow(next) {
        nextInput.value = next || '';
        confirmedSave = true;
        form.requestSubmit();
    }

    form.addEventListener('submit', event => {
        if (confirmedSave) return;   // already confirmed: let it go through
        event.preventDefault();
        event.stopImmediatePropagation();

        const n = changedCount();
        if (n === 0) { Swal.fire({ icon: 'info', text: T.nothing, confirmButtonColor: '#ff8555' }); return; }

        Swal.fire({
            icon: 'question',
            title: T.saveTitle,
            text: pendingSaveText(n),
            showCancelButton: true,
            confirmButtonText: T.saveConfirm,
            cancelButtonText: T.cancel,
            confirmButtonColor: '#ff8555',
            cancelButtonColor: '#8b949d',
            reverseButtons: true,
            focusConfirm: true,
        }).then(result => { if (result.isConfirmed) submitNow(''); });
    });

    // ---- leaving the page with unsaved changes ---------------------------------
    function askLeave(onLeave, next) {
        const n = changedCount();
        Swal.fire({
            icon: 'warning',
            title: T.leaveTitle,
            text: pendingText(n) + ' ' + <?php echo e(\Illuminate\Support\Js::from(__('If you leave now, they will be lost.'))); ?>,
            showCancelButton: true,
            showDenyButton: true,
            confirmButtonText: next ? T.saveContinue : T.saveOnly,
            denyButtonText: T.discard,
            cancelButtonText: T.keep,
            confirmButtonColor: '#ff8555',
            denyButtonColor: '#f05d56',
            cancelButtonColor: '#8b949d',
            focusCancel: false,
        }).then(result => {
            if (result.isConfirmed) submitNow(next);
            else if (result.isDenied) onLeave();
        });
    }

    // Any link away from the checklist (sidebar, student switcher, header...).
    document.addEventListener('click', event => {
        if (!isDirty() || event.defaultPrevented || event.button !== 0) return;
        if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        const link = event.target.closest && event.target.closest('a[href]');
        if (!link || (link.target && link.target !== '_self') || link.hasAttribute('download')) return;
        const raw = link.getAttribute('href') || '';
        if (/^(#|javascript:|mailto:|tel:|sms:|blob:|data:)/i.test(raw)) return;

        let url;
        try { url = new URL(link.href, location.href); } catch (e) { return; }
        if (url.origin !== location.origin) return;                // the browser's own prompt covers it
        if (url.pathname === location.pathname && url.search === location.search && url.hash) return;

        event.preventDefault();
        event.stopPropagation();
        askLeave(() => { allowLeave = true; location.href = url.href; }, url.pathname + url.search + url.hash);
    }, true);

    // Any other form (language switch, log out, child mode...).
    document.addEventListener('submit', event => {
        const other = event.target;
        if (other === form || !isDirty() || !(other instanceof HTMLFormElement)) return;
        if ((other.target && other.target !== '_self') || (other.getAttribute('method') || '').toLowerCase() === 'dialog') return;

        event.preventDefault();
        event.stopImmediatePropagation();
        const submitter = event.submitter;
        askLeave(() => {
            allowLeave = true;
            other.requestSubmit(submitter || undefined);
            // A form with its own confirmation can still be cancelled; keep guarding then.
            if (other.hasAttribute('data-confirm')) allowLeave = false;
        }, '');
    }, true);

    // Closing the tab, refreshing or typing a new address: the browser asks.
    window.addEventListener('beforeunload', event => {
        if (!isDirty()) return;
        event.preventDefault();
        event.returnValue = '';
        // The page loader starts on every unload; if the teacher stays, take it down again.
        setTimeout(() => window.dispatchEvent(new Event('pageshow')), 0);
    });

    refresh();
})();
</script>
<style>
    .eccd-row-changed { background-color: #fff6f0; box-shadow: inset 3px 0 0 #ff8555; }
</style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.teacher', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/teacher/eccd.blade.php ENDPATH**/ ?>