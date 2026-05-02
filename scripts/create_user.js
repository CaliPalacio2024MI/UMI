import fs from 'fs';
import path from 'path';
import readline from 'readline';
import { fileURLToPath } from 'url';
import mysql from 'mysql2/promise';
import bcrypt from 'bcryptjs';

/**
 * CONFIGURACIÓN Y RUTAS
 */
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const BASE_PATH = path.resolve(__dirname, '..');
const ENV_FILE = path.join(BASE_PATH, '.env');

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
};

function getHashedPassword(password) {
    let hash = bcrypt.hashSync(password, 10);
    hash = hash.replace(/^\$2a\$/, '$2y$');
    hash = hash.replace(/^\$2b\$/, '$2y$');
    return hash;
}

/**
 * ACCIONES
 */

async function selectMasterUser(connection) {
    const [masterUsers] = await connection.execute(
        'SELECT u.id, u.email, u.nombre, u.apellido_paterno, u.role_id, u.institution_id FROM users u JOIN roles r ON u.role_id = r.id WHERE r.name LIKE ?',
        ['%master%']
    );

    if (masterUsers.length === 0) {
        console.error('\n❌ No se encontraron usuarios con rol Master.');
        return null;
    }

    console.log('\nUsuarios Master encontrados:');
    masterUsers.forEach((user, idx) => {
        console.log(`${idx + 1}. ${user.nombre} ${user.apellido_paterno} (${user.email})`);
    });
    console.log(`${masterUsers.length + 1}. <-- Cancelar`);

    const selection = await question('\nSelecciona el número del usuario: ');
    const index = parseInt(selection) - 1;

    if (index === masterUsers.length) return null;
    return masterUsers[index] || null;
}

async function createNewMaster() {
    console.log('\n--- 🆕 CREAR NUEVO USUARIO MASTER ---');
    
    let connection;
    try {
        connection = await mysql.createConnection(dbConfig);
        
        // 1. Obtener datos básicos
        const nombre = await question('Nombre(s): ');
        const apPaterno = await question('Apellido Paterno: ');
        const apMaterno = await question('Apellido Materno: ');
        const email = await question('Email: ');
        const rfc = await question('RFC: ');
        const passwordRaw = await question('Contraseña: ');

        if (!nombre || !apPaterno || !email || !passwordRaw) {
            console.error('\n❌ Error: Datos obligatorios faltantes.');
            return;
        }

        // 2. Obtener Rol Master
        const [roles] = await connection.execute('SELECT id FROM roles WHERE name LIKE ? LIMIT 1', ['%master%']);
        if (roles.length === 0) throw new Error('Rol master no encontrado.');
        const masterRoleId = roles[0].id;

        // 3. Seleccionar Institución Principal
        const [institutions] = await connection.execute('SELECT id, name FROM institutions');
        if (institutions.length === 0) throw new Error('No hay instituciones disponibles.');

        console.log('\nSelecciona la Institución Principal (Propiedad Base):');
        institutions.forEach((inst, idx) => console.log(`${idx + 1}. ${inst.name}`));
        const instChoice = await question('Opción: ');
        const primaryInst = institutions[parseInt(instChoice) - 1];
        if (!primaryInst) throw new Error('Selección de institución principal inválida.');

        // 4. Seleccionar Accesos Adicionales
        console.log('\n¿A qué otras instituciones tendrá acceso?');
        console.log('A. TODAS');
        console.log('N. SOLO LA PRINCIPAL');
        console.log('S. SELECCIONAR ESPECÍFICAS');
        const accessOpt = await question('Opción: ');

        let targetIds = [];
        if (accessOpt.toUpperCase() === 'A') {
            targetIds = institutions.map(i => i.id);
        } else if (accessOpt.toUpperCase() === 'N') {
            targetIds = [primaryInst.id];
        } else if (accessOpt.toUpperCase() === 'S') {
            console.log('\nSelecciona los accesos (la principal se incluirá automáticamente):');
            institutions.forEach((inst, idx) => {
                const label = inst.id === primaryInst.id ? ' [PRINCIPAL]' : '';
                console.log(`${idx + 1}. ${inst.name}${label}`);
            });
            const selection = await question('Ingresa los números separados por coma: ');
            targetIds = selection.split(',').map(s => parseInt(s.trim()) - 1)
                .filter(idx => institutions[idx])
                .map(idx => institutions[idx].id);
            
            if (!targetIds.includes(primaryInst.id)) targetIds.push(primaryInst.id);
        } else {
            console.log('⚠️  Opción inválida, se asignará solo la principal.');
            targetIds = [primaryInst.id];
        }

        const hashedPassword = getHashedPassword(passwordRaw);

        // 5. Insertar Usuario
        console.log('\n📝 Creando usuario...');
        const [userResult] = await connection.execute(
            `INSERT INTO users 
            (nombre, apellido_paterno, apellido_materno, email, RFC, password, role_id, institution_id, is_active, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())`,
            [nombre, apPaterno, apMaterno, email, rfc, hashedPassword, masterRoleId, primaryInst.id]
        );
        const newUserId = userResult.insertId;

        // 6. Insertar Accesos
        console.log(`🔗 Vinculando con ${targetIds.length} instituciones...`);
        for (const instId of targetIds) {
            await connection.execute(
                'INSERT IGNORE INTO institution_user (user_id, institution_id, created_at, updated_at) VALUES (?, ?, NOW(), NOW())', 
                [newUserId, instId]
            );
            await connection.execute(
                'INSERT IGNORE INTO user_roles_institution (user_id, institution_id, role_id, created_at, updated_at, is_active) VALUES (?, ?, ?, NOW(), NOW(), 1)', 
                [newUserId, instId, masterRoleId]
            );
        }

        console.log(`\n✅ Usuario Master creado exitosamente con ID: ${newUserId}`);

    } catch (error) {
        console.error('\n❌ Error:', error.message);
    } finally {
        if (connection) await connection.end();
    }
}

