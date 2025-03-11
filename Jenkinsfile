pipeline {
    agent any 

    environment {   
        GIT_REPO = "https://github.com/kvn92/FoodAlert.git"
        GIT_BRANCH = "Test"
        DEPLOY_DIR = "web013"
    }

    stages {
        stage('Cloner le dépôt') {
            steps {
                sh "rm -rf ${DEPLOY_DIR}" // Nettoyage du précédent build 
                sh "git clone -b ${GIT_BRANCH} ${GIT_REPO} ${DEPLOY_DIR}"
            }
        }

        stage('Installation des dépendances') {
            steps {
                dir("${DEPLOY_DIR}") {
                    sh 'composer install --no-dev --optimize-autoloader'
                }
            }
        }

        stage('Configuration de l\'environnement') {
            steps {
                script {
                    def envLocal = """
                    APP_ENV=prod
                    APP_DEBUG=1
                    DATABASE_URL="mysql://root:root@127.0.0.1:8889/food_alert?serverVersion=8.0.32&charset=utf8mb4"
                    """.stripIndent()

                    writeFile file:"${DEPLOY_DIR}/.env.local", text:envLocal
                }
            }
        }

        stage('Migration de la base de données') {
            steps {
                dir("${DEPLOY_DIR}") {
                    sh 'php bin/console doctrine:database:create --if-not-exists --env=prod'
                    sh 'php bin/console doctrine:migrations:migrate --no-interaction --env=prod'
                }
            }
        }

        stage('Déploiement') {
            steps {
                sh "mkdir -p /var/www/html/${DEPLOY_DIR}"
                sh "rm -rf /var/www/html/${DEPLOY_DIR}/*"
                sh "cp -rT ${DEPLOY_DIR} /var/www/html/${DEPLOY_DIR}"
                sh "chmod -R 775 /var/www/html/${DEPLOY_DIR}/var"
                sh "chown -R www-data:www-data /var/www/html/${DEPLOY_DIR}"
            }
        }
    }

    post {
        success {
            echo '✅ Déploiement réussi !'
        }
        failure {
            echo '❌ Échec du déploiement. Vérifie les logs Jenkins.'
        }
    }
}
