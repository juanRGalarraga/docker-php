# Docker + Docker compose ingrado al framework

El archivo **docker-compose.yml** contiene las configuraciones para integrar los contenedores 
y lograr que se comuniquen entre sí.

**.dockerignore** contiene los directorios y archivos que se ignoran al construir los contenedores. Funciona igual que **.gitignore**.

Cada proyecto debe tener su propio **Dockerfile** funcionando en un mismo contexto. Dicho contexto se especifica en el **docker-compose.yml**. Esto es importante para la orquestación del multi-container.


## Shell-Helpers
###### Shell scripts para facilitar debug

**build**: ejecuta tres comandos de Docker compose. Hecho con el propósito de automatizar la construcción y ejecución de los contenedores.

**cbash**: alias del comando *docker exec -it [container] bash*. Se le pasa el nombre o ID del container para ejecutar bash adentro del mismo.

**ps**: alias del comando *docker ps* con formato personalizado
