# KidLink Database Schema

Engine: **PostgreSQL** (production/Supabase). Local/tests run on **SQLite**
(`:memory:`), so types below are the Postgres rendering; SQLite uses the
nearest equivalent (`enum` becomes a `varchar` + `CHECK`, `bigint` an
`INTEGER`). All `enum`/`CHECK`-constrained columns are widened via dedicated
follow-up migrations — edit the original creation migration *and* add a
driver-branched widening migration.

- 17 base tables plus the Laravel framework tables (`migrations`, `cache`,
  `cache_locks`, `sessions`, `password_reset_tokens`).
- Primary keys are `bigint` identity (`id`) unless noted otherwise.
- Every user-owned table cascades on delete (`students`, `users`) unless noted.

Legend: `PK` primary key · `FK` foreign key · `UQ` unique · `NN` not null ·
`→` references.

---

## Entity overview

```
users ──┬──< students (teacher_id, parent_id, user_id)
        ├──< activities (teacher_id)
        ├──< announcements (author_id)
        ├──< messages (sender_id, receiver_id)
        └──< parent_student >── students
students ─┬──< attendance (marked_by → users)
          ├──< game_scores
          ├──< points
          ├──< eccd_records (assessed_by → users)
          └──< activity_attendance >── activities
```

- `users.role` drives the four dashboards: `teacher`, `parent`, `student`,
  `super_admin`.
- `students` links a child to their `teacher_id`, an optional login account
  `user_id` (role `student`), and a primary `parent_id`; additional
  guardianships live in the `parent_student` pivot.

---

## users

| Column | Type | Constraints |
| --- | --- | --- |
| id | bigint | **PK** |
| first_name | varchar(255) | NN |
| last_name | varchar(255) | NN |
| email | varchar(255) | NN, **UQ** |
| email_verified_at | timestamp | |
| password | varchar(255) | NN |
| role | varchar(255) | NN, default `parent`, CHECK(in `teacher`,`parent`,`student`,`super_admin`) |
| contact | varchar(255) | |
| remember_token | varchar(100) | |
| is_active | boolean | NN, default `true` |
| created_at / updated_at | timestamp | |

Indexes: `users_email_unique (email)`, `idx_users_role_created (role, created_at)`.

## students

| Column | Type | Constraints |
| --- | --- | --- |
| id | bigint | **PK** |
| first_name | varchar(255) | NN |
| last_name | varchar(255) | NN |
| full_name | varchar(255) | |
| sex | varchar(255) | NN, CHECK(in `Female`,`Male`) |
| date_of_birth | date | NN |
| address | varchar(255) | |
| dominant_hand | varchar(255) | NN, default `Not yet established`, CHECK(in `Right`,`Left`,`Both`,`Not yet established`) |
| parent_id | bigint | **FK** → users.id, `ON DELETE CASCADE` |
| teacher_id | bigint | **FK** → users.id, `ON DELETE SET NULL` |
| user_id | bigint | **FK** → users.id, `ON DELETE SET NULL` |
| enrolled_date | date | |
| is_active | boolean | NN, default `true` |
| father_name | varchar(255) | |
| father_contact | varchar(255) | |
| mother_name | varchar(255) | |
| mother_contact | varchar(255) | |
| siblings_count | integer | NN, default `0` |
| class_name | varchar(255) | `2-3 years old` / `4-5 years old` |
| created_at / updated_at | timestamp | |

Indexes: `idx_students_parent_id`, `idx_students_teacher_id`.

Notes:
- `class_name` section list is defined in `Student::SECTIONS`; `sex` and
  `dominant_hand` option lists in `Student::HANDS`.
- `full_name` is kept in sync by the model.

## parent_student (guardian pivot)

| Column | Type | Constraints |
| --- | --- | --- |
| id | bigint | **PK** |
| parent_id | bigint | NN, **FK** → users.id, `ON DELETE CASCADE` |
| student_id | bigint | NN, **FK** → students.id, `ON DELETE CASCADE` |
| created_at / updated_at | timestamp | |

Indexes: **UQ** `(parent_id, student_id)`, `idx_parent_student_parent`,
`idx_parent_student_student`.

## activities

