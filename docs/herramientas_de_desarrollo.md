═══════════════════════════════════════════════════════════
      GUÍA DE SOFTWARE Y HERRAMIENTAS DE DESARROLLO
      Todo lo que necesitas en tu máquina para desarrollar
═══════════════════════════════════════════════════════════

## 📋 ÍNDICE

1. Software Esencial (Instala Sí o Sí)
2. Software Opcional pero Muy Útil
3. Configuraciones Post-Instalación
4. Organización de Tu Entorno
5. Verificación de Instalación
6. Problemas Comunes y Soluciones
7. Checklist de Instalación
8. Recursos Adicionales

---

## 1. SOFTWARE ESENCIAL (INSTALA SÍ O SÍ)

### 1.1 XAMPP - Servidor Local

**¿Qué es?**
XAMPP es un paquete que instala Apache (servidor web), MySQL (base de datos), 
y PHP en tu computadora. Básicamente convierte tu PC en un servidor para que 
puedas desarrollar y probar todo localmente antes de subirlo a internet.

**¿Por qué XAMPP específicamente?**
Hay varias opciones como WAMP, Laragon, y MAMP, pero XAMPP es la más universal. 
Funciona exactamente igual en Windows, Mac y Linux. Cuando busques tutoriales 
o tengas problemas, vas a encontrar millones de soluciones para XAMPP porque 
es el más popular.

**Descargar:** https://www.apachefriends.org/

**Versión recomendada:**
La última versión estable que incluya PHP 8.1 o PHP 8.2. Al momento de escribir 
esto sería XAMPP 8.2.12 o superior. Evita versiones con PHP 7.x porque ya están 
desactualizadas.

**Instalación:**
La instalación es muy directa: siguiente, siguiente, siguiente. Cuando te 
pregunte qué componentes instalar, déjalo todo marcado:
- Apache (servidor web)
- MySQL (base de datos)
- PHP (lenguaje)
- phpMyAdmin (administrador de BD visual)
- FileZilla (opcional pero útil)

**Ruta de instalación:**
En Windows, se instala normalmente en `C:\xampp`. NO lo instales en "Archivos 
de Programa" o "Program Files" porque puedes tener problemas de permisos. 
Déjalo en `C:\xampp` directamente.

**Cómo usarlo:**
1. Abres el "XAMPP Control Panel" (se crea un acceso directo en el escritorio)
2. Le das "Start" a Apache (servidor web)
3. Le das "Start" a MySQL (base de datos)
4. Cuando ambos estén corriendo (fondo verde), tu servidor local está activo

**Dónde van tus proyectos:**
Todos tus proyectos web los guardas en `C:\xampp\htdocs\`. Por ejemplo:
- `C:\xampp\htdocs\tienda\` → accedes con `http://localhost/tienda/`
- `C:\xampp\htdocs\consultorio\` → accedes con `http://localhost/consultorio/`

**Verificar que funciona:**
Después de iniciar Apache, abre tu navegador y ve a `http://localhost`. 
Deberías ver la página de bienvenida de XAMPP con un diseño naranja.

**Acceder a phpMyAdmin:**
Ve a `http://localhost/phpmyadmin` en tu navegador. Ahí puedes crear bases 
de datos, tablas, ver datos, todo visualmente sin escribir SQL (aunque también 
puedes escribir SQL si quieres).

---

### 1.2 Visual Studio Code - Editor de Código

**¿Qué es?**
VS Code es el editor de código más popular del mundo actualmente. Es gratis, 
súper potente, tiene extensiones para absolutamente todo, y es lo que usa la 
mayoría de desarrolladores profesionales.

**¿Por qué VS Code y no otro editor?**
Podrías usar Sublime Text, Atom, Notepad++, o incluso PHPStorm (de pago). Pero 
VS Code es el estándar de la industria actual. Cuando le pidas ayuda a Claude 
con código, Claude asume que usas VS Code. Los tutoriales en YouTube usan VS Code. 
Tu futuro yo que colabore con otros desarrolladores usará VS Code. Además es 
gratis y open source.

**Descargar:** https://code.visualstudio.com/

**Instalación:**
La instalación es muy simple. Solo asegúrate de marcar estas opciones durante 
la instalación:
- "Add to PATH" (Agregar al PATH) - Esto te permite abrir VS Code desde la terminal
- "Add 'Open with Code' action to Windows Explorer file context menu" - Para abrir 
  carpetas con click derecho
- "Add 'Open with Code' action to Windows Explorer directory context menu"

**Primera vez que abres VS Code:**
Te va a mostrar una página de bienvenida. Puedes cerrarla. Familiarízate con 
la interfaz:
- Barra lateral izquierda: Explorador de archivos, búsqueda, control de versiones
- Área central: Donde editas tu código
- Barra inferior: Terminal integrada, problemas, output

