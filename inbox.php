<?php
// inbox: unified chat view for buyers and sellers
session_start();
$root      = "";
$pageTitle = "My Inbox — sgCar";

include "inc/auth.inc.php";

$user_id   = (int)$_SESSION['user_id'];
$filterCar = isset($_GET['car']) ? (int)$_GET['car'] : 0;

include "inc/db.inc.php";

// fetch conversations where user is the seller
$stmt = $conn->prepare("
    SELECT e.enquiry_id, e.sender_name, e.sender_email, e.sender_user_id,
           e.is_read, e.buyer_unread, e.created_at,
           c.car_id, c.brand, c.model, c.year
    FROM enquiries e
    JOIN cars c ON e.car_id = c.car_id
    WHERE c.user_id = ?
    ORDER BY e.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$asSellerRows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// fetch conversations where user is the buyer
$stmt2 = $conn->prepare("
    SELECT e.enquiry_id, e.sender_name, e.sender_email, e.sender_user_id,
           e.is_read, e.buyer_unread, e.created_at,
           c.car_id, c.brand, c.model, c.year,
           u.fname, u.lname
    FROM enquiries e
    JOIN cars c ON e.car_id = c.car_id
    JOIN users u ON c.user_id = u.user_id
    WHERE e.sender_user_id = ?
    ORDER BY e.created_at DESC
");
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$asBuyerRows = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt2->close();

// mark seller's unread as read
$conn->query("UPDATE enquiries e JOIN cars c ON e.car_id = c.car_id SET e.is_read = 1 WHERE c.user_id = $user_id AND e.is_read = 0");
// mark buyer's unread as read
$conn->query("UPDATE enquiries SET buyer_unread = 0 WHERE sender_user_id = $user_id AND buyer_unread = 1");

// merge into one list
$conversations = [];
foreach ($asSellerRows as $e) {
    $conversations[] = array_merge($e, ['role' => 'seller', 'other_party' => $e['sender_name']]);
}
foreach ($asBuyerRows as $e) {
    $conversations[] = array_merge($e, ['role' => 'buyer', 'other_party' => $e['fname'] . ' ' . $e['lname']]);
}

// load all messages for these conversations
$messages = [];
if (!empty($conversations)) {
    $ids          = array_column($conversations, 'enquiry_id');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types        = str_repeat('i', count($ids));
    $stmt3        = $conn->prepare("
        SELECT message_id, enquiry_id, sender_user_id, body, created_at
        FROM messages
        WHERE enquiry_id IN ($placeholders)
        ORDER BY created_at ASC
    ");
    $stmt3->bind_param($types, ...$ids);
    $stmt3->execute();
    foreach ($stmt3->get_result()->fetch_all(MYSQLI_ASSOC) as $msg) {
        $messages[$msg['enquiry_id']][] = $msg;
    }
    $stmt3->close();

    // set last message preview and re-sort by most recent activity
    foreach ($conversations as &$c) {
        $msgList          = $messages[$c['enquiry_id']] ?? [];
        $last             = !empty($msgList) ? $msgList[count($msgList) - 1] : null;
        $c['last_msg']    = $last ? $last['body']       : '';
        $c['last_msg_at'] = $last ? $last['created_at'] : $c['created_at'];
    }
    unset($c);
    usort($conversations, function($a, $b) {
        return strtotime($b['last_msg_at']) - strtotime($a['last_msg_at']);
    });
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <?php include "inc/head.inc.php"; ?>
</head>

<body class="d-flex flex-column min-vh-100">

    <?php include "inc/nav.inc.php"; ?>

    <main id="main-content">

        <div class="bg-light border-bottom py-3">
            <div class="container">
                <h1 class="h4 fw-bold mb-0">My Inbox</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Inbox</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="container py-4">

            <?php if (empty($conversations)): ?>
                <div class="text-center py-5 text-muted border rounded">
                    <span class="material-icons" style="font-size:3rem;">chat_bubble_outline</span>
                    <p class="mt-2">No conversations yet.</p>
                </div>
            <?php else: ?>

                <div class="chat-layout border rounded overflow-hidden">
                    <div class="d-flex" style="height:580px;">

                        <!-- left: conversation list -->
                        <div class="chat-list border-end">
                            <?php foreach ($conversations as $i => $c):
                                $hasUnread = ($c['role'] === 'seller' && $c['is_read'] == 0)
                                          || ($c['role'] === 'buyer'  && $c['buyer_unread'] == 1);
                            ?>
                                <div class="chat-list-item <?= $i === 0 ? 'active' : '' ?>"
                                     data-target="conv-<?= $i ?>"
                                     data-enquiry-id="<?= $c['enquiry_id'] ?>">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="fw-semibold small text-truncate" style="max-width:150px;">
                                            <?= htmlspecialchars($c['year'] . ' ' . $c['brand'] . ' ' . $c['model']) ?>
                                        </span>
                                        <div class="d-flex align-items-center gap-1">
                                            <?php if ($hasUnread): ?>
                                                <span class="chat-unread-dot"></span>
                                            <?php endif; ?>
                                            <span class="text-muted" style="font-size:0.65rem;white-space:nowrap;">
                                                <?= date('d M', strtotime($c['last_msg_at'])) ?>
                                            </span>
                                        </div>
                                    </div>
                                    <p class="text-muted mb-1 text-truncate" style="font-size:0.78rem;">
                                        <?= htmlspecialchars(substr($c['last_msg'], 0, 42)) ?><?= strlen($c['last_msg']) > 42 ? '…' : '' ?>
                                    </p>
                                    <span class="chat-role-badge <?= $c['role'] === 'seller' ? 'badge-seller' : 'badge-buyer' ?>">
                                        <?= $c['role'] === 'seller' ? 'Received' : 'Sent' ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- right: chat thread -->
                        <div class="flex-grow-1" style="min-width:0;">
                            <?php foreach ($conversations as $i => $c):
                                $msgs = $messages[$c['enquiry_id']] ?? [];
                            ?>
                                <div class="chat-thread" id="conv-<?= $i ?>"
                                     style="<?= $i > 0 ? 'display:none;' : '' ?>">

                                    <!-- header -->
                                    <div class="chat-thread-header border-bottom px-3 py-2 bg-light d-flex align-items-center gap-2">
                                        <span class="material-icons text-danger" style="font-size:1rem;">directions_car</span>
                                        <a href="car-detail.php?id=<?= (int)$c['car_id'] ?>"
                                           class="text-decoration-none text-dark fw-semibold small text-truncate">
                                            <?= htmlspecialchars($c['year'] . ' ' . $c['brand'] . ' ' . $c['model']) ?>
                                        </a>
                                        <span class="text-muted small">· <?= htmlspecialchars($c['other_party']) ?></span>
                                    </div>

                                    <!-- messages -->
                                    <div class="chat-messages p-3" id="msgs-<?= $i ?>" tabindex="0" aria-label="Chat messages">
                                        <?php if (empty($msgs)): ?>
                                            <p class="text-muted small text-center fst-italic">No messages yet.</p>
                                        <?php else: ?>
                                            <?php foreach ($msgs as $msg):
                                                $isMe = ((int)$msg['sender_user_id'] === $user_id);
                                            ?>
                                                <div class="d-flex <?= $isMe ? 'justify-content-end' : 'justify-content-start' ?> mb-2">
                                                    <div class="<?= $isMe ? 'chat-sent' : 'chat-received' ?>">
                                                        <?= nl2br(htmlspecialchars($msg['body'])) ?>
                                                        <div class="chat-time"><?= date('d M Y, g:ia', strtotime($msg['created_at'])) ?></div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>

                                    <!-- message input (always visible for both parties) -->
                                    <div class="chat-reply-area border-top p-3">
                                        <form action="process_reply.php" method="post" class="d-flex gap-2 align-items-end">
                                            <input type="hidden" name="enquiry_id" value="<?= (int)$c['enquiry_id'] ?>">
                                            <input type="hidden" name="_ajax" value="1">
                                            <textarea name="body" class="form-control form-control-sm" rows="2"
                                                      placeholder="Write a message…" required maxlength="1000"></textarea>
                                            <button type="submit" class="btn btn-sm btn-sgcar text-nowrap">
                                                <span class="material-icons btn-icon" aria-hidden="true">send</span>
                                                Send
                                            </button>
                                        </form>
                                    </div>

                                </div>
                            <?php endforeach; ?>
                        </div>

                    </div>
                </div>

            <?php endif; ?>

        </div>
    </main>

    <?php include "inc/footer.inc.php"; ?>

<script>
// track last known message_id per enquiry so we only fetch new messages
var lastMsgId = {};
<?php foreach ($conversations as $c):
    $msgList = $messages[$c['enquiry_id']] ?? [];
    $lastId  = !empty($msgList) ? $msgList[count($msgList) - 1]['message_id'] : 0;
?>
lastMsgId[<?= $c['enquiry_id'] ?>] = <?= $lastId ?>;
<?php endforeach; ?>

var myUserId = <?= $user_id ?>;

// switch conversation on left-panel click
document.querySelectorAll('.chat-list-item').forEach(function (item) {
    item.addEventListener('click', function () {
        document.querySelectorAll('.chat-list-item').forEach(function(i) { i.classList.remove('active'); });
        document.querySelectorAll('.chat-thread').forEach(function(t) { t.style.display = 'none'; });
        this.classList.add('active');
        var thread = document.getElementById(this.dataset.target);
        thread.style.display = 'flex';
        var msgs = thread.querySelector('.chat-messages');
        if (msgs) msgs.scrollTop = msgs.scrollHeight;
        pollActive(); // immediately check for new messages when switching
    });
});

// scroll first conversation to bottom on load
var first = document.querySelector('.chat-messages');
if (first) first.scrollTop = first.scrollHeight;

function escHtml(s) {
    return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function fmtDate(dt) {
    var d = new Date(dt.replace(' ', 'T'));
    return d.toLocaleDateString('en-GB', {day:'2-digit', month:'short', year:'numeric'}) + ', ' +
           d.toLocaleTimeString('en-GB', {hour:'numeric', minute:'2-digit', hour12:true});
}

function appendMsg(container, msg) {
    var isMe = (parseInt(msg.sender_user_id) === myUserId);
    // remove "no messages yet" placeholder if still showing
    var placeholder = container.querySelector('.fst-italic');
    if (placeholder) placeholder.closest('.d-flex, p').remove();
    var wrap = document.createElement('div');
    wrap.className = 'd-flex ' + (isMe ? 'justify-content-end' : 'justify-content-start') + ' mb-2';
    wrap.innerHTML = '<div class="' + (isMe ? 'chat-sent' : 'chat-received') + '">' +
        escHtml(msg.body).replace(/\n/g, '<br>') +
        '<div class="chat-time">' + fmtDate(msg.created_at) + '</div></div>';
    container.appendChild(wrap);
}

function pollActive() {
    var active = document.querySelector('.chat-list-item.active');
    if (!active) return;
    var enquiryId = active.dataset.enquiryId;
    var afterId   = lastMsgId[enquiryId] || 0;

    fetch('api/get-messages.php?enquiry_id=' + enquiryId + '&after_id=' + afterId)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.messages || !data.messages.length) return;
            var thread  = document.getElementById(active.dataset.target);
            var msgArea = thread.querySelector('.chat-messages');
            data.messages.forEach(function(msg) {
                appendMsg(msgArea, msg);
                lastMsgId[enquiryId] = msg.message_id;
            });
            msgArea.scrollTop = msgArea.scrollHeight;
        })
        .catch(function() {});
}

// poll the active conversation every 5 seconds for new incoming messages
setInterval(pollActive, 5000);

// intercept reply form submit — send via AJAX so page doesn't reload
document.querySelectorAll('.chat-reply-area form').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var textarea = form.querySelector('textarea');
        if (!textarea.value.trim()) return;
        var data = new FormData(form);
        fetch('process_reply.php', {method: 'POST', body: data})
            .then(function() {
                textarea.value = '';
                pollActive(); // immediately show the just-sent message
            })
            .catch(function() {});
    });
});
</script>

</body>
</html>
