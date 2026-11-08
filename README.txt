KidLink — Individual Analytics Student Search Patch (Starter)

IMPORTANT
This is a starter patch, not a drop-in replacement for your entire project. Your exact routes, controller names, model names, and database columns may differ. Review the TODOs and adapt them to your existing KidLink code before using in production.

Purpose
- Let a teacher search students by full name or nickname.
- Return only students the signed-in teacher is allowed to access.
- Select a student and open that student's individual analytics page.
- Change age-group wording from “2–3 year olds” to “1–3 year olds” wherever that wording appears.

Files
- resources/views/teacher/analytics/partials/student-search.blade.php
  Search UI for a teacher analytics page.
- app/Http/Controllers/Teacher/IndividualAnalyticsController.example.php
  Example controller methods. Rename/remove ".example" when integrating and adapt model/relationship names.
- routes/web.php.example
  Example routes to merge into your existing routes/web.php.
- integration-notes.txt
  Checklist for adapting this patch.

Do not overwrite your existing routes or controller blindly. Merge the example into the existing app and preserve your current authentication/authorization checks.