---

### 1.2.1 Extensiones ESENCIALES para VS Code

Las extensiones hacen que VS Code sea realmente potente. Ve a la sección de 
extensiones (ícono de cuadraditos en la barra izquierda o presiona Ctrl+Shift+X) 
y busca e instala estas:

**1. PHP Intelephense** (por Ben Mewburn)
Esta extensión es IMPRESCINDIBLE si vas a programar en PHP. Te da:
- Autocompletado inteligente (empieza a escribir una función y te sugiere)
- Detección de errores en tiempo real
- Ir a definición (Ctrl+Click en una función y te lleva donde está definida)
- Documentación al pasar el mouse

**2. HTML CSS Support** (por ecmel)
Autocompletado para HTML y CSS. Te sugiere clases de CSS mientras escribes 
HTML, detecta errores de sintaxis, etc.

**3. JavaScript (ES6) code snippets** (por charalampos karypidis)
Atajos para escribir código JavaScript común más rápido. Por ejemplo, escribes 
"fori" y te genera un for loop completo.

**4. MySQL** (por Jun Han)
Te permite conectarte a tu base de datos MySQL directamente desde VS Code y 
hacer queries sin abrir phpMyAdmin. Muy útil.

**5. Live Server** (por Ritwick Dey)
Esto es oro. Click derecho en un archivo HTML → "Open with Live Server" y se 
abre en el navegador. Cada vez que guardes un cambio en tu código, el navegador 
se recarga automáticamente. Es mágico para desarrollo frontend.

**6. PHP Debug** (por Xdebug)
Para debuggear código PHP paso a paso. Es más avanzado, pero es bueno tenerlo 
instalado para cuando lo necesites.

**7. Auto Rename Tag** (por Jun Han)
Cuando cambias una etiqueta HTML de apertura `<div>`, automáticamente cambia 
la de cierre `</div>`. Parece simple pero te ahorra muchos errores tontos.

**8. Bracket Pair Colorizer 2** (por CoenraadS)
Colorea los paréntesis, llaves, y corchetes por pares. Te ayuda a ver dónde 
abre y cierra cada bloque de código. Super útil para evitar errores de llaves 
sin cerrar.

**9. indent-rainbow** (por oderwat)
Colorea la indentación de tu código para que sea más fácil ver la estructura. 
Hace el código más legible.

**10. GitLens** (opcional por ahora)
Si decides usar Git (lo cual recomiendo eventualmente), esta extensión te 
muestra quién cambió qué línea de código y cuándo. Es increíblemente útil.

**Cómo instalar extensiones:**
1. Ctrl+Shift+X para abrir el panel de extensiones
2. Buscar el nombre de la extensión
3. Click en "Install"
4. Algunas requieren reiniciar VS Code

---

### 1.2.2 Configuración Recomendada de VS Code

Ve a File → Preferences → Settings (o Ctrl+,) y configura lo siguiente:

**Auto Save:**
Busca "auto save" y ponlo en "afterDelay" con 1000ms. Así tu código se guarda 
automáticamente cada segundo y nunca pierdes trabajo.

**Format On Save:**
Busca "format on save" y actívalo. Tu código se formatea automáticamente 
(indentación correcta, espacios, etc.) cada vez que guardas. Mantiene tu 
código limpio sin esfuerzo.

**Font Size:**
Busca "font size" y ajústalo a tu gusto. 14 o 16 es cómodo para muchos. No 
uses menos de 12 porque cansa la vista.

**Word Wrap:**
Busca "word wrap" y actívalo (pon "on"). Así las líneas largas se rompen 
visualmente y no tienes que hacer scroll horizontal.

**Tab Size:**
Busca "tab size" y ponlo en 4. Esto es estándar para PHP.

**Files: Auto Save:**
Ya lo mencionamos pero es tan importante que lo repito. Busca "files.autoSave" 
y ponlo en "afterDelay".

**Tema recomendado:**
Ve a File → Preferences → Color Theme (o Ctrl+K Ctrl+T). Prueba "Dark+ (default 
dark)" que viene incluido o instala la extensión "One Dark Pro" que es muy 
popular. El tema oscuro es más cómodo para los ojos cuando programas muchas horas.

---

### 1.3 Navegador Principal: Google Chrome

**¿Por qué Chrome?**
Chrome tiene las mejores herramientas de desarrollo (DevTools) integradas. 
Vas a estar inspeccionando elementos HTML, viendo errores de JavaScript en 
la consola, monitoreando peticiones de red, debuggeando CSS, etc. Chrome 
DevTools es simplemente el mejor para esto.

**Ya lo tienes instalado probablemente**, pero si no:
**Descargar:** https://www.google.com/chrome/

