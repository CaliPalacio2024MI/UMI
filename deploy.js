import { spawn } from "child_process";
import { existsSync, copyFileSync } from "fs";
import { join } from "path";
import chalk from "chalk";

import { createInterface } from "readline";

// --- CONFIGURACIÓN ---
// Ruta del binario de PHP en el VPS (FastPanel)
const PHP_BIN = "/opt/php84/bin/php";

// Definición de Pasos con categorías
const STEPS = [
    {
        name: "Verificar Archivo de Entorno (.env)",
        file: ".env", // Check existence
        tags: ["initial"],
        action: async () => {
            if (!existsSync(".env")) {
                console.log(
                    chalk.yellow(
                        "⚠️  ¡No se encontró .env! Copiando .env.production.example...",
                    ),
                );
                if (existsSync(".env.production.example")) {
                    copyFileSync(".env.production.example", ".env");
                    console.log(
                        chalk.green(
                            "✅ Copiado .env.production.example a .env",
                        ),
                    );
                } else {
                    throw new Error(
                        "¡Faltan .env y .env.production.example! No se puede continuar.",
                    );
                }
            } else {
                console.log(chalk.green("✅ Archivo .env encontrado."));
            }
        },
    },
    {
        name: "Instalar Dependencias de PHP (Composer)",
        tags: ["initial", "update"],
        action: async () => {
            console.log(
                chalk.cyan(
                    "Usando composer global en /usr/local/bin/composer con PHP 8.4",
                ),
            );
            await runCommand(PHP_BIN, [
                "/usr/local/bin/composer",
                "install",
                "--no-dev",
                "--optimize-autoloader",
                "--classmap-authoritative",
                "--no-interaction",
            ]);
        },
    },
    {
        name: "Generar Clave de Aplicación (si falta)",
        tags: ["initial", "update"],
        confirm: true,
        action: async () => {
            const fs = await import("fs");
            let envContent = "";
            try {
                envContent = fs.readFileSync(".env", "utf-8");
            } catch (err) {
                console.log(chalk.gray("   (No se pudo leer .env)"));
            }

            if (
                envContent.includes("APP_KEY=base64:YOUR_GENERATED_KEY_HERE") ||
                envContent.includes("APP_KEY=") ||
                !envContent.includes("APP_KEY")
            ) {
                console.log(
                    chalk.yellow("🔑 Generando nueva clave de aplicación..."),
                );
                await runCommand(PHP_BIN, [
                    "artisan",
                    "key:generate",
                    "--force",
                ]);
            } else {
                console.log(chalk.green("✅ APP_KEY ya configurada."));
            }
        },
    },
    {
        name: "Generar Clave JWT (si falta)",
        tags: ["initial"],
        action: async () => {
            const fs = await import("fs");
            let envContent = "";
            try {
                envContent = fs.readFileSync(".env", "utf-8");
            } catch (e) {}

            // Check if JWT_SECRET exists and is not the placeholder or empty
            if (
                envContent.includes(
                    "JWT_SECRET=generar_con_artisan_jwt_secret",
                ) ||
                !envContent.includes("JWT_SECRET") ||
                envContent.includes("JWT_SECRET=")
            ) {
                console.log(chalk.yellow("🔒 Generando secreto JWT..."));
                // jwt:secret might require user interaction if not forced, but usually --force works or just running it.
                await runCommand(PHP_BIN, ["artisan", "jwt:secret", "--force"]);
            } else {
                console.log(chalk.green("✅ JWT_SECRET ya configurado."));
            }
        },
    },
    {
        name: "Instalar Dependencias de Node (NPM)",
        tags: ["initial", "update"],
        command: "npm",
        args: ["install"],
    },
    {
        name: "Construir Assets del Frontend (Build)",
        tags: ["initial", "update"],
        command: "npm",
        args: ["run", "build"],
    },
    {
        name: "Ejecutar Migraciones (Forzado)",
        tags: ["initial", "update"],
        command: PHP_BIN,
        args: ["artisan", "migrate", "--force"],
        confirm: true,
    },
    {
        name: "Poblar Base de Datos (Seed Forzado)",
        tags: ["initial", "update"], // También en modo actualización
        command: PHP_BIN,
        args: ["artisan", "db:seed", "--force"],
        confirm: true,
    },
    {
        name: "Limpiar y Cachear Configuración",
        tags: ["initial", "update"],
        command: PHP_BIN,
        args: ["artisan", "config:cache"],
    },
    {
        name: "Cachear Rutas",
        tags: ["initial", "update"],
        command: PHP_BIN,
        args: ["artisan", "route:cache"],
    },
    {
        name: "Cachear Vistas",
        tags: ["initial", "update"],
        command: PHP_BIN,
        args: ["artisan", "view:cache"],
    },
    {
        name: "Vincular Storage (Symlink)",
        tags: ["initial"], // Usualmente solo una vez
        command: PHP_BIN,
        args: ["artisan", "storage:link"],
    },
    {
        name: "Crear API Key (Opcional)",
        tags: ["initial", "update"],
        action: async () => {
            const confirmed = await askConfirmation(
                "¿Deseas crear una nueva API Key?",
            );
            if (confirmed) {
                const rl = createRL();
                const name = await new Promise((resolve) => {
                    rl.question(
                        chalk.yellow(
                            "\nNombre para la API Key (ej. ClienteMovil): ",
                        ),
                        (answer) => {
                            rl.close();
                            resolve(answer.trim() || "DefaultClient");
                        },
                    );
                });

                console.log(chalk.cyan(`Generando API Key para: ${name}...`));
                // Assuming make:api-key takes the name as an argument or option.
                // Based on previous context, the command likely signature is `make:api-key {name?}`
                await runCommand(PHP_BIN, ["artisan", "make:api-key", name]);
            } else {
                console.log(chalk.gray("Ignorando creación de API Key."));
            }
        },
    },
];

