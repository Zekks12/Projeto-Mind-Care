<?php
// allin.php — All-in-one backend (session-based)
// Place at project root. Requires MySQL DB 'mindcare' created earlier.

header("Content-Type: application/json; charset=utf-8");
session_start();

// DB config — edit if necessary
$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "";
$DB_NAME = "mindcare";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "DB connect error"]);
    exit;
}
$conn->set_charset("utf8mb4");

// helper
function resp($data) {
    echo json_encode($data);
    exit;
}

$action = $_REQUEST['action'] ?? null;

// -----------------------------
// UTIL
// -----------------------------
function getSessionUser() {
    return $_SESSION['user'] ?? null;
}
function requireLogin() {
    if (!isset($_SESSION['user'])) resp(["success" => false, "message" => "not_logged"]);
}
function safeFetch($arr, $k, $default = null) {
    return isset($arr[$k]) ? $arr[$k] : $default;
}
// -----------------------------
// ROUTES
// -----------------------------
if ($action === "register") {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // CORREÇÃO 1: O JS envia o tipo como 'type', não 'role'. 
    $role = $_POST['type'] ?? 'user'; // user | professional
    $crm = $_POST['crm'] ?? null;

    if (!$name || !$email || !$password) resp(["success" => false, "message" => "missing_fields"]);

    // check exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->fetch_assoc()) resp(["success" => false, "message" => "email_exists"]);

    $hash = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO users (name,email,password,type,crm) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $email, $hash, $role, $crm);
    
    if ($stmt->execute()) {
        
        // CORREÇÃO 2: Precisamos retornar o objeto do usuário para o JS
        $newUserId = $conn->insert_id;
        
        // Fetch o usuário recém-criado
        $stmt2 = $conn->prepare("SELECT id, name, email, type AS role, crm FROM users WHERE id = ?");
        $stmt2->bind_param("i", $newUserId);
        $stmt2->execute();
        $newUser = $stmt2->get_result()->fetch_assoc();
        
        // Armazena na sessão
        $_SESSION['user'] = $newUser;
        
        // Retorna o objeto do usuário para o JavaScript
        resp(["success" => true, "user" => $newUser]);
        
    } else resp(["success" => false, "message" => "db_error", "error" => $stmt->error]);
}

// ... restante do código (login, logout, etc.) ...
// LOGIN -> sets PHP session; returns user object
if ($action === "login") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    if (!$email || !$password) resp(["success" => false, "message" => "missing_fields"]);

    $stmt = $conn->prepare("SELECT id,name,email,password,type,crm,created_at FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();
    if (!$user) resp(["success" => false, "message" => "user_not_found"]);

    if (!password_verify($password, $user['password'])) resp(["success" => false, "message" => "invalid_password"]);

    // remove password field before storing session
    unset($user['password']);
    $_SESSION['user'] = $user;

    resp(["success" => true, "user" => $user]);
}

// LOGOUT
if ($action === "logout") {
    session_unset();
    session_destroy();
    resp(["success" => true]);
}

// GET CURRENT USER (from session)
if ($action === "get_current_user") {
    $u = getSessionUser();
    if ($u) resp(["success" => true, "user" => $u]);
    else resp(["success" => false, "message" => "not_logged"]);
}