**Aprender a usar DevTools:**
Esto es CRUCIAL. DevTools va a ser tu mejor amigo.

**Cómo abrir DevTools:**
- Presiona F12
- O click derecho en cualquier parte de una página → "Inspeccionar"
- O Ctrl+Shift+I

**Pestañas importantes de DevTools:**

**Elements (Elementos):**
Ves el HTML de la página. Puedes hacer click en cualquier elemento y ver su 
código. Puedes incluso editar el HTML en vivo para probar cosas (los cambios 
no se guardan, solo son temporales).

También ves el CSS aplicado a cada elemento. Super útil para debuggear por 
qué algo se ve como se ve.

**Console (Consola):**
Aquí ves errores de JavaScript. Si tu código JavaScript tiene un error, aparece 
aquí en rojo con el número de línea. También puedes escribir JavaScript 
directamente en la consola para probar cosas.

**Network (Red):**
Ves todas las peticiones que hace tu página: archivos CSS, JavaScript, imágenes, 
peticiones AJAX, etc. Cuánto tardó cada una, si hubo errores, qué datos se 
enviaron y recibieron.

Esto es SUPER útil cuando trabajas con AJAX. Puedes ver exactamente qué le 
estás enviando a tu PHP y qué te responde.

**Application (Aplicación):**
Ves cookies, sesiones, localStorage, sessionStorage. Útil para debuggear 
problemas de login o cuando guardas datos en el navegador.

**Sources (Fuentes):**
Para debuggear JavaScript paso a paso. Puedes poner breakpoints (pausas) en 
tu código y ver qué está pasando línea por línea.

**Extensión útil para Chrome:**
Instala "Pesticide for Chrome". Te dibuja bordes de colores alrededor de todos 
los elementos HTML. Super útil para entender el layout y encontrar problemas 
de CSS.

---

### 1.4 Navegador Secundario: Firefox Developer Edition

**¿Por qué?**
Tus clientes no todos usan Chrome. Algunos usan Firefox, otros Safari (en Mac), 
otros Edge. Debes probar que tu sistema funcione bien en varios navegadores.

Firefox Developer Edition tiene excelentes DevTools también, a veces incluso 
mejores que Chrome para ciertas cosas. Y es gratis.

**Descargar:** https://www.mozilla.org/es-ES/firefox/developer/

**No es obligatorio desde el día uno**, pero cuando tengas tu primer sistema 
funcionando, abrelo en Firefox para verificar que se vea y funcione bien.

---

### 1.5 Gestor de Base de Datos: HeidiSQL

**¿Qué es?**
phpMyAdmin está bien y lo vas a usar, pero HeidiSQL es una aplicación de 
escritorio que te da una interfaz mucho más cómoda y rápida para trabajar 
con bases de datos MySQL.

**¿Por qué usarlo?**
- Es más rápido que phpMyAdmin
- Interfaz más intuitiva y moderna
- Puedes tener múltiples conexiones abiertas al mismo tiempo
- Puedes conectarte a tu BD local (XAMPP) y a tu BD en producción (Hostinger) 
  simultáneamente y comparar
- Mejor editor de queries con autocompletado
- Exportar/importar datos más fácil

**Descargar:** https://www.heidisql.com/download.php

**Alternativas según tu sistema operativo:**
- **Windows:** HeidiSQL (recomendado)
- **Mac:** Sequel Pro (gratis) o TablePlus (freemium)
- **Multiplataforma:** DBeaver (gratis, funciona en todo, un poco más pesado)

**Cómo conectarte a tu BD local de XAMPP:**
1. Abres HeidiSQL
2. Click en "New" (Nueva sesión)
3. Configuración:
   - Network type: MySQL (TCP/IP)
   - Hostname / IP: localhost (o 127.0.0.1)
   - User: root
   - Password: (dejar vacío, XAMPP no tiene password por defecto)
   - Port: 3306
4. Click en "Open"
5. Listo, ves todas tus bases de datos del lado izquierdo

**Crear una base de datos:**
Click derecho en la lista de bases de datos → Create new → Database. Le pones 
nombre y listo.

**Crear tablas, insertar datos, etc.:**
Todo es visual y mucho más intuitivo que phpMyAdmin.

---

### 1.6 Cliente FTP: FileZilla

**¿Qué es?**
FileZilla es un programa para subir y descargar archivos a tu servidor por 
FTP/SFTP. Es la forma más rápida y cómoda de subir muchos archivos a Hostinger.

**¿Cuándo lo usas?**
El File Manager de Hostinger está bien para archivos individuales o cambios 
pequeños. Pero cuando tienes que subir tu proyecto completo (100+ archivos) 
o hacer updates frecuentes, FileZilla es mucho más eficiente.

**Descargar:** https://filezilla-project.org/

