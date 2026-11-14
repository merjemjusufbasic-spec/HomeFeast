```mermaid
erDiagram
    users o|--o{ recipes : creates
    categories o|--o{ recipes : categorizes
    users ||--o{ pantry : owns
    users ||--o{ favorites : saves
    recipes ||--o{ favorites : receives
    recipes ||--o{ recipe_ingredients : contains

    users {
        int id PK
        varchar name
        varchar email UK
        varchar password
        enum role
        timestamp created_at
    }
    categories {
        int id PK
        varchar name UK
        text description
        timestamp created_at
    }
    recipes {
        int id PK
        varchar title
        text description
        text instructions
        int prep_time
        int category_id FK
        int user_id FK
        timestamp created_at
    }
    recipe_ingredients {
        int id PK
        int recipe_id FK
        varchar ingredient_name
        varchar quantity
    }
    pantry {
        int id PK
        int user_id FK
        varchar ingredient_name
        decimal quantity
        varchar unit
        timestamp created_at
    }
    favorites {
        int id PK
        int user_id FK
        int recipe_id FK
        timestamp created_at
    }
```

## Relationships and delete rules
| Relationship | Type | On delete |
|---|---|---|
| users → recipes | 1 : N | CASCADE |
| users → pantry | 1 : N | CASCADE |
| users → favorites | 1 : N | CASCADE |
| categories → recipes | 1 : N | SET NULL |
| recipes → favorites | 1 : N | CASCADE |
| recipes → recipe_ingredients | 1 : N | CASCADE |

`recipes.category_id` and `recipes.user_id` are nullable foreign keys. Deleting a category sets `recipes.category_id` to `NULL`; deleting a user cascades to that user's recipes, pantry rows, and favorites. Deleting a recipe cascades to its recipe ingredients and favorites. The `favorites` table has a unique constraint on (`user_id`, `recipe_id`) to prevent duplicate favorites.

**What can I cook?**
The pantry matching feature compares ingredient names in `pantry` with `recipe_ingredients` and returns recipes ranked by the number of matching ingredients.