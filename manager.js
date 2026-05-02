import inquirer from 'inquirer';
import chalk from 'chalk';
import { spawn, exec } from 'child_process';

// ===============================
// HELPERS
// ===============================
const run = (cmd, args) =>
    new Promise((resolve, reject) => {
        const p = spawn(cmd, args, { stdio: 'inherit', shell: false });
        p.on('close', code => code === 0 ? resolve() : reject());
    });

const runSilent = (cmd, args) =>
    new Promise((resolve) => {
        exec(`${cmd} ${args.join(' ')}`, (err, stdout) => {
            resolve(stdout || '');
        });
    });

const pause = async () => {
    await inquirer.prompt([{ name: 'x', message: 'Enter para continuar' }]);
};

// ===============================
// GIT CORE
// ===============================
const getBranches = async () => {
    const out = await runSilent('git', ['branch', '-a']);
    return out
        .split('\n')
        .map(b => b.trim().replace('* ', '').replace('remotes/origin/', ''))
        .filter(b => b && !b.includes('HEAD'))
        .filter((v, i, a) => a.indexOf(v) === i);
};

const getCurrent = async () => {
    const out = await runSilent('git', ['rev-parse', '--abbrev-ref', 'HEAD']);
    return out.trim();
};

// ===============================
// STASH
// ===============================
const getStashes = async () => {
    const out = await runSilent('git', ['stash', 'list']);
    return out.split('\n').filter(Boolean).map(line => {
        const match = line.match(/^(stash@\{\d+\})/);
        return { label: line, ref: match ? match[1] : line };
    });
};

async function stashList() {
    const stashes = await getStashes();

    if (!stashes.length) {
        console.log(chalk.yellow('\n⚠️ No hay stashes'));
    } else {
        console.log(chalk.cyan('\n📚 Stashes:\n'));
        stashes.forEach((s, i) => console.log(`${i + 1}. ${s.label}`));
    }

    await pause();
}

async function stashCreate() {
    const { msg } = await inquirer.prompt([{
        name: 'msg',
        message: 'Mensaje del stash:',
        default: `stash-${new Date().toISOString().slice(0,19).replace(/[:T]/g,'-')}`
    }]);

    const { include } = await inquirer.prompt([{
        type: 'confirm',
        name: 'include',
        message: '¿Incluir archivos nuevos?',
        default: true
    }]);

    const args = ['stash', 'push'];
    if (include) args.push('-u');
    args.push('-m', msg);

    await run('git', args);

    console.log(chalk.green('\n✅ Stash creado'));
    await pause();
}

async function stashRestore() {
    const stashes = await getStashes();

    if (!stashes.length) {
        console.log(chalk.yellow('⚠️ No hay stash'));
        return pause();
    }

    const { stash } = await inquirer.prompt([{
        type: 'select',
        name: 'stash',
        message: 'Selecciona stash:',
        choices: stashes.map(s => ({ name: s.label, value: s.ref }))
    }]);

    const { mode } = await inquirer.prompt([{
        type: 'select',
        name: 'mode',
        message: '¿Cómo restaurar?',
        choices: [
            { name: 'Aplicar (mantener stash)', value: 'apply' },
            { name: 'Aplicar y eliminar (pop)', value: 'pop' }
        ]
    }]);

    await run('git', ['stash', mode, stash]); // 🔥 sin comillas

    console.log(chalk.green('\n✅ Stash restaurado'));
    await pause();
}

async function stashMenu() {
    const { action } = await inquirer.prompt([{
        type: 'select',
        name: 'action',
        message: 'Opciones de Stash:',
        choices: [
            'Listar stash',
            'Crear stash',
            'Restaurar stash',
            'Volver'
        ]
    }]);

    switch (action) {
        case 'Listar stash':
            await stashList();
            break;
        case 'Crear stash':
            await stashCreate();
            break;
        case 'Restaurar stash':
            await stashRestore();
            break;
        case 'Volver':
            return;
    }

    await stashMenu();
}

// ===============================
// FUNCIONES PRINCIPALES
// ===============================
async function listBranches() {
    const branches = await getBranches();
    console.log(chalk.cyan('\n🌿 Ramas:\n'));
    branches.forEach(b => console.log(' -', b));
    await pause();
}

async function compareBranches() {
    const current = await getCurrent();
    const branches = await getBranches();

    const { target } = await inquirer.prompt([{
        type: 'select',
        name: 'target',
        message: `Comparar ${current} con:`,
        choices: branches.filter(b => b !== current)
    }]);

    await run('git', ['log', `${current}..origin/${target}`, '--oneline', '--graph', '--name-status']);
    await pause();
}

async function pullFromBranch() {
    const branches = await getBranches();

    const { branch } = await inquirer.prompt([{
        type: 'select',
        name: 'branch',
        message: 'Traer cambios desde:',
        choices: branches
    }]);

    await run('git', ['pull', 'origin', branch]);
    await pause();
}

async function updateCurrentBranch() {
    const current = await getCurrent();
    await run('git', ['pull', 'origin', current]);
    await pause();
}

async function commitAndPush() {
    const { msg } = await inquirer.prompt([{
        name: 'msg',
        message: 'Mensaje del commit:',
        validate: v => v.length > 0
    }]);

    await run('git', ['add', '.']);
    await run('git', ['commit', '-m', msg]);

    const current = await getCurrent();
    await run('git', ['push', 'origin', current]);

    await pause();
}

// ===============================
// MENU
// ===============================
async function mainMenu() {
    console.clear();

    console.log(chalk.cyan.bold(`
╔══════════════════════════════╗
║        GIT MANAGER          ║
╚══════════════════════════════╝
`));

    const { action } = await inquirer.prompt([{
        type: 'select',
        name: 'action',
        message: 'Selecciona:',
        choices: [
            'Listar ramas',
            'Comparar ramas',
            'Traer cambios de otra rama',
            'Actualizar rama actual',
            'Commit + Push',
            'Stash',
            'Salir'
        ]
    }]);

    switch (action) {
        case 'Listar ramas': await listBranches(); break;
        case 'Comparar ramas': await compareBranches(); break;
        case 'Traer cambios de otra rama': await pullFromBranch(); break;
        case 'Actualizar rama actual': await updateCurrentBranch(); break;
        case 'Commit + Push': await commitAndPush(); break;
        case 'Stash': await stashMenu(); break;
        case 'Salir': process.exit(0);
    }

    await mainMenu();
}

mainMenu();