// --- FUNCIONES DE UTILERÍA ---

const createRL = () =>
    createInterface({ input: process.stdin, output: process.stdout });

const askConfirmation = (question) => {
    return new Promise((resolve) => {
        const rl = createRL();
        rl.question(chalk.yellow(`\n❓ ${question} (s/N): `), (answer) => {
            rl.close();
            resolve(
                answer.toLowerCase() === "s" || answer.toLowerCase() === "si",
            );
        });
    });
};

const showMenu = () => {
    return new Promise((resolve) => {
        console.log("\nSelecciona una opción:");
        console.log(
            chalk.cyan("1.") +
                " 🚀 Despliegue Inicial (Todo desde cero: .env, keys, seeds, etc.)",
        );
        console.log(
            chalk.cyan("2.") +
                " 🔄 Aplicar Actualización (NPM, Composer, Migraciones, Caché)",
        );
        console.log(chalk.cyan("3.") + " ❌ Salir");

        const rl = createRL();
        rl.question(chalk.green("\n> Opción (1-3): "), (answer) => {
            rl.close();
            resolve(answer.trim());
        });
    });
};

const runCommand = (command, args) => {
    return new Promise((resolve, reject) => {
        console.log(chalk.gray(`> ${command} ${args.join(" ")}`));
        const child = spawn(command, args, { stdio: "inherit", shell: true });
        child.on("close", (code) => {
            if (code === 0) resolve();
            else reject(new Error(`El comando falló con código ${code}`));
        });
    });
};

async function main() {
    console.clear();
    console.log(
        chalk.green.bold("╔════════════════════════════════════════════════╗"),
    );
    console.log(
        chalk.green.bold("║    🚀 SCRIPT DE DESPLIEGUE VPS (PHP 8.4)       ║"),
    );
    console.log(
        chalk.green.bold("╚════════════════════════════════════════════════╝"),
    );
    console.log(chalk.cyan(`Ruta PHP: ${PHP_BIN}`));

    // Verificar si corre en Windows
    if (process.platform === "win32") {
        console.log(chalk.yellow("⚠️  Advertencia: Ejecutando en Windows."));
    }

    const option = await showMenu();
    let mode = "";

    if (option === "1") {
        mode = "initial";
        console.log(chalk.blue.bold("\n--- MODO: DESPLIEGUE INICIAL ---"));
    } else if (option === "2") {
        mode = "update";
        console.log(chalk.blue.bold("\n--- MODO: APLICAR ACTUALIZACIÓN ---"));
    } else {
        console.log(chalk.gray("Saliendo..."));
        process.exit(0);
    }

    for (const step of STEPS) {
        // Filtrar pasos según el modo
        if (!step.tags.includes(mode)) {
            continue;
        }

        console.log(chalk.magenta.bold(`\n🔹 [PASO] ${step.name}...`));

        if (step.confirm) {
            const confirmed = await askConfirmation(
                `¿Deseas ejecutar: ${step.name}?`,
            );
            if (!confirmed) {
                console.log(chalk.gray(`⏭️  Saltando paso: ${step.name}`));
                continue;
            }
        }

        try {
            if (step.action) {
                await step.action();
            } else {
                await runCommand(step.command, step.args);
            }
            console.log(chalk.green(`✅ ${step.name} Completado.`));
        } catch (e) {
            console.error(chalk.red(`\n❌ Falló el paso: ${e.message}`));
            if (step.name.includes("Storage") && mode === "initial") {
                console.log(
                    chalk.gray(
                        "(Se ignora error de Storage, puede que ya exista)",
                    ),
                );
            } else {
                console.log(
                    chalk.red("Deteniendo despliegue por error crítico."),
                );
                process.exit(1);
            }
        }
    }

    console.log(chalk.green.bold("\n✨ ¡DESPLIEGUE COMPLETADO CON ÉXITO! ✨"));
    console.log(
        chalk.white("Recuerda verificar permisos y logs si algo falló."),
    );
}

main();
