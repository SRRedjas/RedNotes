<?php

namespace Database\Seeders;

use App\Models\Note;
use App\Models\User;
use App\Services\NoteService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

/**
 * Public wiki pages about operating systems, based on Tanenbaum's
 * "Modern Operating Systems". Safe to run more than once: existing pages
 * (matched by title) are updated instead of duplicated.
 *
 * php artisan db:seed --class=OperatingSystemsWikiSeeder
 */
class OperatingSystemsWikiSeeder extends Seeder
{
    public function run(NoteService $service): void
    {
        $user = User::query()->orderBy('id')->first();

        if (! $user) {
            $this->command?->error('No users found; register one before seeding the wiki.');

            return;
        }

        Auth::login($user);

        $pages = $this->pages();

        // Two passes: [[wikilinks]] only resolve to pages that already exist,
        // so the second pass picks up links between pages created in the first.
        foreach ([1, 2] as $pass) {
            foreach ($pages as $title => $content) {
                $data = ['title' => $title, 'content' => trim($content), 'visibility' => 'public'];
                $note = Note::where('visibility', 'public')->where('title', $title)->first();

                $note ? $service->updateNote($note, $data) : $service->createNote($user, $data);
            }
        }

        $this->command?->info(count($pages).' wiki pages seeded as '.$user->email);
    }