**IMPORTANTE:** Descarga "FileZilla Client", NO "FileZilla Server". El Client 
es para ti como desarrollador, el Server es otra cosa que no necesitas.

**ADVERTENCIA:** El instalador de FileZilla trae software adicional (bundleware). 
Durante la instalación lee con cuidado y desmarca cualquier cosa que no sea 
FileZilla mismo. O mejor aún, usa la alternativa WinSCP.

**Alternativa recomendada: WinSCP** (solo Windows)
Es open source, sin bloatware, y hace exactamente lo mismo que FileZilla.
Descargar: https://winscp.net/

**Cómo conectarte a Hostinger:**
1. Abres FileZilla (o WinSCP)
2. En la barra superior pones:
   - Host: ftp.tudominio.com (o la IP que te da Hostinger en hPanel)
   - Username: tu usuario de FTP (lo ves en hPanel → FTP Accounts)
   - Password: tu password de FTP
   - Port: 21 (para FTP) o 22 (para SFTP, más seguro)
3. Click en "Quickconnect"
4. Si es la primera vez, te pregunta si confías en el certificado. Di que sí.
5. Listo, conectado

**Interfaz de FileZilla:**
- **Lado izquierdo:** Tu computadora
- **Lado derecho:** El servidor (Hostinger)
- **Cómo subir archivos:** Arrastras de izquierda a derecha
- **Cómo descargar archivos:** Arrastras de derecha a izquierda

**Pro tip:**
Guarda la conexión para no tener que poner los datos cada vez. File → Site 
Manager → New Site → pones todos los datos → Connect.

---

### 1.7 Git y GitHub Desktop

**¿Qué es Git?**
Git es un sistema de control de versiones. Te permite guardar "snapshots" 
(fotos) de tu código a través del tiempo. Puedes volver a versiones anteriores, 
ver qué cambió, trabajar en equipo sin pisarte los cambios, etc.

**¿Por qué instalarlo ahora si dijiste que no lo usara al principio?**
Porque aunque no lo uses para deployment al principio, Git es fundamental 
para trabajar eficientemente con Claude en proyectos grandes. Claude puede 
ver todo tu código y ayudarte mejor si usas Git. Además, es mejor aprender 
de a poco que tener que aprenderlo todo de golpe después.

**Instalar Git:**

**Windows:**
1. Ve a https://git-scm.com/download/win
2. Descarga el instalador
3. Durante instalación, deja todo por defecto EXCEPTO:
   - Cuando pregunte por editor, elige "Visual Studio Code"
   - El resto déjalo como viene

**Mac:**
Git ya viene instalado. Para verificar, abre Terminal y escribe `git --version`.
Si no está, instálalo con `brew install git` (necesitas Homebrew instalado).

**Linux:**
`sudo apt-get install git` (Ubuntu/Debian) o `sudo yum install git` (CentOS/Fedora)

**Verificar instalación:**
Abre una terminal (en Windows: cmd o PowerShell) y escribe:
```
git --version
```
Debería mostrar algo como "git version 2.43.0"

**Configuración inicial de Git:**
Abre la terminal y ejecuta estos comandos (reemplaza con tu info):
```
git config --global user.name "Tu Nombre"
git config --global user.email "tu@email.com"
```

Esto es solo para que Git sepa quién eres cuando haces commits.

---

### 1.7.1 GitHub Desktop

**¿Qué es?**
GitHub Desktop es Git pero con interfaz visual. No tienes que aprender comandos 
de terminal todavía. Todo se hace con clicks.

**Descargar:** https://desktop.github.com/

**Instalación:**
Simple, siguiente-siguiente. Cuando te pida loguearte con GitHub, hazlo 
(si no tienes cuenta, créala gratis en github.com).

**No te agobies con Git ahora.**
Simplemente tenlo instalado. Conforme vayas desarrollando, te va a empezar 
a hacer sentido. Por ahora, con que lo tengas instalado es suficiente.

**Uso básico (para cuando estés listo):**
1. File → New Repository → Eliges la carpeta de tu proyecto
2. Escribes un mensaje describiendo cambios
3. Click en "Commit to main"
4. Click en "Push origin" para subirlo a GitHub

Pero de nuevo, no te preocupes por esto ahora. Solo ten el software instalado.

---

## 2. SOFTWARE OPCIONAL PERO MUY ÚTIL

Estos no son obligatorios pero te van a hacer la vida más fácil.

### 2.1 Postman

**¿Qué es?**
Una herramienta para probar APIs y peticiones HTTP. Súper útil cuando estés 
haciendo peticiones AJAX desde JavaScript a tu PHP.

**¿Cuándo lo necesitas?**
Imagina que tienes un archivo `api/buscar_producto.php` que recibe un código 
de producto y devuelve JSON. Con Postman puedes probar ese archivo directamente 
sin tener que hacer todo el frontend primero.

