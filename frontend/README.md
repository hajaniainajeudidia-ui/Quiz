```markdown
# Quiz App - Frontend

Application frontend d'un système de quiz en ligne, développée avec **Vue 3 + Vite**.

---

## Fonctionnalités

- Authentification (Login / Register)
- Redirection intelligente selon le rôle (Admin / Utilisateur)
- Liste des quizzes disponibles
- Passage de quiz en séquence
- Timer par quiz
- Calcul et affichage des scores
- Corrections détaillées
- Leaderboard (classement)
- Historique des scores personnels
- Dashboard Admin
- Gestion des Quizzes (CRUD)
- Gestion des Questions (CRUD)
- Notifications Toast
- Design moderne avec Tailwind CSS
- Icônes professionnelles (Lucide)

---

## Technologies

- **Vue 3**
- **Vite 5.2.4**
- **Pinia** (Gestion d'état)
- **Vue Router 4**
- **Axios**
- **Tailwind CSS**
- **Lucide Vue Next** (Icônes)
- **Vue Toastification** (Notifications)

---

## Installation

### 1. Cloner le projet

```bash
git clone <url-du-repo>
cd quiz-frontend
```

### 2. Installer les dépendances

```bash
npm install
```

### 3. Configurer l'API

Crée un fichier `.env` à la racine :

```env
VITE_API_URL=http://127.0.0.1:8000/api/v1
```

### 4. Lancer le serveur de développement

```bash
npm run dev
```

L’application sera accessible sur : `http://localhost:5173`

---

## Structure du Projet

```
src/
├── assets/
├── components/
│   └── Layout/
│       └── AppLayout.vue
├── pages/
│   ├── Admin/
│   │   ├── Dashboard.vue
│   │   ├── Quiz/
│   │   │   └── Index.vue
│   │   └── Question/
│   │       └── Index.vue
│   ├── Quiz/
│   │   ├── Start.vue
│   │   ├── TakeQuiz.vue
│   │   └── Result.vue
│   ├── Home.vue
│   ├── Login.vue
│   ├── Register.vue
│   ├── Leaderboard.vue
│   └── MyScores.vue
├── router/
│   └── index.js
├── services/
│   └── api.js
├── stores/
│   └── auth.js
├── App.vue
└── main.js
```

---

## Authentification

- Le token est stocké dans `localStorage`
- Le store Pinia (`auth.js`) gère l’état de l’utilisateur
- Redirection automatique :
  - **Admin** → `/admin`
  - **Utilisateur** → `/`

---

## Protection des Routes

Les routes sont protégées via `meta` dans `router/index.js` :

```js
meta: { requiresAuth: true }
meta: { requiresAdmin: true }
meta: { requiresGuest: true }
```

---

## Design

- **Tailwind CSS** pour le styling
- **Lucide Icons** pour les icônes
- Design responsive et moderne
- Animations CSS (modals, transitions)

---

## Communication avec le Backend

Toutes les requêtes passent par Axios (`src/services/api.js`) avec :

- Base URL configurable via `.env`
- Intercepteur pour ajouter automatiquement le token Bearer
- Gestion des erreurs globales

---

## Scripts Disponibles

```bash
npm run dev      # Lancer en mode développement
npm run build    # Builder pour la production
npm run preview  # Prévisualiser le build
```

---

## Comptes de Test

| Rôle       | Email              | Mot de passe |
|------------|--------------------|--------------|
| Admin      | admin@quiz.com     | password     |
| Utilisateur| (créer via Register) | -          |

---

## Licence

Ce projet est sous licence **MIT**.  
Voir le fichier [LICENSE](LICENSE) pour plus de détails.