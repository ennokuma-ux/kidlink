KidLink targeted fixes

Included files:
- public/css/kidlink-theme.css: consistent orange primary action styling, hover, focus, active, and disabled states; avoids recoloring pale orange surfaces.
- app/Http/Middleware/PreventDuplicateSubmission.php: cache backend failures no longer block form submissions; duplicate submissions return a clearer message.
- app/Http/Controllers/AnnouncementController.php: announcement publishing returns clearer database/schema error guidance.
- app/Exceptions/Handler.php: clearer JSON error messages for common HTTP/server failures.
- app/Services/LevelCompletionRecorder.php: explicitly calculates awarded level points from accuracy and caps them at 100.
- public/js/kidlink-game.js: shows the final accuracy-based score out of 100 and displays useful server/network errors with retry guidance.

Deployment:
1. Extract these files into the root of your KidLink repository, preserving the folder paths.
2. Commit and push to the branch connected to Render.
3. Wait for the Render deployment to finish successfully.
4. Test publishing an announcement and completing a game level.

Scoring rule:
Final score = round((correct answers / (total questions + mistakes)) * 100), clamped to 0-100.
A completed level can award no more than 100 points. Existing stored scores are not rewritten by this patch.

Validation performed: PHP syntax checks passed for all modified PHP files; Node syntax check passed for the modified game JavaScript. Full Laravel integration tests were not run because this ZIP does not include the vendor dependencies or a test suite.