async function resetPassword() {
    let connection;
    try {
        connection = await mysql.createConnection(dbConfig);
        console.log('\n--- 🔑 RENOVAR CONTRASEÑA ---');
        const selectedUser = await selectMasterUser(connection);
        if (!selectedUser) return;
        const newPasswordRaw = await question('Nueva Contraseña: ');
        if (!newPasswordRaw) return;
        const hashedPassword = getHashedPassword(newPasswordRaw);
        await connection.execute('UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?', [hashedPassword, selectedUser.id]);
        console.log(`\n✅ Contraseña actualizada para: ${selectedUser.email}`);
    } catch (error) {
        console.error('\n❌ Error:', error.message);
    } finally {
        if (connection) await connection.end();
    }
}

async function manageAccess() {
    let connection;
    try {
        connection = await mysql.createConnection(dbConfig);
        console.log('\n--- 🏢 GESTIONAR ACCESO A INSTITUCIONES ---');
        const selectedUser = await selectMasterUser(connection);
        if (!selectedUser) return;

        const [allInstitutions] = await connection.execute('SELECT id, name FROM institutions');
        const [currentAccess] = await connection.execute('SELECT institution_id FROM institution_user WHERE user_id = ?', [selectedUser.id]);
        const activeIds = currentAccess.map(row => row.institution_id);

        console.log(`\nAccesos para: ${selectedUser.email}`);
        allInstitutions.forEach((inst, idx) => {
            const isPrimary = inst.id === selectedUser.institution_id;
            const status = activeIds.includes(inst.id) ? '✅' : '❌';
            const label = isPrimary ? ' [PRINCIPAL - REQUERIDA]' : '';
            console.log(`${idx + 1}. ${status} ${inst.name}${label}`);
        });

        console.log('\nOpciones: [A] Todas, [N] Solo Principal, [S] Seleccionar, [C] Cancelar');
        const opt = await question('\nOpción: ');
        if (opt.toUpperCase() === 'C') return;

        let targetIds = [];
        if (opt.toUpperCase() === 'A') {
            targetIds = allInstitutions.map(i => i.id);
        } else if (opt.toUpperCase() === 'N') {
            targetIds = [selectedUser.institution_id];
        } else if (opt.toUpperCase() === 'S') {
            const selection = await question('Números (ej: 1,3): ');
            targetIds = selection.split(',').map(s => parseInt(s.trim()) - 1)
                .filter(idx => allInstitutions[idx])
                .map(idx => allInstitutions[idx].id);
            
            if (!targetIds.includes(selectedUser.institution_id)) {
                targetIds.push(selectedUser.institution_id);
            }
        }

        await connection.execute('DELETE FROM institution_user WHERE user_id = ?', [selectedUser.id]);
        await connection.execute('DELETE FROM user_roles_institution WHERE user_id = ?', [selectedUser.id]);

        for (const instId of targetIds) {
            await connection.execute('INSERT IGNORE INTO institution_user (user_id, institution_id, created_at, updated_at) VALUES (?, ?, NOW(), NOW())', [selectedUser.id, instId]);
            await connection.execute('INSERT IGNORE INTO user_roles_institution (user_id, institution_id, role_id, created_at, updated_at, is_active) VALUES (?, ?, ?, NOW(), NOW(), 1)', [selectedUser.id, instId, selectedUser.role_id]);
        }
        console.log('✅ Accesos actualizados.');
    } catch (error) {
        console.error('\n❌ Error:', error.message);
    } finally {
        if (connection) await connection.end();
    }
}

