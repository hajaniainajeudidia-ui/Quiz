# 🎯 Quiz App

Application web complète de quiz en ligne, composée d’un **backend Laravel 12** et d’un **frontend Vue 3**.

Le parcours de quiz suit l’ordre de création des quizzes (`created_at` croissant), avec timer, scores détaillés, classement et espace d’administration.

---

## 📁 Structure du Projet




```

Quiz/

├── backend/ # API Laravel 12 + Sanctum

├── frontend/ # Application Vue 3 + Vite + Tailwind

├── LICENSE # Licence MIT (si présent à la racine)

└── README.md # Ce fichier

```



---



## 🚀 Fonctionnalités



### Utilisateur

- Inscription / Connexion (Laravel Sanctum)

- Redirection selon le rôle (Admin / Utilisateur)

- Bouton **Commencer le Quiz**

- Parcours des quizzes dans l’ordre de création

- Timer par quiz

- Passage automatique au quiz suivant

- Résultat final avec pourcentage

- Historique des scores avec corrections détaillées

- Classement (Leaderboard)

- Gestion des erreurs réseau



### Administrateur

- Tableau de bord

- CRUD des Quizzes

- CRUD des Questions (4 réponses, 1 correcte)

- Protection des routes admin (backend + frontend)



### Thématiques disponibles

- Madagascar (Géographie, Histoire, Culture, Faune & Flore, Provinces, Climat…)

- Développement (HTML/CSS, JavaScript, Vue.js 3, Laravel & PHP, Git & GitHub, MySQL, API REST)



---



## 🛠️ Technologies



| Partie   | Technologies                                                                          |
| -------- | ------------------------------------------------------------------------------------- |
| Backend  | Laravel 12, Sanctum, MySQL, PHP 8.2+                                                  |
| Frontend | Vue 3, Vite, Pinia, Vue Router, Tailwind CSS, Axios, Lucide Icons, Vue Toastification |



---



## ⚙️ Installation



### Prérequis

- PHP 8.2+

- Composer

- Node.js 18+

- MySQL

- Git



---


## Clone this repository

```bash
git clone https://github.com/YourUsername/Quiz.git


```
### 1. Backend



```bash

cd backend

composer install

cp .env.example .env

php artisan key:generate

```



Configure le fichier `.env` :



```env

DB_CONNECTION=mysql

DB_HOST=127.0.0.1

DB_PORT=3306

DB_DATABASE=quiz_db

DB_USERNAME=root

DB_PASSWORD=

```



Puis :



```bash

php artisan migrate --seed

php artisan serve

```



API disponible sur : `http://127.0.0.1:8000`



---



### 2. Frontend



```bash

cd frontend

npm install



```
Lance le serveur :



```bash

npm run dev

```



Application disponible sur : `http://localhost:5173`



---



## 👤 Compte Admin par défaut



```

Email : admin@quiz.com

Password : password

```



---



## 📡 Principales Routes API



### Authentification

- `POST /api/register`

- `POST /api/login`

- `POST /api/logout`



### Quiz (Utilisateur)

- `GET /api/quiz` → liste des quizzes actifs (ordre `created_at` croissant)

- `POST /api/quiz/start`

- `GET /api/quiz/{attempt}/question/{number}`

- `POST /api/quiz/submit-answer`

- `POST /api/quiz/finish`

- `GET /api/quiz/result/{attempt}`

- `GET /api/quiz/leaderboard`

- `GET /api/quiz/my-scores` → scores + corrections détaillées



### Admin

- `GET /api/admin/dashboard/stats`

- `GET|POST|PUT|DELETE /api/admin/quizzes`

- `GET|POST|PUT|DELETE /api/admin/questions`



---



## 🔐 Authentification



L’API utilise **Laravel Sanctum** (Bearer Token).



Après le login, ajoute ce header :



```

Authorization: Bearer {token}

```



Le frontend stocke le token dans `localStorage` et l’ajoute automatiquement via Axios.



---



## 🎮 Parcours de Quiz



1. L’utilisateur clique sur **Commencer le Quiz**

2. Le système démarre le **premier quiz** (`created_at` croissant)

3. 10 questions par quiz + timer

4. À la fin d’un quiz → passage automatique au suivant

5. À la fin du dernier quiz → page Résultat

6. Les scores et corrections sont visibles dans **Mes Scores**



---



## 🖥️ Pages Frontend Principales



| Page                       | Rôle                                             |
| -------------------------- | ------------------------------------------------ |
| `Home.vue`                 | Accueil + bouton Commencer le Quiz               |
| `TakeQuiz.vue`             | Passage des questions / enchaînement des quizzes |
| `QuizResult.vue`           | Score final                                      |
| `MyScores.vue`             | Historique + corrections détaillées              |
| `Leaderboard.vue`          | Classement                                       |
| `AdminDashboard.vue`       | Tableau de bord admin                            |
| `AdminQuiz.vue`            | Gestion des quizzes                              |
| `AdminQuestion.vue`        | Gestion des questions                            |
| `Register.vue`             | Page d'inscription                               |
| `Login.vue`                | Page d'authentification                          |
| `QuizStart.vue`            | Page de demarrage de Quiz                        |


---



## 🧪 Test rapide



1. Lancer le backend : `php artisan serve`

2. Lancer le frontend : `npm run dev`

3. Créer un compte ou se connecter

4. Cliquer sur **Commencer le Quiz**

5. Vérifier le parcours, le résultat, puis **Mes Scores**



---



## 📝 Licence



Ce projet est sous licence **MIT**.

Voir les fichiers `LICENSE` dans `backend/` et `frontend/`.



---


