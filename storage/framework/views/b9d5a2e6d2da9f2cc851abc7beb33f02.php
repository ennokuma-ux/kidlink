

<?php $__env->startSection('title', 'Dashboard'); ?>
<?php $__env->startSection('page-title', 'Teacher Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#ff8555]">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[#5f6873] text-sm">Total Students</p>
                <p class="text-3xl font-bold text-[#182438]"><?php echo e($totalStudents); ?></p>
            </div>
            <div class="w-12 h-12 bg-[#fff0e6] rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-[#ff8555]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/></svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#68b82f]">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[#5f6873] text-sm">Present Today</p>
                <p class="text-3xl font-bold text-[#182438]"><?php echo e($presentToday); ?></p>
            </div>
            <div class="w-12 h-12 bg-[#e2f4e8] rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-[#68b82f]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#f05d56]">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[#5f6873] text-sm">Absent Today</p>
                <p class="text-3xl font-bold text-[#182438]"><?php echo e($absentToday); ?></p>
            </div>
            <div class="w-12 h-12 bg-[#fde8ef] rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-[#f05d56]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-[#258cc5]">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[#5f6873] text-sm">Activities Today</p>
                <p class="text-3xl font-bold text-[#182438]"><?php echo e($activitiesToday); ?></p>
            </div>
            <div class="w-12 h-12 bg-[#e1f0f8] rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-[#258cc5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-4">Weekly Attendance</h3>
        <canvas id="weeklyAttendanceChart" height="200"></canvas>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-4">Enrollment Trend</h3>
        <canvas id="enrollmentChart" height="200"></canvas>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-4">Age Distribution</h3>
        <canvas id="ageChart" height="250"></canvas>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-4">Activity Completion</h3>
        <canvas id="activityChart" height="250"></canvas>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
const weeklyCtx = document.getElementById('weeklyAttendanceChart').getContext('2d');
new Chart(weeklyCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($weeklyAttendance->pluck('date')); ?>,
datasets: [{
            label: 'Present',
            data: <?php echo json_encode($weeklyAttendance->pluck('present')); ?>,
            backgroundColor: '#68b82f',
            borderRadius: 4
        }, {
            label: 'Total',
            data: <?php echo json_encode($weeklyAttendance->pluck('total')); ?>,
            backgroundColor: '#ff8555',
            borderRadius: 4
        }]
    },
    options: { responsive: true, plugins: { legend: { position: 'top' } } }
});

const enrollCtx = document.getElementById('enrollmentChart').getContext('2d');
new Chart(enrollCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($enrollmentTrend->pluck('month')); ?>,
datasets: [{
            label: 'Enrollments',
            data: <?php echo json_encode($enrollmentTrend->pluck('total')); ?>,
            borderColor: '#258cc5',
            tension: 0.3,
            fill: true,
            backgroundColor: 'rgba(37, 140, 197, 0.1)'
        }]
    },
    options: { responsive: true, plugins: { legend: { position: 'top' } } }
});

const ageCtx = document.getElementById('ageChart').getContext('2d');
new Chart(ageCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($ageDistribution->keys()); ?>,
        datasets: [{
            data: <?php echo json_encode($ageDistribution->values()); ?>,
            backgroundColor: ['#ff8555', '#f05d56', '#258cc5', '#68b82f', '#d9b348']
        }]
    },
    options: { responsive: true, plugins: { legend: { position: 'right' } } }
});

const activityCtx = document.getElementById('activityChart').getContext('2d');
new Chart(activityCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($activityCompletion->pluck('type')); ?>,
datasets: [{
            label: 'Completed',
            data: <?php echo json_encode($activityCompletion->pluck('completed')); ?>,
            backgroundColor: '#68b82f',
            borderRadius: 4
        }, {
            label: 'Total',
            data: <?php echo json_encode($activityCompletion->pluck('total')); ?>,
            backgroundColor: '#ff8555',
            borderRadius: 4
        }]
    },
    options: { responsive: true, indexAxis: 'y', plugins: { legend: { position: 'top' } } }
});
</script>
<?php $__env->stopPush(); ?>


<?php echo $__env->make('layouts.teacher', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kidlink-laravel\resources\views/teacher/dashboard.blade.php ENDPATH**/ ?>