Puedes hacer peticiones GET, POST, ver qué responde, ver los headers, todo 
eso. Es como tener un cliente HTTP completo.

**Descargar:** https://www.postman.com/downloads/

**Alternativa más simple: Thunder Client**
Es una extensión de VS Code que hace lo mismo pero integrada en el editor. 
Busca "Thunder Client" en las extensiones de VS Code. Para empezar, esto es 
más cómodo que Postman.

**Ejemplo de uso:**
Tienes `api/buscar_producto.php?codigo=001`. En lugar de abrir el navegador 
y poner esa URL, en Postman/Thunder Client haces una petición GET a 
`http://localhost/tu-proyecto/api/buscar_producto.php?codigo=001` y ves la 
respuesta JSON formateada bonito.

---

### 2.2 Notepad++

**¿Qué es?**
Un editor de texto simple pero potente para Windows.

**¿Para qué?**
Para cuando necesites abrir rápido un archivo de configuración, editar algo 
muy simple, ver un log, o simplemente leer un archivo .txt grande. VS Code 
a veces es "demasiado" para abrir un archivo de texto de 3 líneas.

**Descargar:** https://notepad-plus-plus.org/

**Usuarios de Mac/Linux:**
No lo necesitan. Mac tiene TextEdit y Linux tiene gedit, nano, vim. Todos son 
suficientes para lo mismo.

---

### 2.3 Herramienta de Screenshots: ShareX o Lightshot

**¿Para qué?**
Vas a necesitar tomar MUCHAS capturas de pantalla:
- Para documentación de tu sistema
- Para mostrarle avances al cliente
- Para reportar bugs o pedir ayuda
- Para guardar un error antes de que desaparezca

**ShareX (Windows, gratis, open source):**
https://getsharex.com/

Es increíblemente potente. Tomas una captura, automáticamente la sube a la 
nube, y te da un link para compartir. También graba video de la pantalla.

**Lightshot (Multiplataforma, más simple):**
https://app.prntscr.com/en/index.html

Más simple que ShareX pero hace lo básico muy bien.

**Mac:**
Cmd+Shift+4 ya viene integrado y es suficiente. O usa CleanShot X (de pago 
pero excelente).

**Linux:**
Flameshot es excelente: `sudo apt install flameshot`

---

### 2.4 Software de Diseño (Para mockups básicos)

No necesitas ser diseñador, pero a veces ayuda hacer un boceto visual de 
cómo va a verse una pantalla antes de programarla.

**Opción 1: Figma** (Gratis, basado en web)
https://www.figma.com/

Es lo que usan los diseñadores profesionales. Tiene una curva de aprendizaje 
pero no es difícil. Puedes hacer wireframes (esquemas simples) de tus pantallas, 
diseñar el sistema completo visualmente, y después programarlo.

**Opción 2: Excalidraw** (Gratis, más simple)
https://excalidraw.com/

Para diagramas y bocetos rápidos a mano alzada. Perfecto para diseñar tu 
base de datos visualmente (diagrama ER) o hacer un flowchart de cómo funciona 
tu sistema.

**Opción 3: Draw.io / diagrams.net** (Gratis)
https://www.drawio.com/

Específicamente para diagramas técnicos. Excelente para modelar bases de datos, 
hacer diagramas de flujo, arquitectura del sistema, etc.

**Recomendación:**
Empieza con Excalidraw para bocetar ideas rápidas. Cuando necesites algo más 
profesional para mostrarle al cliente, usa Figma.

---

### 2.5 Compresor de Imágenes

Tus clientes te van a mandar imágenes de 5MB para logos o fotos de productos. 
Necesitas comprimirlas antes de subirlas al sistema.

**TinyPNG (Web, gratis):**
https://tinypng.com/

Arrastras una imagen, la comprime sin perder calidad visual, la descargas. 
Simple y efectivo.

**Alternativa offline: RIOT**
Para Windows, si prefieres algo local.

**Nota:** También puedes hacer esto programáticamente con PHP usando la 
librería GD o Imagick, pero para desarrollo es útil tener una herramienta 
rápida de escritorio.

---

## 3. CONFIGURACIONES POST-INSTALACIÓN

### 3.1 Configurar XAMPP

Después de instalar XAMPP, hay algunas cosas que deberías configurar.

**Iniciar servicios automáticamente (opcional):**
Si quieres que Apache y MySQL se inicien automáticamente cuando prendes tu 
PC, en XAMPP Control Panel hay un botón con una X al lado de cada servicio. 
Click ahí y selecciona "Install as service". Ahora se inician solos.

Personalmente recomiendo NO hacer esto. Es mejor iniciarlos manualmente cuando 
vas a trabajar para no tener servicios corriendo innecesariamente.

