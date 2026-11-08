

<?php $__env->startSection('title', __('teacher.activities_title')); ?>
<?php $__env->startSection('page-title', __('teacher.activities_title')); ?>

<?php $__env->startSection('content'); ?>
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div class="flex items-center gap-2">
        <a href="<?php echo e(route('teacher.activities.index', ['week' => $prevWeek])); ?>"
           class="px-3 py-2 border border-[#ebe8e1] rounded-lg text-[#182438] hover:bg-[#faf8f3] transition text-sm">&larr; <?php echo e(__('teacher.prev')); ?></a>

        <div class="px-4 py-2 bg-white rounded-lg border border-[#ebe8e1] shadow-sm text-center">
            <span class="font-semibold text-[#182438]"><?php echo e($weekStart->format('M j')); ?> &ndash; <?php echo e($weekEnd->format('M j, Y')); ?></span>
            <?php if (! ($isCurrentWeek)): ?>
                <span class="ml-2 px-2 py-0.5 rounded text-xs font-medium bg-[#d9b348] text-white"><?php echo e(__('teacher.planning_ahead')); ?></span>
            <?php endif; ?>
        </div>

        <a href="<?php echo e(route('teacher.activities.index', ['week' => $nextWeek])); ?>"
           class="px-3 py-2 border border-[#ebe8e1] rounded-lg text-[#182438] hover:bg-[#faf8f3] transition text-sm"><?php echo e(__('teacher.next')); ?> &rarr;</a>

        <?php if (! ($isCurrentWeek)): ?>
            <a href="<?php echo e(route('teacher.activities.index')); ?>"
               class="px-3 py-2 text-sm text-[#ff8555] hover:text-[#f47548] font-medium"><?php echo e(__('teacher.this_week')); ?></a>
        <?php endif; ?>
    </div>

    <div class="flex gap-3">
        <button onclick="document.getElementById('addActivityModal').classList.remove('hidden')" class="bg-[#ff8555] text-white px-6 py-2.5 rounded-lg hover:bg-[#f47548] transition font-medium">
            <?php echo e(__('teacher.add_activity_plus')); ?>

        </button>
        <select id="typeFilter" onchange="filterActivities()" class="px-4 py-2.5 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
            <option value="all"><?php echo e(__('teacher.all_types')); ?></option>
            <option value="Lesson"><?php echo e(__('teacher.type_lesson')); ?></option>
            <option value="Craft"><?php echo e(__('teacher.type_craft')); ?></option>
            <option value="Music"><?php echo e(__('teacher.type_music')); ?></option>
            <option value="Story"><?php echo e(__('teacher.type_story')); ?></option>
            <option value="Movement"><?php echo e(__('teacher.type_movement')); ?></option>
        </select>
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-4 text-center border-l-4 border-[#ff8555]">
        <p class="text-2xl font-bold text-[#182438]"><?php echo e($stats['total']); ?></p>
        <p class="text-sm text-[#5f6873]"><?php echo e(__('teacher.activities_this_week')); ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 text-center border-l-4 border-[#68b82f]">
        <p class="text-2xl font-bold text-[#68b82f]"><?php echo e($stats['completed']); ?></p>
        <p class="text-sm text-[#5f6873]"><?php echo e(__('teacher.completed')); ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 text-center border-l-4 border-[#d9b348]">
        <p class="text-2xl font-bold text-[#d9b348]"><?php echo e($stats['pending']); ?></p>
        <p class="text-sm text-[#5f6873]"><?php echo e(__('teacher.pending')); ?></p>
    </div>
</div>

<?php if($stats['total'] === 0): ?>
    <div class="bg-white rounded-xl shadow-sm p-8 mb-6 text-center">
        <p class="text-[#5f6873]"><?php echo e(__('teacher.no_activities_this_week')); ?></p>
        <p class="text-sm text-[#8b949d] mt-1"><?php echo e(__('teacher.use_add_activity')); ?></p>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
    <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $offset => $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php ($date = $weekStart->copy()->addDays($offset)); ?>
        <div class="bg-white rounded-xl shadow-sm overflow-hidden <?php echo e($date->isToday() ? 'ring-2 ring-[#ff8555]' : ''); ?>">
            <div class="text-center py-3 font-semibold <?php echo e($date->isToday() ? 'bg-[#ff8555] text-white' : 'bg-[#ebe8e1] text-[#182438]'); ?>">
                <?php echo e($date->format('D')); ?>

                <span class="block text-xs font-normal opacity-80"><?php echo e($date->format('M j')); ?></span>
            </div>
            <div class="p-3 space-y-2 min-h-[200px]">
                <?php $__empty_1 = true; $__currentLoopData = ($activities[$day] ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="p-3 rounded-lg border text-sm activity-card <?php echo e($activity->is_completed ? 'opacity-60' : ''); ?>"
                     data-type="<?php echo e($activity->type); ?>"
                     data-id="<?php echo e($activity->id); ?>"
                     data-title="<?php echo e($activity->title); ?>"
                     data-day="<?php echo e($activity->day_of_week); ?>"
                     data-start="<?php echo e(substr($activity->start_time, 0, 5)); ?>"
                     data-end="<?php echo e(substr($activity->end_time, 0, 5)); ?>"
                     data-group="<?php echo e($activity->group_name); ?>"
                     data-update-url="<?php echo e(route('teacher.activities.update', $activity)); ?>">
                    <div class="flex items-center justify-between mb-1">
                        <span class="font-semibold text-[#182438]"><?php echo e($activity->title); ?></span>
                        <span class="px-2 py-0.5 rounded text-xs font-medium
                            <?php echo e($activity->type === 'Lesson' ? 'bg-blue-100 text-blue-700' : ''); ?>

                            <?php echo e($activity->type === 'Craft' ? 'bg-orange-100 text-orange-700' : ''); ?>

                            <?php echo e($activity->type === 'Music' ? 'bg-pink-100 text-pink-700' : ''); ?>

                            <?php echo e($activity->type === 'Story' ? 'bg-green-100 text-green-700' : ''); ?>

                            <?php echo e($activity->type === 'Movement' ? 'bg-yellow-100 text-yellow-700' : ''); ?>">
                            <?php echo e($activity->type); ?>

                        </span>
                    </div>
                    <p class="text-[#5f6873] text-xs"><?php echo e(substr($activity->start_time, 0, 5)); ?> - <?php echo e(substr($activity->end_time, 0, 5)); ?></p>
                    <?php if($activity->group_name): ?><p class="text-[#8b949d] text-xs mt-1"><?php echo e(__('teacher.group_label')); ?> <?php echo e($activity->group_name); ?></p><?php endif; ?>
                    <div class="flex items-center justify-between mt-2 pt-2 border-t">
                        <form method="POST" action="<?php echo e(route('teacher.activities.toggle', $activity)); ?>" class="inline">
                            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                            <button type="submit" class="text-xs <?php echo e($activity->is_completed ? 'text-[#68b82f]' : 'text-[#8b949d]'); ?>">
                                <?php echo e($activity->is_completed ? __('teacher.completed') : __('teacher.mark_done')); ?>

                            </button>
                        </form>
                        <div class="flex items-center gap-3">
                            <a href="<?php echo e(route('teacher.activities.participation', $activity)); ?>" class="text-xs text-[#258cc5] hover:underline"><?php echo e(__('teacher.record')); ?></a>
                            <button type="button" onclick="openEditModal(this)" class="text-xs text-[#8b949d] hover:text-[#182438]"><?php echo e(__('teacher.edit')); ?></button>
                            <form method="POST" action="<?php echo e(route('teacher.activities.destroy', $activity)); ?>" class="inline" data-confirm="<?php echo e(__('teacher.delete_activity_confirm')); ?>" data-confirm-title="<?php echo e(__('teacher.delete_activity_title')); ?>" data-confirm-yes="<?php echo e(__('teacher.delete')); ?>" data-confirm-no="<?php echo e(__('teacher.cancel')); ?>">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="text-xs text-red-500"><?php echo e(__('teacher.delete')); ?></button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-[#8b949d] text-xs text-center py-4"><?php echo e(__('teacher.no_activities')); ?></p>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>


<div class="mt-8 bg-white rounded-xl shadow-sm p-6 w-full"
     x-data="activityCalendar(<?php echo \Illuminate\Support\Js::from($calendarDays)->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($weekStart->toDateString())->toHtml() ?>, <?php echo \Illuminate\Support\Js::from(route('teacher.activities.index'))->toHtml() ?>, <?php echo \Illuminate\Support\Js::from([
        'today' => __('teacher.calendar_today'), 'go' => __('teacher.calendar_go'), 'none' => __('teacher.calendar_none'),
        'one' => __('teacher.calendar_activity'), 'many' => __('teacher.calendar_activities'), 'done' => __('teacher.calendar_done'),
     ])->toHtml() ?>)">
    <div class="flex items-center justify-between mb-4">
        <button type="button" @click="shift(-1)" class="w-9 h-9 rounded-lg border border-[#ebe8e1] hover:bg-[#faf8f3] text-[#182438]" aria-label="Previous month">&lsaquo;</button>
        <h3 class="font-semibold text-[#182438]" x-text="monthLabel"></h3>
        <button type="button" @click="shift(1)" class="w-9 h-9 rounded-lg border border-[#ebe8e1] hover:bg-[#faf8f3] text-[#182438]" aria-label="Next month">&rsaquo;</button>
    </div>
    <div class="grid grid-cols-7 text-center text-[11px] font-semibold uppercase text-[#8b949d] mb-1">
        <template x-for="d in ['Mon','Tue','Wed','Thu','Fri','Sat','Sun']" :key="d"><span x-text="d"></span></template>
    </div>
    <div class="grid grid-cols-7 gap-y-1">
        <template x-for="cell in cells" :key="cell.date">
            <button type="button" @click="go(cell.date)" @mouseenter="hover = cell.date" @mouseleave="hover = null" :title="describe(cell.date)"
                    class="relative h-12 md:h-16 text-sm transition hover:bg-[#ffe3d1] flex flex-col items-center justify-start pt-1.5"
                    :class="{ 'text-[#c9ced6]': !cell.inMonth, 'text-[#182438]': cell.inMonth, 'bg-[#fff0e6]': cell.inWeek, 'rounded-l-lg': cell.weekStart, 'rounded-r-lg': cell.weekEnd }">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full" :class="cell.today ? 'ring-2 ring-[#ff8555] font-bold' : ''" x-text="cell.day"></span>
                <span x-show="days[cell.date]" class="mt-0.5 flex items-center gap-1">
                    <span class="block w-2 h-2 rounded-full" :class="days[cell.date] && days[cell.date].done === days[cell.date].total ? 'bg-[#68b82f]' : 'bg-[#ff8555]'"></span>
                    <span class="hidden md:inline text-[10px] text-[#5f6873]" x-text="days[cell.date] ? days[cell.date].total + ' ' + (days[cell.date].total === 1 ? labels.one : labels.many) : ''"></span>
                </span>
            </button>
        </template>
    </div>
    <div class="mt-4 pt-3 border-t border-[#ebe8e1] flex items-center justify-between text-xs text-[#5f6873] min-h-[2rem]">
        <span x-text="hover ? describe(hover) : ''"></span>
        <button type="button" @click="go(todayStr)" class="font-medium text-[#ff8555] hover:text-[#f47548]" x-text="labels.today"></button>
    </div>
</div>

<div id="addActivityModal" class="fixed inset-0 bg-black/50 flex items-start sm:items-center justify-center z-50 hidden p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl p-8 w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-[#182438]"><?php echo e(__('teacher.new_activity')); ?></h3>
            <button onclick="this.closest('#addActivityModal').classList.add('hidden')" class="text-[#8b949d] hover:text-[#5f6873] text-2xl">&times;</button>
        </div>

        <?php if($errors->any()): ?>
            <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">
                <ul class="list-disc pl-4 space-y-1">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('teacher.activities.store')); ?>" class="space-y-4">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="week" value="<?php echo e($weekStart->toDateString()); ?>">
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2"><?php echo e(__('teacher.title')); ?></label>
                <input type="text" name="title" required maxlength="255" value="<?php echo e(old('title')); ?>" class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2"><?php echo e(__('teacher.type')); ?></label>
                <select name="type" required class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                    <option value="Lesson"><?php echo e(__('teacher.type_lesson')); ?></option>
                    <option value="Craft"><?php echo e(__('teacher.type_craft')); ?></option>
                    <option value="Music"><?php echo e(__('teacher.type_music')); ?></option>
                    <option value="Story"><?php echo e(__('teacher.type_story')); ?></option>
                    <option value="Movement"><?php echo e(__('teacher.type_movement')); ?></option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2"><?php echo e(__('teacher.day')); ?></label>
                <select name="day_of_week" required class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                    <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $offset => $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($day); ?>"><?php echo e($weekStart->copy()->addDays($offset)->format('D')); ?>, <?php echo e($weekStart->copy()->addDays($offset)->format('M j')); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-[#182438] mb-2"><?php echo e(__('teacher.start_time')); ?></label>
                    <input type="time" name="start_time" required value="<?php echo e(old('start_time')); ?>" class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#182438] mb-2"><?php echo e(__('teacher.end_time')); ?></label>
                    <input type="time" name="end_time" required value="<?php echo e(old('end_time')); ?>" class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2"><?php echo e(__('teacher.group_optional')); ?></label>
                <input type="text" name="group_name" maxlength="255" value="<?php echo e(old('group_name')); ?>" class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
            </div>
            <p class="text-xs text-[#8b949d]">
                <?php echo e(__('teacher.scheduled_for_week', ['date' => $weekStart->format('M j, Y')])); ?>

            </p>
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="this.closest('#addActivityModal').classList.add('hidden')" class="px-6 py-2.5 border border-[#ebe8e1] rounded-lg text-[#182438] hover:bg-[#faf8f3] transition"><?php echo e(__('teacher.cancel')); ?></button>
                <button type="submit" class="px-6 py-2.5 bg-[#ff8555] text-white rounded-lg hover:bg-[#f47548] transition font-medium"><?php echo e(__('teacher.add_activity')); ?></button>
            </div>
        </form>
    </div>
</div>
<div id="editActivityModal" class="fixed inset-0 bg-black/50 flex items-start sm:items-center justify-center z-50 hidden p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl p-8 w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-[#182438]"><?php echo e(__('teacher.edit_activity')); ?></h3>
            <button onclick="this.closest('#editActivityModal').classList.add('hidden')" class="text-[#8b949d] hover:text-[#5f6873] text-2xl">&times;</button>
        </div>

        <?php if($errors->any()): ?>
            <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">
                <ul class="list-disc pl-4 space-y-1">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" id="editActivityForm" class="space-y-4">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            <input type="hidden" name="_activity_id" id="editActivityId">
            <input type="hidden" name="week" id="editWeek" value="<?php echo e($weekStart->toDateString()); ?>">
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2"><?php echo e(__('teacher.title')); ?></label>
                <input type="text" name="title" id="editTitle" required maxlength="255" class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2"><?php echo e(__('teacher.type')); ?></label>
                <select name="type" id="editType" required class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                    <option value="Lesson"><?php echo e(__('teacher.type_lesson')); ?></option>
                    <option value="Craft"><?php echo e(__('teacher.type_craft')); ?></option>
                    <option value="Music"><?php echo e(__('teacher.type_music')); ?></option>
                    <option value="Story"><?php echo e(__('teacher.type_story')); ?></option>
                    <option value="Movement"><?php echo e(__('teacher.type_movement')); ?></option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2"><?php echo e(__('teacher.day')); ?></label>
                <select name="day_of_week" id="editDay" required class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                    <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($day); ?>"><?php echo e($day); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-[#182438] mb-2"><?php echo e(__('teacher.start_time')); ?></label>
                    <input type="time" name="start_time" id="editStart" required class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#182438] mb-2"><?php echo e(__('teacher.end_time')); ?></label>
                    <input type="time" name="end_time" id="editEnd" required class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2"><?php echo e(__('teacher.group_optional')); ?></label>
                <input type="text" name="group_name" id="editGroup" maxlength="255" class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
            </div>
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="this.closest('#editActivityModal').classList.add('hidden')" class="px-6 py-2.5 border border-[#ebe8e1] rounded-lg text-[#182438] hover:bg-[#faf8f3] transition"><?php echo e(__('teacher.cancel')); ?></button>
                <button type="submit" class="px-6 py-2.5 bg-[#ff8555] text-white rounded-lg hover:bg-[#f47548] transition font-medium"><?php echo e(__('teacher.save_changes')); ?></button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
window.activityCalendar = function (days, weekStart, baseUrl, labels) {
    const iso = d => d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    const parse = s => { const [y, m, d] = s.split('-').map(Number); return new Date(y, m - 1, d); };
    const start = parse(weekStart);
    return {
        open: false, hover: null, days: days, labels: labels,
        view: new Date(start.getFullYear(), start.getMonth(), 1),
        todayStr: iso(new Date()),
        get monthLabel() { return this.view.toLocaleDateString(undefined, { month: 'long', year: 'numeric' }); },
        get cells() {
            const first = new Date(this.view.getFullYear(), this.view.getMonth(), 1);
            const offset = (first.getDay() + 6) % 7;            // Monday first
            const gridStart = new Date(first.getFullYear(), first.getMonth(), 1 - offset);
            const end = new Date(start.getFullYear(), start.getMonth(), start.getDate() + 6);
            const out = [];
            for (let i = 0; i < 42; i++) {
                const d = new Date(gridStart.getFullYear(), gridStart.getMonth(), gridStart.getDate() + i);
                out.push({
                    date: iso(d), day: d.getDate(), inMonth: d.getMonth() === this.view.getMonth(),
                    today: iso(d) === this.todayStr, inWeek: d >= start && d <= end,
                    weekStart: iso(d) === iso(start), weekEnd: iso(d) === iso(end),
                });
            }
            return out;
        },
        shift(n) { this.view = new Date(this.view.getFullYear(), this.view.getMonth() + n, 1); },
        describe(date) {
            const e = this.days[date];
            const label = parse(date).toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric' });
            if (!e) return label + ' · ' + this.labels.none;
            return label + ' · ' + e.total + ' ' + (e.total === 1 ? this.labels.one : this.labels.many) + ' (' + e.done + ' ' + this.labels.done + ')';
        },
        go(date) { window.location.href = baseUrl + '?week=' + date; },
    };
};

function filterActivities() {
    const type = document.getElementById('typeFilter').value;
    document.querySelectorAll('.activity-card').forEach(card => {
        card.style.display = (type === 'all' || card.dataset.type === type) ? '' : 'none';
    });
}

function openEditModal(button) {
    const card = button.closest('.activity-card');
    document.getElementById('editActivityForm').action = card.dataset.updateUrl;
    document.getElementById('editActivityId').value = card.dataset.id;
    document.getElementById('editTitle').value = card.dataset.title || '';
    document.getElementById('editType').value = card.dataset.type || 'Lesson';
    document.getElementById('editDay').value = card.dataset.day || 'Monday';
    document.getElementById('editStart').value = card.dataset.start || '';
    document.getElementById('editEnd').value = card.dataset.end || '';
    document.getElementById('editGroup').value = card.dataset.group || '';
    document.getElementById('editActivityModal').classList.remove('hidden');
}

<?php if($errors->any()): ?>
    <?php if(old('_activity_id')): ?>
        // The edit form bounced: restore it instead of the create form.
        document.getElementById('editActivityForm').action = <?php echo json_encode(route('teacher.activities.update', old('_activity_id')), 512) ?>;
        document.getElementById('editActivityId').value = <?php echo json_encode(old('_activity_id'), 15, 512) ?>;
        document.getElementById('editTitle').value = <?php echo json_encode(old('title'), 15, 512) ?>;
        document.getElementById('editType').value = <?php echo json_encode(old('type'), 15, 512) ?>;
        document.getElementById('editDay').value = <?php echo json_encode(old('day_of_week'), 15, 512) ?>;
        document.getElementById('editStart').value = <?php echo json_encode(old('start_time'), 15, 512) ?>;
        document.getElementById('editEnd').value = <?php echo json_encode(old('end_time'), 15, 512) ?>;
        document.getElementById('editGroup').value = <?php echo json_encode(old('group_name'), 15, 512) ?>;
        document.getElementById('editWeek').value = <?php echo json_encode(old('week'), 15, 512) ?>;
        const editTarget = document.querySelector('#editActivityModal [data-activity-target]');
        editTarget.querySelector('[data-target-mode]').value = <?php echo json_encode(old('target_mode', 'section'), 512) ?>;
        const oldTargetIds = <?php echo json_encode(old('target_student_ids', [] ), 512) ?>.map(String);
        Array.from(document.getElementById('editTargetStudents').options).forEach(option => option.selected = oldTargetIds.includes(option.value));
        syncActivityTarget(editTarget);
        document.getElementById('editActivityModal').classList.remove('hidden');
    <?php else: ?>
        document.getElementById('addActivityModal').classList.remove('hidden');
    <?php endif; ?>
<?php endif; ?>
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.teacher', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/teacher/activities.blade.php ENDPATH**/ ?>