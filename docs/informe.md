# Informe Técnico de Desarrollo - Taller Juego de Lotería v1.1

**Objetivo:** Aplicar conocimientos de Git, Docker y DevOps realizando ajustes funcionales y de diseño en la aplicación de PHP "Juego de Lotería".

**Repositorio:** https://github.com/EmanuelGGG/lottery-game

---

## 1. Mejora de la Interfaz Web (UI/UX)

Se rediseñó completamente la interfaz del juego aplicando mejores prácticas de UI/UX:

### Características implementadas:
- **Máquina tragamonedas animada**: Los rodillos giran verticalmente de arriba hacia abajo simulando una máquina de casino real, con efecto de desaceleración progresiva (cada rodillo para en cascada).
- **Marco de máquina con luces LED**: Luces parpadeantes animadas con `@keyframes blinkLights` que alternan colores dorado y naranja.
- **Línea de ganancia**: Línea horizontal dorada que cruza los rodillos indicando la línea de premio.
- **Efectos de feedback visual**:
  - `winPulse`: Pulso verde brillante al ganar (+200 puntos)
  - `loseShake`: Vibración roja al perder (-10 puntos)
  - `gameOverFlash`: Parpadeo dramático en Game Over
  - `scorePop`: Animación de entrada del puntaje
- **Tabla de premios**: Panel informativo con las combinaciones y sus valores.
- **Header de casino**: Título con fuente Orbitron y gradiente dorado.
- **Accesibilidad**: `aria-label`, `aria-live`, `focus-visible`, `prefers-reduced-motion`.
- **Responsive**: Diseño adaptable con `clamp()` y media queries para móviles.
- **Tecla Espacio**: Atajo de teclado para girar sin mouse.

### Tecnologías CSS utilizadas:
- Glassmorphism (backdrop-filter: blur)
- Gradientes dinámicos
- Animaciones `@keyframes` personalizadas
- Pseudo-elementos `::before` para efectos hover
- Variables de diseño con `clamp()`

*(Inserta aquí un pantallazo de la interfaz con la slot machine animada)*

---

## 2. Modificación Inicial de Puntaje a 2000

Se verificó y mantuvo el puntaje inicial en **2000 puntos**:

| Archivo | Línea | Cambio |
|---|---|---|
| `controlador/controller.php` | Constructor | `$_SESSION['puntaje'] : 2000` |
| `controlador/controller.php` | `guardar()` | Reset a `2000` |
| `view.php` | Vista | Valor por defecto `2000` |

**Lógica de puntuación:**
- 3 iguales: **+200 puntos**
- Sin coincidencia: **-10 puntos**
- Puntaje < 0: **Game Over** (reset a 0)

---

## 3. Control de Versiones en GitHub

**Repositorio:** https://github.com/EmanuelGGG/lottery-game

### Comandos ejecutados:
```bash
git add .
git commit -m "feat: UI casino animada, Docker con MariaDB, CI/CD mejorado, tests"
git push origin develop
```

### Estructura de ramas:
- `main` / `master`: Rama de producción
- `develop`: Rama de desarrollo (activa)

*(Inserta pantallazo del repositorio en GitHub con los commits)*

---

## 4. PHP Lint - Integración Continua (CI)

### ¿Qué es PHP Lint?

**PHP Lint** (`php -l`) es una herramienta nativa de PHP que verifica la sintaxis de archivos PHP sin ejecutarlos. Analiza el código en busca de errores como:
- Puntos y comas faltantes
- Llaves desbalanceadas
- Nombres de funciones inválidos
- Errores de parseo

Es la primera línea de defensa en un pipeline de **Integración Continua (CI)**, previniendo que código con errores de sintaxis llegue a producción.

### Ejecución local:
```bash
find . -name "*.php" -exec php -l {} \;
```

### Resultados:
```
No syntax errors detected in index.php
No syntax errors detected in view.php
No syntax errors detected in controlador/controller.php
No syntax errors detected in modelo/model.php
No syntax errors detected in tests/test_jugar.php
```

### Pipeline CI automatizado (GitHub Actions):
El archivo `.github/workflows/php-lint.yml` ejecuta automáticamente en cada push/PR:
1. **Job `lint`**: PHP Lint en todos los archivos `.php`
2. **Job `test`**: Ejecución de pruebas unitarias (depende de lint)

*(Inserta pantallazo del pipeline de GitHub Actions en verde)*

---

## 5. Pruebas Funcionales: método jugar()

Se ejecutaron **6 pruebas automatizadas** sobre el método `jugar()`:

| # | Prueba | Descripción | Resultado |
|---|---|---|---|
| 1 | Tirada perdedora | Verifica que reste 10 puntos | PASSED |
| 2 | Tirada ganadora | Verifica que sume 200 puntos | PASSED |
| 3 | Game Over | Verifica que puntaje llegue a 0 | PASSED |
| 4 | Puntaje inicial | Verifica valor 2000 por defecto | PASSED |
| 5 | Múltiples tiradas | 10 tiradas aleatorias consecutivas | PASSED |
| 6 | Guardar/Reset | Verifica reinicio a 2000 | PASSED |