**Probar que funciona:**
1. Inicia Apache y MySQL en XAMPP Control Panel
2. Abre navegador y ve a `http://localhost`
3. Deberías ver la página de bienvenida de XAMPP
4. Ve a `http://localhost/phpmyadmin`
5. Deberías ver phpMyAdmin

Si ambas páginas cargan, todo está bien.

---

### 3.2 Configurar PHP en XAMPP

Edita el archivo de configuración de PHP para ajustar límites y opciones.

**Ubicación del archivo:**
`C:\xampp\php\php.ini`

**Ábrelo con Notepad++ o VS Code** y busca estas líneas (Ctrl+F para buscar):

**Aumentar límite de memoria:**
Busca `memory_limit` y cámbialo a:
```
memory_limit = 256M
```

**Aumentar tiempo de ejecución:**
Busca `max_execution_time` y cámbialo a:
```
max_execution_time = 300
```

**Aumentar tamaño de subida de archivos:**
Busca `upload_max_filesize` y `post_max_size`:
```
upload_max_filesize = 64M
post_max_size = 64M
```

**Habilitar errores en desarrollo:**
Busca `display_errors` y `error_reporting`:
```
display_errors = On
error_reporting = E_ALL
```

**Zona horaria:**
Busca `date.timezone` y ponlo así (Guatemala):
```
date.timezone = America/Guatemala
```

**Guardar y reiniciar Apache:**
Después de hacer estos cambios, guarda el archivo y en XAMPP Control Panel 
haz Stop y después Start en Apache para que los cambios tomen efecto.

---

### 3.3 Configurar VS Code

Ya mencionamos las configuraciones en la sección de VS Code, pero aquí un 
resumen rápido:

1. Ctrl+, para abrir Settings
2. Busca y configura:
   - Auto Save: afterDelay (1000ms)
   - Format On Save: activado
   - Font Size: 14-16
   - Word Wrap: on
   - Tab Size: 4

3. Instala tema oscuro si no lo hiciste:
   - Ctrl+K Ctrl+T
   - Selecciona "One Dark Pro" o "Dark+ (default dark)"

---

## 4. ORGANIZACIÓN DE TU ENTORNO

### 4.1 Estructura de Carpetas en tu PC

Organízate desde el principio. Crea esta estructura de carpetas en tu PC:

```
C:\Users\TuNombre\
├── Proyectos\
│   ├── sistema-tienda\
│   ├── sistema-consultorio\
│   └── sistema-hospital\  (proyectos futuros)
│
├── Recursos\
│   ├── plantillas\
│   │   └── plantilla-base\  (tu plantilla inicial de proyecto)
│   ├── librerías\
│   │   ├── bootstrap-5.3.2\
│   │   ├── datatables-1.13.8\
│   │   ├── chart.js-4.4.0\
│   │   ├── fpdf\
│   │   ├── phpmailer\
│   │   └── sweetalert2\
│   └── documentación\
│       ├── contratos\
│       ├── propuestas\
│       └── manuales\
│
└── Backups\
    ├── 2025-01\
    │   ├── tienda-2025-01-15.zip
    │   └── consultorio-2025-01-15.zip
    └── 2025-02\
```

### 4.2 Estructura en XAMPP

```
C:\xampp\htdocs\
├── tienda\  (tu proyecto activo de tienda)
│   ├── index.php
│   ├── config.php
│   ├── /assets/
│   └── ...
│
├── consultorio\  (tu proyecto activo de consultorio)
│   ├── index.php
│   ├── config.php
│   └── ...
│
└── pruebas\  (carpeta para experimentar)
    └── test.php
```

**Acceso:**
- Tienda: `http://localhost/tienda/`
- Consultorio: `http://localhost/consultorio/`
- Pruebas: `http://localhost/pruebas/`

---

## 5. VERIFICACIÓN DE INSTALACIÓN

### 5.1 Test Completo del Ambiente

Vamos a hacer un test para verificar que TODO está funcionando.

**Paso 1: Crear carpeta de prueba**
1. Ve a `C:\xampp\htdocs\`
2. Crea una carpeta llamada `test`

**Paso 2: Crear archivo PHP de prueba**
1. Abre VS Code
2. File → Open Folder → Selecciona `C:\xampp\htdocs\test`
3. New File (Ctrl+N)
4. Escribe exactamente esto:

```php
<?php
// test.php
echo "<h1>Test de PHP</h1>";
echo "<p>PHP está funcionando correctamente</p>";
echo "<p>Versión de PHP: " . phpversion() . "</p>";