| Column | Type | Constraints |
| --- | --- | --- |
| id | bigint | **PK** |
| title | varchar(255) | NN |
| type | varchar(255) | NN, CHECK(in `Lesson`,`Craft`,`Music`,`Story`,`Movement`) |
| day_of_week | varchar(255) | NN, CHECK(in `Monday`…`Friday`) |
| start_time | time | NN |
| end_time | time | NN |
| group_name | varchar(255) | |
| teacher_id | bigint | NN, **FK** → users.id, `ON DELETE CASCADE` |
| week_start | date | |
| is_completed | boolean | NN, default `false` |
| created_at / updated_at | timestamp | |

## activity_attendance

| Column | Type | Constraints |
| --- | --- | --- |
| id | bigint | **PK** |
| activity_id | bigint | NN, **FK** → activities.id, `ON DELETE CASCADE` |
| student_id | bigint | NN, **FK** → students.id, `ON DELETE CASCADE` |
| score | integer | |
| feedback | text | |
| created_at / updated_at | timestamp | |

Indexes: **UQ** `(activity_id, student_id)`, `idx_activity_attendance_student (student_id)`.

## attendance

| Column | Type | Constraints |
| --- | --- | --- |
| id | bigint | **PK** |
| student_id | bigint | NN, **FK** → students.id, `ON DELETE CASCADE` |
| date | date | NN |
| status | varchar(255) | NN, CHECK(in `Present`,`Absent`,`Late`,`Excused`) |
| marked_by | bigint | NN, **FK** → users.id, `ON DELETE CASCADE` |
| created_at / updated_at | timestamp | |

Indexes: **UQ** `(student_id, date)` + `idx_attendance_student_date (student_id, date)`.

## game_scores

| Column | Type | Constraints |
| --- | --- | --- |
| id | bigint | **PK** |
| student_id | bigint | NN, **FK** → students.id, `ON DELETE CASCADE` |
| game_type | varchar(255) | NN, CHECK (see list below) |
| score | integer | NN |
| total | integer | NN |
| level | smallint | NN, default `1` |
| percentage | integer | |
| time_taken | integer | seconds |
| mistakes | smallint | |
| created_at / updated_at | timestamp | |

Indexes: **UQ** `(student_id, game_type, level)`, `idx_game_scores_student (student_id)`,
`game_scores_level_index (level)`.

`game_type` allowed values:
`letters`, `numbers`, `colors`, `colors-sort`, `whats-missing`, `shapes`,
`animals`, `animals-memory`, `animals-match`, `shapes-bingo`.

Accuracy used everywhere is `score / (total + coalesce(mistakes, 0))`
(`AnalyticsService::ACCURACY_SQL`).

## points

| Column | Type | Constraints |
| --- | --- | --- |
| id | bigint | **PK** |
| student_id | bigint | NN, **FK** → students.id, `ON DELETE CASCADE` |
| game_type | varchar(255) | NN |
| level | smallint | NN, default `1` |
| points_earned | integer | NN, default `0` |
| total_points | integer | NN, default `0` — running balance per game type |
| source | varchar(255) | NN, e.g. `level_complete` |
| metadata | json | |
| created_at / updated_at | timestamp | |

Indexes: `points_student_id_game_type_index`, `points_student_id_created_at_index`,
`points_game_type_index`, `points_source_index`.

Notes: lifetime totals sum `points_earned`, never `total_points` (the latter is
a cumulative balance and re-summing would double-count).

## eccd_records

Early Childhood Care and Development checklist — one row per child per
indicator. The active checklist is **7 domains / 109 indicators**, defined in
`Teacher\EccdController::$domains` / `$indicators` and mirrored in
`AnalyticsService::ECCD_DOMAINS`.

| Column | Type | Constraints |
| --- | --- | --- |
| id | bigint | **PK** |
| student_id | bigint | NN, **FK** → students.id, `ON DELETE CASCADE` |
| domain | varchar(255) | NN, CHECK (7 domains below) |
| indicator | text | NN — canonical English text (lang keys index off position) |
| notes | text | |
| status | varchar(255) | NN, default `Not Yet`, CHECK(in `Not Yet`,`In Progress`,`Achieved`) |
| assessed_by | bigint | NN, **FK** → users.id, `ON DELETE CASCADE` |
| assessed_at | date | |
| created_at / updated_at | timestamp | |

Indexes: `idx_eccd_records_student (student_id)`.

`domain` allowed values and indicator counts:

