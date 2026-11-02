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