// Test de conexión a MySQL
try {
    $pdo = new PDO("mysql:host=localhost", "root", "");
    echo "<p style='color: green;'>✓ Conexión a MySQL exitosa</p>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Error de conexión a MySQL</p>";
}
?>
```

5. Guarda como `index.php` dentro de la carpeta test

**Paso 3: Probar**
1. Asegúrate que Apache y MySQL estén corriendo en XAMPP
2. Abre tu navegador
3. Ve a `http://localhost/test/`
4. Deberías ver:
   - "Test de PHP"
   - "PHP está funcionando correctamente"
   - La versión de PHP (debería ser 8.1 o 8.2)
   - "✓ Conexión a MySQL exitosa" en verde

Si ves todo eso, ¡FELICIDADES! Tu ambiente de desarrollo está perfecto.

**Paso 4: Test de base de datos**
1. Ve a `http://localhost/phpmyadmin`
2. Click en "New" (Nueva base de datos)
3. Nombre: `test_db`
4. Click en "Create"
5. Si se crea sin errores, perfecto

**Paso 5: Test de VS Code con Live Server**
1. En VS Code, en tu carpeta test, crea un archivo `test.html`
2. Escribe esto:

```html
<!DOCTYPE html>
<html>
<head>
    <title>Test HTML</title>
</head>
<body>
    <h1>Test de HTML</h1>
    <p>Si ves esto, HTML funciona</p>
</body>
</html>
```

3. Guarda
4. Click derecho en el archivo → "Open with Live Server"
5. Se debería abrir en el navegador

Si funciona todo esto, estás 100% listo para empezar a desarrollar.

---

## 6. PROBLEMAS COMUNES Y SOLUCIONES

### Problema: "Apache no inicia en XAMPP"

**Síntoma:** Cuando le das Start a Apache en XAMPP, se pone rojo y dice "Port 
80 in use by..." o simplemente no arranca.

**Causa:** El puerto 80 (que usa Apache) está siendo usado por otro programa. 
Normalmente es Skype, IIS (Internet Information Services de Windows), o algún 
antivirus.

**Soluciones:**

**Opción 1 - Cerrar el programa conflictivo:**
- Si es Skype: Ciérralo completamente
- Si es IIS: Ve a Panel de Control → Programas → Activar o desactivar 
  características de Windows → Desmarcar "Internet Information Services"

**Opción 2 - Cambiar el puerto de Apache:**
1. En XAMPP Control Panel, click en "Config" al lado de Apache
2. Selecciona "httpd.conf"
3. Busca la línea que dice `Listen 80` y cámbiala a `Listen 8080`
4. Guarda y reinicia Apache
5. Ahora accedes con `http://localhost:8080/` en lugar de `http://localhost/`

---

### Problema: "MySQL no inicia en XAMPP"

**Síntoma:** MySQL no arranca, se pone rojo.

**Causa:** Puerto 3306 ocupado por otra instancia de MySQL.

**Solución:**
1. En XAMPP Control Panel, click en "Config" al lado de MySQL
2. Selecciona "my.ini"
3. Busca `port=3306` y cámbialo a `port=3307`
4. Guarda y reinicia MySQL

Ahora tendrás que conectarte al puerto 3307 en lugar de 3306.

---

### Problema: "No puedo acceder a localhost"

**Síntoma:** Cuando pones `http://localhost` en el navegador no carga nada.

**Verificaciones:**
1. ¿Apache está corriendo en XAMPP? (debe estar verde)
2. ¿Pusiste http:// antes de localhost? No pongas solo "localhost"
3. ¿Tienes firewall bloqueando? Desactívalo temporalmente para probar
4. Intenta con `http://127.0.0.1` en lugar de localhost

---

### Problema: "Los cambios en mi código no se ven en el navegador"

**Causa:** Caché del navegador.

**Solución:**
Ctrl+F5 para recargar sin caché (hard refresh). O abre DevTools (F12), 
click derecho en el botón de recargar, selecciona "Empty Cache and Hard Reload".

---

### Problema: "Error al conectar con la base de datos"

**Síntoma:** Tu código PHP dice "Error de conexión a base de datos"

**Verificaciones:**
1. ¿MySQL está corriendo en XAMPP?
2. ¿El nombre de la BD es correcto?
3. ¿Usuario es "root" y password vacío (en local)?
4. ¿Host es "localhost"?

**Test:**
Intenta este código:

```php
<?php
try {
    $pdo = new PDO("mysql:host=localhost", "root", "");
    echo "Conexión OK";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
```

---

### Problema: "Git no se reconoce como comando"

**Síntoma:** Cuando escribes `git --version` en la terminal dice "git no se 
reconoce como comando..."

**Causa:** Git no está en el PATH de Windows.

**Solución:**
Durante la instalación de Git hay que marcar "Add to PATH". Si no lo hiciste, 
reinstala Git y asegúrate de marcar esa opción.

---

## 7. CHECKLIST DE INSTALACIÓN

Usa este checklist para verificar que tienes todo:

