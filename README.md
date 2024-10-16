# TEAM Todolist Project

Bienvenue dans ce projet d'entrainement : une API todolist en PHP 8.2 avec Symfony 7.1 

## Prérequis

Avant de commencer, assurez-vous d'avoir les éléments suivants installés sur votre machine:
- [Docker](https://www.docker.com/get-started)
- [Docker Compose](https://docs.docker.com/compose/install/)

## Installation
Suivez les étapes ci-dessous pour installer et lancer le projet:

1. **Arrêtez votre serveur MySQL local:**
    ```bash
    sudo systemctl stop mysql
    ```
    > **Note:** Le conteneur Docker utilise le même port par défaut que MySQL (3306). Assurez-vous que votre serveur MySQL local est arrêté pour éviter les conflits de port.

2. **Construisez et lancez les conteneurs Docker:**
    ```bash
    docker-compose up --build
    ```

3. **Accédez à l'application:**
    Ouvrez votre navigateur et allez à l'adresse [http://localhost:8080](http://localhost:8080).
Suivez les étapes ci-dessous pour installer et lancer le projet

## Commandes 

- **Reconstruction des conteneurs:**
        Si vous apportez des modifications au Dockerfile ou au fichier `docker-compose.yml`, vous devrez reconstruire les conteneurs
    ```bash
    docker-compose up --build
    ```

- **Arrêter les conteneurs:**
        Pour arrêter les conteneurs en cours d'exécution
    ```bash
    docker-compose down
    ```

- **Logs des conteneurs:**
        Pour voir les logs des conteneurs
    ```bash
    docker-compose logs
    ```
- **Réinitialiser totalement les conteneurs:**
        Pour supprimer tous les conteneurs, réseaux, volumes et images créés par `docker-compose`
    ```bash
    docker-compose down --volumes --rmi all
    ```


## Routes disponibles

- **GET /tasks**

*toutes les tâches sans ordres particuliers*
*paramètres disponibles : " page, limit "*
- **GET /tasks/{id}**
- **GET /tasks/search**

*paramètres disponibles : " isComplete, user, dueDate, keywords, page, limit "*

- **GET /tasks/due-date** :

*paramètres disponibles : " dueDate, page, limit "*
- **POST /tasks**
- **PUT /tasks/{id}**
- **DELETE /tasks/{id}**