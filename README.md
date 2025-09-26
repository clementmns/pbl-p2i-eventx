# EventX 🎉

EventX est une application web de gestion d’événements avec système de comptes utilisateurs, profils, inscriptions et wishlist.

## 🚀 Stack technique
- **API** en PHP 8.2 (MVC maison)
- **Front-end** en PHP avec **Twig** comme moteur de templates
- **Base de données** MySQL 8.0
- **Docker Compose** pour orchestrer les services

---

## 🛠️ Prérequis

Avant de démarrer, assurez-vous d’avoir installé localement :

- [PHP 8+](https://www.php.net/downloads.php)
- [Composer](https://getcomposer.org/download/)
- [Docker](https://docs.docker.com/get-docker/)
- [Docker Compose](https://docs.docker.com/compose/)
- [Git](https://git-scm.com/downloads)

---

## 📦 Installation

1. **Cloner le projet**
   ```bash
   git clone https://github.com/ton-compte/eventx.git
   cd eventx
   ```

2. **Installer les dépendances Composer (en local)**
    - Dans l’API :
      ```bash
      cd api
      composer install
      cd ..
      ```
    - Dans le Front (Twig et autres libs) :
      ```bash
      cd front-end
      composer install
      cd ..
      ```

3. **Configurer les variables d’environnement**  
   Créez un fichier `.env` à la racine avec par exemple :
   ```env
   DB_NAME=eventx
   DB_USER=eventx_user
   DB_PASS=eventx_pass
   JWT_SECRET=super_secret_key
   ```

4. **Lancer les conteneurs Docker**
   ```bash
   docker-compose up -d --build
   ```

---

## 🌍 Accès aux services

- **API** : [http://localhost:8000/api](http://localhost:8000/api)
- **Front-end (Twig)** : [http://localhost:8080](http://localhost:8080)
- **Base MySQL** : port `3306` (utiliser les identifiants `.env`)

---

## 🗄️ Base de données

Au premier démarrage, la BDD est automatiquement initialisée depuis `./api/sql`.  
Le schéma inclut :
- `users`, `roles`, `profiles`
- `events`, `registrations`, `wishlists`

---

## 🔧 Commandes utiles

- **Arrêter les conteneurs**
  ```bash
  docker-compose down
  ```
- **Rebuilder après modification**
  ```bash
  docker-compose up -d --build
  ```
- **Vider les volumes (⚠️ perte des données !)**
  ```bash
  docker-compose down -v
  ```

---