| Domain | Indicators | Lang slug |
| --- | --- | --- |
| Gross Motor | 13 | `gross_motor_0..12` |
| Fine Motor | 11 | `fine_motor_0..10` |
| Self-Help | 27 | `self_help_0..26` |
| Receptive Language | 5 | `receptive_0..4` |
| Expressive Language | 8 | `expressive_0..7` |
| Cognitive | 21 | `cognitive_0..20` |
| Social-Emotional | 24 | `social_emotional_0..23` |

Notes:
- Rows are bootstrapped (all `Not Yet`) when a teacher opens the ECCD screen
  and that child has zero records with them; the 109-row set is created in one
  pass.
- Indicator labels are localized via `lang/{en,fil,hil}/eccd.php` using the
  domain slug + zero-based position (`__('eccd.self_help_3')`), so **indicator
  order in the controller must not change** without updating the lang files.
- Percentage per domain = `count(Achieved) / count(rows in domain)`.

## expert_recommendations

| Column | Type | Constraints |
| --- | --- | --- |
| id | bigint | **PK** |
| title | varchar(255) | NN |
| message | text | NN |
| severity | varchar(255) | NN, default `info`, CHECK(in `info`,`warning`,`alert`,`success`) |
| created_at / updated_at | timestamp | |

## announcements

| Column | Type | Constraints |
| --- | --- | --- |
| id | bigint | **PK** |
| author_id | bigint | NN, **FK** → users.id, `ON DELETE CASCADE` |
| title | varchar(255) | NN |
| body | text | NN |
| created_at / updated_at | timestamp | |

Index: `announcements_created_at_index (created_at)`.

## messages

| Column | Type | Constraints |
| --- | --- | --- |
| id | bigint | **PK** |
| sender_id | bigint | NN, **FK** → users.id, `ON DELETE CASCADE` |
| receiver_id | bigint | NN, **FK** → users.id, `ON DELETE CASCADE` |
| subject | text | NN |
| body | text | NN |
| is_read | boolean | NN, default `false` |
| read_at | timestamp | |
| created_at / updated_at | timestamp | |

Indexes: `idx_messages_receiver_read (receiver_id, is_read)`, `idx_messages_sender`,
`messages_sender_id_created_at_index (sender_id, created_at)`.

---

## Framework tables (Laravel)

| Table | Key columns | Purpose |
| --- | --- | --- |
| migrations | id **PK**, migration, batch | applied migrations |
| cache | key **PK**, value, expiration | cache store (`VIEW_CACHE`) |
| cache_locks | key **PK**, owner, expiration | atomic locks |
| sessions | id **PK**, user_id, ip_address(45), user_agent, payload, last_activity | session driver |
| password_reset_tokens | email **PK**, token, created_at | password resets |

---

## Foreign-key actions at a glance

| From | Column | To | On delete |
| --- | --- | --- | --- |
| students | parent_id | users.id | CASCADE |
| students | teacher_id | users.id | SET NULL |
| students | user_id | users.id | SET NULL |
| parent_student | parent_id | users.id | CASCADE |
| parent_student | student_id | students.id | CASCADE |
| activities | teacher_id | users.id | CASCADE |
| activity_attendance | activity_id | activities.id | CASCADE |
| activity_attendance | student_id | students.id | CASCADE |
| attendance | student_id | students.id | CASCADE |
| attendance | marked_by | users.id | CASCADE |
| game_scores | student_id | students.id | CASCADE |
| points | student_id | students.id | CASCADE |
| eccd_records | student_id | students.id | CASCADE |
| eccd_records | assessed_by | users.id | CASCADE |
| announcements | author_id | users.id | CASCADE |
| messages | sender_id | users.id | CASCADE |
| messages | receiver_id | users.id | CASCADE |

---

## Migration notes

- Migrations live in `database/migrations`. `2024_01_01_00000*` create the
  core tables; later files widen enum/`CHECK` constraints.
- To add a value to an `enum`/`CHECK` column:
  1. Edit the original creation migration (covers fresh installs + SQLite tests).
  2. Add a new migration with driver branches: `sqlite` → return early;
     `mysql` → `MODIFY ... ENUM(...)`; `pgsql` → `DROP CONSTRAINT IF EXISTS` +
     `ADD CONSTRAINT` (+ `SET DEFAULT` if the default changes).
  3. Run `php artisan migrate --force` against the live Postgres database.
- Recent constraint widenings: `students.dominant_hand` (4 values) and
  `eccd_records.domain` (7 values, incl. `Social-Emotional`).
