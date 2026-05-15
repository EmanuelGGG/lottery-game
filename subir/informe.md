# Informe Técnico de Desarrollo - Taller Juego de Lotería

**Objetivo:** Aplicar conocimientos de Git, Docker y DevOps realizando ajustes funcionales y de diseño en la aplicación de PHP "Juego de Lotería".

---

## 1. Mejora de la Interfaz Web
Se refactorizó el archivo `view.php` introduciendo etiquetas HTML5 semánticas y la fuente web _Google Fonts_ (Poppins) para una tipografía moderna. 
Se sobrescribió el archivo `style.css` aplicando técnicas avanzadas de CSS como *Glassmorphism* (fondos difuminados), gradientes de fondo dinámicos, sombras responsivas y pseudo-clases `hover` que agregan animaciones de rotación y zoom (`scale`) a las imágenes. El resultado es un panel visual estilo "Casino" más interactivo, manteniendo las imágenes originales.

*(Inserta aquí un pantallazo de tu navegador web mostrando el diseño actual)*

## 2. Modificación Inicial de Puntaje a 2000
Se modificó la lógica de inicio del sistema ajustando el archivo `controller.php`:
- Línea 11: Se cambió el `1000` de inicialización por defecto a `2000`.
- Línea 39: Dentro del método `guardar()`, se resetea la sesión en `2000` en lugar del valor original. 
También se ajustó la vista en `view.php` (Línea 10) para reflejar la modificación directamente.

## 3. Control de Versiones en GitHub
Se ejecutaron los siguientes comandos de Git para llevar el seguimiento de los cambios:
- `git init` (Inicializar el repositorio base).
- `git add .` (Anexar archivos).
- `git commit -m "Solución completa del Taller: UI, puntos y docker"`
- *Action*: Posteriormente se debe ejecutar el `git push -u origin master` hacia el repositorio público.

*(Inserta aquí el enlace de tu repositorio de GitHub)*
*(Inserta pantallazo de la terminal o de GitHub comprobando los commits)*

## 4. Pruebas de Sintaxis Continua (PHP Lint)
**¿Qué es el PHP Lint?**
Es una herramienta nativa de PHP de línea de comandos que permite validar la sintaxis de un archivo PHP sin tener que ejecutarlo por completo en el servidor (`php -l nombre_archivo.php`). Actúa como una etapa de Integración Continua (CI) temprana previniendo que un código mal escrito (ej. falta de puntos y comas) rompa la aplicación en producción.

**Resultados de la Ejecución:**
Tras aplicar Lint en nuestro código con `php -l controller.php` y `php -l view.php`, la terminal arrojó: 
`No syntax errors detected in controller.php` - Validando que nuestros cambios son seguros antes de un deployment.

## 5. Pruebas Funcionales: método jugar()
Para el concepto de *Test*, se generó un archivo puro de pruebas llamado `test_jugar.php`. Este script levanta una sesión simulada en 2000 puntos, instancia la clase `Controller` y ejecuta el método `$controller->jugar()` en un bucle automatizado (3 intentos), imprimiendo los arreglos de imágenes que salen y si los puntos sumaron +200 o restaron -10 al resultado esperado.

## 6. Empaquetado del Código (Versión 1.1)
Utilizando utilidades de terminal, se procesaron los archivos fuente (Archivos PHP, CSS, .sql y carpeta de imágenes `/imagenes`) para comprimirlos en un empaquetado unitario llamado `entrega_v1.1.zip` listo para despacharse como artefacto o distribuible en el ciclo de vida del producto.

## 7. Despliegue en Servidor Apache con Docker
Alineándonos al formato DevOps del Taller, se creó el manifiesto `docker-compose.yml`. Este archivo detalla el despliegue automático del juego en un contenedor aislado con imagen `php:8.2-apache`. El motor Docker se encarga de montar los archivos del juego bajo la ruta `/var/www/html` en el puerto 8080.
De este modo, se eliminan las dependencias del servidor en las computadoras de otros desarrolladores ("en mi máquina sí funciona").

---
**Conclusión:** 
El presente taller integró satisfactoriamente un proceso de mejora de software aplicando versionamiento, pruebas estáticas, dinámicas y contenerización, afianzando un entorno pre-productivo completo de DevOps.