// ---------- MOODS ----------
if ($action === "saveMood") {
    requireLogin();
    $user = getSessionUser();
    $mood = $_POST['mood'] ?? null;
    $note = $_POST['note'] ?? null;
    if (!$mood) resp(["success" => false, "message" => "missing_mood"]);

    $stmt = $conn->prepare("INSERT INTO moods (user_id, mood, note) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user['id'], $mood, $note);
    $ok = $stmt->execute();
    resp(["success" => $ok]);
}

if ($action === "listMoods") {
    requireLogin();
    $user = getSessionUser();
    $stmt = $conn->prepare("SELECT id, mood, note, created_at FROM moods WHERE user_id = ? ORDER BY created_at DESC LIMIT 200");
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    resp($rows);
}

// ---------- HABITS ----------
if ($action === "saveHabit") {
    requireLogin();
    $user = getSessionUser();
    $habit = $_POST['habit'] ?? null;
    if (!$habit) resp(["success" => false, "message" => "missing_habit"]);
    $stmt = $conn->prepare("INSERT INTO user_habits (user_id, habit_id, habit_name, completed_at) VALUES (?, NULL, ?, NOW())");
    $stmt->bind_param("is", $user['id'], $habit);
    $ok = $stmt->execute();
    resp(["success" => $ok]);
}

if ($action === "listHabits") {
    requireLogin();
    $user = getSessionUser();
    $stmt = $conn->prepare("SELECT id, habit_name, completed_at FROM user_habits WHERE user_id = ? ORDER BY completed_at DESC");
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    resp($rows);
}

// ---------- SESSIONS (AGENDAMENTO) ----------
if ($action === "createSession") {
    requireLogin();
    $user = getSessionUser();
    $pro_id = intval($_POST['pro_id'] ?? 0);
    $date = $_POST['date'] ?? null;
    $time = $_POST['time'] ?? null;
    $notes = $_POST['notes'] ?? null;
    if (!$pro_id || !$date || !$time) resp(["success" => false, "message" => "missing_fields"]);

    $stmt = $conn->prepare("INSERT INTO sessions (user_id, professional_id, date, time, status, notes) VALUES (?, ?, ?, ?, 'pending', ?)");
    $stmt->bind_param("iisss", $user['id'], $pro_id, $date, $time, $notes);
    $ok = $stmt->execute();
    resp(["success" => $ok]);
}

// list sessions for user (viewer)
if ($action === "listSessionsForUser") {
    requireLogin();
    $user = getSessionUser();
    $stmt = $conn->prepare("SELECT s.id, s.date, s.time, s.status, s.notes, s.professional_id, u.name as professional_name FROM sessions s LEFT JOIN users u ON u.id = s.professional_id WHERE s.user_id = ? ORDER BY s.date ASC, s.time ASC");
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    resp($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
}

// list sessions for professional
if ($action === "listSessionsForPro") {
    requireLogin();
    $user = getSessionUser();
    // only professionals should call, but we'll still filter by session user id
    $stmt = $conn->prepare("SELECT s.id, s.date, s.time, s.status, s.notes, s.user_id, u.name as user_name FROM sessions s LEFT JOIN users u ON u.id = s.user_id WHERE s.professional_id = ? ORDER BY s.date ASC, s.time ASC");
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    resp($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
}

// ---------- ARTICLES ----------
if ($action === "createArticle") {
    requireLogin();
    $user = getSessionUser();
    if ($user['type'] !== 'professional' && $user['type'] !== 'professional') resp(["success" => false, "message" => "not_allowed"]);
    $title = $_POST['title'] ?? null;
    $content = $_POST['content'] ?? null;
    $category = $_POST['category'] ?? null;
    if (!$title || !$content) resp(["success" => false, "message" => "missing_fields"]);
    $stmt = $conn->prepare("INSERT INTO articles (title, category, content, professional_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $title, $category, $content, $user['id']);
    $ok = $stmt->execute();
    resp(["success" => $ok]);
}

if ($action === "listArticles") {
    $stmt = $conn->prepare("SELECT a.*, u.name as author_name FROM articles a LEFT JOIN users u ON u.id = a.professional_id ORDER BY created_at DESC");
    $stmt->execute();
    resp($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
}

// ---------- PROFILE ----------
if ($action === "updateProfile") {
    requireLogin();
    $user = getSessionUser();
    $name = $_POST['name'] ?? null;
    $crm = $_POST['crm'] ?? null;
    if (!$name) resp(["success" => false, "message" => "missing_name"]);
    $stmt = $conn->prepare("UPDATE users SET name = ?, crm = ? WHERE id = ?");
    $stmt->bind_param("ssi", $name, $crm, $user['id']);
    $ok = $stmt->execute();
    if ($ok) {
        // update session user
        $_SESSION['user']['name'] = $name;
        $_SESSION['user']['crm'] = $crm;
    }
    resp(["success" => $ok]);
}

// fallback
resp(["success" => false, "message" => "invalid_action"]);
// chama allin.php?action=get_current_user para checar sessão
function syncUserFromServer() {
  return fetch("allin.php", {
    method: "POST",
    body: new FormData(Object.fromEntries([["action","get_current_user"]]))
  }).then(r => r.json())
  .then(res => {
    if (res.success && res.user) {
      saveUserToLocal(res.user);
      updateHeaderAuthUI();
      return res.user;
    } else {
      clearUserLocal();
      updateHeaderAuthUI();
      return null;
    }
  }).catch(() => { clearUserLocal(); updateHeaderAuthUI(); return null; });
}
