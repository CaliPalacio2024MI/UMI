import fs from 'fs';
import path from 'path';
import { execSync } from 'child_process';
import readline from 'readline';
import { fileURLToPath } from 'url';
import mysql from 'mysql2/promise';

/**
 * CONFIGURACIÓN Y RUTAS
 */
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const BASE_PATH = path.resolve(__dirname, '..');
const BACKUP_DIR = path.join(BASE_PATH, 'database', 'manager', 'backups');
const ORIGINAL_FILE = path.join(BASE_PATH, 'database', 'manager', 'originalfiles', 'umi_mrki.sql');
const ENV_FILE = path.join(BASE_PATH, '.env');

if (!fs.existsSync(BACKUP_DIR)) {
    fs.mkdirSync(BACKUP_DIR, { recursive: true });
}

/**
 * UTILIDADES
 */
const rl = readline.createInterface({
    input: process.stdin,
    output: process.stdout
});

const question = (query) => new Promise((resolve) => rl.question(query, resolve));

function parseEnv() {
    if (!fs.existsSync(ENV_FILE)) {
        console.error('❌ Error: No se encontró el archivo .env');
        process.exit(1);
    }
    const content = fs.readFileSync(ENV_FILE, 'utf8');
    const config = {};
    content.split('\n').forEach(line => {
        const [key, ...value] = line.split('=');
        if (key && value.length > 0) {
            config[key.trim()] = value.join('=').trim().replace(/^['"]|['"]$/g, '');
        }
    });
    return config;
}

const envConfig = parseEnv();
const dbConfig = {
    host: envConfig.DB_HOST || '127.0.0.1',
    user: envConfig.DB_USERNAME,
    password: envConfig.DB_PASSWORD,
    database: envConfig.DB_DATABASE,
    multipleStatements: true // Crítico para ejecutar archivos .sql completos
};

/**
 * ACCIONES
 */

async function createBackup() {
    console.log('\n📦 Iniciando respaldo de base de datos...');
    const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
    const fileName = `backup-${dbConfig.database}-${timestamp}.sql`;
    const filePath = path.join(BACKUP_DIR, fileName);

    try {
        // NOTA: Para el DUMP seguimos usando la herramienta de sistema mysqldump
        // ya que es la forma más eficiente y completa de generar un archivo .sql estructurado.
        execSync(`mysqldump -h ${dbConfig.host} -u ${dbConfig.user} ${dbConfig.database} > "${filePath}"`, {
            env: { ...process.env, MYSQL_PWD: dbConfig.password }
        });
        console.log(`✅ Respaldo creado exitosamente: ${fileName}`);
    } catch (error) {
        console.error('❌ Error al crear el respaldo:', error.message);
    }
}

async function restoreDatabase(filePath) {
    if (!fs.existsSync(filePath)) {
        console.error(`❌ Error: El archivo no existe en ${filePath}`);
        return;
    }

    console.log(`\n⚠️  Restaurando base de datos desde JS (driver mysql2): ${path.basename(filePath)}...`);
    const confirm = await question('¿Estás seguro? Esto SOBRESCRIBIRÁ la base de datos actual (s/N): ');
    
    if (confirm.toLowerCase() !== 's') {
        console.log('🚫 Operación cancelada.');
        return;
    }

    let connection;
    try {
        let sql = fs.readFileSync(filePath, 'utf8');
        
        // Limpiamos el SQL de sentencias CREATE DATABASE y USE que podrían causar errores de permisos
        // si el nombre de la BD en el archivo no coincide con el de .env
        console.log('🧹 Limpiando sentencias de base de datos incompatibles...');
        sql = sql.replace(/CREATE DATABASE\s+(IF NOT EXISTS\s+)?`.*?`.*;/gi, '-- [CREATE DATABASE REMOVED]');
        sql = sql.replace(/USE\s+`.*?`;/gi, '-- [USE DATABASE REMOVED]');

        console.log('🔌 Conectando a la base de datos...');
        connection = await mysql.createConnection(dbConfig);
        
        console.log('🚀 Ejecutando script SQL...');
        await connection.query(sql);
        
        console.log('✅ Restauración completada con éxito.');
    } catch (error) {
        console.error('❌ Error durante la restauración con el driver:', error.message);
    } finally {
        if (connection) await connection.end();
    }
}

async function restoreFromBackup() {
    const files = fs.readdirSync(BACKUP_DIR).filter(f => f.endsWith('.sql'));
    
    if (files.length === 0) {
        console.log('\n📭 No hay respaldos disponibles en la carpeta de backups.');
        return;
    }

    console.log('\n📂 Respaldos disponibles:');
    files.forEach((file, index) => console.log(`${index + 1}. ${file}`));
    
    const choice = await question('\nSelecciona el número de respaldo a restaurar (o Enter para cancelar): ');
    const index = parseInt(choice) - 1;

    if (files[index]) {
        await restoreDatabase(path.join(BACKUP_DIR, files[index]));
    } else {
        console.log('🚫 Selección inválida o cancelada.');
    }
}

async function showMenu() {
    console.log('\n=========================================');
    console.log('   🛠️  GESTOR DE DB (Driver: mysql2)     ');
    console.log('=========================================');
    console.log('1. 📥 Crear Respaldo (usando mysqldump)');
    console.log('2. 📤 Restaurar desde un Respaldo (via mysql2)');
    console.log('3. 🔄 Restaurar desde Archivo Original (via mysql2)');
    console.log('4. 🚪 Salir');
    
    const choice = await question('\nSelecciona una opción: ');

    switch (choice) {
        case '1':
            await createBackup();
            break;
        case '2':
            await restoreFromBackup();
            break;
        case '3':
            await restoreDatabase(ORIGINAL_FILE);
            break;
        case '4':
            console.log('👋 ¡Hasta luego!');
            rl.close();
            process.exit(0);
        default:
            console.log('❌ Opción no válida.');
            break;
    }
    
    await showMenu();
}

showMenu();