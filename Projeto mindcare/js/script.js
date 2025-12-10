/* =========================================================================
   script.js — ARQUIVO DE UTILIDADES E BACK-END SIMULADO (FUNÇÕES GLOBAIS)
   ========================================================================== */

/* -------------------------
   UTILIDADES & SIMULAÇÃO DE API (Mantidas como funções exportáveis)
   ------------------------- */

// Simulação de usuário para testes
const MOCK_USER_USER = {
    id: 1,
    name: "Usuário Comum",
    email: "user@teste.com",
    role: "user"
};
const MOCK_USER_PRO = {
    id: 2,
    name: "Profissional",
    email: "pro@teste.com",
    role: "professional",
    crm: "CRP 12345"
};

/**
 * SIMULAÇÃO de comunicação com o servidor (Substitui o jsonPost real).
 * @param {object} bodyObj - Objeto com a ação (action).
 * @returns {Promise<object>} Resposta JSON SIMULADA.
 */
function jsonPost(bodyObj) {
    console.warn(`[SIMULAÇÃO API] Chamada da ação: ${bodyObj.action}`);

    return new Promise(resolve => {
        setTimeout(() => {
            if (bodyObj.action === 'login' || bodyObj.action === 'register') {
                // Login/Register são tratados diretamente no HTML, mas mantemos o mock básico aqui.
                const isPro = bodyObj.email ? bodyObj.email.includes('pro') : bodyObj.type === 'professional';
                const user = isPro ? MOCK_USER_PRO : MOCK_USER_USER;

                // Atualiza o nome (para o Toast de sucesso)
                if (bodyObj.name) user.name = bodyObj.name;

                resolve({
                    success: true,
                    user: user
                });
            }
            // Mock para salvar humor
            else if (bodyObj.action === 'saveMood') {
                resolve({
                    success: true
                });
            } else {
                resolve({
                    success: true
                });
            }
        }, 100);
    });
}

// Funções para manipulação do estado do usuário via localStorage (REAL)
function saveUserToLocal(userObj) {
    localStorage.setItem("mindcare_user", JSON.stringify(userObj));
}

function getUserFromLocal() {
    try {
        return JSON.parse(localStorage.getItem("mindcare_user") || "null");
    } catch (e) {
        return null;
    }
}

function clearUserLocal() {
    localStorage.removeItem("mindcare_user");
}


// ============================================================
// FUNÇÃO GLOBAL: TOAST NOTIFICATION
// ============================================================
function showToast(message, type = "info") {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;

    let container = document.getElementById('toastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        container.style.position = 'fixed';
        container.style.bottom = '20px';
        container.style.right = '20px';
        container.style.zIndex = '10000';
        document.body.appendChild(container);
    }

    container.appendChild(toast);

    setTimeout(() => {
        // Usa a classe 'fadeOut' do seu styles.css (assumindo que existe)
        toast.classList.add('fading-out');
        setTimeout(() => toast.remove(), 500); // Remove após a transição
    }, 3000);
}

// ============================================================
// FUNÇÃO GLOBAL: ATUALIZAÇÃO DO HEADER (Será chamada em todas as páginas)
// ============================================================
function updateHeaderUI() {
    const user = getUserFromLocal();
    const loginBtn = document.getElementById("loginBtn");
    const registerBtn = document.getElementById("registerBtn");
    const dashboardBtn = document.getElementById("dashboardBtn");
    const logoutBtn = document.querySelector('[data-action="logout"]');

    // Configura o evento do Logo (MindCare)
    document.querySelectorAll(".logo").forEach(logo => {
        logo.style.cursor = 'pointer';
        logo.onclick = () => {
            window.location.href = "index.html";
        };
    });

    if (user) {
        // Logado
        if (loginBtn) loginBtn.style.display = "none";
        if (registerBtn) registerBtn.style.display = "none";

        if (dashboardBtn) {
            dashboardBtn.style.display = "inline-block";
            dashboardBtn.onclick = () => {
                const targetPage = user.role === "professional" ? "dashboard-pro.html" : "dashboard-user.html";
                window.location.href = targetPage;
            };
        }
        if (logoutBtn) logoutBtn.style.display = "inline-block";

    } else {
        // Deslogado
        if (loginBtn) {
            loginBtn.style.display = "inline-block";
            loginBtn.onclick = () => {
                window.location.href = "login.html";
            };
        }
        if (registerBtn) {
            registerBtn.style.display = "inline-block";
            registerBtn.onclick = () => {
                window.location.href = "register.html";
            };
        }
        if (dashboardBtn) dashboardBtn.style.display = "none";
        if (logoutBtn) logoutBtn.style.display = "none";
    }

    // Configura o evento de Logout (Pode estar em qualquer página)
    if (logoutBtn) {
        logoutBtn.onclick = () => {
            clearUserLocal();
            showToast("Sessão encerrada!");
            window.location.href = "index.html";
        };
    }
}

// ============================================================
// FUNÇÃO GLOBAL: SELEÇÃO DE HUMOR (Usada em index e dashboard-user)
// ============================================================
function setupMoodSelection() {
    const moodGrid = document.getElementById('moodGrid');
    if (!moodGrid) return;

    const moodOptions = moodGrid.querySelectorAll('.mood-option');
    const saveMoodBtn = document.getElementById('saveMoodBtn');
    const moodNote = document.getElementById('moodNote');

    moodOptions.forEach(option => {
        option.addEventListener('click', () => {
            moodOptions.forEach(item => item.classList.remove('selected'));
            option.classList.add('selected');
        });
    });

    if (saveMoodBtn) {
        saveMoodBtn.onclick = async () => {
            const selectedMood = document.querySelector('.mood-option.selected');
            if (!selectedMood) {
                return showToast("Selecione um humor antes de salvar.", "error");
            }
            const moodValue = selectedMood.getAttribute('data-mood');
            const noteValue = moodNote ? moodNote.value.trim() : '';

            // Chamada SIMULADA (usando o jsonPost do script.js)
            const data = await jsonPost({
                action: 'saveMood',
                mood: moodValue,
                note: noteValue
            });
            if (data.success) {
                showToast(`Humor '${moodValue}' salvo com sucesso!`, "success");
                moodOptions.forEach(item => item.classList.remove('selected'));
                if (moodNote) moodNote.value = '';
            }
        };
    }
}

// ------------------------
// Chamada inicial (para garantir que o header sempre funcione)
// ------------------------
document.addEventListener("DOMContentLoaded", () => {
    updateHeaderUI();
    // Verifica se o usuário logado está tentando acessar login/register e redireciona
    const user = getUserFromLocal();
    if (user) {
        const path = window.location.pathname;
        if (path.includes('login.html') || path.includes('register.html')) {
            const targetPage = user.role === 'professional' ? 'dashboard-pro.html' : 'dashboard-user.html';
            window.location.replace(targetPage);
        }
    }
});