**Resultado: 6/6 pasaron, 0 fallaron**

El test usa un `MockModel` para evitar dependencias de base de datos, haciendo las pruebas rápidas y portables.

---

## 6. Empaquetado del Código (Versión 1.1)

Se empaquetaron todos los archivos del proyecto en `entrega_v1.1.zip`:

### Contenido del paquete:
```
lottery-game/
├── index.php                    (Router principal)
├── view.php                     (Vista HTML con slot machine)
├── controlador/controller.php   (Lógica del juego)
├── modelo/model.php             (Acceso a base de datos)
├── public/
│   ├── style.css                (Estilos CSS animados)
│   └── imagenes/
│       ├── 1.jpg                (Símbolo de slot)
│       ├── 2.jpg                (Símbolo de slot)
│       └── 3.jpg                (Símbolo de slot)
├── database/juego.sql           (Schema de base de datos)
├── docker-compose.yml           (Configuración Docker)
├── tests/test_jugar.php         (Pruebas automatizadas)
├── docs/informe.md              (Este informe)
└── .github/workflows/php-lint.yml (CI Pipeline)
```

---

## 7. Despliegue en Servidor Apache con Docker

### ¿Qué es Docker?

**Docker** es una plataforma de contenerización que empaqueta una aplicación con todas sus dependencias (servidor web, base de datos, librerías) en un contenedor aislado y portable. Esto garantiza que la aplicación funcione idénticamente en cualquier entorno ("funciona en mi máquina" deja de ser un problema).

### Arquitectura del docker-compose.yml:

```
┌─────────────────────────────────────────┐
│           Docker Network                │
│                                         │
│  ┌──────────────┐    ┌───────────────┐ │
│  │  PHP 8.2     │    │   MariaDB     │ │
│  │  Apache :80  │◄──►│   :3306       │ │
│  │  Port 8080   │    │   Volume      │ │
│  └──────────────┘    └───────────────┘ │
│       ▲                    ▲           │
│       │                    │           │
│   localhost:8080      db_data          │
└─────────────────────────────────────────┘
```

### Servicios configurados:

| Servicio | Imagen | Puerto | Función |
|---|---|---|---|
| `app` | `php:8.2-apache` | 8080:80 | Servidor web Apache + PHP |
| `db` | `mariadb:10.4` | interna | Base de datos MySQL |

### Variables de entorno:
- `DB_HOST=db` (hostname del servicio MariaDB)
- `DB_USER=root`
- `DB_PASS=root`
- `DB_NAME=juego`

### Cómo desplegar:

```bash
# 1. Iniciar los contenedores
docker-compose up -d

# 2. Verificar que estén corriendo
docker-compose ps

# 3. Acceder al juego
# Abrir navegador en: http://localhost:8080

# 4. Ver logs
docker-compose logs -f

# 5. Detener
docker-compose down

# 6. Detener y eliminar volúmenes
docker-compose down -v
```

### Características del Docker:
- **Volumen persistente**: `db_data` mantiene la base de datos entre reinicios
- **Healthcheck**: MariaDB verifica su estado antes de que Apache inicie
- **Auto-init**: El archivo `juego.sql` se importa automáticamente al primer inicio
- **Hot reload**: Los cambios en archivos PHP se reflejan instantáneamente (volumen montado)

### Despliegue en XAMPP (alternativa sin Docker):

1. Copiar carpeta `lottery-game` a `C:\xampp\htdocs\`
2. Abrir phpMyAdmin: `http://localhost/phpmyadmin`
3. Crear base de datos `juego`
4. Importar `database/juego.sql`
5. Acceder: `http://localhost/lottery-game`

---

## Resumen de Cambios v1.1

| Componente | Antes | Después |
|---|---|---|
| UI | Estática, tarjetas fijas | Slot machine animada con rodillos giratorios |
| CSS | Básico | Glassmorphism, animaciones, responsive |
| session_start() | En controller.php | En index.php (lugar correcto) |
| DB credentials | Hardcodeadas | Variables de entorno |
| Docker | Solo Apache | Apache + MariaDB con healthcheck |
| CI | Solo lint | Lint + Tests automatizados |
| Tests | 3 tiradas básicas | 6 pruebas con assertions |
| Archivos | Duplicado en `subir/` | Limpio, sin duplicados |

---

**Conclusión:**
El presente taller integró satisfactoriamente un proceso completo de mejora de software aplicando versionamiento con Git, pruebas estáticas (PHP Lint), pruebas dinámicas (test_jugar.php), contenerización con Docker (Apache + MariaDB), y despliegue automatizado, afianzando un entorno pre-productivo completo de DevOps con mejores prácticas de UI/UX.
