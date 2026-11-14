# AI Log

## Milestone 1 — Setup, static frontend, ERD

**Tools used**: gemini pro, claude.

**Tasks completed by AI**
 1.generated static views for the front page. 
 2.generated placeholder meal examples.
 3.generated app.js generic code.
 4. helped me fix the logic in my db diagram.
 5. helped me center the images on the front page.

## Mistakes AI made 

1. what: asked AI to help me move my files from main to milestone1 and it moved everything but      index.html.
    noticed: checked my github repository and noticed the extra file.
    fix: fixed it by finding the inital commit hash and force reseting it.
2. what: AI placed <script> inside an html file which violated agents.md rules.
    noticed: noticed when reading the code AI generated.
    fix: fixed it by moving js code inside app.js.

## Milestone 2 — Database and DAO layer

### Tools used
gemini pro, claude.

### Most important prompts
1. help me resolve the milestone1 merge conflict.
2. give me all entity daos i need based on my database
3. give me dummy data and create test doa.

### Cases where the AI was wrong or produced poor code
1. **What was wrong:** the config template was named `config_example.php`, but `AGENTS.md` and the course requirements say `config.example.php`. **How I noticed:** checked file names against the requirements. **How I fixed it:** renamed the file.

2. **What was wrong:** `docs/ERD.md` still described an `ingredients` table and a `created_by` column, but the real database has `categories`, `recipe_ingredients` and `user_id`. **How I noticed:** compared the ERD against the exported `.sql`. **How I fixed it:** redrew the ERD from the actual schema.

## Milestone 3 — Services, routes, OpenAPI, external API

### Tools used
Claude (Claude Code, model claude-sonnet-5-5): wrote the service layer, the FlightPHP routes with OpenAPI annotations, the TheMealDB integration and the test harness; updated README, AGENTS.md and this log. I reviewed the code and ran the checks described below.

### Most important prompts
1. "Strictly follow the requirements for Milestone 3 and do them, and explain what you did so I can copy-paste the code when needed" (attached the project zip and the requirements document).
2. Follow-up prompts about how each part works so I can explain it at the defense (BaseService validation, error handler, external API failure handling).

### Cases where the AI was wrong or produced poor code
1. **What was wrong:** the AI added the new TheMealDB settings (`MEALDB_BASE_URL`, `MEALDB_API_KEY`, `EXTERNAL_API_TIMEOUT`) only to `config.example.php`. My real `config.php` is gitignored and did not have them, so every external API call failed with `Call to undefined method Config::MEALDB_BASE_URL()`. **How I noticed:** the first run of the test harness against my real config returned 500 for all `/external/...` calls and the error log showed that message. **How I fixed it:** copied the three new methods from `config.example.php` into my `config.php` and added this step to the README run instructions.
2. **TODO before submitting:** add one more real case from your own testing (for example something in Swagger UI that did not match the code, a route that returned the wrong status code, or validation that was too strict or too loose) in the same format: what was wrong / how I noticed / how I fixed it. Do not submit this file with this line still in it, and do not invent a case that did not happen.