**Software Esencial:**
- [ ] XAMPP instalado y funcionando (Apache + MySQL corriendo)
- [ ] VS Code instalado
- [ ] Extensiones mínimas de VS Code instaladas:
  - [ ] PHP Intelephense
  - [ ] HTML CSS Support
  - [ ] JavaScript (ES6) code snippets
  - [ ] Live Server
- [ ] Chrome instalado con DevTools conocido
- [ ] HeidiSQL (o DBeaver) instalado
- [ ] FileZilla (o WinSCP) instalado
- [ ] Git instalado
- [ ] GitHub Desktop instalado
- [ ] Cuenta en GitHub creada

**Software Opcional:**
- [ ] Firefox Developer Edition
- [ ] Postman o Thunder Client (extensión VS Code)
- [ ] Notepad++ (Windows) o editor de texto simple
- [ ] ShareX o Lightshot (screenshots)
- [ ] Figma o Excalidraw (diseño/mockups)

**Configuraciones:**
- [ ] PHP.ini ajustado en XAMPP
- [ ] VS Code configurado (auto save, format on save, etc.)
- [ ] Tema oscuro instalado en VS Code
- [ ] Git configurado con tu nombre y email

**Organización:**
- [ ] Carpeta de Proyectos creada
- [ ] Carpeta de Recursos creada
- [ ] Carpeta de Backups creada

**Verificación:**
- [ ] Test de PHP funcionando (`http://localhost/test/`)
- [ ] phpMyAdmin accesible
- [ ] HeidiSQL conectado a BD local
- [ ] Live Server funciona en VS Code
- [ ] Git responde `git --version`

---

## 8. RECURSOS ADICIONALES

### Tutoriales para Aprender las Herramientas

**VS Code:**
- Video oficial: "Visual Studio Code Intro & Setup" en YouTube
- Práctica los shortcuts básicos:
  - Ctrl+P: Buscar archivo
  - Ctrl+Shift+F: Buscar en todo el proyecto
  - Ctrl+`: Abrir/cerrar terminal integrada
  - Alt+↑/↓: Mover línea arriba/abajo
  - Ctrl+D: Seleccionar siguiente ocurrencia
  - Ctrl+/: Comentar/descomentar línea

**XAMPP:**
- Tutorial oficial en Apache Friends
- Práctica creando bases de datos en phpMyAdmin
- Aprende la estructura de carpetas de XAMPP

**Chrome DevTools:**
- Google tiene un curso gratis: "Chrome DevTools" en web.dev
- Practica inspeccionando elementos en sitios web
- Aprende a usar la consola para debuggear JavaScript

**Git (cuando estés listo):**
- "Git and GitHub for Beginners" en YouTube por freeCodeCamp
- "Learn Git Branching" (learngitbranching.js.org) - Tutorial interactivo

### Documentación Oficial

Guarda estos links, los vas a usar constantemente:

**PHP:**
https://www.php.net/manual/es/
La documentación oficial de PHP. Cada función tiene ejemplos.

**MySQL:**
https://dev.mysql.com/doc/
Documentación de MySQL.

**Bootstrap:**
https://getbootstrap.com/docs/
Documentación de Bootstrap con ejemplos de cada componente.

**MDN (HTML/CSS/JavaScript):**
https://developer.mozilla.org/es/
La mejor documentación para tecnologías web.

**W3Schools:**
https://www.w3schools.com/
Para referencia rápida de HTML, CSS, JavaScript, PHP, SQL.

### Comunidades y Ayuda

**Stack Overflow:**
https://stackoverflow.com/
Cuando tengas un error, búscalo aquí. Probablemente alguien ya lo tuvo.

**Reddit:**
- r/PHP
- r/webdev
- r/learnprogramming

**Discord:**
Hay muchos servidores de programación en español. Busca "Programación en 
Español Discord".

---

## CONCLUSIÓN

Con todo este software instalado y configurado correctamente, tienes un 
ambiente de desarrollo profesional completo. No necesitas nada más para 
empezar a crear sistemas web de calidad.

**Recuerda:**
- XAMPP para tu servidor local (Apache + MySQL + PHP)
- VS Code para escribir código
- Chrome DevTools para debuggear
- HeidiSQL para manejar bases de datos
- FileZilla para subir archivos a producción
- Git para control de versiones (eventualmente)

**No te agobies si al principio te parece mucho.** Instala todo siguiendo 
esta guía, haz el test de verificación, y cuando empieces a desarrollar todo 
va a empezar a hacer sentido.

La primera vez configurando el ambiente toma tiempo. Pero solo lo haces UNA 
vez. Después simplemente usas las herramientas.

═══════════════════════════════════════════════════════════
          ¡Tu ambiente de desarrollo está listo! 🚀
═══════════════════════════════════════════════════════════
