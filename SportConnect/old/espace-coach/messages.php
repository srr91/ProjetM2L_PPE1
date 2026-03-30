<?php
require_once '../configuration/database.php';
require_once '../configuration/session.php';
requireRole('coach');

$conn = getConnection();
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT id, prenom, nom, photo_profil FROM utilisateurs WHERE id = ? LIMIT 1");
$stmt->execute([$user_id]);
$me = $stmt->fetch();

function sc_avatar_color($name) {
    $s = strtolower(trim((string)$name));
    if ($s === '') $s = 'user';
    $hash = md5($s);
    $h = (int)round((hexdec(substr($hash, 0, 2)) / 255) * 360);
    return 'hsl(' . $h . ', 70%, 45%)';
}

$selected_id = isset($_GET['with']) ? (int)$_GET['with'] : 0;
$other_id = $selected_id;

// Liste des conversations (sportifs) du coach
$stmt = $conn->prepare("
    SELECT 
        CASE 
            WHEN sender_id = ? THEN receiver_id
            ELSE sender_id
        END AS other_id,
        MAX(date_envoi) AS last_date
    FROM messages
    WHERE sender_id = ? OR receiver_id = ?
    GROUP BY other_id
    ORDER BY last_date DESC
");
$stmt->execute([$user_id, $user_id, $user_id]);
$conversations = $stmt->fetchAll();

$unreadBySender = [];
$stmt = $conn->prepare("SELECT sender_id, COUNT(*) AS nb FROM messages WHERE receiver_id = ? AND lu = 0 GROUP BY sender_id");
$stmt->execute([$user_id]);
$rowsUnread = $stmt->fetchAll();
foreach ($rowsUnread as $r) {
    $unreadBySender[(int)$r['sender_id']] = (int)$r['nb'];
}

$lastDateByOther = [];
foreach ($conversations as $c) {
    $lastDateByOther[(int)$c['other_id']] = $c['last_date'];
}

$contacts = [];
if (!empty($conversations)) {
    $ids = [];
    foreach ($conversations as $c) {
        $ids[] = (int)$c['other_id'];
    }
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $stmt = $conn->prepare("SELECT id, prenom, nom, role, photo_profil FROM utilisateurs WHERE id IN ($placeholders) AND role = 'sportif'");
    $stmt->execute($ids);
    $rows = $stmt->fetchAll();

    $byId = [];
    foreach ($rows as $r) {
        $byId[(int)$r['id']] = $r;
    }

    foreach ($conversations as $c) {
        $oid = (int)$c['other_id'];
        if (isset($byId[$oid])) {
            $contacts[] = $byId[$oid];
        }
    }
}

$sportif = null;
$error = '';

if ($other_id > 0) {
    $stmt = $conn->prepare("SELECT id, prenom, nom, role, photo_profil FROM utilisateurs WHERE id = ? AND role = 'sportif' LIMIT 1");
    $stmt->execute([$other_id]);
    $sportif = $stmt->fetch();

    if (!$sportif) {
        $error = "Sportif introuvable.";
        $other_id = 0;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $other_id > 0) {
    $contenu = trim($_POST['contenu'] ?? '');

    if ($contenu === '') {
        $error = "Veuillez écrire un message.";
    } else {
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, contenu) VALUES (?, ?, ?)");
        if ($stmt->execute([$user_id, $other_id, $contenu])) {
            header('Location: /SportConnect/espace-coach/messages.php?with=' . $other_id);
            exit();
        }
        $error = "Une erreur est survenue lors de l'envoi.";
    }
}

$messages = [];
if ($other_id > 0) {
    // Marquer comme lus les messages reçus
    $stmt = $conn->prepare("UPDATE messages SET lu = 1 WHERE sender_id = ? AND receiver_id = ? AND lu = 0");
    $stmt->execute([$other_id, $user_id]);

    $stmt = $conn->prepare("
        SELECT * FROM messages
        WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
        ORDER BY date_envoi ASC
        LIMIT 200
    ");
    $stmt->execute([$user_id, $other_id, $other_id, $user_id]);
    $messages = $stmt->fetchAll();
}

$titre = 'Messages - SportConnect';
require_once '../vues/mise-en-page/entete-sous-dossier.php';
?>

<style>
    body {
        background:
            radial-gradient(1200px 700px at 15% -10%, rgba(120, 92, 255, 0.35), transparent 60%),
            radial-gradient(900px 500px at 90% 10%, rgba(0, 208, 255, 0.28), transparent 58%),
            radial-gradient(900px 550px at 60% 105%, rgba(255, 60, 140, 0.18), transparent 60%),
            linear-gradient(180deg, #0B1020 0%, #070A14 100%);
        color: rgba(255,255,255,0.92);
        font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, "Apple Color Emoji", "Segoe UI Emoji";
        letter-spacing: 0.2px;
    }

    .container {
        max-width: 1200px;
    }

    .container > div[style*="grid-template-columns"] {
        background: rgba(255,255,255,0.02);
        border: 1px solid rgba(255,255,255,0.06);
        border-radius: 26px;
        padding: 18px;
        box-shadow: 0 24px 60px rgba(0,0,0,0.45);
    }

    .container {
        --primary: #7C5CFF;
        --border: rgba(255,255,255,0.10);
        --gray: rgba(255,255,255,0.70);
        --sc-accent: #00D0FF;
        --sc-hot: #FF3C8C;
        --sc-surface: rgba(255,255,255,0.07);
        --sc-surface-2: rgba(255,255,255,0.10);
        --sc-text: rgba(255,255,255,0.92);
        --sc-text-dim: rgba(255,255,255,0.70);
        --sc-shadow: 0 18px 45px rgba(0,0,0,0.40);
        --sc-ring: 0 0 0 4px rgba(124,92,255,0.25);
    }

    .card {
        background: linear-gradient(180deg, rgba(255,255,255,0.10), rgba(255,255,255,0.06)) !important;
        border: 1px solid rgba(255,255,255,0.12) !important;
        border-radius: 22px !important;
        box-shadow: var(--sc-shadow);
        overflow: hidden;
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
    }

    .card-header {
        background:
            radial-gradient(800px 180px at 20% 0%, rgba(124,92,255,0.35), transparent 60%),
            radial-gradient(700px 220px at 95% 0%, rgba(0,208,255,0.25), transparent 60%),
            rgba(255,255,255,0.04) !important;
        border-bottom: 1px solid rgba(255,255,255,0.10) !important;
    }

    .card-title {
        color: rgba(255,255,255,0.94);
        letter-spacing: 0.6px;
        text-transform: uppercase;
        font-weight: 900;
        font-size: 0.95rem;
    }

    .alert.alert-danger {
        border-radius: 16px;
        border: 1px solid rgba(255,60,140,0.35);
        background: rgba(255,60,140,0.12);
        color: rgba(255,255,255,0.94);
        box-shadow: 0 14px 30px rgba(0,0,0,0.35);
    }

    .btn {
        border-radius: 16px !important;
        transition: transform .18s ease, box-shadow .18s ease, background-color .18s ease, border-color .18s ease, color .18s ease;
        will-change: transform;
    }
    .btn:focus-visible {
        outline: none;
        box-shadow: var(--sc-ring) !important;
    }

    .btn.btn-outline {
        background: rgba(255,255,255,0.06) !important;
        border: 1px solid rgba(255,255,255,0.16) !important;
        color: rgba(255,255,255,0.92) !important;
    }
    .btn.btn-outline:hover {
        transform: translateY(-1px);
        box-shadow: 0 14px 35px rgba(0,0,0,0.40);
        border-color: rgba(124,92,255,0.55) !important;
        background: rgba(124,92,255,0.14) !important;
    }

    [data-sc-conv-item] {
        border-radius: 18px;
        background: rgba(255,255,255,0.06) !important;
        border: 1px solid rgba(255,255,255,0.12) !important;
        box-shadow: 0 12px 30px rgba(0,0,0,0.25);
        transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease, background-color .16s ease;
        position: relative;
        overflow: hidden;
    }
    [data-sc-conv-item]::before {
        content: "";
        position: absolute;
        inset: 0;
        background: radial-gradient(700px 220px at 10% 0%, rgba(0,208,255,0.12), transparent 60%);
        opacity: 0;
        transition: opacity .16s ease;
        pointer-events: none;
    }
    [data-sc-conv-item]:hover {
        transform: translateY(-2px);
        box-shadow: 0 18px 40px rgba(0,0,0,0.38);
        border-color: rgba(124,92,255,0.45) !important;
    }
    [data-sc-conv-item]:hover::before { opacity: 1; }

    [data-sc-conv-unread] {
        background: linear-gradient(135deg, var(--sc-hot), #FFB000) !important;
        box-shadow: 0 10px 24px rgba(0,0,0,0.35);
    }

    .sc-chat { --sc-avatar-size: 36px; --sc-avatar-gap: 10px; }
    .sc-chat {
        flex: 1;
        overflow: auto;
        padding: 18px;
        background: rgba(255,255,255,0.06);
        border: 1px solid rgba(255,255,255,0.10);
        border-radius: 20px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        justify-content: flex-end;
        position: relative;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.07);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
    }

    .sc-chat::-webkit-scrollbar { width: 10px; }
    .sc-chat::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, rgba(124,92,255,0.65), rgba(0,208,255,0.45));
        border-radius: 999px;
        border: 2px solid rgba(10,12,24,0.6);
    }
    .sc-chat::-webkit-scrollbar-track { background: rgba(0,0,0,0.15); }

    .sc-msg-row {
        display: flex;
        align-items: flex-start;
        gap: var(--sc-avatar-gap);
    }
    .sc-msg-row--mine { justify-content: flex-end; }
    .sc-msg-row--theirs { justify-content: flex-start; }

    .sc-msg-col {
        display: flex;
        flex-direction: column;
        max-width: 70%;
    }
    .sc-msg-row--mine .sc-msg-col { align-items: flex-end; }
    .sc-msg-row--theirs .sc-msg-col { align-items: flex-start; }

    .sc-avatar,
    .sc-avatar-fallback {
        width: var(--sc-avatar-size);
        height: var(--sc-avatar-size);
        border-radius: 50%;
        flex: 0 0 var(--sc-avatar-size);
        box-shadow: 0 10px 22px rgba(0,0,0,0.35);
    }
    .sc-avatar {
        object-fit: cover;
        border: 2px solid rgba(255,255,255,0.80);
        background: rgba(255,255,255,0.90);
    }
    .sc-avatar-fallback {
        background: radial-gradient(circle at 30% 20%, rgba(255,255,255,0.26), transparent 52%), var(--sc-avatar-bg, #8E8E93);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 950;
        letter-spacing: 0.5px;
        border: 2px solid rgba(255,255,255,0.55);
    }

    .sc-bubble {
        max-width: 100%;
        padding: 12px 16px;
        border-radius: 18px;
        white-space: pre-wrap;
        line-height: 1.5;
        box-shadow: 0 12px 30px rgba(0,0,0,0.30);
        border: 1px solid rgba(255,255,255,0.10);
        transform: translateZ(0);
        transition: transform .12s ease, box-shadow .12s ease;
    }
    .sc-bubble:hover {
        transform: translateY(-1px);
        box-shadow: 0 16px 38px rgba(0,0,0,0.40);
    }
    .sc-bubble--mine {
        background: linear-gradient(135deg, rgba(124,92,255,0.95), rgba(0,208,255,0.75));
        color: #06101C;
        border-bottom-right-radius: 8px;
    }
    .sc-bubble--theirs {
        background: rgba(255,255,255,0.10);
        color: rgba(255,255,255,0.94);
        border-bottom-left-radius: 8px;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }

    .sc-meta {
        margin-top: 7px;
        font-size: 12px;
        color: rgba(255,255,255,0.70);
        letter-spacing: 0.4px;
    }
    .sc-msg-row--mine .sc-meta { text-align: right; }
    .sc-msg-row--theirs .sc-meta { text-align: left; }

    .sc-status { margin-left: 6px; font-weight: 900; }
    .sc-status--sent { color: rgba(255,255,255,0.65); }
    .sc-status--read { color: rgba(0,208,255,0.95); }

    .sc-composer {
        position: sticky;
        bottom: 0;
        background: linear-gradient(180deg, rgba(10,12,24,0.35), rgba(10,12,24,0.65));
        border-top: 1px solid rgba(255,255,255,0.10);
        padding: 14px;
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
    }
    .sc-composer form {
        display: flex;
        gap: 12px;
        align-items: flex-end;
    }
    .sc-input {
        flex: 1;
        border-radius: 18px;
        background: rgba(255,255,255,0.07);
        border: 1px solid rgba(255,255,255,0.14);
        padding: 12px 14px;
        min-height: 44px;
        max-height: 140px;
        resize: none;
        line-height: 1.45;
        color: rgba(255,255,255,0.92);
        outline: none;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.05);
        transition: box-shadow .15s ease, border-color .15s ease, background-color .15s ease;
    }
    .sc-input::placeholder { color: rgba(255,255,255,0.55); }
    .sc-input:focus {
        border-color: rgba(124,92,255,0.65);
        background: rgba(255,255,255,0.09);
        box-shadow: var(--sc-ring), inset 0 1px 0 rgba(255,255,255,0.05);
    }
    .sc-send {
        width: 46px;
        height: 46px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(255,255,255,0.18);
        background: linear-gradient(135deg, rgba(124,92,255,0.95), rgba(255,60,140,0.75));
        color: rgba(255,255,255,0.96);
        font-weight: 950;
        cursor: pointer;
        opacity: 0.55;
        box-shadow: 0 14px 34px rgba(0,0,0,0.45);
        transition: transform .16s ease, box-shadow .16s ease, opacity .16s ease, filter .16s ease;
    }
    .sc-send.is-active {
        opacity: 1;
        filter: saturate(1.05);
    }
    .sc-send:hover { transform: translateY(-1px); box-shadow: 0 18px 44px rgba(0,0,0,0.55); }
    .sc-send:disabled { cursor: not-allowed; transform: none; }

    .sc-new-msg {
        position: absolute;
        right: 14px;
        bottom: 86px;
        z-index: 5;
        display: none;
        background: rgba(255,255,255,0.12);
        color: rgba(255,255,255,0.92);
        border: 1px solid rgba(255,255,255,0.20);
        border-radius: 999px;
        padding: 10px 12px;
        font-weight: 900;
        letter-spacing: 0.4px;
        box-shadow: 0 18px 40px rgba(0,0,0,0.40);
        cursor: pointer;
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        transition: transform .16s ease, background-color .16s ease, border-color .16s ease;
    }
    .sc-new-msg:hover {
        transform: translateY(-1px);
        background: rgba(124,92,255,0.18);
        border-color: rgba(124,92,255,0.45);
    }

    @media (max-width: 1000px) {
        .container > div[style*="grid-template-columns"] {
            grid-template-columns: 1fr !important;
            gap: 1rem !important;
        }
    }

    @media (max-width: 768px) {
        .sc-chat { --sc-avatar-size: 28px; --sc-avatar-gap: 8px; padding: 14px; }
        .sc-msg-col { max-width: 85%; }
        .sc-bubble { padding: 11px 12px; }
        .sc-composer { padding: 12px; }
        .sc-new-msg { bottom: 78px; }
    }
</style>

<div class="container" style="margin-top: 2rem;">
    <div style="display: grid; grid-template-columns: 320px 1fr; gap: 1.5rem; align-items: start;">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Messages</h2>
            </div>

            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <?php if (empty($contacts)): ?>
                    <div style="color: var(--gray); padding: 1rem; text-align: center;">
                        Aucune conversation
                    </div>
                <?php else: ?>
                    <?php foreach ($contacts as $c): ?>
                        <?php
                            $cid = (int)$c['id'];
                            $active = ($other_id === $cid);
                            $unread = $unreadBySender[$cid] ?? 0;
                            $lastDate = $lastDateByOther[$cid] ?? null;
                        ?>
                        <a href="/SportConnect/espace-coach/messages.php?with=<?= (int)$c['id'] ?>" style="text-decoration: none; color: inherit;">
                            <div data-sc-conv-item="<?= $cid ?>" style="padding: 0.9rem; border: 2px solid <?= $active ? 'var(--primary)' : 'var(--border)' ?>; background: white;">
                                <div style="display:flex; justify-content: space-between; gap: 0.5rem; align-items: center;">
                                    <div style="font-weight: <?= $unread > 0 ? '900' : '800' ?>;">
                                    <?= htmlspecialchars($c['prenom'] . ' ' . $c['nom']) ?>
                                    </div>
                                    <span data-sc-conv-unread="<?= $cid ?>" style="<?= $unread > 0 ? '' : 'display:none;' ?> min-width: 22px; height: 22px; padding: 0 7px; border-radius: 999px; background: #DC3545; color: #fff; font-weight: 800; font-size: 0.8rem; display: inline-flex; align-items: center; justify-content: center;">
                                        <?= (int)$unread ?>
                                    </span>
                                </div>
                                <div style="color: var(--gray); font-size: 0.9rem;">
                                    Sportif
                                </div>
                                <?php if ($lastDate): ?>
                                    <div data-sc-conv-last="<?= $cid ?>" style="color: var(--gray); font-size: 0.8rem; margin-top: 0.35rem;">
                                        <?= date('d/m/Y H:i', strtotime($lastDate)) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="card" style="min-height: 520px; display: flex; flex-direction: column;">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; gap: 1rem;">
                <div>
                    <div style="font-weight: 900; font-size: 1.2rem;">
                        <?php if ($sportif): ?>
                            <?= htmlspecialchars($sportif['prenom'] . ' ' . $sportif['nom']) ?>
                        <?php else: ?>
                            Sélectionnez une conversation
                        <?php endif; ?>
                    </div>
                    <?php if ($sportif): ?>
                        <div style="color: var(--gray);">Sportif</div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div id="chat" class="sc-chat">
                <button type="button" id="scNewMsg" class="sc-new-msg">Nouveau message ↓</button>
                <?php if ($other_id === 0): ?>
                    <div style="color: var(--gray); text-align: center; padding: 2rem;">
                        Choisissez un sportif pour répondre.
                    </div>
                <?php else: ?>
                    <?php if (empty($messages)): ?>
                        <div style="color: var(--gray); text-align: center; padding: 2rem;">
                            Aucun message.
                        </div>
                    <?php else: ?>
                        <?php foreach ($messages as $m): ?>
                            <?php $mine = ((int)$m['sender_id'] === $user_id); ?>
                            <?php
                                $otherName = trim(($sportif['prenom'] ?? '') . ' ' . ($sportif['nom'] ?? ''));
                                $meName = trim(($me['prenom'] ?? '') . ' ' . ($me['nom'] ?? ''));
                                $otherColor = sc_avatar_color($otherName);
                                $meColor = sc_avatar_color($meName);
                            ?>
                            <div class="sc-msg-row <?= $mine ? 'sc-msg-row--mine' : 'sc-msg-row--theirs' ?>">
                                <?php if (!$mine): ?>
                                    <?php if (!empty($sportif['photo_profil']) && file_exists(__DIR__ . '/../telechargements/profils/' . $sportif['photo_profil'])): ?>
                                        <img class="sc-avatar" src="/SportConnect/telechargements/profils/<?= htmlspecialchars($sportif['photo_profil']) ?>" alt="" />
                                    <?php else: ?>
                                        <span class="sc-avatar-fallback" style="--sc-avatar-bg: <?= htmlspecialchars($otherColor) ?>;">
                                            <?= strtoupper(substr($sportif['prenom'] ?? 'S', 0, 1)) ?>
                                        </span>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <div class="sc-msg-col">
                                    <div data-sc-msg-id="<?= (int)$m['id'] ?>" class="sc-bubble <?= $mine ? 'sc-bubble--mine' : 'sc-bubble--theirs' ?>">
                                        <?= htmlspecialchars($m['contenu']) ?>
                                    </div>
                                    <div class="sc-meta">
                                        <?php $t = date('H:i', strtotime($m['date_envoi'])); ?>
                                        <span><?= $t ?></span>
                                        <?php if ($mine): ?>
                                            <?php $isRead = ((int)$m['lu'] === 1); ?>
                                            <span class="sc-status <?= $isRead ? 'sc-status--read' : 'sc-status--sent' ?>"><?= $isRead ? '✓✓' : '✓' ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php if ($mine): ?>
                                    <?php if (!empty($me['photo_profil']) && file_exists(__DIR__ . '/../telechargements/profils/' . $me['photo_profil'])): ?>
                                        <img class="sc-avatar" src="/SportConnect/telechargements/profils/<?= htmlspecialchars($me['photo_profil']) ?>" alt="" />
                                    <?php else: ?>
                                        <span class="sc-avatar-fallback" style="--sc-avatar-bg: <?= htmlspecialchars($meColor) ?>;">
                                            <?= strtoupper(substr($me['prenom'] ?? 'M', 0, 1)) ?>
                                        </span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <div class="sc-composer">
                <?php if ($other_id > 0): ?>
                    <form method="POST" id="scComposerForm">
                        <textarea name="contenu" id="scComposerInput" class="sc-input" rows="1" placeholder="Message..."></textarea>
                        <button type="submit" id="scComposerSend" class="sc-send" aria-label="Envoyer">➤</button>
                    </form>
                <?php else: ?>
                    <div style="color: var(--gray); text-align: center; padding: 1rem;">
                        Sélectionnez un sportif pour écrire.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const chat = document.getElementById('chat');
    if (chat) {
        chat.scrollTo({ top: chat.scrollHeight, behavior: 'auto' });
    }

    const newMsgBtn = document.getElementById('scNewMsg');
    if (newMsgBtn && chat) {
        newMsgBtn.addEventListener('click', function() {
            chat.scrollTo({ top: chat.scrollHeight, behavior: 'smooth' });
            newMsgBtn.style.display = 'none';
        });
    }

    const input = document.getElementById('scComposerInput');
    const sendBtn = document.getElementById('scComposerSend');
    const form = document.getElementById('scComposerForm');
    function setSendState() {
        if (!input || !sendBtn) return;
        const hasText = (input.value || '').trim().length > 0;
        if (hasText) {
            sendBtn.classList.add('is-active');
            sendBtn.disabled = false;
        } else {
            sendBtn.classList.remove('is-active');
            sendBtn.disabled = true;
        }
    }
    function autosize() {
        if (!input) return;
        input.style.height = 'auto';
        const h = Math.min(input.scrollHeight, 120);
        input.style.height = h + 'px';
    }
    if (input) {
        input.addEventListener('input', function() { autosize(); setSendState(); });
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (form && !sendBtn.disabled) form.submit();
            }
        });
        autosize();
        setSendState();
    }

    const withId = <?= (int)$other_id ?>;

    const scTheirAvatarUrl = <?= json_encode(!empty($sportif['photo_profil']) ? ('/SportConnect/telechargements/profils/' . $sportif['photo_profil']) : '') ?>;
    const scTheirAvatarExists = <?= (!empty($sportif['photo_profil']) && file_exists(__DIR__ . '/../telechargements/profils/' . ($sportif['photo_profil'] ?? ''))) ? 'true' : 'false' ?>;
    const scTheirInitial = <?= json_encode(strtoupper(substr($sportif['prenom'] ?? 'S', 0, 1))) ?>;
    const scTheirColor = <?= json_encode(sc_avatar_color(trim(($sportif['prenom'] ?? '') . ' ' . ($sportif['nom'] ?? '')))) ?>;

    async function markRead(id) {
        if (!id) return;
        try {
            const fd = new FormData();
            fd.append('with', String(id));
            await fetch('/SportConnect/api/messages-lus.php', { method: 'POST', body: fd, credentials: 'same-origin' });
        } catch (e) {}
    }

    if (withId > 0) {
        markRead(withId);
        const badge = document.querySelector('[data-sc-conv-unread="' + withId + '"]');
        if (badge) badge.style.display = 'none';
    }

    let lastId = 0;
    const msgs = document.querySelectorAll('[data-sc-msg-id]');
    for (let i = 0; i < msgs.length; i++) {
        const v = parseInt(msgs[i].getAttribute('data-sc-msg-id') || '0', 10);
        if (v > lastId) lastId = v;
    }

    function appendIncomingMessage(m) {
        const container = document.getElementById('chat');
        if (!container) return;

        const wasNearBottom = (container.scrollHeight - container.scrollTop - container.clientHeight) < 80;

        const row = document.createElement('div');
        row.className = 'sc-msg-row sc-msg-row--theirs';

        if (scTheirAvatarExists && scTheirAvatarUrl) {
            const img = document.createElement('img');
            img.className = 'sc-avatar';
            img.src = scTheirAvatarUrl;
            img.alt = '';
            row.appendChild(img);
        } else {
            const fb = document.createElement('span');
            fb.className = 'sc-avatar-fallback';
            fb.style.setProperty('--sc-avatar-bg', scTheirColor || '#8E8E93');
            fb.textContent = (scTheirInitial || 'S');
            row.appendChild(fb);
        }

        const col = document.createElement('div');
        col.className = 'sc-msg-col';

        const bubble = document.createElement('div');
        bubble.className = 'sc-bubble sc-bubble--theirs';
        bubble.textContent = (m.contenu || '').toString();
        col.appendChild(bubble);

        const meta = document.createElement('div');
        meta.className = 'sc-meta';
        meta.textContent = (m.date_envoi || '').toString();
        col.appendChild(meta);

        row.appendChild(col);
        container.appendChild(row);

        if (wasNearBottom) {
            container.scrollTo({ top: container.scrollHeight, behavior: 'smooth' });
            if (newMsgBtn) newMsgBtn.style.display = 'none';
        } else {
            if (newMsgBtn) newMsgBtn.style.display = 'inline-flex';
        }
    }

    function updateConvUnread(senderId, inc) {
        const el = document.querySelector('[data-sc-conv-unread="' + senderId + '"]');
        if (!el) return;
        const current = parseInt((el.textContent || '0').trim() || '0', 10);
        const next = Math.max(0, current + inc);
        if (next > 0) {
            el.style.display = 'inline-flex';
            el.textContent = String(next);
        } else {
            el.style.display = 'none';
            el.textContent = '';
        }
    }

    let inFlight = false;
    async function pollChat() {
        if (inFlight) return;
        inFlight = true;
        try {
            const res = await fetch('/SportConnect/api/actualisation-messages.php?last_id=' + encodeURIComponent(String(lastId)), { credentials: 'same-origin', cache: 'no-store' });
            if (!res.ok) return;
            const data = await res.json();
            if (typeof data.max_id === 'number' && data.max_id > lastId) {
                lastId = data.max_id;
            }

            if (!Array.isArray(data.new_messages) || data.new_messages.length === 0) return;

            for (let i = 0; i < data.new_messages.length; i++) {
                const m = data.new_messages[i];
                const senderId = parseInt(m.sender_id || 0, 10);
                if (!senderId) continue;

                if (withId > 0 && senderId === withId) {
                    appendIncomingMessage(m);
                    updateConvUnread(senderId, -999999);
                    markRead(withId);
                } else {
                    updateConvUnread(senderId, 1);
                }
            }
        } catch (e) {
        } finally {
            inFlight = false;
        }
    }

    setInterval(pollChat, 3000);
})();
</script>

<?php require_once '../vues/mise-en-page/pied-de-page-sous-dossier.php'; ?>
