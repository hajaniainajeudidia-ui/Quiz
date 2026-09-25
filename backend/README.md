```markdown
# Quiz App - Backend API

API REST complète pour une application de quiz en ligne, développée avec **Laravel 12**.

---

## Fonctionnalités

- Authentification sécurisée avec **Laravel Sanctum**
- Gestion des rôles (Admin / Utilisateur)
- CRUD complet des Quizzes et Questions
- Système de tentatives de quiz
- Calcul automatique des scores
- Leaderboard (classement)
- Historique des scores avec corrections détaillées
- Protection des routes admin

---

## Technologies

- **Laravel 12**
- **Laravel Sanctum** (Authentification par token)
- **MySQL**
- **PHP 8.2+**

---

## Installation

### 1. Cloner le projet

```bash
git clone <url-du-repo>
cd quiz-backend
```

### 2. Installer les dépendances

```bash
composer install
```

### 3. Configurer l'environnement

```bash
cp .env.example .env
php artisan key:generate
```

Modifie le fichier `.env` :

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=quiz_db
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Lancer les migrations et le seeder

```bash
php artisan migrate --seed
```

### 5. Lancer le serveur

```bash
php artisan serve
```

L’API sera accessible sur : `http://127.0.0.1:8000`

---

## Authentification

### Register

```http
POST /api/v1/register
```

```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

### Login

```http
POST /api/v1/login
```

```json
{
  "email": "admin@quiz.com",
  "password": "password"
}
```

**Réponse :**

```json
{
  "status": true,
  "user": {
    "id": 1,
    "name": "Administrateur",
    "email": "admin@quiz.com",
    "is_admin": true
  },
  "token": "1|xxxxxxxxxxxxxxxx"
}
```

---

## Routes Principales

### Utilisateur

| Méthode | Endpoint                        | Description                  |
|---------|---------------------------------|------------------------------|
| GET     | `/api/v1/quiz`                  | Liste des quizzes actifs     |
| POST    | `/api/v1/quiz/start`            | Démarrer un quiz             |
| GET     | `/api/v1/quiz/{attempt}/question/{number}` | Récupérer une question |
| POST    | `/api/v1/quiz/submit-answer`    | Soumettre une réponse        |
| POST    | `/api/v1/quiz/finish`           | Terminer le quiz             |
| GET     | `/api/v1/quiz/result/{attempt}` | Voir le résultat             |
| GET     | `/api/v1/quiz/leaderboard`      | Classement                   |
| GET     | `/api/v1/quiz/my-scores`        | Mes scores + corrections     |

### Admin (protégé)

| Méthode | Endpoint                        | Description                  |
|---------|---------------------------------|------------------------------|
| GET     | `/api/v1/admin/dashboard/stats` | Statistiques du dashboard    |
| GET     | `/api/v1/admin/quizzes`         | Liste des quizzes            |
| POST    | `/api/v1/admin/quizzes`         | Créer un quiz                |
| PUT     | `/api/v1/admin/quizzes/{id}`    | Modifier un quiz             |
| DELETE  | `/api/v1/admin/quizzes/{id}`    | Supprimer un quiz            |
| GET     | `/api/v1/admin/questions`       | Liste des questions          |
| POST    | `/api/v1/admin/questions`       | Créer une question           |
| PUT     | `/api/v1/admin/questions/{id}`  | Modifier une question        |
| DELETE  | `/api/v1/admin/questions/{id}`  | Supprimer une question       |

---

## Compte Admin par défaut

```
Email    : admin@quiz.com
Password : password
```

---

## Structure Principale

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   │   ├── AuthController.php
│   │   │   ├── QuizController.php
│   │   │   └── Admin/
│   │   │       ├── DashboardController.php
│   │   │       ├── QuizController.php
│   │   │       └── QuestionController.php
│   └── Middleware/
│       └── AdminMiddleware.php
├── Models/
│   ├── User.php
│   ├── Quiz.php
│   ├── Question.php
│   ├── Answer.php
│   └── UserQuizAttempt.php
database/
├── migrations/
└── seeders/
    └── QuizSeeder.php
routes/
└── api.php
```

---

## Middleware Admin

Les routes admin sont protégées par :

```php
Route::middleware(['auth:sanctum', 'admin'])
```

---

## Test avec Insomnia

1. Faire un `Login`
2. Copier le `token`
3. Ajouter le header :
   ```
   Authorization: Bearer {token}
   ```

---

## Licence

Ce projet est sous licence **MIT**.  
Voir le fichier [LICENSE](LICENSE) pour plus de détails.