    /**
     * @return array<string, string>
     */
    private function pages(): array
    {
        return [
            'Sistemas Operativos' => <<<'MD'
> Basado en *Modern Operating Systems* (Andrew S. Tanenbaum y Herbert Bos).

Un **sistema operativo** es la capa de software que se sitúa entre el hardware y los programas de usuario. Tanenbaum lo describe desde dos perspectivas complementarias:

1. **Como máquina extendida** (de arriba hacia abajo): oculta la complejidad del hardware y ofrece abstracciones limpias — procesos, archivos, espacios de direcciones — en lugar de discos, registros e interrupciones.
2. **Como administrador de recursos** (de abajo hacia arriba): reparte de forma ordenada y controlada la CPU, la memoria y los dispositivos de E/S entre los programas que compiten por ellos, multiplexando en el **tiempo** (turnos de CPU) y en el **espacio** (porciones de memoria o disco).

## Modo kernel y modo usuario

El hardware ofrece al menos dos modos de ejecución. En **modo kernel** el código tiene acceso completo a todas las instrucciones y al hardware; en **modo usuario** sólo a un subconjunto. El sistema operativo es, en sentido estricto, la parte que corre en modo kernel. Ver [[Sistemas Operativos/Kernel]].

## Conceptos fundamentales

- [[Sistemas Operativos/Kernel]] — núcleo y arquitecturas (monolítico, microkernel, etc.).
- [[Sistemas Operativos/Llamadas al sistema]] — la interfaz entre programas y SO.
- [[Sistemas Operativos/Procesos]] — programas en ejecución.
- [[Sistemas Operativos/Hilos]] — múltiples flujos de control dentro de un proceso.
- [[Sistemas Operativos/Concurrencia]] — condiciones de carrera, regiones críticas, exclusión mutua.
- [[Sistemas Operativos/Semáforos]] — primitiva de sincronización de Dijkstra.
- [[Sistemas Operativos/Mutex y monitores]] — sincronización de más alto nivel.
- [[Sistemas Operativos/Problemas clásicos de IPC]] — productor-consumidor, filósofos, lectores-escritores.
- [[Sistemas Operativos/Planificación]] — qué proceso corre a continuación.
- [[Sistemas Operativos/Interbloqueos]] — deadlocks y cómo tratarlos.
- [[Sistemas Operativos/Memoria virtual]] — paginación y reemplazo de páginas.
- [[Sistemas Operativos/Sistemas de archivos]] — archivos, directorios e implementación.
- [[Sistemas Operativos/Entrada y salida]] — dispositivos, controladores e interrupciones.

## Breve historia (generaciones)

| Generación | Época | Tecnología | Característica |
|---|---|---|---|
| 1ª | 1945–55 | Tubos de vacío | Sin SO, programación en tableros |
| 2ª | 1955–65 | Transistores | Sistemas por lotes (*batch*) |
| 3ª | 1965–80 | Circuitos integrados | Multiprogramación, tiempo compartido (MULTICS, UNIX) |
| 4ª | 1980– | Computadoras personales | GUI, MS-DOS, Windows, Linux, macOS |
| 5ª | 1990– | Dispositivos móviles | Android, iOS |

#sistemas-operativos #tanenbaum
MD,

            'Sistemas Operativos/Kernel' => <<<'MD'
El **kernel** (núcleo) es la parte del [[Sistemas Operativos]] que se ejecuta en **modo kernel** (privilegiado). Tiene acceso total al hardware: puede ejecutar cualquier instrucción, manipular la MMU, deshabilitar interrupciones y acceder a los dispositivos. Los programas de usuario sólo pueden pedirle servicios mediante [[Sistemas Operativos/Llamadas al sistema]].

## Estructuras de sistemas operativos

Tanenbaum describe varias formas de organizar el núcleo:

### Monolítico
Todo el SO se ejecuta como un único programa en modo kernel. Es una colección de procedimientos enlazados que pueden llamarse entre sí libremente.

- ✅ Muy eficiente: las llamadas internas son simples llamadas a función.
- ❌ Un fallo en cualquier componente (p. ej. un driver) puede tumbar todo el sistema.
- Ejemplos: UNIX tradicional, **Linux** (con módulos cargables), Windows (en gran parte).

### Sistemas en capas
El SO se organiza como una jerarquía de capas, cada una construida sobre la inferior. El ejemplo clásico es **THE** (Dijkstra, 1968) con 6 capas; MULTICS generalizó la idea con anillos concéntricos de privilegio.

### Microkernel
Sólo un núcleo mínimo corre en modo kernel (manejo de interrupciones, IPC básico, planificación de bajo nivel). Drivers, sistema de archivos y gestión de memoria corren como **procesos de usuario** separados.

- ✅ Alta fiabilidad: un driver que falla no compromete al kernel; puede reiniciarse.
- ❌ Más cambios de contexto y paso de mensajes → coste de rendimiento.
- Ejemplos: **MINIX 3** (del propio Tanenbaum), QNX, L4, seL4.

### Modelo cliente-servidor
Variante del microkernel: los procesos se dividen en *servidores* que ofrecen servicios y *clientes* que los usan, comunicándose por paso de mensajes. Escala naturalmente a sistemas distribuidos.

### Máquinas virtuales
Un **hipervisor** multiplexa el hardware y ofrece varias copias exactas de la máquina, cada una con su propio SO. Tipo 1 corre sobre el hardware (VM/370, Xen, ESXi); tipo 2 sobre un SO anfitrión (VirtualBox, VMware Workstation).

### Exokernels
El núcleo sólo asigna recursos de forma segura y deja las abstracciones a bibliotecas en espacio de usuario.

## Funciones típicas del kernel

- Gestión de [[Sistemas Operativos/Procesos]] e [[Sistemas Operativos/Hilos]].
- [[Sistemas Operativos/Planificación]] de CPU.
- Gestión de memoria: [[Sistemas Operativos/Memoria virtual]].
- [[Sistemas Operativos/Sistemas de archivos]].
- Manejo de interrupciones y [[Sistemas Operativos/Entrada y salida]].
- Primitivas de sincronización: [[Sistemas Operativos/Semáforos]], [[Sistemas Operativos/Mutex y monitores]].

> El debate **Tanenbaum–Torvalds** (1992) enfrentó precisamente microkernel (MINIX) contra kernel monolítico (Linux).

#sistemas-operativos #tanenbaum #kernel
MD,

            'Sistemas Operativos/Llamadas al sistema' => <<<'MD'
Una **llamada al sistema** (*system call*, *syscall*) es el mecanismo mediante el cual un programa en modo usuario solicita un servicio al [[Sistemas Operativos/Kernel]]. Es la verdadera interfaz entre los programas y el [[Sistemas Operativos]].

## Cómo funciona una syscall

Tanenbaum la ilustra con `read(fd, buffer, nbytes)`:

1. El programa coloca los parámetros en la pila o en registros.
2. Llama al procedimiento de biblioteca (p. ej. `read` de libc).
3. La biblioteca coloca el **número de la syscall** en un registro conocido.
4. Ejecuta una instrucción **TRAP** (`syscall` en x86-64, `svc` en ARM), que cambia a modo kernel y salta a una dirección fija.
5. El kernel examina el número y despacha al **manejador** correspondiente mediante una tabla de punteros.
6. El manejador hace el trabajo.
7. El control vuelve a la biblioteca en modo usuario (o el proceso se bloquea si debe esperar E/S).
8. La biblioteca regresa al programa, que limpia la pila.

> Una syscall se parece a una llamada a procedimiento, pero cruza la frontera usuario/kernel, lo que la hace mucho más costosa.

## Principales llamadas POSIX

### Gestión de [[Sistemas Operativos/Procesos]]
| Llamada | Descripción |
|---|---|
| `pid = fork()` | Crea un proceso hijo idéntico al padre |
| `pid = waitpid(pid, &status, opts)` | Espera a que termine un hijo |
| `s = execve(name, argv, envp)` | Reemplaza la imagen del proceso |
| `exit(status)` | Termina el proceso |

### Gestión de archivos
| Llamada | Descripción |
|---|---|
| `fd = open(file, how, ...)` | Abre un archivo |
| `s = close(fd)` | Cierra un archivo |
| `n = read(fd, buf, nbytes)` | Lee datos |
| `n = write(fd, buf, nbytes)` | Escribe datos |
| `pos = lseek(fd, offset, whence)` | Mueve el puntero del archivo |
| `s = stat(name, &buf)` | Obtiene información de estado |

### Directorios y sistema de archivos
`mkdir`, `rmdir`, `link`, `unlink`, `mount`, `umount`.

### Misceláneas
`chdir`, `chmod`, `kill`, `time`.

## Ejemplo: un shell mínimo

```c
while (TRUE) {
    type_prompt();
    read_command(command, parameters);
    if (fork() != 0) {
        /* Código del padre */
        waitpid(-1, &status, 0);
    } else {
        /* Código del hijo */
        execve(command, parameters, 0);
    }
}
```

## API Win32
Windows separa las llamadas reales del kernel de la API pública **Win32** (`CreateProcess`, `WaitForSingleObject`, `CreateFile`, `ReadFile`...). Las llamadas internas cambian entre versiones; la API se mantiene estable.

#sistemas-operativos #tanenbaum #syscalls
MD,

            'Sistemas Operativos/Procesos' => <<<'MD'
Un **proceso** es un programa en ejecución, incluyendo los valores actuales del contador de programa, los registros y las variables. Es la abstracción central del [[Sistemas Operativos]]: convierte una sola CPU en varias CPUs virtuales (**pseudoparalelismo**).

## Modelo de procesos

Conceptualmente cada proceso tiene su propia CPU virtual; en realidad la CPU conmuta entre ellos (**multiprogramación**). La diferencia entre programa y proceso: el programa es la receta; el proceso es la actividad de cocinar.

## Creación
Un proceso se crea por:
1. Inicialización del sistema.
2. Una llamada de creación desde otro proceso (`fork` en UNIX, `CreateProcess` en Windows).
3. Petición del usuario.
4. Inicio de un trabajo por lotes.

Ver [[Sistemas Operativos/Llamadas al sistema]].

## Terminación
1. Salida normal (voluntaria).
2. Salida por error (voluntaria).
3. Error fatal (involuntaria).
4. Eliminado por otro proceso (`kill`).

## Estados de un proceso

```
         ┌────────────┐
         │ En ejecución│
         └────────────┘
      1 ↙     ↑3     ↘ 2
┌──────────┐  │   ┌──────────┐
│Bloqueado │  │   │  Listo   │
└──────────┘  │   └──────────┘
        4 ↘   └───────┘
          → Listo
```

1. **En ejecución → Bloqueado**: el proceso espera una entrada.
2. **En ejecución → Listo**: el planificador elige otro proceso.
3. **Listo → En ejecución**: el planificador elige este proceso.
4. **Bloqueado → Listo**: la entrada ya está disponible.

Las transiciones 2 y 3 las decide la [[Sistemas Operativos/Planificación]].

## Tabla de procesos (PCB)
El SO mantiene una entrada por proceso (*Process Control Block*) con:
- **Gestión de procesos**: registros, contador de programa, puntero de pila, estado, prioridad, PID, proceso padre, tiempo de CPU usado.
- **Gestión de memoria**: punteros a segmentos de texto, datos y pila / tabla de páginas.
- **Gestión de archivos**: directorio raíz y de trabajo, descriptores de archivo, UID, GID.

## Interrupciones y cambio de contexto
Cuando llega una interrupción, el hardware guarda el contador de programa, salta al vector de interrupciones, una rutina en ensamblador guarda los registros, se ejecuta el servicio en C y luego el planificador decide qué proceso reanudar. Guardar y restaurar este estado es el **cambio de contexto**.

## Modelado de la multiprogramación
Si un proceso pasa una fracción *p* de su tiempo esperando E/S, con *n* procesos en memoria:

**Uso de CPU = 1 − pⁿ**

Con 80 % de espera de E/S, se necesitan ~10 procesos para desperdiciar menos del 10 % de CPU.

## Relacionado
- [[Sistemas Operativos/Hilos]]
- [[Sistemas Operativos/Concurrencia]]

#sistemas-operativos #tanenbaum #procesos
MD,

            'Sistemas Operativos/Hilos' => <<<'MD'
Un **hilo** (*thread*) es un flujo de control dentro de un proceso. Varios hilos comparten el mismo espacio de direcciones, lo que los hace mucho más ligeros que los procesos ("procesos ligeros").

Ver también: [[Sistemas Operativos/Procesos]].

## ¿Por qué usar hilos?
1. Muchas aplicaciones realizan varias actividades a la vez; algunas se bloquean. Descomponerlas en hilos cuasi-paralelos simplifica el modelo de programación.
2. Son más fáciles (rápidos) de crear y destruir que los procesos: 10–100 veces más.
3. Permiten solapar cómputo y E/S.
4. Aprovechan múltiples CPUs para paralelismo real.

**Ejemplo de Tanenbaum**: un procesador de textos con un hilo que interactúa con el usuario, otro que reformatea el documento y otro que guarda en disco periódicamente. O un servidor web con un hilo *despachador* y varios hilos *trabajadores*.

## Qué se comparte y qué no

| Elementos por proceso (compartidos) | Elementos por hilo (privados) |
|---|---|
| Espacio de direcciones | Contador de programa |
| Variables globales | Registros |
| Archivos abiertos | Pila |
| Procesos hijos | Estado |
| Alarmas pendientes | |
| Señales y manejadores | |

Cada hilo necesita su propia **pila** porque llama a procedimientos distintos y tiene su propio historial de ejecución.

## Pthreads (POSIX)
| Llamada | Descripción |
|---|---|
| `pthread_create` | Crea un hilo |
| `pthread_exit` | Termina el hilo actual |
| `pthread_join` | Espera a que un hilo termine |
| `pthread_yield` | Cede la CPU a otro hilo |
| `pthread_attr_init` | Inicializa atributos |

## Implementaciones

### Hilos en espacio de usuario
Una biblioteca en espacio de usuario gestiona los hilos; el kernel sólo ve un proceso.
- ✅ Cambio de hilo muy rápido (sin trap al kernel); planificación personalizable.
- ❌ Una llamada bloqueante bloquea a **todos** los hilos; un fallo de página también; no hay apropiación por reloj dentro del proceso.

### Hilos en el kernel
El [[Sistemas Operativos/Kernel]] conoce y planifica cada hilo.
- ✅ Si un hilo se bloquea, el kernel puede ejecutar otro del mismo proceso.
- ❌ Crear y conmutar hilos requiere [[Sistemas Operativos/Llamadas al sistema]], más costoso.

### Implementaciones híbridas
Se multiplexan *m* hilos de usuario sobre *n* hilos de kernel (modelo M:N). Las **activaciones del planificador** (*scheduler activations*) usan *upcalls* del kernel para avisar a la biblioteca cuando un hilo se bloquea.

### Hilos emergentes (*pop-up threads*)
Se crea un hilo nuevo al llegar cada mensaje, en lugar de tener uno bloqueado esperándolo.

## Problemas al convertir código a multihilo
- Variables globales como `errno` que varios hilos sobrescriben → usar variables globales privadas por hilo.
- Bibliotecas no **reentrantes** (p. ej. `malloc`).
- Señales: ¿qué hilo las recibe?
- Gestión de la pila de cada hilo.

Compartir memoria trae los problemas de [[Sistemas Operativos/Concurrencia]].

#sistemas-operativos #tanenbaum #hilos
MD,

            'Sistemas Operativos/Concurrencia' => <<<'MD'
Cuando varios [[Sistemas Operativos/Procesos]] o [[Sistemas Operativos/Hilos]] comparten datos, surge la necesidad de **comunicación entre procesos (IPC)** y de sincronizarlos. Tanenbaum identifica tres problemas: cómo pasar información, cómo evitar que se estorben y cómo ordenar correctamente sus acciones.

## Condiciones de carrera
Una **condición de carrera** (*race condition*) ocurre cuando dos o más procesos leen o escriben datos compartidos y el resultado final depende del orden exacto de ejecución.

**Ejemplo del spooler de impresión**: dos procesos A y B leen `in = 7` (siguiente ranura libre). A es interrumpido antes de actualizar `in`; B escribe su archivo en la ranura 7 y pone `in = 8`; A reanuda, escribe también en la ranura 7 y pone `in = 8`. El trabajo de B se pierde y nadie se entera.

## Regiones críticas
La **región crítica** (o sección crítica) es la parte del programa donde se accede a memoria compartida. Para evitar carreras hace falta **exclusión mutua**. Una buena solución debe cumplir cuatro condiciones:

1. Dos procesos no pueden estar simultáneamente dentro de sus regiones críticas.
2. No se pueden hacer suposiciones sobre velocidades o número de CPUs.
3. Ningún proceso fuera de su región crítica puede bloquear a otros.
4. Ningún proceso debe esperar indefinidamente para entrar a su región crítica.

## Exclusión mutua con espera ocupada

### Deshabilitar interrupciones
Sencillo en monoprocesador, pero peligroso dar ese poder a procesos de usuario y no funciona en multiprocesadores. Útil sólo dentro del [[Sistemas Operativos/Kernel]].

### Variables de candado
Un simple `lock = 0/1` tiene la misma condición de carrera que pretende resolver.

### Alternancia estricta
```c
/* Proceso 0 */                 /* Proceso 1 */
while (TRUE) {                  while (TRUE) {
    while (turn != 0) ;             while (turn != 1) ;
    critical_region();              critical_region();
    turn = 1;                       turn = 0;
    noncritical_region();           noncritical_region();
}                               }
```
Viola la condición 3: un proceso lento fuera de su región crítica bloquea al otro. Una variable que se consulta en bucle es un **spin lock**.

### Solución de Peterson
```c
int turn;
int interested[2];

void enter_region(int process) {
    int other = 1 - process;
    interested[process] = TRUE;
    turn = process;
    while (turn == process && interested[other] == TRUE) ;
}

void leave_region(int process) {
    interested[process] = FALSE;
}
```

### Instrucción TSL (Test and Set Lock)
Soporte de hardware: `TSL REGISTER,LOCK` lee la palabra `LOCK` en un registro y guarda un valor distinto de cero, de forma **atómica** (bloquea el bus de memoria). `XCHG` en x86 es equivalente.

```
enter_region:
    TSL REGISTER,LOCK   | copia lock al registro y pone lock a 1
    CMP REGISTER,0      | ¿era cero?
    JNE enter_region    | si no, el candado estaba puesto: repetir
    RET
leave_region:
    MOVE LOCK,0
    RET
```

## Problema de la espera ocupada
Desperdicia CPU y sufre el **problema de inversión de prioridades**: un proceso H de alta prioridad hace espera ocupada mientras L, de baja prioridad, tiene el candado pero nunca es planificado.

La alternativa es **dormir y despertar** (`sleep`/`wakeup`), que lleva a los [[Sistemas Operativos/Semáforos]] y a los [[Sistemas Operativos/Mutex y monitores]].

## Otras formas de IPC
- **Paso de mensajes**: `send(destino, &mensaje)` y `receive(origen, &mensaje)`. Funciona entre máquinas.
- **Barreras**: ningún proceso pasa a la siguiente fase hasta que todos han llegado.
- **RCU** (*read-copy-update*): lectores sin candados mientras los escritores publican versiones nuevas.

Ver [[Sistemas Operativos/Problemas clásicos de IPC]] e [[Sistemas Operativos/Interbloqueos]].

#sistemas-operativos #tanenbaum #concurrencia
MD,

            'Sistemas Operativos/Semáforos' => <<<'MD'
Un **semáforo** es una variable entera propuesta por **E. W. Dijkstra (1965)** para contar el número de "despertares" pendientes. Resuelve el problema de los despertares perdidos de `sleep`/`wakeup` descrito en [[Sistemas Operativos/Concurrencia]].

## Operaciones
Dijkstra las llamó **P** (*proberen*, probar) y **V** (*verhogen*, incrementar); Tanenbaum usa `down` y `up`:

- **down(s)**: si `s > 0`, lo decrementa y continúa. Si `s == 0`, el proceso se **duerme** sin completar el `down`.
- **up(s)**: incrementa `s`. Si había procesos dormidos en el semáforo, el sistema elige uno y le permite completar su `down`.

Ambas operaciones son **indivisibles (atómicas)**: una vez iniciada, ningún otro proceso puede acceder al semáforo hasta que termine. Normalmente se implementan como [[Sistemas Operativos/Llamadas al sistema]], deshabilitando brevemente las interrupciones o usando TSL/XCHG en multiprocesadores.

## Tipos
- **Semáforo binario (mutex)**: inicializado en 1, garantiza exclusión mutua.
- **Semáforo contador**: inicializado en N, cuenta recursos disponibles.

Además de exclusión mutua, los semáforos sirven para **sincronización**: garantizar que ciertos eventos ocurren en cierto orden.

## Productor-consumidor con semáforos

```c
const int N = 100;
typedef int semaphore;
semaphore mutex = 1;     /* controla el acceso a la región crítica */
semaphore empty = N;     /* cuenta ranuras vacías del búfer */
semaphore full  = 0;     /* cuenta ranuras llenas del búfer */

void producer(void) {
    int item;
    while (TRUE) {
        item = produce_item();
        down(&empty);
        down(&mutex);
        insert_item(item);
        up(&mutex);
        up(&full);
    }
}

void consumer(void) {
    int item;
    while (TRUE) {
        down(&full);
        down(&mutex);
        item = remove_item();
        up(&mutex);
        up(&empty);
        consume_item(item);
    }
}
```

- `mutex` se usa para **exclusión mutua**.
- `full` y `empty` se usan para **sincronización**.

## ⚠️ El orden importa
Si el productor invierte `down(&empty)` y `down(&mutex)` y el búfer está lleno, se bloquea con el mutex tomado: el consumidor nunca puede entrar y ambos quedan bloqueados para siempre. Es un interbloqueo — ver [[Sistemas Operativos/Interbloqueos]].

Esta fragilidad motivó primitivas de más alto nivel: [[Sistemas Operativos/Mutex y monitores]].

## Semáforos en la práctica (POSIX)
`sem_init`, `sem_wait` (down), `sem_post` (up), `sem_destroy`.

#sistemas-operativos #tanenbaum #semaforos #concurrencia
MD,

            'Sistemas Operativos/Mutex y monitores' => <<<'MD'
Primitivas de sincronización derivadas de los [[Sistemas Operativos/Semáforos]], pensadas para resolver la [[Sistemas Operativos/Concurrencia]] de forma más simple y menos propensa a errores.

## Mutex
Un **mutex** es una versión simplificada del semáforo, sin capacidad de contar. Tiene dos estados: **abierto** o **cerrado**.

- `mutex_lock`: si está abierto, lo cierra y el hilo entra a la región crítica. Si está cerrado, el hilo se bloquea.
- `mutex_unlock`: lo abre; si hay hilos bloqueados, se elige uno al azar.

Implementación de Tanenbaum para hilos en espacio de usuario (con TSL):
```
mutex_lock:
    TSL REGISTER,MUTEX
    CMP REGISTER,0
    JZE ok              | si era 0, estaba abierto: regresar
    CALL thread_yield   | ocupado: planificar otro hilo
    JMP mutex_lock
ok: RET
mutex_unlock:
    MOVE MUTEX,0
    RET
```
La diferencia con `enter_region` es que no hace espera ocupada: cede la CPU.

### Futex (Linux)
Un **futex** (*fast user space mutex*) evita entrar al [[Sistemas Operativos/Kernel]] cuando no hay contención: el candado se toma con una operación atómica en espacio de usuario y sólo se llama al kernel para bloquearse si está ocupado.

### Pthreads
| Llamada | Descripción |
|---|---|
| `pthread_mutex_init` / `destroy` | Crear / destruir mutex |
| `pthread_mutex_lock` | Adquirir o bloquearse |
| `pthread_mutex_trylock` | Adquirir o fallar sin bloquearse |
| `pthread_mutex_unlock` | Liberar |

## Variables de condición
Permiten que un hilo se bloquee hasta que se cumpla una condición. Se usan **siempre junto con un mutex**.

- `pthread_cond_wait(&cond, &mutex)`: libera el mutex atómicamente y se bloquea.
- `pthread_cond_signal(&cond)`: despierta a un hilo.
- `pthread_cond_broadcast(&cond)`: despierta a todos.

A diferencia de los semáforos, **no tienen memoria**: si se señala una variable de condición sin nadie esperando, la señal se pierde. Por eso se espera siempre dentro de un `while` que revisa la condición.

## Monitores
Propuestos por **Hoare (1974)** y **Brinch Hansen (1975)**. Un **monitor** es una colección de procedimientos, variables y estructuras de datos agrupados en un módulo especial, con una propiedad clave:

> **Sólo un proceso puede estar activo dentro del monitor en un instante dado.**

La exclusión mutua la garantiza el **compilador**, no el programador, eliminando los errores de orden típicos de los semáforos.

Dentro del monitor se usan variables de condición con `wait` y `signal`. Ante un `signal`:
- **Hoare**: el proceso despertado se ejecuta inmediatamente y el que señaló se suspende.
- **Brinch Hansen**: `signal` debe ser la última instrucción del procedimiento.
- **Mesa**: el que señala continúa; el despertado compite después (requiere `while`).

### Productor-consumidor con monitor (pseudo-Pascal)
```
monitor ProducerConsumer
    condition full, empty;
    integer count;

    procedure insert(item: integer);
    begin
        if count = N then wait(full);
        insert_item(item);
        count := count + 1;
        if count = 1 then signal(empty)
    end;

    function remove: integer;
    begin
        if count = 0 then wait(empty);
        remove = remove_item;
        count := count - 1;
        if count = N - 1 then signal(full)
    end;

    count := 0;
end monitor;
```

### Monitores en Java
Java soporta monitores con `synchronized` en métodos, más `wait()`, `notify()` y `notifyAll()`.

## Limitaciones
Monitores y semáforos asumen memoria compartida. En sistemas distribuidos se usa **paso de mensajes**.

Ver [[Sistemas Operativos/Problemas clásicos de IPC]].

#sistemas-operativos #tanenbaum #concurrencia #mutex
MD,

            'Sistemas Operativos/Problemas clásicos de IPC' => <<<'MD'
Problemas de sincronización usados para probar nuevas primitivas de [[Sistemas Operativos/Concurrencia]].

## Productor-consumidor (búfer limitado)
Dos procesos comparten un búfer de tamaño fijo: el productor inserta y el consumidor extrae. El productor debe dormir si el búfer está lleno y el consumidor si está vacío. La solución clásica usa tres semáforos (`mutex`, `empty`, `full`) — ver [[Sistemas Operativos/Semáforos]] — o un monitor — ver [[Sistemas Operativos/Mutex y monitores]].

## La cena de los filósofos (Dijkstra, 1965)
Cinco filósofos alrededor de una mesa, cada uno con un plato y un tenedor entre cada par. Para comer necesitan **los dos** tenedores adyacentes; alternan entre pensar y comer.

**Solución ingenua** (tomar el izquierdo y luego el derecho): si todos toman el izquierdo a la vez, nadie obtiene el derecho → interbloqueo (ver [[Sistemas Operativos/Interbloqueos]]). Soltar y reintentar tras un tiempo fijo produce **inanición** (*starvation*); con tiempo aleatorio funciona casi siempre, pero no garantizado.

**Solución de Tanenbaum**: un arreglo `state[N]` (PENSANDO, HAMBRIENTO, COMIENDO) y un semáforo por filósofo. Un filósofo sólo pasa a COMIENDO si ninguno de sus vecinos está comiendo.

```c
const int N = 5;
int LEFT(int i)  { return (i + N - 1) % N; }
int RIGHT(int i) { return (i + 1) % N; }
int state[N];
semaphore mutex = 1;
semaphore s[N];       /* uno por filósofo, inicializados en 0 */

void take_forks(int i) {
    down(&mutex);
    state[i] = HUNGRY;
    test(i);            /* intenta obtener ambos tenedores */
    up(&mutex);
    down(&s[i]);        /* se bloquea si no los obtuvo */
}

void put_forks(int i) {
    down(&mutex);
    state[i] = THINKING;
    test(LEFT(i));         /* ¿puede comer el vecino izquierdo? */
    test(RIGHT(i));        /* ¿y el derecho? */
    up(&mutex);
}

void test(int i) {
    if (state[i] == HUNGRY && state[LEFT(i)] != EATING && state[RIGHT(i)] != EATING) {
        state[i] = EATING;
        up(&s[i]);
    }
}
```
Permite el máximo paralelismo posible (dos filósofos comiendo a la vez con N = 5).

## Lectores y escritores (Courtois et al., 1971)
Modela el acceso a una base de datos: muchos **lectores** pueden leer simultáneamente, pero un **escritor** necesita acceso exclusivo.

```c
semaphore mutex = 1;   /* controla el acceso a rc */
semaphore db = 1;      /* controla el acceso a la base de datos */
int rc = 0;            /* lectores leyendo o queriendo leer */

void reader(void) {
    while (TRUE) {
        down(&mutex);
        rc = rc + 1;
        if (rc == 1) down(&db);   /* primer lector bloquea a escritores */
        up(&mutex);
        read_data_base();
        down(&mutex);
        rc = rc - 1;
        if (rc == 0) up(&db);     /* último lector libera */
        up(&mutex);
        use_data_read();
    }
}

void writer(void) {
    while (TRUE) {
        think_up_data();
        down(&db);
        write_data_base();
        up(&db);
    }
}
```
Esta versión da **prioridad a los lectores**: con un flujo constante de lectores, el escritor puede sufrir inanición. Una alternativa hace que los lectores nuevos esperen detrás de un escritor en espera.

## El barbero dormilón
Una barbería con un barbero, una silla de barbero y *n* sillas de espera. Sin clientes, el barbero duerme; un cliente que llega lo despierta o espera, y se va si no hay sillas libres. Es un problema de colas que se resuelve con semáforos `customers`, `barbers` y `mutex`, más un contador `waiting`.

#sistemas-operativos #tanenbaum #concurrencia
MD,

            'Sistemas Operativos/Planificación' => <<<'MD'
Cuando varios [[Sistemas Operativos/Procesos]] o [[Sistemas Operativos/Hilos]] están listos, el **planificador** (*scheduler*) decide cuál se ejecuta, usando un **algoritmo de planificación**.

## Comportamiento de los procesos
- **Limitados por CPU** (*CPU-bound*): ráfagas largas de cómputo, poca E/S.
- **Limitados por E/S** (*I/O-bound*): ráfagas cortas de CPU, mucha E/S.

A medida que las CPUs se vuelven más rápidas que los discos, los procesos tienden a ser cada vez más limitados por E/S.

## ¿Cuándo planificar?
1. Al crear un proceso.
2. Al terminar un proceso.
3. Cuando un proceso se bloquea (E/S, semáforo...).
4. Al ocurrir una interrupción de E/S.
5. En cada interrupción de reloj (si es apropiativo).

- **No apropiativo** (*non-preemptive*): el proceso corre hasta que se bloquea o cede la CPU.
- **Apropiativo** (*preemptive*): el proceso corre como máximo un tiempo fijo (*quantum*).

## Metas según el entorno

| Todos los sistemas | Por lotes | Interactivos | Tiempo real |
|---|---|---|---|
| Equidad | Rendimiento (trabajos/hora) | Tiempo de respuesta | Cumplir plazos |
| Aplicar políticas | Tiempo de retorno | Proporcionalidad | Predictibilidad |
| Balance (todo ocupado) | Uso de CPU | | |

## Algoritmos para sistemas por lotes
- **FCFS** (*First-Come, First-Served*): una cola FIFO, no apropiativo. Simple, pero un proceso largo retrasa a todos.
- **SJF** (*Shortest Job First*): ejecuta primero el trabajo más corto; **óptimo** en tiempo de retorno promedio cuando todos están disponibles a la vez.
  - Con trabajos de 8, 4, 4, 4 min: en orden FCFS el retorno promedio es 14 min; con SJF, 11 min.
- **SRTN** (*Shortest Remaining Time Next*): versión apropiativa de SJF.

## Algoritmos para sistemas interactivos
- **Round Robin**: cada proceso recibe un **quantum**; al agotarse, va al final de la cola. Un quantum muy corto genera demasiados cambios de contexto; uno muy largo empeora la respuesta. Un valor razonable es 20–50 ms.
- **Por prioridad**: se ejecuta el proceso listo con mayor prioridad. Riesgo de **inanición**, mitigado reduciendo la prioridad con el tiempo (*aging*). Asignar prioridad 1/f (f = fracción del quantum usada) favorece a procesos de E/S.
- **Múltiples colas**: clases de prioridad con quantums crecientes (CTSS).
- **Proceso más corto a continuación**: estimación por **envejecimiento**: T = a·T₀ + (1−a)·T₁.
- **Planificación garantizada**: con *n* procesos, cada uno recibe 1/n de la CPU.
- **Por lotería**: cada proceso tiene boletos; se sortea uno en cada decisión. Simple y proporcional.
- **Por partes equitativas** (*fair-share*): reparte la CPU por usuario, no por proceso.

## Sistemas de tiempo real
- **Duro** (*hard*): los plazos son absolutos. **Suave** (*soft*): fallar ocasionalmente es tolerable.
- Con *m* eventos periódicos, cada uno con periodo Pᵢ y CPU Cᵢ, el sistema es **planificable** si: **Σ Cᵢ / Pᵢ ≤ 1**.

## Política vs mecanismo
El kernel ofrece el **mecanismo** (p. ej. prioridades); el proceso padre o el usuario puede fijar la **política**.

## Planificación de hilos
Con hilos de usuario, el kernel elige el proceso y la biblioteca el hilo. Con hilos de kernel, el kernel elige directamente el hilo, a mayor coste de cambio de contexto pero con la ventaja de que un hilo bloqueado no detiene al proceso.

## En la práctica
Linux usa **CFS** (*Completely Fair Scheduler*), un árbol rojo-negro ordenado por tiempo de CPU virtual (y desde 6.6, **EEVDF**). Windows usa 32 niveles de prioridad con aumentos dinámicos.

#sistemas-operativos #tanenbaum #planificacion
MD,

            'Sistemas Operativos/Interbloqueos' => <<<'MD'
Un conjunto de procesos está en **interbloqueo** (*deadlock*) si cada proceso del conjunto espera un evento que sólo puede causar otro proceso del conjunto. Aparece con frecuencia al usar [[Sistemas Operativos/Semáforos]] y [[Sistemas Operativos/Mutex y monitores]] en orden incorrecto.

## Recursos
- **Apropiativos**: pueden quitarse al proceso sin efectos negativos (p. ej. memoria).
- **No apropiativos**: no pueden quitarse sin que falle el cómputo (p. ej. una impresora a medio imprimir).

Uso de un recurso: **solicitar → usar → liberar**.

## Condiciones de Coffman (1971)
Las cuatro deben cumplirse para que haya interbloqueo:

1. **Exclusión mutua**: cada recurso está asignado a un solo proceso o disponible.
2. **Contención y espera** (*hold and wait*): un proceso que tiene recursos puede pedir más.
3. **No apropiación**: los recursos no se pueden quitar a la fuerza.
4. **Espera circular**: existe una cadena circular de dos o más procesos, cada uno esperando un recurso del siguiente.

## Grafos de asignación de recursos
Procesos como círculos, recursos como cuadrados. Arista recurso → proceso: el recurso está asignado. Arista proceso → recurso: el proceso lo solicita. **Un ciclo en el grafo** (con una instancia por recurso) indica interbloqueo.

## Estrategias

### 1. Ignorar el problema (algoritmo del avestruz)
Si los interbloqueos son raros y el coste de evitarlos alto, se ignoran. Es lo que hacen la mayoría de los SO de propósito general.

### 2. Detección y recuperación
- Una instancia por recurso: buscar ciclos en el grafo.
- Varias instancias: usar la matriz de asignación actual **C**, la de solicitudes **R**, el vector de existentes **E** y el de disponibles **A**; buscar procesos cuyas solicitudes puedan satisfacerse.
- Recuperación: **apropiación**, **retroceso** (*rollback* a un *checkpoint*) o **eliminación de procesos**.

### 3. Evitación (*avoidance*)
Conceder recursos sólo si el sistema queda en un **estado seguro**: uno donde existe un orden en que todos los procesos pueden terminar.

**Algoritmo del banquero** (Dijkstra, 1965): como un banquero que concede créditos, comprueba antes de cada asignación que el estado resultante sea seguro. Problema: requiere conocer de antemano las necesidades máximas, algo raro en la práctica.

### 4. Prevención
Atacar una de las cuatro condiciones:

| Condición | Enfoque |
|---|---|
| Exclusión mutua | Usar *spooling* para todo (ej. un demonio de impresión) |
| Contención y espera | Solicitar todos los recursos al inicio |
| No apropiación | Virtualizar recursos |
| Espera circular | **Ordenar numéricamente los recursos** y pedirlos en orden |

La última es la más práctica: si todos los hilos toman los candados en el mismo orden global, no puede haber ciclos.

## Problemas relacionados
- **Bloqueo en dos fases** (bases de datos): fase de adquisición y fase de liberación.
- **Interbloqueos de comunicación**: A espera respuesta de B que nunca llega; se resuelven con *timeouts*.
- **Livelock**: los procesos no están bloqueados pero no progresan (ej. dos personas que se apartan hacia el mismo lado en un pasillo).
- **Inanición** (*starvation*): un proceso nunca obtiene el recurso aunque no haya interbloqueo; se evita con FCFS.

Ver [[Sistemas Operativos/Problemas clásicos de IPC]] (la cena de los filósofos).

#sistemas-operativos #tanenbaum #concurrencia #deadlock
MD,

            'Sistemas Operativos/Memoria virtual' => <<<'MD'
La memoria es un recurso que el [[Sistemas Operativos]] debe administrar. La **jerarquía de memoria** va de registros y caché (rápidos, pequeños, caros) a memoria principal y disco (lentos, grandes, baratos).

## Sin abstracción
Los primeros sistemas exponían la memoria física directamente: un programa podía sobrescribir al SO y era difícil ejecutar varios a la vez. Se usaron **registros base y límite** para dar a cada proceso su propio **espacio de direcciones** — la abstracción de memoria que equivale al proceso para la CPU.

## Intercambio (*swapping*)
Llevar procesos completos a disco y traerlos de vuelta. Genera huecos en memoria (**fragmentación externa**), que pueden compactarse. El espacio libre se administra con **mapas de bits** o **listas ligadas** usando algoritmos como *first fit*, *next fit*, *best fit*, *worst fit* y *quick fit*.

## Memoria virtual
Idea (Fotheringham, 1961): cada programa tiene su propio espacio de direcciones, dividido en **páginas**, que no necesitan estar todas en memoria física. Cuando se accede a una página ausente ocurre un **fallo de página** y el SO la trae de disco.

### Paginación
- Direcciones **virtuales** → la **MMU** (*Memory Management Unit*) → direcciones **físicas**.
- El espacio virtual se divide en **páginas**; la memoria física en **marcos de página** del mismo tamaño (típicamente 4 KB).
- La dirección virtual se divide en **número de página** y **desplazamiento**.

### Entrada de la tabla de páginas
| Campo | Uso |
|---|---|
| Número de marco | Dónde está la página en memoria física |
| Bit presente/ausente | ¿Está en memoria? |
| Protección | Lectura/escritura/ejecución |
| Bit modificado (*dirty*) | Si se escribió, debe guardarse en disco antes de desalojarla |
| Bit referenciado | Si se accedió recientemente |
| Caché deshabilitada | Para páginas mapeadas a dispositivos |

### Acelerar la paginación
- **TLB** (*Translation Lookaside Buffer*): una caché asociativa en la MMU que guarda traducciones recientes; la mayoría de los accesos aciertan.
- **Tablas de páginas multinivel**: evitan tener en memoria la tabla completa (x86-64 usa 4 o 5 niveles).
- **Tablas de páginas invertidas**: una entrada por marco físico en lugar de por página virtual.

## Algoritmos de reemplazo de páginas
Cuando hay un fallo de página y no hay marcos libres, hay que desalojar una página:

| Algoritmo | Idea | Comentario |
|---|---|---|
| **Óptimo** (Belady) | Desalojar la página que se usará más tarde | No implementable; sirve de referencia |
| **NRU** | Clases según bits R y M; desalojar de la clase más baja | Simple, aceptable |
| **FIFO** | Desalojar la página más antigua | Puede quitar páginas muy usadas; sufre la **anomalía de Belady** |
| **Segunda oportunidad** | FIFO, pero si R = 1 se limpia y se mueve al final | Mejora a FIFO |
| **Reloj** | Segunda oportunidad con una lista circular | Muy usado en la práctica |
| **LRU** | Desalojar la menos usada recientemente | Excelente pero costoso de implementar exactamente |
| **NFU / Envejecimiento** | Aproxima LRU con contadores desplazados | Buena aproximación eficiente |
| **Conjunto de trabajo** | Desalojar páginas fuera del conjunto de trabajo | Costoso |
| **WSClock** | Conjunto de trabajo + reloj | Buena opción en la práctica |

## Conceptos clave
- **Conjunto de trabajo** (*working set*): las páginas que un proceso usa en un intervalo reciente. Si no cabe en memoria, ocurre **hiperpaginación** (*thrashing*).
- **Localidad de referencia**: los programas tienden a acceder a regiones cercanas.
- **Paginación por demanda** vs **prepaginación**.
- **Políticas locales vs globales** de asignación de marcos; algoritmo **PFF** (frecuencia de fallos de página).
- **Páginas compartidas** y **copy-on-write**, clave para que `fork` sea barato (ver [[Sistemas Operativos/Llamadas al sistema]]).

## Segmentación
Varios espacios de direcciones independientes (**segmentos**) por proceso, de tamaño variable: código, datos, pila. Facilita compartir y proteger. MULTICS y el Intel x86 (modo protegido) combinaban segmentación con paginación.

#sistemas-operativos #tanenbaum #memoria
MD,

            'Sistemas Operativos/Sistemas de archivos' => <<<'MD'
Los archivos son la abstracción que el [[Sistemas Operativos]] ofrece para el almacenamiento persistente, igual que los [[Sistemas Operativos/Procesos]] abstraen la CPU y los espacios de direcciones la memoria. Requisitos: guardar mucha información, que sobreviva al proceso, y que varios procesos accedan a ella a la vez.

## Archivos
- **Nombres** y extensiones (en UNIX sólo convenciones).
- **Estructura**: secuencia de bytes (UNIX, Windows), de registros, o árbol.
- **Tipos**: regulares, directorios, especiales de caracteres y de bloques.
- **Acceso**: secuencial o aleatorio.
- **Atributos** (metadatos): protección, propietario, tamaño, fechas...
- **Operaciones**: `create`, `delete`, `open`, `close`, `read`, `write`, `append`, `seek`, `get attributes`, `rename` — ver [[Sistemas Operativos/Llamadas al sistema]].

## Directorios
- Un solo nivel → jerárquicos (árbol).
- **Rutas absolutas** (`/usr/ast/mailbox`) y **relativas** al directorio de trabajo; `.` y `..`.

## Implementación

### Distribución en disco
El disco se divide en particiones; el sector 0 contiene el **MBR**. Cada partición suele tener: bloque de arranque, **superbloque**, gestión de espacio libre, i-nodos, directorio raíz y archivos.

### Asignación de bloques
| Método | Ventajas | Desventajas |
|---|---|---|
| **Contigua** | Simple, lectura secuencial excelente | Fragmentación externa; hay que conocer el tamaño final (bien para CD/DVD) |
| **Lista ligada** | Sin fragmentación externa | Acceso aleatorio muy lento |
| **Lista ligada con tabla (FAT)** | Acceso aleatorio rápido con la tabla en memoria | La tabla completa en memoria no escala |
| **i-nodos** | Sólo en memoria los i-nodos de archivos abiertos | Bloques indirectos para archivos grandes |

El **i-nodo** contiene los atributos y las direcciones de los bloques, más punteros a bloques **indirectos simples, dobles y triples**.

### Directorios
Una entrada de directorio asocia un nombre con un i-nodo (UNIX) o contiene los atributos directamente (FAT). Para nombres largos se usan entradas de longitud variable o un *heap*; para directorios grandes, tablas hash.

### Archivos compartidos
- **Enlaces duros**: varias entradas apuntan al mismo i-nodo (contador de enlaces).
- **Enlaces simbólicos**: un archivo que contiene la ruta de otro.

## Fiabilidad y rendimiento
- **Sistemas con bitácora** (*journaling*): registran lo que se va a hacer antes de hacerlo; las operaciones deben ser **idempotentes**. Ejemplos: NTFS, ext4.
- **Sistemas estructurados como registro** (LFS): todo el disco como un registro secuencial.
- **Sistemas de archivos virtuales (VFS)**: una capa común que integra varios sistemas de archivos.
- **Caché de bloques** (*buffer cache*), **lectura anticipada** y reducción del movimiento del brazo.
- **Respaldos**: físicos vs lógicos, completos vs incrementales.
- **Consistencia**: `fsck` verifica bloques e i-nodos tras una caída.

## Ejemplos
MS-DOS/FAT-32, UNIX V7, ext2/ext3/ext4 (Linux), NTFS (Windows), ISO 9660 (CD-ROM).

#sistemas-operativos #tanenbaum #archivos
MD,

            'Sistemas Operativos/Entrada y salida' => <<<'MD'
Una de las funciones principales del [[Sistemas Operativos]] es controlar los dispositivos de E/S: emitir comandos, atrapar interrupciones, manejar errores y ofrecer una interfaz sencilla e independiente del dispositivo.

## Hardware de E/S
- **Dispositivos de bloque** (discos, SSD): datos en bloques direccionables de tamaño fijo.
- **Dispositivos de caracteres** (teclado, ratón, red): flujo de caracteres no direccionable.
- **Controladores de dispositivo**: la parte electrónica; el SO habla con el controlador, no con el dispositivo.

### Comunicación con el controlador
- **Puertos de E/S** separados (instrucciones `IN`/`OUT`).
- **E/S mapeada a memoria**: los registros del controlador aparecen como direcciones de memoria.
- Esquemas híbridos (x86).

### DMA (Acceso Directo a Memoria)
El controlador DMA transfiere bloques entre dispositivo y memoria sin ocupar la CPU, y genera una interrupción al terminar.

### Interrupciones
Al terminar su trabajo, el dispositivo envía una señal al **controlador de interrupciones**, que se la pasa a la CPU con un número que indexa el **vector de interrupciones**. Las interrupciones **precisas** dejan la máquina en un estado bien definido.

## Principios del software de E/S
Metas: **independencia del dispositivo**, nombres uniformes, manejo de errores lo más cerca posible del hardware, transferencias síncronas (bloqueantes) vs asíncronas, uso de búferes, y dispositivos compartidos vs dedicados.

Tres formas de hacer E/S:
1. **E/S programada**: la CPU hace todo y espera activamente (*polling*).
2. **E/S controlada por interrupciones**: la CPU hace otra cosa hasta que el dispositivo interrumpe.
3. **E/S con DMA**: el controlador DMA hace la transferencia y sólo interrumpe al final.

## Capas del software de E/S

```
┌──────────────────────────────────────┐
│ Software de E/S en espacio de usuario│  printf, spooling
├──────────────────────────────────────┤
│ Software independiente del dispositivo│ nombres, búferes, errores
├──────────────────────────────────────┤
│ Drivers de dispositivo               │  código específico
├──────────────────────────────────────┤
│ Manejadores de interrupciones        │  despiertan al driver
├──────────────────────────────────────┤
│ Hardware                             │
└──────────────────────────────────────┘
```

- **Manejadores de interrupciones**: guardan el estado, reconocen la interrupción y desbloquean al driver (con [[Sistemas Operativos/Semáforos]] o variables de condición).
- **Drivers**: código específico del dispositivo, normalmente dentro del [[Sistemas Operativos/Kernel]] (o en espacio de usuario en un microkernel).
- **Software independiente del dispositivo**: interfaz uniforme, búferes, reporte de errores, asignación de dispositivos dedicados, tamaño de bloque uniforme.
- **Espacio de usuario**: bibliotecas y el **spooling** (un demonio y un directorio de spool para dispositivos dedicados como la impresora).

## Discos
- **Discos magnéticos**: cilindros, pistas y sectores. Tiempo de acceso = búsqueda + latencia rotacional + transferencia.
- **Algoritmos de planificación del brazo**: FCFS, **SSF** (búsqueda más corta primero) y el **algoritmo del elevador** (SCAN).
- **RAID**: niveles 0 a 6 para rendimiento y redundancia.
- **SSD**: sin partes móviles; la planificación del brazo pierde sentido.

## Relojes
El reloj de hardware genera interrupciones periódicas (*ticks*); el software de reloj mantiene la hora, controla quantums para la [[Sistemas Operativos/Planificación]], contabiliza el uso de CPU y gestiona alarmas y temporizadores guardián (*watchdog*).

## Gestión de energía
Apagar o suspender dispositivos inactivos, escalado de voltaje y frecuencia de la CPU.

#sistemas-operativos #tanenbaum #entrada-salida
MD,
        ];
    }
}
