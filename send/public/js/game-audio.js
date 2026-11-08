(function () {
    'use strict';

    const audioRoot = '/assets/audio/games/';
    const sounds = {
        cheer: [new Audio(audioRoot + 'cheer-1.mp3'), new Audio(audioRoot + 'cheer-2.mp3')],
        mistake: new Audio(audioRoot + 'mistake.mp3'),
        complete: new Audio(audioRoot + 'level-complete.wav'),
        gameOver: new Audio(audioRoot + 'game-over.wav')
    };

    Object.values(sounds).flat().forEach(function (audio) {
        audio.preload = 'auto';
        audio.volume = 0.65;
    });
    sounds.complete.volume = 0.8;
    sounds.gameOver.volume = 0.65;

    let completionPlayed = false;
    let exitPlayed = false;

    function play(audio) {
        try {
            audio.pause();
            audio.currentTime = 0;
            const result = audio.play();
            if (result && typeof result.catch === 'function') result.catch(function () {});
        } catch (error) {}
    }

    function playCheer() {
        const options = sounds.cheer;
        play(options[Math.floor(Math.random() * options.length)]);
    }

    function observeAnswerState(element) {
        if (!(element instanceof HTMLElement)) return;
        const isCorrect = element.classList.contains('correct') || element.classList.contains('covered');
        const isWrong = element.classList.contains('wrong') || element.classList.contains('wrong-click');
        const currentState = isCorrect ? 'correct' : (isWrong ? 'wrong' : '');

        if (!currentState || element.dataset.kidlinkSoundState === currentState) return;
        element.dataset.kidlinkSoundState = currentState;

        if (currentState === 'correct') playCheer();
        if (currentState === 'wrong') play(sounds.mistake);
    }

    function observeCompletion() {
        const popup = document.querySelector('.swal2-popup');
        if (!popup || completionPlayed) return;
        if (popup.getAttribute('aria-hidden') === 'true') return;

        const text = (popup.innerText || popup.textContent || '').trim();
        if (!text) return;

        completionPlayed = true;
        play(sounds.complete);
    }

    document.addEventListener('click', function (event) {
        const link = event.target.closest('a[href]');
        if (!link) return;
        if (link.closest('.swal2-popup')) return;
        if (completionPlayed || exitPlayed) return;

        const href = link.getAttribute('href') || '';
        if (href.includes('/student/games/') && href.includes('/play')) return;

        const isBackOrExit = link.textContent.trim().toLowerCase().match(/back|games|home|exit|quit/);
        if (isBackOrExit) {
            exitPlayed = true;
            play(sounds.gameOver);
        }
    }, true);

    const observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            if (mutation.type === 'attributes' && mutation.target instanceof HTMLElement) {
                observeAnswerState(mutation.target);
            }

            mutation.addedNodes.forEach(function (node) {
                if (!(node instanceof HTMLElement)) return;
                observeAnswerState(node);
                node.querySelectorAll('[class]').forEach(observeAnswerState);
            });
        });
        observeCompletion();
    });

    function start() {
        document.querySelectorAll('[class]').forEach(observeAnswerState);
        observer.observe(document.body, {
            subtree: true,
            childList: true,
            attributes: true,
            attributeFilter: ['class', 'aria-hidden']
        });
        observeCompletion();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start, { once: true });
    } else {
        start();
    }
})();