async function changePrimaryInstitution() {
    let connection;
    try {
        connection = await mysql.createConnection(dbConfig);
        console.log('\n--- 🏫 CAMBIAR INSTITUCIÓN PRINCIPAL ---');
        const selectedUser = await selectMasterUser(connection);
        if (!selectedUser) return;

        const [institutions] = await connection.execute('SELECT id, name FROM institutions');
        console.log(`\nUsuario: ${selectedUser.email}`);
        console.log('Selecciona la nueva Institución Principal:');
        institutions.forEach((inst, idx) => {
            const current = inst.id === selectedUser.institution_id ? ' (Actual)' : '';
            console.log(`${idx + 1}. ${inst.name}${current}`);
        });

        const choice = await question('\nSelección (o C para cancelar): ');
        if (choice.toUpperCase() === 'C') return;

        const newInst = institutions[parseInt(choice) - 1];
        if (!newInst) return;

        await connection.execute('UPDATE users SET institution_id = ? WHERE id = ?', [newInst.id, selectedUser.id]);
        await connection.execute('INSERT IGNORE INTO institution_user (user_id, institution_id, created_at, updated_at) VALUES (?, ?, NOW(), NOW())', [selectedUser.id, newInst.id]);
        await connection.execute('INSERT IGNORE INTO user_roles_institution (user_id, institution_id, role_id, created_at, updated_at, is_active) VALUES (?, ?, ?, NOW(), NOW(), 1)', [selectedUser.id, newInst.id, selectedUser.role_id]);

        console.log(`✅ Institución principal actualizada a: ${newInst.name}`);
    } catch (error) {
        console.error('\n❌ Error:', error.message);
    } finally {
        if (connection) await connection.end();
    }
}

async function showMenu() {
    console.log('\n=========================================');
    console.log('   🛠️  GESTOR DE USUARIOS MASTER (JS)    ');
    console.log('=========================================');
    console.log('1. 👤 Crear Nuevo Usuario Master');
    console.log('2. 🔑 Renovar Contraseña');
    console.log('3. 🏢 Gestionar Accesos (Multi-Propiedad)');
    console.log('4. 🏫 Cambiar Institución Principal');
    console.log('5. 🚪 Salir');
    const choice = await question('\nSelecciona una opción: ');
    switch (choice) {
        case '1': await createNewMaster(); break;
        case '2': await resetPassword(); break;
        case '3': await manageAccess(); break;
        case '4': await changePrimaryInstitution(); break;
        case '5': rl.close(); process.exit(0);
        default: console.log('❌ Opción no válida.'); break;
    }
    await showMenu();
}
showMenu();
