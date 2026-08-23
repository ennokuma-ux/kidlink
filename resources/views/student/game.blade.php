@extends('layouts.app')

@section('title', 'Game - '.ucfirst($type))

@push('styles')
<style>
.student-body { background: linear-gradient(135deg, #f9f3d5 0%, #e3f1f2 40%, #e3f2f4 70%, #f9e9ed 100%); min-height: 100vh; }
.glass { background: #ffffff; backdrop-filter: blur(10px); border: 1px solid #ebe8e1; }
.option-btn { transition: all 0.3s ease; cursor: pointer; }
.option-btn:hover { transform: scale(1.05); }
.option-btn.correct { background: #22c55e !important; color: white !important; }
.option-btn.wrong { background: #ef4444 !important; color: white !important; }
.progress-bar { transition: width 0.5s ease; }
.modal-overlay { background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); }
</style>
@endpush

@section('content')
<div class="student-body">
    <div class="container mx-auto px-4 py-6">
        <nav class="flex items-center justify-between mb-8">
            <a href="{{ route('student.home') }}" class="text-[#5f6873] hover:text-[#182438] transition flex items-center gap-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back
            </a>
            <span class="text-[#182438] font-bold text-xl">KidLink Games</span>
            <div class="w-20"></div>
        </nav>

        <div class="max-w-2xl mx-auto">
            <div class="glass rounded-3xl p-8 mb-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-[#182438] capitalize">{{ $type }} Challenge</h2>
                    <span class="text-[#5f6873] text-lg" id="questionCounter">1/{{ count($gameQuestions) }}</span>
                </div>

                <div class="w-full bg-[#ff8555] rounded-full h-3 mb-8">
                    <div class="progress-bar bg-white h-3 rounded-full" id="progressBar" style="width: {{ 100 / count($gameQuestions) }}%"></div>
                </div>

                <div id="questionArea">
                    <h3 class="text-2xl font-bold text-[#182438] text-center mb-8" id="questionText">{{ $gameQuestions[0]['question'] }}</h3>
                    <div class="grid grid-cols-2 gap-4" id="optionsContainer">
                        @foreach($gameQuestions[0]['options'] as $idx => $option)
                        <button class="option-btn bg-[#ff8555] hover:bg-[#f47548] text-[#182438] text-xl font-semibold p-6 rounded-2xl" data-index="{{ $idx }}" onclick="checkAnswer({{ $idx }})">
                            {{ $option }}
                        </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="celebrationModal" class="fixed inset-0 modal-overlay flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-3xl p-10 text-center max-w-md mx-4 transform scale-0 transition-transform" id="celebrationContent">
        <div class="text-6xl mb-4" id="celebrationEmoji">🎉</div>
        <h3 class="text-3xl font-bold text-[#182438] mb-2" id="celebrationTitle">Great Job!</h3>
        <p class="text-[#5f6873] mb-6" id="celebrationMessage">Amazing!</p>
        <button onclick="nextQuestion()" class="bg-gradient-to-r from-[#ff8555] to-[#f05d56] text-[#182438] px-8 py-3 rounded-xl font-semibold hover:from-[#f47548] hover:to-[#e04942] transition">Next →</button>
    </div>
</div>

<div id="encouragementModal" class="fixed inset-0 modal-overlay flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-3xl p-10 text-center max-w-md mx-4">
        <div class="text-6xl mb-4">💪</div>
        <h3 class="text-3xl font-bold text-[#182438] mb-2">Almost There!</h3>
        <p class="text-[#5f6873] mb-6">Keep trying, you'll get it!</p>
        <button onclick="nextQuestion()" class="bg-gradient-to-r from-[#ff8555] to-[#f05d56] text-[#182438] px-8 py-3 rounded-xl font-semibold hover:from-[#f47548] hover:to-[#e04942] transition">Try Next →</button>
    </div>
</div>

<div id="finishModal" class="fixed inset-0 modal-overlay flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-3xl p-10 text-center max-w-md mx-4">
        <div class="text-6xl mb-4" id="finishEmoji">🏆</div>
        <h3 class="text-3xl font-bold text-[#182438] mb-2">Game Complete!</h3>
        <p class="text-5xl font-bold text-[#ff8555] my-4" id="finalScore">0%</p>
        <p class="text-[#5f6873] mb-6" id="finishMessage">Great effort!</p>
        <div class="flex gap-4 justify-center">
            <a href="{{ route('student.game.show', $type) }}" class="bg-gradient-to-r from-[#ff8555] to-[#f05d56] text-[#182438] px-8 py-3 rounded-xl font-semibold hover:from-[#f47548] hover:to-[#e04942] transition">Play Again</a>
            <a href="{{ route('student.home') }}" class="border-2 border-[#ff8555] text-[#ff8555] px-8 py-3 rounded-xl font-semibold hover:bg-[#fff0e6] transition">Home</a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const questions = @json($gameQuestions);
const gameType = '{{ $type }}';
let currentQuestion = 0;
let score = 0;
let answered = false;

function checkAnswer(index) {
    if (answered) return;
    answered = true;

    const correct = questions[currentQuestion].answer;
    const buttons = document.querySelectorAll('.option-btn');

    buttons.forEach((btn, i) => {
        btn.onclick = null;
        if (i === correct) btn.classList.add('correct');
        if (i === index && index !== correct) btn.classList.add('wrong');
    });

    if (index === correct) {
        score++;
        document.getElementById('celebrationModal').classList.remove('hidden');
        document.getElementById('celebrationContent').classList.add('scale-100');
    } else {
        document.getElementById('encouragementModal').classList.remove('hidden');
    }
}

function nextQuestion() {
    document.getElementById('celebrationModal').classList.add('hidden');
    document.getElementById('encouragementModal').classList.add('hidden');
    document.getElementById('celebrationContent').classList.remove('scale-100');
    currentQuestion++;
    answered = false;

    if (currentQuestion >= questions.length) {
        finishGame();
        return;
    }

    const q = questions[currentQuestion];
    document.getElementById('questionText').textContent = q.question;
    document.getElementById('questionCounter').textContent = `${currentQuestion + 1}/${questions.length}`;
    document.getElementById('progressBar').style.width = `${((currentQuestion + 1) / questions.length) * 100}%`;

    const container = document.getElementById('optionsContainer');
    container.innerHTML = '';
    q.options.forEach((opt, idx) => {
        const btn = document.createElement('button');
        btn.className = 'option-btn bg-[#ff8555] hover:bg-[#f47548] text-[#182438] text-xl font-semibold p-6 rounded-2xl';
        btn.textContent = opt;
        btn.dataset.index = idx;
        btn.onclick = () => checkAnswer(idx);
        container.appendChild(btn);
    });
}

function finishGame() {
    const pct = Math.round((score / questions.length) * 100);
    document.getElementById('finalScore').textContent = `${pct}%`;
    document.getElementById('finishEmoji').textContent = pct >= 80 ? '🏆' : (pct >= 50 ? '🌟' : '💪');
    document.getElementById('finishMessage').textContent = pct >= 80 ? 'Excellent work!' : (pct >= 50 ? 'Good job, keep practicing!' : 'Keep trying, you\'re improving!');
    document.getElementById('finishModal').classList.remove('hidden');

    fetch('{{ route('student.score.save') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ type: gameType, score: score, total: questions.length })
    });
}
</script>
@endpush


