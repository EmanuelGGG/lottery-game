# Guía de Herramientas DevOps - Lottery Game

## Tabla de Contenidos
1. [PHP Lint](#1-php-lint)
2. [Tests - test_jugar.php](#2-tests---test_jugarphp)
3. [Docker](#3-docker)

---

## 1. PHP Lint

### ¿Qué es PHP Lint?

**PHP Lint** (abreviatura de *PHP Linter*) es una herramienta **nativa de PHP** que verifica la **sintaxis** de los archivos `.php` sin necesidad de ejecutarlos. Su comando es:

```bash
php -l nombre_archivo.php
```

### ¿Para qué sirve?

| Función | Descripción |
|---|---|
| Detectar errores de sintaxis | Puntos y comas faltantes, llaves desbalanceadas, paréntesis sin cerrar |
| Validar antes de desplegar | Evita que código roto llegue a producción |
| Integración Continua (CI) | Se ejecuta automáticamente en cada push/PR a GitHub |
| No ejecuta el código | Solo analiza la estructura, no corre la lógica |

### ¿Cómo funciona?

PHP Lint lee el archivo PHP y lo **parsea** (analiza su estructura gramatical). Si encuentra un error de sintaxis, lo reporta. Si todo está bien, confirma que no hay errores.

**Ejemplo de salida exitosa:**
```
No syntax errors detected in controller.php
```

**Ejemplo de salida con error:**
```
PHP Parse error: syntax error, unexpected 'echo' (T_ECHO) in model.php on line 10
Errors parsing model.php
```

### ¿Cómo probarlo localmente?

#### Opción A: Verificar un solo archivo
```bash
php -l controlador/controller.php
php -l modelo/model.php
php -l index.php
php -l view.php
php -l tests/test_jugar.php
```

#### Opción B: Verificar todos los archivos PHP del proyecto
```bash
# En Windows PowerShell:
Get-ChildItem -Recurse -Filter "*.php" | ForEach-Object { php -l $_.FullName }

# En Linux/Mac:
find . -name "*.php" -exec php -l {} \;
```

#### Opción C: Verificar automáticamente en GitHub (CI Pipeline)

El archivo `.github/workflows/php-lint.yml` configura un **pipeline automático** que se ejecuta cada vez que haces push o creas un Pull Request:

```yaml
jobs:
  lint:
    name: PHP Lint
    runs-on: ubuntu-latest
    steps:
    - uses: actions/checkout@v4
    - uses: shivammathur/setup-php@v2
      with: php-version: '8.2'
    - run: find . -name "*.php" -exec php -l {} \;
```

**Para verlo en acción:**
1. Haz push a tu repositorio: `git push origin develop`
2. Ve a GitHub → tu repositorio → pestaña **Actions**
3. Verás el workflow ejecutándose con un ✅ verde (éxito) o ❌ rojo (error)

### ¿Qué errores detecta PHP Lint?

| Error | Ejemplo |
|---|---|
| Punto y coma faltante | `echo "hola"` (falta `;`) |
| Llave sin cerrar | `if (true) { echo "x";` |
| Variable mal escrita | `echo $nomre;` (sintaxis válida pero typo) |
| Función no cerrada | `function foo() {` sin `}` |
| Clase mal definida | `class Foo {` sin `}` |

### ¿Qué NO detecta PHP Lint?

- Errores lógicos (la sintaxis es correcta pero el resultado es incorrecto)
- Variables no definidas
- Errores de conexión a base de datos
- Problemas de rendimiento

Para esos casos necesitas **tests** (ver sección 2).

---

## 2. Tests - test_jugar.php

### ¿Qué es un Test?

Un **test** (prueba automatizada) es un script que verifica que tu código funcione correctamente. En lugar de probar manualmente ("hago clic, veo si funciona"), el test ejecuta tu código automáticamente y compara el resultado con lo esperado.

### ¿Qué prueba test_jugar.php?

El archivo `tests/test_jugar.php` contiene **6 pruebas** que verifican la lógica del método `jugar()` del controlador:

| # | Prueba | Qué verifica | Resultado esperado |
|---|---|---|---|
| 1 | **Tirada perdedora** | Que reste 10 puntos cuando los símbolos no coinciden | Puntaje: 2000 → 1990 |
| 2 | **Tirada ganadora** | Que sume 200 puntos cuando los 3 símbolos son iguales | Puntaje: 2000 → 2200 |
| 3 | **Game Over** | Que el puntaje llegue a 0 y no a negativos | Puntaje: 5 → 0 |
| 4 | **Puntaje inicial** | Que el controller se instancie con sesión vacía | Se crea sin errores |
| 5 | **Múltiples tiradas** | Que 10 tiradas consecutivas se procesen correctamente | 10 resultados válidos |
| 6 | **Guardar/Reset** | Que `guardar()` reinicie todo a valores iniciales | Puntaje: 1500 → 2000 |

### ¿Cómo funciona internamente?

```
┌─────────────────────────────────────────────────┐
│              test_jugar.php                      │
│                                                  │
│  ┌──────────────┐    ┌──────────────────────┐   │
│  │  MockModel   │    │   TestController     │   │
│  │  (simula BD) │◄───│   (lógica del juego) │   │
│  └──────────────┘    └──────────────────────┘   │
│         │                       │                │
│         ▼                       ▼                │
│   Siempre retorna        Genera números          │
│   true (sin MySQL)       aleatorios y calcula    │
│                          puntaje                 │
└─────────────────────────────────────────────────┘
```

**MockModel**: Es un "simulacro" del modelo real de base de datos. En lugar de conectarse a MySQL (que podría no estar disponible), siempre retorna `true`. Esto hace que las pruebas sean:
- **Rápidas**: Sin tiempo de conexión a BD
- **Portables**: Funcionan en cualquier máquina sin MySQL
- **Aisladas**: Solo prueban la lógica del juego, no la BD

### ¿Cómo ejecutar las pruebas?

#### Desde la terminal:
```bash
# Navega al directorio del proyecto
cd "C:\Xampp pro\htdocs\Desarollo2\lottery-game"

# Ejecuta las pruebas
php tests/test_jugar.php
```

#### Salida esperada (todo correcto):
```
========================================
  EJECUTANDO PRUEBAS: método jugar()
========================================

Estado Inicial:
  Puntaje: 2000
----------------------------------------

PRUEBA 1: Tirada perdedora (resta 10 puntos)
  PASSED - Puntaje: 2000 -> 1990 (-10)
----------------------------------------

PRUEBA 2: Tirada ganadora (suma 200 puntos)
  PASSED - Puntaje: 2000 -> 2200 (+200)
----------------------------------------

PRUEBA 3: Game Over (puntaje llega a 0)
  PASSED - Puntaje: 5 -> 0 (Game Over)
----------------------------------------

PRUEBA 4: Puntaje inicial correcto (2000)
  PASSED - Controller se instancia correctamente con sesión vacía
----------------------------------------

PRUEBA 5: Múltiples tiradas aleatorias
  PASSED - 10 tiradas ejecutadas: 1 victorias, 9 derrotas
  Puntaje final: 2110
----------------------------------------

PRUEBA 6: Método guardar() reinicia puntaje a 2000
  PASSED - Puntaje reiniciado a 2000, resultado y numeros reseteados
----------------------------------------

========================================
  RESULTADOS: 6 pasaron, 0 fallaron
========================================
```

#### Si alguna prueba falla:
```
========================================
  RESULTADOS: 5 pasaron, 1 fallaron
========================================
```
El script retorna `exit code 1`, lo que hace fallar el pipeline de GitHub Actions.

### ¿Cómo se ejecutan en GitHub Actions?

El workflow CI ejecuta las pruebas automáticamente en cada push:

```yaml
  test:
    name: PHP Tests
    runs-on: ubuntu-latest
    needs: lint                    # Solo corre si el lint pasó
    steps:
    - uses: actions/checkout@v4
    - uses: shivammathur/setup-php@v2
      with: php-version: '8.2'
    - run: cd tests && php test_jugar.php
```

**Para ver los resultados:**
1. Push a GitHub: `git push origin develop`
2. Ve a tu repositorio → **Actions** → clic en el workflow
3. Expande el job **PHP Tests** para ver cada prueba

---

## 3. Docker

### ¿Qué es Docker?

**Docker** es una plataforma de **contenerización** que empaqueta una aplicación con **todas sus dependencias** (servidor web, base de datos, librerías, configuración) en un **contenedor** aislado y portable.

### ¿Por qué usar Docker?

| Problema sin Docker | Solución con Docker |
|---|---|
| "En mi máquina funciona" | Funciona idéntico en cualquier máquina |
| Instalar XAMPP, MySQL, PHP manualmente | Todo viene pre-configurado en la imagen |
| Diferentes versiones de PHP entre devs | Todos usan la misma versión (PHP 8.2) |
| Configurar base de datos manualmente | Se crea e importa automáticamente |

### Arquitectura del proyecto

```
┌─────────────────────────────────────────────────────┐
│                 Tu Computadora                       │
│                                                      │
│  ┌───────────────────────────────────────────────┐  │
│  │           Docker Network (interna)             │  │
│  │                                                │  │
│  │  ┌──────────────────┐    ┌─────────────────┐  │  │
│  │  │  Contenedor APP  │    │  Contenedor DB  │  │  │
│  │  │  php:8.2-apache  │◄──►│  mariadb:10.4   │  │  │
│  │  │  Puerto: 80      │    │  Puerto: 3306   │  │  │
│  │  │  Vol: ./→/var/www│    │  Vol: db_data   │  │  │
│  │  └────────┬─────────┘    └────────┬────────┘  │  │
│  │           │                       │            │  │
│  └───────────┼───────────────────────┼────────────┘  │
│              │                       │               │
│              ▼                       ▼               │
│     localhost:8080            Datos persistentes     │
│     (abres en navegador)      (sobreviven reinicios) │
└─────────────────────────────────────────────────────┘
```

### Los 2 servicios configurados

#### Servicio `app` (Apache + PHP 8.2)
| Propiedad | Valor | Qué hace |
|---|---|---|
| `image` | `php:8.2-apache` | Imagen oficial con Apache y PHP 8.2 preinstalados |
| `ports` | `8080:80` | Mapea puerto 8080 de tu PC al puerto 80 del contenedor |
| `volumes` | `./:/var/www/html/` | Monta tus archivos locales en el servidor (cambios en vivo) |
| `environment` | `DB_HOST=db` etc. | Variables que model.php lee para conectarse a la BD |
| `depends_on` | `db` (healthy) | Espera a que la BD esté lista antes de iniciar |

#### Servicio `db` (MariaDB 10.4)
| Propiedad | Valor | Qué hace |
|---|---|---|
| `image` | `mariadb:10.4` | Base de datos compatible con MySQL |
| `MYSQL_ROOT_PASSWORD` | `root` | Contraseña del usuario root |
| `MYSQL_DATABASE` | `juego` | Crea la base de datos automáticamente |
| `volumes` (SQL) | `juego.sql:/docker-entrypoint-initdb.d/` | Importa el schema al primer inicio |
| `volumes` (data) | `db_data:/var/lib/mysql` | Persiste los datos entre reinicios |
| `healthcheck` | `mysqladmin ping` | Verifica que la BD esté respondiendo |

### ¿Cómo usar Docker?

#### Requisitos previos
1. Instalar **Docker Desktop**: https://www.docker.com/products/docker-desktop/
2. Verificar que Docker está corriendo (icono en la barra de tareas)

#### Comandos esenciales

```bash
# 1. INICIAR los contenedores (primera vez descarga las imágenes)
docker-compose up -d

# 2. VER el estado de los contenedores
docker-compose ps

# 3. VER los logs en tiempo real
docker-compose logs -f

# 4. VER logs de un servicio específico
docker-compose logs -f app
docker-compose logs -f db

# 5. ACCEDER al juego
# Abrir navegador en: http://localhost:8080

# 6. DETENER los contenedores
docker-compose down

# 7. DETENER y eliminar volúmenes (borra datos de la BD)
docker-compose down -v

# 8. REINICIAR un servicio
docker-compose restart app

# 9. EJECUTAR un comando dentro del contenedor
docker-compose exec app php -l /var/www/html/index.php
docker-compose exec db mysql -u root -proot juego
```

#### Flujo típico de trabajo

```bash
# Paso 1: Iniciar todo
docker-compose up -d

# Paso 2: Verificar que está corriendo
docker-compose ps
# Deberías ver ambos contenedores con estado "Up" y "healthy"

# Paso 3: Abrir el juego en el navegador
# http://localhost:8080

# Paso 4: Hacer cambios en el código (edita archivos normalmente)
# Los cambios se reflejan INSTANTÁNEAMENTE (hot reload)

# Paso 5: Cuando termines, detener
docker-compose down
```

### ¿Qué pasa en el primer inicio?

```
docker-compose up -d
       │
       ▼
┌─────────────────────────────────────┐
│ 1. Descarga las imágenes            │
│    - php:8.2-apache (~150MB)        │
│    - mariadb:10.4 (~100MB)          │
└──────────────┬──────────────────────┘
               ▼
┌─────────────────────────────────────┐
│ 2. Inicia el contenedor db          │
│    - Crea la base de datos 'juego'  │
│    - Importa juego.sql              │
│    - Ejecuta healthcheck            │
└──────────────┬──────────────────────┘
               ▼
┌─────────────────────────────────────┐
│ 3. Cuando db está "healthy"         │
│    inicia el contenedor app         │
│    - Apache escucha en puerto 80    │
│    - PHP lee variables de entorno   │
└──────────────┬──────────────────────┘
               ▼
┌─────────────────────────────────────┐
│ 4. ¡Listo! Accede a                 │
│    http://localhost:8080            │
└─────────────────────────────────────┘
```

### ¿Cómo se conecta PHP a la base de datos en Docker?

En `modelo/model.php`, las credenciales se leen de variables de entorno:

```php
$host = getenv('DB_HOST') ?: 'localhost';  // En Docker: 'db'
$user = getenv('DB_USER') ?: 'root';       // En Docker: 'root'
$pass = getenv('DB_PASS') ?: '';           // En Docker: 'root'
$name = getenv('DB_NAME') ?: 'juego';      // En Docker: 'juego'
```

**En Docker**, `DB_HOST=db` usa el **nombre del servicio** como hostname. Docker resuelve automáticamente `db` a la IP interna del contenedor de MariaDB.

**En XAMPP local**, `DB_HOST=localhost` porque MySQL corre en tu máquina directamente.

### Diferencia entre Docker y XAMPP

| Aspecto | XAMPP | Docker |
|---|---|---|
| Instalación | Instalar XAMPP completo | Instalar Docker Desktop |
| PHP | Versión fija de XAMPP | Elegible (8.2 en este caso) |
| MySQL | Se configura manualmente | Se crea automáticamente |
| Aislamiento | Comparte con tu sistema | Aislado en contenedores |
| Portabilidad | Solo en tu máquina | Funciona en cualquier PC con Docker |
| Cambios en código | Directo en htdocs | Directo (volumen montado) |
| Limpieza | Desinstalar XAMPP | `docker-compose down -v` |

---

## Resumen Rápido

| Herramienta | Comando | Qué hace |
|---|---|---|
| **PHP Lint** | `php -l archivo.php` | Verifica sintaxis sin ejecutar |
| **Tests** | `php tests/test_jugar.php` | Ejecuta 6 pruebas de la lógica |
| **Docker** | `docker-compose up -d` | Inicia Apache + MariaDB |
| **CI GitHub** | `git push` | Ejecuta lint + tests automáticamente |
