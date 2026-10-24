```mermaid
erDiagram
    users ||--o{ recipes : "creates"
    users ||--o{ pantry : "owns"
    users ||--o{ favorites : "saves"
    recipes ||--o{ favorites : "favorited in"
    ingredients ||--o{ pantry : "stored as"

    users {
        int id PK
        varchar name
        varchar email
        varchar password
        varchar role
        timestamp created_at
    }
    recipes {
        int id PK
        int created_by FK
        varchar title
        text description
        text instructions
        varchar category
    }
    ingredients {
        int id PK
        varchar name
        varchar unit
    }
    pantry {
        int id PK
        int user_id FK
        int ingredient_id FK
        decimal quantity
        date expiration_date
    }
    favorites {
        int id PK
        int user_id FK
        int recipe_id FK
        timestamp created_at
    }
```
**What is "What can i cook?"**
What can i cook is a feature that lets users input ingridients they have on hand and the backend connects the input with the db which outputs all the meals that are covered by all (or most) ingridients the user picked.